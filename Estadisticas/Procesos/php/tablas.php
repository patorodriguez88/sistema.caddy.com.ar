<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['Eliminar']) && $_POST['Eliminar'] == 1) {
  $id = $_POST['id'];
  $mysqli->query("UPDATE Ctasctes SET Eliminado=1 WHERE idTransClientes='$id' LIMIT 1");
  $mysqli->query("UPDATE TransClientes SET Eliminado=1 WHERE id='$id' LIMIT 1");
  echo json_encode(["success" => 1]);
  exit;
}

if (isset($_POST['Totales'])) {
  $Desde = convertirFecha($_POST['desde']);
  $Hasta = convertirFecha($_POST['hasta']);

  switch ($_POST['Totales']) {
    case 1:
      echo json_encode(obtenerTotalesGenerales($mysqli, $Desde, $Hasta));
      break;

    case 2:
      echo json_encode(obtenerTotalesSimples($mysqli, $Desde, $Hasta));
      break;

    case 3:
      $Facturado = $_POST['facturado'];
      $opcion = $_POST['ciclo'];
      echo json_encode(obtenerTotalesPorCiclo($mysqli, $Desde, $Hasta, $Facturado, $opcion));
      break;
  }
  exit;
}

if (isset($_POST['Recorridos'])) {
  echo json_encode(obtenerVentasPorRecorridos($mysqli, $_POST['desde'], $_POST['hasta']));
  exit;
}

if (isset($_POST['Directas'])) {
  echo json_encode(obtenerVentasDirectas($mysqli, $_POST['desde'], $_POST['hasta']));
  exit;
}

// Funciones auxiliares
function convertirFecha($fecha)
{
  $partes = explode('/', $fecha);
  return "$partes[2]-$partes[0]-$partes[1]";
}

function obtenerTotalesSimples($mysqli, $Desde, $Hasta)
{
  $sql = "SELECT SUM(Debe) AS Total, SUM(Cantidad) AS Cantidad FROM TransClientes WHERE Fecha >= '$Desde' AND Fecha <= '$Hasta' AND Debe > 0 AND Eliminado = 0";
  $res = $mysqli->query($sql)->fetch_assoc();
  return ["success" => 1, "total" => $res['Total'], "cantidad" => $res['Cantidad']];
}

function obtenerTotalesGenerales($mysqli, $Desde, $Hasta)
{
  // Cuentas corrientes
  $sqlVenta = "SELECT SUM(Debe) AS TotalVenta FROM Ctasctes WHERE Debe > 0 AND Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta' AND idLogistica <> 0";
  $Venta = $mysqli->query($sqlVenta)->fetch_assoc()['TotalVenta'];

  $sqlServicios = "SELECT COUNT(id) AS Servicios FROM TransClientes WHERE Debe > 0 AND Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta'";
  $Servicios = $mysqli->query($sqlServicios)->fetch_assoc()['Servicios'];

  // No facturado
  $sqlNFTrans = "SELECT SUM(Debe) AS TotalVenta FROM TransClientes WHERE Facturado = 0 AND Debe > 0 AND Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta'";
  $TotalNFTrans = $mysqli->query($sqlNFTrans)->fetch_assoc()['TotalVenta'];

  $sqlNFLog = "SELECT SUM(ImporteF) AS Debe FROM Logistica INNER JOIN Clientes ON Clientes.id = Logistica.Cliente WHERE Logistica.Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta' AND TotalFacturado <> 0 AND Facturado = 0";
  $TotalNFLog = $mysqli->query($sqlNFLog)->fetch_assoc()['Debe'];
  $TotalNF = $TotalNFLog + $TotalNFTrans;

  $sqlServiciosNF = "SELECT COUNT(id) AS Servicios FROM TransClientes WHERE Debe > 0 AND Eliminado = 0 AND Facturado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta'";
  $ServiciosNF = $mysqli->query($sqlServiciosNF)->fetch_assoc()['Servicios'];

  // Facturado
  $sqlFLog = "SELECT SUM(subquery.TotalFacturado) AS Debe FROM (SELECT TotalFacturado FROM Logistica WHERE Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta' AND NumeroF <> '' GROUP BY NumeroF) AS subquery";
  $TotalFLog = $mysqli->query($sqlFLog)->fetch_assoc()['Debe'];

  $sqlFTrans = "SELECT SUM(Debe) AS TotalVenta FROM TransClientes WHERE Debe > 0 AND Eliminado = 0 AND Facturado = 1 AND Fecha >= '$Desde' AND Fecha <= '$Hasta'";
  $TotalFTrans = $mysqli->query($sqlFTrans)->fetch_assoc()['TotalVenta'];
  $TotalF = $TotalFLog + $TotalFTrans;

  $sqlServiciosF = "SELECT COUNT(id) AS Servicios FROM TransClientes WHERE Debe > 0 AND Eliminado = 0 AND Facturado = 1 AND Fecha >= '$Desde' AND Fecha <= '$Hasta'";
  $ServiciosF = $mysqli->query($sqlServiciosF)->fetch_assoc()['Servicios'];

  return [
    "success" => 1,
    "total" => $TotalF + $TotalNF,
    "totalf" => $TotalF,
    "totalnf" => $TotalNF,
    "servicios" => $Servicios,
    "serviciosf" => $ServiciosF,
    "serviciosnf" => $ServiciosNF
  ];
}

