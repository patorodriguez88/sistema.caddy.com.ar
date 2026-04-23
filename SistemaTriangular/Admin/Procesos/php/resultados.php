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
if ($action === 'clientes') {
    $sql = "
        SELECT DISTINCT
            TS.ingBrutosOrigen AS CodigoProveedor,
            CONCAT(C.nombrecliente, ' (', TS.ingBrutosOrigen, ')') AS Nombre
        FROM TransClientes TS
        LEFT JOIN Clientes C ON C.id = TS.ingBrutosOrigen
        LEFT JOIN (
    SELECT 
        NumerodeOrden,
        MAX(Fecha) AS FechaLogistica
    FROM Logistica
    WHERE Eliminado = 0
    GROUP BY NumerodeOrden
        ) AS LF ON LF.NumerodeOrden = TS.NumerodeOrden
                WHERE TS.Eliminado = 0
        AND (
            CASE
                WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Fecha
                WHEN IFNULL(TS.Debe, 0) = 0 AND IFNULL(TS.Haber, 0) = 0 THEN IFNULL(LF.FechaLogistica, TS.Fecha)
                ELSE TS.Fecha
            END
        ) >= ?
        AND (
            CASE
                WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Fecha
                WHEN IFNULL(TS.Debe, 0) = 0 AND IFNULL(TS.Haber, 0) = 0 THEN IFNULL(LF.FechaLogistica, TS.Fecha)
                ELSE TS.Fecha
            END
        ) <= ?
        AND TS.ingBrutosOrigen <> ''
        AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
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
if ($action === 'repartidores') {
    $sql = "
        SELECT DISTINCT
            U.id,
            U.Usuario AS Nombre
        FROM Externos_rendicion ER
        INNER JOIN usuarios U ON U.id = ER.IdEmpleado
        WHERE (
            CASE
                WHEN ER.FechaRendido IS NOT NULL AND ER.FechaRendido <> ''
                THEN ER.FechaRendido
                ELSE ER.FechaComprobante
            END
        ) >= ?
        AND (
            CASE
                WHEN ER.FechaRendido IS NOT NULL AND ER.FechaRendido <> ''
                THEN ER.FechaRendido
                ELSE ER.FechaComprobante
            END
        ) <= ?
        ORDER BY U.Usuario ASC
    ";

    if (!($stmt = $mysqli->prepare($sql))) {
        jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    }

    $stmt->bind_param('ss', $Inicio, $Final);

    if (!$stmt->execute()) {
        jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $res = $stmt->get_result();
    $repartidores = array();

    while ($r = $res->fetch_assoc()) {
        $repartidores[] = array(
            'id' => $r['id'],
            'Nombre' => $r['Nombre']
        );
    }

    $stmt->close();
    jexit(['ok' => true, 'repartidores' => $repartidores]);
}
// --------- Listar datos ----------
if ($action === 'listar') {

    $cliente = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
    $repartidor = isset($_POST['repartidor']) ? trim($_POST['repartidor']) : '';
    $filtroClientes = '';
    $filtroRepartidor = '';
    $params = array($Inicio, $Final);
    $types  = 'ss';

    if ($cliente !== '') {
        $filtroClientes = " AND TS.ingBrutosOrigen = ? ";
        $params[] = $cliente;
        $types .= 's';
    }
    if ($repartidor !== '') {

        $filtroRepartidor = " AND EXISTS (

        SELECT 1

        FROM Externos_rendicion ERF

        WHERE ERF.CodigoSeguimiento = TS.CodigoSeguimiento

        AND ERF.IdEmpleado = ?

    ) ";

        $params[] = $repartidor;

        $types .= 'i';
    }

    $sql = "
    SELECT 
    CASE
        WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Fecha
        WHEN IFNULL(TS.Debe, 0) = 0 AND IFNULL(TS.Haber, 0) = 0 THEN IFNULL(LF.FechaLogistica, TS.Fecha)
        ELSE TS.Fecha
    END AS Fecha,
    TS.CodigoSeguimiento,
    TS.CodigoProveedor,
    C.nombrecliente AS NombreCliente,
    TS.Wepoint_f,
    TS.Entregado,
    TS.Devuelto,
    TS.Facturado,
    TS.NumeroF,
    TS.Recorrido,

    IFNULL(ER.TotalPagado, 0) AS PrecioPagado_SinIVA,

    ROUND(
        (
        CASE
            WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Debe
            WHEN IFNULL(TS.Debe, 0) = 0 
                AND IFNULL(TS.Haber, 0) = 0
                AND IFNULL(PR.PrecioUnitarioImputado, 0) > 0
            THEN PR.PrecioUnitarioImputado
            ELSE 0
        END
        ) / 1.21
    , 2) AS PrecioCobrado_SinIVA,

    ROUND(
        (
        (
            CASE
            WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Debe
            WHEN IFNULL(TS.Debe, 0) = 0 
                AND IFNULL(TS.Haber, 0) = 0
                AND IFNULL(PR.PrecioUnitarioImputado, 0) > 0
                THEN PR.PrecioUnitarioImputado
            ELSE 0
            END
        ) / 1.21
        ) - IFNULL(ER.TotalPagado, 0)
    , 2) AS Diferencia_SinIVA,

    IFNULL(ER.CantidadRendiciones, 0) AS CantidadRendiciones,
    IFNULL(PR.PrecioUnitarioImputado, 0) AS PrecioRecorridoImputado

    FROM TransClientes AS TS

    LEFT JOIN (
        SELECT 
            CodigoSeguimiento,
            SUM(IFNULL(PrecioPagado, 0)) AS TotalPagado,
            COUNT(*) AS CantidadRendiciones
        FROM Externos_rendicion
        GROUP BY CodigoSeguimiento
    ) AS ER 
        ON ER.CodigoSeguimiento = TS.CodigoSeguimiento

    LEFT JOIN Clientes AS C
        ON C.id = TS.ingBrutosOrigen
    LEFT JOIN (
        SELECT 
            NumerodeOrden,
            MAX(Fecha) AS FechaLogistica
        FROM Logistica
        WHERE Eliminado = 0
        GROUP BY NumerodeOrden
    ) AS LF
        ON LF.NumerodeOrden = TS.NumerodeOrden

        LEFT JOIN (
            SELECT 
                L.NumerodeOrden,
                L.PrecioRecorrido,
            COUNT(TC.id) AS CantidadServiciosSinImporte,
            CASE
                WHEN COUNT(TC.id) > 0 THEN L.PrecioRecorrido / COUNT(TC.id)
                ELSE 0
            END AS PrecioUnitarioImputado
        FROM Logistica L
        INNER JOIN TransClientes TC 
            ON L.NumerodeOrden = TC.NumerodeOrden
        WHERE TC.Eliminado = 0
        AND IFNULL(TC.Debe,0) = 0
        AND IFNULL(TC.Haber,0) = 0
        GROUP BY L.NumerodeOrden, L.PrecioRecorrido
    ) AS PR
        ON PR.NumerodeOrden = TS.NumerodeOrden

    WHERE TS.Eliminado = 0
    AND (
        CASE
            WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Fecha
            WHEN IFNULL(TS.Debe, 0) = 0 AND IFNULL(TS.Haber, 0) = 0 THEN IFNULL(LF.FechaLogistica, TS.Fecha)
            ELSE TS.Fecha
        END
    ) >= ?
    AND (
        CASE
            WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Fecha
            WHEN IFNULL(TS.Debe, 0) = 0 AND IFNULL(TS.Haber, 0) = 0 THEN IFNULL(LF.FechaLogistica, TS.Fecha)
            ELSE TS.Fecha
        END
    ) <= ?
    AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    $filtroClientes
    $filtroRepartidor

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
            TS.Recorrido,
            C.nombrecliente AS NombreCliente,

            CASE
    WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) > 0 THEN TS.Debe
    WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) = 0
         AND CAST(IFNULL(TS.Haber, 0) AS DECIMAL(15,2)) = 0
         AND CAST(IFNULL(PR.PrecioUnitarioImputado, 0) AS DECIMAL(15,2)) > 0
    THEN PR.PrecioUnitarioImputado
    ELSE 0
