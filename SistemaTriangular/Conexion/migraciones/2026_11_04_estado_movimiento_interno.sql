-- Envíos con TransClientes.Estado = 'Movimiento Interno' (4/11).
-- Los movimientos internos no cambian el estado del envío, pero el botón "Corregir" de la ficha de
-- seguimiento copiaba el último movimiento a TransClientes, y así se perdía el estado real ("No se pudo
-- entregar", "Entregado al Cliente"...). Vuelve al último movimiento que sí cambia el estado.
-- Correr DESPUÉS de subir el código de la ficha: si no, se puede volver a pisar.

-- Vista previa: cómo quedarían
SELECT IF(t.Entregado = 1, 'Entregado al Cliente', s.Estado) AS EstadoNuevo, t.Entregado, t.Devuelto, COUNT(*) AS Envios
FROM TransClientes t
JOIN (SELECT CodigoSeguimiento COLLATE utf8mb3_general_ci AS cs, MAX(id) AS mid FROM Seguimiento
      WHERE IFNULL(Eliminado, 0) = 0 AND Estado NOT IN ('Movimiento Interno', 'En centro de distribución', 'Salió del depósito')
      GROUP BY CodigoSeguimiento) u ON u.cs = t.CodigoSeguimiento COLLATE utf8mb3_general_ci
JOIN Seguimiento s ON s.id = u.mid
WHERE t.Eliminado = 0 AND t.Estado = 'Movimiento Interno'
GROUP BY 1, 2, 3 ORDER BY 4 DESC;

UPDATE TransClientes t
JOIN (SELECT CodigoSeguimiento COLLATE utf8mb3_general_ci AS cs, MAX(id) AS mid FROM Seguimiento
      WHERE IFNULL(Eliminado, 0) = 0 AND Estado NOT IN ('Movimiento Interno', 'En centro de distribución', 'Salió del depósito')
      GROUP BY CodigoSeguimiento) u ON u.cs = t.CodigoSeguimiento COLLATE utf8mb3_general_ci
JOIN Seguimiento s ON s.id = u.mid
SET t.Estado = IF(t.Entregado = 1, 'Entregado al Cliente', s.Estado)
WHERE t.Eliminado = 0 AND t.Estado = 'Movimiento Interno';

-- Control: tiene que dar 0
SELECT COUNT(*) AS QuedanEnMovimientoInterno FROM TransClientes WHERE Eliminado = 0 AND Estado = 'Movimiento Interno';
