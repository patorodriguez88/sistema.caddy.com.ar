<?php
session_start();
include_once "../../ConexionBD.php";
date_default_timezone_set('Chile/Continental');

$NdeClienteA=$_POST['clienteemisor_t'];	
$NdeClienteB=$_POST['clientereceptor_t'];	
$CodigoProveedor=$_POST['codigoproveedor_t'];
$Observaciones=$_POST['observaciones_t'];
$Transportista=$_SESSION['NombreUsuario'];
$DatoLogistica=mysql_query("SELECT * FROM Logistica WHERE NombreChofer='$Transportista' AND Estado<>'Cerrada' AND Eliminado='0'");
$Dato=mysql_fetch_array($DatoLogistica);
$Recorrido=$Dato[Recorrido];

$Sucursal=$_SESSION['Sucursal'];
$Hora=date("H:i"); 

//DATOS CLIENTE EMISOR
$BuscarCliente="SELECT * FROM Clientes WHERE NdeCliente='$NdeClienteA';";
$BuscarClienteA=mysql_query($BuscarCliente);
		while($row = mysql_fetch_row($BuscarClienteA)){
			$CuitClienteA=$row[24];	//CUIT
			$clienteA=$row[2];//NOMBRE CLIENTE
		$_SESSION['ClienteA']=$row[2];
		$_SESSION['NClienteA']=$row[1];			
			$DomicilioA=$row[17];//Domicilio
			$SituacionFiscalA=$row[21];//SituacionFiscal
			$TelefonoA=$row[12];//Telefono
			$LocalidadA=$row[8];//Localidad
			$ProvinciaA=$row[9];//Provincia
			$_SESSION['IngBrutosOrigen_t']=$row[0];//Telefono
			}
//DATOS CLIENTE RECEPTOR
$BuscarCliente="SELECT * FROM Clientes WHERE NdeCliente='$NdeClienteB';";
$BuscarClienteA=mysql_query($BuscarCliente);
		while($row = mysql_fetch_row($BuscarClienteA)){

			$_SESSION['ClienteB']=$row[2];
			$_SESSION['NClienteB']=$row[1];			

			$CuitClienteB=$row[24];	//CUIT
			$clienteB=$row[2];//NOMBRE CLIENTE
			$DomicilioB=$row[17];//Domicilio
			$SituacionFiscalB=$row[21];//SituacionFiscal
			$TelefonoB=$row[12];//Telefono
			$LocalidadB=$row[8];//Telefono
			$ProvinciaB=$row[9];//Provincia
		}	

if ($_GET['Comentario']=='si'){
$idPedido=$_GET['id'];
$Comentario=$_GET['Info'];	
$sql="UPDATE Ventas SET Comentario='$Comentario' WHERE idPedido='$idPedido'";
mysql_query($sql);
goto a;
}

//FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS
function generarCodigo($longitud) {
 $key = '';
 $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
 $max = strlen($pattern)-1;
 for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
 return $key;
}

$BuscaRepoAbierta="SELECT NumPedido,NumeroRepo,Cliente,terminado FROM Ventas WHERE Cliente='$clienteA' AND terminado='0' 
AND FechaPedido=curdate() AND Usuario='".$_SESSION['Usuario']."'";

$MuestraRepo=mysql_query($BuscaRepoAbierta);
while($row=mysql_fetch_row($MuestraRepo)){
$NumeroRepo=$row[1];	
$NumeroPedido=$row[0];
}

//Genero un numero aleatorio para la reposicion
$BuscaNumRepo= mysql_query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
if ($row = mysql_fetch_row($BuscaNumRepo)) {
 $NRepo = trim($row[0])+1;
 }

 if ($NumeroRepo==''){
$NumeroRepo=$NRepo;
$_SESSION['NumeroRepo']=$NumeroRepo;
}

if ($NumeroPedido==''){
$NumeroPedido=generarCodigo(6);
$_SESSION['NumeroPedido']=$NumeroPedido;
}

//Primero veo que tabla usar
$OfertasPublicadas='Stock';
$n=$_POST['servicio_t'];
//Busco el titulo en la tabla
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE id='$n'";	

