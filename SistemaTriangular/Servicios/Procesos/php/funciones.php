<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
require_once('../../../Google/geolocalizar.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (isset($_POST['usuarios_registration'])) {
  $query = "SELECT Usuario,NombreCompleto FROM Empleados where Inactivo=0 ORDER BY Usuario ASC";
  $result = $mysqli->query($query);

  // Construir el array de opciones para el select
  $options = array();
  while ($row = $result->fetch_assoc()) {
    $options[] = array('id' => $row['Usuario'], 'text' => $row['NombreCompleto']);
  }

  // Devolver las opciones como JSON
  header('Content-Type: application/json');
  echo json_encode($options);

  // Cerrar la conexión a la base de datos
  $mysqli->close();
}


//VIENE DESDE GUIAS.JS
if (isset($_POST['ChangeFlex'])) {

  $id = $_POST['id_transclientes'];

  $mysqli->query("UPDATE TransClientes SET Flex = 
CASE
    WHEN Flex = 0 THEN 1
    WHEN Flex = 1 THEN 0
ELSE Flex END WHERE id='$id' LIMIT 1");

  $sql = $mysqli->query("SELECT Flex FROM TransClientes WHERE id='$id'");
  $dato = $sql->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('success' => 1, 'value' => $dato['Flex']));
}

if (isset($_POST['ControlPass'])) {

  //SOLO USUARIOS ACTIVOS Y CON NIVEL 1
  $sql = "SELECT Password FROM usuarios WHERE Activo=1 AND NIVEL=1 AND id ='" . $_SESSION['idusuario'] . "' ";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);

  if ($row['Password'] == $_POST['pass']) {

    echo json_encode(array('Result' => 1));
  } else {

    echo json_encode(array('Result' => 0));
  }
}

//VIENE DE GUIAS.JS

if (isset($_POST['ActualizarFechaServicio'])) {

  $Fecha = $_POST['Fecha_new'];
  $Cs = $_POST['Cs'];

  //TRANS CLIENTES
  if ($sql = $mysqli->query("UPDATE TransClientes SET Fecha='$Fecha' WHERE Eliminado=0 AND CodigoSeguimiento='$Cs' LIMIT 1")) {

    //BUSCO ID TRANSCLIENTES
    $sql = "SELECT id FROM TransClientes WHERE CodigoSeguimiento='$Cs' AND Eliminado='0'";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    //ACTUALIZO CTAS CTES
    $mysqli->query("UPDATE Ctasctes SET Fecha='$Fecha' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");

    echo json_encode(array('Result_Fechas' => 1));
  } else {

    echo json_encode(array('Result_Fechas' => 0));
  }
}


if (isset($_POST['BuscarSeguimiento'])) {
  $sql = "SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]')";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  echo json_encode(array('Estado' => $row['Estado']));
}

if ($_POST['id_servicio'] <> '') {
  $id = $_POST['id_servicio'];
  $sqlservicios = $mysqli->query("SELECT id,PrecioVenta,Titulo FROM Productos WHERE id='$id'");
  $datoservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta = $datoservicios['PrecioVenta'];
  $Codigo = $datoservicios['id'];
  echo json_encode(array('success' => 1, 'PrecioVenta' => $PrecioVenta, 'Codigo' => $Codigo, 'Titulo' => $datoservicios['Titulo']));
}


if (isset($_POST['TotalEnvios'])) {
  $sql = "SELECT COUNT(HojaDeRuta.id)as id,Logistica.NombreChofer,Logistica.Patente FROM HojaDeRuta 
  INNER JOIN Logistica ON HojaDeRuta.Recorrido=Logistica.Recorrido 
  WHERE HojaDeRuta.Recorrido='$_POST[Recorrido]' AND Logistica.Eliminado=0 AND 
  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
  AND HojaDeRuta.Eliminado=0 AND Logistica.Estado<>'Cerrada'";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  //VEHICULO
  $sqlvehiculo = "SELECT * FROM Vehiculos WHERE Dominio='$row[Patente]'";
  $Resultadovehiculo = $mysqli->query($sqlvehiculo);
  $rowvehiculo = $Resultadovehiculo->fetch_array(MYSQLI_ASSOC);
  $vehiculo = $rowvehiculo['Marca'] . ' ' . $rowvehiculo['Modelo'] . ' ' . $rowvehiculo['Color'] . ' (' . $rowvehiculo['Dominio'] . ')';
  echo json_encode(array('totalservicios' => $row['id'], 'chofer' => $row['NombreChofer'], 'vehiculo' => $vehiculo));
}

