<?php
ob_start();
// session_start();
// include("../ConexionBD.php");
require("../phpsqlajax_dbinfo.php");

function geolocalizar($direccion){
 
 
    // urlencode codifica datos de texto modificando simbolos como acentos
 
    $direccion = urlencode($direccion);
   
 
    // envio la consulta a Google map api
    $url = "https://maps.google.com/maps/api/geocode/json?address={$direccion}";
 
 
    // recibo la respuesta en formato Json
 
    $datosjson = file_get_contents($url);
    
 
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
 
 
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
 
    if($datosmapa['status']='OK'){
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
 
            // Guardamos los datos en una matriz
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

// $Recorrido=$_SESSION['Recorrido'];  
$Recorrido=$_SESSION['Recorrido'];  
// $Recorrido='1020';  

// Select all the rows in the markers table

$query ="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado=0";	

$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");

// Start XML file, echo parent node
echo '<markers>';
// $DireccionBuscada="Justiniano Posse 1236,Cordoba, Cordoba, Argentina";

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
$Calle=$row['Localizacion'];
$ciudad=$row['Ciudad'];
$provincia=$row['Provincia'];
$pais='Argentina';
// $direccion=$Calle.", ".$ciudad.", ".$pais;
$DireccionBuscada=$Calle.",".$ciudad.",".$provincia.",".$pais;
  
$datosmapa = geolocalizar($DireccionBuscada);
if($datosmapa){
        $latitud = $datosmapa[0];
        $longitud = $datosmapa[1];
        $localizacion = $datosmapa[2];
}
 
  // Add to XML document node
  echo '<marker ';
  echo 'name="' . parseToXML($row['Cliente']) . '" ';
  echo 'address="' . parseToXML($row['Localizacion']) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';

//   echo 'type="' . parseToXML($row['LocalidadDestino']) . '" ';
  echo '/>';
}
// End XML file
echo '</markers>';
ob_end_flush();
?>