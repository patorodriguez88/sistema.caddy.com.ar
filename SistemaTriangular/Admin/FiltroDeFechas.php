<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralContabilidad.php"); 	
echo "</div>"; //lateral
echo  "<div id='principal'>";
  
echo "<form class='login' action='' method='POST' style='float:center; width:500px;'>";
echo "<div><titulo>Filtro de Fechas</titulo></div>";	
echo "<div><hr></ hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>No Operativo:</label><input name='NoOper' size='20' type='checkbox' value='1' style='float:right'/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	
	
if ($_POST['Buscar']=='Aceptar'){
$Desde=$_POST['fechadesde_t'];	
$Hasta=$_POST['fechahasta_t'];
  if($_POST['NoOper']==1){
  ?>
    <script>window.open('http://www.caddy.com.ar/SistemaTriangular/Admin/Informes/SumasySaldosNoOperpdf.php?Desde=<? echo $Desde;?>&Hasta=<? echo $Hasta;?>');</script>
    <?php
  }else{
  ?>
    <script>window.open('http://www.caddy.com.ar/SistemaTriangular/Admin/Informes/SumasySaldospdf.php?Desde=<? echo $Desde;?>&Hasta=<? echo $Hasta;?>');</script>
    <?php
  }
}
goto a;
a:

echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "<div id='pie'>"; 
echo "</div>"; //pie
echo "</div>";  //contenedor
  
ob_end_flush();
?>
</div>
</body>
</center>