<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];

if($_GET[NoOperativo]=='Si'){
$_SESSION[NoOperativo]='1';  
}elseif($_GET[NoOperativo]=='No'){
$_SESSION[NoOperativo]='0';  
}

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
<script src="../js/notification.js" type="text/javascript"></script>
</head>
<script>
function sumaselecc(){
  var imp = document.getElementById('total_t').value;
  
  
  var n2 = document.getElementsByName('NP[]');
var n1 =document.getElementsByName('valor');
var total= n2.length;
   if(n2.checked){
    document.formulariox.importepago_t.value=0; 
   } 
var elementos = 0;  
var Suma = 0;   
  for(var i=0; i<=total; i++){
    if(n2[i].checked == true){
      elementos++;
     var Suma =+ Suma+parseInt(n1[i].value);
     var Sumacdecimales=Suma.toFixed(2);
 
     document.getElementById('importepago_t').value=Sumacdecimales;

    if(imp != Sumacdecimales){
    document.getElementById('ImputaPago').disabled=true;
    document.getElementById('ImputaPago').style.opacity="0.5";
      
    }  
   }
      if(elementos==0){
       document.formulariox.importepago_t.value=0; 
      }
  }
  document.getElementById('Disponible').value=n1;
}
  </script>
 
<script>
function sumar(){
var n1 = parseFloat(document.MyForm.importeneto_t.value); 
var n2 = parseFloat(document.MyForm.iva1_t.value); 
var n3 = parseFloat(document.MyForm.iva2_t.value); 
var n4 = parseFloat(document.MyForm.iva3_t.value);
var n8 = parseFloat(document.MyForm.iva4_t.value);
  
var n5 = parseFloat(document.MyForm.exento_t.value);
var n6 = parseFloat(document.MyForm.perciva_t.value);
var n7 = parseFloat(document.MyForm.perciibb_t.value);
document.MyForm.total_t.value=n1+n2+n3+n4+n5+n6+n7+n8; 
  
document.MyForm.totaliva_t.value=n2+n3+n4+n8; 
document.MyForm.totalSiniva_t.value=n1+n5; 
}
</script> 
<script>
function buscardatos(){
var numero = parseFloat(document.formulario1.ct.value); 
var fecha = parseFloat(document.formulario1.ctf.value); 
var importe = parseFloat(document.formulario1.cti.value); 
  
document.formulario1.numerocheque_t.value=numero; 
document.formulario1.fechacheque_t.value=fecha; 
document.formulario1.importepago_t.value=importe; 
  
}
</script>
<script>
function buscar(){
var n1 = parseFloat(document.MyForm.importeneto_t.value); 
var n2 = parseFloat(document.MyForm.iva1_t.value); 
var n3 = parseFloat(document.MyForm.iva2_t.value); 
var n4 = parseFloat(document.MyForm.iva3_t.value);
var n8 = parseFloat(document.MyForm.iva4_t.value);  
var n5 = parseFloat(document.MyForm.exento_t.value);
var n6 = parseFloat(document.MyForm.perciva_t.value);
var n7 = parseFloat(document.MyForm.perciibb_t.value);
document.MyForm.total_t.value=n1+n2+n3+n4+n5+n6+n7; 
document.MyForm.totaliva_t.value=n2+n3+n4; 
document.MyForm.totalSiniva_t.value=n1+n5+n6+n7; 
  
}
</script>

<script>
function mostrarx(){
var x1 = parseFloat(document.formulariox.formadepago_t.value);
var x2 = parseFloat(document.formulariox.anticipodisponible_t.value); 
var x3 = parseFloat(document.formulariox.totalfacturas_t.value); 

  if (x1=='3'){   //EFECTIVO 000111100
    document.formulariox.importepago_t.value=x3; 
    document.getElementById('total').style.display = 'block';

    document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
		document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
 	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
    document.getElementById('Disponible').style.display='none';
      document.getElementById('tabla1').style.display='none';

  }
  
if (x1=='20'){ // CHEQUES DE TERCEROS
		document.getElementById('TercerosOculto').style.display = 'block';

  	document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
    document.getElementById('Disponible').style.display = 'none';
	  document.getElementById('total').style.display = 'none';
        document.getElementById('tabla1').style.display='none';

}  
if (x1=='4'){    //111200 TRANSFERENCIAS BANCARIAS
    document.formulariox.importepago_t.value=x3; 
		document.getElementById('oculto').style.display = 'block';
		document.getElementById('oculto1').style.display = 'block';
		document.getElementById('BancoOculto').style.display = 'block';
	  document.getElementById('total').style.display = 'block';

		document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
    document.getElementById('Disponible').style.display = 'none';
    document.getElementById('tabla1').style.display='none';

			
}
if (x1=='5'){  // CHEQUES PROPIOS
      document.formulariox.importepago_t.value=x3; 
		document.getElementById('NumeroChequeOculto').style.display = 'block';
		document.getElementById('FechaChequeOculto').style.display = 'block';
		document.getElementById('BancoOculto').style.display = 'block';
    document.getElementById('total').style.display = 'block';
 
    document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
    document.getElementById('Disponible').style.display = 'none';
        document.getElementById('tabla1').style.display='none';

    
 	}
 if (x1=='22'){   //ANTICIPO A PROVEEDORES
    document.getElementById('ImputaPago').disabled=true;
    document.getElementById('ImputaPago').style.opacity="0.5";
   document.getElementById('agregarfp').style.display='block';

   document.formulariox.importepago_t.value=0; 
	 document.getElementById('Disponible').style.display = 'block';
	 document.getElementById('total').style.display = 'block';
   document.getElementById('tabla1').style.display='block';
   
    document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
		document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
 	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
    
  } 
}

</script>
<script>
function mostrary(){
var x1 = parseFloat(document.formularioy.formadepago_t.value);
  
  if (x1=='3'){   //EFECTIVO 000111100
    document.getElementById('total').style.display = 'block';
    document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
		document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
 	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
  }
  
if (x1=='20'){ // CHEQUES DE TERCEROS
		document.getElementById('TercerosOculto').style.display = 'block';

  	document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
	  document.getElementById('total').style.display = 'none';
}  
if (x1=='4'){    //111200 TRANSFERENCIAS BANCARIAS
		document.getElementById('oculto').style.display = 'block';
		document.getElementById('oculto1').style.display = 'block';
		document.getElementById('BancoOculto').style.display = 'block';
	  document.getElementById('total').style.display = 'block';

		document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
	  document.getElementById('NumeroChequeOculto').style.display = 'none';
		document.getElementById('FechaChequeOculto').style.display = 'none';
		document.getElementById('BancoOculto').style.display = 'none';
			
}
if (x1=='5'){  // CHEQUES PROPIOS
		document.getElementById('NumeroChequeOculto').style.display = 'block';
		document.getElementById('FechaChequeOculto').style.display = 'block';
		document.getElementById('BancoOculto').style.display = 'block';
    document.getElementById('total').style.display = 'block';
 
    document.getElementById('oculto').style.display = 'none';
		document.getElementById('oculto1').style.display = 'none';
		document.getElementById('TercerosOculto').style.display = 'none';
 	}
}
</script>  

<script>
function comprobar(){
var valor = parseFloat(document.MyForm.importeneto_t.value);
if (valor>'1000000'){   //EFECTIVO 000111100
document.getElementById('codigodeaprobacion').style.display = 'block';
alertify.success("Requiere Codigo de Autorizacion","",0);
}else{
document.getElementById('codigodeaprobacion').style.display = 'none';
}}
</script>
  

<?php
   include("../Alertas/alertas.html");

if($_GET[CodigoIncorrecto]=='Si'){
?>
  <script>
  alertify.error("Atencion Codigo de Aprobacion incorrecto","",0);
  </script>  
<?  
}
  
if($_SESSION[NoOperativo]=='1'){
?><script>
alertify.error("Atencion carga comprobante NO OPERATIVO","",0);
</script>
  <?  
}