if (isset($_POST['BuscarDatos'])) {
  $sql = "SELECT id,IngBrutosOrigen,idClienteDestino,RazonSocial,ClienteDestino,Retirado,DomicilioOrigen,DomicilioDestino,
    CodigoSeguimiento,Entregado,CobrarEnvio,CobrarCaddy FROM TransClientes WHERE id='$_POST[id]' AND Eliminado='0'";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  $CodigoSeguimiento = $row['CodigoSeguimiento'];
  $sqlhdr = "SELECT Estado FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento'";
  $Resultadohdr = $mysqli->query($sqlhdr);
  $rowhdr = $Resultadohdr->fetch_array(MYSQLI_ASSOC);

  $sqlseguimiento = "SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')";
  $Resultadoseguimiento = $mysqli->query($sqlseguimiento);
  $rowseguimiento = $Resultadoseguimiento->fetch_array(MYSQLI_ASSOC);


  if (isset($row['Retirado'])) {
    $Domicilio = $row['DomicilioDestino'];
    $RazonSocial = $row['ClienteDestino'];
    $idCliente = $row['idClienteDestino'];
    $Servicio = 'Entrega';
  } else {
    $Domicilio = $row['DomicilioOrigen'];
    $RazonSocial = $row['RazonSocial'];
    $idCliente = $row['IngBrutosOrigen'];
    $Servicio = 'Retiro';
  }
  echo json_encode(array('EstadoSeguimiento' => $rowseguimiento['Estado'], 'CobrarCaddy' => $row['CobrarCaddy'], 'CobrarEnvio' => $row['CobrarEnvio'], 'Entregado' => $row['Entregado'], 'Retirado' => $row['Retirado'], 'EstadoHdr' => $rowhdr['Estado'], 'RazonSocial' => $RazonSocial, 'Domicilio' => $Domicilio, 'idCliente' => $idCliente, 'CodigoSeguimiento' => $CodigoSeguimiento, 'Servicio' => $Servicio));
}

if (isset($_POST['BuscarDatosVentas'])) {

  if ($_POST['idPedido'] <> '') {
    $id = $_POST['idPedido'];
    $sql = "SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Precio,Cantidad FROM Ventas WHERE idPedido='$id' AND Eliminado='0'";
  } else {
    $sql = "SELECT CodigoSeguimiento FROM TransClientes WHERE id='$_POST[id]'";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
    $sql = "SELECT idPedido,FechaPedido,Codigo,Titulo,Total,NumPedido,Precio,Cantidad FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";
  }

  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}
