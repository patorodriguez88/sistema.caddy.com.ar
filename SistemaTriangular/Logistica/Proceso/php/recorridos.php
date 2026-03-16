<?php
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['Recorridos'])) {

  $sql = "SELECT Recorridos.DiaSalida,Recorridos.Numero,Recorridos.Nombre,Recorridos.Zona,Recorridos.Kilometros,Recorridos.Peajes,Recorridos.Color,
  Recorridos.CodigoProductos,Clientes.nombrecliente,Productos.PrecioVenta,Recorridos.Activo,Recorridos.id,COUNT(EntregasFijas.id)as Total FROM `Recorridos` 
  LEFT JOIN Clientes ON Recorridos.Cliente=Clientes.id
  LEFT JOIN Productos ON Productos.Codigo=Recorridos.CodigoProductos
  LEFT JOIN EntregasFijas ON EntregasFijas.Recorrido=Recorridos.Numero 
  WHERE Recorridos.Activo=1
  GROUP BY Recorridos.Numero";

  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

    $rows[] = $row;
  }


  echo json_encode(array('data' => $rows), JSON_UNESCAPED_UNICODE);
}

//VER ENVIOS FIJOS DEL RECORRIDO
if (isset($_POST['VerFijos'])) {

  $_SESSION['Recorrido'] = $_POST['id'];

  $sql = "SELECT
    tablaB.id,
    tablaA1.nombrecliente as nombre1,
    tablaA2.nombrecliente as nombre2
FROM EntregasFijas tablaB 
INNER JOIN Clientes as tablaA1 on tablaA1.id = tablaB.idClienteOrigen
INNER JOIN Clientes as tablaA2 on tablaA2.id = tablaB.idClienteDestino
WHERE tablaB.Recorrido='$_POST[id]'";

  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows));
}

//ELIMINAR FIJOS

if (isset($_POST['EliminarFijo'])) {

  $sql = "DELETE FROM EntregasFijas WHERE id='$_POST[id]'";

  if ($mysqli->query($sql)) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['ActivarRecorridos'])) {

  if ($_POST['Activo'] == 0) {
    $Activo = 1;
  } else {
    $Activo = 0;
  }

  $sql = "UPDATE Recorridos SET Activo='$Activo' WHERE id='$_POST[id]'";
  $mysqli->query($sql);

  echo json_encode(array('success' => 1));
}

//BUSCO EL PROXIMO NUMERO DE RECORRIDO

if (isset($_POST['Rec_num'])) {

  $sql = $mysqli->query("SELECT MAX(Numero)as Num FROM Recorridos");
  $row = $sql->fetch_array(MYSQLI_ASSOC);
  $nuevo = $row['Num'] + 1;
  echo json_encode(array('next_num_rec' => $nuevo));
}

//AGREGAR RECORRIDOS

if (isset($_POST['AgregarRecorridos'])) {

  $name = $_POST['name'];
  $number = (int)$_POST['number'];
  $zone = $_POST['zone'];
  $km = $_POST['km'];
  $toll = $_POST['toll'];
  $guest = isset($_POST['guest']) && $_POST['guest'] !== '' ? (int)$_POST['guest'] : 0;
  $service = isset($_POST['service']) && $_POST['service'] !== '' ? (int)$_POST['service'] : 0;
  $color = $_POST['color'];
  $fijo = isset($_POST['fijo']) ? (int)$_POST['fijo'] : 0;

  $sql = $mysqli->query("INSERT INTO `Recorridos`
    (`Numero`, `Nombre`, `Zona`, `Kilometros`, `Peajes`, `Cliente`, `CodigoProductos`, `Activo`, `Color`, `Fijo`) 
    VALUES
    ('{$number}','{$name}','{$zone}','{$km}','{$toll}','{$guest}','{$service}','1','{$color}','{$fijo}')");

  echo json_encode(array('success' => 1));
  exit;
}

//MODIFICAR RECORRIDOS

if (isset($_POST['ModificarRecorridos'])) {
  $id = (int)$_POST['id'];
  $name = $_POST['name'];
  $number = (int)$_POST['number'];
  $zone = $_POST['zone'];
  $km = $_POST['km'];
  $toll = $_POST['toll'];
  $guest = isset($_POST['guest']) && $_POST['guest'] !== '' ? (int)$_POST['guest'] : 0;
  $service = isset($_POST['service']) && $_POST['service'] !== '' ? (int)$_POST['service'] : 0;
  $color0 = explode('#', $_POST['color']);
  $color = isset($color0[1]) ? $color0[1] : $color0[0];
  $fijo = isset($_POST['fijo']) ? (int)$_POST['fijo'] : 0;

  $dias = '';
  if (isset($_POST['dias']) && is_array($_POST['dias'])) {
    $dias = implode(',', $_POST['dias']);
  }

  $sql = "UPDATE `Recorridos` SET 
            `Numero`='$number',
            `Nombre`='$name',
            `Zona`='$zone',
            `Kilometros`='$km',
            `Peajes`='$toll',
            `Cliente`='$guest',
            `CodigoProductos`='$service',
            `Color`='$color',
            `DiaSalida`='$dias',
            `Fijo`='$fijo'
          WHERE id='$id'";

  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
  }
  exit;
}


if (isset($_POST['Rec_datos'])) {
  $Rec = $_POST['Rec'];
  $sql = $mysqli->query("SELECT Recorridos.*,Productos.Titulo,Productos.PrecioVenta,Clientes.nombrecliente,Clientes.Direccion
FROM Recorridos 
LEFT JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo 
LEFT JOIN Clientes ON Recorridos.Cliente=Clientes.id
WHERE Recorridos.id='$Rec'");

  $row = $sql->fetch_array(MYSQLI_ASSOC);
  $rows = array();
  $rows[] = $row;

  echo json_encode(array('data' => $rows));
}

// Desde aca ia
//LISTAR CLIENTES
if (isset($_POST['ListarClientes'])) {
  $sql = "SELECT id,nombrecliente,Direccion FROM Clientes WHERE Eliminado=0 ORDER BY nombrecliente ASC";
  $res = $mysqli->query($sql);
  $clientes = [];
  while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
  }
  echo json_encode(array('data' => $clientes), JSON_UNESCAPED_UNICODE);

  exit;
}

//LISTAR SERVICIOS
if (isset($_POST['ListarServicios'])) {
  $sql = "SELECT Codigo,Titulo,PrecioVenta FROM Productos ORDER BY Titulo ASC";
  $res = $mysqli->query($sql);
  $servicios = [];
  while ($row = $res->fetch_assoc()) {
    $servicios[] = $row;
  }
  echo json_encode(array('data' => $servicios), JSON_UNESCAPED_UNICODE);
  exit;
}
