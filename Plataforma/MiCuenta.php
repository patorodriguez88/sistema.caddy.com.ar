<?
session_start();
include("../ConexionBD.php");
$sql=mysql_query("SELECT nombrecliente,SituacionFiscal FROM Clientes WHERE id='$_SESSION[NCliente]'");
$NombreCliente=mysql_fetch_array($sql);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    <title>.::Plataforma Caddy::.</title>
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="js/miscript.js"></script>
  <?php
echo "<body style='background-color:#ffffff'>"; 
  
  include('Menu/menu.html');
  echo "<div id='lateral'>";
  $sqlCtaCte=mysql_query("SELECT SUM(Debe-Haber)as Saldo FROM Ctasctes WHERE Eliminado=0 AND RazonSocial='$NombreCliente[nombrecliente]' ORDER BY Fecha");
  $saldo=mysql_fetch_array($sqlCtaCte);
    if($saldo[Saldo]>0){
//      echo "<form method='POST' action='Pago.php'class='menuizquierdo' style='margin:10px;float:center;height:100%;'>";
//     echo "<input name='menu' type='submit' value='Ingresar Pago' style='background:#E24F30' >";
//     echo "</form>";
    }  
    
  echo "</div>";
  echo "<div id='principal'>";

    //---------------------------------------IMGRESO----------------------------------------------------------------------
//  DESDE ACA MUESTRO LA TABLA     
    $color='#B8C6DE';
    $font='white';
    $color2='white';

    $dato=$_POST[service];
    $_SESSION[RecSeguimiento]=$dato;
    $Desde=$_POST[Desde];
    $Hasta=$_POST[Hasta];  
  $sqlCtaCte=mysql_query("SELECT * FROM Ctasctes WHERE Eliminado=0 AND RazonSocial='$NombreCliente[nombrecliente]' ORDER BY Fecha");
    echo "<div style='height:86%;overflow:auto;'>";
    echo "<table class='login' >";
    echo "<caption style='background-color: #4D1A50;'>Cuenta Corriente $NombreCliente[nombrecliente]</caption>";
    echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
    echo "<th style='background-color:#E24F30;'>Comprobante</th>"; 
    echo "<th style='background-color:#E24F30;'>Numero</th>"; 
    echo "<th style='background-color:#E24F30;'>Numero Venta</th>";
    echo "<th style='background-color:#E24F30;'>Debe</th>"; 
    echo "<th style='background-color:#E24F30;'>Haber</th>";
    echo "<th style='background-color:#E24F30;'>Saldo</th>";
  
$SaldoInicio=0;
    while($row=mysql_fetch_array($sqlCtaCte)){
$Fecha0=explode('-',$row[Fecha],3);
$Fecha1=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
$Debe=number_format($row[Debe],2,',','.');
$Haber=number_format($row[Haber],2,',','.');
$Saldo0=$row[Debe]-$row[Haber];      
$Saldo=$Saldo+$Saldo0; 
$SaldoFinal=number_format($Saldo,2,',','.');      
if($Debe>0){
$NComprobante=$row[NumeroFactura];  
}elseif($Haber>0){
$NComprobante=$row[NumeroVenta];  
}
    echo "<tr>";
    echo "<td style='padding:15px'>$Fecha1</td>";
    echo "<td style='padding:15px'>$row[TipoDeComprobante] $row[NumeroVenta]</td>";
      
    if($Debe>0){
    echo "<td style='padding:15px'>$row[NumeroFactura]</td>";  
      if($NombreCliente[SituacionFiscal]=='Responsable Inscripto'){
      $F='FA';
      }else{
      $F='FB';
      }
      $ruta=$F.'-'.$row[NumeroFactura].'.pdf';
      if(file_exists($ruta)){
      echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/FacturasVenta/$F-$row[NumeroFactura].pdf'><input type='image' src='images/Remito.png' width='30' height='30' border='0' style='float:center;vertical-align:middle;'></td>";
      }else{
      echo "<td style='padding:15px'>$row[NumeroVenta]</td>";  
      }
    }else{    
    echo "<td style='padding:15px'>$row[NumeroVenta]</td>";
    echo "<td></td>";  
    }  
    echo "<td style='padding:15px'>$ $Debe</td>";
    echo "<td style='padding:15px'>$ $Haber</td>";  
    echo "<td style='padding:15px'>$ $SaldoFinal</td>";  
    echo "</tr>";  
    $numfilas++;
    }
    echo "</table>";
    echo "</div>";

  echo "<div id='pie' style='margin-left:0px'>";
  echo "<table class='login'>";
  echo "<caption style='background-color: #E24F30;'>Total Saldo Cuenta Corriente $ $SaldoFinal</caption>";
  echo "</table>";  
  echo "</div>";
?>     
</body>
</html>
<?
ob_end_flush();
?>