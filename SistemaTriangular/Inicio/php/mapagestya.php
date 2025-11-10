<?php 
session_start();
include_once "../../Conexion/Conexioni.php";
$sql=$mysqli->query("SELECT User,Password FROM Api WHERE Name='Gestya'");
$row = $sql->fetch_array(MYSQLI_ASSOC);
$Codigo=$row['User'];
$token = $row['Password']; // Ingresar el token 

$Datos="DATOSACTUALES";
// $Vehiculo=$_POST[Dominio];
$Vehiculo="AC850YY";

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
$rows=array();
foreach($json as $obj){
  $rows[]=$obj;
}
echo json_encode(array('data'=>$rows));

?>