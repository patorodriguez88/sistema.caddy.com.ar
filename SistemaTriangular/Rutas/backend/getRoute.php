<?php
require_once 'config.php';
require_once 'helpers.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Configurar la zona horaria correcta
date_default_timezone_set('America/Argentina/Cordoba');

/**
 * Función para calcular la distancia entre dos coordenadas usando la fórmula de Haversine
 */
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radio de la Tierra en km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    response('error', 'No se recibieron datos.');
}

$fixedOrigin = ['lat' => -31.444994776141503, 'lng' => -64.1779408896999];
$waypoints = isset($input['waypoints']) ? $input['waypoints'] : [];
$stopTimes = isset($input['stopTimes']) ? $input['stopTimes'] : [];
$vehicleType = isset($input['vehicleType']) ? $input['vehicleType'] : "GASOLINE";
$driversCount = isset($input['driversCount']) ? intval($input['driversCount']) : 1;
$maxKm = isset($input['maxKm']) ? floatval($input['maxKm']) : null;
$maxMinutes = isset($input['maxMinutes']) ? floatval($input['maxMinutes']) : null;
$startTime = isset($input['startTime']) ? $input['startTime'] : '08:00';

$currentDate = date('Y-m-d');
$currentTimestamp = time(); // Timestamp actual en segundos

// Convertir la hora de inicio a timestamp UTC
$departureTimestamp = strtotime("$currentDate $startTime");

// Si la hora ingresada ya pasó hoy, programarla para el día siguiente
if ($departureTimestamp < $currentTimestamp) {
    $departureTimestamp = strtotime("+1 day", $departureTimestamp);
}

// Convertir a formato ISO 8601 para Google API
$departureTimeISO = gmdate("Y-m-d\TH:i:s\Z", $departureTimestamp);


// Obtener la fecha
$timeWindows = isset($input['timeWindows']) ? $input['timeWindows'] : [];

// Verificación de waypoints
if (empty($waypoints)) {
    response('error', 'No se recibieron waypoints.');
}

// Validar el horario de salida y convertirlo en formato UTC ISO 8601
// $currentTime = time();
// $departureTimestamp = date("Y-m-d\TH:i:s\Z", strtotime(date('Y-m-d') . " " . $startTime));

if (strtotime($departureTimeISO) < $currentTimestamp) {
    response('error', 'El horario de salida debe ser en el futuro.');
}

// Implementar K-Means para agrupar waypoints en rutas más eficientes
function kMeans($waypoints, $k, $maxIterations = 100) {
    if (count($waypoints) < $k) {
        return [$waypoints];
    }

    $centroids = array_slice($waypoints, 0, $k);
    $clusters = [];

    for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
        $clusters = array_fill(0, $k, []);
        
        foreach ($waypoints as $point) {
            if (!isset($point['lat']) || !isset($point['lng'])) {
                continue;
            }
            $closestCentroid = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($centroids as $index => $centroid) {
                $distance = haversineDistance($point['lat'], $point['lng'], $centroid['lat'], $centroid['lng']);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $closestCentroid = $index;
                }
            }

            $clusters[$closestCentroid][] = $point;
        }

        $newCentroids = [];
        foreach ($clusters as $cluster) {
            if (count($cluster) > 0) {
                $latSum = array_sum(array_column($cluster, 'lat')) / count($cluster);
                $lngSum = array_sum(array_column($cluster, 'lng')) / count($cluster);
                $newCentroids[] = ['lat' => $latSum, 'lng' => $lngSum];
            } else {
                $newCentroids[] = $centroids[array_rand($centroids)];
            }
        }

        if ($centroids === $newCentroids) break;
        $centroids = $newCentroids;
    }

    return $clusters;
}

$clusters = kMeans($waypoints, $driversCount);
$routes = [];