//MODIFICAR VENTAS
if (isset($_POST['ModificarDatosVentas'])) {
  $info = "M: " . $_SESSION['Usuario'] . ' | ' . date('Y-m-d (h:m:s)');
  $sql = "SELECT Fecha,IF(FormaDePago='Origen',RazonSocial,ClienteDestino)as RazonSocial,
                       IF(FormaDePago='Origen',Cuit,idClienteDestino)as Cuit,TipoDeComprobante,NumeroComprobante,Debe,
                       IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)as idCliente,
                       Observaciones,id,CodigoSeguimiento FROM TransClientes WHERE id='$_POST[idTrans]'";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);


  if ($mysqli->query("UPDATE Ventas SET Codigo='$_POST[codigo]',Titulo='$_POST[titulo]',Precio='$_POST[precio]',Cantidad='$_POST[cantidad]',Total='$_POST[total]',infoABM='$info',comentario='$_POST[observaciones]' WHERE idPedido='$_POST[idPedido]'")) {
    $successventas = 1;
    $sqlV = "SELECT SUM(Total)as Total FROM Ventas WHERE NumPedido='$row[CodigoSeguimiento]' AND Eliminado='0'";
    $ResultadoV = $mysqli->query($sqlV);
    $rowV = $ResultadoV->fetch_array(MYSQLI_ASSOC);

    if ($mysqli->query("UPDATE TransClientes SET Debe='{$rowV['Total']}' WHERE id='{$_POST['idTrans']}' AND Eliminado='0' AND TipoDeComprobante='Remito' LIMIT 1")) {
      $successtrans = 1;

      $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$_POST[idTrans]' AND Eliminado='0' LIMIT 1");
      $result = $mysqli->affected_rows;

      if ($result <> 0) {
        $successctasctes = 1;
      } else {
        $successctasctes = 0;
        if ($rowV['Total'] > 0) {
          //SI NO EXISTE EN CTAS CTES Y EL TOTAL ES MAYOR A CERO LO INSERTO EN CTASCTES  
          if ($mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,`Observaciones`, `idCliente`,`idTransClientes`) VALUES ('{$row['Fecha']}','{$row['RazonSocial']}','{$row['Cuit']}','{$row['TipoDeComprobante']}',
        '{$row['NumeroComprobante']}','{$rowV['Total']}','{$_SESSION['Usuario']}','{$row['Observaciones']}','{$row['idCliente']}','{$row['id']}')")) {
            $successctasctesinsert = 1;
          } else {
            $successctasctesinsert = 0;
          }
        }
      }
    } else {
      $successtrans = 0;
    }
  } else {
    $successventas = 0;
  }
  echo json_encode(array('successventas' => $successventas, 'successtrans' => $successtrans, 'successctasctes' => $successctasctes, 'successctasctesinsert' => $successctasctesinsert));
}
//ELIMINAR DATOS VENTAS
if ($_POST['EliminarDatosVentas'] == 1) {
  $info = "B: " . $_SESSION['Usuario'] . ' | ' . date('Y-m-d (h:m:s)') . ' servicios.procesos.php.funciones';

  //PRIMERO OBTENGO EL NUMPEDIDO(CODIGO SEGUIMIENTO)
  $sql = "SELECT NumPedido FROM Ventas WHERE idPedido='$_POST[idPedido]' AND Eliminado='0'";
  $Resultado = $mysqli->query($sql);
  $rowventas = $Resultado->fetch_array(MYSQLI_ASSOC);

  if ($mysqli->query("UPDATE Ventas SET Eliminado=1,infoABM='$info' WHERE idPedido='$_POST[idPedido]' LIMIT 1")) {

    $sqlV = "SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$rowventas[NumPedido]' AND Eliminado='0'";
    $ResultadoV = $mysqli->query($sqlV);
    $rowV = $ResultadoV->fetch_array(MYSQLI_ASSOC);

    if ($rowV['NumPedido'] <> '') {
      $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$rowV[NumPedido]' AND TipoDeComprobante='Remito' LIMIT 1");
    }
    //BUSCO ID TRANSCLIENTES
    $sql = "SELECT id FROM TransClientes WHERE CodigoSeguimiento='$rowV[NumPedido]' AND Eliminado='0'";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
    //ACTUALIZO CTAS CTES
    $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
}

//AGREGAR DATOS VENTAS
if (isset($_POST['AgregarDatosVentas'])) {
  $_POST['codigoventa'];
  $sql = $mysqli->query("SELECT * FROM Productos WHERE Codigo='{$_POST['codigoventa']}'");
  $dato = $sql->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta = $dato['PrecioVenta'];
  $iva = $dato['PrecioVenta'] - ($PrecioVenta / 1.21);
  $Neto = $PrecioVenta / 1.21;

  if ($_POST['Fecha'] == '0000-00-00') {
    $Fecha = date('Y-m-d');
  } else {
    $Fecha = $_POST['Fecha'];
  }
  //BUSCO ID TRANSCLIENTES
  $sql = "SELECT if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,
               if(FechaEntrega='0000-00-00',Fecha,FechaEntrega) as Fecha, 
               if(FormaDePago='Origen',LocalidadOrigen,LocalidadDestino)as Localidad,NumeroComprobante,
               if(FormaDePago='Origen',IngBrutosOrigen,idClienteDestino)as idCliente
               FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0'";

  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);

  $Codigo = sprintf("%10d", $_POST['codigoventa']);
  if ($mysqli->query("INSERT INTO `Ventas`(`FechaPedido`, `Codigo`, `Titulo`, `Precio`, `Cantidad`, `Comentario`,
  `terminado`, `NumPedido`, `Total`, `Cliente`, `FechaEntrega`, `Localidad`, `NumeroRepo`, `ImporteNeto`, `Exento`, `Iva1`, `Usuario`,`idCliente`) VALUES 
  ('{$_POST['Fecha']}','{$Codigo}','{$_POST['tituloventa']}','{$_POST['precioventa']}','{$_POST['cantidadventa']}','{$_POST['observacionesventa']}','1',
  '{$_POST['codigoseguimiento']}','{$_POST['totalventa']}','{$row['Cliente']}','{$row['Fecha']}','{$row['Localidad']}','{$row['NumeroComprobante']}','{$Neto}','0',
  '{$iva}','{$_SESSION['Usuario']}','{$row['idCliente']}')")) {

    $sqlV = "SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE NumPedido='$_POST[codigoseguimiento]' AND Eliminado='0'";
    $ResultadoV = $mysqli->query($sqlV);
    $rowV = $ResultadoV->fetch_array(MYSQLI_ASSOC);
    $CodigoSeguimiento = $_POST['codigoseguimiento'];

    if ($CodigoSeguimiento <> '') {
      $mysqli->query("UPDATE TransClientes SET Debe='$rowV[Total]' WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0' AND TipoDeComprobante='Remito' LIMIT 1");
    }

    //BUSCO ID TRANSCLIENTES
    $sql = "SELECT id FROM TransClientes WHERE CodigoSeguimiento='$_POST[codigoseguimiento]' AND Eliminado='0'";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
    //ACTUALIZO CTAS CTES
    $mysqli->query("UPDATE Ctasctes SET Debe='$rowV[Total]' WHERE idTransClientes='$row[id]' AND Eliminado='0' LIMIT 1");

    echo json_encode(array('success' => 1));
  }
}

