<?php
session_start();
include_once "../../Conexion/Conexioni.php";

if(isset($_POST['Right-bar'])){

//HORA SALIDA
$sqlLogistica="SELECT Hora,Fecha FROM Logistica WHERE NumeroDeOrden='$_POST[Orden]' AND Recorrido='$_POST[Recorrido]' AND Eliminado='0'";
$RespuestaLogistica=$mysqli->query($sqlLogistica);  
$rowLogistica=$RespuestaLogistica->fetch_array(MYSQLI_ASSOC);

$sql="SELECT COUNT(id)as Retiros FROM TransClientes WHERE Recorrido='$_POST[Recorrido]' AND NumerodeOrden='$_POST[Orden]' AND Eliminado='0' AND Retirado='0'";
$Respuesta=$mysqli->query($sql);  
$row=$Respuesta->fetch_array(MYSQLI_ASSOC);
//RETIRADO
$sqlRetirado="SELECT COUNT(t.id)as Retirado FROM TransClientes t INNER JOIN Seguimiento s ON t.CodigoSeguimiento=s.CodigoSeguimiento WHERE 
t.Recorrido='$_POST[Recorrido]' AND t.NumerodeOrden='$_POST[Orden]' 
AND t.Eliminado='0' AND t.Retirado='0' AND s.Estado='Retirado del Cliente'";
$RespuestaRetirado=$mysqli->query($sqlRetirado);  
$rowRetirado=$RespuestaRetirado->fetch_array(MYSQLI_ASSOC);

//NO SE PUDO RETIRAR
$sql0="SELECT COUNT(t.id)as NoRetirado FROM TransClientes t INNER JOIN Seguimiento s ON t.CodigoSeguimiento=s.CodigoSeguimiento WHERE 
t.Recorrido='$_POST[Recorrido]' AND t.NumerodeOrden='$_POST[Orden]' 
AND t.Eliminado='0' AND t.Retirado='0' AND s.Estado='No se pudo Retirar'";
$Respuesta0=$mysqli->query($sql0);  
$row0=$Respuesta0->fetch_array(MYSQLI_ASSOC);


$sql1="SELECT COUNT(id)as Entregas FROM TransClientes WHERE Recorrido='$_POST[Recorrido]' AND NumerodeOrden='$_POST[Orden]' AND Eliminado='0' AND Retirado='1'";
$Respuesta1=$mysqli->query($sql1);  
$row1=$Respuesta1->fetch_array(MYSQLI_ASSOC);

//ENTREGADOS
$sql2="SELECT COUNT(id)as Entregas FROM TransClientes WHERE Recorrido='$_POST[Recorrido]' AND NumerodeOrden='$_POST[Orden]' 
AND Eliminado='0' AND Retirado='1' AND Entregado='1'";
$Respuesta2=$mysqli->query($sql2);  
$row1=$Respuesta2->fetch_array(MYSQLI_ASSOC);

echo json_encode(array('success'=> 1,'HoraSalida'=>$rowLogistica[Hora],'Fecha'=>$rowLogistica[Fecha],'Retiros'=>$row[Retiros],'Retirado'=>$rowRetirado[Retirado],'NoRetirado'=>$row0[NoRetirado],
'Entregas'=>$row1[Entregas],'Entregado'=>$row2[Entregas]));  
  
}
?>