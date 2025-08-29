<?php
session_start();
header('location:https://www.caddy.com.ar/AppRecorridos/hdr');

unset($_SESSION['Nivel']);
$_POST['user']='';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html style="font-family: Arial, Helvetica, sans-serif;
	-webkit-text-size-adjust: none;
	overflow-y: hidden;
	height: 100%;">
	<head>
	<title>Caddy Yo lo llevo!</title>
    <link rel="stylesheet" href="css/style.css" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
		</head>

	<body style="font-family: Arial, Helvetica, sans-serif;
	-webkit-text-size-adjust: none;
	overflow-y: hidden;
	height: 100%;">
<?
include("MenuSmartphone/MenuLogo.html");
?>

		<!----DESDE ACA---->
	
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
    }
}
echo "<div class='contact-form'>";
echo "<table width='90%' style='margin-top:15px'>";
echo "<th><b>PANEL DE INGRESO</b></th>";
echo "<th<hr /></th></table>";
echo "<form action='PrimerPaso.php?Usuario=$usuario' method='POST' class='login' style='width:420px;margin-bottom:3px; margin-top:30px;'>";

//IMGRESO--------------------------------------------------------------------------------------
echo "<div>".$titulo."</div>";
if ($_SESSION['CuentaError']>=10){
echo "<div class='error'><p>Ha superado los intentos de ingreso permitidos, por favor solicite recordar su Password, o comuniquese con nosotros.</p></div>";
}else{
echo "<div style='margin-left:10px;float:center;width:70%'>";
echo "<div><input name='user' type='text' style='height:30px;' placeholder='Usuario'></div>";	
echo "<div><input name='password' type='password' style='height:30px;' placeholder='Password'></span></div>";
echo "<div><input name='login' type='submit'  style='float:right'value='login'></div>";
echo "<div class='error'>$ErrorIngreso</div>";
echo "</div>";
echo "</form>";
$_SESSION['ERROR']=0;
}
?>