<?php
require_once 'config.php';
require_once 'helpers.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    response('error', 'No se recibieron datos.');
}

$fixedOrigin = ['lat' => -31.444994776141503, 'lng' => -64.1779408896999];
$waypoints = $input['waypoints'];
$stopTimes = $input['stopTimes'];
$vehicleType = $input['vehicleType'];
$driversCount = isset($input['driversCount']) ? intval($input['driversCount']) : 1;
$maxKm = isset($input['maxKm']) ? floatval($input['maxKm']) : null;
$maxMinutes = isset($input['maxMinutes']) ? floatval($input['maxMinutes']) : null;

if ($driversCount < 1) $driversCount = 1;

// Función para calcular la distancia entre dos puntos (haversine formula)
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

// Implementación simple de DBSCAN
function dbscan($waypoints, $epsilon = 2, $minPoints = 2) {
    $clusters = [];
    $visited = [];
    $noise = [];

    foreach ($waypoints as $index => $point) {
        if (isset($visited[$index])) continue;
        $visited[$index] = true;

        // Encontrar vecinos cercanos
        $neighbors = [];
        foreach ($waypoints as $i => $otherPoint) {
            if ($i === $index) continue;
            if (haversineDistance($point['lat'], $point['lng'], $otherPoint['lat'], $otherPoint['lng']) <= $epsilon) {
                $neighbors[] = $i;
            }
        }

        if (count($neighbors) < $minPoints) {
            $noise[] = $index;
        } else {
            $clusterId = count($clusters);
            $clusters[$clusterId] = [$index];

            foreach ($neighbors as $neighborIndex) {
                if (!isset($visited[$neighborIndex])) {
                    $visited[$neighborIndex] = true;
                    $clusters[$clusterId][] = $neighborIndex;
                }
            }
        }
    }
    return $clusters;
}

$clusters = dbscan($waypoints, 2, 2);
$routes = [];

foreach ($clusters as $index => $cluster) {
    $waypointsGroup = array_map(fn($i) => $waypoints[$i], $cluster);
    if (count($waypointsGroup) < 1) {
        response('error', 'Cada ruta debe tener al menos 1 waypoint.');
    }
    
    $routeDestination = array_pop($waypointsGroup);
    $intermediates = [];
    foreach ($waypointsGroup as $point) {
        $intermediates[] = [
            "location" => [
                "latLng" => [
                    "latitude" => $point['lat'],
                    "longitude" => $point['lng']
                ]
            ]
        ];
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
        "routingPreference" => "TRAFFIC_AWARE_OPTIMAL"
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
    $routeData = $responseData['routes'][0];
    $distanceKm = $routeData['distanceMeters'] / 1000;
    $durationMinutes = intval($routeData['duration']) / 60;

    if (($maxKm && $distanceKm > $maxKm) || ($maxMinutes && $durationMinutes > $maxMinutes)) {
        response('error', "La ruta del repartidor " . ($index + 1) . " supera los límites permitidos: {$distanceKm} km y {$durationMinutes} min.");
    }

    $routes[] = $routeData;
}

response('success', 'Rutas independientes calculadas exitosamente.', $routes);
?>
