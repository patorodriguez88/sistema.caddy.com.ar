<?php
include_once "../Conexion/Conexioni.php";
//CAMBIO DE RECORRIDO
// ---- Entrada universal (JSON o x-www-form-urlencoded) ----
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);

if (isset($in['ActualizaRecorrido'])) {

    header('Content-Type: application/json; charset=utf-8');

    // ---- Inputs ----
    $cs = isset($in['cs']) ? trim($in['cs']) : '';
    $r  = isset($in['r'])  ? trim($in['r'])  : '';

    if ($cs === '' || $r === '') {
        echo json_encode(['success' => 0, 'message' => 'Faltan parámetros: cs y/o r.']);
        exit;
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
        echo json_encode(['success' => 0, 'message' => 'El Código de Seguimiento no es válido para cambio de recorrido.']);
        exit;
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
        echo json_encode(['success' => 0, 'message' => 'El recorrido no existe o no está activo.']);
        exit;
    }

    // ---- 2) Buscar hoja abierta en Logistica para tomar NumerodeOrden y NombreChofer (si hay) ----
    $NO = 0;
    $NombreChofer = '';
    if ($stmt = $mysqli->prepare("
        SELECT NumerodeOrden, NombreChofer
        FROM Logistica
        WHERE Recorrido = ? AND Estado <> 'Cerrado' AND Eliminado = 0
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
            // tipos: s (Recorrido), i (NumerodeOrden), s (Transportista), s (CodigoSeguimiento)
            $stmt->bind_param('siss', $r, $NO, $NombreChofer, $cs);
            $stmt->execute();
            if ($stmt->errno !== 0) {
                throw new Exception('Error al actualizar TransClientes');
            }
            $ok_tc = 1;
            $stmt->close();
        } else {
            throw new Exception('No se pudo preparar UPDATE TransClientes');
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

        // 5.c) Seguimiento (Movimiento Interno)
        $Observaciones = 'CMS: Cambio a Recorrido ' . $r;
        // $EstadoSeg     = 'Movimiento Interno';
        $Entregado     = 0;
        $Devuelto      = 0;
        $Estado_id        = 10; // Estado "Movimiento Interno"
        $estadoNombre = '';        // (no lo usás, pero lo leo por claridad)
        $estadoSlug = '';
        $visitasDummy = 0;

        // Traer state_id y status desde Estados
        $sql_status = "SELECT Estado,slug,Visitas FROM Estados WHERE id=?";
        $state_id = 0;
        $status = 0;

        if ($stmt = $mysqli->prepare($sql_status)) {
            $stmt->bind_param('i', $Estado_id);
            $stmt->execute();
            $stmt->bind_result($EstadoSeg, $slug, $visitasDummy);
            if ($stmt->fetch()) {
                $state_id = $Estado_id;
                $status = $slug;
            }
            $stmt->close();
        }


        if ($stmt = $mysqli->prepare("INSERT INTO Seguimiento
            (Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,idCliente,
             Retirado,Visitas,idTransClientes,Recorrido,Devuelto,NumerodeOrden,Destino,Estado_id,state_id,status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")) {
            // tipos: s s s s s s i s i i i i s i i s (16)
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
        echo json_encode([
            'success'        => 1,
            'message'        => 'OK',
            'numerodeorden'  => $NO,
            'tc_updated'     => $ok_tc,
            'hdr_updated'    => $ok_hdr,
            'seg_inserted'   => $ok_seg

        ]);
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
}
