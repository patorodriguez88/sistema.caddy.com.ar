<?php
// Incluí la conexión (no debe emitir sali)
include_once "../../Conexion/Conexioni.php";

// Responder JSON siempre
header('Content-Type: application/json; charset=utf-8');

// Guardrail de sesión (si Conexioni.php ya corta con 401, esto no se ejecuta,
// pero si algún día lo cambiás, este check te salva el endpoint)
// Aceptar sesión válida si existe cualquiera de estas claves

if (empty($_SESSION['Usuario']) && empty($_SESSION['idusuario']) && empty($_SESSION['NCliente'])) {
  header('X-Session-Expired: 1');
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'NO_AUTH', 'USUARIO' => $_SESSION['Usuario'] ?? '', 'IDUSUARIO' => $_SESSION['idusuario'] ?? '', 'NCLIENTE' => $_SESSION['NCliente'] ?? '']);
  exit;
}

if (isset($_POST['Transporte'])) {
  $sql = "SELECT Recorridos.Nombre, Logistica.id, Logistica.NumerodeOrden, Logistica.Fecha, Logistica.Hora,
                   Patente, NombreChofer, NombreChofer2, Logistica.Recorrido, Logistica.Estado
            FROM Logistica
            INNER JOIN Recorridos ON Logistica.Recorrido = Recorridos.Numero
            WHERE (Logistica.Estado IN ('Alta','Cargada','Pendiente'))
              AND Logistica.Eliminado = 0
            GROUP BY Logistica.NumerodeOrden";
  $Resultado = $mysqli->query($sql);
  $rows = [];
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}

if (isset($_POST['Pendientes'])) {
  $id = $mysqli->real_escape_string($_POST['id'] ?? '');
  $sql = "SELECT TransClientes.DomicilioOrigen, TransClientes.Notas, TransClientes.id,
                   TransClientes.Fecha AS Fecha, TransClientes.FechaEntrega AS FechaEntrega,
                   TransClientes.RazonSocial AS Origen, TransClientes.ClienteDestino AS Destino,
                   TransClientes.DomicilioDestino, TransClientes.CodigoSeguimiento AS Seguimiento
            FROM TransClientes
            INNER JOIN HojaDeRuta ON HojaDeRuta.Seguimiento = TransClientes.CodigoSeguimiento
            WHERE TransClientes.Entregado = 0
              AND TransClientes.Recorrido = '$id'
              AND TransClientes.Eliminado = 0
              AND HojaDeRuta.Eliminado = 0
              AND TransClientes.Devuelto = 0";
  $MuestraTrans = $mysqli->query($sql);
  $rows = [];
  while ($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}


if (isset($_POST['PreVenta'])) {
  $sql = "SELECT COUNT(Cantidad) AS Cantidad, RazonSocial, DomicilioOrigen
            FROM PreVenta
            WHERE Cargado = 0 AND Eliminado = 0
            GROUP BY NCliente";
  $MuestraTrans = $mysqli->query($sql);
  if ($MuestraTrans->num_rows == 0) {
    echo json_encode(['success' => 0]);
    exit;
  }
  $rows = [];
  while ($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}


if (isset($_POST['Flota'])) {
  $sql = "SELECT CONCAT_WS(' ', Marca, Modelo) AS Marca, Dominio, Ano, Kilometros, Activo, Estado
            FROM Vehiculos
            WHERE VehiculoOperativo=1 AND Aliados=0";
  $Resultado = $mysqli->query($sql);
  $rows = [];
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}


if (isset($_POST['Logistica'])) {
  $sql = "SELECT HojaDeRuta.Recorrido,
                   COUNT(HojaDeRuta.id) AS id,
                   CONCAT_WS(' ', Logistica.NombreChofer, Logistica.NombreChofer2) AS Chofer,
                   Vehiculos.ColorSistema, Vehiculos.Dominio,
                   CONCAT_WS(' ', Vehiculos.Marca, Vehiculos.Modelo) AS Marca,
                   Logistica.NumerodeOrden, Recorridos.Nombre
            FROM HojaDeRuta
            INNER JOIN Logistica  ON HojaDeRuta.Recorrido = Logistica.Recorrido
            INNER JOIN Vehiculos  ON Logistica.Patente  = Vehiculos.Dominio
            INNER JOIN Recorridos ON HojaDeRuta.Recorrido = Recorridos.Numero
            WHERE HojaDeRuta.Estado='Abierto'
              AND HojaDeRuta.Eliminado=0
              AND Logistica.Eliminado=0
              AND Logistica.Estado<>'Cerrada'
            GROUP BY HojaDeRuta.Recorrido";
  $MuestraTrans = $mysqli->query($sql);
  $rows = [];
  while ($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}


if (isset($_POST['Logistica1'])) {
  $sql = "SELECT Recorridos.Zona, Recorridos.Color, TransClientes.Recorrido,
                   COUNT(TransClientes.id) AS id, Recorridos.Nombre
            FROM TransClientes
            INNER JOIN Recorridos ON Recorridos.Numero = TransClientes.Recorrido
            WHERE TransClientes.Entregado = 0
              AND TransClientes.Eliminado = 0
              AND TransClientes.Devuelto  = 0
              AND Haber = 0
            GROUP BY TransClientes.Recorrido";
  $MuestraTrans = $mysqli->query($sql);
  $rows = [];
  while ($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(['data' => $rows]);
  exit;
}


// Si llegó acá, no hubo 'flag' reconocido:
http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'BAD_REQUEST']);
