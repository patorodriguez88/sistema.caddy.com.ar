<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$out = [];

function q($mysqli, $sql) {
    try {
        $res = $mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : ['__error' => $mysqli->error];
    } catch (\Throwable $e) {
        return ['__exception' => $e->getMessage()];
    }
}

// 1. Cliente Dynamic
$out['clientes_dynamic'] = q($mysqli, "SELECT id, nombrecliente FROM Clientes WHERE nombrecliente LIKE '%Dynamic%' AND Eliminado=0");

// 2. Colecta rows recientes para Recorrido 1472
$out['colecta_recorrido_1472'] = q($mysqli, "SELECT * FROM Colecta WHERE Recorrido=1472 AND Eliminado=0 ORDER BY id DESC LIMIT 20");

// 3. Colecta rows recientes para Dynamic (cualquier recorrido), últimos 3 días
$out['colecta_dynamic_recientes'] = q($mysqli, "
    SELECT C.* FROM Colecta C
    JOIN Clientes CL ON CL.id = C.idCliente
    WHERE CL.nombrecliente LIKE '%Dynamic%' AND C.Eliminado=0 AND C.Fecha >= CURDATE() - INTERVAL 3 DAY
    ORDER BY C.id DESC LIMIT 20
");

// 4. Logistica de hoy/recientes recorrido 1472
$out['logistica_1472'] = q($mysqli, "SELECT id, Fecha, Recorrido, Usuario, HoraSalidaReal, NumerodeOrden, Eliminado FROM Logistica WHERE Recorrido=1472 ORDER BY Fecha DESC LIMIT 5");

// 5. HojaDeRuta para recorrido 1472 (hoy) - a ver si el codigo de la colecta esta o no
$out['hdr_recorrido_1472'] = q($mysqli, "
    SELECT id, Fecha, Recorrido, Estado, NumerodeOrden, Posicion, Seguimiento, idCliente, idTransClientes, Eliminado
    FROM HojaDeRuta
    WHERE Recorrido=1472 AND Fecha >= CURDATE() - INTERVAL 1 DAY
    ORDER BY Posicion ASC
");

// 6. Buscar directamente el/los CodigoSeguimiento de la colecta de Dynamic dentro de HojaDeRuta/TransClientes/Seguimiento
$codigos = [];
foreach ((array)$out['colecta_dynamic_recientes'] as $row) {
    if (!empty($row['CodigoSeguimiento'])) $codigos[] = $row['CodigoSeguimiento'];
}
$out['codigos_colecta_dynamic'] = $codigos;
if ($codigos) {
    $in = "'" . implode("','", array_map([$mysqli, 'real_escape_string'], $codigos)) . "'";
    $out['hdr_por_codigo'] = q($mysqli, "SELECT id, Fecha, Recorrido, Estado, NumerodeOrden, Posicion, Seguimiento, Eliminado FROM HojaDeRuta WHERE Seguimiento IN ($in)");
    $out['transclientes_por_codigo'] = q($mysqli, "SELECT id, Fecha, idCliente, NumerodeOrden, CodigoDeSeguimiento, Recorrido, Eliminado FROM TransClientes WHERE CodigoDeSeguimiento IN ($in)");
    $out['seguimiento_por_codigo'] = q($mysqli, "SELECT id, Fecha, CodigoSeguimiento, Estado, Usuario, Recorrido, Eliminado FROM Seguimiento WHERE CodigoSeguimiento IN ($in) ORDER BY id ASC");
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
