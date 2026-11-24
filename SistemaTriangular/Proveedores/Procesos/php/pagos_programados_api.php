<?php
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : '';
$fechaDesde = '2026-01-01';

function responder($success, $message = '', $data = array())
{
    echo json_encode(array(
        'success' => $success,
        'message' => $message,
        'data' => $data
    ));
    exit;
}

function n($valor)
{
    return is_numeric($valor) ? (float)$valor : 0;
}

// =====================================================================================================
// PROGRAMACIÓN DE PAGOS (rediseño 24/11/2026, pedido de Patricio)
// Cada factura impaga (desde $fechaDesde) tiene UNA fecha en el calendario:
//   - la que eligió el operador (pagos_programados_proveedores activa: PROGRAMADO / REPROGRAMADO), o
//   - si no la movió, su vencimiento: TransProveedores.FechaVencimiento, o uno estimado con los "Días de
//     pago" del proveedor (Pago_comprobantes); sábado o domingo pasa al lunes.
// Las de meses anteriores al actual van a la lista de la izquierda para reubicarlas; el resto está en
// el calendario. El importe es siempre el SALDO actual (Debe - lo aplicado en
// TransProveedores_Imputaciones, igual que la Cuenta Corriente): cuando se asocia el pago, desaparece.
// =====================================================================================================

/** Todas las facturas impagas con su fecha de calendario. */
function facturasPendientesCalendario(mysqli $mysqli, string $fechaDesde): array
{
    $resCol = $mysqli->query("SHOW COLUMNS FROM TransProveedores LIKE 'FechaVencimiento'");
    $tieneVto = $resCol && $resCol->num_rows > 0;
    $est = "DATE_ADD(TP.Fecha, INTERVAL IFNULL(PR.Pago_comprobantes, 15) DAY)";
    $est = "CASE DAYOFWEEK({$est}) WHEN 7 THEN DATE_ADD({$est}, INTERVAL 2 DAY)
            WHEN 1 THEN DATE_ADD({$est}, INTERVAL 1 DAY) ELSE {$est} END";
    $vto = $tieneVto ? "COALESCE(TP.FechaVencimiento, {$est})" : $est;
    $vtoEst = $tieneVto ? "(TP.FechaVencimiento IS NULL)" : "1";

    $sql = "
        SELECT
            TP.id, TP.Fecha, TP.RazonSocial, TP.Cuit, TP.TipoDeComprobante, TP.NumeroComprobante,
            TP.Debe, TP.Concepto, TP.Descripcion, TP.idProveedor,
            IFNULL(AP.Pagado, 0) AS Pagado,
            TP.Debe - IFNULL(AP.Pagado, 0) AS Saldo,
            {$vto} AS Vencimiento,
            {$vtoEst} AS VencimientoEstimado,
            PP.id AS idProgramacion,
            PP.fecha_promesa AS FechaProgramada,
            PP.observacion AS Observacion
        FROM TransProveedores TP
        LEFT JOIN (
            SELECT idMovimientoOrigen AS idTransProveedores, SUM(Importe) AS Pagado
            FROM TransProveedores_Imputaciones
            WHERE Eliminado = 0
            GROUP BY idMovimientoOrigen
        ) AP ON AP.idTransProveedores = TP.id
        LEFT JOIN (
            -- la programación activa más reciente de cada factura
            SELECT P1.* FROM pagos_programados_proveedores P1
            INNER JOIN (
                SELECT idTransProveedores, MAX(id) AS id FROM pagos_programados_proveedores
                WHERE eliminado = 0 AND estado IN ('PROGRAMADO','REPROGRAMADO')
                GROUP BY idTransProveedores
            ) P2 ON P2.id = P1.id
        ) PP ON PP.idTransProveedores = TP.id
        LEFT JOIN Proveedores PR ON PR.id = TP.idProveedor
        WHERE TP.Eliminado = 0
          AND TP.Debe > 0
          AND TP.Fecha >= '{$fechaDesde}'
        HAVING Saldo > 0.009
        ORDER BY TP.RazonSocial ASC
    ";
    $res = $mysqli->query($sql);
    if (!$res) responder(false, $mysqli->error);

    $out = array();
    while ($r = $res->fetch_assoc()) {
        $r['Debe'] = n($r['Debe']);
        $r['Pagado'] = n($r['Pagado']);
        $r['Saldo'] = n($r['Saldo']);
        $r['SaldoPendiente'] = $r['Saldo']; // compatibilidad con el JS de las tarjetas
        $r['VencimientoEstimado'] = (int) $r['VencimientoEstimado'];
        $r['Programada'] = $r['FechaProgramada'] ? 1 : 0;
        $r['FechaCalendario'] = substr((string) ($r['FechaProgramada'] ?: $r['Vencimiento']), 0, 10);
        $out[] = $r;
    }
    return $out;
}

