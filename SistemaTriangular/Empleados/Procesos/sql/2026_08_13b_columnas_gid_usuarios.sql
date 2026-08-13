-- Migración: agregar gid_asana / gid_hubspot a usuarios (faltaban en producción)
-- Fecha: 2026-08-13
-- Aplicar manualmente en sandbox/producción (ya existen en local).
-- No es destructivo: solo agrega columnas nuevas a `usuarios`, no toca datos.
--
-- Causa: el alta de empleado (Empleados/Procesos/php/empleados.php) inserta
-- gid_asana/gid_hubspot en `usuarios` desde hace tiempo, pero esa migración nunca
-- se corrió fuera de local. No se notaba porque casi no se creaban usuarios de
-- sistema (Nivel 1/2); con el nuevo Nivel 5 (Operaciones) volvió a ejercitarse
-- ese INSERT y quedó expuesto: "Unknown column 'gid_hubspot' in 'INSERT INTO'".

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS gid_asana CHAR(20) NOT NULL DEFAULT '' AFTER TokenExpira,
  ADD COLUMN IF NOT EXISTS gid_hubspot CHAR(20) DEFAULT NULL AFTER gid_asana;
