<?php
// Script de corrección ÚNICA Y TEMPORAL (2026-09-15) - se borra apenas corre bien.
// Paquete WXHRZEPOP (TransClientes.id=332785 / HojaDeRuta.id=323107): quedó
// Devuelto=1 para siempre por el bug corregido en Servicios/Procesos/php/
// funciones.php (INSERT de Seguimiento sin columna Devuelto). Se revierte a
// mano lo que la reversión automática debería haber hecho al borrar el
// movimiento "Devuelto al Cliente".
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$idTC = 332785;
$idHdr = 323107;
$codigo = 'WXHRZEPOP';

// Verificación defensiva antes de tocar nada.
$chk = $mysqli->query("SELECT id, CodigoSeguimiento, Devuelto, Entregado, Eliminado FROM TransClientes WHERE id={$idTC}")->fetch_assoc();
if (!$chk || $chk['CodigoSeguimiento'] !== $codigo) {
    echo "ABORTA: la fila TransClientes id={$idTC} no coincide con {$codigo} (o no existe). No se tocó nada.\n";
    exit;
}
if ((int)$chk['Devuelto'] !== 1) {
    echo "ABORTA: TransClientes.Devuelto ya no es 1 (" . $chk['Devuelto'] . "). No se tocó nada, revisar a mano.\n";
    exit;
}

$r1 = $mysqli->query("UPDATE TransClientes SET Devuelto=0 WHERE id={$idTC} AND Devuelto=1 LIMIT 1");
echo "TransClientes id={$idTC}: " . ($r1 ? "OK ({$mysqli->affected_rows} fila)" : "ERROR " . $mysqli->error) . "\n";

$r2 = $mysqli->query("UPDATE HojaDeRuta SET Devuelto=0 WHERE id={$idHdr} AND Devuelto=1 LIMIT 1");
echo "HojaDeRuta id={$idHdr}: " . ($r2 ? "OK ({$mysqli->affected_rows} fila)" : "ERROR " . $mysqli->error) . "\n";

echo "FIN.\n";
