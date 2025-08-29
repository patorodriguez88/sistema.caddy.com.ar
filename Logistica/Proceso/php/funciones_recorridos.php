<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
require_once('../../../Google/geolocalizar.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['BuscarSeguimiento']==1){
    $sql="SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]')";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
  echo json_encode(array('Estado'=>$row[Estado]));
}  

if($_POST['id_servicio']<>''){
  $id=$_POST['id_servicio'];
  $sqlservicios=$mysqli->query("SELECT id,PrecioVenta,Titulo FROM Productos WHERE id='$id'");
  $datoservicios=$sqlservicios->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta=$datoservicios['PrecioVenta'];
  $Codigo=$datoservicios['id'];  
  echo json_encode(array('success'=> 1,'PrecioVenta'=> $PrecioVenta,'Codigo'=>$Codigo,'Titulo'=>$datoservicios[Titulo]));
}


if($_POST['TotalEnvios']==1){
  $sql="SELECT COUNT(HojaDeRuta.id)as id,Logistica.NombreChofer,Logistica.Patente FROM HojaDeRuta 
  INNER JOIN Logistica ON HojaDeRuta.Recorrido=Logistica.Recorrido 
  WHERE HojaDeRuta.Recorrido='$_POST[Recorrido]' AND Logistica.Eliminado=0 AND 
  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
  AND HojaDeRuta.Eliminado=0 AND Logistica.Estado<>'Cerrada'";
  $Resultado=$mysqli->query($sql);
  $row=$Resultado->fetch_array(MYSQLI_ASSOC);
  //VECHICULO
  $sqlvehiculo="SELECT * FROM Vehiculos WHERE Dominio='$row[Patente]'";
  $Resultadovehiculo=$mysqli->query($sqlvehiculo);
  $rowvehiculo=$Resultadovehiculo->fetch_array(MYSQLI_ASSOC);
  $vehiculo=$rowvehiculo[Marca].' '.$rowvehiculo[Modelo].' '.$rowvehiculo[Color].' ('.$rowvehiculo[Dominio].')';
  echo json_encode(array('totalservicios'=>$row[id],'chofer'=>$row[NombreChofer],'vehiculo'=>$vehiculo));
}

if($_POST['BuscarDatos']==1){
    $sql="SELECT id,IngBrutosOrigen,idClienteDestino,RazonSocial,ClienteDestino,Retirado,DomicilioOrigen,DomicilioDestino,
    CodigoSeguimiento,Entregado,CobrarEnvio,CobrarCaddy FROM TransClientes WHERE id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
    $CodigoSeguimiento=$row[CodigoSeguimiento];
    $sqlhdr="SELECT Estado FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento'";
    $Resultadohdr=$mysqli->query($sqlhdr);  
    $rowhdr=$Resultadohdr->fetch_array(MYSQLI_ASSOC);

    $sqlseguimiento="SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')";
    $Resultadoseguimiento=$mysqli->query($sqlseguimiento);  
    $rowseguimiento=$Resultadoseguimiento->fetch_array(MYSQLI_ASSOC);


    if($row[Retirado]==1){
    $Domicilio=$row[DomicilioDestino]; 
    $RazonSocial=$row[ClienteDestino]; 
    $idCliente=$row[idClienteDestino];
    $Servicio='Entrega';  
    }else{
    $Domicilio=$row[DomicilioOrigen]; 
    $RazonSocial=$row[RazonSocial];
    $idCliente=$row[IngBrutosOrigen];  
    $Servicio='Retiro';
    }
  echo json_encode(array('EstadoSeguimiento'=>$rowseguimiento[Estado],'CobrarCaddy'=>$row[CobrarCaddy],'CobrarEnvio'=>$row[CobrarEnvio],'Entregado'=>$row[Entregado],'Retirado'=>$row[Retirado],'EstadoHdr'=>$rowhdr[Estado],'RazonSocial'=>$RazonSocial,'Domicilio'=>$Domicilio,'idCliente'=>$idCliente,'CodigoSeguimiento'=>$CodigoSeguimiento,'Servicio'=>$Servicio));
}

