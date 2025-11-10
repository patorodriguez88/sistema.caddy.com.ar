<?php
$config = require __DIR__ . '/config_oscommerce.php';

// Autenticación Bearer
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (strpos($auth, 'Bearer ') !== 0) {
    http_response_code(401);
    exit('Missing Bearer');
}
$token = trim(substr($auth, 7));
if ($token !== $config['bearer_token']) {
    http_response_code(403);
    exit('Invalid token');
}

// Procesar body
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

require __DIR__ . '/map_oscommerce.php';
$row = mapOrderToTransClientesArray($payload);

// Acá hacés INSERT a TransClientes con mysqli

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'row' => $row], JSON_UNESCAPED_UNICODE);
