-- ============================================================================
-- Preferencia por cliente: ¿Caddy les avisa a los destinatarios de sus envíos?
-- Generado 2026-11-16.
--
-- 1 (por defecto, como hasta ahora): Caddy manda sus mails al destinatario según
--   Estados.Notificacion_destino (app de reparto, Mail/notices.php) y el mail de
--   "Recibimos tu solicitud" al crear el envío por API si el caller pide EnviarMail.
-- 0: Caddy no le escribe al destinatario; el cliente le avisa por su cuenta (por
--   ejemplo con el webhook de estados). Caso MeFaltaUno.
--
-- Se edita en Clientes → Webhook. Idempotente. El código funciona antes de correrla
-- (sin la columna, se avisa como siempre).
-- ============================================================================

ALTER TABLE Clientes
  ADD COLUMN IF NOT EXISTS AvisarDestinatario TINYINT(1) NOT NULL DEFAULT 1
    COMMENT '1 = Caddy avisa a los destinatarios de los envíos de este cliente; 0 = los avisa el cliente';

-- Verificación
SELECT COUNT(*) AS clientes, SUM(AvisarDestinatario = 0) AS sin_aviso_caddy FROM Clientes;
