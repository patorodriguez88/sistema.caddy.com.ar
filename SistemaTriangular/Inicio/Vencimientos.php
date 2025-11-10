<?php
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Estadisticas</title>
    <link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />

		<style type="text/css">
		</style>
	</head>
	<body>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="../Highcharts-6.0.2/code/highcharts.js"></script>
<script src="../Highcharts-6.0.2/code/modules/exporting.js"></script>
<link href="../css/iconic.css" media="screen" rel="stylesheet" type="text/css" />
    
    <?
    include("../Menu/MenuGestion.php");
  $sql=mysql_query("SELECT NombreCompleto,VencimientoLicencia FROM Empleados WHERE Inactivo=0 ORDER BY VencimientoLicencia ASC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:red 3px solid;width:350px;float:left;margin-left:100px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Vencimiento de Registros</label>
  <img src='../images/botones/document.png' style='width:30px;height:30px;margin-left:70px;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  $Fecha= explode("-",$Dato[VencimientoLicencia],3);
  $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 if($Dato[VencimientoLicencia]<date("Y-m-d")){
  $color="red";
  }else{
  $color="black";  
  }  
 
    echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[NombreCompleto]</label>
    <label style='float:right;color:$color';> $Fecha1</label></div>";
  }
  echo "</form>";

  //PROXIMO SERVICE
      $sql=mysql_query("SELECT Marca,Modelo,Dominio,ProximoService,Kilometros FROM Vehiculos WHERE Estado<>'Vendida' AND Aliados = 0 ORDER BY ProximoService-Kilometros ASC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:blue 3px solid;width:350px;float:left;margin-left:20px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Proximos Service</label>
   <img src='../images/botones/battery.png' style='width:30px;height:30px;float:right;'> </div>";	  
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  $KmService=$Dato[ProximoService]-$Dato[Kilometros];
  if($KmService<2000){
  $color="red";
  }else{
  $color="black";  
  }  
  
   echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[Marca] $Dato[Modelo] $Dato[Dominio]</label>
    <label style='float:right;color:$color';> $KmService km</label></div>";
  }
  echo "</form>";

  //VENCIMIENTO SEGURO
  $sql=mysql_query("SELECT Marca,Modelo,Dominio,FechaVencSeguro FROM Vehiculos WHERE Estado<>'Vendida' ORDER BY FechaVencSeguro ASC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:orange 3px solid;width:350px;float:left;margin-left:20px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Vencimiento Seguro</label>
  <img src='../images/botones/calendar.png' style='width:30px;height:30px;float:right;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  $Fecha= explode("-",$Dato[FechaVencSeguro],3);
  $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
  if($Dato[FechaVencSeguro]<date("Y-m-d")){
  $color="red";
  }else{
  $color="black";  
  }  
    echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[Marca] $Dato[Modelo] $Dato[Dominio]</label>
    <label style='float:right;color:$color'> $Fecha1</label></div>";
  }
  echo "</form>";
  
    //VENCIMIENTO ITV
  $sql=mysql_query("SELECT Marca,Modelo,Dominio,FechaVencITV FROM Vehiculos WHERE Estado<>'Vendida' ORDER BY FechaVencITV ASC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:black 3px solid;width:350px;float:left;margin-left:100px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Vencimiento I.T.V.</label>
  <img src='../images/botones/checked.png' style='width:30px;height:30px;float:right;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  $Fecha= explode("-",$Dato[FechaVencITV],3);
  $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
  if($Dato[FechaVencITV]<date("Y-m-d")){
  $color="red";
  }else{
  $color="black";  
  }  
    echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[Marca] $Dato[Modelo] $Dato[Dominio]</label>
    <label style='float:right;color:$color'> $Fecha1</label></div>";
  }
  echo "</form>";

     //KM ACTUALES
  $sql=mysql_query("SELECT Marca,Modelo,Dominio,Kilometros FROM Vehiculos WHERE Estado<>'Vendida' ORDER BY Kilometros DESC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:green 3px solid;width:350px;float:left;margin-left:20px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Kilometros Actuales</label>
  <img src='../images/botones/zoom.png' style='width:30px;height:30px;float:right;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
   echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[Marca] $Dato[Modelo] $Dato[Dominio]</label>
    <label style='float:right;color:$color'> $Dato[Kilometros] Km.</label></div>";
  }
  echo "</form>";
  //VENCIMIENTO TARJETA VERDE
  $sql=mysql_query("SELECT Marca,Modelo,Dominio,VencimientoTarjetaVerde FROM Vehiculos WHERE Estado<>'Vendida' ORDER BY VencimientoTarjetaVerde DESC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:violet 3px solid;width:350px;float:left;margin-left:20px;min-height:380px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Vencimiento Tarjeta Verde</label>
  <img src='../images/botones/zoom.png' style='width:30px;height:30px;float:right;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  if($Dato[VencimientoTarjetaVerde]<date("Y-m-d")){
  $color="red";
  }else{
  $color="black";  
  }  
    echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Dato[Marca] $Dato[Modelo] $Dato[Dominio]</label>
    <label style='float:right;color:$color'> $Dato[VencimientoTarjetaVerde]</label></div>";
  }
  echo "</form>";

  //CHEQUES A VENCER
  $Fecha=date('d-m-Y'); 
  $sql=mysql_query("SELECT * FROM Cheques WHERE Terceros=0 AND FechaCobro>='$Fecha' AND Utilizado='1' AND Pagado='0' ORDER BY FechaCobro ASC");
  echo "<form class='login' style='margin-top:10px;background:white;border-top:yellow 3px solid;width:1135px;float:left;margin-left:100px;min-height:305px;'>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Cheques Propios</label>
  <img src='../images/botones/Factura.png' style='width:30px;height:30px;float:right;'> </div>";	
  echo "<div><hr></hr></div>";  
  while($Dato=mysql_fetch_array($sql)){
  if($Dato[FechaCobro]<=date("Y-m-d")){
  $color="red";
  }else{
  $color="black";  
  }
  $Fecha0= explode("-",$Dato[FechaCobro],3);
  $Fecha1= $Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
  $Total=number_format($Dato[Importe],2,".",",");  
   echo "<div style='margin-bottom:3px;font-size:12px;'><label>$Fecha1 $Dato[NumeroCheque] $Dato[Proveedor]</label>
    <label style='float:right;color:$color'>$ $Total</label></div>";
  }
  echo "</form>";

   
  ?>
  
  </body>
</html>
