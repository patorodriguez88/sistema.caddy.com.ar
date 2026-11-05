<?php
// Recepción desde Wepoint: el operador de Wepoint escanea en su sistema y Wepoint llama acá, que
// hace lo mismo que un escaneo de CrossDocking (resuelve la etiqueta, marca el ingreso en Caddy, lo
// anota para Wepoint y arma el rótulo) y le devuelve además el aviso del envío para que Wepoint lo
// dé de alta y lo reciba en el momento. Sin sesión: se autentica con WEPOINT_ENTRADA_TOKEN de
// Conexion/wepoint_config.php. POST JSON {codigo, proveedorForzado?, operador?}, o {accion: 'estado' | 'pendientes' | 'controlar'}
// para el tablero del día, lo que falta de un recorrido y el control final, o {accion: 'egreso'}
// con cómo quedaron los egresos solicitados desde Hoja de Ruta (wepoint_egresos.php).
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');

$responder = function (int $http, array $data) {
    http_response_code($http);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $responder(405, ['ok' => false, 'error' => 'METODO']);
}

require __DIR__ . '/../../../Conexion/conexion_publica.php';
require_once __DIR__ . '/wepoint_lib.php';
wepoint_config(); // carga las constantes de wepoint_config.php

$token = (string)($_SERVER['HTTP_X_WEPOINT_TOKEN'] ?? '') ?: preg_replace('/^Bearer\s+/i', '', (string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
if (!defined('WEPOINT_ENTRADA_TOKEN') || WEPOINT_ENTRADA_TOKEN === '' || !hash_equals((string)WEPOINT_ENTRADA_TOKEN, $token)) {
    $responder(401, ['ok' => false, 'error' => 'NO_AUTORIZADO']);
}
if (!wepoint_configurado()) {
    $responder(503, ['ok' => false, 'error' => 'APAGADO', 'mensaje' => 'La integración con Wepoint está apagada en Caddy.']);
}

$in = json_decode((string)file_get_contents('php://input'), true) ?: $_POST;

// El interruptor de Wepoint (Integraciones): funcionan con la conexión desactivada
if (($in['accion'] ?? '') === 'conexion') {
    wepoint_conexion_guardar($mysqli, !empty($in['activa']));
    $responder(200, ['ok' => true]);
}
if (($in['accion'] ?? '') === 'cola_pendiente') {
    $responder(200, ['ok' => true] + wepoint_cola_acumulada($mysqli));
}
if (($in['accion'] ?? '') === 'descartar_cola') {
    $responder(200, ['ok' => true, 'descartados' => wepoint_cola_descartar($mysqli)]);
}
if (!wepoint_activo($mysqli)) {
    $responder(503, ['ok' => false, 'error' => 'APAGADO', 'mensaje' => 'La conexión con Wepoint está desactivada.']);
}
$operador = mb_substr(trim((string)($in['operador'] ?? '')), 0, 40);
$_SESSION = ['Usuario' => 'Wepoint' . ($operador !== '' ? ": $operador" : '')];

// Wepoint resolvió un egreso que Caddy solicitó desde Hoja de Ruta (aceptado, rechazado, cambios, salió todo)
if (($in['accion'] ?? '') === 'egreso') {
    require_once __DIR__ . '/wepoint_egresos.php';
    foreach ((array)($in['egresos'] ?? []) as $egreso) {
        if (is_array($egreso)) {
            wepoint_egreso_aplicar($mysqli, $egreso);
        }
    }
    $responder(200, ['ok' => true]);
}

define('CD_COMO_LIBRERIA', true);
require_once __DIR__ . '/crossdocking.php';
// Tablero del día (lo mismo que muestra la pantalla de CrossDocking)
if (($in['accion'] ?? '') === 'estado') {
    $responder(200, cd_estado($mysqli, (string)($in['fecha'] ?? '')));
}
// Lo que falta escanear de un recorrido, y el control final de un recorrido (no marca nada)
if (($in['accion'] ?? '') === 'pendientes') {
    $responder(200, cd_pendientes($mysqli, (int)($in['recorrido'] ?? 0)));
}
if (($in['accion'] ?? '') === 'controlar') {
    $responder(200, cd_controlar($mysqli, (string)($in['codigo'] ?? ''), (int)($in['recorrido'] ?? 0)));
}

$r = cd_escanear($mysqli, (string)($in['codigo'] ?? ''), (int)($in['proveedorForzado'] ?? 0));
if (empty($r['ok'])) {
    $responder(200, $r);
}

// El aviso del envío, igual que lo arma la cola (wepoint_mandar_ingresos)
$idTc = (int)$r['idTransClientes'];
$envio = $mysqli->query("SELECT " . wepoint_campos_tc() . " FROM TransClientes TC
    LEFT JOIN Clientes CO ON CO.id = TC.idClienteOrigen WHERE TC.id = $idTc")->fetch_assoc();
if (!$envio || (int)$envio['idClienteOrigen'] <= 0) {
    $responder(200, ['ok' => false, 'error' => 'SIN_ORIGEN', 'mensaje' => 'El envío no tiene cliente de origen en Caddy.'] + $r);
}
$colectas = wepoint_colectas($mysqli, [$idTc]);
$aviso = wepoint_bulto_payload($envio, wepoint_modalidad_sugerida($envio)) + (isset($colectas[$idTc]) ? ['colecta' => $colectas[$idTc]] : []);
$serie = $aviso['piezas'] > 1 ? $r['codigoEtiqueta'] : $aviso['codigo_seguimiento'];

$responder(200, $r + ['aviso' => $aviso, 'serie' => $serie, 'fecha' => date('Y-m-d')]);
