-- ============================================================================
-- Estados propios del depósito de Wepoint en Seguimiento.
-- Generado 2026-11-04.
--
-- Hasta ahora el ingreso y la salida del depósito de Wepoint se anotaban como
-- "Movimiento Interno" (internal_shift), mezclados con los cambios de
-- recorrido. Pasan a tener su estado (Logistica/Proceso/php/wepoint_lib.php,
-- wepoint_seguimiento()):
--   wepoint_in  "En centro de distribución": Mostrar=1, el destinatario lo ve
--               en el seguimiento público (no dice Wepoint; la observación sí).
--   wepoint_out "Salió del depósito": interno, después viene "En Transito".
-- Sin webhook, sin avisos y Visitas 0. Tienda Nube no los publica (no están en
-- su lista) y el control de escaneo de reparto no los mira.
--
-- El texto se copia a Seguimiento.Estado (varchar(30)) -> <= 30 chars.
-- Se puede correr antes o después del código (sin los estados, el código cae a
-- "Movimiento Interno"). Idempotente.
-- ============================================================================

INSERT INTO `Estados` (`Estado`, `Slug`, `Mostrar`, `Visitas`, `Webhook`, `Notificacion_origen`, `Notificacion_destino`)
SELECT 'En centro de distribución', 'wepoint_in', 1, 0, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `Estados` WHERE `Slug` = 'wepoint_in');

INSERT INTO `Estados` (`Estado`, `Slug`, `Mostrar`, `Visitas`, `Webhook`, `Notificacion_origen`, `Notificacion_destino`)
SELECT 'Salió del depósito', 'wepoint_out', 0, 0, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `Estados` WHERE `Slug` = 'wepoint_out');

-- Lo que ya se anotó como Movimiento Interno pasa al estado nuevo
UPDATE `Seguimiento` S
JOIN `Estados` E ON E.`Slug` = 'wepoint_in'
SET S.`Estado` = E.`Estado`, S.`Estado_id` = E.`id`, S.`state_id` = E.`id`, S.`status` = E.`Slug`
WHERE S.`Usuario` = 'Wepoint' AND S.`status` = 'internal_shift' AND S.`Eliminado` = 0
  AND S.`Observaciones` = 'Ingresó al depósito de Wepoint';

UPDATE `Seguimiento` S
JOIN `Estados` E ON E.`Slug` = 'wepoint_out'
SET S.`Estado` = E.`Estado`, S.`Estado_id` = E.`id`, S.`state_id` = E.`id`, S.`status` = E.`Slug`
WHERE S.`Usuario` = 'Wepoint' AND S.`status` = 'internal_shift' AND S.`Eliminado` = 0
  AND S.`Observaciones` LIKE 'Salió del depósito de Wepoint%';

-- Verificación
SELECT id, Estado, Slug, Mostrar, Visitas, Webhook, Notificacion_origen, Notificacion_destino
FROM Estados WHERE Slug IN ('wepoint_in', 'wepoint_out');
SELECT status, Estado, COUNT(*) AS lineas FROM Seguimiento
WHERE Usuario = 'Wepoint' AND Eliminado = 0 GROUP BY status, Estado;
