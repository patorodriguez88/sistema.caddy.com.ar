<?
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST[CargarPago_mp]==1){
//---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------

$idMercadoPago=$_POST[numero_mp];

if($_POST[formadepagofecha]==''){
$Fecha=date('Y-m-d');
}else{
$Fecha=$_POST[formadepagofecha];    
}
//DATOS CLIENTE
$id=$_POST[id];
$sqlCliente=$mysqli->query("SELECT nombrecliente,Cuit FROM Clientes WHERE id='$id'");  
$datoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
$RazonSocial=$datoCliente[nombrecliente];  
$Cuit=$datoCliente[Cuit];
//NUMERO DE COMPROBANTE
$sqlnrecibo=$mysqli->query("SELECT Max(NumeroComprobante)as nrecibo FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado='0'");
if($datonrecibo=$sqlnrecibo->fetch_array(MYSQLI_ASSOC)){
$NumeroComprobante = trim($datonrecibo[nrecibo])+1;
}
//NUMERO DE ASIENTO CONTABLE
$BuscaNumAsiento=$mysqli->query("SELECT MAX(NumeroAsiento) as NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
$row=$BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
$NAsiento = trim($row[NumeroAsiento])+1;

//BUSCO LA CUENTA CONTABLE
$FormaDePago=$_POST[formadepago];
$sqlCuenta=$mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
$datoCuenta=$sqlCuenta->fetch_array(MYSQLI_ASSOC);
$Cuenta1=$datoCuenta[NombreCuenta];
$Cuenta0=$datoCuenta[Cuenta];

//BUSCO LA FORMA DE PAGO
$sqlformadepago=$mysqli->query("SELECT FormaDePago,CuentaContable FROM FormaDePago WHERE AdmiteCobranzas=1 AND CuentaContable='$FormaDePago'");  
$datoformadepago=$sqlformadepago->fetch_array(MYSQLI_ASSOC);
$FormaDePagoTabla=$datoformadepago[FormaDePago];
  
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
$Total=$_POST[importe];
$Fee=$_POST[fee_mp];
// $FechaTrans0=explode('/',$_POST[fecha_mp],3);
// $FechaTrans=$FechaTrans0[2].'-'.$FechaTrans0[1].'-'.$FechaTrans0[0];
$FechaTrans=$_POST[fecha_mp];
$NumeroTrans=$_POST[id_mp];
$TipoDeComprobante='Recibo de Pago';

$Importe=$_POST[importe];
  
$sqltransclientes="INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,FormaDePago,IngBrutosOrigen,Usuario)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$FormaDePago}','{$id}','{$Usuario}')";

if($mysqli->query($sqltransclientes)){
    
    $idTransClientes=$mysqli->insert_id;
    
    $insertTransClientes=1;

    }else{
    
    $insertTransClientes=0;

};
 
$Obs_mp=$_POST[obs];

//------------INGRESA EL PAGO A CTAS CTES----------------------
$sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,idCliente,Facturado,idTransClientes,idMercadoPago,Observaciones)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Usuario}','{$id}','1','{$idTransClientes}','{$idMercadoPago}','{$Obs_mp}')";

if($mysqli->query($sqlCtasctes)){
  
    $idCtasctes=$mysqli->insert_id;

    $insertCtasctes=1;

}else{

    $insertCtasctes=0;

};  

//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
$Cuenta2='DEUDORES POR VENTAS';	
$Cuenta3='112200';
$Observaciones=$TipoDeComprobante." Numero: ".$NumeroComprobante;	
$Cuenta4='COMISION MERCADO PAGO';
$Cuenta5='425100';
$Cuenta6='IVA CREDITO FISCAL	';
$Cuenta7='113100';
$Iva_fee=number_format($Fee-($Fee/1.21),2,'.',' ');
$Neto_fee=number_format($Fee-$Iva_fee, 2, '.', ' ');
$Neto_importe=number_format($Importe-$Fee, 2, '.', ' ');

	//DEBE
	 $sqlTesoreriaDebe="INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Usuario,Sucursal,NumeroAsiento,FechaTrans,
     NumeroTrans,FormaDePago,idCtasctes) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Neto_importe}','{$Observaciones}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}')"; 

    //INGRESO COMISION MERCADO PAGO SIN IVA
     $mysqli->query("INSERT INTO `Tesoreria`(
    Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Usuario,Sucursal,NumeroAsiento,FechaTrans,
    NumeroTrans,FormaDePago,idCtasctes) VALUES 
    ('{$Fecha}','{$Cuenta4}','{$Cuenta5}','{$Neto_fee}','{$Observaciones}','{$Usuario}','{$Sucursal}',
    '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}')"); 
    
    //INGRESO EL IVA DE MERCADO PAGO
    $mysqli->query("INSERT INTO `Tesoreria`(
    Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Usuario,Sucursal,NumeroAsiento,FechaTrans,
    NumeroTrans,FormaDePago,idCtasctes) VALUES 
    ('{$Fecha}','{$Cuenta6}','{$Cuenta7}','{$Iva_fee}','{$Observaciones}','{$Usuario}','{$Sucursal}',
    '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}')"); 

    //HABER
  	$sqlTesoreriaHaber="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans,FormaDePago,idCtasctes) VALUES 
	 ('{$Fecha}','{$Cuenta2}','{$Cuenta3}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}')"; 

    if($mysqli->query($sqlTesoreriaDebe)){$insertTesoreriaDebe=1;}else{$insertTesoreriaDebe=0;};  
    
    if($mysqli->query($sqlTesoreriaHaber)){$insertTesoreriaHaber=1;}else{$insertTesoreriaHaber=0;};    
  
    $idTesoreria=$mysqli->insert_id;

    if($idCtasctes){
    
        $mysqli->query("UPDATE Ctasctes SET idTesoreria ='$idTesoreria' WHERE id='$idCtasctes' LIMIT 1");
    
    }
    
echo json_encode(array('success'=>1,'transclientes'=>$insertTransClientes,'ctasctes'=>$insertCtasctes,'tesoreriaDebe'=>$insertTesoreriaDebe,'tesoreriaHaber'=>$insertTesoreriaHaber));

}else{

    echo json_encode(array('success'=>0));    

}