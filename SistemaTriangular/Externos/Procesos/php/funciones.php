<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
// Polígono en formato [lng, lat]
$poligono_circunvalacion = [
    [-64.26536754051685, -31.430159003374783],
    [-64.25420702375214, -31.438888541341164],
    [-64.25231512716908, -31.44955526648878],
    [-64.25099074065369, -31.4576373242879],
    [-64.24189923009112, -31.46103417543032],
    [-64.22172580992526, -31.463547379190047],
    [-64.18536076677405, -31.468038300106052],
    [-64.16657389576024, -31.468922133507917],
    [-64.15724179183128, -31.466326243895686],
    [-64.14309249436198, -31.45715924186611],
    [-64.12630309515009, -31.444852729063633],
    [-64.11968737724618, -31.439035824656308],
    [-64.10955943955834, -31.42898559202729],
    [-64.1093251200819, -31.42105257066865],
    [-64.11733420172807, -31.4131132920827],
    [-64.12207293049353, -31.408697815096495],
    [-64.12621943162702, -31.40234064726576],
    [-64.12748611807277, -31.39793155966617],
    [-64.12691493412666, -31.392298448443476],
    [-64.12610128014566, -31.38859864140811],
    [-64.12776204047354, -31.371139612526584],
    [-64.13045057789964, -31.36690798285538],
    [-64.13423260420004, -31.36457487447929],
    [-64.14496557575441, -31.362705899802577],
    [-64.15818309471751, -31.360578087072767],
    [-64.17448092583587, -31.35795980757492],
    [-64.18812749370663, -31.354837994302713],
    [-64.19560878351658, -31.353214085464067],
    [-64.20646286718211, -31.35459708741459],
    [-64.21643553417596, -31.354600743791686],
    [-64.22639506838716, -31.353236600569147],
    [-64.2309358178963, -31.35299056986898],
    [-64.23578152440079, -31.354363213540132],
    [-64.2397531783821, -31.357486463549364],
    [-64.24245696255933, -31.361710135539532],
    [-64.24672674799868, -31.363835410229704],
    [-64.25025940791573, -31.367091739051453],
    [-64.25241124256341, -31.37287693962186],
    [-64.25528996119641, -31.376902282433534],
    [-64.26009948653932, -31.3826678002033],
    [-64.2642006170063, -31.389547525799017],
    [-64.26802896287634, -31.397652896853394],
    [-64.26968272614346, -31.402207453767552],
    [-64.269640177771, -31.41670850716344],
    [-64.27251246659266, -31.420778596302135],
    [-64.27394912559002, -31.422955254971527],
    [-64.27428283868657, -31.425229052672805],
    [-64.27284237491062, -31.42807216826207],
    [-64.2698532213004, -31.42959217315355],
    [-64.26730398600616, -31.429878917093404],
    [-64.26536754051685, -31.430159003374783]
];

// Convertimos a [lat, lng]
$poligono = array_map(function ($p) {
    return [$p[1], $p[0]];
}, $poligono_circunvalacion);

// Aseguramos que esté cerrado
if ($poligono[0] !== end($poligono)) {
    $poligono[] = $poligono[0];
}

// Función ray casting
function puntoDentroDelAnillo($lat, $lng, $poligono)
{
    $dentro = false;
    $j = count($poligono) - 1;

    for ($i = 0; $i < count($poligono); $i++) {
        $lat_i = $poligono[$i][0];
        $lng_i = $poligono[$i][1];
        $lat_j = $poligono[$j][0];
        $lng_j = $poligono[$j][1];

        if ((($lat_i > $lat) != ($lat_j > $lat)) &&
            ($lng < ($lng_j - $lng_i) * ($lat - $lat_i) / (($lat_j - $lat_i) + 1e-12) + $lng_i)
        ) {
            $dentro = !$dentro;
        }
        $j = $i;
    }

    return $dentro;
}




function obtenerDistanciaORS($lat1, $lon1, $lat2, $lon2, $apiKey)
{
    $url = "https://api.openrouteservice.org/v2/directions/driving-car";

    $body = json_encode([
        "coordinates" => [[$lon1, $lat1], [$lon2, $lat2]],
    ]);

    $headers = [
        "Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8",
        "Content-Type: application/json",
        "Authorization: $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return "Error: " . curl_error($ch);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['routes'][0]['summary'])) {
        $distancia_metros = $data['routes'][0]['summary']['distance'];
        $duracion_segundos = $data['routes'][0]['summary']['duration'];

        return [
            "km" => round($distancia_metros / 1000, 2),
            "tiempo" => gmdate("H:i:s", $duracion_segundos)
        ];
    } else {
        return "Error: no se pudo obtener la ruta.";
    }
}

