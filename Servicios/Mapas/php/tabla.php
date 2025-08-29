<?
ob_start();
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
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
$Codigo = "triangular";//ingresar el codigo a consultar
$token = "logistica2"; // Ingresar el token 
$Datos="DATOSACTUALES";
// $Vehiculo="AC850YY";
$Tipo="patente";
$urlPush = 'http://infoweb.gestya.com/Api/WServiceDev.jss';
// $Data = "user=".$Codigo."&pwd=".$token."&action=".$Datos."&tipoID=".$Tipo."&vehiculos='AA523RS'";
$Data = 'user='.$Codigo.'&pwd='.$token.'&action='.$Datos;
// $Data=$_POST['dato'];
$cURL = curl_init();
curl_setopt($cURL, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=utf-8"));
curl_setopt($cURL, CURLOPT_URL, $urlPush);
curl_setopt($cURL, CURLOPT_POST, true);
curl_setopt($cURL, CURLOPT_POSTFIELDS, $Data);
curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($cURL, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);  
curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);  
curl_setopt($cURL, CURLOPT_FRESH_CONNECT, true);   
curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
// Timeout super fast once connected, so it goes into async.
curl_setopt($cURL, CURLOPT_NOSIGNAL, 1);
curl_setopt($cURL, CURLOPT_TIMEOUT_MS, 3000);
$result = curl_exec($cURL);
curl_close($cURL);
$json=json_decode($result);
$Dominio=$json[0]->patente;

// print $_SESSION['RecorridoMapa'];

$sql=mysql_query("SELECT Patente,NombreChofer FROM Logistica WHERE Recorrido='".$_SESSION['RecorridoMapa']."' AND Estado IN('Cargada','Alta') AND Eliminado='0'");
$Dato=mysql_fetch_array($sql);
$NombreChofer=$Dato[NombreChofer];
$Dominiopost=$Dato['Patente'];
// $Dominiopost="AB235TM";

$sqlVehiculo=mysql_query("SELECT * FROM Vehiculos WHERE Dominio='$Dominiopost'");	
$DatoVehiculo=mysql_fetch_array($sqlVehiculo);
$Color='#'.$DatoVehiculo[ColorSistema];

foreach($json as $obj){
  if($Dominiopost==$obj->patente){
  $latitudv = $obj->latitud;
  $longitudv = $obj->longitud;
  }
}
$type=1;
// header("Content-type: text/xml");
// echo '<markers>';
// if($_SESSION[NO]<>''){
// $query=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$_SESSION[RecorridoMapa]' AND NumerodeOrden='$_SESSION[NO]' AND Eliminado='0' ORDER BY Posicion ASC");	
// }else{
// $query=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$_SESSION[RecorridoMapa]' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion ASC");	  
// }
$sqltransclientes=mysql_query("SELECT * FROM TransClientes WHERE Eliminado='0' AND FechaEntrega='2021-06-24'");	
// $dato=mysql_fetch_array($sqltransclientes);
// $latitud=array();
// $longitud=array();
$rows=array();
while ($dato = mysql_fetch_array($sqltransclientes)){
$sqlseguimiento=mysql_query("SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id)as id FROM Seguimiento WHERE CodigoSeguimiento='$dato[CodigoSeguimiento]')");
$Entregado=mysql_fetch_array($sqlseguimiento);  
$sqlorden=mysql_query("SELECT Posicion FROM HojaDeRuta WHERE Seguimiento='$dato[CodigoSeguimiento]' Eliminado='0'");	
$row=mysql_fetch_array($sqlorden);
$rows[]=$dato;
  if($dato[Retirado]==1){
  $NombreCliente=$dato[RazonSocial];  
  $localizar=$dato[DomicilioDestino];    
  }else{
  $NombreCliente=$datop[ClienteDestino];  
  $localizar=$dato[DomicilioOrigen];    
  }
  
// $type=$row['Estado'];
  
$datosmapa = geolocalizar($localizar);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];
$sqlid=mysql_query("SELECT idProveedor FROM Clientes WHERE id='$dato[idClienteDestino]'");
$rowidcliente=@mysql_fetch_array($sqlid);  
  // Add to XML document node

//   echo '<marker ';
// //   echo 'conductor="' . parseToXML($Dato['NombreChofer']) . '" ';
//   echo 'dominio="' .  $Dominiopost . '" ';
//   echo 'marca="' .  $DatoVehiculo[Marca] .' '. $DatoVehiculo[Modelo] . '" ';
//   echo 'color="' .  parseToXML($Color) . '" ';
//   echo 'latv="' . parseToXML($latitudv) . '" ';
//   echo 'lngv="' . parseToXML($longitudv) . '" ';
// //   echo 'idhdr="' . parseToXML($row['id']) . '" ';  
//   echo 'resultado="' . parseToXML($resultado['repe']) . '" ';  
//   echo 'posicion="' . parseToXML($row['Posicion']) . '" ';  
//   echo 'retiro="' . parseToXML($dato['Retirado']) . '" ';  
//   echo 'clienteorigen="' . parseToXML(utf8_encode($dato['RazonSocial'])) . '" ';  
//   echo 'clientedestino="' . parseToXML(utf8_encode($dato['ClienteDestino'])) . '" ';  
//   echo 'ncliente="' . parseToXML($rowidcliente['idProveedor']) . '" ';  
//   echo 'name="' . parseToXML($NombreCliente) . '" ';
//   echo 'address="' . parseToXML($direccion) . '" ';
//   echo 'lat="' .  parseToXML($latitud) . '" ';
//   echo 'lng="' . parseToXML($longitud) . '" ';
//   echo 'type="' . parseToXML($Entregado['Estado']) . '" ';
//   echo 'estado="' . parseToXML($Entregado['Estado']) . '" ';
//   echo 'codigoseguimiento="' . parseToXML($dato['CodigoSeguimiento']) . '" ';
//   echo '/>';

// End XML file
// echo '</markers>';
// Add to XML document node
  echo json_encode(array('data'=>(array('latitude'=>$latitud,'longitude'=>$longitud))));
}
//   echo json_encode(array('data'=>$rows));



//   echo json_encode(array('data'=>array('latitud'=>$latitud,'longitud'=>$longitud)));
//    echo json_encode(array("photo_id"=> 5193281,
//                         "photo_title"=>"The park at Gamlehaugen a bautiful day in September 2007, Bergen - Norway",
//                         "photo_url"=> "http://www.panoramio.com/photo/5193281",
//                         "photo_file_url"=>"http://mw2.google.com/mw-panoramio/photos/medium/5193281.jpg",
//                         "longitude"=> $longitud,
//                         "latitude"=> $latitud,
//                         "width"=> 500,
//                         "height"=> 279,
//                         "upload_date"=> "09 October 2007",
//                         "owner_id"=> 121518,
//                         "owner_name"=> "S.M Tunli - www.tunliweb.no",
//                         "owner_url"=> "http://www.panoramio.com/user/121518"));
// }
?>

