-- Cargos de recorridos de octubre 2026 en la cuenta corriente (30/10). Desde el 30/10 se cargan solos
-- al cerrar la orden (Funciones/php/cargo_recorrido.php); las órdenes cerradas antes de eso quedaron
-- sin cargo. Mismo movimiento que "Ingresar Recorridos" y que la carga automática: fecha de la orden,
-- precio del día del servicio (o el de la lista), FacturacionxRecorrido = 1.
-- Solo órdenes CERRADAS de octubre sin ningún movimiento: las que siguen en curso se cargan solas al
-- cerrarse. Se puede correr dos veces (no duplica).
-- Al 30/10: 85 órdenes = Dinter S.A. CBA 72 ($ 27.559.695,59), Embotelladora del Atlántico 9
-- ($ 3.171.428,82), Crecer Río Cuarto 4 ($ 916.200,12).

INSERT INTO Ctasctes (Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroVenta, Debe, Usuario,
                      Observaciones, idCliente, FacturacionxRecorrido, idLogistica, Eliminado, Facturado)
SELECT L.Fecha, C.nombrecliente, IFNULL(C.Cuit, ''), CONCAT('RECORRIDO ', L.Recorrido), L.NumerodeOrden,
       IF(L.PrecioRecorrido > 0, L.PrecioRecorrido, P.PrecioVenta), 'prodriguez',
       CONCAT('ORDEN N ', L.NumerodeOrden, ' RECORRIDO ', L.Recorrido), R.Cliente, 1, L.id, 0, 0
FROM Logistica L
JOIN Recorridos R ON R.Numero = L.Recorrido
JOIN Productos P ON P.Codigo = R.CodigoProductos
JOIN Clientes C ON C.id = R.Cliente
WHERE L.Eliminado = 0 AND L.Estado = 'Cerrada'
  AND L.Fecha BETWEEN '2026-10-01' AND '2026-10-31'
  AND R.Cliente > 0
  AND IF(L.PrecioRecorrido > 0, L.PrecioRecorrido, P.PrecioVenta) > 0
  AND NOT EXISTS (SELECT 1 FROM Ctasctes c WHERE c.idLogistica = L.id AND IFNULL(c.Eliminado, 0) = 0)
ORDER BY L.Fecha, L.NumerodeOrden;

-- Control: tiene que dar 72 / 9 / 4.
SELECT idCliente, RazonSocial, COUNT(*) AS cargos, SUM(Debe) AS importe
FROM Ctasctes
WHERE FacturacionxRecorrido = 1 AND Eliminado = 0 AND Usuario = 'prodriguez'
  AND TipoDeComprobante LIKE 'RECORRIDO %' AND Fecha BETWEEN '2026-10-01' AND '2026-10-31'
GROUP BY idCliente, RazonSocial;
