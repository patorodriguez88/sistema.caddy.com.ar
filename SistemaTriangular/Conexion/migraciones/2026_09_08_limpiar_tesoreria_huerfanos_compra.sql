-- ============================================================================
-- LIMPIEZA de lineas Tesoreria HUERFANAS de facturas de compra dadas de baja
-- Generado 2026-09-07 23:59 desde produccion. NO es migracion de esquema.
--
-- El bug (Borrar_Factura_ok anulaba Tesoreria solo por idTransProvee, dejaba
-- viva la linea de ACREEDORES con idTransProvee=0) ya esta tapado en codigo:
--   sistema.caddy.com.ar commit 8f1cde71  +  Caddy_produccion commit 1e6d6f0.
-- Esto limpia lo que ya quedo sucio: asientos de compra cuyo IvaCompras esta
-- 100% Eliminado=1 pero tienen lineas de Tesoreria vivas que NO cuadran.
-- Se anulan esas lineas (la factura esta borrada -> el asiento no debe tener
-- ninguna linea viva). Solo desde 2025-07-01 (2025 H2 + 2026).
--
-- 133 asientos, 133 lineas.
-- >>> Revisar con contaduria. Transaccion. Verificar descuadre mensual. COMMIT/ROLLBACK. <<<
-- ============================================================================

-- Descuadre mensual que corrige (por mes de la linea huerfana):
--   2025-07 : -5,142,510.02
--   2025-08 : -11,300,373.71
--   2025-09 : -4,242,295.18
--   2025-10 : -1,244,134.33
--   2025-11 : -1,275,402.04
--   2025-12 : -2,123,888.97
--   2026-01 : -866,057.97
--   2026-02 : -2,077,591.73
--   2026-03 : -2,167,954.40
--   2026-04 : -6,654,326.07
--   2026-05 : -5,001,916.83
--   2026-06 : -13,765,397.92
--   2026-07 : -5,584,923.16
--   2026-08 : -20,625.01

START TRANSACTION;

-- asiento 37367857 | 2025-07-01 | Carga de: FACTURAS A Numero: 00001-00043942 | dif -70,800.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121258) AND Eliminado=0;

-- asiento 37367888 | 2025-07-01 | Carga de: FACTURAS A Numero: 01331-00758879 | dif -130,254.71 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121350) AND Eliminado=0;

-- asiento 37367872 | 2025-07-02 | Carga de: FACTURAS A Numero: 00001-00002025 | dif -34,099.99 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121312) AND Eliminado=0;

-- asiento 37367962 | 2025-07-02 | Carga de: FACTURAS A Numero: 00001-00005051 | dif -20,000.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121588) AND Eliminado=0;

-- asiento 37368118 | 2025-07-02 | Carga de: FACTURAS A Numero: 00001-00002013 | dif -34,099.99 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122038) AND Eliminado=0;

-- asiento 37368282 | 2025-07-04 | Carga de: OTROS COMP  QUE NO CUMPLEN CON LA R G  | dif -2,237,882.90 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122497) AND Eliminado=0;

-- asiento 37367886 | 2025-07-08 | Carga de: FACTURAS C Numero: 00001-00000032 | dif -300,411.21 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121346) AND Eliminado=0;

-- asiento 37367959 | 2025-07-15 | Carga de: FACTURAS A Numero: 00126-00052251 | dif -4,714.05 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121579) AND Eliminado=0;

-- asiento 37368130 | 2025-07-16 | Carga de: FACTURAS C Numero: 00002-00000584 | dif -180,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122066) AND Eliminado=0;

-- asiento 37368131 | 2025-07-18 | Carga de: FACTURAS C Numero: 00002-00000586 | dif -480,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122068) AND Eliminado=0;

-- asiento 37368003 | 2025-07-21 | Carga de: FACTURAS C Numero: 00001-00000030 | dif -488,600.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121700) AND Eliminado=0;

-- asiento 37367884 | 2025-07-25 | Carga de: FACTURAS A Numero: 00044-00044220 | dif -121,369.76 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121340) AND Eliminado=0;

-- asiento 37368144 | 2025-07-29 | Carga de: FACTURAS A Numero: 00152-00758853 | dif -692,513.53 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122099) AND Eliminado=0;

