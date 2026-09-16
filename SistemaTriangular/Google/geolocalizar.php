<?php
require_once __DIR__ . '/../Conexion/google_config.php';

function geolocalizar($direccion)
{
    $direccion = urlencode($direccion);
    // FIX (2026-09-16, reportado con IGALFER: localidades random tipo
    // "Worblaufen"/"Madrid" para direcciones de Córdoba): esta consulta no
    // tenía ningún sesgo geográfico - una dirección corta o ambigua podía
    // matchear con un lugar de cualquier parte del mundo. region=ar (Caddy
    // solo opera en Argentina) inclina el resultado hacia acá sin
    // restringirlo del todo. Sigue siendo responsabilidad de quien llama
    // validar la provincia devuelta antes de confiar en la localidad
    // (ver AgregarRepoVentaWeb.php).
    $url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . GOOGLE_API_KEY_SERVER . "&address={$direccion}&language=es&region=ar";
    // recibo la respuesta en formato Json
    $datosjson = @file_get_contents($url);
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    if (isset($datosmapa['status']) && $datosmapa['status'] === 'OK') {
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];

        // Del geocode sacamos tambien el CP y la localidad REALES (address_components).
        // Los sistemas de origen mandan a veces CP/Ciudad basura (ej. San Francisco
        // guardado como X5016) - esto deja el dato correcto para quien lo quiera usar.
        // Se devuelve en indices nuevos (3,4) para NO romper a los ~25 callers que
        // solo leen [0] lat y [1] lng.
        $codigoPostal = '';
        $localidad    = '';
        $provincia    = '';
        $componentes  = $datosmapa['results'][0]['address_components'] ?? [];
        foreach ($componentes as $comp) {
            $tipos = $comp['types'] ?? [];
            if (in_array('postal_code', $tipos, true) && $codigoPostal === '') {
                $codigoPostal = $comp['long_name'] ?? '';
            }
            if ($localidad === '' && (in_array('locality', $tipos, true) || in_array('administrative_area_level_2', $tipos, true))) {
                $localidad = $comp['long_name'] ?? '';
            }
            if (in_array('administrative_area_level_1', $tipos, true) && $provincia === '') {
                $provincia = $comp['long_name'] ?? '';
            }
        }

        return array($latitud, $longitud, $localizacion, $codigoPostal, $localidad, $provincia);
    }
}
