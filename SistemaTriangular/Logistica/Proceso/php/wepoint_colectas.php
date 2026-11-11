<?php
// Preaviso por colecta confirmada. El padre es el servicio de retiro: se anuncian sus HIJOS,
// nunca el padre como si fuera otra pieza. La cola conserva el snapshot y su versión al reintentar.
require_once __DIR__ . '/wepoint_lib.php';

function wepoint_colectas_habilitadas(): bool
{
    if (!wepoint_configurado()) return false;
    // El interruptor explícito conserva prioridad para poder desactivar el flujo.
    if (defined('WEPOINT_COLECTAS_ACTIVAS')) return (bool) WEPOINT_COLECTAS_ACTIVAS;
    $host = preg_replace('/^www\.|:\d+$/', '', strtolower($_SERVER['HTTP_HOST'] ?? ''));
    $sandbox = (defined('ENTORNO') && ENTORNO === 'sandbox') || $host === 'sandbox.sistema.caddy.com.ar';
    $produccion = (defined('ENTORNO') && ENTORNO === 'produccion') || $host === 'sistema.caddy.com.ar';
    return ($sandbox && wepoint_config()['url'] === 'https://testing.wepoint.ar')
        || ($produccion && wepoint_config()['url'] === 'https://sistema.wepoint.ar');
}

function wepoint_colectas_tablas(mysqli $mysqli): void
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_colectas (
        id_colecta BIGINT UNSIGNED PRIMARY KEY,
        version BIGINT UNSIGNED NOT NULL DEFAULT 0,
        payload_hash CHAR(64) NULL,
        payload LONGTEXT NULL,
        estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
        nro_ingreso VARCHAR(100) NULL,
        ultimo_error VARCHAR(500) NULL,
        proximo_intento DATETIME NULL,
        actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        enviado_en DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_colectas_inicio (
        id TINYINT PRIMARY KEY, id_desde BIGINT UNSIGNED NOT NULL
    ) ENGINE=InnoDB");
    // No se importan colectas históricas al instalar. El hook anuncia las que se confirman desde ahora.
    $mysqli->query("INSERT IGNORE INTO wepoint_colectas_inicio (id, id_desde) SELECT 1, COALESCE(MAX(id), 0) FROM Colecta");
}

