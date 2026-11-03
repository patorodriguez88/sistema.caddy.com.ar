<?php
date_default_timezone_set('America/Argentina/Cordoba');
/** DEBUG: capturar errores fatales como JSON (quitar en prod) */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// ✅ conexión disponible para TODOS los handlers
if (!isset($mysqli)) {
    include_once '../../../Conexion/Conexioni.php';
}
require_once __DIR__ . '/wepoint_lib.php'; // ID_WEPOINT, conexión con Wepoint y armado del aviso

/** ===========================================================
 * PRECHECK EGRESO: cuenta cuántos códigos de la orden tienen id_wepoint (id_bulto) y devuelve el detalle
 * POST: PrecheckEgreso=1, NumerodeOrden
 * Resp: { ok:true, total:int, listos:int, pendientes:int, recorrido:int|null, fecha:string, no_referencia:string, data:{listos:[{codigo,id_bulto}], pendientes:[codigo,...]} }
 * =========================================================== */
if (isset($_POST['PrecheckEgreso']) && (int)$_POST['PrecheckEgreso'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    $numeroOrden = isset($_POST['NumerodeOrden']) ? (int)$_POST['NumerodeOrden'] : 0;
    if ($numeroOrden <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No hay conexión a base de datos ($mysqli)']);
        exit;
    }

    // Traer recorrido (si existe) y fecha de logística como sugerencias
    $recorrido = null;
    $fechaORD = date('Y-m-d');
    if ($stR = $mysqli->prepare("SELECT Recorrido, Fecha FROM Logistica WHERE Eliminado=0 AND NumerodeOrden=? LIMIT 1")) {
        $stR->bind_param('i', $numeroOrden);
        if ($stR->execute()) {
            $rR = $stR->get_result()->fetch_assoc();
            if ($rR) {
                $recorrido = isset($rR['Recorrido']) ? (int)$rR['Recorrido'] : null;
                $fechaORD = !empty($rR['Fecha']) ? (string)$rR['Fecha'] : $fechaORD;
            }
        }
        $stR->close();
    }

    // 1) Bases + cantidades esperadas (por pieza)
    $bases = []; // base => cantidad esperada total (sumada si el mismo base aparece varias veces)
    $sqlB = "SELECT 
                tc.CodigoSeguimiento AS base,
                CASE 
                    WHEN COALESCE(tc.Cantidad,1) > 0 THEN COALESCE(tc.Cantidad,1)
                    ELSE 1
                END AS cant
             FROM HojaDeRuta hr
             JOIN TransClientes tc ON tc.id = hr.idTransClientes
             WHERE hr.NumerodeOrden = ? AND hr.Eliminado = 0";

    if ($st = $mysqli->prepare($sqlB)) {
        $st->bind_param('i', $numeroOrden);
        if ($st->execute()) {
            $res = $st->get_result();
            while ($r = $res->fetch_assoc()) {
                $b = (string)($r['base'] ?? '');
                $c = (int)($r['cant'] ?? 0);
                if ($b === '') continue;
                if (!isset($bases[$b])) $bases[$b] = 0;
                $bases[$b] += max(1, $c);
            }
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => $st->error]);
            $st->close();
            exit;
        }
        $st->close();
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $mysqli->error]);
        exit;
    }

    if (empty($bases)) {
        echo json_encode([
            'ok' => true,
            'total' => 0,
            'listos' => 0,
            'pendientes' => 0,
            'recorrido' => $recorrido,
            'fecha' => $fechaORD,
            'no_referencia' => (string)$numeroOrden,
            'data' => ['listos' => [], 'pendientes' => []]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2) Traer ENVIADOS por base desde wepoint_api (tipo IN)
    $placeholders = implode(',', array_fill(0, count($bases), '?'));
    $types = str_repeat('s', count($bases));
    $params = array_keys($bases);

    $sqlW = "SELECT CodigoSeguimiento, CodigoSeguimiento_enviado, id_wepoint,Time
             FROM wepoint_api
             WHERE tipo='IN' AND CodigoSeguimiento IN ($placeholders)";
    $enviadosMap = []; // base => set(codigo_enviado => ['id_wepoint'=>int,'time'=>string|null])
    if ($stW = $mysqli->prepare($sqlW)) {
        $stW->bind_param($types, ...$params);
        if ($stW->execute()) {
            $resW = $stW->get_result();
            while ($rw = $resW->fetch_assoc()) {
                $base  = (string)$rw['CodigoSeguimiento'];
                $env   = (string)$rw['CodigoSeguimiento_enviado']; // puede ser base o base_i
                $idw   = (int)($rw['id_wepoint'] ?? 0);
                $time  = isset($rw['Time']) ? (string)$rw['Time'] : null;
                if ($base === '' || $env === '' || $idw <= 0) continue;
                if (!isset($enviadosMap[$base])) $enviadosMap[$base] = [];
                $enviadosMap[$base][$env] = [
                    'id_wepoint' => $idw,
                    'time'       => $time,
                ];
            }
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => $stW->error]);
            $stW->close();
            exit;
        }
        $stW->close();
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $mysqli->error]);
        exit;
    }

    // 3) Construir listas de "listos" (con id_bulto) y "pendientes" a nivel pieza
    $listos = [];     // [{codigo, id_bulto, time}]
    $pend   = [];     // [codigo]
    $totalPiezas = 0;

    foreach ($bases as $base => $cant) {
        if ($cant <= 1) {
            $piezaCodigo = $base;
            $envInfo = isset($enviadosMap[$base][$piezaCodigo]) ? $enviadosMap[$base][$piezaCodigo] : null;
            $idw  = $envInfo && isset($envInfo['id_wepoint']) ? (int)$envInfo['id_wepoint'] : 0;
            $time = $envInfo && isset($envInfo['time']) ? (string)$envInfo['time'] : null;
            if ($idw > 0) {
                $listos[] = ['codigo' => $piezaCodigo, 'id_bulto' => $idw, 'time' => $time];
            } else {
                $pend[] = $piezaCodigo;
            }
            $totalPiezas += 1;
        } else {
            for ($i = 1; $i <= $cant; $i++) {
                $piezaCodigo = $base . '_' . $i;
                $envInfo = isset($enviadosMap[$base][$piezaCodigo]) ? $enviadosMap[$base][$piezaCodigo] : null;
                $idw  = $envInfo && isset($envInfo['id_wepoint']) ? (int)$envInfo['id_wepoint'] : 0;
                $time = $envInfo && isset($envInfo['time']) ? (string)$envInfo['time'] : null;
                if ($idw > 0) {
                    $listos[] = ['codigo' => $piezaCodigo, 'id_bulto' => $idw, 'time' => $time];
                } else {
                    $pend[] = $piezaCodigo;
                }
                $totalPiezas += 1;
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'total' => $totalPiezas,
        'listos' => count($listos),
        'pendientes' => count($pend),
        'recorrido' => $recorrido,
        'fecha' => $fechaORD,
        'no_referencia' => (string)$numeroOrden,
        'data' => [
            'listos' => $listos,
            'pendientes' => $pend
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


// === Handler: Detalle pieza a pieza para egreso (por Número de Orden) ===
if (isset($_POST['DetalleEgresoPorOrden']) && (int)$_POST['DetalleEgresoPorOrden'] === 1) {
    header('Content-Type: application/json; charset=utf-8');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Token para consultar estado en WePoint
    $token = wepoint_token();

    $orden = isset($_POST['NumerodeOrden']) ? (int)$_POST['NumerodeOrden'] : 0;
    if ($orden <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No hay conexión a base de datos ($mysqli)']);
        exit;
    }

    // 1) Bases + cantidades
    $bases = [];
    $sqlB = "SELECT tc.CodigoSeguimiento AS base,
                    CASE WHEN COALESCE(tc.Cantidad,1) > 0 THEN COALESCE(tc.Cantidad,1) ELSE 1 END AS cant
             FROM HojaDeRuta hr
             JOIN TransClientes tc ON tc.id = hr.idTransClientes
             WHERE hr.NumerodeOrden = ? AND hr.Eliminado = 0";
    if ($st = $mysqli->prepare($sqlB)) {
        $st->bind_param('i', $orden);
        $st->execute();
        $res = $st->get_result();
        while ($r = $res->fetch_assoc()) {
            $b = (string)$r['base'];
            $c = (int)$r['cant'];
            if ($b === '') continue;
            if (!isset($bases[$b])) $bases[$b] = 0;
            $bases[$b] += max(1, $c);
        }
        $st->close();
    }

    if (empty($bases)) {
        echo json_encode(['ok' => true, 'total' => 0, 'listos' => 0, 'pendientes' => 0, 'data' => ['items' => []]]);
        exit;
    }

    // 2) Traer enviados (tipo IN) para esos bases
    $placeholders = implode(',', array_fill(0, count($bases), '?'));
    $types = str_repeat('s', count($bases));
    $params = array_keys($bases);

    $sqlW = "SELECT CodigoSeguimiento, CodigoSeguimiento_enviado, id_wepoint,Time
             FROM wepoint_api
             WHERE tipo='IN' AND CodigoSeguimiento IN ($placeholders)";
    $enviadosSet = []; // codigo_enviado => ['id_wepoint'=>int,'time'=>string|null]
    if ($st = $mysqli->prepare($sqlW)) {
        $st->bind_param($types, ...$params);
        $st->execute();
        $res = $st->get_result();
        while ($r = $res->fetch_assoc()) {
            $enviado = (string)$r['CodigoSeguimiento_enviado'];
            $idw     = (int)($r['id_wepoint'] ?? 0);
            $time    = isset($r['Time']) ? (string)$r['Time'] : null;
            if ($enviado !== '' && $idw > 0) {
                $enviadosSet[$enviado] = [
                    'id_wepoint' => $idw,
                    'time'       => $time,
                ];
            }
        }
        $st->close();
    }

    // 3) Generar piezas esperadas y clasificar
    $items = [];
    $total = 0;
    $ok = 0;
    foreach ($bases as $base => $cant) {
        if ($cant <= 1) {
            $codigoPieza = $base;
            $envInfo = $enviadosSet[$codigoPieza] ?? null;
            $idw  = $envInfo && isset($envInfo['id_wepoint']) ? (int)$envInfo['id_wepoint'] : 0;
            $time = $envInfo && isset($envInfo['time']) ? (string)$envInfo['time'] : null;

            // Estado local por defecto
            $estadoLocal = $idw ? 'ENVIADO' : 'PENDIENTE';
            $estadoWepoint = null;

            if ($idw > 0 && $token !== '') {
                list($okApi, $estadoApi, $errApi, $rawApi) = wepoint_get_bulto_estado($token, $idw);
                if ($okApi && $estadoApi) {
                    $estadoWepoint = $estadoApi;

                    // Mapear a tu lógica interna
                    if (strcasecmp($estadoApi, 'Pendiente') === 0) {
                        $estadoLocal = 'IN OK';
                    } else {
                        $estadoLocal = $estadoApi; // Recibido, etc.
                    }

                    // 🔁 ACTUALIZAR TABLA wepoint_api CON EL NUEVO ESTADO
                    if ($upd = $mysqli->prepare("UPDATE wepoint_api 
                                             SET Estado = ?, Time = ? 
                                             WHERE id_wepoint = ? ")) {
                        $now = date('Y-m-d H:i:s');
                        $upd->bind_param('ssi', $estadoLocal, $now, $idw);
                        $upd->execute(); // si falla, no cortamos el flujo
                        $upd->close();
                    }
                }
            }

            $items[] = [
                'base' => $base,
                'pieza' => 1,
                'codigo_enviado' => $codigoPieza,
                'time' => $time,
                'id_wepoint' => $idw,
                'estado' => $estadoLocal,
                'estado_wepoint' => $estadoWepoint,
            ];
            $total += 1;
            if ($idw) $ok += 1;
        } else {

            for ($i = 1; $i <= $cant; $i++) {
                $codigoPieza = $base . '_' . $i;
                $envInfo = $enviadosSet[$codigoPieza] ?? null;
                $idw  = $envInfo && isset($envInfo['id_wepoint']) ? (int)$envInfo['id_wepoint'] : 0;
                $time = $envInfo && isset($envInfo['time']) ? (string)$envInfo['time'] : null;

                $estadoLocal = $idw ? 'ENVIADO' : 'PENDIENTE';
                $estadoWepoint = null;

                if ($idw > 0 && $token !== '') {
                    list($okApi, $estadoApi, $errApi, $rawApi) = wepoint_get_bulto_estado($token, $idw);
                    if ($okApi && $estadoApi) {
                        $estadoWepoint = $estadoApi;
                        if (strcasecmp($estadoApi, 'Pendiente') === 0) {
                            $estadoLocal = 'IN OK';
                        } else {
                            $estadoLocal = $estadoApi;
                        }

                        if ($upd = $mysqli->prepare("UPDATE wepoint_api 
                                             SET Estado = ?, Time = ? 
                                             WHERE id_wepoint = ? AND tipo = 'IN'")) {
                            $now = date('Y-m-d H:i:s');
                            $upd->bind_param('ssi', $estadoLocal, $now, $idw);
                            $upd->execute();
                            $upd->close();
                        }
                    }
                }

                $items[] = [
                    'base' => $base,
                    'pieza' => $i,
                    'codigo_enviado' => $codigoPieza,
                    'time' => $time,
                    'id_wepoint' => $idw,
                    'estado' => $estadoLocal,
                    'estado_wepoint' => $estadoWepoint,
                ];
                $total += 1;
                if ($idw) $ok += 1;
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'total' => $total,
        'listos' => $ok,
        'pendientes' => $total - $ok,
        'data' => ['items' => $items]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper: consulta estado de un bulto en WePoint por id_wepoint
function wepoint_get_bulto_estado(string $token, int $idWe): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => wepoint_url('/api/v2/bultos/' . $idWe),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: ' . $token,
        ],
    ]);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return [false, null, 'cURL error: ' . $err, $resp];
    }

    $json = json_decode($resp, true);
    if (!is_array($json) || $http < 200 || $http >= 300 || empty($json['success'])) {
        return [false, null, 'HTTP ' . $http, $resp];
    }

    $estado = $json['data']['estado'] ?? null;

    return [true, $estado, null, $resp];
}



/** ===========================================================
 * CREAR EGRESO EN WEPOINT: envía id_bulto (id_wepoint) para generar Orden de Egreso
 * POST: CrearEgreso=1, token, NumerodeOrden, ids_bulto[] (opcional), fecha_egreso?, observaciones?, no_referencia?, recorrido?
 * =========================================================== */
if (isset($_POST['CrearEgreso']) && (int)$_POST['CrearEgreso'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    // Token
    $token = wepoint_token();
    if ($token === '') {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Falta configurar Conexion/wepoint_config.php']);
        exit;
    }

    $numeroOrden = isset($_POST['NumerodeOrden']) ? (int)$_POST['NumerodeOrden'] : 0;
    if ($numeroOrden <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }

    // ids_bulto puede venir como array o JSON string
    $ids_bulto = [];
    if (isset($_POST['ids_bulto'])) {
        if (is_array($_POST['ids_bulto'])) {
            $ids_bulto = array_values(array_filter(array_map('intval', $_POST['ids_bulto']), function ($v) {
                return $v > 0;
            }));
        } else {
            $tmp = json_decode((string)$_POST['ids_bulto'], true);
            if (is_array($tmp)) {
                $ids_bulto = array_values(array_filter(array_map('intval', $tmp), function ($v) {
                    return $v > 0;
                }));
            }
        }
    }

    // Si no mandaron ids_bulto, los resolvemos desde la DB (solo listos)
    if (empty($ids_bulto)) {
        $sqlIds = "SELECT 
                        MAX(CASE WHEN wa.tipo='IN' THEN wa.id_wepoint END) AS id_bulto
                   FROM HojaDeRuta hr
                   INNER JOIN TransClientes tc ON tc.id = hr.idTransClientes
                   LEFT JOIN wepoint_api wa ON wa.CodigoSeguimiento = tc.CodigoSeguimiento
                   WHERE hr.NumerodeOrden = ?
                     AND hr.Eliminado = 0
                   GROUP BY tc.CodigoSeguimiento
                   HAVING id_bulto IS NOT NULL";
        if ($stI = $mysqli->prepare($sqlIds)) {
            $stI->bind_param('i', $numeroOrden);
            if ($stI->execute()) {
                $resI = $stI->get_result();
                while ($ri = $resI->fetch_assoc()) {
                    $v = (int)($ri['id_bulto'] ?? 0);
                    if ($v > 0) $ids_bulto[] = $v;
                }
            }
            $stI->close();
        }
    }

    if (empty($ids_bulto)) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'message' => 'No hay bultos listos (id_wepoint) para egreso en esta orden']);
        exit;
    }

    // Defaults
    $fecha_egreso  = isset($_POST['fecha_egreso']) ? (string)$_POST['fecha_egreso'] : date('Y-m-d');
    $observaciones = isset($_POST['observaciones']) ? (string)$_POST['observaciones'] : 'Sin Observaciones';
    $no_referencia = isset($_POST['no_referencia']) ? (string)$_POST['no_referencia'] : (string)$numeroOrden;
    $recorrido     = isset($_POST['recorrido']) ? (int)$_POST['recorrido'] : null;

    // Si no vino recorrido, intentamos obtenerlo de Logistica
    if ($recorrido === null) {
        if ($stR = $mysqli->prepare("SELECT Recorrido FROM Logistica WHERE Eliminado=0 AND NumerodeOrden=? LIMIT 1")) {
            $stR->bind_param('i', $numeroOrden);
            if ($stR->execute()) {
                $rR = $stR->get_result()->fetch_assoc();
                if ($rR && isset($rR['Recorrido'])) $recorrido = (int)$rR['Recorrido'];
            }
            $stR->close();
        }
    }
    if ($recorrido === null) $recorrido = 0; // si tu API tolera 0/ null

    // Construir payload
    $detalles = [];
    foreach ($ids_bulto as $idb) {
        $detalles[] = ['id_bulto' => (int)$idb];
    }

    $payload = [
        'fecha_egreso' => $fecha_egreso,
        'observaciones' => $observaciones,
        'no_referencia' => $no_referencia,
        'recorrido' => $recorrido,
        'detalles' => $detalles
    ];

    // cURL al endpoint v2 egresos/bultos
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => wepoint_url('/api/v2/egresos/bultos'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $token
        ],
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    // Opcional: registrar salida en wepoint_api como OUT
    $decoded = json_decode($resp, true);
    $nro_egreso = $decoded['data']['nro_egreso'] ?? ($decoded['nro_egreso'] ?? null);

    if ($http >= 200 && $http < 300) {
        // Insertar rastro por cada id_bulto enviado
        $now     = date('Y-m-d H:i:s');
        $Usuario = $_SESSION['Usuario'] ?? 'Sistema';
        $sqlIns = "INSERT INTO wepoint_api (`Time`, `no_referencia`, `nro_orden_egreso_bulto`, `CodigoSeguimiento`, `CodigoSeguimiento_enviado`, `id_wepoint`, `Usuario`, `Estado`, `tipo`, `http_code`)
                   VALUES (?,?,?,?,?,?,?,?,?,?)";
        if ($ins = $mysqli->prepare($sqlIns)) {
            foreach ($ids_bulto as $idb) {
                $vacio = '';
                $estado = 'OUT OK';
                $tipo = 'OUT';
                $httpCode = (int)$http;
                $ins->bind_param('ssssssissi', $now, $no_referencia, $nro_egreso, $vacio, $vacio, $idb, $Usuario, $estado, $tipo, $httpCode);
                $ins->execute(); // si falla, lo ignoramos para no cortar la respuesta OK
            }
            $ins->close();
        }
    }

    http_response_code($http ?: 502);
    echo json_encode([
        'ok' => ($http >= 200 && $http < 300),
        'http_code' => $http,
        'curl_error' => $err,
        'sent' => $payload,
        'wepoint_raw' => $resp
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'ok' => false,
            'message' => 'Fatal error',
            'error' => $e['message'] ?? '',
            'file' => $e['file'] ?? '',
            'line' => $e['line'] ?? 0,
        ], JSON_UNESCAPED_UNICODE);
    }
});
// ==========================================================================
// PANEL DE WEPOINT (Logística > Wepoint)
// Lo que se escanea en CrossDocking (ingreso) y lo que carga el chofer en la app de reparto
// (salida) va a la cola wepoint_cola y se le manda a Wepoint sin frenar el escaneo. Acá se ve lo
// que Wepoint todavía no confirmó, lo que tiene en el depósito y lo que salió.
// ==========================================================================

// Manda la cola a Wepoint (lo llama CrossDocking después de cada escaneo y el panel cada tanto).
// Suelta la sesión antes: si no, el próximo escaneo de la misma persona esperaría a que termine.
if (isset($_POST['ProcesarCola'])) {
    header('Content-Type: application/json; charset=utf-8');
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    echo json_encode(['ok' => true] + wepoint_procesar_cola($mysqli));
    exit;
}

// Lo que Wepoint todavía no confirmó, y cuánto se confirmó hoy
if (isset($_POST['ColaWepoint'])) {
    header('Content-Type: application/json; charset=utf-8');
    wepoint_cola_tabla($mysqli);
    $pendientes = $mysqli->query("SELECT c.id, c.tipo, c.codigo, c.serie, c.fecha, c.recorrido, c.chofer, c.estado,
               c.intentos, c.proximo_intento, c.ultimo_error, c.creado_en, TC.RazonSocial AS Origen, TC.ClienteDestino AS Destino
        FROM wepoint_cola c LEFT JOIN TransClientes TC ON TC.id = c.id_tc
        WHERE c.estado IN ('pendiente', 'error')
        ORDER BY c.creado_en DESC LIMIT 1000")->fetch_all(MYSQLI_ASSOC);
    $hoy = $mysqli->query("SELECT tipo, COUNT(*) AS n FROM wepoint_cola WHERE estado = 'ok' AND enviado_en >= CURDATE() GROUP BY tipo")
        ->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok' => true, 'activo' => wepoint_activo(), 'data' => $pendientes, 'confirmados_hoy' => array_column($hoy, 'n', 'tipo')], JSON_UNESCAPED_UNICODE);
    exit;
}

// Reintentar ya (todo lo que falló, o los ids indicados)
if (isset($_POST['ReintentarCola'])) {
    header('Content-Type: application/json; charset=utf-8');
    wepoint_cola_tabla($mysqli);
    $ids = array_filter(array_map('intval', json_decode((string)($_POST['ids'] ?? '[]'), true) ?: []));
    $donde = $ids ? 'id IN (' . implode(',', $ids) . ')' : "estado = 'error'";
    $mysqli->query("UPDATE wepoint_cola SET proximo_intento = NOW() WHERE estado IN ('pendiente', 'error') AND $donde");
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    echo json_encode(['ok' => true] + wepoint_procesar_cola($mysqli));
    exit;
}

// Lo que Wepoint tiene en el depósito (vista=en_deposito) o lo que salió entre fechas (vista=salieron)
if (isset($_POST['BultosWepoint'])) {
    header('Content-Type: application/json; charset=utf-8');
    $params = array_filter([
        'vista' => ($_POST['vista'] ?? '') === 'salieron' ? 'salieron' : 'en_deposito',
        'desde' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['desde'] ?? '')) ? $_POST['desde'] : null,
        'hasta' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['hasta'] ?? '')) ? $_POST['hasta'] : null,
    ]);
    [$http, $err, $resp] = wepoint_request('GET', '/api/v2/integraciones/caddy/bultos?' . http_build_query($params), null, 20);
    $json = json_decode((string)$resp, true);
    if ($http !== 200 || !is_array($json)) {
        http_response_code(502);
        echo json_encode(['ok' => false, 'message' => $http ? "Wepoint respondió HTTP $http" : ($err ?: 'Wepoint no responde')]);
        exit;
    }
    echo json_encode(['ok' => true] + ($json['data'] ?? []), JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_POST['Colectas_out']) && (int)$_POST['Colectas_out'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    $rows = [];

    // --- MODO LISTADO: todos los recorridos con cantidad ---
    $sql = "SELECT
            lg.NumerodeOrden,
            hdr.Recorrido,
            lg.Fecha,
            r.Nombre,
            lg.NombreChofer,
            COUNT(hdr.id) AS Cantidad
            FROM HojaDeRuta AS hdr
            JOIN Logistica AS lg
              ON lg.NumerodeOrden = hdr.NumerodeOrden
            LEFT JOIN Recorridos AS r
              ON r.Numero = hdr.Recorrido
            WHERE
              hdr.Estado = 'Abierto'
              AND hdr.Recorrido <> 0
              AND hdr.Eliminado = 0
              AND hdr.Devuelto = 0
              AND hdr.Seguimiento IS NOT NULL
              AND hdr.Seguimiento <> ''
            GROUP BY
              lg.NumerodeOrden, hdr.Recorrido, lg.Fecha, r.Nombre, lg.NombreChofer
            ORDER BY
              hdr.Recorrido";

    if ($res = $mysqli->query($sql)) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'Recorrido' => (string)$r['Recorrido'],
                'Cantidad'  => (int)$r['Cantidad'],
                'Fecha'     => $r['Fecha'],
                'Nombre'    => (string)$r['Nombre'],
                'Chofer'    => (string)$r['NombreChofer'],
                'NumerodeOrden' => (int)$r['NumerodeOrden']
            ];
        }
        $res->free();
        echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $mysqli->error, 'data' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// TODAS