-- asiento 37367918 | 2025-07-30 | Carga de: FACTURAS C Numero: 00001-00000041 | dif -236,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121458) AND Eliminado=0;

-- asiento 37367939 | 2025-07-30 | Carga de: FACTURAS A Numero: 00002-00006456 | dif -111,763.82 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121519) AND Eliminado=0;

-- asiento 37368358 | 2025-08-04 | Carga de: FACTURAS C Numero: 00001-00000042 | dif -270,750.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122697) AND Eliminado=0;

-- asiento 37368064 | 2025-08-05 | Carga de: FACTURAS A Numero: 00005-00040576 | dif -235,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121850) AND Eliminado=0;

-- asiento 37368161 | 2025-08-06 | Carga de: FACTURAS A Numero: 00004-00006286 | dif -104,320.44 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122146) AND Eliminado=0;

-- asiento 37368171 | 2025-08-07 | Carga de: FACTURAS A Numero: 00010-00020094 | dif -89,335.13 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122175) AND Eliminado=0;

-- asiento 37368170 | 2025-08-08 | Carga de: FACTURAS A Numero: 00054-00007744 | dif -840,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122170) AND Eliminado=0;

-- asiento 37368521 | 2025-08-08 | Carga de: FACTURAS A Numero: 00005-00030839 | dif -100,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123161) AND Eliminado=0;

-- asiento 37368370 | 2025-08-12 | Carga de: FACTURAS A Numero: 00010-00020154 | dif -74,839.59 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122726) AND Eliminado=0;

-- asiento 37368549 | 2025-08-12 | Carga de: FACTURAS A Numero: 00010-00020154 | dif -74,839.59 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123235) AND Eliminado=0;

-- asiento 37368397 | 2025-08-14 | Carga de: FACTURAS C Numero: 00001-00000039 | dif -87,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122806) AND Eliminado=0;

-- asiento 37368360 | 2025-08-15 | Carga de: FACTURAS C Numero: 00002-00000940 | dif -579,800.54 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122701) AND Eliminado=0;

-- asiento 37368494 | 2025-08-18 | Carga de: FACTURAS C Numero: 00001-00000041 | dif -440,108.19 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123081) AND Eliminado=0;

-- asiento 37368730 | 2025-08-18 | Carga de: FACTURAS A Numero: 00004-00004850 | dif -72,336.60 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123745) AND Eliminado=0;

-- asiento 37368731 | 2025-08-18 | Carga de: FACTURAS A Numero: 00004-00004852 | dif -72,336.60 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123748) AND Eliminado=0;

-- asiento 37368732 | 2025-08-18 | Carga de: FACTURAS A Numero: 00004-00004851 | dif -72,336.60 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123751) AND Eliminado=0;

-- asiento 37368444 | 2025-08-19 | Carga de: FACTURAS C Numero: 00001-00000005 | dif -90,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (122947) AND Eliminado=0;

-- asiento 37368597 | 2025-08-20 | Carga de: FACTURAS A Numero: 04264-05879608 | dif -26,638.90 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123378) AND Eliminado=0;

-- asiento 37368004 | 2025-08-21 | Carga de: FACTURAS C Numero: 00001-00000030 | dif -488,600.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (121702) AND Eliminado=0;

-- asiento 37368540 | 2025-08-21 | Carga de: FACTURAS C Numero: 00002-00000941 | dif -579,800.54 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123212) AND Eliminado=0;

-- asiento 37368541 | 2025-08-21 | Carga de: FACTURAS C Numero: 00002-00000942 | dif -642,651.57 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123214) AND Eliminado=0;

-- asiento 37368545 | 2025-08-21 | Carga de: FACTURAS A Numero: 00020-00007214 | dif -359,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123223) AND Eliminado=0;

-- asiento 37368546 | 2025-08-21 | Carga de: FACTURAS A Numero: 00020-00007214 | dif -359,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123226) AND Eliminado=0;

-- asiento 37368547 | 2025-08-21 | Carga de: FACTURAS A Numero: 00002-00007214 | dif -359,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123229) AND Eliminado=0;

-- asiento 37368556 | 2025-08-21 | Carga de: FACTURAS A Numero: 00005-00025783 | dif -1,452,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123252) AND Eliminado=0;

-- asiento 37368675 | 2025-08-21 | Carga de: FACTURAS A Numero: 00008-00014219 | dif -63,571.15 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123591) AND Eliminado=0;

