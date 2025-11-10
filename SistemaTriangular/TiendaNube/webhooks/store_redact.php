<?php
require __DIR__ . '/tn_hmac.php';

$raw = tn_raw_body();
$sig = tn_get_signature();

if (!tn_verify_hmac($raw, $sig)) {
    tn_log_all_headers();
    tn_log('store_redact: HMAC inválido', ['sig' => $sig], true);
    http_response_code(401);
    exit('Invalid signature');
}

$payload = json_decode($raw, true);
tn_log('store_redact OK', $payload);

// Ejemplo: borrar datos ligados a la tienda
$storeId = $payload['store_id'] ?? null;
// TODO: ejecutar tus deletes/anonimizaciones por $storeId

http_response_code(200);
echo 'ok';
