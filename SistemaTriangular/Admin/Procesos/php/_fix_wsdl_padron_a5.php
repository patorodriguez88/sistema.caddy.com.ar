<?php
// Script temporal (se borra tras usarse): el WSDL local
// (afip.php/src/Afip_res/ws_sr_padron_a5-production.wsdl) es viejo - solo
// define getPersona (v1), pero el servicio de ARCA (ahora llamado
// ws_sr_constancia_inscripcion, renombrado desde el deprecado
// ws_sr_padron_a5) exige getPersona_v2 desde feb-2026. Se reemplaza por el
// WSDL vivo actual, pedido en caliente al propio endpoint de ARCA
// (?WSDL, estandar SOAP) - no esta en git (como cert/key), asi que se
// escribe directo en el server. Guarda backup del viejo por las dudas.
define('ALLOW_NO_SESSION', true);
header('Content-Type: application/json; charset=utf-8');

$resFolder = __DIR__ . '/../../../afip.php/src/Afip_res/';
$destino = $resFolder . 'ws_sr_padron_a5-production.wsdl';
$backup = $resFolder . 'ws_sr_padron_a5-production.wsdl.bak-20260923';

$resultado = [];

$ctx = stream_context_create(['http' => ['timeout' => 15]]);
$wsdlNuevo = @file_get_contents('https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5?WSDL', false, $ctx);

if ($wsdlNuevo === false || strlen($wsdlNuevo) < 1000) {
    $resultado['ok'] = false;
    $resultado['error'] = 'No se pudo descargar el WSDL vivo de ARCA.';
    echo json_encode($resultado);
    exit;
}

if (strpos($wsdlNuevo, 'getPersona_v2') === false) {
    $resultado['ok'] = false;
    $resultado['error'] = 'El WSDL descargado no contiene getPersona_v2 - no se reemplaza, algo cambio.';
    echo json_encode($resultado);
    exit;
}

// Backup del viejo (si no existe ya uno)
if (file_exists($destino) && !file_exists($backup)) {
    copy($destino, $backup);
    $resultado['backup_creado'] = true;
}

$bytes = file_put_contents($destino, $wsdlNuevo);
$resultado['ok'] = $bytes !== false;
$resultado['bytes_escritos'] = $bytes;
$resultado['tiene_getPersona_v2'] = strpos(file_get_contents($destino), 'getPersona_v2') !== false;

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
