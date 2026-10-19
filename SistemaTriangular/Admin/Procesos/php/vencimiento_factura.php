<?php
require_once __DIR__ . '/../../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../../Funciones/php/vencimientos_facturacion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !puedeEditarVencimiento($mysqli)) {
        http_response_code(403);
        throw new DomainException('No tenés permiso para modificar vencimientos.');
    }
    if (empty($_SESSION['csrf_vencimiento']) || !hash_equals($_SESSION['csrf_vencimiento'], (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        throw new DomainException('La sesión cambió. Recargá la página.');
    }
    cambiarVencimientoFactura($mysqli, (int)($_POST['id'] ?? 0), (string)($_POST['anterior'] ?? ''), (string)($_POST['fecha'] ?? ''), (string)($_POST['motivo'] ?? ''), (string)$_SESSION['Usuario']);
    echo json_encode(['ok' => true]);
} catch (InvalidArgumentException | DomainException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Modificar vencimiento: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el vencimiento.']);
}
