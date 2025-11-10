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
    $sql = "SELECT DISTINCT ingBrutosOrigen AS CodigoProveedor 
          FROM TransClientes
          WHERE Eliminado=0 AND Fecha>=? AND Fecha<=?
          ORDER BY CodigoProveedor";
    if (!($stmt = $mysqli->prepare($sql))) jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    $stmt->bind_param('ss', $Inicio, $Final);
    if (!$stmt->execute()) jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    $res = $stmt->get_result();
    $clientes = array();
    while ($r = $res->fetch_assoc()) {
        if ($r['CodigoProveedor'] !== null && $r['CodigoProveedor'] !== '') {
            $clientes[] = $r['CodigoProveedor'];
        }
    }
    $stmt->close();
    jexit(['ok' => true, 'clientes' => $clientes]);
}

// --------- Listar datos ----------
if ($action === 'listar') {
    $clientes = isset($_POST['clientes']) ? $_POST['clientes'] : array();
    $filtroClientes = '';
    $params = array($Inicio, $Final);
    $types  = 'ss';

    if (is_array($clientes) && count($clientes) > 0) {
        // construir IN dinámico
        $place = array_fill(0, count($clientes), '?');
        $filtroClientes = " AND TS.ingBrutosOrigen IN (" . implode(',', $place) . ") ";
        foreach ($clientes as $c) {
            $params[] = $c;
            $types .= 's';
        }
    }

    $sql = "
    SELECT 
      TS.Fecha,
      TS.CodigoSeguimiento,
      TS.CodigoProveedor,
      TS.Wepoint_f,
      TS.Entregado,
      TS.Devuelto,
      TS.Facturado,
      TS.NumeroF,
      IFNULL(ER.PrecioPagado,0)  AS PrecioPagado_SinIVA,
      IFNULL(ER.PrecioCobrado,0) AS PrecioCobrado_SinIVA,
      (IFNULL(ER.PrecioCobrado,0) - IFNULL(ER.PrecioPagado,0)) / 1.21 AS Diferencia_SinIVA,
      ER.FechaComprobante,
      ER.NumeroComprobante,
      ER.IdEmpleado
    FROM TransClientes AS TS
    LEFT JOIN Externos_rendicion AS ER 
      ON TS.CodigoSeguimiento = ER.CodigoSeguimiento
    WHERE TS.Eliminado=0
      AND TS.Fecha>=?
      AND TS.Fecha<=?
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

// acción desconocida
jexit(['ok' => false, 'error' => 'Acción inválida']);
