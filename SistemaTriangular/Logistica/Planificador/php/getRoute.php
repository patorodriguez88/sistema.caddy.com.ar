<?php
// Calcula rutas óptimas agrupando los waypoints de un recorrido en clusters
// (uno por vehículo/repartidor) y pidiéndole a la Routes API de Google la
// ruta real para cada cluster. Portado desde Caddy_produccion/Rutas/Procesos/php/getRoute.php.
//
// Mejora sobre el original: el K-Means ya no arma los clusters solo por
// cercanía geográfica y valida la capacidad recién al final (rechazando la
// ruta entera si se pasa). Ahora la asignación de cada punto a un vehículo
// respeta desde el vector origen cuántas paradas y/o km puede tomar ese
// vehículo (info que carga el usuario en el form), así el reparto de carga
// entre vehículos es parejo desde el inicio en vez de descartar rutas enteras.

require_once __DIR__ . "/../../../Conexion/Conexioni.php";
require_once __DIR__ . "/../../../Conexion/google_config.php";
require_once __DIR__ . "/helpers.php";

header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Cordoba');

define('ROUTES_API_URL', 'https://routes.googleapis.com/directions/v2:computeRoutes');

function haversineDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Semilla de centroides por "punto más lejano" (farthest-point sampling) en vez de
// tomar los primeros k puntos: evita que clusters iniciales queden pegoteados y
// mejora la convergencia geográfica del K-Means.
function semillarCentroides(array $waypoints, int $k): array
{
    $centroides = [];
    $primero = $waypoints[array_rand($waypoints)];
    $centroides[] = ['lat' => $primero['lat'], 'lng' => $primero['lng']];

    while (count($centroides) < $k) {
        $mejorPunto = null;
        $mejorDistMin = -1;
        foreach ($waypoints as $punto) {
            $distMin = INF;
            foreach ($centroides as $c) {
                $d = haversineDistance($punto['lat'], $punto['lng'], $c['lat'], $c['lng']);
                if ($d < $distMin) $distMin = $d;
            }
            if ($distMin > $mejorDistMin) {
                $mejorDistMin = $distMin;
                $mejorPunto = $punto;
            }
        }
        $centroides[] = ['lat' => $mejorPunto['lat'], 'lng' => $mejorPunto['lng']];
    }

    return $centroides;
}

// Asigna cada waypoint al centroide más cercano que todavía tenga capacidad
// disponible (greedy por distancia global ascendente). Si algún punto no
// entra en ningún cluster con capacidad (todas llenas), se le asigna igual
// al más cercano para no perder el pedido — el límite de paradas es una
// preferencia de reparto, no un motivo para dejar un cliente sin ruta.
function distribuirConCapacidad(array $waypoints, array $centroides, array $capacidades): array
{
    $k = count($centroides);
    $clusters = array_fill(0, $k, []);
    $ocupacion = array_fill(0, $k, 0);

    $pares = [];
    foreach ($waypoints as $pi => $punto) {
        foreach ($centroides as $ci => $centroide) {
            $pares[] = [
                'pi' => $pi,
                'ci' => $ci,
                'd' => haversineDistance($punto['lat'], $punto['lng'], $centroide['lat'], $centroide['lng']),
            ];
        }
    }
    usort($pares, function ($a, $b) {
        return $a['d'] <=> $b['d'];
    });

    $asignado = array_fill(0, count($waypoints), false);
    foreach ($pares as $par) {
        if ($asignado[$par['pi']]) continue;
        $cap = $capacidades[$par['ci']] ?? null;
        if ($cap !== null && $ocupacion[$par['ci']] >= $cap) continue;
        $clusters[$par['ci']][] = $waypoints[$par['pi']];
        $ocupacion[$par['ci']]++;
        $asignado[$par['pi']] = true;
    }

    foreach ($waypoints as $pi => $punto) {
        if ($asignado[$pi]) continue;
        $mejorCi = 0;
        $mejorD = INF;
        foreach ($centroides as $ci => $centroide) {
            $d = haversineDistance($punto['lat'], $punto['lng'], $centroide['lat'], $centroide['lng']);
            if ($d < $mejorD) {
                $mejorD = $d;
                $mejorCi = $ci;
            }
        }
        $clusters[$mejorCi][] = $punto;
    }

    return $clusters;
}

