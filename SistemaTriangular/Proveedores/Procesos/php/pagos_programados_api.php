<?php
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : '';

function responder($success, $message = '', $data = array())
{
    echo json_encode(array(
        'success' => $success,
        'message' => $message,
        'data' => $data
    ));
    exit;
}

function n($valor)
{
    return is_numeric($valor) ? (float)$valor : 0;
}

function obtenerSaldoDisponibleFactura($mysqli, $idTransProveedores)
{
    $sql = "
        SELECT 
            TP.id,
            TP.Debe,
            IFNULL(AP.Pagado, 0) AS Pagado,
            IFNULL(PP.Programado, 0) AS Programado,
            TP.Debe - IFNULL(AP.Pagado, 0) - IFNULL(PP.Programado, 0) AS SaldoDisponible
        FROM TransProveedores TP
        LEFT JOIN (
            SELECT 
                idTransProveedores,
                SUM(Haber) AS Pagado
            FROM AnticiposProveedores
            WHERE Eliminado = 0
            GROUP BY idTransProveedores
        ) AP ON AP.idTransProveedores = TP.id
        LEFT JOIN (
            SELECT 
                idTransProveedores,
                SUM(importe_programado) AS Programado
            FROM pagos_programados_proveedores
            WHERE eliminado = 0
              AND estado IN ('PROGRAMADO','REPROGRAMADO')
            GROUP BY idTransProveedores
        ) PP ON PP.idTransProveedores = TP.id
        WHERE TP.id = ?
          AND TP.Eliminado = 0
          AND TP.Fecha >='2026-01-01'
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $idTransProveedores);
    $stmt->execute();

    $res = $stmt->get_result();

    if (!$res || $res->num_rows == 0) {
        return false;
    }

    return $res->fetch_assoc();
}

if ($accion == 'listar_facturas_pendientes') {

    $sql = "
        SELECT 
            TP.id,
            TP.Fecha,
            TP.RazonSocial,
            TP.Cuit,
            TP.TipoDeComprobante,
            TP.NumeroComprobante,
            TP.CompraMercaderia,
            TP.Debe,
            TP.Concepto,
            TP.FormaDePago,
            TP.Descripcion,
            TP.idProveedor,
            IFNULL(AP.Pagado, 0) AS Pagado,
            IFNULL(PP.Programado, 0) AS Programado,
            TP.Debe - IFNULL(AP.Pagado, 0) - IFNULL(PP.Programado, 0) AS SaldoPendiente
        FROM TransProveedores TP
        LEFT JOIN (
            SELECT 
                idTransProveedores,
                SUM(Haber) AS Pagado
            FROM AnticiposProveedores
            WHERE Eliminado = 0
            GROUP BY idTransProveedores
        ) AP ON AP.idTransProveedores = TP.id
        LEFT JOIN (
            SELECT 
                idTransProveedores,
                SUM(importe_programado) AS Programado
            FROM pagos_programados_proveedores
            WHERE eliminado = 0
              AND estado IN ('PROGRAMADO','REPROGRAMADO')
            GROUP BY idTransProveedores
        ) PP ON PP.idTransProveedores = TP.id
        WHERE TP.Eliminado = 0
          AND TP.Debe > 0
        HAVING SaldoPendiente > 0
        ORDER BY TP.Fecha ASC, TP.RazonSocial ASC
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        responder(false, $mysqli->error);
    }

    $data = array();

    while ($row = $res->fetch_assoc()) {
        $row['Debe'] = n($row['Debe']);
        $row['Pagado'] = n($row['Pagado']);
        $row['Programado'] = n($row['Programado']);
        $row['SaldoPendiente'] = n($row['SaldoPendiente']);
        $data[] = $row;
    }

    responder(true, '', $data);
}

if ($accion == 'listar_eventos') {

    $sql = "
        SELECT 
            PP.id,
            PP.idTransProveedores,
            PP.fecha_promesa,
            PP.importe_programado,
            PP.observacion,
            PP.estado,
            TP.RazonSocial,
            TP.Cuit,
            TP.TipoDeComprobante,
            TP.NumeroComprobante,
            TP.Descripcion,
            TP.Concepto
        FROM pagos_programados_proveedores PP
        INNER JOIN TransProveedores TP 
            ON TP.id = PP.idTransProveedores
        WHERE PP.eliminado = 0
          AND TP.Eliminado = 0
        ORDER BY PP.fecha_promesa ASC
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        responder(false, $mysqli->error);
    }

    $eventos = array();
    $hoy = date('Y-m-d');

    while ($row = $res->fetch_assoc()) {

        $color = '#39afd1';

        if ($row['estado'] == 'PAGADO') {
            $color = '#0acf97';
        } elseif ($row['estado'] == 'CANCELADO') {
            $color = '#6c757d';
        } elseif ($row['fecha_promesa'] < $hoy && ($row['estado'] == 'PROGRAMADO' || $row['estado'] == 'REPROGRAMADO')) {
            $color = '#fa5c7c';
        } elseif ($row['fecha_promesa'] == $hoy) {
            $color = '#ffbc00';
        }

        $importe = n($row['importe_programado']);

        $eventos[] = array(
            'id' => $row['id'],
            'title' => $row['RazonSocial'] . ' - $ ' . number_format($importe, 2, ',', '.'),
            'start' => $row['fecha_promesa'],
            'allDay' => true,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => array(
                'idTransProveedores' => $row['idTransProveedores'],
                'RazonSocial' => $row['RazonSocial'],
                'Cuit' => $row['Cuit'],
                'TipoDeComprobante' => $row['TipoDeComprobante'],
                'NumeroComprobante' => $row['NumeroComprobante'],
                'Descripcion' => $row['Descripcion'],
                'Concepto' => $row['Concepto'],
                'importe_programado' => $importe,
                'observacion' => $row['observacion'],
                'estado' => $row['estado']
            )
        );
    }

    echo json_encode($eventos);
    exit;
}

