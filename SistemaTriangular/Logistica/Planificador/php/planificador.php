<?php
// Capa de datos del Planificador de Rutas: recorridos disponibles, waypoints
// validados de un recorrido y asignación de los pedidos aceptados a un
// recorrido nuevo. Portado y saneado desde Caddy_produccion/Rutas/Procesos/php/rutas.php.

require_once __DIR__ . "/../../../Conexion/Conexioni.php";
require_once __DIR__ . "/../../../Funciones/Funciones.php";
date_default_timezone_set('America/Argentina/Cordoba');

header('Content-Type: application/json; charset=UTF-8');

function traducirDia($diaIngles)
{
    $dias = [
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo',
    ];
    return isset($dias[$diaIngles]) ? $dias[$diaIngles] : $diaIngles;
}

if (isset($_POST['BuscarRecorridos'])) {
    $recorridos = [];
    $res = $mysqli->query("SELECT Numero, Nombre FROM Recorridos WHERE Activo = 1");

    while ($fila = $res->fetch_assoc()) {
        $activo = "";
        $activos = $mysqli->query("SELECT NombreChofer FROM Logistica WHERE Recorrido = '{$fila['Numero']}' AND Estado = 'Cargada' AND Eliminado = '0'");
        if ($activos && $activos->num_rows > 0) {
            $filaActivo = $activos->fetch_assoc();
            $activo = '-> En Ruta ' . $filaActivo['NombreChofer'];
        }
        $recorridos[] = [
            'Numero' => $fila['Numero'],
            'Nombre' => $fila['Nombre'],
            'Estado' => $activo,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $recorridos]);
    exit;
}

if (isset($_POST['BuscarWaypoints']) && isset($_POST['Recorrido'])) {
    $Recorrido = intval($_POST['Recorrido']);

    // COALESCE con Clientes.HorarioEntregaSolicitado: TransClientes.HorarioEntregaSolicitado
    // solo se completa hoy desde el flujo normal de Ventas.php - Colecta/Flex
    // y las altas desde TiendaNube/Meli/importacion masiva nunca lo cargan,
    // aunque el cliente sí tenga la preferencia guardada en su ficha. Se usa
    // para priorizar paradas urgentes al ordenar (ver ordenarWaypointsPorCercania
    // en planificador.js), mismo criterio que ya usa orden_automatico.php.
    $stmt = $mysqli->prepare("
        SELECT ts.id, ts.CodigoSeguimiento, cs.nombrecliente, cs.Latitud, cs.Longitud,
               COALESCE(ts.HorarioEntregaSolicitado, cs.HorarioEntregaDesde) AS HorarioEntregaSolicitado
        FROM TransClientes AS ts
        LEFT JOIN Clientes AS cs ON ts.idClienteDestino = cs.id
        WHERE ts.Eliminado = 0
          AND ts.Entregado = 0
          AND ts.Devuelto = 0
          AND ts.Haber = 0
          AND ts.Recorrido = ?
    ");
    $stmt->bind_param("i", $Recorrido);
    $stmt->execute();
    $res = $stmt->get_result();

    $puntos = [];
    $sinCoordenadas = [];
    $isValidCoord = function ($lat, $lng) {
        return $lat <= -21.0 && $lat >= -55.0 && $lng <= -53.0 && $lng >= -75.0;
    };

    while ($row = $res->fetch_assoc()) {
        $lat = floatval($row['Latitud']);
        $lng = floatval($row['Longitud']);
        $nombre = $row['nombrecliente'] ?: 'Cliente sin nombre';

        if ($lat !== 0.0 && $lng !== 0.0 && $isValidCoord($lat, $lng)) {
            $puntos[] = [
                'lat' => $lat,
                'lng' => $lng,
                'nombrecliente' => $nombre,
                'CodigoSeguimiento' => $row['CodigoSeguimiento'],
                'horarioSolicitado' => $row['HorarioEntregaSolicitado'],
            ];
        } else {
            $sinCoordenadas[] = "• $nombre (Seguimiento {$row['CodigoSeguimiento']})";
        }
    }
    $stmt->close();

    if (!empty($sinCoordenadas)) {
        echo json_encode([
            'status' => 'error',
            'message' => "Hay " . count($sinCoordenadas) . " servicios sin coordenadas válidas.",
            'faltantes' => $sinCoordenadas,
        ]);
        exit;
    }

    if (empty($puntos)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El recorrido seleccionado no tiene servicios pendientes para planificar.',
        ]);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $puntos]);
    exit;
}

