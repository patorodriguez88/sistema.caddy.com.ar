<?php
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.triangularlogistica.com.ar");
}
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
$FacturaHeredada=$_GET['Factura'];
$Pant=$_GET['Pant'];
$CuitHeredado=$_GET['numerocuit'];

// print $_SESSION['NCliente'];
$user= $_POST['user'];
$password= $_POST['password'];

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
$Asiento=$_GET['NA'];
	if ($_GET['Ver']=='Si'){

$ordenar="SELECT * FROM Tesoreria WHERE NumeroAsiento='$Asiento'";	
$MuestraTrans=mysql_query($ordenar);
$numfilas = mysql_num_rows($MuestraTrans);

echo "<table border='0' width='100%' vspace='5px' style='margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'>";
echo "<td>ASIENTOS CONTABLES</td></tr>";
echo "<tr style='color:$font2;background:$color2;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='9';		
echo "<table class='' border='0' width='100%' vspace='15px' style='margin-top:5px;float:center;padding:8px'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:14px;'>";
echo "<td colspan='$Extender' style='font-size:22px;padding:8px'>Asientos Contables Numero: $Asiento</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:16px;'>";
		
echo "<td>Asiento</td>";
echo "<td>Fecha</td>";
echo "<td>N Cuenta</td>";
echo "<td>Cuenta</td>";
echo "<td>Observaciones</td>";
echo "<td>Debe</td>";
echo "<td>Haber</td></tr>";

$numfilas =0;
	while($fila = mysql_fetch_array($MuestraTrans)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:12px;color:$font2;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:12px;color:$font2;background:$color2;' >";
	}	

echo "<td>".$fila['NumeroAsiento']."</td>";

$fecha=$fila['Fecha'];
$arrayfecha=explode('-',$fecha,3);
	echo "<td style='padding:8px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
	echo "<td>".$fila['Cuenta']."</td>";
	echo "<td>".$fila['NombreCuenta']."</td>"; 
  echo "<td>".$fila['Observaciones']."</td>";
  echo "<td>".$fila['Debe']."</td>";
  echo "<td>".$fila['Haber']."</td>";
	echo "<td>".$fila['Cantidad']."</td></tr>";
 	$numfilas++; 
	}

    $ordenarTotal=mysql_query("SELECT SUM(Debe-Haber)as Total FROM Tesoreria WHERE NumeroAsiento=$Asiento");	
    $row = mysql_fetch_array($ordenarTotal);
    
		$Total= money_format('%i',$row[Total]);
  	echo "<tr align='right' style='font-size:12px;color:$font2;background:F2F2F2;' >";
		echo "<td colspan='8'>Total Asiento: $Total</td></tr>";
		echo "<form action=''>";
		echo "<input type='hidden' name='Factura' value='$FacturaHeredada'>";
		echo "<input type='hidden' name='Pant' value='$Pant'>";
		echo "<input type='hidden' name='numerocuit' value='$CuitHeredado'>";
		echo "<tr><td colspan='8'><input type='submit' name='Accion' value='Volver' style='float:right'></td></tr>";
		echo "</form>";
	
goto a;	
}
if ($_GET['Ver']=='Ver Todos los Asientos!'){
$ordenar="SELECT * FROM Tesoreria WHERE Debe>'0' ORDER BY NumeroAsiento DESC";
$Leyenda='Ver Solo Anticipos';	
}else{	
$ordenar="SELECT * FROM Tesoreria WHERE Cuenta>='112100' AND Cuenta<='112900' AND Debe>'0' ORDER BY NumeroAsiento DESC";	
$Leyenda='Ver Todos los Asientos!';	

}
$MuestraTrans=mysql_query($ordenar);
$numfilas = mysql_num_rows($MuestraTrans);

echo "<table border='0' width='100%' vspace='5px' style='margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'>";
// echo "<td>ASIENTOS CONTABLES</td></tr>";
echo "<tr style='color:$font2;background:$color2;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='9';		
echo "<table class='' border='0' width='100%' vspace='15px' style='margin-top:5px;float:center;padding:10px'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:14px;'>";
echo "<a href='VentanaAsientos.php?Factura=$FacturaHeredada&Pant=$Pant&Ver=$Leyenda' style='font-size:12px'>$Leyenda</a>";
echo "<td colspan='$Extender' style='font-size:22px;padding:8px'>Listado de Asientos Contables</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:14px;'>";
		
echo "<td>Numero</td>";
echo "<td>Fecha</td>";
echo "<td>N Cuenta</td>";
echo "<td>Cuenta</td>";
echo "<td>Debe</td>";
echo "<td>Haber</td>";
echo "<td>Ver Asiento</td>";
echo "<td>Seleccionar</td></tr>";

$numfilas =0;
	while($fila = mysql_fetch_array($MuestraTrans)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:12px;color:$font2;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:12px;color:$font2;background:$color2;' >";
	}	

echo "<td>".$fila['NumeroAsiento']."</td>";

$fecha=$fila['Fecha'];
$arrayfecha=explode('-',$fecha,3);
	echo "<td style='padding:8px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
	echo "<td>".$fila['Cuenta']."</td>";
	echo "<td>".$fila['NombreCuenta']."</td>";
	echo "<td>".$fila['Debe']."</td>";
  echo "<td>".$fila['Haber']."</td>";
	echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
	echo "<input type='hidden' name='id' value='$u'>";
//VER ASIENTO
		echo "<td align='center'><a href='VentanaAsientos.php?Ver=Si&NA=".$fila['NumeroAsiento']."&Factura=$FacturaHeredada&Pant=$Pant&numerocuit=$CuitHeredado'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
//SELECCIONAR
	if ($_GET['Pant']=='Compras'){
		echo "<td align='center'><a href='http://www.caddy.com.ar/SistemaTriangular/Proveedores/Compras.php?CargarPago=Si&Factura=$FacturaHeredada&NA=".$fila['NumeroAsiento']."'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td></tr>";
	}elseif($_GET['Pant']=='ComprasCargaFactura'){	
	echo "<td align='center'><a href='http://www.caddy.com.ar/SistemaTriangular/Proveedores/Compras.php?Cargar=Si&Cuit=$CuitHeredado&NA=".$fila['NumeroAsiento']."'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td></tr>";
	}else{ 
		echo "<td align='center'><a href='Contabilidad.php?IngresaAsientos=Si&NA=".$fila['NumeroAsiento']."&Eliminado=No'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td></tr>";
}
 	$numfilas++; 
	}
  echo "</form>";
a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
?> 