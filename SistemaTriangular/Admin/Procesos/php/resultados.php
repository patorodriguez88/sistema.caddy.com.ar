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

$tipoFecha = isset($_POST['tipo_fecha']) ? trim($_POST['tipo_fecha']) : 'servicio';

$campoFecha = "TS.Fecha";

if ($tipoFecha === 'entrega') {
    $campoFecha = "FS.FechaEntrega";
}

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
    LEFT JOIN (
        SELECT 
            CodigoSeguimiento,
            MAX(Fecha) AS FechaEntrega
        FROM Seguimiento
        WHERE Eliminado = 0
        AND Estado = 'Entregado al Cliente'
        GROUP BY CodigoSeguimiento
    ) AS FS
        ON FS.CodigoSeguimiento = TS.CodigoSeguimiento
        WHERE TS.Eliminado = 0
        AND $campoFecha >= ?
        AND $campoFecha <= ?
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
    $cliente = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
    $filtroCliente = '';

    if ($cliente !== '') {
        $filtroCliente = " AND TS.ingBrutosOrigen = ? ";
    }

    // Los mismos parámetros se usan dos veces: una por cada mitad del UNION (externos + propios)
    $paramsUnaVez = array($Inicio, $Final);
    $typesUnaVez  = 'ss';
    if ($cliente !== '') {
        $paramsUnaVez[] = $cliente;
        $typesUnaVez .= 's';
    }
    $params = array_merge($paramsUnaVez, $paramsUnaVez);
    $types  = $typesUnaVez . $typesUnaVez;

    $sql = "
        SELECT id, Nombre FROM (
            SELECT DISTINCT
                U.id,
                U.Usuario AS Nombre
            FROM TransClientes TS
            LEFT JOIN (
                SELECT
                    CodigoSeguimiento,
                    MAX(Fecha) AS FechaEntrega
                FROM Seguimiento
                WHERE Eliminado = 0
                AND Estado = 'Entregado al Cliente'
                GROUP BY CodigoSeguimiento
            ) AS FS
                ON FS.CodigoSeguimiento = TS.CodigoSeguimiento
            INNER JOIN Externos_rendicion ER
                ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            INNER JOIN usuarios U
                ON U.id = ER.IdEmpleado
            WHERE TS.Eliminado = 0
            AND $campoFecha >= ?
            AND $campoFecha <= ?
            AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
            $filtroCliente

            UNION

            SELECT DISTINCT
                U.id,
                U.Usuario AS Nombre
            FROM TransClientes TS
            LEFT JOIN (
                SELECT
                    CodigoSeguimiento,
                    MAX(Fecha) AS FechaEntrega
                FROM Seguimiento
                WHERE Eliminado = 0
                AND Estado = 'Entregado al Cliente'
                GROUP BY CodigoSeguimiento
            ) AS FS
                ON FS.CodigoSeguimiento = TS.CodigoSeguimiento
            INNER JOIN Logistica L
                ON L.NumerodeOrden = TS.NumerodeOrden
                AND L.Eliminado = 0
            INNER JOIN Vehiculos V
                ON V.Dominio = L.Patente
                AND V.Aliados = 0
            INNER JOIN usuarios U
                ON U.id = L.idUsuarioChofer
            WHERE TS.Eliminado = 0
            AND $campoFecha >= ?
            AND $campoFecha <= ?
            AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
            $filtroCliente
        ) AS Repartidores
        ORDER BY Nombre ASC
    ";

    if (!($stmt = $mysqli->prepare($sql))) {
        jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    }

    $stmt->bind_param($types, ...$params);

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

        $filtroRepartidor = " AND (
        EXISTS (
            SELECT 1
            FROM Externos_rendicion ERF
            WHERE ERF.CodigoSeguimiento = TS.CodigoSeguimiento
            AND ERF.IdEmpleado = ?
        )
        OR EXISTS (
            SELECT 1
            FROM Logistica LF2
            INNER JOIN Vehiculos VF2
                ON VF2.Dominio = LF2.Patente
                AND VF2.Aliados = 0
            WHERE LF2.NumerodeOrden = TS.NumerodeOrden
            AND LF2.Eliminado = 0
            AND LF2.idUsuarioChofer = ?
        )
    ) ";

        $params[] = $repartidor;
        $params[] = $repartidor;

        $types .= 'ii';
    }

    $sql = "SELECT 
    $campoFecha AS Fecha,
    TS.CodigoSeguimiento,
    TS.CodigoProveedor,
    C.nombrecliente AS NombreCliente,
    COALESCE(ER.Repartidor, UP.Usuario, '-') AS Repartidor,
    TS.Wepoint_f,
    TS.Entregado,
    TS.Devuelto,
    TS.Facturado,
    TS.NumeroF,
    TS.Recorrido,
    IFNULL(
        ER.TotalPagado,
        IF(
            V.Aliados = 0,
            IFNULL(
                ROUND(
                    LOG.CostoKmTotalImputado
                    / NULLIF(COALESCE(NULLIF(SR.Cant, 0), PR.CantidadServiciosSinImporte), 0)
                , 2)
            , 0)
        , 0)
    ) AS PrecioPagado_SinIVA,
    IFNULL(VNI.COD_NotInvoice, 0) AS COD_NotInvoice,
    IFNULL(VNI.SurrenderNumbers, '') AS SurrenderNumbers,
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
        ) + IFNULL(VNI.COD_NotInvoice, 0)
    , 2) AS PrecioCobrado_SinIVA,

    ROUND(
    (
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
        ) + IFNULL(VNI.COD_NotInvoice, 0)
    ) - IFNULL(
        ER.TotalPagado,
        IF(
            V.Aliados = 0,
            IFNULL(
                ROUND(
                    LOG.CostoKmTotalImputado
                    / NULLIF(COALESCE(NULLIF(SR.Cant, 0), PR.CantidadServiciosSinImporte), 0)
                , 2)
            , 0)
        , 0)
    )
