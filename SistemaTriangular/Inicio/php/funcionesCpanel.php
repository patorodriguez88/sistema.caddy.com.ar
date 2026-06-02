<?php

include_once "../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['AgregarNotas'])) {

  $Notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
  $id    = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($id <= 0) {
    echo json_encode(array('success' => 0, 'error' => 'ID inválido'));
    exit;
  }

  $stmt = $mysqli->prepare("UPDATE TransClientes SET Notas=? WHERE id=? LIMIT 1");
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("si", $Notas, $id);

  if ($stmt->execute()) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0, 'error' => $stmt->error));
  }

  $stmt->close();
  exit;
}

if (isset($_POST['VerNotas'])) {

  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($id <= 0) {
    echo json_encode(array('success' => 0, 'error' => 'ID inválido'));
    exit;
  }

  $stmt = $mysqli->prepare("SELECT Notas FROM TransClientes WHERE id=? LIMIT 1");
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $dato = $res ? $res->fetch_array(MYSQLI_ASSOC) : null;
  $stmt->close();

  echo json_encode(array(
    'success' => 1,
    'notas'   => isset($dato['Notas']) ? $dato['Notas'] : ''
  ));
  exit;
}

if (isset($_POST['VaciarRecorrido'])) {

  $recorrido = isset($_POST['Recorrido']) ? trim($_POST['Recorrido']) : '';

  if ($recorrido === '') {
    echo json_encode(array('success' => 0, 'error' => 'Recorrido inválido'));
    exit;
  }

  $sql = "SELECT 
                Retirado,
                id,
                CodigoSeguimiento,
                IF(Retirado=1,ClienteDestino,RazonSocial) AS Nombre,
                IF(Retirado=1,DomicilioDestino,DomicilioOrigen) AS Domicilio,
                IF(Retirado=1,IngBrutosOrigen,idClienteDestino) AS idCliente
            FROM TransClientes
            WHERE Recorrido=? AND Eliminado=0 AND Entregado=0";

  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("s", $recorrido);
  $stmt->execute();
  $Respuesta = $stmt->get_result();

  $FechaHoy = date('Y-m-d');
  $Hora     = date("H:i");
  $Usuario  = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : '';
  $Estado   = 'Cargado en Hoja De Ruta';

  while ($row = $Respuesta->fetch_array(MYSQLI_ASSOC)) {

    $Nombre     = isset($row['Nombre']) ? $row['Nombre'] : '';
    $Domicilio  = isset($row['Domicilio']) ? $row['Domicilio'] : '';
    $idTrans    = isset($row['id']) ? $row['id'] : 0;
    $idCliente  = isset($row['idCliente']) ? $row['idCliente'] : 0;
    $Retirado   = isset($row['Retirado']) ? $row['Retirado'] : 0;
    $codigoSeg  = isset($row['CodigoSeguimiento']) ? $row['CodigoSeguimiento'] : '';

    if ($codigoSeg !== '') {

      $mysqli->query("UPDATE HojaDeRuta SET Recorrido='80', Estado='Abierto' WHERE Seguimiento='" . $mysqli->real_escape_string($codigoSeg) . "'");
      $mysqli->query("UPDATE TransClientes SET Recorrido='80' WHERE CodigoSeguimiento='" . $mysqli->real_escape_string($codigoSeg) . "'");

      $sqlvisitas = $mysqli->query("SELECT Visitas FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='" . $mysqli->real_escape_string($codigoSeg) . "')");
      $Visitas = $sqlvisitas ? $sqlvisitas->fetch_array(MYSQLI_ASSOC) : array('Visitas' => 0);

      $Observaciones = '';

      $mysqli->query("INSERT INTO Seguimiento
                (Fecha, Hora, Usuario, Sucursal, CodigoSeguimiento, Observaciones, Estado, NombreCompleto, Destino, idCliente, Retirado, Visitas, idTransClientes, Recorrido)
                VALUES
                (
                    '{$FechaHoy}',
                    '{$Hora}',
                    '" . $mysqli->real_escape_string($Usuario) . "',
                    'Córdoba',
                    '" . $mysqli->real_escape_string($codigoSeg) . "',
                    '" . $mysqli->real_escape_string($Observaciones) . "',
                    '{$Estado}',
                    '" . $mysqli->real_escape_string($Nombre) . "',
                    '" . $mysqli->real_escape_string($Domicilio) . "',
                    '" . $mysqli->real_escape_string($idCliente) . "',
                    '" . $mysqli->real_escape_string($Retirado) . "',
                    '" . $mysqli->real_escape_string(isset($Visitas['Visitas']) ? $Visitas['Visitas'] : 0) . "',
                    '" . $mysqli->real_escape_string($idTrans) . "',
                    '80'
                )");
    }
  }

  $stmt->close();

  echo json_encode(array('success' => 1));
  exit;
}

if (isset($_POST['OC'])) {

  $usuario = isset($_SESSION['NombreUsuario']) ? $_SESSION['NombreUsuario'] : '';

  $stmt = $mysqli->prepare("
        SELECT Estado, COUNT(id) AS id
        FROM OrdenesDeCompra
        WHERE UsuarioCarga=? AND CompraRelacionada=0
        GROUP BY Estado
    ");

  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("s", $usuario);
  $stmt->execute();
  $sql = $stmt->get_result();

  if ($sql && $sql->num_rows != 0) {
    $row = null;
    while ($fila = $sql->fetch_array(MYSQLI_ASSOC)) {
      $row = $fila;
    }

    $texto = ((int)$row['id'] > 1) ? 's' : '';

    echo json_encode(array(
      'success' => 1,
      'Total'   => $row['id'],
      'Estado'  => $row['Estado'],
      'Plural'  => $texto
    ));
  } else {
    echo json_encode(array('success' => 0));
  }

  $stmt->close();
  exit;
}

if (isset($_POST['DashboardOperativo'])) {

  echo json_encode(array(
    'success' => 1,
    'pendientes_total' => 128,
    'pendientes_sin_salir' => 33,
    'pendientes_en_ruta' => 95,
    'en_ruta_total' => 95,
    'recorridos_activos' => 7,
    'entregados_total' => 342,
    'entregados_variacion' => 18,
    'incidencias_total' => 21,
    'ausentes' => 8,
    'rechazados' => 5,
    'reprogramados' => 8,
    'simples_total' => 120,
    'simples_entregados' => 80,
    'simples_pendientes' => 40,
    'flex_total' => 220,
    'flex_entregados' => 180,
    'flex_pendientes' => 40,
    'meli_total' => 197,
    'meli_entregados' => 82,
    'meli_pendientes' => 115
  ));
  exit;
}

echo json_encode(array(
  'success' => 0,
  'error' => 'Acción no válida'
));
exit;
