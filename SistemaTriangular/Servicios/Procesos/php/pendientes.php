<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');


if (isset($_POST['CambiarFlex'])) {
  $sql = "SELECT Flex FROM TransClientes WHERE id='$_POST[id]'";
  $result = $mysqli->query($sql);
  $Flex = $result->fetch_array(MYSQLI_ASSOC);

  if ($Flex['Flex'] == 1) {
    $mysqli->query("UPDATE TransClientes SET Flex=0 WHERE id='$_POST[id]' LIMIT 1");
  } else {
    $mysqli->query("UPDATE TransClientes SET Flex=1 WHERE id='$_POST[id]' LIMIT 1");
  }

  echo json_encode(array('success' => 1, 'Flex' => $Flex['Flex']));
}

if (isset($_POST['Pendientes'])) {

  $sql = "SELECT * FROM TransClientes WHERE Entregado=0 AND Eliminado=0 AND Haber=0 AND Devuelto=0 AND  CodigoSeguimiento<>''";
  $Resultado = $mysqli->query($sql);
  $rows = array();

  if ($Resultado->num_rows > 0) {

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
      //SI ESTE PAQUETE NO ESTA MARCADO PROCEDO AL CONTROL DE WAREHOUSE
      //CONTROLO SI EL PAQUETE PASO POR WEPOINT, DE SER ASI LO MARCO EN TRANSCLIENTES
      if (isset($row['Wepoint_f']) && $row['Wepoint_f'] == '0000-00-00') {

        $sql = $mysqli->query("SELECT Time,CodigoSeguimiento_caddy FROM wepoint WHERE id=(SELECT MIN(id) FROM wepoint WHERE CodigoSeguimiento_caddy='$row[CodigoSeguimiento]')");
        $result_sql_wepoint = $sql->fetch_array(MYSQLI_ASSOC);

        if (isset($result_sql_wepoint['Time'])) {
          $separar = explode(" ", $result_sql_wepoint['Time']);
          $fecha = $separar[0];
          $hora = $separar[1];

          $mysqli->query("UPDATE TransClientes SET Wepoint_f='$fecha',Wepoint_h='$hora' WHERE CodigoSeguimiento='$result_sql_wepoint[CodigoSeguimiento_caddy]' AND Wepoint_f='' LIMIT 1");
        }
      }
      $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
  } else {

    echo json_encode(array('data' => []));
  }

  // echo json_encode(array('data'=>$rows));

}

if (isset($_POST['Actualiza'])) {

  $Entregado = $_POST['entregado'];
  $Observaciones = 'CMS: ' . $_POST['Observaciones'];

  if ($_POST['Fecha'] == '') {
    $Fecha = date("Y-m-d");
  } else {
    $Fecha = date("Y-m-d", strtotime($_POST['Fecha']));
  }
  if ($_POST['Hora'] == '') {
    $Hora = date("H:i");
  } else {
    $Hora = date('H:i', strtotime($_POST['Hora']));
  }

  $sql = $mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino,Recorrido,NumerodeOrden FROM TransClientes WHERE id='$_POST[id]' AND Eliminado='0'");
  $sqldato = $sql->fetch_array(MYSQLI_ASSOC);
  $sql = $mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]' LIMIT 1");

  $sqlseguimiento = $mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
                              `idCliente`, `Retirado`,`idTransClientes`,`Destino`,`Recorrido`,`NumerodeOrden`)VALUES('{$Fecha}','{$Hora}','{$_SESSION['Usuario']}',
                              '{$_SESSION['Sucursal']}','{$sqldato['CodigoSeguimiento']}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
                              '{$sqldato['idClienteDestino']}','1','{$sqldato['id']}','{$sqldato['ClienteDestino']}','{$sqldato['Recorrido']}','{$sqldato['NumerodeOrden']}')");

  $sql = $mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");

  $sql = $mysqli->query("UPDATE `TransClientes` SET Estado='Entregado al Cliente',FechaEntrega='$Fecha' WHERE CodigoSeguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");

  //ACTUALIZA ROADMAP
  $sql = $mysqli->query("UPDATE `Roadmap` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]' LIMIT 1");

  echo json_encode(array('success' => 1));
}