-- asiento 37368876 | 2025-08-21 | Carga de: FACTURAS A Numero: 00008-00014219 | dif -63,571.15 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124159) AND Eliminado=0;

-- asiento 37368891 | 2025-08-21 | Carga de: FACTURAS A Numero: 00002-00000941 | dif -579,800.54 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124207) AND Eliminado=0;

-- asiento 37368658 | 2025-08-26 | Carga de: FACTURAS A Numero: 00009-00004337 | dif -8,145.98 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123545) AND Eliminado=0;

-- asiento 37368640 | 2025-08-27 | Carga de: FACTURAS A Numero: 00019-00077562 | dif -718,749.43 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123499) AND Eliminado=0;

-- asiento 37368664 | 2025-08-27 | Carga de: FACTURAS A Numero: 00005-00000771 | dif -1,447,117.23 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123563) AND Eliminado=0;

-- asiento 37368878 | 2025-08-29 | Carga de: FACTURAS A Numero: 00032-00002718 | dif -621,659.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124167) AND Eliminado=0;

-- asiento 37368854 | 2025-08-31 | Carga de: FACTURAS A Numero: 00002-00003078 | dif -323,904.90 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124096) AND Eliminado=0;

-- asiento 37368939 | 2025-08-31 | Carga de: FACTURAS A Numero: 00600-00077128 | dif -4,160.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124349) AND Eliminado=0;

-- asiento 37369021 | 2025-09-01 | Carga de: FACTURAS A Numero: 00001-00044670 | dif -70,800.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124642) AND Eliminado=0;

-- asiento 37369028 | 2025-09-01 | Carga de: FACTURAS A Numero: 01331-01079704 | dif -135,702.38 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124668) AND Eliminado=0;

-- asiento 37369031 | 2025-09-02 | Carga de: FACTURAS A Numero: 00002-00097077 | dif -164,076.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124678) AND Eliminado=0;

-- asiento 37368813 | 2025-09-03 | Carga de: FACTURAS A Numero: 00004-00004370 | dif -32,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (123977) AND Eliminado=0;

-- asiento 37368843 | 2025-09-04 | Carga de: FACTURAS A Numero: 00002-00026590 | dif -210,881.79 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124056) AND Eliminado=0;

-- asiento 37368886 | 2025-09-08 | Carga de: FACTURAS A Numero: 00005-00030839 | dif -100,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124192) AND Eliminado=0;

-- asiento 37368981 | 2025-09-09 | Carga de: FACTURAS A Numero: 00005-00026813 | dif -1,176,120.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124539) AND Eliminado=0;

-- asiento 37368987 | 2025-09-09 | Carga de: FACTURAS A Numero: 15016-00003540 | dif -115,228.06 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124556) AND Eliminado=0;

-- asiento 37368988 | 2025-09-09 | Carga de: FACTURAS A Numero: 15016-00003540 | dif -115,228.06 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124561) AND Eliminado=0;

-- asiento 37369318 | 2025-09-10 | Carga de: FACTURAS A Numero: 00002-00038130 | dif -35,730.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125487) AND Eliminado=0;

-- asiento 37368991 | 2025-09-11 | Carga de: FACTURAS A Numero: 00004-00004394 | dif -5,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124571) AND Eliminado=0;

-- asiento 37369337 | 2025-09-11 | Carga de: FACTURAS A Numero: 00004-00004394 | dif -5,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125549) AND Eliminado=0;

-- asiento 37369107 | 2025-09-18 | Carga de: FACTURAS C Numero: 00001-00000013 | dif -342,913.75 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124897) AND Eliminado=0;

-- asiento 37369200 | 2025-09-18 | Carga de: FACTURAS C Numero: 00001-00000013 | dif -342,913.75 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125141) AND Eliminado=0;

-- asiento 37369133 | 2025-09-19 | Carga de: FACTURAS C Numero: 00003-00000047 | dif -56,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (124963) AND Eliminado=0;

-- asiento 37369241 | 2025-09-23 | Carga de: FACTURAS A Numero: 00003-00009647 | dif -106,255.74 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125261) AND Eliminado=0;

-- asiento 37369166 | 2025-09-24 | Carga de: FACTURAS A Numero: 15016-00003618 | dif -122,926.56 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125051) AND Eliminado=0;

