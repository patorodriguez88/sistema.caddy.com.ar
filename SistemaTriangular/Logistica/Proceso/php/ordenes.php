<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

//ESTO SE EJECUTAL AL ABRIR EL MODAL PARA CARGAR LA ORDEN

if (isset($_POST['MonetizarRecorrido'])) {

  header('Content-Type: application/json; charset=utf-8');

  $orden = isset($_POST['Orden']) ? trim($_POST['Orden']) : '';
  if ($orden === '') {
    echo json_encode(['success' => 0, 'message' => 'Orden requerida']);
    exit;
  }

  // Traemos Servicios y el número de Recorrido
  $sql = "SELECT 
            Recorridos.Servicios AS Servicios,
            Logistica.Recorrido  AS Recorrido
          FROM Recorridos 
          INNER JOIN Logistica ON Recorridos.Numero = Logistica.Recorrido
          WHERE Logistica.Eliminado = 0
            AND Logistica.NumerodeOrden = ?
          LIMIT 1";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('s', $orden);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
      $servicios = (int)$row['Servicios'];     // Codigo en Productos (int unsigned zerofill)
      $recorrido = (int)$row['Recorrido'];

      $precioServicio = 0.0;

      // Si Servicios != 0 buscamos su precio
      if ($servicios !== 0) {
        $sqlPrecio = "SELECT PrecioVenta FROM Productos WHERE Codigo = ? LIMIT 1";
        if ($st2 = $mysqli->prepare($sqlPrecio)) {
          $st2->bind_param('i', $servicios);   // bind como entero
          $st2->execute();
          $rs2 = $st2->get_result();
          if ($r2 = $rs2->fetch_assoc()) {
            $precioServicio = (float)$r2['PrecioVenta'];
          }
          $st2->close();
        }
      }

      // Suma de Debe en Transacciones para el Recorrido con los filtros pedidos
      $sumaDebe = 0.0;
      $sqlDebe = "SELECT COALESCE(SUM(Debe),0) AS SumaDebe
                  FROM TransClientes
                  WHERE Recorrido = ?
                    AND Eliminado = 0
                    AND Devuelto  = 0
                    AND Haber     = 0
                    AND Entregado = 0";
      if ($st3 = $mysqli->prepare($sqlDebe)) {
        $st3->bind_param('i', $recorrido);
        $st3->execute();
        $rs3 = $st3->get_result();
        if ($r3 = $rs3->fetch_assoc()) {
          $sumaDebe = (float)$r3['SumaDebe'];
        }
        $st3->close();
      }

      $totalRecorrido = $precioServicio + $sumaDebe;

      echo json_encode([
        'success'         => 1,
        'Orden'           => $orden,
        'Recorrido'       => $recorrido,
        'Servicios'       => $servicios,
        'PrecioServicio'  => $precioServicio,
        'SumaDebe'        => $sumaDebe,
        'TotalRecorrido'  => $totalRecorrido,
      ], JSON_UNESCAPED_UNICODE);
    } else {
      echo json_encode(['success' => 0, 'message' => 'Vehículo/recorrido no encontrado']);
    }

    $stmt->close();
  } else {
    echo json_encode(['success' => 0, 'message' => 'No se pudo preparar la consulta']);
  }

  exit;
}


if (isset($_POST['BuscarKmVehiculo'])) {
  header('Content-Type: application/json; charset=utf-8');

  $patente = isset($_POST['Patente']) ? trim($_POST['Patente']) : '';
  if ($patente === '') {
    echo json_encode(['success' => 0, 'message' => 'Patente requerida']);
    exit;
  }

  $sql = "SELECT Kilometros, NivelCombustible, Marca, Modelo, Dominio,Aliados FROM Vehiculos
            WHERE Dominio = ?
            LIMIT 1";
  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('s', $patente);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
      echo json_encode([
        'success'          => 1,
        'Kilometros'       => $row['Kilometros'],
        'NivelCombustible' => $row['NivelCombustible'],
        'Marca'            => $row['Marca'],
        'Modelo'           => $row['Modelo'],
        'Dominio'          => $row['Dominio'],
        'Aliados'          => $row['Aliados'],
      ]);
    } else {
      echo json_encode(['success' => 0, 'message' => 'Vehículo no encontrado']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => 0, 'message' => 'No se pudo preparar la consulta']);
  }
  exit;
}

