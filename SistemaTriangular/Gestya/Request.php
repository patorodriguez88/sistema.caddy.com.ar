<?
$Codigo = "triangular";//ingresar el codigo a consultar
$token = "4986"; // Ingresar el token 
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
// $Dominiopost=$_POST[Dominio];
// print $result;

foreach($json as $obj){
if($Vehiculo==$obj->patente){
echo json_encode(array('success' => 1,'vehiculo'=> $obj->nombre,'latitud'=>$obj->latitud,'longitud'=>$obj->longitud,'sentido'=>$obj->sentido,'velocidad'=>$obj->velocidad));
}
}

// $json=json_decode($result);
// $vehiculo=$json[0]->nombre;
// print $vehiculo;
// echo json_encode(array('success' => 1,'MaxKm'=> $maxkm,'PrecioPlano'=>$precioplano));
