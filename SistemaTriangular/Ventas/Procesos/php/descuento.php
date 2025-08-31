<?php
session_start();
include('../../../Conexion/Conexioni.php');
mysqli_set_charset($mysqli,"utf8"); 
header('Content-Type: application/json');

$OrdenN=$_SESSION['idTransClientes']; 
$Descuento=$_POST['Descuento'];
// if($_POST[Descontar]==1){
  $OrdenN=$_SESSION['idTransClientes']; 
  $Descuento=$_POST['Descuento'];

  for($i=0;$i<=count($OrdenN);$i++)
  {
  // ACTUALIZO FACTURADO A SI EN TABLA LOGISTICA  
  $sql=$mysqli->query("UPDATE `TransClientes` SET Debe=Debe*((100-$Descuento)/100) WHERE id='$OrdenN[$i]' AND TipoDeComprobante='Remito' AND Eliminado='0'");
  $sqlbuscar=$mysqli->query("SELECT NumeroComprobante,Debe,IngBrutosOrigen FROM TransClientes WHERE id='$OrdenN[$i]' AND TipoDeComprobante='Remito' AND Eliminado='0'");
  $Dato= $sqlbuscar->fetch_array(MYSQLI_ASSOC);
  $Actualizar_CtasCtes="UPDATE Ctasctes SET Debe='$Dato[Debe]' WHERE idCliente='$Dato[IngBrutosOrigen]' AND NumeroVenta='$Dato[NumeroComprobante]' AND Haber='0' LIMIT 1";
  $mysqli->query($Actualizar_CtasCtes);
  }

?>