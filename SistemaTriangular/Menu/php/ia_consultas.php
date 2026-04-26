<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

header('Content-Type: application/json; charset=UTF-8');

function salir($arr)
{
    echo json_encode($arr);
    exit;
}

function contieneAlguna($texto, $palabras)
{
    foreach ($palabras as $p) {
        if (strpos($texto, $p) !== false) {
            return true;
        }
    }
    return false;
}

function normalizarTexto($texto)
{
    return str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '?'],
        ['a', 'e', 'i', 'o', 'u', 'n', '', ''],
        mb_strtolower($texto, 'UTF-8')
    );
}

function normalizarPregunta($q)
{
    $q = normalizarTexto($q);

    $esFlex = strpos($q, 'flex') !== false;

    if ($esFlex && contieneAlguna($q, ['ruta', 'en ruta', 'pendiente', 'pendientes', 'distribucion', 'calle'])) {
        return 'flex_en_ruta_hoy';
    }

    if ($esFlex && contieneAlguna($q, ['entreg', 'entregados', 'entregaron'])) {
        return 'flex_entregados_hoy';
    }

    if ($esFlex && contieneAlguna($q, ['fall', 'no entreg', 'no se pudo', 'fallidos'])) {
        return 'flex_fallidos_hoy';
    }

    if ($esFlex && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios', 'envíos'])) {
        return 'flex_salieron_hoy';
    }

    return '';
}

function contar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) {
        return false;
    }

    $row = $res->fetch_assoc();
    return isset($row['total']) ? (int)$row['total'] : 0;
}

function sumar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) {
        return false;
    }

    $row = $res->fetch_assoc();
    return isset($row['total']) ? (float)$row['total'] : 0;
}

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

if ($pregunta === '') {
    salir([
        'success' => 0,
        'msg' => 'Pregunta vacía.'
    ]);
}

$q = normalizarTexto($pregunta);
$intent = normalizarPregunta($q);

$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

/*
    CONSULTAS FLEX
    Importante: van primero para que no las capture una consulta genérica.
*/

if ($intent === 'flex_en_ruta_hoy') {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Flex = 1
          AND TS.Fecha = '$hoy'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy hay <strong>$total</strong> paquetes Flex pendientes/en ruta.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Fecha = $hoy, Entregado = 0 y Devuelto = 0."
    ]);
}

if ($intent === 'flex_entregados_hoy') {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        JOIN Seguimiento S 
            ON S.CodigoSeguimiento = TS.CodigoSeguimiento
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Estado = 'Entregado al Cliente'
          AND S.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy se entregaron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'Entregado al Cliente' y Seguimiento.Fecha = $hoy."
    ]);
}

if ($intent === 'flex_fallidos_hoy') {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        JOIN Seguimiento S 
            ON S.CodigoSeguimiento = TS.CodigoSeguimiento
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Estado = 'No se pudo entregar'
          AND S.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy no se pudieron entregar <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'No se pudo entregar' y Seguimiento.Fecha = $hoy."
    ]);
}

if ($intent === 'flex_salieron_hoy') {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Flex = 1
          AND TS.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy salieron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1 y Fecha = $hoy."
    ]);
}


if (
    (strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false)
    && (
        strpos($q, 'pendiente') !== false ||
        strpos($q, 'pendientes') !== false ||
        strpos($q, 'ruta') !== false ||
        strpos($q, 'distribucion') !== false
    )
) {

    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            U.Usuario AS Repartidor
        FROM TransClientes TS

        LEFT JOIN Externos_rendicion ER 
            ON ER.CodigoSeguimiento = TS.CodigoSeguimiento

        LEFT JOIN usuarios U 
            ON U.id = ER.IdEmpleado

        WHERE TS.Eliminado = 0
          AND IFNULL(TS.shipments_id, 0) <> 0
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''

        GROUP BY TS.CodigoSeguimiento
        ORDER BY TS.CodigoSeguimiento ASC
        LIMIT 20
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir([
            'success' => 0,
            'msg' => 'Error consultando paquetes Meli.'
        ]);
    }

    $total = 0;
    $detalle = '';

    while ($row = $res->fetch_assoc()) {
        $total++;

        $detalle .= "#$total {$row['CodigoSeguimiento']}";

        if (!empty($row['Repartidor'])) {
            $detalle .= " - {$row['Repartidor']}";
        }

        $detalle .= "<br>";
    }

    salir([
        'success' => 1,
        'respuesta' => "Hay <strong>$total</strong> paquetes de Mercado Libre pendientes.",
        'detalle' => $detalle
    ]);
}

if (
    strpos($q, 'pendiente') !== false
    && strpos($q, 'repartidor') !== false
    && strpos($q, 'hoy') !== false
) {
    $sql = "
        SELECT 
            IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor,
            COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER 
            ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U 
            ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND TS.Fecha = '$hoy'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY U.Usuario
        ORDER BY total DESC
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir([
            'success' => 0,
            'msg' => 'Error consultando pendientes por repartidor.'
        ]);
    }

    $totalGeneral = 0;
    $detalle = '';
    $i = 1;

    while ($row = $res->fetch_assoc()) {
        $total = (int)$row['total'];
        $totalGeneral += $total;

        $detalle .= "#$i {$row['Repartidor']}: <strong>$total</strong><br>";
        $i++;
    }

    salir([
        'success' => 1,
        'respuesta' => "Hoy hay <strong>$totalGeneral</strong> paquetes pendientes agrupados por repartidor.",
        'detalle' => $detalle ?: 'Sin pendientes para hoy.'
    ]);
}



