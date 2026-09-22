<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$codigos = ['4YG9OR3NQ','OHWNZK4U1','LOJXGEXUW','FHJ1FA3Y9','MOXID4397','OAIN5HZFO',
            'AECH2O2HB','8C4XXV8OG','SD9VOUM7R','9AUGY7OOT','TDVWTY118','7N6GWV16Q'];
$in = "'" . implode("','", array_map([$mysqli, 'real_escape_string'], $codigos)) . "'";

function q($mysqli, $sql) {
    try {
        $res = $mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : ['__error' => $mysqli->error];
    } catch (\Throwable $e) {
        return ['__exception' => $e->getMessage()];
    }
}

$out = [];
$out['codigos'] = $codigos;

$out['transclientes'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, CodigoSeguimiento, Estado, Entregado, Eliminado FROM TransClientes WHERE CodigoSeguimiento IN ($in) ORDER BY CodigoSeguimiento");
$out['seguimiento'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, CodigoSeguimiento, Estado, Eliminado FROM Seguimiento WHERE CodigoSeguimiento IN ($in) ORDER BY CodigoSeguimiento, id");
$out['hojaderuta'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, Seguimiento, Estado, Eliminado FROM HojaDeRuta WHERE Seguimiento IN ($in) ORDER BY Seguimiento");

$out['logistica_17093'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, Estado, Eliminado FROM Logistica WHERE NumerodeOrden=17093");
$out['logistica_17086'] = q($mysqli, "SELECT id, Fecha, NumerodeOrden, Recorrido, Estado, Eliminado FROM Logistica WHERE NumerodeOrden=17086");

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
