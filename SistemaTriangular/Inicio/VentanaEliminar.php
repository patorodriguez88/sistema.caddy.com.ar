<?php
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.triangularlogistica.com.ar");
}
$color='red';
$font='white';
$color2='white';
$font2='black';
$FacturaHeredada=$_GET['Factura'];
$Pant=$_GET['Pant'];
$CuitHeredado=$_GET['numerocuit'];

?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
echo "<div id='fade' class='overlay'></div>";
echo "<div id='light' class='modal'>";

  
$CodigoSeguimiento=$_GET['CS'];
$NumeroVenta=$_GET['NV'];  

  if($_GET['Accion']=='Eliminar'){
  echo "<table border='0' width='100%' vspace='5px' style='margin-top:5px;float:center;'>";
  $sql1=mysql_query("UPDATE HojaDeRuta SET Eliminado='1' WHERE Seguimiento='$CodigoSeguimiento'");
  if($sql1==TRUE){
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro Eliminado de Hoja de Ruta</td>";
  }else{
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro no encontrado en Hoja de Ruta</td>";
  }
    $sql2=mysql_query("UPDATE TransClientes SET Eliminado='1' WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
  if($sql2==TRUE){
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro Eliminado de TransClientes</td>";
  }else{
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro no encontrado en TransClientes</td>";
  }
    $sql3=mysql_query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$CodigoSeguimiento'");  
  if($sql3==TRUE){
    echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro Eliminado de Ventas</td>";
    }else{
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Registro no encontrado en Ventas</td>";
  }
  $BuscarTrans=mysql_query("SELECT Debe FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
  $DatoBuscarTrans=mysql_fetch_array($BuscarTrans);
  $DatoBuscarTransFinal=$DatoBuscarTrans[Debe];
  $Buscar=mysql_query("SELECT Debe FROM Ctasctes WHERE NumeroVenta='$NumeroVenta'");
  $Dato=mysql_fetch_array($Buscar);
  $DatoBuscar=$Dato[Debe];
  $Saldo=$DatoBuscar-$DatoBuscarTransFinal;  
  $sql4=mysql_query("UPDATE Ctasctes SET Debe='$Saldo' WHERE NumeroVenta='$NumeroVenta' LIMIT 1");
  if($sql4==TRUE){
  echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'><td>Se descontaron $DatoBuscarTransFinal 
  de la venta $NumeroVenta quedando un saldo de $Saldo </td>";
  }
  echo "</table>";
echo "<form action='Cpanel.php' method='GET'>";
echo "<input type='submit' name='Accion' value='Aceptar' style='float:right'></td></tr>";
echo "</form>";


  goto a;  
}  
$ordenar="SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'";	
$MuestraTrans=mysql_query($ordenar);
$numfilas = mysql_num_rows($MuestraTrans);

echo "<table border='0' width='100%' vspace='5px' style='margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'>";
echo "<td>ELIMINAR VENTA</td></tr>";
// echo "<tr style='color:$font2;background:$color2;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='9';		
echo "<table class='' border='0' width='100%' vspace='15px' style='margin-top:5px;float:center;padding:8px'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:14px;'>";
// echo "<td colspan='$Extender' style='font-size:22px;padding:8px'>Asientos Contables Numero: $Asiento</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:16px;'>";
		
echo "<td>Fecha</td>";
echo "<td>Origen</td>";
echo "<td>Destino</td>";  
echo "<td>Codigo Seguimiento</td>";
echo "<td>Numero Venta</td>";
echo "<td>Cantidad</td>";
echo "<td>Total</td></tr>";

$fila = mysql_fetch_array($MuestraTrans);
	echo "<tr align='left' style='font-size:12px;color:$font2;background: #f2f2f2;' >";

$fecha=$fila['Fecha'];
$arrayfecha=explode('-',$fecha,3);
	echo "<td style='padding:8px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
	echo "<td>".$fila[RazonSocial]."</td>"; 
	echo "<td>".$fila[ClienteDestino]."</td>"; 
  echo "<td>".$fila[CodigoSeguimiento]."</td>";
  echo "<td>".$fila[NumeroComprobante]."</td>";
  echo "<td>".$fila[Cantidad]."</td>";
	echo "<td>".$fila[Debe]."</td></tr>";
  echo "<tr align='right' style='font-size:12px;color:$font2;background:F2F2F2;' >";
		echo "<form action='' method='GET'>";
		echo "<input type='hidden' name='CS' value='".$fila[CodigoSeguimiento]."'>";
			echo "<input type='hidden' name='NV' value='".$fila[NumeroComprobante]."'>";	
  echo "<tr><td colspan='8'><input type='submit' name='Accion' value='Eliminar' style='float:right'></td></tr>";
		echo "</form>";


a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
?> 