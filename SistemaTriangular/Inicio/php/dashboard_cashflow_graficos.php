<?php
include_once("../../Conexion/Conexioni.php");

$año = isset($_POST['anio']) ? intval($_POST['anio']) : date('Y');
$fechaDesde = date('Y-m-01', strtotime('-11 months'));
$fechaHasta = date('Y-m-t');

// 1. VENTAS SIMPLES (TransClientes)
$queryVentasSimples = "SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo,
SUM(Debe) AS total
FROM TransClientes
WHERE Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
AND Eliminado = 0
GROUP BY periodo";

// 2. VENTAS RECORRIDOS (Logistica)
$queryRecorridos = "SELECT DATE_FORMAT(Fecha, '%Y-%m') AS periodo,
SUM(IF(ImporteF=0, TotalFacturado, ImporteF)) AS total
FROM Logistica
WHERE Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
AND Eliminado = 0
GROUP BY periodo";

// 3. VENTAS COBRANZA (5% del CobrarEnvio)
$queryCobranza = "SELECT DATE_FORMAT(FechaPedido, '%Y-%m') AS periodo, 
SUM(CobrarEnvio) * 0.05 AS total
FROM Ventas
WHERE FechaPedido BETWEEN '$fechaDesde' AND '$fechaHasta'
AND Eliminado = 0
AND surrender_number <> 0
AND CobrarEnvio > 0
GROUP BY periodo";

// 4. GASTOS
$queryGastos = "SELECT DATE_FORMAT(Tesoreria.Fecha, '%Y-%m') AS periodo,
SUM(Tesoreria.Debe) AS total
FROM Tesoreria
JOIN PlanDeCuentas ON PlanDeCuentas.Cuenta = Tesoreria.Cuenta
WHERE Tesoreria.NoOperativo = 0
AND PlanDeCuentas.MuestraGastos = 1
AND Tesoreria.Eliminado = 0
AND Tesoreria.Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
GROUP BY periodo";

function obtenerDatos($query, $mysqli)
{
    $res = $mysqli->query($query);
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[$row['periodo']] = round($row['total'], 2);
    }
    return $datos;
}

// Ejecutar consultas
$ventasSimples = obtenerDatos($queryVentasSimples, $mysqli);
$ventasRecorridos = obtenerDatos($queryRecorridos, $mysqli);
$ventasCobranza = obtenerDatos($queryCobranza, $mysqli);
$gastos = obtenerDatos($queryGastos, $mysqli);

// 5. SUMA TOTAL DE VENTAS
$ventas = $ventasSimples; // copiar base

// Sumar ventas por recorrido
foreach ($ventasRecorridos as $mes => $valorRecorrido) {
    if (isset($ventas[$mes])) {
        $ventas[$mes] += $valorRecorrido;
    } else {
        $ventas[$mes] = $valorRecorrido;
    }
}

// Sumar cobranzas
foreach ($ventasCobranza as $mes => $valorCobranza) {
    if (isset($ventas[$mes])) {
        $ventas[$mes] += $valorCobranza;
    } else {
        $ventas[$mes] = $valorCobranza;
    }
}

// 6. CALCULO DE SALDO FINAL
// 6. CALCULO DE SALDO FINAL (ventas netas sin IVA menos gastos)
$saldo = [];
$meses = array_unique(array_merge(
    array_keys($ventasSimples),
    array_keys($ventasRecorridos),
    array_keys($ventasCobranza),
    array_keys($gastos)
));

foreach ($meses as $mes) {
    $simples = isset($ventasSimples[$mes]) ? $ventasSimples[$mes] / 1.21 : 0;
    $recorridos = isset($ventasRecorridos[$mes]) ? $ventasRecorridos[$mes] / 1.21 : 0;
    $cobranza = isset($ventasCobranza[$mes]) ? $ventasCobranza[$mes] : 0;
    $g = isset($gastos[$mes]) ? $gastos[$mes] : 0;

    $saldo[$mes] = round($simples + $recorridos + $cobranza - $g, 2);
}

// 7. RESPUESTA JSON DETALLADA
echo json_encode([
    "ventas_simples" => $ventasSimples,
    "ventas_recorridos" => $ventasRecorridos,
    "ventas_cobranza" => $ventasCobranza,
    "ventas" => $ventas,
    "gastos" => $gastos,
    "saldo" => $saldo
]);
