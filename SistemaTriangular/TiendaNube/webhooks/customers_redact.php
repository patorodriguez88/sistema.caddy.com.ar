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
tn_log('customers_redact OK', $payload);

// Ejemplo: anonimizar cliente en tus tablas
$customerId = $payload['customer']['id'] ?? null;
// TODO: UPDATE ... SET nombre=NULL, email=NULL, etc. WHERE id_cliente = $customerId

http_response_code(200);
echo 'ok';