if (isset($_POST['Todas']) && (int)$_POST['Todas'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    $rows = [];

    // Seleccioná columnas explícitas
    $sql = "SELECT  `Time` AS Fecha,`no_referencia`, `nro_orden_ingreso_bulto` AS NumerodeOrden, `CodigoSeguimiento_enviado`, `Usuario`, `id_wepoint`,`Estado` FROM wepoint_api ORDER BY id DESC";

    if ($res = $mysqli->query($sql)) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        $res->free();

        echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => $mysqli->error,
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// === Handler: Códigos por Número de Orden (con estado de envío) ===
if (isset($_POST['listar_codigos_por_orden']) && (int)$_POST['listar_codigos_por_orden'] === 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=utf-8');

    $numeroOrden = isset($_POST['NumerodeOrden']) ? (int)$_POST['NumerodeOrden'] : 0;
    if ($numeroOrden <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No hay conexión a base de datos ($mysqli)']);
        exit;
    }

    /**
     * Listamos cada CodigoSeguimiento de la orden y buscamos si existe un envío a warehouse
     * en wepoint_api. Usamos LEFT JOIN por CodigoSeguimiento base (no por nro_serie).
     */
    $sql = "SELECT 
            tc.CodigoSeguimiento AS codigo,
            MAX(CASE WHEN wa.tipo='IN' THEN wa.id_wepoint END) AS wepoint_id,
            MAX(CASE WHEN wa.tipo='IN' THEN wa.nro_orden_ingreso_bulto END) AS nro_oib,
            MAX(wa.Time) AS UltimoEnvio
            FROM HojaDeRuta hr
            INNER JOIN TransClientes tc ON tc.id = hr.idTransClientes
            LEFT JOIN wepoint_api wa ON wa.CodigoSeguimiento = tc.CodigoSeguimiento
            WHERE hr.NumerodeOrden = ?
            AND hr.Eliminado = 0
            GROUP BY tc.CodigoSeguimiento
            ORDER BY MIN(tc.id) ASC";

    $rows = [];
    if ($st = $mysqli->prepare($sql)) {
        $st->bind_param('i', $numeroOrden);
        if ($st->execute()) {
            $res = $st->get_result();
            while ($r = $res->fetch_assoc()) {
                $rows[] = [
                    'codigo'        => (string)$r['codigo'],
                    'wepoint_id'    => $r['wepoint_id'] ? (string)$r['wepoint_id'] : '',
                    'nro_oib'       => $r['nro_oib'] ? (string)$r['nro_oib'] : '',
                    'UltimoEnvio'   => $r['UltimoEnvio'] ? (string)$r['UltimoEnvio'] : null,
                    'observaciones' => '' // placeholder si querés sumar comentarios
                ];
            }
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => $st->error, 'data' => []]);
            $st->close();
            exit;
        }
        $st->close();
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $mysqli->error, 'data' => []]);
        exit;
    }

    echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

