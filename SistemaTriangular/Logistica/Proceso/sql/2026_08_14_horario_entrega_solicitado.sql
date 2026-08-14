-- Horario de entrega solicitado: campo informativo que carga el operador al
-- confirmar una venta (Ventas.php), usado como prioridad al ordenar el
-- recorrido en el Planificador. No obliga a cumplirlo, solo ayuda a que la
-- parada no quede muy desacomodada en la ruta.
-- Fecha: 2026-08-14
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructiva: agrega una columna nullable, no toca datos existentes.

ALTER TABLE TransClientes
  ADD COLUMN HorarioEntregaSolicitado TIME NULL DEFAULT NULL
  COMMENT 'Horario de entrega solicitado por el cliente/operador, informativo, se usa como prioridad en el Planificador'
  AFTER FechaEntrega;
