<?php
require __DIR__ . '/tn_hmac.php';

$raw = tn_raw_body();
$sig = tn_get_signature();

if (!tn_verify_hmac($raw, $sig)) {
    tn_log_all_headers();
    tn_log('customers_redact: HMAC inválido', ['sig' => $sig], true);
    http_response_code(401);
    exit('Invalid signature');
}

$payload = json_decode($raw, true);
tn_log('customers_redact OK', ['store_id' => $payload['store_id'] ?? null, 'customer_id' => $payload['customer']['id'] ?? null, 'pedidos' => $payload['orders_to_redact'] ?? []]);

// El comprador pidió borrar sus datos: se anonimizan en los envíos de esos pedidos
// (criterio en tn_privacidad.php). Queda constancia en TiendaNube_privacidad.
require __DIR__ . '/tn_privacidad.php';
$storeId = (int)($payload['store_id'] ?? 0);
$pedidos = is_array($payload['orders_to_redact'] ?? null) ? $payload['orders_to_redact'] : [];
$afectadas = tn_anonimizar_pedidos($mysqli, $storeId, $pedidos);
tn_registrar($mysqli, 'customers/redact', $storeId, isset($payload['customer']['id']) ? (int)$payload['customer']['id'] : null, $pedidos, $afectadas);

http_response_code(200);
echo 'ok';
