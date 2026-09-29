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

// 4. VENTAS RECORRIDOS: los cargos por orden de salida que se cargan en la cuenta corriente
// de los clientes que facturan por recorrido (Clientes > recorridos -> Ctasctes con
// FacturacionxRecorrido=1 e idLogistica de la orden, fechados el día de la orden). Es lo que
// después se agrupa en la factura del mes (ej. FA 2520 = las 5 órdenes del recorrido 1050).
// Antes salía de Logistica con IF(ImporteF=0, TotalFacturado, ImporteF): facturar.php graba en
// TotalFacturado el total de la FACTURA COMPLETA en cada orden que incluye, así que en las
// órdenes sin precio (recorrido 1314) se sumaba la factura entera varias veces - julio 2026
// mostraba $116,7 M en vez de $32,8 M (4 órdenes x FA 2581 de $21,1 M).
$queryRecorridos = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo, SUM(Debe) AS total
    FROM Ctasctes
    WHERE Eliminado = 0 AND FacturacionxRecorrido = 1 AND idLogistica > 0
      AND Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
    GROUP BY periodo
";
$ventasRecorridos = [];
$result = $mysqli->query($queryRecorridos);
while ($row = $result->fetch_assoc()) {
    $ventasRecorridos[$row['periodo']] = floatval($row['total']);
}

// 5. VENTAS COBRANZA (5% del CobrarEnvio)
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

// 6. GASTOS
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

// 6c. COBRADO A CLIENTES: los Recibos de Pago de la cuenta corriente (plata que entró,
// con IVA). Es informativo: no se suma al resultado, que se calcula sobre lo vendido.
$queryCobrado = "
    SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo, SUM(Haber) AS total
    FROM Ctasctes
    WHERE Eliminado = 0 AND TipoDeComprobante = 'Recibo de Pago'
      AND Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
    GROUP BY periodo
";
$cobrado = [];
$result = $mysqli->query($queryCobrado);
while ($row = $result->fetch_assoc()) {
    $cobrado[$row['periodo']] = floatval($row['total']);
}

// 7. Salida JSON
header('Content-Type: application/json');
echo json_encode([
    'saldo_inicial' => $saldoInicial,
    'ventas_simples' => $ventasSimples,
    'ventas_flex' => $ventasFlex,
    'ventas_recorridos' => $ventasRecorridos,
    'ventas_cobranza' => $ventasCobranza,
    'gastos' => $gastos,
    'cobrado' => $cobrado
]);
exit;
