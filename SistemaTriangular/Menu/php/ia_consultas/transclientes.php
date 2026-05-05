<?php
function obtenerMontoMinimoSeguro($mysqli)
{
    $stmt = $mysqli->prepare("
        SELECT Valor 
        FROM Variables 
        WHERE Nombre = 'MontoMinimoSeguro' 
        LIMIT 1
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ? (float)$row['Valor'] : 0;
}

function consultarSeguroEntreFechas($mysqli, $ctx)
{
    $q = $ctx['q'];

    if (
        strpos($q, 'seguro') === false &&
        strpos($q, 'declarado') === false &&
        strpos($q, 'valor declarado') === false &&
        strpos($q, 'prevision') === false
    ) {
        return false;
    }

    list($desde, $hasta, $textoPeriodo) = detectarPeriodoConsulta($q);

    $minimoSeguro = obtenerMontoMinimoSeguro($mysqli);

    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) AS cantidad,
            SUM(IFNULL(ValorDeclarado, 0)) AS total_original,
            SUM(
                CASE 
                    WHEN IFNULL(ValorDeclarado, 0) < ? THEN ?
                    ELSE IFNULL(ValorDeclarado, 0)
                END
            ) AS total_ajustado
        FROM TransClientes
        WHERE Eliminado = 0
          AND Fecha >= ?
          AND Fecha <= ?
    ");

    if (!$stmt) {
        salir(['success' => 0, 'msg' => 'Error preparando consulta de seguro.']);
    }

    $stmt->bind_param("ddss", $minimoSeguro, $minimoSeguro, $desde, $hasta);
    $stmt->execute();

    $res = $stmt->get_result();
    $datos = $res->fetch_assoc();
    $stmt->close();

    $cantidad = (int)$datos['cantidad'];
    $totalOriginal = (float)$datos['total_original'];
    $totalAjustado = (float)$datos['total_ajustado'];
    $prevision = $totalAjustado * 0.01;

    $usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'Pato';

    salir([
        'success' => 1,
        'respuesta' => "{$usuario}, el valor declarado para <strong>{$textoPeriodo}</strong> es de <strong>" . dinero($totalOriginal) . "</strong>.",
        'detalle' => "
            📦 <strong>Cantidad de envíos:</strong> {$cantidad}<br>
            🛡️ <strong>Monto mínimo asegurado:</strong> " . dinero($minimoSeguro) . "<br>
            📊 <strong>Total ajustado para seguro:</strong> " . dinero($totalAjustado) . "<br>
            💰 <strong>Previsión sugerida 1%:</strong> <span class='text-success fw-bold'>" . dinero($prevision) . "</span><br>
            <hr class='my-1'>
            <small>
                Criterio: cada envío menor a " . dinero($minimoSeguro) . " se calcula como mínimo asegurado.<br>
                Fecha entre {$desde} y {$hasta}.
            </small>
        "
    ]);

    return true;
}
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
        'cuanto',
        'cuantos',
        'cuanta',
        'cuantas',
        'ventas',
        'venta',
        'facturacion',
        'facturo',
        'facturar',
        'facturado',
        'facturada',
        'cliente',
        'clientes',
        'paquetes',
        'paquete',
        'envio',
        'envios',
        'envia',
        'enviado',
        'enviados',
        'movimientos',
        'movimiento',
        'recepciones',
        'recepcion',
        'retiros',
        'retiro',
        'de',
        'del',
        'la',
        'el',
        'las',
        'los',
        'a',
        'en',
        'por',
        'para',
        'mes',
        'semana',
        'hoy',
        'ayer',
        'este',
        'esta',
        'actual',
        'pasado',
        'pasada',
        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre'
    ];

    $busqueda = normalizarDB($busqueda);

    foreach ($limpiar as $palabra) {
        $palabra = normalizarDB($palabra);
        $busqueda = preg_replace('/\b' . preg_quote($palabra, '/') . '\b/u', ' ', $busqueda);
    }

    $busqueda = trim(preg_replace('/\s+/', ' ', $busqueda));

    $busqueda = normalizarDB(trim(preg_replace('/\s+/', ' ', $busqueda)));

    if (strlen($busqueda) < 3) {
        return false;
    }

    $stmt = $mysqli->prepare("
        SELECT 
            RazonSocial,
            COUNT(*) AS total
        FROM TransClientes
        WHERE Eliminado = 0
          AND IFNULL(TRIM(RazonSocial), '') <> ''
        GROUP BY RazonSocial
        ORDER BY total DESC
        LIMIT 1000
    ");

    if (!$stmt) return false;

    $stmt->execute();
    $res = $stmt->get_result();

    $clientes = [];

    while ($row = $res->fetch_assoc()) {
        $razonNormalizada = normalizarDB($row['RazonSocial']);

        if (
            strpos($razonNormalizada, $busqueda) !== false ||
            strpos($busqueda, $razonNormalizada) !== false
        ) {
            $clientes[] = $row;
        }
    }

    $stmt->close();

    if (count($clientes) === 0) {
        return false;
    }

    return [
        'busqueda' => $busqueda,
        'clientes' => array_slice($clientes, 0, 5)
    ];
}
function obtenerClienteExactoDesdePregunta($q)
{
    if (preg_match('/cliente_exacto:(.+)$/', $q, $m)) {
        return trim($m[1]);
    }

    return false;
}

