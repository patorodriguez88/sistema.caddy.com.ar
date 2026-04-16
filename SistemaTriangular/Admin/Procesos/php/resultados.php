<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// SistemaTriangular/Admin/Procesos/php/resultados.php
header('Content-Type: application/json; charset=UTF-8');
// session_start();

include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

function jexit($arr)
{
    echo json_encode($arr);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$Inicio = isset($_POST['Inicio']) ? trim($_POST['Inicio']) : '';
$Final  = isset($_POST['Final'])  ? trim($_POST['Final'])  : '';
if ($Inicio === '' || $Final === '') {
    jexit(['ok' => false, 'error' => 'Fechas requeridas']);
}

// --------- Cargar clientes (distinct) ----------
// if ($action === 'clientes') {
//     $sql = "SELECT DISTINCT ingBrutosOrigen AS CodigoProveedor 
//           FROM TransClientes
//           WHERE Eliminado=0 AND Fecha>=? AND Fecha<=?
//           ORDER BY CodigoProveedor";
//     if (!($stmt = $mysqli->prepare($sql))) jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
//     $stmt->bind_param('ss', $Inicio, $Final);
//     if (!$stmt->execute()) jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
//     $res = $stmt->get_result();
//     $clientes = array();

//     while ($r = $res->fetch_assoc()) {
//         if ($r['CodigoProveedor'] !== null && $r['CodigoProveedor'] !== '') {
//             $clientes[] = $r['CodigoProveedor'];
//         }
//     }
//     $stmt->close();
//     jexit(['ok' => true, 'clientes' => $clientes]);
// }

if ($action === 'clientes') {
    $sql = "
        SELECT DISTINCT
            TS.ingBrutosOrigen AS CodigoProveedor,
            CONCAT(C.nombrecliente, ' (', TS.ingBrutosOrigen, ')') AS Nombre
        FROM TransClientes TS
        LEFT JOIN Clientes C ON C.id = TS.ingBrutosOrigen
        WHERE TS.Eliminado = 0
          AND TS.Fecha >= ?
          AND TS.Fecha <= ?
          AND TS.ingBrutosOrigen <> ''
        ORDER BY C.nombrecliente ASC
    ";

    if (!($stmt = $mysqli->prepare($sql))) {
        jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    }

    $stmt->bind_param('ss', $Inicio, $Final);

    if (!$stmt->execute()) {
        jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $res = $stmt->get_result();
    $clientes = array();

    while ($r = $res->fetch_assoc()) {
        $clientes[] = array(
            'CodigoProveedor' => $r['CodigoProveedor'],
            'Nombre' => $r['Nombre'] ? $r['Nombre'] : ('Cliente ' . $r['CodigoProveedor'])
        );
    }

    $stmt->close();
    jexit(['ok' => true, 'clientes' => $clientes]);
}

// --------- Listar datos ----------
if ($action === 'listar') {

    $cliente = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
    $filtroClientes = '';
    $params = array($Inicio, $Final);
    $types  = 'ss';

    if ($cliente !== '') {
        $filtroClientes = " AND TS.ingBrutosOrigen = ? ";
        $params[] = $cliente;
        $types .= 's';
    }
    $sql = "
        SELECT 
        TS.Fecha,
        TS.CodigoSeguimiento,
        TS.CodigoProveedor,
        C.nombrecliente AS NombreCliente,
        TS.Wepoint_f,
        TS.Entregado,
        TS.Devuelto,
        TS.Facturado,
        TS.NumeroF,

        IFNULL(ER.TotalPagado, 0) AS PrecioPagado_SinIVA,
        ROUND(IFNULL(TS.Debe, 0) / 1.21, 2) AS PrecioCobrado_SinIVA,
        ROUND((IFNULL(TS.Debe, 0) / 1.21) - IFNULL(ER.TotalPagado, 0), 2) AS Diferencia_SinIVA,

        IFNULL(ER.CantidadRendiciones, 0) AS CantidadRendiciones

        FROM TransClientes AS TS

        LEFT JOIN (
            SELECT 
                CodigoSeguimiento,
                SUM(IFNULL(PrecioPagado, 0)) AS TotalPagado,
                COUNT(*) AS CantidadRendiciones
            FROM Externos_rendicion
            GROUP BY CodigoSeguimiento
        ) AS ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento

        LEFT JOIN Clientes AS C
        ON C.id = TS.ingBrutosOrigen

        WHERE TS.Eliminado = 0
        AND TS.Fecha >= ?
        AND TS.Fecha <= ?
        $filtroClientes

        ORDER BY TS.Fecha DESC, TS.CodigoSeguimiento DESC
        ";

    if (!($stmt = $mysqli->prepare($sql))) {
        jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    }

    // bind dinámico
    // Nota: para versiones viejas de PHP, armamos bind_param dinámico con call_user_func_array
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $res = $stmt->get_result();
    $data = array();
    while ($row = $res->fetch_assoc()) {
        // devolvemos tal cual; el formateo lo hace DataTables
        $data[] = $row;
    }
    $stmt->close();
    jexit(['ok' => true, 'data' => $data]);
}
if ($action === 'detalle') {
    $codigo = isset($_POST['CodigoSeguimiento']) ? trim($_POST['CodigoSeguimiento']) : '';

    if ($codigo === '') {
        jexit(['ok' => false, 'error' => 'Código requerido']);
    }

    // ===== Proceso de venta =====
    $sqlVenta = "
        SELECT 
            TS.CodigoSeguimiento,
            TS.CodigoProveedor,
            C.nombrecliente AS NombreCliente,
            TS.Debe AS TotalConIVA,
            ROUND(IFNULL(TS.Debe,0) / 1.21, 2) AS NetoSinIVA,
            ROUND(IFNULL(TS.Debe,0) - (IFNULL(TS.Debe,0) / 1.21), 2) AS IVA,
            TS.Facturado,
            TS.NumeroF,
            TS.Fecha
        FROM TransClientes TS
        LEFT JOIN Clientes C ON C.id = TS.ingBrutosOrigen
        WHERE TS.CodigoSeguimiento = ?
        LIMIT 1
    ";

    $stmtVenta = $mysqli->prepare($sqlVenta);
    if (!$stmtVenta) {
        jexit(['ok' => false, 'error' => 'Error preparando venta: ' . $mysqli->error]);
    }

    $stmtVenta->bind_param("s", $codigo);

    if (!$stmtVenta->execute()) {
        jexit(['ok' => false, 'error' => 'Error ejecutando venta: ' . $stmtVenta->error]);
    }

    $venta = $stmtVenta->get_result()->fetch_assoc();
    $stmtVenta->close();

    // ===== Proceso de compra / rendiciones =====
    $sqlCompra = "
        SELECT 
            ER.id,
            ER.CodigoSeguimiento,
            ER.PrecioPagado,
            ER.TipoLiquidacion,
            ER.NumeroComprobante,
            ER.FechaComprobante,
            ER.FechaRendido,
            E.Usuario AS Repartidor
        FROM Externos_rendicion ER
        LEFT JOIN usuarios E ON E.id = ER.IdEmpleado
        WHERE ER.CodigoSeguimiento = ?
        ORDER BY ER.id ASC
    ";

    $stmtCompra = $mysqli->prepare($sqlCompra);
    if (!$stmtCompra) {
        jexit(['ok' => false, 'error' => 'Error preparando compra: ' . $mysqli->error]);
    }

    $stmtCompra->bind_param("s", $codigo);

    if (!$stmtCompra->execute()) {
        jexit(['ok' => false, 'error' => 'Error ejecutando compra: ' . $stmtCompra->error]);
    }

    $resCompra = $stmtCompra->get_result();
    $compras = [];
    $totalPagado = 0;

    while ($row = $resCompra->fetch_assoc()) {
        $totalPagado += (float)$row['PrecioPagado'];
        $compras[] = $row;
    }

    $stmtCompra->close();

    $totalCobradoNeto = isset($venta['NetoSinIVA']) ? (float)$venta['NetoSinIVA'] : 0;
    $resultado = $totalCobradoNeto - $totalPagado;
    $rentabilidad = $totalCobradoNeto > 0 ? (($resultado / $totalCobradoNeto) * 100) : null;

    jexit([
        'ok' => true,
        'venta' => $venta,
        'compras' => $compras,
        'resumen' => [
            'TotalCobradoNeto' => round($totalCobradoNeto, 2),
            'TotalPagado' => round($totalPagado, 2),
            'Resultado' => round($resultado, 2),
            'Rentabilidad' => $rentabilidad !== null ? round($rentabilidad, 2) : null
        ]
    ]);
}


// acción desconocida
jexit(['ok' => false, 'error' => 'Acción inválida']);
