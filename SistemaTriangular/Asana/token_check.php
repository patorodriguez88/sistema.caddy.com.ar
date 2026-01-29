<?php

declare(strict_types=1);

include_once __DIR__ . "/../Conexion/Conexioni.php";
require_once __DIR__ . "/AsanaClient.php";

header('Content-Type: application/json; charset=utf-8');

try {
    global $mysqli;

    $asana = new AsanaClient($mysqli);
    $r = $asana->request('GET', 'https://app.asana.com/api/1.0/users/me?opt_fields=gid,name,email');

    echo json_encode([
        'success' => $r['ok'],
        'http_code' => $r['http_code'],
        'data' => $r['decoded']['data'] ?? null,
        'errors' => $r['decoded']['errors'] ?? null
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