if ($accion == 'programar_pago') {

    $idTransProveedores = isset($_POST['idTransProveedores']) ? (int)$_POST['idTransProveedores'] : 0;
    $fechaPromesa = isset($_POST['fecha_promesa']) ? $_POST['fecha_promesa'] : '';
    $importeProgramado = isset($_POST['importe_programado']) ? (float)$_POST['importe_programado'] : 0;
    $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : '';

    if ($idTransProveedores <= 0) {
        responder(false, 'Factura inválida.');
    }

    if ($fechaPromesa == '') {
        responder(false, 'Debe indicar una fecha de promesa.');
    }

    if ($importeProgramado <= 0) {
        responder(false, 'El importe programado debe ser mayor a cero.');
    }

    $saldo = obtenerSaldoDisponibleFactura($mysqli, $idTransProveedores);

    if (!$saldo) {
        responder(false, 'No se encontró la factura.');
    }

    $saldoDisponible = n($saldo['SaldoDisponible']);

    if ($importeProgramado > $saldoDisponible) {
        responder(false, 'El importe programado supera el saldo disponible de la factura.');
    }

    $estado = 'PROGRAMADO';

    $sql = "
        INSERT INTO pagos_programados_proveedores
        (
            idTransProveedores,
            fecha_promesa,
            importe_programado,
            observacion,
            estado,
            usuario,
            created_at,
            eliminado
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ";

    $fechaAhora = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        responder(false, $mysqli->error);
    }

    $stmt->bind_param(
        "isdssss",
        $idTransProveedores,
        $fechaPromesa,
        $importeProgramado,
        $observacion,
        $estado,
        $usuario,
        $fechaAhora
    );

    if (!$stmt->execute()) {
        responder(false, $stmt->error);
    }

    responder(true, 'Pago programado correctamente.');
}

if ($accion == 'reprogramar_pago') {

    $idProgramacion = isset($_POST['idProgramacion']) ? (int)$_POST['idProgramacion'] : 0;
    $fechaPromesa = isset($_POST['fecha_promesa']) ? $_POST['fecha_promesa'] : '';

    if ($idProgramacion <= 0) {
        responder(false, 'Programación inválida.');
    }

    if ($fechaPromesa == '') {
        responder(false, 'Debe indicar una fecha.');
    }

    $estado = 'REPROGRAMADO';
    $fechaAhora = date('Y-m-d H:i:s');

    $sql = "
        UPDATE pagos_programados_proveedores
        SET 
            fecha_promesa = ?,
            estado = ?,
            updated_at = ?
        WHERE id = ?
          AND eliminado = 0
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        responder(false, $mysqli->error);
    }

    $stmt->bind_param("sssi", $fechaPromesa, $estado, $fechaAhora, $idProgramacion);

    if (!$stmt->execute()) {
        responder(false, $stmt->error);
    }

    responder(true, 'Pago reprogramado correctamente.');
}

if ($accion == 'eliminar_programacion') {

    $idProgramacion = isset($_POST['idProgramacion']) ? (int)$_POST['idProgramacion'] : 0;

    if ($idProgramacion <= 0) {
        responder(false, 'Programación inválida.');
    }

    $fechaAhora = date('Y-m-d H:i:s');

    $sql = "
        UPDATE pagos_programados_proveedores
        SET 
            eliminado = 1,
            estado = 'CANCELADO',
            updated_at = ?
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        responder(false, $mysqli->error);
    }

    $stmt->bind_param("si", $fechaAhora, $idProgramacion);

    if (!$stmt->execute()) {
        responder(false, $stmt->error);
    }

    responder(true, 'Programación eliminada correctamente.');
}