function esFinDeSemana(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && (int) $d->format('N') >= 6;
}

$inicioMes = date('Y-m-01');
$hoy = date('Y-m-d');

// Lista de la izquierda: impagas que quedaron en meses anteriores, para volver a ubicarlas.
if ($accion == 'listar_facturas_pendientes') {
    $data = array_values(array_filter(
        facturasPendientesCalendario($mysqli, $fechaDesde),
        fn($f) => $f['FechaCalendario'] < $inicioMes
    ));
    usort($data, fn($a, $b) => strcmp($a['FechaCalendario'], $b['FechaCalendario']));
    responder(true, '', $data);
}

// Calendario: impagas desde el mes actual (programadas por el operador o en su vencimiento).
if ($accion == 'listar_eventos') {
    $eventos = array();
    foreach (facturasPendientesCalendario($mysqli, $fechaDesde) as $f) {
        if ($f['FechaCalendario'] < $inicioMes) continue;
        $vencida = $f['FechaCalendario'] < $hoy;
        $color = $vencida ? '#fa5c7c' : ($f['FechaCalendario'] == $hoy ? '#ffbc00' : '#39afd1');
        $sugerida = !$f['Programada'];
        $eventos[] = array(
            'id' => 'f' . $f['id'],
            'title' => $f['RazonSocial'] . ' - $ ' . number_format($f['Saldo'], 2, ',', '.'),
            'start' => $f['FechaCalendario'],
            'allDay' => true,
            // programada por el operador: relleno; sugerida por vencimiento: solo contorno
            'backgroundColor' => $sugerida ? '#ffffff' : $color,
            'borderColor' => $color,
            'textColor' => $sugerida ? $color : '#ffffff',
            'classNames' => array($sugerida ? 'pago-sugerido' : 'pago-programado'),
            'extendedProps' => array(
                'idTransProveedores' => (int) $f['id'],
                'idProgramacion' => $f['idProgramacion'] ? (int) $f['idProgramacion'] : null,
                'RazonSocial' => $f['RazonSocial'],
                'Cuit' => $f['Cuit'],
                'TipoDeComprobante' => $f['TipoDeComprobante'],
                'NumeroComprobante' => $f['NumeroComprobante'],
                'Descripcion' => $f['Descripcion'],
                'Concepto' => $f['Concepto'],
                'Fecha' => $f['Fecha'],
                'Debe' => $f['Debe'],
                'Pagado' => $f['Pagado'],
                'importe_programado' => $f['Saldo'],
                'Vencimiento' => $f['Vencimiento'],
                'VencimientoEstimado' => $f['VencimientoEstimado'],
                'observacion' => $f['Observacion'],
                'estado' => $sugerida ? 'SUGERIDO' : 'PROGRAMADO',
                'vencida' => $vencida ? 1 : 0,
            ),
        );
    }
    echo json_encode($eventos);
    exit;
}

