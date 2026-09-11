<?php

declare(strict_types=1);

// Metricas del informe mensual en JSON, para la pestana Estadisticas de Clientes.
// Misma fuente que el PDF (informe_mensual_datos.php).

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $e): void {
    error_log('informe_mensual_json EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
    exit;
});

require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/informe_mensual_datos.php';

$id   = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$anio = (int) ($_POST['anio'] ?? $_GET['anio'] ?? 0);
$mes  = (int) ($_POST['mes'] ?? $_GET['mes'] ?? 0);

if ($anio < 2015 || $mes < 1 || $mes > 12) {
    $prev = strtotime('first day of last month');
    $anio = (int) date('Y', $prev);
    $mes  = (int) date('n', $prev);
}

echo json_encode(
    calcularInformeMensual($mysqli, $id, $anio, $mes),
    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
);
