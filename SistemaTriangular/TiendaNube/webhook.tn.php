<?php
$store_id = '1579';
$access_token = 'AQUI_TU_ACCESS_TOKEN';

// URL base de tu aplicación donde se recibirán los eventos
$base_url = 'https://sistema.caddy.com.ar/SistemaTriangular/TiendaNube/webhook.tn';

$webhooks = [
    [
        "event" => "order/created",
        "url" => "$base_url/order_created.php"
    ],
    [
        "event" => "order/paid",
        "url" => "$base_url/order_paid.php"
    ],
    [
        "event" => "order/cancelled",
        "url" => "$base_url/order_cancelled.php"
    ],
    [
        "event" => "order/updated",
        "url" => "$base_url/order_updated.php"
    ]
];

foreach ($webhooks as $webhook) {
    $ch = curl_init("https://api.tiendanube.com/v1/{$store_id}/webhooks");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authentication: Bearer $access_token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Registrando webhook [{$webhook['event']}]: HTTP $httpCode\n";
    echo "Respuesta: $response\n\n";
}
