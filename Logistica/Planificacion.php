<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.triangularlogistica.com.ar/SistemaTriangular/index.php");
}

# definimos los valores iniciales para nuestro calendario
if($_GET[m]==''){
$month=date("n");
}else{
$month=$_GET[m];
}
if($_GET[y]==''){
$year=date("Y");
}else{
$year=$_GET[y];
}

$diaActual=date("j");
$mesactual=date("n");
 
# Obtenemos el dia de la semana del primer dia
# Devuelve 0 para domingo, 6 para sabado
$diaSemana=date("w",mktime(0,0,0,$month,1,$year))+7;
# Obtenemos el ultimo dia del mes
$ultimoDiaMes=date("d",(mktime(0,0,0,$month+1,1,$year)-1));
 
$meses=array(1=>"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
"Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../js/geolocalizar.js" type="text/javascript"></script>
  </head>	
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
  
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
  
echo "</div>"; //lateral
echo  "<div id='principal'>";
?>  
<p>What fruits do you like?</p>
<ol ondragstart="dragStartHandler(event)">
 <li draggable="true" data-value="fruit-apple">Apples</li>
 <li draggable="true" data-value="fruit-orange">Oranges</li>
 <li draggable="true" data-value="fruit-pear">Pears</li>
</ol>
<script>
  var internalDNDType = 'text/x-example'; // set this to something specific to your site
  function dragStartHandler(event) {
    if (event.target instanceof HTMLLIElement) {
      // use the element's data-value="" attribute as the value to be moving:
      event.dataTransfer.setData(internalDNDType, event.target.dataset.value);
      event.dataTransfer.effectAllowed = 'move'; // only allow moves
    } else {
      event.preventDefault(); // don't allow selection to be dragged
    }
  }
</script>  
  
 <? 
  
  
echo "</div>";//cuerpo
echo "</div>";//lateral
echo "</div>";//principal
  
  
  
  ?> 
<body>
</body>
</html>