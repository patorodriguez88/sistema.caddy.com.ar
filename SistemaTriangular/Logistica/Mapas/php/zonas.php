<?php
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['listarZonas'])) {
  $rows = [];
  if ($q = $mysqli->query("SELECT id,Nombre,LatitudN,LatitudS,LongitudE,LongitudO,Poligono,Color FROM ZonasMapa WHERE Eliminado=0 ORDER BY Nombre")) {
    while ($r = $q->fetch_assoc()) {
      $rows[] = $r;
    }
  }
  echo json_encode($rows, JSON_UNESCAPED_UNICODE);
  exit;
}

if (isset($_POST['Limpiar'])) {

  $_SESSION['rec'] = '';
  echo json_encode(['success' => 1]);
  exit;
}

//BUSCAR POLIGONOS
if (isset($_POST['Buscar_poly'])) {

  $rec = $_POST['rec'];
  $exito_r = json_encode($rec);
  $exito_r = trim($exito_r, '[]');
  $exito_r = str_replace('"', '', $exito_r);

  $_SESSION['rec'] = $exito_r;

  $zona = $_POST['zona'];
  $exito0 = json_encode($zona);
  $exito = trim($exito0, '[]');


  $sql = $mysqli->query("SELECT * FROM ZonasMapaPoly");
  $rows = array();

  while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows));
}


//BUSCAR RECTANGULOS
if (isset($_POST['Buscar'])) {

  $idZona = isset($_POST['idZona']) ? (int)$_POST['idZona'] : 0;

  if ($idZona > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM ZonasMapa WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $idZona);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_array(MYSQLI_ASSOC) : null;
    $stmt->close();
  } else {
    $zona = isset($_POST['zona']) ? $_POST['zona'] : '';
    $stmt = $mysqli->prepare("SELECT * FROM ZonasMapa WHERE Nombre=? LIMIT 1");
    $stmt->bind_param('s', $zona);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_array(MYSQLI_ASSOC) : null;
    $stmt->close();
  }

  if (!$row) {
    echo json_encode(['success' => 0, 'error' => 'Zona no encontrada']);
    exit;
  }

  $rec = $_POST['rec'] ?? null;   // no rompe si no viene

  $exito = json_encode($rec);
  $exito = trim($exito, '[]');
  $exito = str_replace('"', '', $exito);
  $_SESSION['rec'] = $exito;

  $sqlservicios = $mysqli->query("SELECT COUNT(Clientes.id)as total FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
   WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' 
   AND Clientes.Latitud>'$row[LatitudN]' AND Clientes.Latitud<'$row[LatitudS]' AND Clientes.Longitud>'$row[LongitudE]' AND Clientes.Longitud<'$row[LongitudO]' AND HojaDeRuta.Recorrido IN($exito)");
  $rowservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array(
    'exito' => $exito,
    'id' => $row['id'],
    'LatitudN' => $row['LatitudN'],
    'LatitudS' => $row['LatitudS'],
    'LongitudE' => $row['LongitudE'],
    'LongitudO' => $row['LongitudO'],
    'Poligono' => $row['Poligono'],
    'Color' => isset($row['Color']) ? $row['Color'] : null,
    'Total' => $rowservicios['total']
  ));
  exit;
}

//ACTUALIZA EL RECTANGULO CUANDO SE MUEVEN LOS PUNTOS

if (isset($_POST['Subir'])) {

  $idZona = isset($_POST['idZona']) ? (int)$_POST['idZona'] : 0;

  if ($idZona > 0) {
    $stmt = $mysqli->prepare("UPDATE ZonasMapa SET LatitudN=?, LatitudS=?, LongitudE=?, LongitudO=? WHERE id=? LIMIT 1");
    $stmt->bind_param('ddddi', $_POST['nelat'], $_POST['swlat'], $_POST['nelng'], $_POST['swlng'], $idZona);
    $stmt->execute();
    $stmt->close();
  } else {
    $zona = isset($_POST['zona']) ? $_POST['zona'] : '';
    $stmt = $mysqli->prepare("UPDATE ZonasMapa SET LatitudN=?, LatitudS=?, LongitudE=?, LongitudO=? WHERE Nombre=? LIMIT 1");
    $stmt->bind_param('dddds', $_POST['nelat'], $_POST['swlat'], $_POST['nelng'], $_POST['swlng'], $zona);
    $stmt->execute();
    $stmt->close();
  }

  $rec = $_POST['rec'];
  $exito = json_encode($rec);
  $exito = trim($exito, '[]');
  $exito = str_replace('"', '', $exito);
  $_SESSION['rec'] = $exito;

  $sqlservicios = $mysqli->query("SELECT COUNT(Clientes.id)as total FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
   WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' 
   AND Clientes.Latitud>'$_POST[nelat]' AND Clientes.Latitud<'$_POST[swlat]' AND Clientes.Longitud>'$_POST[nelng]' AND Clientes.Longitud<'$_POST[swlng]' AND HojaDeRuta.Recorrido IN($exito)");
  $rowservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('success' => 1, 'Total' => $rowservicios['total']));
  exit;
}

