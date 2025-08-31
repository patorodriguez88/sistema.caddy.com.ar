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
$Dominio=$_GET['Dominio'];
date_default_timezone_set('Chile/Continental');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script> -->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<!-- <link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" /> -->
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<!-- <script src="ajax.js"></script> -->
	</head>	
  <body>
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MLRecorridos.php"); 
echo "<form class='login' style='margin-top:10px;width:85%;' method='POST'>";
echo "<div><titulo style='font-size:15px'>Seleccione Recorridos</titulo></div>";
echo "<div><b style='font-style: oblique;font-size:10px'>Cmd + Shift (Multi Select)</b></div>";
echo "<div><select multiple  style='width:170px;font-size:13;height:410px;margin-top:0px;' size='15' name='Recorridos[]'>";
$sql=mysql_query("SELECT Recorrido FROM Logistica WHERE Estado IN('Cargada','Alta') AND Eliminado='0' GROUP BY Recorrido ");
$selected=$_POST[Recorridos];
// $sele='selected';    
$i=0;
while($row=mysql_fetch_array($sql)){
    if($selected[$i]==$row[Recorrido]){
    $sele[$i]='selected';  
    }else{
    $sele[$i]='';  
    }  
  
echo "<option style='padding:3%' value='$row[Recorrido]' $sele[$i]>Recorrido $row[Recorrido]</option>";  
$i++;
}    
echo "</select></div>";
echo "<div><input type='submit' value='Abrir Mapa' name='vermapa'></div>";
    echo "</form>"; 
    
echo "</div>"; //lateral
echo  "<div id='principal'>";
    
//-----------------------------------------------DESDE ACA VER ORDENES---------------------------
if($_POST[vermapa]=='Abrir Mapa'){
  if($_POST[Recorridos]<>''){
    $_SESSION[recorridos]=$_POST[Recorridos];
  include("maparecorridos.html"); 
  
  }else{
  ?> 
 <script>alertify.error("No hay Recorrido Seleccionado");</script> 
  <?  
  }  
}
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
    
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>