// Llama a esta función cuando detectes ?Orden=Cargar

function handleOrdenCargar(mysqli $mysqli)
{
  header('Content-Type: application/json; charset=utf-8'); // SIEMPRE JSON
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // ---- Validar parámetros POST necesarios ----
  $Orden     = trim($_POST['orden_t']    ?? '');
  $Patente   = trim($_POST['patente_t']  ?? '');
  $Chofer    = trim($_POST['chofer_t']   ?? '');
  $Recorrido = trim($_POST['recorrido_t'] ?? '');

  if ($Orden === '' || $Patente === '' || $Chofer === '' || $Recorrido === '') {
    echo json_encode(['success' => 0, 'message' => 'Faltan datos obligatorios (orden, patente, chofer, recorrido).']);
    return;
  }

  // ---- Inputs restantes (POST) ----
  $Kilometros         = $_POST['kilometros_t']       ?? '';
  $TarjetaVerde       = (int)($_POST['tarjetaverde_t'] ?? 0);
  $TarjetaAzul        = (int)($_POST['tarjetaazul_t']  ?? 0);
  $ComprobanteSeguro  = (int)($_POST['seguro_t']       ?? 0);
  $Cubiertas          = (int)($_POST['cubiertas_t']    ?? 0);
  $Auxilio            = (int)($_POST['auxilio_t']      ?? 0);
  $Patentes           = (int)($_POST['patentes_t']     ?? 0);
  $LuzPosicion        = (int)($_POST['luzposicion_t']  ?? 0);
  $LuzBaja            = (int)($_POST['luzbaja_t']      ?? 0);
  $LuzAlta            = (int)($_POST['luzalta_t']      ?? 0);
  $LuzFreno           = (int)($_POST['luzfreno_t']     ?? 0);
  $Gnc                = (int)($_POST['gnc_t']          ?? 0);
  $Observaciones      = $_POST['observaciones_t']   ?? '';
  $TarjetaCombustible = $_POST['ypf_t']             ?? '';
  $Acompanante        = $_POST['acompanante_t']     ?? '';
  $Combustible        = $_POST['combustible_t']     ?? '';
  $Estado             = 'Cargada';

  $FechaHoy = date('Y-m-d');
  $HoraHoy  = date('H:i');
  $Usuario  = $_SESSION['Usuario']  ?? 'sistema';
  $Sucursal = $_SESSION['Sucursal'] ?? '';

  // ---- 1) Datos del vehículo por patente ----
  $veh = null;
  if ($stmt = $mysqli->prepare("SELECT Kilometros, Observaciones, NivelCombustible, FechaVencSeguro, Marca, Modelo FROM Vehiculos WHERE Dominio=? LIMIT 1")) {
    $stmt->bind_param('s', $Patente);
    $stmt->execute();
    $veh = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  }
  $FechaVencSeguro = $veh['FechaVencSeguro'] ?? null;
  if ($veh) {
    if ($Kilometros === '')  $Kilometros  = (string)($veh['Kilometros'] ?? '');
    if ($Observaciones === '') $Observaciones = (string)($veh['Observaciones'] ?? '');
    if ($Combustible === '') $Combustible = (string)($veh['NivelCombustible'] ?? '');
  }

  // ---- 2) Chequeo de órdenes activas del vehículo ----
  $cantActivas = 0;
  if ($stmt = $mysqli->prepare("SELECT COUNT(*) FROM Logistica WHERE Patente=? AND Estado IN('Alta','Cargada','Pendiente') AND Eliminado=0")) {
    $stmt->bind_param('s', $Patente);
    $stmt->execute();
    $stmt->bind_result($cantActivas);
    $stmt->fetch();
    $stmt->close();
  }
  // Si querés bloquear la carga si YA hay otra orden activa:
  if ($cantActivas <> 1) {
    echo json_encode(['success' => 0, 'message' => 'El vehículo ya tiene una orden activa (Alta/Cargada/Pendiente).']);
    return;
  }

  // ---- 3) PrecioVenta del recorrido asociado a esta orden ----
  $PrecioVenta = 0.0;
  if ($stmt = $mysqli->prepare("
    SELECT p.PrecioVenta
    FROM Recorridos r
    INNER JOIN Logistica l ON r.Numero = l.Recorrido
    INNER JOIN Productos p ON r.CodigoProductos = p.Codigo
    WHERE l.NumerodeOrden = ?
    LIMIT 1")) {
    $stmt->bind_param('s', $Orden);
    $stmt->execute();
    $stmt->bind_result($PrecioVenta);
    $stmt->fetch();
    $stmt->close();
  }

  // ---- 4) Datos del chofer ----
  $FechaVencRegistro = null;
  $IdUsuarioChofer   = null;
  if ($stmt = $mysqli->prepare("SELECT VencimientoLicencia, Usuario FROM Empleados WHERE NombreCompleto=? LIMIT 1")) {
    $stmt->bind_param('s', $Chofer);
    $stmt->execute();
    $stmt->bind_result($FechaVencRegistro, $IdUsuarioChofer);
    $stmt->fetch();
    $stmt->close();
  }

  // ---- 5) Transacción ----
  $mysqli->begin_transaction();

  try {



    $sql = "UPDATE Logistica SET
  Patente=?, Kilometros=?, NombreChofer=?, idUsuarioChofer=?, NombreChofer2=?,
  Recorrido=?, TarjetaVerde=?, ComprobanteSeguro=?, Cubiertas=?, Auxilio=?,
  ChapasPatentes=?, LucesPosicion=?, LucesBajas=?, LucesAltas=?, LucesFreno=?,
  GNCFuncionando=?, TarjetaCombustible=?, Observaciones=?, Estado=?,
  TotalFacturado=?, CombustibleSalida=?
  WHERE NumerodeOrden=? AND Eliminado=0 LIMIT 1";

    if ($stmt = $mysqli->prepare($sql)) {
      $stmt->bind_param(
        'sisisiiiiiiiiiiiissiii',
        $Patente,
        $Kilometros,
        $Chofer,
        $IdUsuarioChofer,
        $Acompanante,
        $Recorrido,
        $TarjetaVerde,
        $ComprobanteSeguro,
        $Cubiertas,
        $Auxilio,
        $Patentes,
        $LuzPosicion,
        $LuzBaja,
        $LuzAlta,
        $LuzFreno,
        $Gnc,
        $TarjetaCombustible,
        $Observaciones,
        $Estado,
        $PrecioVenta,       // d
        $Combustible,       // s
        $Orden              // s (WHERE)
      );
      $stmt->execute();
      if ($stmt->errno) throw new Exception('Error al actualizar Logistica: ' . $stmt->error);
      $stmt->close();
    } else {
      throw new Exception('No se pudo preparar UPDATE Logistica');
    }

    // 5.b) UPDATE Vehiculos (estado + obs)
    if ($stmt = $mysqli->prepare("UPDATE Vehiculos SET Estado=?, Observaciones=? WHERE Dominio=? LIMIT 1")) {
      $estadoVeh = "Alta en recorrido $Recorrido";
      $stmt->bind_param('sss', $estadoVeh, $Observaciones, $Patente);
      $stmt->execute();
      if ($stmt->errno) throw new Exception('Error al actualizar Vehiculos');
      $stmt->close();
    }

    // 5.c) HojaDeRuta: numerar hojas abiertas del recorrido
    if ($stmt = $mysqli->prepare("UPDATE HojaDeRuta SET NumerodeOrden=? WHERE Recorrido=? AND Estado='Abierto' AND Eliminado=0")) {
      $stmt->bind_param('ss', $Orden, $Recorrido);
      $stmt->execute();
      if ($stmt->errno) throw new Exception('Error al actualizar HojaDeRuta');
      $stmt->close();
    }

    // 5.d) Fecha de orden (para TransClientes)
    $FechaDeOrden = $FechaHoy;
    if ($stmt = $mysqli->prepare("SELECT Fecha FROM Logistica WHERE NumerodeOrden=? AND Eliminado=0 LIMIT 1")) {
      $stmt->bind_param('s', $Orden);
      $stmt->execute();
      $stmt->bind_result($FechaDeOrdenDB);
      if ($stmt->fetch()) $FechaDeOrden = $FechaDeOrdenDB ?: $FechaHoy;
      $stmt->close();
    }

    // 5.e) UPDATE TransClientes pendientes del recorrido
    if ($stmt = $mysqli->prepare("
      UPDATE TransClientes
      SET Transportista=?, FechaEntrega=?, NumerodeOrden=?
      WHERE Recorrido=? AND Entregado='0' AND Eliminado='0' AND Devuelto='0' AND Haber='0'")) {
      $stmt->bind_param('ssss', $Chofer, $FechaDeOrden, $Orden, $Recorrido);
      $stmt->execute();
      if ($stmt->errno) throw new Exception('Error al actualizar TransClientes');
      $stmt->close();
    }

    // 5.f) INSERT Seguimiento (evita duplicados)
    if ($stmt = $mysqli->prepare("
      SELECT id, CodigoSeguimiento, DomicilioDestino, LocalidadDestino, Retirado
      FROM TransClientes
      WHERE Recorrido=? AND Entregado='0' AND Eliminado='0' AND Devuelto='0' AND Haber='0'")) {
      $stmt->bind_param('s', $Recorrido);
      $stmt->execute();
      $rs = $stmt->get_result();

      $stmtBuscaSeg = $mysqli->prepare("SELECT id FROM Seguimiento WHERE CodigoSeguimiento=? AND Estado=? AND Observaciones=? LIMIT 1");
      $stmtInsSeg   = $mysqli->prepare("
        INSERT INTO Seguimiento
          (Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Destino,Recorrido,NumerodeOrden)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)");

      while ($row = $rs->fetch_assoc()) {
        $CodigoSeguimiento = $row['CodigoSeguimiento'];
        $Localizacion      = trim(($row['DomicilioDestino'] ?? '') . ', ' . ($row['LocalidadDestino'] ?? ''));
        $EntregadoSeg      = 0;
        $EstadoSeg         = ((int)$row['Retirado'] === 0) ? 'A Retirar' : 'En Transito';
        $ObsSeg            = 'Cargado en la Hoja de Ruta';

        $stmtBuscaSeg->bind_param('sss', $CodigoSeguimiento, $EstadoSeg, $ObsSeg);
        $stmtBuscaSeg->execute();
        $stmtBuscaSeg->store_result();
        if ($stmtBuscaSeg->num_rows === 0) {
          $stmtInsSeg->bind_param(
            'ssssssissss',
            $FechaHoy,
            $HoraHoy,
            $Usuario,
            $Sucursal,
            $CodigoSeguimiento,
            $ObsSeg,
            $EntregadoSeg,
            $EstadoSeg,
            $Localizacion,
            $Recorrido,
            $Orden
          );
          $stmtInsSeg->execute();
          if ($stmtInsSeg->errno) throw new Exception('Error al insertar Seguimiento');
        }
      }
      $stmtBuscaSeg->close();
      $stmtInsSeg->close();
      $stmt->close();
    }

    // 5.g) (Opcional) mail
    // ... (si lo dejás, no imprime nada en salida)

    $mysqli->commit();
    echo json_encode(['success' => 1, 'message' => 'Orden cargada correctamente.']);
    return;
  } catch (Exception $e) {
    $mysqli->rollback();
    error_log("Orden Cargar ERROR: " . $e->getMessage());
    echo json_encode(['success' => 0, 'message' => 'Error al cargar la orden: ' . $e->getMessage()]);
    return;
  }
}


if (isset($_POST['Logistica'])) {

  $Fecha = explode(' - ', $_POST['Fechas'], 2);

  $FechaInicio = explode('/', $Fecha[0], 3);
  $FechaI = $FechaInicio[2] . '-' . $FechaInicio[0] . '-' . $FechaInicio[1];

  $FechaFinal = explode('/', $Fecha[1], 3);
  $FechaF = $FechaFinal[2] . '-' . $FechaFinal[0] . '-' . $FechaFinal[1];

  $sql = "SELECT r.Nombre,l.NumerodeOrden,l.Fecha,l.Hora,l.HoraRetorno,l.Kilometros,l.KilometrosRegreso,l.KilometrosRecorridos,l.Recorrido,l.Estado,
    l.NombreChofer,l.NombreChofer2,l.Facturado,l.TotalFacturado,l.TotalRecorrido,v.Marca,v.Modelo,v.Dominio 
    FROM Logistica as l 
    INNER JOIN Vehiculos as v ON l.Patente=v.Dominio
    INNER JOIN Recorridos as r ON l.Recorrido=r.Numero
    WHERE l.Eliminado=0 AND l.Fecha>='$FechaI' AND l.Fecha<='$FechaF'";

  $Resultado = $mysqli->query($sql);
  $rows = array();

  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows));
}

if (isset($_POST['BuscoRecorridos'])) {
  // Obtenemos el próximo ID
  $sqlMaxId = "SELECT MAX(id) as max_id FROM Logistica";
  $resMax = $mysqli->query($sqlMaxId);
  $prox_id = 1;

  if ($resMax && $rowMax = $resMax->fetch_assoc()) {
    $prox_id = intval($rowMax['max_id']) + 1;
  }
  //Obtenemos el proximo Recorrido
  $SQL = $mysqli->query("SELECT MAX(Numero) as Numero FROM Recorridos");
  $Recorrido_sql = $SQL->fetch_array();
  $prox_rec = intval($Recorrido_sql['Numero'] + 1);
  // $Recorrido_nombre = $Fecha . " " . $Hora . " - " . $Chofer;

  $recorridos = [];
  $sql = "SELECT Numero, Nombre FROM Recorridos WHERE Activo = 1 AND Fijo=1 ORDER BY Numero ASC";
  $res = $mysqli->query($sql);

  if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
      $recorridos[] = $row;
    }

    echo json_encode([
      'success' => true,
      'recorridos' => $recorridos,
      'proximo_id_logistica' => $prox_id,
      'proximo_recorrido' => $prox_rec,
      'usuario' => $_SESSION['Usuario']
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'mensaje' => 'No se encontraron recorridos',
      'proximo_id_logistica' => $prox_id, // Lo devolvemos igual
      'proximo_recorrido' => $prox_rec,
      'usuario' => $_SESSION['Usuario']
    ]);
  }
}

