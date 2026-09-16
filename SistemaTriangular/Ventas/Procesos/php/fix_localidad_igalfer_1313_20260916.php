<?php
// Script de corrección ÚNICA Y TEMPORAL (2026-09-16) - se borra apenas corre bien.
// Recorrido 1313 (IGALFER): 11 pedidos con LocalidadDestino claramente mal
// (nombres de ciudades de cualquier parte del mundo - "Worblaufen",
// "Tlaltenango", "Madrid", "Comuna 1" - o el nombre de la CALLE en vez de
// la localidad). Causa raíz: Ventas/AgregarRepoVentaWeb.php pisaba la
// Ciudad ya buena con lo que devolviera geolocalizar() (geocode de Google
// sin sesgo geográfico), sin validar que la provincia devuelta tuviera
// sentido - ya corregido en el código (region=ar + validación de
// provincia). Esto repara los 11 que ya habían quedado mal.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$filas = [
    // CodigoSeguimiento => [LocalidadDestino esperado actual-en-DB-a-verificar]
    ['IHE2OETBH', 'Buenos Aires', 'Villa María'],
    ['ZISFF6R52', 'Tlaltenango', 'Rio Ceballos'],
    ['6APLM449Q', 'General Piran', 'Pilar'],
    ['Q0AOJSNBL', 'Worblaufen', 'Villa María'],
    ['6396CJ4XZ', 'Malvinas Argentinas', 'Jesus María'],
    ['SX9701HUT', 'Comuna 1', 'Monte Cristo'],
    ['XGABLEKE5', 'General Guemes', 'Jesus María'],
    ['J5KVT4KRZ', 'General Rodríguez', 'Pilar'],
    ['OLQIP478N', 'Santiago', 'Sinsacate'],
    ['1DEF66TJI', 'Salta', 'Villa María'],
    ['CEZW1CTSM', 'Madrid', 'Villa Parque Santa Ana'],
];

$stmt = $mysqli->prepare("UPDATE TransClientes SET LocalidadDestino=? WHERE CodigoSeguimiento=? AND LocalidadDestino=? AND Eliminado=0 LIMIT 1");

foreach ($filas as [$cs, $actualEsperado, $nuevo]) {
    $stmt->bind_param('sss', $nuevo, $cs, $actualEsperado);
    $ok = $stmt->execute();
    $filas_afectadas = $ok ? $stmt->affected_rows : -1;
    echo "{$cs}: " . ($filas_afectadas === 1 ? "OK -> {$nuevo}" : ($filas_afectadas === 0 ? "SALTEADO (ya no tenía \"{$actualEsperado}\", revisar a mano)" : "ERROR " . $mysqli->error)) . "\n";
}

echo "FIN.\n";
