<?php
// Conexioni.php ya hace su propio session_start() - este de aca duplicaba
// la llamada y el Notice de PHP ("Ignoring session_start()...") se colaba
// como HTML antes del JSON, rompiendo el JSON.parse() del lado del cliente.
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Buenos_Aires');

if(isset($_POST['Actualiza']) && $_POST['Actualiza']==1){

    $Entregado=$_POST['entregado'];  
    $Observaciones='CMS: '.$_POST['Observaciones'];
    if($_POST['Fecha']==''){
    $Fecha= date("Y-m-d");
    }else{
    $Fecha= date("Y-m-d", strtotime($_POST['Fecha']));
    }
    if($_POST['Hora']==''){
    $Hora=date("H:i");   
    }else{
    $Hora=date('H:i',strtotime($_POST['Hora']));  
    }  
      
    $sql=$mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino,Recorrido,NumerodeOrden FROM TransClientes WHERE id='$_POST[id]' AND Eliminado='0'");
    $sqldato=$sql->fetch_array(MYSQLI_ASSOC);  

    $sql=$mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]' LIMIT 1");    
      
    $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
                                  `idCliente`, `Retirado`,`idTransClientes`,`Destino`,`Recorrido`,`NumerodeOrden`)VALUES('{$Fecha}','{$Hora}','{$_SESSION['Usuario']}',
                                  '{$_SESSION['Sucursal']}','{$sqldato['CodigoSeguimiento']}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
                                  '{$sqldato['idClienteDestino']}','1','{$sqldato['id']}','{$sqldato['ClienteDestino']}','{$sqldato['Recorrido']}','{$sqldato['NumerodeOrden']}')");
      
    $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
    
    $sql=$mysqli->query("UPDATE `TransClientes` SET Estado='Entregado al Cliente',FechaEntrega='$Fecha' WHERE CodigoSeguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
    
    //ACTUALIZA ROADMAP
    $sql=$mysqli->query("UPDATE `Roadmap` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");
      
    echo json_encode(array('success'=>1));
    }

// Antes había acá un `if ($_SESSION['Nivel'] <> 1) echo json_encode(['success' => 401]);`
// suelto, fuera de cualquier bloque de accion - se ejecutaba en TODAS las
// requests a este archivo (sin importar que accion se pidiera) y para
// cualquier usuario que no fuera Nivel 1 (SuperAdministrador) agregaba un
// segundo JSON pegado al de la accion real, rompiendo el JSON.parse() del
// lado del cliente. El control de Nivel que hace falta ya esta bien scopeado
// mas abajo, adentro del bloque 'BuscarDatos'.

if(isset($_POST['BuscarDatosVentas']) && $_POST['BuscarDatosVentas']==1){
  
    if(isset($_POST['idPedido']) && $_POST['idPedido']<>''){
    
        $id=$_POST['idPedido'];
        $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Cantidad,Precio,Comentario,not_invoice FROM Ventas WHERE idPedido='$id' AND Eliminado='0'";

    }else{

        $sql="SELECT CodigoSeguimiento FROM TransClientes WHERE id='$_POST[id]'";
        $Resultado=$mysqli->query($sql);
        $row=$Resultado->fetch_array(MYSQLI_ASSOC);
        $sql="SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Cantidad,Precio,not_invoice FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";

    }
  
    $Resultado=$mysqli->query($sql);  
    $rows=array();

    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    
        $rows[]=$row;  
    }
   
    echo json_encode(array('data'=>$rows));
}