function kMeansCapacitado(array $waypoints, int $k, array $capacidades, int $maxIterations = 50): array
{
    if ($k < 1) $k = 1;

    if (count($waypoints) <= $k) {
        // Menos (o igual) puntos que vehículos: como mucho un punto por cluster,
        // en vez del comportamiento anterior que amontonaba todo en un único cluster.
        $clusters = array_fill(0, $k, []);
        foreach ($waypoints as $i => $p) {
            $clusters[$i][] = $p;
        }
        return $clusters;
    }

    $centroides = semillarCentroides($waypoints, $k);
    $clusters = [];

    for ($iter = 0; $iter < $maxIterations; $iter++) {
        $clusters = distribuirConCapacidad($waypoints, $centroides, $capacidades);

        $nuevosCentroides = [];
        foreach ($clusters as $ci => $grupo) {
            if (count($grupo)) {
                $nuevosCentroides[] = [
                    'lat' => array_sum(array_column($grupo, 'lat')) / count($grupo),
                    'lng' => array_sum(array_column($grupo, 'lng')) / count($grupo),
                ];
            } else {
                $nuevosCentroides[] = $centroides[$ci];
            }
        }

        if ($nuevosCentroides === $centroides) break;
        $centroides = $nuevosCentroides;
    }

    return $clusters;
}

function findWaypointData($lat, $lng, $originalData, $tolerance = 0.0001)
{
    foreach ($originalData as $item) {
        if (abs($item['lat'] - $lat) < $tolerance && abs($item['lng'] - $lng) < $tolerance) {
            return $item;
        }
    }
    return [
        'nombrecliente' => 'Cliente',
        'CodigoSeguimiento' => 'Sin código',
    ];
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) response('error', 'No se recibieron datos.');

$originalData = isset($input['waypointsData']) ? $input['waypointsData'] : [];
$fixedOrigin = ['lat' => -31.444994776141503, 'lng' => -64.1779408896999];
$waypoints = isset($input['waypoints']) ? $input['waypoints'] : [];
$driversCount = isset($input['driversCount']) ? max(1, intval($input['driversCount'])) : 1;
$maxKm = isset($input['maxKm']) ? $input['maxKm'] : null;
$maxMinutes = isset($input['maxMinutes']) ? floatval($input['maxMinutes']) : null;
$startTime = isset($input['startTime']) ? $input['startTime'] : '08:00';

// Capacidad de paradas por vehículo: array paralelo a driversCount (índice = vehículo),
// o un único número aplicado a todos. Alimenta directamente al K-Means, no es solo
// una validación posterior.
$maxStopsInput = isset($input['maxStops']) ? $input['maxStops'] : null;
$capacidades = array_fill(0, $driversCount, null);
if (is_array($maxStopsInput)) {
    foreach ($maxStopsInput as $i => $v) {
        if ($i < $driversCount && is_numeric($v) && intval($v) > 0) {
            $capacidades[$i] = intval($v);
        }
    }
} elseif (is_numeric($maxStopsInput) && intval($maxStopsInput) > 0) {
    $capacidades = array_fill(0, $driversCount, intval($maxStopsInput));
}

if (empty($waypoints)) response('error', 'No se recibieron waypoints.');

if (!defined('GOOGLE_API_KEY_SERVER')) {
    response('error', 'No está configurada la clave de Google (GOOGLE_API_KEY_SERVER).');
}

$currentDate = date('Y-m-d');
$currentTimestamp = time();
$departureTimestamp = strtotime("$currentDate $startTime");
if ($departureTimestamp < $currentTimestamp) {
    $departureTimestamp = strtotime("+1 day", $departureTimestamp);
}
$departureTimeISO = gmdate("Y-m-d\TH:i:s\Z", $departureTimestamp);

$clusters = kMeansCapacitado($waypoints, $driversCount, $capacidades);
$routeColors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997'];
$routes = [];
$routeSummary = [];
$warnings = [];

