<?php
include_once "../../Conexion/Conexioni.php";

$año = isset($_POST['anio']) ? intval($_POST['anio']) : date('Y');
$año = 2024;
// 1. SALDO INICIAL
$query1 = "
    SELECT SUM(Debe) - SUM(Haber) AS saldo_inicial
    FROM TransClientes
    WHERE YEAR(Fecha) < $año
";
$result1 = $mysqli->query($query1);
$row1 = $result1->fetch_assoc();
$saldoInicial = floatval($row1['saldo_inicial'] ?? 0);

// 2. VENTAS SIMPLES (Flex = 0)
$querySimples = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo, SUM(Debe) AS total
    FROM TransClientes
    WHERE YEAR(Fecha) = $año AND Flex = 0
    GROUP BY periodo
";
$ventasSimples = [];
$result = $mysqli->query($querySimples);
while ($row = $result->fetch_assoc()) {
    $ventasSimples[$row['periodo']] = floatval($row['total']);
}

// 3. VENTAS FLEX (Flex = 1)
$queryFlex = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo, SUM(Debe) AS total
    FROM TransClientes
    WHERE YEAR(Fecha) = $año AND Flex = 1
    GROUP BY periodo
";
$ventasFlex = [];
$result = $mysqli->query($queryFlex);
while ($row = $result->fetch_assoc()) {
    $ventasFlex[$row['periodo']] = floatval($row['total']);
}

// 4. VENTAS RECORRIDOS (tabla Logistica)
$queryRecorridos = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo,
           SUM(IF(ImporteF=0, TotalFacturado, ImporteF)) AS total
    FROM Logistica
    WHERE YEAR(Fecha) = $año AND Eliminado = 0
    GROUP BY periodo
";
$ventasRecorridos = [];
$result = $mysqli->query($queryRecorridos);
while ($row = $result->fetch_assoc()) {
    $ventasRecorridos[$row['periodo']] = floatval($row['total']);
}

// 5. Salida JSON
header('Content-Type: application/json');
echo json_encode([
    'saldo_inicial' => $saldoInicial,
    'ventas_simples' => $ventasSimples,
    'ventas_flex' => $ventasFlex,
    'ventas_recorridos' => $ventasRecorridos
]);
exit;
