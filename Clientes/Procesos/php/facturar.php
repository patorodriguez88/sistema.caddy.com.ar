<?php

include_once "../../../Conexion/Conexioni.php";

//FACTURACION X REMITO
if(isset($_POST['Facturar'])){

  //DATOS CLIENTE
  $id=$_POST['id'];
  $sqlCliente=$mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f,Observaciones_f FROM Clientes WHERE id='$id'");  
  $datoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial=$datoCliente[nombrecliente];  
  $Cuit=$datoCliente[Cuit];
  $Observaciones_f=$datoCliente['Observaciones_f'];

  $sqlTipo=$mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo=$sqlTipo->fetch_array(MYSQLI_ASSOC);
  
  //SI O SI DEFINIR LA CONDICION SI NO NO PERMITE FACTURAR
  if($datosqlTipo['Codigo']==''){

  echo json_encode(array('success'=>3)); 
    
  }else{

  // DESDE ACA PARA SUBIR EL ARCHIVO
  $TipoDeComprobante=$_POST['tipodecomprobante_t'];

  if($_POST['tipodecomprobante_t']=='FACTURAS A'){
    $TCA='FA';
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE CREDITO A'){
    $TCA='NCA';  
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE DEBITO A'){
    $TCA='NDA';    
  }elseif($_POST['tipodecomprobante_t']=='FACTURAS B'){
    $TCA='FB';    
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE DEBITO B'){
    $TCA='NDB';    
  }

  //DESDE ACA FACTURACION AFIP POR REMITO
  
  //VALORES PARA LA FACTURA  
  $Documento=$_POST['Documento'];
  $NumeroDocumento=$_POST['NumeroDocumento'];
  $ImpTotal=$_POST['ImpTotal'];
  $ImpTotalConc=$_POST['ImpTotalConc'];
  $ImpTrib=$_POST['ImpTrib'];

  $Codigo='0000000028';
  $Titulo=$_POST['titulo_t'];
    
  if (!empty($_POST[fecha])) { // <= false
  $Fecha=$_POST[fecha];
  } else {
  $Fecha=date('Y-m-d');
  }

  $TipoDeComprobante=$_POST['Comprobante'];  
  
  $NumeroComp_Afip=str_pad($_POST['NumeroComprobante'], 8, '0', STR_PAD_LEFT);   

//   if($PtoVta){

    $PtoVta=str_pad($_POST['PtoVta'], 5, '0', STR_PAD_LEFT);   
    
    $NumeroComprobante=$PtoVta."-".$NumeroComp_Afip;  
  
//   }else{
    
    // $NumeroComprobante=$NumeroComp_Afip;  

//   }
  
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
  
	$ImporteNeto=$_POST['ImpNeto']*$Valor;
	$Iva3=$_POST['ImpIva']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['ImpTotal']*$Valor;
    $Cantidad=$_POST['cantidad_t'];
    $Usuario=$_SESSION['NombreUsuario'];
    $Sucursal=$_SESSION['Sucursal'];  
    $Terminado='1';
    $Observaciones=$_POST['observaciones_t'];
    $Observaciones_ctasctes=$_POST['Observaciones_ctasctes'];
    $Precio=$ImporteNeto/$Cantidad;

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1='112200';	
    $Cuenta2='410100';	
    $NombreCuenta1='DEUDORES POR VENTAS';
    $NombreCuenta2='VENTAS';
    $Haber=$Total-$Iva1-$Iva2-$Iva3;
    $Debe=$Total;

	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento=$mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row[NumeroAsiento])+1;
    $Observaciones="Facturacion x Remito ".$TipoDeComprobante." ".$NumeroComprobante; 
  
   $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql1);
  
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql2);
  
  if(($Iva1+$Iva2+$Iva3)>'0'){
  $Cuenta1='213200';	
  $NombreCuenta1='IVA DEBITO FISCAL';
  $Importe=($Iva1+$Iva2+$Iva3);
    $sql3="INSERT INTO `Tesoreria`(
     Fecha,
     NombreCuenta,
     Cuenta,
     Debe,
     Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
    $mysqli->query($sql3);// SE ELMINA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
  }	

  $CAE=$_POST['CAE'];
  $FechaVencimientoCAE=$_POST['FechaVencimientoCAE'];
  $RazonSocial_f=$datoCliente['RazonSocial_f'];
  $Cuit_f=$datoCliente['Cuit_f'];
  $TipoDeComprobante_f=$datosqlTipo['Descripcion'];
  
  $Comprobante=$_POST['Comprobante'];
  
  //DESDE ACA FECHA VENCIMIENTO PAGO  
  $fechaOriginal = $_POST['Vencpago'];

  // Dividir la cadena de fecha en partes usando '/'
  $partesFecha = explode('/', $fechaOriginal);

  // Crear un nuevo formato de fecha y reorganizar las partes
  $fechaFormateada = $partesFecha[2] . '-' . $partesFecha[1] . '-' . $partesFecha[0];
    
  $Vencpago=$fechaFormateada;
  
 //INGRESO LA INFO A TABLA FACTURACION
  $sql_table_facturacion="INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente,Observaciones,Vencimiento)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}','{$Observaciones_f}','{$Vencpago}')";
  $mysqli->query($sql_table_facturacion);

  if($Comprobante<>'FACTURA PROFORMA'){ 

  $sql4="INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}')";
  $mysqli->query($sql4);
  $id_iva = $mysqli->insert_id;

  $SQL="UPDATE `AfipTipoDeComprobante` SET `NumeroComprobante`='$NumeroComprobante' WHERE Descripcion='$Comprobante'";
  $mysqli->query($SQL);

  }
    
//HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA  
  $OrdenN=$_POST['Remitos']; 

  $sqlCtasctes="INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
  `Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`) VALUES 
  ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observaciones_ctasctes}',
  '{$id}','1','{$id_iva}')";


if($mysqli->query($sqlCtasctes)){

    $idFacturado=$mysqli->insert_id;

    for($i=0;$i<count($OrdenN);$i++)
    {
    
    // ACTUALIZO FACTURADO 
    $sql=$mysqli->query("UPDATE `TransClientes` SET Facturado=1, `ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante'
    WHERE id='$OrdenN[$i]' AND Eliminado='0'");

    $sql=$mysqli->query("SELECT NumeroComprobante FROM TransClientes WHERE id='$OrdenN[$i]' AND Eliminado='0'");
    $Dato=$sql->fetch_array(MYSQLI_ASSOC);

    $Observ=$Observ.' | '.$Dato['NumeroComprobante'];
    
    $sqlCtasctesE="UPDATE Ctasctes SET idFacturado='$idFacturado',Facturado=1, NumeroFactura='$NumeroComprobante' WHERE Haber=0 AND NumeroVenta='$Dato[NumeroComprobante]' AND idCliente='$id' AND Eliminado=0 LIMIT 1";
    $mysqli->query($sqlCtasctesE);// ACTUALIZO LOS REMITOS X EL TIPO DE COMPROBANTE
    }

    //CREAR TAREA ASANA
    include_once "../../../Empleados/Procesos/php/asana_api.php";
    $TotalAsana = number_format($Total, 2, ',', '.');
    $due_on = date('Y-m-d', strtotime($Vencpago));
    $name="COBRANZA CLIENTE".$RazonSocial;    
    $notes="Realizar el control de la cobranza del comprobante ".$Comprobante.' '.$NumeroComprobante.' por un importe de '.$TotalAsana.' con fecha de vencimiento el '.$Vencpago;
    $assignee='734348738194841';
    $Projects='1202454550277567';
    $Workspaces='734348733635084';

    Create_task($Projects,$name,$notes,$due_on,$assignee,$Workspaces);

    echo json_encode(array('success'=>1));
  }  
}

}


//FACTURACION X RECORRIDO

