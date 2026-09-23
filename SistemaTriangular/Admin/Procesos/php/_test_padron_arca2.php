<?php
// Test temporal (se borra tras usarse): re-test tras autorizar
// ws_sr_constancia_inscripcion al Computador Fiscal "Caddy2022".
define('ALLOW_NO_SESSION', true);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../afip.php/src/Afip.php';

$resultado = [];

try {
    $afip = new Afip(array('CUIT' => 30715344943, 'production' => TRUE));
    $datos = $afip->RegisterInscriptionProof->GetTaxpayerDetails(30715344943);
    $resultado['ok'] = true;
    $resultado['datos'] = $datos;
} catch (\Throwable $e) {
    $resultado['ok'] = false;
    $resultado['error'] = $e->getMessage();
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
