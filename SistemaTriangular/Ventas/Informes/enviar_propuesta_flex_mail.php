<?php

declare(strict_types=1);

// Envia la Propuesta Comercial - Servicio Flex por mail (PDF adjunto).
// POST action=enviar, cliente, titulo, bonif (0/1), detalle, mails (csv).

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $e): void {
    error_log('enviar_propuesta_flex_mail EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../Funciones/php/enviar_mail.php';

define('PROPUESTA_FLEX_LIB', 1);
require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/PropuestaFlexPdf.php';

if (($_POST['action'] ?? '') !== 'enviar') {
    echo json_encode(['ok' => false, 'error' => 'Solicitud invalida']);
    exit;
}

$cliente = trim((string) ($_POST['cliente'] ?? ''));
$mails = array_values(array_unique(array_filter(
    array_map('trim', explode(',', (string) ($_POST['mails'] ?? ''))),
    static fn($v) => $v !== ''
)));

if ($cliente === '') {
    echo json_encode(['ok' => false, 'error' => 'Ingresá el nombre del cliente']);
    exit;
}
if (empty($mails)) {
    echo json_encode(['ok' => false, 'error' => 'Agregá al menos un mail']);
    exit;
}
foreach ($mails as $m) {
    if (!filter_var($m, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => "Correo inválido: {$m}"]);
        exit;
    }
}

$in = [
    'cliente' => $cliente,
    'titulo'  => trim((string) ($_POST['titulo'] ?? '')),
    'usuario' => trim((string) ($_SESSION['Usuario'] ?? '')),
    'bonif'   => (string) ($_POST['bonif'] ?? '0') === '1',
    'detalle' => trim((string) ($_POST['detalle'] ?? '')),
];

$dirTmp = __DIR__ . '/../../archivos_tmp';
if (!is_dir($dirTmp)) {
    @mkdir($dirTmp, 0755, true);
}
$slug = preg_replace('/[^A-Za-z0-9]+/', '_', $cliente) ?: 'cliente';
$ruta = $dirTmp . '/Caddy_Propuesta_Flex_' . $slug . '.pdf';

try {
    $pdf = construirPropuestaFlexPDF($mysqli, $in);
    $pdf->Output('F', $ruta);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo generar el PDF: ' . $e->getMessage()]);
    exit;
}

$hora = (int) (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('H');
$saludo = $hora < 13 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');
$asunto = 'Propuesta comercial - Servicio Flex | Caddy Logística';

$html = "
<p>{$saludo},</p>
<p>Desde <strong>Caddy Logística</strong> le acercamos la <strong>propuesta comercial del Servicio Flex</strong>
de distribución, con las condiciones operativas y la tarifa vigente.</p>
<p>El detalle completo está en el PDF adjunto. Quedamos a disposición para coordinar una reunión
y ajustar lo que haga falta.</p>
<p>Atentamente,<br><strong>Caddy Logística</strong></p>
";

$resp = enviarMail($mails, $cliente, $asunto, $html, $ruta);
if (file_exists($ruta)) {
    @unlink($ruta);
}

if (!empty($resp['success']) && (int) $resp['success'] === 1) {
    error_log('Propuesta Flex (' . $cliente . ') enviada a ' . implode(', ', $mails) . ' por ' . ($_SESSION['Usuario'] ?? 'sistema'));
    echo json_encode(['ok' => true, 'msg' => 'Propuesta enviada a ' . implode(', ', $mails)]);
} else {
    echo json_encode(['ok' => false, 'error' => $resp['msg'] ?? 'No se pudo enviar el mail']);
}
