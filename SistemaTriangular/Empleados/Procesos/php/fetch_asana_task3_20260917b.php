<?php
// Script temporal - lee stories (comentarios) de la tarea Asana 1218536231905383
// para encontrar el comentario duplicado y poder borrarlo.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$taskGid = '1218536231905383';

$row = $mysqli->query("SELECT * FROM Api WHERE id=2")->fetch_assoc();
$accessToken = $row['access_token'];
$refreshToken = $row['refresh_token'];

function asanaRequest($url, $token, $method = 'GET', $body = null) {
    $ch = curl_init($url);
    $headers = ["Authorization: Bearer $token"];
    if ($body !== null) {
        $headers[] = "Content-Type: application/json";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($resp, true)];
}

function refreshAsanaToken($mysqli, $refreshToken) {
    $ch = curl_init('https://app.asana.com/-/oauth_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => '1204867479928301',
        'client_secret' => '84f466e023db6f9958ddebad539b4df6',
        'redirect_uri' => 'https://www.sistema.caddy.com.ar/Api/Asana/recepcion.php',
        'refresh_token' => $refreshToken,
    ]));
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    if (isset($data['access_token'])) {
        $newToken = $mysqli->real_escape_string($data['access_token']);
        $mysqli->query("UPDATE Api SET access_token='$newToken' WHERE id=2");
        return $data['access_token'];
    }
    return null;
}

list($code, $stories) = asanaRequest("https://app.asana.com/api/1.0/tasks/$taskGid/stories?opt_fields=text,created_at,created_by.name,type", $accessToken);
if ($code == 401) {
    $accessToken = refreshAsanaToken($mysqli, $refreshToken);
    list($code, $stories) = asanaRequest("https://app.asana.com/api/1.0/tasks/$taskGid/stories?opt_fields=text,created_at,created_by.name,type", $accessToken);
}

echo json_encode(['code' => $code, 'stories' => $stories], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
