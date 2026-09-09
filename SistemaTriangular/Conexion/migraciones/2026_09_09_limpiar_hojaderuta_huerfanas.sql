-- =====================================================================
-- 2026-09-09  Limpiar HojaDeRuta 'Abierto' huérfanas (TransClientes borrado)
--             y TransClientes con Eliminado NULL
-- =====================================================================
--
-- Síntoma: en Zonas, el chequeo "Hay servicios sin geolocalizar" listaba
-- códigos (ej. MXWCU0BHB, Oscar Oviedo, rec 1313) que NO aparecen en
-- Pendientes ni en Seguimiento. Causa: filas de HojaDeRuta con Estado='Abierto'
-- cuyo TransClientes está Eliminado=1 (o Eliminado IS NULL). Pendientes /
-- Seguimiento filtran TransClientes.Eliminado=0 y no las ven; Zonas joineaba
-- Clientes<->HojaDeRuta sin mirar TransClientes y sí las contaba.
--
-- El código de Zonas ya se blindó (join a TransClientes con Eliminado=0). Esta
-- migración además limpia las filas muertas para que no ensucien otras
-- pantallas (panel de HdR, gates de cierre, etc.).
--
-- Al 2026-09-09: ~8 TransClientes con Eliminado NULL, ~77 HojaDeRuta 'Abierto'
-- huérfanas. Correr una vez en producción.
-- =====================================================================

-- 1) TransClientes.Eliminado NULL -> 1. La columna tiene default 0; estas filas
--    (2023-2026, algunas duplicadas) quedaron en NULL por algún INSERT/UPDATE
--    viejo y son invisibles a todos los filtros Eliminado=0, así que ya estaban
--    de hecho fuera de juego.
UPDATE TransClientes SET Eliminado = 1 WHERE Eliminado IS NULL;

-- 2) HojaDeRuta 'Abierto' cuyo TransClientes quedó Eliminado=1 (el borrado del
--    servicio no propagó a la HdR). Se cierran / marcan eliminadas.
UPDATE HojaDeRuta h
JOIN TransClientes t ON t.id = h.idTransClientes
SET h.Eliminado = 1
WHERE h.Estado = 'Abierto'
  AND h.Eliminado = 0
  AND h.Devuelto = 0
  AND t.Eliminado = 1;

-- (Roadmap: mismo criterio, por las dudas queden filas espejo)
UPDATE Roadmap r
JOIN TransClientes t ON t.id = r.idTransClientes
SET r.Eliminado = 1
WHERE IFNULL(r.Eliminado,0) = 0
  AND IFNULL(r.Estado,'') <> 'Cerrado'
  AND t.Eliminado = 1;