, 2) AS Diferencia_SinIVA,
    IFNULL(ER.CantidadRendiciones, 0) AS CantidadRendiciones,
    IFNULL(PR.PrecioUnitarioImputado, 0) AS PrecioRecorridoImputado
    FROM TransClientes AS TS
    LEFT JOIN (
    SELECT 
        ER.CodigoSeguimiento,
        SUM(
            IFNULL(ER.PrecioPagado, 0) + IFNULL(ER.CobranzaIntegrada, 0)
        ) AS TotalPagado,
        COUNT(*) AS CantidadRendiciones,
        GROUP_CONCAT(DISTINCT U.Usuario ORDER BY U.Usuario SEPARATOR ', ') AS Repartidor
    FROM Externos_rendicion ER
    LEFT JOIN usuarios U 
        ON U.id = ER.IdEmpleado
    GROUP BY ER.CodigoSeguimiento
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
        LEFT JOIN (
        SELECT 
            NumPedido,
            SUM(IFNULL(Total, 0)) AS COD_NotInvoice,
            GROUP_CONCAT(DISTINCT surrender_number ORDER BY surrender_number SEPARATOR ', ') AS SurrenderNumbers
        FROM Ventas
        WHERE Eliminado = 0
        AND not_invoice = 1
        AND IFNULL(surrender_number, 0) <> 0
        GROUP BY NumPedido
    ) AS VNI
    ON VNI.NumPedido = TS.CodigoSeguimiento  
    LEFT JOIN (
    SELECT 
        CodigoSeguimiento,
        MAX(Fecha) AS FechaEntrega
    FROM Seguimiento
    WHERE Eliminado = 0
      AND Estado = 'Entregado al Cliente'
    GROUP BY CodigoSeguimiento
) AS FS
    ON FS.CodigoSeguimiento = TS.CodigoSeguimiento

    LEFT JOIN Logistica AS LOG
        ON LOG.NumerodeOrden = TS.NumerodeOrden
        AND LOG.Eliminado = 0
    LEFT JOIN Vehiculos AS V
        ON V.Dominio = LOG.Patente
    LEFT JOIN usuarios AS UP
        ON UP.id = LOG.idUsuarioChofer
    LEFT JOIN (
        SELECT
            NumerodeOrden,
            COUNT(DISTINCT CodigoSeguimiento) AS Cant
        FROM Seguimiento
        WHERE Eliminado = 0
        AND Estado IN ('Entregado al Cliente', 'Retirado del Cliente', 'No se pudo entregar', 'No se pudo Retirar')
        GROUP BY NumerodeOrden
    ) AS SR
        ON SR.NumerodeOrden = TS.NumerodeOrden

    WHERE TS.Eliminado = 0
    AND $campoFecha >= ?
    AND $campoFecha <= ?
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

            (

            CASE

                WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) > 0 THEN TS.Debe

                WHEN CAST(IFNULL(TS.Debe, 0) AS DECIMAL(15,2)) = 0

                    AND CAST(IFNULL(TS.Haber, 0) AS DECIMAL(15,2)) = 0

                    AND CAST(IFNULL(PR.PrecioUnitarioImputado, 0) AS DECIMAL(15,2)) > 0

                THEN PR.PrecioUnitarioImputado

                ELSE 0

            END

        ) + IFNULL(VNI.COD_NotInvoice, 0) AS TotalConIVA,
         IFNULL(VNI.SurrenderNumbers, '') AS SurrenderNumbers,

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
                    ) + IFNULL(VNI.COD_NotInvoice, 0)
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
            IFNULL(VNI.COD_NotInvoice, 0) AS COD_NotInvoice,
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
            LEFT JOIN (
    SELECT 
        NumPedido,
        SUM(IFNULL(Total, 0)) AS COD_NotInvoice,
        GROUP_CONCAT(DISTINCT surrender_number ORDER BY surrender_number SEPARATOR ', ') AS SurrenderNumbers
    FROM Ventas
    WHERE Eliminado = 0
      AND not_invoice = 1
      AND IFNULL(surrender_number, 0) <> 0
    GROUP BY NumPedido
) AS VNI
    ON VNI.NumPedido = TS.CodigoSeguimiento
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
            ER.CobranzaIntegrada,
            ER.PrecioPagado + ER.CobranzaIntegrada AS TotalPagado,  
            (IFNULL(ER.PrecioPagado, 0) + IFNULL(ER.CobranzaIntegrada, 0)) AS TotalPagadoReal,
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
        $totalPagado += (float)$row['TotalPagadoReal'];
        $compras[] = $row;
    }

    $stmtCompra->close();

    // ===== Costo imputado de reparto propio (solo si no hubo rendiciones de externos) =====
    $propio = null;

    if (empty($compras)) {
        $sqlPropio = "
            SELECT
                V.Aliados,
                VKF.Nombre AS SegmentoNombre,
                IFNULL(LOG.CostoKmValorImputado, 0) AS ValorKm,
                IFNULL(LOG.KilometrosRecorridos, 0) AS KilometrosRecorridos,
                LOG.CostoKmTotalImputado,
                UP.Usuario AS Repartidor,
                COALESCE(NULLIF(SR.Cant, 0), PR.CantidadServiciosSinImporte) AS CantidadServicios
            FROM TransClientes TS
            LEFT JOIN Logistica LOG
                ON LOG.NumerodeOrden = TS.NumerodeOrden
                AND LOG.Eliminado = 0
            LEFT JOIN Vehiculos V
                ON V.Dominio = LOG.Patente
            LEFT JOIN ValorxKilometro VKF
                ON VKF.id = LOG.CostoKmSegmentoImputado
            LEFT JOIN usuarios UP
                ON UP.id = LOG.idUsuarioChofer
            LEFT JOIN (
                SELECT L.NumerodeOrden, COUNT(TC.id) AS CantidadServiciosSinImporte
                FROM Logistica L
                INNER JOIN TransClientes TC ON L.NumerodeOrden = TC.NumerodeOrden
                WHERE TC.Eliminado = 0 AND IFNULL(TC.Debe,0) = 0 AND IFNULL(TC.Haber,0) = 0
                GROUP BY L.NumerodeOrden
            ) AS PR
                ON PR.NumerodeOrden = TS.NumerodeOrden
            LEFT JOIN (
                SELECT NumerodeOrden, COUNT(DISTINCT CodigoSeguimiento) AS Cant
                FROM Seguimiento
                WHERE Eliminado = 0
                AND Estado IN ('Entregado al Cliente', 'Retirado del Cliente', 'No se pudo entregar', 'No se pudo Retirar')
                GROUP BY NumerodeOrden
            ) AS SR
                ON SR.NumerodeOrden = TS.NumerodeOrden
            WHERE TS.CodigoSeguimiento = ?
            LIMIT 1
        ";

        $stmtPropio = $mysqli->prepare($sqlPropio);
        if ($stmtPropio) {
            $stmtPropio->bind_param("s", $codigo);
            if ($stmtPropio->execute()) {
                $rowPropio = $stmtPropio->get_result()->fetch_assoc();

                if ($rowPropio && (int)$rowPropio['Aliados'] === 0 && $rowPropio['Repartidor']) {
                    $km            = (float)$rowPropio['KilometrosRecorridos'];
                    $valorKm       = (float)$rowPropio['ValorKm'];
                    $cant          = (int)$rowPropio['CantidadServicios'];
                    $totalImputado = $rowPropio['CostoKmTotalImputado'];
                    // El costo se lee del valor CONGELADO al cierre de la orden (no se recalcula
                    // con el ValorKm actual, para no alterar reportes de órdenes ya cerradas).
                    $costo = ($totalImputado !== null && $cant > 0)
                        ? round(((float)$totalImputado) / $cant, 2)
                        : 0;

                    $propio = [
                        'Repartidor'           => $rowPropio['Repartidor'],
                        'Segmento'             => $rowPropio['SegmentoNombre'],
                        'KilometrosRecorridos' => $km,
                        'ValorKm'              => $valorKm,
                        'CantidadServicios'    => $cant,
                        'CostoImputado'        => $costo,
                    ];

                    $totalPagado = $costo;
                }
            }
            $stmtPropio->close();
        }
    }

    $totalCobradoNeto = isset($venta['NetoSinIVA']) ? (float)$venta['NetoSinIVA'] : 0;
    $resultado = $totalCobradoNeto - $totalPagado;
    $rentabilidad = $totalCobradoNeto > 0 ? (($resultado / $totalCobradoNeto) * 100) : null;

    jexit([
        'ok' => true,
        'venta' => $venta,
        'compras' => $compras,
        'propio' => $propio,
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
