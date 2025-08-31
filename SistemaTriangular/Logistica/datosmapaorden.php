<?php
ob_start();
session_start();
// require("../../phpsqlajax_dbinfo.php");
$username = "dinter6_prodrig";
$password = "pato@4986";
$database = "dinter6_triangular";

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
// Select all the rows in the markers table
$_SESSION['RecorridoAsignado']=$_SESSION[Recorrido];

$query="SELECT id,RazonSocial,ClienteDestino,DomicilioDestino,LocalidadDestino,ProvinciaDestino,Observaciones,LocalidadOrigen,
DomicilioOrigen,ProvinciaOrigen,CodigoSeguimiento,Recorrido,Retirado 
FROM `TransClientes` 
WHERE Eliminado='0' AND Entregado='0' AND Haber='0'";                      

$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");

// Start XML file, echo parent node
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
$sqlHojaDeRuta=mysql_query("SELECT * FROM HojaDeRuta WHERE Seguimiento = '$row[CodigoSeguimiento]' AND Eliminado=0");
$resultHojaDeRuta=mysql_fetch_array($sqlHojaDeRuta);
  
$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE 
id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento); 

$sqlvehiculo=mysql_query("SELECT Vehiculos.ColorSistema FROM Logistica,Vehiculos 
WHERE Logistica.Patente=Vehiculos.Dominio 
AND Logistica.Estado IN('Cargada','Alta')
AND Logistica.Recorrido='$row[Recorrido]'
AND Logistica.Eliminado='0'");  
$datosqlvehiculo=mysql_fetch_array($sqlvehiculo);
  
if($resultSeguimiento[Estado]=='No se pudo entregar'){
$ColorBoton='red';
}else{
$ColorBoton='#145A32';  
}  
if($idProveedor[idProveedor]==''){
$Titulo="";  
}else{
$Titulo = "(".$idProveedor[idProveedor].")";	  
}  
  
$sqlNCliente=mysql_query("SELECT t1.idProveedor FROM Clientes t1, HojaDeRuta t2
WHERE t2.idCliente= t1.id 
AND t2.Seguimiento='$row[CodigoSeguimiento]'");
$DatoNCliente=mysql_fetch_array($sqlNCliente);

if($row[Retirado]==1){
$NombreCliente=utf8_encode($row['ClienteDestino']);  
$ciudad=$row['LocalidadDestino'];
$direccion=$row['DomicilioDestino'];
$provincia=$row['ProvinciaDestino'];
$pais='Argentina';
$localizar=utf8_encode($direccion.','.$ciudad);
// $localizar=utf8_encode($direccion);

}else{
$NombreCliente=utf8_encode($row['RazonSocial']);    
$ciudad=$row['LocalidadOrigen'];
// $direccion=utf8_encode($row['DomicilioOrigen']);
$direccion=$row['DomicilioOrigen'];  
$provincia=$row['ProvinciaOrigen'];
$pais='Argentina';
$localizar=utf8_encode($direccion.','.$ciudad);
// $localizar=utf8_encode($direccion);

}
$Observaciones=utf8_encode($row[Observaciones]);
$type=$resultSeguimiento['Entregado'];  
  
$datosmapa = geolocalizar($localizar);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];

if($resultSeguimiento[Entregado]==1){
$HoraEntrega=$resultSeguimiento[Hora];
}else{
$HoraEntrega='';
}
  // Add to XML document node
  echo '<marker ';
  echo 'Recorrido="' . parseToXML($row[Recorrido]) . '" ';
  echo 'Color="' . parseToXML($datosqlvehiculo[ColorSistema]) . '" ';
  echo 'Posicion="' . parseToXML($resultHojaDeRuta[Posicion]) . '" ';
  echo 'Avisado="' . parseToXML($resultSeguimiento[Avisado]) . '" ';
  echo 'Observaciones="' . parseToXML($row[Observaciones]) . '" ';
  echo 'idTransClientes="' . parseToXML($row[id]) . '" ';
  echo 'Retirado="' . parseToXML($row[Retirado]) . '" ';//ver RETIRADO DESDE SEGUIMIENTO
  echo 'Entregado="' . parseToXML($resultSeguimiento[Entregado]) . '" ';
  echo 'CodigoSeguimiento="' . parseToXML($row[CodigoSeguimiento]) . '" ';
  echo 'Estado="' . parseToXML($resultSeguimiento[Estado]) . '" ';
  echo 'idProveedor="' . parseToXML($DatoNCliente[idProveedor]) . '" ';
  echo 'name="' . parseToXML($NombreCliente) . '" ';
  echo 'address="' . parseToXML($direccion) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo 'type="' . parseToXML($resultSeguimiento['Entregado']) . '" ';
  echo 'hora="' . parseToXML($HoraEntrega) . '" ';  
  echo '/>';
}
// End XML file
echo '</markers>';
ob_end_flush();
?>