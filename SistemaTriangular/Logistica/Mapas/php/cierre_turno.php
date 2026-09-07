<?php
// Cierre de Turno - resumen de la jornada de reparto para copiar a un grupo
// de WhatsApp. Una fila por orden Cargada de hoy: recorrido, chofer,
// entregados / no entregados / pendientes, estado del vehículo (en ruta,
// pausado, sin arrancar, terminado) y los motivos de no entrega del día.
//
// El armado del texto final lo hace el front (repartidores_live.js); acá
// solo se devuelven los datos estructurados.
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Argentina/Buenos_Aires');

// ¿Existe PausasRecorrido? (feature "Parar Ruta" de la app de reparto)
$tienePausas = false;
try {
    $chk = $mysqli->query("SHOW TABLES LIKE 'PausasRecorrido'");
    $tienePausas = $chk && $chk->num_rows > 0;
} catch (Throwable $e) {
    $tienePausas = false;
}
$joinPausa = $tienePausas
    ? "LEFT JOIN PausasRecorrido p ON p.idUsuario = l.idUsuarioChofer AND p.Fin IS NULL"
    : "";
$selPausa = $tienePausas ? "p.Motivo AS PausaMotivo" : "NULL AS PausaMotivo";

// Órdenes activas de hoy.
$sql = "
    SELECT
        l.NumerodeOrden, l.Recorrido, l.HoraSalidaReal,
        COALESCE(us.Nombre, l.NombreChofer) AS Chofer,
        r.Nombre AS RecorridoNombre,
        u.TimeStamp AS UbicTS,
        {$selPausa}
    FROM Logistica l
    LEFT JOIN Recorridos r          ON r.Numero = l.Recorrido
    LEFT JOIN UbicacionRepartidor u ON u.idUsuario = l.idUsuarioChofer
    LEFT JOIN usuarios us           ON us.id = l.idUsuarioChofer
    {$joinPausa}
    WHERE l.Estado = 'Cargada' AND l.Eliminado = 0 AND l.Fecha = CURDATE()
    ORDER BY (l.HoraSalidaReal IS NULL), l.HoraSalidaReal, l.NumerodeOrden
";
$res = $mysqli->query($sql);

$filas    = [];
$ordenIds = [];
while ($row = $res->fetch_assoc()) {
    $ordenIds[] = (int) $row['NumerodeOrden'];
    $filas[(int) $row['NumerodeOrden']] = [
        'orden'           => (int) $row['NumerodeOrden'],
        'recorrido'       => $row['Recorrido'],
        'recorridoNombre' => $row['RecorridoNombre'],
        'chofer'          => trim((string) $row['Chofer']),
        'arranco'         => !empty($row['HoraSalidaReal']),
        'horaSalida'      => $row['HoraSalidaReal'],
        'pausaMotivo'     => $row['PausaMotivo'],
        'ubicTS'          => $row['UbicTS'],
        'entregados'      => 0,
        'noEntregados'    => 0,
        'total'           => 0,
        'motivos'         => [],
    ];
}

if ($ordenIds) {
    $inOrden = implode(',', array_map('intval', $ordenIds));

    // Conteos por orden.
    $sqlPaq = "
        SELECT h.NumerodeOrden,
               COUNT(*)              AS Total,
               SUM(tc.Entregado = 1) AS Entregados,
               SUM(tc.Entregado = 0 AND tc.Estado = 'No se pudo entregar') AS NoEntregados
        FROM HojaDeRuta h
        INNER JOIN TransClientes tc ON tc.CodigoSeguimiento = h.Seguimiento
        WHERE h.NumerodeOrden IN ({$inOrden})
          AND h.Eliminado = 0 AND h.Devuelto = 0 AND tc.Eliminado = 0
        GROUP BY h.NumerodeOrden
    ";
    foreach ($mysqli->query($sqlPaq) as $row) {
        $no = (int) $row['NumerodeOrden'];
        if (!isset($filas[$no])) continue;
        $filas[$no]['total']        = (int) $row['Total'];
        $filas[$no]['entregados']   = (int) $row['Entregados'];
        $filas[$no]['noEntregados'] = (int) $row['NoEntregados'];
    }

    // Motivos de no entrega de hoy (Seguimiento). Si un envío falló más de una
    // vez en el día, queda el último motivo cargado.
    $sqlMot = "
        SELECT h.NumerodeOrden, s.CodigoSeguimiento, h.Cliente,
               s.Observaciones, s.Hora
        FROM HojaDeRuta h
        INNER JOIN Seguimiento s ON s.CodigoSeguimiento = h.Seguimiento
        WHERE h.NumerodeOrden IN ({$inOrden})
          AND h.Eliminado = 0 AND h.Devuelto = 0
          AND s.Estado = 'No se pudo entregar'
          AND s.Fecha = CURDATE()
        ORDER BY h.NumerodeOrden, s.CodigoSeguimiento, s.Hora
    ";
    $motPorCs = [];
    foreach ($mysqli->query($sqlMot) as $row) {
        $no = (int) $row['NumerodeOrden'];
        $cs = $row['CodigoSeguimiento'];
        $motPorCs[$no][$cs] = [
            'cs'      => $cs,
            'cliente' => trim((string) $row['Cliente']),
            'motivo'  => trim((string) $row['Observaciones']),
            'hora'    => substr((string) $row['Hora'], 0, 5),
        ];
    }
    foreach ($motPorCs as $no => $porCs) {
        if (isset($filas[$no])) {
            $filas[$no]['motivos'] = array_values($porCs);
        }
    }
}

// Estado del vehículo + minutos desde la última señal.
$ahora = new DateTime();
$recorridos = [];
foreach ($filas as $f) {
    $pend = max(0, $f['total'] - $f['entregados'] - $f['noEntregados']);

    if ($pend === 0 && $f['total'] > 0) {
        $estado = 'terminado';
    } elseif (!empty($f['pausaMotivo'])) {
        $estado = 'pausado';
    } elseif (!$f['arranco']) {
        $estado = 'sin_arrancar';
    } else {
        $estado = 'en_ruta';
    }

    $ultSenalMin = null;
    if (!empty($f['ubicTS'])) {
        try {
            $ts = new DateTime($f['ubicTS']);
            $ultSenalMin = (int) floor(($ahora->getTimestamp() - $ts->getTimestamp()) / 60);
        } catch (Throwable $e) {
            $ultSenalMin = null;
        }
    }

    $recorridos[] = [
        'recorrido'       => $f['recorrido'],
        'recorridoNombre' => $f['recorridoNombre'],
        'chofer'          => $f['chofer'],
        'orden'           => $f['orden'],
        'arranco'         => $f['arranco'],
        'horaSalida'      => $f['horaSalida'] ? substr((string) $f['horaSalida'], 11, 5) : null,
        'entregados'      => $f['entregados'],
        'noEntregados'    => $f['noEntregados'],
        'pendientes'      => $pend,
        'total'           => $f['total'],
        'estado'          => $estado,
        'pausaMotivo'     => $f['pausaMotivo'],
        'ultSenalMin'     => $ultSenalMin,
        'appActiva'       => $ultSenalMin !== null && $ultSenalMin <= 15,
        'motivos'         => $f['motivos'],
    ];
}

echo json_encode([
    'success'    => 1,
    'fecha'      => $ahora->format('d/m/Y'),
    'hora'       => $ahora->format('H:i'),
    'operador'   => $_SESSION['Usuario'] ?? 'sistema',
    'recorridos' => $recorridos,
]);
