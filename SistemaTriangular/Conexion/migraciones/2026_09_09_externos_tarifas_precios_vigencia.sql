-- Tarifas de repartidores externos CON VIGENCIA POR FECHA.
--
-- Problema: Externos_tarifas tiene UN solo Precio por tarifa. Cuando el operador
-- cambia un precio "a partir de tal dia", los servicios anteriores a esa fecha
-- tienen que seguir liquidandose con el precio viejo. Antes no habia forma.
--
-- Solucion: Externos_tarifas queda como CATALOGO (id, Nombre, Observaciones) -
-- los ids son fijos porque los usa la logica hardcodeada del informe (>50km->4,
-- colecta->6, cobranza->9, Recorridos.Tarifa_externos, etc.). El precio pasa a
-- una tabla-timeline: Externos_tarifas_precios (idExternos_tarifas, Precio,
-- VigenciaDesde). El precio de un servicio se resuelve con:
--   SELECT Precio FROM Externos_tarifas_precios
--    WHERE idExternos_tarifas = ? AND VigenciaDesde <= <Seg.Fecha>
--    ORDER BY VigenciaDesde DESC LIMIT 1
-- (con fallback al mas viejo si el servicio es anterior a todo).
--
-- Externos_tarifas.Precio se mantiene como ESPEJO del precio vigente hoy (lo
-- actualiza el form de Datos > Tarifas Externos al cargar un precio con
-- VigenciaDesde <= hoy), asi nada que hoy lea esa columna se rompe.
--
-- Backfill: por cada tarifa del catalogo se inserta su Precio actual con
-- VigenciaDesde = '2020-01-01' -> todos los informes historicos dan lo mismo.
--
-- Correr a mano contra produccion (no forma parte del deploy automatico).
-- Idempotente: CREATE TABLE IF NOT EXISTS + el backfill sale de la sub-consulta
-- NOT EXISTS, se puede re-correr sin duplicar.

-- Sin FOREIGN KEY: Externos_tarifas es MyISAM (no soporta FK). La integridad
-- la maneja la app (form de Datos > Tarifas Externos).
CREATE TABLE IF NOT EXISTS Externos_tarifas_precios (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  idExternos_tarifas INT NOT NULL,
  Precio             DECIMAL(10,2) NOT NULL,
  VigenciaDesde      DATE NOT NULL,
  Usuario            VARCHAR(100) NULL,
  Observaciones      TEXT NULL,
  Timestamp          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tarifa_vigencia (idExternos_tarifas, VigenciaDesde)
);

-- Backfill: precio actual de cada tarifa como vigente "desde siempre".
INSERT INTO Externos_tarifas_precios (idExternos_tarifas, Precio, VigenciaDesde, Usuario, Observaciones)
SELECT et.id, et.Precio, '2020-01-01', 'migracion', 'Backfill precio inicial (migracion 2026_09_09)'
  FROM Externos_tarifas et
 WHERE NOT EXISTS (
   SELECT 1 FROM Externos_tarifas_precios p WHERE p.idExternos_tarifas = et.id
 );

-- Defensivo: en produccion Externos_rendicion.TipoLiquidacion ya existe, pero
-- algunas copias (sandbox / local) quedaron sin esa columna y el informe de
-- Externos revienta con "Unknown column 'TipoLiquidacion'". IF NOT EXISTS ->
-- no-op donde ya esta.
ALTER TABLE Externos_rendicion
  ADD COLUMN IF NOT EXISTS TipoLiquidacion CHAR(50) NOT NULL DEFAULT 'VISITA';

-- Idem: Externos_tarifas.Observaciones existe en prod; en copias viejas no, y
-- el form de Datos > Tarifas Externos lo necesita.
ALTER TABLE Externos_tarifas
  ADD COLUMN IF NOT EXISTS Observaciones TEXT NULL;

-- Verificacion:
-- SELECT et.id, et.Nombre, et.Precio AS precio_catalogo,
--        (SELECT p.Precio FROM Externos_tarifas_precios p
--          WHERE p.idExternos_tarifas = et.id AND p.VigenciaDesde <= CURDATE()
--          ORDER BY p.VigenciaDesde DESC LIMIT 1) AS precio_vigente_hoy
--   FROM Externos_tarifas et ORDER BY et.id;
