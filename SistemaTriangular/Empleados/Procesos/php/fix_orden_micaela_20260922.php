<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Pedido de Micaela Quinteros (Asana, tarea 1218695097925713): estos 12
// códigos se colectaron correctamente bajo la orden 17086 (Recorrido 1023
// - Seguimiento ya muestra NumerodeOrden=17086 en "A Retirar"/"Retirado
// del Cliente"), pero al marcarse "Entregado al Cliente" quedaron con
// NumerodeOrden=17093 (Recorrido 80) en vez de 17086. Se corrige
// TransClientes + la fila "Entregado al Cliente" de Seguimiento (las
// anteriores ya están bien) + HojaDeRuta (Cerrado, NumerodeOrden=0).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$codigos = ['4YG9OR3NQ','OHWNZK4U1','LOJXGEXUW','FHJ1FA3Y9','MOXID4397','OAIN5HZFO',
            'AECH2O2HB','8C4XXV8OG','SD9VOUM7R','9AUGY7OOT','TDVWTY118','7N6GWV16Q'];
$in = "'" . implode("','", array_map([$mysqli, 'real_escape_string'], $codigos)) . "'";

$out = ['dry_run' => $dry];

function q($mysqli, $sql) {
    try {
        $res = $mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : ['__error' => $mysqli->error];
    } catch (\Throwable $e) {
        return ['__exception' => $e->getMessage()];
    }
}

$out['antes'] = [
    'transclientes' => q($mysqli, "SELECT CodigoSeguimiento, NumerodeOrden FROM TransClientes WHERE CodigoSeguimiento IN ($in) ORDER BY CodigoSeguimiento"),
    'seguimiento_entregado' => q($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden, Estado FROM Seguimiento WHERE CodigoSeguimiento IN ($in) AND Estado='Entregado al Cliente' AND Eliminado=0 ORDER BY CodigoSeguimiento"),
    'hojaderuta' => q($mysqli, "SELECT id, Seguimiento, NumerodeOrden, Estado FROM HojaDeRuta WHERE Seguimiento IN ($in) ORDER BY Seguimiento"),
];

if (!$dry) {
    $r1 = $mysqli->query("UPDATE TransClientes SET NumerodeOrden=17086 WHERE CodigoSeguimiento IN ($in) AND NumerodeOrden=17093 AND Eliminado=0");
    $out['transclientes_actualizados'] = $mysqli->affected_rows;

    $r2 = $mysqli->query("UPDATE Seguimiento SET NumerodeOrden=17086 WHERE CodigoSeguimiento IN ($in) AND NumerodeOrden=17093 AND Estado='Entregado al Cliente' AND Eliminado=0");
    $out['seguimiento_actualizados'] = $mysqli->affected_rows;

    $r3 = $mysqli->query("UPDATE HojaDeRuta SET NumerodeOrden=17086 WHERE Seguimiento IN ($in) AND NumerodeOrden=0");
    $out['hojaderuta_actualizados'] = $mysqli->affected_rows;
}

$out['despues'] = [
    'transclientes' => q($mysqli, "SELECT CodigoSeguimiento, NumerodeOrden FROM TransClientes WHERE CodigoSeguimiento IN ($in) ORDER BY CodigoSeguimiento"),
    'seguimiento_entregado' => q($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden, Estado FROM Seguimiento WHERE CodigoSeguimiento IN ($in) AND Estado='Entregado al Cliente' AND Eliminado=0 ORDER BY CodigoSeguimiento"),
    'hojaderuta' => q($mysqli, "SELECT id, Seguimiento, NumerodeOrden, Estado FROM HojaDeRuta WHERE Seguimiento IN ($in) ORDER BY Seguimiento"),
];

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
