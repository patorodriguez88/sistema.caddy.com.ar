<?php

declare(strict_types=1);

include_once __DIR__ . "/../Conexion/Conexioni.php";
require_once __DIR__ . "/AsanaClient.php";

header('Content-Type: application/json; charset=utf-8');

try {
    global $mysqli;

    $gid = $_GET['gid'] ?? '';
    if ($gid === '') {
        echo json_encode(['success' => false, 'message' => 'Falta gid'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $asana = new AsanaClient($mysqli);
    $r = $asana->request('GET', "https://app.asana.com/api/1.0/tasks/{$gid}?opt_fields=gid,name,notes,due_on,completed,assignee.gid,assignee.name,permalink_url");

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
