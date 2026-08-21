<?php

require_once('../../../Conexion/Conexioni.php');

// Antes dependia solo de $_SESSION['rec'], escrito por la accion Buscar de
// zonas.php en un request POST separado - como el frontend dispara ambos
// requests casi en simultaneo (sin encadenarlos), esta pantalla podia leer
// la sesion vacia o con los Recorridos de la zona vista anteriormente segun
// cual de los dos requests llegaba primero al servidor (race condition: los
// waypoints no aparecian, o aparecian los de otra zona). Ahora recibe los
// Recorridos directo por POST, sin pasar por sesion; se mantiene el fallback
// a sesion por compatibilidad con Mapas/js/zonas.poly*.js (variantes viejas
// no cargadas hoy, que si llaman a este archivo sin mandar "rec").
$recPost = $_POST['rec'] ?? null;
if (is_array($recPost)) {
    $rec = implode(',', array_map('intval', $recPost));
} elseif ($recPost !== null && $recPost !== '') {
    $rec = preg_replace('/[^0-9,]/', '', (string)$recPost);
} else {
    $rec = $_SESSION['rec'] ?? '';
}
if ($rec === '') {
    echo json_encode(['data' => []]);
    exit;
}

$query = "SELECT nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2
    from Clientes INNER JOIN HojaDeRuta
    ON Clientes.id = HojaDeRuta.idCliente
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND HojaDeRuta.Devuelto=0 AND HojaDeRuta.Recorrido IN($rec)";
$result = $mysqli->query($query);
$i = 0;
$rows = $result->num_rows;
$rowss = array();
$co = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $queryr = "SELECT Color FROM Recorridos WHERE Numero='$row[Recorrido]'";
    $resultR = $mysqli->query($queryr);
    $rowR = $resultR->fetch_array(MYSQLI_ASSOC);
    $co[] = $rowR['Color'];
    $rowss[] = $row;
}
echo json_encode(array('data' => $rowss, $co));
