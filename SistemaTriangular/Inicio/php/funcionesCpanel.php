<?php

include_once "../../Conexion/Conexioni.php";
include_once __DIR__ . "/../../Funciones/Funciones.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['AgregarNotas'])) {

  $Notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
  $id    = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($id <= 0) {
    echo json_encode(array('success' => 0, 'error' => 'ID inválido'));
    exit;
  }

  $stmt = $mysqli->prepare("UPDATE TransClientes SET Notas=? WHERE id=? LIMIT 1");
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("si", $Notas, $id);

  if ($stmt->execute()) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0, 'error' => $stmt->error));
  }

  $stmt->close();
  exit;
}

if (isset($_POST['VerNotas'])) {

  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($id <= 0) {
    echo json_encode(array('success' => 0, 'error' => 'ID inválido'));
    exit;
  }

  $stmt = $mysqli->prepare("SELECT Notas FROM TransClientes WHERE id=? LIMIT 1");
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $dato = $res ? $res->fetch_array(MYSQLI_ASSOC) : null;
  $stmt->close();

  echo json_encode(array(
    'success' => 1,
    'notas'   => isset($dato['Notas']) ? $dato['Notas'] : ''
  ));
  exit;
}

if (isset($_POST['VaciarRecorrido'])) {

  $recorrido = isset($_POST['Recorrido']) ? trim($_POST['Recorrido']) : '';

  if ($recorrido === '') {
    echo json_encode(array('success' => 0, 'error' => 'Recorrido inválido'));
    exit;
  }

  $sql = "SELECT CodigoSeguimiento
            FROM TransClientes
            WHERE Recorrido=? AND Eliminado=0 AND Entregado=0";

  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    exit;
  }

  $stmt->bind_param("s", $recorrido);
  $stmt->execute();
  $Respuesta = $stmt->get_result();

  while ($row = $Respuesta->fetch_array(MYSQLI_ASSOC)) {
    $codigoSeg = isset($row['CodigoSeguimiento']) ? $row['CodigoSeguimiento'] : '';

    if ($codigoSeg !== '') {
      // Estado_id 6 = "Cargado en Hoja de Ruta" (mismo motivo que este bloque registraba a mano antes).
      cambiarRecorrido($mysqli, $codigoSeg, '80', 6);
    }
  }

  $stmt->close();

  echo json_encode(array('success' => 1));
  exit;
}

