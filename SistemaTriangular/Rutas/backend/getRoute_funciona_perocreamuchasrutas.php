<?php
require_once 'config.php';
require_once 'helpers.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

date_default_timezone_set('America/Argentina/Cordoba');

// Función para calcular la distancia entre dos coordenadas (Haversine)
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    response('error', 'No se recibieron datos.');
}

// Parámetros recibidos
$fixedOrigin = ["latitude" => -31.444994776141503, "longitude" => -64.1779408896999];
$waypoints = $input['waypoints'] ?? [];
$timeWindows = $input['timeWindows'] ?? [];
$startTime = isset($input['startTime']) ? $input['startTime'] : '08:00';

// Validación de waypoints
if (empty($waypoints)) {
    response('error', 'No se recibieron waypoints.');
}

// Convertir `startTime` al formato UTC ISO 8601
$currentDate = date('Y-m-d');
$departureTimestamp = strtotime("$currentDate $startTime");
if ($departureTimestamp < time()) {
    $departureTimestamp = strtotime("$currentDate $startTime +1 day");
}
$departureTimeISO = gmdate("Y-m-d\TH:i:s\Z", $departureTimestamp);

// Velocidad promedio en km/h
$averageSpeed = 40;

// Construcción de segmentos de ruta
$routes = [];
$segmentStart = $fixedOrigin;
$currentDeparture = $departureTimeISO;

foreach ($waypoints as $index => $point) {
    $nextWaypoint = ["latitude" => $point['lat'], "longitude" => $point['lng']];

    // Calcular duración estimada de viaje
    $distanceToNext = haversineDistance(
        $segmentStart['latitude'],
        $segmentStart['longitude'],
        $point['lat'],
        $point['lng']
    );
    $estimatedTravelTime = ($distanceToNext / $averageSpeed) * 3600; // En segundos

    // Si el waypoint tiene restricción horaria, ajustar el `departureTime`
    if (!empty($timeWindows[$index]) && strpos($timeWindows[$index], '-') !== false) {
        list($start, $end) = explode('-', $timeWindows[$index]);
        $arrivalTime = gmdate("Y-m-d\TH:i:s\Z", strtotime("$currentDate $start"));
        $waitTime = max(0, strtotime($arrivalTime) - (strtotime($currentDeparture) + $estimatedTravelTime));
        $newDepartureTime = gmdate("Y-m-d\TH:i:s\Z", strtotime($currentDeparture) + $estimatedTravelTime + $waitTime);
    } else {
        $newDepartureTime = gmdate("Y-m-d\TH:i:s\Z", strtotime($currentDeparture) + $estimatedTravelTime);
    }

    // Construcción del JSON de solicitud
    $body = [
        "origin" => ["location" => ["latLng" => $segmentStart]],
        "destination" => ["location" => ["latLng" => $nextWaypoint]],
        "travelMode" => "DRIVE",
        "routingPreference" => "TRAFFIC_AWARE_OPTIMAL",
        "departureTime" => $newDepartureTime
    ];

    // Enviar solicitud a Google Routes API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . GOOGLE_API_KEY,
        'X-Goog-FieldMask: routes.legs,routes.duration,routes.distanceMeters'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Manejo de errores
    if ($error) {
        response('error', 'Error en la solicitud para el waypoint ' . ($index + 1) . ': ' . $error);
    }

    // Decodificar respuesta
    $responseData = json_decode($response, true);
    if (!$responseData || !isset($responseData['routes'])) {
        response('error', 'La API de Google no devolvió rutas. Respuesta: ' . json_encode($responseData));
    }

    $routes[] = $responseData['routes'][0];

    // Actualizar inicio del siguiente tramo y `departureTime`
    $segmentStart = $nextWaypoint;
    $currentDeparture = $newDepartureTime;
}

// Respuesta final
response('success', 'Rutas calculadas exitosamente.', $routes);
?>