if ($_POST['Alta']=='Aceptar'){

if($_POST[total_t]>'1000000'){
  $Codigodeaprobacion=$_POST[codigodeaprobacion];
  $sql=mysql_query("SELECT * FROM OrdenesDeCompra WHERE CodigoAprobacion='$Codigodeaprobacion' AND Eliminado=0 AND Estado='Aprobada' AND CompraRelacionada=0");
  $CodigoObtenido=mysql_fetch_array($sql);
  if($CodigoObtenido[CodigoAprobacion]==''){  
    header('location:Compras.php?CodigoIncorrecto=Si');
  goto a;  
  }
  
}
// $sql=mysql_query("INSERT INTO `OrdenesDeCompra`(`CompraRelacionada`) VALUES () WHERE CodigoAprobacion='$CodigoObtenido[CodigoAprobacion]'");  

  $NoOperativo=$_SESSION[NoOperativo];  
  $Fecha=$_POST['fecha_t'];
	$Mes=date("n",strtotime($Fecha));
	$Ano=date("Y",strtotime($Fecha));
	
	$Buscar=mysql_query("SELECT * FROM CierreIva WHERE Libro='IvaCompras' AND Mes='$Mes' AND Ano='$Ano'");
	$numerofilas = mysql_num_rows($Buscar);	
	
	if ($numerofilas<>'0'){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alert("ERROR: EL LIBRO IVA DEL MES <? echo "$Mes";?> DEL AÑO <? echo "$Ano"; ?> YA ESTA CERRADO")
		</script>
		<?php
  unset($_SESSION[NoOperativo]);  
	goto a;
	}	
	if (($_POST['total_t']=='0')||($_POST['total_t']=='')){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alert("ERROR: EL TOTAL NO PUEDE ESTAR EN CERO, NI SER NULL EL COMPROBANTE NO SE CARGO")
		</script>

	<?php
  unset($_SESSION[NoOperativo]);  		
	goto a;
	}
	$RazonSocial=$_POST['razonsocial_t'];
	$Cuit2=$_POST['cuit_t'];
//	$TipoDeComprobante='FACTURA';
 	$TipoDeComprobante=$_POST['tipodecomprobante_t'];

	if(($TipoDeComprobante=='NOTAS DE CREDITO A')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO B')
	||($TipoDeComprobante=='NOTAS DE CREDITO C')
	||($TipoDeComprobante=='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
	||($TipoDeComprobante=='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
	||($TipoDeComprobante=='NOTAS DE CREDITO M')
	||($TipoDeComprobante=='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
	||($TipoDeComprobante=='RECIBOS FACTURA DE CREDITO')
	||($TipoDeComprobante=='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
	||($TipoDeComprobante=='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
	||($TipoDeComprobante=='NOTA DE CREDITO DE ASIGNACION')){
$Valor=-1;	
	}else{
$Valor=1;
	}

	$NumeroComprobante=$_POST['numerocomprobante_t'];
	$ImporteNeto0=$_POST['importeneto_t']*$Valor;
	$ImporteNeto=number_format($ImporteNeto0,2,'.','');
	$Iva10=$_POST['iva1_t']*$Valor;
	$Iva1=number_format($Iva10,2,'.','');
	$Iva20=$_POST['iva2_t']*$Valor;
	$Iva2=number_format($Iva20,2,'.','');
	$Iva30=$_POST['iva3_t']*$Valor;
	$Iva3=number_format($Iva30,2,'.','');
  $Iva40=$_POST['iva4_t']*$Valor;
	$Iva4=number_format($Iva40,2,'.','');
  $Exento0=$_POST['exento_t']*$Valor;
	$Exento=number_format($Exento0,2,'.','');
	$Total0=$_POST['total_t']*$Valor;
	$Total=number_format($Total0,2,'.','');
	$Compra=$_POST['compra_t'];
	$NumeroAsiento=$_POST['nasiento_t'];
  $Concepto='COMPROBANTE A PAGAR';
  $Descripcion=$_POST[descripcion_t];
	
  $CuentaIva='IVA CREDITO FISCAL';
  $NumeroCuentaIva='113100';
  $TotalIva0=$_POST['totaliva_t']*$Valor;
	$TotalIva=number_format($TotalIva0,2,'.','');
  
  $TotalSinIva0=$_POST['totalSiniva_t']*$Valor;
	$TotalSinIva=number_format($TotalSinIva0,2,'.','');
  
  $datosEncontrados=mysql_query("SELECT CtaAsignada FROM Proveedores WHERE Cuit='$Cuit2'");
	$row = mysql_fetch_array($datosEncontrados);
	$CtaAsignada=$row[CtaAsignada];
  
	$resultado=mysql_query("SELECT * FROM TransProveedores WHERE RazonSocial='$RazonSocial' AND TipoDeComprobante='$TipoDeComprobante' AND NumeroComprobante='$NumeroComprobante'");
	$numero_filas = mysql_num_rows($resultado);	
	$PercepcionIva0=$_POST['perciva_t']*$Valor;
	$PercepcionIva=number_format($PercepcionIva0,2,'.','');
	$PercepcionIIBB0=$_POST['perciibb_t']*$Valor;
	$PercepcionIIBB=number_format($PercepcionIIBB0,2,'.','');
	if ($numero_filas<>'0'){
	?>
	<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
	<script language="JavaScript" type="text/javascript">
	alert("ERROR: EL COMPROBANTE NO SE CARGO, EL NUMERO DE COMPROBANTE YA HA SIDO CARGADO")
	</script>
	<?php
	goto a;
	}	
		
$sql="INSERT INTO IvaCompras(
Fecha,
RazonSocial,
Cuit,
TipoDeComprobante,
NumeroComprobante,
ImporteNeto,
Iva1,
Iva2,
Iva3,
Iva4,
Exento,
Total,
CompraMercaderia,Saldo,NumeroAsiento,PercepcionIva,PercepcionIIBB,Cuenta,NoOperativo)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Iva4}','{$Exento}',
'{$Total}','{$Compra}','{$Total}','{$NumeroAsiento}','{$PercepcionIva}','{$PercepcionIIBB}','{$CtaAsignada}','{$NoOperativo}')";
mysql_query($sql);
//unset($_SESSION['Cuit']);
//---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------
$sqlTransacciones="INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Concepto,Descripcion,NoOperativo,CodigoAprobacion)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit2}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Concepto}','{$Descripcion}','{$NoOperativo}','{$Codigodeaprobacion}')";
mysql_query($sqlTransacciones);	

$IdTransProvD=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit2' AND NumeroComprobante='$NumeroComprobante' AND Debe>0 AND Eliminado=0");	
$IdTransProvCompraD=mysql_result($IdTransProvD,0);

$IdTransProvH=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit2' AND NumeroComprobante='$NumeroComprobante' AND Haber>0 AND Eliminado=0");	
$IdTransProvCompraH=mysql_result($IdTransProvH,0);
  
//--------------------DESDE ACA PARA CARGAR EL ASIENTO CONTABLE EN TESORERIA--------------------
$BuscaCuentaProv=mysql_query("SELECT CtaAsignada,Cuit FROM Proveedores WHERE Cuit='$Cuit2'");
$CuentaEncontrada=mysql_result($BuscaCuentaProv,0);

$BuscaCuenta=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$CuentaEncontrada'");
$NombreCuenta=mysql_result($BuscaCuenta,0);
$Cuenta=mysql_result($BuscaCuenta,0,1);

	
if ($_POST['compra_t']=='S'){
$CuentaProveedores='PROVEEDORES';
$NumeroCuentaProveedores='211100';
}else{
$CuentaProveedores='ACREEDORES';
$NumeroCuentaProveedores='211400';
}

$Observaciones="Carga de: ".$TipoDeComprobante." Numero: ".$NumeroComprobante;	
$Sucursal=$_SESSION['Sucursal'];	 
$Usuario=$_SESSION['Usuario'];
$NAsiento=$_POST['nasiento_t'];	
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo) VALUES 
	 ('{$Fecha}','{$NombreCuenta}','{$Cuenta}','{$TotalSinIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}')"; 
 	mysql_query($sql1);
  
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo) VALUES 
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Total}','{$Observaciones}','{$IdTransProvCompraH}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}')"; 
 	mysql_query($sql2);
  
if(($TotalIva)<>0){
   
  $sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo) VALUES 
	 ('{$Fecha}','{$CuentaIva}','{$NumeroCuentaIva}','{$TotalIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}')"; 
 	mysql_query($sql2);
  }
if(($PercepcionIva)<>0){
   $NumeroCuentaPiva='113200';
   $CuentaPiva='PERCEPCION DE IVA';
  $sql3="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento) VALUES 
	 ('{$Fecha}','{$CuentaPiva}','{$NumeroCuentaPiva}','{$PercepcionIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}')"; 
 	mysql_query($sql3);
  }
if(($PercepcionIIBB)<>0){
   $NumeroCuentaPiibb='113300';
   $CuentaPiibb='PERCEPCION DE IIBB';
  $sql4="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento) VALUES 
	 ('{$Fecha}','{$CuentaPiibb}','{$NumeroCuentaPiibb}','{$PercepcionIIBB}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}')"; 
 	mysql_query($sql4);
  }
  
  if($_SESSION['Rubro']=='Si'){
  $Combustible=$_POST['combustible_t'];
  if($Combustible=='G.N.C.'){
  $Unidad='Mts3';  
  }else{
  $Unidad='Litros';    
  }  
  $Dominio=$_POST['dominio_t'];
  $Cantidad=$_POST['cantidad_t']; 
  $BuscarChofer=mysql_query("SELECT NombreChofer WHERE Logistica WHERE Patente ='$Dominio' AND Estado='Alta'");
  $Chofer=mysql_result($BuscarChofer,0);  
  $sqlcombustible="INSERT INTO Combustible (`Fecha`, `Vehiculo`, `Unidad`, `Cantidad`, `Combustible`, `Precio`, `Chofer`, `Usuario`) 
  VALUES ('{$Fecha}','{$Dominio}','{$Unidad}','{$Cantidad}','{$Combustible}','{$Total}','{$Chofer}','{$Usuario}')";  
  mysql_query($sqlcombustible);  
  $_SESSION['Rubro']='';  
  }
  
    //DESDE ACA PARA SUBIR EL ARCHIVO
  //comprobamos si ha ocurrido un error.

