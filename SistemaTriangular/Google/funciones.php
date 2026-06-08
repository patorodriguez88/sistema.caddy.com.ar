<?php
require_once __DIR__ . '/../Conexion/google_config.php';

function google_normalizar_direccion(string $direccion, string $localidad = '', string $provincia = ''): array
{
  $query = trim("$direccion, $localidad, $provincia, Argentina");
  $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($query) . "&key=" . GOOGLE_API_KEY_SERVER . "&language=es";

  $opts = [
    "http" => [
      "method" => "GET",
      "header" => "User-Agent: SistemaTriangular/1.0 (caddy.com.ar)"
    ]
  ];
  $context = stream_context_create($opts);
  $response = @file_get_contents($url, false, $context);

  if ($response === false) {
    return ['ok' => false, 'msg' => 'Error de conexión con Google API'];
  }

  $data = json_decode($response, true);
  if (!isset($data['results'][0])) {
    return ['ok' => false, 'msg' => 'Sin resultados'];
  }

  $r = $data['results'][0];
  $components = $r['address_components'];
  $out = [
    'ok' => true,
    'lat' => $r['geometry']['location']['lat'] ?? null,
    'lon' => $r['geometry']['location']['lng'] ?? null,
    'direccion_normalizada' => $r['formatted_address'] ?? '',
    'calle' => '',
    'numero' => '',
    'barrio' => '',
    'ciudad' => '',
    'provincia' => '',
    'cp' => '',
    'confidence' => 100,
    'fuente' => 'google'
  ];

  foreach ($components as $c) {
    if (in_array('street_number', $c['types'])) {
      $out['numero'] = $c['long_name'];
    } elseif (in_array('route', $c['types'])) {
      $out['calle'] = $c['long_name'];
    } elseif (in_array('sublocality', $c['types']) || in_array('neighborhood', $c['types'])) {
      $out['barrio'] = $c['long_name'];
    } elseif (in_array('locality', $c['types'])) {
      $out['ciudad'] = $c['long_name'];
    } elseif (in_array('administrative_area_level_1', $c['types'])) {
      $out['provincia'] = $c['long_name'];
    } elseif (in_array('postal_code', $c['types'])) {
      $out['cp'] = $c['long_name'];
    }
  }

  return $out;
}
function google_reverse_geocode(float $lat, float $lon): array
{
  $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lon}&key=" . GOOGLE_API_KEY_SERVER . "&language=es";

  $response = @file_get_contents($url);
  if ($response === false) {
    return ['ok' => false, 'msg' => 'Error de conexión con Google API'];
  }

  $data = json_decode($response, true);
  if (!isset($data['results'][0])) {
    return ['ok' => false, 'msg' => 'Sin resultados'];
  }

  $r = $data['results'][0];
  return [
    'ok' => true,
    'direccion' => $r['formatted_address'],
    'componentes' => $r['address_components']
  ];
}
function google_obtener_coordenadas(string $direccion, string $localidad = '', string $provincia = ''): array
{
  $q = trim("$direccion, $localidad, $provincia, Argentina");
  $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($q) . "&key=" . GOOGLE_API_KEY_SERVER . "&language=es";

  $response = @file_get_contents($url);
  if ($response === false) return [null, null];

  $data = json_decode($response, true);
  if (!isset($data['results'][0]['geometry']['location'])) return [null, null];

  $loc = $data['results'][0]['geometry']['location'];
  return [(float)$loc['lat'], (float)$loc['lng']];
}