if($_POST['BuscarDatosVentas']==1){
  
  if($_POST[idPedido]<>''){
    $id=$_POST[idPedido];  
    $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Precio,Cantidad FROM Ventas WHERE idPedido='$id' AND Eliminado='0'";
  }else{
    $sql="SELECT CodigoSeguimiento FROM TransClientes WHERE id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
    $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Precio,Cantidad FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";
  }
  
    $Resultado=$mysqli->query($sql);  
    $rows=array();
    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;  
    }
   echo json_encode(array('data'=>$rows));
}
//MODIFICAR VENTAS
if($_POST['ModificarDatosVentas']==1){
$info="M: ".$_SESSION[Usuario].' | '.date('Y-m-d (h:m:s)');
    $sql="SELECT Fecha,IF(FormaDePago='Origen',RazonSocial,ClienteDestino)as RazonSocial,
                       IF(FormaDePago='Origen',Cuit,idClienteDestino)as Cuit,TipoDeComprobante,NumeroComprobante,Debe,
                       IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)as idCliente,
                       Observaciones,id,CodigoSeguimiento FROM TransClientes WHERE id='$_POST[idTrans]'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);

  
  if($mysqli->query("UPDATE Ventas SET Codigo='$_POST[codigo]',Titulo='$_POST[titulo]',Precio='$_POST[precio]',Cantidad='$_POST[cantidad]',Total='$_POST[total]',infoABM='$info' WHERE idPedido='$_POST[idPedido]'"))
  {
    $successventas=1;  
    $sqlV="SELECT SUM(Total)as Total FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";
    $ResultadoV=$mysqli->query($sqlV);  
    $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
    
    if($mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE id='$_POST[idTrans]' AND Eliminado='0' AND TipoDeComprobante='Remito'")){
      $successtrans=1;  
      
      $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$_POST[idTrans]' AND Eliminado='0' LIMIT 1");
      $result=$mysqli->affected_rows;

      if($result<>0){
        $successctasctes=1;  
      }else{
        $successctasctes=0;        
       if($rowV[Total]>0){ 
        if($mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,`Observaciones`, `idCliente`,`idTransClientes`) VALUES ('{$row[Fecha]}','{$row[RazonSocial]}','{$row[Cuit]}','{$row[TipoDeComprobante]}',
        '{$row[NumeroComprobante]}','{$rowV[Total]}','{$_SESSION[Usuario]}','{$row[Observaciones]}','{$row[idCliente]}','{$row[id]}')")){
        $successctasctesinsert=1;    
        }else{
        $successctasctesinsert=0;      
        }
       }
      }
    }else{
    $successtrans=0;    
    }
  }else{
      $successventas=0;
  }   
  echo json_encode(array('successventas'=>$successventas,'successtrans'=>$successtrans,'successctasctes'=>$successctasctes,'successctasctesinsert'=>$successctasctesinsert)); 
}
    //ELIMINAR DATOS VENTAS
if($_POST['EliminarDatosVentas']==1){
    $info="B: ".$_SESSION[Usuario].' | '.date('Y-m-d (h:m:s)');
  
    //PRIMERO OBTENGO EL NUMPEDIDO(CODIGO SEGUIMIENTO)
    $sql="SELECT NumPedido FROM Ventas WHERE idPedido='$_POST[idPedido]' AND Eliminado='0'";
    $Resultado=$mysqli->query($sql);  
    $rowventas=$Resultado->fetch_array(MYSQLI_ASSOC);               
                 
  if($mysqli->query("UPDATE Ventas SET Eliminado=1,infoABM='$info' WHERE idPedido='$_POST[idPedido]'")){
    
    $sqlV="SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$rowventas[NumPedido]' AND Eliminado='0'";
    $ResultadoV=$mysqli->query($sqlV);  
    $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
    
    if($rowV['NumPedido']<>''){
    $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$rowV[NumPedido]' AND TipoDeComprobante='Remito'");
    }
    //BUSCO ID TRANSCLIENTES
    $sql="SELECT id FROM TransClientes WHERE CodigoSeguimiento='$rowV[NumPedido]' AND Eliminado='0'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
   //ACTUALIZO CTAS CTES
    $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");
      
  echo json_encode(array('success'=>1));  
  }else{
  echo json_encode(array('success'=>0));    
  }
}

