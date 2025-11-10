<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires'); //America/Argentina/Cordoba
$FechaActual = date('Y-m-d');

$NumeroRepo = $_SESSION['NumeroRepo'];
$idCliente = $_SESSION['idOrigen'];

if (isset($_POST['Servicios'])) {

  // Consulta para obtener las cuentas
  $sqlservicios = "SELECT id,Titulo,PrecioVenta FROM Productos";
  $resultado = $mysqli->query($sqlservicios);

  $servicios = [];
  while ($row = $resultado->fetch_assoc()) {
    $servicios[] = $row;
  }

  echo json_encode($servicios);

  $mysqli->close();
}

if (isset($_POST['datos'])) {

  $sql = "SELECT idPedido,Codigo,Titulo,Cantidad,Precio,Total,Comentario FROM Ventas WHERE idCliente='$idCliente' AND terminado='0' AND NumeroRepo='$NumeroRepo' AND FechaPedido='$FechaActual' AND Eliminado=0";
  $ResultadoTesoreria = $mysqli->query($sql);
  $rows = array();
  while ($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['totales'])) {
  $NombreCliente = $_SESSION['NombreClienteA'];
  $sql = "SELECT SUM(Cantidad)as Cantidad,SUM(ImporteNeto)as Neto,SUM(Total)as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumeroRepo='$NumeroRepo' AND idCliente='$idCliente' AND terminado='0' AND FechaPedido='$FechaActual' AND Eliminado=0";
  $ResultadoTesoreria = $mysqli->query($sql);
  $rows = array();
  while ($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

//DATOS BUSCAR

if (isset($_POST['datos_buscar'])) {
  $cs = $_POST['cs_cliente'];
  $sql = "SELECT idPedido,Codigo,Titulo,Cantidad,Precio,Total,Comentario FROM Ventas WHERE NumPedido='$cs' AND Eliminado=0";
  $ResultadoVentas = $mysqli->query($sql);
  $rows = array();
  while ($row = $ResultadoVentas->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['totales_buscar'])) {
  $cs = $_POST['cs_cliente'];
  $sql = "SELECT SUM(ImporteNeto)as Neto,SUM(Total)as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumPedido='$cs' AND terminado='1' AND Eliminado=0";
  $ResultadoTesoreria = $mysqli->query($sql);
  $rows = array();
  while ($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}
