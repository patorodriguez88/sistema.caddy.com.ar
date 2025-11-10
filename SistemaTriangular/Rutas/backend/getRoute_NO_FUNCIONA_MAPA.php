<?php
require_once 'config.php';
require_once 'helpers.php';
require_once 'kmeans.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

date_default_timezone_set('America/Argentina/Cordoba');

// Función Haversine para calcular distancias entre coordenadas
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
$driverCounts = isset($input['driverCounts']) ? (int)$input['driverCounts'] : 1;
$maxDistancePerDriver = isset($input['maxDistance']) ? (float)$input['maxDistance'] : 50; // km
$maxTimePerDriver = isset($input['maxTime']) ? (float)$input['maxTime'] : 4 * 3600; // 4 horas en segundos

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

// **1. Aplicar K-Means para agrupar waypoints entre los choferes**
// require 'kmeans.php';  // Archivo donde definimos el algoritmo de clustering K-Means

$clusters = kMeansClustering($waypoints, $driverCounts);

// **2. Optimización de rutas para cada chofer**
$routes = [];

foreach ($clusters as $driverId => $driverWaypoints) {
    $segmentStart = $fixedOrigin;
    $currentDeparture = $departureTimeISO;
    $driverRoute = [];

    foreach ($driverWaypoints as $index => $point) {
        $nextWaypoint = ["latitude" => $point['lat'], "longitude" => $point['lng']];
        $distanceToNext = haversineDistance(
            $segmentStart['latitude'],
            $segmentStart['longitude'],
            $point['lat'],
            $point['lng']
        );
        $estimatedTravelTime = ($distanceToNext / 40) * 3600; // Suponiendo 40 km/h

        // Manejo de ventanas horarias
        if (!empty($timeWindows[$index]) && strpos($timeWindows[$index], '-') !== false) {
            list($start, $end) = explode('-', $timeWindows[$index]);
            $arrivalTime = gmdate("Y-m-d\TH:i:s\Z", strtotime("$currentDate $start"));
            $waitTime = max(0, strtotime($arrivalTime) - (strtotime($currentDeparture) + $estimatedTravelTime));
            $newDepartureTime = gmdate("Y-m-d\TH:i:s\Z", strtotime($currentDeparture) + $estimatedTravelTime + $waitTime);
        } else {
            $newDepartureTime = gmdate("Y-m-d\TH:i:s\Z", strtotime($currentDeparture) + $estimatedTravelTime);
        }

        // Construcción de la solicitud a Google Advanced Routes API
        $body = [
            "origin" => ["location" => ["latLng" => $segmentStart]],
            "destination" => ["location" => ["latLng" => $nextWaypoint]],
            "travelMode" => "DRIVE",
            "routingPreference" => "TRAFFIC_AWARE_OPTIMAL",
            "departureTime" => $newDepartureTime
        ];

        // Enviar solicitud
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

        if ($error) {
            response('error', 'Error en la solicitud: ' . $error);
        }

        $responseData = json_decode($response, true);
        if (!$responseData || !isset($responseData['routes'])) {
            response('error', 'La API de Google no devolvió rutas.');
        }

        $driverRoute[] = $responseData['routes'][0];

        // Actualizar inicio del siguiente tramo
        $segmentStart = $nextWaypoint;
        $currentDeparture = $newDepartureTime;
    }

    $routes[$driverId] = $driverRoute;
}

response('success', 'Rutas calculadas exitosamente.', $routes);
?>