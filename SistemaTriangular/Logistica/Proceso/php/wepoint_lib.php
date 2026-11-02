<?php
// Lo que comparten Logística > Wepoint (wepoint.php) y CrossDocking (crossdocking.php) para avisarle
// a Wepoint los bultos que entran a su depósito: conexión con su API, armado del aviso previo y
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
// Modo de ingreso: "manual" (se revisa y se manda desde Logística > Wepoint) u "operativo" (cada
// escaneo en CrossDocking da de alta el envío en Wepoint y lo da por recibido).
// ---------------------------------------------------------------------------------------------
function wepoint_ajustes_tabla(mysqli $mysqli): void
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_ajustes (
        clave VARCHAR(50) NOT NULL PRIMARY KEY,
        valor VARCHAR(255) NOT NULL,
        actualizado_por VARCHAR(100) NULL,
        actualizado_en DATETIME NULL
    )");
}

function wepoint_modo(mysqli $mysqli): string
{
    wepoint_ajustes_tabla($mysqli);
    $r = $mysqli->query("SELECT valor FROM wepoint_ajustes WHERE clave = 'modo_ingreso'");
    $valor = $r ? ($r->fetch_assoc()['valor'] ?? '') : '';
    return $valor === 'operativo' ? 'operativo' : 'manual';
}

function wepoint_guardar_modo(mysqli $mysqli, string $modo, string $usuario): string
{
    $modo = $modo === 'operativo' ? 'operativo' : 'manual';
    wepoint_ajustes_tabla($mysqli);
    $st = $mysqli->prepare("INSERT INTO wepoint_ajustes (clave, valor, actualizado_por, actualizado_en)
        VALUES ('modo_ingreso', ?, ?, NOW())
        ON DUPLICATE KEY UPDATE valor = VALUES(valor), actualizado_por = VALUES(actualizado_por), actualizado_en = NOW()");
    $st->bind_param('ss', $modo, $usuario);
    $st->execute();
    $st->close();
    return $modo;
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

    $existe = $mysqli->prepare("SELECT id FROM wepoint_api WHERE tipo = 'IN' AND CodigoSeguimiento_enviado = ? AND id_wepoint = ? LIMIT 1");
    $ins = $mysqli->prepare("INSERT INTO wepoint_api
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
            $existe->bind_param('si', $serie, $idBulto);
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
