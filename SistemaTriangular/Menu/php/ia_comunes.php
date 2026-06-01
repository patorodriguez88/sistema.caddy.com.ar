<?php
function normalizarDB($texto)
{
    return str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', '.', ','],
        ['a', 'e', 'i', 'o', 'u', 'n', '', ''],
        mb_strtolower(trim($texto), 'UTF-8')
    );
}
function logIA($mysqli, $data)
{
    $stmt = $mysqli->prepare("
        INSERT INTO ia_logs (
            pregunta,
            respuesta,
            success,
            modulo,
            usuario,
            fecha,
            tiempo_ms
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) return;

    $pregunta = $data['pregunta'] ?? '';
    $respuesta = $data['respuesta'] ?? '';
    $success = isset($data['success']) ? (int)$data['success'] : 0;
    $modulo = $data['modulo'] ?? '';
    $usuario = $_SESSION['Usuario'] ?? 'sistema';
    $fecha = date('Y-m-d H:i:s');
    $tiempo = $data['tiempo'] ?? 0;

    $stmt->bind_param(
        "ssisssi",
        $pregunta,
        $respuesta,
        $success,
        $modulo,
        $usuario,
        $fecha,
        $tiempo
    );

    $stmt->execute();
    $stmt->close();
}

function salir($arr)
{
    global $mysqli, $startTime;

    $tiempo = isset($startTime)
        ? round((microtime(true) - $startTime) * 1000)
        : 0;

    logIA($mysqli, [
        'pregunta' => $_POST['pregunta'] ?? '',
        'respuesta' => $arr['respuesta'] ?? ($arr['msg'] ?? ''),
        'success' => $arr['success'] ?? 0,
        'modulo' => detectarModulo($arr),
        'tiempo' => $tiempo
    ]);

    echo json_encode($arr);
    exit;
}
function detectarModulo($arr)
{
    $texto = strtolower(
        ($arr['respuesta'] ?? '') . ' ' .
            ($arr['detalle'] ?? '') . ' ' .
            ($_POST['pregunta'] ?? '')
    );

    if (strpos($texto, 'seguro') !== false || strpos($texto, 'declarado') !== false) return 'seguro';
    if (strpos($texto, 'venta') !== false || strpos($texto, 'facturacion') !== false || strpos($texto, 'cliente') !== false || strpos($texto, 'envio') !== false || strpos($texto, 'paquete') !== false) return 'ventas';
    if (strpos($texto, 'seguimiento') !== false || strpos($texto, 'codigo') !== false) return 'seguimiento';
    if (strpos($texto, 'logistica') !== false || strpos($texto, 'rendicion') !== false || strpos($texto, 'recorrido') !== false) return 'logistica';
    if (strpos($texto, 'tarifa') !== false || strpos($texto, 'producto') !== false) return 'productos';

    return 'general';
}

function normalizarSinonimos($q)
{
    $buscar = [
        // Paquetes / envíos
        'encomiendas', 'encomienda',
        'bultos',       'bulto',
        'pedidos',      'pedido',
        'despachos',    'despacho',
        // Repartidores
        'choferes',      'chofer',
        'mensajeros',    'mensajero',
        'conductores',   'conductor',
        'distribuidores','distribuidor',
        'motoristas',    'motorista',
        // Acciones de entrega
        'repartidos',   'distribuidos',
        'no llego',     'no llegaron',
        // Localidades
        'ciudades',     'ciudad',
        'municipios',   'municipio',
        'zonas',        'zona',
        // Precios / tarifas
        'cuesta',       'cuanto cuesta',
        'cobran',
        'costo',        'costos',
        'importe',      'importes',
        // Ventas / facturación
        'ingresos',     'cobros',
        'monto facturado',
    ];

    $reemplazar = [
        'paquetes', 'paquete',
        'paquetes', 'paquete',
        'paquetes', 'paquete',
        'paquetes', 'paquete',
        'repartidores', 'repartidor',
        'repartidores', 'repartidor',
        'repartidores', 'repartidor',
        'repartidores', 'repartidor',
        'repartidores', 'repartidor',
        'entregados',   'entregados',
        'no entreg',    'no entregaron',
        'localidades',  'localidad',
        'localidades',  'localidad',
        'localidades',  'localidad',
        'sale',         'cuanto sale',
        'sale',
        'precio',       'precios',
        'valor',        'valores',
        'ventas',       'ventas',
        'facturacion',
    ];

    return str_replace($buscar, $reemplazar, $q);
}

function normalizarTexto($texto)
{
    return str_replace(
        [
            'á',
            'é',
            'í',
            'ó',
            'ú',
            'ñ',
            '¿',
            '?',
            '.',
            ',',
            ';',
            ':',
            '"',
            "'",
            '“',
            '”',
            '‘',
            '’'
        ],
        [
            'a',
            'e',
            'i',
            'o',
            'u',
            'n',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        mb_strtolower(trim($texto), 'UTF-8')
    );
}

function contieneAlguna($texto, $palabras)
{
    foreach ($palabras as $p) {
        if (strpos($texto, $p) !== false) return true;
    }
    return false;
}

function contar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (int)$row['total'] : 0;
}

function sumar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (float)$row['total'] : 0;
}

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

