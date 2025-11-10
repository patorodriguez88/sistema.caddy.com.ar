<?php

$cfg   = require __DIR__ . '/../config_oscommerce.php';
$TOKEN = isset($cfg['bearer_token']) ? trim($cfg['bearer_token']) : '';
$LOG   = isset($cfg['log_file']) ? $cfg['log_file'] : __DIR__ . '/../oscommerce_bridge.log';

function log_bridge($msg)
{
    global $LOG;
    @file_put_contents($LOG, date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}

function get_auth_header()
{
    // 1) Vía $_SERVER
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) return $_SERVER['HTTP_AUTHORIZATION'];
    // 2) Vía REDIRECT_HTTP_AUTHORIZATION (común en Apache con FastCGI)
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    // 3) Vía apache_request_headers() (si está disponible)
    if (function_exists('apache_request_headers')) {
        $h = apache_request_headers();
        foreach ($h as $k => $v) {
            if (strcasecmp($k, 'Authorization') === 0) return $v;
        }
    }
    return '';
}

function get_token()
{
    // Prioridad: Authorization: Bearer xxx
    $auth = get_auth_header();
    if (stripos($auth, 'Bearer ') === 0) {
        return trim(substr($auth, 7));
    }
    // Fallback por querystring si el conector la usa
    if (isset($_GET['key']))   return trim($_GET['key']);
    if (isset($_GET['token'])) return trim($_GET['token']);
    // Fallback por header alternativo
    if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
    return '';
}

// ---------- Healthcheck ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'HEAD') {
    header('Content-Type: text/plain; charset=utf-8');
    // logueamos para saber si pega el admin
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    log_bridge("PING method={$_SERVER['REQUEST_METHOD']} ip={$ip} ua={$ua} uri={$_SERVER['REQUEST_URI']}");
    // en HEAD no se manda cuerpo (pero igual 200)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo 'OK - OSCommerce bridge alive';
    }
    exit;
}
// ---------- Preflight ----------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Auth-Token');
    header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
    http_response_code(204);
    exit;
}

// ---------- Solo POST para data ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: GET, POST, OPTIONS');
    exit('Method Not Allowed');
}

// ---------- Auth ----------
$recvToken = get_token();
if (!$TOKEN || $recvToken !== $TOKEN) {
    log_bridge('AUTH FAIL | recvToken="' . $recvToken . '" | expected="' . $TOKEN . '" | headers=' . json_encode($_SERVER));
    http_response_code(403);
    exit('Invalid token');
}

header('Content-Type: application/json; charset=utf-8');

// ---------- Body ----------
$raw = file_get_contents('php://input');
$ct  = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$payload = [];
if (stripos($ct, 'json') !== false) {
    $payload = json_decode($raw, true);
} else {
    $xml = @simplexml_load_string($raw);
    if ($xml) $payload = json_decode(json_encode($xml), true);
}
log_bridge('POST OK | bytes=' . strlen($raw) . ' | CT=' . $ct . ' | sample=' . substr($raw, 0, 300));

// ---------- Map ----------
require __DIR__ . '/../map_oscommerce.php';
$row = mapOrderToTransClientesArray(is_array($payload) ? $payload : []);

// ---------- (Opcional) Guardar en DB ----------
// TODO: aquí va tu INSERT mysqli a Importaciones
// --- Conexión a DB (usando tu Conexioni.php) ---
// include_once __DIR__ . '/../../Conexion/Conexioni.php';
// ✔️ usa 3 niveles hacia arriba
$_SESSION['usuario'] = 'oscommerce_bridge';
// 👇 antes del require de Conexioni.php
define('ALLOW_NO_SESSION', true);

$pathConex = __DIR__ . '/../../../Conexion/Conexioni.php';

if (!is_file($pathConex) || !is_readable($pathConex)) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'include_path',
        'msg' => 'No se encontró Conexioni.php en: ' . $pathConex
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $pathConex;

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    throw new Exception('Conexioni.php no definió $mysqli');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset('utf8mb4');
// activar reportes de error de MySQLi como excepciones
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// $mysqli->set_charset("utf8mb4");

