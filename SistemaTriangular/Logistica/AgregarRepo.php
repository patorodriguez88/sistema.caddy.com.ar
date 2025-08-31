<?php
ob_start();
session_start();
include_once "../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');
//FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS
function generarCodigo($longitud) {
 $key = '';
//  $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
 $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  
 $max = strlen($pattern)-1;
 for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
 return $key;
}

function cargaventa($id,$clienteorigen,$Recorrido){
// DEFINO EL CLIENTE ORIGEN

//Genero el ultimo numero para la reposicion
$BuscaNumRepo= $mysqli->query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
if ($row = $BuscaNumRepo->fetch_array()) {
 $NRepo = trim($row[NumeroRepo])+1;
 }

  if ($NumeroRepo==''){
  $NumeroRepo=$NRepo;
  $_SESSION['NumeroRepo']=$NumeroRepo;
  }

//PRIMERO DEFINO LAS VARIABLES
    $Usuario=$_SESSION['Usuario'];
    $fecha=date('Y-m-d');

    $idHojaDeRuta=$clientefijo_t;//ID DE Hoja de Ruta

    $idClienteOrigenPreVenta=$clienteorigen_t;
    $NumeroPedido=generarCodigo(9);
  
    $buscodatos=$mysqli->query("SELECT * FROM HojaDeRuta WHERE id='$id'");
    while($datobuscodatos=mysql_fetch_array($buscodatos)){
    //     print $datobuscodatos[idCliente];
        // BUSCO LOS DATOS DEL CLIENTE DE ORIGEN
        $BuscarClienteOrigen=$mysqli->query("SELECT * FROM Clientes WHERE id='$clienteorigen';");
        $rowO = mysql_fetch_array($BuscarClienteOrigen);
          $_SESSION['idClienteOrigen_t']=$rowO[id];	//id
          $_SESSION['NCliente']=$rowO[Cuit];	//CUIT
          $_SESSION['NombreClienteOrigen_t']=$rowO[nombrecliente];//NOMBRE CLIENTE
          $_SESSION['OrdenCliente_t']=$rowO[Orden];//Provincia Destino
          $_SESSION['DomicilioEmisor_t']=$rowO[Direccion];//Domicilio
          $_SESSION['SituacionFiscalEmisor_t']=$rowO[SituacionFiscal];//SituacionFiscal
          $_SESSION['LocalidadOrigen_t']=$rowO[Ciudad];//Ciudad
          $_SESSION['ProvinciaOrigen_t']=$rowO[Provincia];//Provincia Destino
          $_SESSION['TelefonoEmisor_t']=$rowO[Celular];//Telefono
        	
    // BUSCO LOS DATOS DEL CLIENTE DESTINO  
        $BuscarCliente=$mysqli->query("SELECT * FROM Clientes WHERE id='$datobuscodatos[idCliente]';");
        $row = mysql_fetch_array($BuscarCliente);
          $_SESSION['idClienteDestino_t']=$row[id];	//id
          $_SESSION['NClienteDestino_t']=$row[Cuit];	//CUIT
          $_SESSION['NombreClienteDestino_t']=$row[nombrecliente];//NOMBRE CLIENTE
          $_SESSION['DomicilioDestino_t']=$row[Direccion];//Domicilio
          $_SESSION['SituacionFiscalDestino_t']=$row[SituacionFiscal];//SituacionFiscal
          $_SESSION['TelefonoDestino_t']=$row[Celular];//Telefono
          $_SESSION['LocalidadDestino_t']=$row[Ciudad];//Ciudad
          $_SESSION['ProvinciaDestino_t']=$row[Provincia];//Provincia Destino
          $_SESSION['OrdenCliente_t']=$row[Orden];//Provincia Destino
        
// INGRESA EN VENTAS
$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,idPreVenta,NVentaWeb)
VALUES('{$Codigo}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$ClienteOrigen}',
'{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$idPreVenta[$i]}','{$NVentaWeb}')";
$mysqli->query($sql);

      
//INGRESA EN TRANSCLIENTES
    $Fecha=date('Y-m-d');
    $CuitClienteA=$_SESSION['NCliente'];	//CUIT
	$TipoDeComprobante='Remito'; 
    $Compra='0';
    $Haber='0';
    $ClienteDestino=$_SESSION['NombreClienteDestino_t'];//NOMBRE CLIENTE
    $CuitDestino=$_SESSION['NClienteDestino_t'];
    $DomicilioDestino=$_SESSION['DomicilioDestino_t'];//Domicilio
	$LocalidadDestino=$_SESSION['LocalidadDestino_t'];//Ciudad
    $ProvinciaDestino=$_SESSION['ProvinciaDestino_t'];//Provincia Destino
    $SituacionFiscalDestino=$_SESSION['SituacionFiscalDestino_t'];//SituacionFiscal
    $IngBrutosDestino='';
    $TelefonoDestino=$_SESSION['TelefonoDestino_t'];//Telefono
    $ClienteOrigen=$_SESSION['NombreClienteOrigen_t'];//Nombre Cliente Origen
	$DomicilioOrigen=$_SESSION['DomicilioEmisor_t'];//Domicilio
    $SituacionFiscalOrigen=$_SESSION['SituacionFiscalEmisor_t'];//SituacionFiscal
    $LocalidadOrigen=$_SESSION['LocalidadOrigen_t'];
    $ProvinciaOrigen=$_SESSION['ProvinciaOrigen_t'];//ProvinciaOrigen
    $IdOrigen=$_SESSION['idClienteOrigen_t'];//id
    $TelefonoOrigen=$_SESSION['TelefonoEmisor_t'];//Telefono
    $FormaDePago='Origen';
    $EntregaEn='Domicilio';
    $CodigoProveedor=$_GET['codigoproveedor_t'];
    $Observaciones=$_GET['observaciones'];
	$Trasnsportista='No Asignado';	
  
    $Cero='0';
    
    $Retiro=$_GET['retiro_t'];//0 Retiro y Entrega 1 Solo Entrega

    $IngresaTransaccion="INSERT INTO 
    TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,CompraMercaderia,Debe,Haber,
    ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,IngBrutosDestino,TelefonoDestino,
    CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
    FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Transportista,Recorrido,ProvinciaDestino,ProvinciaOrigen,Estado)
    VALUES('{$Fecha}','{$ClienteOrigen}','{$CuitClienteA}',
    '{$TipoDeComprobante}','{$NumeroRepo}','{$Compra}','{$Total}','{$Haber}','{$ClienteDestino}','{$CuitDestino}',
    '{$DomicilioDestino}','{$LocalidadDestino}','{$SituacionFiscalDestino}','{$IngBrutosDestino}','{$TelefonoDestino}',
    '{$NumeroPedido}','{$NumeroRepo}','{$Cantidad[$i]}','{$DomicilioOrigen}','{$SituacionFiscalOrigen}','{$LocalidadOrigen}',
    '{$IdOrigen}','{$TelefonoOrigen}','{$FormaDePago}','{$EntregaEn}','{$Usuario}','{$CodigoProveedor}','{$Observaciones}',
    '{$Transportista}','{$Recorrido}','{$ProvinciaDestino}','{$ProvinciaOrigen}','En Origen')";
    $mysqli->query($IngresaTransaccion);
  
    //OBTENGO EL ULTIMO ID DE TRANSCLIENTES;
    $idTransClientes=mysql_insert_id();
  
    // INGRESA MOVIMIENTO EN HOJA DE RUTA
    $Asignado='Unica Vez';
    $EstadoH='Abierto';
    $NOrden=$NumeroRepo;

    //DETECTO LA FECHA DE SALIDA Y EL NUMERO DE ORDEN DE LOGISTICA
    $sqlveorecabierto=$mysqli->query("SELECT Fecha,NumerodeOrden FROM Logistica WHERE Recorrido='$Recorrido' AND Estado IN('Alta','Cargada') AND Eliminado=0");
    $datoveorecabierto=mysql_fetch_array($sqlveorecabierto);
    $FechaSalida=$datoveorecabierto[Fecha];
    $NOrdenLogistica=$datoveorecabierto[NumerodeOrden];
    
    $Pais='Argentina';
    $idCliente=$_SESSION['idClienteDestino_t'];
    if($_SESSION['OrdenCliente_t']=='0'){
    $sql=$mysqli->query("SELECT MAX(Posicion)as Posicion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto'");  
    $Dato=mysql_fetch_array($sql);	
    $Orden = trim($Dato[Posicion])+1;
    }else{
    $Orden=$_SESSION['OrdenCliente_t'];
	  }	
    $Ingresahojaderuta="INSERT INTO `HojaDeRuta`(
    `Fecha`,
    `Recorrido`,
    `Localizacion`,
    `Ciudad`,
    `Provincia`,
    `Pais`,
    `Cliente`,
    `Titulo`,
    `Observaciones`,
    `Usuario`,
    `Asignado`,
    `Estado`,
    `NumerodeOrden`,
    `Seguimiento`,
    `idCliente`,
    `Posicion`,
    `Celular`,
    `NumeroRepo`,`idTransClientes`)VALUES ('{$Fecha}','{$Recorrido}','{$DomicilioDestino}','{$LocalidadDestino}','{$ProvinciaDestino}','{$Pais}',
    '{$ClienteDestino}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}','{$NOrdenLogistica}',
    '{$NumeroPedido}','{$idCliente}','{$Orden}','{$TelefonoDestino}','{$NOrden}','{$idTransClientes}')";
		$mysqli->query($Ingresahojaderuta);

