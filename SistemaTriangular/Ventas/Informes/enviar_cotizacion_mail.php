<?php

declare(strict_types=1);

// Envia una cotizacion de envio por mail (PDF adjunto).
// POST action=enviar, id, mails (csv).

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $e): void {
    error_log('enviar_cotizacion_mail EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../Funciones/php/enviar_mail.php';

define('COTIZACION_ENVIO_LIB', 1);
require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/CotizacionEnvioPdf.php';

if (($_POST['action'] ?? '') !== 'enviar') {
    echo json_encode(['ok' => false, 'error' => 'Solicitud invalida']);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$mails = array_values(array_unique(array_filter(
    array_map('trim', explode(',', (string) ($_POST['mails'] ?? ''))),
    static fn($v) => $v !== ''
)));

if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Guardá la cotización antes de enviarla']);
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

$st = $mysqli->prepare("SELECT * FROM CotizacionesEnvio WHERE id = ? AND Eliminado = 0 LIMIT 1");
$st->bind_param('i', $id);
$st->execute();
$row = $st->get_result()->fetch_assoc();
$st->close();
if (!$row) {
    echo json_encode(['ok' => false, 'error' => 'Cotización no encontrada']);
    exit;
}

$dirTmp = __DIR__ . '/../../archivos_tmp';
if (!is_dir($dirTmp)) {
    @mkdir($dirTmp, 0755, true);
}
$nro = str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT);
$ruta = $dirTmp . '/Caddy_Cotizacion_' . $nro . '.pdf';

try {
    $pdf = construirCotizacionEnvioPDF($mysqli, $row);
    $pdf->Output('F', $ruta);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo generar el PDF: ' . $e->getMessage()]);
    exit;
}

$cliente = (string) ($row['RazonSocial'] ?: 'Consumidor Final');
$hora = (int) (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('H');
$saludo = $hora < 13 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');
$asunto = 'Cotización de envío N° ' . $nro . ' | Caddy Logística';

$totalTxt = '$ ' . number_format((float) $row['Total'], 2, ',', '.');
$html = "
<p>{$saludo},</p>
<p>Desde <strong>Caddy Logística</strong> le acercamos la <strong>cotización de envío N° {$nro}</strong>
correspondiente al recorrido " . htmlspecialchars((string) $row['OrigenLocalidad'], ENT_QUOTES) . " → " .
htmlspecialchars((string) $row['DestinoLocalidad'], ENT_QUOTES) . ".</p>
<p><strong>Total (IVA incluido): {$totalTxt}</strong> — válida por 7 días.</p>
<p>El detalle completo está en el PDF adjunto. Ante cualquier consulta quedamos a disposición.</p>
<p>Atentamente,<br><strong>Caddy Logística</strong></p>
";

$resp = enviarMail($mails, $cliente, $asunto, $html, $ruta);
if (file_exists($ruta)) {
    @unlink($ruta);
}

if (!empty($resp['success']) && (int) $resp['success'] === 1) {
    error_log('Cotizacion ' . $id . ' enviada a ' . implode(', ', $mails) . ' por ' . ($_SESSION['Usuario'] ?? 'sistema'));
    echo json_encode(['ok' => true, 'msg' => 'Cotización enviada a ' . implode(', ', $mails)]);
} else {
    echo json_encode(['ok' => false, 'error' => $resp['msg'] ?? 'No se pudo enviar el mail']);
}
