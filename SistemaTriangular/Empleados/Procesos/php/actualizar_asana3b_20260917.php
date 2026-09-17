<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// 1) Borra el comentario duplicado (story 1218612127788277) de la tarea
//    1218536231905383 - quedó publicado dos veces por error.
// 2) Publica el informe de auditoría de otros posibles asientos
//    colisionados (6 recientes + 18 históricos) para que Agustina decida.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = '1218536231905383';
$storyDuplicadoGid = '1218612127788277';
$informe = file_get_contents(__DIR__ . '/informe_asientos_20260917.txt');

function asanaCall(string $url, string $token, string $method = 'GET', $body = null) {
    $curl = curl_init();
    $headers = ['accept: application/json', 'authorization: Bearer ' . $token];
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
    ];
    if ($body !== null) {
        $headers[] = 'content-type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($curl, $opts);
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

function refrescarSiHaceFalta(&$token, $data, $mysqli, $refreshToken) {
    if (!tokenExpirado($data)) return $data;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://app.asana.com/-/oauth_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id=1204867479928301&redirect_uri=https%3A%2F%2Fwww.sistema.caddy.com.ar%2FApi%2FAsana%2Frecepcion.php&client_secret=84f466e023db6f9958ddebad539b4df6&refresh_token=' . $refreshToken,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $refreshResp = curl_exec($curl);
    curl_close($curl);
    $refreshData = json_decode($refreshResp, true);
    if (isset($refreshData['access_token'])) {
        $token = $refreshData['access_token'];
        $mysqli->query("UPDATE Api SET token='" . $mysqli->real_escape_string($token) . "' WHERE id=2");
    }
    return null;
}

// 1) Borrar el comentario duplicado
$delUrl = "https://app.asana.com/api/1.0/stories/$storyDuplicadoGid";
$delResp = asanaCall($delUrl, $token, 'DELETE');
if (tokenExpirado($delResp)) {
    refrescarSiHaceFalta($token, $delResp, $mysqli, $row['refresh_token']);
    $delResp = asanaCall($delUrl, $token, 'DELETE');
}

// 2) Publicar el informe
$comentarioUrl = "https://app.asana.com/api/1.0/tasks/$gid/stories";
$comentarioResp = asanaCall($comentarioUrl, $token, 'POST', ['data' => ['text' => $informe]]);
if (tokenExpirado($comentarioResp)) {
    refrescarSiHaceFalta($token, $comentarioResp, $mysqli, $row['refresh_token']);
    $comentarioResp = asanaCall($comentarioUrl, $token, 'POST', ['data' => ['text' => $informe]]);
}

echo json_encode([
    'borrado_duplicado' => $delResp,
    'comentario_informe' => $comentarioResp,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
