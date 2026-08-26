<?php
// Posición en vivo de los repartidores, alimentada por SistemaReparto/Proceso/php/ubicacion.php
// (guarda solo la última posición conocida por usuario, no un historial).
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT u.Latitud, u.Longitud, u.Precision_, u.TimeStamp, u.Recorrido,
           COALESCE(us.Nombre, u.Usuario) AS Nombre, u.Usuario
    FROM UbicacionRepartidor u
    LEFT JOIN usuarios us ON us.id = u.idUsuario
    ORDER BY u.TimeStamp DESC
";
$res = $mysqli->query($sql);

$repartidores = [];
while ($row = $res->fetch_assoc()) {
    $repartidores[] = [
        'nombre'    => $row['Nombre'],
        'usuario'   => $row['Usuario'],
        'recorrido' => $row['Recorrido'],
        'lat'       => (float)$row['Latitud'],
        'lng'       => (float)$row['Longitud'],
        'precision' => $row['Precision_'] !== null ? (int)$row['Precision_'] : null,
        'timestamp' => $row['TimeStamp'],
    ];
}

echo json_encode(['success' => 1, 'repartidores' => $repartidores]);