if ($_FILES["imagen"]["error"] > 0){
	echo "ha ocurrido un error";
} else {
	//ahora vamos a verificar si el tipo de archivo es un tipo de imagen permitido.
	//y que el tamano del archivo no exceda los 100kb
	$permitidos = array("image/jpg", "image/pdf", "image/gif", "image/png","application/pdf");
	$limite_kb = 1000;

	if (in_array($_FILES['imagen']['type'], $permitidos) && $_FILES['imagen']['size'] <= $limite_kb * 40960){
		//esta es la ruta donde copiaremos la imagen
		//recuerden que deben crear un directorio con este mismo nombre
		//en el mismo lugar donde se encuentra el archivo subir.php
//     $ruta = "../FacturasCompra/" . $_FILES['imagen']['name'];
    $extension = end(explode(".", $_FILES['imagen']['name']));
    $ruta = "../FacturasCompra/" . $NumeroComprobante.".".$extension;
    //comprovamos si este archivo existe para no volverlo a copiar.
		//pero si quieren pueden obviar esto si no es necesario.
		//o pueden darle otro nombre para que no sobreescriba el actual.
		if (!file_exists($ruta)){
			//aqui movemos el archivo desde la ruta temporal a nuestra ruta
			//usamos la variable $resultado para almacenar el resultado del proceso de mover el archivo
			//almacenara true o false
			$resultado = @move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);
			if ($resultado){
				echo "El archivo ha sido movido exitosamente";
// 			header("location:http://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");

			} else {
				echo "Ocurrio un error al mover el archivo.";
// 			goto a;
      }
		} else {
			echo $NumeroComprobante.".".$extension. ", este archivo existe";
// 		goto a;
    }
	} else {
		echo "archivo no permitido, es tipo de archivo prohibido o excede el tamano de $limite_kb Kilobytes";
// 	goto a;
  }
}
  
  //HASTA ACA PARA SUBIR EL ARCHIVO
 
  unset($_SESSION[NoOperativo]);    
  header("location:Compras.php");
}  
//  HASTA ACA COMPRAS 
  
  
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
  include("../Menu/MenuGestion.php"); 
  echo "</div>";//cabecera 
     echo "<div id='cuerpo'>"; 
      	        echo "<div id='lateral'>"; 
  include("Menu/MenuLateral.php"); 	
  echo "</div>"; //lateral
   echo  "<div id='principal'>";
    
setlocale(LC_ALL,'es_AR');
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';

//----------------------------------DESDE ACA IMPUTA PAGO EN TABLA TransProveedores--------------------
if ($_GET['ImputaPago']=='Aceptar'){
$sqlBuscoCuenta=mysql_query("SELECT CuentaContable FROM FormaDePago WHERE id='".$_GET['formadepago_t']."'");    
$sqlCuenta0=mysql_fetch_array($sqlBuscoCuenta);
$FDePago=$sqlCuenta0[CuentaContable];    
  
// $FDePago=$_GET['formadepago_t'];  
$NAsiento=$_GET['nasiento_t'];	  
$TipoDeComprobante=$_GET['tipodecomprobante_t'];
$NumeroComprobante=$_GET['numerodecomprobante_t'];
$Fecha=date('Y-m-d');
$RazonSocial=$_GET['razonsocial_t'];
$Cuit=$_GET['cuit_t'];
$idIvaCompras=$_GET['idIvaCompras'];
$Importe=$_GET['saldo']; 
$TotalAnticipo=$_GET[anticipodisponible_t];  

  
//SI LA FORMA DE PAGO ES CON PAGOS A CUENTA SOLO UPDATE A TRANSPROVEEDORES Y A IVACOMPRAS
  if($_GET['formadepago_t']=='22'){		
    //PRIMERO MODIFICO IVACOMPRAS
    $TotalAnticipo=$_GET['importepago_t'];
//DESPUES TRANSPROVEEDORES
    $Anticipos=$_GET['NP'];
    $Valor=$_GET['valor'];
    $saldopendiente=0;
   for($i=0;$i<count($Anticipos);$i++){
    //PRIMER BUSCO EL SALDO DE LA PRIMER FACTURA SELECCIONADA  
    $SaldoIvaCompras1=mysql_query("SELECT Saldo FROM IvaCompras WHERE id='$idIvaCompras[$i]'");
    $SaldoIvaCompras=mysql_fetch_array($SaldoIvaCompras1);
     print "Saldo iva compras ".$SaldoIvaCompras[Saldo]."<br/>";
    //SEGUNDO SELECCIONO EL SALDO DEL PRIMER PAGO A CUENTA SELECCIONADO
    $sql=mysql_query("SELECT Saldo FROM AnticiposProveedores WHERE id='$Anticipos[$i]'");
    $Dato=mysql_fetch_array($sql);
    print "Saldo primer anticipo ".$Dato[Saldo]."<br/>";
     
     //VERIFICO SI QUEDA ALGO DE SALDO PARA DESPUES
    if($saldopendiente==0){
    $saldopendiente=$TotalAnticipo-$SaldoIvaCompras[Saldo];     
    }
    if($saldopendiente>0){
    $GuardoSaldo=$saldopendiente;
    $saldopendiente=$saldopendiente-$SaldoIvaCompras[Saldo];
      if($saldopendiente<0){
      $saldopendiente=$GuardoSaldo;  
      }
    }
  
//     print $saldopendiente."<br/>";
    //SI QUEDA SALDO PENDIENTE, AGREGO UN REGISTRO EN TRANSPROVEEDORES CON EL SALDO      
    if($saldopendiente>0){
     //ACTUALIZO TRANS PROVEEDORES Y PONGO LOS DATOS DE LA FACTURA
//     $sql=mysql_query("UPDATE `TransProveedores` SET `TipoDeComprobante`='$TipoDeComprobante[$i]',`NumeroComprobante`='$NumeroComprobante[$i]', Haber='$SaldoIvaCompras[Saldo]' WHERE id='$Anticipos[$i]'");
      
//     $sql=mysql_query("INSERT INTO TransProveedores (`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroComprobante`, `CompraMercaderia`, 
//     `Debe`, `Haber`, `Concepto`, `FormaDePago`, `Descripcion`, `NoOperativo`) VALUES 
//     ('{$Fecha}','{$RazonSocial}','{$Cuit}','ANTICIPO A PROVEEDORES','','N','0','{$saldopendiente}','ANTICIPO A PROVEEDORES',
//     '000111100','Saldo de Anticipo a Proveedores','0')");
     //LUEGO UTILIZO LO PAGADO PARA SALDAR IVA COMPRAS     
     //COMO ESTOY CON SALDO PENDIENTE SE SUPONE QUE CUBRI TODO EL SALDO DE LA FACTURA
      //ME FIJO EL TOTAL DE IVA COMPRAS
    $CuantoHay1=mysql_query("SELECT Total FROM IvaCompras WHERE id='$idIvaCompras[$i]'");
    $CuantoHayresult1=mysql_fetch_array($CuantoHay1);
//     $sql1=mysql_query("UPDATE IvaCompras SET Pagado='$CuantoHayresult1[Total]',Saldo='0' WHERE id='$idIvaCompras[$i]'");
    }    
      //SI LO QUE PAGO NO LLEGO A CUBRIR EL SALDO DE LA FACTURA O LLEGO JUSTO
      if(($saldopendiente<0)||($saldopendiente==0)){
     //ACTUALIZO TRANS PROVEEDORES Y PONGO LOS DATOS DE LA FACTURA
        
//       $sql=mysql_query("UPDATE `TransProveedores` SET `TipoDeComprobante`='$TipoDeComprobante[$i]',`NumeroComprobante`='$NumeroComprobante[$i]',Haber='$saldopendiente' WHERE id='$Anticipos[$i]'");
        
      //VERIFICO EL TOTAL DE LA FACTURA EN IVA COMPRAS
      $CuantoHay1=mysql_query("SELECT Total FROM IvaCompras WHERE id='$idIvaCompras[$i]'");
      $CuantoHayresult1=mysql_fetch_array($CuantoHay1);
      //ME FIJO LO PAGADO EN IVA COMPRAS 
      $CuantoHay=mysql_query("SELECT Pagado FROM IvaCompras WHERE id='$idIvaCompras[$i]'");
      $CuantoHayresult=mysql_fetch_array($CuantoHay);
      //SUMO LO QUE PAGO       
      $ImporteFinal=number_format($Dato[Haber]+$CuantoHayresult[Pagado],2,'.','');
      $Saldo=number_format($CuantoHayresult1[Total],2,'.','')-number_format($Dato[Haber]+$CuantoHayresult[Pagado],2,'.','');
//       $sql1=mysql_query("UPDATE IvaCompras SET Pagado='$ImporteFinal',Saldo='$Saldo' WHERE id='$idIvaCompras[0]'");

      }
  }
      
//      }
    
//     DESDE ACA INGRESA EL PAGO EFECTUADO CON ANTICIPO A TESORERIA GENERANDO EL ASIENTO PARA EQUILIBRAR LAS CUENTAS

$IdTransProvD=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante' AND Debe>0 AND Eliminado=0");	
$IdTransProvCompraD=mysql_fetch_array($IdTransProvD);

$IdTransProvH=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante' AND Haber>0 AND Eliminado=0");	
$IdTransProvCompraH=mysql_fetch_array($IdTransProvH);
    

$sqlBuscoCuenta=mysql_query("SELECT CuentaContable FROM FormaDePago WHERE id='".$_GET['formadepago_t']."'");    
$sqlCuenta0=mysql_fetch_array($sqlBuscoCuenta);
$Cuenta0=$sqlCuenta0[CuentaContable];    
    
$Observaciones="Pago a: ".$RazonSocial." con Anticipo a Proveedores";	
$Debe2=$_GET['debe2_t'];
	
$CuentaProveedores='ANTICIPO A PROVEEDORES';
$NumeroCuentaProveedores='000112500';

$CuentaDebe='ACREEDORES';
$NumeroCuentaDebe='211400';
    
$FechaTransferencia=$_GET['fechatransferencia_t'];
$NumeroTransferencia=$_GET['numerotransferencia_t'];

if($_GET['fechacheque_t']<>''){
$Fecha=$_GET['fechacheque_t'];
}
    
$FechaCheque=$_GET['fechacheque_t'];
$BancoTrasnferencia=$_GET['bancotransferencia_t'];
$Sucursal=$_SESSION['Sucursal'];
$Usuario=$_SESSION['Usuario'];
// $NAsiento=$_GET['nasiento_t'];	
	
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,Banco,FechaCheque,NumeroCheque,idTransProvee,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$CuentaDebe}','{$NumeroCuentaDebe}','{$TotalAnticipo}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$IdTransProvCompraD[id]}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}')"; 
 	 mysql_query($sql1);
  
 	 $sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,idTransProvee,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$TotalAnticipo}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$IdTransProvCompraH[id]}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}')"; 
  	mysql_query($sql2);
  	$Utilizado='1';

