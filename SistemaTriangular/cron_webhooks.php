<?php
// Disparador HTTP del envío de Webhook_notifications, para usar desde un cron externo
// (cron-job.org) que pega a esta URL por HTTP. Mismo patrón que api.caddy.com.ar/cron_worker.php:
// no dependemos del cron de Apache (históricamente no nos funcionó), y esto reemplaza tener
// que dejar Datos/webhook_server.php abierto en un navegador para que SendWebhooks corra.

define('ALLOW_NO_SESSION', true);
require_once __DIR__ . '/Conexion/Conexioni.php';

const CRON_WEBHOOKS_SECRET = 'eb24ca3c86786d2f0e159abc7865ed39';

$secreto = $_POST['secret'] ?? $_GET['secret'] ?? '';
if ($secreto !== CRON_WEBHOOKS_SECRET) {
    http_response_code(403);
    echo json_encode(['ok' => 0, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$_SESSION['Usuario'] = $_SESSION['Usuario'] ?? 'cron';
$_POST['SendWebhooks'] = 1;

ob_start();
require __DIR__ . '/Datos/Procesos/php/webhook.php';
$detalle = ob_get_clean();

echo json_encode(['ok' => 1, 'detalle' => $detalle], JSON_UNESCAPED_UNICODE);
