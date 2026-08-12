<?php
// Herramientas (tools) que el asistente de IA puede invocar. Cada una es una consulta
// segura y parametrizada a la base — Claude nunca ejecuta SQL propio, solo puede llamar
// a estas funciones con los parámetros que definimos acá. Así el asistente queda acotado
// a lo que este archivo permite, sin importar qué le pidan.

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

// Definición de las herramientas en el formato que espera la API de Claude (tools).
function iaDefinirHerramientas(): array
{
    return [
        [
            'name' => 'calcular_seguro',
            'description' => 'Calcula el valor declarado / seguro de los envíos en un período. Cada envío con valor declarado menor al mínimo asegurado se computa como ese mínimo. Devuelve además una previsión sugerida del 1% sobre el total ajustado. Esta es la consulta contable más usada del sistema (mensual).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha desde, formato YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha hasta, formato YYYY-MM-DD'],
                    'cliente' => ['type' => 'string', 'description' => 'Razón social exacta de un cliente puntual a incluir (opcional). Usar buscar_cliente antes si no estás seguro del nombre exacto.'],
                    'excluir_clientes' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Lista de nombres (parciales) de clientes a excluir del cálculo, ej. cuentas internas de la empresa.'
                    ]
                ],
                'required' => ['desde', 'hasta']
            ]
        ],
        [
            'name' => 'buscar_cliente',
            'description' => 'Busca clientes por nombre parcial en TransClientes (razón social) para desambiguar antes de pedir un resumen de ventas. Devuelve hasta 5 coincidencias con su cantidad de movimientos.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'texto' => ['type' => 'string', 'description' => 'Nombre o parte del nombre del cliente a buscar']
                ],
                'required' => ['texto']
            ]
        ],
        [
            'name' => 'resumen_ventas_cliente',
            'description' => 'Resumen de movimientos (envíos, recepciones, entregados, pendientes, devueltos) y facturación de un cliente puntual en un período. Usar la razón social EXACTA devuelta por buscar_cliente.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'cliente' => ['type' => 'string', 'description' => 'Razón social exacta del cliente'],
                    'desde' => ['type' => 'string', 'description' => 'Fecha desde, formato YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha hasta, formato YYYY-MM-DD']
                ],
                'required' => ['cliente', 'desde', 'hasta']
            ]
        ],
        [
            'name' => 'buscar_seguimiento',
            'description' => 'Busca el estado y los datos completos de un envío puntual por su código de seguimiento.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'codigo' => ['type' => 'string', 'description' => 'Código de seguimiento del envío']
                ],
                'required' => ['codigo']
            ]
        ],
        [
            'name' => 'contar_envios',
            'description' => 'Cuenta envíos en un período según su estado (entregados, fallidos, devueltos, retirados, en ruta, o pendientes/no resueltos), con filtros opcionales de canal (Flex, Meli) y repartidor. Si el total es 20 o menos, también devuelve el detalle línea por línea.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha desde, formato YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha hasta, formato YYYY-MM-DD'],
                    'estado' => [
                        'type' => 'string',
                        'enum' => ['entregado', 'fallido', 'devuelto', 'retirado', 'en_ruta', 'pendiente'],
                        'description' => 'entregado=entregados al cliente, fallido=no se pudo entregar, devuelto=devuelto al cliente, retirado=retirado del cliente de origen, en_ruta=en tránsito o cargado en hoja de ruta, pendiente=todavía no entregado ni devuelto (sin importar estado puntual)'
                    ],
                    'canal' => ['type' => 'string', 'enum' => ['flex', 'meli', 'todos'], 'description' => 'Filtrar por canal de venta. "todos" o ausente = sin filtro.'],
                    'repartidor' => ['type' => 'string', 'description' => 'Nombre (parcial) del repartidor/chofer para filtrar (opcional)']
                ],
                'required' => ['desde', 'hasta', 'estado']
            ]
        ],
        [
            'name' => 'ranking_repartidores',
            'description' => 'Ranking de repartidores en un período, por cantidad de envíos entregados o por cantidad de pendientes (a elección).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha desde, formato YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha hasta, formato YYYY-MM-DD'],
                    'criterio' => ['type' => 'string', 'enum' => ['entregados', 'pendientes'], 'description' => 'Por qué ordenar el ranking']
                ],
                'required' => ['desde', 'hasta', 'criterio']
            ]
        ],
        [
            'name' => 'buscar_tarifa',
            'description' => 'Busca productos/tarifas activas por nombre, descripción o código. Devuelve precio con y sin IVA.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'texto' => ['type' => 'string', 'description' => 'Nombre, código o parte de la descripción de la tarifa/producto']
                ],
                'required' => ['texto']
            ]
        ],
        [
            'name' => 'buscar_localidad',
            'description' => 'Busca localidades de cobertura por nombre o código postal. Sin parámetros, devuelve el listado general de localidades.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'texto' => ['type' => 'string', 'description' => 'Nombre de localidad o código postal (opcional)'],
                    'solo_web' => ['type' => 'boolean', 'description' => 'true = solo localidades habilitadas para la web']
                ]
            ]
        ],
        [
            'name' => 'resumen_logistica_mes',
            'description' => 'Rendiciones controladas pendientes de facturar, y total facturado en el mes actual (este último dato solo visible para SuperAdministrador).',
            'input_schema' => ['type' => 'object', 'properties' => (object)[]]
        ],
    ];
}

