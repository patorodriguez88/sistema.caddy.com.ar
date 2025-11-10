<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.triangularlogistica.com.ar");
}
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
//$FacturaHeredada=$_GET['Factura'];
//$Pant=$_GET['Pant'];
//$NAsiento=$_GET['nasiento_t'];

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
$idHeredado=$_GET['id'];
	
echo "<form name='MyForm' class='login' action='' method='get' style='float:center; width:95%;'>";
// echo "<div><label style='float:center;color:red;font-size:16px'>Ingreso de Asientos</label></div>";
echo "<div><input name='nasiento_t' size='10' type='text' value='$idHeredado'/></div>";
echo "<div><label style='font-size:14px'>Rubro:</label><input name='rubro_t' size='10' type='text' value=''/></div>";
echo "<div ><label style='width:0px;float:left;font-size:12px'>Observaciones</label><input type='text' name='observaciones_t' value='' style='width:500px'></div>";
echo "<div><input name='CargarMovimiento' class='bottom' type='submit' value='Cancelar'>";
echo "<input name='CargarMovimiento' class='bottom' type='submit' value='Aceptar' ></div>";
echo "</form>";

if ($_GET['CargarMovimiento']=='Cancelar'){
header('location:Proveedores.php?id='.$idHeredado);
}elseif ($_GET['CargarMovimiento']=='Aceptar'){
//BUSCA EL NUMERO DE CUENTA SEGUN LA CUENTA SELECCIONADA
	$Rubro=$_GET['rubro_t'];
	 $sql="INSERT INTO Rubros(Rubro) VALUES ('{$Rubro}')"; 
	mysql_query($sql);
  header('location:Proveedores.php?id='.$idHeredado);
	}

a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 