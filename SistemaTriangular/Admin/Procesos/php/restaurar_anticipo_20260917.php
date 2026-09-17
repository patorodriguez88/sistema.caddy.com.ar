<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Restaura el Anticipo a Acreedores del 06/08 (asiento 37374412) que
// quedó eliminado como daño colateral del bug de número de asiento
// colisionado (ver tarea Asana "Eliminar asientos y corregir fecha de
// carga"). Son exactamente 2 filas: id 140283 y 140284.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$ids = [140283, 140284];
$in = implode(',', $ids);

$antes = $mysqli->query("SELECT id, Fecha, NombreCuenta, Debe, Haber, Eliminado FROM Tesoreria WHERE id IN ($in)");
$filasAntes = [];
while ($r = $antes->fetch_assoc()) $filasAntes[] = $r;

$infoABM = "Restaurado por pedido de Patricio Rodriguez (tarea Asana, era daño colateral del bug de asiento colisionado - la fila estaba activa hasta que la pisó el asiento 37374412 nuevo) el " . date('d-m-Y H:i');
$infoABMEsc = $mysqli->real_escape_string($infoABM);

$ok = $mysqli->query("UPDATE Tesoreria SET Eliminado=0, InfoABM='{$infoABMEsc}' WHERE id IN ($in)");
$actualizados = $ok ? $mysqli->affected_rows : 0;

$despues = $mysqli->query("SELECT id, Fecha, NombreCuenta, Debe, Haber, Eliminado FROM Tesoreria WHERE id IN ($in)");
$filasDespues = [];
while ($r = $despues->fetch_assoc()) $filasDespues[] = $r;

echo json_encode([
    'actualizados' => $actualizados,
    'antes' => $filasAntes,
    'despues' => $filasDespues,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