if($_POST['Facturar']==2){

  //DATOS CLIENTE
  $id=$_POST['id'];
  $sqlCliente=$mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f,Observaciones_f FROM Clientes WHERE id='$id'");  
  $datoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial=$datoCliente['nombrecliente'];  
  $Cuit=$datoCliente['Cuit'];
  $Observaciones_f=$datoCliente['Observaciones_f'];  
  $Observaciones_ctasctes=$_POST['Observaciones_ctasctes'];

  $sqlTipo=$mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo=$sqlTipo->fetch_array(MYSQLI_ASSOC);
  
  if($datosqlTipo[Codigo]==''){
  echo json_encode(array('success'=>3)); 
    
  }else{
  // DESDE ACA PARA SUBIR EL ARCHIVO
  $TipoDeComprobante=$_POST[tipodecomprobante_t];
  if($_POST[tipodecomprobante_t]=='FACTURAS A'){
  $TCA='FA';
  }elseif($_POST[tipodecomprobante_t]=='NOTAS DE CREDITO A'){
  $TCA='NCA';  
  }elseif($_POST[tipodecomprobante_t]=='NOTAS DE DEBITO A'){
  $TCA='NDA';    
  }elseif($_POST[tipodecomprobante_t]=='FACTURAS B'){
  $TCA='FB';    
  }elseif($_POST[tipodecomprobante_t]=='NOTAS DE CREDITO B'){
  $TCA='NCB';    
  }elseif($_POST[tipodecomprobante_t]=='NOTAS DE DEBITO B'){
  $TCA='NDB';    
  }

  //DESDE ACA FACTURACION AFIP POR REMITO
  
  //VALORES PARA LA FACTURA  
  $Documento=$_POST['Documento'];
  $NumeroDocumento=$_POST['NumeroDocumento'];
  $ImpTotal=$_POST['ImpTotal'];
  $ImpTotalConc=$_POST['ImpTotalConc'];
  $ImpTrib=$_POST['ImpTrib'];

  $Codigo='0000000028';
  $Titulo=$_POST['titulo_t'];
    
  if (!empty($_POST[fecha])) { // <= false
  $Fecha=$_POST[fecha];
  } else {
  $Fecha=date('Y-m-d');
  }
  
  $NumeroComprobante=$_POST['NumeroComprobante'];

  if($_POST[condicion]=='001'){
  $TipoDeComprobante="FACTURAS A";  
  $NumeroComprobante=$_POST['NumeroComprobante'];
  }
  
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
  
	$ImporteNeto=$_POST['ImpNeto']*$Valor;
	$Iva3=$_POST['ImpIva']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['ImpTotal']*$Valor;
	$Cantidad=$_POST['cantidad_t'];
	$Usuario=$_SESSION['NombreUsuario'];
    $Sucursal=$_SESSION['Sucursal'];  
	$Terminado='1';
	$Observaciones=$_POST['observaciones_t'];
    $Precio=$ImporteNeto/$Cantidad;

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1='112200';	
    $Cuenta2='410100';	
    $NombreCuenta1='DEUDORES POR VENTAS';
    $NombreCuenta2='VENTAS';
    $Haber=$Total-$Iva1-$Iva2-$Iva3;
    $Debe=$Total;

	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento=$mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row[NumeroAsiento])+1;
    $Observaciones="Facturacion x Remito ".$TipoDeComprobante." ".$NumeroComprobante; 
  
   $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql1);
  
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql2);
  
    if(($Iva1+$Iva2+$Iva3)>'0'){
        $Cuenta1='213200';	
        $NombreCuenta1='IVA DEBITO FISCAL';
        $Importe=($Iva1+$Iva2+$Iva3);
        $sql3="INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Debe,
        Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
        $mysqli->query($sql3);
    }	

        $RazonSocial_f=$datoCliente['RazonSocial_f'];
        $Cuit_f=$datoCliente['Cuit_f'];
        $TipoDeComprobante_f=$datosqlTipo['Descripcion'];
        $CAE=$_POST['CAE'];
        $FechaVencimientoCAE=$_POST['FechaVencimientoCAE'];
        $Comprobante=$_POST['Comprobante'];
            
        //INGRESO LA INFO A TABLA FACTURACION
        $sql_table_facturacion="INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
        CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente,Observaciones)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
        '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}','{$Observaciones_f}')";
        $mysqli->query($sql_table_facturacion);
        

        if($Comprobante<>'FACTURA PROFORMA'){  
        $sql4="INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
        CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
        '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}')";
        $mysqli->query($sql4);
        $id_iva = $mysqli->insert_id;
        }

//HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA  
$OrdenN=$_POST['Remitos']; 

$sqlCtasctes="INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
`Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`,`FacturacionxRecorrido`) VALUES 
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observaciones_ctasctes}',
'{$id}','1','{$id_iva}','1')";

if($mysqli->query($sqlCtasctes)){

  $idFacturado=$mysqli->insert_id;

  $cuento=0;

    for($i=0;$i<count($OrdenN);$i++)
    {
      //OBTENGO EL NUMERO DE ORDEN
      $sqlOrden=$mysqli->query("SELECT NumerodeOrden,Recorrido FROM Logistica INNER JOIN Ctasctes ON Ctasctes.idLogistica=Logistica.id WHERE Logistica.Eliminado=0 AND Ctasctes.id='$OrdenN[$i]'");
      $datosOrden=$sqlOrden->fetch_array(MYSQLI_ASSOC);
  
      if($datosOrden['NumerodeOrden']<>0){
  
      // ACTUALIZO FACTURADO SI DEBE ES CERO (FACTURACION X RECORRIDO)
      $sql=$mysqli->query("UPDATE `TransClientes` SET Facturado=1, `ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante' 
      WHERE IngBrutosOrigen='$_POST[id]' AND NumerodeOrden='$datosOrden[NumerodeOrden]' AND Eliminado='0' AND Debe='0' AND Haber='0'");
    
      //BUSCO EL IMPORTE DEL RECORRIDO ASI LO INCLUYO EN EL CAMPO ImporteF
      $sqlPrecio=$mysqli->query("SELECT PrecioVenta FROM Productos INNER JOIN Recorridos ON Productos.Codigo=Recorridos.CodigoProductos WHERE Recorridos.Numero='$datosOrden[Recorrido]'");  
      $precioOrden=$sqlPrecio->fetch_array(MYSQLI_ASSOC);


      // ACTUALIZO LOS DATOS DE FACTURACION EN LOGISTICA
      $sql_1=$mysqli->query("UPDATE `Logistica` SET `Facturado`=1,`FechaF`='$Fecha',`ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante',`TotalFacturado`='$Total',`ImporteF`='$precioOrden[PrecioVenta]' 
      WHERE Eliminado=0 AND NumerodeOrden='$datosOrden[NumerodeOrden]' AND Facturado=0");
      
    }
  
      $sqlCtasctesE="UPDATE Ctasctes SET FacturacionxRecorrido=1,idFacturado='$idFacturado',Facturado=1,TipoDeComprobante='$Comprobante',NumeroFactura='$NumeroComprobante' WHERE id='$OrdenN[$i]' LIMIT 1";
    
      if($mysqli->query($sqlCtasctesE)){

      $cuento++;  

      }
  
    }
  
    if($cuento<>0){
     echo json_encode(array('success'=>1,'cuento'=>$cuento));
    }  
    }
  }
}




