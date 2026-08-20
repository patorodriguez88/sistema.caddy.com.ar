<?php
include_once "../../Conexion/Conexioni.php";

// La tabla muestra los ultimos 12 meses corridos (igual que el resto de los
// endpoints de este dashboard), no un año calendario -- si se filtraba por
// YEAR(Fecha) = año actual, los meses del año anterior que entran en la
// ventana de 12 meses (ej. sep-dic si estamos en agosto) nunca aparecian.
$fechaDesde = date('Y-m-01', strtotime('-11 months'));
$fechaHasta = date('Y-m-t');

// 1. SALDO INICIAL (todo lo anterior al inicio de la ventana de 12 meses)
$query1 = "
    SELECT SUM(Debe) - SUM(Haber) AS saldo_inicial
    FROM TransClientes
    WHERE Eliminado=0 AND Fecha < '$fechaDesde'
";
$result1 = $mysqli->query($query1);
$row1 = $result1->fetch_assoc();
$saldoInicial = floatval($row1['saldo_inicial'] ?? 0);

// 2. VENTAS SIMPLES (Flex = 0)
$querySimples = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo, SUM(Debe) AS total
    FROM TransClientes
    WHERE  Eliminado=0 AND Fecha BETWEEN '$fechaDesde' AND '$fechaHasta' AND Flex = 0
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
    WHERE  Eliminado=0 AND Fecha BETWEEN '$fechaDesde' AND '$fechaHasta' AND Flex = 1
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
    WHERE Fecha BETWEEN '$fechaDesde' AND '$fechaHasta' AND Eliminado = 0
    GROUP BY periodo
";
$ventasRecorridos = [];
$result = $mysqli->query($queryRecorridos);
while ($row = $result->fetch_assoc()) {
    $ventasRecorridos[$row['periodo']] = floatval($row['total']);
}

// 5. VENTAS COBRANZA (5% del CobrarEnvio) -- misma query que dashboard_cashflow_graficos.php
$queryCobranza = "
    SELECT DATE_FORMAT(FechaPedido, '%Y-%m') AS periodo,
           SUM(CobrarEnvio) * 0.05 AS total
    FROM Ventas
    WHERE FechaPedido BETWEEN '$fechaDesde' AND '$fechaHasta'
      AND Eliminado = 0
      AND surrender_number <> 0
      AND CobrarEnvio > 0
    GROUP BY periodo
";
$ventasCobranza = [];
$result = $mysqli->query($queryCobranza);
while ($row = $result->fetch_assoc()) {
    $ventasCobranza[$row['periodo']] = floatval($row['total']);
}

// 6. GASTOS -- misma query que dashboard_cashflow_graficos.php
$queryGastos = "
    SELECT DATE_FORMAT(Tesoreria.Fecha, '%Y-%m') AS periodo,
           SUM(Tesoreria.Debe) AS total
    FROM Tesoreria
    JOIN PlanDeCuentas ON PlanDeCuentas.Cuenta = Tesoreria.Cuenta
    WHERE Tesoreria.NoOperativo = 0
      AND PlanDeCuentas.MuestraGastos = 1
      AND Tesoreria.Eliminado = 0
      AND Tesoreria.Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
    GROUP BY periodo
";
$gastos = [];
$result = $mysqli->query($queryGastos);
while ($row = $result->fetch_assoc()) {
    $gastos[$row['periodo']] = floatval($row['total']);
}

// 7. Salida JSON
header('Content-Type: application/json');
echo json_encode([
    'saldo_inicial' => $saldoInicial,
    'ventas_simples' => $ventasSimples,
    'ventas_flex' => $ventasFlex,
    'ventas_recorridos' => $ventasRecorridos,
    'ventas_cobranza' => $ventasCobranza,
    'gastos' => $gastos
]);
exit;
