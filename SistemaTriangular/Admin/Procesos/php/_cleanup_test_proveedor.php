<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
$mysqli->query("DELETE FROM Proveedores WHERE RazonSocial LIKE 'TEST COMILLA %'");
echo json_encode(['borrados' => $mysqli->affected_rows]);
