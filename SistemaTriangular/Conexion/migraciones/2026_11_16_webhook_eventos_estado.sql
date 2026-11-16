-- ============================================================================
-- Webhook de estados, opcional por cliente.
-- Generado 2026-11-16.
--
-- Webhook.EventosEstado = 1: Datos/Procesos/php/cron_webhook_estados.php le avisa
-- al webhook del cliente cada cambio de estado de sus envíos (los que ve el
-- destinatario, Estados.Mostrar = 1). Por defecto 0: las integraciones que ya
-- existen no reciben nada nuevo. Se prende en Clientes → Webhook.
--
-- Idempotente. Correrla ANTES de agregar el cron (el cron la necesita).
-- ============================================================================

ALTER TABLE Webhook
  ADD COLUMN IF NOT EXISTS EventosEstado TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = se le avisan al webhook todos los cambios de estado (cron_webhook_estados.php)';

-- Verificación
SELECT idCliente, Activo, EventosEstado, Endpoint FROM Webhook;