function consultarVentasCliente($mysqli, $ctx)
{
    $q = $ctx['q'];

    if (

        !contieneAlguna($q, [
            'ventas',
            'venta',
            'facturacion',
            'facturación',
            'facturo',
            'facturó',
            'facturar',
            'facturado',
            'facturada',
            'cliente',
            'paquetes',
            'paquete',
            'envio',
            'envió',
            'envios',
            'envíos',
            'paquetes',
            'movimientos',
            'movimiento'
        ])

        || !contieneAlguna($q, [
            'dame',
            'decime',
            'mostrame',
            'ver',
            'consultar',
            'cuanto',
            'cuánto',
            'cuanta',
            'cuánto',
            'cuantos',
            'cuántos',
            'semana',
            'mes',
            'hoy',
            'ayer',
            'este',
            'actual',
            'pasado'
        ])

    ) {

        return false;
    }

    list($fechaDesdeConsulta, $fechaHastaConsulta, $textoPeriodoConsulta) = detectarPeriodoConsulta($q);
    $clienteExacto = obtenerClienteExactoDesdePregunta($q);

    if ($clienteExacto) {
        $clientes = [
            [
                'RazonSocial' => $clienteExacto,
                'total' => 1
            ]
        ];
    } else {
        $clienteDetectado = detectarClienteConsulta($mysqli, $q);

        if (!$clienteDetectado) {
            salir([
                'success' => 0,
                'msg' => 'No pude identificar el cliente. Probá por ejemplo: “Dame las ventas de Ferniplast del mes pasado”.'
            ]);
        }

        $clientes = $clienteDetectado['clientes'];
    }

    if (count($clientes) > 1 && strpos($q, 'cliente_exacto:') === false) {
        $detalle = '';

        foreach ($clientes as $i => $cli) {
            $razon = htmlspecialchars($cli['RazonSocial'], ENT_QUOTES, 'UTF-8');

            $detalle .= "
            <button 
                type='button' 
                class='btn btn-sm btn-light border me-1 mb-1 ia-cliente-opcion'
                data-cliente='{$razon}'>
                {$razon} <small>({$cli['total']})</small>
            </button>
        ";
        }

        salir([
            'success' => 1,
            'respuesta' => "Encontré varios clientes para <strong>{$clienteDetectado['busqueda']}</strong>. Elegí uno:",
            'detalle' => $detalle
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
