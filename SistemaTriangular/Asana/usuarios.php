<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . "/../Conexion/Conexioni.php";
require_once __DIR__ . "/AsanaClient.php";

try {
    global $mysqli;

    $asana = new AsanaClient($mysqli);

    // Opt fields para traer solo lo necesario
    $url = "https://app.asana.com/api/1.0/users?opt_fields=gid,name";

    $r = $asana->request('GET', $url);

    if (!$r['ok']) {
        echo json_encode([
            'success' => false,
            'http_code' => $r['http_code'] ?? 0,
            'errors' => $r['decoded']['errors'] ?? null,
            'message' => 'No se pudo obtener usuarios de Asana'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $users = $r['decoded']['data'] ?? [];
    if (!is_array($users)) $users = [];

    // Orden alfabético
    usort($users, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });

    echo json_encode([
        'success' => true,
        'num_elements' => count($users),
        'data' => $users
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
