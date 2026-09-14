<?php
// DEBUG TEMPORAL - sacar apenas se identifique el 500 en producción.
error_reporting(E_ALL);
ini_set('display_errors', '1');
// FIX (a pedido): esto era una página HTML completa con su propia
// DataTable (tema viejo "saas"), nunca conectada a ningún JS que la
// llenara de datos (cobranza_integrada_invoice.js no se cargaba acá) -
// por eso se veía "vacía, no se sabe si terminó". Se reemplaza por el
// mismo esquema que ya usa el resto de los comprobantes del sistema
// (ver Clientes/Informes/ver_factura_pdf.php): un PDF generado en el
// momento y mostrado inline en esta misma URL.
include_once __DIR__ . "/../../Conexion/Conexioni.php";
require_once __DIR__ . "/CobranzaIntegradaPdf.php";

$numero = intval($_GET['id'] ?? 0);
if (!$numero) {
    http_response_code(400);
    exit('Falta el número de liquidación (id).');
}

// Se usa el archivos_tmp COMPARTIDO de la raíz de SistemaTriangular (mismo
// que ya usa factura_pdf.php para sus QR de AFIP) en vez de crear una
// carpeta nueva bajo Admin/ - ya existe y tiene los permisos correctos en
// el servidor, sin depender de que mkdir() funcione ahí.
$dirTemp = __DIR__ . '/../../archivos_tmp';
if (!is_dir($dirTemp)) {
    mkdir($dirTemp, 0755, true);
}

$rutaTemp = $dirTemp . '/cobranza_integrada_' . $numero . '_' . uniqid() . '.pdf';

try {
    $resultado = generarCobranzaIntegradaPDF($mysqli, $numero, $rutaTemp);
} catch (Throwable $e) {
    http_response_code(500);
    exit('No se pudo generar la liquidación: ' . $e->getMessage());
}

if (empty($resultado['success'])) {
    http_response_code(404);
    exit($resultado['error'] ?? 'No se encontró la liquidación.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="liquidacion_' . $numero . '.pdf"');
header('Content-Length: ' . filesize($rutaTemp));
readfile($rutaTemp);
unlink($rutaTemp);
exit;
