<?php
session_start();
include_once "../conexionmy.php";
header("location:smartphone/iniciosesion.php");
?>

<!--
Author: W3layouts
Author URL: http://w3layouts.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Mobilestore website for high end mobiles,like samsung nokia mobile website templates for free | Home :: w3layouts</title>
		<link href="css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="keywords" content="Mobilestore iphone web template, Andriod web template, Smartphone web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
	</head>
	<body>
		<div class="top-header">
			<div class="wrap">
		<!----start-logo---->
			<div class="logo">
				<a href="index.html"><img src="images/logo.png" title="logo" /></a>
			</div>
		</div>
		<div class="clear"> </div>
		</div>
		</div>
<!--DESDE ACA-->

<script>
function color(x){
	x.style.background="yellow";
}
function colorno(x){
	x.style.background="white";
}
</script>
<?

z:
if ($_GET['Submit']=='Crear Username'){

		if ($_SESSION['ERROR']==1){
		$Error="Debe ingresar un Usuario";
		$ErrorIngreso='Error 1';
		}elseif($_SESSION['ERROR']==2){
		$Error="Debe Ingresar un Email";		
		$ErrorIngreso='Error 2';
		}elseif($_SESSION['ERROR']==3){
		$Error="Debe Ingresar una clave";	
		$ErrorIngreso='Error 3';
		}elseif($_SESSION['ERROR']==4){
		$Error="Debe Ingresar un Password";	
		$ErrorIngreso='Error 4';
		}elseif($_SESSION['ERROR']==5){
		$Error="Debe Ingresar una Confirmacion de Password";	
		$ErrorIngreso='Error 5';
		}elseif($_SESSION['ERROR']==6){
		$Error="La clave no coincide con el D.N.I, consulte en ";	
		$ErrorIngreso='Error 6';
		}elseif($_SESSION['ERROR']==7){
		$Error="No coinciden las contraseᡳ";	
		$ErrorIngreso='Error 7';
		}elseif($_SESSION['ERROR']==8){
		$Error="La clave no coincide con el D.N.I, consulte en ";	
		$ErrorIngreso='Error 8';
		}
		
		if ($_SESSION['ERROR']>0){
		$us=$_SESSION['usu'];
		$ap=$_SESSION['ape'];
		$mail=$_SESSION['mail'];
		$clav=$_SESSION['clav'];
		
echo "<tr><td>";
echo "<form action='insert.php' method='post' class='login' style='margin-bottom:15px; margin-top:50px; width:350px'>";
echo "<div><br>Crear Usuario</br></div>";

echo "<div><label>Nombre</label><input name='user_t' type='text' maxlength='15' value='$us' onfocus='color(this)' onblur='colorno(this)'></div>";
echo "<div><label>Apellido</label><input name='apell_t' type='text' maxlength='15' value='$ap' onfocus='color(this)' onblur='colorno(this)'></div>";
//---------------------------------------------------------------------------------------------
echo "<div><label>Localidad</label><select name='ciudad_t' style='width:150px;' size='1' selected='$sel'>";
$Grupo="SELECT Localidad FROM Localidades GROUP BY Localidad;";
$estructura= mysql_query($Grupo);
while ($row = mysql_fetch_row($estructura)){
$GrupoS=$row[0];
echo "<option value='".$GrupoS."'>".$GrupoS."</option>";
}
echo "</select></div>";
//----------
echo "<span class='textfieldRequiredMsg'>Dato necesario</br></span></span>";	
echo "<div><label>E-mail</label><input name='Mail_t' type='text' maxlength='30' value='$mail'onfocus='color(this)' onblur='colorno(this)'>";	
echo "<span id='sprytextfield1'></div>";

echo "<div><label>Usuario</label><input name='Usuario_t' type='text' maxlength='30' value='$Usuario' onfocus='color(this)' onblur='colorno(this)'></div>";	

echo "<div><label>Password</label><input name='password_t' type='password' maxlength='10' onfocus='color(this)' onblur='colorno(this)'>	</div>";
echo "<div><label>Confirm Password</label><input name='rpassword' type='password' maxlength='10' onfocus='color(this)' onblur='colorno(this)'></div>";
echo "<div><input name='login' type='submit' value='login'></div>";
echo "<div class='error'>$Error ($ErrorIngreso)</div>";
echo "</form>";
		$_SESSION['Cargar']=8;
		}else{
echo "<tr><td>";

echo "<table class='notificaciones'><tr><td>Nota: Formulario solo para clientes particulares, los kioscos deben solicitar su Usuario y Contraseᡠdirectamente a su distribuidor.</td></tr></table>";

echo "<form action='insert.php' method='post' class='login' style='margin-bottom:3px; margin-top:50px; width:350px'>";
//echo "<span class='textfieldRequiredMsg'>Dato necesario</span>";


echo "<div>Crear Usuario</div>";
		
echo "<div><label>Nombre</label><input name='user_t' type='text' onfocus='color(this)' onblur='colorno(this)' ></div>";
echo "<div><label>Apellido</label><input name='apell_t' type='text' onfocus='color(this)' onblur='colorno(this)' ></div>";

//--------------------Provincia
echo "<div><label>Provincia/Ciudad</label><select name='provincia_t' style='width:180px;' size='1' selected='$sel' id='cont' onChange='load(this.value)'>";
$Grupo="SELECT Cliente,Provincia FROM Localidades GROUP BY Provincia;";
$estructura= mysql_query($Grupo);
while ($row = mysql_fetch_row($estructura)){
$Dist=$row[0];
$GrupoS=$row[1];
echo "<option value='".$Dist."'>".$GrupoS."</option>";
}
echo "</select></div>";
echo "<div id='myDiv'></div>";

echo "<div><label>Mail</label><input name='Mail_t' type='text' maxlength='50' value='$doc'onfocus='color(this)' onblur='colorno(this)'>";	
		echo "<span id='sprytextfield1'></div>";

		echo "<div><label>Usuario</label><input name='Usuario_t' type='text' maxlength='30' value='$Usuario' onfocus='color(this)' onblur='colorno(this)'>";	
		echo "<span id='sprytextfield1'></div>";
		
		echo "<div><label>Constraseña</label><input name='password_t' type='password' onfocus='color(this)' onblur='colorno(this)'></div>";
		echo "<div><label>Confirmar Contraseña </label><input name='rpassword' type='password' onfocus='color(this)' onblur='colorno(this)'></div>";
		echo "<div><input name='login' type='submit' value='Crear'></div>";
		echo "<div class='error'>$Error $ErrorIngreso</div>";
		echo "</form>";
		$_SESSION['Cargar']=8;
		
		echo "<form class='' style='margin-bottom:0px;width:390px'>";
		echo "<div><input class='boton' type='Submit'  name='1' value='Olvide mi Password' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float:left; font-weight: bold; padding: 4px 8px; width:195px;'></div></td>";
		echo "<td><div><input class='boton' type='Submit'  name='Submit' value='Crear Username' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float: rigth; font-weight: bold; padding: 4px 8px; width:195px'></div></td></tr>";
		
		
		}}
