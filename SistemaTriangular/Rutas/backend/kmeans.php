<?php

function kMeansClustering($waypoints, $numClusters) {
    if ($numClusters <= 1 || count($waypoints) <= $numClusters) {
        // Si hay un solo chofer o menos waypoints que clusters, se asignan de forma directa
        return array_chunk($waypoints, ceil(count($waypoints) / $numClusters));
    }

    // Inicializar centroides aleatoriamente
    // $centroids = array_rand($waypoints, $numClusters);
    $clusters = [];
    $centroids = [];
    $keys = array_rand($waypoints, $numClusters); // Obtiene claves aleatorias
    
    if ($numClusters == 1) {
        $keys = [$keys]; // array_rand() devuelve un valor directo si $numClusters == 1
    }
    
    foreach ($keys as $key) {
        $centroids[] = $waypoints[$key]; // Guardamos los valores, no las claves
    }
    
    for ($i = 0; $i < 10; $i++) { // Iteraciones para convergencia
        // Reiniciar clusters
        $clusters = array_fill(0, $numClusters, []);

        // Asignar cada waypoint al cluster más cercano
        foreach ($waypoints as $waypoint) {
            $closest = null;
            $minDistance = PHP_INT_MAX;

            foreach ($centroids as $index => $centroidKey) {
                $centroid = $waypoints[$centroidKey];
                $distance = haversineDistance($waypoint['lat'], $waypoint['lng'], $centroid['lat'], $centroid['lng']);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $closest = $index;
                }
            }

            $clusters[$closest][] = $waypoint;
        }

        // Recalcular centroides
        $newCentroids = [];
        foreach ($clusters as $key => $cluster) {
            $latSum = 0;
            $lngSum = 0;
            $count = count($cluster);

            foreach ($cluster as $point) {
                $latSum += $point['lat'];
                $lngSum += $point['lng'];
            }

            if ($count > 0) {
                $newCentroids[$key] = ['lat' => $latSum / $count, 'lng' => $lngSum / $count];
            }
        }

        // Si los centroides no cambian mucho, terminamos el proceso
        if ($centroids === $newCentroids) {
            break;
        }

        $centroids = $newCentroids;
    }

    return $clusters;
}

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Radio de la Tierra en metros

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dLat = $lat2 - $lat1;
    $dLon = $lon2 - $lon1;

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos($lat1) * cos($lat2) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Devuelve la distancia en metros
}
?>