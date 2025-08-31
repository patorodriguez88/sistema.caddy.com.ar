<?php
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
$color='#B8C6DE';
$font='white';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script>  
<?php
echo "<body style='background:".$_SESSION['ColorFondo']."'>";
include("../Menu/MenuGestion.php"); 	
		include("Menu/MenuLateralVentas.php"); 	

echo "<center>";
	if ($_POST['Buscar']=='Aceptar'){
	$Desde=$_POST['fechadesde_t'];
	$Hasta=$_POST['fechahasta_t'];	
		//header("location:IvaCompraspdf.php?Desde=$Desde&Hasta=$Hasta");	
	
// // MUESTRA TODAS LAS ENTREGAS
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
// setlocale(LC_ALL,'es_AR');
$ClienteActivo=$_SESSION['ClienteActivo'];
    
$Grupo="SELECT * FROM Logistica WHERE Cliente=$NdeCliente";
    
$ordenar="SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Entregado='1' 
AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND RazonSocial='$ClienteActivo'";	
$MuestraTrans=mysql_query($ordenar);
$numfilas = mysql_num_rows($MuestraTrans);

if ($numfilas==''){
echo "<table style='margin-top: 250px'><tr><td style='font-size:25px;color:white;'>SISTEMA DE GESTION TRIANGULAR S.A.</td></tr></table>";

goto a;
}
echo "<table border='0' width='80%' vspace='5px' style='margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:22px;'>";
echo "<td>REMITOS EN CIRCULACION</td></tr>";
echo "<tr style='color:$font2;background:$color2;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='15';		
echo "<table class='' border='0' width='80%' vspace='15px' style='margin-top:5px;float:center;padding:8px'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:14px;'>";
echo "<td colspan='$Extender' style='font-size:22px;padding:8px'>Listado de Remitos</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:16px;'>";
		
echo "<td>Numero</td>";
echo "<td>Fecha</td>";
echo "<td>Codigo</td>";
echo "<td>Origen</td>";
echo "<td>Destino</td>";
echo "<td>Entrega En</td>";
echo "<td>Recorrido</td>";
echo "<td>Bultos</td>";
echo "<td>Total</td>";
echo "<td>Remito</td>";
echo "<td>Rotulo</td>";
echo "<td>Modificar</td></tr>";

// $numfilas =0;
	while($fila = mysql_fetch_array($MuestraTrans)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:12px;color:$font1;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:12px;color:$font1;background:$color2;' >";
	}	

echo "<td>".$fila['NumeroComprobante']."</td>";
$Total= money_format('%i',$fila['Debe']);
$fecha=$fila['Fecha'];
$arrayfecha=explode('-',$fecha,3);
	echo "<td style='padding:8px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
	echo "<td>".$fila['CodigoSeguimiento']."</td>";
	echo "<td>".$fila['RazonSocial']."</td>";
	echo "<td>".$fila['ClienteDestino']."</td>";
  echo "<td>".$fila['EntregaEn']."</td>";
  echo "<td>".$fila[Recorrido]."</td>";
	echo "<td>".$fila['Cantidad']."</td>";
	echo "<td>$Total</td>";
	echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
	echo "<input type='hidden' name='id' value='$u'>";
	echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Remitopdf.php?NR=".$fila['NumeroComprobante']."'><input type='image' src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
	echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Rotulospdf.php?NR=".$fila['NumeroComprobante']."'><input type='image' src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
 	echo "<td align='center'><a href='#'><input type='image' src='../../images/botones/lapiz.png' width='15' height='15' border='0' style='float:center;'></td>";
  echo "</form>";
 	$numfilas++; 
	}
echo "</tr></table>";
goto a;
 }  
echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
echo "<div><label style='float:center;color:red;font-size:22px'>Ventas Mensuales</label></div>";	
echo "<div><hr></hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	
$Desde=$_POST['fechadesde_t'];
$Hasta=$_POST['fechahasta_t'];	
	

a:
?>