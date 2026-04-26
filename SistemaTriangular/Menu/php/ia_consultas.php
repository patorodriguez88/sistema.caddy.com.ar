<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

function salir($arr)
{
    echo json_encode($arr);
    exit;
}

function normalizarTexto($texto)
{
    return str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '?'],
        ['a', 'e', 'i', 'o', 'u', 'n', '', ''],
        mb_strtolower($texto, 'UTF-8')
    );
}

function contieneAlguna($texto, $palabras)
{
    foreach ($palabras as $p) {
        if (strpos($texto, $p) !== false) return true;
    }
    return false;
}

function contar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (int)$row['total'] : 0;
}

function sumar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (float)$row['total'] : 0;
}

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

function detectarFechaConsulta($q)
{
    $hoy = date('Y-m-d');

    if (strpos($q, 'hoy') !== false) {
        return [$hoy, 'hoy'];
    }

    if (strpos($q, 'ayer') !== false) {
        return [date('Y-m-d', strtotime('-1 day')), 'ayer'];
    }

    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $q, $m)) {
        $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $anio = $m[3];
        return ["$anio-$mes-$dia", "$dia/$mes/$anio"];
    }

    $dias = [
        'lunes' => 'monday',
        'martes' => 'tuesday',
        'miercoles' => 'wednesday',
        'jueves' => 'thursday',
        'viernes' => 'friday',
        'sabado' => 'saturday',
        'domingo' => 'sunday'
    ];

    foreach ($dias as $es => $en) {
        if (strpos($q, $es . ' pasado') !== false) {
            return [date('Y-m-d', strtotime("last $en")), $es . ' pasado'];
        }
    }

    return [$hoy, 'hoy'];
}

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

if ($pregunta === '') {
    salir(['success' => 0, 'msg' => 'Pregunta vacía.']);
}

$q = normalizarTexto($pregunta);

$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

list($fechaConsulta, $textoFechaConsulta) = detectarFechaConsulta($q);

/* ======================================================
   CONSULTA DIRECTA POR CÓDIGO
====================================================== */

$codigoPosible = strtoupper(trim($pregunta));

if (preg_match('/^[A-Z0-9]{6,}$/', $codigoPosible)) {

    $codigo = $mysqli->real_escape_string($codigoPosible);

    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            TS.RazonSocial AS Origen,
            TS.DomicilioOrigen,
            TS.LocalidadOrigen,
            TS.ClienteDestino,
            TS.DomicilioDestino,
            TS.LocalidadDestino,
            TS.Entregado,
            TS.Devuelto,
            TS.Fecha,
            TS.Flex,
            TS.shipments_id,
            U.Usuario AS Repartidor
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER 
            ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U 
            ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND TS.CodigoSeguimiento = '$codigo'
        LIMIT 1
    ";

    $res = $mysqli->query($sql);

    if (!$res || $res->num_rows == 0) {
        salir([
            'success' => 0,
            'msg' => "No encontré el código <strong>$codigo</strong>."
        ]);
    }

    $row = $res->fetch_assoc();

    if ((int)$row['Devuelto'] === 1) {
        $estado = "Devuelto";
    } elseif ((int)$row['Entregado'] === 1) {
        $estado = "Entregado";
    } else {
        $estado = "En ruta / Pendiente";
    }

    $tipo = '';
    if ((int)$row['Flex'] === 1) {
        $tipo .= '<span class="badge bg-info me-1">Flex</span>';
    }

    if (!empty($row['shipments_id']) && (int)$row['shipments_id'] !== 0) {
        $tipo .= '<span class="badge bg-warning text-dark me-1">Meli</span>';
    }

    salir([
        'success' => 1,
        'respuesta' => "<strong>$codigo</strong> → $estado",
        'detalle' => "
            $tipo<br>
            <strong>Origen:</strong> {$row['Origen']}<br>
            <strong>Dirección origen:</strong> {$row['DomicilioOrigen']} {$row['LocalidadOrigen']}<br>
            <hr class='my-1'>
            <strong>Destino:</strong> {$row['ClienteDestino']}<br>
            <strong>Dirección destino:</strong> {$row['DomicilioDestino']} {$row['LocalidadDestino']}<br>
            <strong>Repartidor:</strong> " . ($row['Repartidor'] ?: 'Sin asignar') . "<br>
            <strong>Fecha servicio:</strong> {$row['Fecha']}
        "
    ]);
}

/* ======================================================
   FLEX
====================================================== */