-- asiento 37369193 | 2025-09-26 | Carga de: FACTURAS A Numero: 00002-00001729 | dif -368,173.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125122) AND Eliminado=0;

-- asiento 37369194 | 2025-09-26 | Carga de: FACTURAS A Numero: 00002-00001729 | dif -368,173.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125125) AND Eliminado=0;

-- asiento 37369313 | 2025-09-26 | Carga de: FACTURAS A Numero: 00002-00001729 | dif -368,173.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125470) AND Eliminado=0;

-- asiento 37369277 | 2025-10-01 | Carga de: FACTURAS A Numero: 00001-00044670 | dif -70,800.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125367) AND Eliminado=0;

-- asiento 37369390 | 2025-10-01 | Carga de: FACTURAS C Numero: 00002-00000024 | dif -234,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (125709) AND Eliminado=0;

-- asiento 37369540 | 2025-10-06 | Carga de: FACTURAS A Numero: 00002-00000025 | dif -187,050.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126129) AND Eliminado=0;

-- asiento 37369549 | 2025-10-06 | Carga de: FACTURAS A Numero: 00003-00000051 | dif -124,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126152) AND Eliminado=0;

-- asiento 37369552 | 2025-10-06 | Carga de: FACTURAS A Numero: 00002-00000025 | dif -187,050.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126158) AND Eliminado=0;

-- asiento 37369541 | 2025-10-13 | Carga de: FACTURAS A Numero: 00002-00000026 | dif -145,200.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126131) AND Eliminado=0;

-- asiento 37369573 | 2025-10-22 | Carga de: FACTURAS A Numero: 00006-00002373 | dif -189,350.48 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126215) AND Eliminado=0;

-- asiento 37369726 | 2025-10-29 | Carga de: FACTURAS A Numero: 00004-00048885 | dif -77,284.04 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126662) AND Eliminado=0;

-- asiento 37369735 | 2025-10-30 | Carga de: FACTURAS A Numero: 00005-00117956 | dif -28,899.76 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126689) AND Eliminado=0;

-- asiento 37369813 | 2025-11-03 | Carga de: FACTURAS A Numero: 00003-00049301 | dif -98,394.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (126901) AND Eliminado=0;

-- asiento 37369915 | 2025-11-07 | Carga de: FACTURAS A Numero: 00002-00000953 | dif -521,399.50 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (127200) AND Eliminado=0;

-- asiento 37370126 | 2025-11-20 | Carga de: FACTURAS A Numero: 00008-00379786 | dif -134,207.82 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (127825) AND Eliminado=0;

-- asiento 37370097 | 2025-11-28 | Carga de: FACTURAS A Numero: 00002-00000955 | dif -521,400.71 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (127724) AND Eliminado=0;

-- asiento 37370242 | 2025-12-08 | Carga de: FACTURAS C Numero: 00003-00000058 | dif -240,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (128119) AND Eliminado=0;

-- asiento 37370326 | 2025-12-15 | Carga de: FACTURAS A Numero: 00014-00004061 | dif -73,540.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (128359) AND Eliminado=0;

-- asiento 37370427 | 2025-12-15 | Carga de: FACTURAS A Numero: 00141-00008574 | dif -734,998.95 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (128642) AND Eliminado=0;

-- asiento 37370308 | 2025-12-16 | Carga de: FACTURAS A Numero: 00002-00000959 | dif -726,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (128306) AND Eliminado=0;

-- asiento 37370476 | 2025-12-22 | Carga de: FACTURAS A Numero: 00001-00000057 | dif -349,350.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (128780) AND Eliminado=0;

-- asiento 37370953 | 2026-01-11 | Carga de: FACTURAS A Numero: 00002-00000039 | dif -125,100.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (130360) AND Eliminado=0;

-- asiento 37370968 | 2026-01-21 | Carga de: FACTURAS A Numero: 00004-01751146 | dif -192,708.95 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (130394) AND Eliminado=0;

-- asiento 37370969 | 2026-01-23 | Carga de: FACTURAS A Numero: 02000-00075899 | dif -170,499.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (130397) AND Eliminado=0;

-- asiento 37371260 | 2026-01-29 | Carga de: FACTURAS C Numero: 00003-00000066 | dif -297,750.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131282) AND Eliminado=0;

