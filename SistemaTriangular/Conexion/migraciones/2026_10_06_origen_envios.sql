-- =====================================================================
-- 2026-10-06  Origen de cada envío (PreVenta.Origen y TransClientes.Origen)
-- =====================================================================
-- Hasta ahora el origen de un envío había que deducirlo: PreVenta.TipoDeComprobante mezcla
-- canales ("SOLICITUD WEB" lo graban Plataforma y el importador de Dinter; "Tarifa X | Y" sale
-- de la API) y TransClientes.TipoDeComprobante es siempre 'Remito'. Desde ahora cada alta graba
-- su origen, y al aceptar la Preventa se copia a TransClientes.
--
-- Valores:
--   API_MELI            Mercado Libre (webhook / integración)
--   API_TIENDANUBE      Tienda Nube (webhook)
--   API                 clientes que mandan por la API de api.caddy.com.ar
--   EXCEL_PLATAFORMA    Excel que sube el cliente en Plataforma (entra por la API)
--   EXCEL_SISTEMA       Excel que sube el operador en sistema > Importaciones de Plataforma
--   PLATAFORMA          envío cargado a mano por el cliente en Plataforma
--   IMPORTACION_DINTER  importador de recorridos de Dinter (sistema > Importar)
--   VENTA_MANUAL        venta cargada por el operador en sistema (ConfirmarVenta)
--   COLECTA             retiro "padre" de una colecta (GUIA DE CARGA -> depósito)
--   PAGO                recibos de pago (TransClientes 'Recibo de Pago')
--
-- ORDEN: correr esto en producción (dinter6_triangular) Y en sandbox (dinter6_triangularcopia)
-- ANTES de pushear el código que graba Origen (si no, los INSERT fallan por columna inexistente).
-- Los pasos 3 y 4 (histórico) son idempotentes: solo tocan filas con Origen NULL.
-- =====================================================================

-- 1) Columnas
ALTER TABLE PreVenta      ADD COLUMN Origen VARCHAR(30) NULL DEFAULT NULL, ADD INDEX idx_origen (Origen);
ALTER TABLE TransClientes ADD COLUMN Origen VARCHAR(30) NULL DEFAULT NULL, ADD INDEX idx_origen (Origen);

-- 2) Histórico de PreVenta (deducido de TipoDeComprobante).
--    Dinter: "SOLICITUD WEB" del cliente 36 cargado por operadores (el importador); lo que cargó
--    gente de Dinter (@dintersa.com.ar) desde Plataforma queda como PLATAFORMA.
--    "Tarifa ..." histórico: no se puede separar API de Excel, queda API.
UPDATE PreVenta SET Origen = CASE
    WHEN TipoDeComprobante = 'API_MELI' THEN 'API_MELI'
    WHEN TipoDeComprobante = 'API_TIENDANUBE' THEN 'API_TIENDANUBE'
    WHEN TipoDeComprobante = 'SOLICITUD WEB' AND NCliente = '36'
         AND IFNULL(Usuario, '') NOT LIKE '%@dintersa.com.ar' THEN 'IMPORTACION_DINTER'
    WHEN TipoDeComprobante = 'SOLICITUD WEB' THEN 'PLATAFORMA'
    WHEN TipoDeComprobante LIKE 'Tarifa %' OR TipoDeComprobante IN ('TARIFA FLEX', 'SOLICITUD API') THEN 'API'
    ELSE NULL END
 WHERE Origen IS NULL;

-- 3) Histórico de TransClientes
--    a) los que vinieron de Preventa
UPDATE TransClientes t
  JOIN PreVenta p ON p.CodigoSeguimiento = t.CodigoSeguimiento AND p.CodigoSeguimiento <> '' AND p.Origen IS NOT NULL
   SET t.Origen = p.Origen
 WHERE t.Origen IS NULL AND t.TipoDeComprobante = 'Remito';
--    b) colectas (retiro padre) y pagos
UPDATE TransClientes SET Origen = 'COLECTA' WHERE Origen IS NULL AND TipoDeComprobante = 'GUIA DE CARGA';
UPDATE TransClientes SET Origen = 'PAGO'    WHERE Origen IS NULL AND TipoDeComprobante = 'Recibo de Pago';
--    c) el resto de los remitos se cargaron a mano en sistema
UPDATE TransClientes SET Origen = 'VENTA_MANUAL' WHERE Origen IS NULL AND TipoDeComprobante = 'Remito';

-- 4) Control
-- SELECT Origen, COUNT(*) FROM TransClientes WHERE Fecha >= CURDATE() - INTERVAL 30 DAY GROUP BY Origen;
