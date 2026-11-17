-- Bonificación por cantidad por servicio (17/11, tarea 61).
-- La curva (1º bulto 100%, 2º bonificado, 3º en adelante 50%) la aplican la API y Venta Simple.
-- BonificaCantidad = 0 la apaga para ese servicio: cada unidad se cobra completa, en ventas y en la API.
-- Se cambia desde Servicios (columna "Bonifica por cantidad"). Por defecto todos bonifican.
-- Correr ANTES de subir el código (la API pide la columna en sus consultas).
ALTER TABLE Productos ADD COLUMN IF NOT EXISTS BonificaCantidad TINYINT(1) NOT NULL DEFAULT 1;

-- OPCIONAL (recomendado): servicios que en Venta Simple se cargan por hora o por unidad, no por bulto.
-- Sin esto, 3 horas de Sprinter o 10 FLETE INTERNO se cobrarían con la curva.
-- UPDATE Productos SET BonificaCantidad = 0 WHERE id IN (7, 201, 208); -- FLETE INTERNO, CAJA FRESH, Hora Sprinter

-- Control
SELECT BonificaCantidad, COUNT(*) FROM Productos GROUP BY BonificaCantidad;
