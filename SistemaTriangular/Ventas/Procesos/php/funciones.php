<?php
include_once "../../../Conexion/Conexioni.php";
function generarCodigo($longitud)
{
  $key = '';
  $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $max = strlen($pattern) - 1;
  for ($i = 0; $i < $longitud; $i++) {
    $key .= $pattern[mt_rand(0, $max)];
  }
  return $key;
}

// Verificar si el parámetro 'Limpiar' está presente en la solicitud
if (isset($_POST['Limpiar'])) {
  // Suponiendo que tienes una conexión $mysqli correctamente configurada
  $Dato = 'Eliminado';

  // Ejecutar la consulta de actualización
  $resultado = $mysqli->query("UPDATE Ventas SET Eliminado = 1 WHERE terminado = 0 AND FechaPedido <> CURDATE()");

  // Verificar si la consulta fue exitosa y si afectó a alguna fila
  if ($resultado) {
    $Num = $mysqli->affected_rows;

    if ($Num !== 0) {
      echo json_encode(['success' => 1, 'Num' => $Num]);
    } else {
      // Si no se afectaron filas, también puedes devolver un mensaje opcional
      echo json_encode(['success' => 0, 'message' => 'No se encontraron filas para actualizar']);
    }
  } else {
    // Si hubo un error en la consulta, puedes devolver el error
    echo json_encode(['success' => 0, 'error' => $mysqli->error]);
  }
}



//BUSCO COBRO A CUENTA
if (isset($_POST['cobro_a_cuenta'])) {
  $cs = $_POST['cs'];
  $sql = "SELECT SUM(CobrarEnvio)as CobrarEnvio FROM Ventas WHERE NumPedido='$cs' AND Eliminado='0'";
  $ResultadoVentas = $mysqli->query($sql);
  $row = $ResultadoVentas->fetch_array(MYSQLI_ASSOC);
  $CobrarEnvio = $row['CobrarEnvio'];
  if ($CobrarEnvio <> 0) {
    echo json_encode(array('success' => 1, 'CobrarEnvio' => $CobrarEnvio));
  } else {
    echo json_encode(array('success' => 0));
  }
}


if (isset($_POST['BuscarVenta'])) {
  $BuscarVenta = $mysqli->query("SELECT * FROM TransClientes WHERE CodigoSeguimiento = '$_POST[cs]'");
  if (($BuscarVenta->num_rows) == 1) {
    $rows = array();
    while ($row = $BuscarVenta->fetch_array(MYSQLI_ASSOC)) {
      $rows[] = $row;
    }
    echo json_encode(array('data' => $rows));
  }
}
//SELECT RECORRIDOS
if (isset($_POST['BuscarRecorridos'])) {
  $BuscarVenta = $mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Activo=1");
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
  $BuscarVenta->free_result();
}
//HASTA ACA SELET RECORRIDOS

//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
if (isset($_POST['id_servicio'])) {
  $id = $_POST['id_servicio'];

  $sqlservicios = $mysqli->query("SELECT id,PrecioVenta FROM Productos WHERE id='$id'");
  $datoservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);
  $PrecioVenta = $datoservicios['PrecioVenta'];
  $Codigo = $datoservicios['id'];
  echo json_encode(array('success' => 1, 'PrecioVenta' => $PrecioVenta, 'Codigo' => $Codigo));
}

if (isset($_POST['id_origen'])) {
  $id = $_POST['id_origen'];
  $sqlclientes = $mysqli->query("SELECT nombrecliente,Direccion,Retiro,Direccion1 FROM Clientes WHERE id='$id' AND Eliminado=0");
  $datoclientes = $sqlclientes->fetch_array(MYSQLI_ASSOC);


  $Nombre = $datoclientes['nombrecliente'];
  $Direccion = $datoclientes['Direccion'];
  $Direccion1 = $datoclientes['Direccion1'];
  $Retiro = $datoclientes['Retiro'];
  $_SESSION['idOrigen'] = $id;
  $_SESSION['NombreClienteA'] = $Nombre;

  $BuscaRepoAbierta = $mysqli->query("SELECT NumPedido,NumeroRepo FROM Ventas WHERE idCliente='$id' AND terminado='0' AND FechaPedido=curdate() AND Eliminado=0 AND NumPedido<>''");

  if (($BuscaRepoAbierta->num_rows) <> 0) {
    $row = $BuscaRepoAbierta->fetch_array(MYSQLI_ASSOC);
    $NumeroRepo = $row['NumeroRepo'];
    $NumeroPedido = $row['NumPedido'];
    $_SESSION['NumeroRepo'] = $NumeroRepo;
    $_SESSION['NumeroPedido'] = $NumeroPedido;
  } else {


    $BuscaNumRepo = $mysqli->query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");

    if ($row = $BuscaNumRepo->fetch_row()) {
      // Asegurar que sea número (si no hay resultados, usar 0)
      $numeroActual = is_numeric($row[0]) ? (int)$row[0] : 0;

      $NumeroRepo = str_pad($numeroActual + 1, 10, "0", STR_PAD_LEFT);

      $_SESSION['NumeroRepo'] = $NumeroRepo;
    }

    $NumeroPedido = generarCodigo(9);

    $_SESSION['NumeroPedido'] = $NumeroPedido;
  }



  echo json_encode(array('success' => 1, 'Nombre' => $Nombre, 'Direccion' => $Direccion, 'Retiro' => $Retiro, 'NumeroPedido' => $NumeroPedido, 'NumeroRepo' => $NumeroRepo, 'Direccion1' => $Direccion1));
}

if (isset($_POST['id_destino'])) {
  $id = $_POST['id_destino'];
  $sqlclientes = $mysqli->query("SELECT nombrecliente,Direccion FROM Clientes WHERE id='$id'");
  $datoclientes = $sqlclientes->fetch_array(MYSQLI_ASSOC);
  $_SESSION['idDestino'] = $id;
  $Nombre = $datoclientes['nombrecliente'];
  $Direccion = $datoclientes['Direccion'];
  echo json_encode(array('success' => 1, 'Nombre' => $Nombre, 'Direccion' => $Direccion));
}


if (isset($_POST['id_tercero'])) {
  $id = $_POST['id_tercero'];
  $sqlclientes = $mysqli->query("SELECT nombrecliente,Direccion FROM Clientes WHERE id='$id'");
  $datoclientes = $sqlclientes->fetch_array(MYSQLI_ASSOC);
  $_SESSION['idTercero'] = $id;
  $Nombre = $datoclientes['nombrecliente'];
  $Direccion = $datoclientes['Direccion'];
  echo json_encode(array('success' => 1, 'Nombre' => $Nombre, 'Direccion' => $Direccion));
}


if (isset($_POST['ComprobarNombre'])) {
  $BuscarNombre = $mysqli->query("SELECT id FROM Clientes WHERE nombrecliente = '$_POST[Nombre]' AND Eliminado=0");
  if (($BuscarNombre->num_rows) == 0) {
    echo json_encode(array('success' => 1));
  } else {
    $NumRows = $BuscarNombre->num_rows;
    echo json_encode(array('success' => 0, 'num' => $NumRows));
  }
}
