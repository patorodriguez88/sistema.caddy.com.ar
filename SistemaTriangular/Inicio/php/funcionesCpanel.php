<?php

include_once "../../Conexion/Conexioni.php";
include_once __DIR__ . "/../../Funciones/Funciones.php";

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

  $sql = "SELECT CodigoSeguimiento
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

  while ($row = $Respuesta->fetch_array(MYSQLI_ASSOC)) {
    $codigoSeg = isset($row['CodigoSeguimiento']) ? $row['CodigoSeguimiento'] : '';

    if ($codigoSeg !== '') {
      // Estado_id 6 = "Cargado en Hoja de Ruta" (mismo motivo que este bloque registraba a mano antes).
      cambiarRecorrido($mysqli, $codigoSeg, '80', 6);
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
