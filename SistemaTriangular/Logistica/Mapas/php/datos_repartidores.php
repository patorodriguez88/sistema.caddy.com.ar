<?php
// Repartidores en vivo.
//
// Criterio: una fila por ORDEN de salida activa de hoy
// (Logistica.Estado='Cargada' AND Fecha = CURDATE()). La posición sale de
// UbicacionRepartidor (1 fila por usuario, última posición conocida que manda
// la PWA de reparto vía SistemaReparto/Proceso/php/ubicacion.php) uniendo por
// idUsuarioChofer.
//
// Los conteos entregados/pendientes van scopeados al NumerodeOrden de esa
// orden -- NO por número de recorrido: el nº de recorrido se reusa entre
// órdenes y antes sumaba meses de entregas ("6457 entregados").
//
// Es un panel en vivo: solo repartidores con orden Cargada hoy. Los que tienen
// la app abierta sin reparto asignado no se muestran acá.
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

// La tabla PausasRecorrido (feature "Parar Ruta" de la app de reparto) puede
// no existir en todos los ambientes; si falta, el mapa sigue andando sin el
// dato de pausa.
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
$selPausa = $tienePausas
    ? "p.Motivo AS PausaMotivo, p.Detalle AS PausaDetalle, p.Inicio AS PausaInicio"
    : "NULL AS PausaMotivo, NULL AS PausaDetalle, NULL AS PausaInicio";

$sql = "
    SELECT
        l.NumerodeOrden, l.Recorrido, l.idUsuarioChofer, l.HoraSalidaReal,
        COALESCE(us.Nombre, l.NombreChofer, u.Usuario) AS Nombre,
        u.Usuario,
        r.Nombre AS RecorridoNombre,
        r.Color  AS RecorridoColor,
        u.Latitud, u.Longitud, u.Precision_, u.TimeStamp,
        {$selPausa}
    FROM Logistica l
    LEFT JOIN Recorridos r          ON r.Numero = l.Recorrido
    LEFT JOIN UbicacionRepartidor u ON u.idUsuario = l.idUsuarioChofer
    LEFT JOIN usuarios us           ON us.id = l.idUsuarioChofer
    {$joinPausa}
    WHERE l.Estado = 'Cargada' AND l.Eliminado = 0 AND l.Fecha = CURDATE()
    ORDER BY (l.HoraSalidaReal IS NULL), l.HoraSalidaReal DESC, l.NumerodeOrden
";

$res = $mysqli->query($sql);

$repartidores = [];
$ordenIds     = [];
while ($row = $res->fetch_assoc()) {
    $ordenIds[]  = (int) $row['NumerodeOrden'];
    $tienePos = $row['Latitud'] !== null && $row['Longitud'] !== null;
    $repartidores[] = [
        'nombre'          => trim((string) $row['Nombre']),
        'usuario'         => $row['Usuario'],
        'orden'           => (int) $row['NumerodeOrden'],
        'recorrido'       => $row['Recorrido'],
        'recorridoNombre' => $row['RecorridoNombre'],
        'color'           => $row['RecorridoColor'],
        'horaSalida'      => $row['HoraSalidaReal'],           // null = no inició el recorrido
        'arranco'         => !empty($row['HoraSalidaReal']),
        'lat'             => $tienePos ? (float) $row['Latitud'] : null,
        'lng'             => $tienePos ? (float) $row['Longitud'] : null,
        'precision'       => $row['Precision_'] !== null ? (int) $row['Precision_'] : null,
        'timestamp'       => $row['TimeStamp'], // null si nunca mandó posición
        'pausaMotivo'     => $row['PausaMotivo'],
        'pausaDetalle'    => $row['PausaDetalle'],
        'pausaInicio'     => $row['PausaInicio'],
        'totalPaquetes'   => 0,
        'entregados'      => 0,
        'noEntregados'    => 0,
    ];
}

// Entregados/total por orden, en una sola consulta acotada a las órdenes
// activas de hoy (rápida, ~pocas órdenes por día).
if ($ordenIds) {
    $inOrden = implode(',', array_map('intval', $ordenIds));
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
    $resPaq = $mysqli->query($sqlPaq);
    $porOrden = [];
    while ($row = $resPaq->fetch_assoc()) {
        $porOrden[(int) $row['NumerodeOrden']] = [
            'total'        => (int) $row['Total'],
            'entregados'   => (int) $row['Entregados'],
            'noEntregados' => (int) $row['NoEntregados'],
        ];
    }
    foreach ($repartidores as &$rep) {
        if (isset($porOrden[$rep['orden']])) {
            $rep['totalPaquetes'] = $porOrden[$rep['orden']]['total'];
            $rep['entregados']    = $porOrden[$rep['orden']]['entregados'];
            $rep['noEntregados']  = $porOrden[$rep['orden']]['noEntregados'];
        }
    }
    unset($rep);
}

echo json_encode([
    'success'      => 1,
    'repartidores' => $repartidores,
]);