-- asiento 37371141 | 2026-01-30 | Carga de: FACTURAS A Numero: 00003-00011111 | dif -80,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (130931) AND Eliminado=0;

-- asiento 37371372 | 2026-02-02 | Carga de: FACTURAS A Numero: 00002-00000042 | dif -327,200.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131595) AND Eliminado=0;

-- asiento 37371273 | 2026-02-04 | Carga de: FACTURAS A Numero: 00010-00000074 | dif -30,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131317) AND Eliminado=0;

-- asiento 37371278 | 2026-02-06 | Carga de: FACTURAS A Numero: 00002-00000966 | dif -645,000.18 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131328) AND Eliminado=0;

-- asiento 37371329 | 2026-02-13 | Carga de: FACTURAS A Numero: 00005-00000216 | dif -185,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131467) AND Eliminado=0;

-- asiento 37371390 | 2026-02-16 | Carga de: FACTURAS C Numero: 00001-00000065 | dif -358,700.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131641) AND Eliminado=0;

-- asiento 37371504 | 2026-02-27 | Carga de: FACTURAS A Numero: 00001-00000047 | dif -161,250.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (131974) AND Eliminado=0;

-- asiento 37371612 | 2026-02-28 | Carga de: FACTURAS A Numero: 00600-00852502 | dif -370,441.50 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (132282) AND Eliminado=0;

-- asiento 37371851 | 2026-03-05 | Carga de: FACTURAS A Numero: 00005-00035100 | dif -2,076,206.40 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (132894) AND Eliminado=0;

-- asiento 37371835 | 2026-03-30 | Carga de: FACTURAS A Numero: 00014-00139027 | dif -91,748.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (132853) AND Eliminado=0;

-- asiento 37372290 | 2026-04-01 | Carga de: FACTURAS A Numero: 00840-00073952 | dif -11,818.10 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134145) AND Eliminado=0;

-- asiento 37372323 | 2026-04-10 | Carga de: FACTURAS A Numero: 00008-00015802 | dif -3,513,483.24 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134217) AND Eliminado=0;

-- asiento 37372326 | 2026-04-10 | Carga de: FACTURAS C Numero: 00001-00003007 | dif -50,000.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134226) AND Eliminado=0;

-- asiento 37372306 | 2026-04-16 | Carga de: FACTURAS C Numero: 00001-00000077 | dif -259,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134183) AND Eliminado=0;

-- asiento 37372352 | 2026-04-16 | Carga de: FACTURAS A Numero: 00001-00000450 | dif -1,090,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134300) AND Eliminado=0;

-- asiento 37372362 | 2026-04-20 | Carga de: FACTURAS A Numero: 00002-00001626 | dif -372,377.50 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134324) AND Eliminado=0;

-- asiento 37372377 | 2026-04-24 | Carga de: FACTURAS A Numero: 00002-00012511 | dif -82,191.67 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134363) AND Eliminado=0;

-- asiento 37372372 | 2026-04-29 | Carga de: FACTURAS C Numero: 00002-00000498 | dif -408,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134350) AND Eliminado=0;

-- asiento 37372373 | 2026-04-30 | Carga de: FACTURAS A Numero: 00152-00830868 | dif -866,455.52 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134352) AND Eliminado=0;

-- asiento 37372714 | 2026-05-03 | Carga de: FACTURAS A Numero: 00001-00064937 | dif -65,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (135415) AND Eliminado=0;

-- asiento 37372284 | 2026-05-04 | Carga de: FACTURAS A Numero: 00002-00014448 | dif -42,930.54 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134127) AND Eliminado=0;

-- asiento 37372697 | 2026-05-11 | Carga de: FACTURAS A Numero: 00003-00000006 | dif -1,125,300.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (135338) AND Eliminado=0;

-- asiento 37372556 | 2026-05-20 | Carga de: FACTURAS C Numero: 00001-00000007 | dif -470,935.20 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (134845) AND Eliminado=0;

-- asiento 37372957 | 2026-05-21 | Carga de: FACTURAS A Numero: 00001-00003192 | dif -50,000.02 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (136160) AND Eliminado=0;

-- asiento 37372937 | 2026-05-26 | Carga de: FACTURAS A Numero: 00002-00025407 | dif -1,376,883.20 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (136103) AND Eliminado=0;

