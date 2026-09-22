<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$out = [];
$out['usuarios_nivel1'] = [];
$res = $mysqli->query("SELECT id, Usuario, Nivel, PuedeGestionarGastosExtras, Activo FROM usuarios WHERE Nivel='1' ORDER BY id");
if ($res) $out['usuarios_nivel1'] = $res->fetch_all(MYSQLI_ASSOC);

$res2 = $mysqli->query("SHOW COLUMNS FROM usuarios LIKE 'PuedeGestionarGastosExtras'");
$out['columna_existe'] = $res2 ? $res2->num_rows > 0 : null;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
