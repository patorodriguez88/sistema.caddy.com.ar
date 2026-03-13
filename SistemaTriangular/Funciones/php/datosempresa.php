<?php
include_once "../../Conexion/Conexioni.php";

if (isset($_POST['Empresa'])) {
  $sql = "SELECT * FROM DatosEmpresa";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}
