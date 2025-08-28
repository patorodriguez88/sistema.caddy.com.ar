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
<script src="../Highcharts-6.0.2/code/highcharts.js"></script>
<script src="../Highcharts-6.0.2/code/modules/series-label.js"></script>
<script src="../Highcharts-6.0.2/code/modules/exporting.js"></script>
</head>
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>";
include('Menu/MenuLateral.php');  
echo "</div>"; //lateral
echo  "<div id='principal'>";

$Year=$_POST['ano_t'];
$Desde=$_POST['fechadesde_t'];
$Hasta=$_POST['fechahasta_t'];	

if(!isset($Desde)){
echo "<form class='Caddy' action='' method='post' style='float:center; width:500px'>";
echo "<div><titulo>Resultados</titulo></div>";	
echo "<div><hr></hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	
goto a;
}	
echo "<table class='login' border='0' vspace='15px' style='width:100%;float:center;margin-top:15px'>";
echo "<caption>Resultados Desde el $Desde hasta el $Hasta</caption>";
echo "<th>Cliente</th>";
echo "<th>Cantidad</th>";
echo "<th>Entregados</th>"; 
echo "<th>No Entregados</th>";  
echo "<th>Total</th>";
$sqlResultadoNegativo=mysql_query("SELECT RazonSocial,SUM(Cantidad)as Total,SUM(Debe)as TotalImporte,SUM(Entregado)as Entregado FROM TransClientes 
WHERE Fecha>='$Desde'AND Fecha<='$Hasta' AND Eliminado=0 AND Debe>0 GROUP BY (RazonSocial) ORDER BY SUM(Debe) DESC");
echo "<tr align='left' font-size:'13px' style='background:white;color:black'>";
while($row=mysql_fetch_array($sqlResultadoNegativo)){
$entregados=$entregados+$row[Entregado];
$NoEntregado=$row[Total]-$row[Entregado];  
echo "<td>$row[RazonSocial]</td><td>$row[Total]</td><td>$row[Entregado]</td><td>$NoEntregado</td><td>$ $row[TotalImporte]</td>";  
echo "</tr>";  
$total=$total+$row[TotalImporte];
$envios=$envios+$row[Total];  
}
$totalf=number_format(($total),2,".",",");

echo "<tfoot>";
echo "<tr>";
echo "<td>Totales:</td><td>Total Envios: $envios</td><td>Entregados: $entregados</td><td>No Entregados: $nentregados</td><td>Total: $ $totalf</td>";  
echo "</tr>";
echo "</tfoot>";  
echo "</table>";

echo "<table class='login' border='0' vspace='15px' style='width:100%;float:center;margin-top:15px'>";
echo "<caption>Resultados Desde el $Desde hasta el $Hasta</caption>";
echo "<th>Cliente</th>";
echo "<th>Cantidad</th>";
echo "<th>Entregados</th>";  
echo "<th>No Entregados</th>";  
echo "<th>Total</th>";
  $sqlResultadoNegativo=mysql_query("SELECT RazonSocial,SUM(Cantidad)as Total,SUM(Debe)as TotalImporte,SUM(Entregado)as Entregado FROM TransClientes 
  WHERE Fecha>='$Desde'AND Fecha<='$Hasta' AND Eliminado=0 AND Debe=0 AND Haber=0 GROUP BY (RazonSocial) ORDER BY SUM(Debe) DESC");
  echo "<tr align='left' font-size:'13px' style='background:white;color:black'>";
  while($row=mysql_fetch_array($sqlResultadoNegativo)){
  $entregados1=$entregados1+$row[Entregado];  
  $NoEntregado1=$row[Total]-$row[Entregado];  
  echo "<td>$row[RazonSocial]</td><td>$row[Total]</td><td>$row[Entregado]</td><td>$NoEntregado1</td><td>$ $row[TotalImporte]</td>";  
  echo "</tr>";  
  $total1=$total1+$row[TotalImporte];
  $envios1=$envios1+$row[Total];  
  $nentregados1=$nentregados1+$row[Total]-$row[Entregado];  
 }
$totalf1=number_format(($total1),2,".",",");

  echo "<tfoot>";
  echo "<tr>";
  echo "<td>Totales:</td><td>Total Envios: $envios1</td><td>Entregados: $entregados1</td><td>No Entregados: $nentregados1</td><td>Total: $ $totalf1</td>";  
  echo "</tr>";
  echo "</tfoot>";  
  echo "</table>";

  echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
a:
 ?>