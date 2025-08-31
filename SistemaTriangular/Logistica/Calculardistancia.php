<?php

session_start();
include_once "../Conexion/Conexioni.php";
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$Recorrido=$_POST[Recorrido];
$Km=$_POST[km];
$Direccion=$_POST[Direccion];
$tiempo=$_POST[tiempo];
$Orden=$_POST[orden];
$sqlbuscar=$mysqli->query("SELECT id FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Localizacion Like '%$Direccion%' AND Estado='Abierto' AND Eliminado=0");
// $sqlbuscar=$mysqli->query("SELECT id FROM HojaDeRuta WHERE Recorrido='1030' AND Localizacion like '%Tambo Nuevo 185, X5008 GHC, Córdoba, Argentina%' AND Estado='Abierto' AND Eliminado=0");
if(($fila = $sqlbuscar->fetch_array(MYSQLI_ASSOC))!= NULL) {
$id=$fila[id];
$sql=$mysqli->query("UPDATE HojaDeRuta SET KmO='$Km',Tiempo='$tiempo',Posicion='$Orden' WHERE id='$id' LIMIT 1");
echo json_encode(array('success'=>1,'id'=>$id,'orden'=>$Orden,'km'=>$Km,'tiempo'=>$tiempo));
}else{
echo json_encode(array('success'=>0));  
}
