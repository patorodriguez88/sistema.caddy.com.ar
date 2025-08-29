<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
include_once "../../../Funciones/generarCodigo.php";

mysqli_set_charset($mysqli,"utf8"); 
date_default_timezone_set('America/Argentina/Buenos_Aires');

function distancia($origen,$destino){
    global $mysqli;
    //ORIGEN
$sqlOrigen=$mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion FROM Clientes WHERE id='$origen'");
$ResultadoOrigen=$sqlOrigen->fetch_array(MYSQLI_ASSOC);
    
$Origenpost=$ResultadoOrigen['Direccion'];

// $Origenpost='Andres Lamas 2479, Cordoba, Argentina';
//DESTINO
$sqlDestino=$mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion FROM Clientes WHERE id='$destino'");
$ResultadoDestino=$sqlDestino->fetch_array(MYSQLI_ASSOC);
    
$Destinopost=$ResultadoDestino['Direccion'];

// $Destinopost='Justiniano Posse 1236, Cordoba, Argentina';
$Key = 'AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8';//APY KEY GOOGLE

$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
// $Origen=$Origenpost;
// $Destino=$Destinopost;  
$Modo="driving";
$Lenguaje="es-ES";
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
$obj=json_decode($json,true);
$result=$obj['rows'][0]['elements'][0]['distance']['value'];
$result2=$obj['rows'][0]['elements'][0]['distance']['text'];
$resultduration=$obj['rows'][0]['elements'][0]['duration']['text'];
$resultduration2=$obj['rows'][0]['elements'][0]['duration']['value'];

return array('success' => 1,'distancia'=> $result,'origen'=>$Origen,'destino'=>$Destino,'duration'=>$resultduration,'distanciat'=>$result2
                      ,'duration2'=>$resultduration2);

}

$FechaActual=date('Y-m-d');
$HoraActual=date("H:i");

