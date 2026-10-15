<?php
// Legacy URL retained for bookmarks and callers. Values are net operating income, not cash flow.
date_default_timezone_set('America/Argentina/Cordoba');
ini_set('display_errors','0');
require_once __DIR__.'/../../Conexion/Conexioni.php';
require_once __DIR__.'/../../Menu/php/permisos_menu.php';
header('Content-Type: application/json; charset=utf-8');
if (!nivelPuedeEntrarAlSistema($_SESSION['Nivel']??0) || !tieneMenuPermiso('Home','Resultados')) {
    http_response_code(403);echo json_encode(['ok'=>false,'error'=>'No tenés permiso para consultar Resultados del negocio.']);exit;
}
session_write_close();
require_once __DIR__.'/dashboard_logistica_model.php';
try {
    $month=date('Y-m');$start=(new DateTimeImmutable($month.'-01'))->modify('-11 months')->format('Y-m-d');
    $end=(new DateTimeImmutable($month.'-01'))->modify('+1 month')->format('Y-m-d');
    $receipts=[];
    $stmt=$mysqli->prepare("SELECT DATE_FORMAT(Fecha,'%Y-%m') month,SUM(Haber) amount FROM Ctasctes
        WHERE Eliminado=0 AND TipoDeComprobante='Recibo de Pago' AND Fecha>=? AND Fecha<? GROUP BY month");
    $stmt->bind_param('ss',$start,$end);$stmt->execute();
    foreach($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r)$receipts[$r['month']]=(float)$r['amount'];$stmt->close();
    $model=new DashboardLogistica($mysqli);
    echo json_encode(DashboardLogistica::businessSummary($model->load($month,false),$month,$receipts),JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
} catch(Throwable $e) {
    error_log('Resultados del negocio: '.$e->getMessage());http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'No se pudo calcular el resultado. Volvé a intentar.']);
}
