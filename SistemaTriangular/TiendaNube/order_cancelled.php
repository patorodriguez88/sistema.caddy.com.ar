<?php
// order_created.php (hacer lo mismo para los otros dos cambiando nombre)

$input = file_get_contents('php://input');
$headers = getallheaders();
$client_secret = 'zbqIs1XZ0ZZdV9uHDH6zgMPQYLVjWjC1K7kBMO9x4Cl6jarC';
// OPCIONAL: validar autenticidad con HMAC
$firma = $headers['X-Tiendanube-Hmac-Sha256'] ?? '';
$calculado = base64_encode(hash_hmac('sha256', $input, $client_secret, true));
if ($firma !== $calculado) {
    http_response_code(403);
    exit('Firma no válida');
}

// Guardamos el pedido como log o lo procesamos
file_put_contents('logs/order_cancelled.log', date('Y-m-d H:i:s') . " - " . $input . PHP_EOL, FILE_APPEND);

http_response_code(200);
echo "OK";
