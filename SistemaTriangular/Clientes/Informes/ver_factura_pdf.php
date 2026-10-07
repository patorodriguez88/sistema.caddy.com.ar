<?php
include_once "../../Conexion/Conexioni.php";
require_once __DIR__ . "/factura_pdf.php";

$id = intval($_GET['id'] ?? 0);
// detalle=1: "Detalle de operaciones" (mismo comprobante con cada línea de la venta)
$operaciones = !empty($_GET['detalle']);
if (!$id) {
    http_response_code(400);
    exit('Falta id');
}

$dirTemp = __DIR__ . '/../archivos_tmp';
if (!is_dir($dirTemp)) {
    mkdir($dirTemp, 0755, true);
}

$rutaTemp = $dirTemp . '/preview_factura_' . $id . '_' . uniqid() . '.pdf';

try {
    generarFacturaPDF($id, $rutaTemp, $operaciones);
} catch (Exception $e) {
    http_response_code(500);
    exit('No se pudo generar el PDF: ' . $e->getMessage());
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . ($operaciones ? 'detalle_operaciones.pdf' : 'factura.pdf') . '"');
header('Content-Length: ' . filesize($rutaTemp));
readfile($rutaTemp);
unlink($rutaTemp);
exit;
