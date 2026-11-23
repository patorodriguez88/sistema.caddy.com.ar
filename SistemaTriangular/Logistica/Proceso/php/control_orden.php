<?php
// Logística > Órdenes > Control de la orden (23/11/2026).
//
// Muestra, en vivo, qué tiene el chofer en su recorrido y qué le falta escanear en Warehouse, con el
// motivo, si la carga quedó confirmada y cada vez que la app lo trabó (tabla RepartoBloqueos, la escribe
// la app de reparto). Desde acá la oficina destraba: saca del recorrido lo que no está (va al
// DEPÓSITO, recorrido 80) o prende "Omitir control de escaneo" (ordenes.php ToggleOmitirEscaneo).
//
// REGLA DE ESCANEO: es la misma que usa la app (4_reparto.caddy.com.ar SistemaReparto/Funciones/
// control_escaneo.php, candidatosSinEscaneoWarehouse). Si se cambia allá, cambiarla acá.
//   - Retiros y colectas (Retirado = 0): no se escanean en Warehouse (se escanean en el cliente).
//   - Entregas a Wepoint (destino 18587) y "retiro y entrega" (pickup_ready de esta orden): tampoco.
//   - El resto de las entregas: escaneadas en Warehouse en ESTA orden (warehouse_validated) o
//     confirmadas por Mercado Libre en la colecta (ml_confirmado). Si no, el chofer no las ve y no arranca.

error_reporting(E_ALL);
ini_set('display_errors', '0');
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');
header('Content-Type: application/json; charset=utf-8');

const CO_WEPOINT_DESTINO = 18587;
const CO_RECORRIDO_DEPOSITO = '80';

function coResponder(array $r): void
{
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['Usuario'])) {
    coResponder(['success' => 0, 'message' => 'La sesión expiró. Volvé a ingresar.']);
}

