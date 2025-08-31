<?php
session_start();
require_once('../../../Conexion/Conexioni.php');
date_default_timezone_set("America/Argentina/Cordoba");

$Rec='80';
$order=$_POST['waypoint_order'];
$sql=$mysqli->query("SELECT Hora FROM Logistica WHERE Recorrido='".$Rec."' AND Eliminado='0' AND Estado<>'Cerrada'");
$Hora=$sql->fetch_array(MYSQLI_ASSOC);
$duration=array();
$duration=$_POST['Duration'];
$duration=['466','1106','1164','841','891','2938','2841','1104'];

$Hora_actual=array();
// $Horas=array();

for($i=0;$i<count($duration);$i++){

$time_delivered=5;//TIEMPO ADICIONAL POR ENTREGA DEL PAQUETE
$horas=floor($duration[$i]/3600);

$minutos=number_format($duration[$i]/60,0)+$time_delivered;
$segundos= $duration[$i] % 60;

$Hora = date('H:i:s'); 
$newHora = new DateTime($Hora); 
$newHora->modify('+'.$horas.' hours'); 
$newHora->modify('+'.$minutos.' minute'); 
$newHora->modify('+'.$segundos.' second'); 
$Hora_actual[]= $newHora->format('H:i:s');
}
echo json_encode(array('dato'=>$Hora_actual));

$sql=$mysqli->query("SELECT id,Localizacion FROM HojaDeRuta WHERE Recorrido='$Rec' AND Estado='Abierto' AND Eliminado=0 AND Devuelto=0 ORDER BY Posicion");
$i=0;        

   while($row = $sql->fetch_array(MYSQLI_ASSOC)){

//    $mysqli->query("UPDATE HojaDeRuta SET Posicion='".$order[$i]."',Hora='".$Hora_actual[$i]."' WHERE id='".$row['id']."' LIMIT 1 ");
    
    $i++;
      
   }
   
//    echo json_encode(array('success'=>1));
