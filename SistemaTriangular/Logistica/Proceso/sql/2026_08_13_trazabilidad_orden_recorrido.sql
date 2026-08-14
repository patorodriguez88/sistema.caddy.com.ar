-- Trazabilidad del orden ejecutado en un Recorrido (quien, cuando, con que
-- metodo: Manual / Automatico / Gestya).
-- Fecha: 2026-08-13
-- Aplicar manualmente en sandbox/producción (ya aplicado y verificado en local).
-- No es destructiva: solo agrega 3 columnas nuevas, todas NULL por default.

ALTER TABLE Recorridos
  ADD COLUMN UltimoOrdenUsuario VARCHAR(60) NULL,
  ADD COLUMN UltimoOrdenFecha DATETIME NULL,
  ADD COLUMN UltimoOrdenMetodo VARCHAR(30) NULL;
