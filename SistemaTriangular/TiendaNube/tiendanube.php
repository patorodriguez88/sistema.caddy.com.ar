<!-- //Este archivo sirve para recibir toda la informacion desde tienda nube y devolver las tarifas, esta vigente desde hoy 23/10/2025 -->
<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Cordoba');

// --- LOG simple a archivo ---
$LOG = __DIR__ . '/tiendanube_rates.log';
function tnlog($label, $data)
{
    global $LOG;
    $line = '[' . date('Y-m-d H:i:s') . "] $label: " . (is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE)) . PHP_EOL;
    file_put_contents($LOG, $line, FILE_APPEND);
}

// --- LECTURA DE INPUT ---
$raw = file_get_contents('php://input');
tnlog('INPUT_RAW', $raw);
$in = json_decode($raw, true);
if (!is_array($in)) {
    tnlog('ERROR', 'JSON inválido');
    echo json_encode(['rates' => []]);
    exit;
}

// Soportar payload en raíz o dentro de "rate"
$root = isset($in['rate']) && is_array($in['rate']) ? $in['rate'] : $in;

$dest = $root['destination'] ?? [];
$items = $root['items'] ?? [];
$postal_code = (string)($dest['postal_code'] ?? '');

// Acumular totales
$totalWeight = 0;  // en gramos
$totalPrice  = 0;
$totalWidth  = 0;
$totalHeight = 0;
$totalDepth = 0;

foreach ($items as $item) {
    $q = (int)($item['quantity'] ?? 1);
    $dims = $item['dimensions'] ?? [];
    $w = (float)($dims['width']  ?? 10);
    $h = (float)($dims['height'] ?? 10);
    $d = (float)($dims['depth']  ?? 10);
    $g = (int)  ($item['grams']  ?? 0);
    $p = (float)($item['price']  ?? 0);

    $totalWidth  += $w * $q;
    $totalHeight += $h * $q;
    $totalDepth  += $d * $q;
    $totalWeight += $g * $q;
    $totalPrice  += $p * $q;
}

$weightKg = max(0.1, $totalWeight / 1000); // evita 0
$width  = max(1, $totalWidth);
$height = max(1, $totalHeight);
$depth  = max(1, $totalDepth);

// --- Zona Flex Córdoba (CP 5000–5023) ---
$esFlex = (ctype_digit($postal_code) && (int)$postal_code >= 5000 && (int)$postal_code <= 5023);

// --- Armar request a tu API de Caddy ---
$flexFlag = $esFlex ? 1 : 0;
$payloadCaddy = [
    "Token" => "24c2862db2fb1f807e3f18c9374e813e",
    "flex"  => (string)$flexFlag,
    "Destination" => [[
        "Localidad" => "Destino",
        "CodigoPostal" => $postal_code
    ]],
    "Service" => [[
        "Cantidad" => 1,
        "Servicio" => 1,
        "ValorDeclarado" => (string)$totalPrice
    ]],
    "Box" => [[
        "Length" => (string)$depth,
        "Width"  => (string)$width,
        "Height" => (string)$height,
        "Weight" => (string)$weightKg
    ]]
];
tnlog('CADDY_REQ', $payloadCaddy);

// --- cURL a Caddy ---
$ch = curl_init('https://www.caddy.com.ar/api/rates');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payloadCaddy, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 8,
]);
$respCaddy = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
tnlog('CADDY_HTTP', $httpCode);
tnlog('CADDY_RESP', $respCaddy ?: $curlErr);

$data = json_decode($respCaddy, true);
if (!is_array($data) || !isset($data['result'])) {
    // Falla backend → no rates
    tnlog('WARN', 'Respuesta Caddy inválida');
    echo json_encode(['rates' => []]);
    exit;
}

$r = $data['result'];
$titulo = (string)($r['Titulo'] ?? 'Caddy Envío');
$total  = (float) ($r['Total']  ?? 0);

// Fechas de entrega (ISO 8601 con offset)
$nowIso = date('Y-m-d\TH:i:sO');
$maxIso = $nowIso; // si tenés una fecha específica, ponela aquí

// --- **CLAVE**: CODE DEBE COINCIDIR CON OPTIONS ACTIVAS ---

// Si es zona Flex → code "196" (simple)
// Si no, usá un code "especial" que tengas ACTIVO (ej. 205, 207, etc.)
$code = 'Simple'; // <-- AJUSTÁ '205' a un ID que tengas activo

$rate = [
    "name" => "Caddy. " . $titulo,
    "code" => $code,
    "price" => $total,
    "price_merchant" => $total,
    "currency" => "ARS",
    "type" => "ship",
    "min_delivery_date" => $nowIso,
    "max_delivery_date" => $maxIso,
    "phone_required" => true,
    "reference" => $titulo
];

$out = ['rates' => [$rate]];
tnlog('OUTPUT', $out);
echo json_encode($out, JSON_UNESCAPED_UNICODE);