$datos=mysql_query($ordenar);
while($row = mysql_fetch_row($datos)){

$Codigo=$row[1];
$fecha=date('Y-m-d');
$titulo=$row[2];
$_SESSION['Servicio']=$row[2];
$_SESSION['idServicio']=$row[0];	
$edicion=$row[16];
$precio=$row[8];
$iva=$row[19];	
$Cantidad=$_POST['cantidad_t'];
$Total=$Cantidad*$precio;
$TipoDeComprobante='Servicio de Logistica';	
	if ($iva==0){
	$ImporteNeto=$Cantidad*$precio;
	}else{
	$ImporteNeto=$Cantidad*($precio/$iva);
	}

	if ($row[19]==1.025){
	$iva1=$Total-$ImporteNeto;
	$iva2='0';
	$iva3='0';	
	}elseif($row[19]==1.105){
	$iva2=$Total-$ImporteNeto;	
	$iva1='0';
	$iva3='0';	
	}elseif($row[19]==1.21){
	$iva3=$Total-$ImporteNeto;	
	$iva1='0';
	$iva2='0';	
	}
}
$Usuario=$_SESSION['Usuario'];
if($_POST['cantidad_t']>'0'){
//INGRESAR DATOS EN TABLA VENTAS
$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,Comentario)
VALUES('{$Codigo}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$clienteA}',
'{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$Observaciones}')";
mysql_query($sql);
}
if($_POST['CerrarRemito']=='Cerrar Remito'){

//INGRESAR DATOS EN TABLA CUENTAS CORRIENTES
$sql1="INSERT INTO `Ctasctes`(`Fecha`,`RazonSocial`,`Cuit`,`TipoDeComprobante`,`NumeroVenta`,`Debe`,`Usuario`)
VALUES('{$fecha}','{$clienteA}','{$CuitClienteA}','{$TipoDeComprobante}','{$NumeroRepo}','{$Total}','{$Usuario}')";
if($Total>'0'){
mysql_query($sql1);
}

// COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
$sqlredespacho=mysql_query("SELECT * FROM Localidades WHERE Localidad='$_SESSION[LocalidadDestino_t]'");
$datosql=mysql_fetch_array($sqlredespacho);
if($datosql[Web]==0){
$Redespacho=0;  // CAMBIAR A 1 CUANDO ESTE SEGURO QUE ESTAN OK TODAS LAS LOCALIDADES DE LOS CLIENTES
}else{
$Redespacho=0;   
}
  
  
//INGRESAR DATOS EN TABLA TRANSCLIENTES
$sql2="INSERT INTO `TransClientes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroComprobante`, `CompraMercaderia`,
`Debe`, `ClienteDestino`, `DocumentoDestino`, `DomicilioDestino`, `LocalidadDestino`, `SituacionFiscalDestino`,`TelefonoDestino`,
`CodigoSeguimiento`, `NumeroVenta`, `DomicilioOrigen`, `SituacionFiscalOrigen`, `LocalidadOrigen`,`TelefonoOrigen`, `Cantidad`,
`Usuario`, `Entregado`, `FormaDePago`, `EntregaEn`, `Eliminado`, `CodigoProveedor`, `Observaciones`,`Transportista`, `Recorrido`,
`ProvinciaDestino`,`ProvinciaOrigen`,Redespacho)
VALUES ('{$fecha}','{$clienteA}',$CuitClienteA,'Remito','{$NumeroRepo}','0','{$Total}','{$clienteB}','{$CuitClienteB}','{$DomicilioB}',
'{$LocalidadB}','{$SituacionFiscalB}','{$TelefonoB}','{$NumeroPedido}','{$NumeroRepo}','{$DomicilioA}','{$SituacionFiscalA}',
'{$LocalidadA}','{$TelefonoA}','{$Cantidad}','{$Usuario}','0','Origen','Retira','0','{$CodigoProveedor}',
'{$Observaciones}','{$Transportista}','{$Recorrido}','{$ProvinciaB}','{$ProvinciaA}','{$Redespacho}')";
mysql_query($sql2);
	
//INGRESAR DATOS EN TABLA SEGUIMIENTO
$sql3="INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`,`Entregado`, `Estado`, `Observaciones`,`Recorrido`)
VALUES ('{$fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$NumeroPedido}','0','EnOrigen','{$Observaciones}','{$Recorrido}')";
mysql_query($sql3);

// INGRESA MOVIMIENTO EN HOJA DE RUTA
		$Asignado='Unica Vez';
		$EstadoH='Abierto';
		$NOrden='0';	
		$Ingresahojaderuta="INSERT INTO `HojaDeRuta`(`Fecha`,`Recorrido`, `Localizacion`, `Cliente`, `Titulo`, `Observaciones`,
		`Usuario`, `Asignado`, `Estado`, `NumerodeOrden`)
		VALUES ('{$fecha}','{$Recorrido}','{$DomicilioB}','{$clienteB}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}','{$NOrden}')";
		mysql_query($Ingresahojaderuta);
		$_SESSION['ClienteA']='';
		$_SESSION['ClienteB']='';
		$_SESSION['NClienteA']='';
		$_SESSION['NClienteB']='';



	//MODIFICA LAS REPOSICIONES A TERMINADO		
		$Termina0="SELECT * FROM Ventas WHERE Cliente='$clienteA' AND fechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."'";
		$Termina1=mysql_query($Termina0);
			while($row = mysql_fetch_row($Termina1)){
			$Termina="UPDATE Ventas SET terminado=1 WHERE idPedido='$row[0]'";
			mysql_query($Termina);
			}
// 			unset($_SESSION['NumeroRepo']); 	
			$NumeroRepo= ''; 
			$Comentario='';
			unset($_SESSION['NumeroPedido']);
			unset($_SESSION['NCliente']);	
			unset($_SESSION['NClienteDestino_t']);	
		
}


if ($_GET['Eliminar']=='si'){
	$idPedido=$_GET['id'];
$sql="DELETE FROM Ventas WHERE idPedido='$idPedido'";
mysql_query($sql);
// header("location:Reposiciones.php?buscador=$busq");
header("location:Entregas.php");
}
a:
 header("location:Entregas.php");

?>