// DESDE ACA SI LA FORMA DE PAGO ES CHEQUE DE TEREROS    
if($_GET['formadepago_t']=='20'){
$sql3="UPDATE Cheques SET Utilizado=1,Asiento='$NAsiento',Proveedor='$_GET[idproveedor_t]' WHERE id='$idCheque'";
mysql_query($sql3);
}
// DESDE ACA SI LA FORMA DE PAGO ES CHEQUES PROPIOS
if($_GET['formadepago_t']=='5'){
	 $sql3="UPDATE `Cheques` SET Utilizado='$Utilizado',Asiento='$NAsiento',Importe='$AnticiImporte',FechaCobro='$FechaCheque',Sucursal='$Sucursal',Usuario='$Usuario'
	 WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'";
	 mysql_query($sql3);
}    
goto a;
  }
//   HASTA ACA SI EL PAGO SE REALIZO CON ANTICIPO A PROVEEDORES
$Banco=$_GET['banco_t'];	
$NumeroCheque=$_GET['numerocheque_t'];
	if($_GET['formadepago_t']=='5'){		
		$Vacio=mysql_query("SELECT * FROM Cheques WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'");
		if(mysql_num_rows($Vacio)==0){
	?>
	<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
	<script language="JavaScript" type="text/javascript">
	alert("ERROR: EL NUMERO DE CHEQUE YA FUE UTILIZADO, NO SE CARGARON LOS DATOS")
	</script>
	<?php
goto a;
}
  }
		
              
  
// $Importe=$_GET['saldo'];  
$IdIvaCompras=$_GET['idIvaCompras'];
$Concepto='PAGO A PROVEEDORES';
$NAsiento=$_GET['nasiento_t'];	
  
  for($i=0;$i<count($IdIvaCompras);$i++){
  
$sql="INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,Concepto,FormaDePago)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante[$i]}','{$NumeroComprobante[$i]}','{$Importe[$i]}','{$Concepto}','{$FDePago}')";
mysql_query($sql);
// COMPRUEBA SI HAY NUMERO DE COMPROBANTE NO SE REALIZA ESTA ACCION EN PAGOS A CUENTA
if($NumeroComprobante[$i]<>''){
$IdTransProvD=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante[$i]' AND Debe>0");	
$IdTransProvCompraD=mysql_result($IdTransProvD,0);

$IdTransProvH=mysql_query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante[$i]' AND Haber>0");	
$IdTransProvCompraH=mysql_result($IdTransProvH,0);
  
//----------INGRESA LOS MOVIMIENTOS EN TABLA IVA VENTAS // IVA COMPRAS---------------
$CuantoHay=mysql_query("SELECT Pagado,Total FROM IvaCompras WHERE NumeroComprobante='$NumeroComprobante[$i]' AND Cuit='$Cuit'");
$CuantoHayresult=mysql_result($CuantoHay,0);
$ImporteFinal=number_format($Importe[$i]+$CuantoHayresult,2,'.','');
$CuantoHay1=mysql_query("SELECT Total FROM IvaCompras WHERE NumeroComprobante='$NumeroComprobante[$i]' AND Cuit='$Cuit'");
$CuantoHayresult1=mysql_result($CuantoHay1,0);
$Saldo=number_format($CuantoHayresult1,2,'.','')-number_format($Importe[$i]+$CuantoHayresult,2,'.','');
$sql1="UPDATE IvaCompras SET Pagado='$ImporteFinal',Saldo='$Saldo' WHERE NumeroComprobante='$NumeroComprobante[$i]' AND Cuit='$Cuit'";
mysql_query($sql1);
}
//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
$sqlBuscoCuenta=mysql_query("SELECT CuentaContable FROM FormaDePago WHERE id='".$_GET['formadepago_t']."'");    
$sqlCuenta0=mysql_fetch_array($sqlBuscoCuenta);
$Cuenta0=$sqlCuenta0[CuentaContable];    
    
$BuscaCuenta=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
$Cuenta1=mysql_result($BuscaCuenta,0);

$BuscaCuentaProv=mysql_query("SELECT CtaAsignada,Cuit FROM Proveedores WHERE Cuit='$Cuit'");
$CuentaEncontrada=mysql_result($BuscaCuentaProv,0);

$BuscaCuentaProv2=mysql_query("SELECT NombreCuenta,Cuenta,TipoCuenta FROM PlanDeCuentas WHERE Cuenta='$CuentaEncontrada'");
$Cuenta2=mysql_result($BuscaCuentaProv2,0);
	
$BuscaCuentaProv3=mysql_query("SELECT Cuenta FROM PlanDeCuentas WHERE Cuenta='$CuentaEncontrada'");
$Cuenta3=mysql_result($BuscaCuentaProv3,0);
$Observaciones="Pago de: ".$TipoDeComprobante[$i]." Numero: ".$NumeroComprobante[$i];	
$Debe2=$_GET['debe2_t'];
	
if ($_GET['compra_t']=='S'){
$CuentaProveedores='PROVEEDORES';
$NumeroCuentaProveedores='211100';
}else{
$CuentaProveedores='ACREEDORES';
$NumeroCuentaProveedores='211400';
}
$FechaTransferencia=$_GET['fechatransferencia_t'];
$NumeroTransferencia=$_GET['numerotransferencia_t'];
$FechaCheque=$_GET['fechacheque_t'];
$BancoTrasnferencia=$_GET['bancotransferencia_t'];
$Sucursal=$_SESSION['Sucursal'];
$Usuario=$_SESSION['Usuario'];
// $NAsiento=$_GET['nasiento_t'];
if($_GET['formadepago_t']=='5'){		
$Fecha=$_GET['fechacheque_t'];
}
	
	 $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Observaciones,Banco,FechaCheque,NumeroCheque,idTransProvee,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Importe[$i]}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento[$i]}','{$FechaTrans}','{$NumeroTrans}')"; 
 	 mysql_query($sql1);
  
 	 $sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,idTransProvee,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe[$i]}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$IdTransProvCompraH}','{$Sucursal}','{$Usuario}','{$NAsiento[$i]}','{$FechaTrans}','{$NumeroTrans}')"; 
  	mysql_query($sql2);
  	$Utilizado='1';
    
	 $sql3="UPDATE `Cheques` SET Utilizado='$Utilizado',Asiento='$NAsiento[$i]',Importe='$Importe[$i]',
   FechaCobro='$FechaCheque',Sucursal='$Sucursal',Usuario='$Usuario',Proveedor='$RazonSocial'
	 WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'";
	 mysql_query($sql3);
}
}
  
  
// DESDE ACA PARA INGRESAR PAGO A CUENTA
if ($_GET['ImputaPagoACuenta']=='Aceptar'){
$sqlBuscoCuenta=mysql_query("SELECT CuentaContable FROM FormaDePago WHERE id='".$_GET['formadepago_t']."'");    
$sqlCuenta0=mysql_fetch_array($sqlBuscoCuenta);
$FormaDePago=$sqlCuenta0[CuentaContable];    
$Importe=$_GET['importepago_t'];  
// $FormaDePago=$_GET[formadepago_t];
$Banco=$_GET['banco_t'];	
$NumeroCheque=$_GET['numerocheque_t'];
	if($_GET['formadepago_t']=='5'){		
		$Vacio=mysql_query("SELECT * FROM Cheques WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'");
		if(mysql_num_rows($Vacio)==0){
	?>
	<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
	<script language="JavaScript" type="text/javascript">
	alert("ERROR: EL NUMERO DE CHEQUE YA FUE UTILIZADO, NO SE CARGARON LOS DATOS")
	</script>
	<?php
goto a;
}
// $Importe=$_GET['importepago_t'];    
}
// SI LA OPCION ES CHEQUE DE TERCERO, RESCATO EL IMPORTE DEL CHEQUE
if($_GET['formadepago_t']=='20'){
$idCheque=$_GET['tercero_t'];    
$buscocheque=mysql_query("SELECT * FROM Cheques WHERE id='$idCheque' AND Utilizado=0 AND Terceros=1");
$ImporteCheque=mysql_fetch_array($buscocheque);
$Importe=$ImporteCheque[Importe];
}
		
$Fecha=date('Y-m-d');
$RazonSocial=$_GET['razonsocial_t'];
$Cuit=$_GET['cuit_t'];
$TipoDeComprobante='ANTICIPO A PROVEEDORES';
$NumeroComprobante='';

$IdIvaCompras=$_GET['idIvaCompras'];
$Concepto='ANTICIPO A PROVEEDORES';  
$sql="INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,Concepto,FormaDePago)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Concepto}','{$FormaDePago}')";
mysql_query($sql);
  
// BUSCA EL ULTIMO REGISTRO DE TRANSPROVEEDORES INGRESADO
  $sqlbuscaid=mysql_query("SELECT MAX(id)as id FROM TransProveedores WHERE Concepto='ANTICIPO A PROVEEDORES'");
  $datosqlbuscaid=mysql_fetch_array($sqlbuscaid);
  $idTransProveedores=$datosqlbuscaid[id];
//-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
$sqlBuscoCuenta=mysql_query("SELECT CuentaContable FROM FormaDePago WHERE id='".$_GET['formadepago_t']."'");    
$sqlCuenta0=mysql_fetch_array($sqlBuscoCuenta);
$Cuenta0=$sqlCuenta0[CuentaContable];    
$BuscaCuenta=mysql_query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$Cuenta0'");
$Cuenta1=mysql_result($BuscaCuenta,0);
$Observaciones="ANTICIPO A PROVEEDORES";	
$Debe2=$_GET['debe2_t'];
$CuentaProveedores='ANTICIPO A PROVEEDORES';
$NumeroCuentaProveedores='112500';
$FechaTrans=$_GET['fechatransferencia_t'];
$NumeroTrans=$_GET['numerotransferencia_t'];
$FechaCheque=$_GET['fechacheque_t'];
$Sucursal=$_SESSION['Sucursal'];
$Usuario=$_SESSION['Usuario'];
$NAsiento=$_GET['nasiento_t'];
$RazonSocial=$_GET['razonsocial_t'];  
// CARGO LOS DATOS EN LA TABLA CHEQUE Y PONGO UTILIZADO EN 1 SI EL PAGO FUE CON CHEQUE DE TERCEROS	
if($_GET['formadepago_t']=='20'){
$sql3="UPDATE Cheques SET Utilizado=1,Asiento='$NAsiento',Proveedor='$_GET[idproveedor_t]' WHERE id='$idCheque'";
mysql_query($sql3);
}
// CARGO LOS DATOS EN LA TABLA CHEQUE Y PONGO UTILIZADO EN 1 SI EL PAGO FUE CON CHEQUE PROPIO	
if($_GET['formadepago_t']=='5'){
$sql3="UPDATE Cheques SET Utilizado='1',Asiento='$NAsiento',Importe='$Importe',FechaCobro='$FechaCheque',
Proveedor='$RazonSocial',Sucursal='$Sucursal',Usuario='$Usuario' WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'";
mysql_query($sql3);
}  
  
  $sql1="INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')"; 
 	 mysql_query($sql1);
  
 	 $sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')"; 
  	mysql_query($sql2);
  }