//RESUMEN

if (isset($_POST['ResumenWepoint']) && (int)$_POST['ResumenWepoint'] === 1) {
    header('Content-Type: application/json; charset=utf-8');
    $out = [
        'total_ingresos' => 0,
        'total_egresos' => 0,
        'pendientes_in_sin_out' => 0,
    ];

    // Total IN
    if ($q = $mysqli->query("SELECT COUNT(*) AS c FROM wepoint_api WHERE tipo='IN'")) {
        $r = $q->fetch_assoc();
        $out['total_ingresos'] = (int)($r['c'] ?? 0);
    }

    // Total OUT
    if ($q = $mysqli->query("SELECT COUNT(*) AS c FROM wepoint_api WHERE tipo='OUT'")) {
        $r = $q->fetch_assoc();
        $out['total_egresos'] = (int)($r['c'] ?? 0);
    }

    // Pendientes: IN que no tienen OUT para el mismo CodigoSeguimiento_enviado
    $sqlPend = "SELECT COUNT(*) AS c
                FROM wepoint_api wi
                WHERE wi.tipo='IN'
                  AND NOT EXISTS (
                    SELECT 1 FROM wepoint_api wo
                    WHERE wo.tipo='OUT'
                      AND wo.CodigoSeguimiento_enviado = wi.CodigoSeguimiento_enviado
                  )";
    if ($q = $mysqli->query($sqlPend)) {
        $r = $q->fetch_assoc();
        $out['pendientes_in_sin_out'] = (int)($r['c'] ?? 0);
    }

    echo json_encode($out);
    exit;
}
