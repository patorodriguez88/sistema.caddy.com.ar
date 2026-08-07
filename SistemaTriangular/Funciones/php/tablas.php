<?php
// session_start();
include_once "../../Conexion/Conexioni.php";
//BUSCAR CODIGO DE SEGUIMIENTO POR CODIGO DE PROVEEDOR
date_default_timezone_set('America/Argentina/Cordoba');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['Webhook'])) {
  $BuscarTrans = $mysqli->query("SELECT * FROM Webhook_notifications WHERE idCaddy='$_POST[CodigoSeguimiento]'");
  $rows = array();
  while ($row = $BuscarTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['Buscar_CodigoProveedor'])) {

  $BuscarSeguimiento = $mysqli->query("SELECT CodigoSeguimiento FROM TransClientes WHERE CodigoProveedor='$_POST[CodigoProveedor]'");
  $row_seguimiento = $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC);

  if ($row_seguimiento['CodigoSeguimiento'] <> NULL) {
    echo json_encode(array('success' => 1, 'CodigoSeguimiento' => $row_seguimiento['CodigoSeguimiento']));
  } else {
    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['DatosClientes'])) {
  $BuscarFormaDePago = $mysqli->query("SELECT id,nombrecliente FROM Clientes ORDER BY nombrecliente");
  echo '<option value="">Seleccione una Opcion</option>';
  while (($fila = $BuscarFormaDePago->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["id"] . '">' . $fila["id"] . '- ' . $fila["nombrecliente"] . ' (Dir: )</option>';
  }
  // Liberar resultados
  // mysql_free_result($BuscarFormaDePago);
}

//VISITAS
if (isset($_POST['Seguimiento_Visitas'])) {
  $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
  $BuscarSeguimiento = $mysqli->query("SELECT Visitas FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
  $row_seguimiento = $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC);

  $BuscarNotas = $mysqli->query("SELECT Notas FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
  $row_notas = $BuscarNotas->fetch_array(MYSQLI_ASSOC);


  if ($row_seguimiento['Visitas'] <> NULL) {
    echo json_encode(array('success' => 1, 'Visitas' => $row_seguimiento['Visitas'], 'Notas' => $row_notas['Notas']));
  } else {
    echo json_encode(array('success' => 0, 'Notas' => $row_notas['Notas']));
  }
}

if (isset($_POST['Seguimiento_Modal'])) {
  $BuscarTrans = $mysqli->query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' AND Eliminado='0'");
  $rows = array();
  while ($row = $BuscarTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  $BuscarSeguimiento = $mysqli->query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]'");
  $rows_seguimiento = array();
  while ($row_seguimiento = $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC)) {
    $rows_seguimiento[] = $row_seguimiento;
  }
  $BuscarHDR = $mysqli->query("SELECT Estado FROM HojaDeRuta WHERE Seguimiento='$_POST[CodigoSeguimiento]'");
  $row_hdr = $BuscarHDR->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('data' => $rows, $rows_seguimiento, $row_hdr));
}
//TABLA AFORO TRANSACCIONES (TRANSCLIENTES)
if (isset($_POST['Aforo_Tabla_Trans'])) {
  $BuscarAforo = $mysqli->query("SELECT TipoDeComprobante,NumeroComprobante,Debe FROM TransClientes WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' AND Eliminado=0");
  $rows_aforo_trans = array();
  while ($row_aforo_trans = $BuscarAforo->fetch_array(MYSQLI_ASSOC)) {
    $rows_aforo_trans[] = $row_aforo_trans;
  }
  echo json_encode(array('data' => $rows_aforo_trans));
}
//COMPRUEBO ULTIMO ESTADO

if (isset($_POST['Compruebo'])) {

  $UltimoEstadosql = $mysqli->query("SELECT id,Estado FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' ORDER BY id DESC LIMIT 0,1");
  $UltimoEstado = $UltimoEstadosql->fetch_array(MYSQLI_ASSOC);
  echo json_encode(array('data' => $UltimoEstado['Estado']));
}

//WHATSAPP
// if (isset($_POST['whatsapp'])) {

//   $UltimoEstadosql = $mysqli->query("SELECT id,Estado FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' ORDER BY id DESC LIMIT 0,1");
//   $UltimoEstado = $UltimoEstadosql->fetch_array(MYSQLI_ASSOC);
//   echo json_encode(array('data' => $UltimoEstado[Estado]));