if($_POST['datos']==1){

    $datos_fecha=explode(' - ',$_POST['date']);
    $datos_fecha_desde=explode('/',$datos_fecha[0]);
    $datos_fecha_desde=$datos_fecha_desde[2].'-'.$datos_fecha_desde[0].'-'.$datos_fecha_desde[1];
    $datos_fecha_hasta=explode('/',$datos_fecha[1]);
    $datos_fecha_hasta=$datos_fecha_hasta[2].'-'.$datos_fecha_hasta[0].'-'.$datos_fecha_hasta[1];


    //CONTROLO ELIMINADOS
    $SQL_ELIMINADOS=$mysqli->query("SELECT id,CodigoSeguimiento FROM Colecta WHERE Fecha>='$datos_fecha_desde' AND Fecha<='$datos_fecha_hasta'");
    
    while($row_eliminados=$SQL_ELIMINADOS->fetch_array(MYSQLI_ASSOC)){

        if($row_eliminados['CodigoSeguimiento']){

        $SQL=$mysqli->query("SELECT Eliminado FROM TransClientes WHERE CodigoSeguimiento='".$row_eliminados['CodigoSeguimiento']."'");
        $dato_eliminado=$SQL->fetch_array(MYSQLI_ASSOC);
        
        if($dato_eliminado['Eliminado']==1){
         
            $mysqli->query("UPDATE Colecta SET Eliminado=1 WHERE id='".$row_eliminados['id']."' LIMIT 1");

        }
        }
    }

//TARIFA FLEX
$SQL_TARIFA=$mysqli->query("SELECT * FROM Productos WHERE Codigo='183'");
$DATO_TARIFA=$SQL_TARIFA->fetch_array(MYSQLI_ASSOC);
$tarifa_flex=$DATO_TARIFA['PrecioVenta'];

$sql="SELECT 
  IF(TransClientes.FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,
  IF(TransClientes.FormaDePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente,
  IF(TransClientes.FormaDePago='Origen',DomicilioOrigen,DomicilioDestino)as Direccion,
  TransClientes.Fecha,
  COUNT(TransClientes.id)AS Cantidad,
  SUM(ValorDeclarado)AS ValorDeclarado
  FROM TransClientes INNER JOIN Clientes ON IF(TransClientes.FormaDePago='Origen',TransClientes.IngBrutosOrigen,TransClientes.idClienteDestino)=Clientes.id
  WHERE TransClientes.Flex='1' AND TransClientes.Fecha>='$datos_fecha_desde' AND TransClientes.Fecha<='$datos_fecha_hasta' AND TransClientes.Eliminado=0 AND Clientes.Colecta=1
  GROUP BY TransClientes.Fecha,Cliente;";
  $Resultado=$mysqli->query($sql);
  $Recorrido='80';

  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

   $sql_control=$mysqli->query("SELECT id,Cantidad,CodigoSeguimiento,Fecha,Cantidad_m FROM Colecta WHERE Fecha='".$row['Fecha']."' AND idCliente='".$row['idCliente']."'");
   $dato_control=$sql_control->fetch_array(MYSQLI_ASSOC);

   $FechaOrdenada_update=split('-',$dato_control['Fecha']);
   $comentario_update='Colecta '.$FechaOrdenada_update[2].'-'.$FechaOrdenada_update[1].'-'.$FechaOrdenada_update[0].' ('.$row['Cantidad'].') envíos';
 
   //SI EXISTE EL SERVICIO
   if($dato_control['id']){
        //SI EL CODIGO DE SEGUIMIENTO ES NULL
        if($dato_control['CodigoSeguimiento']<>''){
            //SI LA CANTIDAD BUSCADA DIFIERE CON LA CANTIDAD CARGADA EN COLECTAS (YA EJECUTADO EL SERVICIO)
            if($row['Cantidad']<>$dato_control['Cantidad']){
                
            $sql=$mysqli->query("UPDATE Colecta SET Cantidad_m='".$row['Cantidad']."' WHERE id='".$dato_control['id']."'");
            $sql=$mysqli->query("UPDATE TransClientes SET Cantidad='".$row['Cantidad']."',Observaciones='".$comentario_update."' WHERE CodigoSeguimiento='".$dato_control['CodigoSeguimiento']."' LIMIT 1"); 
            $sql=$mysqli->query("UPDATE Ventas SET Cantidad='".$row['Cantidad']."',Comentario='".$comentario_update."' WHERE NumPedido='".$dato_control['CodigoSeguimiento']."' LIMIT 1");  
            
            }

        }else{

            $sql=$mysqli->query("UPDATE Colecta SET Cantidad='".$row['Cantidad']."' WHERE id='".$dato_control['id']."'");  
        }

   }else{

   $sql_carga=$mysqli->query("INSERT INTO `Colecta`(`Fecha`, `idCliente`, `Cantidad`, `Importe`,`Usuario`,`ValorDeclarado`,`Recorrido`) 
   VALUES ('{$row['Fecha']}','{$row['idCliente']}','{$row['Cantidad']}','0','{$_SESSION['Usuario']}','{$row['ValorDeclarado']}','{$Recorrido}')");  
        
    }

  }

  $sql_colecta=$mysqli->query("SELECT Colecta.*,Clientes.nombrecliente as Cliente,Clientes.Direccion as Direccion FROM Colecta 
  INNER JOIN Clientes ON Colecta.idCliente=Clientes.id WHERE Fecha>='$datos_fecha_desde' AND Fecha<='$datos_fecha_hasta'");
  $rows=array();

  while($row= $sql_colecta->fetch_array(MYSQLI_ASSOC)){
      
    $rows[]=$row;
  
  }

echo json_encode(array('data'=>$rows));

}

if($_POST['CargarVenta']==1){

    $id_colecta=$_POST['id'];

    $SQL_COLECTA=$mysqli->query("SELECT Fecha,idCliente,Cantidad,ValorDeclarado,Recorrido,CodigoSeguimiento FROM Colecta WHERE id='$id_colecta'");
    $DATO_COLECTA = $SQL_COLECTA->fetch_array(MYSQLI_ASSOC);
    
    if($DATO_COLECTA['CodigoSeguimiento']==''){

        $id_origen=$DATO_COLECTA['idCliente'];
        $id_destino='18587';//wepoint
        $codigo_seguimiento=generarCodigo(9);

        //Genero el ultimo numero para la reposicion
        $BuscaNumRepo= $mysqli->query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
        if ($row = $BuscaNumRepo->fetch_array(MYSQLI_ASSOC)) {
        $NRepo = trim($row['NumeroRepo'])+1;
        }

        //TARIFA 2 A
        $SQL_TARIFA=$mysqli->query("SELECT * FROM Productos WHERE Codigo='56'");
        $DATO_TARIFA=$SQL_TARIFA->fetch_array(MYSQLI_ASSOC);

        $SQL_CLIENTEORIGEN=$mysqli->query("SELECT * FROM Clientes WHERE id='$id_origen'");
        $DATO_CLIENTEORIGEN = $SQL_CLIENTEORIGEN->fetch_array(MYSQLI_ASSOC);

        $SQL_CLIENTEDESTINO=$mysqli->query("SELECT * FROM Clientes WHERE id='$id_destino'");
        $DATO_CLIENTEDESTINO = $SQL_CLIENTEDESTINO->fetch_array(MYSQLI_ASSOC);
        
        $codigo=$DATO_TARIFA['Codigo'];
        $fecha=$DATO_COLECTA['Fecha'];
        $hora=$HoraActual; 
        $titulo='COLECTA FLEX TARIFA '.$DATO_TARIFA['Titulo'];
        $FechaOrdenada=split('-',$DATO_COLECTA['Fecha']);
        $comentario='Colecta '.$FechaOrdenada[2].'-'.$FechaOrdenada[1].'-'.$FechaOrdenada[0].' ('.$DATO_COLECTA['Cantidad'].') envíos';
        $precio=$DATO_TARIFA['PrecioVenta'];
        $cantidad=$DATO_COLECTA['Cantidad'];
        $formadepago='Origen';
        $entregaen='Domicilio';
        $tipodecomprobante='GUIA DE CARGA';
        $numerorepo=$NRepo;
        $numpedido=$codigo_seguimiento;
        $usuario=$_SESSION['Usuario'];
        $sucursal=$_SESSION['Sucursal'];    
        $codigoproveedor='Colecta';
        $observaciones=$comentario;
        $recorrido=$DATO_COLECTA['Recorrido'];
        $retirado='0';
        $valordeclarado=$DATO_COLECTA['ValorDeclarado'];
        $fechaentrega=$fecha;
        $fechaprometida=$fecha;
        $wepoint_c=$codigo_seguimiento;
        $pais='Argentina';
        $asignado='Unica Vez';
        $estado_hdr='Abierto';
        $Estado='En Origen';

        //CLIENTE ORIGEN
        $idclienteorigen=$DATO_CLIENTEORIGEN['id'];
        $clienteorigen=$DATO_CLIENTEORIGEN['nombrecliente'];
        $cuitorigen=$DATO_CLIENTEORIGEN['Cuit'];
        $domicilioorigen=$DATO_CLIENTEORIGEN['Direccion'];
        $localidadorigen=$DATO_CLIENTEORIGEN['Localidad'];
        $situacionfiscalorigen=$DATO_CLIENTEORIGEN['SituacionFiscal'];
        $telefonoorigen=$DATO_CLIENTEORIGEN['Telefono'];
        $provinciaorigen=$DATO_CLIENTEORIGEN['Provincia'];
        
        //CLIENTE DESTINO
        $idclientedestino=$DATO_CLIENTEDESTINO['id'];
        $clientedestino=$DATO_CLIENTEDESTINO['nombrecliente'];
        $cuitdestino=$DATO_CLIENTEDESTINO['Cuit'];
        $domiciliodestino=$DATO_CLIENTEDESTINO['Direccion'];
        $localidaddestino=$DATO_CLIENTEDESTINO['Localidad'];
        $situacionfiscaldestino=$DATO_CLIENTEDESTINO['SituacionFiscal'];
        $telefonodestino=$DATO_CLIENTEDESTINO['Telefono'];
        $provinciadestino=$DATO_CLIENTEDESTINO['Provincia'];

        $distancia=distancia($id_origen,$id_destino);
        $kilometros=$distancia['distancia']/1000;

        if($cantidad<=10){
        $total=$DATO_TARIFA['PrecioVenta'];
        $importeneto=$DATO_TARIFA['PrecioVenta'];
        $iva=(($DATO_TARIFA['PrecioVenta']*21)/100);
        }else{
        $total=0;
        $importeneto=0;
        $iva=0;
        }


        //AGREGAR EN TRANSCLIENTES

        $IngresaTransaccion="INSERT INTO 
        TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Haber,
        ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,TelefonoDestino,
        CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
        FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Recorrido,ProvinciaDestino,ProvinciaOrigen,
        idClienteDestino,Retirado,Kilometros,ValorDeclarado,FechaEntrega,FechaPrometida,Wepoint_c,Redespacho,CompraMercaderia,Estado)
        VALUES('{$fecha}','{$clienteorigen}','{$cuitorigen}',
        '{$tipodecomprobante}','{$numerorepo}','{$total}','0','{$clientedestino}','{$cuitdestino}',
        '{$domiciliodestino}','{$localidaddestino}','{$situacionfiscaldestino}','{$telefonodestino}',
        '{$codigo_seguimiento}','{$numerorepo}','{$cantidad}','{$domicilioorigen}','{$situacionfiscalorigen}','{$localidadorigen}',
        '{$idclienteorigen}','{$telefonoorigen}','{$formadepago}','{$entregaen}','{$usuario}','{$codigoproveedor}','{$observaciones}',
        '{$recorrido}','{$provinciadestino}','{$provinciaorigen}','{$idclientedestino}','{$retirado}',
        '{$kilometros}','{$valordeclarado}','{$fechaentrega}','{$fechaprometida}','{$wepoint_c}','0','0','{$Estado}')";

        $mysqli->query($IngresaTransaccion);

        //obtengo el id de transclientes  
        $idTransClientes=$mysqli->insert_id;
    
        //AGREGAR EN VENTAS
        $sql="INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Comentario,Terminado,Total,Cliente,NumeroRepo,
        ImporteNeto,Iva1,NumPedido,Usuario,idCliente)
        VALUES('{$codigo}','{$fecha}','{$titulo}','{$precio}','{$cantidad}','{$comentario}','1','{$total}','{$clienteorigen}',
        '{$numerorepo}','{$importeneto}','{$iva}','{$numpedido}','{$usuario}','{$id_origen}')";
        
        $mysqli->query($sql);

        //AGREGAR EN CTASCTES
        
        $TipoDeComprobante='Servicios de Logistica';  
            
        $IngresaCtasctes="INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
        ('{$fecha}','{$numerorepo}','{$clienteorigen}','{$cuitorigen}','{$total}','{$usuario}','{$TipoDeComprobante}',
        '{$idclienteorigen}','{$idTransClientes}')"; 
            
        if($total!=0){	
        
            $mysqli->query($IngresaCtasctes);
        }
        
        //INGRESAR EN SEGUIMIENTO
        
        $observaciones_seguimiento='Ya tenemos tu pedido!';  

        $sqlSeg="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Estado,idTransClientes,Recorrido)
        VALUES('{$fecha}','{$hora}','{$usuario}','{$sucursal}','{$codigo_seguimiento}','{$observaciones_seguimiento}','{$Estado}','{$idTransClientes}','{$recorrido}')";

        $mysqli->query($sqlSeg);

        //INGRESAR EN HOJA DE RUTA
        //DETECTO LA FECHA DE SALIDA Y EL NUMERO DE ORDEN DE LOGISTICA
        $sqlveorecabierto=$mysqli->query("SELECT Fecha,NumerodeOrden FROM Logistica WHERE Recorrido='$recorrido' AND Estado IN('Alta','Cargada') AND Eliminado=0");
        $datoveorecabierto=$sqlveorecabierto->fetch_array(MYSQLI_ASSOC);
        $fechasalida=$datoveorecabierto['Fecha'];
        $nordenlogistica=$datoveorecabierto['NumerodeOrden'];
        
        $SQL_ORDEN=$mysqli->query("SELECT MAX(Posicion)as Posicion FROM HojaDeRuta WHERE Recorrido='$recorrido' AND Estado='Abierto' AND Eliminado='0'");  
        $DATO_ORDEN=$SQL_ORDEN->fetch_array(MYSQLI_ASSOC);	
        $orden = trim($DATO_ORDEN['Posicion'])+1;
        
        $Ingresahojaderuta=$mysqli->query("INSERT INTO `HojaDeRuta`(
        `Fecha`,`Recorrido`,`Localizacion`,`Ciudad`,`Provincia`,`Pais`,`Cliente`,`Titulo`,`Observaciones`,`Usuario`,
        `Asignado`,`Estado`,`NumerodeOrden`,`Seguimiento`,`idCliente`,`Posicion`,`Celular`,`NumeroRepo`,`idTransClientes`) VALUES ('{$fecha}','{$recorrido}','{$domiciliodestino}','{$localidaddestino}','{$provinciadestino}','{$pais}',
        '{$clientedestino}','{$tipodecomprobante}','{$observaciones}','{$usuario}','{$asignado}','{$estado_hdr}','{$nordenlogistica}',
        '{$codigo_seguimiento}','{$idclientedestino}','{$orden}','{$telefonodestino}','{$NRepo}',{$idTransClientes})");


        //ACTUALIZAR EN COLECTA
        $mysqli->query("UPDATE Colecta SET CodigoSeguimiento='$codigo_seguimiento' WHERE id='$id_colecta'");  

        echo json_encode(array('success'=>1,'codigo'=>$codigo_seguimiento));
    
    }else{
    
        echo json_encode(array('success'=>0));    
    }
}

if($_POST['ActualizaRecorrido']==1){
    
    $recorrido=$_POST['r'];
    $id=$_POST['id'];

    for($i=0;$i<=count($id);$i++){

        $SQL_COLECTA=$mysqli->query("UPDATE Colecta SET Recorrido='$recorrido' WHERE id='$id[$i]'");
    
    }
    echo json_encode(array('success'=>1));
}