<?php
ob_start();
session_start();
require("../phpsqlajax_dbinfo.php");

$CodigoSeguimiento=$_SESSION[cod];

function geolocalizar($direccion){
    $direccion = urlencode($direccion);
    $url= "https://maps.google.com/maps/api/geocode/json?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&address={$direccion}";
    $datosjson = file_get_contents($url);
    $datosmapa = json_decode($datosjson, true);
 
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

$connection=mysql_connect ('localhost', $username, $password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// // Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}
$CodigoSeguimiento=$_SESSION['cod'];
$query="SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')";

$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
$row = @mysql_fetch_assoc($result);
header("Content-type: text/xml");
echo '<markers>';

if($row[Estado]=='En Transito'){
//BUSCO EL RECORRIDO  
$queryrecorrido=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$datorecorrido= @mysql_fetch_assoc($queryrecorrido);
$Recorrido=$datorecorrido[Recorrido];
//BUSCO EL FLETERO
$queryfletero=mysql_query("SELECT * FROM Logistica WHERE Recorrido='$Recorrido' AND Estado='Cargada' AND Eliminado='0'");
$datofletero=mysql_fetch_array($queryfletero);
$idFletero=$datofletero[idUsuarioChofer];
$queryusuario=mysql_query("SELECT Usuario FROM usuarios WHERE id='$datofletero[idUsuarioChofer]'");
$datousuario=mysql_fetch_array($queryusuario);
$Fletero=$datousuario[Usuario];  

$querygps=mysql_query("SELECT * FROM gps WHERE id=(SELECT MAX(id) FROM gps WHERE Usuario='$datousuario[Usuario]')");
$datogps=mysql_fetch_array($querygps);
$sql=mysql_query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
if($datorec=mysql_fetch_array($sql)){
  $hoy=date('Y-m-d');
  $sqllog=mysql_query("SELECT * FROM HojaDeRuta WHERE Fecha='$hoy' AND Estado='Abierto' AND Recorrido='$datorec[Recorrido]' ORDER BY Posicion ASC");   
  $datosqllog=mysql_fetch_array($sqllog);
  if($datosqllog[Seguimiento]==$CodigoSeguimiento){
    $sqlvehi=mysql_query("SELECT Patente FROM Logistica WHERE Estado='Cargada' AND Eliminado='0' AND Recorrido='$datorec[Recorrido]'");
    $datovehi=mysql_fetch_array($sqlvehi);
    $move=$datovehi[Patente]; 
    }else{
    $latitud = $datogps[latitud];
    $longitud = $datogps[longitud];
    $move=0;
    }
}
  echo '<marker ';
  echo 'vehi="' . parseToXML($move) . '" ';
  echo 'name="' . parseToXML($CodigoSeguimiento) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo '/>';
  
}elseif($row[Estado]=='En Origen'){
  
$queryorigen=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$datoorigen= @mysql_fetch_assoc($queryorigen);
$ciudad=$datoorigen['LocalidadDestino'];
$direccion=$datoorigen['DomicilioDestino'];
$localizar=$datoorigen['DomicilioDestino'];
$datosmapa = geolocalizar($localizar);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];
  
  // Add to XML document node
  echo '<marker ';
  echo 'name="' . parseToXML($CodigoSeguimiento) . '" ';
  echo 'address="' . parseToXML($direccion) . '" ';
  echo 'lat="' . parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo '/>';
}
echo '</markers>';
ob_end_flush();
?>