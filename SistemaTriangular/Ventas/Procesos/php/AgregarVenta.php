<?php
error_reporting(-1);
ini_set('display_errors', '1');
include('../../../Conexion/Conexioni.php');

date_default_timezone_set('America/Argentina/Buenos_Aires');
$Usuario = "";

//DECLARO LAS VARIABLES

// $cliente = $_SESSION['NombreClienteA'];
$cliente = isset($_SESSION['NombreClienteA']) ? $_SESSION['NombreClienteA'] : '';
if (empty($cliente)) {
  echo json_encode(['success' => 2]);
  return;
}
$ClienteOrigen = isset($_SESSION['idOrigen']) ? $_SESSION['idOrigen'] : '';
$BuscarCliente = $mysqli->query("SELECT nombrecliente FROM Clientes WHERE id='$ClienteOrigen'");
$row = $BuscarCliente->fetch_array(MYSQLI_ASSOC);
$NombreCliente = $row['nombrecliente'];
$NumeroRepo = isset($_SESSION['NumeroRepo']) ? $_SESSION['NumeroRepo'] : '';
$NumeroPedido = isset($_SESSION['NumeroPedido']) ? $_SESSION['NumeroPedido'] : '';
$Usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : '';
$busq = isset($_SESSION['buscador']) ? $_SESSION['buscador'] : '';

if (isset($_POST['editor'])) {

  $NumeroRepo = $row[1];
  $NumeroPedido = $_GET['CS'];
}

if (($_POST['id'] <> '') || ($_POST['id'] <> null)) {
  $n = $_POST['id'];
  $Comentario = isset($_POST['Comentario']) ? $_POST['Comentario'] : null;
} else {
  echo json_encode(array('success' => 0));
  exit();
}

// SI LA VENTA VIENE DESDE COTIZADOR
if (isset($_POST['cotizador'])) {
  $CodigoP = $_POST['id'];
  $fecha = date('Y-m-d');
  $titulo = $_POST['tarifa'];
  $precio = $_POST['precio'];
  $Cantidad = $_POST['cantidad_t'];
  $Total = $Cantidad * $precio;
  $iva = ($Cantidad * $precio) - (($Cantidad * $precio) / 1.21);
  $ImporteNeto = ($Cantidad * $precio) - $iva;
  $iva3 = $Total - $ImporteNeto;
  $iva1 = '0';
  $iva2 = '0';
} else {

  if (isset($_POST['servicio'])) {
    $ordenar = "SELECT * FROM ClientesyServicios WHERE id='$n'";
  } else {
    $ordenar = "SELECT * FROM Productos WHERE id='$n'";
  }
  $datos = $mysqli->query($ordenar);
  while ($row = $datos->fetch_array(MYSQLI_ASSOC)) {
    $fecha = date('Y-m-d');

    if (isset($_POST['servicio'])) {
      $sql = $mysqli->query("SELECT * FROM Productos WHERE id='$row[Servicio]'");
      $datoordenar = $sql->fetch_array(MYSQLI_ASSOC);
      $CodigoP = $datoordenar['Codigo'];
      $titulo = $datoordenar['Titulo'];
      $precio = $row['PrecioPlano'];
      $iva = $precio - ($precio / 1.21);
    } else {
      $PorcCobro = $row['PorcentajeVenta']; // EN PORCENTAJE DE VENTA DEBO ACLARAR EL % DEL COBRO POR CUENTA Y ORDEN DEL CLIENTE
      $CodigoP = $row['Codigo'];
      $titulo = $row['Titulo'];
      $precio = $_POST['importe'];
      $seguro = $row['Seguro'];
      //   $precio=$row[PrecioVenta];  
      $iva = $row['Iva'];
    }

    if ($_POST['cantidad'] == '0') {
      $Cantidad = 1;
    } else {
      $Cantidad = $_POST['cantidad'];
    }

    $cliente = $_SESSION['NombreClienteA'];

    $Total = $Cantidad * $precio;
    if ($iva == 0) {
      $ImporteNeto = $Cantidad * $precio;
    } else {
      $ImporteNeto = ($Cantidad * $precio) / $iva;
    }

    if ($iva == 1.025) {
      $iva1 = $Total - $ImporteNeto;
      $iva2 = '0';
      $iva3 = '0';
    } elseif ($iva == 1.105) {
      $iva2 = $Total - $ImporteNeto;
      $iva1 = '0';
      $iva3 = '0';
    } elseif ($iva == 1.21) {
      $iva3 = $Total - $ImporteNeto;
      $iva1 = '0';
      $iva2 = '0';
      $Usuario = $_SESSION['Usuario'];
    }
  }
}


//PROCESOS...
//SI HAY QUE ELIMINAR
if (isset($_POST['Eliminar'])) {
  $idPedido = $_POST['id'];
  $idOrigen = $_SESSION['idOrigen'];
  $sqlelimina = "DELETE FROM Ventas WHERE idPedido='$idPedido' AND idCliente='$idOrigen'";
  if ($mysqli->query($sqlelimina)) {
    echo json_encode(array('success' => 1));
  }
}
//SI HAY QUE CARGAR COBRO A CUENTA
if (isset($_POST['cargarcobroacuenta'])) {
  if ($_POST['idOrigen'] == '') {
    echo json_encode(array('success' => 2));
  } else {
    $Total = ($_POST['importe'] * $PorcCobro) / 100;
    $iva3 = $Total - ($Total / 1.21);
    $ImporteNeto = $Total - $iva3;
    $precio = $Total;
    $Cantidad = 0;
    $sql = "INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
    ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,Comentario,CobrarEnvio,idCliente)
    VALUES('{$CodigoP}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$NombreCliente}',
    '{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$Comentario}'
    ,'{$_POST['importe']}','{$ClienteOrigen}')";

    if ($mysqli->query($sql)) {
      echo json_encode(array('success' => 1));
    } else {
      echo json_encode(array('success' => 0));
    }
  }
}

//SI HAY QUE CARGAR VALOR POR VALOR DECLARADO
if (isset($_POST['valordeclarado'])) {

  if ($_POST['idOrigen'] == '') {
    echo json_encode(array('success' => 2));
  } else if ($_POST['importe'] <= 10000) {
    echo json_encode(array('success' => 3));
  } else {

    // $Total=sure_min($_POST['idOrigen'],$_POST['importe']);

    $Total = ($_POST['importe'] * $seguro) / 100;
    $iva3 = $Total - ($Total / 1.21);
    $ImporteNeto = $Total - $iva3;
    $precio = $Total;
    $Cantidad = 0;
    $sql = "INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
    ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,Comentario,idCliente)
    VALUES('{$CodigoP}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$NombreCliente}',
    '{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$Comentario}'
    ,'{$ClienteOrigen}')";

    if ($mysqli->query($sql)) {
      echo json_encode(array('success' => 1));
    } else {
      echo json_encode(array('success' => 0));
    }
  }
}


if (isset($_POST['subir'])) {

  $Total = $_POST['importe'] * $Cantidad;
  $iva1 = '0';
  $iva2 = '0';
  $iva3 = $Total - ($Total / 1.21);
  $ImporteNeto = $Total - $iva3;
  $precio = $_POST['importe'];

  //SI HAY QUE CARGAR

  $sql = "INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Total,Cliente,NumeroRepo,
  ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,Comentario,idCliente)
  VALUES('{$CodigoP}','{$fecha}','{$titulo}','{$precio}','{$Cantidad}','{$Total}','{$NombreCliente}',
  '{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$Comentario}','{$ClienteOrigen}')";

  if ($mysqli->query($sql)) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
}
