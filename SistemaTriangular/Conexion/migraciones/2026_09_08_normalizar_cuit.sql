-- ============================================================================
-- NORMALIZACION DE CUIT  (Proveedores / IvaCompras / TransProveedores)
-- Generado 2026-09-07 desde produccion.  (data-fix, NO es migracion de esquema)
-- Reemplaza a 2026_09_08_backfill_ivacompras_cuit.sql (aquel solo llenaba vacios;
-- este ademas corrige CUIT mal tipeados y unifica el formato).
--
-- Diagnostico (base de produccion, filas VIVAS):
--   * CierreIva de IvaCompras solo llega hasta 2018-07  -> TODO lo que se toca
--     aca (2023-2026) esta en periodos ABIERTOS. Sin riesgo de tocar un libro
--     ya presentado.
--   * IvaCompras: 14.359 vivas. 360 tienen CUIT != al del proveedor (por
--     RazonSocial, match unico): 86 vacio + ~1 formato + ~273 digitos mal
--     tipeados (ej: SANTIAGO MAURO HEREDIA cargado '38411478' vs '20384114785';
--     PREMIUM WATER '3071766417' vs '30717666417').
--   * TransProveedores: ~668 en la misma situacion.
--   * Formato mezclado: ~6.900 con guiones, ~6.900 sin guiones.
--   * 0 proveedores con RazonSocial -> 2 CUIT distintos (match siempre unico).
--
-- Criterio: el CUIT de Proveedores manda (se valida una vez en ABM). Se empuja
-- a IvaCompras/TransProveedores/AnticiposProveedores por RazonSocial (exacta, sin
-- distinguir mayusculas/espacios). Formato canonico: 11 digitos, SIN guiones.
--
-- TABLAS que guardan CUIT de PROVEEDOR (todas se tocan aca):
--   Proveedores, IvaCompras, TransProveedores, AnticiposProveedores.
-- NO se tocan (son del lado CLIENTES/VENTAS, otra limpieza aparte):
--   Clientes, Ctasctes(_1..9), TransClientes(*), IvaVentas, Ventas(*),
--   Facturacion (factura electronica de ventas), DatosEmpresa (CUIT propio).
-- Avisar a contaduria:
--   * Si ya bajaron/presentaron el Libro IVA Compras (IvaCompraspdf.php) de
--     algun mes 2023-2026, re-exportarlo: los CUIT van a salir corregidos.
--   * 34 entidades son proveedor Y cliente con el mismo CUIT; aca solo se
--     corrige el lado proveedor.
-- No hay integracion con AFIP/SICORE/retenciones que dependa de estos CUIT.
--
-- >>> ANTES de correr esto: arreglar a mano los 38 proveedores con CUIT mal    <<<
-- >>> formado (ver PASO 0). Si no, se propaga la basura.                       <<<
-- >>> Correr en transaccion, verificar, COMMIT; / ROLLBACK;                    <<<
-- >>> En phpMyAdmin: cada "Continuar" es una conexion nueva -> START           <<<
-- >>> TRANSACTION sin COMMIT en el mismo envio se revierte solo. Pegar TODO    <<<
-- >>> junto y terminar con COMMIT;  o correr sin START TRANSACTION con backup.  <<<
-- ============================================================================


