-- Vencimiento de los comprobantes de proveedores (24/11/2026).
-- Al cargar un comprobante se sugiere: fecha + "Días de pago" del proveedor (Proveedores.Pago_comprobantes);
-- si cae sábado o domingo, el lunes. El operador lo puede cambiar. Programación de pagos lo muestra y
-- ordena por él; los comprobantes viejos (sin vencimiento) usan uno estimado con la misma regla.
-- El código funciona sin esta columna (no graba el vencimiento). Correr en PRODUCCIÓN y en el sandbox.

ALTER TABLE TransProveedores ADD COLUMN FechaVencimiento DATE NULL DEFAULT NULL;
