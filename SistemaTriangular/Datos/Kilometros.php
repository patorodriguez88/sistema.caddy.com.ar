<?php
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
$color='#B8C6DE';
$font='white';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
<script>
function ver(){
document.getElementById('vertr').style.display='block';
document.getElementById('vertr1').style.display='block';

}  
</script>  
<?php

echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
// echo "<div id='lateral'>"; 
// echo "</div>"; //lateral
echo  "<div id='principal'>";

if ($_POST['Buscar']=='Aceptar'){
  $Desde=$_POST['fechadesde_t'];
  $Hasta=$_POST['fechahasta_t'];	
  $Desde0=explode("-",$_POST['fechadesde_t'],3);
  $Desde1= $Desde0[2]."/".$Desde0[1]."/".$Desde0[0];
 	$Hasta0=explode("-",$_POST['fechahasta_t'],3);	
	$Hasta1= $Hasta0[2]."/".$Hasta0[1]."/".$Hasta0[0];
  }

if(!isset($Desde)){
echo "<form class='login' action='' method='post' style='float:center; width:500px;'>";
echo "<div><titulo>Resultados Kilometros</titulo></div>";	
echo "<div><hr></hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	
goto a;	
}else{
echo "<table class='login'>";
echo "<caption>Total de Combustible Cargado x Vehiculo</caption>";
echo "<caption>Desde: ".$Desde1." Hasta: ".$Hasta1."</caption>";
echo "<th>Vehiculo</th><th>Dominio</th><th>Cantidad</th><th>Ver</th>";
$Datos1="SELECT *,SUM(KilometrosRecorridos)as Total FROM Logistica INNER JOIN Vehiculos ON Logistica.Patente= Vehiculos.Dominio 
WHERE Fecha>='".$_POST[fechadesde_t]."' AND Fecha<='".$_POST[fechahasta_t]."' AND Eliminado='0' AND Aliados='0' GROUP BY Patente";
$Resultados1=mysql_query($Datos1);  
while($row1 = mysql_fetch_array($Resultados1)){
  $sqlbuscarvehiculo=mysql_query("SELECT * FROM Vehiculos WHERE Dominio = '$row1[Patente]'");
  $Dato=mysql_fetch_array($sqlbuscarvehiculo);
echo "<tr><td>$Dato[Marca] $Dato[Modelo] $row1[Vehiculo]</td><td>$Dato[Dominio]</td>";
echo "<td align='center' style='float:center'>$row1[Total] km.</td>
<td align='center'><a name='cargar' Onclick='ver()' style='display:block' class='img' href='#'><img src='../images/botones/zoom.png' width='15' height='15' border='0' style='float:center;'></a></td></tr>";
      
//       echo "<tr id='vertr' style='display:none'>";
//       echo "<th>Fecha</th><th>Kilometros</th><th>Km.Salida</th><th>Km.Regreso</th><th>Km.Recorridos</th>";
//       $SubTabla="SELECT Fecha,Kilometros,SUM(KilometrosRegreso)as kmr,SUM(KilometrosRecorridos)as Total FROM Logistica WHERE 
//       Fecha>='".$_POST[fechadesde_t]."' AND Fecha<='".$_POST[fechahasta_t]."' AND Eliminado='0' AND Patente='$Dato[Dominio]' GROUP BY Fecha";
//       $SubTablaResultado=mysql_query($SubTabla);  
//       while($row = mysql_fetch_array($SubTablaResultado)){
//       echo "<tr id='vert1' style='display:none'>";
//       echo "<td>$row[Fecha]</td><td>$row[kmr]</td><td>$row[Total]</td>";
//       echo "</tr>";
//       }   
// echo "</tr>";
}       
$Datos=mysql_query("SELECT SUM(KilometrosRecorridos)as Total FROM Logistica 
INNER JOIN Vehiculos ON Logistica.Patente= Vehiculos.Dominio 
WHERE Fecha>='".$_POST[fechadesde_t]."' AND Fecha<='".$_POST[fechahasta_t]."' AND Aliados='0' AND Eliminado='0'");
$Total=mysql_fetch_array($Datos);
echo "<th></th><th></th><th>Total: ".number_format($Total[Total],0,',','.')." km.</th><th></th>";
echo "</table>";
 }
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
 ?>