//GENERAR COMPROBANTES ND NC
if($_POST['Facturar']==3){

  //DATOS CLIENTE
  $id=$_POST[id];
  $sqlCliente=$mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f FROM Clientes WHERE id='$id'");  
  $datoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial=$datoCliente[nombrecliente];  
  $Cuit=$datoCliente[Cuit];

  $sqlTipo=$mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo=$sqlTipo->fetch_array(MYSQLI_ASSOC);
  
  //SI O SI DEFINIR LA CONDICION SI NO NO PERMITE FACTURAR
  if($datosqlTipo['Codigo']==''){

  echo json_encode(array('success'=>3)); 
    
  }else{

  // DESDE ACA PARA SUBIR EL ARCHIVO
  $TipoDeComprobante=$_POST['tipodecomprobante_t'];

  if($_POST['tipodecomprobante_t']=='FACTURAS A'){
    $TCA='FA';
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE CREDITO A'){
    $TCA='NCA';  
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE DEBITO A'){
    $TCA='NDA';    
  }elseif($_POST['tipodecomprobante_t']=='FACTURAS B'){
    $TCA='FB';    
  }elseif($_POST['tipodecomprobante_t']=='NOTAS DE DEBITO B'){
    $TCA='NDB';    
  }

  //DESDE ACA FACTURACION AFIP POR REMITO
  
  //VALORES PARA LA FACTURA  
  $Documento=$_POST['Documento'];
  $NumeroDocumento=$_POST['NumeroDocumento'];
  $ImpTotal=$_POST['ImpTotal'];
  $ImpTotalConc=$_POST['ImpTotalConc'];
  $ImpTrib=$_POST['ImpTrib'];

  $Codigo='0000000028';
  $Titulo=$_POST['titulo_t'];
    
  if (!empty($_POST[fecha])) { // <= false
  $Fecha=$_POST[fecha];
  } else {
  $Fecha=date('Y-m-d');
  }
  
//   $NumeroComprobante=$_POST['NumeroComprobante'];
//   if($_POST[condicion]=='001'){
  $TipoDeComprobante=$_POST['Comprobante'];  
//   $TipoDeComprobante="FACTURAS A";  
  
  $NumeroComp_Afip=str_pad($_POST['NumeroComprobante'], 8, '0', STR_PAD_LEFT);   

  $PtoVta=str_pad($_POST['PtoVta'], 5, '0', STR_PAD_LEFT);   
  
  $NumeroComprobante=$PtoVta."-".$NumeroComp_Afip;  
// }
  
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
  
	$ImporteNeto=$_POST['ImpNeto']*$Valor;
	$Iva3=$_POST['ImpIva']*$Valor;
	$Exento=$_POST['exento_t']*$Valor;
	$Total=$_POST['ImpTotal']*$Valor;
    $Cantidad=$_POST['cantidad_t'];
    $Usuario=$_SESSION['NombreUsuario'];
    $Sucursal=$_SESSION['Sucursal'];  
    $Terminado='1';
    $Observaciones=$_POST['observaciones_t'];
    $Precio=$ImporteNeto/$Cantidad;

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1='112200';	
    $Cuenta2='410100';	
    $NombreCuenta1='DEUDORES POR VENTAS';
    $NombreCuenta2='VENTAS';
    $Haber=$Total-$Iva1-$Iva2-$Iva3;
    $Debe=$Total;

	//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento=$mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row[NumeroAsiento])+1;
    $Observaciones="Facturacion x Remito ".$TipoDeComprobante." ".$NumeroComprobante; 
  
   $sql1="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql1);
  
 	$sql2="INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
 	$mysqli->query($sql2);
  
  if(($Iva1+$Iva2+$Iva3)>'0'){
  $Cuenta1='213200';	
  $NombreCuenta1='IVA DEBITO FISCAL';
  $Importe=($Iva1+$Iva2+$Iva3);
    $sql3="INSERT INTO `Tesoreria`(
     Fecha,
     NombreCuenta,
     Cuenta,
     Debe,
     Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')"; 
    $mysqli->query($sql3);// SE ELMINA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
  }	

  $CAE=$_POST['CAE'];
  $FechaVencimientoCAE=$_POST['FechaVencimientoCAE'];
  $RazonSocial_f=$datoCliente['RazonSocial_f'];
  $Cuit_f=$datoCliente['Cuit_f'];
  $TipoDeComprobante_f=$datosqlTipo['Descripcion'];
  
  $Comprobante=$_POST['Comprobante'];
  
 //INGRESO LA INFO A TABLA FACTURACION
  $sql_table_facturacion="INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}')";
  $mysqli->query($sql_table_facturacion);

  if($Comprobante<>'FACTURA PROFORMA'){ 

  $sql4="INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}')";
  $mysqli->query($sql4);
  $id_iva = $mysqli->insert_id;

  $SQL="UPDATE `AfipTipoDeComprobante` SET `NumeroComprobante`='$NumeroComprobante' WHERE Descripcion='$Comprobante' LIMIT 1";
  $mysqli->query($SQL);

  }
    
//HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA  
  $OrdenN=$_POST['Remitos']; 

  $sqlCtasctes="INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
  `Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`) VALUES 
  ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observaciones_ctasctes}',
  '{$id}','1','{$id_iva}')";

    $mysqli->query($sqlCtasctes);

    echo json_encode(array('success'=>1));

    }  
}

?>