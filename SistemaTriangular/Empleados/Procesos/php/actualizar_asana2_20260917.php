<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = $_GET['gid'] ?? '1218536231905384';

function asanaCall(string $url, string $token, string $method = 'GET', $body = null) {
    $curl = curl_init();
    $headers = ['accept: application/json', 'authorization: Bearer ' . $token];
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($body !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        $headers[] = 'content-type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $headers;
    }
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

$comentario = "Resuelto y desplegado a producción.\n\n"
    . "Causa: Admin/Procesos/php/contabilidad.php (guardarAsiento, carga manual de un asiento contable nuevo) no seteaba la columna Sucursal al insertar en Tesoreria - sin default, quedaba NULL. La Conciliación Bancaria (Admin/Procesos/php/bancos.php) filtra AND Sucursal = 'Córdoba', así que esos asientos quedaban invisibles ahí aunque sí se veían en Buscar Asiento / Libro Diario (que no filtran por Sucursal).\n\n"
    . "Se corrigió el INSERT para que siempre guarde Sucursal='Córdoba' (única sucursal real de la base). Se recalcularon además los 126 asientos históricos (2024-2026) que ya habían quedado con Sucursal=NULL por este mismo motivo - ya aparecen en la Conciliación Bancaria.";

$commentUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid . '/stories';
$commentBody = ['data' => ['text' => $comentario]];

$result = asanaCall($commentUrl, $token, 'POST', $commentBody);

if (tokenExpirado($result)) {
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
        $result = asanaCall($commentUrl, $token, 'POST', $commentBody);
    } else {
        echo json_encode(['error' => 'No se pudo refrescar el token', 'detalle' => $refreshData]);
        exit;
    }
}

$completeUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid;
$completeBody = ['data' => ['completed' => true]];
$completeResult = asanaCall($completeUrl, $token, 'PUT', $completeBody);

echo json_encode(['comentario' => $result, 'completada' => $completeResult], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
