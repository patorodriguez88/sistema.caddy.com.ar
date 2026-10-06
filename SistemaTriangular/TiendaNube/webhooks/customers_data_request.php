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
tn_log('customers_data_request OK', ['store_id' => $payload['store_id'] ?? null, 'customer_id' => $payload['customer']['id'] ?? null, 'pedidos' => $payload['orders_requested'] ?? []]);

// El comprador pidió sus datos. Según TN, la app se los manda directamente al comerciante:
// se arma el resumen de los envíos de esos pedidos y se envía por mail al comerciante, con copia
// a Caddy. Queda constancia en TiendaNube_privacidad.
require __DIR__ . '/tn_privacidad.php';
require_once __DIR__ . '/../../Funciones/php/enviar_mail.php';

$storeId = (int)($payload['store_id'] ?? 0);
$pedidos = is_array($payload['orders_requested'] ?? null) ? $payload['orders_requested'] : [];
$envios = tn_datos_pedidos($mysqli, $storeId, $pedidos);

$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$html = '<p>Hola,</p><p>Un comprador de tu tienda pidió, a través de Tienda Nube, los datos personales que Caddy '
      . 'tiene sobre él (pedido de datos N° ' . $h($payload['data_request']['id'] ?? '-') . '). '
      . 'Estos son los datos de los envíos de sus pedidos:</p>';
if (!$envios) {
    $html .= '<p><b>Caddy no tiene datos de envíos para los pedidos indicados</b> (' . $h(implode(', ', $pedidos)) . ').</p>';
}
foreach ($envios as $e) {
    $html .= '<h4>Pedido ' . $h($e['pedido']) . ' — Código de seguimiento ' . $h($e['CodigoSeguimiento']) . '</h4><ul>'
           . '<li>Destinatario: ' . $h($e['ClienteDestino']) . ($e['DocumentoDestino'] ? ' (DNI ' . $h($e['DocumentoDestino']) . ')' : '') . '</li>'
           . '<li>Dirección: ' . $h($e['DomicilioDestino']) . ', ' . $h($e['LocalidadDestino']) . ', ' . $h($e['ProvinciaDestino']) . ' (CP ' . $h($e['cpdestino']) . ')</li>'
           . '<li>Teléfono: ' . $h(trim($e['Telefono'] . ' ' . $e['Celular'])) . '</li>'
           . '<li>Fecha de carga: ' . $h($e['Fecha']) . '</li></ul>';
    if ($e['estados']) {
        $html .= '<table border="1" cellpadding="4" cellspacing="0"><tr><th>Fecha</th><th>Hora</th><th>Estado</th><th>Recibió</th></tr>';
        foreach ($e['estados'] as $s) {
            $html .= '<tr><td>' . $h($s['Fecha']) . '</td><td>' . $h($s['Hora']) . '</td><td>' . $h($s['Estado']) . '</td><td>'
                   . $h(trim($s['NombreCompleto'] . ($s['Dni'] ? ' (DNI ' . $s['Dni'] . ')' : ''))) . '</td></tr>';
        }
        $html .= '</table>';
    }
}
$html .= '<p>Caddy Logística</p>';

$comerciante = null;
$res = $mysqli->query("SELECT nombrecliente, Mail FROM Clientes WHERE user_id_tn = '" . $storeId . "' AND Mail <> '' LIMIT 1");
if ($res) {
    $comerciante = $res->fetch_assoc();
}
$asunto = 'Pedido de datos de un comprador (Tienda Nube) - Caddy';
$enviado = [];
if ($comerciante) {
    $enviado['comerciante'] = (bool)enviarMail($comerciante['Mail'], $comerciante['nombrecliente'], $asunto, $html);
}
$enviado['copia_caddy'] = (bool)enviarMail('prodriguez@caddy.com.ar', 'Caddy', $asunto . ($comerciante ? '' : ' (SIN MAIL DEL COMERCIANTE)'), $html);

tn_registrar($mysqli, 'customers/data_request', $storeId, isset($payload['customer']['id']) ? (int)$payload['customer']['id'] : null,
             $pedidos, ['envios' => count($envios), 'mail' => $enviado, 'data_request_id' => $payload['data_request']['id'] ?? null]);

http_response_code(200);
echo 'ok';
