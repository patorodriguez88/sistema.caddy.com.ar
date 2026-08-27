<?php
// Posición en vivo de los repartidores, alimentada por SistemaReparto/Proceso/php/ubicacion.php
// (guarda solo la última posición conocida por usuario, no un historial).
require_once __DIR__ . '/../../../Conexion/Conexioni.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT u.idUsuario, u.Latitud, u.Longitud, u.Precision_, u.TimeStamp, u.Recorrido,
           COALESCE(us.Nombre, u.Usuario) AS Nombre, u.Usuario,
           p.Motivo AS PausaMotivo, p.Detalle AS PausaDetalle, p.Inicio AS PausaInicio
    FROM UbicacionRepartidor u
    LEFT JOIN usuarios us ON us.id = u.idUsuario
    LEFT JOIN PausasRecorrido p ON p.idUsuario = u.idUsuario AND p.Fin IS NULL
    ORDER BY u.TimeStamp DESC
";
// La tabla PausasRecorrido es nueva (feature de "Parar Ruta" en la app de
// reparto) - si todavía no existe en algún ambiente, no debe tirar abajo
// todo el mapa de repartidores en vivo, solo queda sin el dato de pausa.
// (mysqli tira excepción en vez de devolver false ante un error SQL, así
// que hace falta try/catch - un @ no alcanza para atajar una excepción.)
try {
    $res = $mysqli->query($sql);
} catch (Throwable $e) {
    $sql = "
        SELECT u.idUsuario, u.Latitud, u.Longitud, u.Precision_, u.TimeStamp, u.Recorrido,
               COALESCE(us.Nombre, u.Usuario) AS Nombre, u.Usuario,
               NULL AS PausaMotivo, NULL AS PausaDetalle, NULL AS PausaInicio
        FROM UbicacionRepartidor u
        LEFT JOIN usuarios us ON us.id = u.idUsuario
        ORDER BY u.TimeStamp DESC
    ";
    $res = $mysqli->query($sql);
}

// Entregados/total de paquetes del recorrido de cada repartidor (para el
// "5/6" que se muestra al lado del nombre en la lista). Una consulta chica
// por repartidor (son pocos por día) en vez de una sola con GROUP BY, para
// no complicar el query principal de arriba.
function contarPaquetesRecorrido(mysqli $mysqli, string $recorrido): array
{
    if ($recorrido === '') {
        return ['total' => null, 'entregados' => null];
    }
    $recorridoEsc = $mysqli->real_escape_string($recorrido);
    $res = $mysqli->query(
        "SELECT COUNT(*) AS total, SUM(TransClientes.Entregado = 1) AS entregados
         FROM HojaDeRuta
         INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento = HojaDeRuta.Seguimiento
         WHERE HojaDeRuta.Recorrido = '{$recorridoEsc}'
           AND HojaDeRuta.Eliminado = 0
           AND HojaDeRuta.Devuelto = 0
           AND TransClientes.Eliminado = 0"
    );
    $row = $res ? $res->fetch_assoc() : null;
    return [
        'total'      => $row ? (int) $row['total'] : null,
        'entregados' => $row ? (int) $row['entregados'] : null,
    ];
}

$repartidores = [];
while ($row = $res->fetch_assoc()) {
    $paquetes = contarPaquetesRecorrido($mysqli, (string) $row['Recorrido']);
    $repartidores[] = [
        'nombre'         => $row['Nombre'],
        'usuario'        => $row['Usuario'],
        'recorrido'      => $row['Recorrido'],
        'lat'            => (float)$row['Latitud'],
        'lng'            => (float)$row['Longitud'],
        'precision'      => $row['Precision_'] !== null ? (int)$row['Precision_'] : null,
        'timestamp'      => $row['TimeStamp'],
        'pausaMotivo'    => $row['PausaMotivo'],
        'pausaDetalle'   => $row['PausaDetalle'],
        'pausaInicio'    => $row['PausaInicio'],
        'totalPaquetes'  => $paquetes['total'],
        'entregados'     => $paquetes['entregados'],
    ];
}

echo json_encode(['success' => 1, 'repartidores' => $repartidores]);
