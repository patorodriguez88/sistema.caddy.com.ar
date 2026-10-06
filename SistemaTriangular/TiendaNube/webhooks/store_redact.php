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

// La tienda desinstaló la app: se borran del comerciante los datos de la integración
// (token, id de tienda y carrier). El cliente y su historial de envíos quedan.
require __DIR__ . '/tn_privacidad.php';
$storeId = (int)($payload['store_id'] ?? 0);
$afectadas = 0;
if ($storeId > 0) {
    $st = $mysqli->prepare("UPDATE Clientes SET token_tiendanube = NULL, user_id_tn = NULL, carrier_id_tn = NULL WHERE user_id_tn = ?");
    $s = (string)$storeId;
    $st->bind_param('s', $s);
    $st->execute();
    $afectadas = $st->affected_rows;
    $st->close();
}
tn_registrar($mysqli, 'store/redact', $storeId, null, [], ['Clientes' => $afectadas]);

http_response_code(200);
echo 'ok';
