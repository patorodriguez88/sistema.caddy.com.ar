-- Tarifas de Flota Propia: suma "Valor por Hora" a ValorxKilometro (antes solo
-- tenia $/km) y la columna de horas totales en CotizacionesEnvio, para el
-- nuevo modo "Por hora" del Cotizador de Envios.
--
-- ValorxKilometro pasa a modelar 2 unidades de cobro por segmento de flota
-- (Moto, Utilitario, Camion...): $/km (ya existia) y $/hora (nuevo). El
-- catalogo de "Servicios" (Productos, Grupo='Web') sigue siendo la fuente de
-- precio para el modo "Por servicio" (tarifa cerrada por bulto) - no se toca.

ALTER TABLE ValorxKilometro
  ADD COLUMN ValorHora DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER ValorKm;

ALTER TABLE CotizacionesEnvio
  ADD COLUMN HorasTotales DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER KmTotales;
