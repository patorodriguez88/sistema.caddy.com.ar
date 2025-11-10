<?php
session_start();
include_once "../ConexionSmartphone.php";
if($_POST[Menu]==1){
$sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Retirado='1'
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' 
AND HojaDeRuta.Eliminado='0'
AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

$sql2=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Retirado='0'
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' 
AND HojaDeRuta.Eliminado='0'
AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      
$Retiros=mysql_numrows($sql2);
$Entregas=mysql_numrows($sql);

$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$NumeroComprobante=$Dato[Patente];
 
$extension = explode(".",$NumeroComprobante,2);
$ruta = "../../Logistica/Polizas/" . $NumeroComprobante.".pdf";
if (file_exists($ruta)){
$Ruta=1;
}else{
$Ruta=0;
}
echo json_encode(array('success'=> 1,'Retiros'=> $Retiros,'Entregas'=>$Entregas,'Ruta'=>$Ruta,'NComprobante'=>$NumeroComprobante));
}  
?>