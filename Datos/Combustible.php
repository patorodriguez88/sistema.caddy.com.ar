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
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script>  
<?php

echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";

echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
echo "<div><titulo>Resultados Combusitble</titulo></div>";	
echo "<div><hr></hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	

$Desde=$_POST['fechadesde_t'];
$Hasta=$_POST['fechahasta_t'];	
	
	if ($_POST['Buscar']=='Aceptar'){
	$Desde0=explode("-",$_POST['fechadesde_t'],3);
  $Desde= $Desde0[2]."/".$Desde0[1]."/".$Desde0[0];
 	$Hasta0=explode("-",$_POST['fechahasta_t'],3);	
	$Hasta= $Hasta0[2]."/".$Hasta0[1]."/".$Hasta0[0];
 	
  }
echo "<table class='login' border='0' vspace='15px' style='width:600px;margin-left:280px;float:center;margin-top:15px'>";
echo "<caption>Total de Combustible Cargado x Vehiculo</caption>";
echo "<caption>Desde: ".$Desde." Hasta: ".$Hasta."</caption>";
echo "<th>Vehiculo</th><th>Cantidad</th><th>Unidad</th><th>Combustible</th>";
$Datos1="SELECT *,SUM(Cantidad)as Total FROM Combustible WHERE Fecha>='".$_POST[fechadesde_t]."' AND Fecha<='".$_POST[fechahasta_t]."' AND Vehiculo<>'' GROUP BY Vehiculo";
$Resultados1=mysql_query($Datos1);  
while($row1 = mysql_fetch_array($Resultados1)){
  $sqlbuscarvehiculo=mysql_query("SELECT * FROM Vehiculos WHERE Dominio = '$row1[Vehiculo]'");
  $Dato=mysql_fetch_array($sqlbuscarvehiculo);
  echo "<tr><td>$Dato[Marca] $Dato[Modelo] $row1[Vehiculo]</td>";
echo "<td>$row1[Total]</td>";
echo "<td>$row1[Unidad]</td>";  
echo "<td>$row1[Combustible]</td></tr>";
}
$Datos=mysql_query("SELECT SUM(Cantidad)as Total FROM Combustible WHERE Fecha>='".$_POST[fechadesde_t]."' AND Fecha<='".$_POST[fechahasta_t]."' AND Vehiculo<>''");
$Total=mysql_fetch_array($Datos);
echo "<tr><td>Total: </td><td></td><td></td><td>$Total[Total]</td></tr>";
echo "</table>";
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

 ?>