-- ============================================================================
-- CORRECCION Sumas y Saldos - NOTAS DE CREDITO DE PROVEEDORES  (data-fix)
-- NO es migracion de esquema. Generado 2026-09-08 desde produccion.
--
-- Contexto: admin reporta que las NC de proveedores "no se reflejan" en el
-- Balance de Sumas y Saldos.
--
-- Diagnostico:
--   * La MAYORIA de las NC SI impactan bien: cargarfactura() usa $Valor=-1 y
--     postea la NC como contra-asiento (Debe NEGATIVO en la cuenta de gasto).
--     El saldo neto por cuenta queda correcto.
--   * PERO 3 NC de 2026 quedaron con la linea de GASTO en Tesoreria con
--     Cuenta='000000000' (NombreCuenta '113700') -> en Sumas y Saldos ese
--     importe cae en "SIN CLASIFICAR" (cuenta 0) en vez de descontar la cuenta
--     de gasto real. El IvaCompras de esas NC ya fue re-imputado por la
--     migracion 2026_09_08_correccion_compras_agosto_sin_cuenta.sql, que las
--     dejo marcadas "revisar a mano". Esto lo resuelve.
--   * Ademas hay 5 filas DUPLICADAS de NC en IvaCompras (mismo proveedor + Nro),
--     sin asiento contable: no afectan Sumas y Saldos/Tesoreria pero doble/triple
--     cuentan en el Libro IVA Compras. Se anulan (Eliminado=1).
--
-- El bug de codigo que originaba el idTransProvee=0 en las NC ya esta corregido
-- en Proveedores/Procesos/php/proveedores.php::cargarfactura() (usa insert_id).
--
-- >>> REVISAR CON CONTADURIA. Correr en transaccion y verificar Sumas y Saldos
-- >>> (feb/2026 y ago/2026) ANTES de COMMIT. <<<
-- ============================================================================

START TRANSACTION;

-- ============================================================================
-- (A) Re-imputar la cuenta de GASTO en las lineas de Tesoreria de 3 NC (2026)
--     que quedaron en Cuenta 0. La cuenta sale del IvaCompras ya corregido.
-- ============================================================================

-- GASTRO-STORE SRL | NOTAS DE CREDITO A 00004-00000020 | 2026-02-10
-- asiento 37371280 | gasto -$649.383,80 -> 421200 GASTOS GENERALES
UPDATE Tesoreria SET Cuenta = 421200, NombreCuenta = 'GASTOS GENERALES'
 WHERE id = 131333 AND Cuenta = 000000000 AND NumeroAsiento = 37371280;

-- BOGADO EDITH | NOTAS DE CREDITO C 00001-00000001 | 2026-08-12
-- asiento 37374243 | gasto -$365.800,01 -> 421600 FLETES Y ENCOMIENDAS
UPDATE Tesoreria SET Cuenta = 421600, NombreCuenta = 'FLETES Y ENCOMIENDAS'
 WHERE id = 139820 AND Cuenta = 000000000 AND NumeroAsiento = 37374243;

-- Valverde Antonio | NOTAS DE CREDITO A 00002-00000054 | 2026-08-27
-- asiento 37374371 | gasto -$1.062.319,00 -> 421600 FLETES Y ENCOMIENDAS
UPDATE Tesoreria SET Cuenta = 421600, NombreCuenta = 'FLETES Y ENCOMIENDAS'
 WHERE id = 140170 AND Cuenta = 000000000 AND NumeroAsiento = 37374371;

-- (verificacion: los 3 asientos tienen que seguir cuadrando Debe = Haber)
-- SELECT NumeroAsiento, ROUND(SUM(Debe),2) sDebe, ROUND(SUM(Haber),2) sHaber
--   FROM Tesoreria WHERE NumeroAsiento IN (37371280,37374243,37374371) AND Eliminado=0
--  GROUP BY NumeroAsiento;

-- ============================================================================
-- (B) Anular filas DUPLICADAS de NC en IvaCompras (sin asiento contable).
--     Se conserva SIEMPRE la fila que tiene el asiento en Tesoreria.
-- ============================================================================

-- DARSIE Y CIA SACI | NC 00031-00003209 | cargada 3 veces (13139, 13140, 13141)
--   -> se conserva 13141 (asiento 37367908, 5 lineas en Tesoreria)
UPDATE IvaCompras SET Eliminado = 1 WHERE id IN (13139, 13140) AND Eliminado = 0;

-- ROJAS HUGO ALBERTO | NC 00001-00000002 | cargada 2 veces (13878, 13879)
--   -> se conserva 13879 (asiento 37370480). 13878 tiene ademas el Total con typo (-301350.10 vs -301350.01)
UPDATE IvaCompras SET Eliminado = 1 WHERE id = 13878 AND Eliminado = 0;

-- Transporte del Interior S.A. | NC 00001-00000023 | cargada 2 veces (14585, 14595)
--   -> se conserva 14595 (asiento 37373547)
UPDATE IvaCompras SET Eliminado = 1 WHERE id = 14585 AND Eliminado = 0;

-- VAMOS A LA CARGA | NC 00005-00001276 | cargada 2 veces (14542, 14594)
--   -> se conserva 14594 (asiento 37373546)
UPDATE IvaCompras SET Eliminado = 1 WHERE id = 14542 AND Eliminado = 0;

-- ============================================================================
-- (C) PENDIENTE - decision de contaduria (NO incluido en esta transaccion)
-- ============================================================================
-- 5 lineas de Tesoreria de 2022 con Cuenta 0, todas del proveedor
-- "HUGO FRAPPA Y RAMON MOINE SRL" (NC, importes de -$289 a -$331, ~$1.500 total,
-- ejercicios cerrados). Falta definir la cuenta de gasto de ese proveedor:
--   id 66140  2022-05-10  -$330.58   asiento 37350448
--   id 66143  2022-05-19  -$289.26   asiento 37350449
--   id 67031  2022-06-08  -$289.26   asiento 37350716
--   id 67083  2022-06-06  -$289.26   asiento 37350732
--   id 67134  2022-07-04  -$289.26   asiento 37350747
-- Cuando contaduria diga la cuenta:
--   UPDATE Tesoreria SET Cuenta = <NNN>, NombreCuenta = '<NOMBRE>'
--    WHERE id IN (66140,66143,67031,67083,67134);

-- ============================================================================
-- Verificar Sumas y Saldos feb/2026 y ago/2026 y luego:  COMMIT;
-- (o  ROLLBACK;  si no cierra)
-- ============================================================================
-- COMMIT;
