<?php
// Posicion EN VIVO del repartidor de una orden de salida.
//
// Se le pasa uno de:
//   - id        = Logistica.id  (mapa de la orden abierta / initMap_order)
//   - recorrido = nro de recorrido (mapa "Servicios Pendientes Recorrido N" /
//                 initMap): se resuelve la orden mas nueva de ese recorrido hoy.
//
// Fuente de la posicion: UbicacionRepartidor (1 fila por usuario, ultima
// posicion que manda la PWA de reparto via SistemaReparto/Proceso/php/ubicacion.php,
// ~cada 30 s), unida por idUsuario = Logistica.idUsuarioChofer. Mismo dato que
// la pantalla "Repartidores en Vivo" (datos_repartidores.php).
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

$id  = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
$rec = isset($_POST['recorrido']) ? trim((string) $_POST['recorrido'])
    : (isset($_GET['recorrido']) ? trim((string) $_GET['recorrido']) : '');

if ($id <= 0 && $rec === '') {
    echo json_encode(['success' => 0, 'error' => 'SIN_PARAMS']);
    exit;
}

$selCols = "
        l.idUsuarioChofer, l.NumerodeOrden, l.Recorrido, l.Estado, l.HoraSalidaReal,
        COALESCE(us.Nombre, l.NombreChofer, u.Usuario) AS Nombre,
        u.Usuario,
        u.Latitud, u.Longitud, u.Precision_, u.TimeStamp
";

if ($id > 0) {
    $sql = "SELECT {$selCols}
            FROM Logistica l
            LEFT JOIN UbicacionRepartidor u ON u.idUsuario = l.idUsuarioChofer
            LEFT JOIN usuarios us            ON us.id = l.idUsuarioChofer
            WHERE l.id = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
} else {
    // orden mas nueva del recorrido hoy (Cargada o Cerrada); si no hay de hoy,
    // la mas nueva sin importar la fecha.
    $sql = "SELECT {$selCols}
            FROM Logistica l
            LEFT JOIN UbicacionRepartidor u ON u.idUsuario = l.idUsuarioChofer
            LEFT JOIN usuarios us            ON us.id = l.idUsuarioChofer
            WHERE l.Recorrido = ? AND l.Eliminado = 0
            ORDER BY (l.Fecha = CURDATE()) DESC, l.id DESC
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $rec);
}

if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'SQL']);
    exit;
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => 0, 'error' => 'ORDEN_NO_ENCONTRADA']);
    exit;
}

$tienePos = $row['Latitud'] !== null && $row['Longitud'] !== null
    && $row['Latitud'] !== '' && $row['Longitud'] !== '';

$minutos = null;
if (!empty($row['TimeStamp'])) {
    $ts = strtotime((string) $row['TimeStamp']);
    if ($ts) {
        $minutos = (int) floor((time() - $ts) / 60);
    }
}

echo json_encode([
    'success'   => 1,
    'tienePos'  => $tienePos ? 1 : 0,
    'lat'       => $tienePos ? (float) $row['Latitud'] : null,
    'lng'       => $tienePos ? (float) $row['Longitud'] : null,
    'precision' => $row['Precision_'] !== null ? (int) $row['Precision_'] : null,
    'timestamp' => $row['TimeStamp'],
    'minutos'   => $minutos,
    'nombre'    => trim((string) $row['Nombre']),
    'usuario'   => $row['Usuario'],
    'recorrido' => $row['Recorrido'],
    'orden'     => (int) $row['NumerodeOrden'],
    'estado'    => $row['Estado'],
    'arranco'   => !empty($row['HoraSalidaReal']) ? 1 : 0,
]);
