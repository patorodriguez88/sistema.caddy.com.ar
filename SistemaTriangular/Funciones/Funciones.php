<?php
include_once __DIR__ . "/../Conexion/Conexioni.php";
// HELPERS
function normalizarImporte($valor)
{
    return (float)($valor ?? 0);
}

function ordenNoFacturada(mysqli $mysqli, $numeroOrden)
{
    if ($numeroOrden === '' || $numeroOrden === '0' || $numeroOrden === 0 || $numeroOrden === null) {
        return false;
    }

    $encontrada = false;
    $facturado = 0;
    $estado = '';

    if ($stmt = $mysqli->prepare("
        SELECT Facturado, Estado
        FROM Logistica
        WHERE NumerodeOrden = ?
          AND Eliminado = 0
        LIMIT 1
    ")) {
        $stmt->bind_param('s', $numeroOrden);
        $stmt->execute();
        $stmt->bind_result($facturadoDB, $estadoDB);

        if ($stmt->fetch()) {
            $encontrada = true;
            $facturado = (int)$facturadoDB;
            $estado = (string)$estadoDB;
        }

        $stmt->close();
    }

    if (!$encontrada) {
        return false;
    }

    if ($facturado !== 0) {
        return false;
    }

    return in_array($estado, ['Alta', 'Cargada', 'Pendiente'], true);
}

function recalcularResumenOrdenLogistica(mysqli $mysqli, $numeroOrden)
{
    if ($numeroOrden === '' || $numeroOrden === '0' || $numeroOrden === 0 || $numeroOrden === null) {
        return;
    }

    $cantidadServicios = 0;
    $totalRecorrido = 0.0;

    $sql = "SELECT
                COUNT(*) AS CantidadServicios,
                COALESCE(SUM(Debe),0) AS TotalRecorrido
            FROM TransClientes
            WHERE NumerodeOrden = ?
              AND Eliminado = 0
              AND Entregado = 0
              AND Devuelto = 0
              AND Haber = 0";

    if (!$stmt = $mysqli->prepare($sql)) {
        throw new Exception('No se pudo preparar recálculo de Logistica.');
    }

    $stmt->bind_param('s', $numeroOrden);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $cantidadServicios = (int)$row['CantidadServicios'];
        $totalRecorrido = (float)$row['TotalRecorrido'];
    }

    $stmt->close();

    $upd = "UPDATE Logistica
            SET Servicios = ?, TotalRecorrido = ?
            WHERE NumerodeOrden = ?
              AND Eliminado = 0
            LIMIT 1";

    if (!$stmt = $mysqli->prepare($upd)) {
        throw new Exception('No se pudo preparar UPDATE de resumen Logistica.');
    }

    $stmt->bind_param('ids', $cantidadServicios, $totalRecorrido, $numeroOrden);
    $stmt->execute();

    if ($stmt->errno !== 0) {
        throw new Exception('Error al actualizar Logistica: ' . $stmt->error);
    }

    $stmt->close();
}

function resolverWebhookCliente(mysqli $mysqli, $idCliente)
{
    if ((int)$idCliente <= 0) {
        return null;
    }

    $endpoint = null;
    $token = null;

    if ($stmt = $mysqli->prepare("SELECT EndPoint, Token FROM Webhook WHERE idCliente = ? AND Activo = 1 LIMIT 1")) {
        $stmt->bind_param('i', $idCliente);
        $stmt->execute();
        $stmt->bind_result($endpoint, $token);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found || $endpoint === null || $endpoint === '') {
            return null;
        }

        return ['endpoint' => $endpoint, 'token' => (string)$token];
    }

    return null;
}

