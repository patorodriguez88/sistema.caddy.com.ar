<?php
// Script de backfill ÚNICO Y TEMPORAL (2026-09-15) - se borra apenas corre bien.
// Corrige LocalidadDestino en pedidos ABIERTOS (no entregados/devueltos/
// eliminados) cuyo campo "Ciudad" vino mal por API (ver fix en
// api.caddy.com.ar/clases/servicios.class.php, misma fecha) - se re-parsea
// la localidad real desde DomicilioDestino ("Calle Numero, Localidad,
// Provincia de X") y solo se actualiza si difiere de lo guardado.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

// Misma lógica exacta que localidadDesdeDireccion() en
// api.caddy.com.ar/clases/servicios.class.php - si cambia una, cambiar la otra.
function localidadDesdeDireccion(string $direccion): ?string
{
    if (preg_match('/,\s*([^,]+?)\s*,\s*Provincia\s+de\s+/iu', $direccion, $m)) {
        $localidad = trim($m[1]);
        if ($localidad !== '') {
            return $localidad;
        }
    }
    return null;
}

$res = $mysqli->query("
    SELECT id, DomicilioDestino, LocalidadDestino
    FROM TransClientes
    WHERE Eliminado=0 AND Entregado=0 AND Devuelto=0
      AND DomicilioDestino LIKE '%Provincia de%'
");

$revisadas = 0;
$corregidas = 0;
$sinCambio = 0;
$sinMatch = 0;

$upd = $mysqli->prepare("UPDATE TransClientes SET LocalidadDestino=? WHERE id=?");

while ($row = $res->fetch_assoc()) {
    $revisadas++;
    $localidadReal = localidadDesdeDireccion($row['DomicilioDestino']);

    if ($localidadReal === null) {
        $sinMatch++;
        continue;
    }

    $actual = trim((string)$row['LocalidadDestino']);
    // Comparación case-insensitive: "CORDOBA" vs "Córdoba" no es un error a
    // corregir, solo nos interesan los casos realmente distintos.
    if (mb_strtolower($actual) === mb_strtolower($localidadReal)) {
        $sinCambio++;
        continue;
    }

    $id = (int)$row['id'];
    $upd->bind_param('si', $localidadReal, $id);
    if ($upd->execute()) {
        $corregidas++;
        echo "OK id={$id}: \"{$actual}\" -> \"{$localidadReal}\" (Domicilio: {$row['DomicilioDestino']})\n";
    } else {
        echo "ERROR id={$id}: " . $mysqli->error . "\n";
    }
}

echo "\nRevisadas: {$revisadas} | Corregidas: {$corregidas} | Sin cambio: {$sinCambio} | Sin match de formato: {$sinMatch}\n";
echo "FIN.\n";
