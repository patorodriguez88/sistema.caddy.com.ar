<?php

function consultarCodigoSeguimiento($mysqli, $ctx)
{
    $pregunta = $ctx['pregunta'];
    $codigoPosible = strtoupper(trim($pregunta));

    if (!preg_match('/^[A-Z0-9]{6,}$/', $codigoPosible)) {
        return false;
    }

    $codigo = $mysqli->real_escape_string($codigoPosible);

    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            TS.RazonSocial AS Origen,
            TS.DomicilioOrigen,
            TS.LocalidadOrigen,
            TS.ClienteDestino,
            TS.DomicilioDestino,
            TS.LocalidadDestino,
            TS.Entregado,
            TS.Devuelto,
            TS.Fecha,
            TS.Flex,
            TS.shipments_id,
            U.Usuario AS Repartidor,
            MAX(S.Fecha) AS UltimaFechaSeguimiento,
            MAX(S.Estado) AS UltimoEstado
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER 
            ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U 
            ON U.id = ER.IdEmpleado
        LEFT JOIN Seguimiento S 
            ON S.CodigoSeguimiento = TS.CodigoSeguimiento 
           AND S.Eliminado = 0
        WHERE TS.Eliminado = 0
          AND TS.CodigoSeguimiento = '$codigo'
        GROUP BY TS.CodigoSeguimiento
        LIMIT 1
    ";

    $res = $mysqli->query($sql);

    if (!$res || $res->num_rows == 0) {
        salir(['success' => 0, 'msg' => "No encontré el código <strong>$codigo</strong>."]);
    }

    $row = $res->fetch_assoc();

    if ((int)$row['Devuelto'] === 1) {
        $estado = "Devuelto";
    } elseif ((int)$row['Entregado'] === 1) {
        $estado = "Entregado";
    } else {
        $estado = "En ruta / Pendiente";
    }

    $tipo = '';

    if ((int)$row['Flex'] === 1) {
        $tipo .= '<span class="badge bg-info me-1">Flex</span>';
    }

    if (!empty($row['shipments_id']) && (int)$row['shipments_id'] !== 0) {
        $tipo .= '<span class="badge bg-warning text-dark me-1">Meli</span>';
    }

    salir([
        'success' => 1,
        'respuesta' => "<strong>$codigo</strong> → $estado",
        'detalle' => "
            $tipo<br>
            <strong>Origen:</strong> {$row['Origen']}<br>
            <strong>Dirección origen:</strong> {$row['DomicilioOrigen']} {$row['LocalidadOrigen']}<br>
            <hr class='my-1'>
            <strong>Destino:</strong> {$row['ClienteDestino']}<br>
            <strong>Dirección destino:</strong> {$row['DomicilioDestino']} {$row['LocalidadDestino']}<br>
            <strong>Repartidor:</strong> " . ($row['Repartidor'] ?: 'Sin asignar') . "<br>
            <strong>Fecha carga:</strong> {$row['Fecha']}<br>
            <strong>Último seguimiento:</strong> " . ($row['UltimoEstado'] ?: '-') . " " . ($row['UltimaFechaSeguimiento'] ?: '') . "
        "
    ]);

    return true;
}

