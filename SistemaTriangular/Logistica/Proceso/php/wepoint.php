<?php
date_default_timezone_set('America/Argentina/Cordoba');
/** DEBUG: capturar errores fatales como JSON (quitar en prod) */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
const ID_WEPOINT = 18587; //ID DE WEPOINT
// ✅ conexión disponible para TODOS los handlers
if (!isset($mysqli)) {
    include_once '../../../Conexion/Conexioni.php';
}

//LOGIN
if (isset($_POST['login_wepoint'])) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sandbox-lv.wepoint.ar/api/auth/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
        "email": "prodriguez@caddy.com.ar",
        "password": "pato4986"
        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}


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
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    if ($token !== '' && stripos($token, 'Bearer ') !== 0) {
        $token = 'Bearer ' . $token;
    }

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
        CURLOPT_URL => 'https://sandbox-lv.wepoint.ar/api/v2/bultos/' . $idWe,
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
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    if ($token !== '' && stripos($token, 'Bearer ') !== 0) $token = 'Bearer ' . $token;
    if ($token === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta token']);
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
        CURLOPT_URL => 'https://sandbox-lv.wepoint.ar/api/v2/egresos/bultos',
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
function post_wepoint_oib($token, $payloadArr)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://sandbox-lv.wepoint.ar/api/orden-ingreso/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payloadArr, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $token,
        ],
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [$http, $err, $resp];
}
/**
 * 
 * Devuelve el id_proveedor de WePoint.
 * - Intenta con el “natural” si lo tenés (p.ej. mapeo propio). Si no, o si falla, cae al flujo pedido:
 *   HR.idTransClientes -> TransClientes.ingBrutosOrigen -> Clientes.wepoint_id (si hay)
 *   Si no hay, crea proveedor en WePoint con datos de Clientes y actualiza su wepoint_id.
 *
 * @return array [bool ok, int|0 wepointProveedorId, array|null debug]
 */
function resolver_wepoint_proveedor(mysqli $mysqli, string $token, array $hrRow)
{
    // 1) Con idTransClientes obtener ingBrutosOrigen (id de cliente origen)
    $ingBrutosOrigen = null;
    if (!empty($hrRow['idTransClientes'])) {
        if ($st = $mysqli->prepare("SELECT IngBrutosOrigen FROM TransClientes WHERE id = ? LIMIT 1")) {
            $st->bind_param('i', $hrRow['idTransClientes']);
            if ($st->execute()) {
                $res = $st->get_result()->fetch_assoc();
                $ingBrutosOrigen = $res['IngBrutosOrigen'] ?? null;
            }
            $st->close();
        }
    }
    if (empty($ingBrutosOrigen)) {
        return [false, 0, ['msg' => 'No se pudo resolver ingBrutosOrigen desde TransClientes', 'idTransClientes' => $hrRow['idTransClientes'] ?? null]];
    }

    // 2) Buscar en Clientes ese id
    if ($st = $mysqli->prepare("SELECT id, nombrecliente, Telefono, Mail, Direccion, wepoint_id FROM Clientes WHERE id = ? LIMIT 1")) {
        $st->bind_param('i', $ingBrutosOrigen);
        if ($st->execute()) {
            $cli = $st->get_result()->fetch_assoc();
        }
        $st->close();
    }
    if (empty($cli)) {
        return [false, 0, ['msg' => 'Cliente origen no existe en Clientes', 'cliente_id' => $ingBrutosOrigen]];
    }

    // 3) Si ya tiene wepoint_id ⇒ usarlo como proveedor
    $wpProvId = (int)($cli['wepoint_id'] ?? 0);
    if ($wpProvId > 0) {
        return [true, $wpProvId, ['source' => 'Clientes.wepoint_id']];
    }

    // 4) No tiene wepoint_id ⇒ crearlo como proveedor en WePoint
    list($ok, $newProvId, $raw, $sent) = wepoint_create_proveedor($token, $cli);
    if (!$ok || !$newProvId) {
        return [false, 0, [
            'msg' => 'No se pudo crear proveedor en WePoint',
            'wepoint_sent' => $sent,
            'wepoint_response' => $raw
        ]];
    }

    // 5) Guardar el id de proveedor en Clientes.wepoint_id (o en tu campo para proveedores)
    if ($upd = $mysqli->prepare('UPDATE Clientes SET wepoint_id = ? WHERE id = ? LIMIT 1')) {
        $upd->bind_param('ii', $newProvId, $cli['id']);
        $upd->execute();
        $upd->close();
    }

    return [true, (int)$newProvId, ['source' => 'created']];
}
function wepoint_create_proveedor(string $token, array $provData)
{
    // Normalización mínima y defaults
    $nombre    = trim((string)($provData['nombre'] ?? $provData['nombrecliente'] ?? 'Proveedor sin nombre'));
    $telefono  = trim((string)($provData['telefono'] ?? $provData['Telefono'] ?? ''));
    $direccion = trim((string)($provData['direccion'] ?? $provData['Direccion'] ?? ''));

    $rawEmail = $provData['email'] ?? $provData['Mail'] ?? '';
    // 2) Separar por coma, punto y coma o espacios usando regex
    $emails = preg_split('/[,\s;]+/', $rawEmail, -1, PREG_SPLIT_NO_EMPTY);
    // 3) Limpiar y quedarnos con el primer email válido
    $emailLimpio = '';
    if (is_array($emails)) {
        foreach ($emails as $e) {
            $e = trim($e);
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $emailLimpio = $e;
                break;
            }
        }
    }
    $body = [
        'nombre'    => $nombre,
        'telefono'  => $telefono,
        'email'     => $emailLimpio,
        'direccion' => $direccion,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://sandbox-lv.wepoint.ar/api/proveedores/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $token,
        ],
    ]);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return [false, "cURL error: $err", null, $body];
    }
    $json = json_decode($resp, true);

    if ($http >= 200 && $http < 300) {
        // según API: suele venir en data.id_proveedor
        $id = $json['data']['id_proveedor'] ?? $json['id_proveedor'] ?? null;
        if ($id) return [true, (int)$id, $json, $body];
        return [false, 'Respuesta sin id_proveedor', $json, $body];
    }

    return [false, "HTTP $http", $json, $body];
}

