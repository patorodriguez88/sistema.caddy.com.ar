<?php
ob_start();
session_start();
// require("../../phpsqlajax_dbinfo.php");
$username = "dinter6_prodrig";
$password = "pato@4986";
$database = "dinter6_triangularcopia";

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
$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$_SESSION['RecorridoAsignado']=$Dato[Recorrido];

$query="SELECT *,TransClientes.Observaciones FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' AND HojaDeRuta.Eliminado='0' ORDER BY HojaDeRuta.Posicion ASC";                      

$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}
header("Content-type: text/xml");

// Start XML file, echo parent node
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
$sqlHojaDeRuta=mysql_query("SELECT Fecha,Hora,Posicion FROM HojaDeRuta WHERE Seguimiento = '$row[CodigoSeguimiento]'");
$resultHojaDeRuta=mysql_fetch_array($sqlHojaDeRuta);
// $_SESSION[Localizacion]=$row[DomicilioDestino];
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
// $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
// $DatoNCliente=mysql_fetch_array($sqlBuscoidProveedor);  

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' ORDER BY id DESC");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento); 

  
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
  
// $sqlSeguimiento=mysql_query("SELECT Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
// $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  
$sqlNCliente=mysql_query("SELECT t1.idProveedor FROM Clientes t1, HojaDeRuta t2
WHERE t2.idCliente= t1.id AND t2.Seguimiento='$row[CodigoSeguimiento]'");
$DatoNCliente=mysql_fetch_array($sqlNCliente);
if($row[Retirado]==1){
$NombreCliente=utf8_encode($row['ClienteDestino']);  
$ciudad=$row['LocalidadDestino'];
$direccion=utf8_encode($row['DomicilioDestino'].",".$ciudad);
$provincia=$row['ProvinciaDestino'];
$pais='Argentina';
$localizar=$direccion.",".$ciudad;
}else{
$NombreCliente=utf8_encode($row[RazonSocial]);    
$ciudad=$row['LocalidadOrigen'];
$direccion=utf8_encode($row['DomicilioOrigen'].",".$ciudad);
$provincia=$row['ProvinciaOrigen'];
$pais='Argentina';
$localizar=$direccion.",".$ciudad;
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
  echo 'Posicion="' . parseToXML($resultHojaDeRuta[Posicion]) . '" ';
  echo 'Avisado="' . parseToXML($resultSeguimiento[Avisado]) . '" ';
  echo 'Observaciones="' . parseToXML($row[Observaciones]) . '" ';
  echo 'idTransClientes="' . parseToXML($row[id]) . '" ';
  echo 'Retirado="' . parseToXML($resultSeguimiento[Retirado]) . '" ';
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