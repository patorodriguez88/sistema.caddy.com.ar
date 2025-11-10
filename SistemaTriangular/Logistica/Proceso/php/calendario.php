<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$month = isset($_GET['m']) ? (int)$_GET['m'] : date('n');
$year  = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');

$first = sprintf('%04d-%02d-01', $year, $month);
$last  = date('Y-m-t', strtotime($first));

$sql = "
  SELECT l.Fecha, l.Recorrido, r.Nombre
  FROM Logistica AS l
  LEFT JOIN Recorridos AS r ON r.Numero = l.Recorrido
  WHERE l.Eliminado = 0
    AND l.Fecha BETWEEN ? AND ?
  ORDER BY l.Fecha, l.Recorrido
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ss', $first, $last);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $f = $row['Fecha'];
    $data[$f][] = [
        'Recorrido' => $row['Recorrido'],
        'Nombre' => $row['Nombre']
    ];
}
$stmt->close();

echo json_encode([
    'success' => true,
    'month'   => $month,
    'year'    => $year,
    'days'    => $data
]);
