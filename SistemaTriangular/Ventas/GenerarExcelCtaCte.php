<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$Desde=$_GET['Desde'];
$Hasta=$_GET['Hasta'];	
$cliente=$_SESSION['ClienteActivo'];
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition:  filename=\"CtaCte:".$cliente."D:".$Desde."H:".$Hasta.".xls\";");  

$sql="SELECT * FROM Ctasctes WHERE RazonSocial='$cliente' AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha,NumeroVenta ASC";

$r = mysql_query($sql);
echo '<table>';
echo '<tr><td>Fecha</td><td>Razon Social</td><td>Cuit</td><td>Tipo de Comprobante</td><td>Numero</td>
<td>Debe</td><td>Haber</td><td>Saldo</td></tr>';
while($arr = mysql_fetch_array($r)) {
$Saldo+=$arr[Debe]-$arr[Haber];
  echo '<tr>';
  echo '<td>'.$arr[Fecha].'</td>';
  echo '<td>'.$arr[RazonSocial].'</td>';
  echo '<td>'.$arr[Cuit].'</td>';
  echo '<td>'.$arr[TipoDeComprobante].'</td>';
  if($arr[Debe]<>0){
  echo '<td>'.$arr[NumeroFactura].'</td>';
  }else{
  echo '<td>'.$arr[NumeroVenta].'</td>';    
  }
  echo '<td>'.$arr[Debe].'</td>';
  echo '<td>'.$arr[Haber].'</td>';
  echo '<td>'.$Saldo.'</td>';
  echo '</tr>'; 
}
echo '</table>';
ob_end_flush();
?>