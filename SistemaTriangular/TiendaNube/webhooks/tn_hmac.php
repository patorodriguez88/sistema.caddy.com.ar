<?php
require __DIR__ . '/tn_config.php';

/**
 * Lee el body RAW tal como llegó (necesario para HMAC).
 */
function tn_raw_body(): string
{
    return file_get_contents('php://input') ?: '';
}

/**
 * Obtiene la firma enviada por Tienda Nube en el header.
 */
function tn_get_signature(): string
{
    if (!empty($_SERVER['HTTP_X_LINKEDSTORE_HMAC_SHA256'])) {
        return $_SERVER['HTTP_X_LINKEDSTORE_HMAC_SHA256'];
    }
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'x-linkedstore-hmac-sha256') {
                return $v;
            }
        }
    }
    return '';
}
function tn_log_all_headers(): void
{
    if (!defined('TN_LOG') || !TN_LOG) return;
    $hdrs = function_exists('getallheaders') ? getallheaders() : [];
    tn_log('HEADERS', $hdrs);
    tn_log('SERVER', [
        'HTTP_X_LINKEDSTORE_HMAC_SHA256' => $_SERVER['HTTP_X_LINKEDSTORE_HMAC_SHA256'] ?? '(no)',
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? '(no)',
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? '(no)',
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '(no)',
    ]);
}
/**
 * Comparación en tiempo constante (seguridad contra timing attacks).
 */
function tn_hash_equals(string $a, string $b): bool
{
    if (function_exists('hash_equals')) return hash_equals($a, $b);
    if (strlen($a) !== strlen($b)) return false;
    $res = 0;
    for ($i = 0; $i < strlen($a); $i++) $res |= (ord($a[$i]) ^ ord($b[$i]));
    return $res === 0;
}

/**
 * Verifica HMAC (base64 del hash_hmac SHA256 con tu APP_SECRET).
 */
function tn_verify_hmac(string $rawBody, string $headerSignature): bool
{
    if ($headerSignature === '') return false;
    $calc = base64_encode(hash_hmac('sha256', $rawBody, TN_APP_SECRET, true));
    return tn_hash_equals($calc, trim($headerSignature));
}

/**
 * Log con rotación (máx. 1 MB).  
 * Si TN_LOG es false o no existe, no se escribe nada.
 */
function tn_log(string $msg, $data = null, bool $isError = false): void
{
    if (!defined('TN_LOG') || !TN_LOG) return;

    $logFile = TN_LOG;
    $maxSize = 1024 * 1024; // 1 MB

    // Rotar archivo si supera el límite
    if (file_exists($logFile) && filesize($logFile) > $maxSize) {
        $backup = $logFile . '.1';
        @rename($logFile, $backup);
        file_put_contents($logFile, "[Rotado " . date('Y-m-d H:i:s') . "]\n");
    }

    // Construir línea
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if ($data !== null) {
        $line .= ' | ' . (is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    // Escribir al final
    file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);

    // Enviar mail si es un error crítico
    if ($isError) {
        tn_notify_error($msg, $data);
    }
}

/**
 * Envía aviso por mail en caso de error (firma inválida o excepciones).
 */
function tn_notify_error(string $msg, $data = null): void
{
    $to = 'prodriguez@caddy.com.ar';
    $subject = '⚠️ Webhook Tienda Nube - Error o firma inválida';
    $body  = "Se detectó un evento inusual en un webhook Tienda Nube:\n\n";
    $body .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $body .= "Mensaje: " . $msg . "\n\n";
    if ($data !== null) {
        $body .= "Datos adicionales:\n" . (is_string($data) ? $data : json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "\n";
    }

    $headers = [
        'From: webhooks@sistema.caddy.com.ar',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    // Enviar correo
    @mail($to, $subject, $body, implode("\r\n", $headers));
}