// }


//TABLA AFORO
if (isset($_POST['Aforo_Tabla'])) {
  $BuscarAforo = $mysqli->query("SELECT * FROM Ventas WHERE NumPedido='$_POST[CodigoSeguimiento]' AND Eliminado=0");
  $rows_aforo = array();
  while ($row_aforo = $BuscarAforo->fetch_array(MYSQLI_ASSOC)) {
    $rows_aforo[] = $row_aforo;
  }
  echo json_encode(array('data' => $rows_aforo));
}

//TABLA SEARCH
if (isset($_POST['Search_Tabla'])) {
  $BuscarAforo = $mysqli->query("SELECT id,Fecha,CodigoSeguimiento,RazonSocial,ClienteDestino,Estado,CodigoProveedor FROM TransClientes WHERE 
RazonSocial like '%$_POST[Variable]%' OR ClienteDestino like '%$_POST[Variable]%' AND Eliminado=0");
  $rows_aforo = array();
  while ($row_aforo = $BuscarAforo->fetch_array(MYSQLI_ASSOC)) {

    $rows_aforo[] = $row_aforo;
  }
  echo json_encode(array('data' => $rows_aforo));
}

if (isset($_POST['Seguimiento_Tabla'])) {
  $BuscarSeguimiento = $mysqli->query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' AND Eliminado='0'");
  $rows_seguimiento = array();
  while ($row_seguimiento = $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC)) {
    $rows_seguimiento[] = $row_seguimiento;
  }
  echo json_encode(array('data' => $rows_seguimiento));
}

//ELIMINAR SEGUIMEINTO
if (isset($_POST['EliminarSeguimiento'])) {
  $id = $_POST['id'];
  $user = $_SESSION['usuario'];
  $fechaHora = date('Y-m-d H:i:s');

  $sql = $mysqli->query("SELECT Entregado,Devuelto,CodigoSeguimiento FROM Seguimiento WHERE id='$id'");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  $Entregado = $row['Entregado'];
  $Devuelto = $row['Devuelto'];
  $CodigoSeguimiento = $row['CodigoSeguimiento'];

  $EliminarSeguimiento = $mysqli->query("UPDATE Seguimiento SET Eliminado=1,Eliminado_date='$fechaHora',Eliminado_user='$user' WHERE id='$id' LIMIT 1");

  if ($EliminarSeguimiento) {

    if ($Entregado == 1) {

      $mysqli->query("UPDATE TransClientes SET Entregado=0 WHERE Eliminado=0 AND CodigoSeguimiento='$CodigoSeguimiento' LIMIT 1");
    }
    if ($Devuelto == 1) {

      $mysqli->query("UPDATE TransClientes SET Devuelto=0 WHERE Eliminado=0 AND CodigoSeguimiento='$CodigoSeguimiento' LIMIT 1");
    }

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
  }
}


if (isset($_POST['Buscar_CodigoSeguimiento'])) {
  $BuscarSeguimiento = $mysqli->query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]'");
  $rows_seguimiento = array();
  while ($row_seguimiento = $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC)) {
    $rows_seguimiento[] = $row_seguimiento;
  }
  echo json_encode(array('data' => $rows_seguimiento));
}

