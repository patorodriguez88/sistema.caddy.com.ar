<?php
session_start();
require("../../../Conexion/Conexioni.php");
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

$query="SELECT id,ClienteDestino,DomicilioDestino,Recorrido,Debe,RazonSocial FROM TransClientes WHERE FechaEntrega=CURDATE() AND Eliminado='0'";
$result = $mysqli->query($query);

if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");

echo '<markers>';
while ($row = $result->fetch_array(MYSQLI_ASSOC)){
$sql=$mysqli->query("SELECT ColorSistema FROM Vehiculos INNER JOIN Logistica ON Vehiculos.Dominio=Logistica.Patente 
WHERE Fecha=CURDATE() AND Eliminado='0' AND Recorrido='$row[Recorrido]'");
$color=$sql->fetch_array(MYSQLI_ASSOC);
  
  $direccion=$row[DomicilioDestino];
  $localizar=$direccion;
  $type=$row['Entregado'];  
  $datosmapa = geolocalizar($localizar);
  $latitud = $datosmapa[0];
  $longitud = $datosmapa[1];
  $localizacion = $datosmapa[2];
  // Add to XML document node
  echo '<marker ';
  echo 'Origen="' . parseToXML($row['RazonSocial']) . '" ';
  echo 'Color="' . parseToXML($color['ColorSistema']) . '" ';
  echo 'Precio="' . parseToXML($row['Debe']) . '" ';
  echo 'name="' . parseToXML($row['ClienteDestino']) . '" ';
  echo 'address="' . parseToXML($direccion) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo 'recorrido="' . parseToXML($row['Recorrido']) . '" ';
  echo '/>';
}
// End XML file
echo '</markers>';
?>