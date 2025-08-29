<?php
ob_start();
session_start();
require("../phpsqlajax_dbinfo.php");
// $username="dinter6_prodrig";
// $password="pato@4986";
// $database="dinter6_triangularcopia";

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
// $CodigoSeguimiento='FGW1J68Z5';
$CodigoSeguimiento=$_SESSION['cod'];
// if($CodigoSeguimiento==''){
//   header('location:Pendientes.php');
// }
// if($_SESSION[recorridomapa]=='Todos'){
// $query="SELECT * FROM TransClientes WHERE FechaEntrega='".$_SESSION['fechamapa']."' AND Eliminado=0";
// }else{
// $query="SELECT * FROM TransClientes WHERE FechaEntrega='".$_SESSION['fechamapa']."' AND Recorrido='".$_SESSION['recorridomapa']."' AND Eliminado=0";

$query="SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')";

// }
$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");

// Start XML file, echo parent node

// Iterate through the rows, printing XML nodes for each
$row = @mysql_fetch_assoc($result);
echo '<markers>';

if($row[Estado]=='En Transito'){
//BUSCO EL RECORRIDO  
$queryrecorrido=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$datorecorrido= @mysql_fetch_assoc($queryrecorrido);
$Recorrido=$datorecorrido[Recorrido];
//   print $Recorrido;
//BUSCO EL FLETERO
$queryfletero=mysql_query("SELECT * FROM Logistica WHERE Recorrido='$Recorrido' AND Estado='Cargada' AND Eliminado='0'");
$datofletero=mysql_fetch_array($queryfletero);
$idFletero=$datofletero[idUsuarioChofer];
// print  $idFletero;
$queryusuario=mysql_query("SELECT Usuario FROM usuarios WHERE id='$datofletero[idUsuarioChofer]'");
$datousuario=mysql_fetch_array($queryusuario);
$Fletero=$datousuario[Usuario];  
// print $Fletero;
$querygps=mysql_query("SELECT * FROM gps WHERE id=(SELECT MAX(id) FROM gps WHERE Usuario='$datousuario[Usuario]')");
$datogps=mysql_fetch_array($querygps);

// print $datogps[latitud];
// echo '<markers>';
$latitud = $datogps[latitud];
$longitud = $datogps[longitud];
  echo '<marker ';
  echo 'name="' . parseToXML($CodigoSeguimiento) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
  echo '/>';
  
}elseif($row[Estado]=='EnOrigen'){
  
// $CodigoSeguimiento=$_GET['cod'];
$queryorigen=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$datoorigen= @mysql_fetch_assoc($queryorigen);

//   echo '<markers>';

// $sqlSeguimiento=mysql_query("SELECT Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");
// $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  
// $sqlNCliente=mysql_query("SELECT t1.idProveedor FROM Clientes t1, HojaDeRuta t2
// WHERE t2.idCliente= t1.id AND t2.Seguimiento='$CodigoSeguimiento'");
// $DatoNCliente=mysql_fetch_array($sqlNCliente);
  
$ciudad=$datoorigen['LocalidadOrigen'];
$direccion=$datoorigen['DomicilioOrigen'].",".$ciudad;
// $ciudad='Cordoba';
//   $direccion='Av. Ciudad de Valparaiso 1100';
// $provincia=$row['ProvinciaDestino'];
// $pais='Argentina';
$localizar=$direccion.",".$ciudad;
// $type=$resultSeguimiento['Entregado'];  
$datosmapa = geolocalizar($localizar);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];
// }

// if($resultSeguimiento[Entregado]==1){
// $HoraEntrega=$resultSeguimiento[Hora];
// }else{
// $HoraEntrega='';
// }
  // Add to XML document node
  echo '<marker ';
//   echo 'idProveedor="' . parseToXML($DatoNCliente[idProveedor]) . '" ';
  echo 'name="' . parseToXML($CodigoSeguimiento) . '" ';
  echo 'address="' . parseToXML($direccion) . '" ';
  echo 'lat="' .  parseToXML($latitud) . '" ';
  echo 'lng="' . parseToXML($longitud) . '" ';
//   echo 'type="' . parseToXML($resultSeguimiento['Entregado']) . '" ';
//   echo 'hora="' . parseToXML($HoraEntrega) . '" ';  
  echo '/>';
}
// End XML file
echo '</markers>';
ob_end_flush();
?>