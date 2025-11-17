<?php
session_start();
include("../ConexionBD.php");

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
		<meta charset="utf-8">
		<title></title>
		<link href="../css/styleMenuSistema.css" media="screen" rel="stylesheet" type="text/css" />
		<link href="../css/iconic.css" media="screen" rel="stylesheet" type="text/css" />
		<script src="../js/prefix-free.js"></script>
	</head>
<?
//	$NombreEmpleado=$_SESSION['NombreEmpleado'];
//	$id=$_SESSION['idEmpleado'];
$Empresa=$_SESSION['NombreClienteA'];
$Cuit=$_SESSION['NCliente'];	
$dato=$_SESSION['NdeCliente'];
$sql="SELECT id FROM Clientes WHERE Cuit='$Cuit'";
$estructura= mysql_query($sql);
while ($row = mysql_fetch_row($estructura)){	
$_SESSION['IdCliente']=$row[0];
}	
	
//if ($_SESSION['Nivel']==4){ //Administradores con acceso a empleados
echo "<form class='login' action='' method='get' style='width:150px;float:left;padding:15px;'>";
echo "<div class='tbHeader3'>$Empresa</div>";

echo "<div class='tbHeader3'>$dato</div>";
	//print $NombreEmpleado;
// echo "<input class='submit' name='MenuPestana' type='submit' value='Cuenta Corriente' style='float:left;width:150px;padding:10px;'>";
echo "<input class='submit' name='MenuPestana' type='submit' value='Modificar' style='float:left;width:150px;padding:10px;'>";
// echo "<input class='submit' name='MenuPestana' type='submit' value='Cargar Cobranza'  style='float:left;width:150px;padding:10px;'>";
// echo "<input class='submit' name='MenuPestana' type='submit' value='Imprimir' style='float:left;width:150px;padding:10px;'>";
 /*echo "<input class='submit' name='MenuPestana' type='submit' value='Sanciones'  style='float:left;width:150px;padding:10px;'>";
	echo "<input class='submit' name='MenuPestana' type='submit' value='Prestamos'  style='float:left;width:150px;padding:10px;'>";		
	echo "<input class='submit' name='MenuPestana' type='submit' value='Vacaciones'  style='float:left;width:150px;padding:10px;'>";
*/
// echo "</div>";
echo "</form>";	

if ($_GET['MenuPestana']=='Cuenta Corriente'){
	header('location:Clientes.php?CtaCte=Si');
}elseif($_GET['MenuPestana']=='Modificar'){
	header('location:Clientes.php?id=Modificar');
}elseif($_GET['MenuPestana']=='Cargar Envios'){
	header('location:Reposiciones.php');
}elseif($_GET['MenuPestana']=='Prestamos'){
	header('location:Prestamos.php');
}elseif($_GET['MenuPestana']=='Vacaciones'){
	header('location:Vacaciones.php');
}
