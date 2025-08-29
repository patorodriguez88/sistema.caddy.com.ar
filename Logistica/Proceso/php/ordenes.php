<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

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

  $recorridos = [];
  $sql = "SELECT Numero, Nombre FROM Recorridos WHERE Activo = 1 AND Fijo=1 ORDER BY Numero ASC";
  $res = $mysqli->query($sql);

  // Obtenemos el próximo ID
  $sqlMaxId = "SELECT MAX(id) as max_id FROM Logistica";
  $resMax = $mysqli->query($sqlMaxId);
  $prox_id = 1;

  if ($resMax && $rowMax = $resMax->fetch_assoc()) {
    $prox_id = intval($rowMax['max_id']) + 1;
  }

  if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
      $recorridos[] = $row;
    }

    echo json_encode([
      'success' => true,
      'recorridos' => $recorridos,
      'proximo_id_logistica' => $prox_id,
      'usuario' => $_SESSION['Usuario']
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'mensaje' => 'No se encontraron recorridos',
      'proximo_id_logistica' => $prox_id // Lo devolvemos igual
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

  if (empty($Recorrido)) {

    $SQL = $mysqli->query("SELECT MAX(Numero) as Numero FROM Recorridos");
    $Recorrido_sql = $SQL->fetch_array();
    $Recorrido = trim($Recorrido_sql['Numero'] + 1);
    $Recorrido_nombre = $Fecha . " " . $Hora . " - " . $Chofer;

    $SQL = "INSERT INTO Recorridos (Numero, Nombre, Kilometros, Peajes, Activo,Tarifa_externos,Fijo,Servicios) VALUES ('{$Recorrido}', '{$Recorrido_nombre}', '500', '0', '1','0','0','0')";

    if ($mysqli->query($SQL)) {
    } else {

      echo json_encode(array('success' => 0, 'error' => $mysqli->error));

      exit;
    }
  } else {

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
  }
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
}

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
