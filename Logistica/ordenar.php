<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
date_default_timezone_set('America/Argentina/Cordoba');
mysql_select_db("dinter6_triangular",$conexion);  
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
// $Recorrido=$_SESSION['Recorrido'];
$Recorrido='1139';
$Direccion=utf8_decode($_POST['Direccion']);
$Orden=$_POST[Orden];
$Horaactual=date('H:i:s');
$minutos=$_POST['Minutos'];
$Hora  = strtotime ( '+'.$minutos.' minute' , $Horaactual )  ;  
$sql=mysql_query("UPDATE HojaDeRuta SET Posicion='$Orden',Hora='$Hora' WHERE Estado='Abierto' AND Eliminado=0 AND Localizacion LIKE '%$Direccion%' AND Recorrido='$Recorrido'");
// echo json_encode(array('success' => 1,'MaxKm'=> $maxkm,'PrecioPlano'=>$precioplano));