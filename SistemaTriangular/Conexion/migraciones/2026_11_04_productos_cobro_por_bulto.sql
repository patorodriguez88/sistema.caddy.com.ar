-- Cantidad que se cobra vs bultos que se etiquetan (4/11).
-- Casi todos los servicios se cobran por bulto: la cantidad de la venta SON los bultos (con la curva
-- de descuento 1º 100%, 2º bonificado, 3º+ 50%) y así sigue. Los que se cobran por pallet o por viaje
-- (CobroPorBulto = 0) cobran su cantidad (1 pallet) pero el envío lleva aparte los bultos físicos
-- (75), que son los que se etiquetan y escanean: Venta Simple los pide en "Bultos a etiquetar".
-- Se cambia desde Servicios (columna "Se cobra por"). Por defecto todo queda "por bulto" = como hoy.
-- Correr ANTES de subir el código.
ALTER TABLE Productos ADD COLUMN IF NOT EXISTS CobroPorBulto TINYINT(1) NOT NULL DEFAULT 1;

-- Los servicios de pallet que hay hoy (Movimiento de Pallet COR, PALLET COR BUE, Pallet Cor - Rosario,
-- Pallet a Rio III, MEDIO PALLET COR BUE). Los fletes por viaje se marcan desde la pantalla.
UPDATE Productos SET CobroPorBulto = 0 WHERE id IN (206, 223, 225, 230, 241);

-- Control: tienen que salir esos 5.
SELECT id, Codigo, Titulo, CobroPorBulto FROM Productos WHERE CobroPorBulto = 0;
