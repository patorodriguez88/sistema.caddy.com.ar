<?php
include_once "../../../Conexion/Conexioni.php";
include_once __DIR__ . "/../../../Funciones/Funciones.php";
include_once __DIR__ . "/zonas_helpers.php";
header('Content-Type: application/json; charset=utf-8');

// Cuenta servicios abiertos de $exito (csv de Recorridos) dentro de la caja
// N/S/E/O - y, si se pasa un $poligono valido (>=3 puntos), refina ese
// resultado con punto-en-poligono real en vez de quedarse con la caja
// envolvente. Usado por Buscar/Subir/SubirPoligono para que el "Total" que
// ve el operador sea consistente con la forma real dibujada, no con su
// rectangulo envolvente (relevante para poligonos irregulares, ej. zonas
// importadas de un KML).
function contarServiciosEnZona(mysqli $mysqli, array $bbox, ?array $poligono, string $exito): int
{
    // LatitudN/LongitudE siempre se guardan como el valor mas grande (mas al
    // norte/este) y LatitudS/LongitudO el mas chico (confirmado revisando
    // como Subir/SubirPoligono arman el bbox desde el rectangulo/poligono
    // dibujado) - la comparacion de abajo estaba invertida desde antes de
    // esta sesion (Latitud>N AND Latitud<S, imposible si N>S siempre) y
    // esta funcion devolvia 0 candidatos para cualquier zona real.
    //
    // Clientes.Latitud/Longitud es TEXT: sin el "+ 0" la comparacion es
    // lexicografica y se rompe con negativos de distinta cantidad de digitos
    // ('-31.4181639' < '-31.417' da FALSE), => 0 candidatos siempre. El "+ 0"
    // fuerza comparacion numerica en los dos lados.
    $sql = $mysqli->query(
        "SELECT Clientes.Latitud, Clientes.Longitud FROM Clientes
         INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
         WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Devuelto=0 AND Clientes.Latitud<>''
           AND (Clientes.Latitud + 0) > ('{$bbox['LatitudS']}' + 0) AND (Clientes.Latitud + 0) < ('{$bbox['LatitudN']}' + 0)
           AND (Clientes.Longitud + 0) > ('{$bbox['LongitudO']}' + 0) AND (Clientes.Longitud + 0) < ('{$bbox['LongitudE']}' + 0)
           AND HojaDeRuta.Recorrido IN($exito)"
    );

    if (!$poligono || count($poligono) < 3) {
        return $sql ? $sql->num_rows : 0;
    }

    $total = 0;
    while ($row = $sql->fetch_assoc()) {
        $punto = ['lat' => (float)$row['Latitud'], 'lng' => (float)$row['Longitud']];
        if (puntoEnPoligono($punto, $poligono)) {
            $total++;
        }
    }
    return $total;
}

// Decodifica ZonasMapa.Poligono a un array de puntos {lat,lng} solo si tiene
// al menos 3 vertices validos - si no, null (para caer al fallback de caja).
function poligonoDeZona(?string $poligonoJson): ?array
{
    if (!$poligonoJson) {
        return null;
    }
    $decoded = json_decode($poligonoJson, true);
    return (is_array($decoded) && count($decoded) >= 3) ? $decoded : null;
}

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

