<?php
// Script temporal ÚNICO (2026-09-17) - diagnóstico puro lectura, sin
// escribir nada. Pedido: "revisá los horarios, no me suenan a hora de
// Córdoba Argentina".
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$phpDefaultTz = date_default_timezone_get();
$phpTimeSinAjustar = date('Y-m-d H:i:s'); // usa el default del server/php.ini

date_default_timezone_set('America/Argentina/Buenos_Aires');
$phpTimeArgentina = date('Y-m-d H:i:s');

$mysqlRow = $mysqli->query("SELECT NOW() AS mysql_now, UTC_TIMESTAMP() AS mysql_utc, @@global.time_zone AS global_tz, @@session.time_zone AS session_tz")->fetch_assoc();

$shell = null;
if (function_exists('shell_exec')) {
    $shell = @shell_exec('date -u "+%Y-%m-%d %H:%M:%S UTC (tz=%Z)"');
}

echo json_encode([
    'php_timezone_default_del_ini' => $phpDefaultTz,
    'php_date_SIN_ajustar_timezone' => $phpTimeSinAjustar,
    'php_date_CON_date_default_timezone_set_argentina' => $phpTimeArgentina,
    'mysql' => $mysqlRow,
    'shell_date_-u' => $shell !== null ? trim($shell) : 'shell_exec deshabilitado',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
