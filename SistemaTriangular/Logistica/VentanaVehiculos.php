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

$Dominio=trim(strip_tags($_GET['Dominio']));
$_SESSION['Dominio']=$Dominio;

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
	
$ordenar="SELECT * FROM Vehiculos";	
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
echo "<td colspan='$Extender' style='font-size:22px;padding:8px'>Listado de Vehiculos</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:14px;'>";
		
echo "<td>Marca</td>";
echo "<td>Modelo</td>";
echo "<td>Dominio</td>";
echo "<td>Seleccionar</td></tr>";

$numfilas =0;
	while($fila = mysql_fetch_array($MuestraTrans)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font2;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font2;background:$color2;' >";
	}	
	echo "<td>".$fila[Marca]."</td>";
	echo "<td>".$fila[Modelo]."</td>";
	echo "<td>".$fila[Dominio]."</td>";

//SELECCIONAR
		echo "<td align='center'><a href='http://www.triangularlogistica.com.ar/SistemaTriangular/Logistica/Logistica.php?id=Ver&Dominio=".$fila[Dominio]."'>
		<input type='image' src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td></tr>";
 	$numfilas++; 
	}
a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
?> 