/*
    CONSULTAS GENERALES
*/

if (strpos($q, 'entreg') !== false && strpos($q, 'hoy') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado = 'Entregado al Cliente'
          AND Fecha = '$hoy'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy se entregaron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Estado = 'Entregado al Cliente' y Fecha = $hoy."
    ]);
}

if (
    (strpos($q, 'no entreg') !== false || strpos($q, 'no se pudieron') !== false)
    && strpos($q, 'hoy') !== false
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado = 'No se pudo entregar'
          AND Fecha = '$hoy'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy no se pudieron entregar <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Estado = 'No se pudo entregar' y Fecha = $hoy."
    ]);
}

if (
    (strpos($q, 'paquetes') !== false || strpos($q, 'envios') !== false)
    && strpos($q, 'hoy') !== false
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Fecha = '$hoy'
          AND Visitas <> 0
          AND Estado <> 'Retirado del Cliente'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy hubo <strong>$total</strong> paquetes con movimiento operativo.",
        'detalle' => "Criterio: Seguimiento.Fecha = $hoy, Visitas <> 0 y excluye 'Retirado del Cliente'."
    ]);
}

if (strpos($q, 'retir') !== false && strpos($q, 'hoy') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado = 'Retirado del Cliente'
          AND Fecha = '$hoy'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy se retiraron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Estado = 'Retirado del Cliente' y Fecha = $hoy."
    ]);
}

if (
    (strpos($q, 'distribucion') !== false || strpos($q, 'ruta') !== false)
    && strpos($q, 'hoy') !== false
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Fecha = '$hoy'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy hay <strong>$total</strong> paquetes pendientes/en distribución según TransClientes.",
        'detalle' => "Criterio: TransClientes.Fecha = $hoy, Entregado = 0 y Devuelto = 0."
    ]);
}

if (strpos($q, 'devuelt') !== false && strpos($q, 'hoy') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado IN ('Devuelto al Cliente', 'Devuelto al Remitente', 'Devuelto')
          AND Fecha = '$hoy'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy figuran <strong>$total</strong> paquetes devueltos.",
        'detalle' => "Criterio: estados de devolución en Seguimiento y Fecha = $hoy."
    ]);
}

if (strpos($q, 'rendicion') !== false || strpos($q, 'rendición') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(*) AS total
        FROM Logistica
        WHERE Eliminado = 0
          AND Rendicion = 0
          AND IFNULL(Costo_rendicion, 0) > 0
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hay <strong>$total</strong> rendiciones controladas pendientes de facturar.",
        'detalle' => "Criterio: Logistica.Rendicion = 0 y Costo_rendicion > 0."
    ]);
}

if (strpos($q, 'recorridos') !== false && strpos($q, 'hoy') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT NumerodeOrden) AS total
        FROM Logistica
        WHERE Eliminado = 0
          AND Fecha = '$hoy'
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy hay <strong>$total</strong> recorridos registrados.",
        'detalle' => "Criterio: Logistica.Fecha = $hoy."
    ]);
}

if (
    (strpos($q, 'facturado') !== false || strpos($q, 'facturacion') !== false)
    && (strpos($q, 'mes') !== false || strpos($q, 'mensual') !== false)
) {
    $total = sumar($mysqli, "
        SELECT SUM(IFNULL(Debe, 0)) AS total
        FROM TransClientes
        WHERE Eliminado = 0
          AND Facturado = 1
          AND Fecha >= '$inicioMes'
          AND Fecha <= '$finMes'
    ");

    salir([
        'success' => 1,
        'respuesta' => "En el mes se facturaron aproximadamente <strong>" . dinero($total) . "</strong>.",
        'detalle' => "Criterio: TransClientes.Facturado = 1 y Fecha entre $inicioMes y $finMes."
    ]);
}

if (
    (strpos($q, 'cobranza') !== false || strpos($q, 'cod') !== false || strpos($q, 'c.o.d') !== false)
    && (strpos($q, 'mes') !== false || strpos($q, 'mensual') !== false)
) {
    $total = sumar($mysqli, "
        SELECT SUM(IFNULL(CobranzaIntegrada, 0)) AS total
        FROM Externos_rendicion
        WHERE FechaRendido >= '$inicioMes'
          AND FechaRendido <= '$finMes 23:59:59'
    ");

    salir([
        'success' => 1,
        'respuesta' => "En el mes se pagó <strong>" . dinero($total) . "</strong> de cobranza integrada a externos.",
        'detalle' => "Criterio: Externos_rendicion.CobranzaIntegrada por FechaRendido del mes actual."
    ]);
}

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta. Probá con: “¿Cuántos paquetes se entregaron hoy?”'
]);
