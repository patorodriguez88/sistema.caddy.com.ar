<?php
session_start();
include_once "../conexionmy.php";
if ($_SESSION['Nivel']==0){
//header("location: ../index.php");
}
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>.::REVISTAS EN LA WEB::.</title>
<link href="../css/styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
<?php 

include("../Menu/MenuUsuario.html"); 
echo "<body style='background:#F4F4F4;'>";
echo "<center>";

//include("../Menu/MenuVerde.html"); 

if ($_SESSION['Nivel']==1){
include("../Menu/MenuLogo.html"); 
}
if ($_SESSION['Nivel']==2){
include("../Menu/MenuLogo.html"); 
}
echo '<div id="contenedor-medio">';
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
echo "<hr />";
echo "<form action='' name='formulario' class='login2' method='get'>";
echo "Bienvenidos al Panel de Control";
echo "</form>";
?>
</div>
</body>
</center>
<?php
include("Menu/MenuUltimo.html");
?>
</html>