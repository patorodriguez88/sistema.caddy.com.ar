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
  // Tipo de envío: Simples = Flex 0; MELI = pedidos de la integración de Mercado Libre (tienen
  // order_id); Flex = paquetes Flex que entran por colecta (Flex 1 sin order_id).
  $uno = function (string $sql) use ($mysqli): array {
    $r = $mysqli->query($sql);
    return ($r && ($f = $r->fetch_assoc())) ? $f : [];
  };
  $tipoSql = "CASE WHEN t.Flex = 0 THEN 'simples' WHEN t.order_id > 0 THEN 'meli' ELSE 'flex' END";

  // Paradas abiertas (sin el depósito, recorrido 80), una por código
  $pendientesSql = "
    SELECT h.Seguimiento,
           MAX(l.Recorrido IS NOT NULL) AS en_ruta,
           MIN(t.Fecha) AS fecha,
           MAX($tipoSql) AS tipo
      FROM HojaDeRuta h
      JOIN TransClientes t ON t.CodigoSeguimiento = h.Seguimiento AND t.Eliminado = 0
                          AND t.Entregado = 0 AND t.Devuelto = 0
      LEFT JOIN (SELECT DISTINCT Recorrido FROM Logistica WHERE Estado = 'Cargada' AND Eliminado = 0) l
             ON l.Recorrido = h.Recorrido
     WHERE h.Eliminado = 0 AND h.Estado = 'Abierto' AND h.Recorrido <> '80'
     GROUP BY h.Seguimiento";
  $pend = $uno("
    SELECT COUNT(*) AS total,
           COALESCE(SUM(en_ruta = 1), 0) AS en_ruta,
           COALESCE(SUM(en_ruta = 0 AND fecha >= CURDATE() - INTERVAL 30 DAY), 0) AS sin_salir,
           COALESCE(SUM(en_ruta = 0 AND fecha <  CURDATE() - INTERVAL 30 DAY), 0) AS atrasados
      FROM ($pendientesSql) p");

  $recorridos = $uno("SELECT COUNT(DISTINCT Recorrido) AS n FROM Logistica WHERE Estado = 'Cargada' AND Eliminado = 0");

  // Entregados hoy vs ayer hasta la misma hora
  $entregados = $uno("
    SELECT COUNT(DISTINCT CASE WHEN Fecha = CURDATE() THEN CodigoSeguimiento END) AS hoy,
           COUNT(DISTINCT CASE WHEN Fecha = CURDATE() - INTERVAL 1 DAY AND Hora <= CURTIME() THEN CodigoSeguimiento END) AS ayer
      FROM Seguimiento
     WHERE Fecha >= CURDATE() - INTERVAL 1 DAY AND Entregado = 1
       AND (Eliminado IS NULL OR Eliminado = 0)");
  $hoy = (int)($entregados['hoy'] ?? 0);
  $ayer = (int)($entregados['ayer'] ?? 0);
  $variacion = $ayer > 0 ? (int)round(($hoy - $ayer) * 100 / $ayer) : null;

  // Incidencias de hoy
  $inc = $uno("
    SELECT COUNT(DISTINCT CASE WHEN Estado = 'No se pudo entregar' THEN CodigoSeguimiento END) AS no_entregados,
           COUNT(DISTINCT CASE WHEN Estado = 'Rechazado' THEN CodigoSeguimiento END) AS rechazados,
           COUNT(DISTINCT CASE WHEN Estado = 'No se Pudo Retirar' THEN CodigoSeguimiento END) AS no_retirados
      FROM Seguimiento
     WHERE Fecha = CURDATE() AND (Eliminado IS NULL OR Eliminado = 0)
       AND Estado IN ('No se pudo entregar', 'Rechazado', 'No se Pudo Retirar')");

  // Operativo del día por tipo: lo entregado hoy + lo que sigue pendiente en recorridos que salieron
  $op = ['simples' => ['e' => 0, 'p' => 0], 'flex' => ['e' => 0, 'p' => 0], 'meli' => ['e' => 0, 'p' => 0]];
  $r = $mysqli->query("SELECT tipo, COUNT(*) AS n FROM ($pendientesSql) p WHERE en_ruta = 1 GROUP BY tipo");
  while ($r && ($f = $r->fetch_assoc())) {
    $op[$f['tipo']]['p'] = (int)$f['n'];
  }
  $r = $mysqli->query("
    SELECT tipo, COUNT(*) AS n FROM (
      SELECT s.CodigoSeguimiento, MAX($tipoSql) AS tipo
        FROM Seguimiento s
        JOIN TransClientes t ON t.CodigoSeguimiento = s.CodigoSeguimiento AND t.Eliminado = 0
       WHERE s.Fecha = CURDATE() AND s.Entregado = 1 AND (s.Eliminado IS NULL OR s.Eliminado = 0)
       GROUP BY s.CodigoSeguimiento) e
     GROUP BY tipo");
  while ($r && ($f = $r->fetch_assoc())) {
    $op[$f['tipo']]['e'] = (int)$f['n'];
  }

  $noEnt = (int)($inc['no_entregados'] ?? 0);
  $rech = (int)($inc['rechazados'] ?? 0);
  $noRet = (int)($inc['no_retirados'] ?? 0);
  echo json_encode(array(
    'success' => 1,
    'pendientes_total' => (int)($pend['en_ruta'] ?? 0) + (int)($pend['sin_salir'] ?? 0),
    'pendientes_sin_salir' => (int)($pend['sin_salir'] ?? 0),
    'pendientes_en_ruta' => (int)($pend['en_ruta'] ?? 0),
    'pendientes_atrasados' => (int)($pend['atrasados'] ?? 0),
    'en_ruta_total' => (int)($pend['en_ruta'] ?? 0),
    'recorridos_activos' => (int)($recorridos['n'] ?? 0),
    'entregados_total' => $hoy,
    'entregados_ayer' => $ayer,
    'entregados_variacion' => $variacion,
    'incidencias_total' => $noEnt + $rech + $noRet,
    'no_entregados' => $noEnt,
    'rechazados' => $rech,
    'no_retirados' => $noRet,
    'simples_total' => $op['simples']['e'] + $op['simples']['p'],
    'simples_entregados' => $op['simples']['e'],
    'simples_pendientes' => $op['simples']['p'],
    'flex_total' => $op['flex']['e'] + $op['flex']['p'],
    'flex_entregados' => $op['flex']['e'],
    'flex_pendientes' => $op['flex']['p'],
    'meli_total' => $op['meli']['e'] + $op['meli']['p'],
    'meli_entregados' => $op['meli']['e'],
    'meli_pendientes' => $op['meli']['p'],
    'hora' => date('H:i'),
  ));
  exit;
}

echo json_encode(array(
  'success' => 0,
  'error' => 'Acción no válida'
));
exit;
