<?php
// Script temporal ÚNICO (2026-09-18) - se borra apenas corre bien.
// Tarea Asana "Corregir visualización de asientos contables al imprimir".
//
// Bug real (ver commit): la fila estática del HTML de "Nuevo Asiento" no
// tenía el input oculto nombreCuenta[] - el parche de confirmarAsiento()
// terminaba grabando el NombreCuenta de una cuenta en la fila de OTRA
// cuenta del mismo asiento (el Cuenta/Debe/Haber de cada fila SIEMPRE
// quedó correcto, solo el texto NombreCuenta salía cruzado).
//
// Esto corrige los 13 asientos históricos (26 filas) ya detectados con
// este cruce, vía consulta directa contra la réplica de solo-lectura
// (el self-join contra PlanDeCuentas en el server de producción daba
// timeout 504 - se resuelve acá con los ids ya identificados, más rápido
// y más auditable). Se excluye a propósito el bug de truncamiento de
// NombreCuenta (columna varchar(35), ej. "COMISIONES E IMPUESTOS DE
// TARJETAS" truncado de "...DE CREDITO") - no es este bug, no se toca.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;

// [id => nombre_correcto]
$correcciones = [
    140354 => 'CAJA',
    140355 => 'FLETES Y ENCOMIENDAS',
    140253 => 'CAJA',
    140254 => 'FLETES Y ENCOMIENDAS',
    140251 => 'CAJA',
    140252 => 'PEAJES Y ESTACIONAMIENTO',
    139932 => 'CAJA',
    139933 => 'RETENCIONES DE GANANCIAS',
    139926 => 'CAJA',
    139927 => 'RETENCIONES DE GANANCIAS',
    139899 => 'ANTICIPOS SUELDOS',
    139900 => 'CAJA',
    139725 => 'CAJA',
    139726 => 'RETENCIONES DE GANANCIAS',
    139697 => 'CAJA',
    139698 => 'RETENCIONES DE GANANCIAS',
    139609 => 'ANTICIPO GASTOS A RENDIR',
    139610 => 'CAJA',
    139569 => 'FONDO FIJO',
    139570 => 'CAJA',
    139558 => 'CUENTA PARTICULAR PATRICIO',
    139559 => 'CAJA',
    139556 => 'CUENTA PARTICULAR PATRICIO',
    139557 => 'CAJA',
    139458 => 'FONDO FIJO',
    139459 => 'PEAJES Y ESTACIONAMIENTO',
];

$ids = implode(',', array_keys($correcciones));

$antes = [];
$r = $mysqli->query("SELECT id, NumeroAsiento, Cuenta, NombreCuenta, Debe, Haber FROM Tesoreria WHERE id IN ($ids) ORDER BY NumeroAsiento DESC, id");
while ($fila = $r->fetch_assoc()) $antes[] = $fila;

$actualizados = 0;
if (!$dry) {
    $infoNota = "Corregido cruce de NombreCuenta (bug de asiento - tarea Asana) el " . date('d-m-Y H:i');
    $infoNotaEsc = $mysqli->real_escape_string($infoNota);
    foreach ($correcciones as $id => $nombreCorrecto) {
        $nombreEsc = $mysqli->real_escape_string($nombreCorrecto);
        $mysqli->query("UPDATE Tesoreria SET NombreCuenta = '{$nombreEsc}', InfoABM = CONCAT(IFNULL(InfoABM,''), ' | {$infoNotaEsc}') WHERE id = {$id} AND Eliminado = 0");
        $actualizados += $mysqli->affected_rows;
    }
}

$despues = [];
$r2 = $mysqli->query("SELECT id, NumeroAsiento, Cuenta, NombreCuenta, Debe, Haber FROM Tesoreria WHERE id IN ($ids) ORDER BY NumeroAsiento DESC, id");
while ($fila = $r2->fetch_assoc()) $despues[] = $fila;

echo json_encode([
    'dry_run' => $dry,
    'total_filas' => count($correcciones),
    'filas_actualizadas' => $actualizados,
    'antes' => $antes,
    'despues' => $despues,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