-- ============================================================================
-- PASO 0  (MANUAL, no automatizable) -- 38 proveedores con CUIT mal formado.
-- Buscar el CUIT real (AFIP / la factura) y:  UPDATE Proveedores SET Cuit='NNNNNNNNNNN' WHERE id=XX;
-- ----------------------------------------------------------------------------
--   #588  Elevar Autoelevadores SRL            '588'
--   #618  CANI SA                              '33-5665486-9'
--   #698  Cerrajeria Dedos de Oro             '20-1615847-2'
--   #706  Cristian R. Macario                  '3-11111111-1'
--   #810  GARCIA DAMIAN EZEQUIEL               '2033808707'
--   #829  GROSSO GONZALO Y FLIA S.H            '307092281425'
--   #900  Estacion Patria S.A                  '3071247561'
--   #911  JUAN MONDINO E HIJO S.A              '337107818439'
--   #931  AIMETA SRL                           '307176128'
--   #936  PAMPA ENERGIA S.A                    '305265522659'
--   #963  EST. DE SERVICIO PANAMERICANA S.R.L  '3061605278'
--   #999  Tienda S.A.S                         '337161771189'
--   #1014 JARDINES URBANOS S.R.L               '3371328799'
--   #1036 Gonzales Sosa, Damian                '232819993119'
--   #1048 COL-VEN S.A                          '3056031241'
--   #1069 FORZA NEUMATICOS                     '321654987654'
--   #1079 BulonerIa y FerreterIa Ind. San Carl '0123654985201'
--   #1112 Switch2.com 3M                       '2020202019'
--   #1123 GRAMMA SEGURIDAD INDUSTRIAL S.R.L    '00213123213213'
--   #1135 Baterias Velez Sarsfield             '321654854792'
--   #1137 Cordoba Suspension                   '6547895432185'
--   #1138 MALDONADO NEUMATICOS                 '654987128524'
--   #1162 LA CASA DEL RETROVISOR               '12365498745621'
--   #1169 MARTINEZ REPUESTOS                   '00012233'
--   #1170 PEUCOR                               '3164632165'
--   #1190 MONTE DE ROBLES SRL                  '307110307035'
--   #1268 DOYLE MATIAS EDGARDO                 '2032800998'
--   #1277 Expreso Micro Cargas                 '20146220364/1'
--   #1309 REPARKER S.A                         '307088477700'
--   #1325 GROUP MOBILE S.A.                    '30-7122226351-'
--   #1359 Martinez & Asoc                     '272237096678'
--   #1436 NEIRA NESTOR                         '2013505531'
--   #1440 PLAZA LAVALLE SA                     '280544132'
--   #1496 CONTRERAS HUGO LUIS (TAURO)          '2011928593'
--   #1528 Oliva Franco Raul                    '2038504659'
--   #1557 MAtias Ferreyra                      '42785496'
--   #1595 REPUESTOS AVENIDA DE GRASSINI HNOS   '307162906777'
--   #1684 OVIEDO MAURICIO GERMAN               '204216039888'
-- ----------------------------------------------------------------------------
-- Chequeo despues de arreglarlos (debe dar 0):
--   SELECT COUNT(*) FROM Proveedores
--   WHERE TRIM(IFNULL(Cuit,'')) NOT IN ('','0')
--     AND REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','') NOT REGEXP '^[0-9]{11}$';
-- ============================================================================


START TRANSACTION;

-- --- PASO 1: formato canonico en Proveedores (11 digitos, sin guiones/espacios)
UPDATE Proveedores
SET Cuit = REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','')
WHERE TRIM(IFNULL(Cuit,'')) NOT IN ('','0')
  AND Cuit <> REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','');

