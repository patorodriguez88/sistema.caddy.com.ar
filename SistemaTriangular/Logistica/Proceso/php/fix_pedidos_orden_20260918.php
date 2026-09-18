<?php
// Script temporal ÚNICO (2026-09-18) - se borra apenas corre bien.
// Tarea Asana "Modificación de pedidos por sistema" (Quinteros Micaela).
//
// GRUPO 1 (7 códigos): se reimportaron hoy porque no se encontraban en el
// sistema. Quedaron con Fecha correcta (01/09) pero colgados de la orden
// 17137 (la hoja de ruta actualmente abierta del recorrido 1372), cuando en
// realidad son parte de la orden 17004 (la hoja de ruta real del 01/09,
// cerrada ese día, mismo recorrido). Se corrige NumerodeOrden 17137->17004
// en TransClientes, Seguimiento (solo la fila "Entregado al Cliente") y
// HojaDeRuta. Además se eliminan (Eliminado=1) las filas duplicadas "En
// Origen" que dejó la reimportación (ya están entregadas, esa fila sobra).
//
// GRUPO 2 (8 códigos): fueron retirados/entregados en la realidad el 01/09,
// pero por demora de sincronización quedaron cargados con fecha de retiro
// 09/09 y entrega 15/09. La oficina (jtorti) ya había empezado a corregir 3
// de los 8 a mano (agregando una fila nueva "Entregado al Cliente" y
// eliminando la del 15/09) - Micaela confirmó que esa corrección fue
// intencional. De esos 3, 2 quedaron con fecha 09/09 en vez de 01/09
// (F2JKCPXJD, IG94RIEKO) - se corrigen. Para los 5 que nunca se tocaron, se
// corrige directamente la Fecha de su fila activa "Entregado al Cliente" a
// 01/09 (no se fabrica una hora real que no tenemos). En los 8 casos se
// corrige además TransClientes.FechaEntrega (quedaba en 15/09, la fecha en
// que se cerró la orden 17130, no la fecha real de entrega).

define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;

$resultado = ['dry_run' => $dry, 'grupo1' => [], 'grupo2' => []];

function filas($mysqli, $sql) {
    $r = $mysqli->query($sql);
    $out = [];
    if ($r) { while ($row = $r->fetch_assoc()) $out[] = $row; }
    return $out;
}

// ===================== GRUPO 1 =====================
$grupo1TC = [333871, 333869, 333866, 333872, 333868, 333867, 333870];
$grupo1SegEntregado = [968663, 968673, 968676, 968652, 968675, 968667, 968674];
$grupo1SegDuplicado = [968641, 968639, 968636, 968642, 968638, 968637, 968640];
$grupo1HDR = [324177, 324175, 324172, 324178, 324174, 324173, 324176];

$inTC = implode(',', $grupo1TC);
$inSegE = implode(',', $grupo1SegEntregado);
$inSegD = implode(',', $grupo1SegDuplicado);
$inHDR = implode(',', $grupo1HDR);

