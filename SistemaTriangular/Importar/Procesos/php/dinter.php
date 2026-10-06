<?php
// Importación de Recorridos de Dinter -> Preventa (Importar/dinter.php).
//
// Archivo: una fila por entrega, sin encabezado: Nº de cliente Dinter ; Cantidad ; Importe ; Recorrido
// (ej. "1602;1;33510,00;09"). CSV (; o ,) o Excel (primeras 4 columnas).
//
// Reemplaza al importador del sistema viejo (Datos/Importar.php), que insertaba cada línea
// directo en PreVenta y recién después buscaba el cliente: si el número no existía quedaba una
// preventa sin destinatario, y el recorrido quedaba el de Dinter ("09"), que en Caddy no existe
// (el "RECORRIDO 9" de Caddy es el 1511). Acá:
//   1) previsualizar: se matchea cada número contra Clientes (idProveedor + Relacion = origen)
//      SIN grabar nada, y se sugiere el recorrido de Caddy para cada recorrido Dinter.
//   2) confirmar: se vuelve a matchear en el servidor y se cargan en PreVenta solo las filas
//      elegidas que matchean con un único cliente, con el recorrido que confirmó el operador.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function dinter_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

// "33.510,00" / "33510,00" / "33510.00" -> 33510.0
function dinter_importe($v): float
{
    $s = preg_replace('/[^\d.,\-]/', '', (string)$v);
    if ($s === '' || $s === '-') return 0.0;
    $coma = strrpos($s, ',');
    $punto = strrpos($s, '.');
    if ($coma !== false && ($punto === false || $coma > $punto)) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        $s = str_replace(',', '', $s);
    }
    return round((float)$s, 2);
}

// Lee el archivo subido y devuelve [[nro, cantidad, importe, recorrido, linea], ...]
function dinter_leer(array $archivo): array
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo.');
    }
    $ext = strtolower(pathinfo((string)$archivo['name'], PATHINFO_EXTENSION));
    $filas = [];
    if (in_array($ext, ['xlsx', 'xls'], true)) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        $hoja = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo['tmp_name'])->getActiveSheet();
        foreach ($hoja->toArray(null, true, false, false) as $i => $r) {
            $filas[] = [$r[0] ?? '', $r[1] ?? '', $r[2] ?? '', $r[3] ?? '', $i + 1];
        }
    } elseif (in_array($ext, ['csv', 'txt'], true)) {
        $texto = (string)file_get_contents($archivo['tmp_name']);
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }
        $lineas = preg_split('/\r\n|\r|\n/', $texto);
        $delim = substr_count($lineas[0] ?? '', ';') >= substr_count($lineas[0] ?? '', ',') ? ';' : ',';
        foreach ($lineas as $i => $l) {
            if (trim($l) === '') continue;
            $r = str_getcsv($l, $delim);
            $filas[] = [$r[0] ?? '', $r[1] ?? '', $r[2] ?? '', $r[3] ?? '', $i + 1];
        }
    } else {
        throw new RuntimeException('Formato no soportado: subí un .csv o un Excel (.xlsx).');
    }

    $out = [];
    foreach ($filas as [$nro, $cant, $imp, $rec, $linea]) {
        $nroTxt = trim((string)$nro);
        // Encabezado o fila vacía: el número de cliente tiene que ser numérico
        if ($nroTxt === '' || !preg_match('/^\d+$/', $nroTxt)) continue;
        $out[] = [
            'linea' => $linea,
            'nro' => (int)$nroTxt,
            'nro_txt' => $nroTxt,
            'cantidad' => max(1, (int)preg_replace('/\D/', '', (string)$cant)),
            'importe' => dinter_importe($imp),
            'rec_dinter' => trim((string)$rec),
        ];
    }
    return $out;
}

