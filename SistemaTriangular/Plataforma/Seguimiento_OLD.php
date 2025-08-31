<?
session_start();
include("../ConexionBD.php");
$sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE NdeCliente='$_SESSION[NCliente]'");
$NombreCliente=mysql_fetch_array($sql);

if($_POST[Aceptar]=='Aceptar'){
$_SESSION[mantener]=1; 
$_SESSION[Desde]=$_POST[Desde];
$_SESSION[Hasta]=$_POST[Hasta];
}elseif($_GET[mantener]==1){
$_SESSION[mantener]=1; 
}else{  
$_SESSION[mantener]=0;  
$_SESSION[Desde]='';
$_SESSION[Hasta]='';
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    <title>.::Plataforma Caddy::.</title>
    <link href="css/popup.css" rel="stylesheet" type="text/css" />        
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="js/miscript.js"></script>
  <?php
echo "<body style='background-color:#ffffff'>"; 
include('Menu/menu.html');
//   echo "<div id='lateral'>";
//   echo "<form method='POST' class='menuizquierdo' style='margin-top:0px;float:center;height:100%;'>";
//   echo "<input name='menu' type='submit' value='Agregar Cliente' >";
//   echo "</form>";
//   echo "</div>";
  
// DESDE ACA EL POPUP  
echo "<div id='principal'>";
if($_GET[cod]<>''){    
echo "<div id='seguimiento' class='overlay'>";
echo "<div id='popupBody'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent'>";
echo "<table class='login' style='width:97%'>";
echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
echo "<th style='background-color:#E24F30;'>Hora</th>"; 
echo "<th style='background-color:#E24F30;'>Destino</th>"; 
echo "<th style='background-color:#E24F30;'>Codigo</th>"; 
echo "<th style='background-color:#E24F30;'>Estado</th>"; 

  $sqlredespacho=mysql_query("SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' 
  AND Eliminado='0' AND CodigoSeguimiento='$_GET[cod]'");
  $dato=mysql_fetch_array($sqlredespacho);
     
    $sqlEstado=mysql_query("SELECT Seguimiento.*,TransClientes.ClienteDestino 
    FROM Seguimiento INNER JOIN TransClientes ON (Seguimiento.CodigoSeguimiento=TransClientes.CodigoSeguimiento)
    WHERE Seguimiento.CodigoSeguimiento='$_GET[cod]'");        

    while($Resultado=mysql_fetch_array($sqlEstado)){
    $Fecha=$Resultado[Fecha];
    $arrayfecha=explode('-',$Fecha,3);
    $Fecha=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
  
    echo "<tr>";
    echo "<td>$Fecha</td>";  
    echo "<td>$Resultado[Hora]</td>"; 
    echo "<td>$Resultado[ClienteDestino]</td>";     
    echo "<td>$Resultado[CodigoSeguimiento]</td>";  
    echo "<td>$Resultado[Estado]</td>";  
    echo "</tr>";
    }
echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";      
    }
    //---------------------------------------IMGRESO----------------------------------------------------------------------
// echo "</head>";
  
if(($_POST[Desde]=='')&&($_POST[Hasta]=='')&&($_SESSION[mantener]==0)){
echo "<form action='' method='POST' class='login' style='height:300px;width:400px;float:center' >";
echo "<h2>SELECCIONAR FECHAS</h2>";
?>
<input name='Desde' placeholder="Desde" class="textbox-n" type="text" onfocus="(this.type='date')"  id="date">   
<input name='Hasta' placeholder="Hasta" class="textbox-n" type="text" onfocus="(this.type='date')"  id="date">   
<?
echo "<input type='submit' name='Aceptar' value='Aceptar' style='width:200px;float:right;margin-right:10px;margin-top:50px'>";
echo "</form>";
}else{
  $_SESSION[RecSeguimiento]=$dato;
  $sqlBuscaCodigos=mysql_query("SELECT * FROM TransClientes WHERE RazonSocial ='$NombreCliente[nombrecliente]' 
  AND Fecha>='$_SESSION[Desde]' AND Fecha<='$_SESSION[Hasta]' AND Entregado = '1' AND Eliminado='0' ORDER BY Fecha");
  echo "<table class='login' >";
  echo "<caption style='background-color: #4D1A50'>ENVIOS ENTREGADOS</caption>";
  echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
  echo "<th style='background-color:#E24F30;'>Seguimiento</th>"; 
  echo "<th style='background-color:#E24F30;'>Cliente</th>"; 
  echo "<th style='background-color:#E24F30;'>Domicilio</th>"; 
  echo "<th style='background-color:#E24F30;'>Localidad</th>"; 
  echo "<th style='background-color:#E24F30;'>Estado</th>"; 
  echo "<th style='background-color:#E24F30;'>Historial</th>"; 

    while($row=mysql_fetch_array($sqlBuscaCodigos)){
//       $sql=mysql_query("SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' 
//       AND Eliminado='0' AND CodigoSeguimiento='$sqlResult[CodigoSeguimiento]'");
//       $Destino=mysql_fetch_array($sqlredespacho);
      
//       $sqlTotal=mysql_query("SELECT SUM(Debe)as Debe FROM TransClientes WHERE TipoDeComprobante='Remito' 
//       AND Eliminado='0' AND CodigoSeguimiento='$sqlResult[CodigoSeguimiento]'");
//       $DestinoTotal=mysql_fetch_array($sqlTotal);  
    $sqlBusca=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND 
    id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");

      $ClienteDestino=$row[ClienteDestino];
      $DomicilioDestino=$row[DomicilioDestino];  
      $LocalidadDestino=$row[LocalidadDestino];
      $TotalStock= money_format('%i',$row[Debe]);
      $Total=$row[Debe];
      $CodigoSeguimiento=$row[CodigoSeguimiento];
      $Origen=$row[RazonSocial];
      $DomicilioOrigen=$row[DomicilioOrigen];
      $LocalidadOrigen=$row[LocalidadOrigen];  
      $CodigoSeguimiento=$row[CodigoSeguimiento];
      
    $sqlBusca=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND 
    id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");
    
    $FechaCarga0=explode('-',$row[Fecha],3);
    $FechaCarga=$FechaCarga0[2]."/".$FechaCarga0[1]."/".$FechaCarga0[0];
    $sqlResult=mysql_fetch_array($sqlBusca);
    
    $Fecha0=explode('-',$sqlResult[Fecha],3);
    $Fecha1=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
    echo "<tr style='background:$color;color:$colortexto'>";
    echo "<td style='padding:5px'>$FechaCarga</td>";
    echo "<td style='padding:5px'> $sqlResult[CodigoSeguimiento]</td>";
    echo "<td style='padding:5px'>$ClienteDestino</td>";
    echo "<td style='padding:5px'>$DomicilioDestino</td>";
    echo "<td style='padding:5px'>$LocalidadDestino</td>";
      //     echo "<td style='padding:5px'>$Fecha1 ( $sqlResult[Hora])</td>";  
    echo "<td style='padding:5px'>$sqlResult[Estado] $Fecha1 $sqlResult[Hora]</td>"; 
    echo "<td style='padding:5px;'><a href='Seguimiento.php?mantener=1&cod=$sqlResult[CodigoSeguimiento]#seguimiento' >Ver Historial</a></td>";
     
    echo "</tr>";  
    $numfilas++;
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
      
 
goto a;
}  
a:
?>     
</body>
</html>
<?
ob_end_flush();
?>