<?
session_start();
global $host,$user,$password,$database;
$host='localhost';
$user='dinter6_prodrig';
$pass='pato@4986';
$database='dinter6_triangular';
$conexion=mysqli_connect($host,$user,$pass,$database);
 
//VARIABLES GOOGLE
$Key = 'AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8';//APY KEY GOOGLE
$Origenpost = "Reconquista 4986 Córdoba Argentina"; // ORIGEN
$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Modo="driving";
$Lenguaje="es";

if($_POST['Orden_Automatic']==1){
//RECORRIDO
$Recorrido=$_POST['Recorrido']; 

    //BUSCO LA HORA DE SALIDA DEL RECORRIDO
    $sql=$conexion->query("SELECT Hora FROM Logistica WHERE Recorrido='$Rec' AND Estado<>'Cerrada' AND Eliminado=0");    
    if($row_inicio=$sql->fetch_array()!=NULL){
    $Hora=$row_inicio['Hora'];    
    }else{
    $sql=$conexion->query("SELECT Hora FROM Logistica WHERE Recorrido='5' AND Eliminado=0 ORDER BY Fecha DESC limit 0,1");    
    $row_inicio=$sql->fetch_array();
    $Hora=$row_inicio['Hora'];    
    }

$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto'";
$resultado=$conexion->query($query);
$total=$resultado->num_rows;

    
    while($row=$resultado->fetch_array()){ 

    //  $Destinopost= utf8_encode($row[Localizacion]);
    //  $Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
     $sql_Clientes="SELECT Latitud,Longitud FROM Clientes WHERE id='$row[idCliente]'";
     $Resultado_clientes=$conexion->query($sql_Clientes);
     $dato_clientes=$Resultado_clientes->fetch_array();
     $Destino=$dato_clientes['Latitud'].','.$dato_clientes['Longitud'];   
     
     $urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?departure_time=now&origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
     $json=file_get_contents($urlPush);
    
      if($obj=json_decode($json,true)){
      $result=$obj['rows'][0]['elements'][0]['distance']['value'];
      $duration=$obj['rows'][0]['elements'][0]['duration']['value'];
      $sql="UPDATE HojaDeRuta SET Hora='$Hora',KmO='$result',Tiempo='$duration',Posicion='' WHERE id='$row[id]' LIMIT 1";
      $conexion->query($sql);    
      }  
     }
    
    
//AGREGO NUMERO DE ORDEN SEGUN LA DISTANCIA
//BUSCO EL CLIENTE MAS CERCA

for($i=1;$i<=$total;$i++){
//PRIMERO MARCO EL CLIENTE MAS CERCANO A CADDY
$queryOrden="SELECT id,Localizacion,idCliente FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO<>'0' AND Posicion='0' ORDER BY KmO ASC LIMIT 1";
$resultadoOrden=$conexion->query($queryOrden);
$rowOrden=$resultadoOrden->fetch_array();
if($sqlOrdeno="UPDATE HojaDeRuta SET Posicion='$i' WHERE id='$rowOrden[id]' LIMIT 1"){  
$conexion->query($sqlOrdeno);
}  
  
  $sql_Clientes_0="SELECT Latitud,Longitud FROM Clientes WHERE id='$rowOrden[idCliente]'";
  $Resultado_clientes_0=$conexion->query($sql_Clientes_0);
  $dato_clientes_0=$Resultado_clientes_0->fetch_array();
  $Origen=$dato_clientes_0['Latitud'].','.$dato_clientes_0['Longitud'];   

  
 // AHORA VUELVO A CALCULAR DISTANCIAS ENTRE LOS OTROS PUNTOS HASTA EL MAS CERCANO AL ANTERIOR
  $query2="SELECT id,Localizacion,idCliente,Hora FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado='0' AND Estado='Abierto' AND KmO<>'0' AND Posicion='0'";
  $resultado2=$conexion->query($query2);
  while($row2=$resultado2->fetch_array()){

    $sql_Clientes_1="SELECT Latitud,Longitud FROM Clientes WHERE id='$row2[idCliente]'";
    $Resultado_clientes_1=$conexion->query($sql_Clientes_1);
    $dato_clientes_1=$Resultado_clientes_1->fetch_array();
    $Destino=$dato_clientes_1['Latitud'].','.$dato_clientes_1['Longitud'];   

    $urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?departure_time=now&origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
    $json=file_get_contents($urlPush);

    if($obj=json_decode($json,true)){
    $result=$obj['rows'][0]['elements'][0]['distance']['value'];
    $duration=$obj['rows'][0]['elements'][0]['duration']['value'];
    
    $time_delivered=5;//TIEMPO ADICIONAL POR ENTREGA DEL PAQUETE
    
    $minutos=number_format($duration/60,0)+$time_delivered;
    $segundos= $duration % 60;
    $newHora = new DateTime($row2['Hora']); 
    $newHora->modify('+0 hours'); 
    $newHora->modify('+'.$minutos.' minute'); 
    $newHora->modify('+'.$segundos.' second'); 
    $Hora_actual= $newHora->format('H:i:s');

    $sql2="UPDATE HojaDeRuta SET KmO='$result',Tiempo='$duration',Hora='$Hora_actual' WHERE id='$row2[id]'";
    $conexion->query($sql2);
    }
   }
}


echo json_encode(array('resultado'=>1));
}

?>  