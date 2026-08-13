-- Migración: usuario de acceso = mail para SuperAdministrador/Administracion/Operaciones
-- (nuevo Nivel 5 = Operaciones), y Grupo Sanguineo / Vencimiento de Licencia dejan de
-- ser obligatorios para empleados que no son Chofer/Reparto.
-- Fecha: 2026-08-13
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructivo: solo relaja dos columnas de NOT NULL a NULL, no toca datos.

-- Antes, estas columnas eran NOT NULL en Empleados. Con Nivel Operaciones (5) el alta
-- de un empleado que no es chofer no completa estos campos, y con
-- sql_mode=STRICT_TRANS_TABLES el INSERT falla si se manda NULL a una columna NOT NULL.
ALTER TABLE Empleados MODIFY VencimientoLicencia DATE NULL;
ALTER TABLE Empleados MODIFY GrupoSanguineo CHAR(10) NULL;
