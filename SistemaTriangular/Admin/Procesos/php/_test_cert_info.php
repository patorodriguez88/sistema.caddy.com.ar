<?php
// Test temporal (se borra tras usarse): identifica el certificado real que
// usa nuestro codigo (afip.php/src/Afip_res/cert, el default de new Afip()
// sin 'cert'=>... explicito) - para saber que alias ("Computador Fiscal")
// de ARCA le corresponde al elegir a quien autorizar el nuevo servicio.
define('ALLOW_NO_SESSION', true);
header('Content-Type: application/json; charset=utf-8');

$resFolder = __DIR__ . '/../../../afip.php/src/Afip_res/';
$resultado = [];

foreach (['cert', 'Caddy2022_45373ffd6b26ca23.crt', 'CaddyHomologacion2026.crt'] as $archivo) {
    $path = $resFolder . $archivo;
    if (!file_exists($path)) {
        $resultado[$archivo] = ['existe' => false];
        continue;
    }
    $contenido = file_get_contents($path);
    $cert = openssl_x509_read($contenido);
    if (!$cert) {
        $resultado[$archivo] = ['existe' => true, 'error' => 'no se pudo parsear como X.509'];
        continue;
    }
    $info = openssl_x509_parse($cert);
    $resultado[$archivo] = [
        'existe' => true,
        'subject' => $info['subject'] ?? null,
        'validFrom' => isset($info['validFrom_time_t']) ? date('Y-m-d', $info['validFrom_time_t']) : null,
        'validTo' => isset($info['validTo_time_t']) ? date('Y-m-d', $info['validTo_time_t']) : null,
        'vencido' => isset($info['validTo_time_t']) ? ($info['validTo_time_t'] < time()) : null,
    ];
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