foreach ($clusters as $index => $waypointsGroup) {
    if (count($waypointsGroup) < 1) {
        response('error', 'Cada ruta debe tener al menos 1 waypoint.');
    }

    // Definir origen y destino
    $routeDestination = array_pop($waypointsGroup);
    $intermediates = [];

    foreach ($waypointsGroup as $i => $point) {
        if (!isset($point['lat']) || !isset($point['lng'])) {
            continue;
        }

        $intermediate = [
            "location" => [
                "latLng" => [
                    "latitude" => floatval($point['lat']),
                    "longitude" => floatval($point['lng'])
                ]
            ]
        ];

        // Si el waypoint tiene una ventana horaria, se agrega
        // if (!empty($timeWindows[$i]) && strpos($timeWindows[$i], '-') !== false) {
        //     list($start, $end) = explode('-', $timeWindows[$i]);
        //     $intermediate["arrivalWindow"] = [
        //         "startTime" => date("Y-m-d\TH:i:s\Z", strtotime(date('Y-m-d') . " " . $start)),
        //         "endTime" => date("Y-m-d\TH:i:s\Z", strtotime(date('Y-m-d') . " " . $end))
        //     ];
        // }

        $intermediates[] = $intermediate;
    }

    $body = [
        "origin" => [
            "location" => [
                "latLng" => [
                    "latitude" => $fixedOrigin['lat'],
                    "longitude" => $fixedOrigin['lng']
                ]
            ]
        ],
        "destination" => [
            "location" => [
                "latLng" => [
                    "latitude" => $routeDestination['lat'],
                    "longitude" => $routeDestination['lng']
                ]
            ]
        ],
        "intermediates" => $intermediates,
        "travelMode" => "DRIVE",
        "routingPreference" => "TRAFFIC_AWARE_OPTIMAL",
        "departureTime" => $departureTimeISO
    ];

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
        response('error', 'Error en la solicitud para la ruta ' . ($index + 1) . ': ' . $error);
    }

    $responseData = json_decode($response, true);

    if (!isset($responseData['routes']) || count($responseData['routes']) === 0) {
        response('error', 'No se encontró ninguna ruta para el repartidor ' . ($index + 1));
    }

    $routes[] = $responseData['routes'][0];
}
$routeSummary = [];
$routeColors = ['#007bff', '#28a745', '#ffc107', '#dc3545'];

foreach ($routes as $index => $route) {
    $totalDistance = 0; // En metros
    $totalDuration = 0; // En segundos
    $waypointsCount = count($route['legs']); // Cantidad de puntos intermedios + destino

    foreach ($route['legs'] as $leg) {
        $totalDistance += isset($leg['distanceMeters']) ? $leg['distanceMeters'] : 0;

        if (isset($leg['duration']) && preg_match('/(\d+)s/', $leg['duration'], $matches)) {
            $totalDuration += intval($matches[1]); // Extrae los segundos correctamente
        }
    }

    // Convertir distancia a kilómetros y duración a horas:minutos
    $totalDistanceKm = round($totalDistance / 1000, 2);
    $totalDurationMinutes = round($totalDuration / 60);
    $hours = floor($totalDurationMinutes / 60);
    $minutes = $totalDurationMinutes % 60;

    // Asegurar que no muestre valores negativos
    if ($totalDuration <= 0) {
        $formattedTime = "00:00";
    } else {
        $formattedTime = sprintf("%02d:%02d", $hours, $minutes);
    }

    $routeSummary[] = [
        "routeIndex" => $index + 1,
        "color" => $routeColors[$index % count($routeColors)], // Cicla sobre la lista de colores
        "distance_km" => $totalDistanceKm,
        "waypoints_count" => $waypointsCount,
        "estimated_time" => $formattedTime // Formato HH:MM
    ];
}

// Agregar el resumen a la respuesta final
response('success', 'Rutas independientes calculadas exitosamente.', [
    "routes" => $routes,
    "summary" => $routeSummary
]);
// response('success', 'Rutas independientes calculadas exitosamente.', $routes);
?>