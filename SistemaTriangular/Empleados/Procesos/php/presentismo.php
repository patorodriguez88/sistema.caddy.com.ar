<?php
// Presentismo y Km de choferes (Empleados/Presentismo.php).
// - Presentismo: horarios de ingreso/egreso por empleado y día, en Empleados_Horarios (la misma
//   tabla que usaba el sistema viejo, así se conserva el historial). Carga por planilla del día.
// - Km choferes: resumen mensual de km por chofer desde las órdenes de salida (Logistica) de
//   vehículos propios, para liquidar a los choferes que cobran por km.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Cordoba');

function pr_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

// "2026-09" -> ['2026-09-01', '2026-09-30'] (o null si no es un mes válido)
function pr_mes(string $mes): ?array
{
    if (!preg_match('/^(\d{4})-(\d{2})$/', $mes, $m) || !checkdate((int)$m[2], 1, (int)$m[1])) return null;
    $desde = "{$m[1]}-{$m[2]}-01";
    return [$desde, date('Y-m-t', strtotime($desde))];
}

// "08:05" / "08:05:00" -> minutos desde las 0, o null
function pr_min(string $h): ?int
{
    if (!preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', trim($h), $m)) return null;
    $hh = (int)$m[1];
    $mm = (int)$m[2];
    return ($hh < 24 && $mm < 60) ? $hh * 60 + $mm : null;
}

$accion = $_POST['accion'] ?? '';

try {
    // Empleados activos (los externos/aliados solo si se piden)
    if ($accion === 'empleados') {
        $conExternos = !empty($_POST['externos']);
        $sql = "SELECT id, NombreCompleto, Puesto, Aliados FROM Empleados WHERE Inactivo = 0"
             . ($conExternos ? "" : " AND IFNULL(Aliados, 0) = 0")
             . " ORDER BY Puesto, NombreCompleto";
        pr_out(['ok' => true, 'empleados' => $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC)]);
    }

    // Planilla de un día: cada empleado con su horario cargado (si tiene)
    if ($accion === 'planilla') {
        $fecha = (string)($_POST['fecha'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) pr_out(['ok' => false, 'error' => 'Fecha inválida']);
        $conExternos = !empty($_POST['externos']);
        $st = $mysqli->prepare("SELECT e.id, e.NombreCompleto, e.Puesto, e.Aliados,
                                       h.id AS idHorario, TIME_FORMAT(h.Ingreso, '%H:%i') AS Ingreso,
                                       TIME_FORMAT(h.Egreso, '%H:%i') AS Egreso, h.OtroDia,
                                       TIME_FORMAT(h.HorasTotales, '%H:%i') AS Horas, h.UsuarioCarga
                                  FROM Empleados e
                                  LEFT JOIN Empleados_Horarios h ON h.idEmpleado = e.id AND h.Fecha = ?
                                 WHERE (e.Inactivo = 0" . ($conExternos ? "" : " AND IFNULL(e.Aliados, 0) = 0") . ") OR h.id IS NOT NULL
                                 ORDER BY e.Puesto, e.NombreCompleto");
        $st->bind_param('s', $fecha);
        $st->execute();
        pr_out(['ok' => true, 'filas' => $st->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }

    // Guarda la planilla del día: alta o modificación por empleado (las filas vacías se ignoran)
    if ($accion === 'guardar') {
        $fecha = (string)($_POST['fecha'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) pr_out(['ok' => false, 'error' => 'Fecha inválida']);
        $filas = json_decode((string)($_POST['filas'] ?? '[]'), true);
        if (!is_array($filas)) pr_out(['ok' => false, 'error' => 'Datos inválidos']);
        $usuario = (string)($_SESSION['Usuario'] ?? '');

        $buscar = $mysqli->prepare("SELECT id FROM Empleados_Horarios WHERE idEmpleado = ? AND Fecha = ? LIMIT 1");
        $insertar = $mysqli->prepare("INSERT INTO Empleados_Horarios (idEmpleado, Fecha, Ingreso, Egreso, UsuarioCarga, HorasTotales, OtroDia)
                                      VALUES (?, ?, ?, ?, ?, SEC_TO_TIME(? * 60), ?)");
        $modificar = $mysqli->prepare("UPDATE Empleados_Horarios SET Ingreso = ?, Egreso = ?, UsuarioCarga = ?,
                                              HorasTotales = SEC_TO_TIME(? * 60), OtroDia = ? WHERE id = ?");
        $errores = [];
        $guardados = 0;
        $mysqli->begin_transaction();
        foreach ($filas as $f) {
            $id = (int)($f['idEmpleado'] ?? 0);
            $in = trim((string)($f['ingreso'] ?? ''));
            $eg = trim((string)($f['egreso'] ?? ''));
            if ($id <= 0 || ($in === '' && $eg === '')) continue;
            $mi = pr_min($in);
            $me = pr_min($eg);
            $nombre = (string)($f['nombre'] ?? $id);
            if ($mi === null || $me === null) {
                $errores[] = "$nombre: falta el ingreso o el egreso.";
                continue;
            }
            // Egreso menor que el ingreso = terminó al día siguiente (turno noche)
            $otroDia = (!empty($f['otro_dia']) || $me < $mi) ? 1 : 0;
            $minutos = $me - $mi + ($otroDia ? 1440 : 0);
            if ($minutos <= 0 || $minutos > 20 * 60) {
                $errores[] = "$nombre: el horario da " . intdiv(max($minutos, 0), 60) . " h, revisalo.";
                continue;
            }
            $inT = sprintf('%02d:%02d:00', intdiv($mi, 60), $mi % 60);
            $egT = sprintf('%02d:%02d:00', intdiv($me, 60), $me % 60);
            $buscar->bind_param('is', $id, $fecha);
            $buscar->execute();
            $existe = $buscar->get_result()->fetch_row();
            if ($existe) {
                $idH = (int)$existe[0];
                $modificar->bind_param('sssiii', $inT, $egT, $usuario, $minutos, $otroDia, $idH);
                $modificar->execute();
            } else {
                $insertar->bind_param('issssii', $id, $fecha, $inT, $egT, $usuario, $minutos, $otroDia);
                $insertar->execute();
            }
            $guardados++;
        }
        $mysqli->commit();
        pr_out(['ok' => true, 'guardados' => $guardados, 'errores' => $errores]);
    }

    // Borra el horario de un empleado en un día
    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $mysqli->prepare("DELETE FROM Empleados_Horarios WHERE id = ? LIMIT 1");
        $st->bind_param('i', $id);
        $st->execute();
        pr_out(['ok' => $st->affected_rows > 0]);
    }

    // Resumen del mes: días trabajados y horas por empleado + el detalle día por día
    if ($accion === 'resumen') {
        $rango = pr_mes((string)($_POST['mes'] ?? ''));
        if (!$rango) pr_out(['ok' => false, 'error' => 'Mes inválido']);
        $st = $mysqli->prepare("SELECT h.id, h.idEmpleado, e.NombreCompleto, e.Puesto, h.Fecha,
                                       TIME_FORMAT(h.Ingreso, '%H:%i') AS Ingreso, TIME_FORMAT(h.Egreso, '%H:%i') AS Egreso,
                                       h.OtroDia, TIME_TO_SEC(h.HorasTotales) / 60 AS Minutos, h.UsuarioCarga
                                  FROM Empleados_Horarios h
                                  JOIN Empleados e ON e.id = h.idEmpleado
                                 WHERE h.Fecha BETWEEN ? AND ?
                                 ORDER BY e.NombreCompleto, h.Fecha");
        $st->bind_param('ss', $rango[0], $rango[1]);
        $st->execute();
        $detalle = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $resumen = [];
        foreach ($detalle as $d) {
            $k = $d['idEmpleado'];
            if (!isset($resumen[$k])) {
                $resumen[$k] = ['idEmpleado' => (int)$k, 'nombre' => $d['NombreCompleto'], 'puesto' => $d['Puesto'], 'dias' => 0, 'minutos' => 0];
            }
            $resumen[$k]['dias']++;
            $resumen[$k]['minutos'] += (int)$d['Minutos'];
        }
        pr_out(['ok' => true, 'desde' => $rango[0], 'hasta' => $rango[1], 'resumen' => array_values($resumen), 'detalle' => $detalle]);
    }

    // Km del mes por chofer, desde las órdenes de salida
    if ($accion === 'km') {
        $rango = pr_mes((string)($_POST['mes'] ?? ''));
        if (!$rango) pr_out(['ok' => false, 'error' => 'Mes inválido']);
        $soloPropios = !isset($_POST['solo_propios']) || (int)$_POST['solo_propios'] === 1;
        $st = $mysqli->prepare("SELECT l.id, l.NumerodeOrden, l.Fecha, l.Recorrido, r.Nombre AS RecorridoNombre,
                                       l.Patente, CONCAT_WS(' ', v.Marca, v.Modelo) AS Vehiculo, IFNULL(v.Aliados, 1) AS Aliados,
                                       l.NombreChofer, l.idUsuarioChofer, l.NombreChofer2, l.Estado,
                                       l.Kilometros AS KmSalida, l.KilometrosRegreso AS KmRegreso, l.KilometrosRecorridos AS Km
                                  FROM Logistica l
                                  LEFT JOIN Vehiculos v ON v.Dominio = l.Patente
                                  LEFT JOIN Recorridos r ON r.Numero = l.Recorrido
                                 WHERE l.Eliminado = 0 AND l.Fecha BETWEEN ? AND ?"
                             . ($soloPropios ? " AND v.Aliados = 0" : "") . "
                                 ORDER BY l.NombreChofer, l.Fecha, l.id");
        $st->bind_param('ss', $rango[0], $rango[1]);
        $st->execute();
        $viajes = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $choferes = [];
        foreach ($viajes as &$v) {
            $v['Km'] = (int)$v['Km'];
            $cerrado = (int)$v['KmRegreso'] > 0;
            // Alertas para revisar antes de liquidar
            $v['alerta'] = !$cerrado ? ($v['Estado'] === 'Cerrada' ? 'Cerrada sin km de regreso' : 'Todavía abierta')
                         : ($v['Km'] > 1000 ? 'Más de 1.000 km en un día' : '');
            $k = $v['NombreChofer'] ?: '(sin chofer)';
            if (!isset($choferes[$k])) {
                $choferes[$k] = ['chofer' => $k, 'salidas' => 0, 'km' => 0, 'sin_km' => 0, 'alertas' => 0, 'vehiculos' => []];
            }
            $choferes[$k]['salidas']++;
            $choferes[$k]['km'] += $cerrado ? $v['Km'] : 0;
            $choferes[$k]['sin_km'] += $cerrado ? 0 : 1;
            $choferes[$k]['alertas'] += $v['alerta'] !== '' ? 1 : 0;
            $choferes[$k]['vehiculos'][$v['Patente']] = true;
        }
        unset($v);
        foreach ($choferes as &$c) {
            $c['vehiculos'] = array_keys($c['vehiculos']);
        }
        unset($c);
        usort($choferes, fn($a, $b) => $b['km'] <=> $a['km']);
        pr_out(['ok' => true, 'desde' => $rango[0], 'hasta' => $rango[1], 'choferes' => $choferes, 'viajes' => $viajes]);
    }

    pr_out(['ok' => false, 'error' => 'Acción no válida']);
} catch (Throwable $e) {
    try { $mysqli->rollback(); } catch (Throwable $e2) {}
    error_log('presentismo: ' . $e->getMessage());
    pr_out(['ok' => false, 'error' => $e->getMessage()]);
}
