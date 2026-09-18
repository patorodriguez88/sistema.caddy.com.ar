<?php
// Script temporal ÚNICO (2026-09-18) - se borra apenas corre bien.
// Consulta puntual de una tarea de Asana (revisión pedida por Patricio).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = $_GET['gid'] ?? '1218020594870895';

function asanaCall(string $url, string $token) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['accept: application/json', 'authorization: Bearer ' . $token],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function tokenExpirado($data): bool {
    if (!isset($data['errors'])) return false;
    foreach ($data['errors'] as $e) {
        if (strpos($e['message'] ?? '', 'expired') !== false) return true;
    }
    return false;
}

$row = $mysqli->query("SELECT token, refresh_token FROM Api WHERE id=2")->fetch_assoc();
$token = $row['token'];

$taskUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid
    . '?opt_fields=name,notes,completed,due_on,assignee.name,created_at,modified_at,permalink_url,projects.name,memberships.section.name,tags.name';
$storiesUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid . '/stories?opt_fields=text,created_at,created_by.name,type,resource_subtype';
$attUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid . '/attachments?opt_fields=name,download_url,view_url';

$task = asanaCall($taskUrl, $token);

if (tokenExpirado($task)) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://app.asana.com/-/oauth_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id=1204867479928301&redirect_uri=https%3A%2F%2Fwww.sistema.caddy.com.ar%2FApi%2FAsana%2Frecepcion.php&client_secret=84f466e023db6f9958ddebad539b4df6&refresh_token=' . $row['refresh_token'],
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $refreshResp = curl_exec($curl);
    curl_close($curl);
    $refreshData = json_decode($refreshResp, true);

    if (isset($refreshData['access_token'])) {
        $token = $refreshData['access_token'];
        $mysqli->query("UPDATE Api SET token='" . $mysqli->real_escape_string($token) . "' WHERE id=2");
        $task = asanaCall($taskUrl, $token);
    } else {
        echo json_encode(['error' => 'No se pudo refrescar el token', 'detalle' => $refreshData]);
        exit;
    }
}

$stories = asanaCall($storiesUrl, $token);
$atts = asanaCall($attUrl, $token);

echo json_encode(['task' => $task, 'stories' => $stories, 'attachments' => $atts], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