function obtenerTotalesPorCiclo($mysqli, $Desde, $Hasta, $Facturado, $ciclo)
{
  $map = [1 => "Diaria", 2 => "Quincenal", 3 => "Mensual"];
  $periodicidad = $map[$ciclo] ?? "";

  $sql = ($periodicidad === "") ?
    "SELECT Clientes.CicloFacturacion,RazonSocial,
    IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)AS idCliente,
    IF(FormaDePago='Origen',RazonSocial,ClienteDestino)AS Pagador,
    TransClientes.id,TransClientes.Fecha,TransClientes.NumeroComprobante AS NumeroVenta,TransClientes.CodigoSeguimiento,
    TransClientes.RazonSocial,
    IF(Facturado='1',CONCAT(ComprobanteF,' ',TransClientes.NumeroF),TipoDeComprobante)as TipoDeComprobante,Facturado,Debe,
    Flex,ClienteDestino,TransClientes.Observaciones
    FROM TransClientes INNER JOIN Clientes ON IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)=Clientes.id
    WHERE TransClientes.Eliminado=0 AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Debe>0 AND Facturado='$Facturado'" :
    "SELECT Clientes.CicloFacturacion,RazonSocial,
    IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)AS idCliente,
    IF(FormaDePago='Origen',RazonSocial,ClienteDestino)AS Pagador,
    TransClientes.id,TransClientes.Fecha,TransClientes.NumeroComprobante AS NumeroVenta,TransClientes.CodigoSeguimiento,
    TransClientes.RazonSocial,
    IF(Facturado='1',CONCAT(ComprobanteF,' ',TransClientes.NumeroF),TipoDeComprobante)as TipoDeComprobante,Facturado,Debe,
    Flex,ClienteDestino,TransClientes.Observaciones
    FROM TransClientes INNER JOIN Clientes ON IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)=Clientes.id WHERE Clientes.CicloFacturacion = '$periodicidad' AND Eliminado = 0 AND Fecha >= '$Desde' AND Fecha <= '$Hasta' AND Debe > 0 AND Facturado = '$Facturado'";

  // Aquí debes poner el SELECT correcto, removido para simplificar
  $res = $mysqli->query($sql);
  $rows = [];

  while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
  }

  return ['data' => $rows, 'ciclo' => $periodicidad];
}

function obtenerVentasPorRecorridos($mysqli, $desde, $hasta)
{
  $sql = "SELECT Logistica.Fecha, Logistica.NumerodeOrden, Logistica.Facturado, Clientes.nombrecliente AS Cliente, Logistica.Patente, Recorridos.Nombre AS Recorrido, Logistica.KilometrosRecorridos AS Kilometros, IFNULL(Productos.PrecioVenta, 0) AS PrecioVenta FROM Logistica LEFT JOIN Recorridos ON Logistica.Recorrido = Recorridos.Numero LEFT JOIN Productos ON Recorridos.CodigoProductos = Productos.Codigo LEFT JOIN Clientes ON Logistica.Cliente = Clientes.id WHERE PrecioVenta <> 0 AND Logistica.Eliminado = 0 AND Logistica.Fecha >= '$desde' AND Logistica.Fecha <= '$hasta' ORDER BY Fecha, Recorrido";
  $res = $mysqli->query($sql);
  $rows = [];
  while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
  }
  return ['data' => $rows];
}

function obtenerVentasDirectas($mysqli, $desde, $hasta)
{
  $sql = "SELECT Fecha, NumeroComprobante, IF(FormaDePago = 'Origen', RazonSocial, ClienteDestino) AS Cliente, FormaDePago, TipoDeComprobante, Entregado, CodigoSeguimiento, Cantidad, Debe FROM TransClientes WHERE Fecha >= '$desde' AND Fecha <= '$hasta' AND Debe > 0 AND Eliminado = 0 ORDER BY Fecha ASC";
  $res = $mysqli->query($sql);
  $rows = [];
  while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
  }
  return ['data' => $rows];
}
