<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.triangularlogistica.com.ar/SistemaTriangular/index.php");
}
$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$color2='white'; 
$font2='black';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script> -->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" />
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<!-- <script src="ajax.js"></script> -->
	</head>	
<?
echo "<body style='background:".$_SESSION['ColorFondo']."'>";

include("../Menu/MenuGestion.php"); 
echo "<center>";
	
//-------------------------------------DESDE ACA TABLA Empleados-------------------------------------
	//-------------------------------------------------DESDE ACA MODIFICAR CLIENTES----------------------------------	

$IdEmpleado=$_GET['IdEmpleado'];	
$sql="SELECT * FROM Empleados ";
$estructura= mysql_query($sql);
while ($file = mysql_fetch_array($estructura)){
echo "<form class='login' action='' method='get' style='width:500px'>";
echo "<div><label style='float:center;color:red;font-size:22px'>Agregar Nuevo Empleado</label></div>";
echo "<div><label>Id:</label><input name='codigo_t' type='text' value='$IdEmpleado' style='width:300px;'/></div>";
echo "<div><label>Nombre Completo:</label><input name='nombrecompleto_t' type='text' value='".$file[NombreCompleto]."' style='width:300px;'/></div>";
echo "<div><label>Domicilio:</label><input name='domicilio_t' type='text' value='".$file[Domicilio]."' style='width:300px;'/></div>";
echo "<div><label>Codigo Postal:</label><input name='codigopostal_t' type='text' value='".$file[CodigoPostal]."' style='width:300px;'/></div>";
echo "<div><label>Localidad:</label><input name='localidad_t' type='text' value='".$file[Localidad]."' style='width:300px;'/></div>";
echo "<div><label>Provincia:</label><input name='provincia_t' type='text' value='".$file[Provincia]."' style='width:300px;'/></div>";
echo "<div><label>Telefono:</label><input name='telefono_t' type='text' value='".$file[Telefono]."' style='width:300px;'/></div>";
echo "<div><label>Fecha Nacimiento:</label><input name='fechanacimiento_t' type='text' value='".$file[FechaNacimiento]."' style='width:300px;'/></div>";
echo "<div><label>Fecha Ingreso:</label><input name='fechaingreso_t' type='text' value='".$file[FechaIngreso]."' style='width:300px;'/></div>";
echo "<div><label>Dni:</label><input name='dni_t' type='text' value='".$file[Dni]."' style='width:300px;'/></div>";
echo "<div><label>Vencimiento Licencia:</label><input name='venlicencia_t' type='date' value='".$file[VencimientoLicencia]."' style='width:300px;'/></div>";
echo "<div><label>Puesto Laboral:</label><select name='puestolaboral_t' value='".$file[Puesto]."' style='float:right;width:310px;' size='0'>";
echo "<option value='Administracion'>Administracion</option>";
echo "<option value='Deposito'>Deposito</option>";
echo "<option value='Transportista'>Transportista</option>";
echo "</select></div>";
$Grupo="SELECT Nivel,NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta>'112500' AND Cuenta<='112999' ORDER BY NombreCuenta ASC";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Cuenta Para Anticipos:</label><select name='nombrecuenta_t' style='float:right;width:310px;' size='0'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[2]."'>".$row[1]."</option>";
	}
	echo "</select></div>";

	echo "<div><label>Observaciones:</label><input name='observaciones_t' type='text' value='".$file[Observaciones]."' style='width:300px;'/></div>";
echo "<div><input  class='submit' name='Empleados' type='submit' value='Modificar'></div></table>";
echo "</form>";
goto a;
}

	
if ($_GET['Empleados']=='Modificar'){
			
	?><script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
							<script language="JavaScript" type="text/javascript">
		function reset () {
				alertify.success("You've clicked OK");
}
	</script>
					<?
	
// echo "<a href='Clientes.php?IdEmpleado=$Codigo&Empleados=Buscar' id='confirm'>Empleado Modificado con Exito</a><br>";
// 	header("location:Clientes.php?IdEmpleado=$Codigo&Empleados=Buscar");			
}
//------------------------------------------------HASTA ACA MODIFICAR CLIENTES------------------------------------------------
a:
include_once "../Alertas/alertas.html";
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>