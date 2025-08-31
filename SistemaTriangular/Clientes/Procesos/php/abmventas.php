<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Buenos_Aires');

if($_POST['Actualiza']==1){

    $Entregado=$_POST['entregado'];  
    $Observaciones='CMS: '.$_POST['Observaciones'];
    if($_POST[Fecha]==''){
    $Fecha= date("Y-m-d");	  
    }else{
    $Fecha= date("Y-m-d", strtotime($_POST['Fecha']));  
    }
    if($_POST[Hora]==''){
    $Hora=date("H:i");   
    }else{
    $Hora=date('H:i',strtotime($_POST['Hora']));  
    }  
      
    $sql=$mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino,Recorrido,NumerodeOrden FROM TransClientes WHERE id='$_POST[id]' AND Eliminado='0'");
    $sqldato=$sql->fetch_array(MYSQLI_ASSOC);  

    $sql=$mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]' LIMIT 1");    
      
    $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
                                  `idCliente`, `Retirado`,`idTransClientes`,`Destino`,`Recorrido`,`NumerodeOrden`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
                                  '{$_SESSION[Sucursal]}','{$sqldato[CodigoSeguimiento]}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
                                  '{$sqldato[idClienteDestino]}','1','{$sqldato[id]}','{$sqldato[ClienteDestino]}','{$sqldato[Recorrido]}','{$sqldato[NumerodeOrden]}')");
      
    $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
    
    $sql=$mysqli->query("UPDATE `TransClientes` SET Estado='Entregado al Cliente',FechaEntrega='$Fecha' WHERE CodigoSeguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
    
    //ACTUALIZA ROADMAP
    $sql=$mysqli->query("UPDATE `Roadmap` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
      
    echo json_encode(array('success'=>1));
    }




if($_SESSION['Nivel']<>1){

    echo json_encode(array('success'=>401));  
}

if($_POST['BuscarDatosVentas']==1){
  
    if($_POST['idPedido']<>''){
    
        $id=$_POST['idPedido'];  
        $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Cantidad,Precio,Comentario FROM Ventas WHERE idPedido='$id' AND Eliminado='0'";
    
    }else{

        $sql="SELECT CodigoSeguimiento FROM TransClientes WHERE id='$_POST[id]'";
        $Resultado=$mysqli->query($sql);  
        $row=$Resultado->fetch_array(MYSQLI_ASSOC);
        $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Cantidad,Precio FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";

    }
  
    $Resultado=$mysqli->query($sql);  
    $rows=array();

    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    
        $rows[]=$row;  
    }
   
    echo json_encode(array('data'=>$rows));
}

