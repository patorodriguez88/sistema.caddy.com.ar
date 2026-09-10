<?php
// Posicion EN VIVO del repartidor de una orden de salida (Logistica.id).
//
// Fuente: UbicacionRepartidor (1 fila por usuario, ultima posicion que manda la
// PWA de reparto via SistemaReparto/Proceso/php/ubicacion.php, ~cada 30 s),
// unida por idUsuario = Logistica.idUsuarioChofer.
//
// Lo usa el mapa de la orden abierta en HojaDeRuta2 (Mapas/js/controlrecorridos.js
// -> initMap_order), que lo pollea cada 30 s. Mismo dato que la pantalla
// "Repartidores en Vivo" (datos_repartidores.php) pero acotado a una orden.
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
if ($id <= 0) {
    echo json_encode(['success' => 0, 'error' => 'SIN_ID']);
    exit;
}

$sql = "
    SELECT
        l.idUsuarioChofer, l.NumerodeOrden, l.Recorrido, l.Estado, l.HoraSalidaReal,
        COALESCE(us.Nombre, l.NombreChofer, u.Usuario) AS Nombre,
        u.Usuario,
        u.Latitud, u.Longitud, u.Precision_, u.TimeStamp
    FROM Logistica l
    LEFT JOIN UbicacionRepartidor u ON u.idUsuario = l.idUsuarioChofer
    LEFT JOIN usuarios us            ON us.id = l.idUsuarioChofer
    WHERE l.id = ?
    LIMIT 1
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'SQL']);
    exit;
}
$stmt->bind_param('i', $id);
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
