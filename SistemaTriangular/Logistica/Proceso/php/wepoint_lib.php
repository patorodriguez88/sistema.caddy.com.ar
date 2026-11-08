<?php
// Lo que comparten Logística > Wepoint (wepoint.php), CrossDocking (crossdocking.php) y el cron
// (cron_wepoint.php) para que Wepoint sepa qué entra y qué sale de su depósito sin frenar a nadie:
// los escaneos se anotan en la cola wepoint_cola y se le mandan a Wepoint después (al toque desde
// la pantalla y cada minuto por cron), con reintentos. También el armado del aviso previo y el
// registro de lo enviado en wepoint_api.

if (!defined('ID_WEPOINT')) {
    define('ID_WEPOINT', 18587); // cliente Wepoint en Caddy
}

const WEPOINT_MODALIDADES = ['transito', 'crossdock', 'almacenamiento'];

// URL y token de la API de Wepoint (usuario de integración de la empresa Caddy): están en
// Conexion/wepoint_config.php, fuera del repositorio (ver wepoint_config.example.php).
function wepoint_config(): array
{
    static $config = null;
    if ($config === null) {
        $archivo = __DIR__ . '/../../../Conexion/wepoint_config.php';
        if (is_file($archivo)) {
            require_once $archivo;
        }
        $config = [
            'url' => rtrim(defined('WEPOINT_API_URL') ? WEPOINT_API_URL : '', '/'),
            'token' => defined('WEPOINT_API_TOKEN') ? WEPOINT_API_TOKEN : '',
            'activo' => defined('WEPOINT_ACTIVO') ? (bool)WEPOINT_ACTIVO : true,
        ];
    }
    return $config;
}

/**
 * Freno de emergencia (WEPOINT_ACTIVO en wepoint_config.php): apagado, o sin config (URL o token),
 * Caddy no anota nada para Wepoint.
 */
function wepoint_configurado(): bool
{
    $c = wepoint_config();
    return $c['activo'] && $c['url'] !== '' && $c['token'] !== '';
}

/**
 * La conexión está activa: configurada y encendida en Wepoint (Integraciones). Desactivada, cada
 * sistema sigue solo: los movimientos se acumulan en la cola, no se manda nada y no hay trabas.
 */
function wepoint_activo(mysqli $mysqli): bool
{
    return wepoint_configurado() && wepoint_conexion($mysqli)['activa'];
}

/** @return array{activa:bool,apagada_en:?string} lo último que dijo Wepoint (sin fila, activa) */
function wepoint_conexion(mysqli $mysqli, bool $releer = false): array
{
    static $estado = null;
    if ($estado === null || $releer) {
        $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_conexion (
            id TINYINT PRIMARY KEY,
            activa TINYINT(1) NOT NULL DEFAULT 1,
            apagada_en DATETIME NULL,
            actualizado_en DATETIME NOT NULL
        )");
        $f = $mysqli->query("SELECT activa, apagada_en FROM wepoint_conexion WHERE id = 1")->fetch_assoc();
        $estado = ['activa' => $f ? (bool)$f['activa'] : true, 'apagada_en' => $f['apagada_en'] ?? null];
    }
    return $estado;
}

/** Guarda lo que dijo Wepoint. apagada_en queda hasta conciliar lo que pasó mientras estuvo apagada. */
function wepoint_conexion_guardar(mysqli $mysqli, bool $activa): void
{
    $antes = wepoint_conexion($mysqli, true);
    $a = (int)$activa;
    $apagada = !$activa && $antes['activa'] ? date('Y-m-d H:i:s') : $antes['apagada_en'];
    $st = $mysqli->prepare("REPLACE INTO wepoint_conexion (id, activa, apagada_en, actualizado_en) VALUES (1, ?, ?, NOW())");
    $st->bind_param('is', $a, $apagada);
    $st->execute();
    $st->close();
    wepoint_conexion($mysqli, true);
}

/** Cron: pregunta el estado. Si Wepoint no responde queda el último conocido. */
function wepoint_conexion_actualizar(mysqli $mysqli): void
{
    if (!wepoint_configurado()) {
        return;
    }
    [$http, , $resp] = wepoint_request('GET', '/api/v2/integraciones/caddy/conexion', null, 10);
    $data = json_decode((string)$resp, true)['data'] ?? null;
    if ($http === 200 && isset($data['activa'])) {
        wepoint_conexion_guardar($mysqli, (bool)$data['activa']);
    }
}

/** Lo acumulado desde que se apagó (lo que espera en la cola). @return array{ingresos:int,salidas:int} */
function wepoint_cola_acumulada(mysqli $mysqli): array
{
    wepoint_cola_tabla($mysqli);
    $apagada = wepoint_conexion($mysqli, true)['apagada_en'];
    $r = ['ingresos' => 0, 'salidas' => 0];
    if ($apagada === null) {
        return $r;
    }
    $st = $mysqli->prepare("SELECT tipo, COUNT(*) AS n FROM wepoint_cola WHERE estado IN ('pendiente', 'error') AND creado_en >= ? GROUP BY tipo");
    $st->bind_param('s', $apagada);
    $st->execute();
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $f) {
        $r[$f['tipo'] === 'ingreso' ? 'ingresos' : 'salidas'] = (int)$f['n'];
    }
    $st->close();
    return $r;
}

/** Al reactivar sin mandar lo acumulado: esas filas quedan "descartado" y no se envían. */
function wepoint_cola_descartar(mysqli $mysqli): int
{
    wepoint_cola_tabla($mysqli);
    $apagada = wepoint_conexion($mysqli, true)['apagada_en'];
    if ($apagada === null) {
        return 0;
    }
    $st = $mysqli->prepare("UPDATE wepoint_cola SET estado = 'descartado' WHERE estado IN ('pendiente', 'error') AND creado_en >= ?");
    $st->bind_param('s', $apagada);
    $st->execute();
    $n = $st->affected_rows;
    $st->close();
    if ($mysqli->query("SHOW TABLES LIKE 'wepoint_colectas'")->fetch_row()) {
        $st = $mysqli->prepare("UPDATE wepoint_colectas SET estado = 'descartado' WHERE estado IN ('pendiente', 'error') AND actualizado_en >= ?");
        $st->bind_param('s', $apagada); $st->execute();
        $n += $st->affected_rows; $st->close();
    }
    return $n;
}

/** Desde cuándo mirar lo que salió de Wepoint: lo normal, o desde que se apagó la conexión. */
function wepoint_mirar_desde(mysqli $mysqli, int $dias = 3): string
{
    $normal = date('Y-m-d', strtotime("-$dias days"));
    $apagada = wepoint_conexion($mysqli)['apagada_en'];
    return $apagada !== null && substr($apagada, 0, 10) < $normal ? substr($apagada, 0, 10) : $normal;
}

/**
 * Reactivada la conexión: lo que salió de Wepoint mientras estuvo apagada (órdenes manuales) deja de
 * figurar en el depósito. Hecho esto, se olvida la fecha de apagado.
 */
