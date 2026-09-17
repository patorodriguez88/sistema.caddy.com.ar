<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Comenta y marca completa la tarea de Asana de "cobranza integrada en
// facturación", ya resuelta (código + recálculo de 302 servicios de
// IGALFER en producción).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = $_GET['gid'] ?? '1218583486936096';

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
    . "Causa: a los clientes con el convenio \"Cobranza Integrada no factura\" (Clientes.CobranzaIntegradaNoFactura=1 - en este caso IGALFER) la línea de fee se carga correctamente marcada como not_invoice=1 (Ventas), pero varios puntos del sistema que recalculan el importe a facturar (TransClientes.Debe) no respetaban ese flag y sumaban igual la Cobranza Integrada.\n\n"
    . "Se corrigió en: Servicios/Procesos/php/funciones.php, Logistica/Proceso/php/funciones_recorridos.php y Clientes/Procesos/php/abmventas.php (ya lo tenía bien).\n\n"
    . "Se recalculó además el importe de los 302 servicios de IGALFER que ya estaban pendientes de facturar con el importe mal calculado - ya quedaron con el monto correcto, sin la Cobranza Integrada sumada. No se tocó ningún otro cliente (para el resto, la Cobranza Integrada sí corresponde facturarla junto con el servicio).";

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

// Marcar completa.
$completeUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid;
$completeBody = ['data' => ['completed' => true]];
$completeResult = asanaCall($completeUrl, $token, 'PUT', $completeBody);

echo json_encode(['comentario' => $result, 'completada' => $completeResult], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
