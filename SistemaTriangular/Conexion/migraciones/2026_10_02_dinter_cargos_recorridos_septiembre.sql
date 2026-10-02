-- =====================================================================
-- 2026-10-02  DINTER S.A. CBA: cargar como cargo los recorridos de septiembre pendientes
-- =====================================================================
-- Contexto (Agustina): en septiembre se hicieron 76 recorridos para Dinter (cliente 36), solo
-- 24 estaban cargados en la cuenta corriente. "Ingresar Recorridos" no listaba los pendientes
-- (arreglado en b7268875), y además cargaba con el precio ACTUAL de la lista, no el del día
-- del servicio. Esto carga los pendientes de una vez, igual que el carrito del modal
-- (mismos campos y texto), pero con el precio guardado en cada orden (Logistica.PrecioRecorrido;
-- si no tuviera, el de la lista).
--
-- Quedan AFUERA, para que Agustina decida si son duplicados: órdenes 17149 y 17285.
-- Al 02/10: 50 cargos, $ 16.295.321,54 (con el precio actual habrían sido $ 16.565.823,96).
-- Idempotente: no vuelve a cargar una orden que ya tenga cargo.
-- =====================================================================

-- 0) PREVIEW (opcional)
-- SELECT l.Fecha, l.NumerodeOrden, l.Recorrido, IF(l.PrecioRecorrido > 0, l.PrecioRecorrido, p.PrecioVenta) AS Debe
-- FROM Logistica l JOIN Recorridos r ON r.Numero = l.Recorrido JOIN Productos p ON p.Codigo = r.CodigoProductos
-- WHERE r.Cliente = '36' AND l.Eliminado = 0 AND l.Facturado = 0 AND l.Fecha BETWEEN '2026-09-01' AND '2026-09-30'
--   AND l.NumerodeOrden NOT IN (17149, 17285)
--   AND NOT EXISTS (SELECT 1 FROM Ctasctes c WHERE c.idLogistica = l.id AND c.Eliminado = 0)
-- ORDER BY l.Fecha, l.NumerodeOrden;

-- Mismo sql_mode que producción (no estricto): las columnas sin valor quedan en su default
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

INSERT INTO Ctasctes (Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroVenta, Debe, Usuario, Observaciones,
                      idCliente, FacturacionxRecorrido, idLogistica)
SELECT l.Fecha, cl.nombrecliente, cl.Cuit, CONCAT('RECORRIDO ', l.Recorrido), l.NumerodeOrden,
       IF(l.PrecioRecorrido > 0, l.PrecioRecorrido, p.PrecioVenta), 'prodriguez',
       CONCAT('ORDEN N ', l.NumerodeOrden, ' RECORRIDO ', l.Recorrido), 36, 1, l.id
FROM Logistica l
JOIN Recorridos r ON r.Numero = l.Recorrido
JOIN Productos p ON p.Codigo = r.CodigoProductos
JOIN Clientes cl ON cl.id = 36
WHERE r.Cliente = '36' AND l.Eliminado = 0 AND l.Facturado = 0
  AND l.Fecha BETWEEN '2026-09-01' AND '2026-09-30'
  AND l.NumerodeOrden NOT IN (17149, 17285)
  AND NOT EXISTS (SELECT 1 FROM Ctasctes c WHERE c.idLogistica = l.id AND c.Eliminado = 0)
ORDER BY l.Fecha, l.NumerodeOrden;

-- Control: órdenes de septiembre de Dinter sin cargo. Tiene que dar 2 (las 17149 y 17285, a revisar)
SELECT COUNT(*) AS ordenes_sin_cargo, GROUP_CONCAT(l.NumerodeOrden) AS ordenes
FROM Logistica l JOIN Recorridos r ON r.Numero = l.Recorrido
WHERE r.Cliente = '36' AND l.Eliminado = 0 AND l.Fecha BETWEEN '2026-09-01' AND '2026-09-30'
  AND NOT EXISTS (SELECT 1 FROM Ctasctes c WHERE c.idLogistica = l.id AND c.Eliminado = 0);
