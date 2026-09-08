-- ============================================================================
-- Trazabilidad de colecta: cierre sin frenar + confirmación de oficina.
-- Generado 2026-09-09.
--
-- Cuando el chofer cierra una colecta con bultos sin escanear (ni a mano ni
-- confirmados por ML), esos servicios quedan en Seguimiento.status =
-- 'pickup_not_scanned'. La oficina los ve en Hoja de Ruta y con "Confirmar
-- colecta" los pasa a 'pickup_scanned'.
--
-- Quién / cuándo / cómo se confirmó va en las columnas que Seguimiento YA
-- tiene: Usuario, TimeStamp/Fecha/Hora y Observaciones (texto libre, mismo
-- criterio que el resto del historial). NO se agregan columnas nuevas.
--
-- Idempotente.
-- ============================================================================

-- Estado nuevo: colecta cerrada sin escaneo, pendiente de confirmar en oficina.
-- El texto se copia tal cual a Seguimiento.Estado (varchar(30)) -> <= 30 chars.
INSERT INTO `Estados` (`Estado`, `Slug`, `Mostrar`, `Visitas`, `Webhook`, `Notificacion_origen`, `Notificacion_destino`)
SELECT 'Colecta sin escanear', 'pickup_not_scanned', 1, 0, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `Estados` WHERE `Slug` = 'pickup_not_scanned');

-- Si ya se habia insertado con el texto largo (no entra en Seguimiento.Estado)
UPDATE `Estados` SET `Estado` = 'Colecta sin escanear'
WHERE `Slug` = 'pickup_not_scanned' AND CHAR_LENGTH(`Estado`) > 30;

-- Verificación
SELECT id, Estado, Slug FROM Estados WHERE Slug IN ('pickup_scanned', 'pickup_not_scanned');
