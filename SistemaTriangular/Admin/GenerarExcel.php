<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$Desde=$_GET['Desde'];
$Hasta=$_GET['Hasta'];	
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition:  filename=\"LibroIva".$_GET['Libro']."Desde".$Desde."Hasta".$Hasta.".xls\";");  

if($_GET['Libro']=='Compras'){
$sql = "SELECT * FROM IvaCompras WHERE Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha ASC";
$r = mysql_query($sql);
echo '<table>';
echo '<tr><td>Fecha</td><td>Razon Social</td><td>Cuit</td><td>Tipo de Comprobante</td><td>Numero</td>
<td>Imp.Neto</td><td>Iva 1</td><td>Iva 2</td><td>Iva 3</td><td>Percepcion iva</td><td>Percepcion IIBB</td><td>Exento</td><td>Total</td></tr>';
  while($arr = mysql_fetch_array($r)) {
  echo '<tr>';
    echo '<td>'.$arr[Fecha].'</td>';
    echo '<td>'.$arr[RazonSocial].'</td>';
    echo '<td>'.$arr[Cuit].'</td>';
    echo '<td>'.$arr[TipoDeComprobante].'</td>';
    echo '<td>'.$arr[NumeroComprobante].'</td>';
    echo '<td>'.$arr[ImporteNeto].'</td>';
    echo '<td>'.$arr[Iva1].'</td>';
    echo '<td>'.$arr[Iva2].'</td>';
    echo '<td>'.$arr[Iva3].'</td>';
    echo '<td>'.$arr[PercepcionIva].'</td>';
    echo '<td>'.$arr[PercepcionIIBB].'</td>';
    echo '<td>'.$arr[Exento].'</td>';
    echo '<td>'.$arr[Total].'</td>';
    echo '</tr>'; 
  }
}elseif($_GET['Libro']=='Ventas'){
$sql = "SELECT * FROM IvaVentas WHERE Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha ASC";
$r = mysql_query($sql);
echo '<table>';
echo '<tr><td>Fecha</td><td>Razon Social</td><td>Cuit</td><td>Tipo de Comprobante</td><td>Numero</td>
<td>Imp.Neto</td><td>Iva 1</td><td>Iva 2</td><td>Iva 3</td><td>Exento</td><td>Total</td></tr>';
  while($arr = mysql_fetch_array($r)) {
    echo '<tr>';
    echo '<td>'.$arr[Fecha].'</td>';
    echo '<td>'.$arr[RazonSocial].'</td>';
    echo '<td>'.$arr[Cuit].'</td>';
    echo '<td>'.$arr[TipoDeComprobante].'</td>';
    echo '<td>'.$arr[NumeroComprobante].'</td>';
    echo '<td>'.$arr[ImporteNeto].'</td>';
    echo '<td>'.$arr[Iva1].'</td>';
    echo '<td>'.$arr[Iva2].'</td>';
    echo '<td>'.$arr[Iva3].'</td>';
    echo '<td>'.$arr[Exento].'</td>';
    echo '<td>'.$arr[Total].'</td>';
    echo '</tr>'; 
  }
}

echo '</table>';
ob_end_flush();
?>