if (isset($_POST['Pendientes'])) {

  $_SESSION['RecorridoMapa'] = $_POST['Recorrido'];

  $sql = "SELECT TransClientes.*,HojaDeRuta.Posicion,Seguimiento.Estado FROM TransClientes 
  INNER JOIN HojaDeRuta ON TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
  INNER JOIN Seguimiento ON TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento
  WHERE TransClientes.Eliminado='0' AND TransClientes.FechaEntrega=curdate() AND TransClientes.Recorrido='$_POST[Recorrido]'
  AND Seguimiento.id=(SELECT MAX(id) FROM Seguimiento WHERE Seguimiento.CodigoSeguimiento=TransClientes.CodigoSeguimiento)
  ORDER BY HojaDeRuta.Posicion ASC";

  $Resultado = $mysqli->query($sql);

  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['ActualizarDireccion'])) {

  $direccion = mb_convert_encoding($_POST['Direccion'], 'UTF-8', 'auto');
  $datosmapa = geolocalizar($direccion);

  $latitud = $datosmapa[0];
  $longitud = $datosmapa[1];

  if ($_POST['Estado'] == '1') {
    $Estado = 'Abierto';
  } else {
    $Estado = 'Cerrado';
  }
  if ($_POST['Retirado'] == '1') {
    $Retirado = 1;
  } else {
    $Retirado = 0;
  }
  if ($_POST['Entregado'] == '1') {
    $Entregado = 1;
  } else {
    $Entregado = 0;
  }
  if ($_POST['Cobranzaintegrada'] == '1') {
    $CobranzaIntegrada = 1;
  } else {
    $CobranzaIntegrada = 0;
  }
  if ($_POST['Cobrarcaddy'] == '1') {
    $CobarCaddy = 1;
  } else {
    $CobarCaddy = 0;
  }

  // ACTUALIZO CLIENTES 
  if (!isset($_POST['idCliente'])) {
    $sql = $mysqli->query("UPDATE `Clientes` SET Direccion='$_POST[Direccion]',
                      Calle='$_POST[calle]',Barrio='$_POST[barrio]',Numero='$_POST[numero]',
                      Ciudad='$_POST[Ciudad]',CodigoPostal='$_POST[cp]',Latitud='$latitud',Longitud='$longitud' WHERE id='$_POST[idCliente]'");
  }
  if (!isset($_POST['CodigoSeguimiento'])) {
    //ACTUALIZO HOJA DE RUTA
    $sql = $mysqli->query("UPDATE `HojaDeRuta` SET Localizacion='$_POST[Direccion]',Estado='$Estado' WHERE Seguimiento='$_POST[CodigoSeguimiento]'");

    //ACTUALIZO ROADMAP (analizar si las direcciones se van a actualizar en las hojas de ruta desde este punto)
    //   $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Localizacion='$_POST[Direccion]',Estado='$Estado' WHERE Seguimiento='$_POST[CodigoSeguimiento]'");

  }
  //ACTUALIZO TRANS CLIENTES
  if ($_POST['Servicio'] == 'Entrega') {
    $sql = $mysqli->query("UPDATE `TransClientes` SET DomicilioDestino='$_POST[Direccion]',Retirado='$Retirado',Entregado='$Entregado',CobrarCaddy='$CobrarCaddy',CobrarEnvio='$CobranzaIntegrada' WHERE id='$_POST[id]' AND Eliminado='0' LIMIT 1");
  } else {
    $sql = $mysqli->query("UPDATE `TransClientes` SET DomicilioOrigen='$_POST[Direccion]',Retirado='$Retirado',Entregado='$Entregado',CobrarCaddy='$CobrarCaddy',CobrarEnvio='$CobranzaIntegrada' WHERE id='$_POST[id]' AND Eliminado='0' LIMIT 1");
  }
  echo json_encode(array('success' => 1, 'estado' => $_POST['Estado']));

  $Fecha = date("Y-m-d");
  $Hora = date("H:i");

  $sqlbusco = $mysqli->query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE  CodigoSeguimiento='$_POST[CodigoSeguimiento]')");
  $dato = $sqlbusco->fetch_array(MYSQLI_ASSOC);
  $EstadoSeguimiento = $_POST['EstadoSeguimiento'];

  $Visitas = $dato['Visitas'] + 1;
  $sqlseguimiento = $mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`, `Destino`,
                              `Avisado`, `idCliente`, `Retirado`, `Visitas`, `idTransClientes`)VALUES('{$Fecha}','{$Hora}','{$_SESSION['Usuario']}',
                              '{$_SESSION['Sucursal']}','{$_POST['CodigoSeguimiento']}','Carga Manual Sistema','{$Entregado}','{$EstadoSeguimiento}','{$dato['Destino']}','{$dato['Avisado']}','{$_POST['idCliente']}',
                              '{$Retirado}','{$Visitas}','{$dato['idTransClientes']}')");
}

//ELIMINAR REGISTROS

if ($_POST['EliminarRegistro'] == 1) {
  $info = "B: " . $_SESSION['Usuario'] . ' | ' . date('Y-m-d H:i') . ' | ' . 'servicios.Procesos.php.funciones.php';
  // 1FAJK96CD
  //ACTURALIZO HOJA DE RUTA
  if ($sql = $mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1")) {

    $sql_roadmap = $mysqli->query("UPDATE `Roadmap` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1");

    $hojaderuta = 1;
  } else {

    $hojaderuta = 0;
  }
  //ACTUALIZO TRANS CLIENTES
  $id_Eliminar = $_POST['id'];

  if ($id_Eliminar) {

    $sqlCtasCtes = $mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$id_Eliminar' AND Eliminado='0' LIMIT 1");

    if ($sql = $mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino: $_SESSION[Usuario]',infoABM='$info' WHERE id='$id_Eliminar' LIMIT 1")) {

      $transclientes = 1;
    } else {

      $transclientes = 0;
    }
  }

  $sqlventas = $mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]' LIMIT 1");

  echo json_encode(array('success' => 1, 'hojaderuta' => $hojaderuta, 'transclientes' => $transclientes));
}


//AL AGREGAR UN MOVIMIENTO DE REGISTRO
if (isset($_POST['enter_registration'])) {

  $sqlbusco = $mysqli->query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE  CodigoSeguimiento='$_POST[CodigoSeguimiento]')");
  $dato = $sqlbusco->fetch_array(MYSQLI_ASSOC);
  $EstadoSeguimiento = $_POST['state'];

  //BUSCO EL ID DE ESTADO
  $sql_state = $mysqli->query("SELECT id FROM Estados WHERE Estado='$EstadoSeguimiento'");
  $id_state = $sql_state->fetch_array(MYSQLI_ASSOC);
  $Obs = $_POST['obs'];

  $Fecha = date("Y-m-d");
  $Hora = date("H:i");

  $Visitas = $dato['Visitas'] + 1;
  $idCliente = $dato['idCliente'];
  $CodigoSeguimiento = $_POST['CodigoSeguimiento'];

  if ($EstadoSeguimiento == 'Entregado al Cliente') {
    if ($Obs == '') {
      $Obs = 'Ya entregamos tu paquete al cliente !.';
    }

    $Entregado = 1;
  } else {
    $Entregado = 0;
  }
  //RETIRADO
  if ($EstadoSeguimiento == 'Retirado del Cliente') {
    if ($CodigoSeguimiento) {
      $mysqli->query("UPDATE TransClientes SET Retirado='1' WHERE Eliminado=0 AND CodigoSeguimiento='$CodigoSeguimiento' LIMIT 1");
    }
    $Retirado = 1;

    if ($Obs == '') {
      $Obs = 'Ya retiramos el paquete !.';
    }

    $Result = $mysqli->query("SELECT * FROM TransClientes WHERE Eliminado=0 AND CodigoSeguimiento='$CodigoSeguimiento'");
    $Datos_transclientes = $Result->fetch_array(MYSQLI_ASSOC);

    $Pais = 'Argentina';

    $IngresaRoadmap = "INSERT INTO `Roadmap`(`Fecha`,`Recorrido`, `Localizacion`, `Ciudad`,
    `Provincia`,`Pais`,`Cliente`, `Titulo`, `Observaciones`,`Usuario`, `Estado`,
    `NumerodeOrden`,`Seguimiento`,`idCliente`,`NumeroRepo`,`ImporteCobranza`,`idTransClientes`)
    VALUES ('{$Fecha}','{$Datos_transclientes['Recorrido']}','{$Datos_transclientes['DomicilioDestino']}',
    '{$Datos_transclientes['CiudadDestino']}','{$Datos_transclientes['ProvinciaDestino']}','{$Pais}',
    '{$Datos_transclientes['ClienteDestino']}','{$Datos_transclientes['TipoDeComprobante']}',
    '{$Datos_transclientes['Observaciones']}','{$Usuario}','Abierto',
    '{$Datos_transclientes['NumerodeOrden']}','{$CodigoSeguimiento}','{$Datos_transclientes['idClienteDestino']}',
    '{$Datos_transclientes['NumeroVenta']}','{$importevalorcobro}','{$Datos_transclientes['id']}')";

    $mysqli->query($IngresaRoadmap);
  } else {
    $Retirado = $dato['Retirado'];
  }

  // DEVUELTO
  if ($EstadoSeguimiento == 'Devuelto al Cliente') {
    $Entregado = '0';
    $Devuelto = '1';
    $Estadohdr = 'Cerrado';

    //ACTUALIZO ROADMAP

    $mysqli->query("UPDATE Roadmap SET Devuelto='$Devuelto',Estado='Cerrado' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0");
  } else {

    $Devuelto = '0';
  }

  //ENTREGADO
  if ($Entregado == 1) {

    $Estadohdr = "Cerrado";

    //ACTUALIZO ROADMAP
    $mysqli->query("UPDATE Roadmap SET Estado='$Estadohdr' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0");
  }

  //EN TRANSITO
  if ($EstadoSeguimiento == 'En Transito') {
    $Estadohdr = "Abierto";
  }

  //OBSERVACIONES LE AGREGO CMS(CARGA MANUAL SISTEMA)
  $Observaciones = 'CMS-' . $Obs;

  //BUSCO EL ULTIMO RECORRIDO
  $sqlbuscotrans = $mysqli->query("SELECT MAX(id)as id,Recorrido,ClienteDestino FROM `TransClientes` WHERE CodigoSeguimiento ='$CodigoSeguimiento' AND Eliminado=0");
  $datosqlbuscotrans = $sqlbuscotrans->fetch_array(MYSQLI_ASSOC);

  //BUSCRO EL NUMERO DE ORDEN
  $sqlNumerodeOrden = $mysqli->query("SELECT NumerodeOrden FROM `HojaDeRuta` WHERE Seguimiento='$CodigoSeguimiento'");
  $datosNumerodeOrden = $sqlNumerodeOrden->fetch_array(MYSQLI_ASSOC);


  //EJECUTO LOS SQL

  $sqlseguimiento = "INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`, `Destino`,
`Avisado`, `idCliente`, `Retirado`, `Visitas`, `idTransClientes`, `Recorrido`,`NombreCompleto`,`state_id`,`NumerodeOrden`)VALUES('{$Fecha}','{$Hora}','{$_SESSION['Usuario']}',
'{$_SESSION['Sucursal']}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$EstadoSeguimiento}','{$dato['Destino']}','{$dato['Avisado']}','{$idCliente}',
'{$Retirado}','{$Visitas}','{$datosqlbuscotrans['id']}','{$datosqlbuscotrans['Recorrido']}','{$datosqlbuscotrans['ClienteDestino']}','{$id_state['id']}','{$datosNumerodeOrden['NumerodeOrden']}')";

  if ($mysqli->query($sqlseguimiento)) {
    //ACTUALIZO TRANSCLIENTES  
    if ($_POST['user']) {
      $new_user = $_POST['user'];
      $Usuario = $_SESSION['Usuario'];
      // $dato_act_x_sistema=$Usuario.' '.date('Y-m-d H:i');  
      $Notas = date('Y-m-d H:i') . ' ' . $Usuario . ' modifico el idABM desde Seguimiento';
      $mysqli->query("UPDATE TransClientes SET Estado='$EstadoSeguimiento',Entregado='$Entregado',Devuelto='$Devuelto',idABM='$new_user',Notas =  CONCAT(Notas, '$Notas'),NumerodeOrden='$datosNumerodeOrden[NumerodeOrden]' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 AND Haber=0 LIMIT 1");
    } else {
      $Usuario = $_SESSION['Usuario'];
      $dato_act_x_sistema = $Usuario . ' ' . date('Y-m-d H:i');
      $mysqli->query("UPDATE TransClientes SET Estado='$EstadoSeguimiento',Entregado='$Entregado',Devuelto='$Devuelto',infoABM='$dato_act_x_sistema' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 AND Haber=0 LIMIT 1");
    }



    //ACTUALIZO HOJA DE RUTA

    $mysqli->query("UPDATE HojaDeRuta SET Devuelto='$Devuelto',Estado='$Estadohdr' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");


    echo json_encode(array('success' => 1, 'estadohdr' => $Estadohdr));
  } else {
    echo json_encode(array('success' => 0));
  }
}
