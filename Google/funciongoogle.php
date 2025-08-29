<?
session_start();
include_once "../Conexion/Conexioni.php";
//ORIGEN
$sqlOrigen=$mysqli->query("SELECT Direccion FROM Clientes WHERE id='$_POST[origen]'");
$ResultadoOrigen=$sqlOrigen->fetch_array(MYSQLI_ASSOC);
$Origenpost=$ResultadoOrigen[Direccion];
// $Origenpost='Andres Lamas 2479, Cordoba, Argentina';
//DESTINO
$sqlDestino=$mysqli->query("SELECT Direccion FROM Clientes WHERE id='$_POST[destino]'");
$ResultadoDestino=$sqlDestino->fetch_array(MYSQLI_ASSOC);
$Destinopost=$ResultadoDestino[Direccion];
// $Destinopost='Justiniano Posse 1236, Cordoba, Argentina';
$Key = 'AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk';//APY KEY GOOGLE

$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
// $Origen=$Origenpost;
// $Destino=$Destinopost;  
$Modo="driving";
$Lenguaje="es-ES";
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
$obj=json_decode($json,true);
$result=$obj['rows'][0]['elements'][0]['distance']['value'];
$result2=$obj['rows'][0]['elements'][0]['distance']['text'];
$resultduration=$obj['rows'][0]['elements'][0]['duration']['text'];
$resultduration2=$obj['rows'][0]['elements'][0]['duration']['value'];
echo json_encode(array('success' => 1,'distancia'=> $result,'origen'=>$Origen,'destino'=>$Destino,'duration'=>$resultduration,'distanciat'=>$result2
                      ,'duration2'=>$resultduration2,));

?>