<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$Recorrido=$_SESSION['Recorrido'];
$id=$_POST[Id];
$Orden=$_POST[Orden];
$sql=mysql_query("UPDATE HojaDeRuta SET Posicion='$Orden' WHERE id='$id'");
// echo json_encode(array('success' => 1,'MaxKm'=> $maxkm,'PrecioPlano'=>$precioplano));