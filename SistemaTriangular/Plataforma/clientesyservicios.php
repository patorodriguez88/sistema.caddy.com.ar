<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$Kilometros=$_POST[km];
$sql=mysql_query("SELECT * FROM `ClientesyServicios` WHERE id=(SELECT MIN(id) FROM ClientesyServicios WHERE NdeCliente='$_SESSION[NCliente]' AND Maxkm>='$Kilometros')");
$row=mysql_fetch_array($sql);
$maxkm=$row[MaxKm];
$precioplano=$row[PrecioPlano];


echo json_encode(array('success' => 1,'MaxKm'=> $maxkm,'PrecioPlano'=>$precioplano));

?>
