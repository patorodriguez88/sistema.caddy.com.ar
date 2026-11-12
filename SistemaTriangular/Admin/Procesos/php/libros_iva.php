<?php
// Libros IVA (pantalla Admin/LibrosIva.php): devuelve comprobantes, columnas
// visibles, totales del período y resúmenes. Mismos datos que el PDF.
ini_set('display_errors', 0);

include_once __DIR__ . '/../../../Conexion/Conexioni.php';
require_once __DIR__ . '/libros_iva_lib.php';
header('Content-Type: application/json; charset=utf-8');

$libro = ($_POST['libro'] ?? '') === 'compras' ? 'compras' : 'ventas';
$desde = (string)($_POST['desde'] ?? '');
$hasta = (string)($_POST['hasta'] ?? '');

if (!liv_fecha_valida($desde) || !liv_fecha_valida($hasta) || $desde > $hasta) {
    echo json_encode(['success' => 0, 'msg' => 'Elegí un período válido (la fecha desde no puede ser mayor que la hasta).']);
    exit;
}

try {
    $datos = liv_datos($mysqli, $libro, $desde, $hasta);
    echo json_encode(['success' => 1] + $datos);
} catch (Throwable $e) {
    error_log('libros_iva.php: ' . $e->getMessage());
    echo json_encode(['success' => 0, 'msg' => 'No se pudo generar el libro: ' . $e->getMessage()]);
}
