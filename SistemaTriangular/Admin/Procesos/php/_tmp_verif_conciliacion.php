<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
$r = $mysqli->query("SHOW COLUMNS FROM Tesoreria LIKE 'UsuarioConciliado'")->fetch_assoc();
$r2 = $mysqli->query("SHOW COLUMNS FROM Tesoreria LIKE 'idConciliacionBancaria'")->fetch_assoc();
$r3 = $mysqli->query("DESCRIBE ConciliacionBancaria");
$cols = [];
while ($row = $r3->fetch_assoc()) $cols[] = $row;
echo json_encode(['UsuarioConciliado'=>$r, 'idConciliacionBancaria'=>$r2, 'ConciliacionBancaria_cols'=>$cols], JSON_PRETTY_PRINT);
