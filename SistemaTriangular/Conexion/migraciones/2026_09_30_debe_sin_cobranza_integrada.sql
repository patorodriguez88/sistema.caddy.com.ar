-- =====================================================================
-- 2026-09-30  TransClientes.Debe sin la Cobranza Integrada (clientes "no factura")
-- =====================================================================
-- Contexto (tarea de Agustina "Corregir suma de cobranza integrada en facturación"):
--  * A los clientes con Clientes.CobranzaIntegradaNoFactura=1 (IGALFER) la línea de
--    COBRANZA INTEGRADA se graba con Ventas.not_invoice=1 y no se tiene que facturar.
--  * El arreglo del 17/9 corrigió el recálculo del Debe al modificar servicios, pero no
--    el alta desde Preventa (Ventas/AgregarRepoVentaWeb.php), que sumaba todas las
--    líneas. Desde el 18/9 cada servicio nuevo de IGALFER quedó con la integrada sumada
--    al importe a facturar (ej. UPCUFOLE7: 15.732,87 + 4.878,40 = 20.611,27).
--    El código ya quedó arreglado.
--
-- Qué hace: deja el Debe = suma de las líneas que sí se facturan, solo en servicios
-- SIN FACTURAR de esos clientes cuyo Debe es exactamente tarifa + integrada (no toca
-- los que alguien ya corrigió a mano). Al 30/9: 303 servicios de IGALFER, $1.247.980,56.
-- Idempotente: si se vuelve a correr no cambia nada.
-- =====================================================================

-- 0) PREVIEW (opcional)
-- SELECT t.id, t.CodigoSeguimiento, t.Fecha, t.Debe AS debe_actual, v.serv AS debe_correcto, v.fee AS integrada
-- FROM TransClientes t
-- JOIN Clientes c ON c.id = t.IngBrutosOrigen AND c.CobranzaIntegradaNoFactura = 1
-- JOIN (SELECT NumPedido, SUM(IF(IFNULL(not_invoice,0)=1, Total, 0)) AS fee,
--              SUM(IF(IFNULL(not_invoice,0)=1, 0, Total)) AS serv
--       FROM Ventas WHERE Eliminado = 0 GROUP BY NumPedido) v ON v.NumPedido = t.CodigoSeguimiento
-- WHERE t.Eliminado = 0 AND t.Facturado = 0 AND v.fee > 0 AND ABS(t.Debe - v.serv - v.fee) < 0.02
-- ORDER BY t.Fecha;

UPDATE TransClientes t
JOIN Clientes c ON c.id = t.IngBrutosOrigen AND c.CobranzaIntegradaNoFactura = 1
JOIN (SELECT NumPedido, SUM(IF(IFNULL(not_invoice,0)=1, Total, 0)) AS fee,
             SUM(IF(IFNULL(not_invoice,0)=1, 0, Total)) AS serv
      FROM Ventas WHERE Eliminado = 0 GROUP BY NumPedido) v ON v.NumPedido = t.CodigoSeguimiento
SET t.Debe = ROUND(v.serv, 2)
WHERE t.Eliminado = 0 AND t.Facturado = 0 AND v.fee > 0 AND ABS(t.Debe - v.serv - v.fee) < 0.02;

-- Control: tiene que dar 0
SELECT COUNT(*) AS quedan_con_integrada
FROM TransClientes t
JOIN Clientes c ON c.id = t.IngBrutosOrigen AND c.CobranzaIntegradaNoFactura = 1
JOIN (SELECT NumPedido, SUM(IF(IFNULL(not_invoice,0)=1, Total, 0)) AS fee,
             SUM(IF(IFNULL(not_invoice,0)=1, 0, Total)) AS serv
      FROM Ventas WHERE Eliminado = 0 GROUP BY NumPedido) v ON v.NumPedido = t.CodigoSeguimiento
WHERE t.Eliminado = 0 AND t.Facturado = 0 AND v.fee > 0 AND ABS(t.Debe - v.serv - v.fee) < 0.02;