// Recorridos para el selector de "Seleccionar Recorridos" del panel
// izquierdo (a diferencia de Proceso/php/pendientes.php::BuscarRecorridos,
// que devuelve TODOS los Recorridos activos y es compartido por muchas otras
// pantallas que si necesitan ver recorridos vacios - aca en Zonas no tiene
// sentido poder elegir uno que no tenga ningun servicio para reasignar, asi
// que se filtra aparte en vez de tocar ese endpoint compartido).
if (isset($_POST['RecorridosConServicios'])) {
  $sql = $mysqli->query(
    "SELECT DISTINCT Recorridos.Numero, Recorridos.Nombre
       FROM Recorridos
      INNER JOIN HojaDeRuta ON HojaDeRuta.Recorrido = Recorridos.Numero
      WHERE Recorridos.Activo=1 AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Devuelto=0
      ORDER BY Recorridos.Numero"
  );

  while ($fila = $sql->fetch_array(MYSQLI_ASSOC)) {
    $stmt2 = $mysqli->prepare("SELECT NombreChofer FROM Logistica WHERE Recorrido=? AND Estado='Cargada' AND Eliminado='0' LIMIT 1");
    $stmt2->bind_param('s', $fila['Numero']);
    $stmt2->execute();
    $rowActivo = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();
    $activo = $rowActivo ? ('-> En Ruta ' . $rowActivo['NombreChofer']) : '';

    echo '<option value="' . htmlspecialchars((string)$fila['Numero']) . '">'
      . htmlspecialchars($fila['Numero'] . ' | ' . $fila['Nombre'] . ' ' . $activo)
      . '</option>';
  }
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

  $total = contarServiciosEnZona($mysqli, $row, poligonoDeZona($row['Poligono']), $exito ?: '0');

  echo json_encode(array(
    'exito' => $exito,
    'id' => $row['id'],
    'LatitudN' => $row['LatitudN'],
    'LatitudS' => $row['LatitudS'],
    'LongitudE' => $row['LongitudE'],
    'LongitudO' => $row['LongitudO'],
    'Poligono' => $row['Poligono'],
    'Color' => isset($row['Color']) ? $row['Color'] : null,
    'Total' => $total
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

  $bbox = [
    'LatitudN' => $_POST['nelat'], 'LatitudS' => $_POST['swlat'],
    'LongitudE' => $_POST['nelng'], 'LongitudO' => $_POST['swlng'],
  ];
  $total = contarServiciosEnZona($mysqli, $bbox, null, $exito ?: '0');

  echo json_encode(array('success' => 1, 'Total' => $total));
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

  // Recalcular total refinando por el poligono real recien guardado, no solo
  // la caja envolvente - relevante en formas irregulares (ver contarServiciosEnZona).
  $bbox = ['LatitudN' => $LatitudN, 'LatitudS' => $LatitudS, 'LongitudE' => $LongitudE, 'LongitudO' => $LongitudO];
  $total = contarServiciosEnZona($mysqli, $bbox, poligonoDeZona($pol), $exito ?: '0');

  echo json_encode(['success' => 1, 'Total' => $total]);
  exit;
}

//AGREGAR NUEVA ZONA
if (isset($_POST['AgregarZona'])) {

  $nombrezona = trim((string)($_POST['nombrezona'] ?? ''));
  if ($nombrezona === '') {
    echo json_encode(['success' => 0, 'error' => 'Nombre requerido']);
    exit;
  }
  $stmt = $mysqli->prepare(
    "INSERT INTO ZonasMapa (Nombre,LatitudN,LatitudS,LongitudE,LongitudO)
     VALUES (?, -31.401121, -31.476530, -64.190392, -64.265930)"
  );
  $stmt->bind_param('s', $nombrezona);
  $ok = $stmt->execute();
  $stmt->close();

  echo json_encode(['success' => $ok ? 1 : 0, 'id' => $mysqli->insert_id]);
  exit;
}

if (isset($_POST['CambiarRecorridos'])) {

  //VARIABLES POST
  $NuevoRecorrido = (string)$_POST['Recnew'];
  $idZona = isset($_POST['idZona']) ? (int)$_POST['idZona'] : 0;

  $rec = $_POST['Recorridos'] ?? [];
  $exito = json_encode($rec);
  $exito = trim($exito, '[]');
  $exito = str_replace('"', '', $exito);

  // Dos formas de indicar la zona: una persistida en ZonasMapa (idZona) o un
  // poligono armado al vuelo por el operador con "Dibujar Zona" en el mapa
  // (PoligonoManual) - ese no se guarda como Zona, es solo para esta
  // asignacion puntual, asi que no hay fila ni bbox en la base para leer.
  $poligonoManualJson = $_POST['PoligonoManual'] ?? '';

  if ($idZona > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM ZonasMapa WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $idZona);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
      echo json_encode(['success' => 0, 'error' => 'Zona no encontrada']);
      exit;
    }

    // Poligono real de la zona si existe (>=3 vertices) - se usa para filtrar
    // en PHP los candidatos traidos por la caja envolvente, en vez de mover
    // servicios que caen en la caja pero visualmente fuera de la forma dibujada
    // (relevante para formas irregulares, ej. zonas importadas de un KML).
    $poligono = poligonoDeZona($row['Poligono']);
    $bbox = ['LatitudN' => $row['LatitudN'], 'LatitudS' => $row['LatitudS'], 'LongitudE' => $row['LongitudE'], 'LongitudO' => $row['LongitudO']];
  } elseif ($poligonoManualJson !== '') {
    $poligono = poligonoDeZona($poligonoManualJson);
    if ($poligono === null) {
      echo json_encode(['success' => 0, 'error' => 'Poligono invalido.']);
      exit;
    }
    $lats = array_column($poligono, 'lat');
    $lngs = array_column($poligono, 'lng');
    $bbox = ['LatitudN' => max($lats), 'LatitudS' => min($lats), 'LongitudE' => max($lngs), 'LongitudO' => min($lngs)];
  } else {
    echo json_encode(['success' => 0, 'error' => 'Falta idZona o PoligonoManual']);
    exit;
  }

  // Mismo criterio que contarServiciosEnZona(): LatitudN/LongitudE son
  // siempre el valor mas grande (norte/este), la comparacion va con el
  // mayor arriba - ver comentario ahi para el detalle del bug que tenia
  // esto antes (invertido, 0 candidatos siempre para cualquier zona real).
  // "+ 0": Clientes.Latitud/Longitud es TEXT; sin forzar numerico la
  // comparacion es lexicografica y con negativos da 0 filas (la previa
  // contaba bien pero el traspaso movia 0).
  $query = "SELECT HojaDeRuta.id,HojaDeRuta.Seguimiento,Clientes.Latitud,Clientes.Longitud
    FROM HojaDeRuta INNER JOIN Clientes ON Clientes.id = HojaDeRuta.idCliente
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Devuelto=0 AND Clientes.Latitud<>''
      AND (Clientes.Latitud + 0) > ('$bbox[LatitudS]' + 0) AND (Clientes.Latitud + 0) < ('$bbox[LatitudN]' + 0)
      AND (Clientes.Longitud + 0) > ('$bbox[LongitudO]' + 0) AND (Clientes.Longitud + 0) < ('$bbox[LongitudE]' + 0)
      AND HojaDeRuta.Recorrido IN($exito)";

  $result = $mysqli->query($query);
  $cuento = 0;
  // Puntos realmente movidos (lat/lng) - el frontend los usa para recolorear
  // en el mapa justo esos markers al color del Recorrido nuevo, sin recargar
  // todo (ver drag&drop de zonas sobre Recorridos "en alta").
  $movidos = [];

  while ($fila = $result->fetch_array(MYSQLI_ASSOC)) {

    if ($poligono !== null) {
      $punto = ['lat' => (float)$fila['Latitud'], 'lng' => (float)$fila['Longitud']];
      if (!puntoEnPoligono($punto, $poligono)) {
        continue; // dentro de la caja pero fuera de la forma real: no se toca
      }
    }

    $seMovio = false;

    if ($fila['Seguimiento'] <> '') {
      // Paquete real: TransClientes + HojaDeRuta + Seguimiento + webhook, todo vía la función unificada.
      $rCambio = cambiarRecorrido($mysqli, $fila['Seguimiento'], $NuevoRecorrido);
      // Solo cuenta como movido si realmente cambió (no si ya estaba en ese
      // recorrido o si la función lo rechazó): así "se movieron N" es honesto.
      $seMovio = is_array($rCambio) && (int)($rCambio['success'] ?? 0) === 1;
    } else {
      // Parada de HojaDeRuta sin paquete asociado todavía: no hay nada que cambiarRecorrido() pueda tocar.
      $qOrd = $mysqli->query("SELECT NumerodeOrden FROM Logistica WHERE Eliminado='0' AND Estado IN('Alta','Cargada') AND Recorrido='$NuevoRecorrido'");
      $DatoLogistica = $qOrd ? $qOrd->fetch_array(MYSQLI_ASSOC) : null;
      $noDest = $DatoLogistica['NumerodeOrden'] ?? '';
      $mysqli->query("UPDATE HojaDeRuta SET Recorrido='$NuevoRecorrido',NumerodeOrden='$noDest' WHERE id='$fila[id]' LIMIT 1");
      $seMovio = $mysqli->affected_rows > 0;
    }

    if ($seMovio) {
      $movidos[] = ['lat' => (float)$fila['Latitud'], 'lng' => (float)$fila['Longitud']];
      $cuento = $cuento + 1;
    }
  }

  echo json_encode(array('success' => 1, 'cuenta' => $cuento, 'movidos' => $movidos));
  exit;
}

