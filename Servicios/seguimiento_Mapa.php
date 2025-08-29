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
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!--<script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>-->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../ajax.js"></script>
<link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" />
</head>
  <!-- <body style="background:> -->
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php");
include("../Alertas/alertas.html");   
  
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralSeguimiento.php");   
echo "</div>"; //lateral
echo  "<div id='principal'>";
//---------------------------------------DESDE ACA AGREGAR CLIENTES------------------------
include('seguimiento_mapa.html');  
goto a;
// }
a:  
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>