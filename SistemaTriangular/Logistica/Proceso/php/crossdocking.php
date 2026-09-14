<?php

/**
 * CrossDocking — pantalla de escaneo para el operador de WePoint.
 *
 * Un mismo operador escanea, sin distinguir de antemano, etiquetas de:
 *  - Ferniplast / IGALFER (código propio del proveedor, va en CodigoProveedor,
 *    a veces con sufijo "_N" para multi-bulto — igual que resuelve
 *    resolverServicioColecta() en 4_reparto/Proceso/php/colecta_scan.php)
 *  - Mercado Libre (QR con JSON {"id": shipments_id}, o el mismo id guardado
 *    en CodigoProveedor cuando es Flex — ver [[project_db_logistica_contabilidad]]
 *    "Gotcha: nº de envío de Meli en colectas Flex")
 *  - Caddy propio (CodigoSeguimiento, con o sin sufijo "_N")
 *
 * En todos los casos se resuelve la MISMA fila de TransClientes y se
 * devuelve el rótulo Caddy (CodigoSeguimiento, origen, destino, recorrido)
 * para mostrar grande en pantalla — no importa qué etiqueta física haya
 * leído el lector.
 *
 * LA MARCA real (¿este bulto entró a depósito?) sigue viviendo SOLO en
 * TransClientes.Wepoint_f/h/status — las mismas columnas que ya usa el
 * circuito externo de api.caddy.com.ar/clases/warehouse.class.php, y que
 * ya se muestran solas en Seguimiento (panel + ficha, ver
 * [[project_warehouse_ingreso_wepoint]]). Por eso Seguimiento NO necesita
 * ningún cambio para esto: cualquier ingreso marcado acá ya le aparece.
 *
 * `crossdocking_eventos` es una tabla NUEVA, pero es solo un LOG de
 * auditoría (quién escaneó qué, cuándo, y si matcheó o no) — no reemplaza
 * ni duplica la marca de arriba. Sirve para diagnosticar códigos de
 * proveedor que no matchean (el tipo de lío que motivó esta pantalla) y
 * para saber qué operador escaneó qué. No la lee Seguimiento ni ninguna
 * otra pantalla — es interna de este módulo.
 */

date_default_timezone_set('America/Argentina/Cordoba');
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($mysqli)) {
    include_once '../../../Conexion/Conexioni.php';
}

// Igual criterio que parseCaddyBase() de colecta_scan.php: "BASE_2" -> "BASE"
function cd_base(string $raw): string
{
    $raw = trim($raw);
    return preg_replace('/_\d+$/', '', $raw) ?? $raw;
}

// Si el QR trae JSON de Meli {"id":"..."} devuelve el id numérico como string, si no null.
function cd_parse_meli_json(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '' || $raw[0] !== '{') {
        return null;
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['id'])) {
        return null;
    }
    $id = (string)$data['id'];
    return ctype_digit($id) ? $id : null;
}

function cd_fila_comun(): string
{
    return "id, CodigoSeguimiento, Wepoint_c, Wepoint_f, Wepoint_h, Wepoint_status,
            Cantidad, RazonSocial, ClienteDestino, DomicilioDestino, LocalidadDestino,
            Recorrido, NumerodeOrden, idClienteOrigen, CodigoProveedor, shipments_id";
}

// Nombre + color del recorrido (Recorridos.Numero = TransClientes.Recorrido).
// El color se manda tal cual está en la base (con o sin '#', a veces vacío o
// '000000') — el front ya sabe normalizarlo igual que el mapa de
// Repartidores en Vivo (misma paleta en toda la app).
function cd_info_recorrido(mysqli $mysqli, int $numero): array
{
    if ($numero <= 0) {
        return ['nombre' => '', 'color' => ''];
    }
    $st = $mysqli->prepare("SELECT Nombre, Color FROM Recorridos WHERE Numero=? LIMIT 1");
    $st->bind_param('i', $numero);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    return [
        'nombre' => (string)($row['Nombre'] ?? ''),
        'color'  => (string)($row['Color'] ?? ''),
    ];
}