//AGREGAR DATOS VENTAS
if($_POST['AgregarDatosVentas']==1){
  $_POST[codigoventa];  
  $sql=$mysqli->query("SELECT * FROM Productos WHERE Codigo='$_POST[codigoventa]'");
  $dato=$sql->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta=$dato[PrecioVenta];
  $iva=$dato[PrecioVenta]-($PrecioVenta/1.21);
  $Neto=$PrecioVenta/1.21;
  
  if($_POST['Fecha']=='0000-00-00'){
  $Fecha=date('Y-m-d');
  }else{
  $Fecha=$_POST['Fecha'];  
  }
  //BUSCO ID TRANSCLIENTES
  $sql="SELECT if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,
               if(FechaEntrega='0000-00-00',Fecha,FechaEntrega) as Fecha, 
               if(FormaDePago='Origen',LocalidadOrigen,LocalidadDestino)as Localidad,NumeroComprobante,
               if(FormaDePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente
               FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0'";

  $Resultado=$mysqli->query($sql);  
  $row=$Resultado->fetch_array(MYSQLI_ASSOC);

  $Codigo= sprintf("%10d", $_POST[codigoventa]);
  if($mysqli->query("INSERT INTO `Ventas`(`FechaPedido`, `Codigo`, `Titulo`, `Precio`, `Cantidad`, `Comentario`,
  `terminado`, `NumPedido`, `Total`, `Cliente`, `FechaEntrega`, `Localidad`, `NumeroRepo`, `ImporteNeto`, `Exento`, `Iva1`, `Usuario`,`idCliente`) VALUES 
  ('{$_POST[Fecha]}','{$Codigo}','{$_POST[tituloventa]}','{$_POST[precioventa]}','{$_POST[cantidadventa]}','{$_POST[observacionesventa]}','1',
  '{$_POST[codigoseguimiento]}','{$_POST[totalventa]}','{$row[Cliente]}','{$row[Fecha]}','{$row[Localidad]}','{$row[NumeroComprobante]}','{$Neto}','0',
  '{$iva}','{$_SESSION[Usuario]}','{$row[idCliente]}')")){
  
  $sqlV="SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$_POST[codigoseguimiento]' AND Eliminado='0'";
  $ResultadoV=$mysqli->query($sqlV);  
  $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
  $CodigoSeguimiento=$_POST['codigoseguimiento'];
  
  if($CodigoSeguimiento<>''){  
  $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0' AND TipoDeComprobante='Remito'");
  }

  //BUSCO ID TRANSCLIENTES
  $sql="SELECT id FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0'";
  $Resultado=$mysqli->query($sql);  
  $row=$Resultado->fetch_array(MYSQLI_ASSOC);
 //ACTUALIZO CTAS CTES
  $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");
    
   echo json_encode(array('success'=>1));  
  }
}

if($_POST['Pendientes']==1){
  $_SESSION[RecorridoMapa]=$_POST[Recorrido];
  $sql="SELECT TransClientes.*,HojaDeRuta.Posicion,Seguimiento.Estado FROM TransClientes 
  INNER JOIN HojaDeRuta ON TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
  INNER JOIN Seguimiento ON TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento
  WHERE TransClientes.Eliminado='0' AND TransClientes.FechaEntrega=curdate() AND TransClientes.Recorrido='$_POST[Recorrido]'
  AND Seguimiento.id=(SELECT MAX(id) FROM Seguimiento WHERE Seguimiento.CodigoSeguimiento=TransClientes.CodigoSeguimiento)
  ORDER BY HojaDeRuta.Posicion ASC";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if($_POST['ActualizarDireccion']==1){
  $datosmapa = geolocalizar(utf8_encode($_POST[Direccion]));
  $latitud = $datosmapa[0];
  $longitud = $datosmapa[1];
  
  if($_POST[Estado]=='1'){
  $Estado='Abierto';
  }else{
  $Estado='Cerrado';  
  }
  if($_POST[Retirado]=='1'){
  $Retirado=1;
  }else{
  $Retirado=0;  
  }
  if($_POST[Entregado]=='1'){
  $Entregado=1;
  }else{
  $Entregado=0;  
  }
  if($_POST[Cobranzaintegrada]=='1'){
  $CobranzaIntegrada=1;  
  }else{
  $CobranzaIntegrada=0;    
  }
  if($_POST[Cobrarcaddy]=='1'){
  $CobarCaddy=1;
  }else{
  $CobarCaddy=0;  
  }
  
  // ACTUALIZO CLIENTES 
 if(!isset($_POST[idCliente])){ 
 $sql=$mysqli->query("UPDATE `Clientes` SET Direccion='$_POST[Direccion]',
                      Calle='$_POST[calle]',Barrio='$_POST[barrio]',Numero='$_POST[numero]',
                      Ciudad='$_POST[Ciudad]',CodigoPostal='$_POST[cp]',Latitud='$latitud',Longitud='$longitud' WHERE id='$_POST[idCliente]'"); 
 }
if(!isset($_POST[CodigoSeguimiento])){
  //ACTURALIZO HOJA DE RUTA
  $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Localizacion='$_POST[Direccion]',Estado='$Estado' WHERE Seguimiento='$_POST[CodigoSeguimiento]'");

}
  //ACTUALIZO TRANS CLIENTES
  if($_POST[Servicio]=='Entrega'){
  $sql=$mysqli->query("UPDATE `TransClientes` SET DomicilioDestino='$_POST[Direccion]',Retirado='$Retirado',Entregado='$Entregado',CobrarCaddy='$CobrarCaddy',CobrarEnvio='$CobranzaIntegrada' WHERE id='$_POST[id]' AND Eliminado='0'");    
  }else{
  $sql=$mysqli->query("UPDATE `TransClientes` SET DomicilioOrigen='$_POST[Direccion]',Retirado='$Retirado',Entregado='$Entregado',CobrarCaddy='$CobrarCaddy',CobrarEnvio='$CobranzaIntegrada' WHERE id='$_POST[id]' AND Eliminado='0'");    
  }
  echo json_encode(array('success'=>1,'estado'=>$_POST[Estado]));

  $Fecha= date("Y-m-d");	
  $Hora=date("H:i"); 

$sqlbusco=$mysqli->query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE  CodigoSeguimiento='$_POST[CodigoSeguimiento]')");
$dato=$sqlbusco->fetch_array(MYSQLI_ASSOC);
$EstadoSeguimiento=$_POST[EstadoSeguimiento];
  
  $Visitas=$dato[Visitas]+1;  
  $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`, `Destino`,
                              `Avisado`, `idCliente`, `Retirado`, `Visitas`, `idTransClientes`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
                              '{$_SESSION[Sucursal]}','{$_POST[CodigoSeguimiento]}','Carga Manual Sistema','{$Entregado}','{$EstadoSeguimiento}','{$dato[Destino]}','{$dato[Avisado]}','{$_POST[idCliente]}',
                              '{$Retirado}','{$Visitas}','{$dato[idTransClientes]}')");
  
}

if($_POST['EliminarRegistro']==1){
  //ACTURALIZO HOJA DE RUTA
  if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]'")){
  $hojaderuta=1;  
  }else{
  $hojaderuta=0;    
  }
  //ACTUALIZO TRANS CLIENTES
  $id_Eliminar=$_POST['id'];

  if($id_Eliminar<>''){
    if($sql=$mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE id='$id_Eliminar'")){
    $transclientes=1;    
    }else{
    $transclientes=0; 
    }
  }
  //BUSCO ID TRANSCLIENTES
  $sql=$mysqli->query("SELECT id FROM TransClientes WHERE id='$_POST[id]'");
  $datoid=$sql->fetch_array(MYSQLI_ASSOC);
  $sqlventas=$mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]'");
  $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]' AND Eliminado='0' LIMIT 1");
  
  echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}

if($_POST['enter_registration']==1){

$sqlbusco=$mysqli->query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE  CodigoSeguimiento='$_POST[CodigoSeguimiento]')");
$dato=$sqlbusco->fetch_array(MYSQLI_ASSOC);
$EstadoSeguimiento=$_POST['state'];

//BUSCO EL ID DE ESTADO
$sql_state=$mysqli->query("SELECT id FROM Estados WHERE Estado='$EstadoSeguimiento'");
$id_state=$sql_state->fetch_array(MYSQLI_ASSOC);
$Obs=$_POST['obs'];

$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 

$Visitas=$dato[Visitas]+1;  
$idCliente=$dato['idCliente'];
$CodigoSeguimiento=$_POST['CodigoSeguimiento'];

if($EstadoSeguimiento=='Entregado al Cliente'){
    if($Obs==''){
    $Obs='Ya entregamos tu paquete al cliente !.';  
    }
    
$Entregado=1;
}else{
$Entregado=0;
}
//SI ESTA RETIRADO
if($EstadoSeguimiento=='Retirado del Cliente'){
    if($CodigoSeguimiento){
    $mysqli->query("UPDATE TransClientes SET Retirado='1' WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
    }
    $Retirado=1;
    if($Obs==''){
    $Obs='Ya retiramos el paquete !.';  
    }
    
}else{
    $Retirado=$dato[Retirado];
}
//SI ESTA DEVUELTO
if($EstadoSeguimiento=='Devuelto al Cliente'){
    // $AvisoMail='Devuelto';  
    // $Estado="Devuelto al Cliente";  
    $Entregado='0';
    $Devuelto='1';  
    $Estadohdr='Cerrado';  
    }else{
    $Devuelto='0';
    }

  //CIERRO EN HOJA DE RUTA
  if($Entregado==1){
    $Estadohdr="Cerrado";
    }
    
    //ABRO EN HOJA DE RUTA
    if($EstadoSeguimiento=='En Transito'){
    $Estadohdr="Abierto";  
    }
  
//OBSERVACIONES LE AGREGO CMS(CARGA MANUAL SISTEMA)
$Observaciones='CMS-'.$Obs;

//BUSCO EL ULTIMO RECORRIDO
$sqlbuscotrans=$mysqli->query("SELECT MAX(id)as id,Recorrido,ClienteDestino FROM `TransClientes` WHERE CodigoSeguimiento ='$CodigoSeguimiento' AND Eliminado=0");
$datosqlbuscotrans=$sqlbuscotrans->fetch_array(MYSQLI_ASSOC);

//EJECUTO LOS SQL

$sqlseguimiento="INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`, `Destino`,
`Avisado`, `idCliente`, `Retirado`, `Visitas`, `idTransClientes`, `Recorrido`,`NombreCompleto`,`state_id`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
'{$_SESSION[Sucursal]}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$EstadoSeguimiento}','{$dato[Destino]}','{$dato[Avisado]}','{$idCliente}',
'{$Retirado}','{$Visitas}','{$datosqlbuscotrans[id]}','{$datosqlbuscotrans[Recorrido]}','{$datosqlbuscotrans[ClienteDestino]}','{$id_state[id]}')";

if($mysqli->query($sqlseguimiento)){
//ACTUALIZO TRANSCLIENTES  
  $mysqli->query("UPDATE TransClientes SET Estado='$EstadoSeguimiento',Entregado='$Entregado',Devuelto='$Devuelto' WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
//ACTUALIZO HOJA DE RUTA
  $mysqli->query("UPDATE HojaDeRuta SET Devuelto='$Devuelto',Estado='$Estadohdr' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0");

  echo json_encode(array('success'=>1,'estadohdr'=>$Estadohdr));  
 }else{
    echo json_encode(array('success'=>0));    
 }
}
?>