<?php
// Script de migración ÚNICA Y TEMPORAL (2026-09-16) - se borra apenas corre bien.
// Agrega a TransClientes las columnas para marcar "ya se imprimió la
// etiqueta/rótulo de este envío, por quién y a qué hora" (pantalla
// Etiquetas por Recorrido) - mismo criterio de nombres que ya usa
// Wepoint_f/h/status para el pistoleo de depósito.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$chk = $mysqli->query("SHOW COLUMNS FROM TransClientes LIKE 'Etiqueta_impresa_f'");
if ($chk && $chk->num_rows > 0) {
    echo "Las columnas ya existen, no se toca nada.\n";
    exit;
}

$sql = "ALTER TABLE TransClientes
    ADD COLUMN Etiqueta_impresa_f DATE NULL DEFAULT NULL AFTER Cantidad,
    ADD COLUMN Etiqueta_impresa_h TIME NULL DEFAULT NULL AFTER Etiqueta_impresa_f,
    ADD COLUMN Etiqueta_impresa_usuario VARCHAR(50) NULL DEFAULT NULL AFTER Etiqueta_impresa_h";

if ($mysqli->query($sql)) {
    echo "OK: columnas agregadas.\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}
