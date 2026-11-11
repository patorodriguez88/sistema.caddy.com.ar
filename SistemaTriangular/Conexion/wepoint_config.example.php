<?php
// Copiar este archivo como wepoint_config.php y completar. wepoint_config.php está en .gitignore.
// Lo usa Logistica/Proceso/php/wepoint.php para avisarle a Wepoint los bultos que se le mandan
// (aviso previo) y armar los egresos.
//
// Token: token personal (Sanctum) del usuario de integración de la empresa Caddy en Wepoint
// (empresa con integration_key = 'caddy'). Va sin el "Bearer ".
// Sandbox de Caddy → testing de Wepoint; producción → producción.

define('WEPOINT_API_URL', 'https://testing.wepoint.ar'); // producción: https://sistema.wepoint.ar
define('WEPOINT_API_TOKEN', 'TOKEN_DEL_USUARIO_DE_INTEGRACION');

// Llave general: en false, CrossDocking no le avisa nada a Wepoint, la cola no se manda y no se
// cierran retiros (lo que estaba en la cola espera). Sin definir = encendida.
define('WEPOINT_ACTIVO', false);

// Recepción desde Wepoint (Logistica/Proceso/php/wepoint_entrada.php): token que Wepoint manda para
// escanear como en CrossDocking. Va también en CADDY_SISTEMA_TOKEN de la API de Wepoint.
define('WEPOINT_ENTRADA_TOKEN', 'UN_TOKEN_LARGO_Y_SECRETO');

// Colecta confirmada → orden de ingreso previa. Sin definir: activo en sandbox/testing
// y en producción/sistema.wepoint.ar, verificando que el destino corresponda al entorno.
// define('WEPOINT_COLECTAS_ACTIVAS', false);
