-- Horario de entrega preferido del Cliente: se carga una sola vez en la
-- ficha del cliente y despues se sugiere solo al crear una venta nueva
-- (Ventas.php), en vez de tener que cargarlo a mano en cada venta.
-- Complementa a TransClientes.HorarioEntregaSolicitado (ver
-- 2026_08_14_horario_entrega_solicitado.sql), que es el valor final que
-- realmente se usa para priorizar en el Planificador para esa venta puntual.
-- Fecha: 2026-08-14
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructiva: agrega una columna nullable, no toca datos existentes.

ALTER TABLE Clientes
  ADD COLUMN HorarioEntregaSolicitado TIME NULL DEFAULT NULL
  COMMENT 'Horario de entrega preferido del cliente, usado como sugerencia al cargar una venta nueva'
  AFTER Retiro;