$resultado['grupo1']['antes'] = [
    'TransClientes' => filas($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden FROM TransClientes WHERE id IN ($inTC)"),
    'Seguimiento_entregado' => filas($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSegE)"),
    'Seguimiento_duplicado' => filas($mysqli, "SELECT id, CodigoSeguimiento, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSegD)"),
    'HojaDeRuta' => filas($mysqli, "SELECT id, Seguimiento, NumerodeOrden FROM HojaDeRuta WHERE id IN ($inHDR)"),
];

if (!$dry) {
    $mysqli->query("UPDATE TransClientes SET NumerodeOrden=17004 WHERE id IN ($inTC) AND NumerodeOrden=17137");
    $resultado['grupo1']['aplicado']['TransClientes'] = $mysqli->affected_rows;

    $mysqli->query("UPDATE Seguimiento SET NumerodeOrden=17004 WHERE id IN ($inSegE) AND NumerodeOrden=17137");
    $resultado['grupo1']['aplicado']['Seguimiento_entregado'] = $mysqli->affected_rows;

    $mysqli->query("UPDATE HojaDeRuta SET NumerodeOrden=17004 WHERE id IN ($inHDR) AND NumerodeOrden=17137");
    $resultado['grupo1']['aplicado']['HojaDeRuta'] = $mysqli->affected_rows;

    $infoElim = $mysqli->real_escape_string('sistema (fix tarea Asana - duplicado de reimportación del ' . date('d-m-Y'));
    $mysqli->query("UPDATE Seguimiento SET Eliminado=1, Eliminado_user='sistema-fix-asana', Eliminado_date=NOW() WHERE id IN ($inSegD) AND Eliminado=0");
    $resultado['grupo1']['aplicado']['Seguimiento_duplicado_eliminado'] = $mysqli->affected_rows;
}

$resultado['grupo1']['despues'] = [
    'TransClientes' => filas($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden FROM TransClientes WHERE id IN ($inTC)"),
    'Seguimiento_entregado' => filas($mysqli, "SELECT id, CodigoSeguimiento, NumerodeOrden, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSegE)"),
    'Seguimiento_duplicado' => filas($mysqli, "SELECT id, CodigoSeguimiento, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSegD)"),
    'HojaDeRuta' => filas($mysqli, "SELECT id, Seguimiento, NumerodeOrden FROM HojaDeRuta WHERE id IN ($inHDR)"),
];

// ===================== GRUPO 2 =====================
// Filas de Seguimiento "Entregado al Cliente" a corregir a Fecha=2026-09-01
$grupo2SegFecha = [
    968991 => 'F2JKCPXJD', // tenía 09/09, corregir a 01/09
    968989 => 'IG94RIEKO', // tenía 09/09, corregir a 01/09
    966181 => 'D3DR4BTXK', // nunca corregido, tenía 15/09
    966253 => 'MQRFL78NZ',
    966261 => 'OD5S2SJG5',
    966301 => 'T0A0O4E04',
    966282 => 'ZKI8ZYJRK',
];
$inSeg2 = implode(',', array_keys($grupo2SegFecha));

$grupo2TC = [
    331023 => 'CXBIWF5R3',
    330871 => 'D3DR4BTXK',
    330864 => 'F2JKCPXJD',
    330863 => 'IG94RIEKO',
    330868 => 'MQRFL78NZ',
    330866 => 'OD5S2SJG5',
    330870 => 'T0A0O4E04',
    330867 => 'ZKI8ZYJRK',
];
$inTC2 = implode(',', array_keys($grupo2TC));

$resultado['grupo2']['antes'] = [
    'Seguimiento' => filas($mysqli, "SELECT id, CodigoSeguimiento, Fecha, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSeg2)"),
    'TransClientes' => filas($mysqli, "SELECT id, CodigoSeguimiento, FechaEntrega FROM TransClientes WHERE id IN ($inTC2)"),
];

if (!$dry) {
    $mysqli->query("UPDATE Seguimiento SET Fecha='2026-09-01' WHERE id IN ($inSeg2) AND Eliminado=0");
    $resultado['grupo2']['aplicado']['Seguimiento_fecha'] = $mysqli->affected_rows;

    $mysqli->query("UPDATE TransClientes SET FechaEntrega='2026-09-01' WHERE id IN ($inTC2)");
    $resultado['grupo2']['aplicado']['TransClientes_fechaentrega'] = $mysqli->affected_rows;
}

$resultado['grupo2']['despues'] = [
    'Seguimiento' => filas($mysqli, "SELECT id, CodigoSeguimiento, Fecha, Estado, Eliminado FROM Seguimiento WHERE id IN ($inSeg2)"),
    'TransClientes' => filas($mysqli, "SELECT id, CodigoSeguimiento, FechaEntrega FROM TransClientes WHERE id IN ($inTC2)"),
];

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
