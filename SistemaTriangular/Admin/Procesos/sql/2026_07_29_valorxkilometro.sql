-- Migración: repartidores propios en Admin/Resultados.php
-- Fecha: 2026-07-29
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructivo: solo agrega una tabla nueva y una columna nueva (nullable).

-- 1) Tabla de segmentos de flota propia y su valor por km
CREATE TABLE IF NOT EXISTS ValorxKilometro (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Segmento INT NOT NULL,
  Nombre VARCHAR(50) NOT NULL,
  ValorKm DECIMAL(10,2) NOT NULL DEFAULT 0,
  Activo TINYINT(1) NOT NULL DEFAULT 1
);

-- 2) Precarga de los 5 segmentos definidos (valores en 0, se cargan luego desde
--    la pantalla Admin/ValorxKilometro.php)
INSERT INTO ValorxKilometro (Segmento, Nombre, ValorKm, Activo) VALUES
(1, 'Moto', 0, 1),
(2, 'Camioneta chica (Fiorino)', 0, 1),
(3, 'Camioneta mediana (Expert)', 0, 1),
(4, 'Camioneta grande (Sprinter)', 0, 1),
(5, 'Camion (Ford Cargo 916)', 0, 1);

-- 3) Columna nueva en Vehiculos para asignar el segmento de cada vehículo propio
--    (se carga desde Logistica/Vehiculos.php, campo "Segmento (costo por km)")
ALTER TABLE Vehiculos ADD COLUMN Segmento INT NULL DEFAULT NULL AFTER Aliados;

-- 4) Columnas de costo CONGELADO por orden en Logistica.
--    Se completan una sola vez al cerrar la orden (admin o smartphone) con el
--    Segmento/ValorKm vigentes en ese momento, y ya no se recalculan después aunque
--    se edite ValorxKilometro más adelante. Evita que un cambio de tarifa altere
--    reportes de órdenes ya cerradas.
ALTER TABLE Logistica
  ADD COLUMN CostoKmSegmentoImputado INT NULL DEFAULT NULL AFTER KilometrosRecorridos,
  ADD COLUMN CostoKmValorImputado DECIMAL(10,2) NULL DEFAULT NULL AFTER CostoKmSegmentoImputado,
  ADD COLUMN CostoKmTotalImputado DECIMAL(10,2) NULL DEFAULT NULL AFTER CostoKmValorImputado;
