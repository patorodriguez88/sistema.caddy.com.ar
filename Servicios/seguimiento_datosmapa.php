<?php
ob_start();
session_start();
require("../phpsqlajax_dbinfo.php");

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
$Fecha=date('2020-04-03');
// Select all the rows in the markers table
$query="SELECT * FROM `gps` WHERE id IN(SELECT MAX(id)as id FROM gps WHERE Fecha='$Fecha' and latitud<>'' and longitud<>''GROUP BY Usuario)";  
$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");
// Start XML file, echo parent node
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
// $sqlSeguimiento=mysql_query("SELECT * FROM `gps` WHERE id IN(SELECT MAX(id)as id FROM gps WHERE Fecha='2020-03-29' and latitud<>'' and longitud<>''GROUP BY Usuario)");  
// $sqlSeguimiento=mysql_query("SELECT latitud,longitud FROM gps WHERE id=(SELECT MAX(id)FROM gps WHERE Fecha=CodigoSeguimiento='$row[CodigoSeguimiento]')");
// $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
$latitud= $row[latitud];
$longitud = $row[longitud];
  
$sqlNCliente=mysql_query("SELECT t1.idProveedor FROM Clientes t1, HojaDeRuta t2
WHERE t2.idCliente= t1.id AND t2.Seguimiento='$row[CodigoSeguimiento]'");
$DatoNCliente=mysql_fetch_array($sqlNCliente);

$sqlidChofer=mysql_query("SELECT id FROM usuarios WHERE Usuario='$row[Usuario]'");
$datoidchofer=mysql_fetch_array($sqlidChofer);  
  
$sqlVehiculo=mysql_query("SELECT Patente FROM Logistica WHERE Fecha='$row[Fecha]' AND idUsuarioChofer='$datoidchofer[id]'");
$DatoVehiculo=mysql_fetch_array($sqlVehiculo);
  
// $ciudad=$row['LocalidadDestino'];
// $direccion=$row['DomicilioDestino'].",".$ciudad;
// $provincia=$row['ProvinciaDestino'];
// $pais='Argentina';
// $localizar=$direccion.",".$ciudad;
// $type=$resultSeguimiento['Entregado'];  
// $datosmapa = geolocalizar($localizar);
// $latitud = $datosmapa[0];
// $longitud = $datosmapa[1];
// $localizacion = $datosmapa[2];

// if($resultSeguimiento[Entregado]==1){
$HoraEntrega=$row[Hora];
// }else{
// $HoraEntrega='';
// }
  
  // Add to XML document node
  echo '<marker ';
  echo 'idProveedor="' . parseToXML($DatoNCliente[idProveedor]) . '" ';
  echo 'name="' . parseToXML($row['Usuario']) . '" ';
  echo 'vehiculo="' . parseToXML($DatoVehiculo['Patente']) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
//   echo 'type="' . parseToXML($resultSeguimiento['Entregado']) . '" ';
  echo 'hora="' . parseToXML($HoraEntrega) . '" ';  
  echo '/>';


}

// End XML file
echo '</markers>';

ob_end_flush();
?>