-- --- PASO 2: empujar el CUIT bueno del proveedor a IvaCompras (vacios + mal tipeados + formato)
UPDATE IvaCompras ic
JOIN (
    SELECT LOWER(TRIM(RazonSocial)) rs, REPLACE(REPLACE(MAX(NULLIF(TRIM(Cuit),'')),'-',''),' ','') cuit
    FROM Proveedores
    WHERE RazonSocial IS NOT NULL AND TRIM(RazonSocial) <> ''
    GROUP BY LOWER(TRIM(RazonSocial))
    HAVING COUNT(DISTINCT NULLIF(TRIM(Cuit),'')) = 1
       AND MAX(NULLIF(TRIM(Cuit),'')) IS NOT NULL
) p ON p.rs = LOWER(TRIM(ic.RazonSocial))
SET ic.Cuit   = p.cuit,
    ic.InfoABM = CONCAT(IFNULL(ic.InfoABM,''), ' CUIT norm<-Proveedores ', DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
WHERE ic.Eliminado = 0
  AND REPLACE(REPLACE(TRIM(IFNULL(ic.Cuit,'')),'-',''),' ','') <> p.cuit;

-- --- PASO 3: idem TransProveedores
UPDATE TransProveedores tp
JOIN (
    SELECT LOWER(TRIM(RazonSocial)) rs, REPLACE(REPLACE(MAX(NULLIF(TRIM(Cuit),'')),'-',''),' ','') cuit
    FROM Proveedores
    WHERE RazonSocial IS NOT NULL AND TRIM(RazonSocial) <> ''
    GROUP BY LOWER(TRIM(RazonSocial))
    HAVING COUNT(DISTINCT NULLIF(TRIM(Cuit),'')) = 1
       AND MAX(NULLIF(TRIM(Cuit),'')) IS NOT NULL
) p ON p.rs = LOWER(TRIM(tp.RazonSocial))
SET tp.Cuit   = p.cuit,
    tp.InfoABM = CONCAT(IFNULL(tp.InfoABM,''), ' CUIT norm<-Proveedores ', DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
WHERE tp.Eliminado = 0
  AND REPLACE(REPLACE(TRIM(IFNULL(tp.Cuit,'')),'-',''),' ','') <> p.cuit;

-- --- PASO 3b: idem AnticiposProveedores (anticipos / pagos a cuenta a proveedor).
--             Se matchea con las facturas por Cuit -> tiene que quedar igual que
--             IvaCompras / TransProveedores o la pantalla de aplicar pagos deja
--             de encontrar las filas.
UPDATE AnticiposProveedores ap
JOIN (
    SELECT LOWER(TRIM(RazonSocial)) rs, REPLACE(REPLACE(MAX(NULLIF(TRIM(Cuit),'')),'-',''),' ','') cuit
    FROM Proveedores
    WHERE RazonSocial IS NOT NULL AND TRIM(RazonSocial) <> ''
    GROUP BY LOWER(TRIM(RazonSocial))
    HAVING COUNT(DISTINCT NULLIF(TRIM(Cuit),'')) = 1
       AND MAX(NULLIF(TRIM(Cuit),'')) IS NOT NULL
) p ON p.rs = LOWER(TRIM(ap.RazonSocial))
SET ap.Cuit = p.cuit
WHERE ap.Eliminado = 0
  AND REPLACE(REPLACE(TRIM(IFNULL(ap.Cuit,'')),'-',''),' ','') <> p.cuit;

-- --- PASO 3c: quitar guiones/espacios en AnticiposProveedores sin match de prov
UPDATE AnticiposProveedores
SET Cuit = REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','')
WHERE Eliminado = 0 AND TRIM(IFNULL(Cuit,'')) NOT IN ('','0')
  AND Cuit <> REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','');

-- --- PASO 4: quitar guiones/espacios en las filas SIN match de proveedor (deja
--            toda la columna en el mismo formato). No cambia digitos.
UPDATE IvaCompras
SET Cuit = REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','')
WHERE Eliminado = 0 AND TRIM(IFNULL(Cuit,'')) NOT IN ('','0')
  AND Cuit <> REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','');

UPDATE TransProveedores
SET Cuit = REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','')
WHERE Eliminado = 0 AND TRIM(IFNULL(Cuit,'')) NOT IN ('','0')
  AND Cuit <> REPLACE(REPLACE(TRIM(Cuit),'-',''),' ','');


-- ============================================================================
-- VERIFICACION (mirar antes de COMMIT)
-- ============================================================================

-- (a) IvaCompras vivas cuyo CUIT NO coincide con el del proveedor -> esperado ~9
--     (los que quedan: RazonSocial vacia, o proveedor sin CUIT en ABM)
SELECT COUNT(*) AS ivacompras_cuit_no_coincide
FROM IvaCompras ic
JOIN (
    SELECT LOWER(TRIM(RazonSocial)) rs, REPLACE(REPLACE(MAX(NULLIF(TRIM(Cuit),'')),'-',''),' ','') cuit
    FROM Proveedores WHERE TRIM(IFNULL(RazonSocial,'')) <> ''
    GROUP BY LOWER(TRIM(RazonSocial))
    HAVING COUNT(DISTINCT NULLIF(TRIM(Cuit),'')) = 1 AND MAX(NULLIF(TRIM(Cuit),'')) IS NOT NULL
) p ON p.rs = LOWER(TRIM(ic.RazonSocial))
WHERE ic.Eliminado = 0
  AND REPLACE(REPLACE(TRIM(IFNULL(ic.Cuit,'')),'-',''),' ','') <> p.cuit;

-- (b) CUIT que quedan con formato != 11 digitos (revisar uno por uno)
SELECT 'IvaCompras' t, COUNT(*) n FROM IvaCompras
 WHERE Eliminado=0 AND TRIM(IFNULL(Cuit,'')) NOT IN ('','0') AND Cuit NOT REGEXP '^[0-9]{11}$'
UNION ALL
SELECT 'TransProveedores', COUNT(*) FROM TransProveedores
 WHERE Eliminado=0 AND TRIM(IFNULL(Cuit,'')) NOT IN ('','0') AND Cuit NOT REGEXP '^[0-9]{11}$'
UNION ALL
SELECT 'Proveedores', COUNT(*) FROM Proveedores
 WHERE TRIM(IFNULL(Cuit,'')) NOT IN ('','0') AND Cuit NOT REGEXP '^[0-9]{11}$';

-- (c) IvaCompras vivas todavia sin CUIT -> esperado 9 (6 RazonSocial vacia + 3 GASTRO/NORTEX)
SELECT COUNT(*) AS ivacompras_sin_cuit
FROM IvaCompras WHERE Eliminado=0 AND (Cuit IS NULL OR TRIM(Cuit) IN ('','0'));

-- Revisar (a)(b)(c) y luego:  COMMIT;   (o  ROLLBACK;)
