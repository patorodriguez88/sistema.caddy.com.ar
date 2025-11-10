<?php
include_once "../../Conexion/Conexioni.php";

$año = isset($_POST['anio']) ? intval($_POST['anio']) : date('Y');

// Generar los últimos 12 meses como claves tipo "jun-25", "jul-25"
$headers = [];
for ($i = 11; $i >= 0; $i--) {
    $fecha = strtotime(date('Y-m-01') . " -$i months");
    $mesAbrev = mb_strtolower(date('M', $fecha), 'UTF-8');
    $mesAbrev = strtr($mesAbrev, ['jan' => 'ene', 'apr' => 'abr', 'aug' => 'ago', 'dec' => 'dic']);
    $anioCorto = date('y', $fecha);
    $headers[] = "$mesAbrev-$anioCorto";
}

// Fechas para la consulta SQL
$fechaDesde = date('Y-m-01', strtotime('-11 months'));
$fechaHasta = date('Y-m-t');

$query = "SELECT 
        Tesoreria.Cuenta,
        Tesoreria.NombreCuenta,
        DATE_FORMAT(Tesoreria.Fecha, '%Y-%m') AS mes,
        SUM(Tesoreria.Debe) AS total
    FROM Tesoreria
    JOIN PlanDeCuentas ON PlanDeCuentas.Cuenta = Tesoreria.Cuenta
    WHERE Tesoreria.NoOperativo = 0 
      AND PlanDeCuentas.MuestraGastos = 1 
      AND Tesoreria.Eliminado = 0
      AND Tesoreria.Fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
    GROUP BY Tesoreria.Cuenta, mes
    ORDER BY Tesoreria.Cuenta, mes
";

$res = $mysqli->query($query);
$datos = [];

// Función para transformar "2025-06" → "jun-25"
function transformarClave($fechaYm)
{
    $ts = strtotime($fechaYm . "-01");
    $mes = mb_strtolower(date('M', $ts), 'UTF-8');
    $mes = strtr($mes, ['jan' => 'ene', 'apr' => 'abr', 'aug' => 'ago', 'dec' => 'dic']);
    $anio = date('y', $ts);
    return "$mes-$anio";
}

while ($row = $res->fetch_assoc()) {
    $cuenta = $row['Cuenta'];
    $nombre = $row['NombreCuenta'];
    $mesFormateado = transformarClave($row['mes']);
    $monto = floatval($row['total']);

    if (!isset($datos[$cuenta])) {
        $datos[$cuenta] = [
            'cuenta' => $cuenta,
            'nombre' => $nombre
        ];
    }

    $datos[$cuenta][$mesFormateado] = $monto;
}

echo json_encode([
    'datos' => array_values($datos)
]);
exit;
