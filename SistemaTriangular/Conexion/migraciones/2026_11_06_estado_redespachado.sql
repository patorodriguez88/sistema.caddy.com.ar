-- ============================================================================
-- Estado "Redespachado" en Seguimiento.
-- Generado 2026-11-06.
--
-- Cuando Wepoint entrega en mano desde una orden de egreso a un comisionista o a
-- otro transporte (redespacho), el servicio queda "Redespachado" (lo ve el
-- destinatario en el seguimiento público). "Entregado al Cliente" lo carga el
-- operador cuando se confirma la entrega. (wepoint_egresos.php,
-- wepoint_egreso_registrar_entrega.) Sin webhook ni avisos.
--
-- El texto se copia a Seguimiento.Estado (varchar(30)). Idempotente. Se puede
-- correr antes o después del código (sin el estado, queda Movimiento Interno).
-- ============================================================================

INSERT INTO `Estados` (`Estado`, `Slug`, `Mostrar`, `Visitas`, `Webhook`, `Notificacion_origen`, `Notificacion_destino`)
SELECT 'Redespachado', 'redispatched', 1, 0, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `Estados` WHERE `Slug` = 'redispatched');

-- Verificación
SELECT id, Estado, Slug, Mostrar, Visitas, Webhook FROM Estados WHERE Slug = 'redispatched';
