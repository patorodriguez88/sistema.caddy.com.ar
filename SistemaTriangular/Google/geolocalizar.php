<?php
function geolocalizar($direccion)
{
    // urlencode codifica datos de texto modificando simbolos como acentos
    $direccion = urlencode($direccion);
    // envio la consulta a Google map api
    $url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&address={$direccion}";
    // recibo la respuesta en formato Json
    $datosjson = file_get_contents($url);
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    if ($datosmapa['status'] === 'OK') {
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
        // Guardamos los datos en una matriz
        $datosmapa = array();
        array_push(
            $datosmapa,
            $latitud,
            $longitud,
            $localizacion
        );
        return $datosmapa;
    }
}
