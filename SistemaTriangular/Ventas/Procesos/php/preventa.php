<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once "../../../Conexion/Conexioni.php";

mysqli_set_charset($mysqli, "utf8");
date_default_timezone_set('America/Argentina/Buenos_Aires');

$FechaActual = date('Y-m-d');

if (isset($_POST['datos'])) {
  $sql = "SELECT * FROM PreVenta WHERE Cargado=0 AND Eliminado=0 ;";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//SELECT RECORRIDOS
if (isset($_POST['BuscarRecorridos'])) {
  $BuscarVenta = $mysqli->query("SELECT Numero,Nombre FROM Recorridos");
  if ($_POST['cs'] <> '') {
    $BuscarRecorrido = $mysqli->query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_POST[cs]'");
    $Recorrido = $BuscarRecorrido->fetch_array(MYSQLI_ASSOC);
    $Rec_label = 'Recorrido ' . $Recorrido['Recorrido'];
    $Rec = $Recorrido['Recorrido'];
  } else {
    $Rec = $Recorrido['Recorrido'];
    $Rec_label = "Seleccionar Recorrido";
  }
  echo '<option value=' . $Rec . '>' . $Rec_label . '</option>';
  while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC)) != NULL) {
    echo '<option value="' . $fila["Numero"] . '">' . $fila["Numero"] . ' | ' . $fila["Nombre"] . '</option>';
  }
  // Liberar resultados
  $BuscarVenta->free();
  // mysql_free_result($BuscarVenta);
}

//HASTA ACA SELET RECORRIDOS

//SELECT RECORRIDOS

if (isset($_POST['ActualizaRecorrido'])) {

  $sql = "UPDATE IGNORE PreVenta SET Recorrido='$_POST[r]' WHERE id='$_POST[id]'";

  if ($mysqli->query($sql)) {

    echo json_encode(array('success' => 1, 'Recorrido' => $_POST['r'], 'CodigoSeguimiento' => $_POST['cs']));
  } else {

    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['ActualizaRecorrido_all'])) {
  $id = $_POST['id'];

  for ($i = 0; $i <= count($id); $i++) {

    $sql = "UPDATE PreVenta SET Recorrido='$_POST[r]' WHERE id='$id[$i]'";
    $mysqli->query($sql);
  }

  echo json_encode(array('success' => 1, 'Recorrido' => $_POST['r']));
}

if (isset($_POST['Eliminar_all'])) {
  header('Content-Type: application/json; charset=utf-8');
  ob_start();
  try {
    // 1) Normalizar entrada: permitir array o string "1,2,3"
    $ids = $_POST['id'] ?? [];
    if (is_string($ids)) {
      // puede venir "1,2,3" o JSON '["1","2","3"]'
      $decoded = json_decode($ids, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $ids = $decoded;
      } else {
        $ids = array_filter(array_map('trim', explode(',', $ids)));
      }
    }
    if (!is_array($ids) || empty($ids)) {
      ob_end_clean();
      echo json_encode(['success' => 0, 'error' => 'No se recibieron IDs a eliminar']);
      exit;
    }

    // 2) Filtrar a enteros únicos
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) {
      ob_end_clean();
      echo json_encode(['success' => 0, 'error' => 'IDs inválidos']);
      exit;
    }

    // 3a) Opción segura y simple: prepared + foreach
    $stmt = $mysqli->prepare("UPDATE IGNORE PreVenta SET Eliminado = 1 WHERE id = ? LIMIT 1");
    if (!$stmt) {
      throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $mysqli->begin_transaction();
    $ok = 0;
    foreach ($ids as $id) {
      $stmt->bind_param("i", $id);
      if ($stmt->execute()) {
        $ok += $stmt->affected_rows; // 1 si tocó, 0 si ya estaba o no existía
      }
    }
    $mysqli->commit();
    $stmt->close();

    ob_end_clean();
    echo json_encode(['success' => 1, 'actualizados' => $ok, 'total_recibidos' => count($ids)]);
    exit;
  } catch (Throwable $e) {
    if ($mysqli && $mysqli->errno === 0) {
      @$mysqli->rollback();
    }
    ob_end_clean();
    echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
    exit;
  }
}
if (isset($_POST['EliminarPreventa'])) {

  $sql = "UPDATE IGNORE PreVenta SET Eliminado=1 WHERE id='$_POST[id]' LIMIT 1";

  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}
//HASTA ACA SELET RECORRIDOS