if (isset($_POST['BuscoEmpleados'])) {

  $empleados = [];
  $sql = "SELECT id,NombreCompleto,Aliados FROM Empleados WHERE Puesto='Transportista' AND Inactivo=0  ORDER BY NombreCompleto ASC";
  $res = $mysqli->query($sql);


  if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
      $empleados[] = $row;
    }

    echo json_encode([
      'success' => true,
      'empleados' => $empleados
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'mensaje' => 'No se encontraron empleados'
    ]);
  }
}



if (isset($_POST['BuscoVehiculos'])) {

  $vechiculos = [];
  $sql = "SELECT * FROM Vehiculos WHERE VehiculoOperativo=1";
  $res = $mysqli->query($sql);


  if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
      $vehiculos[] = $row;
    }

    echo json_encode([
      'success' => true,
      'vehiculos' => $vehiculos
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'mensaje' => 'No se encontraron vehiculos'
    ]);
  }
}

if (isset($_POST['alta_orden'])) {

  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  $Fecha = $_POST['fecha'];
  $Hora = date("H:i:s", strtotime($_POST['hora']));
  $Controla = $_POST['controla'];
  $Vehiculo = $_POST['vehiculo'];
  $Chofer = $_POST['chofer'];
  $Acompanante = $_POST['acomp'];
  $Recorrido = $_POST['recorrido'];
  $PrecioVenta = 0;
  $TotalRecorrido = 0;
  $Cliente = "";
  $Estado = 'Alta';
  $Cliente = "0";
  $Kilometros = '0';
  $Observaciones = '';
  $CombustibleSalida = '';
  $Total_transclientes = 0;
  $Fijo = isset($_POST['fijo']) ? intval($_POST['fijo']) : 0;

  //BUSCO EN LA TABLA RECORRIDOS PARA VER SI EXISTE EL RECORRIDO
  $sql = "SELECT id FROM Recorridos WHERE Numero='$Recorrido'";
  $res = $mysqli->query($sql);

  if ($res && $res->num_rows > 0) {

    $sql_recorridos = $mysqli->query("SELECT Cliente,Nombre,CodigoProductos FROM Recorridos WHERE Numero='$Recorrido'");
    if ($sql_recorridos->num_rows > 0) {
      $datos_recorridos = $sql_recorridos->fetch_array();
      $Recorrido_nombre = $datos_recorridos['Nombre'];

      if ($datos_recorridos['Cliente'] <> '') {
        $Cliente = $datos_recorridos['Cliente'];
      }
      //BUSCO EL TOTAL EN PRODUCTOS
      if (!empty($datos_recorridos['CodigoProductos']) && $datos_recorridos['CodigoProductos'] != '0000000000') {
        $codigoProducto = mysqli_real_escape_string($mysqli, $datos_recorridos['CodigoProductos']);
        $sql_productos = $mysqli->query("SELECT PrecioVenta FROM Productos WHERE Codigo = '$codigoProducto'");

        if ($sql_productos && $sql_productos->num_rows > 0) {
          $dato_productos = $sql_productos->fetch_array();
          $PrecioVenta = $dato_productos['PrecioVenta'] ?? 0;
        }
      }
    }
    //BUSCO SI HAY VALORES EN TRANSCLIENTES PARA ESTE RECORRIDO
    $sql_transclientes = $mysqli->query("SELECT SUM(Debe)as Total FROM TransClientes WHERE Recorrido='$Recorrido' AND Eliminado=0 AND Entregado=0 AND Devuelto=0 AND Haber=0");
    $datos_transclientes = $sql_transclientes->fetch_array();
    $Total_transclientes = $datos_transclientes['Total'] ?? 0;
  } else {


    $SQL = $mysqli->query("SELECT MAX(Numero) as Numero FROM Recorridos");
    $Recorrido_sql = $SQL->fetch_array();
    $Recorrido = trim($Recorrido_sql['Numero'] + 1);
    $Recorrido_nombre = $Fecha . " " . $Hora . " - " . $Chofer;

    $SQL = "INSERT INTO Recorridos (Numero, Nombre, Kilometros, Peajes, Activo,Tarifa_externos,Fijo,Servicios) VALUES ('{$Recorrido}', '{$Recorrido_nombre}', '500', '0', '1','0','{$Fijo}','0')";


    if ($mysqli->query($SQL)) {

      //CALCULO EL TOTAL DEL RECORRIDO SI EXISITA Y TENIA VALOR ASIGNADO Y SI HAY REGISTROS CARGADOS PARA ESTE RECORRIDO VA ESO
      // SINO EL TOTAL DEL RECORRIDO ES 0
      $TotalRecorrido = $Total_transclientes + $PrecioVenta;

      // 🚗 Vehículo
      $sql_vehiculo = $mysqli->query("SELECT * FROM Vehiculos WHERE id='$Vehiculo'");
      $datos_vehiculo = $sql_vehiculo->fetch_array();
      $Patente = $datos_vehiculo['Dominio'];

      // ⛽ Control de uso del vehículo
      $sqlcontrolo = $mysqli->query("SELECT id FROM Logistica WHERE Patente='$Patente' AND Eliminado=0 AND Estado IN ('Alta','Cargada')");

      if ($sqlcontrolo->num_rows != 0) {
        $Kilometros = "0";
        $Observaciones = "";
        $CombustibleSalida = "";
        $Estado = 'Pendiente';
      } else {
        $Kilometros = $datos_vehiculo['Kilometros'];
        $Observaciones = $datos_vehiculo['Observaciones'];
        $CombustibleSalida = $datos_vehiculo['NivelCombustible'];
      }

      $FechaVencSeguro = $datos_vehiculo['FechaVencSeguro'];

      // 👤 Chofer
      $sql_chofer = $mysqli->query("SELECT VencimientoLicencia, Usuario, NombreCompleto FROM Empleados WHERE id='$Chofer'");
      $datos_chofer = $sql_chofer->fetch_array();
      $FechaVencRegistro = $datos_chofer['VencimientoLicencia'];
      $IdUsuarioChofer = $datos_chofer['Usuario'];
      $NombreChofer = $datos_chofer['NombreCompleto'];

      // 🆕 Busco nuevo número de orden
      $sqlMax = $mysqli->query("SELECT MAX(NumerodeOrden) AS maxOrden FROM Logistica");
      $rowMax = $sqlMax->fetch_array();
      $Numero = $rowMax['maxOrden'] + 1;

      $sqlInsert = "INSERT INTO Logistica (
      NumerodeOrden,
      Fecha,
      Hora,
      Controla,
      Patente,
      Kilometros,
      NombreChofer,
      NombreChofer2,
      Recorrido,
      FechaVencRegistro,
      FechaVencSeguro,
      Observaciones,
      Estado,
      CombustibleSalida,
      idUsuarioChofer,
      Cliente,
      TotalRecorrido
    ) VALUES (
      '{$Numero}',
      '{$Fecha}',
      '{$Hora}',
      '{$Controla}',
      '{$Patente}',
      '{$Kilometros}',
      '{$NombreChofer}',
      '{$Acompanante}',
      '{$Recorrido}',
      '{$FechaVencRegistro}',
      '{$FechaVencSeguro}',
      '{$Observaciones}',
      '{$Estado}',
      '{$CombustibleSalida}',
      '{$IdUsuarioChofer}',
      '{$Cliente}',
      '{$TotalRecorrido}')";

      if ($mysqli->query($sqlInsert)) {
        echo json_encode(['success' => true, 'numero' => $Numero]);
      } else {
        echo json_encode(['success' => false, 'mensaje' => $mysqli->error]);
      }

      // FIN DE INSERCIÓN DE NUEVO RECORRIDO 
    } else {

      echo json_encode(array('success' => 0, 'error' => $mysqli->error));

      exit;
    }
  }
}

// CERRAR ORDENES

if (isset($_POST['CerrarOrden'])) {
  $numero = $_POST['numero_orden'];
  $sql = "SELECT * FROM Logistica WHERE NumerodeOrden = '$numero' LIMIT 1";
  $res = $mysqli->query($sql);

  if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();

    echo json_encode([
      "success" => 1,
      "data" => $row
    ]);
  } else {
    echo json_encode([
      "success" => 0,
      "msg" => "No se encontró la orden N° $numero"
    ]);
  }
  exit;
}

//CARGAR ORDENES

if (isset($_POST['Orden']) && $_POST['Orden'] === 'Cargar') {
  handleOrdenCargar($mysqli);
  exit;
}