function detectarClienteConsulta($mysqli, $q)
{
    $busqueda = $q;

    $limpiar = [
        'dame',
        'decime',
        'mostrame',
        'ver',
        'consultar',
        'ventas',
        'venta',
        'cliente',
        'de',
        'del',
        'la',
        'el',
        'mes',
        'pasado',
        'este',
        'actual',
        'semana',
        'hoy',
        'ayer',
        'envios',
        'envíos',
        'recepciones',
        'facturacion',
        'facturación'
    ];

    foreach ($limpiar as $palabra) {
        $busqueda = str_replace($palabra, '', $busqueda);
    }

    $busqueda = trim(preg_replace('/\s+/', ' ', $busqueda));

    if (strlen($busqueda) < 3) {
        return false;
    }

    $like = '%' . $busqueda . '%';

    $stmt = $mysqli->prepare("
        SELECT 
            RazonSocial,
            COUNT(*) AS total
        FROM TransClientes
        WHERE Eliminado = 0
          AND RazonSocial LIKE ?
          AND IFNULL(TRIM(RazonSocial), '') <> ''
        GROUP BY RazonSocial
        ORDER BY total DESC
        LIMIT 5
    ");

    if (!$stmt) return false;

    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();

    $clientes = [];

    while ($row = $res->fetch_assoc()) {
        $clientes[] = $row;
    }

    $stmt->close();

    if (count($clientes) === 0) {
        return false;
    }

    return [
        'busqueda' => $busqueda,
        'clientes' => $clientes
    ];
}

function consultarVentasCliente($mysqli, $ctx)
{
    $q = $ctx['q'];

    if (
        !contieneAlguna($q, ['ventas', 'venta', 'facturacion', 'facturación', 'cliente'])
        || !contieneAlguna($q, ['dame', 'decime', 'mostrame', 'ver', 'consultar', 'cuanto', 'cuánto'])
    ) {
        return false;
    }

    list($fechaDesdeConsulta, $fechaHastaConsulta, $textoPeriodoConsulta) = detectarPeriodoConsulta($q);

    $clienteDetectado = detectarClienteConsulta($mysqli, $q);

    if (!$clienteDetectado) {
        salir([
            'success' => 0,
            'msg' => 'No pude identificar el cliente. Probá por ejemplo: “Dame las ventas de Ferniplast del mes pasado”.'
        ]);
    }

    $clientes = $clienteDetectado['clientes'];

    if (count($clientes) > 1) {
        $detalle = '';

        foreach ($clientes as $i => $cli) {
            $n = $i + 1;
            $detalle .= "#$n {$cli['RazonSocial']} <small>({$cli['total']} registros)</small><br>";
        }

        salir([
            'success' => 1,
            'respuesta' => "Encontré varios clientes parecidos a <strong>{$clienteDetectado['busqueda']}</strong>.",
            'detalle' => $detalle . "<hr class='my-1'><small>Consultá nuevamente usando el nombre más exacto.</small>"
        ]);
    }

    $razonSocial = $clientes[0]['RazonSocial'];

    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) AS cantidad_total,

            SUM(CASE WHEN Retirado = 0 THEN 1 ELSE 0 END) AS cantidad_envios,
            SUM(CASE WHEN Retirado = 1 THEN 1 ELSE 0 END) AS cantidad_recepciones,
            SUM(CASE WHEN Entregado = 1 THEN 1 ELSE 0 END) AS cantidad_entregados,
            SUM(CASE WHEN Devuelto = 1 THEN 1 ELSE 0 END) AS cantidad_devueltos,

            SUM(CASE 
                WHEN Entregado = 0 AND Devuelto = 0 THEN 1 
                ELSE 0 
            END) AS cantidad_pendientes,

            SUM(IFNULL(Debe, 0)) AS facturacion_total,

            SUM(CASE 
                WHEN Facturado = 1 THEN IFNULL(Debe, 0) 
                ELSE 0 
            END) AS facturacion_confirmada

        FROM TransClientes
        WHERE Eliminado = 0
          AND RazonSocial = ?
          AND Fecha >= ?
          AND Fecha <= ?
    ");

    if (!$stmt) {
        salir(['success' => 0, 'msg' => 'Error preparando consulta de ventas por cliente.']);
    }

    $stmt->bind_param("sss", $razonSocial, $fechaDesdeConsulta, $fechaHastaConsulta);
    $stmt->execute();
    $res = $stmt->get_result();
    $datos = $res->fetch_assoc();
    $stmt->close();

    $detalle = "
        🚚 <strong>Envíos:</strong> " . (int)$datos['cantidad_envios'] . "<br>
        📦 <strong>Recepciones / retiros:</strong> " . (int)$datos['cantidad_recepciones'] . "<br>
        ✅ <strong>Entregados:</strong> " . (int)$datos['cantidad_entregados'] . "<br>
        ⏳ <strong>Pendientes:</strong> " . (int)$datos['cantidad_pendientes'] . "<br>
        ↩️ <strong>Devueltos:</strong> " . (int)$datos['cantidad_devueltos'] . "<br>
        📋 <strong>Total movimientos:</strong> " . (int)$datos['cantidad_total'] . "<br>
    ";

    if (isset($_SESSION['Nivel']) && (int)$_SESSION['Nivel'] === 1) {
        $detalle .= "
            <hr class='my-1'>
            💰 <strong>Facturación estimada:</strong> " . dinero($datos['facturacion_total']) . "<br>
            🧾 <strong>Facturación confirmada:</strong> " . dinero($datos['facturacion_confirmada']) . "<br>
        ";
    }

    $detalle .= "
        <hr class='my-1'>
        <small>Criterio: TransClientes.RazonSocial = '{$razonSocial}', Fecha entre {$fechaDesdeConsulta} y {$fechaHastaConsulta}.</small>
    ";

    salir([
        'success' => 1,
        'respuesta' => "Resumen de <strong>{$razonSocial}</strong> en <strong>{$textoPeriodoConsulta}</strong>.",
        'detalle' => $detalle
    ]);

    return true;
}
