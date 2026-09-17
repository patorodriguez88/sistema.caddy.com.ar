<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
$r = $mysqli->query("SELECT NOW() AS mysql_now, UTC_TIMESTAMP() AS mysql_utc, @@session.time_zone AS tz");
echo json_encode($r->fetch_assoc());
