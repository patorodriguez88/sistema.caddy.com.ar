<?php
// --- Utilidades internas ---
function _osm_fetch(string $url): ?array
{
    $ctx = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: SistemaTriangular/1.0 (caddy.com.ar)"
        ]
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    $json = json_decode($raw, true);
    return is_array($json) ? $json : null;
}

function _norm(string $s): string
{
    return trim(mb_strtolower($s, 'UTF-8'));
}

//  * Normaliza una dirección: devuelve la cadena “bonita” y componentes.

function osm_normalizar_direccion(string $direccion, string $localidad, string $provincia = 'Córdoba'): array
{
    $raw = trim($direccion);
    [$lat, $lon, $det] = osm_buscar_direccion_biased($direccion, $localidad, $provincia);

    if ($lat === null || $lon === null) {
        // No se pudo normalizar
        return [
            'ok'         => false,
            'normalized' => $raw,  // dejamos el original
            'components' => [
                'calle' => '',
                'numero' => '',
                'barrio' => '',
                'ciudad' => $localidad,
                'provincia' => $provincia,
                'cp' => '',
                'lat' => null,
                'lon' => null,
            ],
            'confidence' => 0,
            'raw_input'  => $raw
        ];
    }

    // Ensamblar cadena canónica
    $partes = [];
    $calle  = $det['calle'] ?? '';
    $num    = $det['numero'] ?? '';
    $barrio = $det['barrio'] ?? '';
    $ciudad = $det['ciudad'] ?? $localidad;
    $prov   = $det['provincia'] ?? $provincia;
    $cp     = $det['cp'] ?? '';

    if ($calle !== '') {
        $partes[] = trim($calle . ($num ? " $num" : ""));
    } else {
        // Si OSM no detectó la calle, mantené la que vino
        $partes[] = $raw;
    }
    if ($barrio !== '')   $partes[] = $barrio;
    if ($ciudad !== '')   $partes[] = $ciudad;
    if ($prov !== '')     $partes[] = $prov;
    if ($cp !== '')       $partes[] = $cp;

    $normalized = implode(", ", $partes);

    // score simple: suma puntos si tiene piezas clave
    $score = 0;
    if ($calle) $score += 30;
    if ($num)   $score += 25;
    if ($barrio) $score += 10;
    if ($ciudad) $score += 20;
    if ($prov)  $score += 10;
    if ($cp)    $score += 5;

    return [
        'ok'         => true,
        'normalized' => $normalized,
        'components' => [
            'calle' => $calle,
            'numero' => $num,
            'barrio' => $barrio,
            'ciudad' => $ciudad,
            'provincia' => $prov,
            'cp' => $cp,
            'lat' => $lat,
            'lon' => $lon,
        ],
        'confidence' => $score,
        'raw_input'  => $raw
    ];
}


/**
 * Busca bbox de una ciudad (para sesgar la búsqueda de calle).
 * Devuelve [minLon, minLat, maxLon, maxLat] o null.
 */
function osm_bbox_ciudad(string $city, string $state = 'Córdoba', string $countryCode = 'ar'): ?array
{
    $url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
        'city'          => $city,
        'state'         => $state,
        'countrycodes'  => $countryCode,
        'format'        => 'json',
        'limit'         => 1,
        'addressdetails' => 1,
    ]);
    $res = _osm_fetch($url);
    if (!$res || empty($res[0]['boundingbox'])) return null;
    $bb = $res[0]['boundingbox']; // [south, north, west, east]
    // Nominatim: ["minlat","maxlat","minlon","maxlon"]
    return [(float)$bb[2], (float)$bb[0], (float)$bb[3], (float)$bb[1]]; // [minLon,minLat,maxLon,maxLat]
}

/**
 * Geocodifica (lat,lon + detalles) con sesgo a ciudad y variantes de calle.
 * Devuelve [lat, lon, detalles], donde detalles incluye 'calle','numero','barrio','ciudad','provincia','cp'
 */
