<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

/* ======================================================
   1. FUNCIONES BASE
====================================================== */

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
        if (strpos($texto, $p) !== false) {
            return true;
        }
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

/* ======================================================
   2. VARIABLES PRINCIPALES
====================================================== */

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

if ($pregunta === '') {
    salir([
        'success' => 0,
        'msg' => 'Pregunta vacía.'
    ]);
}

$q = normalizarTexto($pregunta);

$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

/* ======================================================
   3. CONSULTA DIRECTA POR CÓDIGO DE SEGUIMIENTO
====================================================== */

$codigoPosible = strtoupper(trim($pregunta));

if (preg_match('/^[A-Z0-9]{6,}$/', $codigoPosible)) {

    $codigo = $mysqli->real_escape_string($codigoPosible);

    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
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
            Cliente: {$row['ClienteDestino']}<br>
            Dirección: {$row['DomicilioDestino']} {$row['LocalidadDestino']}<br>
            Repartidor: " . ($row['Repartidor'] ?: 'Sin asignar') . "<br>
            Fecha servicio: {$row['Fecha']}
        "
    ]);
}

/* ======================================================
   4. CONSULTAS FLEX
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
          AND S.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy se entregaron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'Entregado al Cliente' y Seguimiento.Fecha = $hoy."
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
          AND S.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy no se pudieron entregar <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1, Seguimiento.Estado = 'No se pudo entregar' y Seguimiento.Fecha = $hoy."
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
          AND TS.Fecha = '$hoy'
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hoy salieron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Flex = 1 y Fecha = $hoy."
    ]);
}

/* ======================================================
   5. CONSULTAS MELI
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
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY TS.CodigoSeguimiento, U.Usuario
        ORDER BY U.Usuario ASC, TS.CodigoSeguimiento ASC
        LIMIT 30
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir([
            'success' => 0,
            'msg' => 'Error consultando paquetes Meli pendientes.'
        ]);
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
        'respuesta' => "Hay <strong>$total</strong> paquetes de Mercado Libre pendientes/en ruta.",
        'detalle' => $detalle ?: 'Sin paquetes pendientes.'
    ]);
}

/* ======================================================
   6. PENDIENTES POR REPARTIDOR
====================================================== */

if (
    strpos($q, 'pendiente') !== false &&
    strpos($q, 'repartidor') !== false &&
    strpos($q, 'hoy') !== false
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

/* ======================================================
   7. CONSULTAS GENERALES OPERATIVAS
====================================================== */

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
    contieneAlguna($q, ['no entreg', 'no se pudo', 'fallidos'])
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
    contieneAlguna($q, ['paquetes', 'envios'])
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
    contieneAlguna($q, ['distribucion', 'ruta', 'pendiente'])
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

/* ======================================================
   8. CONSULTAS DE RENDICIÓN / FACTURACIÓN
====================================================== */

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

if (
    contieneAlguna($q, ['cobranza', 'cod', 'c.o.d'])
    && contieneAlguna($q, ['mes', 'mensual'])
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

/* ======================================================
   9. CONSULTA DINÁMICA POR REPARTIDOR
   Ej: "Cuantos paquetes entrego Heredia"
====================================================== */

if (strpos($q, 'entreg') !== false) {

    $resUsuarios = $mysqli->query("
        SELECT id, Usuario 
        FROM usuarios
        WHERE Usuario <> ''
    ");

    $usuarioDetectado = null;

    while ($u = $resUsuarios->fetch_assoc()) {
        $usuarioNormalizado = normalizarTexto($u['Usuario']);

        if (strpos($q, $usuarioNormalizado) !== false) {
            $usuarioDetectado = $u;
            break;
        }
    }

    if ($usuarioDetectado) {
        $idUsuario = (int)$usuarioDetectado['id'];
        $nombreUsuario = $usuarioDetectado['Usuario'];

        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
            FROM TransClientes TS
            INNER JOIN Externos_rendicion ER 
                ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            INNER JOIN Seguimiento S
                ON S.CodigoSeguimiento = TS.CodigoSeguimiento
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND ER.IdEmpleado = $idUsuario
              AND S.Estado = 'Entregado al Cliente'
              AND S.Fecha = '$hoy'
        ");

        salir([
            'success' => 1,
            'respuesta' => "Hoy <strong>$nombreUsuario</strong> entregó <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: entregados hoy por repartidor detectado en usuarios."
        ]);
    }
}

/* ======================================================
   10. FALLBACK FINAL
====================================================== */

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta. Probá con: “¿Cuántos paquetes se entregaron hoy?”'
]);
