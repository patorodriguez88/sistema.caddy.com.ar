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

  $id = $_POST['id'];


  for ($i = 0; $i <= count($id); $i++) {

    $sql = "UPDATE IGNORE PreVenta SET Eliminado='1' WHERE id='$id[$i]' LIMIT 1";
    $mysqli->query($sql);
  }

  echo json_encode(array('success' => 1));
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
