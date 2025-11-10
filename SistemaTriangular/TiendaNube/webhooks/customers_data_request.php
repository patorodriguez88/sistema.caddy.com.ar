<?php
require __DIR__ . '/tn_hmac.php';

$raw = tn_raw_body();
$sig = tn_get_signature();

if (!tn_verify_hmac($raw, $sig)) {
    tn_log_all_headers();
    tn_log('customers_data_request: HMAC inválido', ['sig' => $sig], true);
    http_response_code(401);
    exit('Invalid signature');
}

$payload = json_decode($raw, true);
tn_log('customers_data_request OK', $payload);

// Ejemplo: armar el informe de datos del cliente
$customerId = $payload['customer']['id'] ?? null;

// TODO: buscá info en tus tablas y devolvela
$respuesta = [
    'customer_id' => $customerId,
    'datos' => [
        // 'pedidos' => [...],
        // 'direcciones' => [...],
        // 'mensajes' => [...],
    ],
];

header('Content-Type: application/json');
echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
