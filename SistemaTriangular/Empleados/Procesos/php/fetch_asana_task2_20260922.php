<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$taskGid = '1218739314549395';
$storyGid = '1218748135637620';

$apiRow = $mysqli->query("SELECT * FROM Api WHERE id=2 LIMIT 1")->fetch_assoc();
$token = $apiRow['token'] ?? '';
$refreshToken = $apiRow['refresh_token'] ?? '';

function asanaGet($url, $token) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true)];
}

function refrescarToken($mysqli, $refreshToken) {
    $ch = curl_init('https://app.asana.com/-/oauth_token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => '1204867479928301',
            'client_secret' => '84f466e023db6f9958ddebad539b4df6',
            'redirect_uri' => 'https://www.sistema.caddy.com.ar/Api/Asana/recepcion.php',
            'refresh_token' => $refreshToken,
        ]),
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($body, true);
    if (!empty($data['access_token'])) {
        $newToken = $mysqli->real_escape_string($data['access_token']);
        $mysqli->query("UPDATE Api SET token='{$newToken}' WHERE id=2");
        return $data['access_token'];
    }
    return null;
}

$out = [];

[$code, $task] = asanaGet("https://app.asana.com/api/1.0/tasks/{$taskGid}?opt_fields=name,notes,completed,assignee.name,permalink_url", $token);
if ($code === 401) {
    $newToken = refrescarToken($mysqli, $refreshToken);
    if ($newToken) {
        $token = $newToken;
        [$code, $task] = asanaGet("https://app.asana.com/api/1.0/tasks/{$taskGid}?opt_fields=name,notes,completed,assignee.name,permalink_url", $token);
    }
}
$out['task_http'] = $code;
$out['task'] = $task;

[$code2, $stories] = asanaGet("https://app.asana.com/api/1.0/tasks/{$taskGid}/stories?opt_fields=text,created_by.name,created_at,type", $token);
$out['stories_http'] = $code2;
$out['stories'] = $stories;

[$code3, $story] = asanaGet("https://app.asana.com/api/1.0/stories/{$storyGid}?opt_fields=text,created_by.name,created_at,type", $token);
$out['story_puntual_http'] = $code3;
$out['story_puntual'] = $story;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