if ($accion == 'marcar_pagado') {

    $idProgramacion = isset($_POST['idProgramacion']) ? (int)$_POST['idProgramacion'] : 0;

    if ($idProgramacion <= 0) {
        responder(false, 'Programación inválida.');
    }

    $fechaAhora = date('Y-m-d H:i:s');

    $sql = "
        UPDATE pagos_programados_proveedores
        SET 
            estado = 'PAGADO',
            updated_at = ?
        WHERE id = ?
          AND eliminado = 0
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        responder(false, $mysqli->error);
    }

    $stmt->bind_param("si", $fechaAhora, $idProgramacion);

    if (!$stmt->execute()) {
        responder(false, $stmt->error);
    }

    responder(true, 'Programación marcada como pagada.');
}

if ($accion == 'cards') {

    $hoy = date('Y-m-d');
    $inicioMes = date('Y-m-01');
    $finMes = date('Y-m-t');

    $inicioSemana = date('Y-m-d', strtotime('monday this week'));
    $finSemana = date('Y-m-d', strtotime('sunday this week'));

    $data = array(
        'total_sin_programar' => 0,
        'cantidad_sin_programar' => 0,
        'total_programado_mes' => 0,
        'total_semana' => 0,
        'total_vencido' => 0
    );

    $sqlSinProgramar = "
        SELECT 
            COUNT(*) AS cantidad,
            SUM(SaldoPendiente) AS total
        FROM (
            SELECT 
                TP.id,
                TP.Debe - IFNULL(AP.Pagado, 0) - IFNULL(PP.Programado, 0) AS SaldoPendiente
            FROM TransProveedores TP
            LEFT JOIN (
                SELECT 
                    idTransProveedores,
                    SUM(Haber) AS Pagado
                FROM AnticiposProveedores
                WHERE Eliminado = 0
                GROUP BY idTransProveedores
            ) AP ON AP.idTransProveedores = TP.id
            LEFT JOIN (
                SELECT 
                    idTransProveedores,
                    SUM(importe_programado) AS Programado
                FROM pagos_programados_proveedores
                WHERE eliminado = 0
                  AND estado IN ('PROGRAMADO','REPROGRAMADO')
                GROUP BY idTransProveedores
            ) PP ON PP.idTransProveedores = TP.id
            WHERE TP.Eliminado = 0
              AND TP.Debe > 0
        ) X
        WHERE X.SaldoPendiente > 0
    ";

    $res = $mysqli->query($sqlSinProgramar);
    if ($res) {
        $row = $res->fetch_assoc();
        $data['cantidad_sin_programar'] = (int)$row['cantidad'];
        $data['total_sin_programar'] = n($row['total']);
    }

    $stmt = $mysqli->prepare("
        SELECT IFNULL(SUM(importe_programado), 0) AS total
        FROM pagos_programados_proveedores
        WHERE eliminado = 0
          AND estado IN ('PROGRAMADO','REPROGRAMADO')
          AND fecha_promesa BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $inicioMes, $finMes);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['total_programado_mes'] = n($res->fetch_assoc()['total']);

    $stmt = $mysqli->prepare("
        SELECT IFNULL(SUM(importe_programado), 0) AS total
        FROM pagos_programados_proveedores
        WHERE eliminado = 0
          AND estado IN ('PROGRAMADO','REPROGRAMADO')
          AND fecha_promesa BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $inicioSemana, $finSemana);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['total_semana'] = n($res->fetch_assoc()['total']);

    $stmt = $mysqli->prepare("
        SELECT IFNULL(SUM(importe_programado), 0) AS total
        FROM pagos_programados_proveedores
        WHERE eliminado = 0
          AND estado IN ('PROGRAMADO','REPROGRAMADO')
          AND fecha_promesa < ?
    ");
    $stmt->bind_param("s", $hoy);
    $stmt->execute();
    $res = $stmt->get_result();
    $data['total_vencido'] = n($res->fetch_assoc()['total']);

    responder(true, '', $data);
}

if ($accion == 'resumen_fechas') {

    $hoy = date('Y-m-d');

    $sql = "
        SELECT 
            fecha_promesa,
            COUNT(*) AS cantidad,
            SUM(importe_programado) AS total,
            CASE 
                WHEN fecha_promesa < '$hoy' THEN 'VENCIDO'
                WHEN fecha_promesa = '$hoy' THEN 'HOY'
                ELSE 'PROGRAMADO'
            END AS estado
        FROM pagos_programados_proveedores
        WHERE eliminado = 0
          AND estado IN ('PROGRAMADO','REPROGRAMADO')
        GROUP BY fecha_promesa
        ORDER BY fecha_promesa ASC
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        responder(false, $mysqli->error);
    }

    $data = array();

    while ($row = $res->fetch_assoc()) {
        $row['cantidad'] = (int)$row['cantidad'];
        $row['total'] = n($row['total']);
        $data[] = $row;
    }

    responder(true, '', $data);
}

responder(false, 'Acción no reconocida.');