function wepoint_create_cliente(string $token, array $cli)
{
    $norm = static function ($v) {
        $s = trim((string)$v);
        return preg_replace('/\s+/', ' ', $s);
    };
    $normPhone = static function ($v) {
        return preg_replace('/\D+/', '', (string)$v);
    };
    $nonnull = static function ($v) {
        $s = trim((string)$v);
        return ($s === '') ? null : $s;
    };

    $nombre = $norm($cli['CL_Nombre'] ?? '');
    $dir    = $norm($cli['CL_Direccion'] ?? '');

    // 🔧 PROVINCIA con fallback robusto (Cliente -> HR -> default)
    $provRaw = $cli['CL_Provincia'] ?? $cli['Provincia'] ?? '';
    $prov    = $norm($provRaw);
    if ($prov === '') {
        $prov = 'Córdoba'; // default si sigue vacío
    }

    $ciudad = $norm($cli['CL_Ciudad'] ?? '');
    $cp     = $norm($cli['CL_CodigoPostal'] ?? '5000');
    $barrio = $norm($cli['CL_Barrio'] ?? '');
    $entre  = $norm($cli['CL_EntreCalles'] ?? '');

    $emailRaw = $cli['CL_Email'] ?? '';
    $email    = $nonnull($emailRaw);

    // Teléfono: Cliente -> HR -> dummy
    $telCli   = $normPhone($cli['CL_Telefono'] ?? '');
    $telHR    = $normPhone($cli['Celular']     ?? '');
    $telefono = $telCli !== '' ? $telCli : ($telHR !== '' ? $telHR : '0000000000');

    $body = [
        'nombre'        => $nombre,
        'telefono'      => $telefono,
        'email'         => $email,
        'direccion'     => $dir,
        'provincia'     => $prov,            // ✅ nunca vacío
        'ciudad'        => $ciudad,
        'codigo_postal' => $cp,
        'barrio'        => $barrio,
        'entre_calles'  => ($entre === '') ? null : $entre,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://sandbox-lv.wepoint.ar/api/clientes/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $token, // ojo con los dos puntos
        ],
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) return [false, "cURL error: $err", null, $body];

    $json = json_decode($resp, true);
    if ($http >= 200 && $http < 300) {
        $id = $json['id']
            ?? ($json['data']['id'] ?? null)
            ?? ($json['id_cliente'] ?? ($json['data']['id_cliente'] ?? null));
        if ($id) return [true, (int)$id, $json, $body];
        return [false, 'Respuesta sin id', $json, $body];
    }
    return [false, "HTTP $http", $json, $body];
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//COLECTAS
if (isset($_POST['Colectas']) && (int)$_POST['Colectas'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    $idWepoint = ID_WEPOINT;

    // Agrego TODAS las columnas que usás en el front
    $sql = "SELECT 
                Recorrido,
                SUM(Cantidad) as Cantidad,                                
                NumerodeOrden,
                Transportista
            FROM TransClientes
            WHERE idClienteDestino = ?
              AND Entregado = 0
              AND Eliminado = 0
              AND Devuelto = 0
              AND Haber= 0 
              AND (wepoint_id = 0 OR wepoint_id IS NULL)
              GROUP BY NumerodeOrden";

    $rows = [];
    if ($st = $mysqli->prepare($sql)) {
        $st->bind_param('i', $idWepoint);
        if ($st->execute()) {
            $res = $st->get_result();
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
        } else {
            echo json_encode(['data' => [], 'error' => $st->error]);
            $st->close();
            exit;
        }
        $st->close();
    } else {
        echo json_encode(['data' => [], 'error' => $mysqli->error]);
        exit;
    }

    // DataTables espera { data: [...] }
    echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

// ENVÍO DE ORDEN DE INGRESO DE BULTOS

if (isset($_POST['Ejecutar']) && (int)$_POST['Ejecutar'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    // 1) Token (normalizar con Bearer)
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    if ($token !== '' && stripos($token, 'Bearer ') !== 0) {
        $token = 'Bearer ' . $token;
    }
    if ($token === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta token para Authorization']);
        exit;
    }

    // 2) NumerodeOrden (acepta alias 'j')
    $numeroOrden = null;
    if (!empty($_POST['NumerodeOrden'])) {
        $numeroOrden = (int)$_POST['NumerodeOrden'];
    } elseif (!empty($_POST['j'])) {
        $numeroOrden = (int)$_POST['j'];
    }
    if (!$numeroOrden) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }

    // justo después de leer $numeroOrden / token...
    $excluirCodigos = [];
    if (isset($_POST['ExcluirCodigos'])) {
        // puede venir como JSON string o como array de inputs
        if (is_array($_POST['ExcluirCodigos'])) {
            $excluirCodigos = array_map('strval', $_POST['ExcluirCodigos']);
        } else {
            $tmp = json_decode((string)$_POST['ExcluirCodigos'], true);
            if (is_array($tmp)) $excluirCodigos = array_map('strval', $tmp);
        }
    }
    $exSet = array_flip($excluirCodigos);

    // 3) Defaults opcionales
    $no_referencia = isset($_POST['no_referencia']) ? (string)$_POST['no_referencia'] : '';
    $fecha         = isset($_POST['fecha'])         ? (string)$_POST['fecha']         : '';
    $notas         = isset($_POST['notas'])         ? (string)$_POST['notas']         : '';

    // 4) Conexión a DB
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No hay conexión a base de datos ($mysqli)']);
        exit;
    }


    // 5) Traer HojaDeRuta + Clientes
    $sql = "SELECT 
    HR.id, HR.Fecha, HR.Hora, HR.Recorrido, HR.Localizacion, HR.Ciudad, HR.Provincia, HR.Pais,
    HR.Cliente, HR.Titulo, HR.Observaciones, HR.Usuario, HR.Asignado, HR.Estado, HR.NumerodeOrden,
    HR.Posicion, HR.Seguimiento, HR.idCliente, HR.Celular, HR.TramoMapa, HR.Eliminado, HR.Avisado,
    HR.ImporteCobranza, HR.NumeroRepo, HR.KmO, HR.Tiempo, HR.idTransClientes, HR.TimeStamp, HR.Devuelto,
    HR.Servicio, HR.Posicion_retiro, HR.Hora_retiro,
    CL.wepoint_id,
    CL.nombrecliente AS CL_Nombre,
    CL.Telefono      AS CL_Telefono,
    CL.Mail          AS CL_Email,
    CL.Direccion     AS CL_Direccion,
    CL.Provincia     AS CL_Provincia,
    CL.Ciudad        AS CL_Ciudad,
    CL.CodigoPostal  AS CL_CodigoPostal,
    CL.Barrio        AS CL_Barrio,
    TC.Ancho  AS TC_Ancho,
    TC.Largo  AS TC_Largo,
    TC.Alto   AS TC_Alto,
    TC.Peso   AS TC_Peso, 
    TC.CodigoSeguimiento AS CodigoSeguimiento,
    TC.Flex AS TC_Flex,
    TC.Cantidad AS Cantidad
    FROM TransClientes AS TC
    JOIN HojaDeRuta AS HR ON HR.idTransClientes = TC.id
    JOIN Clientes   AS CL ON HR.idCliente = CL.id    
    WHERE 
    TC.idClienteDestino= ?
    AND TC.Eliminado = 0
    AND TC.Entregado = 0
    AND TC.Devuelto = 0
    AND TC.Haber = 0
    AND (CL.wepoint_id = 0 OR CL.wepoint_id IS NULL)
    AND TC.NumerodeOrden = ? ;";

    // $sql = "SELECT 
    // HR.id, HR.Fecha, HR.Hora, HR.Recorrido, HR.Localizacion, HR.Ciudad, HR.Provincia, HR.Pais,
    // HR.Cliente, HR.Titulo, HR.Observaciones, HR.Usuario, HR.Asignado, HR.Estado, HR.NumerodeOrden,
    // HR.Posicion, HR.Seguimiento, HR.idCliente, HR.Celular, HR.TramoMapa, HR.Eliminado, HR.Avisado,
    // HR.ImporteCobranza, HR.NumeroRepo, HR.KmO, HR.Tiempo, HR.idTransClientes, HR.TimeStamp, HR.Devuelto,
    // HR.Servicio, HR.Posicion_retiro, HR.Hora_retiro,
    // CL.wepoint_id,
    // CL.nombrecliente AS CL_Nombre,
    // CL.Telefono      AS CL_Telefono,
    // CL.Mail          AS CL_Email,
    // CL.Direccion     AS CL_Direccion,
    // CL.Provincia     AS CL_Provincia,
    // CL.Ciudad        AS CL_Ciudad,
    // CL.CodigoPostal  AS CL_CodigoPostal,
    // CL.Barrio        AS CL_Barrio,
    // TC.Ancho  AS TC_Ancho,
    // TC.Largo  AS TC_Largo,
    // TC.Alto   AS TC_Alto,
    // TC.Peso   AS TC_Peso, 
    // TC.CodigoSeguimiento AS CodigoSeguimiento,
    // TC.Flex AS TC_Flex,
    // TC.Cantidad AS Cantidad
    // FROM HojaDeRuta AS HR
    // JOIN Clientes   AS CL ON HR.idCliente = CL.id
    // LEFT JOIN TransClientes AS TC ON HR.idTransClientes = TC.id
    // WHERE HR.Eliminado = 0
    // AND HR.Estado   = 'Abierto'
    // AND HR.NumerodeOrden = ?";

    if (!$stmt = $mysqli->prepare($sql)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Error preparando consulta', 'error' => $mysqli->error]);
        exit;
    }
    $idWepoint = ID_WEPOINT;

    $stmt->bind_param('ii', $idWepoint, $numeroOrden);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Error ejecutando consulta', 'error' => $stmt->error]);
        $stmt->close();
        exit;
    }
    $res  = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();

    if (empty($rows)) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'No hay renglones en HojaDeRuta para esa orden (Abierto / Eliminado=0)']);
        exit;
    }

    // 6) Crear clientes faltantes en WePoint y actualizar wepoint_id en DB
    $clientesVistos = []; // idCliente => wepoint_id
    foreach ($rows as $r) {
        $idCli = (int)$r['idCliente'];
        if (isset($clientesVistos[$idCli])) continue;

        $actual = (int)($r['wepoint_id'] ?? 0);
        if ($actual > 0) {
            $clientesVistos[$idCli] = $actual;
            continue;
        }

        list($ok, $newId, $raw, $sent) = wepoint_create_cliente($token, $r);
        $rawArr = is_array($raw) ? $raw : json_decode((string)$raw, true);

        if ($ok) {
            // si vino null pero la API trae id en otro campo, levantarlo
            if ($newId === null && is_array($rawArr)) {
                $newId = $rawArr['data']['id_cliente'] ?? $rawArr['id_cliente'] ?? null;
            }

            if ($newId) {
                $clientesVistos[$idCli] = (int)$newId;

                if ($upd = $mysqli->prepare('UPDATE Clientes SET wepoint_id = ? WHERE id = ? LIMIT 1')) {
                    $upd->bind_param('ii', $clientesVistos[$idCli], $idCli);
                    $upd->execute();
                    $upd->close();
                }
            } else {
                // Éxito sin id explícito: seguir sin actualizar la tabla local
                // (si preferís abortar, reemplazá por un http_response_code(502)+exit)
                $clientesVistos[$idCli] = 0;
                // file_put_contents('wepoint_warn.log', json_encode($rawArr).PHP_EOL, FILE_APPEND);
            }
        } else {
            http_response_code(502);
            echo json_encode([
                'ok' => false,
                'message' => 'No se pudo crear el cliente en WePoint (API devolvió error)',
                'cliente_id' => $idCli,
                'wepoint_sent' => $sent,
                'wepoint_response' => $raw,
            ]);
            exit;
        }
    }

    // 7) Defaults finales
    // Intentar obtener el NombreChofer de la tabla Logistica para esta orden
    $nombreChofer = '';
    if ($qch = $mysqli->prepare("SELECT NombreChofer FROM Logistica WHERE Eliminado=0 AND NumerodeOrden = ? LIMIT 1")) {
        $qch->bind_param('i', $numeroOrden);
        if ($qch->execute()) {
            $rch = $qch->get_result()->fetch_assoc();
            if ($rch && isset($rch['NombreChofer'])) {
                $nombreChofer = trim((string)$rch['NombreChofer']);
            }
        }
        $qch->close();
    }
    if ($no_referencia === '') {
        // Usar siempre el propio número de orden como no_referencia
        $no_referencia = (string)$numeroOrden;
    }
    if ($fecha === '') {
        $fecha = (string)($rows[0]['Fecha'] ?? date('Y-m-d'));
    }
    if ($notas === '') {
        if ($nombreChofer !== '') {
            $notas = 'Hoja de Ruta de ' . $nombreChofer;
        } elseif (!empty($rows[0]['Observaciones'])) {
            $notas = "";
        }
    }

    // 8) Mapear filas -> detalle_orden_ingreso_bulto usando wepoint_id y dimensiones de TransClientes
    $detalle = [];
    $serieUsadas = []; // set local para evitar duplicados en el mismo payload
    $nroSerieLocked = []; // trackea si el nro_serie proviene de CodigoSeguimiento (locked)
    $nroSerieToCodigoSeg = []; // mapear nro_serie => CodigoSeguimiento

    $slug = static function ($s) {
        $s = strtoupper(trim((string)$s));
        // solo letras/números (quita espacios y símbolos)
        $s = preg_replace('/[^A-Z0-9]/', '', $s);
        // si queda vacío, usa un random de 8
        return $s !== '' ? $s : substr(strtoupper(bin2hex(random_bytes(4))), 0, 8);
    };

    $serieUnica = function ($base, &$set) {
        $try = $base;
        $i = 1;
        while (isset($set[$try])) {
            $try = $base . '-' . $i; // o agregar random si prefieres
            $i++;
        }
        $set[$try] = true;
        return $try;
    };
    // Dimensiones mínimas
    // Normalizador de decimales: redondea a 2 decimales y evita colas de flotantes
    $normDec = static function ($v, $min = 0.01, $dec = 2) {
        $n = (float)$v;
        if (!($n > 0)) $n = $min;
        return (float) sprintf("%.{$dec}f", $n);
    };

    $precioCalc = static function ($importe, $dec = 2) {
        $n = (float)$importe;
        if ($n <= 0) $n = 0.01;
        return (float) sprintf("%.{$dec}f", $n);
    };
    // 216699,216702
    foreach ($rows as $row) {
        // Cantidad de piezas a replicar (mínimo 1)
        $cantidad = isset($row['Cantidad']) ? (int)$row['Cantidad'] : 1;
        if ($cantidad <= 0) $cantidad = 1;

        // 1) Resolver/crear el proveedor en WePoint
        list($provOk, $wpProveedorId, $provDebug) = resolver_wepoint_proveedor($mysqli, $token, $row);
        if (!$provOk || $wpProveedorId <= 0) {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'message' => 'No se pudo resolver/crear el proveedor en WePoint',
                'debug' => $provDebug,
                'row_ref' => [
                    'idTransClientes' => $row['idTransClientes'] ?? null,
                    'NumerodeOrden'   => $row['NumerodeOrden'] ?? null,
                    'Seguimiento'     => $row['Seguimiento'] ?? null,
                ],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $idCli      = (int)$row['idCliente'];
        $wpClientId = (int)($clientesVistos[$idCli] ?? ($row['wepoint_id'] ?? 0));
        if ($wpClientId <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'No se pudo resolver id_cliente']);
            exit;
        }

        $importe = isset($row['ImporteCobranza']) ? (float)$row['ImporteCobranza'] : 0.0;

        // Saltar servicios excluidos por CodigoSeguimiento (excluye TODAS sus piezas)
        $codigoSeg = (string)($row['CodigoSeguimiento'] ?? $row['Seguimiento'] ?? '');
        if ($codigoSeg !== '' && isset($exSet[$codigoSeg])) {
            continue; // descartado por el usuario
        }

        // Base de nro_serie: debe ser el CodigoSeguimiento; si está vacío, generamos fallback
        $lockedBase = false;
        if ($codigoSeg !== '') {
            $baseSerie = $codigoSeg;
            $lockedBase = true; // proviene de CodigoSeguimiento
        } else {
            $baseSerie = $slug('ORD-' . $numeroOrden . '-' . ($row['idTransClientes'] ?? 'X'));
            $lockedBase = false; // fallback
        }

        // Normalizar es_flex desde TC_Flex
        $esFlex = 0;
        if (isset($row['TC_Flex'])) {
            $v = $row['TC_Flex'];
            if (is_string($v)) {
                $vv = strtolower(trim($v));
                $esFlex = in_array($vv, ['1', 'true', 't', 'si', 'sí', 'y', 'yes', 'on'], true) ? 1 : 0;
            } else {
                $esFlex = ((int)$v) ? 1 : 0;
            }
        }

        // Replicar una entrada por cada pieza
        for ($i = 1; $i <= $cantidad; $i++) {
            // Si hay múltiples piezas, sufijamos _1, _2, ... sobre el CodigoSeguimiento/base
            $nroSerieBase = ($cantidad > 1) ? ($baseSerie . '_' . $i) : $baseSerie;

            // Asegurar unicidad en este payload
            $nroSerie = $nroSerieBase;
            if (isset($serieUsadas[$nroSerie])) {
                // si ya existe y la base NO es locked (fallback), tratamos de generar una variante única
                if (!$lockedBase) {
                    $nroSerie = $serieUnica($nroSerieBase, $serieUsadas);
                } else {
                    // si es locked y colisiona (muy raro con sufijo), lo dejamos tal cual para que el 422 lo reporte
                }
            } else {
                $serieUsadas[$nroSerie] = true;
            }

            // marcar locked/unlocked por índice de detalle (para reintentos 422)
            $nroSerieLocked[] = $lockedBase;
            $nroSerieToCodigoSeg[$nroSerie] = $codigoSeg;

            $detalle[] = [
                'nro_serie'              => $nroSerie,
                'id_cliente'             => $wpClientId,
                'destinatario_nombre'    => (string)($row['Cliente'] ?? $row['Titulo'] ?? ''),
                'id_proveedor'           => $wpProveedorId,
                'ancho'                  => $normDec($row['TC_Ancho'] ?? null),
                'largo'                  => $normDec($row['TC_Largo'] ?? null),
                'alto'                   => $normDec($row['TC_Alto']  ?? null),
                'peso'                   => $normDec($row['TC_Peso']  ?? null),
                'precio'                 => $precioCalc($importe),
                'es_contrareembolso'     => (int)($importe > 0),
                'es_flex'                => $esFlex,
                'destinatario_telefono'  => (string)($row['Celular'] ?? ''),
                'destinatario_email'     => (string)($row['CL_Email'] ?? ''),
                'destinatario_direccion' => (string)($row['Localizacion'] ?? ''),
                'destinatario_provincia' => (string)($row['Provincia'] ?? ''),
                'destinatario_ciudad'    => (string)($row['Ciudad'] ?? ''),
            ];
        }

        // actualizar cantidad total después de replicar
        $cantidadTotal = count($detalle);
    }


    // 9) Payload y POST a WePoint (con reintento por nro_serie duplicado)
    $payloadArr = [
        'no_referencia' => $no_referencia,
        'fecha'         => $fecha,
        'notas'         => $notas,
        'detalle_orden_ingreso_bulto' => $detalle,
    ];

    // 1er intento
    list($http, $err, $resp) = post_wepoint_oib($token, $payloadArr);

    // ¿Hubo 422 por nro_serie duplicado? Reintentar regenerando sólo los conflictivos
    if ($http === 422) {
        $asArray = json_decode($resp, true);

        // Detectar por texto o por estructura "errors"
        $hayDuplicados = false;
        if (is_array($asArray)) {
            // Caso estructurado
            if (!empty($asArray['errors']) && is_array($asArray['errors'])) {
                foreach ($asArray['errors'] as $campo => $msgs) {
                    if (preg_match('/detalle_orden_ingreso_bulto\.(\d+)\.nro_serie/', $campo, $m)) {
                        $idx = (int)$m[1];
                        if (isset($payloadArr['detalle_orden_ingreso_bulto'][$idx])) {
                            // Si el nro_serie proviene de CodigoSeguimiento (locked), NO lo alteramos
                            $isLocked = isset($nroSerieLocked[$idx]) ? (bool)$nroSerieLocked[$idx] : false;
                            if (!$isLocked) {
                                $payloadArr['detalle_orden_ingreso_bulto'][$idx]['nro_serie'] =
                                    'ORD' . $numeroOrden . '-' . strtoupper(bin2hex(random_bytes(4)));
                            }
                            $hayDuplicados = true;
                        }
                    }
                }
            }
        } else {
            // Caso texto plano
            if (strpos($resp, 'nro de serie ya existe') !== false) {
                // como fallback, regenerar TODOS por si no vino índice claro
                foreach ($payloadArr['detalle_orden_ingreso_bulto'] as $i => $b) {
                    $isLocked = isset($nroSerieLocked[$i]) ? (bool)$nroSerieLocked[$i] : false;
                    if (!$isLocked) {
                        $payloadArr['detalle_orden_ingreso_bulto'][$i]['nro_serie'] =
                            'ORD' . $numeroOrden . '-' . strtoupper(bin2hex(random_bytes(4)));
                    }
                }
                $hayDuplicados = true;
            }
        }

        if ($hayDuplicados) {
            // Reintento
            list($http, $err, $resp) = post_wepoint_oib($token, $payloadArr);
        }
    }

    // === Procesar respuesta de WePoint e insertar tracking ===
    $decoded = json_decode($resp, true);

    // Solo consideramos éxito cuando es 2xx
    if ($http >= 200 && $http < 300) {
        // Inicializaciones para evitar warnings si no hay updates a TransClientes
        $updated = 0;
        $idsAfectados = [];
        // Extraer no_referencia y nro_orden_ingreso_bulto desde la respuesta si no vinieron set
        $noRef  = $no_referencia ?: ($decoded['data']['no_referencia'] ?? ($decoded['no_referencia'] ?? ''));
        $nroOIB = $decoded['data']['nro_orden_ingreso_bulto'] ?? ($decoded['nro_orden_ingreso_bulto'] ?? '');

        // Seguridad: si falta alguno, seguimos, pero lo registramos vacío
        $now     = date('Y-m-d H:i:s');
        $Usuario = $_SESSION['Usuario'] ?? 'Sistema';

        // Armar mapa nro_serie => id_detalle_orden_ingreso_bulto desde la respuesta
        $mapSerieToIdDetalle = [];
        if (isset($decoded['data']['detalle_orden_ingreso_bulto']) && is_array($decoded['data']['detalle_orden_ingreso_bulto'])) {
            foreach ($decoded['data']['detalle_orden_ingreso_bulto'] as $det) {
                $idDet  = $det['id_detalle_orden_ingreso_bulto'] ?? null;
                $nroSer = $det['bulto']['nro_serie'] ?? null;
                if ($idDet && $nroSer) {
                    $mapSerieToIdDetalle[(string)$nroSer] = (int)$idDet;
                }
            }
        }

        // Insertar un row por cada nro_serie reconocido en la respuesta
        if (!empty($mapSerieToIdDetalle)) {
            $sqlIns = "
                INSERT INTO wepoint_api
                  (`Time`, `no_referencia`, `nro_orden_ingreso_bulto`,
                   `CodigoSeguimiento`, `CodigoSeguimiento_enviado`,
                   `id_wepoint`, `Usuario`, `Estado`, `tipo`, `http_code`)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ";
            if ($ins = $mysqli->prepare($sqlIns)) {
                foreach ($mapSerieToIdDetalle as $serieEnviada => $idWe) {
                    // base que originó ese nro_serie; si no existe, caemos al mismo nro_serie
                    $codigoBase = $nroSerieToCodigoSeg[$serieEnviada] ?? $serieEnviada;

                    // respetar exclusiones del usuario
                    if (!empty($exSet) && isset($exSet[$codigoBase])) continue;

                    $estado   = 'IN OK';
                    $tipo     = 'IN';
                    $httpCode = (int)$http;

                    $ins->bind_param(
                        'ssssssissi',
                        $now,        // Time
                        $noRef,      // no_referencia
                        $nroOIB,     // nro_orden_ingreso_bulto
                        $codigoBase, // CodigoSeguimiento (base)
                        $serieEnviada, // CodigoSeguimiento_enviado (nro_serie)
                        $idWe,       // id_wepoint
                        $Usuario,    // Usuario
                        $estado,     // Estado
                        $tipo,       // tipo
                        $httpCode    // http_code
                    );
                    if (!$ins->execute()) {
                        http_response_code(500);
                        echo json_encode([
                            'ok' => false,
                            'message' => 'Falló INSERT en wepoint_api',
                            'mysqli_error' => $mysqli->error,
                            'stmt_error' => $ins->error,
                            'values' => [
                                'Time' => $now,
                                'no_referencia' => $noRef,
                                'nro_orden_ingreso_bulto' => $nroOIB,
                                'CodigoSeguimiento' => $codigoBase,
                                'CodigoSeguimiento_enviado' => $serieEnviada,
                                'id_wepoint' => $idWe,
                                'Usuario' => $Usuario,
                                'Estado' => 'IN OK',
                                'tipo' => 'IN',
                                'http_code' => $httpCode,
                            ]
                        ], JSON_UNESCAPED_UNICODE);
                        $ins->close();
                        exit;
                    } else {
                        //AGREGO EL ID CARGADO EN $ins
                        $lastId = $ins->insert_id;
                        $sql = "UPDATE TransClientes SET Wepoint_id = ? WHERE CodigoSeguimiento= ? AND Eliminado = 0 AND (Wepoint_id = 0 OR Wepoint_id IS NULL)";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param('is', $lastId, $codigoBase);
                        if (!$stmt->execute()) {
                            http_response_code(500);
                            echo json_encode([
                                'ok' => false,
                                'message' => 'Falló UPDATE en TransClientes',
                                'mysqli_error' => $mysqli->error,
                                'stmt_error' => $stmt->error,
                                'values' => [
                                    'wepoint_id' => $idWe,
                                    'CodigoSeguimiento' => $codigoBase,
                                ]
                            ], JSON_UNESCAPED_UNICODE);
                            $stmt->close();
                            exit;
                        }
                    }
                }
                $ins->close();
            } else {
                http_response_code(500);
                echo json_encode([
                    'ok' => false,
                    'message' => 'Prepare INSERT wepoint_api falló',
                    'mysqli_error' => $mysqli->error,
                    'sql' => $sqlIns
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        echo json_encode([
            'http_code'                  => $http,
            'wepoint_raw'                => $resp,
            'sent'                       => $payloadArr,
            'no_referencia'              => $noRef,
            'nro_orden_ingreso_bulto'    => $nroOIB,
            'cantidad'                   => $cantidadTotal,
            'excluidos'                  => $excluirCodigos,
            'transclientes_actualizados' => $updated,
            'ids_actualizados'           => $idsAfectados,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Si llegó acá, WePoint devolvió error definitivo
    http_response_code($http ?: 502);
    echo json_encode([
        'http_code'   => $http,
        'curl_error'  => $err,
        'wepoint_raw' => $resp,
        'sent'        => $payloadArr,
        'message'     => 'WePoint rechazó el payload',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
// === Handler: Servicios por Orden (lista de CodigoSeguimiento) ===
if (isset($_POST['ServiciosPorOrden']) && (int)$_POST['ServiciosPorOrden'] === 1) {
    header('Content-Type: application/json; charset=utf-8');

    $numeroOrden = isset($_POST['NumerodeOrden']) ? (int)$_POST['NumerodeOrden'] : 0;
    if ($numeroOrden <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Falta NumerodeOrden']);
        exit;
    }

    if (!isset($mysqli)) {
        include_once '../../../Conexion/Conexioni.php';
    }
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No hay conexión a base de datos ($mysqli)']);
        exit;
    }

    $sql = "SELECT CodigoSeguimiento,RazonSocial AS Origen, ClienteDestino AS Destino,Cantidad
            FROM TransClientes            
            WHERE idClienteDestino = ?
              AND NumerodeOrden = ?
              AND Entregado = 0
              AND Devuelto = 0
              AND Eliminado = 0
              AND Haber = 0
              AND (wepoint_id = 0 OR wepoint_id IS NULL)";

    $rows = [];
    $idWepoint = ID_WEPOINT;

    if ($st = $mysqli->prepare($sql)) {

        $st->bind_param('ii', $idWepoint, $numeroOrden);

        if ($st->execute()) {
            $res = $st->get_result();
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
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
    $sql = "SELECT  `Time` AS Fecha,`no_referencia`, `nro_orden_ingreso_bulto` AS NumerodeOrden, `CodigoSeguimiento_enviado`, `Usuario`, `id_wepoint`,`Estado` FROM wepoint_api";

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