-- asiento 37372940 | 2026-05-30 | Carga de: FACTURAS A Numero: 00045-00008834 | dif -1,870,867.87 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (136112) AND Eliminado=0;

-- asiento 37372909 | 2026-06-01 | Carga de: FACTURAS C Numero: 00002-00000984 | dif -812,001.96 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (136017) AND Eliminado=0;

-- asiento 37373430 | 2026-06-05 | Carga de: FACTURAS A Numero: 00005-00039580 | dif -510,620.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137514) AND Eliminado=0;

-- asiento 37373399 | 2026-06-10 | Carga de: FACTURAS A Numero: 00007-00004882 | dif -5,336,402.50 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137434) AND Eliminado=0;

-- asiento 37373400 | 2026-06-10 | Carga de: FACTURAS A Numero: 00007-00004882 | dif -5,336,402.50 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137437) AND Eliminado=0;

-- asiento 37373297 | 2026-06-11 | Carga de: FACTURAS A Numero: 00002-00000266 | dif -60,000.07 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137113) AND Eliminado=0;

-- asiento 37373513 | 2026-06-11 | Carga de: FACTURAS C Numero: 00002-00000266 | dif -60,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137760) AND Eliminado=0;

-- asiento 37373521 | 2026-06-16 | Carga de: FACTURAS A Numero: 00008-00400693 | dif -754,812.88 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137783) AND Eliminado=0;

-- asiento 37373499 | 2026-06-29 | Carga de: FACTURAS A Numero: 00002-00026372 | dif -497,310.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137717) AND Eliminado=0;

-- asiento 37373500 | 2026-06-29 | Carga de: FACTURAS A Numero: 00002-00026359 | dif -397,848.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137720) AND Eliminado=0;

-- asiento 37373487 | 2026-07-01 | Carga de: FACTURAS A Numero: 01340-00031092 | dif -265,013.42 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (137682) AND Eliminado=0;

-- asiento 37374014 | 2026-07-03 | Carga de: FACTURAS A Numero: 00003-00066413 | dif -65,000.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (139192) AND Eliminado=0;

-- asiento 37373657 | 2026-07-13 | Carga de: FACTURAS A Numero: 00004-00000105 | dif -31,999.66 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (138166) AND Eliminado=0;

-- asiento 37373856 | 2026-07-14 | Carga de: FACTURAS C Numero: 00002-00000020 | dif -94,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (138741) AND Eliminado=0;

-- asiento 37373714 | 2026-07-15 | Carga de: FACTURAS C Numero: 00002-00000013 | dif -240,000.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (138347) AND Eliminado=0;

-- asiento 37374016 | 2026-07-15 | Carga de: FACTURAS A Numero: 00001-00001104 | dif -4,372,577.00 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (139197) AND Eliminado=0;

-- asiento 37374080 | 2026-07-21 | Carga de: FACTURAS A Numero: 00033-00039007 | dif -31,833.05 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (139410) AND Eliminado=0;

-- asiento 37374002 | 2026-07-31 | Carga de: FACTURAS C Numero: 00001-00000092 | dif -484,500.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (139158) AND Eliminado=0;

-- asiento 37374276 | 2026-08-29 | Carga de: FACTURAS B Numero: 00468-00707279 | dif -20,625.01 | ACREEDORES
UPDATE Tesoreria SET Eliminado=1, InfoABM=CONCAT(IFNULL(InfoABM,''),' huerfano de baja de compra, limpieza sist ',DATE_FORMAT(NOW(),'%d-%m-%Y %H:%i'))
 WHERE id IN (139902) AND Eliminado=0;

-- ============ VERIFICACION ============
-- descuadre Tesoreria por mes (debe quedar ~0 de 2025-07 en adelante)
SELECT LEFT(Fecha,7) ym, ROUND(SUM(COALESCE(Debe,0))-SUM(COALESCE(Haber,0)),2) dif
FROM Tesoreria WHERE Eliminado=0 AND COALESCE(Pendiente,0)=0 AND Fecha>='2025-07-01' AND Fecha<'2026-10-01'
GROUP BY ym ORDER BY ym;

-- >>> si los meses quedan ~0:  COMMIT;   si no:  ROLLBACK;
