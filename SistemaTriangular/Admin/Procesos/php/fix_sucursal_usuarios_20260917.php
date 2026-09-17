<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Completa usuarios.Sucursal='Córdoba' donde está vacío/NULL (única
// sucursal real de la base) y recalcula los asientos de Tesoreria que
// quedaron con Sucursal='' por este motivo (conciliación bancaria).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dryRun = (int) ($_GET['dry'] ?? 1);

$usuariosAfectados = $mysqli->query("SELECT COUNT(*) AS n FROM usuarios WHERE Sucursal IS NULL OR Sucursal=''")->fetch_assoc()['n'];
$tesoreriaAfectados = $mysqli->query("SELECT COUNT(*) AS n FROM Tesoreria WHERE Eliminado=0 AND Sucursal=''")->fetch_assoc()['n'];

$usuariosActualizados = 0;
$tesoreriaActualizados = 0;

if ($dryRun === 0) {
    $ok1 = $mysqli->query("UPDATE usuarios SET Sucursal='Córdoba' WHERE Sucursal IS NULL OR Sucursal=''");
    $usuariosActualizados = $ok1 ? $mysqli->affected_rows : 0;

    $ok2 = $mysqli->query("UPDATE Tesoreria SET Sucursal='Córdoba' WHERE Eliminado=0 AND Sucursal=''");
    $tesoreriaActualizados = $ok2 ? $mysqli->affected_rows : 0;
}

echo json_encode([
    'dry_run' => $dryRun === 1,
    'usuarios_afectados' => (int) $usuariosAfectados,
    'usuarios_actualizados' => $usuariosActualizados,
    'tesoreria_afectados' => (int) $tesoreriaAfectados,
    'tesoreria_actualizados' => $tesoreriaActualizados,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