foreach ($clusters as $index => $group) {
    if (count($group) < 1) continue;

    $destination = array_pop($group);
    $intermediates = [];
    foreach ($group as $p) {
        $intermediates[] = [
            "location" => ["latLng" => [
                "latitude" => floatval($p['lat']),
                "longitude" => floatval($p['lng']),
            ]],
        ];
    }

    $body = [
        "origin" => ["location" => ["latLng" => ["latitude" => $fixedOrigin['lat'], "longitude" => $fixedOrigin['lng']]]],
        "destination" => ["location" => ["latLng" => ["latitude" => $destination['lat'], "longitude" => $destination['lng']]]],
        "intermediates" => $intermediates,
        "travelMode" => "DRIVE",
        "routingPreference" => "TRAFFIC_AWARE_OPTIMAL",
        "departureTime" => $departureTimeISO,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ROUTES_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . GOOGLE_API_KEY_SERVER,
        'X-Goog-FieldMask: routes.legs,routes.duration,routes.distanceMeters,routes.polyline.encodedPolyline',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        $warnings[] = "❌ Ruta " . ($index + 1) . ": error de conexión con Google (" . $error . ").";
        continue;
    }

    $data = json_decode($respuesta, true);
    if (!isset($data['routes'][0])) {
        $motivo = $data['error']['message'] ?? 'la API no devolvió una ruta válida';
        $warnings[] = "❌ Ruta " . ($index + 1) . ": " . $motivo;
        continue;
    }

    $route = $data['routes'][0];
    $distance = 0;
    $duration = 0;
    foreach ($route['legs'] as $leg) {
        $distance += $leg['distanceMeters'] ?? 0;
        if (isset($leg['duration']) && preg_match('/(\d+)s/', $leg['duration'], $m)) {
            $duration += intval($m[1]);
        }
    }
    $distanceKm = round($distance / 1000, 2);
    $durationMin = round($duration / 60);
    $formattedTime = sprintf("%02d:%02d", floor($durationMin / 60), $durationMin % 60);

    $routes[] = $route;
    $routeSummary[] = [
        "routeIndex" => $index + 1,
        "color" => $routeColors[$index % count($routeColors)],
        "distance_km" => $distanceKm,
        "waypoints_count" => count($route['legs']),
        "estimated_time" => $formattedTime,
    ];
}

$responseRoutes = [];
$responseSummary = [];

foreach ($routeSummary as $i => $summary) {
    if (!isset($routes[$i]) || !isset($clusters[$i])) {
        $warnings[] = "❗ Datos incompletos para la ruta " . ($i + 1) . ", no se puede procesar.";
        continue;
    }

    $distanceKm = $summary['distance_km'];
    list($h, $m) = explode(':', $summary['estimated_time']);
    $durationMin = $h * 60 + $m;

    $limiteKm = null;
    if (is_array($maxKm)) {
        if (isset($maxKm[$i]) && is_numeric($maxKm[$i])) {
            $limiteKm = floatval($maxKm[$i]);
        }
    } elseif (is_numeric($maxKm)) {
        $limiteKm = floatval($maxKm);
    }

    $limiteMin = is_numeric($maxMinutes) ? floatval($maxMinutes) : null;

    if (($limiteKm !== null && $distanceKm > $limiteKm) || ($limiteMin !== null && $durationMin > $limiteMin)) {
        $motivos = [];
        if ($limiteKm !== null && $distanceKm > $limiteKm) {
            $motivos[] = "📏 distancia permitida: {$limiteKm} km, pero se necesitan {$distanceKm} km";
        }
        if ($limiteMin !== null && $durationMin > $limiteMin) {
            $motivos[] = "⏱️ tiempo permitido: {$limiteMin} min, pero se necesitan {$durationMin} min";
        }
        $warnings[] = "⚠️ Ruta {$summary['routeIndex']} no se pudo generar:\n" . implode("\n", $motivos);
        continue;
    }

    try {
        $waypointsEnriquecidos = array_map(function ($punto) use ($originalData) {
            return findWaypointData($punto['lat'], $punto['lng'], $originalData);
        }, $clusters[$i]);

        $routes[$i]['waypointsData'] = array_values($waypointsEnriquecidos);
        $responseRoutes[] = $routes[$i];
        $responseSummary[] = $summary;
    } catch (Exception $e) {
        $warnings[] = "❌ Error al enriquecer ruta {$summary['routeIndex']}: " . $e->getMessage();
        continue;
    }
}

if (count($responseRoutes) === 0) {
    response('partial_success', 'No se pudo generar ninguna ruta válida.', [
        'routes' => [],
        'summary' => [],
        'warnings' => $warnings,
    ]);
}

response(count($warnings) > 0 ? 'partial_success' : 'success', 'Rutas calculadas.', [
    'routes' => $responseRoutes,
    'summary' => $responseSummary,
    'warnings' => $warnings,
]);
