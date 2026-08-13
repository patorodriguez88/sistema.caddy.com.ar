-- Migración: tracking de notificación de acceso (mail de bienvenida / reenvío)
-- Fecha: 2026-08-13
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructivo: solo agrega columnas nuevas a `usuarios`, no toca datos.
--
-- Antes, el mail de bienvenida se mandaba "a ciegas": si enviarMail() fallaba
-- (SMTP caído, mail mal cargado, etc.) no quedaba ningún registro y la UI decía
-- éxito igual. Estas columnas permiten saber si la notificación se envió, y
-- Empleados/Usuarios.php ahora tiene un botón para reenviarla (genera una
-- contraseña temporal nueva, porque la anterior queda hasheada y es irrecuperable).

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS NotificacionAccesoEnviada TINYINT(1) NOT NULL DEFAULT 0 AFTER FechaPassword,
  ADD COLUMN IF NOT EXISTS NotificacionAccesoFecha DATETIME NULL DEFAULT NULL AFTER NotificacionAccesoEnviada;