function wepoint_colecta_snapshot(mysqli $mysqli, int $id): ?array
{
    $c = $mysqli->query("SELECT C.id, C.Fecha, C.Eliminado, P.id AS padre, P.Eliminado AS padre_eliminado, P.idClienteDestino
        FROM Colecta C JOIN TransClientes P ON P.idColecta = C.id AND P.CodigoSeguimiento = C.CodigoSeguimiento
        WHERE C.id = $id ORDER BY P.id LIMIT 1")->fetch_assoc();
    if (!$c) return null;
    $otroDestino = (int) $c['idClienteDestino'] !== ID_WEPOINT;
    if ($otroDestino && !$mysqli->query("SELECT 1 FROM wepoint_colectas WHERE id_colecta = $id")->fetch_row()) return null;
    $cancelada = $otroDestino || (int) $c['Eliminado'] === 1 || (int) $c['padre_eliminado'] === 1;
    $envios = $cancelada ? [] : $mysqli->query("SELECT " . wepoint_campos_tc() . " FROM TransClientes TC
        LEFT JOIN Clientes CO ON CO.id = TC.idClienteOrigen
        WHERE TC.idColecta = $id AND TC.id <> " . (int) $c['padre'] . " AND TC.CodigoSeguimiento <> (SELECT CodigoSeguimiento FROM Colecta WHERE id = $id) AND TC.Eliminado = 0 ORDER BY TC.id")->fetch_all(MYSQLI_ASSOC);
    // No se filtra por Retirado/Entregado: cambiar de estado no quita piezas del ingreso esperado.
    $bultos = array_map(fn ($e) => wepoint_bulto_payload($e, wepoint_modalidad_sugerida($e)), $envios);
    return ['caddy_colecta_id' => $id, 'fecha' => $c['Fecha'], 'destino' => 'wepoint',
        'estado' => $cancelada ? 'cancelada' : 'confirmada', 'bultos' => $bultos];
}

function wepoint_colecta_encolar(mysqli $mysqli, int $id): void
{
    if (!wepoint_colectas_habilitadas() || $id <= 0) return;
    wepoint_colectas_tablas($mysqli);
    $lock = 'wp_colecta_' . $id . '_' . substr(hash('sha256', $mysqli->query('SELECT DATABASE()')->fetch_row()[0]), 0, 16);
    $st = $mysqli->prepare('SELECT GET_LOCK(?, 5)');
    $st->bind_param('s', $lock); $st->execute();
    if ((int) $st->get_result()->fetch_row()[0] !== 1) throw new RuntimeException('Colecta ocupada: reintentar en cron');
    try {
        $snapshot = wepoint_colecta_snapshot($mysqli, $id);
        if ($snapshot === null) return;
        $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $json);
        $previo = $mysqli->query("SELECT version, payload_hash FROM wepoint_colectas WHERE id_colecta = $id")->fetch_assoc();
        if (($previo['payload_hash'] ?? null) === $hash) return;
        $version = (int) ($previo['version'] ?? 0) + 1;
        $json = json_encode($snapshot + ['version' => $version], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $st = $mysqli->prepare("INSERT INTO wepoint_colectas (id_colecta, version, payload_hash, payload) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE version = VALUES(version), payload_hash = VALUES(payload_hash), payload = VALUES(payload),
            estado = 'pendiente', ultimo_error = NULL, proximo_intento = NULL, actualizado_en = NOW()");
        $st->bind_param('iiss', $id, $version, $hash, $json); $st->execute();
    } finally {
        $st = $mysqli->prepare('DO RELEASE_LOCK(?)'); $st->bind_param('s', $lock); $st->execute();
    }
}

/** Un fallo de integración no invalida una colecta que Caddy ya generó correctamente. */
function wepoint_colecta_notificar(mysqli $mysqli, int $id, bool $enviar = false): void
{
    try {
        wepoint_colecta_encolar($mysqli, $id);
        if ($enviar) wepoint_colectas_procesar($mysqli, null, [$id]);
    }
    catch (Throwable $e) { error_log('Preaviso de colecta Wepoint: ' . $e->getMessage()); }
}

/** Cron y cola de escaneos: primero publica el ingreso esperado, después se reciben piezas. */
function wepoint_colectas_procesar(mysqli $mysqli, ?callable $request = null, ?array $soloIds = null): array
{
    $r = ['enviadas' => 0, 'errores' => 0];
    if (!wepoint_colectas_habilitadas()) return $r;
    wepoint_colectas_tablas($mysqli);
    $lock = 'wp_colectas_' . substr(hash('sha256', $mysqli->query('SELECT DATABASE()')->fetch_row()[0]), 0, 16);
    $st = $mysqli->prepare('SELECT GET_LOCK(?, 0)'); $st->bind_param('s', $lock); $st->execute();
    if ((int) $st->get_result()->fetch_row()[0] !== 1) return $r;
    try {
        // Descubre nuevas confirmaciones de otros módulos y revisa las colectas ya anunciadas.
        $filtro = $soloIds === null ? '' : ' AND C.id IN (' . implode(',', array_map('intval', $soloIds ?: [0])) . ')';
        $ids = $mysqli->query("SELECT C.id FROM Colecta C
            LEFT JOIN wepoint_colectas W ON W.id_colecta = C.id
            WHERE (W.id_colecta IS NOT NULL OR C.id > (SELECT id_desde FROM wepoint_colectas_inicio WHERE id = 1)) $filtro
            ORDER BY C.id")->fetch_all(MYSQLI_ASSOC);
        foreach ($ids as $f) wepoint_colecta_notificar($mysqli, (int) $f['id']);
        if (!wepoint_activo($mysqli)) return $r;
        $filtro = $soloIds === null ? '' : ' AND id_colecta IN (' . implode(',', array_map('intval', $soloIds ?: [0])) . ')';
        $filas = $mysqli->query("SELECT * FROM wepoint_colectas WHERE estado IN ('pendiente', 'error')
            AND (proximo_intento IS NULL OR proximo_intento <= NOW()) $filtro ORDER BY id_colecta LIMIT 20")->fetch_all(MYSQLI_ASSOC);
        $request ??= 'wepoint_request';
        foreach ($filas as $fila) {
            $id = (int) $fila['id_colecta']; $version = (int) $fila['version'];
            try {
                [$http, $error, $body] = $request('POST', '/api/v2/integraciones/caddy/colectas', json_decode($fila['payload'], true, 512, JSON_THROW_ON_ERROR), 15);
                $data = json_decode((string) $body, true);
                $ok = $http >= 200 && $http < 300 && isset($data['data']['id_orden_ingreso_bulto']) && (int) ($data['data']['version'] ?? 0) >= $version;
                $mensaje = $ok ? null : mb_substr('HTTP ' . $http . ': ' . ($data['message'] ?? $error ?: 'Respuesta inválida'), 0, 500);
                $numero = $ok ? ($data['data']['nro_orden_ingreso_bulto'] ?? null) : null;
            } catch (Throwable $e) {
                $ok = false; $mensaje = mb_substr($e->getMessage(), 0, 500); $numero = null;
            }
            $estado = $ok ? 'ok' : 'error';
            $st = $mysqli->prepare("UPDATE wepoint_colectas SET estado = ?, ultimo_error = ?, nro_ingreso = COALESCE(?, nro_ingreso),
                proximo_intento = IF(? = 'ok', NULL, DATE_ADD(NOW(), INTERVAL 2 MINUTE)),
                enviado_en = IF(? = 'ok', NOW(), enviado_en) WHERE id_colecta = ? AND version = ?");
            $st->bind_param('sssssii', $estado, $mensaje, $numero, $estado, $estado, $id, $version); $st->execute();
            $r[$ok ? 'enviadas' : 'errores']++;
        }
    } finally {
        $st = $mysqli->prepare('DO RELEASE_LOCK(?)'); $st->bind_param('s', $lock); $st->execute();
    }
    return $r;
}