if (isset($_POST['FormaDePago'])) {
  $BuscarFormaDePago = $mysqli->query("SELECT FormaDePago,CuentaContable FROM FormaDePago WHERE AdmiteCobranzas=1");
  echo '<option value="">Seleccione una Opcion</option>';
  while (($fila = $BuscarFormaDePago->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["CuentaContable"] . '">' . $fila["FormaDePago"] . '</option>';
  }
  // Liberar resultados
  // mysql_free_result($BuscarFormaDePago);
}

if (isset($_POST['TipoDeDocumento'])) {
  $BuscarTipoDocCliente = $mysqli->query("SELECT TipoDocumento_f FROM Clientes WHERE id='$_POST[id]'");
  $DatoTipoDocCliente = $BuscarTipoDocCliente->fetch_array(MYSQLI_ASSOC);
  if ($DatoTipoDocCliente['TipoDocumento_f'] <> '') {
    $TipoDoc_label = $DatoTipoDocCliente['TipoDocumento_f'];
    $TipoDoc = $DatoTipoDocCliente['TipoDocumento_f'];
  } else {
    $TipoDoc = '';
    $TipoDoc_label = "Seleccionar Tipo De Documento";
  }
  $BuscarTipoDoc = $mysqli->query("SELECT Codigo,Descripcion FROM AfipDocumentoIdComprador");
  $DatoTipoDoc = $BuscarTipoDoc->fetch_array(MYSQLI_ASSOC);

  echo '<option value=' . $TipoDoc . '>' . $TipoDoc_label . '</option>';
  while (($fila = $BuscarTipoDoc->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["Codigo"] . '">' . $fila["Codigo"] . ' ' . $fila["Descripcion"] . '</option>';
  }
  // Liberar resultados
  // mysql_free_result($BuscarTipoDoc);
}

if (isset($_POST['TipoDeResponsable'])) {

  $BuscarCondicionCliente = $mysqli->query("SELECT CondicionAnteIva, CondicionAnteIva_f FROM Clientes WHERE id='$_POST[id]'");
  $DatoCondicionCliente = $BuscarCondicionCliente->fetch_array(MYSQLI_ASSOC);

  // "CondicionAnteIva" (Datos Generales) casi nunca se cargó historicamente.
  // El dato real casi siempre esta en "CondicionAnteIva_f" (Datos Facturación),
  // con el mismo codigo de AfipTipoDeResponsables pero sin ceros a la izquierda
  // (ej: 1 en vez de 001), asi que si el primero esta vacio usamos ese.
  if ($DatoCondicionCliente['CondicionAnteIva'] <> '') {
    $CondicionActual = $DatoCondicionCliente['CondicionAnteIva'];
  } elseif ($DatoCondicionCliente['CondicionAnteIva_f'] <> '') {
    $CondicionActual = str_pad($DatoCondicionCliente['CondicionAnteIva_f'], 3, '0', STR_PAD_LEFT);
  } else {
    $CondicionActual = '';
  }

  if ($CondicionActual <> '') {
    $stmtActual = $mysqli->prepare("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo=?");
    $stmtActual->bind_param('s', $CondicionActual);
    $stmtActual->execute();
    $filaActual = $stmtActual->get_result()->fetch_assoc();
    $stmtActual->close();
    $CondicionActual_label = $filaActual['Descripcion'] ?? $CondicionActual;
    echo '<option value="' . $CondicionActual . '">' . $CondicionActual_label . '</option>';
  } else {
    echo '<option value="">Seleccionar Tipo De Responsable</option>';
  }

  $BuscarVenta = $mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeResponsables");

  while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["Codigo"] . '">' . $fila["Descripcion"] . '</option>';
  }
}

if (isset($_POST['RobotRecorrido'])) {

  if ($_POST['Todos'] == 0) {
    $sqlClientes = $mysqli->query("SELECT Ciudad FROM Clientes WHERE id='$_POST[id]'");
    $Localidad = $sqlClientes->fetch_array(MYSQLI_ASSOC);
    if ($Localidad == '') {
      $sqlTransClientes = $mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Activo='1'");
      echo '<option value="">Seleccione un Recorrido</option>';
    } else {
      $sqlTransClientes = $mysqli->query("SELECT Recorridos.Numero,Recorridos.Nombre FROM TransClientes INNER JOIN Recorridos 
        ON Recorridos.Numero=TransClientes.Recorrido 
        WHERE TransClientes.LocalidadDestino='$Localidad[Ciudad]' 
        AND TransClientes.Retirado='1'
        AND TransClientes.Eliminado='0'
        AND Recorridos.Activo='1' GROUP BY Recorridos.Numero ORDER BY COUNT(TransClientes.id)DESC");
      echo '<option value="">Seleccione un Recorrido en ' . $Localidad['Ciudad'] . '</option>';
    }
  } else {
    $sqlTransClientes = $mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Activo='1'");
    echo '<option value="">Seleccione un Recorrido</option>';
  }
  while (($fila = $sqlTransClientes->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["Numero"] . '">' . $fila["Numero"] . ' | ' . $fila["Nombre"] . '</option>';
  }
  // Liberar resultados
  // mysql_free_result($sqlTransClientes);
}
