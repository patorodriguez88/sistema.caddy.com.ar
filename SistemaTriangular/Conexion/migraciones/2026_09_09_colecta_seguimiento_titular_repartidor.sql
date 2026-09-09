-- =====================================================================
-- 2026-09-09  Colecta: acreditar al repartidor los "Entregado al Cliente"
--             de colecta que quedaron con usuario de oficina
-- =====================================================================
--
-- Contexto: hasta hoy, cuando la oficina confirmaba en el sistema la entrega
-- en depósito de una colecta (Servicios > Guías "Agregar Registro", o
-- Servicios > Pendientes), la fila de Seguimiento quedaba con Usuario = el
-- operador de oficina, no el repartidor. El informe de Externos > Liquidaciones
-- filtra Seg.Usuario = <repartidor>, así que esas colectas nunca se le pagaban.
--
-- El flujo hacia adelante ya quedó arreglado (el repartidor elegido es el
-- titular; el "Entregar en depósito" de la app graba con el usuario del
-- repartidor). Esta migración corrige las filas YA cargadas de sept-2026.
--
-- Criterio: el "chofer correcto" sale de Logistica por el NumerodeOrden de la
-- HojaDeRuta de ese código (la del recorrido de retiro de la colecta).
-- Sólo toca filas donde el Usuario actual NO es ya ese chofer.
--
-- Correr en producción una sola vez. Idempotente (si se corre de nuevo no
-- vuelve a tocar nada porque ya coinciden).
-- =====================================================================

-- 1) PREVIEW (opcional, para revisar antes):
-- SELECT s.id, s.CodigoSeguimiento, s.Usuario AS actual, u.Usuario AS chofer_ok,
--        s.NumerodeOrden AS norden_actual, hh.NumerodeOrden AS norden_ok, s.Fecha
-- FROM Seguimiento s
-- JOIN TransClientes t ON t.CodigoSeguimiento = s.CodigoSeguimiento AND t.idClienteDestino = 18587
-- JOIN ( SELECT h1.Seguimiento, MAX(h1.NumerodeOrden) AS NumerodeOrden
--        FROM HojaDeRuta h1 WHERE h1.Eliminado = 0 AND h1.NumerodeOrden > 0
--        GROUP BY h1.Seguimiento ) hh ON hh.Seguimiento = s.CodigoSeguimiento
-- JOIN Logistica l ON l.NumerodeOrden = hh.NumerodeOrden AND l.Eliminado = 0
-- JOIN usuarios u ON u.id = l.idUsuarioChofer
-- WHERE s.Estado = 'Entregado al Cliente' AND s.Fecha >= '2026-09-01' AND s.Eliminado = 0
--   AND (s.Usuario IS NULL OR s.Usuario <> u.Usuario);

-- 2) CORRECCIÓN
UPDATE Seguimiento s
JOIN TransClientes t ON t.CodigoSeguimiento = s.CodigoSeguimiento AND t.idClienteDestino = 18587
JOIN ( SELECT h1.Seguimiento, MAX(h1.NumerodeOrden) AS NumerodeOrden
       FROM HojaDeRuta h1 WHERE h1.Eliminado = 0 AND h1.NumerodeOrden > 0
       GROUP BY h1.Seguimiento ) hh ON hh.Seguimiento = s.CodigoSeguimiento
JOIN Logistica l ON l.NumerodeOrden = hh.NumerodeOrden AND l.Eliminado = 0
JOIN usuarios u ON u.id = l.idUsuarioChofer
SET s.Usuario       = u.Usuario,
    s.NumerodeOrden = hh.NumerodeOrden,
    s.Observaciones = CONCAT(IFNULL(s.Observaciones, ''), ' [titular corregido a ', u.Usuario, ' - mig 2026-09-09]')
WHERE s.Estado = 'Entregado al Cliente'
  AND s.Fecha >= '2026-09-01'
  AND s.Eliminado = 0
  AND (s.Usuario IS NULL OR s.Usuario <> u.Usuario);

-- 3) Propagar el NumerodeOrden real al TransClientes del padre si estaba en 0
UPDATE TransClientes t
JOIN ( SELECT h1.Seguimiento, MAX(h1.NumerodeOrden) AS NumerodeOrden
       FROM HojaDeRuta h1 WHERE h1.Eliminado = 0 AND h1.NumerodeOrden > 0
       GROUP BY h1.Seguimiento ) hh ON hh.Seguimiento = t.CodigoSeguimiento
SET t.NumerodeOrden = hh.NumerodeOrden
WHERE t.idClienteDestino = 18587
  AND t.Eliminado = 0
  AND (t.NumerodeOrden IS NULL OR t.NumerodeOrden = 0)
  AND t.Fecha >= '2026-09-01';

-- Después de correr: regenerar el informe de Externos de las órdenes afectadas
-- para que se snapshotéen las colectas en Externos_rendicion (el informe agrega
-- las filas nuevas que ahora califican).
