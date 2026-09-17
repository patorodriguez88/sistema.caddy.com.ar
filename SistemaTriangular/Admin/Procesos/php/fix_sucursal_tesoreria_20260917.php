<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Corrige Tesoreria.Sucursal=NULL (asientos cargados manualmente vía
// Admin/Procesos/php/contabilidad.php sin ese campo) para que vuelvan a
// aparecer en Conciliación Bancaria (bancos.php filtra Sucursal='Córdoba').
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dryRun = (int) ($_GET['dry'] ?? 1);

$res = $mysqli->query("SELECT id, Fecha, NombreCuenta, Cuenta, Debe, Haber, Usuario, NumeroAsiento
                        FROM Tesoreria WHERE Eliminado=0 AND Sucursal IS NULL ORDER BY Fecha ASC");
$filas = [];
while ($r = $res->fetch_assoc()) {
    $filas[] = $r;
}

$actualizados = 0;
if ($dryRun === 0) {
    $ok = $mysqli->query("UPDATE Tesoreria SET Sucursal='Córdoba' WHERE Eliminado=0 AND Sucursal IS NULL");
    $actualizados = $ok ? $mysqli->affected_rows : 0;
}

echo json_encode([
    'dry_run' => $dryRun === 1,
    'total_afectados' => count($filas),
    'actualizados' => $actualizados,
    'muestra' => array_slice($filas, 0, 10),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
