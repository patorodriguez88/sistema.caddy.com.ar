<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once "../../Conexion/Conexioni.php"; // usa $mysqli (MySQLi)

date_default_timezone_set('America/Argentina/Buenos_Aires');

$anio = isset($_POST['anio']) ? intval($_POST['anio']) : intval(date('Y'));

// Ventana: últimos 12 meses hasta hoy
$fin = date('Y-m-t');
$inicio = date('Y-m-01', strtotime('-11 months'));

$grupos = ["Personal", "Logistica", "Generales", "Financieros"];

$sql = "
SELECT 
  DATE_FORMAT(T.Fecha, '%Y-%m') AS periodo,
  CASE
    -- PERSONAL
    WHEN T.Cuenta IN ('000420700','000420800','000421400','000402400') THEN 'Personal'
    -- LOGISTICA / OPERACIONES
    WHEN T.Cuenta IN ('000420600','000422700','000421600','000421800','000420200') THEN 'Logistica'
    -- GENERALES / ADMIN
    WHEN T.Cuenta IN ('000421200','000421300','000421700','000420900','000424200','000424100','000422500','000424000','000421900','000425000','0004210000') THEN 'Generales'
    -- FINANCIEROS / IMPUESTOS
    WHEN T.Cuenta IN ('000421000','000420300','000423900','000423300','000423400','000422800','000423200','000424600','000424700','000424800','000423700','000423600') THEN 'Financieros'
    ELSE 'Generales'
  END AS grupo,
  SUM(T.Debe) AS total
FROM Tesoreria T
LEFT JOIN PlanDeCuentas P ON P.Cuenta = T.Cuenta
WHERE T.Eliminado = 0
  AND P.MuestraGastos = 1
  AND T.Fecha BETWEEN '{$inicio}' AND '{$fin}'
GROUP BY periodo, grupo
ORDER BY periodo ASC
";

$res = $mysqli->query($sql);
if (!$res) {
    echo json_encode(["error" => $mysqli->error]);
    exit;
}

// Armo meses últimos 12 (YYYY-MM)
$meses = [];
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $meses[] = $m;
}

$totales = []; // [mes][grupo] = $
$total_mes = []; // [mes] = $
foreach ($meses as $m) {
    $totales[$m] = ["Personal" => 0, "Logistica" => 0, "Generales" => 0, "Financieros" => 0];
    $total_mes[$m] = 0;
}

while ($row = $res->fetch_assoc()) {
    $p = $row['periodo'];
    $g = $row['grupo'];
    $v = floatval($row['total']);

    if (!isset($totales[$p])) {
        $totales[$p] = ["Personal" => 0, "Logistica" => 0, "Generales" => 0, "Financieros" => 0];
        $total_mes[$p] = 0;
    }
    if (!isset($totales[$p][$g])) $totales[$p][$g] = 0;
    $totales[$p][$g] += $v;
    $total_mes[$p] += $v;
}

// % por mes
$porcentajes = []; // [mes][grupo] = %
foreach ($meses as $m) {
    $porcentajes[$m] = [];
    $tm = max(0.000001, $total_mes[$m]); // evito /0
    foreach ($grupos as $g) {
        $porcentajes[$m][$g] = round(($totales[$m][$g] / $tm) * 100, 2);
    }
}

echo json_encode([
    "grupos" => $grupos,
    "meses" => $meses,               // YYYY-MM
    "totales" => $totales,           // $
    "total_mes" => $total_mes,       // $
    "porcentajes" => $porcentajes    // %
]);
