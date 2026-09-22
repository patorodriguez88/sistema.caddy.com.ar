<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function q($mysqli, $sql) {
    try {
        $res = $mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : ['__error' => $mysqli->error];
    } catch (\Throwable $e) {
        return ['__exception' => $e->getMessage()];
    }
}

$out = [];
$out['logistica_1470'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, NombreChofer, Estado, Eliminado, HoraSalidaReal FROM Logistica WHERE Recorrido=1470 ORDER BY Fecha DESC LIMIT 5");
$out['hdr_recorrido_1470_todas'] = q($mysqli, "SELECT id, Fecha, Estado, NumerodeOrden, Posicion, Seguimiento, Eliminado FROM HojaDeRuta WHERE Recorrido=1470 AND Fecha >= CURDATE() - INTERVAL 1 DAY ORDER BY id DESC LIMIT 30");
$out['hdr_orden_17203'] = q($mysqli, "SELECT id, Fecha, Recorrido, Estado, NumerodeOrden, Posicion, Seguimiento, Eliminado FROM HojaDeRuta WHERE NumerodeOrden=17203 ORDER BY id DESC LIMIT 30");
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
