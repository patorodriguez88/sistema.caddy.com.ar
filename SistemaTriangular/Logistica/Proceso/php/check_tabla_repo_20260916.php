<?php
// Script temporal ÚNICO (2026-09-16) - chequea si reposiciones_dinter
// existe en esta base (duda de Patricio: ¿hay que crearla en producción?).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$r = $mysqli->query("SHOW TABLES LIKE 'reposiciones_dinter'");
$existe = $r && $r->num_rows > 0;

echo json_encode(['existe' => $existe]);