z:  
//-----------------------------------------DESDE ACA PARA AGREGAR FACTURAS DE COMPRA------------------------
if ($_GET['Cargar']=='Si'){
//   $prueba=-1;
//   echo 	number_format($prueba,2,'.','');
  
  
     if($_SESSION[NoOperativo]=='1'){
      $Mensaje='--No Operativo--';  
      }

		if (($_POST['razonsocial']=='')&&($_GET['Cuit']=='')){
			echo "<form name='CargaFacturas' class='login' action='' method='post' style='float:center; width:500px;'>";
			echo "<div><label style='float:center;color:red;font-size:22px'>Cargar Factura de Compra $Mensaje</label></div>";
      echo "<div><hr></hr></div>";
			$Grupo="SELECT RazonSocial,Cuit FROM Proveedores ORDER BY RazonSocial ASC";
			$estructura= mysql_query($Grupo);
			echo "<div><label>Razon Social:</label><select name='razonsocial' onchange='submit()' style='float:center;width:390px;' size='1'>";
			echo "<option>SELECCIONE PROVEEDOR</option>";
			while ($row = mysql_fetch_row($estructura)){
			echo "<option value='".$row[1]."'>".$row[0]."</option>";
			}
			echo "</select></div>";
		echo "</form>";	
		goto a;
		}

$Cuit=$_POST['razonsocial'];
if ($Cuit==''){
$Cuit=$_GET['Cuit'];	
}	
$datosEncontrados="SELECT * FROM Proveedores WHERE Cuit='$Cuit'";
$estructura2= mysql_query($datosEncontrados);
	while ($row = mysql_fetch_row($estructura2)){
	$Cuit1=$row[12];
	$Nombre=$row[2];
	$Direccion=$row[3];	
	$CtaAsignada=$row[23];
  $Rubro=$row[13];
  $SolicitaCombustible=$row[24];
  $SolicitaVehiculo=$row[25];
  $SolicitaChofer=$row[26];
    
	$_SESSION['CuitHeredado']=$row[12];	
	}	
   
echo "<form name='MyForm' class='login' action='' method='POST' style='float:center; width:750px;'  enctype='multipart/form-data' >";
echo "<div><label style='float:center;color:red;font-size:22px'>Cargar Factura de Compra $Mensaje</label></div>";
echo "<div><hr></hr></div>";
echo "<div><label>Fecha del Comprobante:</label><input name='fecha_t' size='20' type='date' style='float:right;' value='' required/></div>";
echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' readonly></div>";
echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' readonly></div>";
echo "<div><label>Cuit:</label><input name='cuit_t' size='20' type='text' value='$Cuit1' readonly/></div>";
	
	$Grupo="SELECT Codigo,Descripcion FROM AfipTipoDeComprobante ORDER BY Codigo ASC";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Tipo De Comprobante:</label><select name='tipodecomprobante_t' style='float:center;width:310px;' size='1' required/>";
	echo "<option value=''>Seleccione una Opcion</option>";	   
  while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[1]."</option>";
	}
	echo "</select></div>";

//SI NO TENGO EL NUMERO DE ASIENTO EN EL GET BUSCO EL ULTIMO DE TESORERIA
	$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
	if ($row = mysql_fetch_row($BuscaNumAsiento)) {
								if ($_GET['NA']==''){
								$NAsiento = trim($row[0])+1;
								}else{
								$NAsiento = $_GET['NA'];
								}
								}	
echo "<div><label>Num de Asiento Contable:</label><input name='nasiento_t' size='20' type='text' value='$NAsiento' readonly/>
<label style='float:right'><a href='http://www.caddy.com.ar/SistemaTriangular/Admin/VentanaAsientos.php?Pant=ComprasCargaFactura&numerocuit=$Cuit1'>Ver Asientos!</a></label>
</div>";
	
echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' value='' size='15' type='text' maxlenght='12' required /></div>";
echo "<div><label>Descripcion:</label><input name='descripcion_t'  type='text' value=''  style='width:300px' required/></div>";
echo "<div><label>Importe Neto:</label><input name='importeneto_t' step='any' type='number' value='' onblur='sumar();comprobar()' required/></div>";
echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' required/></div>";
echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='number' step='0.01' value='0' onblur='sumar()'/></div>";	
echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
echo "<div><label>Iva 27%:</label><input name='iva4_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
echo "<div><label>Exento:</label><input name='exento_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
echo "<div><label>Percepcion Iva:</label><input name='perciva_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";
echo "<div><label>Percepcion IIBB:</label><input name='perciibb_t' size='10' type='number' step='0.01' value='0' onblur='sumar()' /></div>";

if($SolicitaCombustible==1 OR $SolicitaVehiculo==1){
echo "<div><hr></hr></div>";      
}  

  if($SolicitaVehiculo==1){
  $_SESSION['Rubro']='Si';  
  
	$Grupo="SELECT * FROM Vehiculos WHERE Activo='Si'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Vehiculo:</label><select name='dominio_t' style='float:center;width:310px;' size='1' required>";
  echo "<option value=''>Seleccione una Opcion</option>";	   
	while ($row = mysql_fetch_array($estructura)){
	echo "<option value='".$row[Dominio]."'>".$row[Marca]." ".$row[Modelo]." ".$row[Dominio]."</option>";
  }
	echo "</select></div>";
  }
  if($SolicitaCombustible==1){
  echo "<div><label>Combustible:</label><select name='combustible_t' style='float:center;width:310px;' size='1' required>";
  echo "<option value=''>Seleccione una Opcion</option>";	
  echo "<option value='Nafta Premium'>Nafta Premium</option>";	
  echo "<option value='Nafta Super'>Nafta Super</option>";	
	echo "<option value='Nafta Diesel'>Diesel</option>";	
  echo "<option value='G.N.C.'>G.N.C.</option>";	
  echo "</select></div>";
  echo "<div><label>Litros / Mts.3 Cargados:</label><input name='cantidad_t' size='10' type='number' step='0.01' value='' required/></div>";

  }
if($SolicitaCombustible==1 OR $SolicitaVehiculo==1){
echo "<div><hr></hr></div>";      
}  
echo "<div><input name='totaliva_t' size='10' type='hidden' step='0.01' value='' readonly/></div>";
echo "<div><input name='totalSiniva_t' size='10' type='hidden' step='0.01' value='' readonly/></div>";
echo "<div><label>Imagen del Comprobante:</label><input type='file' name='imagen' id='imagen' /></div>";
echo "<div><label>Total:</label><input name='total_t' size='10' type='number' step='.01' value='' readonly/></div>";
echo "<div id='codigodeaprobacion' style='display:none'><label>Codigo de Aprobacion:</label><input name='codigodeaprobacion' type='text'></div>";
echo "<div><input name='Alta' class='bottom' type='submit' value='Aceptar' ></label></div>";
echo "</form>";
	
goto a;
}