elseif ($_GET['1']=='Olvide mi Password'){

	echo "<tr><td>";
	echo "<form action='recordarpasswordmsg.php' method='post' class='login' name='contacto' style='margin-bottom:3px; margin-top:50px; width:390px'>";
	echo "<div>Recordar Password</div>";
	echo "<div><label>Username</label><input name='user_t' type='text' id='usuario' value='introducir e-mail' style='width:200px'></div>";
	echo "<div><input name='login' type='submit' value='login'></div>";
	echo "</form>";
	
	echo "<form class='' style='margin-bottom:0px;width:390px'>";
echo "<div><input class='boton' type='Submit'  name='1' value='Olvide mi Password' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float:left; font-weight: bold; padding: 4px 8px; width:195px;'></div></td>";
echo "<td><div><input class='boton' type='Submit'  name='Submit' value='Crear Username' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float: rigth; font-weight: bold; padding: 4px 8px; width:195px'></div></td></tr>";
}
else{
$CuentaError=$_SESSION['CuentaError'];
$ErrorIngreso=$_SESSION['ErrIngreso'];
echo "<tr><td>";
echo "<table width='450px'><tr><td>PANEL DE INGRESO</td></tr>";
echo "<tr><td><hr /></td></tr></table>";
echo "<div class='contact-form'>";
	echo "<form action='ProcesoPago/PrimerPaso.php?Usuario=$usuario' method='POST' class='login' style='width:420px;margin-bottom:3px; margin-top:30px;background:".$colorfondo.";'>";

//IMGRESO--------------------------------------------------------------------------------------
echo "<div>".$titulo."</div>";
if ($_SESSION['CuentaError']>=10){
echo "<div class='error'><p>Ha superado los intentos de ingreso permitidos, por favor solicite recordar su Password, o comuniquese con nosotros.</p></div>";
}else{
echo "<div>";

echo "<div><span><label>Usuario</label></span><span><input name='user' type='text'></span></div>";	
echo "<div><span><label>Password</label></span><span><input name='password' type='password'></span></div>";
echo "<div><input name='login' type='submit' value='login'></div>";
echo "<div class='error'>$ErrorIngreso</div>";
echo "</form>";

echo "<form class='' style='margin-bottom:0px;width:450px'>";
echo "<tr><td><div><input class='boton' type='Submit' name='1' value='Olvide mi Password' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float:left; font-weight: bold; padding: 4px 8px;width:220px;'></td>";
echo "<td><input class='boton' type='Submit'  name='Submit' value='Crear Username' style='font-size:12px; background: none repeat scroll 0 0 #DEDEDE;border: 1px solid #C6C6C6; float: rigth; font-weight: bold; padding: 4px 8px;width:220px;'></div></td></tr>";
$_SESSION['ERROR']=0;
}
}
?>
</table>
</div>
</center>
</html>
<!--HASTA ACA-->
			
				
	
				<div class="clear"> </div>
		    </div>
		<div class="footer">
			<div class="content">
			<div class="wrap">
			<div class="section group">
				<div class="col_1_of_4 span_1_of_4">
					<h3>Our Info</h3>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
				</div>
				<div class="col_1_of_4 span_1_of_4 footer-lastgrid">
					<h3>News-Letter</h3>
					<input type="text"><input type="submit" value="go" />
					<h3>Fallow Us:</h3>
					 <ul>
					 	<li><a href="#"><img src="mobile/images/twitter.png" title="twitter" />Twitter</a></li>
					 	<li><a href="#"><img src="mobile/images/facebook.png" title="Facebook" />Facebook</a></li>
					 	<li><a href="#"><img src="mobile/images/rss.png" title="Rss" />Rss</a></li>
					 </ul>
				</div>
			</div>
		</div>
		
		<div class="clear"> </div>
		<div class="wrap">
		<div class="copy-right">
			<p>Mobilestore  &#169	 All Rights Reserved | Design By <a href="http://w3layouts.com/">W3Layouts</a></p>
		</div>
		</div>
		</div>
		</div>
	</body>
</html>