if (isset($_POST['Envios'])) {

    if ($_POST['Filtro'] == 'inactivo') {
        $SQL = $mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=1 AND Vehiculos.id_usuario<>0");
    } else if ($_POST['Filtro'] == 'activo') {
        $SQL = $mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=0 AND Vehiculos.id_usuario<>0");
    } else {

        $SQL = $mysqli->query("SELECT Empleados.id,Empleados.NombreCompleto,Empleados.Dni,Empleados.Telefono,Empleados.Puesto,Empleados.FechaIngreso,Empleados.VencimientoLicencia,Empleados.Inactivo,Empleados.Observaciones, Vehiculos.Marca,Vehiculos.Modelo,Vehiculos.Dominio,Empleados.Usuario FROM `Empleados` 
      INNER JOIN Vehiculos ON Empleados.Usuario =Vehiculos.id_usuario WHERE Empleados.Aliados=1 AND Empleados.Inactivo=0 AND Vehiculos.id_usuario<>0");
    }

    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        //ACTUALIZO LOS CLIENTES
        if ($DATOS_CLIENTES['VencimientoLicencia'] < date('Y-m-d')) {

            $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        } else {

            $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        }

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}
//VER EXTERNO
if (isset($_POST['VerExterno'])) {

    // $SQL=$mysqli->query("SELECT * FROM `Empleados` WHERE id='".$_POST['id']."'");
    $SQL = $mysqli->query("SELECT Empleados.*,usuarios.Usuario,usuarios.PASSWORD FROM `Empleados` INNER JOIN usuarios ON Empleados.Usuario=usuarios.id WHERE Empleados.id='" . $_POST['id'] . "'");
    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}

//MODIFICAR EXTERNO
if (isset($_POST['ModificarExterno'])) {
    $VencimientoLic = explode("/", $_POST['licencia'], 3);
    $FechaVencimientoLicencia = $VencimientoLic[2] . '-' . $VencimientoLic[0] . '-' . $VencimientoLic[1];

    $Nacimiento = explode("/", $_POST['nac'], 3);
    $FechaNacimiento = $Nacimiento[2] . '-' . $Nacimiento[0] . '-' . $Nacimiento[1];

    $Ingreso = explode("/", $_POST['ing'], 3);
    $FechaIngreso = $Ingreso[2] . '-' . $Ingreso[0] . '-' . $Ingreso[1];

    $SQL = "UPDATE `Empleados` SET `NombreCompleto`='" . $_POST['nombre'] . "',`Domicilio`='" . $_POST['domicilio'] . "',`Localidad`='" . $_POST['city'] . "',
    `Provincia`='" . $_POST['state'] . "',`CodigoPostal`='" . $_POST['codigopostal'] . "',`Telefono`='" . $_POST['telefono'] . "',`FechaNacimiento`='" . $FechaNacimiento . "',
    `FechaIngreso`='" . $FechaIngreso . "',`Dni`='" . $_POST['dni'] . "',`VencimientoLicencia`='" . $FechaVencimientoLicencia . "',
    `Observaciones`='" . $_POST['obs'] . "',`GrupoSanguineo`='" . $_POST['gruposanguineo'] . "',`TelefonoEmergencia`='" . $_POST['phone_emergency'] . "'
     WHERE id='" . $_POST['id_externo'] . "' LIMIT 1";

    if ($mysqli->query($SQL)) {

        echo json_encode(array('success' => 1, 'Fecha' => $FechaVencimientoLicencia));
    } else {

        echo json_encode(array('success' => 0));
    }
}

//AGREGAR EXTERNO

if (isset($_POST['Agregar_externo'])) {

    $FechaHoy = date('Y-m-d');
    $Usuario  = $_POST['nombre'];

    // 1) Insert usuario
    $SQL_USUARIO = "INSERT INTO `usuarios`
        (`Nombre`, `PASSWORD`, `NIVEL`, `ACTIVO`, `Direccion`, `Localidad`, `Ciudad`, `Telefono`,
         `Observaciones`, `Usuario`, `FechaPassword`, `Estado`)
        VALUES (
         '{$_POST['nombre']}','{$_POST['dni']}','3','1','{$_POST['domicilio']}','{$_POST['city']}','{$_POST['state']}',
         '{$_POST['telefono']}','{$_POST['obs']}','{$Usuario}','{$FechaHoy}','Activo'
        )";
    $mysqli->query($SQL_USUARIO);
    $id_usuario = $mysqli->insert_id;

    // 2) Insert empleado
    $SQL = "INSERT INTO `Empleados`
        (`NombreCompleto`, `Domicilio`, `Localidad`, `Provincia`, `CodigoPostal`, `Telefono`,
         `FechaNacimiento`, `FechaIngreso`, `Dni`, `VencimientoLicencia`, `Puesto`, `Observaciones`,
         `CuentaAnticipos`, `GrupoSanguineo`, `TelefonoEmergencia`, `Inactivo`, `Aliados`, `Usuario`)
        VALUES (
         '{$_POST['nombre']}','{$_POST['domicilio']}','{$_POST['city']}','{$_POST['state']}','{$_POST['codigopostal']}',
         '{$_POST['telefono']}','{$_POST['nac']}','{$_POST['ing']}','{$_POST['dni']}','{$_POST['lic']}',
         'Transportista','{$_POST['obs']}','112500','{$_POST['gruposanguineo']}','{$_POST['phone_emergency']}',
         '0','1','{$id_usuario}'
        )";

    $vehiculo      = 0;
    $SQL_VEHICULO  = null; // <- IMPORTANTE: inicializar

    // 3) Preparar (solo si hay datos de vehículo)
    if (!empty($_POST['marca']) && !empty($_POST['modelo']) && !empty($_POST['dominio'])) {
        $SQL_VEHICULO = "INSERT INTO `Vehiculos`
            (`Marca`, `Modelo`, `Dominio`, `FechaVencSeguro`, `Kilometros`, `Color`, `Seguro`,
             `NumeroPoliza`, `Motor`, `Chasis`, `Ano`, `Observaciones`, `Activo`, `ObleaITV`,
             `FechaVencITV`, `Estado`, `CapacidadTotalCarga`, `PesoTotalCarga`, `VehiculoOperativo`,
             `Aliados`, `id_usuario`)
            VALUES (
             '{$_POST['marca']}','{$_POST['modelo']}','{$_POST['dominio']}','{$_POST['seguro_vencimiento']}',
             '{$_POST['km']}','{$_POST['color']}','{$_POST['seguro']}','{$_POST['poliza']}','{$_POST['motor']}',
             '{$_POST['chasis']}','{$_POST['ano']}','{$_POST['vehiculo_obs']}','Si','{$_POST['itv_oblea']}',
             '{$_POST['itv_vencimiento']}','Disponible','{$_POST['volumen']}','{$_POST['peso']}','1','1','{$id_usuario}'
            )";
    }

    if ($mysqli->query($SQL)) {
        // actualizar usuario
        $Usuario = strtok($_POST['nombre'], " ") . "_" . $id_usuario;
        $mysqli->query("UPDATE usuarios SET Usuario='$Usuario' WHERE id='$id_usuario' AND PASSWORD='{$_POST['dni']}' LIMIT 1");

        // 4) Ejecutar vehículo SOLO si hay query
        if (is_string($SQL_VEHICULO) && $SQL_VEHICULO !== '') {
            if ($mysqli->query($SQL_VEHICULO)) {
                $vehiculo = 1;
            }
        }

        $aliado = 1;
    } else {
        $aliado = 0;
    }

    echo json_encode(['success' => $aliado, 'vehiculo' => $vehiculo, 'user_id' => $id_usuario]);
}

if (isset($_POST['Desempeno'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);



    $SQL_EXTERNOS = $mysqli->query("SELECT Usuario FROM usuarios WHERE id='" . $_POST['id'] . "' ");
    $DATO_EXTERNOS = $SQL_EXTERNOS->fetch_array(MYSQLI_ASSOC);
    $NombreUsuario = $DATO_EXTERNOS['Usuario'];
    $SQL = $mysqli->query("SELECT 
    Logistica.Rendicion,
    Logistica.Costo_rendicion,
    Recorridos.Nombre,
    Logistica.Fecha,
    Logistica.Recorrido,
    Logistica.NumerodeOrden,
    COUNT(Seguimiento.id)as Total 
    FROM `Logistica` 
    JOIN Seguimiento ON Seguimiento.NumerodeOrden=Logistica.NumerodeOrden  
    JOIN Recorridos ON Recorridos.Numero=Logistica.Recorrido
    WHERE Logistica.Fecha>='" . $_POST['Desde'] . "' AND Logistica.Fecha<='" . $_POST['Hasta'] . "' 
    AND Logistica.Eliminado=0 and Logistica.idUsuarioChofer='" . $_POST['id'] . "' 
    AND Seguimiento.Usuario='" . $NombreUsuario . "' 
    AND Seguimiento.Eliminado=0 GROUP BY NumerodeOrden");

    $ROWS = array();

    while ($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)) {

        //ACTUALIZO LOS CLIENTES
        if ($DATOS_CLIENTES['VencimientoLicencia'] < date('Y-m-d')) {

            $mysqli->query("UPDATE Empleados SET Inactivo=1 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        } else {

            $mysqli->query("UPDATE Empleados SET Inactivo=0 WHERE id='$DATOS_CLIENTES[id]' LIMIT 1");
        }

        $ROWS[] = $DATOS_CLIENTES;
    }

    echo json_encode(array('data' => $ROWS));
}

// -------------------- LÓGICA PRINCIPAL --------------------

if (isset($_POST['Reporte'])) {
    if ($_POST['controlado'] == 0) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        // include_once "../../Conexion/Conexioni.php";

        $SQL_EXTERNOS = $mysqli->query("SELECT Usuario FROM usuarios WHERE id='" . $_POST['id'] . "' ");
        $DATO_EXTERNOS = $SQL_EXTERNOS->fetch_array(MYSQLI_ASSOC);
        $NombreUsuario = $DATO_EXTERNOS['Usuario'];

        $SQL = $mysqli->query("SELECT 
        Seg.Entregado,
        ts.RazonSocial,
        ts.ingBrutosOrigen,
        ts.Recorrido,
        ts.idPago,
        ts.ClienteDestino,
        ts.idClienteDestino,
        Seg.Fecha,
        ts.DomicilioDestino,
        ts.LocalidadDestino,
        ts.CodigoSeguimiento,
        Seg.Estado,
        CONCAT(Seg.Usuario, '_', Seg.Fecha, 'T', Seg.Hora) AS infoABM,
        ts.Kilometros,
        ts.Debe,
        cl.Latitud,
        cl.Longitud
        FROM Seguimiento AS Seg
        JOIN TransClientes AS ts ON Seg.CodigoSeguimiento = ts.CodigoSeguimiento
        JOIN Clientes AS cl ON ts.idClienteDestino = cl.id
        WHERE 
                Seg.Eliminado = 0 
        AND ts.Eliminado = 0 
        AND Seg.NumerodeOrden = " . $_POST['NOrden'] . " AND Seg.Visitas<>0
        AND Seg.Estado <>'Retirado del Cliente'
        AND ts.idPago = 0
        AND Seg.Usuario = '" . $NombreUsuario . "'");

        $ROWS = [];
        $latOrigen = -31.445003929354897;
        $lngOrigen = -64.17790870319338;
        $apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjQ2N2MyOGNjMTI0ZjQxY2VhMTQ0NzZkYzU2NWUzYThlIiwiaCI6Im11cm11cjY0In0=';

        // Tarifa externas
        $tarifas = [];
        $tarifas_nombres = [];
        $q = $mysqli->query("SELECT id, Precio, Nombre FROM Externos_tarifas");

        while ($r = $q->fetch_assoc()) {
            $tarifas[$r['id']] = $r['Precio'];
            $tarifas_nombres[$r['id']] = $r['Nombre'];
        }

        // Preconteo para promedios
        $conteoServicios = [];
        $tempRows = [];

        while ($row = $SQL->fetch_array(MYSQLI_ASSOC)) {
            $key = $row['Recorrido'] . '_' . $row['ingBrutosOrigen'];
            if (floatval($row['Debe']) == 0 && intval($row['idClienteDestino']) != 18587) {
                if (!isset($conteoServicios[$key])) {
                    $conteoServicios[$key] = 0;
                }
                $conteoServicios[$key]++;
            }
            $tempRows[] = $row;
        }

        // Procesar filas
        foreach ($tempRows as $row) {
            $lat2 = $row['Latitud'];
            $lng2 = $row['Longitud'];
            $km = floatval($row['Kilometros']);
            $dentro = puntoDentroDelAnillo($lat2, $lng2, $poligono);

            if ($km == 0 && $lat2 && $lng2) {
                $distancia = obtenerDistanciaORS($latOrigen, $lngOrigen, $lat2, $lng2, $apiKey);
                if (is_array($distancia)) {
                    $km = $distancia['km'];
                    $stmt = $mysqli->prepare("UPDATE TransClientes SET Kilometros = ? WHERE CodigoSeguimiento = ? LIMIT 1");
                    $stmt->bind_param("ds", $km, $row['CodigoSeguimiento']);
                    $stmt->execute();
                }
            }

            // Si es Colecta (Wepoint)
            if ($row['idClienteDestino'] == 18587) {
                $row['TarifaID'] = 6;
                $row['Precio'] = isset($tarifas[6]) ? $tarifas[6] : 0;
                $row['NombreTarifa'] = isset($tarifas_nombres[6]) ? $tarifas_nombres[6] : "Colecta";
                $row['Kilometros'] = $km;
                $row['DentroAnillo'] = $dentro;
                $row['CobrarEnvio'] = 0;
                $row['Total'] = round(floatval($row['Precio']) + floatval($row['CobrarEnvio']), 2);
                $ROWS[] = $row;
                continue;
            }

            // Buscar tarifa especial desde recorrido
            $idTarifa = 0;
            $stmtTarifa = $mysqli->prepare("SELECT Tarifa_externos FROM Recorridos WHERE Numero = ? AND Cliente = ? LIMIT 1");
            $stmtTarifa->bind_param("ii", $row['Recorrido'], $row['ingBrutosOrigen']);
            $stmtTarifa->execute();
            $stmtTarifa->bind_result($tarifa_externa_id);
            $tieneTarifaEspecial = false;
            if ($stmtTarifa->fetch() && $tarifa_externa_id > 0 && isset($tarifas[$tarifa_externa_id])) {
                $idTarifa = $tarifa_externa_id;
                $tieneTarifaEspecial = true;
            }
            $stmtTarifa->close();

            // // Tarifa por distancia
            // if (!$tieneTarifaEspecial) {
            //     if ($km > 50) $idTarifa = 4;
            //     elseif ($km > 25) $idTarifa = 3;
            //     elseif (!$dentro) $idTarifa = 2;
            //     else $idTarifa = 1;
            // }

            // $row['Kilometros'] = $km;
            // $row['DentroAnillo'] = $dentro;
            // $row['TarifaID'] = $idTarifa;
            // $row['Precio'] = isset($tarifas[$idTarifa]) ? $tarifas[$idTarifa] : 0;
            // $row['NombreTarifa'] = isset($tarifas_nombres[$idTarifa]) ? $tarifas_nombres[$idTarifa] : "Tarifa " . $idTarifa;

            // Tarifa por distancia
            if (!$tieneTarifaEspecial) {
                if ($km > 50) {
                    $idTarifa = 4;
                } elseif ($km > 25) {
                    $idTarifa = 3;
                } elseif (!$dentro) {
                    $idTarifa = 2;
                } else {
                    $idTarifa = 1;
                }
            }

            $row['Kilometros'] = $km;
            $row['DentroAnillo'] = $dentro;
            $row['TarifaID'] = $idTarifa;

            // Precio base normal
            $precioBase = isset($tarifas[$idTarifa]) ? floatval($tarifas[$idTarifa]) : 0;
            $nombreTarifa = isset($tarifas_nombres[$idTarifa]) ? $tarifas_nombres[$idTarifa] : "Tarifa " . $idTarifa;

            // REGLA ESPECIAL CLIENTE 47305:
            // visitado pero NO entregado = cobrar 50%
            if (
                intval($row['ingBrutosOrigen']) === 47305 &&
                intval($row['Entregado']) !== 1
            ) {
                $precioBase = round($precioBase * 0.5, 2);
                $nombreTarifa .= " (50%)";
            }

            $row['Precio'] = $precioBase;
            $row['NombreTarifa'] = $nombreTarifa;


            if (intval($row['Entregado']) === 1) {
                // Obtener porcentaje de la tarifa 7
                $sql_tarifa_externos_cobranza = $mysqli->prepare("SELECT Precio FROM Externos_tarifas WHERE id = ?");
                $tarifa_numero = 9;
                $sql_tarifa_externos_cobranza->bind_param("i", $tarifa_numero);
                $sql_tarifa_externos_cobranza->execute();
                $sql_tarifa_externos_cobranza->bind_result($porcentaje);

                $porcentaje = 0;
                if ($sql_tarifa_externos_cobranza->fetch()) {
                    // porcentaje ya seteado
                }
                $sql_tarifa_externos_cobranza->close();

                // Obtener CobrarEnvio
                $sql_ventas = $mysqli->prepare("SELECT CobrarEnvio FROM Ventas WHERE Eliminado = 0 AND surrender_number <> 0 AND NumPedido = ?");
                $sql_ventas->bind_param("s", $row['CodigoSeguimiento']);
                $sql_ventas->execute();
                $sql_ventas->bind_result($cobrarEnvio);

                if ($sql_ventas->fetch()) {
                    $row['CobrarEnvio'] = round(($cobrarEnvio * $porcentaje / 100), 2);
                } else {
                    $row['CobrarEnvio'] = 0;
                }

                $sql_ventas->close();
            }

            // Si Debe es 0 y no es colecta, buscar precio prorrateado
            if (floatval($row['Debe']) == 0) {
                $recorrido = intval($row['Recorrido']);
                $cliente = intval($row['ingBrutosOrigen']);
                $key = $recorrido . '_' . $cliente;

                $sql_recorrido = $mysqli->prepare("SELECT CodigoProductos FROM Recorridos WHERE Numero = ? AND Cliente = ? LIMIT 1");
                $sql_recorrido->bind_param("ii", $recorrido, $cliente);
                $sql_recorrido->execute();
                $sql_recorrido->bind_result($codigoProducto);

                if ($sql_recorrido->fetch() && $codigoProducto) {
                    $sql_recorrido->close();

                    $sql_precio = $mysqli->prepare("SELECT PrecioVenta FROM Productos WHERE Codigo = ? LIMIT 1");
                    $sql_precio->bind_param("s", $codigoProducto);
                    $sql_precio->execute();
                    $sql_precio->bind_result($precioTotal);

                    if ($sql_precio->fetch() && $conteoServicios[$key] > 0) {
                        $row['Debe'] = round($precioTotal / $conteoServicios[$key], 2);
                    }
                    $sql_precio->close();
                } else {
                    $sql_recorrido->close();
                }
            }

            $row['CobrarEnvio'] = isset($row['CobrarEnvio']) ? $row['CobrarEnvio'] : 0;

            $row['Total'] = round(floatval($row['Precio']) + floatval($row['CobrarEnvio']), 2);

            $ROWS[] = $row;
        }

        echo json_encode(['data' => $ROWS]);
    } elseif ($_POST['controlado'] == 1) {


        $SQL_EXTERNOS = $mysqli->query("SELECT Usuario FROM usuarios WHERE id='" . $_POST['id'] . "' ");
        $DATO_EXTERNOS = $SQL_EXTERNOS->fetch_array(MYSQLI_ASSOC);
        $NombreUsuario = $DATO_EXTERNOS['Usuario'];

        $SQL = $mysqli->query("SELECT 
        Seg.Entregado,
        ts.RazonSocial,
        ts.ingBrutosOrigen,
        ts.Recorrido,
        ts.idPago,
        ts.ClienteDestino,
        ts.idClienteDestino,
        Seg.Fecha,
        ts.DomicilioDestino,
        ts.LocalidadDestino,
        ts.CodigoSeguimiento,
        Seg.Estado,
        CONCAT(Seg.Usuario, '_', Seg.Fecha, 'T', Seg.Hora) AS infoABM,
        ts.Kilometros,
        ts.Debe,
        cl.Latitud,
        cl.Longitud,
        er.PrecioPagado as Precio,
        er.Observaciones as ObservacionManual,
        er.idExternos_tarifas as TarifaID,
        et.Nombre as NombreTarifa,
        ROUND(IFNULL(vt.CobrarEnvio, 0) * 0.02, 2) AS CobrarEnvio
        FROM Seguimiento AS Seg
        JOIN TransClientes AS ts ON Seg.CodigoSeguimiento = ts.CodigoSeguimiento
        JOIN Clientes AS cl ON ts.idClienteDestino = cl.id
        JOIN Externos_rendicion AS er ON Seg.CodigoSeguimiento=er.CodigoSeguimiento
        JOIN Externos_tarifas AS et ON er.idExternos_tarifas = et.id
        LEFT JOIN Ventas AS vt ON vt.NumPedido = Seg.CodigoSeguimiento AND vt.Eliminado = 0 AND vt.surrender_number <> 0
        WHERE Seg.Eliminado = 0 
        AND ts.Eliminado = 0 
        AND Seg.NumerodeOrden = " . $_POST['NOrden'] . "
        AND Seg.Visitas<>0
        AND Seg.Usuario = '" . $NombreUsuario . "'
        AND Seg.Estado <>'Retirado del Cliente' 
        AND er.idRendicion = " . $_POST['NOrden'] . " ");

        $ROWS = [];

        while ($row = $SQL->fetch_array(MYSQLI_ASSOC)) {
            $ROWS[] = $row;
        }

        //OBTENGO EL TOTAL PARA VERIFICACION

        $total = 0;
        foreach ($ROWS as $row) {
            $total += floatval($row['Precio']) + floatval($row['CobrarEnvio']);
        }
        $total = round($total, 2);

        $sql = $mysqli->prepare("UPDATE Logistica SET Costo_rendicion = ? WHERE Eliminado=0 AND NumerodeOrden = ? LIMIT 1");
        $sql->bind_param("di", $total, $_POST['NOrden']);
        $sql->execute();
        $sql->close();

        echo json_encode(['data' => $ROWS, 'total_verificacion' => $total]);
    }
}

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['Rendicion'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Validaciones
    if (empty($input['rendicion']) || !is_array($input['rendicion'])) {
        echo json_encode(["success" => false, "error" => "Datos de rendición inválidos"]);
        exit;
    }
    if (empty($input['nrendicion']) || empty($input['id_externo'])) {
        echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
        exit;
    }

    $errores = [];

    // Validaciones básicas
    if (!isset($input['rendicion']) || !is_array($input['rendicion'])) {
        echo json_encode(["success" => false, "error" => "Datos de rendición inválidos"]);
        exit;
    }

    if (!isset($input['nrendicion']) || !isset($input['id_externo'])) {
        echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
        exit;
    }

    $idRendicion = $input['nrendicion'];
    $idExterno = $input['id_externo'];
    $observaciones_logistica = isset($input['Observaciones']) ? $input['Observaciones'] : '';
    $usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'Desconocido';

    foreach ($input['rendicion'] as $row) {
        // Validación mínima por fila
        if (!isset($row['CodigoSeguimiento']) || !isset($row['Precio']) || !isset($row['Debe']) || !isset($row['Kilometros'])) {
            $errores[] = "Datos incompletos para una fila. Código: " . (isset($row['CodigoSeguimiento']) ? $row['CodigoSeguimiento'] : 'N/D');
            continue;
        }

        $stmt = $mysqli->prepare("INSERT INTO Externos_rendicion(CodigoSeguimiento, IdEmpleado, PrecioPagado, PrecioCobrado, Timestamp, Usuario, Observaciones, idRendicion, Kilometros,idExternos_tarifas)VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $errores[] = "Error al preparar INSERT: " . $mysqli->error;
            continue;
        }


        $stmt->bind_param(
            "siddssidi",
            $row['CodigoSeguimiento'],
            $idExterno,
            $row['Precio'],
            $row['Debe'],
            $usuario,
            $row['ObservacionManual'], // 👈 esta
            $idRendicion,
            $row['Kilometros'],
            $row['TarifaID']
        );

        if (!$stmt->execute()) {
            $errores[] = "Error al ejecutar INSERT para código " . $row['CodigoSeguimiento'] . ": " . $stmt->error;
        }

        $stmt->close();

        // Actualizar idPago en TransClientes
        $update = $mysqli->prepare("UPDATE TransClientes SET idPago = ? WHERE CodigoSeguimiento = ? AND Eliminado = 0 LIMIT 1");

        if (!$update) {
            $errores[] = "Error al preparar UPDATE: " . $mysqli->error;
            continue;
        }

        $update->bind_param("is", $idRendicion, $row['CodigoSeguimiento']);

        if (!$update->execute()) {
            $errores[] = "Error al ejecutar UPDATE para código " . $row['CodigoSeguimiento'] . ": " . $update->error;
        }

        $update->close();
    }

    // Calcular el total de la rendición
    // $total_rendicion = 0;
    // $total_rendicion = $row['Total_rendicion'];
    $total_rendicion = isset($input['Total_rendicion']) ? (float)$input['Total_rendicion'] : 0.0;
    $total_rendicion = round($total_rendicion, 2);


    // foreach ($input['rendicion'] as $row) {
    //     $total_rendicion += floatval($row['Total']+floatval($row['']));
    // }

    // Actualizar Logistica
    $updateLogistica = $mysqli->prepare("UPDATE Logistica SET Costo_rendicion = ?, Observaciones_rendicion = ? WHERE Eliminado=0 AND NumerodeOrden = ? LIMIT 1;");

    if ($updateLogistica) {
        $updateLogistica->bind_param("dsi", $total_rendicion, $observaciones_logistica, $idRendicion);
        if (!$updateLogistica->execute()) {
            $errores[] = "Error al actualizar Logistica: " . $updateLogistica->error;
        }
        $updateLogistica->close();
    } else {
        $errores[] = "Error al preparar UPDATE en Logistica: " . $mysqli->error;
    }


    if (!empty($errores)) {
        file_put_contents("log_rendicion.txt", implode("\n", $errores) . "\n", FILE_APPEND);
        echo json_encode(["success" => false, "errores" => $errores]);
    } else {
        echo json_encode(["success" => true, "mensaje" => "Rendición y actualización realizadas correctamente."]);
    }
}
if (isset($_POST['TiposComprobante'])) {
    $q = $mysqli->query("SELECT Codigo,Descripcion FROM `AfipTipoDeComprobante`;");
    $tipos = [];
    while ($r = $q->fetch_assoc()) {
        $tipos[] = $r;
    }
    echo json_encode($tipos);
    exit;
}

if (isset($_POST['ConfirmarFactura'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    header('Content-Type: application/json; charset=UTF-8');

    $ordenes         = isset($_POST['Ordenes']) ? (array)$_POST['Ordenes'] : [];
    $numeroControl   = isset($_POST['NumeroControl']) ? trim((string)$_POST['NumeroControl']) : '';
    $numeroFactura   = isset($_POST['NumeroFactura']) ? trim((string)$_POST['NumeroFactura']) : '';
    $tipoComprobante = isset($_POST['TipoComprobante']) ? trim((string)$_POST['TipoComprobante']) : '';
    $importe         = isset($_POST['Importe']) ? (float)$_POST['Importe'] : 0;
    $fecha           = isset($_POST['FechaControl']) ? trim((string)$_POST['FechaControl']) : '';

    if (empty($ordenes) || $numeroControl === '' || $numeroFactura === '' || $tipoComprobante === '' || $fecha === '') {
        echo json_encode(["success" => 0, "msg" => "Faltan datos"]);
        exit;
    }

    // Normalizar fecha (ej: quita punto final)
    $fecha = preg_replace('/[^0-9\-]/', '', $fecha);
    $dt = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$dt || $dt->format('Y-m-d') !== $fecha) {
        echo json_encode(["success" => 0, "msg" => "Fecha inválida (use YYYY-MM-DD)"]);
        exit;
    }

    $numeroComprobante = $numeroControl . '-' . $numeroFactura;

    $ok = true;
    $errores = [];

    $mysqli->begin_transaction();

    try {
        $sqlER = "UPDATE Externos_rendicion 
                  SET NumeroComprobante = ?, TipoComprobante = ?, FechaComprobante = ? 
                  WHERE idRendicion = ?";
        $stmtER = $mysqli->prepare($sqlER);
        if (!$stmtER) {
            throw new Exception("Prepare Externos_rendicion: " . $mysqli->error);
        }
        $stmtER->bind_param("sssi", $numeroComprobante, $tipoComprobante, $fecha, $idRend);

        $sqlLG = "UPDATE Logistica SET Rendicion = 1 WHERE Eliminado = 0 AND NumerodeOrden = ?";
        $stmtLG = $mysqli->prepare($sqlLG);
        if (!$stmtLG) {
            throw new Exception("Prepare Logistica: " . $mysqli->error);
        }
        $stmtLG->bind_param("i", $idRend);

        foreach ($ordenes as $idRend) {
            $idRend = (int)$idRend;

            if (!$stmtER->execute()) {
                $ok = false;
                $errores[] = "ER idRendicion $idRend: " . $stmtER->error;
            }
            if (!$stmtLG->execute()) {
                $ok = false;
                $errores[] = "LG idRendicion $idRend: " . $stmtLG->error;
            }
        }

        $stmtER->close();
        $stmtLG->close();

        if ($ok) {
            $mysqli->commit();
        } else {
            $mysqli->rollback();
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        $ok = false;
        $errores[] = $e->getMessage();
    }

    echo json_encode([
        "success" => $ok ? 1 : 0,
        "msg"     => $ok ? "Factura registrada correctamente" : "Errores al registrar",
        "errors"  => $errores
    ]);
}