// ahora ya tenés $mysqli disponible desde Conexioni.php
// ahora ya tenés $mysqli disponible desde Conexioni.php
// =================== Preparar datos desde $row ===================
$CodigoExterno = isset($row['CodigoExterno']) ? (string)$row['CodigoExterno'] : '';
$NombreDest    = isset($row['NombreDest'])    ? (string)$row['NombreDest']    : '';
$Direccion     = isset($row['Direccion'])     ? (string)$row['Direccion']     : '';
$Localidad     = isset($row['Localidad'])     ? (string)$row['Localidad']     : '';
$CP            = isset($row['CP'])            ? (string)$row['CP']            : '';
$Provincia     = isset($row['Provincia'])     ? (string)$row['Provincia']     : '';
$Telefono      = isset($row['Telefono'])      ? (string)$row['Telefono']      : '';
$Email         = isset($row['Email'])         ? (string)$row['Email']         : '';
$Importe       = isset($row['Importe'])       ? (float)$row['Importe']        : 0.0;
$CobrarEnvio   = isset($row['CobrarEnvio'])   ? (float)$row['CobrarEnvio']    : 0.0;
$Observaciones = isset($row['Observaciones']) ? (string)$row['Observaciones'] : '';
$Bultos        = isset($row['Bultos'])        ? (int)$row['Bultos']           : 0;
$Peso          = isset($row['Peso'])          ? (float)$row['Peso']           : 0.0;

// =================== Normalizaciones de longitud/tipo ===================
$CodigoSeguimiento = substr($CodigoExterno, 0, 10);    // VARCHAR(10)
$CP        = substr($CP, 0, 10);                       // CHAR(10)
$Email     = substr($Email, 0, 50);                    // VARCHAR(50)
$Telefono  = substr($Telefono, 0, 20);                 // VARCHAR(20)
$Celular   = $Telefono;                                // mismo por ahora
$PesoInt   = max(0, min(255, (int)round($Peso)));      // tinyint(4)
$NumeroVenta = (int)preg_replace('/\D+/', '', $CodigoExterno); // INT

// Totales
$Cantidad = ($Bultos > 0) ? (int)$Bultos : 1;
$Precio   = (float)$Importe;
$Total    = (float)$Importe;
$Importe  = (float)$Importe; // ValorDeclarado

// Fijos
$NCliente    = '19020';                                // CHAR(20)
$FormaDePago = ($CobrarEnvio > 0) ? 'Contraentrega' : 'Prepago';

// --- Descripción con ítems ---
$Descripcion = $Observaciones; // lo que ya venías poniendo

$itemsDesc = [];
if (isset($payload['items']) && is_array($payload['items'])) {
    foreach ($payload['items'] as $it) {
        $name = isset($it['name']) ? $it['name'] : '';
        $qty  = isset($it['qty'])  ? (int)$it['qty'] : 1;
        $sku  = isset($it['sku'])  ? $it['sku'] : '';
        $itemsDesc[] = trim($name . " x$qty" . ($sku ? " (SKU:$sku)" : ''));
    }
}
if ($itemsDesc) {
    $Descripcion = trim(($Descripcion ? $Descripcion . " | " : "") . "Items: " . implode(" | ", $itemsDesc));
}
// =================== SQL ===================
$sql = "INSERT INTO `Importaciones`
(`Fecha`,`RazonSocial`,`NCliente`,`TipoDeComprobante`,`NumeroComprobante`,
 `Cantidad`,`Precio`,`Total`,`ClienteDestino`,`idClienteDestino`,`DocumentoDestino`,
 `DomicilioDestino`,`LocalidadDestino`,`CodigoSeguimiento`,`NumeroVenta`,
 `DomicilioOrigen`,`LocalidadOrigen`,`Usuario`,`Cargado`,`FormaDePago`,`EntregaEn`,
 `Eliminado`,`Observaciones`,`Transportista`,`Recorrido`,`ProvinciaDestino`,
 `ProvinciaOrigen`,`Kilometros`,`TimeStamp`,`Hora`,`idProveedor`,`FechaEntrega`,
 `Cobranza`,`Retirado`,`ValorDeclarado`,`Telefono`,`Celular`,`Length`,`Width`,`Height`,
 `Weight`,`cpdestino`,`dni_destino`,`mail_destino`,`Flex`,`Meli`,`Status`,`order_id`,
 `logistic_type`,`shipments_id`,`date_created`,`estimated_delivery_time`,
 `tracking_method`,`agency_description`,`description`)
