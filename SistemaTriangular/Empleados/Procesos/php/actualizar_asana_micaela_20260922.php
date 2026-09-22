<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$taskGid = '1218695097925713';

$apiRow = $mysqli->query("SELECT * FROM Api WHERE id=2 LIMIT 1")->fetch_assoc();
$token = $apiRow['token'] ?? '';
$refreshToken = $apiRow['refresh_token'] ?? '';

function asanaCall($method, $url, $token, $payload = null) {
    $ch = curl_init($url);
    $headers = ['Authorization: Bearer ' . $token];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($payload !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $headers;
    }
    curl_setopt_array($ch, $opts);
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

$comentario = "Listo Micaela! Ya moví los 12 códigos de la orden 17093 a la 17086:\n\n"
    . "4YG9OR3NQ, OHWNZK4U1, LOJXGEXUW, FHJ1FA3Y9, MOXID4397, OAIN5HZFO, AECH2O2HB, 8C4XXV8OG, SD9VOUM7R, 9AUGY7OOT, TDVWTY118, 7N6GWV16Q\n\n"
    . "Cualquier cosa avisame.";

$out = [];

[$code, $res] = asanaCall('POST', "https://app.asana.com/api/1.0/tasks/{$taskGid}/stories", $token, ['data' => ['text' => $comentario]]);
if ($code === 401) {
    $newToken = refrescarToken($mysqli, $refreshToken);
    if ($newToken) {
        $token = $newToken;
        [$code, $res] = asanaCall('POST', "https://app.asana.com/api/1.0/tasks/{$taskGid}/stories", $token, ['data' => ['text' => $comentario]]);
    }
}
$out['comment_http'] = $code;
$out['comment'] = $res;

[$code2, $res2] = asanaCall('PUT', "https://app.asana.com/api/1.0/tasks/{$taskGid}", $token, ['data' => ['completed' => true]]);
$out['complete_http'] = $code2;
$out['complete'] = $res2;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
