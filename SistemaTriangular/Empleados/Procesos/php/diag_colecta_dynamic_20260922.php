<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$out = [];

// 1. Cliente Dynamic
$res = $mysqli->query("SELECT id, Nombre FROM Clientes WHERE Nombre LIKE '%Dynamic%' AND Eliminado=0");
$out['clientes_dynamic'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : null;

// 2. Colecta rows recientes para Recorrido 1472
$res = $mysqli->query("SELECT * FROM Colecta WHERE Recorrido=1472 AND Eliminado=0 ORDER BY id DESC LIMIT 20");
$out['colecta_recorrido_1472'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : null;

// 3. Colecta rows recientes para Dynamic (cualquier recorrido), últimos 2 días
$res = $mysqli->query("
    SELECT C.* FROM Colecta C
    JOIN Clientes CL ON CL.id = C.idCliente
    WHERE CL.Nombre LIKE '%Dynamic%' AND C.Eliminado=0 AND C.Fecha >= CURDATE() - INTERVAL 2 DAY
    ORDER BY C.id DESC LIMIT 20
");
$out['colecta_dynamic_recientes'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : null;

// 4. Logistica de hoy recorrido 1472
$res = $mysqli->query("SELECT id, Fecha, Recorrido, Usuario, HoraSalidaReal, NumerodeOrden, Eliminado FROM Logistica WHERE Recorrido=1472 ORDER BY Fecha DESC LIMIT 5");
$out['logistica_1472'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : null;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
