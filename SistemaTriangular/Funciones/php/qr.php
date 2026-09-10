<?php

// QR PNG on the fly. Uso: qr.php?d=<texto>&s=<pixelPerPoint 1-10>
// Sirve para la vista previa del Rotulo (Zebra) en el panel/ficha de seguimiento.

require_once __DIR__ . '/../../phpqrcode/qrlib.php';

$d = isset($_GET['d']) ? (string) $_GET['d'] : '';
$s = isset($_GET['s']) ? (int) $_GET['s'] : 4;
if ($s < 1) {
    $s = 1;
}
if ($s > 10) {
    $s = 10;
}

if ($d === '' || strlen($d) > 256) {
    http_response_code(400);
    exit;
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

QRcode::png($d, false, QR_ECLEVEL_M, $s, 1);