function osm_buscar_direccion_biased(string $direccion, string $localidad, string $provincia = 'Córdoba'): array
{
    $localidad  = trim($localidad);
    $provincia  = trim($provincia);

    // 1) separar "calle + número" si vino en un solo string
    $streetOriginal = trim($direccion);
    $street = $streetOriginal;
    $house  = '';
    if (preg_match('/^(.*?)[,\s]+(\d+)[\s,]*$/u', $streetOriginal, $m)) {
        $street = trim($m[1]);
        $house  = trim($m[2]);
    }

    // Variantes de calle
    $variantes = [];
    $variantes[] = trim($street . ($house ? " $house" : ''));
    // Prefijos comunes
    $prefijos = ['Gobernador', 'Gdor.', 'Gral.', 'General', 'Av.', 'Avenida'];
    foreach ($prefijos as $pf) {
        // si ya tiene el prefijo, no lo duplico
        if (_norm($street) !== _norm("$pf $street")) {
            $variantes[] = trim("$pf $street" . ($house ? " $house" : ""));
        }
    }
    // También probar solo calle (sin número) como último recurso
    $variantes[] = $street;

    // 2) primer intento: búsqueda estructurada estricta (sin bbox)
    $try = function (string $streetQuery) use ($localidad, $provincia) {
        $parts = [];
        // si tengo número separado uso parámetro street con todo:
        $parts['street']       = $streetQuery;
        $parts['city']         = $localidad;
        $parts['state']        = $provincia;
        $parts['countrycodes'] = 'ar';
        $parts['format']       = 'json';
        $parts['limit']        = 1;
        $parts['addressdetails'] = 1;

        $url = "https://nominatim.openstreetmap.org/search?" . http_build_query($parts);
        $js  = _osm_fetch($url);
        return $js && !empty($js[0]) ? $js[0] : null;
    };

    // 3) si no coincide ciudad, probamos con bbox de la ciudad
    $bbox = osm_bbox_ciudad($localidad, $provincia, 'ar'); // null si no se consiguió
    $try_bbox = function (string $streetQuery) use ($localidad, $provincia, $bbox) {
        if (!$bbox) return null;
        [$minLon, $minLat, $maxLon, $maxLat] = $bbox;
        $parts = [
            'street'        => $streetQuery,
            'city'          => $localidad,
            'state'         => $provincia,
            'countrycodes'  => 'ar',
            'format'        => 'json',
            'limit'         => 1,
            'addressdetails' => 1,
            'viewbox'       => "$minLon,$maxLat,$maxLon,$minLat",
            'bounded'       => 1
        ];
        $url = "https://nominatim.openstreetmap.org/search?" . http_build_query($parts);
        $js  = _osm_fetch($url);
        return $js && !empty($js[0]) ? $js[0] : null;
    };

    // función para validar que el resultado pertenece a la ciudad deseada
    $esCiudad = function (array $addr) use ($localidad) {
        $cand = [
            $addr['city']        ?? null,
            $addr['town']        ?? null,
            $addr['village']     ?? null,
            $addr['municipality'] ?? null,
            $addr['state_district'] ?? null // a veces Córdoba capital aparece raro
        ];
        $locNorm = _norm($localidad);
        foreach ($cand as $c) {
            if ($c && _norm($c) === $locNorm) return true;
        }
        return false;
    };

    // 4) recorrer variantes: primero sin bbox
    foreach ($variantes as $v) {
        if ($v === '') continue;
        $hit = $try($v);
        if ($hit && isset($hit['address']) && $esCiudad($hit['address'])) {
            return _armar_detalle($hit);
        }
    }
    // 5) luego con bbox
    foreach ($variantes as $v) {
        if ($v === '') continue;
        $hit = $try_bbox($v);
        if ($hit && isset($hit['address'])) {
            // con bbox asumimos que pertenece, igual podemos chequear
            return _armar_detalle($hit);
        }
    }

    // 6) si nada sirve, devolvemos nulos
    return [null, null, [
        'calle'     => '',
        'numero'    => '',
        'barrio'    => '',
        'ciudad'    => $localidad,
        'provincia' => $provincia,
        'cp'        => ''
    ]];
}

/** Arma el triple [lat,lon,detalles] desde un hit de Nominatim */
function _armar_detalle(array $hit): array
{
    $addr = $hit['address'] ?? [];
    $det = [
        'calle'     => $addr['road'] ?? ($addr['pedestrian'] ?? ($addr['footway'] ?? '')),
        'numero'    => $addr['house_number'] ?? '',
        'barrio'    => $addr['neighbourhood'] ?? ($addr['suburb'] ?? ''),
        'ciudad'    => $addr['city'] ?? ($addr['town'] ?? ($addr['village'] ?? ($addr['municipality'] ?? ''))),
        'provincia' => $addr['state'] ?? '',
        'cp'        => $addr['postcode'] ?? ''
    ];
    return [(float)$hit['lat'], (float)$hit['lon'], $det];
}