//AGREGAR DATOS VENTAS
if($_POST['AgregarDatosVentas']==1){
  
  $_POST['codigoventa'];  
  $sql=$mysqli->query("SELECT * FROM Productos WHERE Codigo='$_POST[codigoventa]'");
  $dato=$sql->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta=$dato['PrecioVenta'];
  $iva=$dato['PrecioVenta']-($PrecioVenta/1.21);
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
  ('{$Fecha}','{$Codigo}','{$_POST[tituloventa]}','{$_POST[precioventa]}','{$_POST[cantidadventa]}','{$_POST[observacionesventa]}','1',
  '{$_POST[codigoseguimiento]}','{$_POST[totalventa]}','{$row[Cliente]}','{$row[Fecha]}','{$row[Localidad]}','{$row[NumeroComprobante]}','{$Neto}','0',
  '{$iva}','{$_SESSION[Usuario]}','{$row[idCliente]}')")){
  
  $sqlV="SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$_POST[codigoseguimiento]' AND Eliminado='0'";
  $ResultadoV=$mysqli->query($sqlV);  
  $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
  $CodigoSeguimiento=$_POST['codigoseguimiento'];

  if($CodigoSeguimiento<>''){  
  $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND TipoDeComprobante='Remito' AND Eliminado='0'");
  
    //BUSCO ID TRANSCLIENTES
    $sql="SELECT id FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);

   if ($row['id'] !== null && $row['id'] !== 0) {
   //ACTUALIZO CTAS CTES
   $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' LIMIT 1");
   
    }  
  }

   echo json_encode(array('success'=>1));  
  }
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
    
    if($_POST['fecha']=='0000-00-00'){
        $Fecha=date('Y-m-d');
        }else{
        $Fecha=$_POST['fecha'];  
        }
      

  if($mysqli->query("UPDATE Ventas SET Comentario='$_POST[comentario]',Codigo='$_POST[codigo]',Titulo='$_POST[titulo]',Total='$_POST[total]',
    infoABM='$info',Cantidad='$_POST[cantidad]',Precio='$_POST[precio]',FechaPedido='$Fecha' WHERE idPedido='$_POST[idPedido]' LIMIT 1"))
  {
    $successventas=1;  
    $sqlV="SELECT SUM(Total)as Total FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";
    $ResultadoV=$mysqli->query($sqlV);  
    $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
    
    if($mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE id='$_POST[idTrans]' AND Eliminado='0' AND (TipoDeComprobante = 'Remito' OR TipoDeComprobante = 'GUIA DE CARGA') LIMIT 1 ")){
      $successtrans=1;  
      
      if($mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$_POST[idTrans]' AND Eliminado='0' LIMIT 1")){
      
              $successctasctes=1;  

            }else{ //SI NO ACTUALIZO ENTIENDO QUE NO EXISTE EL ROW Y AGREGO EN CTAS CTES.
      
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
// if($_POST['EliminarDatosVentas']==1){

//     if($_SESSION['Nivel']==1){
//     $info="B: ".$_SESSION[Usuario].' | '.date('Y-m-d (h:m:s)');
  
//     //PRIMERO OBTENGO EL NUMPEDIDO(CODIGO SEGUIMIENTO)
//     $sql="SELECT NumPedido FROM Ventas WHERE idPedido='$_POST[idPedido]' AND Eliminado='0'";
//     $Resultado=$mysqli->query($sql);  
//     $rowventas=$Resultado->fetch_array(MYSQLI_ASSOC);               
                 
//   if($mysqli->query("UPDATE Ventas SET Eliminado=1,infoABM='$info' WHERE idPedido='$_POST[idPedido]' LIMIT 1")){
    
//     $sqlV="SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$rowventas[NumPedido]' AND Eliminado='0'";
//     $ResultadoV=$mysqli->query($sqlV);  
//     $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
    
//     $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$rowV[NumPedido]' AND TipoDeComprobante='Remito' AND Eliminado='0' LIMIT 1");
//     //BUSCO ID TRANSCLIENTES
//     $sql="SELECT id FROM TransClientes WHERE CodigoSeguimiento='$rowV[NumPedido]' AND Eliminado='0'";
//     $Resultado=$mysqli->query($sql);  
//     $row=$Resultado->fetch_array(MYSQLI_ASSOC);
//    //ACTUALIZO CTAS CTES
//     $idTrans=$row['id'];

//     if($idTrans<>''){
    
//     $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");
    
//     }

//   echo json_encode(array('success'=>1));  
//   }else{
//   echo json_encode(array('success'=>0));    
//   }
// }else{
//   echo json_encode(array('error'=>401));      
// }
// }

//ELIMINAR DATOS VENTAS 

// Verificar si se debe eliminar datos de ventas
if ($_POST['EliminarDatosVentas'] == 1) {

    // Verificar nivel de sesión
    if ($_SESSION['Nivel'] == 1) {

        if (isset($_POST['idPedido'])) {

        // Sanitización de datos
        $idPedido = mysqli_real_escape_string($mysqli, $_POST['idPedido']);
        
        // Obtener información de sesión
        $info = "B: " . $_SESSION['Usuario'] . ' | ' . date('Y-m-d (h:m:s)');

        // Obtener el NumPedido (Código de Seguimiento)
        $sql = "SELECT NumPedido FROM Ventas WHERE idPedido='$idPedido' AND Eliminado='0'";
        $Resultado = $mysqli->query($sql);
        $rowVentas = $Resultado->fetch_array(MYSQLI_ASSOC);

        // Actualizar Ventas
        if ($mysqli->query("UPDATE Ventas SET Eliminado=1,infoABM='$info' WHERE idPedido='$idPedido' LIMIT 1")) {

            // Obtener información de Ventas
            $sqlVentas = "SELECT SUM(Total) as Total FROM Ventas WHERE NumPedido='$rowVentas[NumPedido]' AND Eliminado='0'";
            $ResultadoVentas = $mysqli->query($sqlVentas);
            $rowVentasTotal = $ResultadoVentas->fetch_array(MYSQLI_ASSOC);
            
            if($rowVentasTotal['Total']<>0){
            
                $TotalVentas=$rowVentasTotal['Total'];
            
            }else{
            
                $TotalVentas='0';
            
            }
            
            // Actualizar TransClientes
            $mysqli->query("UPDATE TransClientes SET Debe='$TotalVentas' WHERE CodigoSeguimiento='$rowVentas[NumPedido]' AND TipoDeComprobante='Remito' AND Eliminado='0' LIMIT 1");

            // Buscar ID TransClientes
            $sqlTransClientes = "SELECT id FROM TransClientes WHERE CodigoSeguimiento='$rowVentas[NumPedido]' AND Eliminado='0'";
            $ResultadoTransClientes = $mysqli->query($sqlTransClientes);
            $rowTransClientes = $ResultadoTransClientes->fetch_array(MYSQLI_ASSOC);

            // Actualizar Ctasctes
            $idTrans = $rowTransClientes['id'];
            if ($idTrans != '') {
                $mysqli->query("UPDATE Ctasctes SET Debe='$TotalVentas' WHERE idTransClientes='$idTrans' AND Eliminado='0' LIMIT 1");
            }

            echo json_encode(array('success' => 1));

        } else {
            // Manejo de errores
            echo json_encode(array('success' => 0, 'error' => $mysqli->error));
        }

    } else {
        // Manejo de error si 'idPedido' no está definido
        echo json_encode(array('error' => 'idPedido not defined'));
    }

    } else {
        // Sesión no válida
        echo json_encode(array('error' => 401));
    }
}


//SERVICIOS
if($_POST['id_servicio']<>''){
  $id=$_POST['id_servicio'];
  $sqlservicios=$mysqli->query("SELECT id,PrecioVenta,Titulo FROM Productos WHERE id='$id'");
  $datoservicios=$sqlservicios->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta=$datoservicios['PrecioVenta'];
  $Codigo=$datoservicios['id'];  
  echo json_encode(array('success'=> 1,'PrecioVenta'=> $PrecioVenta,'Codigo'=>$Codigo,'Titulo'=>$datoservicios[Titulo]));
}

//BUSCAR DATOS
if($_POST['BuscarDatos']==1){

  if($_POST['Nivel']<>1){
  
    echo json_encode(array('error'=>401));
  
  }else{  

    $sql="SELECT IF(FormadePago='Origen',RazonSocial,ClienteDestino)as Cliente,
                 IF(FormadePago='Origen',DomicilioOrigen,DomicilioDestino)as Domicilio, 
                 IF(FormadePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente, CodigoSeguimiento,Entregado FROM TransClientes WHERE id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
    $CodigoSeguimiento = $row['CodigoSeguimiento'];
    $Domicilio = $row['Domicilio']; 
    $RazonSocial = $row['Cliente']; 
    $idCliente = $row['idCliente'];
    $Entregado = $row['Entregado'];

  echo json_encode(array('RazonSocial'=>$RazonSocial,'Domicilio'=>$Domicilio,'idCliente'=>$idCliente,'CodigoSeguimiento'=>$CodigoSeguimiento,'Entregado'=>$Entregado));
  }  
}

//ELIMINAR GUIA DE CARGA
if($_POST['EliminarRegistro']==1){
  $info="B: ".$_SESSION[Usuario].' | '.date('Y-m-d (h:m:s)').' clientes.procesos.php.abmventas';
  //ACTURALIZO HOJA DE RUTA
  if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1")){
  $hojaderuta=1;  
  }else{
  $hojaderuta=0;    
  }
  //ACTUALIZO TRANS CLIENTES
  if($sql=$mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]',infoABM='$info' WHERE id='$_POST[id]' LIMIT 1")){
  $transclientes=1;    
  }else{
  $transclientes=0; 
  }
  //BUSCO ID TRANSCLIENTES
  $idTrans=$_POST['id'];
  $CodigoSeguimiento=$_POST['CodigoSeguimiento'];
  
  $sql=$mysqli->query("SELECT id FROM TransClientes WHERE id='$idTrans'");
  $datoid=$sql->fetch_array(MYSQLI_ASSOC);

  $sqlventas=$mysqli->query("UPDATE Ventas SET Eliminado='1',infoABM='$info' WHERE NumPedido='$CodigoSeguimiento' LIMIT 1");
  $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Eliminado='1' WHERE idTransClientes='$datoid[id]' LIMIT 1");
  
  echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}


?>