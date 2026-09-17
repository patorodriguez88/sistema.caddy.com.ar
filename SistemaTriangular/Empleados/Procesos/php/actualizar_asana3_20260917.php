<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = $_GET['gid'] ?? '1218536231905383';

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
    . "La fecha en sí ya se respetaba (fix anterior del 14/09). El problema real: al abrir \"Nuevo Asiento\", el número sugerido se calculaba a partir de la última fila insertada por id (autoincremental) en vez del número de asiento más alto real - id y N° de asiento no son la misma secuencia (otros flujos del sistema, como pagos/recibos, insertan con su propio número). Eso podía sugerir un número YA USADO.\n\n"
    . "Confirmado en tus 3 asientos: el 37374412 tenía originalmente un \"Anticipo a Acreedores\" del 06/08 - al cargar el pago de tarjeta de crédito del 15/09, el sistema sugirió ese mismo número (ya usado), y como el sistema trata cualquier guardado con un N° de asiento existente como una edición, pisó el asiento viejo sin que nadie lo pidiera. Por eso parecía \"cambiar de fecha\": en realidad era otro asiento tapando al primero.\n\n"
    . "Se corrigió el cálculo del próximo número (ahora usa el máximo N° de asiento real de toda la tabla). Se eliminaron además los 3 asientos pedidos (37374403, 37374411, 37374412) para que los vuelvas a cargar bien.";

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
