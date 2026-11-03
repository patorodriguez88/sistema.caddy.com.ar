<?php
// Cron (cada minuto): manda a Wepoint lo que quedó en la cola wepoint_cola (escaneos de CrossDocking
// y cargas de choferes), con reintentos, y cierra como entregados los envíos que el destinatario
// retiró en el depósito de Wepoint. Solo por CLI; el host elige la base (config / config_sandbox):
//   php cron_wepoint.php sistema.caddy.com.ar
//   php cron_wepoint.php sandbox.sistema.caddy.com.ar
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
date_default_timezone_set('America/Argentina/Cordoba');

$_SERVER['HTTP_HOST'] = $argv[1] ?? 'sistema.caddy.com.ar';
require __DIR__ . '/../../../Conexion/conexion_publica.php';
require __DIR__ . '/wepoint_lib.php';

$conciliadas = wepoint_conciliar_salidas($mysqli);
$resultado = wepoint_procesar_cola($mysqli);
$resultado['salidas_conciliadas'] = $conciliadas;
$resultado['seguimientos'] = wepoint_seguimiento_pendientes($mysqli);
$resultado['retiros_cerrados'] = wepoint_cerrar_retiros($mysqli);
if ($resultado['ingresos'] || $resultado['salidas'] || $resultado['errores'] || $resultado['retiros_cerrados'] || $conciliadas || $resultado['seguimientos']) {
    echo date('Y-m-d H:i:s') . ' ' . json_encode($resultado) . "\n";
}
