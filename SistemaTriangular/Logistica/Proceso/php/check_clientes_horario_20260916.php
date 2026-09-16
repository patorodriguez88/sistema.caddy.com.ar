<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
$r = $mysqli->query("SHOW COLUMNS FROM Clientes LIKE 'HorarioEntrega%'");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