//ACTUALIZA EL POLIGONO (y también la caja N/S/E/O) CUANDO SE EDITA
if (isset($_POST['SubirPoligono'])) {

  $zona = isset($_POST['zona']) ? trim($_POST['zona']) : '';
  $pol  = isset($_POST['Poligono']) ? $_POST['Poligono'] : '';

  // rec puede venir o no
  $rec = $_POST['rec'] ?? null;
  $exito = json_encode($rec);
  $exito = trim($exito, '[]');
  $exito = str_replace('"', '', $exito);
  $_SESSION['rec'] = $exito;

  if ($zona === '' || $pol === '') {
    echo json_encode(['success' => 0, 'error' => 'Faltan parámetros zona o Poligono']);
    exit;
  }

  // Caja (bounding box) calculada en frontend
  $LatitudN = isset($_POST['LatitudN']) ? (float)$_POST['LatitudN'] : null;
  $LatitudS = isset($_POST['LatitudS']) ? (float)$_POST['LatitudS'] : null;
  $LongitudE = isset($_POST['LongitudE']) ? (float)$_POST['LongitudE'] : null;
  $LongitudO = isset($_POST['LongitudO']) ? (float)$_POST['LongitudO'] : null;

  $idZona = isset($_POST['idZona']) ? (int)$_POST['idZona'] : 0;

  if ($idZona > 0) {
    $stmt = $mysqli->prepare("UPDATE ZonasMapa SET Poligono=?, LatitudN=?, LatitudS=?, LongitudE=?, LongitudO=? WHERE id=? LIMIT 1");
  } else {
    $stmt = $mysqli->prepare("UPDATE ZonasMapa SET Poligono=?, LatitudN=?, LatitudS=?, LongitudE=?, LongitudO=? WHERE Nombre=? LIMIT 1");
  }

  if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'Prepare failed: ' . $mysqli->error]);
    exit;
  }

  if ($idZona > 0) {
    $stmt->bind_param('sddddi', $pol, $LatitudN, $LatitudS, $LongitudE, $LongitudO, $idZona);
  } else {
    $stmt->bind_param('sdddds', $pol, $LatitudN, $LatitudS, $LongitudE, $LongitudO, $zona);
  }
  $ok = $stmt->execute();
  $stmt->close();

  if (!$ok) {
    echo json_encode(['success' => 0, 'error' => 'Error SQL: ' . $mysqli->error]);
    exit;
  }

  // Recalcular total usando la caja (rápido). El filtrado exacto por polígono lo hace el frontend.
  $sqlservicios = $mysqli->query("SELECT COUNT(Clientes.id)as total FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>''
    AND Clientes.Latitud>'{$LatitudN}' AND Clientes.Latitud<'{$LatitudS}'
    AND Clientes.Longitud>'{$LongitudE}' AND Clientes.Longitud<'{$LongitudO}'
    AND HojaDeRuta.Recorrido IN({$exito})");

  $rowservicios = $sqlservicios ? $sqlservicios->fetch_array(MYSQLI_ASSOC) : ['total' => 0];

  echo json_encode(['success' => 1, 'Total' => $rowservicios['total']]);
  exit;
}

//AGREGAR NUEVA ZONA
if (isset($_POST['AgregarZona'])) {

  $sql = $mysqli->query("INSERT INTO `ZonasMapa` (Nombre,LatitudN,LatitudS,LongitudE,LongitudO)VALUES('{$_POST['nombrezona']}','-31.401121','-31.476530','-64.190392','-64.265930')");

  echo json_encode(array('success' => 1));
  exit;
}

if (isset($_POST['CambiarRecorridos'])) {

  //VARIABLES POST
  $NuevoRecorrido = $_POST['Recnew'];
  $Zona = $_POST['Zona'];

  $rec = $_POST['Recorridos'];
  $exito = json_encode($rec);
  $exito = trim($exito, '[]');
  $exito = str_replace('"', '', $exito);

  //BUSCO DATOS ZONA  
  $sql = $mysqli->query("SELECT * FROM ZonasMapa WHERE Nombre='$Zona'");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  $query = "SELECT HojaDeRuta.id,HojaDeRuta.Seguimiento FROM HojaDeRuta INNER JOIN Clientes ON Clientes.id = HojaDeRuta.idCliente
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND Clientes.Latitud>'$row[LatitudN]' AND 
    Clientes.Latitud<'$row[LatitudS]' AND Clientes.Longitud>'$row[LongitudE]' AND Clientes.Longitud<'$row[LongitudO]' AND HojaDeRuta.Recorrido IN($exito)";

  $result = $mysqli->query($query);
  $cuento = 0;

  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

    $query = $mysqli->query("SELECT NumerodeOrden,NombreChofer FROM Logistica WHERE Eliminado='0' AND Estado IN('Alta','Cargada') AND Recorrido='$NuevoRecorrido'");
    $DatoLogistica = $query->fetch_array(MYSQLI_ASSOC);

    $mysqli->query("UPDATE HojaDeRuta SET Recorrido='$NuevoRecorrido',NumerodeOrden='$DatoLogistica[NumerodeOrden]' WHERE id='$row[id]' LIMIT 1");

    if ($row['Seguimiento'] <> '') {
      $mysqli->query("UPDATE TransClientes SET Recorrido='$NuevoRecorrido',NumerodeOrden='$DatoLogistica[NumerodeOrden]',Transportista='$DatoLogistica[NombreChofer]' 
        WHERE CodigoSeguimiento='$row[Seguimiento]' LIMIT 1");
    }

    $cuento = $cuento + 1;
  }

  echo json_encode(array('success' => 1, 'cuenta' => $cuento));
  exit;
}

if (isset($_POST['eliminarZona'])) {

  $idZona = isset($_POST['idZona']) ? (int)$_POST['idZona'] : 0;

  if ($idZona <= 0) {
    echo json_encode(['success' => 0, 'error' => 'ID de zona inválido']);
    exit;
  }

  // ✅ Opción A: borrado físico
  $sql = "UPDATE ZonasMapa SET Eliminado=1 WHERE id = {$idZona} LIMIT 1";
  $ok  = $mysqli->query($sql);

  if ($ok) {
    echo json_encode(['success' => 1]);
  } else {
    echo json_encode(['success' => 0, 'error' => 'Error SQL: ' . $mysqli->error]);
  }
  exit;
}