//AGREGAR DATOS VENTAS
if(isset($_POST['AgregarDatosVentas']) && $_POST['AgregarDatosVentas']==1){
  // Reescrito 2026-09-29 (Guias a Facturar > "Agregar Venta a Codigo"):
  //  - el producto llega por id (idproducto) y se guarda su Codigo real (antes buscaba
  //    Productos.Codigo con el id: no lo encontraba y Neto/IVA quedaban en 0);
  //  - Neto e IVA segun la alicuota del producto, igual que Venta Simple (AgregarVenta.php);
  //  - consultas preparadas: un apostrofo en titulo/observaciones rompia el INSERT.
  $cs = trim((string)($_POST['codigoseguimiento'] ?? ''));
  $idProducto = (int)($_POST['idproducto'] ?? ($_POST['codigoventa'] ?? 0));
  $cantidad = (float)($_POST['cantidadventa'] ?? 1);
  if ($cantidad <= 0) { $cantidad = 1; }
  $precio = (float)str_replace(',', '.', (string)($_POST['precioventa'] ?? 0));
  $observaciones = (string)($_POST['observacionesventa'] ?? '');
  $Fecha = (isset($_POST['Fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['Fecha']) && $_POST['Fecha'] !== '0000-00-00') ? $_POST['Fecha'] : date('Y-m-d');

  $st = $mysqli->prepare("SELECT id, Codigo, Titulo, Iva FROM Productos WHERE id = ?");
  $st->bind_param('i', $idProducto);
  $st->execute();
  $prod = $st->get_result()->fetch_assoc();
  $st->close();
  if ($cs === '' || !$prod || $precio <= 0) {
    echo json_encode(array('success'=>0, 'error'=> !$prod ? 'Elegí un servicio.' : ($precio <= 0 ? 'Ingresá un precio mayor a 0.' : 'Falta el código de seguimiento.')));
    exit;
  }
  $titulo = trim((string)($_POST['tituloventa'] ?? '')) !== '' ? (string)$_POST['tituloventa'] : (string)$prod['Titulo'];

  $Total = round($cantidad * $precio, 2);
  $ivaFactor = (float)$prod['Iva'];
  if ($ivaFactor > 2) { $ivaFactor = 1 + $ivaFactor / 100; } // hay productos con "21.00" en vez de 1.21
  $ImporteNeto = $ivaFactor > 0 ? round($Total / $ivaFactor, 2) : $Total;
  $iva1 = $iva2 = $iva3 = 0.0;
  if (abs($ivaFactor - 1.025) < 0.0001) { $iva1 = $Total - $ImporteNeto; }
  elseif (abs($ivaFactor - 1.105) < 0.0001) { $iva2 = $Total - $ImporteNeto; }
  elseif (abs($ivaFactor - 1.21) < 0.0001) { $iva3 = $Total - $ImporteNeto; }

  //BUSCO DATOS DEL ENVIO EN TRANSCLIENTES
  $st = $mysqli->prepare("SELECT if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,
               if(FechaEntrega='0000-00-00',Fecha,FechaEntrega) as Fecha,
               if(FormaDePago='Origen',LocalidadOrigen,LocalidadDestino)as Localidad,NumeroComprobante,
               if(FormaDePago='Origen',idClienteOrigen,idClienteDestino)as idCliente, id
               FROM TransClientes WHERE CodigoSeguimiento = ? AND Eliminado='0' LIMIT 1");
  $st->bind_param('s', $cs);
  $st->execute();
  $row = $st->get_result()->fetch_assoc();
  $st->close();
  if (!$row) {
    echo json_encode(array('success'=>0, 'error'=>'No se encontró el envío '.$cs));
    exit;
  }

  $usuario = (string)($_SESSION['Usuario'] ?? '');
  $st = $mysqli->prepare("INSERT INTO `Ventas`(`FechaPedido`, `Codigo`, `Titulo`, `Precio`, `Cantidad`, `Comentario`,
    `terminado`, `NumPedido`, `Total`, `Cliente`, `FechaEntrega`, `Localidad`, `NumeroRepo`, `ImporteNeto`, `Exento`, `Iva1`, `Iva2`, `Iva3`, `Usuario`,`idCliente`)
    VALUES (?,?,?,?,?,?,'1',?,?,?,?,?,?,?,'0',?,?,?,?,?)");
  $vals = array($Fecha, (string)$prod['Codigo'], $titulo, (string)$precio, (string)$cantidad, $observaciones,
                $cs, (string)$Total, (string)$row['Cliente'], (string)$row['Fecha'], (string)$row['Localidad'], (string)$row['NumeroComprobante'],
                (string)$ImporteNeto, (string)round($iva1,2), (string)round($iva2,2), (string)round($iva3,2), $usuario, (string)$row['idCliente']);
  $st->bind_param(str_repeat('s', count($vals)), ...$vals);

  if($st->execute()){
    $st->close();
    // Recalcula el Debe del remito y su cuenta corriente con el total de ventas del envio
    $st = $mysqli->prepare("SELECT SUM(Total) as Total FROM Ventas WHERE NumPedido = ? AND Eliminado='0' AND not_invoice=0");
    $st->bind_param('s', $cs);
    $st->execute();
    $totalVentas = (float)($st->get_result()->fetch_assoc()['Total'] ?? 0);
    $st->close();

    $st = $mysqli->prepare("UPDATE TransClientes SET Debe = ? WHERE CodigoSeguimiento = ? AND TipoDeComprobante='Remito' AND Eliminado='0'");
    $st->bind_param('ds', $totalVentas, $cs);
    $st->execute();
    $st->close();

    $idTrans = (int)$row['id'];
    if ($idTrans > 0) {
      $st = $mysqli->prepare("UPDATE Ctasctes SET Debe = ? WHERE idTransClientes = ? LIMIT 1");
      $st->bind_param('di', $totalVentas, $idTrans);
      $st->execute();
      $st->close();
    }
    echo json_encode(array('success'=>1));
  } else {
    echo json_encode(array('success'=>0, 'error'=>'No se pudo guardar la venta.'));
  }
}

//MODIFICAR VENTAS
if(isset($_POST['ModificarDatosVentas']) && $_POST['ModificarDatosVentas']==1){
// Los 4 flags de mas abajo solo se pisan segun que rama se recorre - sin
// esto, cualquier rama que no los toque (ej. successventas=0) los deja
// indefinidos y el "Undefined variable" se cuela antes del json_encode,
// rompiendo el JSON.parse() del lado del cliente.
$successventas=0; $successtrans=0; $successctasctes=0; $successctasctesinsert=0;
$info="M: ".$_SESSION['Usuario'].' | '.date('Y-m-d (h:m:s)');
    $sql="SELECT Fecha,IF(FormaDePago='Origen',RazonSocial,ClienteDestino)as RazonSocial,
                       IF(FormaDePago='Origen',Cuit,idClienteDestino)as Cuit,TipoDeComprobante,NumeroComprobante,Debe,
                       IF(FormaDePago='Origen',idClienteOrigen,idClienteDestino)as idCliente,
                       Observaciones,id,CodigoSeguimiento FROM TransClientes WHERE id='$_POST[idTrans]'";

    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
    
    if($_POST['fecha']=='0000-00-00'){
        $Fecha=date('Y-m-d');
        }else{
        $Fecha=$_POST['fecha'];  
        }
      

  // Consulta preparada: un apostrofo en comentario/titulo rompia el UPDATE (2026-09-29)
  $stMod = $mysqli->prepare("UPDATE Ventas SET Comentario=?,Codigo=?,Titulo=?,Total=?,infoABM=?,Cantidad=?,Precio=?,FechaPedido=? WHERE idPedido=? LIMIT 1");
  $valsMod = array((string)($_POST['comentario'] ?? ''), (string)($_POST['codigo'] ?? ''), (string)($_POST['titulo'] ?? ''), (string)($_POST['total'] ?? ''),
                   $info, (string)($_POST['cantidad'] ?? ''), (string)($_POST['precio'] ?? ''), (string)$Fecha, (string)($_POST['idPedido'] ?? ''));
  $stMod->bind_param(str_repeat('s', count($valsMod)), ...$valsMod);
  if($stMod->execute())
  {
    $successventas=1;  
    $sqlV="SELECT SUM(Total)as Total FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0' AND not_invoice=0";
    $ResultadoV=$mysqli->query($sqlV);  
    $rowV=$ResultadoV->fetch_array(MYSQLI_ASSOC);
    
    if($mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE id='$_POST[idTrans]' AND Eliminado='0' AND (TipoDeComprobante = 'Remito' OR TipoDeComprobante = 'GUIA DE CARGA') LIMIT 1 ")){
      $successtrans=1;  
      
      if($mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$_POST[idTrans]' AND Eliminado='0' LIMIT 1")){
      
              $successctasctes=1;  

            }else{ //SI NO ACTUALIZO ENTIENDO QUE NO EXISTE EL ROW Y AGREGO EN CTAS CTES.
      
              $successctasctes=0;   
      
            if($rowV[Total]>0){ 

                    $stCc = $mysqli->prepare("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,`Observaciones`, `idCliente`,`idTransClientes`) VALUES (?,?,?,?,?,?,?,?,?,?)");
                    $valsCc = array((string)$row['Fecha'], (string)$row['RazonSocial'], (string)$row['Cuit'], (string)$row['TipoDeComprobante'], (string)$row['NumeroComprobante'],
                                    (string)$rowV['Total'], (string)($_SESSION['Usuario'] ?? ''), (string)$row['Observaciones'], (string)$row['idCliente'], (string)$row['id']);
                    $stCc->bind_param(str_repeat('s', count($valsCc)), ...$valsCc);
                    if($stCc->execute()){
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
if (isset($_POST['EliminarDatosVentas']) && $_POST['EliminarDatosVentas'] == 1) {

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
            $sqlVentas = "SELECT SUM(Total) as Total FROM Ventas WHERE NumPedido='$rowVentas[NumPedido]' AND Eliminado='0' AND not_invoice=0";
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
if(isset($_POST['id_servicio']) && $_POST['id_servicio']<>''){
  // Codigo = Productos.Codigo (el que se guarda en Ventas); antes devolvia el id
  $id=(int)$_POST['id_servicio'];
  $sqlservicios=$mysqli->query("SELECT id,PrecioVenta,Titulo,Codigo FROM Productos WHERE id='$id'");
  $datoservicios=$sqlservicios ? $sqlservicios->fetch_array(MYSQLI_ASSOC) : null;
  if(!$datoservicios){
    echo json_encode(array('success'=>0));
  }else{
    echo json_encode(array('success'=> 1,'id'=>(int)$datoservicios['id'],'PrecioVenta'=> $datoservicios['PrecioVenta'],'Codigo'=>$datoservicios['Codigo'],'Titulo'=>$datoservicios['Titulo']));
  }
}

//LISTA DE SERVICIOS para "Agregar Venta a Codigo" (el <select> estaba vacio:
//el SELECT que lo llenaba en Clientes.php quedo comentado)
if(isset($_POST['ListarServicios'])){
  $res=$mysqli->query("SELECT id,Titulo,PrecioVenta FROM Productos ORDER BY Titulo");
  $out=array();
  while($res && $r=$res->fetch_assoc()){ $out[]=$r; }
  echo json_encode(array('success'=>1,'data'=>$out));
}

//BUSCAR DATOS
if(isset($_POST['BuscarDatos']) && $_POST['BuscarDatos']==1){

  if($_POST['Nivel']<>1){
  
    echo json_encode(array('error'=>401));
  
  }else{  

    $sql="SELECT IF(FormadePago='Origen',RazonSocial,ClienteDestino)as Cliente,
                 IF(FormadePago='Origen',DomicilioOrigen,DomicilioDestino)as Domicilio, 
                 IF(FormadePago='Origen',idClienteOrigen,idClienteDestino)as idCliente, CodigoSeguimiento,Entregado FROM TransClientes WHERE id='$_POST[id]'";
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
if(isset($_POST['EliminarRegistro']) && $_POST['EliminarRegistro']==1){
  $info="B: ".$_SESSION['Usuario'].' | '.date('Y-m-d (h:m:s)').' clientes.procesos.php.abmventas';
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