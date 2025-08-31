<?php
session_start();
include_once "../../conexionmy.php";
if ($_SESSION['Nivel']==""){
header("location: ../iniciosesion.php");
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Revistas en la Web</title>
			<link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
		</script>
		<link rel="stylesheet" href="smartphone/css/responsiveslides.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<link href="smartphone/css/menu.css" rel="stylesheet" type="text/css" media="all"/>
		<script type="text/javascript">window.onload = function() { w3Init(); };</script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="smartphone/js/mobile.js"></script>
<script>
function comprueba(){ 
if(numeros.sc.checked){ 
document.getElementById('oculto').style.visibility="visible"; 
document.getElementById('myDiv').style.visibility="visible"; 
}else{ 
document.getElementById('oculto').style.visibility="hidden"; 
document.getElementById('myDiv').style.visibility="hidden"; 
} 
} 
</script> 
<!--<body style="background:#F4F4F4">-->

	</head>
<body>
			<div class="top-header">
			<div class="wrap">
		<!----start-logo---->
			<div class="logo">
<!-- 				<a href="index.html"><img src="../images/logo.png" title="logo" /></a> -->
			</div>
	  	</div>
	 	</div>
<?

	 echo "<form action='' method='get'>";
		echo "<span><label style='font-size: 1.8em;'>Recorrido:</label></span>";
		echo "<span><input style='font-size: 1.8em;' type='number' name='recorrido_t'></span>";
		echo "<span><input style='float:center;width:152px;height:45px;font-size:1.8em;' type='submit' name='SelecRecorrido' value='Aceptar'></span>";
		echo "</form>";

		if ($_GET['SelecRecorrido']=='Aceptar'){
			$_SESSION['RecorridoCargaPagos']=$_GET['recorrido_t'];	
	header("location:cargaPagos.php");
		} 
?>
</body>
</html>
