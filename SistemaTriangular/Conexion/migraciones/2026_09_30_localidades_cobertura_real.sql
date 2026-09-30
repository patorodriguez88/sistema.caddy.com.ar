-- =====================================================================
-- 2026-09-30  Localidades: cobertura real (base de la oferta ME1 de Mercado Libre)
-- =====================================================================
-- Se cruzó la tabla con los servicios ENTREGADOS de los últimos 6 meses (26 semanas):
--   Frecuente  = 52 días o más con entregas (2+ días por semana)
--   Semanal    = 20 a 51 días (alrededor de 1 por semana)
--   Esporádica = 1 a 19 días
--   (sin valor) = ninguna entrega en 6 meses
-- Córdoba Capital se marca Frecuente a mano (en los servicios figura como "Córdoba").
--
-- Qué hace:
--  1) Columna nueva Frecuencia (para saber qué plazo prometer, ej. en ME1).
--  2) Web = 1 en todas las frecuentes y semanales (se suman 11 que no estaban:
--     Laguna Larga, Malvinas Argentinas, Villa del Rosario, Carlos Paz CP 5153,
--     Embalse (x2), Las Varillas, Santa Rosa de Calamuchita, Luque, Pozo del Molle, Sacanta).
--  3) Web = 0 en las 8 ofrecidas sin ninguna entrega en 6 meses: Cabana, Colonia
--     Prosperidad, Despeñaderos, Frontera, Icho Cruz, Anisacate, Tío Pujio, Cuesta Blanca.
--  4) Alta de 3 localidades activas que no estaban (CP y km sacados de nuestros clientes
--     y servicios): Monte Cristo, Calchín, Villa Giardino.
-- No se borra nada: Carlos Paz (5152/5153) y Embalse (5856/5157) tienen dos filas porque
-- la API busca por código postal. La marca Web hoy no filtra ninguna cotización
-- (Ventas/Procesos/php/localidades.php devuelve siempre 1). Idempotente.
-- =====================================================================

-- 1) Columna Frecuencia
ALTER TABLE Localidades ADD COLUMN IF NOT EXISTS Frecuencia VARCHAR(20) NULL DEFAULT NULL AFTER DiaSalida;

UPDATE Localidades SET Frecuencia = 'Frecuente'  WHERE id IN (1, 8, 9, 47, 48, 49, 54, 56, 61, 68, 70, 76, 77, 78, 89, 91, 97);
UPDATE Localidades SET Frecuencia = 'Semanal'    WHERE id IN (3, 5, 7, 14, 19, 25, 32, 38, 42, 55, 59, 69, 73, 79, 81, 83, 87, 90, 95, 128, 138, 165, 173, 203);
UPDATE Localidades SET Frecuencia = 'Esporádica' WHERE id IN (4, 13, 16, 20, 23, 26, 27, 29, 39, 44, 45, 50, 58, 63, 64, 65, 66, 74, 84, 85, 88, 92, 93, 94, 96, 98, 99, 100, 101, 109, 115, 124, 127, 129, 131, 132, 139, 144, 153, 156, 164, 169, 171, 178, 179, 191, 198, 199, 200, 206, 220);
UPDATE Localidades SET Frecuencia = NULL         WHERE id IN (2, 6, 10, 11, 12, 15, 17, 18, 21, 22, 24, 28, 30, 31, 33, 34, 35, 36, 37, 40, 41, 43, 46, 51, 52, 53, 57, 60, 62, 67, 71, 72, 75, 80, 82, 86, 102, 103, 104, 105, 108, 110, 111, 112, 113, 114, 116, 117, 118, 119, 120, 121, 122, 123, 125, 126, 130, 133, 134, 136, 137, 141, 142, 143, 145, 146, 147, 148, 149, 150, 151, 152, 154, 155, 157, 160, 161, 162, 163, 166, 167, 168, 170, 172, 174, 175, 176, 177, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 192, 193, 194, 195, 196, 197, 201, 202, 204, 205, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219);

-- 2) y 3) Marca Web
UPDATE Localidades SET Web = 1 WHERE Frecuencia IN ('Frecuente', 'Semanal');
UPDATE Localidades SET Web = 0 WHERE id IN (6, 12, 18, 22, 24, 80, 82, 86);

-- 4) Localidades activas que faltaban
INSERT INTO Localidades (Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida, Frecuencia)
SELECT * FROM (SELECT 'Monte Cristo' l, 'Cordoba' p, '' r, 1 w, '36' k, '5125' c, '' d, 'Semanal' f) x
WHERE NOT EXISTS (SELECT 1 FROM Localidades WHERE Localidad = 'Monte Cristo');
INSERT INTO Localidades (Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida, Frecuencia)
SELECT * FROM (SELECT 'Calchín' l, 'Cordoba' p, '' r, 1 w, '117' k, '5969' c, '' d, 'Semanal' f) x
WHERE NOT EXISTS (SELECT 1 FROM Localidades WHERE Localidad = 'Calchín');
INSERT INTO Localidades (Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida, Frecuencia)
SELECT * FROM (SELECT 'Villa Giardino' l, 'Cordoba' p, '' r, 1 w, '82' k, '5176' c, '' d, 'Semanal' f) x
WHERE NOT EXISTS (SELECT 1 FROM Localidades WHERE Localidad = 'Villa Giardino');

-- Control
SELECT Frecuencia, Web, COUNT(*) FROM Localidades GROUP BY Frecuencia, Web ORDER BY Frecuencia, Web;
