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
// Esto corrige los asientos históricos ya afectados: se detectan pares de
// filas ACTIVAS del mismo NumeroAsiento donde el NombreCuenta guardado de
// una fila coincide exactamente con el nombre REAL (PlanDeCuentas) de la
// OTRA fila, y viceversa (swap limpio de a 2, sin ambigüedad). Se excluye
// a propósito el bug de truncamiento de NombreCuenta (columna varchar(35),
// ej. "COMISIONES E IMPUESTOS DE TARJETAS" truncado de "...DE CREDITO") -
// no es este bug, no se toca.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;

$sqlDetectar = "
SELECT t1.NumeroAsiento,
       t1.id AS id1, t1.Cuenta AS cuenta1, t1.NombreCuenta AS guardado1, p1.NombreCuenta AS real1,
       t2.id AS id2, t2.Cuenta AS cuenta2, t2.NombreCuenta AS guardado2, p2.NombreCuenta AS real2
FROM Tesoreria t1
JOIN PlanDeCuentas p1 ON p1.Cuenta = t1.Cuenta
JOIN Tesoreria t2 ON t2.NumeroAsiento = t1.NumeroAsiento AND t2.id > t1.id AND t2.Eliminado = 0
JOIN PlanDeCuentas p2 ON p2.Cuenta = t2.Cuenta
WHERE t1.Eliminado = 0
  AND t1.NombreCuenta <> p1.NombreCuenta
  AND t2.NombreCuenta <> p2.NombreCuenta
  AND t1.NombreCuenta = p2.NombreCuenta
  AND t2.NombreCuenta = p1.NombreCuenta
  AND t1.Cuenta <> t2.Cuenta
ORDER BY t1.NumeroAsiento DESC";

$resultado = $mysqli->query($sqlDetectar);
$pares = [];
while ($fila = $resultado->fetch_assoc()) {
    $pares[] = $fila;
}

$actualizados = 0;
$detalle = [];
foreach ($pares as $par) {
    $detalle[] = [
        'NumeroAsiento' => $par['NumeroAsiento'],
        'fila1' => ['id' => $par['id1'], 'cuenta' => $par['cuenta1'], 'guardado' => $par['guardado1'], 'corregido_a' => $par['real1']],
        'fila2' => ['id' => $par['id2'], 'cuenta' => $par['cuenta2'], 'guardado' => $par['guardado2'], 'corregido_a' => $par['real2']],
    ];

    if (!$dry) {
        $real1Esc = $mysqli->real_escape_string($par['real1']);
        $real2Esc = $mysqli->real_escape_string($par['real2']);
        $infoNota = "Corregido cruce de NombreCuenta (bug de asiento - tarea Asana) el " . date('d-m-Y H:i');
        $infoNotaEsc = $mysqli->real_escape_string($infoNota);

        $mysqli->query("UPDATE Tesoreria SET NombreCuenta = '{$real1Esc}', InfoABM = CONCAT(IFNULL(InfoABM,''), ' | {$infoNotaEsc}') WHERE id = {$par['id1']}");
        $actualizados += $mysqli->affected_rows;

        $mysqli->query("UPDATE Tesoreria SET NombreCuenta = '{$real2Esc}', InfoABM = CONCAT(IFNULL(InfoABM,''), ' | {$infoNotaEsc}') WHERE id = {$par['id2']}");
        $actualizados += $mysqli->affected_rows;
    }
}

echo json_encode([
    'dry_run' => $dry,
    'pares_encontrados' => count($pares),
    'filas_actualizadas' => $actualizados,
    'detalle' => $detalle,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
