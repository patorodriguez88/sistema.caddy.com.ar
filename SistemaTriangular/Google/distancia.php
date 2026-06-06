<?php
header('Content-Type: application/json; charset=utf-8');
include_once "../Conexion/Conexioni.php";

$idOrigen  = (int)($_POST['origen']  ?? 0);
$idDestino = (int)($_POST['destino'] ?? 0);

if (!$idOrigen || !$idDestino) {
    echo json_encode(['success' => 0, 'error' => 'Parámetros inválidos']);
    exit;
}

function dur_texto(int $seg): string {
    $h = (int)floor($seg / 3600);
    $m = (int)ceil(($seg % 3600) / 60);
    return $h > 0 ? "{$h} h {$m} min" : "{$m} min";
}

// ═══════════════════════════════════════════════════════════════
// 1. CACHÉ — TransClientes con el mismo par origen-destino
// ═══════════════════════════════════════════════════════════════
$stmt = $mysqli->prepare("
    SELECT google_km, google_time
    FROM   TransClientes
    WHERE  idClienteOrigen  = ?
      AND  idClienteDestino = ?
      AND  google_km  > 0
      AND  google_time > 0
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param('ii', $idOrigen, $idDestino);
$stmt->execute();
$cacheRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cacheRow) {
    $km  = (float)$cacheRow['google_km'];
    $seg = (int)$cacheRow['google_time'];
    echo json_encode([
        'success'    => 1,
        'distancia'  => $km,
        'duration'   => dur_texto($seg),
        'duration2'  => $seg,
        'distanciat' => round($km / 1000, 1) . ' km',
        'fuente'     => 'cache',
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════
// 2. OSRM — si ambos clientes tienen coordenadas guardadas
// ═══════════════════════════════════════════════════════════════
$stmt2 = $mysqli->prepare("
    SELECT id,
           IF(DireccionPredeterminadas = 0, Direccion, Direccion1) AS Direccion,
           Latitud, Longitud
    FROM   Clientes
    WHERE  id IN (?, ?)
");
$stmt2->bind_param('ii', $idOrigen, $idDestino);
$stmt2->execute();
$rows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

$clientes = [];
foreach ($rows as $r) {
    $clientes[(int)$r['id']] = $r;
}

$orig = $clientes[$idOrigen] ?? null;
$dest = $clientes[$idDestino] ?? null;

$oLat = isset($orig['Latitud'])  ? (float)$orig['Latitud']  : 0;
$oLon = isset($orig['Longitud']) ? (float)$orig['Longitud'] : 0;
$dLat = isset($dest['Latitud'])  ? (float)$dest['Latitud']  : 0;
$dLon = isset($dest['Longitud']) ? (float)$dest['Longitud'] : 0;

if ($oLat && $oLon && $dLat && $dLon) {
    $osrmUrl = "https://router.project-osrm.org/route/v1/driving/{$oLon},{$oLat};{$dLon},{$dLat}?overview=false";
    $ctx = stream_context_create(['http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: SistemaTriangular/1.0 (caddy.com.ar)\r\n",
        'timeout' => 5,
    ]]);
    $raw = @file_get_contents($osrmUrl, false, $ctx);

    if ($raw !== false) {
        $osrm = json_decode($raw, true);
        if (!empty($osrm['routes'][0])) {
            $dm  = (int)$osrm['routes'][0]['distance'];
            $seg = (int)$osrm['routes'][0]['duration'];
            echo json_encode([
                'success'    => 1,
                'distancia'  => $dm,
                'duration'   => dur_texto($seg),
                'duration2'  => $seg,
                'distanciat' => round($dm / 1000, 1) . ' km',
                'fuente'     => 'osrm',
            ]);
            exit;
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// 3. FALLBACK — Google Distance Matrix API
// ═══════════════════════════════════════════════════════════════
if (!$orig || !$dest) {
    echo json_encode(['success' => 0, 'error' => 'Clientes no encontrados']);
    exit;
}

$Key     = 'AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk';
$Origen  = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $orig['Direccion']);
$Destino = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $dest['Direccion']);
$urlG    = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$Origen}&destinations={$Destino}&mode=driving&language=es-ES&key={$Key}";

$raw = @file_get_contents($urlG);
if ($raw !== false) {
    $obj  = json_decode($raw, true);
    $elem = $obj['rows'][0]['elements'][0] ?? null;
    if ($elem && isset($elem['distance'], $elem['duration'])) {
        echo json_encode([
            'success'    => 1,
            'distancia'  => $elem['distance']['value'],
            'duration'   => $elem['duration']['text'],
            'duration2'  => $elem['duration']['value'],
            'distanciat' => $elem['distance']['text'],
            'fuente'     => 'google',
        ]);
        exit;
    }
}

echo json_encode(['success' => 0, 'error' => 'No se pudo calcular la distancia entre origen y destino']);
