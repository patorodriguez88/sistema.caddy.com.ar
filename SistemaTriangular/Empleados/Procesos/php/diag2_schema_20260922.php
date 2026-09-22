<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
$out = [];
try {
    $res = $mysqli->query("SHOW COLUMNS FROM Colecta");
    $out['colecta_cols'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : $mysqli->error;
} catch (\Throwable $e) { $out['colecta_cols_error'] = $e->getMessage(); }
try {
    $res = $mysqli->query("SHOW COLUMNS FROM Clientes");
    $out['clientes_cols'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : $mysqli->error;
} catch (\Throwable $e) { $out['clientes_cols_error'] = $e->getMessage(); }
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