// TODOS los Recorridos activos (no solo los "en alta") para poblar el <select>
// "Recorrido destino" de cada zona en el panel de redistribucion. Se marca
// EnAlta / NombreChofer si hay una orden Alta/Pendiente/Cargada abierta, e
// Iniciado si el chofer ya arranco el recorrido (Logistica.HoraSalidaReal), para
// avisar antes de mandarle paquetes.
if (isset($_POST['TodosLosRecorridosActivos'])) {
  $res = $mysqli->query("
    SELECT r.Numero, r.Nombre, r.Color,
           MAX(l.NombreChofer) AS NombreChofer,
           MAX(l.NumerodeOrden IS NOT NULL) AS EnAlta,
           MAX(l.HoraSalidaReal IS NOT NULL) AS Iniciado
      FROM Recorridos r
      LEFT JOIN Logistica l
        ON l.Recorrido = r.Numero
       AND l.Eliminado = 0
       AND l.Estado IN ('Alta','Pendiente','Cargada')
     WHERE r.Activo = 1 AND r.Numero > 0
     GROUP BY r.Numero, r.Nombre, r.Color
     ORDER BY EnAlta DESC, r.Nombre ASC
  ");
  $recorridos = [];
  while ($row = $res->fetch_assoc()) {
    $recorridos[] = [
      'Numero'       => (int)$row['Numero'],
      'Nombre'       => $row['Nombre'],
      'Color'        => $row['Color'] ?: '666666',
      'EnAlta'       => (int)$row['EnAlta'] === 1,
      'Iniciado'     => (int)$row['Iniciado'] === 1,
      'NombreChofer' => $row['NombreChofer'] ?: '',
    ];
  }
  echo json_encode(['status' => 'success', 'data' => $recorridos]);
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

// Reemplaza TODAS las zonas activas por un "set" nuevo de N zonas que el
// frontend genero balanceando la carga del dia (biseccion recursiva de los
// waypoints, ver generarZonasBalanceadas() en zonas.js). Un set por vez: se
// borran (soft) las que habia y se insertan las nuevas, en una transaccion.
// $_POST['zonas'] = [ { Nombre, Poligono(JSON), LatitudN, LatitudS, LongitudE,
// LongitudO, Color }, ... ].
if (isset($_POST['GenerarZonasSet'])) {
  $nuevas = $_POST['zonas'] ?? [];
  if (!is_array($nuevas) || count($nuevas) < 1) {
    echo json_encode(['success' => 0, 'error' => 'No se recibieron zonas para generar.']);
    exit;
  }
  if (count($nuevas) > 20) {
    echo json_encode(['success' => 0, 'error' => 'Demasiadas zonas (max 20).']);
    exit;
  }

  $mysqli->begin_transaction();
  try {
    $mysqli->query("UPDATE ZonasMapa SET Eliminado = 1 WHERE Eliminado = 0");

    $stmt = $mysqli->prepare(
      "INSERT INTO ZonasMapa (Nombre, LatitudN, LatitudS, LongitudE, LongitudO, Poligono, Color, Eliminado)
       VALUES (?, ?, ?, ?, ?, ?, ?, 0)"
    );
    if (!$stmt) {
      throw new Exception('prepare: ' . $mysqli->error);
    }

    $creadas = 0;
    foreach ($nuevas as $z) {
      $nombre = mb_substr(trim((string)($z['Nombre'] ?? '')), 0, 60);
      if ($nombre === '') {
        $nombre = 'Zona ' . ($creadas + 1);
      }
      $latN = (float)($z['LatitudN'] ?? 0);
      $latS = (float)($z['LatitudS'] ?? 0);
      $lngE = (float)($z['LongitudE'] ?? 0);
      $lngO = (float)($z['LongitudO'] ?? 0);
      $poli = (string)($z['Poligono'] ?? '');
      // Validacion minima: JSON con >=3 puntos {lat,lng}.
      $dec = json_decode($poli, true);
      if (!is_array($dec) || count($dec) < 3) {
        throw new Exception("Poligono invalido en \"{$nombre}\".");
      }
      $color = trim((string)($z['Color'] ?? ''));
      if (!preg_match('/^#?[0-9A-Fa-f]{6}$/', $color)) {
        $color = '#4D1A50';
      } elseif ($color[0] !== '#') {
        $color = '#' . $color;
      }

      $stmt->bind_param('sddddss', $nombre, $latN, $latS, $lngE, $lngO, $poli, $color);
      if (!$stmt->execute()) {
        throw new Exception('insert: ' . $stmt->error);
      }
      $creadas++;
    }
    $stmt->close();

    $mysqli->commit();
    echo json_encode(['success' => 1, 'creadas' => $creadas]);
  } catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
  }
  exit;
}

// Chequea que todos los servicios abiertos de los Recorridos seleccionados
// tengan coordenadas validas antes de trabajar con zonas - hoy quedaban
// silenciosamente afuera del mapa (Clientes.Latitud<>'' no detecta '0' ni
// basura fuera de Argentina) sin ningun aviso ni tabla que los liste.
if (isset($_POST['VerificarGeolocalizacion'])) {
  $recIds = array_map('intval', is_array($_POST['rec'] ?? null) ? $_POST['rec'] : []);
  $exito = implode(',', $recIds ?: [0]);

  $res = $mysqli->query(
    "SELECT Clientes.nombrecliente, HojaDeRuta.Seguimiento, Clientes.Latitud, Clientes.Longitud
       FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
      WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Devuelto=0
        AND HojaDeRuta.Recorrido IN($exito)"
  );

  $faltantes = [];
  $validos = 0;
  while ($row = $res->fetch_assoc()) {
    $lat = (float)$row['Latitud'];
    $lng = (float)$row['Longitud'];
    if (esCoordenadaValida($lat, $lng)) {
      $validos++;
    } else {
      $nombre = $row['nombrecliente'] ?: 'Cliente sin nombre';
      $seg = $row['Seguimiento'] ?: 'sin seguimiento';
      $faltantes[] = "$nombre (Seguimiento $seg)";
    }
  }

  echo json_encode([
    'status' => empty($faltantes) ? 'ok' : 'warning',
    'message' => empty($faltantes) ? '' : ('Hay ' . count($faltantes) . ' servicio(s) sin coordenadas válidas.'),
    'faltantes' => $faltantes,
    'total_validos' => $validos,
  ]);
  exit;
}

// Importa zonas desde un KML (o KMZ, que es un KML empaquetado en ZIP) -
// pensado para el mapa de zonas FLEX de Mercado Libre que el operador
// exporta desde Google My Maps: cada <Placemark> con <Polygon> se convierte
// en una Zona lista para usar (dibujada, editable, y ya utilizable con
// "Cambiar Recorrido"). Si ya existe una zona no eliminada con el mismo
// nombre, se actualiza en vez de duplicar - permite reimportar el archivo
// mas adelante si Mercado Libre cambia los limites.
if (isset($_POST['ImportarKML'])) {
  if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió ningún archivo.']);
    exit;
  }

  $tmpPath = $_FILES['archivo']['tmp_name'];
  $nombreArchivo = $_FILES['archivo']['name'];
  $esKmz = preg_match('/\.kmz$/i', $nombreArchivo);

  if ($esKmz) {
    $zip = new ZipArchive();
    if ($zip->open($tmpPath) !== true) {
      echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo KMZ (¿está corrupto?).']);
      exit;
    }
    $kmlContent = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $entry = $zip->getNameIndex($i);
      if (preg_match('/\.kml$/i', $entry)) {
        $kmlContent = $zip->getFromName($entry);
        break;
      }
    }
    $zip->close();
    if ($kmlContent === null) {
      echo json_encode(['status' => 'error', 'message' => 'El KMZ no contiene ningún archivo .kml.']);
      exit;
    }
  } else {
    $kmlContent = file_get_contents($tmpPath);
  }

  libxml_use_internal_errors(true);
  $xml = simplexml_load_string($kmlContent);
  if ($xml === false) {
    echo json_encode(['status' => 'error', 'message' => 'El archivo no es un KML/KMZ válido.']);
    exit;
  }
  $xml->registerXPathNamespace('k', 'http://www.opengis.net/kml/2.2');

  $placemarks = $xml->xpath('//k:Placemark[.//k:Polygon]');
  if (!$placemarks) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró ningún polígono en el archivo.']);
    exit;
  }

  $paleta = ['#1E6FD1', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#e83e8c', '#17a2b8', '#6610f2'];
  $creadas = 0;
  $actualizadas = 0;
  $omitidas = [];
  $zonas = [];

  foreach ($placemarks as $i => $pm) {
    $pm->registerXPathNamespace('k', 'http://www.opengis.net/kml/2.2');
    $nombreZona = trim((string)($pm->xpath('.//k:name')[0] ?? ''));
    $coordsNodo = $pm->xpath('.//k:Polygon//k:outerBoundaryIs//k:coordinates | .//k:Polygon//k:coordinates');
    $coordsTexto = trim((string)($coordsNodo[0] ?? ''));

    if ($nombreZona === '' || $coordsTexto === '') {
      $omitidas[] = $nombreZona !== '' ? $nombreZona : '(sin nombre, placemark #' . ($i + 1) . ')';
      continue;
    }

    $tuplas = preg_split('/\s+/', $coordsTexto, -1, PREG_SPLIT_NO_EMPTY);
    $puntos = [];
    foreach ($tuplas as $tupla) {
      $partes = explode(',', $tupla);
      if (count($partes) < 2) continue;
      $lng = (float)$partes[0];
      $lat = (float)$partes[1];
      $puntos[] = ['lat' => $lat, 'lng' => $lng];
    }
    // Un anillo KML cerrado repite el primer punto al final - se descarta el
    // duplicado, Google Maps Polygon no lo necesita.
    if (count($puntos) > 1 && $puntos[0] == $puntos[count($puntos) - 1]) {
      array_pop($puntos);
    }

    if (count($puntos) < 3) {
      $omitidas[] = "$nombreZona (menos de 3 vértices válidos)";
      continue;
    }

    $lats = array_column($puntos, 'lat');
    $lngs = array_column($puntos, 'lng');
    $bbox = ['LatitudN' => max($lats), 'LatitudS' => min($lats), 'LongitudE' => max($lngs), 'LongitudO' => min($lngs)];
    $poligonoJson = json_encode($puntos);
    $color = $paleta[$i % count($paleta)];
    $nombreZona = mb_substr($nombreZona, 0, 60);

    $stmt = $mysqli->prepare("SELECT id FROM ZonasMapa WHERE Nombre=? AND Eliminado=0 LIMIT 1");
    $stmt->bind_param('s', $nombreZona);
    $stmt->execute();
    $existente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existente) {
      $stmt = $mysqli->prepare("UPDATE ZonasMapa SET LatitudN=?, LatitudS=?, LongitudE=?, LongitudO=?, Poligono=?, Color=? WHERE id=? LIMIT 1");
      $stmt->bind_param('ddddssi', $bbox['LatitudN'], $bbox['LatitudS'], $bbox['LongitudE'], $bbox['LongitudO'], $poligonoJson, $color, $existente['id']);
      $stmt->execute();
      $stmt->close();
      $idZonaResultado = $existente['id'];
      $actualizadas++;
    } else {
      $stmt = $mysqli->prepare("INSERT INTO ZonasMapa (Nombre,LatitudN,LatitudS,LongitudE,LongitudO,Poligono,Color,Eliminado) VALUES (?,?,?,?,?,?,?,0)");
      $stmt->bind_param('sddddss', $nombreZona, $bbox['LatitudN'], $bbox['LatitudS'], $bbox['LongitudE'], $bbox['LongitudO'], $poligonoJson, $color);
      $stmt->execute();
      $stmt->close();
      $idZonaResultado = $mysqli->insert_id;
      $creadas++;
    }

    $zonas[] = ['id' => $idZonaResultado, 'Nombre' => $nombreZona, 'Color' => $color, 'vertices' => count($puntos)];
  }

  echo json_encode([
    'status' => 'success',
    'message' => "$creadas zona(s) creada(s), $actualizadas actualizada(s)" . (count($omitidas) ? ', ' . count($omitidas) . ' omitida(s)' : '') . '.',
    'creadas' => $creadas,
    'actualizadas' => $actualizadas,
    'omitidas' => $omitidas,
    'zonas' => $zonas,
  ]);
  exit;
}