// Matchea las filas contra Clientes y arma la sugerencia de recorrido Caddy
function dinter_analizar(mysqli $mysqli, int $origen, array $filas, string $fechaEntrega): array
{
    $stCli = $mysqli->prepare("SELECT id, nombrecliente, Direccion, Ciudad, Recorrido FROM Clientes
                                WHERE Relacion = ? AND idProveedor = ? AND Eliminado = 0");
    $stHdr = $mysqli->prepare("SELECT Recorrido FROM HojaDeRuta WHERE idCliente = ? AND Eliminado = 0
                                 AND Recorrido NOT IN ('', '0', '80') ORDER BY id DESC LIMIT 1");
    $stPrev = $mysqli->prepare("SELECT COUNT(*) FROM PreVenta WHERE NCliente = ? AND idClienteDestino = ?
                                  AND Eliminado = 0 AND Cargado = 0");
    $stEnv = $mysqli->prepare("SELECT COUNT(*) FROM TransClientes WHERE idClienteDestino = ? AND FechaEntrega = ?
                                 AND Eliminado = 0");
    $origenTxt = (string)$origen;

    $recorridos = [];
    $r = $mysqli->query("SELECT Numero, Nombre FROM Recorridos");
    while ($f = $r->fetch_assoc()) {
        $recorridos[(int)$f['Numero']] = (string)$f['Nombre'];
    }

    $vistos = [];
    $votos = []; // rec_dinter => [rec_caddy => cantidad de clientes]
    foreach ($filas as &$f) {
        $f['estado'] = 'ok';
        $f['motivo'] = '';
        $f['cliente'] = null;
        $f['rec_ultimo'] = null;

        $nro = $f['nro'];
        $stCli->bind_param('si', $origenTxt, $nro);
        $stCli->execute();
        $clientes = $stCli->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($clientes) === 0) {
            $f['estado'] = 'error';
            $f['motivo'] = 'No hay ningún cliente de este origen con ese número de Dinter.';
        } elseif (count($clientes) > 1) {
            $f['estado'] = 'error';
            $f['motivo'] = 'Hay ' . count($clientes) . ' clientes con ese número (' .
                implode(', ', array_map(fn($c) => $c['id'] . ' ' . $c['nombrecliente'], $clientes)) . '). Hay que dejar uno solo.';
        } else {
            $c = $clientes[0];
            $f['cliente'] = ['id' => (int)$c['id'], 'nombre' => $c['nombrecliente'], 'direccion' => $c['Direccion'], 'ciudad' => $c['Ciudad']];
            $idc = (int)$c['id'];

            if (isset($vistos[$nro])) {
                $f['estado'] = 'duplicado';
                $f['motivo'] = 'El número se repite en el archivo (línea ' . $vistos[$nro] . ').';
            } else {
                $vistos[$nro] = $f['linea'];
                $stPrev->bind_param('si', $origenTxt, $idc);
                $stPrev->execute();
                if ((int)$stPrev->get_result()->fetch_row()[0] > 0) {
                    $f['estado'] = 'duplicado';
                    $f['motivo'] = 'Ya tiene una preventa de este origen sin aceptar.';
                } elseif ($fechaEntrega !== '') {
                    $stEnv->bind_param('is', $idc, $fechaEntrega);
                    $stEnv->execute();
                    if ((int)$stEnv->get_result()->fetch_row()[0] > 0) {
                        $f['estado'] = 'aviso';
                        $f['motivo'] = 'Ya tiene un envío cargado para esa fecha de entrega.';
                    }
                }
            }

            $stHdr->bind_param('i', $idc);
            $stHdr->execute();
            $ult = $stHdr->get_result()->fetch_row();
            if ($ult && isset($recorridos[(int)$ult[0]])) {
                $f['rec_ultimo'] = (int)$ult[0];
                $votos[$f['rec_dinter']][(int)$ult[0]] = ($votos[$f['rec_dinter']][(int)$ult[0]] ?? 0) + 1;
            }
        }
    }
    unset($f);

    // Sugerencia de recorrido Caddy por cada recorrido Dinter del archivo:
    // 1) el más usado en la última hoja de ruta de esos clientes; 2) un recorrido de Caddy con ese
    // mismo número; 3) uno que se llame "RECORRIDO <n>".
    $grupos = [];
    foreach ($filas as $f) {
        $rd = $f['rec_dinter'];
        if (!isset($grupos[$rd])) $grupos[$rd] = ['rec_dinter' => $rd, 'filas' => 0, 'sugerido' => null, 'por' => ''];
        $grupos[$rd]['filas']++;
    }
    foreach ($grupos as $rd => &$g) {
        if (!empty($votos[$rd])) {
            arsort($votos[$rd]);
            $g['sugerido'] = (int)array_key_first($votos[$rd]);
            $g['por'] = 'último recorrido de estos clientes';
        } elseif ($rd !== '' && ctype_digit($rd) && isset($recorridos[(int)$rd])) {
            $g['sugerido'] = (int)$rd;
            $g['por'] = 'mismo número en Caddy';
        } elseif ($rd !== '' && ctype_digit($rd)) {
            foreach ($recorridos as $num => $nom) {
                if (preg_match('/^\s*recorrido\s+0*' . (int)$rd . '\s*$/i', $nom)) {
                    $g['sugerido'] = $num;
                    $g['por'] = 'nombre "' . $nom . '"';
                    break;
                }
            }
        }
        $g['sugerido_nombre'] = $g['sugerido'] !== null ? ($recorridos[$g['sugerido']] ?? '') : '';
    }
    unset($g);

    return [$filas, array_values($grupos), $recorridos];
}

$accion = $_POST['accion'] ?? '';
$origen = (int)($_POST['origen'] ?? 36);
$fechaEntrega = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['fecha_entrega'] ?? '')) ? $_POST['fecha_entrega'] : '';

try {
    // Clientes que tienen destinatarios relacionados (DINTER S.A. CBA = 36, etc.)
    if ($accion === 'origenes') {
        $r = $mysqli->query("SELECT c.id, c.nombrecliente, COUNT(*) AS relacionados
                               FROM Clientes r JOIN Clientes c ON c.id = r.Relacion
                              WHERE r.Eliminado = 0 AND r.Relacion REGEXP '^[0-9]+$' AND r.idProveedor > 0
                              GROUP BY c.id, c.nombrecliente ORDER BY relacionados DESC");
        dinter_out(['ok' => true, 'origenes' => $r->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($accion === 'previsualizar') {
        $filas = dinter_leer($_FILES['archivo'] ?? []);
        if (!$filas) {
            dinter_out(['ok' => false, 'error' => 'El archivo no tiene filas con número de cliente.']);
        }
        [$filas, $grupos, $recorridos] = dinter_analizar($mysqli, $origen, $filas, $fechaEntrega);
        // Se guarda lo leído para confirmar sin volver a subir el archivo
        $token = bin2hex(random_bytes(8));
        $_SESSION['importar_dinter'][$token] = ['origen' => $origen, 'filas' => array_map(
            fn($f) => array_intersect_key($f, array_flip(['linea', 'nro', 'nro_txt', 'cantidad', 'importe', 'rec_dinter'])),
            $filas
        )];
        $lista = [];
        foreach ($recorridos as $num => $nom) $lista[] = ['numero' => $num, 'nombre' => $nom];
        dinter_out(['ok' => true, 'token' => $token, 'filas' => $filas, 'grupos' => $grupos, 'recorridos' => $lista]);
    }

    if ($accion === 'confirmar') {
        $token = (string)($_POST['token'] ?? '');
        $guardado = $_SESSION['importar_dinter'][$token] ?? null;
        if (!$guardado || (int)$guardado['origen'] !== $origen) {
            dinter_out(['ok' => false, 'error' => 'La vista previa venció. Volvé a subir el archivo.']);
        }
        if ($fechaEntrega === '') {
            dinter_out(['ok' => false, 'error' => 'Elegí la fecha de entrega.']);
        }
        $lineasElegidas = array_map('intval', (array)($_POST['lineas'] ?? []));
        $mapa = [];
        foreach ((array)json_decode((string)($_POST['recorridos'] ?? '{}'), true) as $rd => $rc) {
            $mapa[(string)$rd] = (int)$rc;
        }

        // Se vuelve a matchear acá: no se confía en lo que mandó el navegador
        [$filas, , $recorridos] = dinter_analizar($mysqli, $origen, $guardado['filas'], $fechaEntrega);

        $st = $mysqli->prepare("SELECT nombrecliente, Direccion, Ciudad FROM Clientes WHERE id = ?");
        $st->bind_param('i', $origen);
        $st->execute();
        $cliOrigen = $st->get_result()->fetch_assoc();
        if (!$cliOrigen) {
            dinter_out(['ok' => false, 'error' => 'No existe el cliente de origen.']);
        }

        $ins = $mysqli->prepare("INSERT INTO PreVenta
            (Fecha, RazonSocial, NCliente, TipoDeComprobante, NumeroComprobante, Cantidad, ClienteDestino,
             idClienteDestino, DomicilioDestino, LocalidadDestino, NumeroVenta, DomicilioOrigen, LocalidadOrigen,
             Usuario, Cargado, EntregaEn, Eliminado, Recorrido, idProveedor, FechaEntrega, ValorDeclarado, Origen)
            VALUES (CURDATE(), ?, ?, 'SOLICITUD WEB', 49, ?, ?, ?, ?, ?, 0, ?, ?, ?, 0, 'Domicilio', 0, ?, ?, ?, ?, 'IMPORTACION_DINTER')");
        $usuario = (string)($_SESSION['Usuario'] ?? '');
        $origenTxt = (string)$origen;
        $cargadas = 0;
        $salteadas = [];

        $mysqli->begin_transaction();
        foreach ($filas as $f) {
            if (!in_array((int)$f['linea'], $lineasElegidas, true)) continue;
            if (!in_array($f['estado'], ['ok', 'aviso'], true) || !$f['cliente']) {
                $salteadas[] = 'Línea ' . $f['linea'] . ' (' . $f['nro_txt'] . '): ' . $f['motivo'];
                continue;
            }
            $rec = $mapa[$f['rec_dinter']] ?? 0;
            if (!isset($recorridos[$rec])) {
                $salteadas[] = 'Línea ' . $f['linea'] . ' (' . $f['nro_txt'] . '): falta elegir el recorrido de Caddy.';
                continue;
            }
            $c = $f['cliente'];
            $recTxt = (string)$rec;
            $idProv = (string)$f['nro'];
            $ins->bind_param(
                'ssisissssssssd',
                $cliOrigen['nombrecliente'], $origenTxt, $f['cantidad'], $c['nombre'], $c['id'], $c['direccion'], $c['ciudad'],
                $cliOrigen['Direccion'], $cliOrigen['Ciudad'], $usuario, $recTxt, $idProv, $fechaEntrega, $f['importe']
            );
            $ins->execute();
            $cargadas++;
        }
        $mysqli->commit();
        unset($_SESSION['importar_dinter'][$token]);
        dinter_out(['ok' => true, 'cargadas' => $cargadas, 'salteadas' => $salteadas]);
    }

    dinter_out(['ok' => false, 'error' => 'Acción no válida']);
} catch (Throwable $e) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        try { $mysqli->rollback(); } catch (Throwable $e2) {}
    }
    error_log('importar dinter: ' . $e->getMessage());
    dinter_out(['ok' => false, 'error' => $e->getMessage()]);
}
