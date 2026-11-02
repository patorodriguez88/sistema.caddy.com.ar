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
        ];
    }
    return $config;
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
        KEY ix_wepoint_cola_estado (estado, proximo_intento)
    )");
}

function wepoint_encolar(mysqli $mysqli, string $tipo, int $idTc, string $codigo, string $serie, string $usuario, ?string $recorrido = null, ?string $chofer = null): void
{
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
    wepoint_cola_tabla($mysqli);
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
    $ids = array_values(array_unique(array_map(fn($f) => (int)$f['id_tc'], $filas)));
    $envios = $mysqli->query("SELECT " . wepoint_campos_tc() . " FROM TransClientes TC
        LEFT JOIN Clientes CO ON CO.id = TC.idClienteOrigen WHERE TC.id IN (" . implode(',', $ids) . ")")->fetch_all(MYSQLI_ASSOC);

    $bultos = [];
    $sinOrigen = [];
    foreach ($envios as $e) {
        if ((int)$e['idClienteOrigen'] <= 0) {
            $sinOrigen[(int)$e['id']] = true;
            continue;
        }
        $bultos[] = wepoint_bulto_payload($e, wepoint_modalidad_sugerida($e));
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

    return ['ok' => count($ok), 'error' => count($filas) - count($ok) + count($sinOrigenRows)];
}

/** Lo que cargó el chofer de un recorrido: sale del depósito de Wepoint. */
function wepoint_mandar_salidas(mysqli $mysqli, string $recorrido, array $filas): array
{
    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/salidas', [
        'codigos' => array_values(array_unique(array_column($filas, 'codigo'))),
        'recorrido' => $recorrido,
        'chofer' => $filas[0]['chofer'] ?? null,
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
    foreach ($mal as $motivo => $idsMal) {
        wepoint_cola_marcar($mysqli, $idsMal, false, $motivo);
    }

    return ['ok' => count($ok), 'error' => count($filas) - count($ok)];
}