// Ejecuta una herramienta por nombre. $nivel es el Nivel del usuario logueado (para
// gating de datos sensibles, igual que ya hacía el sistema de reglas viejo).
function iaEjecutarHerramienta(mysqli $mysqli, string $nombre, array $input, int $nivel): array
{
    switch ($nombre) {
        case 'calcular_seguro':
            return iaCalcularSeguro($mysqli, $input);
        case 'buscar_cliente':
            return iaBuscarCliente($mysqli, $input);
        case 'resumen_ventas_cliente':
            return iaResumenVentasCliente($mysqli, $input, $nivel);
        case 'buscar_seguimiento':
            return iaBuscarSeguimiento($mysqli, $input);
        case 'contar_envios':
            return iaContarEnvios($mysqli, $input);
        case 'ranking_repartidores':
            return iaRankingRepartidores($mysqli, $input);
        case 'buscar_tarifa':
            return iaBuscarTarifa($mysqli, $input);
        case 'buscar_localidad':
            return iaBuscarLocalidad($mysqli, $input);
        case 'resumen_logistica_mes':
            return iaResumenLogisticaMes($mysqli, $nivel);
        default:
            return ['error' => "Herramienta desconocida: $nombre"];
    }
}

function iaObtenerMontoMinimoSeguro(mysqli $mysqli): float
{
    $res = $mysqli->query("SELECT Valor FROM Variables WHERE Nombre = 'MontoMinimoSeguro' LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    return $row ? (float)$row['Valor'] : 0;
}

function iaCalcularSeguro(mysqli $mysqli, array $in): array
{
    $desde = $in['desde'] ?? '';
    $hasta = $in['hasta'] ?? '';
    $cliente = trim($in['cliente'] ?? '');
    $excluir = is_array($in['excluir_clientes'] ?? null) ? $in['excluir_clientes'] : [];

    if (!$desde || !$hasta) {
        return ['error' => 'Faltan fechas desde/hasta.'];
    }

    $minimo = iaObtenerMontoMinimoSeguro($mysqli);

    $whereExtra = '';
    $params = [$minimo, $minimo, $desde, $hasta];
    $types = 'ddss';

    if ($cliente !== '') {
        $whereExtra .= ' AND RazonSocial = ? ';
        $params[] = $cliente;
        $types .= 's';
    }

    foreach ($excluir as $c) {
        $c = trim((string)$c);
        if ($c === '') continue;
        $whereExtra .= ' AND LOWER(RazonSocial) NOT LIKE ? ';
        $params[] = '%' . strtolower($c) . '%';
        $types .= 's';
    }

    $stmt = $mysqli->prepare("
        SELECT
            COUNT(*) AS cantidad,
            SUM(IFNULL(ValorDeclarado, 0)) AS total_original,
            SUM(CASE WHEN IFNULL(ValorDeclarado, 0) < ? THEN ? ELSE IFNULL(ValorDeclarado, 0) END) AS total_ajustado
        FROM TransClientes
        WHERE Eliminado = 0
          AND Fecha >= ?
          AND Fecha <= ?
          $whereExtra
    ");
    if (!$stmt) return ['error' => 'Error preparando la consulta de seguro.'];

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $totalAjustado = (float)$datos['total_ajustado'];

    return [
        'periodo' => "$desde a $hasta",
        'cliente_incluido' => $cliente ?: null,
        'clientes_excluidos' => $excluir,
        'cantidad_envios' => (int)$datos['cantidad'],
        'monto_minimo_asegurado_por_envio' => dinero($minimo),
        'valor_declarado_total' => dinero($datos['total_original']),
        'total_ajustado_para_seguro' => dinero($totalAjustado),
        'prevision_sugerida_1pct' => dinero($totalAjustado * 0.01),
        'criterio' => 'Cada envío con valor declarado menor al mínimo se computa como el mínimo asegurado.'
    ];
}

function iaBuscarCliente(mysqli $mysqli, array $in): array
{
    $texto = trim($in['texto'] ?? '');
    if (mb_strlen($texto) < 2) {
        return ['error' => 'Dame al menos 2 caracteres para buscar el cliente.'];
    }

    $like = '%' . $mysqli->real_escape_string($texto) . '%';
    $stmt = $mysqli->prepare("
        SELECT RazonSocial, COUNT(*) AS total
        FROM TransClientes
        WHERE Eliminado = 0
          AND RazonSocial LIKE ?
        GROUP BY RazonSocial
        ORDER BY total DESC
        LIMIT 5
    ");
    if (!$stmt) return ['error' => 'Error preparando la búsqueda de cliente.'];

    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();

    $clientes = [];
    while ($row = $res->fetch_assoc()) {
        $clientes[] = ['razon_social' => $row['RazonSocial'], 'movimientos_totales' => (int)$row['total']];
    }
    $stmt->close();

    if (!$clientes) {
        return ['encontrados' => 0, 'mensaje' => "No encontré clientes que coincidan con \"$texto\"."];
    }

    return ['encontrados' => count($clientes), 'clientes' => $clientes];
}

function iaResumenVentasCliente(mysqli $mysqli, array $in, int $nivel): array
{
    $cliente = trim($in['cliente'] ?? '');
    $desde = $in['desde'] ?? '';
    $hasta = $in['hasta'] ?? '';

    if ($cliente === '' || !$desde || !$hasta) {
        return ['error' => 'Faltan datos: cliente, desde y hasta son obligatorios.'];
    }

    $stmt = $mysqli->prepare("
        SELECT
            COUNT(*) AS cantidad_total,
            SUM(CASE WHEN Retirado = 0 THEN 1 ELSE 0 END) AS envios,
            SUM(CASE WHEN Retirado = 1 THEN 1 ELSE 0 END) AS recepciones,
            SUM(CASE WHEN Entregado = 1 THEN 1 ELSE 0 END) AS entregados,
            SUM(CASE WHEN Devuelto = 1 THEN 1 ELSE 0 END) AS devueltos,
            SUM(CASE WHEN Entregado = 0 AND Devuelto = 0 THEN 1 ELSE 0 END) AS pendientes,
            SUM(IFNULL(Debe, 0)) AS facturacion_total,
            SUM(CASE WHEN Facturado = 1 THEN IFNULL(Debe, 0) ELSE 0 END) AS facturacion_confirmada
        FROM TransClientes
        WHERE Eliminado = 0 AND RazonSocial = ? AND Fecha >= ? AND Fecha <= ?
    ");
    if (!$stmt) return ['error' => 'Error preparando el resumen de ventas.'];

    $stmt->bind_param('sss', $cliente, $desde, $hasta);
    $stmt->execute();
    $d = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $resultado = [
        'cliente' => $cliente,
        'periodo' => "$desde a $hasta",
        'movimientos_totales' => (int)$d['cantidad_total'],
        'envios' => (int)$d['envios'],
        'recepciones_o_retiros' => (int)$d['recepciones'],
        'entregados' => (int)$d['entregados'],
        'pendientes' => (int)$d['pendientes'],
        'devueltos' => (int)$d['devueltos'],
    ];

    // Facturación: solo SuperAdministrador (mismo criterio que usaba el sistema anterior).
    if ($nivel === 1) {
        $resultado['facturacion_estimada'] = dinero($d['facturacion_total']);
        $resultado['facturacion_confirmada'] = dinero($d['facturacion_confirmada']);
    } else {
        $resultado['facturacion'] = 'No disponible para tu nivel de acceso.';
    }

    return $resultado;
}

function iaBuscarSeguimiento(mysqli $mysqli, array $in): array
{
    $codigo = strtoupper(trim($in['codigo'] ?? ''));
    if ($codigo === '') {
        return ['error' => 'Falta el código de seguimiento.'];
    }

    $stmt = $mysqli->prepare("
        SELECT
            TS.CodigoSeguimiento, TS.RazonSocial AS Origen, TS.DomicilioOrigen, TS.LocalidadOrigen,
            TS.ClienteDestino, TS.DomicilioDestino, TS.LocalidadDestino,
            TS.Entregado, TS.Devuelto, TS.Fecha, TS.Flex, TS.shipments_id,
            U.Usuario AS Repartidor,
            MAX(S.Fecha) AS UltimaFechaSeguimiento, MAX(S.Estado) AS UltimoEstado
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
        LEFT JOIN Seguimiento S ON S.CodigoSeguimiento = TS.CodigoSeguimiento AND S.Eliminado = 0
        WHERE TS.Eliminado = 0 AND TS.CodigoSeguimiento = ?
        GROUP BY TS.CodigoSeguimiento
        LIMIT 1
    ");
    if (!$stmt) return ['error' => 'Error preparando la búsqueda de seguimiento.'];

    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $stmt->close();
        return ['encontrado' => false, 'mensaje' => "No encontré el código $codigo."];
    }

    $row = $res->fetch_assoc();
    $stmt->close();

    $estado = 'En ruta / Pendiente';
    if ((int)$row['Devuelto'] === 1) $estado = 'Devuelto';
    elseif ((int)$row['Entregado'] === 1) $estado = 'Entregado';

    $canal = [];
    if ((int)$row['Flex'] === 1) $canal[] = 'Flex';
    if (!empty($row['shipments_id']) && (int)$row['shipments_id'] !== 0) $canal[] = 'Meli';

    return [
        'encontrado' => true,
        'codigo' => $row['CodigoSeguimiento'],
        'estado' => $estado,
        'canal' => $canal ?: ['Directo'],
        'origen' => $row['Origen'],
        'direccion_origen' => trim($row['DomicilioOrigen'] . ' ' . $row['LocalidadOrigen']),
        'destino' => $row['ClienteDestino'],
        'direccion_destino' => trim($row['DomicilioDestino'] . ' ' . $row['LocalidadDestino']),
        'repartidor' => $row['Repartidor'] ?: 'Sin asignar',
        'fecha_carga' => $row['Fecha'],
        'ultimo_estado_seguimiento' => $row['UltimoEstado'] ?: null,
        'ultima_fecha_seguimiento' => $row['UltimaFechaSeguimiento'] ?: null,
    ];
}

function iaCondicionEstado(string $estado): string
{
    switch ($estado) {
        case 'entregado':
            return "(S.Estado_id = 7 OR S.Estado = 'Entregado al Cliente' OR E.Slug = 'delivered')";
        case 'fallido':
            return "(S.Estado_id = 8 OR S.Estado = 'No se pudo entregar' OR E.Slug = '1st_visit_fail')";
        case 'devuelto':
            return "(S.Estado_id = 9 OR S.Estado = 'Devuelto al Cliente' OR E.Slug = 'returned_to_origin')";
        case 'retirado':
            return "(S.Estado_id = 3 OR S.Estado = 'Retirado del Cliente' OR E.Slug = 'pickup_ready')";
        case 'en_ruta':
            return "(S.Estado_id IN (5,6) OR S.Estado IN ('En Transito', 'Cargado en Hoja de Ruta') OR E.Slug = 'last_mile')";
        default:
            return '1=1';
    }
}

function iaContarEnvios(mysqli $mysqli, array $in): array
{
    $desde = $in['desde'] ?? '';
    $hasta = $in['hasta'] ?? '';
    $estado = $in['estado'] ?? '';
    $canal = $in['canal'] ?? 'todos';
    $repartidorTexto = trim($in['repartidor'] ?? '');

    if (!$desde || !$hasta || !$estado) {
        return ['error' => 'Faltan desde/hasta/estado.'];
    }

    $where = "TS.Eliminado = 0 AND S.Eliminado = 0 AND S.Fecha >= ? AND S.Fecha <= ?";
    $params = [$desde, $hasta];
    $types = 'ss';
    $usaSeguimiento = true;

    if ($estado === 'pendiente') {
        // Pendiente se define sobre TransClientes, no sobre eventos de Seguimiento.
        $usaSeguimiento = false;
        $where = "TS.Eliminado = 0 AND TS.Fecha >= ? AND TS.Fecha <= ? AND TS.Entregado = 0 AND TS.Devuelto = 0";
    } else {
        $where .= " AND " . iaCondicionEstado($estado);
    }

    if ($canal === 'flex') {
        $where .= " AND TS.Flex = 1";
    } elseif ($canal === 'meli') {
        $where .= " AND IFNULL(TS.shipments_id, 0) <> 0";
    }

    $joinRepartidor = '';
    if ($repartidorTexto !== '') {
        $joinRepartidor = " INNER JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
                             INNER JOIN usuarios URep ON URep.id = ER.IdEmpleado ";
        $where .= " AND URep.Usuario LIKE ?";
        $params[] = '%' . $repartidorTexto . '%';
        $types .= 's';
    }

    $joinSeguimiento = $usaSeguimiento
        ? " INNER JOIN Seguimiento S ON S.CodigoSeguimiento = TS.CodigoSeguimiento LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado "
        : "";

    $sql = "SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
            FROM TransClientes TS
            $joinSeguimiento
            $joinRepartidor
            WHERE $where";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return ['error' => 'Error preparando el conteo de envíos.'];
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    $resultado = [
        'periodo' => "$desde a $hasta",
        'estado' => $estado,
        'canal' => $canal,
        'repartidor_filtro' => $repartidorTexto ?: null,
        'total' => $total,
    ];

    if ($total > 0 && $total <= 20) {
        $sqlDet = "SELECT DISTINCT TS.CodigoSeguimiento, TS.ClienteDestino, TS.LocalidadDestino
                   FROM TransClientes TS
                   $joinSeguimiento
                   $joinRepartidor
                   WHERE $where
                   ORDER BY TS.Fecha DESC
                   LIMIT 20";
        $stmtD = $mysqli->prepare($sqlDet);
        if ($stmtD) {
            if ($types) $stmtD->bind_param($types, ...$params);
            $stmtD->execute();
            $resD = $stmtD->get_result();
            $detalle = [];
            while ($r = $resD->fetch_assoc()) {
                $detalle[] = "{$r['CodigoSeguimiento']} - {$r['ClienteDestino']} / {$r['LocalidadDestino']}";
            }
            $resultado['detalle'] = $detalle;
            $stmtD->close();
        }
    }

    return $resultado;
}

function iaRankingRepartidores(mysqli $mysqli, array $in): array
{
    $desde = $in['desde'] ?? '';
    $hasta = $in['hasta'] ?? '';
    $criterio = $in['criterio'] ?? 'entregados';

    if (!$desde || !$hasta) {
        return ['error' => 'Faltan desde/hasta.'];
    }

    if ($criterio === 'pendientes') {
        $stmt = $mysqli->prepare("
            SELECT IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor, COUNT(DISTINCT TS.CodigoSeguimiento) AS total
            FROM TransClientes TS
            LEFT JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
            WHERE TS.Eliminado = 0 AND TS.Fecha >= ? AND TS.Fecha <= ?
              AND TS.Entregado = 0 AND TS.Devuelto = 0
            GROUP BY U.Usuario
            ORDER BY total DESC
            LIMIT 15
        ");
    } else {
        $stmt = $mysqli->prepare("
            SELECT IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor, COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            INNER JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
            LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0 AND S.Eliminado = 0 AND S.Fecha >= ? AND S.Fecha <= ?
              AND " . iaCondicionEstado('entregado') . "
            GROUP BY U.Usuario
            ORDER BY total DESC
            LIMIT 15
        ");
    }

    if (!$stmt) return ['error' => 'Error preparando el ranking.'];
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $res = $stmt->get_result();

    $ranking = [];
    while ($row = $res->fetch_assoc()) {
        $ranking[] = ['repartidor' => $row['Repartidor'], 'total' => (int)$row['total']];
    }
    $stmt->close();

    return ['periodo' => "$desde a $hasta", 'criterio' => $criterio, 'ranking' => $ranking];
}

function iaBuscarTarifa(mysqli $mysqli, array $in): array
{
    $texto = trim($in['texto'] ?? '');
    if ($texto === '') {
        return ['error' => 'Falta el texto a buscar.'];
    }

    $like = '%' . $texto . '%';
    $stmt = $mysqli->prepare("
        SELECT Codigo, Titulo, Descripcion, PrecioVenta, Iva
        FROM Productos
        WHERE Inactivo = 0 AND (Titulo LIKE ? OR Descripcion LIKE ? OR Codigo LIKE ?)
        ORDER BY Titulo ASC
        LIMIT 15
    ");
    if (!$stmt) return ['error' => 'Error preparando la búsqueda de tarifas.'];

    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();

    $tarifas = [];
    while ($row = $res->fetch_assoc()) {
        $iva = (float)$row['Iva'];
        if ($iva <= 0) $iva = 1.21;
        $conIva = (float)$row['PrecioVenta'];

        $tarifas[] = [
            'codigo' => $row['Codigo'],
            'titulo' => $row['Titulo'],
            'precio_con_iva' => dinero($conIva),
            'precio_sin_iva' => dinero($conIva / $iva),
        ];
    }
    $stmt->close();

    if (!$tarifas) {
        return ['encontradas' => 0, 'mensaje' => "No encontré tarifas activas que coincidan con \"$texto\"."];
    }

    return ['encontradas' => count($tarifas), 'tarifas' => $tarifas];
}

function iaBuscarLocalidad(mysqli $mysqli, array $in): array
{
    $texto = trim($in['texto'] ?? '');
    $soloWeb = !empty($in['solo_web']);

    $where = "IFNULL(TRIM(Localidad), '') <> ''";
    $params = [];
    $types = '';

    if ($texto !== '') {
        if (preg_match('/^\d{4,5}$/', $texto)) {
            $where .= " AND Cp = ?";
            $params[] = $texto;
            $types .= 's';
        } else {
            $where .= " AND Localidad LIKE ?";
            $params[] = '%' . $texto . '%';
            $types .= 's';
        }
    }

    if ($soloWeb) {
        $where .= " AND Web = 1";
    }

    $sql = "SELECT Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida
            FROM Localidades
            WHERE $where
            ORDER BY Provincia ASC, Localidad ASC
            LIMIT 50";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return ['error' => 'Error preparando la búsqueda de localidades.'];
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $localidades = [];
    while ($row = $res->fetch_assoc()) {
        $localidades[] = [
            'localidad' => $row['Localidad'],
            'provincia' => $row['Provincia'],
            'codigo_postal' => $row['Cp'],
            'recorrido' => $row['Recorrido'],
            'km' => $row['Km'],
            'dia_salida' => $row['DiaSalida'],
            'habilitada_web' => (int)$row['Web'] === 1,
        ];
    }
    $stmt->close();

    if (!$localidades) {
        return ['encontradas' => 0, 'mensaje' => 'No encontré localidades que coincidan.'];
    }

    return ['encontradas' => count($localidades), 'localidades' => $localidades];
}

function iaResumenLogisticaMes(mysqli $mysqli, int $nivel): array
{
    $inicioMes = date('Y-m-01');
    $finMes = date('Y-m-t');

    $res = $mysqli->query("
        SELECT COUNT(*) AS total FROM Logistica
        WHERE Eliminado = 0 AND Rendicion = 0 AND IFNULL(Costo_rendicion, 0) > 0
    ");
    $rendicionesPendientes = (int)($res ? $res->fetch_assoc()['total'] : 0);

    $resultado = [
        'rendiciones_controladas_pendientes_de_facturar' => $rendicionesPendientes,
        'periodo_facturacion' => "$inicioMes a $finMes",
    ];

    if ($nivel === 1) {
        $stmt = $mysqli->prepare("
            SELECT SUM(IFNULL(Debe, 0)) AS total
            FROM TransClientes
            WHERE Eliminado = 0 AND Facturado = 1 AND Fecha >= ? AND Fecha <= ?
        ");
        $stmt->bind_param('ss', $inicioMes, $finMes);
        $stmt->execute();
        $total = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();
        $resultado['facturado_en_el_mes'] = dinero($total);
    } else {
        $resultado['facturado_en_el_mes'] = 'No disponible para tu nivel de acceso.';
    }

    return $resultado;
}