if (isset($_POST['EliminarRegistro'])) {

  $info = "B: " . $_SESSION['Usuario'] . " | " . date('Y-m-d (H:i)') . " | " . 'Servicios/Procesos/php/pendientes.php';
  //ACTUALIZO HOJA DE RUTA
  if ($sql = $mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1")) {
    //ELIMINO ROADMAP
    $mysqli->query("UPDATE `Roadmap` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1");

    $hojaderuta = 1;
  } else {
    $hojaderuta = 0;
  }
  //ACTUALIZO TRANS CLIENTES
  if ($sql = $mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]',infoABM='$info' WHERE id='$_POST[id]' LIMIT 1")) {
    $transclientes = 1;
  } else {
    $transclientes = 0;
  }
  //BUSCO ID TRANSCLIENTES
  $sql = $mysqli->query("SELECT id FROM TransClientes WHERE id='$_POST[id]' AND Eliminado='0'");
  $datoid = $sql->fetch_array(MYSQLI_ASSOC);
  $sqlventas = $mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]' LIMIT 1");
  $sqlCtasCtes = $mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]' LIMIT 1");

  echo json_encode(array('success' => 1, 'hojaderuta' => $hojaderuta, 'transclientes' => $transclientes));
}

//SELECT RECORRIDOS
if (isset($_POST['BuscarRecorridos'])) {
  $BuscarVenta = $mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Activo=1");

  if ($_POST['cs'] <> '') {
    $BuscarRecorrido = $mysqli->query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_POST[cs]'");
    $Recorrido = $BuscarRecorrido->fetch_array(MYSQLI_ASSOC);


    $Rec_label = 'Recorrido ' . $Recorrido['Recorrido'] . ' ' . $Activo;
    $Rec = $Recorrido['Recorrido'];
  } else {
    $Rec = $Recorrido['Recorrido'];
    $Rec_label = "Seleccionar Recorrido";
  }
  echo '<option value=' . $Rec . '>' . $Rec_label . '</option>';

  while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC)) != NULL) {
    $BuscarActivo = $mysqli->query("SELECT Recorrido,NombreChofer FROM Logistica WHERE Estado='Cargada' AND Eliminado=0 AND Recorrido='" . $fila['Numero'] . "'");
    $DatosActivo = $BuscarActivo->fetch_array(MYSQLI_ASSOC);

    if ($BuscarActivo->num_rows <> 0) {
      $Activo = ' -> En Ruta ' . ' ' . $DatosActivo['NombreChofer'];
    } else {
      $Activo = '';
    }

    echo '<option value="' . $fila["Numero"] . '">' . $fila["Numero"] . ' | ' . $fila["Nombre"] . ' ' . $Activo . '</option>';
  }
  // Liberar resultados
  // mysql_free_result($BuscarVenta);
}

//HASTA ACA SELET RECORRIDOS
//SELECT RECORRIDOS
if (isset($_POST['ActualizaRecorrido'])) {
  if ($_POST['cs'] <> '') {
    $sql = $mysqli->query("SELECT NumerodeOrden,NombreChofer FROM `Logistica` WHERE Recorrido='$_POST[r]' AND Eliminado=0 AND Estado ='Cargada'");
    $NOrden = $sql->fetch_array(MYSQLI_ASSOC);

    if (($sql->num_rows) == 0) {
      $NO = 0;
    } else {
      $NO = $NOrden['NumerodeOrden'];
    }

    $ActualizarTransClientes = $mysqli->query("UPDATE TransClientes SET Recorrido='$_POST[r]',NumerodeOrden='$NO',Transportista='$NOrden[NombreChofer]' WHERE CodigoSeguimiento='$_POST[cs]' LIMIT 1");
    $ActualizarHojaDeRuta = $mysqli->query("UPDATE HojaDeRuta SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE Seguimiento='$_POST[cs]' LIMIT 1");


    echo json_encode(array('success' => 1, 'Recorrido' => $_POST['r'], 'CodigoSeguimiento' => $_POST['cs']));
  } else {
    echo json_encode(array('success' => 0));
  }
}
//HASTA ACA SELET RECORRIDOS
