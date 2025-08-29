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
<script>
    function ver(){
    document.getElementById('desde').style.display='block';
    document.getElementById('hasta').style.display='block';
    
    }
</script>
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

if (($_POST['hasta_t']=='')||($_POST['desde_t']=='')){
echo "<form class='login' action='' method='POST' style='width:600px;'><div>";
echo "<div id='desde' style='display:block'><label>Fechas Desde:</label><input type='date' name='desde_t'></div>";
echo "<div id='hasta' style='display:block'><label>Fechas Hasta:</label><input type='date' name='hasta_t'></div>";
echo "<div><input type='submit' name='Ver' value='Aceptar'></div>";
echo "</form>";
}
if($_POST[Ver]=='Aceptar'){  
$BuscarDatos=mysql_query("SELECT Usuario,COUNT(id)as Total FROM Seguimiento WHERE Entregado= 1 AND Fecha>='$_POST[desde_t]' AND Fecha<='$_POST[hasta_t]' group by Usuario");
$numfilas = mysql_num_rows($BuscarDatos);
  if($numfilas==0){
  ?>
  <script>
  alertify.error("No existen datos para la consulta");
  </script>
  <?  
  goto a;
}	
$color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';

echo "<div style='height:590px;overflow:auto'>";
echo "<table class='login' style='whidth:200px'>";
echo "<caption>Puntos Caddy</caption>";
echo "<th>Empleado</th>";
echo "<th>Total Entregado</th>";  

  while($row = mysql_fetch_array($BuscarDatos)){
	if($numfilas%2 == 0){
	echo "<tr style='background: #f2f2f2;' >";
	}else{
	echo "<tr style='background:$color1;' >";
	}	
echo "<td>$row[Usuario]</td>";
echo "<td>$row[Total]</td>";

$sqlHojaDeRuta=mysql_query("SELECT Fecha,Hora FROM HojaDeRuta WHERE Seguimiento = '$row[CodigoSeguimiento]'");
$resultHojaDeRuta=mysql_fetch_array($sqlHojaDeRuta);
echo "<td>$resultHojaDeRuta[Hora]</td>";
$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora FROM Seguimiento WHERE CodigoSeguimiento = '$row[CodigoSeguimiento]' AND id=(SELECT MAX(id)FROM Seguimiento 
WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
if($resultHojaDeRuta[Fecha]<>$resultSeguimiento[Fecha]){
$color3=red;  

}else{

  $color3='';
}
 echo "<td style='color:$color3'>$resultSeguimiento[Hora]</td>";
$HoraSeguimiento= strtotime($resultSeguimiento[Hora]);    
$HoraProgramada=strtotime($resultHojaDeRuta[Hora]);    
$resta = $HoraSeguimiento-$HoraProgramada;
echo "<td></td>";	    
echo "<input type='hidden' id='waypoints' value='$row[ClienteDestino]'>";	
$numfilas++; 	
}
echo "</table>";
echo "</div>";  
goto a;
}
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