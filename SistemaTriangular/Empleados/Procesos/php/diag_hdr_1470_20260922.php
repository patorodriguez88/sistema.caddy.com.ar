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

$out['clientes_venex'] = q($mysqli, "SELECT id, nombrecliente FROM Clientes WHERE nombrecliente LIKE '%VENEX%' AND Eliminado=0");
$out['colecta_1470_hoy'] = q($mysqli, "SELECT * FROM Colecta WHERE Recorrido=1470 AND Fecha=CURDATE() AND Eliminado=0 ORDER BY id DESC");
$out['colecta_venex_recientes'] = q($mysqli, "
    SELECT C.* FROM Colecta C
    JOIN Clientes CL ON CL.id = C.idCliente
    WHERE CL.nombrecliente LIKE '%VENEX%' AND C.Eliminado=0 AND C.Fecha >= CURDATE() - INTERVAL 1 DAY
    ORDER BY C.id DESC LIMIT 10
");
$codigosVenex = [];
foreach ((array)$out['colecta_venex_recientes'] as $row) {
    if (!empty($row['CodigoSeguimiento'])) $codigosVenex[] = $row['CodigoSeguimiento'];
}
if ($codigosVenex) {
    $in = "'" . implode("','", array_map([$mysqli, 'real_escape_string'], $codigosVenex)) . "'";
    $out['transclientes_venex'] = q($mysqli, "SELECT * FROM TransClientes WHERE CodigoSeguimiento IN ($in)");
    $out['seguimiento_venex'] = q($mysqli, "SELECT id, Fecha, CodigoSeguimiento, Estado, Usuario, Recorrido, Eliminado FROM Seguimiento WHERE CodigoSeguimiento IN ($in)");
    $out['hdr_venex'] = q($mysqli, "SELECT id, Fecha, Recorrido, Estado, Seguimiento, Eliminado FROM HojaDeRuta WHERE Seguimiento IN ($in)");
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
