<?php
ob_start();
session_start();
require("../phpsqlajax_dbinfo.php");
// mysql_set_charset("utf8"); 

function geolocalizar($direccion){
    // urlencode codifica datos de texto modificando simbolos como acentos
    $direccion = urlencode($direccion);
    // envio la consulta a Google map api
    $url= "https://maps.google.com/maps/api/geocode/json?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&address={$direccion}";
//     $url = "http://maps.google.com/maps/api/geocode/json?address={$direccion}";
    // recibo la respuesta en formato Json
    $datosjson = file_get_contents($url);
//     print "datos:".$datosjson;
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    if($datosmapa['status']==='OK'){
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
           // Guardamos los datos en una matriz
//   print "lati:".$latitud;
            $datosmapa = array();           
                array_push(
                $datosmapa,
                $latitud,
                $longitud,
                $localizacion
                );
            return $datosmapa;
        }else{
    alert('Geocode was not successful for the following reason: ' + $datomapa['status']);

    }
} 

function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}

// Opens a connection to a MySQL server
$connection=mysql_connect ('localhost', $username, $password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// // Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}
// Select all the rows in the markers table
// $query="SELECT * FROM TransClientes WHERE Entregado='0' AND Recorrido='".$_SESSION['Recorrido']."' AND Eliminado=0";
$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$_SESSION[Recorrido]' AND Estado='Abierto' AND Eliminado='0' AND Devuelto='0' AND Seguimiento<>'' ORDER BY Posicion ASC";	

$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml;charset=UTF-8");

// Start XML file, echo parent node
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
$sqltransclientes=mysql_query("SELECT RazonSocial,ClienteDestino,DomicilioOrigen,LocalidadOrigen,Retirado,IngBrutosOrigen,idClienteDestino FROM TransClientes WHERE CodigoSeguimiento='$row[Seguimiento]' AND Eliminado=0 ");	
$dato=mysql_fetch_array($sqltransclientes);  

if($dato['Retirado']==1){
$ciudad=$row['Ciudad'];
$direccion=utf8_encode($row['Localizacion'].','.$ciudad);
// $direccion=utf8_encode($row['Localizacion']);
$pais='Argentina';
$idCliente=$dato[idClienteDestino];  
}else{
$sql = mysql_query("SELECT count(id)as repe FROM TransClientes WHERE RazonSocial like '%$dato[RazonSocial]%' AND Eliminado=0 AND Retirado = 0 AND Entregado= 0 AND Recorrido='$_SESSION[Recorrido]' ");  
$resultado=mysql_fetch_array($sql);
$ciudad=$dato['LocalidadOrigen'];
$direccion=utf8_encode($dato['DomicilioOrigen'].','.$ciudad);
// $direccion=$dato['DomicilioOrigen'];
$idCliente=$dato[IngBrutosOrigen];  
$pais='Argentina';
}  
$sqllatlong=mysql_query("SELECT Latitud,Longitud FROM Clientes WHERE id='$idCliente'");
$datosqllatlong=mysql_fetch_array($sqllatlong);
if($datosqllatlong[Latitud]==''){
$localizar=$direccion;
$datosmapa = geolocalizar($direccion);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];  
}else{
$latitud=$datosqllatlong[Latitud];
$longitud=$datosqllatlong[Longitud];
$localizacion=$direccion;  
}
// $localizar=$direccion;
$type=$row['Estado'];
  
// $datosmapa = geolocalizar($direccion);
// $latitud = $datosmapa[0];
// $longitud = $datosmapa[1];
// $localizacion = $datosmapa[2];
$sqlid=mysql_query("SELECT idProveedor FROM Clientes WHERE id='$row[idCliente]'");
$rowidcliente=@mysql_fetch_array($sqlid);  
  // Add to XML document node

  echo '<marker ';
  echo 'idhdr="' . parseToXML($row['id']) . '" ';  
  echo 'resultado="' . parseToXML($resultado['repe']) . '" ';  
  echo 'posicion="' . parseToXML($row['Posicion']) . '" ';  
  echo 'retiro="' . parseToXML($dato['Retirado']) . '" ';  
  echo 'clienteorigen="' . parseToXML(utf8_encode($dato['RazonSocial'])) . '" ';  
  echo 'clientedestino="' . parseToXML(utf8_encode($dato['ClienteDestino'])) . '" ';  
  echo 'ncliente="' . parseToXML($rowidcliente['idProveedor']) . '" ';  
  echo 'name="' . parseToXML(utf8_encode($row['Cliente'])) . '" ';
  echo 'address="' . parseToXML($direccion) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo 'type="' . parseToXML($row['Estado']) . '" ';
  echo 'CodigoSeguimiento="' . parseToXML($row['Seguimiento']) . '" ';
  echo '/>';

}
// End XML file
echo '</markers>';
ob_end_flush();
?>