// Ubicar una factura en un día (arrastrándola desde la lista o dentro del calendario). Si ya tenía
// programación activa se le cambia la fecha; si no, se crea por el saldo actual. Sábado y domingo no.
if ($accion == 'ubicar_factura') {
    $idTransProveedores = (int) ($_POST['idTransProveedores'] ?? 0);
    $fecha = substr((string) ($_POST['fecha'] ?? ''), 0, 10);
    if ($idTransProveedores <= 0) responder(false, 'Factura inválida.');
    if (!DateTime::createFromFormat('Y-m-d', $fecha)) responder(false, 'Fecha inválida.');
    if (esFinDeSemana($fecha)) responder(false, 'Sábados y domingos no se paga: elegí un día hábil.');

    $f = null;
    foreach (facturasPendientesCalendario($mysqli, $fechaDesde) as $x) {
        if ((int) $x['id'] === $idTransProveedores) { $f = $x; break; }
    }
    if (!$f) responder(false, 'La factura no tiene saldo pendiente.');

    $ahora = date('Y-m-d H:i:s');
    if ($f['idProgramacion']) {
        $stmt = $mysqli->prepare("UPDATE pagos_programados_proveedores
                                  SET fecha_promesa = ?, importe_programado = ?, estado = 'REPROGRAMADO', updated_at = ?
                                  WHERE id = ? AND eliminado = 0 LIMIT 1");
        $idp = (int) $f['idProgramacion'];
        $stmt->bind_param("sdsi", $fecha, $f['Saldo'], $ahora, $idp);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO pagos_programados_proveedores
                                  (idTransProveedores, fecha_promesa, importe_programado, observacion, estado, usuario, created_at, eliminado)
                                  VALUES (?, ?, ?, '', 'PROGRAMADO', ?, ?, 0)");
        $stmt->bind_param("isdss", $idTransProveedores, $fecha, $f['Saldo'], $usuario, $ahora);
    }
    if (!$stmt->execute()) responder(false, $stmt->error);
    responder(true, 'Pago ubicado el ' . date('d/m/Y', strtotime($fecha)) . '.');
}

// Quitar la programación: la factura vuelve a su vencimiento.
if ($accion == 'eliminar_programacion') {
    $idProgramacion = (int) ($_POST['idProgramacion'] ?? 0);
    if ($idProgramacion <= 0) responder(false, 'Programación inválida.');
    $ahora = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("UPDATE pagos_programados_proveedores SET eliminado = 1, estado = 'CANCELADO', updated_at = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param("si", $ahora, $idProgramacion);
    if (!$stmt->execute()) responder(false, $stmt->error);
    responder(true, 'Se quitó la fecha elegida: la factura vuelve a su vencimiento.');
}

// Tarjetas de arriba.
if ($accion == 'cards') {
    $inicioSemana = date('Y-m-d', strtotime('monday this week'));
    $finSemana = date('Y-m-d', strtotime('sunday this week'));
    $finMes = date('Y-m-t');
    $data = array(
        'total_sin_programar' => 0, 'cantidad_sin_programar' => 0, // para reubicar (meses anteriores)
        'total_programado_mes' => 0, 'total_semana' => 0, 'total_vencido' => 0,
    );
    foreach (facturasPendientesCalendario($mysqli, $fechaDesde) as $f) {
        $d = $f['FechaCalendario'];
        if ($d < $inicioMes) { $data['cantidad_sin_programar']++; $data['total_sin_programar'] += $f['Saldo']; }
        if ($d >= $inicioMes && $d <= $finMes) $data['total_programado_mes'] += $f['Saldo'];
        if ($d >= $inicioSemana && $d <= $finSemana) $data['total_semana'] += $f['Saldo'];
        if ($d < $hoy) $data['total_vencido'] += $f['Saldo'];
    }
    responder(true, '', $data);
}

// Tabla "Resumen por fecha" (lo que está en el calendario).
if ($accion == 'resumen_fechas') {
    $grupos = array();
    foreach (facturasPendientesCalendario($mysqli, $fechaDesde) as $f) {
        $d = $f['FechaCalendario'];
        if ($d < $inicioMes) continue;
        if (!isset($grupos[$d])) {
            $grupos[$d] = array('fecha_promesa' => $d, 'cantidad' => 0, 'total' => 0,
                'estado' => $d < $hoy ? 'VENCIDO' : ($d == $hoy ? 'HOY' : 'PROGRAMADO'));
        }
        $grupos[$d]['cantidad']++;
        $grupos[$d]['total'] += $f['Saldo'];
    }
    ksort($grupos);
    responder(true, '', array_values($grupos));
}

responder(false, 'Acción no reconocida.');