function detectarFechaConsulta($q)
{
    $hoy = date('Y-m-d');

    if (strpos($q, 'hoy') !== false) {
        return [$hoy, 'hoy'];
    }

    if (strpos($q, 'ayer') !== false) {
        return [date('Y-m-d', strtotime('-1 day')), 'ayer'];
    }

    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $q, $m)) {
        $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $anio = $m[3];

        return ["$anio-$mes-$dia", "$dia/$mes/$anio"];
    }

    $dias = [
        'lunes' => 'monday',
        'martes' => 'tuesday',
        'miercoles' => 'wednesday',
        'jueves' => 'thursday',
        'viernes' => 'friday',
        'sabado' => 'saturday',
        'domingo' => 'sunday'
    ];

    foreach ($dias as $es => $en) {
        if (strpos($q, $es . ' pasado') !== false) {
            return [date('Y-m-d', strtotime("last $en")), $es . ' pasado'];
        }

        if (strpos($q, $es) !== false) {
            if (strtolower(date('l')) === $en) {
                return [$hoy, $es];
            }

            return [date('Y-m-d', strtotime("last $en")), $es];
        }
    }

    return [$hoy, 'hoy'];
}

function detectarPeriodoConsulta($q)
{
    if (strpos($q, 'este mes') !== false || strpos($q, 'mes actual') !== false) {
        return [date('Y-m-01'), date('Y-m-t'), 'este mes'];
    }

    if (strpos($q, 'mes pasado') !== false) {
        return [
            date('Y-m-01', strtotime('first day of last month')),
            date('Y-m-t', strtotime('last day of last month')),
            'el mes pasado'
        ];
    }

    if (strpos($q, 'esta semana') !== false) {
        return [
            date('Y-m-d', strtotime('monday this week')),
            date('Y-m-d', strtotime('sunday this week')),
            'esta semana'
        ];
    }

    if (strpos($q, 'semana pasada') !== false) {
        return [
            date('Y-m-d', strtotime('monday last week')),
            date('Y-m-d', strtotime('sunday last week')),
            'la semana pasada'
        ];
    }

    // Meses por nombre: "abril", "abril 2025", etc.
    $meses = [
        'enero' => '01',
        'febrero' => '02',
        'marzo' => '03',
        'abril' => '04',
        'mayo' => '05',
        'junio' => '06',
        'julio' => '07',
        'agosto' => '08',
        'septiembre' => '09',
        'octubre' => '10',
        'noviembre' => '11',
        'diciembre' => '12'
    ];

    if (preg_match('/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s*(\d{4})?/i', $q, $m)) {
        $mes = strtolower($m[1]);
        $anio = isset($m[2]) && $m[2] !== '' ? $m[2] : date('Y');

        $desde = $anio . '-' . $meses[$mes] . '-01';
        $hasta = date('Y-m-t', strtotime($desde));

        return [$desde, $hasta, ucfirst($mes) . ' ' . $anio];
    }

    list($fecha, $texto) = detectarFechaConsulta($q);

    return [$fecha, $fecha, $texto];
}
