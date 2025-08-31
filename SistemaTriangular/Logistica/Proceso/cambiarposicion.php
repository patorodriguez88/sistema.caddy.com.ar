<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$Posicionnew=$_POST['Posicion'];
$id=$_POST['Idhdr'];
if($_POST['Retirado']==1){
$sqlhojaderuta="UPDATE HojaDeRuta SET Posicion='$Posicionnew' WHERE id='$id'";
}else{
$sqlhojaderuta="UPDATE HojaDeRuta SET Posicion_retiro='$Posicionnew' WHERE id='$id'";    
}
if(mysql_query($sqlhojaderuta)){
  $resultado="1";
  }else{
  $resultado="0";
  }
echo json_encode(array('resultado'=> $resultado,'Posicionnew'=>$Posicionnew,'id'=>$id));