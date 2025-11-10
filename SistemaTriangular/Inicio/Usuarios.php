<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.triangularlogistica.com.ar");
}
// print $_SESSION['NCliente'];
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<?php 
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";
 
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
				$sql="UPDATE usuarios SET PASSWORD='$Nuevopass' WHERE id='$Usuario'";
				mysql_query($sql);	
				header("location:../../iniciosesion.php");
				}else{
				$error="#Error: Los nuevos password no coinciden";
				}
		}else{
			$error="#Error: El password actual esta mal escrito";	
		}
}

a:			
echo "<form class='login' method='post'>";
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
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

ob_end_flush();	
?> 
</div>
</body>
</center>
</html>