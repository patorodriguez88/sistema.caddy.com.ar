-- =====================================================================
-- 2026-09-30  Seguimiento: movimientos a nombre de 'APP' -> chofer de la orden
-- =====================================================================
-- Contexto (tarea de Micaela "Facturación", orden 17128: figuraban 18 servicios
-- y el chofer hizo 27):
--  * Cuando a un repartidor se le cortaba la sesión en la calle, la app de reparto
--    grababa la entrega / no entrega igual, pero con Usuario = 'APP'. Externos le
--    paga a cada chofer por Seguimiento.Usuario, así que esos movimientos no le
--    figuraban (en la 17128, 8 de las 27 entregas de Icazatti).
--  * Las "No entrega" sin sesión además quedaban sin NumerodeOrden.
--  * La app ya quedó arreglada (4_reparto funciones.php: completarOrdenYChofer).
--
-- Qué hace (una sola vez, en la base dinter6_triangular):
--  1) Movimientos 'APP' con orden: Usuario = usuario del chofer de esa orden.
--  2) 'APP' sin orden: si el paquete estaba en una Hoja de Ruta de una orden del
--     mismo día, se completa la orden y el chofer. Los que no tienen Hoja de Ruta
--     ese día quedan como están (no hay a quién atribuirlos con certeza).
-- Al 30/9: 25 movimientos (1) + 2 (2). Idempotente.
-- =====================================================================

-- 0) PREVIEW (opcional)
-- SELECT s.id, s.Fecha, s.CodigoSeguimiento, s.Estado, s.NumerodeOrden, u.Usuario AS chofer
-- FROM Seguimiento s
-- JOIN Logistica l ON l.NumerodeOrden = s.NumerodeOrden AND l.Eliminado = 0
-- JOIN usuarios u ON u.id = l.idUsuarioChofer
-- WHERE s.Usuario = 'APP' AND s.Eliminado = 0 AND s.NumerodeOrden > 0;

-- 1) Con orden
UPDATE Seguimiento s
JOIN Logistica l ON l.NumerodeOrden = s.NumerodeOrden AND l.Eliminado = 0
JOIN usuarios u ON u.id = l.idUsuarioChofer AND TRIM(IFNULL(u.Usuario, '')) <> ''
SET s.Usuario = u.Usuario
WHERE s.Usuario = 'APP' AND s.Eliminado = 0 AND s.NumerodeOrden > 0;

-- 2) Sin orden, con Hoja de Ruta de una orden del mismo día
UPDATE Seguimiento s
JOIN ( SELECT s2.id,
              ( SELECT h.NumerodeOrden
                  FROM HojaDeRuta h
                  JOIN Logistica l2 ON l2.NumerodeOrden = h.NumerodeOrden AND l2.Eliminado = 0
                 WHERE h.Seguimiento = s2.CodigoSeguimiento AND h.Eliminado = 0
                   AND h.NumerodeOrden > 0 AND l2.Fecha = s2.Fecha
                 ORDER BY h.id DESC LIMIT 1 ) AS orden
         FROM Seguimiento s2
        WHERE s2.Usuario = 'APP' AND s2.Eliminado = 0 AND s2.NumerodeOrden = 0 ) x ON x.id = s.id
JOIN Logistica l ON l.NumerodeOrden = x.orden AND l.Eliminado = 0
JOIN usuarios u ON u.id = l.idUsuarioChofer AND TRIM(IFNULL(u.Usuario, '')) <> ''
SET s.NumerodeOrden = x.orden, s.Usuario = u.Usuario;

-- Control: lo que queda como 'APP' (solo "No entrega" sin Hoja de Ruta ese día)
SELECT Fecha, CodigoSeguimiento, Estado, NumerodeOrden
FROM Seguimiento WHERE Usuario = 'APP' AND Eliminado = 0 ORDER BY Fecha;