if ($_GET['ModificarFactura']=='Si'){
	$u=$_GET['idFactura'];
	$Grupo="SELECT * FROM IvaCompras WHERE id='$u'";
	$estructura= mysql_query($Grupo);

while ($file = mysql_fetch_array($estructura)){
	$arrayfecha=explode('-',$file[Fecha],3);
	$Fecha=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
	
			echo "<form name='MyForm' class='login' action='' method='get' style='float:center; width:600px;'>";
			echo "<div><label style='float:center;color:red;font-size:22px'>Modificar Factura Compra</label></div>";
			echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
			echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='".$file[RazonSocial]."' readonly></div>";
			echo "<div><label>Cuit:</label><input name='cuit_t' size='20' type='text' value=".$file[Cuit]." readonly/></div>";
			echo "<div><label>Tipo De Comprobante:</label><input name='tipodecomprobante_t' type='text' value='".$file[TipoDeComprobante]."' ></div>";
			echo "<div><label>N de Asiento Contable:</label><input name='nasiento_t' size='20' type='text' value='".$file[NumeroAsiento]."' required />
			<label style='float:right'><a href='http://www.triangularlogistica.com.ar/SistemaTriangular/VentanaAsientos.php?Pant=ComprasCargaFactura&numerocuit=$Cuit1'>Ver Asientos!</a></label>
			</div>";
			echo "<div><label>Numero de Comprobante:</label><input name='numerocomprobante_t' size='20' type='text' value='".$file[NumeroComprobante]."' required /></div>";
			echo "<div><label>Importe Neto:</label><input name='importeneto_t' size='10' type='number' step='0.01' value='".$file[ImporteNeto]."' onblur='sumar()' required/></div>";
			echo "<div><label>Iva 2.5%:</label><input name='iva1_t' size='10' type='number' step='0.01' value='".$file[Iva1]."' onblur='sumar()' required/></div>";
			echo "<div><label>Iva 10.5%:</label><input name='iva2_t' size='10' type='number' step='0.01' value='".$file[Iva2]."' onblur='sumar()'/></div>";	
			echo "<div><label>Iva 21%:</label><input name='iva3_t' size='10' type='number' step='0.01' value='".$file[Iva3]."' onblur='sumar()' /></div>";
			echo "<div><label>Iva 27%:</label><input name='iva4_t' size='10' type='number' step='0.01' value='".$file[Iva4]."' onblur='sumar()' /></div>";
      echo "<div><label>Exento:</label><input name='exento_t' size='10' type='number' step='0.01' value='".$file[Exento]."' onblur='sumar()' /></div>";
			echo "<div><label>Percepcion Iva:</label><input name='perciva_t' size='10' type='number' step='0.01' value='".$file[PercepcionIva]."' onblur='sumar()' /></div>";
			echo "<div><label>Percepcion IIBB:</label><input name='perciibb_t' size='10' type='number' step='0.01' value='".$file[PercepcionIIBB]."' onblur='sumar()' /></div>";
			echo "<div><label>Total:</label><input name='total_t' size='10' type='number' step='0.01' value='".$file[Total]."' readonly/></div>";
			echo "<div><label>Es Compra de Mercaderia:</label><input name='compra_t' type='checkbox' value='S'/></div>";
			echo "<div><input name='Modificar' class='bottom' type='submit' value='Aceptar' ></label></div>";
			echo "</form>";
			goto a;
	}
}
  c:
if ($_GET['Pagar']=='Si'){
		if ($_POST['idProveedor']==''){
			echo "<form class='login' action='' method='POST' style='float:center; width:500px;'>";
			echo "<div><label style='float:center;color:red;font-size:22px'>Pagar Factura</label></div>";
      echo "<div><hr></hr></div>";

			$Grupo="SELECT id,RazonSocial FROM Proveedores ORDER BY RazonSocial ASC";
			$estructura= mysql_query($Grupo);
			echo "<div><label>Razon Social:</label><select name='idProveedor' onchange='submit()' 
			style='float:center;width:390px;' size='1'>";
			echo "<option>SELECCIONE PROVEEDOR</option>";

			while ($row = mysql_fetch_row($estructura)){
				echo "<option value='".$row[0]."'>".$row[1]."</option>";
			}
			echo "</select></div>";
		echo "</form>";	
		goto a;
		}

//----desde aca selecciona factura de compra para pagar----------
					$idProveedor=$_POST['idProveedor'];
					$_SESSION['idProveedor']=$_POST['idProveedor'];
					$datosEncontrados="SELECT * FROM Proveedores WHERE id='$idProveedor'";
					$estructura2= mysql_query($datosEncontrados);
					while ($row = mysql_fetch_row($estructura2)){
					$Cuit1=$row[12];
					$Nombre=$row[2];
					$Direccion=$row[3];	
					}	
					if ($_GET['Factura']==''){		
					echo "<table class='login' >";
					echo "<caption>Facturas de Compra</caption>";
					echo "<th>Fecha</th>";
					echo "<th>Numero</th>";
					echo "<th>Comprobante</th>";
					echo "<th>Total</th>";
          echo "<th>Pagos</th>";
					echo "<th>Saldo</th>";
					echo "<th>Seleccionar</td>";
            
$sqlCuit=mysql_query("SELECT Cuit FROM Proveedores WHERE id='$idProveedor'");
$Cuit0=mysql_fetch_array($sqlCuit);
$Cuit=$Cuit0[Cuit];            
$ordenar="SELECT Fecha,NumeroComprobante,TipoDeComprobante,NumeroComprobante,Total,Pagado,Saldo,NumeroAsiento,id 
FROM IvaCompras WHERE Cuit='$Cuit' AND Saldo>'0.001' ";
			
						$MuestraStock=mysql_query($ordenar);
						$box=1;
						$CantidadDeReg=mysql_num_rows($MuestraStock);					
						while($row=mysql_fetch_row($MuestraStock)){
						//		$Suma="SELECT SUM(Importe) as Total,NumeroDeComprobante FROM Ingresos WHERE NumeroDeComprobante='$row[1]'"	
						$Saldo=$row[4]-$row[5];
						$u=$row[3];
						$n=$row[2];
						$f=$row[3];
						$_SESSION['NC']=$row[3];	
						$t=$row[4];
						$fecha=$row[0];
						$NumeroAsiento=$row[7];	
						$arrayfecha=explode('-',$fecha,3);
						$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
						
            echo "<tr style='font-size:12px;background:$color2; color:$font2'><td>$Fecha2</td>";
						echo "<td>$row[3]</td>";
						echo "<td>$row[2]</td>";
						echo "<td>$ ".number_format($row[4],2,",",".")."</td>";
						echo "<td>$ ".number_format($row[5],2,",",".")."</td>";
 						echo "<td>$ ".number_format($row[6],2,",",".")."</td>";
						echo "<input type='hidden' name='Factura[]' value='$f'>";
						echo "<input type='hidden' name='NA[]' value='$NumeroAsiento'>";
						echo "<input type='hidden' name='CR' value='$CantidadDeReg'>";

						echo "<form name='java' action=''>";
						$total=mysql_num_rows($MuestraStock);	
 						echo "<td align='center'><input type='checkbox' name='NR[]' value='$row[8]' width='20' height='20' border='0' style='float:center;' ></td>";
						}
						echo "</tr><tr><td colspan='7' align='right'><input type='submit' name='FacturasSeleccionadas' value='Pagar'
            style='background: none repeat scroll 0 0 #E24F30;
            border: 1px solid #C6C6C6;
            float: right;
            font-weight: bold;
            padding: 8px 26px;
            color:#FFFFFF;
            font-size:12px;'
            ></td></tr></form></table>";

// 					echo "<table class='login' >";
// 					echo "<caption>Pagos imputados a cuenta</caption>";
// 					echo "<th>Fecha</th>";
// 					echo "<th>Observaciones</th>";
// 					echo "<th>Pagos</th>";
// // 					echo "<th>Asociar</th>";

// 						$ordenar="SELECT * FROM TransProveedores WHERE Cuit='$Cuit' AND TipoDeComprobante='ANTICIPO A PROVEEDORES' AND NumeroComprobante='' AND Eliminado='0' ";
					
// 						$MuestraStock=mysql_query($ordenar);
// 						$box=1;
// 						$CantidadDeReg=mysql_num_rows($MuestraStock);					
// 						while($row=mysql_fetch_array($MuestraStock)){
// 						$fecha=$row[Fecha];
// 						$NumeroAsiento=$row[7];	
// 						$arrayfecha=explode('-',$fecha,3);
// 						$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
// 						echo "<tr style='font-size:12px;background:$color2; color:$font2'><td>$Fecha2</td>";
// 						echo "<td>Pago a cuenta</td>";
//  						echo "<td>$ $row[Haber]</td>";

//               // 						echo "<td align='center'><a href='Compras.php?AsociarPago=Si&Importe=$row[Haber]&id=$row[id]'><input type='image' src='../images/botones/Factura.png' width='20' height='20' border='0' style='float:center;'></td>";
// 						echo"</tr>";
// 						$total=mysql_num_rows($MuestraStock);	
// 						}
//HASTA ACA MUESTRA LOS PAGOS A CUENTA
						goto a;
					}
}

  
  //----hasta aca selecciona factura de compra para pagar-----------	