// Encola en Webhook_notifications el evento "recorrido_cambio", si Estados.Webhook=1 para el motivo dado.
// Best-effort: se ejecuta después de haber confirmado el cambio de recorrido, y una falla acá nunca revierte el cambio.
function encolarWebhookRecorrido(mysqli $mysqli, $cs, $recorridoAnterior, $recorridoNuevo, $estadoNombre, $notifOrigen, $notifDestino, $usuario)
{
    $encolados = [];

    if (!$notifOrigen && !$notifDestino) {
        return $encolados;
    }

    $idClienteOrigen = 0;
    $idClienteDestino = 0;

    if ($stmt = $mysqli->prepare("SELECT idClienteOrigen, idClienteDestino FROM TransClientes WHERE CodigoSeguimiento = ? LIMIT 1")) {
        $stmt->bind_param('s', $cs);
        $stmt->execute();
        $stmt->bind_result($idClienteOrigen, $idClienteDestino);
        $stmt->fetch();
        $stmt->close();
    }

    $destinatarios = [];
    if ($notifOrigen)  { $destinatarios['origen']  = (int)$idClienteOrigen; }
    if ($notifDestino) { $destinatarios['destino'] = (int)$idClienteDestino; }

    $Fecha = date('Y-m-d');
    $Hora  = date('H:i');

    $payload = json_encode([
        'evento'              => 'recorrido_cambio',
        'codigo_seguimiento'  => $cs,
        'recorrido_anterior'  => $recorridoAnterior,
        'recorrido_nuevo'     => $recorridoNuevo,
        'estado'              => $estadoNombre,
        'fecha'                => $Fecha . 'T' . $Hora,
    ], JSON_UNESCAPED_UNICODE);

    foreach ($destinatarios as $rol => $idCliente) {
        if ($idCliente <= 0) {
            continue;
        }

        $webhook = resolverWebhookCliente($mysqli, $idCliente);
        if ($webhook === null) {
            continue;
        }

        if ($stmt = $mysqli->prepare("
            INSERT INTO Webhook_notifications
                (idCliente, idCaddy, idProveedor, Servidor, State, Estado, Fecha, Hora, User, Response, Send, Stop)
            VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, 0, 0, 0)
        ")) {
            $stmt->bind_param(
                'isssssss',
                $idCliente,
                $cs,
                $webhook['endpoint'],
                $payload,
                $estadoNombre,
                $Fecha,
                $Hora,
                $usuario
            );
            $stmt->execute();
            $stmt->close();
            $encolados[] = $rol;
        }
    }

    return $encolados;
}

