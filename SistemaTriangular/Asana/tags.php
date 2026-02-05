<?php

declare(strict_types=1);

include_once __DIR__ . "/../Conexion/Conexioni.php";
require_once __DIR__ . "/AsanaClient.php";

header('Content-Type: application/json; charset=utf-8');

try {
    global $mysqli;

    $raw = file_get_contents('php://input');
    $in = json_decode($raw, true);

    $action = $in['action'] ?? '';
    $taskGid = $in['task_gid'] ?? '';
    $tagGid = $in['tag_gid'] ?? '';

    if (!in_array($action, ['add', 'remove'], true) || $taskGid === '' || $tagGid === '') {
        echo json_encode(['success' => false, 'message' => 'Body inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $endpoint = ($action === 'add') ? 'addTag' : 'removeTag';
    $url = "https://app.asana.com/api/1.0/tasks/{$taskGid}/{$endpoint}";

    $asana = new AsanaClient($mysqli);
    $r = $asana->request('POST', $url, ['tag' => $tagGid]);

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
