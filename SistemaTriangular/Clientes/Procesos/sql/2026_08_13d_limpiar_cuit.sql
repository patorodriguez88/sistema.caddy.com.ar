-- Limpieza de Clientes.Cuit: espacios finales y sufijo "/N" de sucursal.
-- Fecha: 2026-08-13
-- Aplicar manualmente en sandbox/producción (ya aplicado y verificado en local).
-- No es destructiva: solo recorta espacios y saca un sufijo "/N" que no es parte
-- de un CUIT válido. No borra filas ni toca otras columnas.
--
-- Origen: detectado durante una prueba integral en localhost — TransClientes.Cuit
-- es varchar(13) (el ancho justo de un CUIT con guiones: XX-XXXXXXXX-X) y algunas
-- ventas a clientes reales fallaban porque Clientes.Cuit tenía basura agregada:
--
--   1) ~627 clientes con el CUIT correcto pero con 1-2 espacios pegados al final
--      (ej. "20-14797459-1  "). Ojo: MySQL con collation no-binaria ignora los
--      espacios finales en comparaciones ('x' = 'x  ' da TRUE), por eso el
--      chequeo de "cuántos hay" y el UPDATE usan LENGTH(), no comparación directa.
--
--   2) 10 clientes donde alguien pegó "/N" al final del CUIT para poder
--      distinguir sucursales que legalmente comparten un mismo CUIT
--      (ej. "DINTER S.A. Villa Maria" y "DINTER S.A. San Francisco" con
--      "30-71527914-9/1" y "30-71527914-9/2"). El nombre del cliente ya alcanza
--      para distinguirlas — el CUIT real es el mismo para las dos y no debería
--      llevar sufijo. Se verificó que ningún código del sistema parsea este
--      patrón "/N" (no es una convención usada en ningún lado, solo carga manual).
--
-- Para previsualizar antes de aplicar:
--   SELECT id, nombrecliente, Cuit, LENGTH(Cuit) FROM Clientes
--    WHERE Cuit IS NOT NULL AND LENGTH(Cuit) <> LENGTH(TRIM(Cuit));
--   SELECT id, nombrecliente, Cuit FROM Clientes
--    WHERE Cuit REGEXP '^[0-9]{2}-[0-9]{8}-[0-9]/[0-9]+$';

UPDATE Clientes
   SET Cuit = TRIM(Cuit)
 WHERE Cuit IS NOT NULL
   AND LENGTH(Cuit) <> LENGTH(TRIM(Cuit));

UPDATE Clientes
   SET Cuit = SUBSTRING_INDEX(Cuit, '/', 1)
 WHERE Cuit REGEXP '^[0-9]{2}-[0-9]{8}-[0-9]/[0-9]+$';

-- Verificación posterior (debería dar 0 filas):
--   SELECT id, nombrecliente, Cuit FROM Clientes
--    WHERE Cuit IS NOT NULL AND LENGTH(Cuit) <> LENGTH(TRIM(Cuit))
--       OR Cuit REGEXP '^[0-9]{2}-[0-9]{8}-[0-9]/[0-9]+$';
--
-- NO cubre (quedan afuera a propósito, requieren revisión humana - no son
-- espacios ni sufijo "/N", son datos directamente mal cargados en el campo):
--   id 11  Rodriguez Patricio        Cuit "20-27014986-11" (pisa el dígito
--          verificador con el propio id de cliente)
--   id 47  Patricio Rodriguez        Cuit "20-27014986-47" (mismo caso, es
--          un duplicado del cliente 11)
--   id 30  MARTIN RUIZ               Cuit "30-9999999-9/1" (CUIT dummy de
--          9 repetidos, con sufijo "/N" pero con 7 dígitos en vez de 8, no
--          coincide con el patrón válido)
--   id 1399, 1563, 6443, 6583, 4762, 735, 54025, 54028, 54029
--          Cuit con una dirección, un nombre de persona, o un numero
--          placeholder en vez de un CUIT real (columnas cargadas cruzadas)
