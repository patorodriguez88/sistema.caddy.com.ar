-- Variable de configuracion: minutos estimados por parada, usados en
-- "Ordenar segun Reparto" (orden_automatico.php) y como valor por defecto
-- en el campo "tiempo por parada" del Planificador.
-- Fecha: 2026-08-14
-- Aplicar manualmente en sandbox/producción (ya aplicado en local).
-- No es destructiva: un solo INSERT, no toca datos existentes.
-- Se puede ajustar despues editando el Valor directo en la tabla Variables,
-- igual que CostoPeajes o PrecioNaftaSuper.

INSERT INTO Variables (Nombre, Valor, Observaciones, Usuario)
VALUES ('TiempoPorParada', '5', 'Minutos estimados por parada, usado en Ordenar segun Reparto y como default en el Planificador', 'sistema');
