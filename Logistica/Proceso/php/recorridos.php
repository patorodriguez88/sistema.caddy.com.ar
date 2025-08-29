<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (isset($_POST['Recorridos'])) {

  // $_SESSION['RecorridoMapa'] = $_POST['Recorrido'];

  $sql = "SELECT Recorridos.DiaSalida,Recorridos.Numero,Recorridos.Nombre,Recorridos.Zona,Recorridos.Kilometros,Recorridos.Peajes,Recorridos.Color,
  Recorridos.CodigoProductos,Clientes.nombrecliente,Productos.PrecioVenta,Recorridos.Activo,Recorridos.id,COUNT(EntregasFijas.id)as Total FROM `Recorridos` 
  LEFT JOIN Clientes ON Recorridos.Cliente=Clientes.id
  LEFT JOIN Productos ON Productos.Codigo=Recorridos.CodigoProductos
  LEFT JOIN EntregasFijas ON EntregasFijas.Recorrido=Recorridos.Numero 
  GROUP BY Recorridos.Numero";

  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows));
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
  $number = $_POST['number'];
  $zone = $_POST['zone'];
  $km = $_POST['km'];
  $toll = $_POST['toll'];
  $guest = $_POST['guest'];
  $service = $_POST['service'];
  $color = $_POST['color'];

  $sql = $mysqli->query("INSERT INTO `Recorridos`(`Numero`, `Nombre`, `Zona`, `Kilometros`, `Peajes`, `Cliente`, `CodigoProductos`, `Activo`, `Color`) 
VALUES ('{$number}','{$name}','{$zone}','{$km}','{$toll}','{$guest}','{$service}','1','{$color}')");

  echo json_encode(array('success' => 1));
}

//MODIFICAR RECORRIDOS

if (isset($_POST['ModificarRecorridos'])) {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $number = $_POST['number'];
  $zone = $_POST['zone'];
  $km = $_POST['km'];
  $toll = $_POST['toll'];
  $guest = $_POST['guest'];
  $service = $_POST['service'];
  $color0 = explode('#', $_POST['color']);
  $color = $color0[1];

  for ($i = 0; $i < count($_POST['dias']); $i++) {
    if ($i == count($_POST['dias']) - 1) {
      $dia = $_POST['dias'][$i];
    } else {
      $dia = $_POST['dias'][$i] . ",";
    }
    $dias .= $dia;
  }
  $sql = "UPDATE `Recorridos` SET `Numero`='$number', `Nombre`='$name', `Zona`='$zone', `Kilometros`='$km', `Peajes`='$toll',
     `Cliente`='$guest', `CodigoProductos`='$service', `Color`='$color',`DiaSalida`='$dias' WHERE id='$id'";

  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
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
