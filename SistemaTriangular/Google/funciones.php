<?
session_start();
include_once "../Conexion/Conexioni.php";
//ORIGEN
$sql=$mysqli->query("SELECT id,TransClientes.IngBrutosOrigen,TransClientes.idClienteDestino FROM TransClientes WHERE TransClientes.Entregado=1 
AND Eliminado=0 AND IngBrutosOrigen<>0 AND idClienteDestino<>0 AND IngBrutosOrigen<>'36' AND Fecha>'2021-05-01'");

while($row=$sql->fetch_array(MYSQLI_ASSOC)){
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

$Modo="driving";
$Lenguaje="es-ES";
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
$obj=json_decode($json,true);

$result=$obj['rows'][0]['elements'][0]['distance']['value'];
  if($result<>0){
    $result2=$obj['rows'][0]['elements'][0]['distance']['text'];
    $resultduration=$obj['rows'][0]['elements'][0]['duration']['text'];
    $resultduration2=$obj['rows'][0]['elements'][0]['duration']['value'];
    $result1=$result/1000;
    print $row[id].' '.$result1.' '.$resultduration2;
//     $sqlupdate=$mysqli->query("UPDATE `TransClientes` SET Kilometros='$result1',google_km='$result',google_time='$resultduration2' WHERE id='$row[id]'");
  }
}  

?>