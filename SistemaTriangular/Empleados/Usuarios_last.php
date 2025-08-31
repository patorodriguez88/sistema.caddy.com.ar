<?php
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.caddy.com.ar");
}
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
  
      <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

     <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->


</head>
<body>  
</body>  
</html>
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralUsuarios.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";
 
if($_GET['UsuarioCreado']=='Ok'){
echo "<form class='login' style='width:500px;' method='post'>";
echo "<div><label style='color:red;font-size:22;'>Alta de Usuarios</label></div>";
echo "<div><hr></hr></div>";
echo "<div><label style='color:red;float:left;'>El usuario y el password de fueron creados con éxito.</label></div>";
echo "</form>";
 goto b; 
}
if($_GET['id']=='Ver'){
  
  
goto b;  
}
if($_GET['Cargar']=='Si'){

echo "<form class='login'  method='post'>";
echo "<div><titulo>Alta de Usuarios</titulo></div>";
echo "<div><hr></hr></div>";

$Grupo="SELECT id,NombreCompleto FROM Empleados WHERE Inactivo=0 ORDER BY NombreCompleto ASC";
$estructura= mysql_query($Grupo);
echo "<div><label>Seleccione un Nombre:</label><select name='nombre_empleado' style='float:right;width:350px;' size='0' Onchange='veo(this.value);' id='nombre_empleado'>";
while ($row = mysql_fetch_array($estructura)){
echo "<option value='".$row[id]."'>".$row[NombreCompleto]."</option>";
}
echo "</select></div>";
  
echo "<div><label>Nombre:</label><input type='text' id='nombre_t' name='nombre_t' value='".$_POST['nombre_t']."' style='width:350px' readonly></div>";
// echo "<div><label>Apellido:</label><input type='text' name='apellido_t' value='".$_POST['apellido_t']."' ></div>";
echo "<input id='id_empleado' type='hidden'>";  
echo "<div><label>Usuario:</label><input name='usuario_t' type='text' value='".$_POST['usuario_t']."' style='width:350px' readonly></div>";
echo "<div><label>Sucursal:</label><input type='text' name='sucursal_t' value='Córdoba' style='width:350px' readonly></div>";
echo "<div><label>Nivel:</label><select name='nivel_t' style='width:350px'>";
// echo "<option value='1'>Supervisor</option>";
echo "<option value='3'>Transportistas</option>";
echo "</select></div>";
echo "<div><label>Password:</label><input id='nuevopass_t' name='nuevopass_t' type='text' value='' maxlength='9' style='width:350px' readonly></div>";
echo "<div><label>Confirmar Password:</label><input name='confnuevopass_t' id='confnuevopass_t' type='text' value='' maxlength='15' style='width:350px' readonly></div>";
echo "<div><label style='color:red;float:right;'>$error</label></div>";
echo "<div><input name='CrearPass' id='CrearPass' type='Submit' value='Aceptar'></div>";
echo "</form>";

goto b;  
}

if($_GET['Modificar']=='Si'){

  goto b;  
}

$Usuario=$_SESSION['idusuario'];
$ordenar="SELECT * FROM usuarios WHERE id ='$Usuario'";	
$MuestraTrans=mysql_query($ordenar);
$fila=mysql_fetch_array($MuestraTrans);
$PassActual=$_POST['pass_t'];

if ($_POST['ModificarPass']=='Aceptar'){

		if ($_POST['pass_t']==''){
		$error="El password actual no puede estar vacio";	
		goto a;	
		}elseif($_POST['pass_t']==$fila[PASSWORD]){
				if ($_POST['nuevopass_t']==''){
				$error="El nuevo password no puede estar vacio";	
				goto a;	
				}elseif($_POST['nuevopass_t']==$_POST['confnuevopass_t']){	
				$error='';
				$Nuevopass=$_POST['nuevopass_t'];	
				$sql="UPDATE usuarios SET PASSWORD='$Nuevopass' WHERE Nombre='$Usuario'";
				mysql_query($sql);	
				header("location:../iniciosesion.php");
				}else{
				$error="#Error: Los nuevos password no coinciden";
				}
		}else{
			$error="#Error: El password actual esta mal escrito";	
		}
}

a:			
echo "<form class='login'  method='post'>";
echo "<div><titulo>Ficha de Usuarios</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><label>Nombre</label><input type='text' value='$fila[Nombre]' readonly></div>";
echo "<div><label>Usuario</label><input name='usuario_t' type='text' value='$fila[Usuario]' readonly></div>";
echo "<div><label>Sucursal</label><input name='sucursal_t' type='text' value='$fila[Sucursal]' readonly></div>";
echo "<div><titulo>Cambiar Password</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><label>Password Actual</label><input name='pass_t' type='password' value='$PassActual'></div>";
echo "<div><label>Nuevo Password</label><input name='nuevopass_t' type='password' value=''></div>";
echo "<div><label>Confirmar Nuevo Password</label><input name='confnuevopass_t' type='password' value=''></div>";
echo "<div><label style='color:red;float:right;'>$error</label></div>";
echo "<div><input name='ModificarPass' type='Submit' value='Aceptar'></div>";
echo "</form>";
b:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor


?> 
<!-- bundle -->
<script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
<script src="../hyper/dist/saas/assets/js/app.min.js"></script>


<script src="Procesos/js/funciones.js"></script>

