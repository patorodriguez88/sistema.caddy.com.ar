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