if($_GET['CargarPago']=='Si'){	

			$idProveedor=$_SESSION['idProveedor'];
			$datosEncontrados="SELECT * FROM Proveedores WHERE idProveedor='$idProveedor'";
			$estructura2= mysql_query($datosEncontrados);
				while ($row = mysql_fetch_row($estructura2)){
				$Cuit1=$row[12];
				$Nombre=$row[2];
				$Direccion=$row[3];	
				}	
						
						$NC=$_GET['Factura'];
						$_SESSION['NA']=$Nc;
						$ordenar="SELECT TipoDeComprobante,Total,Cuit,CompraMercaderia,NumeroComprobante,NumeroAsiento FROM IvaCompras WHERE NumeroComprobante='$NC'";
						$MuestraStock=mysql_query($ordenar);
						
						while($row=mysql_fetch_array($MuestraStock)){
						$TC=$row[TipoDeComprobante];
						$Total=$row[Total];
						$Cuit=$row[Cuit];
						$EsCompra=$row[CompraMercaderia];
            $NAsiento=$row[NumeroAsiento];  
						}
 						$Fecha=date('d/m/Y');
						echo "<form name='formulario1' class='login' action='' method='GET' style='float:center; width:600px;'>";
						$CR=$_GET['CR'];						
	
						echo "<div><label style='float:center;color:red;font-size:22px'>Cargar pago a Factura de $Nombre</label></div>";
					  echo "<div><hr></hr></div>";
            echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
						echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' OnFocus='this.blur()'></div>";
						echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' OnFocus='this.blur()'></div>";
						echo "<input type='hidden' name='cuit_t' value='$Cuit'>";
						echo "<div><label>Tipo de Comprobante:</label><input name='tipodecomprobante_t' size='20' type='text' value='$TC' OnFocus='this.blur()' /></div>";
						echo "<div><label>Numero de Comprobante:</label><input name='numerodecomprobante_t' size='20' type='text' value='$NC' OnFocus='this.blur()' /></div>";
						echo "<div><label>Total Comprobantes:</label><input name='total_t' size='20' type='text' value='$SumarFactura' /></div>";
						$NAsiento = $_GET['NA'];

						echo "<div><label>Numero de Asiento:</label><input name='nasiento_t' size='20' type='text' value='$NAsiento' /></div>";
// 					echo "<label><a href='http://www.triangularlogistica.com.ar/SistemaTriangular/VentanaAsientos.php?Factura=$Nc&Pant=Compras'>Ver Asientos!</a></label></div>";
						$Grupo="SELECT FormaDePago,CuentaContable FROM FormaDePago ORDER BY FormaDePago ASC";
						$estructura= mysql_query($Grupo);
						echo "<div><label>Forma de Pago:</label><select name='formadepago_t' onchange='mostrar()' style='float:center;width:280px;' size='1'>";
						while ($row = mysql_fetch_row($estructura)){
										
						echo "<option value='".$row[1]."'";
						if($row[0]=='Efectivo' ) {
				    echo "selected";
						}
						echo ">".$row[0]."</option>";
						}
						echo "</select></div>";
						echo "<div id='BancoOculto' style='display:none;'><label>Banco:</label><input name='banco_t' size='20' type='text' value='' /></div>";
						echo "<div id='oculto' style='display:none;'><label>Numero Transferencia</label><input name='numerotransferencia_t' size='20' type='text' value='' /></div>";
						echo "<div id='oculto1' style='display:none;'><label>Fecha De Transferencia:</label><input name='fechatransferencia_t' size='20' type='text' value='' /></div>";

            echo "<div id='BancoPropioOculto' style='display:none;'><label>Banco:</label><input name='banco_t' size='20' type='text' value='' /></div>";
            echo "<div id='NumeroChequePropioOculto' style='display:none;'><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
						echo "<div id='FechaChequePropioOculto' style='display:none;'><label>Fecha De Pago:</label><input name='fechacheque_t' size='20' type='text' value='' /></div>";
  
            echo "<div id='NumeroChequeOculto' style='display:none;'><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
						echo "<div id='FechaChequeOculto' style='display:none;'><label>Fecha De Pago:</label><input name='fechacheque_t' size='20' type='text' value='' /></div>";
						echo "<div><label>Importe a Pagar:</label><input name='importepago_t' size='20' type='text' value='$Total' /></div>";
						echo "<div><input name='ImputaPago' class='bottom' type='submit' value='Aceptar' ></label></div>";
					  echo "</form>";
						goto a;
						}		
//DESDE ACA PAGO A CUENTA
  
