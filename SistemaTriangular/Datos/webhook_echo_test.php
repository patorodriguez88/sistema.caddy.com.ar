<?php
// Herramienta de prueba: recibe cualquier POST y lo loguea, para validar el webhook de cambio de recorrido
// sin depender de un servidor externo real. No requiere sesión: el webhook llega desde nuestro propio
// backend (SendWebhooks), sin cookie de admin.
//
// Uso:
//   1) Dar de alta una fila de prueba en Webhook con EndPoint apuntando a esta URL.
//   2) Disparar un cambio de recorrido cuyo Estado tenga Webhook=1.
//   3) Ver lo recibido en: webhook_echo_test.php?key=caddy-test-2026

$logFile = __DIR__ . '/webhook_echo_test.log';
$viewKey = 'caddy-test-2026'; // cambiar (o borrar el archivo) antes de dejarlo expuesto más tiempo del necesario

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $token = $headers['X-Caddy-Webhook-Token'] ?? $headers['x-caddy-webhook-token'] ?? '';
    $body = file_get_contents('php://input');

    $entry = [
        'fecha' => date('Y-m-d H:i:s'),
        'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
        'token' => $token,
        'body'  => $body,
    ];

    file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(['ok' => 1]);
    exit;
}

// GET: ver el log acumulado (protegido por key simple, esto es solo una herramienta de prueba)
if (($_GET['key'] ?? '') !== $viewKey) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if (($_GET['clear'] ?? '') === '1') {
    @unlink($logFile);
    header('Location: ?key=' . urlencode($viewKey));
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
if (!file_exists($logFile)) {
    echo "(sin eventos todavia)\n";
    exit;
}
echo file_get_contents($logFile);
