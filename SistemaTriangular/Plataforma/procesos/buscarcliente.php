<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$id=$_POST[id];
$sql=mysql_query("SELECT * FROM `Clientes` WHERE id='$id'");
$row=mysql_fetch_array($sql);
$Id=$row[id];
$RazonSocial=utf8_encode($row[nombrecliente]);
$Direccion=utf8_encode($row[Direccion]);
$Ciudad=utf8_encode($row[Ciudad]);
$Contacto=utf8_encode($row[Contacto]);
$Observaciones=utf8_encode($row[Observaciones]);
echo json_encode(array('success' => 1,'id' => $Id,'RazonSocial' => $RazonSocial,'Direccion' => $Direccion,'Ciudad' => $Ciudad,'Contacto' => $Contacto,'Observaciones' => $Observaciones));