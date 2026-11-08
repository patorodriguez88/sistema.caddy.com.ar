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
require_once __DIR__ . '/wepoint_colectas.php';

wepoint_conexion_actualizar($mysqli); // primero: lo que sigue depende de si la conexión está activa
$conciliadas = wepoint_conciliar_salidas($mysqli);
$colectas = wepoint_colectas_procesar($mysqli);
$resultado = wepoint_procesar_cola($mysqli);
$resultado['colectas'] = $colectas;
$resultado['salidas_conciliadas'] = $conciliadas;
$resultado['seguimientos'] = wepoint_seguimiento_pendientes($mysqli);
$resultado['retiros_cerrados'] = wepoint_cerrar_retiros($mysqli);
$resultado['salieron_con_conexion_apagada'] = wepoint_conciliar_al_reactivar($mysqli); // al final: borra la fecha de apagado
require_once __DIR__ . '/wepoint_egresos.php';
$resultado['egresos'] = wepoint_egresos_sincronizar($mysqli);
if ($colectas['enviadas'] || $colectas['errores'] || $resultado['ingresos'] || $resultado['salidas'] || $resultado['errores'] || $resultado['retiros_cerrados'] || $conciliadas || $resultado['seguimientos']) {
    echo date('Y-m-d H:i:s') . ' ' . json_encode($resultado) . "\n";
}
