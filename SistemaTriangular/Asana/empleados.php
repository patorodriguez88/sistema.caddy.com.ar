<?php

declare(strict_types=1);

include_once __DIR__ . "/../Conexion/Conexioni.php";
require_once __DIR__ . "/AsanaClient.php";

header('Content-Type: application/json; charset=utf-8');

try {
    global $mysqli;

    $asana = new AsanaClient($mysqli);
    $r = $asana->request('GET', 'https://app.asana.com/api/1.0/users?opt_fields=gid,name');

    if (!$r['ok']) {
        echo json_encode(['success' => false, 'http_code' => $r['http_code'], 'error' => $r['decoded'] ?? $r['raw']], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $users = $r['decoded']['data'] ?? [];
    if (!is_array($users)) $users = [];

    usort($users, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });

    echo json_encode([
        'success' => true,
        'num_elements' => count($users),
        'data' => $users
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
