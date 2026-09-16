<?php
// Script temporal ÚNICO (2026-09-16) - se borra apenas corre bien.
// Trae los adjuntos (capturas) de la tarea de Asana para poder verlos.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$gid = $_GET['gid'] ?? '1218536231905394';

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

$row = $mysqli->query("SELECT token FROM Api WHERE id=2")->fetch_assoc();
$token = $row['token'];

$attUrl = 'https://app.asana.com/api/1.0/tasks/' . $gid . '/attachments?opt_fields=name,download_url,permanent_url,view_url,host,resource_subtype';
$atts = asanaCall($attUrl, $token);

echo json_encode($atts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
