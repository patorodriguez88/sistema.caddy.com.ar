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

$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto'";
$resultado=$conexion->query($query);
$total=$resultado->num_rows;

while($row=$resultado->fetch_array()){ 
$Destinopost= utf8_encode($row[Localizacion]);
$Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
 if($obj=json_decode($json,true)){
$result=$obj['rows'][0]['elements'][0]['distance']['value'];
$duration=$obj['rows'][0]['elements'][0]['duration']['value'];
$sql="UPDATE HojaDeRuta SET KmO='$result',Tiempo='$duration',Posicion='' WHERE id='$row[id]'";
$conexion->query($sql);
 }  
}
//AGREGO NUMERO DE ORDEN SEGUN LA DISTANCIA
//BUSCO EL CLIENTE MAS CERCA

for($i=1;$i<=$total;$i++){
//PRIMERO MARCO EL CLIENTE MAS CERCANO A CADDY
$queryOrden="SELECT id,Localizacion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO<>'0' AND Posicion='0' ORDER BY KmO ASC LIMIT 1";
$resultadoOrden=$conexion->query($queryOrden);
$rowOrden=$resultadoOrden->fetch_array();
if($sqlOrdeno="UPDATE HojaDeRuta SET Posicion='$i' WHERE id='$rowOrden[id]'"){  
$conexion->query($sqlOrdeno);
}  
  $Origenpost = utf8_encode($rowOrden[Localizacion]); // ORIGEN
  $Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
 // AHORA VUELVO A CALCULAR DISTANCIAS ENTRE LOS OTROS PUNTOS
  $query2="SELECT id,Localizacion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO<>'0' AND Posicion='0'";
  $resultado2=$conexion->query($query2);
  while($row2=$resultado2->fetch_array()){
  $Destinopost= utf8_encode($row2[Localizacion]);
  $Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
  $urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
  $json=file_get_contents($urlPush);
  $obj=json_decode($json,true);
    if($obj=json_decode($json,true)){
    $result=$obj['rows'][0]['elements'][0]['distance']['value'];
    $duration=$obj['rows'][0]['elements'][0]['duration']['value'];
    $sql2="UPDATE HojaDeRuta SET KmO='$result',Tiempo='$duration' WHERE id='$row2[id]'";
    $conexion->query($sql2);
    }
   }

}

header('location:https://www.caddy.com.ar/SistemaTriangular/Logistica/HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);
?>  