if (
    strpos($q, 'flex') !== false &&
    contieneAlguna($q, ['ruta', 'en ruta', 'pendiente', 'pendientes', 'distribucion', 'calle'])
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Flex = 1
          AND TS.Fecha = '$fechaConsulta'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$total</strong> paquetes Flex pendientes/en ruta.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Fecha = $fechaConsulta, Entregado = 0 y Devuelto = 0."
    ]);
}

if (
    strpos($q, 'flex') !== false &&
    contieneAlguna($q, ['entreg', 'entregados', 'entregaron'])
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        JOIN Seguimiento S 
            ON S.CodigoSeguimiento = TS.CodigoSeguimiento
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Estado = 'Entregado al Cliente'
          AND S.Fecha = '$fechaConsulta'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se entregaron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'Entregado al Cliente' y Seguimiento.Fecha = $fechaConsulta."
    ]);
}

if (
    strpos($q, 'flex') !== false &&
    contieneAlguna($q, ['fall', 'no entreg', 'no se pudo', 'fallidos'])
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        JOIN Seguimiento S 
            ON S.CodigoSeguimiento = TS.CodigoSeguimiento
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Estado = 'No se pudo entregar'
          AND S.Fecha = '$fechaConsulta'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'No se pudo entregar' y Seguimiento.Fecha = $fechaConsulta."
    ]);
}

if (
    strpos($q, 'flex') !== false &&
    contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios'])
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Flex = 1
          AND TS.Fecha = '$fechaConsulta'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1 y Fecha = $fechaConsulta."
    ]);
}

/* ======================================================
   MELI
====================================================== */

if (
    (strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false)
    && contieneAlguna($q, ['pendiente', 'pendientes', 'ruta', 'distribucion'])
) {
    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER 
            ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U 
            ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND IFNULL(TS.shipments_id, 0) <> 0
          AND TS.Fecha = '$fechaConsulta'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY TS.CodigoSeguimiento, U.Usuario
        ORDER BY U.Usuario ASC, TS.CodigoSeguimiento ASC
        LIMIT 30
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir(['success' => 0, 'msg' => 'Error consultando paquetes Meli pendientes.']);
    }

    $i = 1;
    $detalle = '';

    while ($row = $res->fetch_assoc()) {
        $detalle .= "#$i {$row['CodigoSeguimiento']} - {$row['Repartidor']}<br>";
        $i++;
    }

    $total = $i - 1;

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$total</strong> paquetes de Mercado Libre pendientes/en ruta.",
        'detalle' => $detalle ?: 'Sin paquetes pendientes.'
    ]);
}

if (
    (strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false)
    && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios'])
) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND IFNULL(TS.shipments_id, 0) <> 0
          AND TS.Fecha = '$fechaConsulta'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron <strong>$total</strong> paquetes de Mercado Libre.",
        'detalle' => "Criterio: TransClientes.shipments_id <> 0 y Fecha = $fechaConsulta."
    ]);
}

/* ======================================================
   PENDIENTES POR REPARTIDOR
====================================================== */

if (
    strpos($q, 'pendiente') !== false &&
    strpos($q, 'repartidor') !== false
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
          AND TS.Fecha = '$fechaConsulta'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY U.Usuario
        ORDER BY total DESC
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir(['success' => 0, 'msg' => 'Error consultando pendientes por repartidor.']);
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
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$totalGeneral</strong> paquetes pendientes agrupados por repartidor.",
        'detalle' => $detalle ?: 'Sin pendientes para esa fecha.'
    ]);
}

/* ======================================================
   GENERALES
====================================================== */

if (strpos($q, 'entreg') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado = 'Entregado al Cliente'
          AND Fecha = '$fechaConsulta'
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se entregaron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Estado = 'Entregado al Cliente' y Fecha = $fechaConsulta."
    ]);
}

if (contieneAlguna($q, ['no entreg', 'no se pudo', 'fallidos'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT CodigoSeguimiento) AS total
        FROM Seguimiento
        WHERE Eliminado = 0
          AND Estado = 'No se pudo entregar'
          AND Fecha = '$fechaConsulta'
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Estado = 'No se pudo entregar' y Fecha = $fechaConsulta."
    ]);
}

if (contieneAlguna($q, ['paquetes', 'envios', 'salieron', 'salio', 'salida'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Fecha = '$fechaConsulta'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: TransClientes.Fecha = $fechaConsulta."
    ]);
}

/* ======================================================
   RENDICIÓN / FACTURACIÓN
====================================================== */

if (strpos($q, 'rendicion') !== false) {
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

if (
    contieneAlguna($q, ['facturado', 'facturacion'])
    && contieneAlguna($q, ['mes', 'mensual'])
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

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta. Probá con: “¿Cuántos paquetes salieron hoy?”'
]);
