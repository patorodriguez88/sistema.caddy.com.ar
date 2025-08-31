<?
session_start();
include("../ConexionBD.php");
$sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE id='$_SESSION[NCliente]'");
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
<!--     <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script> -->
<!--     <script src="js/miscript.js"></script> -->
    <?php
  echo "<body style='background-color:#ffffff'>"; 
  include('Menu/menu.html');
  echo "<div id='lateral'>";
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
$sqlCtaCte=mysql_query("SELECT SUM(Debe)as Debe,SUM(Haber)AS Haber FROM Ctasctes WHERE Eliminado=0 AND RazonSocial='$NombreCliente[nombrecliente]' ORDER BY Fecha");
$row=mysql_fetch_array($sqlCtaCte);
$Fecha0=explode('-',$row[Fecha],3);
$Fecha1=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
$Debe=number_format($row[Debe],2,',','.');
$Haber=number_format($row[Haber],2,',','.');
$Saldo0=$row[Debe]-$row[Haber];      
$Saldo=$Saldo+$Saldo0; 
$SaldoFinal=number_format($Saldo,2,',','.');      
$Saldo=100;   
?>    
      <form class='login' method='POST' action="" style='width:50%;float:center;'>
      <h2>Pagar a Caddy con Mercado Pago:</h2>
      <div style='width:100%;display: inline-block;'>
      <label>Saldo Cuenta Corriente:</label>  
      <input style='margin-bottom:20px;' type="text" name='nc_t' id="saldototal"  value="$ <? echo $SaldoFinal;?>" required>
        </div>
        <script
      src="https://www.mercadopago.com.ar/integrations/v1/web-tokenize-checkout.js"
      data-button-label="Pagar con Mercado Pago"
      data-public-key="APP_USR-17b98749-da51-49c7-94e7-1fee7528771d"
      data-transaction-amount= "<? echo $Saldo;?>"
      data-summary-product-label="Servicio de Envio">
      </script>
         </form>
    <?
  echo "</div>";
?>     
</body>
</html>
<?
ob_end_flush();
?>