<?php
header('Content-Type: application/json; charset=utf-8');

// ========== HELPER HTTP ==========
function http_get_json(string $url): ?array
{
    $opts = [
        "http" => [
            "method"  => "GET",
            "header"  => "User-Agent: SistemaTriangular/1.0 (caddy.com.ar)\r\n"
        ]
    ];
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $ctx);

    $out = [
        'url'   => $url,
        'raw'   => $raw,
        'headers' => isset($http_response_header) ? $http_response_header : null,
    ];

    if ($raw === false) {
        $out['ok'] = false;
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = json_decode($raw, true);
    $out['ok']   = is_array($data);
    $out['json'] = $data;

    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : 'Andres Lamas 2479, Cordoba, Argentina';

$params = [
    'q'              => $dir,
    'format'         => 'json',
    'limit'          => 1,
    'addressdetails' => 1,
    'countrycodes'   => 'ar',
];

$url = "https://nominatim.openstreetmap.org/search?" . http_build_query($params);
http_get_json($url);