VALUES
(NOW(), ?, ?, 'API_OSCOMMERCE', '', ?, ?, ?, ?, 0, '', ?, ?, ?, ?,
 '', '', 'oscommerce_bridge', 1, ?, '', 0, ?, '', '',
 ?, '', 0, NOW(), TIME(NOW()), 0, NULL,
 0, 0, ?, ?, ?, 0, 0, 0, ?,
 ?, '', ?, 0, 0, 'pending', ?, '', '', NOW(), NULL,
 '', '', ?)";

$types = "ssiddssssisssdssissss";

try {
    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
        $types,
        $NombreDest,
        $NCliente,
        $Cantidad,
        $Precio,
        $Total,
        $NombreDest,
        $Direccion,
        $Localidad,
        $CodigoSeguimiento,
        $NumeroVenta,
        $FormaDePago,
        $Observaciones,
        $Provincia,
        $Importe,
        $Telefono,
        $Celular,
        $PesoInt,
        $CP,
        $Email,
        $CodigoExterno,
        $Descripcion
    );

    $stmt->execute();
    // $affected = $stmt->affected_rows; // si lo querés usar, hacelo acá (antes de cerrar)

} catch (mysqli_sql_exception $e) {
    throw new Exception('DB EXEC: ' . $e->getMessage());
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        // el arroba evita warnings si ya se cerró por alguna razón
        @$stmt->close();
    }
}

// try {
//     $stmt = $mysqli->prepare($sql);
// } catch (mysqli_sql_exception $e) {
//     throw new Exception('DB PREP: ' . $e->getMessage());
// }

// /* Orden y tipos (21):
//  1 s RazonSocial
//  2 s NCliente (CHAR)
//  3 i Cantidad
//  4 d Precio
//  5 d Total
//  6 s ClienteDestino
//  7 s DomicilioDestino
//  8 s LocalidadDestino
//  9 s CodigoSeguimiento (<=10)
// 10 i NumeroVenta (INT)
// 11 s FormaDePago
// 12 s Observaciones
// 13 s ProvinciaDestino
// 14 d ValorDeclarado
// 15 s Telefono
// 16 s Celular
// 17 i Weight (tinyint)
// 18 s cpdestino (<=10)
// 19 s mail_destino (<=50)
// 20 s order_id  (char(60))
// 21 s description
// */
// $types = "ssiddssssisssdssissss";

// try {
//     $stmt->bind_param(
//         $types,
//         $NombreDest,        // 1
//         $NCliente,          // 2
//         $Cantidad,          // 3
//         $Precio,            // 4
//         $Total,             // 5
//         $NombreDest,        // 6
//         $Direccion,         // 7
//         $Localidad,         // 8
//         $CodigoSeguimiento, // 9
//         $NumeroVenta,       // 10
//         $FormaDePago,       // 11
//         $Observaciones,     // 12
//         $Provincia,         // 13
//         $Importe,           // 14
//         $Telefono,          // 15
//         $Celular,           // 16
//         $PesoInt,           // 17
//         $CP,                // 18
//         $Email,             // 19
//         $CodigoExterno,     // 20
//         $Observaciones      // 21
//     );
// } catch (mysqli_sql_exception $e) {
//     throw new Exception('DB BIND: ' . $e->getMessage());
// }

// try {
//     $stmt->execute();
//     $stmt->close();
// } catch (mysqli_sql_exception $e) {
//     throw new Exception('DB EXEC: ' . $e->getMessage());
// }



// $stmt->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'received' => $row], JSON_UNESCAPED_UNICODE);
