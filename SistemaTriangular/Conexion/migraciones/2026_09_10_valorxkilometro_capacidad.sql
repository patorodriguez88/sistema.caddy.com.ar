-- Capacidad por vehiculo de flota (ValorxKilometro), para el Cotizador de Envios.
--
-- Problema: el cotizador (modo "por km" y "automatico") elegia una Moto para
-- 100 kg porque ValorxKilometro no tenia limite de carga. Se agregan MaxKg y
-- MaxM3; el cotizador descarta / rechaza el vehiculo que no soporta el peso o
-- el volumen total de los paquetes.
--
-- Correr a mano en produccion. Idempotente.

ALTER TABLE `ValorxKilometro`
  ADD COLUMN IF NOT EXISTS `MaxKg` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `ValorKm`,
  ADD COLUMN IF NOT EXISTS `MaxM3` DECIMAL(10,3) NOT NULL DEFAULT 0 AFTER `MaxKg`;

-- Defaults orientativos por segmento (ajustar desde Admin > Valor por Kilometro).
-- Solo pisa filas que todavia estan en 0 (no toca lo que el operador ya cargo).
UPDATE `ValorxKilometro` SET `MaxKg` = 25,   `MaxM3` = 0.12  WHERE `Segmento` = 1 AND `MaxKg` = 0;  -- Moto
UPDATE `ValorxKilometro` SET `MaxKg` = 650,  `MaxM3` = 3.00  WHERE `Segmento` = 2 AND `MaxKg` = 0;  -- Fiorino
UPDATE `ValorxKilometro` SET `MaxKg` = 1000, `MaxM3` = 5.50  WHERE `Segmento` = 3 AND `MaxKg` = 0;  -- Expert
UPDATE `ValorxKilometro` SET `MaxKg` = 1500, `MaxM3` = 12.00 WHERE `Segmento` = 4 AND `MaxKg` = 0;  -- Sprinter
UPDATE `ValorxKilometro` SET `MaxKg` = 5000, `MaxM3` = 30.00 WHERE `Segmento` = 5 AND `MaxKg` = 0;  -- Ford Cargo