function coOrden(mysqli $mysqli, int $no): ?array
{
    $st = $mysqli->prepare("SELECT NumerodeOrden, Fecha, Recorrido, NombreChofer, idUsuarioChofer, Estado, HoraSalidaReal,
                                   IFNULL(OmitirControlEscaneo, 0) AS OmitirControlEscaneo, OmitirControlEscaneo_Por
                              FROM Logistica WHERE NumerodeOrden = ? AND Eliminado = 0 LIMIT 1");
    $st->bind_param('i', $no);
    $st->execute();
    $o = $st->get_result()->fetch_assoc();
    $st->close();
    return $o ?: null;
}

if (isset($_POST['Detalle'])) {
    $no = (int)($_POST['NumerodeOrden'] ?? 0);
    $orden = $no > 0 ? coOrden($mysqli, $no) : null;
    if (!$orden) coResponder(['success' => 0, 'message' => 'No encontré la orden.']);
    $recorrido = (string)$orden['Recorrido'];

    // Lo que el chofer tiene hoy en el recorrido (mismo criterio que la app: hoja de ruta abierta).
    $st = $mysqli->prepare("SELECT h.NumerodeOrden AS ordenHdr, h.Posicion, t.id, t.CodigoSeguimiento, t.CodigoProveedor,
                                   t.RazonSocial, t.ClienteDestino, t.DomicilioDestino, t.Cantidad, t.Retirado, t.Entregado,
                                   IFNULL(t.idColecta, 0) AS idColecta, t.idClienteDestino, t.Wepoint_status
                              FROM HojaDeRuta h
                              JOIN TransClientes t ON t.id = h.idTransClientes
                             WHERE h.Recorrido = ? AND h.Estado = 'Abierto' AND h.Eliminado = 0 AND h.Devuelto = 0
                               AND t.Eliminado = 0
                             ORDER BY t.Retirado, h.Posicion, t.CodigoSeguimiento");
    $st->bind_param('s', $recorrido);
    $st->execute();
    $filas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    // Escaneos y movimientos de esos envíos (una consulta, por código: Seguimiento tiene índice).
    $porBase = [];
    $codigos = array_values(array_unique(array_map(fn($f) => (string)$f['CodigoSeguimiento'], $filas)));
    foreach (array_chunk($codigos, 200) as $chunk) {
        $conds = [];
        foreach ($chunk as $c) {
            $e = $mysqli->real_escape_string($c);
            $conds[] = "CodigoSeguimiento = '{$e}'";
            $conds[] = "CodigoSeguimiento LIKE '" . addcslashes($e, '%_') . "\\_%'";
        }
        $res = $mysqli->query("SELECT id, CodigoSeguimiento, status, Usuario, NumerodeOrden FROM Seguimiento
                                WHERE (" . implode(' OR ', $conds) . ")
                                  AND status IN ('warehouse_validated','pickup_scanned','pickup_ready','pickup_not_scanned','wepoint_in','wepoint_out','last_mile')
                                  AND (Eliminado IS NULL OR Eliminado = 0)
                                ORDER BY id");
        while ($res && $r = $res->fetch_assoc()) {
            $cs = strtoupper((string)$r['CodigoSeguimiento']);
            $base = strpos($cs, '_') !== false ? strstr($cs, '_', true) : $cs;
            $porBase[$base][] = $r;
        }
    }

    // Confirmados por Mercado Libre en la colecta (mismo dato que la app: Colecta.ColectaScans).
    $mlConfirmado = [];
    $idsColecta = array_values(array_unique(array_filter(array_map(fn($f) => (int)$f['idColecta'], $filas))));
    if ($idsColecta) {
        $res = $mysqli->query("SELECT ColectaScans FROM Colecta WHERE id IN (" . implode(',', $idsColecta) . ")");
        while ($res && $c = $res->fetch_assoc()) {
            $det = json_decode((string)($c['ColectaScans'] ?? ''), true)['expected']['servicios_detalle'] ?? null;
            if (!is_array($det)) continue;
            foreach ($det as $sd) {
                if (!empty($sd['ml_confirmado']) && (int)($sd['idTransCliente'] ?? 0) > 0) $mlConfirmado[(int)$sd['idTransCliente']] = true;
            }
        }
    }

    $override = (int)$orden['OmitirControlEscaneo'] === 1;
    $envios = [];
    $resumen = ['entregas' => 0, 'escaneadas' => 0, 'pendientes' => 0, 'retiros' => 0, 'entregados' => 0];
    foreach ($filas as $f) {
        $base = strtoupper((string)$f['CodigoSeguimiento']);
        $movs = $porBase[$base] ?? [];
        $ordenHdr = (string)$f['ordenHdr'];
        $tiene = function (string $status, ?string $orden = null) use ($movs): bool {
            foreach ($movs as $m) {
                if ($m['status'] === $status && ($orden === null || (string)$m['NumerodeOrden'] === $orden)) return true;
            }
            return false;
        };
        $ultimoWepoint = null;
        foreach ($movs as $m) if ($m['status'] === 'wepoint_in' || $m['status'] === 'wepoint_out') $ultimoWepoint = $m['status'];

        $esRetiro = (int)$f['Retirado'] === 0;
        $prov = trim((string)$f['CodigoProveedor']);
        $e = [
            'codigo' => (string)$f['CodigoSeguimiento'],
            'pedido' => ($prov !== '' && $prov !== '0') ? $prov : '',
            'cliente' => (string)$f['RazonSocial'],
            'destino' => (string)$f['ClienteDestino'],
            'direccion' => (string)$f['DomicilioDestino'],
            'bultos' => max(1, (int)$f['Cantidad']),
            'tipo' => $esRetiro ? ((int)$f['idColecta'] > 0 ? 'Colecta' : 'Retiro') : 'Entrega',
            'entregado' => (int)$f['Entregado'] === 1,
            'estado' => 'ok',
            'detalle' => '',
            'puede_sacar' => false,
        ];
        if ($e['entregado']) $resumen['entregados']++;

        if ($esRetiro) {
            $resumen['retiros']++;
            $e['detalle'] = 'No se escanea en Warehouse (se escanea en el cliente)';
        } else {
            $resumen['entregas']++;
            if ((int)$f['idClienteDestino'] === CO_WEPOINT_DESTINO) {
                $e['detalle'] = 'Va a Wepoint: no se escanea en Warehouse';
            } elseif ($tiene('pickup_ready', $ordenHdr)) {
                $e['detalle'] = 'Retiro y entrega en esta salida: lo lleva arriba';
            } elseif ($tiene('warehouse_validated', $ordenHdr)) {
                $e['detalle'] = 'Escaneado en Warehouse';
                $resumen['escaneadas']++;
            } elseif (isset($mlConfirmado[(int)$f['id']])) {
                $e['detalle'] = 'Confirmado por Mercado Libre';
                $resumen['escaneadas']++;
            } else {
                $motivos = [];
                if ($e['bultos'] > 1) $motivos[] = "tiene {$e['bultos']} bultos: hay que escanear todos";
                if ($tiene('pickup_not_scanned')) $motivos[] = 'la colecta se cerró sin escanearlo en el cliente';
                if ($ultimoWepoint === 'wepoint_in' || ($ultimoWepoint === null && (string)$f['Wepoint_status'] === 'Ingreso')) {
                    $motivos[] = 'figura en el depósito de Wepoint';
                }
                if ($tiene('warehouse_validated')) $motivos[] = 'se escaneó en otra salida, no en esta';
                $e['estado'] = $override ? 'omitido' : 'pendiente';
                $e['detalle'] = ($override ? 'Sin escanear (control omitido para esta orden)' : 'Falta escanear en Warehouse')
                    . ($motivos ? ': ' . implode('; ', $motivos) : '');
                $e['puede_sacar'] = !$e['entregado'];
                $resumen['pendientes']++;
            }
        }
        $envios[] = $e;
    }

    // ¿Quedó registrada la salida (En Tránsito) de esta orden?
    $st = $mysqli->prepare("SELECT MIN(CONCAT(Fecha, ' ', Hora)) FROM Seguimiento WHERE NumerodeOrden = ? AND status = 'last_mile'");
    $st->bind_param('i', $no);
    $st->execute();
    $st->bind_result($cargaConfirmada);
    $st->fetch();
    $st->close();

    // Cada vez que la app trabó al chofer (si la tabla ya existe).
    $bloqueos = [];
    $hay = $mysqli->query("SHOW TABLES LIKE 'RepartoBloqueos'");
    if ($hay && $hay->num_rows > 0) {
        $st = $mysqli->prepare("SELECT Fecha, Hora, Usuario, Accion, Motivo, Cantidad, Codigos, Mensaje
                                  FROM RepartoBloqueos WHERE NumerodeOrden = ? ORDER BY id DESC LIMIT 30");
        $st->bind_param('i', $no);
        $st->execute();
        $bloqueos = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();
    }

    coResponder([
        'success' => 1,
        'orden' => [
            'numero' => (int)$orden['NumerodeOrden'],
            'recorrido' => $recorrido,
            'chofer' => (string)$orden['NombreChofer'],
            'estado' => (string)$orden['Estado'],
            'salida' => $orden['HoraSalidaReal'],
            'carga_confirmada' => $cargaConfirmada,
            'omitir_escaneo' => $override,
            'omitir_por' => (string)($orden['OmitirControlEscaneo_Por'] ?? ''),
        ],
        'resumen' => $resumen,
        'envios' => $envios,
        'bloqueos' => $bloqueos,
    ]);
}

// Sacar del recorrido un envío que no está para escanear: vuelve al DEPÓSITO (recorrido 80) con el
// mismo cambio de recorrido de siempre (cambiarRecorrido: Seguimiento + aviso a Wepoint si corresponde).
if (isset($_POST['SacarDelRecorrido'])) {
    $no = (int)($_POST['NumerodeOrden'] ?? 0);
    $cs = trim((string)($_POST['cs'] ?? ''));
    $orden = $no > 0 ? coOrden($mysqli, $no) : null;
    if (!$orden || $cs === '') coResponder(['success' => 0, 'message' => 'Faltan datos.']);

    $st = $mysqli->prepare("SELECT t.Recorrido FROM TransClientes t WHERE t.CodigoSeguimiento = ? AND t.Eliminado = 0 AND t.Entregado = 0 LIMIT 1");
    $st->bind_param('s', $cs);
    $st->execute();
    $t = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$t || (string)$t['Recorrido'] !== (string)$orden['Recorrido']) {
        coResponder(['success' => 0, 'message' => 'Ese envío ya no está en el recorrido ' . $orden['Recorrido'] . '.']);
    }

    require_once __DIR__ . '/../../../Funciones/Funciones.php';
    $nota = 'Sacado del recorrido ' . $orden['Recorrido'] . ' (orden ' . $no . ') desde Control de la orden: no estaba para escanear.';
    $r = cambiarRecorrido($mysqli, $cs, CO_RECORRIDO_DEPOSITO, 10, false, $nota);
    coResponder([
        'success' => !empty($r['success']) ? 1 : 0,
        'message' => !empty($r['success']) ? "Listo: {$cs} pasó al DEPÓSITO." : ($r['message'] ?? 'No se pudo sacar del recorrido.'),
    ]);
}

coResponder(['success' => 0, 'message' => 'Acción desconocida.']);