// Posición del envío dentro del recorrido (orden de entrega) — mismo dato
// que ya usa caddy_cs.php en wepoint.ar para imprimir "Pos:" en el rótulo
// de colecta. Vive en HojaDeRuta, no en TransClientes, matcheando por el
// código de Caddy (sin sufijo _N — HojaDeRuta es por envío, no por bulto).
function cd_info_posicion(mysqli $mysqli, string $codigoBase): ?int
{
    if ($codigoBase === '') {
        return null;
    }
    $st = $mysqli->prepare("SELECT Posicion FROM HojaDeRuta
                             WHERE Seguimiento=? AND Eliminado=0
                             ORDER BY (Estado='Abierto') DESC, id DESC
                             LIMIT 1");
    $st->bind_param('s', $codigoBase);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    // Posicion default es 0 (no NULL) para filas donde nunca se calculó —
    // se trata igual que "no hay dato" en vez de imprimir "Pos: 0".
    $pos = $row ? (int)$row['Posicion'] : 0;
    return $pos > 0 ? $pos : null;
}

/**
 * Resuelve un código crudo escaneado contra TransClientes. Devuelve:
 *  - ['tipo'=>..., 'row'=>array]              → match único, seguir normal
 *  - ['tipo'=>'AMBIGUO', 'candidatos'=>[...]] → el código de proveedor
 *    coincide con pendientes de MÁS DE UN cliente de origen distinto — no
 *    se adivina, se le pregunta al operador (ver EscanearCrossdocking).
 *  - null                                      → no matcheó nada
 * Prioridad: Meli (JSON) > Wepoint_c > Caddy propio > CodigoProveedor.
 *
 * $proveedorForzado (Clientes.id): cuando el operador ya desambiguó una vez
 * para este código, se lo manda de vuelta y el paso 4 filtra directo por
 * ese cliente en vez de volver a preguntar.
 */
function cd_resolver(mysqli $mysqli, string $raw, int $proveedorForzado = 0): ?array
{
    $cols = cd_fila_comun();
    $condBase = "Eliminado=0 AND Entregado=0 AND Devuelto=0";

    // 1) Mercado Libre: QR JSON {"id": shipments_id}
    $meliId = cd_parse_meli_json($raw);
    if ($meliId !== null) {
        $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                                 WHERE $condBase AND shipments_id=?
                                 ORDER BY id DESC LIMIT 1");
        $st->bind_param('s', $meliId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        if ($row) return ['tipo' => 'ML_JSON', 'row' => $row];

        // Flex guarda el nro de envío de Meli en CodigoProveedor, no en shipments_id.
        $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                                 WHERE $condBase AND CodigoProveedor=?
                                 ORDER BY id DESC LIMIT 1");
        $st->bind_param('s', $meliId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        if ($row) return ['tipo' => 'ML_CODPROV', 'row' => $row];

        return null; // era JSON de Meli y no matcheó nada: no seguir probando como texto
    }

    $base = cd_base($raw);
    if ($base === '') {
        return null;
    }

    // 2) Wepoint_c — la clave universal (CodigoProveedor si existía al armar la
    //    venta, o el propio código Caddy si no). Cubre Ferniplast/IGALFER/Caddy
    //    en un solo lugar.
    $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                             WHERE $condBase AND Wepoint_c IN (?, ?)
                             ORDER BY id DESC LIMIT 1");
    $st->bind_param('ss', $raw, $base);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if ($row) return ['tipo' => 'WEPOINT_C', 'row' => $row];

    // 3) Caddy propio, por si Wepoint_c no se pobló (filas viejas).
    $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                             WHERE $condBase AND SUBSTRING_INDEX(CodigoSeguimiento,'_',1)=?
                             ORDER BY id DESC LIMIT 1");
    $st->bind_param('s', $base);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if ($row) return ['tipo' => 'CADDY_QR', 'row' => $row];

    // 4) CodigoProveedor directo (Ferniplast/IGALFER), con y sin sufijo _N.
    //    Si el operador ya nos dijo de qué proveedor es (desambiguó antes),
    //    filtramos directo por ese cliente — sin ambigüedad posible.
    if ($proveedorForzado > 0) {
        $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                                 WHERE $condBase AND CodigoProveedor IN (?, ?) AND idClienteOrigen=?
                                 ORDER BY id DESC LIMIT 1");
        $st->bind_param('ssi', $raw, $base, $proveedorForzado);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        if ($row) return ['tipo' => 'PROV_CODPROV', 'row' => $row];
        return null;
    }

    // Traemos TODOS los candidatos (no LIMIT 1) para poder distinguir
    // "varias filas del mismo proveedor" (normal, no hay nada que preguntar,
    // nos quedamos con la más nueva) de "coincide con proveedores DISTINTOS"
    // (colisión real — ahí sí hay que preguntar, no adivinar).
    $st = $mysqli->prepare("SELECT $cols FROM TransClientes
                             WHERE $condBase AND CodigoProveedor IN (?, ?)
                             ORDER BY id DESC");
    $st->bind_param('ss', $raw, $base);
    $st->execute();
    $candidatos = $st->get_result()->fetch_all(MYSQLI_ASSOC);

    if (!$candidatos) {
        return null;
    }

    $clientesDistintos = [];
    foreach ($candidatos as $c) {
        $cid = (int)($c['idClienteOrigen'] ?? 0);
        if (!isset($clientesDistintos[$cid])) {
            $clientesDistintos[$cid] = $c; // la más nueva de ese cliente (van ORDER BY id DESC)
        }
    }

    if (count($clientesDistintos) === 1) {
        return ['tipo' => 'PROV_CODPROV', 'row' => $candidatos[0]];
    }

    // Colisión real: mismo código, más de un cliente de origen con algo
    // pendiente. Se le devuelve al front la lista para que el operador
    // elija — nada de adivinar (antes acá se priorizaba Ferniplast a ciegas).
    return [
        'tipo' => 'AMBIGUO',
        'candidatos' => array_map(function ($c) {
            return [
                'idClienteOrigen' => (int)($c['idClienteOrigen'] ?? 0),
                'razonSocial'     => (string)($c['RazonSocial'] ?? '(sin nombre)'),
            ];
        }, array_values($clientesDistintos)),
    ];
}

function cd_marcar_ingreso(mysqli $mysqli, int $id, string $wepointCFallback): array
{
    $fecha = date('Y-m-d');
    $hora  = date('H:i:s');

    $st = $mysqli->prepare("SELECT Wepoint_status, Wepoint_f, Wepoint_h FROM TransClientes WHERE id=? LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $prev = $st->get_result()->fetch_assoc() ?: [];

    $st = $mysqli->prepare("UPDATE TransClientes
                             SET Wepoint_f=?, Wepoint_h=?, Wepoint_status='Ingreso',
                                 Wepoint_c = IF(Wepoint_c IS NULL OR Wepoint_c='', ?, Wepoint_c)
                             WHERE id=? AND Eliminado=0
                             LIMIT 1");
    $st->bind_param('sssi', $fecha, $hora, $wepointCFallback, $id);
    $st->execute();

    return [
        'wepointFAnterior' => !empty($prev['Wepoint_f']) && $prev['Wepoint_f'] !== '0000-00-00' ? $prev['Wepoint_f'] : null,
        'fecha'            => $fecha,
        'hora'             => $hora,
    ];
}

function cd_log_evento(mysqli $mysqli, string $fecha, string $hora, string $codigoCrudo, ?string $bultoSufijo, string $resultado, ?string $matchTipo, ?int $idTC, ?int $recorrido): void
{
    $usuario = (string)($_SESSION['Usuario'] ?? '');
    // Defensa extra además de ensanchar la columna: un QR de Meli con
    // hash_code trae ~140 caracteres y ya rompió esto una vez (Fatal error
    // "Data too long"). Si mañana aparece un formato todavía más largo,
    // mejor truncar el log que tirar abajo el escaneo real.
    if (strlen($codigoCrudo) > 490) {
        $codigoCrudo = substr($codigoCrudo, 0, 490);
    }
    $st = $mysqli->prepare("INSERT INTO crossdocking_eventos
        (fecha, hora, usuario, codigo_crudo, bulto_sufijo, resultado, match_tipo, idTransClientes, recorrido)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $st->bind_param('sssssssii', $fecha, $hora, $usuario, $codigoCrudo, $bultoSufijo, $resultado, $matchTipo, $idTC, $recorrido);
    $st->execute();
}

/**
 * ¿Este BULTO (envío + sufijo _N, o "sin sufijo" si Cantidad=1) ya se
 * había escaneado hoy?
 *
 * OJO — esto NO compara el texto crudo leído. Un mismo bulto físico puede
 * tener DOS etiquetas encima (la propia del proveedor/Meli Y la de Caddy):
 * si comparáramos el texto exacto, "CADDY123" y el JSON de Meli nunca
 * matchean entre sí aunque sean la misma pieza — eso hacía que reescanear
 * el mismo bulto con la OTRA etiqueta no se detectara como duplicado.
 * La identidad real de "qué bulto es" es (idTransClientes, sufijo _N) —
 * no importa qué formato de código lo leyó.
 *
 * Esto es distinto de "el envío ya está Ingreso" en TransClientes: un
 * pedido con Cantidad=3 trae 3 etiquetas físicas con sufijo _1/_2/_3. Si
 * sólo miráramos Wepoint_status, el bulto 2 y el 3 aparecerían como "ya
 * ingresado" apenas se lee el 1 — son paquetes distintos, no un reintento.
 *
 * Esto depende de que cada bulto físico traiga un sufijo _N distinguible
 * — si dos bultos del mismo pedido imprimen el MISMO sufijo (o ninguno),
 * no hay forma de diferenciarlos por software; eso ya lo vimos con
 * Ferniplast al principio (etiquetas _3/_3/_3 en vez de _1/_2/_3).
 */
function cd_ya_escaneado_hoy(mysqli $mysqli, string $fecha, int $idTC, ?string $bultoSufijo): bool
{
    $st = $mysqli->prepare("SELECT id FROM crossdocking_eventos
                             WHERE fecha=? AND idTransClientes=? AND bulto_sufijo <=> ? AND resultado IN ('ok','dup')
                             LIMIT 1");
    $st->bind_param('sis', $fecha, $idTC, $bultoSufijo);
    $st->execute();
    return (bool)$st->get_result()->fetch_assoc();
}

// Cuántos bultos DISTINTOS (por sufijo) de este envío se escanearon hoy
// (incluyendo el de este request), para el "Bulto X de Y" del banner.
function cd_bultos_escaneados_hoy(mysqli $mysqli, string $fecha, int $idTC): int
{
    $st = $mysqli->prepare("SELECT COUNT(DISTINCT COALESCE(bulto_sufijo,'\\0')) AS n FROM crossdocking_eventos
                             WHERE fecha=? AND idTransClientes=? AND resultado IN ('ok','dup')");
    $st->bind_param('si', $fecha, $idTC);
    $st->execute();
    return (int)($st->get_result()->fetch_assoc()['n'] ?? 0);
}

// Bultos físicos distintos escaneados HOY para un recorrido (mismo criterio
// que la agregación de EstadoCrossdocking, pero para un solo recorrido — se
// usa al responder un escaneo en vivo, así la tarjeta no depende de que el
// navegador haya sumado bien localmente).
function cd_escaneados_hoy_recorrido(mysqli $mysqli, string $fecha, int $recorrido): int
{
    $st = $mysqli->prepare("SELECT COUNT(DISTINCT CONCAT(idTransClientes,'|',COALESCE(bulto_sufijo,'\\0'))) AS n
                             FROM crossdocking_eventos
                             WHERE fecha=? AND recorrido=? AND resultado IN ('ok','dup')");
    $st->bind_param('si', $fecha, $recorrido);
    $st->execute();
    return (int)($st->get_result()->fetch_assoc()['n'] ?? 0);
}

// Total esperado para un recorrido, para el "de Y" de su tarjeta: suma de
// Cantidad de todo lo pendiente de entrega asignado a ese Recorrido — no
// filtra por si pasa por WePoint o no (TransClientes.Wepoint_c se puebla en
// TODAS las ventas, no es una marca confiable de "esto cruza por el
// depósito"), así que puede sobrecontar si hay entregas directas que nunca
// tocan la pantalla de crossdocking. Decisión consciente: mejor un
// denominador simple y transparente que uno "más preciso" pero frágil.
function cd_bultos_esperados(mysqli $mysqli, int $recorrido): int
{
    $st = $mysqli->prepare("SELECT SUM(Cantidad) AS n FROM TransClientes
                             WHERE Recorrido=? AND Eliminado=0 AND Entregado=0 AND Devuelto=0");
    $st->bind_param('i', $recorrido);
    $st->execute();
    return (int)($st->get_result()->fetch_assoc()['n'] ?? 0);
}

if (isset($_POST['EscanearCrossdocking'])) {
    header('Content-Type: application/json; charset=utf-8');

    $raw = trim((string)($_POST['codigo'] ?? ''));
    if ($raw === '') {
        echo json_encode(['ok' => false, 'error' => 'CODIGO_VACIO']);
        exit;
    }
    $proveedorForzado = (int)($_POST['proveedorForzado'] ?? 0);

    $ahoraFecha = date('Y-m-d');
    $ahoraHora  = date('H:i:s');

    // Sufijo _N del texto crudo (si lo tiene) — identifica el BULTO puntual,
    // sea cual sea el formato/proveedor de la etiqueta que lo trae.
    $tieneSufijo = preg_match('/_(\d+)$/', $raw, $m) === 1;
    $bultoSufijo = $tieneSufijo ? $m[1] : null;

    $resuelto = cd_resolver($mysqli, $raw, $proveedorForzado);
    if (!$resuelto) {
        cd_log_evento($mysqli, $ahoraFecha, $ahoraHora, $raw, $bultoSufijo, 'no_match', null, null, null);
        echo json_encode(['ok' => false, 'error' => 'NO_MATCH', 'codigo' => $raw]);
        exit;
    }

    if ($resuelto['tipo'] === 'AMBIGUO') {
        // No se loguea como no_match ni se marca nada todavía — se le
        // devuelve la lista al operador y se espera a que elija. El
        // reintento (con proveedorForzado) sí queda logueado normal.
        echo json_encode([
            'ok' => false,
            'error' => 'AMBIGUO',
            'codigo' => $raw,
            'candidatos' => $resuelto['candidatos'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $row = $resuelto['row'];
    $base = cd_base($raw);
    // Si el código escaneado traía sufijo _N (bulto puntual), lo mantenemos en
    // el rótulo mostrado aunque CodigoSeguimiento en la base esté sin sufijo.
    $codigoEtiqueta = $tieneSufijo
        ? cd_base((string)$row['CodigoSeguimiento']) . '_' . $m[1]
        : (string)$row['CodigoSeguimiento'];

    $idTC = (int)$row['id'];

    // Duplicado = este BULTO (envío + sufijo _N) ya se había leído hoy —
    // NO "el envío ya está Ingreso" (rompía multi-bulto) NI "mismo texto
    // exacto" (rompía leer la etiqueta de Caddy y despues la del proveedor
    // para el mismo bulto sin sufijo — cada formato trae un texto distinto
    // aunque sea la misma pieza física).
    $esRepetido = cd_ya_escaneado_hoy($mysqli, $ahoraFecha, $idTC, $bultoSufijo);

    $marca = cd_marcar_ingreso($mysqli, $idTC, $row['CodigoProveedor'] ?: $base);
    $recorridoNum = (int)($row['Recorrido'] ?? 0);
    $recInfo = cd_info_recorrido($mysqli, $recorridoNum);
    $posicion = cd_info_posicion($mysqli, cd_base((string)$row['CodigoSeguimiento']));

    cd_log_evento(
        $mysqli,
        $marca['fecha'],
        $marca['hora'],
        $raw,
        $bultoSufijo,
        $esRepetido ? 'dup' : 'ok',
        $resuelto['tipo'],
        $idTC,
        $recorridoNum ?: null
    );

    $bultoTotal = max(1, (int)($row['Cantidad'] ?? 1));
    $bultoActual = min($bultoTotal, cd_bultos_escaneados_hoy($mysqli, $ahoraFecha, $idTC));

    // Total del recorrido para la tarjeta grande — se recalcula server-side
    // en cada escaneo (no confiamos en que el navegador haya ido sumando
    // bien solo; así, si hay dos pantallas abiertas a la vez, las dos
    // terminan mostrando el mismo número).
    $recEscaneadosHoy = $recorridoNum > 0 ? cd_escaneados_hoy_recorrido($mysqli, $ahoraFecha, $recorridoNum) : 0;
    $recEsperados = $recorridoNum > 0 ? max($recEscaneadosHoy, cd_bultos_esperados($mysqli, $recorridoNum)) : 0;

    echo json_encode([
        'ok'                => true,
        'match'             => $resuelto['tipo'],
        'idTransClientes'   => $idTC,
        'codigoSeguimiento' => (string)$row['CodigoSeguimiento'],
        'codigoEtiqueta'    => $codigoEtiqueta,
        'cantidad'          => $bultoTotal,
        'bultoActual'       => $bultoActual,
        'bultoTotal'        => $bultoTotal,
        'razonSocialOrigen' => (string)($row['RazonSocial'] ?? ''),
        'clienteDestino'    => (string)($row['ClienteDestino'] ?? ''),
        'domicilioDestino'  => (string)($row['DomicilioDestino'] ?? ''),
        'localidadDestino'  => (string)($row['LocalidadDestino'] ?? ''),
        'recorrido'         => $recorridoNum,
        'recorridoNombre'   => $recInfo['nombre'],
        'recorridoColor'    => $recInfo['color'],
        'posicion'          => $posicion,
        'recorridoEscaneadosHoy' => $recEscaneadosHoy,
        'recorridoEsperados'     => $recEsperados,
        'numeroOrden'       => (int)($row['NumerodeOrden'] ?? 0),
        'yaIngresado'       => $esRepetido,
        'wepointFAnterior'  => $marca['wepointFAnterior'],
        'hora'              => $marca['hora'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Estado de la pantalla al abrir/recargar: reconstruye todo desde
 * TransClientes.Wepoint_f/h (la marca) — no desde crossdocking_eventos (que
 * es solo auditoría, podría tener ruido de escaneos fallidos/reintentos).
 * Devuelve:
 *  - porRecorrido: para las tarjetas grandes (agrupado, con color)
 *  - items: los últimos escaneos individuales (feed chico, trazabilidad)
 *
 * porRecorrido sale de crossdocking_eventos, NO de contar filas de
 * TransClientes: un pedido con Cantidad=3 es UNA fila de TransClientes pero
 * TRES bultos físicos distintos (tres etiquetas _1/_2/_3 escaneadas). Contar
 * filas subcontaría paquetes reales — por eso se cuentan pares
 * (idTransClientes, bulto_sufijo) distintos, que es "cuántos bultos
 * puntuales se leyeron" (no "cuántos textos distintos", para no contar dos
 * veces el mismo bulto solo porque se leyó con la etiqueta del proveedor Y
 * con la de Caddy) — el mismo criterio que usa "Bulto X de Y" en el banner
 * (cd_bultos_escaneados_hoy).
 * GET/POST: EstadoCrossdocking=1, opcional fecha=YYYY-MM-DD (default hoy)
 */
if (isset($_REQUEST['EstadoCrossdocking'])) {
    header('Content-Type: application/json; charset=utf-8');

    $fecha = trim((string)($_REQUEST['fecha'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fecha = date('Y-m-d');
    }

    $condHoy = "Eliminado=0 AND Wepoint_status='Ingreso' AND Wepoint_f=?";

    // Agrupado por recorrido, con nombre y color, ordenado por el más
    // recién tocado primero (pantalla "en vivo": el recorrido en el que
    // están trabajando ahora tiene que quedar arriba).
    $st = $mysqli->prepare("SELECT ce.recorrido AS recorrido,
                                    COALESCE(r.Nombre,'') AS nombre,
                                    COALESCE(r.Color,'') AS color,
                                    COUNT(DISTINCT CONCAT(ce.idTransClientes,'|',COALESCE(ce.bulto_sufijo,'\\0'))) AS cantidad,
                                    MAX(ce.hora) AS ultimaHora
                             FROM crossdocking_eventos ce
                             LEFT JOIN Recorridos r ON r.Numero = ce.recorrido
                             WHERE ce.fecha=? AND ce.resultado IN ('ok','dup') AND ce.recorrido IS NOT NULL
                             GROUP BY ce.recorrido
                             ORDER BY ultimaHora DESC");
    $st->bind_param('s', $fecha);
    $st->execute();
    $porRecorrido = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($porRecorrido as &$r) {
        $r['recorrido'] = (int)$r['recorrido'];
        $r['cantidad'] = (int)$r['cantidad'];
        // "esperados" no puede ser menor a lo ya escaneado (si algo se
        // entregó/eliminó después de pasar por acá, cae del pendiente) —
        // se acota para no mostrar un "5 de 4" que confunde más de lo que aclara.
        $r['esperados'] = max($r['cantidad'], cd_bultos_esperados($mysqli, $r['recorrido']));
    }
    unset($r);

    $st = $mysqli->prepare("SELECT " . cd_fila_comun() . "
                             FROM TransClientes
                             WHERE $condHoy
                             ORDER BY Wepoint_h DESC, id DESC
                             LIMIT 60");
    $st->bind_param('s', $fecha);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $idTC = (int)$row['id'];
        $bultoTotal = max(1, (int)($row['Cantidad'] ?? 1));
        $items[] = [
            'idTransClientes'   => $idTC,
            'codigoSeguimiento' => (string)$row['CodigoSeguimiento'],
            'codigoEtiqueta'    => (string)$row['CodigoSeguimiento'], // sin sufijo: no se guarda cuál bulto puntual fue
            'cantidad'          => $bultoTotal,
            'bultoTotal'        => $bultoTotal,
            'bultoActual'       => min($bultoTotal, cd_bultos_escaneados_hoy($mysqli, $fecha, $idTC)),
            'razonSocialOrigen' => (string)($row['RazonSocial'] ?? ''),
            'clienteDestino'    => (string)($row['ClienteDestino'] ?? ''),
            'localidadDestino'  => (string)($row['LocalidadDestino'] ?? ''),
            'recorrido'         => (int)($row['Recorrido'] ?? 0),
            'posicion'          => cd_info_posicion($mysqli, cd_base((string)$row['CodigoSeguimiento'])),
            'numeroOrden'       => (int)($row['NumerodeOrden'] ?? 0),
            'hora'              => (string)($row['Wepoint_h'] ?? ''),
        ];
    }

    echo json_encode([
        'ok' => true,
        'fecha' => $fecha,
        'porRecorrido' => $porRecorrido,
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sufijos _N (o null si Cantidad=1, sin sufijo) ya escaneados HOY para un
// envío — para saber, bulto por bulto, cuáles de los N de un multi-bulto
// todavía faltan (no alcanza con un contador: hace falta saber CUÁLES).
function cd_sufijos_escaneados_hoy(mysqli $mysqli, string $fecha, int $idTC): array
{
    $st = $mysqli->prepare("SELECT DISTINCT bulto_sufijo FROM crossdocking_eventos
                             WHERE fecha=? AND idTransClientes=? AND resultado IN ('ok','dup')");
    $st->bind_param('si', $fecha, $idTC);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_map(function ($r) {
        return $r['bulto_sufijo']; // string "1","2",... o null (sin sufijo)
    }, $rows);
}

/**
 * Lista de BULTOS pendientes (todavía no ingresados) de un recorrido, para
 * el modal que se abre al tocar la tarjeta.
 *
 * OJO — esto tiene que devolver UN RENGLÓN POR BULTO físico, no uno por
 * envío: la tarjeta ("X de Y") cuenta bultos (SUM(Cantidad) esperados vs.
 * pares idTransClientes+sufijo escaneados), así que si acá listáramos un
 * renglón por envío, un pedido de 3 bultos con 1 solo escaneado contaría
 * como "1 pendiente" en la lista pero "2 bultos" en la resta de la
 * tarjeta - los números no cerrarían entre la tarjeta y el modal (bug real
 * encontrado en la primera versión de este endpoint). Por eso se expande
 * cada envío multi-bulto en sus sufijos _N pendientes puntuales.
 * GET/POST: PendientesCrossdocking=1, recorrido=<numero>
 */
if (isset($_REQUEST['PendientesCrossdocking'])) {
    header('Content-Type: application/json; charset=utf-8');

    $recorrido = (int)($_REQUEST['recorrido'] ?? 0);
    if ($recorrido <= 0) {
        echo json_encode(['ok' => false, 'error' => 'RECORRIDO_INVALIDO']);
        exit;
    }

    $fecha = date('Y-m-d');

    $st = $mysqli->prepare("SELECT id, CodigoSeguimiento, ClienteDestino, DomicilioDestino,
                                    LocalidadDestino, Cantidad, NumerodeOrden, RazonSocial
                             FROM TransClientes
                             WHERE Recorrido=? AND Eliminado=0 AND Entregado=0 AND Devuelto=0
                             ORDER BY CodigoSeguimiento");
    $st->bind_param('i', $recorrido);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

    $pendientes = [];
    foreach ($rows as $row) {
        $idTC = (int)$row['id'];
        $bultoTotal = max(1, (int)($row['Cantidad'] ?? 1));
        $hechos = cd_sufijos_escaneados_hoy($mysqli, $fecha, $idTC);
        $codigoBase = (string)$row['CodigoSeguimiento'];

        if ($bultoTotal === 1) {
            // Sin sufijo: un solo bulto, "hecho" si hay CUALQUIER evento hoy
            // para este envío (el sufijo grabado es null en ese caso).
            if (!empty($hechos)) {
                continue;
            }
            $pendientes[] = [
                'codigoEtiqueta'    => $codigoBase,
                'clienteDestino'    => (string)($row['ClienteDestino'] ?? ''),
                'domicilioDestino'  => (string)($row['DomicilioDestino'] ?? ''),
                'localidadDestino'  => (string)($row['LocalidadDestino'] ?? ''),
                'numeroOrden'       => (int)($row['NumerodeOrden'] ?? 0),
                'origen'            => (string)($row['RazonSocial'] ?? ''),
            ];
            continue;
        }

        for ($i = 1; $i <= $bultoTotal; $i++) {
            if (in_array((string)$i, $hechos, true)) {
                continue; // este bulto puntual ya se escaneó hoy
            }
            $pendientes[] = [
                'codigoEtiqueta'    => $codigoBase . '_' . $i,
                'clienteDestino'    => (string)($row['ClienteDestino'] ?? ''),
                'domicilioDestino'  => (string)($row['DomicilioDestino'] ?? ''),
                'localidadDestino'  => (string)($row['LocalidadDestino'] ?? ''),
                'numeroOrden'       => (int)($row['NumerodeOrden'] ?? 0),
                'origen'            => (string)($row['RazonSocial'] ?? ''),
            ];
        }
    }

    $recInfo = cd_info_recorrido($mysqli, $recorrido);

    // La tarjeta de este recorrido en la grilla solo se actualiza cuando
    // entra un escaneo nuevo PARA ESE RECORRIDO — si a la ruta le asignan un
    // bulto nuevo (o le sacan uno) y nadie vuelve a escanear ahí, la
    // tarjeta se queda mostrando un total viejo indefinidamente (caso real:
    // tarjeta en "26 de 26 ✔" con 1 pendiente real que el propio modal sí
    // veía). Se manda esperados/escaneadosHoy recalculados fresco acá para
    // que el front pueda corregir la tarjeta apenas se abre este modal, sin
    // esperar a que llegue un escaneo.
    $escaneadosHoy = cd_escaneados_hoy_recorrido($mysqli, $fecha, $recorrido);
    $esperados = max($escaneadosHoy, cd_bultos_esperados($mysqli, $recorrido));

    echo json_encode([
        'ok' => true,
        'recorrido' => $recorrido,
        'recorridoNombre' => $recInfo['nombre'],
        'pendientes' => $pendientes,
        'escaneadosHoy' => $escaneadosHoy,
        'esperados' => $esperados,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * "Controlar recorrido": el operador escanea de nuevo, de corrido, TODOS los
 * bultos que tiene físicamente juntos para un recorrido — un control final
 * antes de mandarlo, para detectar algo que se mezcló de otro recorrido.
 *
 * A propósito NO escribe nada en ningún lado (ni crossdocking_eventos ni
 * Wepoint_status) — es un chequeo de lectura pura, la cuenta la lleva el
 * navegador en memoria mientras el modal está abierto. Reusa cd_resolver(),
 * el mismo motor de matching que el escaneo real, así "pertenece a este
 * recorrido" usa exactamente el mismo criterio que resolvería un escaneo
 * normal (Meli/Wepoint_c/Caddy/CodigoProveedor).
 *
 * Un código AMBIGUO (coincide con más de un proveedor) se informa como "no
 * pertenece" acá a propósito — no tiene sentido parar el control a mitad de
 * un escaneo rápido para desambiguar, así que se marca en rojo y el
 * operador puede resolverlo después con un escaneo normal si hace falta.
 *
 * POST: ControlarCrossdocking=1, codigo=<texto>, recorrido=<numero>
 */
if (isset($_POST['ControlarCrossdocking'])) {
    header('Content-Type: application/json; charset=utf-8');

    $raw = trim((string)($_POST['codigo'] ?? ''));
    $recorridoObjetivo = (int)($_POST['recorrido'] ?? 0);
    if ($raw === '' || $recorridoObjetivo <= 0) {
        echo json_encode(['ok' => false, 'error' => 'DATOS_INVALIDOS']);
        exit;
    }

    $tieneSufijo = preg_match('/_(\d+)$/', $raw, $m) === 1;
    $bultoSufijo = $tieneSufijo ? $m[1] : null;

    $resuelto = cd_resolver($mysqli, $raw);
    if (!$resuelto || $resuelto['tipo'] === 'AMBIGUO') {
        echo json_encode([
            'ok' => true,
            'pertenece' => false,
            'codigo' => $raw,
            'motivo' => $resuelto ? 'AMBIGUO' : 'NO_MATCH',
        ]);
        exit;
    }

    $row = $resuelto['row'];
    $idTC = (int)$row['id'];
    $codigoBase = cd_base((string)$row['CodigoSeguimiento']);
    $codigoEtiqueta = $tieneSufijo ? $codigoBase . '_' . $m[1] : (string)$row['CodigoSeguimiento'];
    $recorridoReal = (int)($row['Recorrido'] ?? 0);

    echo json_encode([
        'ok'                => true,
        'pertenece'         => $recorridoReal === $recorridoObjetivo,
        'idTransClientes'   => $idTC,
        'bultoSufijo'       => $bultoSufijo,
        'codigoEtiqueta'    => $codigoEtiqueta,
        'clienteDestino'    => (string)($row['ClienteDestino'] ?? ''),
        'recorridoReal'     => $recorridoReal ?: null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'ACCION_DESCONOCIDA']);
