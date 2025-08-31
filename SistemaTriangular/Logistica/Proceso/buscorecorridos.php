<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
//  mysql_set_charset("utf8"); 
// include_once "../../ConexionSmartphone.php";
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$CodigoSeguimiento=$_POST['CodigoSeguimiento'];
$Reconew=$_POST['Reconew'];

$sqlvehiculo=mysql_query("SELECT Vehiculos.ColorSistema,Logistica.Recorrido FROM Logistica,Vehiculos 
WHERE Logistica.Patente=Vehiculos.Dominio 
AND Logistica.Estado IN('Cargada','Alta')
AND Logistica.Eliminado='0'");  
$datosqlvehiculo=mysql_fetch_array($sqlvehiculo);
$Color=$datosqlvehiculo[ColorSistema];
$Recorridos = array();
$Recorridos=$datosqlvehiculo[Recorrido];

// $sqlhdr=mysql_query("SELECT * FROM HojaDeRuta WHERE id='$id'");
// $datohdr=mysql_fetch_array($sqlhdr);
// $nombre=$datohdr[Cliente];
// $posicion=$datohdr[Posicion];
// $domicilio=utf8_decode($datohdr[Localizacion]);
// $sql=mysql_query("SELECT Retirado FROM `TransClientes` WHERE CodigoSeguimiento='$datohdr[Seguimiento]'");
// $row=mysql_fetch_array($sql);
// $Retirado=$row[Retirado];
// $Seguimiento=$datohdr[Seguimiento];
// echo json_encode(array('success'=> 1,'Nombre'=> $nombre,'Posicion'=>$posicion,'Domicilio'=>$domicilio,'Retirado'=>$Retirado,'Seguimiento'=>$Seguimiento));
echo json_encode(array('resultado'=> 1,'Colornew'=>$Color,'Recorridos'=>$Recorridos,'Cantidad'=>5));