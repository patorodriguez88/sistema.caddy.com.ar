<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['Pendientes']==1){
  
  //CONSIDERAMOS CAMPAÑAS A TODOS LOS GRUPOS DE ENVIOS DEL MISMO ORIGEN MISMO NUMERO DE VENTA Y MAYOR DE 50 ENVIOS.
 
 $sql="SELECT Fecha,RazonSocial,NumeroVenta,COUNT(id) as Cantidad,SUM(Debe)as Total,SUM(Entregado)as Entregados 
 FROM `TransClientes` WHERE Fecha>='2021-01-01' AND Eliminado=0 AND Debe<>0 GROUP BY NumeroVenta, RazonSocial,Fecha 
 HAVING COUNT(id) >=50";

 $Resultado=$mysqli->query($sql);
 $rows=array();   
 while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
 $rows[]=$row;
 }
 echo json_encode(array('data'=>$rows));
}