if($_GET['FacturasSeleccionadas']=='Pagar'){	
  $Numeroid=$_GET['NR'];
  if(count($Numeroid)=='0'){
    ?><script>
  alertify.error("Debe seleccionar al menos una factura","",0);
  </script>
  <?  
goto c;
  }
// print $Sumarfactura;  
			$idProveedor=$_SESSION['idProveedor'];
			$datosEncontrados="SELECT * FROM Proveedores WHERE id='$idProveedor'";
			$estructura2= mysql_query($datosEncontrados);
				while ($row = mysql_fetch_row($estructura2)){
				$Cuit1=$row[12];
				$Nombre=$row[2];
				$Direccion=$row[3];	
				}	
						$Fecha=date('d/m/Y');
						$NC=$_GET['Factura'];
						$_SESSION['NA']=$Nc;
            $Factura=$_GET['factura'];
            $NC=$_GET['Factura'];
            $Numeroid=$_GET['NR'];
  				  $CR=$_GET['CR'];

            $SumarFactura=0;
    				echo "<form name='formulariox' class='login' action='' method='GET' style='float:center; width:800px;'>";
						echo "<div><label style='float:center;color:red;font-size:22px'>Cargar pago a Factura de $Nombre</label></div>";
					  echo "<div><hr></hr></div>";
            echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
						echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' OnFocus='this.blur()'></div>";
						echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' OnFocus='this.blur()'></div>";
						echo "<input type='hidden' name='cuit_t' value='$Cuit1'>";
            
            echo "<table class='login'>";
	          echo "<th>N Asiento</th>";
            echo "<th>Tipo de Comprobante</th>";
            echo "<th>Numero de Comprobante</th>";  
	          echo "<th>Importe</th>";
// 	          echo "<th>Imp.A pagar</th>";
      
  for($i=0;$i<count($Numeroid);$i++){
            $BuscarComprobantes=mysql_query("SELECT id,TipoDeComprobante,NumeroComprobante,Cuit,CompraMercaderia,Saldo,NumeroAsiento
            FROM IvaCompras WHERE id='$Numeroid[$i]'");
        		while($row=mysql_fetch_array($BuscarComprobantes)){
						$SumarFactura+=$row[Saldo];// SUMA EL SALDO TOTAL DE IVA COMPRAS
            $TC=$row[TipoDeComprobante];
            $NAsiento=$row[NumeroAsiento];
            $NumComprobante=$row[NumeroComprobante];  
						$Total=$row[Saldo];
            $Cuit=$row[Cuit];
						$EsCompra=$row[CompraMercaderia];	
              
            echo"<tr style='background:white;'>";
            echo "<td>$NAsiento</td>"; 
            echo "<td>$row[TipoDeComprobante]</td>";
            echo "<td>$NumComprobante</td>";
            echo "<td>$ ".number_format($Total,2,",",".")."</td>";
//             echo "<td><input type='number' name='valor[]'></td>";  
            echo "</tr>";
              
            echo "<input type='hidden' name='idIvaCompras[]' value='$row[id]'/>";
            echo "<input type='hidden' name='tipodecomprobante_t[]' value='$TC'/>";
						echo "<input type='hidden' name='numerodecomprobante_t[]' value='$NumComprobante'/>";
						echo "<input type='hidden' name='saldo[]' value='$Total'/>";
        		echo "<input type='hidden' name='nasiento_t[]' value='$NAsiento'/>";              
            
            }
    } 
            echo "</table>";
            
            echo "<div style='margin-top:10px'><label>Total Comprobantes:</label><input id='total_t' name='total_t' size='20' type='text' value='$SumarFactura' /></div>";
						$NAsiento = $_GET['nasiento_t'];
// 						echo "<div><label>Numero de Asiento:</label><input name='nasiento_t' size='20' type='text' value='$NAsiento' /></div>";
            //FORMA DE PAGO
            $N=$_GET['NR'];

            if(count($N) > '1'){
//             $Grupo="SELECT id,FormaDePago,CuentaContable FROM FormaDePago WHERE FormaDePago<>'ANTICIPO A PROVEEDORES' ORDER BY FormaDePago ASC";  
            $Grupo="SELECT id,FormaDePago,CuentaContable FROM FormaDePago ORDER BY FormaDePago ASC";  

            }else{
            $Grupo="SELECT id,FormaDePago,CuentaContable FROM FormaDePago ORDER BY FormaDePago ASC";
						}
              $estructura= mysql_query($Grupo);
						echo "<div><label>Forma de Pago:</label><select name='formadepago_t' onchange='mostrarx(this)' style='float:center;width:280px;' size='1'>";
						while ($row = mysql_fetch_row($estructura)){
						echo "<option value='".$row[0]."'";
						if($row[0]=='3' ) {
				    echo "selected";
						}
						echo ">".$row[1]."</option>";
						}
						echo "</select></div>";
  
  
  	          // PAGO A CUENTA
            $idProveedor=$_SESSION['idProveedor'];
          $BuscarCuit=mysql_query("SELECT Cuit FROM Proveedores WHERE id='$idProveedor'");
            $BuscarCuit0=mysql_fetch_array($BuscarCuit);
            $sql=mysql_query("SELECT SUM(Haber)as Total FROM TransProveedores WHERE RazonSocial='$Nombre' AND TipoDeComprobante='ANTICIPO A PROVEEDORES' AND NumeroComprobante='' AND Eliminado='0' ");
            $dato=mysql_fetch_array($sql);
						$ordenar="SELECT * FROM TransProveedores WHERE Cuit='$BuscarCuit0[Cuit]' AND TipoDeComprobante='ANTICIPO A PROVEEDORES' AND NumeroComprobante='' AND Eliminado='0' AND Haber>0 ";
						$MuestraStock=mysql_query($ordenar);
            
            echo "<div id='tabla1' style='display:none;height:200px;overflow:auto'>";
            echo "<table class='login'>";
  
            if (mysql_num_rows($MuestraStock)==0){
            echo "<caption>No hay pagos imputados a cuenta</caption>";
//             $SumarFactura=0;
              goto b;
            
            }
             
            echo "<caption>Pagos imputados a cuenta</caption>";
            echo "<th>Fecha</th>";
            echo "<th>Observaciones</th>";
            echo "<th>Pagos</th>";
            echo "<th>Asociar</th>";
						$ordenar="SELECT * FROM TransProveedores WHERE Cuit='$BuscarCuit0[Cuit]' AND TipoDeComprobante='ANTICIPO A PROVEEDORES' AND NumeroComprobante='' AND Eliminado='0' ";
//             $ordenar="SELECT * FROM AnticiposProveedores WHERE idProveedor='$idProveedor' AND Saldo>0";

            $box=1;
						$CantidadDeReg=mysql_num_rows($MuestraStock);					
						while($row=mysql_fetch_array($MuestraStock)){
						$fecha=$row[Fecha];
						$NumeroAsiento=$row[7];	
						$arrayfecha=explode('-',$fecha,3);
						$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
						echo "<tr style='font-size:12px;background:$color2; color:$font2'><td>$Fecha2</td>";
						echo "<td>Pago a cuenta</td>";
            echo "<input type='hidden' name='valor' value='$row[Haber]'>";  
 						echo "<td>$ $row[Haber]</td>";
           	echo "<td><input type='checkbox' name='NP[]' value='$row[id]' width='20' height='20' border='0' style='float:center;' onclick='sumaselecc()' ></td>";
				     echo"</tr>";
						$total=mysql_num_rows($MuestraStock);	
					  
            }
            b:
            echo "</table>";
            echo "</div>";
            echo "<div id='agregarfp' style='display:none'><label>Agregar Forma de Pago</label><input type='checkbox' id='agregarfp' style='display:block' onClick='agregarfp()' ></div>";
            
  echo "<div id='fp2' style='display:none'><label>Forma de Pago</label>
  <input type='checkbox' id='agregarfp' style='display:block' onClick='agregarfp()' ></div>";
            
            echo "<div id='Disponible' style='display:none;'><label>Anticipo Disponible para $Nombre:</label><input name='anticipodisponible_t' size='20' type='text' value='$dato[Total]' placeholder='No hay Anticipos' readonly/></div>";
  
					//CHEQUES DE TERCEROS
             $Grupo="SELECT * FROM Cheques WHERE Utilizado=0 AND Terceros=1 ORDER BY Banco";
            $estructura= mysql_query($Grupo);
            echo "<div id='TercerosOculto' style='display:none;'><label>Cheque:</label>
            <select name='tercero_t' onchange='buscardatos()' style='float:center;width:280px;' size='1'>";
            echo "<option value=''>Seleccione un Cheque</option>";
            while ($row = mysql_fetch_array($estructura)){
            echo "<option value='".$row[id]."'>".$row[FechaCobro]." / $ ".$row[Importe]."</option>";
            }
						echo "</select></div>";
            //TRANSFERENCIA BANCARIA
            echo "<div><input name='totalfacturas_t' value='$SumarFactura' type='hidden' value='' /></div>";
            echo "<div id='oculto' style='display:none;'><label>Numero Transferencia</label><input name='numerotransferencia_t' size='20' type='text' value='' /></div>";
						echo "<div id='oculto1' style='display:none;'><label>Fecha De Transferencia:</label><input name='fechatransferencia_t' size='20' type='text' value='' /></div>";
            echo "<div id='BancoPropioOculto' style='display:none;'><label>Banco:</label><input name='bancotransferencia_t' size='20' type='text' value='' /></div>";
            //CHEQUES PROPIOS
            $Grupo="SELECT Banco FROM Cheques WHERE Utilizado=0 AND Terceros=0 GROUP BY Banco";
            $estructura= mysql_query($Grupo);
            echo "<div id='BancoOculto' style='display:none;'><label>Cheque:</label><select name='banco_t' style='float:center;width:280px;' size='1'>";
            echo "<option value=''>Seleccione un Banco</option>";				
            while ($row = mysql_fetch_array($estructura)){
            echo "<option value='".$row[Banco]."'>".$row[Banco]."</option>";
            }
            echo "</select></div>";
            echo "<div id='NumeroChequeOculto' style='display:none;'><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
						echo "<div id='FechaChequeOculto' style='display:none;'><label>Fecha De Pago:</label><input name='fechacheque_t' size='20' type='date' value='' /></div>";
  //   					//TOTAL
            echo "<div id='total'><label>Importe a Pagar:</label><input id='importepago_t' name='importepago_t' size='20' type='text' value='' /></div>";
						echo "<div><input id='ImputaPago' name='ImputaPago' class='bottom' type='submit' value='Aceptar' ></div>";
					  echo "</form>";
  
goto a;
} 
  
if($_GET['Pagoacuenta']=='Si'){	
		if($_POST['razonsocial_pagoacuenta']==''){
			echo "<form class='login' action='' method='POST' style='float:center; width:500px;'>";
			echo "<div><label style='float:center;color:red;font-size:22px'>Pago a Cuenta</label></div>";
      echo "<div><hr></hr></div>";
			$Grupo="SELECT RazonSocial,Cuit FROM Proveedores ORDER BY RazonSocial ASC";
			$estructura= mysql_query($Grupo);
			echo "<div><label>Razon Social:</label><select name='razonsocial_pagoacuenta' onchange='submit()' 
			style='float:center;width:390px;' size='1'>";
			echo "<option>SELECCIONE PROVEEDOR</option>";

			while ($row = mysql_fetch_row($estructura)){
				echo "<option value='".$row[1]."'>".$row[0]."</option>";
			}
			echo "</select></div>";
		echo "</form>";	
		goto a;
			}else{	
			$Cuit=$_POST['razonsocial_pagoacuenta'];
			$datosEncontrados="SELECT * FROM Proveedores WHERE Cuit='$Cuit'";
			$estructura2= mysql_query($datosEncontrados);
				while ($row = mysql_fetch_row($estructura2)){
				$idProveedor=$row[0];
        $Cuit1=$row[12];
				$Nombre=$row[2];
				$Direccion=$row[3];	
				}	
						
			$Fecha=date('d/m/Y');
	
      echo "<form name='formularioy' class='login' action='' method='GET' style='float:center; width:600px;'>";
			$CR=$_GET['CR'];						
	
			echo "<div><titulo>Cargar pago a Factura de $Nombre</titulo></div>";
      echo "<div><hr></hr></div>";
			echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' required/></div>";
			echo "<div><label>Razon Social:</label><input name='razonsocial_t' size='50' type='text' value='$Nombre' OnFocus='this.blur()'></div>";
			echo "<div><label>Direccion:</label><input name='direccion_t' size='50' type='text' value='$Direccion' OnFocus='this.blur()'></div>";
			echo "<input type='hidden' name='cuit_t' value='$Cuit'>";
			echo "<input type='hidden' name='idproveedor_t' value='$idProveedor'>";
					
						//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
						$BuscaNumAsiento= mysql_query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
						$row = mysql_fetch_row($BuscaNumAsiento); 
						$NAsiento = trim($row[0])+1;
				
						echo "<div><label>Numero de Asiento:</label><input name='nasiento_t' size='20' type='text' value='$NAsiento' /></div>";
// 					echo "<label><a href='http://www.triangularlogistica.com.ar/SistemaTriangular/VentanaAsientos.php?Factura=$Nc&Pant=Compras'>Ver Asientos!</a></label></div>";
						$Grupo="SELECT id,FormaDePago,CuentaContable FROM FormaDePago WHERE FormaDePago<>'ANTICIPO A PROVEEDORES' ORDER BY FormaDePago ASC";
						$estructura= mysql_query($Grupo);
						echo "<div><label>Forma de Pago:</label><select name='formadepago_t' onchange='mostrary(this)' style='float:center;width:280px;' size='1'>";
						while ($row = mysql_fetch_row($estructura)){
										
						echo "<option value='".$row[0]."'";
						if($row[0]=='3' ) {
				    echo "selected";
						}
						echo ">".$row[1]."</option>";
						}
						echo "</select></div>";
       					//CHEQUES DE TERCEROS
             $Grupo="SELECT * FROM Cheques WHERE Utilizado=0 AND Terceros=1 GROUP BY Banco";
            $estructura= mysql_query($Grupo);
            echo "<div id='TercerosOculto' style='display:none;'><label>Cheque:</label>
            <select name='tercero_t' onchange='buscardatos()' style='float:center;width:280px;' size='1'>";
            echo "<option value=''>Seleccione un Cheque</option>";
            while ($row = mysql_fetch_array($estructura)){
            echo "<option value='".$row[id]."'>".$row[FechaCobro]." / $ ".$row[Importe]."</option>";
            }
						echo "</select></div>";

      					//CHEQUES PROPIOS
            $Grupo="SELECT Banco FROM Cheques WHERE Utilizado=0 AND Terceros=0 GROUP BY Banco";
							$estructura= mysql_query($Grupo);
							echo "<div id='BancoOculto' style='display:none;'><label>Banco:</label><select name='banco_t' style='float:center;width:280px;' size='1'>";
							echo "<option value=''>Seleccione un Banco</option>";				
              while ($row = mysql_fetch_array($estructura)){
							echo "<option value='".$row[Banco]."'>$row[Banco]</option>";
							}
						echo "</select></div>";
            echo "<div id='NumeroChequeOculto' style='display:none;'><label>Numero de Cheque:</label><input name='numerocheque_t' size='20' type='text' value='' /></div>";
						echo "<div id='FechaChequeOculto' style='display:none;'><label>Fecha De Pago:</label><input name='fechacheque_t' size='20' type='date' value='' /></div>";
          
            //TRANSFERENCIA BANCARIA
            echo "<div><input name='totalfacturas_t' value='$SumarFactura' type='hidden' value='' /></div>";
            echo "<div id='oculto' style='display:none;'><label>Numero Transferencia</label><input name='numerotransferencia_t' size='20' type='text' value='' /></div>";
						echo "<div id='oculto1' style='display:none;'><label>Fecha De Transferencia:</label><input name='fechatransferencia_t' size='20' type='text' value='' /></div>";
            echo "<div id='BancoPropioOculto' style='display:none;'><label>Banco:</label><input name='bancotransferencia_t' size='20' type='text' value='' /></div>";
            //TOTAL
            echo "<div id='total'><label>Importe a Pagar 1501:</label><input name='importepago_t' size='20' type='text' value='$Total' /></div>";
            echo "<div><input name='ImputaPagoACuenta' class='bottom' type='submit' value='Aceptar' ></label></div>";
					  echo "</form>";
						goto a;
						}		
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