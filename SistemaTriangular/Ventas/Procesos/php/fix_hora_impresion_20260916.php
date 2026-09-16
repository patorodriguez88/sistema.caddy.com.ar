<?php
// Script de corrección ÚNICA Y TEMPORAL (2026-09-16) - se borra apenas corre bien.
// La marca de "Etiqueta_impresa_h" de TransClientes.id=333539 (L0L28D5QZ)
// quedó con 3hs de más (CURTIME() del server de MySQL, en vez de hora de
// Argentina) - mismo bug que se acaba de corregir en
// Logistica/Proceso/php/etiquetas_recorrido.php. Se resta 3hs a esa marca
// puntual.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$id = 333539;
$codigo = 'L0L28D5QZ';

$chk = $mysqli->query("SELECT id, CodigoSeguimiento, Etiqueta_impresa_h FROM TransClientes WHERE id={$id}")->fetch_assoc();
if (!$chk || $chk['CodigoSeguimiento'] !== $codigo) {
    echo "ABORTA: la fila id={$id} no coincide con {$codigo} (o no existe). No se tocó nada.\n";
    exit;
}
if ($chk['Etiqueta_impresa_h'] !== '07:50:45') {
    echo "ABORTA: Etiqueta_impresa_h ya no es 07:50:45 (" . $chk['Etiqueta_impresa_h'] . "). No se tocó nada, revisar a mano.\n";
    exit;
}

$r = $mysqli->query("UPDATE TransClientes SET Etiqueta_impresa_h = SUBTIME(Etiqueta_impresa_h, '03:00:00') WHERE id={$id} LIMIT 1");
echo "TransClientes id={$id}: " . ($r ? "OK ({$mysqli->affected_rows} fila)" : "ERROR " . $mysqli->error) . "\n";

echo "FIN.\n";
