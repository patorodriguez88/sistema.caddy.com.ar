<?php
// config_oscommerce.php

return [
    /* ========= PULL (SOAP) - opcional, por si luego te lo habilitan ========= */
    'soap_url'         => 'https://TIENDA-CLIENTE.com/soap?wsdl', // dejar placeholder
    'api_key'          => 'API_KEY_DE_OSCOMMERCE',
    'fallback_minutes' => 60,

    /* ========= PUSH (Connector / Bridge) - lo que estás usando ahora ========= */
    // Token que vas a poner también en el admin del Connector (Bearer Token)
    'bearer_token'     => 'CADDY2025TOKEN123',

    // Si además querés doble seguridad por IP, dejá la allowlist (o vacía para no filtrar)
    'ip_allowlist'     => [], // p.ej. ['1.2.3.4','5.6.7.8']

    // Si alguna vez querés volver a HMAC, lo dejamos por si usás otro endpoint:
    'push_shared_secret' => '', // '' desactiva la verificación HMAC

    /* ============================ BASE DE DATOS ============================= */
    'db' => [
        'host'    => 'localhost',
        'user'    => 'usuario',
        'pass'    => 'password',
        'name'    => 'basededatos',
        'charset' => 'utf8mb4'
    ],

    /* ============================ LOGS OPCIONALES =========================== */
    'log_file' => __DIR__ . '/oscommerce_bridge.log', // para auditar POST/errores
];