// CAMBIO DE RECORRIDO — punto único: valida, mueve TransClientes+HojaDeRuta+Logistica en
// transacción, registra Seguimiento con el motivo (estadoIdIn, FK a Estados) y, si ese Estado
// tiene Webhook=1, encola el aviso a quien corresponda (origen/destino) vía encolarWebhookRecorrido().
// Devuelve el mismo array que antes se echoeaba directo — cualquier llamador (HTTP o server-side)
// recibe la misma forma de respuesta.
function cambiarRecorrido(mysqli $mysqli, string $cs, string $r, int $estadoIdIn = 10): array
{
    if ($cs === '' || $r === '') {
        return ['success' => 0, 'message' => 'Faltan parámetros: cs y/o r.'];
    }

    //Verificar en TransClientes que el cs no esté entregado, devuelto, eliminado o en haber
    $transOk = false;
    if ($stmt = $mysqli->prepare("SELECT 1 FROM TransClientes WHERE CodigoSeguimiento = ? AND Eliminado = 0 AND Entregado = 0 AND Devuelto = 0 AND Haber = 0 LIMIT 1")) {
        $stmt->bind_param('s', $cs);
        $stmt->execute();
        $stmt->store_result();
        $transOk = ($stmt->num_rows > 0);
        $stmt->close();
    }
    if (!$transOk) {
        return ['success' => 0, 'message' => 'El Código de Seguimiento no es válido para cambio de recorrido.'];
    }

    // ---- 0) Traer orden anterior e importe del servicio ----
    $ordenAnterior = '';
    $importeServicio = 0.0;
    $recorridoAnterior = '';

    if ($stmt = $mysqli->prepare("
        SELECT NumerodeOrden, Debe, Recorrido
        FROM TransClientes
        WHERE CodigoSeguimiento = ?
          AND Eliminado = 0
          AND Entregado = 0
          AND Devuelto = 0
          AND Haber = 0
        LIMIT 1
    ")) {
        $stmt->bind_param('s', $cs);
        $stmt->execute();
        $stmt->bind_result($ordenAnteriorDB, $debeDB, $recorridoAnteriorDB);

        if ($stmt->fetch()) {
            $ordenAnterior = (string)$ordenAnteriorDB;
            $importeServicio = normalizarImporte($debeDB);
            $recorridoAnterior = (string)$recorridoAnteriorDB;
        }

        $stmt->close();
    }

    // ---- Variables de sesión (null-safe) ----
    $Fecha    = date("Y-m-d");
    $Hora     = date("H:i");
    $Usuario  = isset($_SESSION['Usuario'])  ? (string)$_SESSION['Usuario']  : 'sistema';
    $Sucursal = isset($_SESSION['Sucursal']) ? (string)$_SESSION['Sucursal'] : '';

    // ---- 1) Validar que el recorrido exista y esté activo ----
    $recOk = false;
    if ($stmt = $mysqli->prepare("SELECT 1 FROM Recorridos WHERE Numero = ? AND Activo = 1 LIMIT 1")) {
        $stmt->bind_param('s', $r);
        $stmt->execute();
        $stmt->store_result();
        $recOk = ($stmt->num_rows > 0);
        $stmt->close();
    }
    if (!$recOk) {
        return ['success' => 0, 'message' => 'El recorrido no existe o no está activo.'];
    }

    // ---- 2) Buscar orden operativa en Logistica para ese recorrido (si existe) ----
    $NO = 0;
    $NombreChofer = '';

    if ($stmt = $mysqli->prepare("
        SELECT NumerodeOrden, NombreChofer
        FROM Logistica
        WHERE Recorrido = ?
          AND Eliminado = 0
          AND Facturado = 0
          AND Estado IN ('Alta','Cargada','Pendiente')
        ORDER BY Fecha DESC, NumerodeOrden DESC
        LIMIT 1
    ")) {
        $stmt->bind_param('s', $r);
        $stmt->execute();
        $stmt->bind_result($numOrdDB, $choferDB);

        if ($stmt->fetch()) {
            $NO = (int)$numOrdDB;
            $NombreChofer = (string)$choferDB;
        }

        $stmt->close();
    }

    // ---- 2.1) Si el servicio ya está en el mismo recorrido, no hacer nada ----
    if ((string)$recorridoAnterior === (string)$r) {
        return [
            'success' => 0,
            'message' => 'El servicio ya pertenece al recorrido ' . $r . '. No se realizaron cambios.',
            'ya_estaba' => 1,
            'numerodeorden' => $NO,
            'orden_anterior' => $ordenAnterior,
            'recorrido_anterior' => $recorridoAnterior,
            'recorrido_nuevo' => $r
        ];
    }

    // ---- 3) Validación administrativa sobre órdenes afectadas ----
    // Solo validar destino si efectivamente existe una orden destino
    if ($NO != 0 && !ordenNoFacturada($mysqli, $NO)) {
        return ['success' => 0, 'message' => 'La orden destino no está disponible para asignación.'];
    }

    if ($ordenAnterior !== '' && $ordenAnterior !== '0' && $ordenAnterior != $NO) {
        if (!ordenNoFacturada($mysqli, $ordenAnterior)) {
            return ['success' => 0, 'message' => 'La orden origen no puede modificarse por consistencia administrativa.'];
        }
    }

    // ---- 4) Traer último Seguimiento para completar campos heredados ----
    $seguimiento = null;

    if ($stmt = $mysqli->prepare("SELECT idCliente, Retirado, Visitas, idTransClientes, Destino
        FROM Seguimiento
        WHERE CodigoSeguimiento = ? AND Eliminado = 0
        ORDER BY id DESC
        LIMIT 1
    ")) {
        $stmt->bind_param('s', $cs);
        $stmt->execute();
        $res = $stmt->get_result();
        $seguimiento = $res ? $res->fetch_assoc() : null;
        $stmt->close();
    }

    // Valores null-safe para el INSERT
    $idCliente        = isset($seguimiento['idCliente'])        ? (int)$seguimiento['idCliente']        : 0;
    $Retirado         = isset($seguimiento['Retirado'])         ? (int)$seguimiento['Retirado']         : 0;
    $Visitas          = isset($seguimiento['Visitas'])          ? (int)$seguimiento['Visitas']          : 0;
    $idTransClientes  = isset($seguimiento['idTransClientes'])  ? (int)$seguimiento['idTransClientes']  : 0;
    $Destino          = isset($seguimiento['Destino'])          ? (string)$seguimiento['Destino']       : '';

    // ---- 5) Transacción: TC + HDR + SEG ----
    $ok_tc = 0;
    $ok_hdr = 0;
    $ok_seg = 0;

    $mysqli->begin_transaction();
    try {
        // 5.a) TransClientes
        if ($stmt = $mysqli->prepare("
            UPDATE TransClientes
            SET Recorrido = ?, NumerodeOrden = ?, Transportista = ?
            WHERE CodigoSeguimiento = ? AND Eliminado = 0 AND Entregado = 0 AND Devuelto = 0 AND Haber = 0
            LIMIT 1
        ")) {
            $stmt->bind_param('siss', $r, $NO, $NombreChofer, $cs);
            $stmt->execute();

            if ($stmt->errno !== 0) {
                throw new Exception('Error al actualizar TransClientes');
            }

            if ($stmt->affected_rows === 0) {
                throw new Exception('No se actualizó TransClientes. Verifique el estado del servicio.');
            }

            $ok_tc = 1;
            $stmt->close();
        } else {
            throw new Exception('No se pudo preparar UPDATE TransClientes');
        }

        // 5.a.1) Recalcular resumen de Logistica
        // Primero la orden anterior (si era distinta)
        if ($ordenAnterior !== '' && $ordenAnterior !== '0' && $ordenAnterior != $NO) {
            recalcularResumenOrdenLogistica($mysqli, $ordenAnterior);
        }

        // Después la nueva orden
        if ($NO != 0) {
            recalcularResumenOrdenLogistica($mysqli, $NO);
        }

        // 5.b) HojaDeRuta
        $sqlHdr = "UPDATE HojaDeRuta
            SET Recorrido = ?, NumerodeOrden = ?
            WHERE Seguimiento = ? AND Eliminado = 0
        ";
        if ($stmt = $mysqli->prepare($sqlHdr)) {
            // tipos: s (Recorrido), i (NumerodeOrden), s (CodigoSeguimiento/Seguimiento)
            $stmt->bind_param('sis', $r, $NO, $cs);
            $stmt->execute();
            if ($stmt->errno !== 0) {
                throw new Exception('Error al actualizar HojaDeRuta');
            }
            $ok_hdr = 1; // aunque no toque filas, no es error (puede no existir en HR)
            $stmt->close();
        } else {
            throw new Exception('No se pudo preparar UPDATE HojaDeRuta');
        }

        // 5.c) Seguimiento
        $Observaciones = 'CMS: Cambio a Recorrido ' . $r;
        $Entregado     = 0;
        $Devuelto      = 0;
        $Estado_id     = $estadoIdIn;
        $visitasDummy  = 0;

        // Traer state_id, status y flags de webhook desde Estados
        $sql_status = "SELECT Estado, slug, Visitas, Webhook, Notificacion_origen, Notificacion_destino FROM Estados WHERE id=?";
        $state_id = 0;
        $status = 0;
        $estadoWebhook = 0;
        $notifOrigen = 0;
        $notifDestino = 0;

        if ($stmt = $mysqli->prepare($sql_status)) {
            $stmt->bind_param('i', $Estado_id);
            $stmt->execute();
            $stmt->bind_result($EstadoSeg, $slug, $visitasDummy, $estadoWebhook, $notifOrigen, $notifDestino);
            if ($stmt->fetch()) {
                $state_id = $Estado_id;
                $status = $slug;
            } else {
                // estado_id inválido: no rompemos el cambio de recorrido, pero no hay Estado con qué documentarlo.
                $EstadoSeg = 'Movimiento Interno';
                $state_id = $Estado_id;
                $status = '';
            }
            $stmt->close();
        }

        if ($stmt = $mysqli->prepare("INSERT INTO Seguimiento
            (Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,idCliente,
             Retirado,Visitas,idTransClientes,Recorrido,Devuelto,NumerodeOrden,Destino,Estado_id,state_id,status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")) {
            $stmt->bind_param(
                'ssssssisiiiisiisiis',
                $Fecha,
                $Hora,
                $Usuario,
                $Sucursal,
                $cs,
                $Observaciones,
                $Entregado,
                $EstadoSeg,
                $idCliente,
                $Retirado,
                $Visitas,
                $idTransClientes,
                $r,
                $Devuelto,
                $NO,
                $Destino,
                $Estado_id,
                $state_id,
                $status
            );
            $stmt->execute();
            if ($stmt->errno !== 0) {
                throw new Exception('Error al insertar Seguimiento');
            }
            $ok_seg = 1;
            $stmt->close();
        } else {
            throw new Exception('No se pudo preparar INSERT Seguimiento');
        }

        $mysqli->commit();

        // Webhook: best-effort, fuera de la transacción del cambio de recorrido en sí.
        // El cambio de recorrido ya está commiteado en este punto: una falla acá nunca debe
        // reportarse como que el cambio de recorrido falló.
        $webhookEncolado = [];
        $webhookError = null;
        if ((int)$estadoWebhook === 1) {
            try {
                $webhookEncolado = encolarWebhookRecorrido(
                    $mysqli,
                    $cs,
                    $recorridoAnterior,
                    $r,
                    $EstadoSeg,
                    (int)$notifOrigen === 1,
                    (int)$notifDestino === 1,
                    $Usuario
                );
            } catch (Throwable $eWebhook) {
                $webhookError = $eWebhook->getMessage();
                error_log('encolarWebhookRecorrido failed for ' . $cs . ': ' . $webhookError);
            }
        }

        return [
            'success'        => 1,
            'message'        => 'OK',
            'numerodeorden'  => $NO,
            'orden_anterior' => $ordenAnterior,
            'recorrido_anterior' => $recorridoAnterior,
            'recorrido_nuevo'    => $r,
            'importe'        => $importeServicio,
            'tc_updated'     => $ok_tc,
            'hdr_updated'    => $ok_hdr,
            'seg_inserted'   => $ok_seg,
            'webhook_encolado' => $webhookEncolado,
            'webhook_error'  => $webhookError
        ];
    } catch (Exception $e) {
        $mysqli->rollback();
        return [
            'success' => 0,
            'message' => $e->getMessage(),
        ];
    }
}

//CAMBIO DE RECORRIDO — entrada HTTP (JSON o x-www-form-urlencoded), usada por el front-end directo
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);

if (isset($in['ActualizaRecorrido'])) {
    header('Content-Type: application/json; charset=utf-8');

    $cs = isset($in['cs']) ? trim($in['cs']) : '';
    $r  = isset($in['r'])  ? trim($in['r'])  : '';
    // Motivo del cambio (FK a Estados). Si no se envía, se mantiene el comportamiento actual: Movimiento Interno.
    $estadoIdIn = isset($in['estado_id']) ? (int)$in['estado_id'] : 10;

    echo json_encode(cambiarRecorrido($mysqli, $cs, $r, $estadoIdIn));
    exit;
}