//INGRESA EN ROADMAP
    $IngresaRoadMap="INSERT INTO `Roadmap`(
    `Fecha`,
    `Recorrido`,
    `Localizacion`,
    `Ciudad`,
    `Provincia`,
    `Pais`,
    `Cliente`,
    `Titulo`,
    `Observaciones`,
    `Usuario`,
    `Asignado`,
    `Estado`,
    `NumerodeOrden`,
    `Seguimiento`,
    `idCliente`,
    `Posicion`,
    `Celular`,
    `NumeroRepo`,`idTransClientes`)VALUES ('{$Fecha}','{$Recorrido}','{$DomicilioDestino}','{$LocalidadDestino}','{$ProvinciaDestino}','{$Pais}',
    '{$ClienteDestino}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}','{$NOrdenLogistica}',
    '{$NumeroPedido}','{$idCliente}','{$Orden}','{$TelefonoDestino}','{$NOrden}','{$idTransClientes}')";
	 $mysqli->query($IngresaRoadMap);

//INGRESA EN SEGUIMIENTO
 		$Fecha= date("Y-m-d");	
		$Hora=date("H:i"); 

		$Sucursal=$_SESSION['Sucursal'];
		$Estado='En Origen';
    if($Observaciones==''){
    $Observaciones='Ya tenemos tu pedido!';  
    }
      $sqlSeg="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado)
		VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$NumeroPedido}','{$Observaciones}','{$Entregado}','{$Estado}')";
		$mysqli->query($sqlSeg);
  //INGRESA EN CTAS CTES
    $sql=$mysqli->query("SELECT SUM(Total)as Total FROM Ventas WHERE NumeroRepo='$NumeroRepo'");
    $DatoTotal=mysql_fetch_array($sql);
    $Dato=($DatoTotal[Total]);
    $TipoDeComprobante='Servicios de Logistica';
		if ($FormaDePago=='Origen'){	
		$IngresaCtasctes="INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante)VALUES
		('{$Fecha}','{$NumeroRepo}','{$cliente}','{$CuitClienteA}','{$Dato}','{$Cero}','{$Usuario}','{$TipoDeComprobante}')"; 
		
		}elseif($FormaDePago=='Destino'){
		$IngresaCtasctes="INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante)VALUES
		('{$Fecha}','{$NumeroRepo}','{$ClienteDestino}','{$CuitDestino}','{$Dato}','{$Cero}','{$Usuario},'{$TipoDeComprobante}')"; 
		}
		if($Dato!=0){	
		$mysqli->query($IngresaCtasctes);
		}

    //PONE LAS REPOSICIONES EN 1 TERMINADO   
			$Termina="UPDATE Ventas SET terminado=1 WHERE NumeroRepo='$NumeroRepo'";
			$mysqli->query($Termina);
			$NumeroRepo= ''; 
			$Comentario='';
			unset($_SESSION['NumeroPedido']);
			unset($_SESSION['NCliente']);	
			unset($_SESSION['NClienteDestino_t']);	

      
    }
}

//Busco el titulo en la tabla
// $ClienteOrigen=$_SESSION['NombreClienteOrigen_t'];

$iva1=$Total*0.21;     
$ImporteNeto=$Total-$iva1;
// ASIGNO EL CODIGO DE SEGUIMIENTO EN LA PREVENTA    
// $sqlPreventa=$mysqli->query("UPDATE PreVenta SET CodigoSeguimiento='$NumeroPedido' WHERE id='$idPreVenta[$i]'");    
    
  

//     $Cargado=$mysqli->query("UPDATE PreVenta SET Cargado=1 WHERE id='$idPreVenta[$i]'");  
  
// //  }
// // }




// if ($_GET['Eliminar']=='si'){
// $idPreVenta=$_GET['id'];
// $sql="UPDATE PreVenta SET Eliminado=1 WHERE id='$idPreVenta'";
// $mysqli->query($sql);
// // header("location:Pendientes.php");
// }
a:
// header("location:Pendientes.php");
ob_end_flush();	
?>