if (isset($_POST['DashboardOperativo'])) {
  // Indicadores del Panel de Control, calculados en vivo (antes devolvía números fijos de ejemplo).
  // Tipo de envío (definido con Patricio, 2026-09-29): Simples = Flex 0; Flex = Flex 1 (en el día);
  // MELI = los Flex con número de envío de Mercado Libre (shipments_id, o en CodigoProveedor en las
  // colectas Flex: 11 dígitos que empiezan con 4). MELI es un subconjunto de Flex.
  // TN = pedidos de Tienda Nube (TransClientes.Origen, ver migraciones/2026_10_06_origen_envios.sql).
  // Guardan el nº de orden de TN en shipments_id (10 dígitos), por eso MELI se reconoce por el formato
  // del número de ML y no por shipments_id > 0. MELI no usa Origen: también hay envíos de ML que
  // entran por la API o a mano (colectas Flex), y esos también son MELI.
  // Las colectas "padre" (retiro -> depósito Wepoint, idClienteDestino 18587) no son envíos a
  // clientes: se excluyen de todo y se cuentan aparte en "colectas".
  $DEPOSITO = 18587;
  $uno = function (string $sql) use ($mysqli): array {
    $r = $mysqli->query($sql);
    return ($r && ($f = $r->fetch_assoc())) ? $f : [];
  };
  $esMeli = "(t.shipments_id REGEXP '^4[0-9]{10}$' OR t.CodigoProveedor REGEXP '^4[0-9]{10}$')";
  $esTn = "(t.Origen = 'API_TIENDANUBE')";

  // Paradas abiertas (sin el depósito, recorrido 80), una por código
  $pendientesSql = "
    SELECT h.Seguimiento,
           MAX(l.Recorrido IS NOT NULL) AS en_ruta,
           MIN(t.Fecha) AS fecha,
           MAX(t.Flex = 1) AS flex,
           MAX(t.Flex = 1 AND $esMeli) AS meli,
           MAX($esTn) AS tn
      FROM HojaDeRuta h
      JOIN TransClientes t ON t.CodigoSeguimiento = h.Seguimiento AND t.Eliminado = 0
                          AND t.Entregado = 0 AND t.Devuelto = 0 AND t.idClienteDestino <> $DEPOSITO
      LEFT JOIN (SELECT DISTINCT Recorrido FROM Logistica WHERE Estado = 'Cargada' AND Eliminado = 0) l
             ON l.Recorrido = h.Recorrido
     WHERE h.Eliminado = 0 AND h.Estado = 'Abierto' AND h.Recorrido <> '80'
     GROUP BY h.Seguimiento";
  $pend = $uno("
    SELECT COALESCE(SUM(en_ruta = 1), 0) AS en_ruta,
           COALESCE(SUM(en_ruta = 1 AND flex = 0), 0) AS simples,
           COALESCE(SUM(en_ruta = 1 AND flex = 1), 0) AS flex,
           COALESCE(SUM(en_ruta = 1 AND meli = 1), 0) AS meli,
           COALESCE(SUM(en_ruta = 1 AND tn = 1 AND flex = 0), 0) AS tn_simples,
           COALESCE(SUM(en_ruta = 1 AND tn = 1 AND flex = 1), 0) AS tn_flex,
           COALESCE(SUM(en_ruta = 0 AND fecha >= CURDATE() - INTERVAL 30 DAY), 0) AS sin_salir,
           COALESCE(SUM(en_ruta = 0 AND fecha <  CURDATE() - INTERVAL 30 DAY), 0) AS atrasados
      FROM ($pendientesSql) p");

  $recorridos = $uno("SELECT COUNT(DISTINCT Recorrido) AS n FROM Logistica WHERE Estado = 'Cargada' AND Eliminado = 0");

  // Entregados hoy (a clientes) y ayer hasta la misma hora.
  // "cerrada" = se entregó en una salida que ya volvió (Logistica Cerrada, ej. un reintento de la
  // mañana): operaciones cuenta lo que está en las camionetas que siguen afuera, no esos.
  $entregadosSql = function (string $cuando) use ($DEPOSITO, $esMeli): string {
    return "
      SELECT s.CodigoSeguimiento, MAX(t.Flex = 1) AS flex, MAX(t.Flex = 1 AND $esMeli) AS meli,
             MAX(l.Estado = 'Cerrada') AS cerrada
        FROM Seguimiento s
        JOIN TransClientes t ON t.CodigoSeguimiento = s.CodigoSeguimiento AND t.Eliminado = 0
                            AND t.idClienteDestino <> $DEPOSITO
        LEFT JOIN Logistica l ON l.NumerodeOrden = s.NumerodeOrden AND s.NumerodeOrden > 0 AND l.Eliminado = 0
       WHERE $cuando AND s.Entregado = 1 AND (s.Eliminado IS NULL OR s.Eliminado = 0)
       GROUP BY s.CodigoSeguimiento";
  };
  $ent = $uno("
    SELECT COUNT(*) AS total, COALESCE(SUM(flex = 0), 0) AS simples,
           COALESCE(SUM(flex = 1), 0) AS flex, COALESCE(SUM(meli = 1), 0) AS meli,
           COALESCE(SUM(flex = 0 AND cerrada = 1), 0) AS simples_cerrada,
           COALESCE(SUM(flex = 1 AND cerrada = 1), 0) AS flex_cerrada
      FROM (" . $entregadosSql("s.Fecha = CURDATE()") . ") e");
  $entAyer = $uno("SELECT COUNT(*) AS total FROM (" . $entregadosSql("s.Fecha = CURDATE() - INTERVAL 1 DAY AND s.Hora <= CURTIME()") . ") e");
  $hoy = (int)($ent['total'] ?? 0);
  $ayer = (int)($entAyer['total'] ?? 0);
  $variacion = $ayer > 0 ? (int)round(($hoy - $ayer) * 100 / $ayer) : null;

  // Incidencias de hoy
  $inc = $uno("
    SELECT COUNT(DISTINCT CASE WHEN Estado = 'No se pudo entregar' THEN CodigoSeguimiento END) AS no_entregados,
           COUNT(DISTINCT CASE WHEN Estado = 'Rechazado' THEN CodigoSeguimiento END) AS rechazados,
           COUNT(DISTINCT CASE WHEN Estado = 'No se Pudo Retirar' THEN CodigoSeguimiento END) AS no_retirados
      FROM Seguimiento
     WHERE Fecha = CURDATE() AND (Eliminado IS NULL OR Eliminado = 0)
       AND Estado IN ('No se pudo entregar', 'Rechazado', 'No se Pudo Retirar')");

  // Colectas de hoy: estado del retiro (el "padre") y bultos escaneados en el cliente
  $col = $uno("
    SELECT COUNT(*) AS total,
           COALESCE(SUM(c.Cantidad), 0) AS bultos_declarados,
           COALESCE(SUM(COALESCE(p.Retirado, 0) = 0 AND COALESCE(p.Entregado, 0) = 0), 0) AS a_retirar,
           COALESCE(SUM(p.Retirado = 1 AND p.Entregado = 0), 0) AS en_camino,
           COALESCE(SUM(p.Entregado = 1), 0) AS en_deposito
      FROM Colecta c
      LEFT JOIN TransClientes p ON p.CodigoSeguimiento = c.CodigoSeguimiento AND p.Eliminado = 0
     WHERE c.Fecha = CURDATE() AND c.Eliminado = 0");
  $colBultos = $uno("
    SELECT COUNT(DISTINCT k.CodigoSeguimiento) AS bultos,
           COUNT(DISTINCT CASE WHEN s.status = 'pickup_scanned' THEN k.CodigoSeguimiento END) AS escaneados
      FROM Colecta c
      JOIN TransClientes k ON k.idColecta = c.id AND k.Eliminado = 0 AND k.idClienteDestino <> $DEPOSITO
      LEFT JOIN Seguimiento s ON s.CodigoSeguimiento = k.CodigoSeguimiento AND s.status = 'pickup_scanned'
                             AND (s.Eliminado IS NULL OR s.Eliminado = 0)
     WHERE c.Fecha = CURDATE() AND c.Eliminado = 0");

  $noEnt = (int)($inc['no_entregados'] ?? 0);
  $rech = (int)($inc['rechazados'] ?? 0);
  $noRet = (int)($inc['no_retirados'] ?? 0);
  $i = fn($a, $k) => (int)($a[$k] ?? 0);
  echo json_encode(array(
    'success' => 1,
    // En vivo (hoy)
    'en_ruta_total' => $i($pend, 'en_ruta'),
    'recorridos_activos' => $i($recorridos, 'n'),
    'entregados_total' => $hoy,
    'entregados_ayer' => $ayer,
    'entregados_variacion' => $variacion,
    'incidencias_total' => $noEnt + $rech + $noRet,
    'no_entregados' => $noEnt,
    'rechazados' => $rech,
    'no_retirados' => $noRet,
    // Operativo del día por tipo (MELI está incluido en Flex)
    'simples_entregados' => $i($ent, 'simples'),
    'simples_pendientes' => $i($pend, 'simples'),
    'flex_entregados' => $i($ent, 'flex'),
    'flex_pendientes' => $i($pend, 'flex'),
    'meli_entregados' => $i($ent, 'meli'),
    'meli_pendientes' => $i($pend, 'meli'),
    'tn_simples_pendientes' => $i($pend, 'tn_simples'),
    'tn_flex_pendientes' => $i($pend, 'tn_flex'),
    // Entregados hoy en salidas que ya volvieron (no están en las camionetas que siguen afuera)
    'simples_entregados_cerrada' => $i($ent, 'simples_cerrada'),
    'flex_entregados_cerrada' => $i($ent, 'flex_cerrada'),
    // Colectas de hoy
    'colectas_total' => $i($col, 'total'),
    'colectas_a_retirar' => $i($col, 'a_retirar'),
    'colectas_en_camino' => $i($col, 'en_camino'),
    'colectas_en_deposito' => $i($col, 'en_deposito'),
    'colectas_bultos' => $i($colBultos, 'bultos'),
    'colectas_escaneados' => $i($colBultos, 'escaneados'),
    // Todavía no salieron
    'pendientes_sin_salir' => $i($pend, 'sin_salir'),
    'pendientes_atrasados' => $i($pend, 'atrasados'),
    'hora' => date('H:i'),
  ));
  exit;
}

echo json_encode(array(
  'success' => 0,
  'error' => 'Acción no válida'
));
exit;
