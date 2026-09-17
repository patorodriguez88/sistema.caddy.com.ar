<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Elimina (Eliminado=1) las filas ACTIVAS de los asientos 37374403,
// 37374411 y 37374412, a pedido explícito de la tarea Asana. Deja
// intactas las filas ya eliminadas (ej. el Anticipo del 06/08 que
// compartía el 37374412, marcado Eliminado=1 desde antes).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dryRun = (int) ($_GET['dry'] ?? 1);
$numeros = ['37374403', '37374411', '37374412'];
$in = "'" . implode("','", $numeros) . "'";

$res = $mysqli->query("SELECT id, Fecha, NombreCuenta, Cuenta, Debe, Haber, Observaciones, NumeroAsiento
                        FROM Tesoreria WHERE NumeroAsiento IN ($in) AND Eliminado=0
                        ORDER BY NumeroAsiento, id");
$filas = [];
while ($r = $res->fetch_assoc()) {
    $filas[] = $r;
}

$actualizados = 0;
if ($dryRun === 0) {
    $infoABM = "Eliminado por pedido de Agustina Oviedo (tarea Asana, asiento con número duplicado/colisionado) el " . date('d-m-Y H:i');
    $infoABMEsc = $mysqli->real_escape_string($infoABM);
    $ok = $mysqli->query("UPDATE Tesoreria SET Eliminado=1, InfoABM='{$infoABMEsc}' WHERE NumeroAsiento IN ($in) AND Eliminado=0");
    $actualizados = $ok ? $mysqli->affected_rows : 0;
}

echo json_encode([
    'dry_run' => $dryRun === 1,
    'total_afectados' => count($filas),
    'actualizados' => $actualizados,
    'filas' => $filas,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