if (isset($_POST['BuscarOrdenesDisponibles'])) {
    // Ordenes de Salida ya cargadas por el operador (Logistica.Estado Alta/Pendiente)
    // que todavía no tienen paradas asignadas a su Recorrido — son los "choferes en
    // blanco" que se arrastran una ruta calculada encima en el Planificador.
    $res = $mysqli->query("
        SELECT l.NumerodeOrden, l.Patente, l.NombreChofer, l.Recorrido, l.Estado,
               v.Marca, v.Modelo
        FROM Logistica l
        LEFT JOIN Vehiculos v ON v.Dominio = l.Patente
        WHERE l.Eliminado = 0 AND l.Estado IN ('Alta', 'Pendiente')
        ORDER BY l.NumerodeOrden ASC
    ");

    $ordenes = [];
    while ($row = $res->fetch_assoc()) {
        $ordenes[] = [
            'NumerodeOrden' => (int)$row['NumerodeOrden'],
            'Patente' => $row['Patente'],
            'NombreChofer' => $row['NombreChofer'],
            'Recorrido' => (int)$row['Recorrido'],
            'Estado' => $row['Estado'],
            'Vehiculo' => trim(($row['Marca'] ?? '') . ' ' . ($row['Modelo'] ?? '')),
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $ordenes]);
    exit;
}

if (isset($_POST['AsignarRecorrido'])) {
    // Nunca crea un Recorrido nuevo: siempre actualiza el Recorrido "en blanco" que ya
    // trae la Orden de Salida (creada por el operador en Ordenes.php), evitando duplicar
    // la lógica de alta de orden/vehículo/chofer que ya vive ahí.
    $nombreRecorrido = $mysqli->real_escape_string($_POST['recorrido'] ?? '');
    $datos = json_decode($_POST['datos'] ?? '', true);
    $recorrido_actual = intval($_POST['recorrido_actual'] ?? 0);
    $recorrido_destino = intval($_POST['recorrido_destino'] ?? 0);
    $zona = $mysqli->real_escape_string($_POST['zona'] ?? '');
    $color = isset($_POST['color']) ? $mysqli->real_escape_string($_POST['color']) : '000000';
    $km = isset($_POST['kilometros']) ? floatval($_POST['kilometros']) : 0;
    $tiempoTotal = isset($_POST['tiempoTotal']) ? intval($_POST['tiempoTotal']) : 0;
    $puntos = isset($_POST['puntos']) ? intval($_POST['puntos']) : 0;
    $polyline = isset($_POST['polyline']) ? $mysqli->real_escape_string($_POST['polyline']) : '';
    $diaSalida = traducirDia(date('l'));
    $Usuario = $_SESSION['Usuario'] ?? 'sistema';

    if ($nombreRecorrido === '' || !is_array($datos) || count($datos) === 0 || $recorrido_destino <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o vacíos.']);
        exit;
    }

    $existeRecorrido = $mysqli->query("SELECT id FROM Recorridos WHERE Numero = '$recorrido_destino' LIMIT 1");
    if (!$existeRecorrido || $existeRecorrido->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "El recorrido destino ($recorrido_destino) no existe. ¿La orden se cargó correctamente en Ordenes de Salida?",
        ]);
        exit;
    }

    $verificaHoja = $mysqli->query("SELECT COUNT(*) AS total FROM HojaDeRuta WHERE Eliminado = 0 AND Estado = 'Abierto' AND Recorrido = '$recorrido_destino'");
    if (intval($verificaHoja->fetch_assoc()['total']) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "El recorrido destino ($recorrido_destino) ya tiene paradas asignadas.",
        ]);
        exit;
    }

    $stmtUpd = $mysqli->prepare("
        UPDATE Recorridos
        SET Nombre = ?, Zona = ?, Kilometros = ?, Activo = 1, Color = ?, DiaSalida = ?, Servicios = ?, Polyline = ?,
            UltimoOrdenUsuario = ?, UltimoOrdenFecha = NOW(), UltimoOrdenMetodo = 'Planificador', UltimoOrdenKm = ?, UltimoOrdenMinutos = ?
        WHERE Numero = ?
    ");
    $stmtUpd->bind_param(
        "ssdssissdis",
        $nombreRecorrido,
        $zona,
        $km,
        $color,
        $diaSalida,
        $puntos,
        $polyline,
        $Usuario,
        $km,
        $tiempoTotal,
        $recorrido_destino
    );
    $stmtUpd->execute();
    $stmtUpd->close();

    $errores = [];
    $stmtCheck = $mysqli->prepare("SELECT id FROM HojaDeRuta WHERE Seguimiento = ? AND Eliminado = 0 AND Recorrido = ? LIMIT 1");
    $stmtPosicion = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ? WHERE Seguimiento = ? AND Eliminado = 0 LIMIT 1");

    foreach ($datos as $dato) {
        $codigo = (string)($dato['codigo'] ?? '');
        $orden = intval($dato['orden'] ?? 0);
        if ($codigo === '') {
            continue;
        }

        // Confirma que el pedido siga en recorrido_actual antes de moverlo (guarda contra
        // que otro operador ya lo haya movido desde que se armó este lote).
        $stmtCheck->bind_param("si", $codigo, $recorrido_actual);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result();

        if ($existe->num_rows === 0) {
            $errores[] = $codigo;
            continue;
        }

        // TransClientes + HojaDeRuta.Recorrido + Seguimiento + webhook, vía la función unificada.
        $resultado = cambiarRecorrido($mysqli, $codigo, (string)$recorrido_destino);
        if (empty($resultado['success']) && empty($resultado['ya_estaba'])) {
            $errores[] = $codigo;
            continue;
        }

        // Posicion (orden de la parada dentro del recorrido nuevo) es propio de este flujo de
        // planificación, cambiarRecorrido() no lo maneja.
        $stmtPosicion->bind_param("is", $orden, $codigo);
        if (!$stmtPosicion->execute()) {
            $errores[] = $codigo;
        }
    }
    $stmtCheck->close();
    $stmtPosicion->close();

    if (count($errores) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar algunos pedidos.', 'fallidos' => $errores]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Recorrido asignado correctamente']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida.']);