END AS TotalConIVA,

            ROUND(
                (
                    CASE
                        WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Debe
                        WHEN IFNULL(TS.Debe, 0) = 0
                            AND IFNULL(TS.Haber, 0) = 0
                            AND IFNULL(PR.PrecioUnitarioImputado, 0) > 0
                        THEN PR.PrecioUnitarioImputado
                        ELSE 0
                    END
                ) / 1.21
            , 2) AS NetoSinIVA,

            ROUND(
                (
                    CASE
                        WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Debe
                        WHEN IFNULL(TS.Debe, 0) = 0
                            AND IFNULL(TS.Haber, 0) = 0
                            AND IFNULL(PR.PrecioUnitarioImputado, 0) > 0
                        THEN PR.PrecioUnitarioImputado
                        ELSE 0
                    END
                ) - (
                    (
                        CASE
                            WHEN IFNULL(TS.Debe, 0) > 0 THEN TS.Debe
                            WHEN IFNULL(TS.Debe, 0) = 0
                                AND IFNULL(TS.Haber, 0) = 0
                                AND IFNULL(PR.PrecioUnitarioImputado, 0) > 0
                            THEN PR.PrecioUnitarioImputado
                            ELSE 0
                        END
                    ) / 1.21
                )
            , 2) AS IVA,

            TS.Facturado,
            TS.NumeroF,
            TS.Fecha,
            TS.NumerodeOrden,
            IFNULL(PR.PrecioUnitarioImputado, 0) AS PrecioRecorridoImputado,

            CASE
    WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) > 0 THEN 'TRANSCLIENTES'
    WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) = 0
         AND CAST(IFNULL(TS.Haber, 0) AS DECIMAL(15,2)) = 0
         AND CAST(IFNULL(PR.PrecioUnitarioImputado, 0) AS DECIMAL(15,2)) > 0
    THEN 'PRORRATEO_RECORRIDO'
    ELSE 'SIN_VALOR'
END AS OrigenCobrado

        FROM TransClientes TS
        LEFT JOIN Clientes C 
            ON C.id = TS.ingBrutosOrigen
        LEFT JOIN (
            SELECT 
                L.NumerodeOrden,
                L.PrecioRecorrido,
                COUNT(TC.id) AS CantidadServiciosSinImporte,
                CASE
                    WHEN COUNT(TC.id) > 0 THEN L.PrecioRecorrido / COUNT(TC.id)
                    ELSE 0
                END AS PrecioUnitarioImputado
            FROM Logistica L
            INNER JOIN TransClientes TC 
                ON L.NumerodeOrden = TC.NumerodeOrden
            WHERE TC.Eliminado = 0
            AND IFNULL(TC.Debe,0) = 0
            AND IFNULL(TC.Haber,0) = 0
            GROUP BY L.NumerodeOrden, L.PrecioRecorrido
        ) PR 
            ON PR.NumerodeOrden = TS.NumerodeOrden
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
