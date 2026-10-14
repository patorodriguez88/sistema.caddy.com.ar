<?php
date_default_timezone_set('America/Argentina/Cordoba');
ini_set('display_errors', '0');
require_once __DIR__.'/../../Conexion/Conexioni.php';
require_once __DIR__.'/../../Menu/php/permisos_menu.php';
header('Content-Type: application/json; charset=utf-8');
if (!nivelPuedeEntrarAlSistema($_SESSION['Nivel'] ?? 0) || !tieneMenuPermiso('Home', 'Panel Admin')) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'No tenés permiso para consultar el Panel Admin.']); exit;
}
$month = $_GET['month'] ?? date('Y-m');
if (!is_string($month) || !preg_match('/^(19|20|21)\d{2}-(0[1-9]|1[0-2])$/D',$month)) {
    http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Seleccioná un mes válido.']); exit;
}
$filters=[];
foreach (['client','driver','fleet','service','route','trip','zone','province','integration'] as $key) {
    $value=$_GET[$key]??'';
    if (!is_string($value) || strlen($value)>1000) {http_response_code(422);echo json_encode(['ok'=>false,'error'=>'Filtro inválido.']);exit;}
    $filters[$key]=$value;
}
// Release the session lock before the read-only report, so the rest of the system remains usable.
session_write_close();
require_once __DIR__.'/dashboard_logistica_model.php';
try {
    $model=new DashboardLogistica($mysqli);
    echo json_encode(DashboardLogistica::report($model->load($month),$month,$filters),JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('Dashboard logística: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'No se pudo calcular el dashboard. Revisá la conexión y volvé a intentar.']);
}