function wepoint_conciliar_al_reactivar(mysqli $mysqli): int
{
    if (!wepoint_activo($mysqli) || wepoint_conexion($mysqli)['apagada_en'] === null) {
        return 0;
    }
    wepoint_cola_tabla($mysqli);
    [$http, , $resp] = wepoint_request('GET', '/api/v2/integraciones/caddy/bultos?' . http_build_query([
        'vista' => 'salieron', 'desde' => wepoint_mirar_desde($mysqli), 'hasta' => date('Y-m-d'),
    ]), null, 30);
    $json = json_decode((string)$resp, true);
    if ($http !== 200 || !is_array($json)) {
        return 0; // se reintenta en el próximo cron
    }
    $envios = [];
    foreach ($json['data']['bultos'] ?? [] as $b) {
        if (!empty($b['id_caddy'])) {
            $id = (int)$b['id_caddy'];
            $envios[$id]['piezas'] = ($envios[$id]['piezas'] ?? 0) + 1;
            $envios[$id]['nombre'] = $b['retiro'] ?? '';
        }
    }
    $n = 0;
    foreach ($envios as $idTc => $e) {
        $tc = $mysqli->query("SELECT CodigoSeguimiento, Cantidad FROM TransClientes WHERE id = $idTc AND Eliminado = 0")->fetch_assoc();
        $hay = $mysqli->query("SELECT 1 FROM wepoint_cola WHERE id_tc = $idTc AND tipo = 'salida' LIMIT 1")->fetch_row();
        if (!$tc || $hay || $e['piezas'] < max(1, (int)$tc['Cantidad'])) {
            continue;
        }
        $codigo = (string)$tc['CodigoSeguimiento'];
        $st = $mysqli->prepare("INSERT IGNORE INTO wepoint_cola (tipo, id_tc, codigo, serie, fecha, estado, usuario, creado_en, enviado_en)
            VALUES ('salida', ?, ?, ?, CURDATE(), 'ok', 'Wepoint', NOW(), NOW())");
        $st->bind_param('iss', $idTc, $codigo, $codigo);
        $st->execute();
        $st->close();
        wepoint_seguimiento($mysqli, $idTc, WEPOINT_ESTADO_SALIDA, 'Salió de Wepoint con la conexión desactivada' . ($e['nombre'] ? ": {$e['nombre']}" : ''));
        $n++;
    }
    $mysqli->query("UPDATE wepoint_conexion SET apagada_en = NULL WHERE id = 1 AND activa = 1");
    wepoint_conexion($mysqli, true);
    return $n;
}

function wepoint_url(string $path): string
{
    return wepoint_config()['url'] . $path;
}

function wepoint_token(): string
{
    $token = wepoint_config()['token'];
    return $token === '' ? '' : 'Bearer ' . $token;
}

/** @return array{0:int,1:string,2:string|false} [http, error de curl, cuerpo] */
function wepoint_request(string $method, string $path, ?array $body = null, int $timeout = 60): array
{
    if (wepoint_config()['url'] === '' || wepoint_token() === '') {
        return [0, 'Falta configurar Conexion/wepoint_config.php', false];
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => wepoint_url($path),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . wepoint_token(),
        ],
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [$http, $err, $resp];
}

// ---------------------------------------------------------------------------------------------
// Aviso previo: un elemento por envío (TransClientes) con sus piezas.
// ---------------------------------------------------------------------------------------------

/** Columnas de TransClientes (alias TC, con Clientes CO del origen) que necesita el aviso. */
function wepoint_campos_tc(): string
{
    return "TC.id, TC.CodigoSeguimiento, TC.Cantidad, TC.idClienteOrigen, TC.RazonSocial, TC.DomicilioOrigen,
            TC.TelefonoOrigen, CO.Mail AS MailOrigen, TC.ClienteDestino, TC.TelefonoDestino, TC.DomicilioDestino,
            TC.LocalidadDestino, TC.ProvinciaDestino, TC.Ancho, TC.Largo, TC.Alto, TC.Peso, TC.Flex,
            TC.ValorDeclarado, TC.Observaciones, TC.idClienteDestino, TC.Wepoint_status,
            (SELECT MAX(HR.ImporteCobranza) FROM HojaDeRuta HR WHERE HR.idTransClientes = TC.id AND HR.Eliminado = 0) AS Cobranza";
}

/**
 * Quién trajo cada envío al depósito: el recorrido de su colecta y el chofer, la patente y la orden
 * de ese recorrido en la fecha de la colecta; si no vino por colecta (servicio con retiro), el
 * recorrido y la orden del retiro ("Retirado del Cliente" en Seguimiento). Wepoint arma una
 * recepción por recorrido con esto.
 *
 * @param int[] $ids TransClientes.id
 * @return array<int,array{recorrido:string,chofer:?string,patente:?string,orden:?string}>
 */
function wepoint_colectas(mysqli $mysqli, array $ids): array
{
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) {
        return [];
    }
    $filas = $mysqli->query("SELECT TC.id, C.Recorrido, C.Fecha FROM TransClientes TC
        JOIN Colecta C ON C.id = TC.idColecta
        WHERE TC.id IN (" . implode(',', $ids) . ") AND TC.idColecta > 0")->fetch_all(MYSQLI_ASSOC);

    $porEnvio = [];
    $orden = $mysqli->prepare("SELECT NombreChofer, Patente, NumerodeOrden FROM Logistica
        WHERE Recorrido = ? AND Fecha = ? AND Eliminado = 0 ORDER BY id DESC LIMIT 1");
    $ordenes = [];
    foreach ($filas as $f) {
        $recorrido = trim((string)$f['Recorrido']);
        if ($recorrido === '' || $recorrido === '0') {
            continue;
        }
        $clave = $recorrido . '|' . $f['Fecha'];
        if (!array_key_exists($clave, $ordenes)) {
            $orden->bind_param('ss', $recorrido, $f['Fecha']);
            $orden->execute();
            $ordenes[$clave] = $orden->get_result()->fetch_assoc() ?: [];
        }
        $o = $ordenes[$clave];
        $porEnvio[(int)$f['id']] = [
            'recorrido' => mb_substr($recorrido, 0, 20),
            'chofer' => trim((string)($o['NombreChofer'] ?? '')) !== '' ? mb_substr(trim($o['NombreChofer']), 0, 100) : null,
            'patente' => trim((string)($o['Patente'] ?? '')) !== '' ? mb_substr(trim($o['Patente']), 0, 20) : null,
            'orden' => !empty($o['NumerodeOrden']) ? (string)$o['NumerodeOrden'] : null,
        ];
    }
    $orden->close();

    // Sin colecta, o colecta sin orden de ese día: el repartidor que hizo el retiro (la línea de
    // Seguimiento trae su orden)
    $sinColecta = array_values(array_filter($ids, fn($id) => empty($porEnvio[$id]['chofer'])));
    if ($sinColecta) {
        $retiros = $mysqli->query("SELECT sg.idTransClientes AS id, sg.Recorrido, L.NombreChofer, L.Patente, L.NumerodeOrden
            FROM Seguimiento sg
            JOIN (SELECT idTransClientes, MAX(id) AS id FROM Seguimiento
                  WHERE idTransClientes IN (" . implode(',', $sinColecta) . ") AND Eliminado = 0
                    AND (status = 'pickup_ready' OR Estado = 'Retirado del Cliente')
                  GROUP BY idTransClientes) u ON u.id = sg.id
            LEFT JOIN Logistica L ON L.NumerodeOrden = sg.NumerodeOrden AND L.Eliminado = 0 AND sg.NumerodeOrden > 0")->fetch_all(MYSQLI_ASSOC);
        foreach ($retiros as $r) {
            $recorrido = trim((string)$r['Recorrido']);
            if ($recorrido === '' || $recorrido === '0' || (isset($porEnvio[(int)$r['id']]) && trim((string)($r['NombreChofer'] ?? '')) === '')) {
                continue; // la colecta ya dio el recorrido y el retiro no agrega el chofer
            }
            $porEnvio[(int)$r['id']] = [
                'recorrido' => mb_substr($recorrido, 0, 20),
                'chofer' => trim((string)($r['NombreChofer'] ?? '')) !== '' ? mb_substr(trim($r['NombreChofer']), 0, 100) : null,
                'patente' => trim((string)($r['Patente'] ?? '')) !== '' ? mb_substr(trim($r['Patente']), 0, 20) : null,
                'orden' => !empty($r['NumerodeOrden']) ? (string)$r['NumerodeOrden'] : null,
            ];
        }
    }

    return $porEnvio;
}

/** Piezas de la fila (CODIGO o CODIGO_n, como las numera Wepoint). */
function wepoint_series(array $bulto): array
{
    return $bulto['piezas'] > 1
        ? array_map(fn($i) => $bulto['codigo_seguimiento'] . '_' . $i, range(1, $bulto['piezas']))
        : [$bulto['codigo_seguimiento']];
}

/** Flex sale en el próximo recorrido (crossdock); el resto queda en tránsito en Wepoint. */
function wepoint_modalidad_sugerida(array $f): string
{
    return (int)$f['Flex'] === 1 ? 'crossdock' : 'transito';
}

/** Elemento "bultos" del aviso previo para una fila de wepoint_campos_tc(). */
function wepoint_bulto_payload(array $f, string $modalidad): array
{
    $codigo = preg_replace('/_\d+$/', '', trim((string)$f['CodigoSeguimiento']));

    return [
        'codigo_seguimiento' => $codigo,
        'id_caddy' => (int)$f['id'],
        'piezas' => max(1, (int)$f['Cantidad']),
        'modalidad' => in_array($modalidad, WEPOINT_MODALIDADES, true) ? $modalidad : wepoint_modalidad_sugerida($f),
        'observaciones' => $f['Observaciones'] ?: null,
        'valor_declarado' => (float)$f['ValorDeclarado'],
        'contrareembolso' => (float)$f['Cobranza'],
        'es_flex' => (int)$f['Flex'] === 1,
        'remitente' => array_filter([
            'id' => (int)$f['idClienteOrigen'],
            'nombre' => mb_substr(trim((string)$f['RazonSocial']), 0, 100),
            'telefono' => mb_substr(trim((string)$f['TelefonoOrigen']), 0, 50),
            'email' => filter_var(trim((string)$f['MailOrigen']), FILTER_VALIDATE_EMAIL) ?: null,
            'direccion' => mb_substr(trim((string)$f['DomicilioOrigen']), 0, 255),
        ], fn($v) => $v !== null && $v !== ''),
        'destinatario' => array_filter([
            'nombre' => mb_substr(trim((string)$f['ClienteDestino']), 0, 100),
            'telefono' => mb_substr(trim((string)$f['TelefonoDestino']), 0, 50),
            'direccion' => mb_substr(trim((string)$f['DomicilioDestino']), 0, 255),
            'ciudad' => mb_substr(trim((string)$f['LocalidadDestino']), 0, 100),
            'provincia' => mb_substr(trim((string)$f['ProvinciaDestino']), 0, 100),
        ], fn($v) => $v !== ''),
        'medidas' => [
            'ancho' => (int)$f['Ancho'],
            'largo' => (int)$f['Largo'],
            'alto' => (int)$f['Alto'],
            'peso' => (int)ceil((float)$f['Peso']),
        ],
    ];
}

/**
 * Guarda lo que Wepoint dio de alta: un row por pieza en wepoint_api (id_wepoint = id del bulto
 * en Wepoint, lo usa después el egreso) y marca cada envío avisado (TransClientes.wepoint_id) para
 * que salga de la lista de pendientes. Las piezas que ya estaban registradas no se repiten.
 * $idsExtra: otros TransClientes a marcar (el padre de la colecta).
 *
 * @return string[] códigos avisados
 */
function wepoint_guardar_respuesta(mysqli $mysqli, array $data, array $bultos, string $noRef, int $http, array $idsExtra = []): array
{
    $nroOIB = (string)($data['nro_orden_ingreso_bulto'] ?? '');
    $now = date('Y-m-d H:i:s');
    $usuario = $_SESSION['Usuario'] ?? 'Sistema';
    $estado = 'IN OK';
    $tipo = 'IN';
    $idsPorCodigo = [];
    foreach ($bultos as $b) {
        $idsPorCodigo[$b['codigo_seguimiento']] = $b['id_caddy'];
    }

    // Misma clave que el índice único de wepoint_api (pieza + tipo)
    $existe = $mysqli->prepare("SELECT id FROM wepoint_api WHERE tipo = 'IN' AND CodigoSeguimiento_enviado = ? LIMIT 1");
    $ins = $mysqli->prepare("INSERT IGNORE INTO wepoint_api
        (`Time`, `no_referencia`, `nro_orden_ingreso_bulto`, `CodigoSeguimiento`, `CodigoSeguimiento_enviado`,
         `id_wepoint`, `Usuario`, `Estado`, `tipo`, `http_code`)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $upd = $mysqli->prepare("UPDATE TransClientes SET wepoint_id = ? WHERE id = ? AND (wepoint_id = 0 OR wepoint_id IS NULL)");

    $avisados = [];
    $ultimoId = 0;
    foreach (array_merge($data['nuevos'] ?? [], $data['ya_informados'] ?? []) as $envio) {
        $codigo = (string)$envio['codigo_seguimiento'];
        $idDelEnvio = 0;
        foreach ($envio['bultos'] ?? [] as $pieza) {
            $serie = (string)$pieza['nro_serie'];
            $idBulto = (int)$pieza['id_bulto'];
            $existe->bind_param('s', $serie);
            $existe->execute();
            $previo = $existe->get_result()->fetch_assoc();
            if ($previo) {
                $idDelEnvio = (int)$previo['id'];
                continue;
            }
            $ins->bind_param('sssssisssi', $now, $noRef, $nroOIB, $codigo, $serie, $idBulto, $usuario, $estado, $tipo, $http);
            $ins->execute();
            $idDelEnvio = $ins->insert_id ?: $idDelEnvio;
        }
        if ($idDelEnvio && isset($idsPorCodigo[$codigo])) {
            $idTc = (int)$idsPorCodigo[$codigo];
            $upd->bind_param('ii', $idDelEnvio, $idTc);
            $upd->execute();
            $ultimoId = $idDelEnvio;
        }
        $avisados[] = $codigo;
    }
    if ($ultimoId) {
        foreach ($idsExtra as $idTc) {
            $idTc = (int)$idTc;
            $upd->bind_param('ii', $ultimoId, $idTc);
            $upd->execute();
        }
    }
    $existe->close();
    $ins->close();
    $upd->close();

    return $avisados;
}

// ---------------------------------------------------------------------------------------------
// Cola: un row por pieza, tipo (ingreso: escaneo en CrossDocking / salida: carga del chofer en la
// app de reparto) y día. La app de reparto escribe en la misma tabla (misma base).
// ---------------------------------------------------------------------------------------------
/**
 * Cómo está cada envío en Wepoint: la foto que manda Wepoint (estado, órdenes de ingreso, recepción,
 * egreso y envío, posiciones y fechas reales). La muestran Pendientes, Hoja de Ruta y Seguimiento.
 */
function wepoint_trazabilidad_tabla(mysqli $mysqli): void
{
    static $hecho = false;
    if ($hecho) {
        return;
    }
    $hecho = true;
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_trazabilidad (
        id_tc INT NOT NULL PRIMARY KEY,
        codigo VARCHAR(100) NOT NULL,
        estado VARCHAR(30) NULL,
        piezas INT NOT NULL DEFAULT 0,
        en_deposito INT NOT NULL DEFAULT 0,
        oib VARCHAR(50) NULL,
        orb VARCHAR(255) NULL,
        oeb VARCHAR(255) NULL,
        envio VARCHAR(255) NULL,
        transportista VARCHAR(255) NULL,
        posiciones VARCHAR(255) NULL,
        ingreso_at DATETIME NULL,
        salida_at DATETIME NULL,
        actualizado_en DATETIME NOT NULL
    )");
}

/** Guarda la foto de cada envío (se puede repetir). @return int envíos guardados */
function wepoint_trazabilidad_guardar(mysqli $mysqli, array $envios): int
{
    wepoint_trazabilidad_tabla($mysqli);
    $texto = fn($v, int $max) => $v === null || $v === '' ? null : mb_substr((string)$v, 0, $max);
    $fecha = fn($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $v) ? $v : null;
    $guardar = $mysqli->prepare("INSERT INTO wepoint_trazabilidad
        (id_tc, codigo, estado, piezas, en_deposito, oib, orb, oeb, envio, transportista, posiciones, ingreso_at, salida_at, actualizado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE codigo = VALUES(codigo), estado = VALUES(estado), piezas = VALUES(piezas), en_deposito = VALUES(en_deposito),
            oib = VALUES(oib), orb = VALUES(orb), oeb = VALUES(oeb), envio = VALUES(envio), transportista = VALUES(transportista),
            posiciones = VALUES(posiciones), ingreso_at = VALUES(ingreso_at), salida_at = VALUES(salida_at), actualizado_en = NOW()");
    // Las marcas de TransClientes: solo si el envío no tenía ingreso (no se pisa lo que escaneó CrossDocking)
    $marca = $mysqli->prepare("UPDATE TransClientes SET Wepoint_f = DATE(?), Wepoint_h = TIME(?), Wepoint_status = IF(IFNULL(Wepoint_status, '') = '', 'Ingreso', Wepoint_status)
        WHERE id = ? AND (Wepoint_f IS NULL OR Wepoint_f = '0000-00-00') LIMIT 1");
    $guardados = 0;
    foreach ($envios as $e) {
        $id = (int)($e['id_caddy'] ?? 0);
        $codigo = trim((string)($e['codigo'] ?? ''));
        if ($id <= 0 || $codigo === '') {
            continue;
        }
        $tc = $mysqli->query("SELECT id, CodigoSeguimiento FROM TransClientes WHERE id = $id")->fetch_assoc();
        if (!$tc || (string)$tc['CodigoSeguimiento'] !== $codigo) {
            error_log("wepoint_trazabilidad: el envío $id no coincide con el código $codigo");
            continue;
        }
        $estado = $texto($e['estado'] ?? null, 30);
        $piezas = (int)($e['piezas'] ?? 0);
        $enDeposito = (int)($e['en_deposito'] ?? 0);
        $oib = $texto($e['oib'] ?? null, 50);
        $orb = $texto($e['orb'] ?? null, 255);
        $oeb = $texto($e['oeb'] ?? null, 255);
        $envio = $texto($e['envio'] ?? null, 255);
        $transportista = $texto($e['transportista'] ?? null, 255);
        $posiciones = $texto($e['posiciones'] ?? null, 255);
        $ingreso = $fecha($e['ingreso_at'] ?? null);
        $salida = $fecha($e['salida_at'] ?? null);
        $guardar->bind_param('issiissssssss', $id, $codigo, $estado, $piezas, $enDeposito, $oib, $orb, $oeb, $envio, $transportista, $posiciones, $ingreso, $salida);
        $guardar->execute();
        if ($ingreso !== null) {
            $marca->bind_param('ssi', $ingreso, $ingreso, $id);
            $marca->execute();
        }
        $guardados++;
    }
    $guardar->close();
    $marca->close();

    return $guardados;
}

/** La foto de estos envíos. @param int[] $ids @return array<int,array> por id de TransClientes */
function wepoint_trazabilidad_de(mysqli $mysqli, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (!$ids) {
        return [];
    }
    wepoint_trazabilidad_tabla($mysqli);
    $porEnvio = [];
    foreach (array_chunk($ids, 1000) as $parte) {
        foreach ($mysqli->query("SELECT * FROM wepoint_trazabilidad WHERE id_tc IN (" . implode(',', $parte) . ")")->fetch_all(MYSQLI_ASSOC) as $f) {
            $porEnvio[(int)$f['id_tc']] = $f;
        }
    }

    return $porEnvio;
}

function wepoint_cola_tabla(mysqli $mysqli): void
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_cola (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo VARCHAR(10) NOT NULL,
        id_tc INT NOT NULL,
        codigo VARCHAR(100) NOT NULL,
        serie VARCHAR(100) NOT NULL,
        fecha DATE NOT NULL,
        recorrido VARCHAR(20) NULL,
        chofer VARCHAR(100) NULL,
        estado VARCHAR(12) NOT NULL DEFAULT 'pendiente',
        intentos INT NOT NULL DEFAULT 0,
        proximo_intento DATETIME NULL,
        ultimo_error VARCHAR(500) NULL,
        nro_orden VARCHAR(30) NULL,
        usuario VARCHAR(100) NULL,
        creado_en DATETIME NOT NULL,
        enviado_en DATETIME NULL,
        UNIQUE KEY uq_wepoint_cola (tipo, serie, fecha),
        KEY ix_wepoint_cola_estado (estado, proximo_intento),
        KEY ix_wepoint_cola_tc (id_tc, tipo)
    )");
}

function wepoint_encolar(mysqli $mysqli, string $tipo, int $idTc, string $codigo, string $serie, string $usuario, ?string $recorrido = null, ?string $chofer = null): void
{
    // Con la conexión desactivada también se anota: se acumula hasta que se reactive
    if (!wepoint_configurado()) {
        return;
    }
    wepoint_cola_tabla($mysqli);
    // Repetido el mismo día: no se duplica, y si había fallado se reintenta ya
    $st = $mysqli->prepare("INSERT INTO wepoint_cola (tipo, id_tc, codigo, serie, fecha, recorrido, chofer, usuario, creado_en)
        VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE proximo_intento = IF(estado = 'error', NOW(), proximo_intento)");
    $st->bind_param('sisssss', $tipo, $idTc, $codigo, $serie, $recorrido, $chofer, $usuario);
    $st->execute();
    $st->close();
}

/** Marca rows de la cola: ok, o error con reintento cada vez más espaciado (2, 4, 8… hasta 60 min). */
function wepoint_cola_marcar(mysqli $mysqli, array $ids, bool $ok, ?string $mensaje = null, ?string $nroOrden = null): void
{
    if (!$ids) {
        return;
    }
    $lista = implode(',', array_map('intval', $ids));
    if ($ok) {
        $st = $mysqli->prepare("UPDATE wepoint_cola SET estado = 'ok', enviado_en = NOW(), ultimo_error = ?, nro_orden = ? WHERE id IN ($lista)");
        $st->bind_param('ss', $mensaje, $nroOrden);
    } else {
        $mensaje = mb_substr((string)$mensaje, 0, 500);
        $st = $mysqli->prepare("UPDATE wepoint_cola SET estado = 'error', intentos = intentos + 1, ultimo_error = ?,
            proximo_intento = NOW() + INTERVAL LEAST(POW(2, intentos + 1), 60) MINUTE WHERE id IN ($lista)");
        $st->bind_param('s', $mensaje);
    }
    $st->execute();
    $st->close();
}

/**
 * Manda a Wepoint lo pendiente de la cola (primero ingresos, después salidas). Un solo proceso a la
 * vez (GET_LOCK): si ya hay uno corriendo, no hace nada.
 *
 * @return array{ingresos:int,salidas:int,errores:int,ocupado?:bool}
 */
function wepoint_procesar_cola(mysqli $mysqli, int $limite = 300): array
{
    if (!wepoint_activo($mysqli)) {
        return ['ingresos' => 0, 'salidas' => 0, 'errores' => 0, 'apagado' => true];
    }
    wepoint_cola_tabla($mysqli);
    // Se descartó el ingreso de un envío (al reactivar): su salida tampoco tiene nada que avisar
    $mysqli->query("UPDATE wepoint_cola s SET s.estado = 'descartado' WHERE s.tipo = 'salida' AND s.estado IN ('pendiente', 'error')
        AND EXISTS (SELECT 1 FROM (SELECT id_tc FROM wepoint_cola WHERE tipo = 'ingreso' AND estado = 'descartado') d WHERE d.id_tc = s.id_tc)
        AND NOT EXISTS (SELECT 1 FROM (SELECT id_tc FROM wepoint_cola WHERE tipo = 'ingreso' AND estado = 'ok') o WHERE o.id_tc = s.id_tc)");
    $r = $mysqli->query("SELECT GET_LOCK('wepoint_cola', 0) AS ok")->fetch_assoc();
    if ((int)$r['ok'] !== 1) {
        return ['ingresos' => 0, 'salidas' => 0, 'errores' => 0, 'ocupado' => true];
    }

    $resultado = ['ingresos' => 0, 'salidas' => 0, 'errores' => 0];
    try {
        $filas = $mysqli->query("SELECT * FROM wepoint_cola
            WHERE estado IN ('pendiente', 'error') AND (proximo_intento IS NULL OR proximo_intento <= NOW())
            ORDER BY (tipo = 'salida'), id LIMIT " . (int)$limite)->fetch_all(MYSQLI_ASSOC);

        $ingresos = array_filter($filas, fn($f) => $f['tipo'] === 'ingreso');
        foreach (array_chunk(array_values($ingresos), 100) as $lote) {
            foreach (wepoint_agrupar($lote, 'fecha') as $fecha => $deFecha) {
                $r = wepoint_mandar_protegido($mysqli, $deFecha, fn() => wepoint_mandar_ingresos($mysqli, (string)$fecha, $deFecha));
                $resultado['ingresos'] += $r['ok'];
                $resultado['errores'] += $r['error'];
            }
        }

        $salidas = array_filter($filas, fn($f) => $f['tipo'] === 'salida');
        foreach (wepoint_agrupar(array_values($salidas), 'recorrido') as $recorrido => $deRecorrido) {
            $r = wepoint_mandar_protegido($mysqli, $deRecorrido, fn() => wepoint_mandar_salidas($mysqli, (string)$recorrido, $deRecorrido));
            $resultado['salidas'] += $r['ok'];
            $resultado['errores'] += $r['error'];
        }
    } finally {
        $mysqli->query("DO RELEASE_LOCK('wepoint_cola')");
    }

    return $resultado;
}

/** Un error inesperado en un grupo no traba la cola: esas filas quedan con el error y se reintentan. */
function wepoint_mandar_protegido(mysqli $mysqli, array $filas, callable $mandar): array
{
    try {
        return $mandar();
    } catch (\Throwable $e) {
        error_log('Cola Wepoint: ' . $e->getMessage());
        wepoint_cola_marcar($mysqli, array_column($filas, 'id'), false, 'Error en Caddy: ' . $e->getMessage());
        return ['ok' => 0, 'error' => count($filas)];
    }
}

function wepoint_agrupar(array $filas, string $campo): array
{
    $grupos = [];
    foreach ($filas as $f) {
        $grupos[(string)$f[$campo]][] = $f;
    }
    return $grupos;
}

/** Ingresos escaneados un mismo día: aviso previo de cada envío + recibir las piezas leídas. */
function wepoint_mandar_ingresos(mysqli $mysqli, string $fecha, array $filas): array
{
    require_once __DIR__ . '/wepoint_colectas.php';
    if (wepoint_colectas_habilitadas()) {
        $idsTc = implode(',', array_map(fn ($f) => (int) $f['id_tc'], $filas));
        $idsColecta = array_column($mysqli->query("SELECT DISTINCT idColecta FROM TransClientes WHERE id IN ($idsTc) AND idColecta > 0")->fetch_all(MYSQLI_ASSOC), 'idColecta');
        wepoint_colectas_procesar($mysqli, null, $idsColecta);
        // Si todavía falta publicar la colecta, no crear un ingreso diario con sus escaneos.
        $idsTc = implode(',', array_map(fn ($f) => (int) $f['id_tc'], $filas));
        $bloqueados = $mysqli->query("SELECT TC.id FROM TransClientes TC JOIN wepoint_colectas W ON W.id_colecta = TC.idColecta
            WHERE TC.id IN ($idsTc) AND W.estado IN ('pendiente', 'error')")->fetch_all(MYSQLI_ASSOC);
        $bloqueados = array_flip(array_column($bloqueados, 'id'));
        $esperan = array_values(array_filter($filas, fn ($f) => isset($bloqueados[$f['id_tc']])));
        if ($esperan) {
            wepoint_cola_marcar($mysqli, array_column($esperan, 'id'), false, 'Esperando sincronización de la orden de ingreso de la colecta');
            $resto = array_values(array_filter($filas, fn ($f) => !isset($bloqueados[$f['id_tc']])));
            $r = $resto ? wepoint_mandar_ingresos($mysqli, $fecha, $resto) : ['ok' => 0, 'error' => 0];
            return ['ok' => $r['ok'], 'error' => $r['error'] + count($esperan)];
        }
    }
    $ids = array_values(array_unique(array_map(fn($f) => (int)$f['id_tc'], $filas)));
    $envios = $mysqli->query("SELECT " . wepoint_campos_tc() . " FROM TransClientes TC
        LEFT JOIN Clientes CO ON CO.id = TC.idClienteOrigen WHERE TC.id IN (" . implode(',', $ids) . ")")->fetch_all(MYSQLI_ASSOC);

    $colectas = wepoint_colectas($mysqli, $ids);
    $bultos = [];
    $sinOrigen = [];
    foreach ($envios as $e) {
        if ((int)$e['idClienteOrigen'] <= 0) {
            $sinOrigen[(int)$e['id']] = true;
            continue;
        }
        $bultos[] = wepoint_bulto_payload($e, wepoint_modalidad_sugerida($e))
            + (isset($colectas[(int)$e['id']]) ? ['colecta' => $colectas[(int)$e['id']]] : []);
    }

    $sinOrigenRows = array_column(array_filter($filas, fn($f) => isset($sinOrigen[(int)$f['id_tc']])), 'id');
    wepoint_cola_marcar($mysqli, $sinOrigenRows, false, 'El envío no tiene cliente de origen en Caddy');
    $filas = array_values(array_filter($filas, fn($f) => !isset($sinOrigen[(int)$f['id_tc']])));
    if (!$filas) {
        return ['ok' => 0, 'error' => count($sinOrigenRows)];
    }

    $noRef = 'CROSSDOCKING-' . $fecha;
    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/preavisos', [
        'referencia' => $noRef,
        'fecha' => $fecha,
        'recibir' => array_values(array_unique(array_column($filas, 'serie'))),
        'bultos' => $bultos,
    ], 30);
    $json = json_decode((string)$resp, true);

    if ($http < 200 || $http >= 300 || !is_array($json)) {
        $motivo = $http ? "Wepoint respondió HTTP $http" . (is_array($json) && !empty($json['message']) ? ': ' . $json['message'] : '') : ($err ?: 'Wepoint no responde');
        wepoint_cola_marcar($mysqli, array_column($filas, 'id'), false, $motivo);
        return ['ok' => 0, 'error' => count($filas) + count($sinOrigenRows)];
    }

    $data = $json['data'] ?? [];
    wepoint_guardar_respuesta($mysqli, $data, $bultos, $noRef, $http);
    $recibidas = array_flip(array_merge($data['recepcion']['recibidos'] ?? [], $data['recepcion']['ya_recibidos'] ?? []));
    $rechazos = array_column($data['rechazados'] ?? [], 'motivo', 'codigo_seguimiento');

    $ok = $mal = [];
    foreach ($filas as $f) {
        if (isset($recibidas[$f['serie']])) {
            $ok[] = $f['id'];
        } else {
            $mal[$rechazos[$f['codigo']] ?? 'Wepoint no lo dio por recibido'][] = $f['id'];
        }
    }
    wepoint_cola_marcar($mysqli, $ok, true, null, $data['nro_orden_ingreso_bulto'] ?? null);
    foreach ($mal as $motivo => $idsMal) {
        wepoint_cola_marcar($mysqli, $idsMal, false, $motivo);
    }
    $okIds = array_flip($ok);
    require_once __DIR__ . '/wepoint_egresos.php';
    foreach (array_unique(array_map(fn($f) => (int)$f['id_tc'], array_filter($filas, fn($f) => isset($okIds[$f['id']])))) as $idTc) {
        wepoint_seguimiento($mysqli, $idTc, WEPOINT_ESTADO_INGRESO, 'Ingresó al depósito de Wepoint');
        wepoint_egreso_sumar_llegado($mysqli, $idTc); // su recorrido ya pidió el egreso: se le pide sumarlo
    }

    return ['ok' => count($ok), 'error' => count($filas) - count($ok) + count($sinOrigenRows)];
}

/** Lo que cargó el chofer de un recorrido: sale del depósito de Wepoint. */
function wepoint_mandar_salidas(mysqli $mysqli, string $recorrido, array $filas): array
{
    // Los datos del repartidor para la orden de envío: la orden de ese recorrido hoy
    $st = $mysqli->prepare("SELECT NombreChofer, Patente, NumerodeOrden FROM Logistica WHERE Recorrido = ? AND Eliminado = 0 AND Fecha <= CURDATE() ORDER BY Fecha DESC, id DESC LIMIT 1");
    $st->bind_param('s', $recorrido);
    $st->execute();
    $orden = $st->get_result()->fetch_assoc() ?: [];
    $st->close();
    $chofer = trim((string)($filas[0]['chofer'] ?? '')) ?: trim((string)($orden['NombreChofer'] ?? ''));

    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/salidas', [
        'codigos' => array_values(array_unique(array_column($filas, 'codigo'))),
        'recorrido' => $recorrido,
        'chofer' => $chofer !== '' ? mb_substr($chofer, 0, 100) : null,
        'patente' => trim((string)($orden['Patente'] ?? '')) !== '' ? mb_substr(trim($orden['Patente']), 0, 20) : null,
        'orden' => !empty($orden['NumerodeOrden']) ? (string)$orden['NumerodeOrden'] : null,
    ], 30);
    $json = json_decode((string)$resp, true);

    if ($http < 200 || $http >= 300 || !is_array($json)) {
        $motivo = $http ? "Wepoint respondió HTTP $http" : ($err ?: 'Wepoint no responde');
        wepoint_cola_marcar($mysqli, array_column($filas, 'id'), false, $motivo);
        return ['ok' => 0, 'error' => count($filas)];
    }

    $data = $json['data'] ?? [];
    $salieron = array_merge($data['egresados'] ?? [], $data['ya_egresados'] ?? []);
    $base = fn($serie) => preg_replace('/_\d+$/', '', (string)$serie);
    $codigosQueSalieron = array_flip(array_map($base, $salieron));
    $noEstan = array_flip($data['no_encontrados'] ?? []);

    $ok = $mal = [];
    foreach ($filas as $f) {
        if (isset($codigosQueSalieron[$f['codigo']])) {
            $ok[] = $f['id'];
        } else {
            $mal[isset($noEstan[$f['codigo']]) ? 'Wepoint todavía no lo tiene (se reintenta)' : 'Wepoint no lo tenía recibido'][] = $f['id'];
        }
    }
    wepoint_cola_marcar($mysqli, $ok, true, null, $data['nro_orden_egreso_bulto'] ?? null);
    $egresados = array_flip(array_map($base, $data['egresados'] ?? []));
    foreach ($filas as $f) {
        if (isset($egresados[$f['codigo']])) {
            wepoint_seguimiento($mysqli, (int)$f['id_tc'], WEPOINT_ESTADO_SALIDA, 'Salió del depósito de Wepoint' . (!empty($data['nro_orden_egreso_bulto']) ? " ({$data['nro_orden_egreso_bulto']})" : ''));
        }
    }
    foreach ($mal as $motivo => $idsMal) {
        wepoint_cola_marcar($mysqli, $idsMal, false, $motivo);
    }

    return ['ok' => count($ok), 'error' => count($filas) - count($ok)];
}

// ---------------------------------------------------------------------------------------------
// Retiros en el depósito: si el destinatario retiró el paquete en Wepoint, el envío de Caddy se
// cierra como entregado (igual que una entrega cargada a mano en Seguimiento), así no queda en
// Pendientes ni se puede volver a asignar a un recorrido. Lo corre el cron.
// ---------------------------------------------------------------------------------------------
/** Estados de Seguimiento propios del depósito de Wepoint (migración 2026_11_04_estados_wepoint.sql). */
const WEPOINT_ESTADO_INGRESO = 'wepoint_in';  // "En centro de distribución" (lo ve el destinatario)
const WEPOINT_ESTADO_SALIDA = 'wepoint_out';  // "Salió del depósito" (interno)

/**
 * Deja asentado en Seguimiento un movimiento del envío en el depósito de Wepoint con su estado
 * propio ($slug, WEPOINT_ESTADO_*): no dispara webhooks ni avisos y Tienda Nube no lo publica; si
 * el destinatario lo ve lo decide Estados.Mostrar. Sin la migración cae a "Movimiento Interno".
 * Copia Visitas de la última fila (el webhook de Meli las toma de ahí). Una sola vez por texto.
 */
function wepoint_seguimiento(mysqli $mysqli, int $idTc, string $slug, string $obs, ?string $cuando = null, string $usuario = 'Wepoint'): bool
{
    $tc = $mysqli->query("SELECT CodigoSeguimiento, Recorrido, NumerodeOrden FROM TransClientes WHERE id = $idTc AND Eliminado = 0")->fetch_assoc();
    if (!$tc) {
        return false;
    }
    // Por código (Seguimiento tiene índice por CodigoSeguimiento, no por idTransClientes)
    $codigo = (string)$tc['CodigoSeguimiento'];
    $st = $mysqli->prepare("SELECT Observaciones, Destino, Avisado, idCliente, Retirado, Visitas FROM Seguimiento
        WHERE CodigoSeguimiento = ? AND idTransClientes = ? AND Eliminado = 0 ORDER BY id DESC");
    $st->bind_param('si', $codigo, $idTc);
    $st->execute();
    $filas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    if (in_array($obs, array_column($filas, 'Observaciones'), true)) {
        return false;
    }
    $ultimo = $filas[0] ?? [];
    static $estados = [];
    if (!isset($estados[$slug])) {
        $st = $mysqli->prepare("SELECT id, Estado, Slug FROM Estados WHERE Slug IN (?, 'internal_shift') ORDER BY Slug = ? DESC, id LIMIT 1");
        $st->bind_param('ss', $slug, $slug);
        $st->execute();
        $estados[$slug] = $st->get_result()->fetch_assoc() ?: ['id' => 0, 'Estado' => 'Movimiento Interno', 'Slug' => 'internal_shift'];
        $st->close();
    }
    $estado = $estados[$slug];

    $st = $mysqli->prepare("INSERT INTO Seguimiento (Fecha, Hora, Usuario, Sucursal, CodigoSeguimiento, Observaciones,
        Entregado, Estado, Destino, Avisado, idCliente, Retirado, Visitas, idTransClientes, TimeStamp,
        Recorrido, Devuelto, Webhook, Estado_id, state_id, NumerodeOrden, status, Eliminado)
        VALUES (DATE(?), DATE_FORMAT(?, '%H:%i'), ?, '', ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0, 0, ?, ?, ?, ?, 0)");
    $cuando = $cuando ?: date('Y-m-d H:i:s');
    $nombreEstado = (string)$estado['Estado'];
    $destino = (string)($ultimo['Destino'] ?? '');
    $avisado = (int)($ultimo['Avisado'] ?? 0);
    $idCliente = (int)($ultimo['idCliente'] ?? 0);
    $retirado = (int)($ultimo['Retirado'] ?? 0);
    $visitas = (int)($ultimo['Visitas'] ?? 0);
    $recorrido = (string)$tc['Recorrido'];
    $estadoId = (int)$estado['id'];
    $orden = (string)$tc['NumerodeOrden'];
    $status = (string)$estado['Slug'];
    $st->bind_param('sssssssiiiiisiiss', $cuando, $cuando, $usuario, $codigo, $obs, $nombreEstado, $destino, $avisado, $idCliente, $retirado, $visitas, $idTc, $recorrido, $estadoId, $estadoId, $orden, $status);
    $ok = $st->execute();
    $st->close();

    return $ok;
}

/** Lo que entró a Wepoint y todavía no tiene su línea en Seguimiento (lo de antes de que existiera). */
function wepoint_seguimiento_pendientes(mysqli $mysqli, int $dias = 1): int
{
    if (!wepoint_activo($mysqli)) {
        return 0;
    }
    wepoint_cola_tabla($mysqli);
    $filas = $mysqli->query("SELECT i.id_tc, MIN(i.enviado_en) AS entro FROM wepoint_cola i
        JOIN TransClientes TC ON TC.id = i.id_tc
        WHERE i.tipo = 'ingreso' AND i.estado = 'ok' AND i.fecha >= CURDATE() - INTERVAL $dias DAY
          AND NOT EXISTS (SELECT 1 FROM Seguimiento sg WHERE sg.CodigoSeguimiento = TC.CodigoSeguimiento AND sg.Eliminado = 0
                          AND sg.Observaciones = 'Ingresó al depósito de Wepoint')
        GROUP BY i.id_tc LIMIT 300")->fetch_all(MYSQLI_ASSOC);
    $n = 0;
    foreach ($filas as $f) {
        $n += (int)wepoint_seguimiento($mysqli, (int)$f['id_tc'], WEPOINT_ESTADO_INGRESO, 'Ingresó al depósito de Wepoint', $f['entro'] ?: null);
    }

    return $n;
}

/**
 * Lo que salió de Caddy sin pasar por Warehouse (Flex leído con la app de Meli, recorrido sin control
 * de escaneo, entregado o devuelto) también sale de Wepoint: si un envío que entró por la cola tiene
 * después una salida de warehouse o un cierre en Seguimiento, se encola su salida. Mira todo lo que
 * sigue en Wepoint de los últimos $dias días (un envío en tránsito o almacenado puede quedar semanas).
 *
 * @return int envíos encolados
 */
function wepoint_conciliar_salidas(mysqli $mysqli, int $dias = 60): int
{
    if (!wepoint_activo($mysqli)) {
        return 0;
    }
    wepoint_cola_tabla($mysqli);
    $filas = $mysqli->query("SELECT e.id_tc, e.codigo, e.Recorrido FROM (
            SELECT i.id_tc, i.codigo, MIN(i.creado_en) AS entro, TC.Recorrido, TC.CodigoSeguimiento
            FROM wepoint_cola i
            JOIN TransClientes TC ON TC.id = i.id_tc AND TC.Eliminado = 0
            WHERE i.tipo = 'ingreso' AND i.estado = 'ok' AND i.fecha >= CURDATE() - INTERVAL $dias DAY
              AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.tipo = 'salida' AND s.id_tc = i.id_tc)
            GROUP BY i.id_tc, i.codigo, TC.Recorrido, TC.CodigoSeguimiento
        ) e
        WHERE EXISTS (SELECT 1 FROM Seguimiento sg
            WHERE sg.CodigoSeguimiento = e.CodigoSeguimiento AND sg.Eliminado = 0 AND sg.Usuario <> 'Wepoint'
              AND TIMESTAMP(sg.Fecha, sg.Hora) >= e.entro
              AND (sg.status IN ('last_mile', 'warehouse_validated', 'delivered', '1st_visit_fail', 'returned_to_origin')
                   OR sg.Estado IN ('Entregado al Cliente', 'No se pudo entregar', 'Devuelto al Cliente')))
        LIMIT 500")->fetch_all(MYSQLI_ASSOC);

    foreach ($filas as $f) {
        wepoint_encolar($mysqli, 'salida', (int)$f['id_tc'], (string)$f['codigo'], (string)$f['codigo'], 'Wepoint', (string)(int)$f['Recorrido'], 'Salió sin escaneo en Warehouse');
    }

    return count($filas);
}

/**
 * El destinatario retiró en Wepoint: queda entregado igual que un pedido normal (Seguimiento con
 * Entregado, TransClientes con Estado, Entregado, Retirado y FechaEntrega, y la parada cerrada). Una
 * sola vez: si ya estaba entregado no hace nada y devuelve false.
 */
function wepoint_cerrar_entregado(mysqli $mysqli, int $idTc, string $obs, string $nombre, string $dni, ?string $cuando = null): bool
{
    $cuando = $cuando ?: date('Y-m-d H:i:s');
    [$fecha, $hora] = array_pad(explode(' ', $cuando), 2, '00:00:00');
    $estado = $mysqli->query("SELECT id, Estado FROM Estados WHERE Slug = 'delivered' LIMIT 1")->fetch_assoc() ?: ['id' => 7, 'Estado' => 'Entregado al Cliente'];
    $nombreEstado = (string)$estado['Estado'];
    $estadoId = (int)$estado['id'];

    $info = 'Wepoint (retiro en depósito) ' . date('Y-m-d H:i');
    $st = $mysqli->prepare("UPDATE TransClientes SET Estado = ?, Entregado = 1, Retirado = 1, Devuelto = 0, FechaEntrega = ?, infoABM = ?
        WHERE id = ? AND Entregado = 0 AND Eliminado = 0 LIMIT 1");
    $st->bind_param('sssi', $nombreEstado, $fecha, $info, $idTc);
    $st->execute();
    $cerrado = $st->affected_rows === 1;
    $st->close();
    if (!$cerrado) {
        return false;
    }

    $tc = $mysqli->query("SELECT CodigoSeguimiento, Recorrido, NumerodeOrden FROM TransClientes WHERE id = $idTc")->fetch_assoc();
    $codigo = (string)$tc['CodigoSeguimiento'];
    $esc = $mysqli->real_escape_string($codigo);
    $ultimo = $mysqli->query("SELECT Destino, Avisado, idCliente, Visitas FROM Seguimiento
        WHERE CodigoSeguimiento = '$esc' AND Eliminado = 0 ORDER BY id DESC LIMIT 1")->fetch_assoc() ?: [];
    $st = $mysqli->prepare("INSERT INTO Seguimiento (Fecha, Hora, Usuario, Sucursal, CodigoSeguimiento, Observaciones,
        Entregado, Estado, NombreCompleto, Dni, Destino, Avisado, idCliente, Retirado, Visitas, idTransClientes, TimeStamp,
        Recorrido, Devuelto, Webhook, Estado_id, state_id, NumerodeOrden, status, Eliminado)
        VALUES (?, ?, 'Wepoint', '', ?, ?, 1, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW(), ?, 0, 0, ?, ?, ?, 'delivered', 0)");
    $hora = substr($hora, 0, 5);
    $nombre = mb_substr($nombre, 0, 100);
    $dni = mb_substr($dni, 0, 20);
    $destino = (string)($ultimo['Destino'] ?? '');
    $avisado = (int)($ultimo['Avisado'] ?? 0);
    $idCliente = (int)($ultimo['idCliente'] ?? 0);
    $visitas = (int)($ultimo['Visitas'] ?? 0) + 1;
    $recorrido = (string)$tc['Recorrido'];
    $orden = (string)$tc['NumerodeOrden'];
    $st->bind_param('ssssssssiiiisiis', $fecha, $hora, $codigo, $obs, $nombreEstado, $nombre, $dni, $destino, $avisado, $idCliente, $visitas, $idTc, $recorrido, $estadoId, $estadoId, $orden);
    $st->execute();
    $st->close();

    $mysqli->query("UPDATE HojaDeRuta SET Estado = 'Cerrado' WHERE Seguimiento = '$esc' AND Eliminado = 0");
    $mysqli->query("UPDATE Roadmap SET Estado = 'Cerrado' WHERE Seguimiento = '$esc' AND Eliminado = 0");

    return true;
}

function wepoint_cerrar_retiros(mysqli $mysqli, int $dias = 3): int
{
    if (!wepoint_activo($mysqli)) {
        return 0;
    }
    $desde = wepoint_mirar_desde($mysqli, $dias);
    [$http, , $resp] = wepoint_request('GET', '/api/v2/integraciones/caddy/bultos?' . http_build_query([
        'vista' => 'salieron', 'desde' => $desde, 'hasta' => date('Y-m-d'),
    ]), null, 20);
    $json = json_decode((string)$resp, true);
    if ($http !== 200 || !is_array($json)) {
        return 0;
    }

    // Por envío de Caddy: las piezas retiradas y el último retiro
    $retiros = [];
    foreach ($json['data']['bultos'] ?? [] as $b) {
        if (($b['estado'] ?? '') !== 'Retirado en depósito' || empty($b['id_caddy'])) {
            continue;
        }
        $id = (int)$b['id_caddy'];
        $retiros[$id]['piezas'] = ($retiros[$id]['piezas'] ?? 0) + 1;
        if (($b['salida'] ?? '') >= ($retiros[$id]['salida'] ?? '')) {
            $retiros[$id] = array_merge($retiros[$id], ['salida' => $b['salida'], 'nombre' => $b['retiro'] ?? '', 'dni' => $b['retiro_dni'] ?? '']);
        }
    }
    if (!$retiros) {
        return 0;
    }

    $abiertos = $mysqli->query("SELECT id, CodigoSeguimiento, Cantidad, Recorrido, NumerodeOrden FROM TransClientes
        WHERE id IN (" . implode(',', array_keys($retiros)) . ") AND Entregado = 0 AND Eliminado = 0")->fetch_all(MYSQLI_ASSOC);

    $cerrados = 0;
    foreach ($abiertos as $tc) {
        $r = $retiros[(int)$tc['id']];
        if ($r['piezas'] < max(1, (int)$tc['Cantidad'])) {
            continue; // todavía no retiró todas las piezas
        }
        $obs = 'Retirado en el depósito de Wepoint por ' . trim($r['nombre'] . ($r['dni'] ? " (DNI {$r['dni']})" : ''));
        if (!wepoint_cerrar_entregado($mysqli, (int)$tc['id'], $obs, (string)$r['nombre'], (string)$r['dni'], (string)$r['salida'])) {
            continue;
        }
        $cerrados++;
    }

    return $cerrados;
}
