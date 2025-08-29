<?
session_start();
global $host,$user,$password,$database;
$host='localhost';
$user='dinter6_prodrig';
$pass='pato@4986';
$database='dinter6_triangular';
$conexion=mysqli_connect($host,$user,$pass,$database);
//RECORRIDO
$Recorrido=$_SESSION['Recorrido'];  
//VARIABLES GOOGLE
$Key = 'AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8';//APY KEY GOOGLE
$Origenpost = "Reconquista 4986 Córdoba Argentina"; // ORIGEN
$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Modo="driving";
$Lenguaje="fr-FR";

$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO='0'";

$resultado=$conexion->query($query);
while($row=$resultado->fetch_array()){ 
$Destinopost= utf8_encode($row[Localizacion]);
$Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
$obj=json_decode($json,true);
$result=$obj['rows'][0]['elements'][0]['distance']['value'];
$duration=$obj['rows'][0]['elements'][0]['duration']['value'];
  $sql="UPDATE HojaDeRuta SET KmO='$result',Tiempo='$duration' WHERE id='$row[id]'";
  
$conexion->query($sql);  
}
//AGREGO NUMERO DE ORDEN SEGUN LA DISTANCIA
$queryOrden="SELECT id FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO<>'0' ORDER BY KmO ASC";
$resultadoOrden=$conexion->query($queryOrden);
$i=0;
while($row=$resultadoOrden->fetch_array()){
$sqlOrdeno="UPDATE HojaDeRuta SET Posicion='$i' WHERE id='$row[id]'";  
$conexion->query($sqlOrdeno);  
$i++;
}
header('location:https://www.caddy.com.ar/SistemaTriangular/Logistica/HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);
?>  