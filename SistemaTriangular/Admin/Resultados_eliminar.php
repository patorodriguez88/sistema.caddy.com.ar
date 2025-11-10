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
 document.getElementById('row').style.display='table-row';
 document.getElementById('cell').style.display='table-cell';

  }  
</script>  
<?php

echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";

$Desde=$_POST['fechadesde_t'];
$Hasta=$_POST['fechahasta_t'];	

if(!isset($Desde)){
echo "<form class='login' action='' method='post' style='float:center; width:500px'>";
echo "<div><titulo>Resultados</titulo></div>";	
echo "<div><hr></hr></div>";  
echo "<div><label>Desde:</label><input name='fechadesde_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Hasta:</label><input name='fechahasta_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar'></label></div>";
echo "</form>";	
goto a;
}	
	if ($_POST['Buscar']=='Aceptar'){
	$Desde=$_POST['fechadesde_t'];
  $Desde1=explode("-",$Desde,3);
  $Desde2=$Desde1[2]."/".$Desde1[1]."/".$Desde1[0];  
    
	$Hasta=$_POST['fechahasta_t'];	
  $Hasta1=explode("-",$Hasta,3);
  $Hasta2=$Hasta1[2]."/".$Hasta1[1]."/".$Hasta1[0];  

  }

echo "<table class='login' border='0' vspace='15px' style='width:100%;float:center;margin-top:15px'>";
echo "<caption>Resultados Importes Desde $Desde2 Hasta $Hasta2</caption>";
echo "<th>Cuenta</th>";
echo "<th>Nombre Cuenta</th>";
echo "<th>R-</th>";
echo "<th>R+</th>";
$sqlResultadoNegativo=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE TipoCuenta='R-'");
while($row=mysql_fetch_array($sqlResultadoNegativo)){
$sqlTesoreria=mysql_query("SELECT SUM(Debe)as Total,NombreCuenta,Cuenta FROM Tesoreria WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Cuenta='$row[Cuenta]' AND Eliminado=0");
  while($row = mysql_fetch_array($sqlTesoreria)){
    if($row[Total]<>0){
  $Total=number_format($row[Total],2,",",".");
  echo "<tr align='left' font-size:'13px' style='background:white;color:black'>
  <td>$row[Cuenta]</td>  
  <td>$row[NombreCuenta]</td><td>$ ".$Total."</td>";
  echo "<td></td></tr>";

    }
  }

}

$sqlResultadoPositivo=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE TipoCuenta='R+'");
while($row=mysql_fetch_array($sqlResultadoPositivo)){
$sqlTesoreria=mysql_query("SELECT SUM(Haber)as Total,NombreCuenta,Cuenta FROM Tesoreria WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Cuenta='$row[Cuenta]' AND Eliminado=0");
  while($row = mysql_fetch_array($sqlTesoreria)){
    if($row[Total]<>0){
  $Total=number_format($row[Total],2,",",".");
  echo "<tr align='left' font-size:'13px' style='background:white;color:black'>";
      echo "<td>$row[Cuenta]</td><td>$row[NombreCuenta]</td><td></td><td>$ ".$Total."<a onclick='ver()'>  + </td>";
      echo "</tr>";
    }
  }
}
   $sql=mysql_query("SELECT *,SUM(Haber)as Haber FROM Ctasctes
    WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Debe<>0 GROUP BY NumeroFactura");
    while($datosql=mysql_fetch_array($sql)){
    echo "<tr style='display:none' id='row'>";
    $debef=number_format($datosql[Debe]/1.21,2,",",".");  
    echo "<tr><td>$datosql[RazonSocial]</td><td>$datosql[ComprobanteF] $datosql[NumeroF]</td><td></td><td>$ $debef</td>";
    echo "</tr>";
    }    
    echo "</tr>";
    
  
  
// echo "</table>";
// echo "<table class='login' border='0' vspace='15px' style='width:800px;float:center;'>";
$sqlRP=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE TipoCuenta='R-'");
while($row=mysql_fetch_array($sqlRP)){
$sqlTesoreria=mysql_query("SELECT SUM(Debe)as TotalRN FROM Tesoreria WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Cuenta='$row[Cuenta]' AND Eliminado=0");
  while($row = mysql_fetch_array($sqlTesoreria)){
  $TotalRN+=$row[TotalRN];
  }
}
$TotalRNf=number_format($TotalRN,2,",",".");
  echo "<tr><td></td><td>Resultados</td>";
  echo "<td>$ $TotalRNf</td>";

$sqlRP=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE TipoCuenta='R+'");
while($row=mysql_fetch_array($sqlRP)){
$sqlTesoreria=mysql_query("SELECT SUM(Haber)as TotalRP FROM Tesoreria WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Cuenta='$row[Cuenta]' AND Eliminado=0");
  while($row = mysql_fetch_array($sqlTesoreria)){
  $TotalRP+=$row[TotalRP];
  }
}
$TotalRPf=number_format($TotalRP,2,",",".");
  echo "<td>$ $TotalRPf</td>";

$Resultado= number_format($Total,2,",",".");
echo "</table>";

echo "<table class='login' border='0' vspace='15px' style='width:100%;float:center;'>";
$Saldo=number_format($TotalRP-$TotalRN,2,",",".");
echo "<th>Saldo:</th>";
  echo "<th>$ $Saldo</th>";
echo "</table>";

echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
a:
 ?>