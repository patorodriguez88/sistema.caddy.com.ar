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
  // FIX (reportado: "queda pensando" al buscar) - si el código no existe (o
  // está Eliminado), fetch_array() devuelve null y el acceso directo
  // $row_seguimiento['Visitas'] tira un warning de PHP; con
  // display_errors=1 (como está arriba en este archivo) ese warning se
  // imprime ANTES del json_encode y rompe el JSON - el JS nunca llega a
  // JSON.parse con éxito, el modal de "Buscando..." se queda abierto para
  // siempre. Se combinan ambas consultas en una sola (era innecesario
  // pegarle 2 veces a la misma fila) y se usa ?? para no depender de que
  // la fila exista.
  $BuscarSeguimiento = $mysqli->query("SELECT Visitas, Notas FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
  $row_seguimiento = $BuscarSeguimiento ? $BuscarSeguimiento->fetch_array(MYSQLI_ASSOC) : null;

  if (($row_seguimiento['Visitas'] ?? null) !== null) {
    echo json_encode(array('success' => 1, 'Visitas' => $row_seguimiento['Visitas'], 'Notas' => $row_seguimiento['Notas'] ?? null));
  } else {
    echo json_encode(array('success' => 0, 'Notas' => $row_seguimiento['Notas'] ?? null));
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
  $BuscarHDR = $mysqli->query("SELECT Estado, Posicion, Posicion_retiro FROM HojaDeRuta WHERE Seguimiento='$_POST[CodigoSeguimiento]'");
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

// MODIFICAR CANTIDAD de una linea de Ventas (desde la ficha de Seguimiento,
// tabla "Informacion en Ventas") + recalculo de tarifa + sincronizar
// TransClientes.Cantidad. Convencion acordada (2026-09-16):
//   - Si la linea tiene Precio=0 (flete sin cargo): se cambia la Cantidad
//     libremente, sin tocar ningun campo de plata.
//   - Si tiene Precio>0: se escala PROPORCIONAL a la cantidad nueva (factor
//     = cantidadNueva/cantidadActual) - Precio, Total, ImporteNeto e Iva1/2/3
//     todos con el mismo factor, para no romper el desglose de IVA. No se
//     vuelve a buscar la tarifa del cliente/zona (podria haber cambiado
//     desde que se armo el pedido) - se escala lo que ya esta cobrado.
//   - Solo toca la linea de Ventas elegida (por idPedido) - si el pedido
//     tiene otras lineas (seguro/cobranza, que se guardan con Cantidad=0),
//     esas NO se tocan.
//   - Lineas con Cantidad actual <= 0 no son de bultos (son seguro/cobranza)
//     y esta accion las rechaza.
if (isset($_POST['ModificarCantidadVenta'])) {
  $idPedido = (int) ($_POST['idPedido'] ?? 0);
  $cantidadNueva = (int) ($_POST['Cantidad'] ?? 0);

  if ($idPedido <= 0 || $cantidadNueva <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Cantidad invalida.']);
    exit;
  }

  $st = $mysqli->prepare("SELECT idPedido, NumPedido, Cantidad, Precio, Total, ImporteNeto, Iva1, Iva2, Iva3
                           FROM Ventas WHERE idPedido = ? AND Eliminado = 0 LIMIT 1");
  $st->bind_param('i', $idPedido);
  $st->execute();
  $venta = $st->get_result()->fetch_assoc();
  $st->close();

  if (!$venta) {
    echo json_encode(['success' => 0, 'error' => 'No se encontro esa linea de Ventas.']);
    exit;
  }

  $cantidadActual = (float) $venta['Cantidad'];
  if ($cantidadActual <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Esta linea no es de bultos (cantidad 0) - no se puede modificar cantidad aca.']);
    exit;
  }

  $precioActual = (float) $venta['Precio'];
  $factor = $cantidadNueva / $cantidadActual;

  if ($precioActual > 0) {
    $precioNuevo = round($precioActual * $factor, 2);
    $totalNuevo = round((float) $venta['Total'] * $factor, 2);
    $netoNuevo = round((float) $venta['ImporteNeto'] * $factor, 2);
    $iva1Nuevo = round((float) $venta['Iva1'] * $factor, 2);
    $iva2Nuevo = round((float) $venta['Iva2'] * $factor, 2);
    $iva3Nuevo = round((float) $venta['Iva3'] * $factor, 2);
  } else {
    // Flete sin cargo: se deja todo en 0, solo cambia la cantidad.
    $precioNuevo = $precioActual;
    $totalNuevo = (float) $venta['Total'];
    $netoNuevo = (float) $venta['ImporteNeto'];
    $iva1Nuevo = (float) $venta['Iva1'];
    $iva2Nuevo = (float) $venta['Iva2'];
    $iva3Nuevo = (float) $venta['Iva3'];
  }

  $upd = $mysqli->prepare("UPDATE Ventas SET Cantidad=?, Precio=?, Total=?, ImporteNeto=?, Iva1=?, Iva2=?, Iva3=?
                            WHERE idPedido = ? LIMIT 1");
  $upd->bind_param('iddddddi', $cantidadNueva, $precioNuevo, $totalNuevo, $netoNuevo, $iva1Nuevo, $iva2Nuevo, $iva3Nuevo, $idPedido);
  $okVenta = $upd->execute();
  $upd->close();

  // Sincronizar TransClientes.Cantidad (misma convencion que ya usa
  // Logistica/Proceso/php/etiquetas_recorrido.php::ModificarCantidad).
  $numPedido = (string) $venta['NumPedido'];
  $updTC = $mysqli->prepare("UPDATE TransClientes SET Cantidad=? WHERE CodigoSeguimiento=? AND Eliminado=0 LIMIT 1");
  $updTC->bind_param('is', $cantidadNueva, $numPedido);
  $okTC = $updTC->execute();
  $filasTC = $okTC ? $updTC->affected_rows : 0;
  $updTC->close();

  echo json_encode([
    'success' => $okVenta ? 1 : 0,
    'error' => $okVenta ? null : $mysqli->error,
    'transClientesActualizado' => $filasTC,
    'cantidadAnterior' => $cantidadActual,
    'precioAnterior' => $precioActual,
    'cantidadNueva' => $cantidadNueva,
    'precioNuevo' => $precioNuevo,
    'totalNuevo' => $totalNuevo,
  ]);
  exit;
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
  // FIX (2026-09-15, encontrado de paso): la clave de sesión es 'Usuario'
  // (mayúscula) - 'usuario' no existe, así que Eliminado_user quedaba
  // siempre vacío, sin registro de quién elimina cada movimiento.
  $user = $_SESSION['Usuario'] ?? '';
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
