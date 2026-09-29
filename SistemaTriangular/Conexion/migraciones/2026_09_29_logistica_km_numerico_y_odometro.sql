-- =====================================================================
-- 2026-09-29  Logistica: km de regreso numérico + corrección de odómetros
-- =====================================================================
--
-- Contexto (lo detectó el pedido de Jerónimo de un resumen de km por chofer):
--  * Desde el 7/9 los recorridos de vehículos propios se cierran desde la app
--    de reparto (finalizar_recorrido.php), que grababa el km de regreso en la
--    orden pero NO actualizaba Vehiculos.Kilometros. El cierre desde el sistema
--    (Órdenes de salida) tampoco lo hacía. Cada orden nueva toma el km de salida
--    de Vehiculos.Kilometros, así que salían con un odómetro viejo (AI066JD con
--    16474 desde el 9/9) y los "km recorridos" se acumulaban de un viaje al otro
--    (Ángel 29/9: 2493 km en vez de 193).
--    El código ya quedó arreglado (los dos cierres actualizan el odómetro).
--  * Logistica.KilometrosRegreso era char(50): MAX()/comparaciones daban mal
--    ("9835" > "18967"). Pasa a INT, como Kilometros y KilometrosRecorridos.
--
-- Qué hace (correr en producción una sola vez, en este orden):
--  1) Limpia los 4 valores no numéricos + vacíos/NULL de KilometrosRegreso.
--  2) Cambia la columna a INT.
--  3) Vehículos propios, órdenes desde el 7/9/2026: la salida pasa a ser el
--     último regreso anterior de esa patente (si es mayor y no más de 10.000 km),
--     y se recalculan los km recorridos y el costo por km imputado.
--  4) Deja Vehiculos.Kilometros en el último regreso real.
-- Idempotente: si se vuelve a correr no cambia nada más.
-- =====================================================================

-- 0) PREVIEW (opcional): órdenes que se corrigen en el paso 3
-- SELECT x.id, x.Fecha, x.Patente, x.NombreChofer, x.Kilometros AS salida_actual, x.prev AS salida_ok,
--        x.reg AS regreso, x.KilometrosRecorridos AS rec_actual,
--        IF(x.reg > 0, GREATEST(x.reg - x.prev, 0), 0) AS rec_ok
-- FROM ( SELECT l.id, l.Fecha, l.Patente, l.NombreChofer, l.Kilometros, l.KilometrosRecorridos,
--               CAST(l.KilometrosRegreso AS UNSIGNED) AS reg,
--               MAX(CAST(l.KilometrosRegreso AS UNSIGNED)) OVER (PARTITION BY l.Patente ORDER BY l.id
--                   ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING) AS prev
--        FROM Logistica l JOIN Vehiculos v ON v.Dominio = l.Patente AND v.Aliados = 0
--        WHERE l.Eliminado = 0 AND l.Fecha >= '2026-09-01' ) x
-- WHERE x.Fecha >= '2026-09-07' AND x.prev > x.Kilometros AND x.prev - x.Kilometros <= 10000
-- ORDER BY x.Patente, x.id;

-- 1) Valores no numéricos (4 filas viejas, 2021-2025) y vacíos
-- (se compara como texto para que se pueda volver a correr con la columna ya numérica)
UPDATE Logistica SET KilometrosRegreso = '161337' WHERE id = 5229  AND CAST(KilometrosRegreso AS CHAR) = '161337º';
UPDATE Logistica SET KilometrosRegreso = '222508' WHERE id = 9378  AND CAST(KilometrosRegreso AS CHAR) = ' 222508';
UPDATE Logistica SET KilometrosRegreso = '0'      WHERE id = 10237 AND CAST(KilometrosRegreso AS CHAR) = '4/8';
UPDATE Logistica SET KilometrosRegreso = '0'      WHERE id = 13053 AND CAST(KilometrosRegreso AS CHAR) = '9:50';
UPDATE Logistica SET KilometrosRegreso = '0'      WHERE KilometrosRegreso IS NULL OR TRIM(CAST(KilometrosRegreso AS CHAR)) = '';

-- Control: tiene que dar 0 antes de seguir
SELECT COUNT(*) AS no_numericos FROM Logistica WHERE CAST(KilometrosRegreso AS CHAR) NOT REGEXP '^[0-9]+$';

-- 2) Columna numérica
ALTER TABLE Logistica MODIFY KilometrosRegreso INT(20) NULL DEFAULT 0;

-- 3) Salida = último regreso anterior de la patente; km recorridos y costo imputado
UPDATE Logistica l
JOIN ( SELECT x.id, x.prev
       FROM ( SELECT l2.id, l2.Fecha, l2.Kilometros,
                     MAX(l2.KilometrosRegreso) OVER (PARTITION BY l2.Patente ORDER BY l2.id
                         ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING) AS prev
              FROM Logistica l2 JOIN Vehiculos v ON v.Dominio = l2.Patente AND v.Aliados = 0
              WHERE l2.Eliminado = 0 AND l2.Fecha >= '2026-09-01' ) x
       WHERE x.Fecha >= '2026-09-07' AND x.prev > x.Kilometros AND x.prev - x.Kilometros <= 10000 ) c
  ON c.id = l.id
SET l.Kilometros = c.prev,
    l.KilometrosRecorridos = IF(l.KilometrosRegreso > 0, GREATEST(l.KilometrosRegreso - c.prev, 0), 0),
    l.CostoKmTotalImputado = IF(l.CostoKmValorImputado > 0 AND l.KilometrosRegreso > 0,
                                ROUND(GREATEST(l.KilometrosRegreso - c.prev, 0) * l.CostoKmValorImputado, 2),
                                l.CostoKmTotalImputado);

-- 4) Odómetro de los vehículos propios = último regreso real (si es mayor y coherente)
UPDATE Vehiculos v
JOIN ( SELECT l.Patente, l.KilometrosRegreso AS ultimo
       FROM Logistica l
       JOIN ( SELECT Patente, MAX(id) AS id FROM Logistica
              WHERE Eliminado = 0 AND KilometrosRegreso > 0 GROUP BY Patente ) u ON u.id = l.id ) r
  ON r.Patente = v.Dominio
SET v.Kilometros = r.ultimo
WHERE v.Aliados = 0 AND r.ultimo > IFNULL(v.Kilometros, 0) AND r.ultimo - IFNULL(v.Kilometros, 0) <= 10000;

-- Control final
SELECT Dominio, Kilometros FROM Vehiculos WHERE Aliados = 0 AND VehiculoOperativo = 1;
