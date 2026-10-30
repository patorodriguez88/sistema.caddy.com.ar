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

// Búsqueda por número de pedido del cliente (CodigoProveedor). FIX (29/10): devolvía el primero que
// encontraba sin decir de qué cliente era, y el mismo número se repite entre clientes (IGALFER y
// VENEX, por ejemplo: 128 casos en 2026). Ahora devuelve todos (sin eliminados), del más nuevo al más
// viejo, con cliente, fecha, destino y estado, para que la pantalla avise y deje elegir.
if (isset($_POST['Buscar_CodigoProveedor'])) {
  $codigoProveedor = trim((string) ($_POST['CodigoProveedor'] ?? ''));
  $envios = [];
  if ($codigoProveedor !== '') {
    $st = $mysqli->prepare("SELECT CodigoSeguimiento, RazonSocial, IngBrutosOrigen, ClienteDestino, Fecha, Estado, Entregado
                            FROM TransClientes WHERE CodigoProveedor = ? AND Eliminado = 0 ORDER BY id DESC LIMIT 50");
    $st->bind_param('s', $codigoProveedor);
    $st->execute();
    $envios = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  }
  echo json_encode([
    'success' => $envios ? 1 : 0,
    'CodigoSeguimiento' => $envios[0]['CodigoSeguimiento'] ?? null, // compatibilidad: el más nuevo
    'envios' => $envios,
    'clientes' => count(array_unique(array_column($envios, 'IngBrutosOrigen'))),
  ]);
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
    // No es un envío: ¿es una preventa (pedido que entró y no se aceptó)? Pasa con etiquetas que el
    // cliente imprime al cargar el pedido y después la preventa se elimina en Pendientes.
    $preventa = null;
    $hayRegistro = ($r = $mysqli->query("SHOW TABLES LIKE 'PreVentaEliminadas'")) && $r->num_rows > 0;
    $st = !$hayRegistro ? false : $mysqli->prepare("SELECT p.id, p.Fecha, p.RazonSocial, p.ClienteDestino, p.idProveedor, p.Observaciones, p.Eliminado, p.Cargado,
                                   e.Usuario AS EliminoUsuario, e.Fecha AS EliminoFecha
                            FROM PreVenta p
                            LEFT JOIN PreVentaEliminadas e ON e.idPreVenta = p.id
                            WHERE p.CodigoSeguimiento = ? ORDER BY p.id DESC, e.id DESC LIMIT 1");
    if (!$st) { // sin la tabla de registro todavía (nadie eliminó desde el 30/10)
      $st = $mysqli->prepare("SELECT id, Fecha, RazonSocial, ClienteDestino, idProveedor, Observaciones, Eliminado, Cargado,
                                     NULL AS EliminoUsuario, NULL AS EliminoFecha
                              FROM PreVenta WHERE CodigoSeguimiento = ? ORDER BY id DESC LIMIT 1");
    }
    if ($st) {
      $st->bind_param('s', $CodigoSeguimiento);
      $st->execute();
      $preventa = $st->get_result()->fetch_assoc() ?: null;
    }
    echo json_encode(array('success' => 0, 'Notas' => $row_seguimiento['Notas'] ?? null, 'preventa' => $preventa));
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

// Un "Entregado" de una orden ya cerrada no se borra: se corrige a "No se pudo entregar".
// (2026-10-14, caso 7R1DR4JIZ) Si se borraba, el repartidor perdía la visita en su rendición
// (Externos cuenta Seguimiento con su usuario, la orden y Visitas<>0) y el envío quedaba
// pendiente pero adentro de la orden cerrada, sin poder cambiarlo de recorrido.
// Devuelve la fila si requiere corrección, o null.
function seguimientoRequiereCorreccion(mysqli $mysqli, int $id): ?array
{
  $st = $mysqli->prepare("SELECT s.*, l.Estado AS EstadoOrden FROM Seguimiento s
      JOIN Logistica l ON l.NumerodeOrden = s.NumerodeOrden AND l.Eliminado = 0
      WHERE s.id = ? AND s.Eliminado = 0 AND s.Entregado = 1 AND s.NumerodeOrden <> 0 LIMIT 1");
  $st->bind_param('i', $id);
  $st->execute();
  $fila = $st->get_result()->fetch_assoc();
  $st->close();
  if (!$fila || in_array($fila['EstadoOrden'], ['Alta', 'Cargada', 'Pendiente'], true)) {
    return null;
  }
  return $fila;
}

if (isset($_POST['InfoEliminarSeguimiento'])) {
  $fila = seguimientoRequiereCorreccion($mysqli, (int) ($_POST['id'] ?? 0));
  echo json_encode($fila ? [
    'requiere_motivo' => 1,
    'usuario' => $fila['Usuario'],
    'fecha' => $fila['Fecha'],
    'hora' => substr((string) $fila['Hora'], 0, 5),
    'orden' => $fila['NumerodeOrden'],
  ] : ['requiere_motivo' => 0]);
  exit;
}

//ELIMINAR SEGUIMEINTO
if (isset($_POST['EliminarSeguimiento'])) {
  $id = $_POST['id'];
  // FIX (2026-09-15, encontrado de paso): la clave de sesión es 'Usuario'
  // (mayúscula) - 'usuario' no existe, así que Eliminado_user quedaba
  // siempre vacío, sin registro de quién elimina cada movimiento.
  $user = $_SESSION['Usuario'] ?? '';
  $fechaHora = date('Y-m-d H:i:s');

  $correccion = seguimientoRequiereCorreccion($mysqli, (int) $id);
  if ($correccion) {
    $motivo = trim((string) ($_POST['Motivo'] ?? ''));
    if ($motivo === '') {
      echo json_encode(['success' => 0, 'error' => 'Es una entrega de una orden cerrada: hay que indicar el motivo por el que no se entregó.']);
      exit;
    }
    require_once __DIR__ . '/../Funciones.php';
    $c = $correccion;
    $idTrans = (int) $c['idTransClientes'];
    $cs = (string) $c['CodigoSeguimiento'];
    $mysqli->begin_transaction();
    try {
      $st = $mysqli->prepare("UPDATE Seguimiento SET Eliminado=1, Eliminado_date=?, Eliminado_user=? WHERE id=? LIMIT 1");
      $st->bind_param('ssi', $fechaHora, $user, $c['id']);
      $st->execute();
      $st->close();

      // La visita queda a nombre del repartidor, en su orden, con la fecha/hora reales.
      $est = $mysqli->query("SELECT id, Slug FROM Estados WHERE Estado='No se pudo entregar' LIMIT 1")->fetch_assoc();
      $estadoId = (int) ($est['id'] ?? 8);
      $slug = (string) ($est['Slug'] ?? '1st_visit_fail');
      $obs = $motivo . ' (corrección de ' . $user . ': estaba marcado Entregado)';
      $st = $mysqli->prepare("INSERT INTO Seguimiento (Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,
          Destino,idCliente,Retirado,Visitas,idTransClientes,Recorrido,NumerodeOrden,Estado_id,state_id,status)
          VALUES (?,?,?,?,?,?,0,'No se pudo entregar',?,?,?,1,?,?,?,?,?,?)");
      $st->bind_param('sssssssiiisiiis', $c['Fecha'], $c['Hora'], $c['Usuario'], $c['Sucursal'], $cs, $obs,
        $c['Destino'], $c['idCliente'], $c['Retirado'], $idTrans, $c['Recorrido'], $c['NumerodeOrden'], $estadoId, $estadoId, $slug);
      $st->execute();
      $st->close();

      // El envío sale de la orden cerrada (como un no entregado de la app) para poder rutearlo de nuevo.
      $whereTc = $idTrans > 0 ? "id=$idTrans" : "CodigoSeguimiento='" . $mysqli->real_escape_string($cs) . "' AND Eliminado=0";
      $mysqli->query("UPDATE TransClientes SET Entregado=0, NumerodeOrden=0 WHERE $whereTc LIMIT 1");

      // La hoja de ruta: copia al histórico (el manifiesto de la orden cerrada lo sigue mostrando) y se reabre.
      $st = $mysqli->prepare("SELECT * FROM HojaDeRuta WHERE Seguimiento=? AND IFNULL(Eliminado,0)=0 LIMIT 1");
      $st->bind_param('s', $cs);
      $st->execute();
      $hdr = $st->get_result()->fetch_assoc();
      $st->close();
      if ($hdr) {
        snapshotHojaDeRutaHistorico($mysqli, $hdr, (string) $hdr['Recorrido'], 0, date('Y-m-d'), date('H:i'), $user,
          'Corrección Entregado -> No se pudo entregar: ' . $motivo);
        $st = $mysqli->prepare("UPDATE HojaDeRuta SET Estado='Abierto', NumerodeOrden=0 WHERE id=? LIMIT 1");
        $st->bind_param('i', $hdr['id']);
        $st->execute();
        $st->close();
      }

      $mysqli->commit();
      echo json_encode(['success' => 1, 'corregido' => 1]);
    } catch (\Throwable $e) {
      $mysqli->rollback();
      echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
    }
    exit;
  }

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
