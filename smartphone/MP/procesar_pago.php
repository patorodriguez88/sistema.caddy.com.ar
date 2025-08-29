<?php  
ob_start();
session_start();
include_once "../ConexionSmartphone.php";
$user= $_SESSION['NCliente'];

  $token = $_REQUEST["token"];
  $payment_method_id = $_REQUEST["payment_method_id"];
  $installments = $_REQUEST["installments"];
  $issuer_id = $_REQUEST["issuer_id"];

require_once '../../../../vendor/autoload.php';
//     MercadoPago\SDK::setAccessToken("APP_USR-613814688175055-092315-52b685fe71a5b870cc86bba91b59bb29-50894474");
    MercadoPago\SDK::setAccessToken("TEST-613814688175055-092315-9623a3b7ee4304868003bb0f38724125-50894474");
    //...
    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = 138;
    $payment->token = $token;
    $payment->description = "Servicio de Logistica";
    $payment->installments = $installments;
    $payment->payment_method_id = $payment_method_id;
    $payment->issuer_id = $issuer_id;
    $payment->payer = array(
    "email" => "prodriguez@caddy.com.ar"
    );
    // Guarda y postea el pago
    $payment->save();
    //...
    // Imprime el estado del pago
    echo $payment->status;
    echo $payment->status_detail;

  if ($payment->status=='approved') {
$sqlbuscodatos=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$_SESSION[cdmp]'");
$datosqlbuscodatos=mysql_fetch_array($sqlbuscodatos);

$Fecha=date('Y-m-d');
$RazonSocial=$datosqlbuscodatos[RazonSocial]; 
$Cuit=$datosqlbuscodatos[Cuit];
$TipoDeComprobante="Recibo de Pago";
// $NumeroComprobante=$datosqlbuscodatos[NumeroComprobante];
$Importe=$datosqlbuscodatos[Debe];
$Usuario=$_SESSION['Usuario'];
$FormaDePago="111400";
  
$sqlbuscar=mysql_query("SELECT MAX(NumeroComprobante)as NumeroComprobante FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado=0");
$datosqlbuscar=mysql_fetch_array($sqlbuscar);
$NumeroComprobante = trim($row[0])+1;

  
$sql="INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,FormaDePago)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$FormaDePago}')";
mysql_query($sql);

$mptoken=$token;
$mppayment_method_id=$payment_method_id;
$mpissuer_id=$issuer_id;	
//------------INGRESA EL PAGO A CTAS CTES----------------------
$sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,mptoken,mppayment_method_id,mpissuer_id)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Usuario}','{$mptoken}','{$mppayment_method_id}','{$mpissuer_id}')";
mysql_query($sqlCtasctes);	

//------------------------------------------------------------------------			
	
//----------INGRESA LOS MOVIMINTOS EN TABLA IVA VENTAS---------------
$CuantoHay=mysql_query("SELECT Ingresos,Total FROM IvaVentas WHERE NumeroComprobante='$NumeroComprobante'");
$CuantoHayresult=mysql_result($CuantoHay,0);
$ImporteFinal=$Importe+$CuantoHayresult;
	
$CuantoHay1=mysql_query("SELECT Total FROM IvaVentas WHERE NumeroComprobante='$NumeroComprobante'");
$CuantoHayresult1=mysql_result($CuantoHay1,0);
$Saldo=$CuantoHayresult1-$ImporteFinal;
$sql1="UPDATE IvaVentas SET Ingresos='$ImporteFinal',Saldo='$Saldo' WHERE NumeroComprobante='$NumeroComprobante'";
mysql_query($sql1);

//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
$Cuenta0='111400';
		
$BuscaCuenta=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
$Cuenta1=mysql_result($BuscaCuenta,0);

	$BuscaCuentaProv=mysql_query("SELECT CtaAsignada,Cuit FROM Proveedores WHERE Cuit='$Cuit'");
$CuentaEncontrada=mysql_result($BuscaCuentaProv,0);

// $BuscaCuentaProv2=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
// $Cuenta2=mysql_result($BuscaCuentaProv2,0);
$Cuenta2='DEUDORES POR VENTAS';	
$Cuenta3='112200';
// $BuscaCuentaProv3=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
// $Cuenta3=mysql_result($BuscaCuentaProv3,0);
$Observaciones=$TipoDeComprobante." Numero: ".$NumeroComprobante;	
$Debe2=$_GET['debe2_t'];
$Banco=$_GET['banco_t'];
$FechaCheque=$_GET['fechacheque_t'];
$NumeroCheque=$_GET['numerocheque_t'];
$NumeroTrans=$_GET['numerotransaccion_t'];
$FechaTrans=$_GET['fechatransaccion_t'];  
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
  
$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
$row = mysql_fetch_row($BuscaNumAsiento);
$NAsiento = trim($row[0])+1;
	
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe}','{$Observaciones}',
	 '{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Usuario}','{$Sucursal}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}')"; 
  	mysql_query($sql1);
  	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Usuario,Sucursal,NumeroAsiento) VALUES 
	 ('{$Fecha}','{$Cuenta2}','{$Cuenta3}','{$Importe}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}')"; 
  	mysql_query($sql2);
    
    if($_GET[web]=='si'){
    header('location:https://www.caddy.com.ar/Cobranza.php?status=approved');
    }else{
    header('location:https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/Status.php?status=approved');
    }
   }elseif($payment->status=='rejected'){
   header('location:https://www.caddy.com.ar/SistemaTriangular/smartphone/MP/Status.php?status=rejected');
  }

//...
ob_end_flush();		
?>