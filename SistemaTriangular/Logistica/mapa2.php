<?
$direccion = 'Calle Serrano 154, Madrid, España';
 
// Obtener los resultados JSON de la peticion.
$geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($direccion).'&sensor=false&key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8');
 
// Convertir el JSON en array.
$geo = json_decode($geo, true);
 
// Si todo esta bien
if ($geo['status'] = 'OK') {
	// Obtener los valores
	$latitud = $geo['results'][0]['geometry']['location']['lat'];
	$longitud = $geo['results'][0]['geometry']['location']['lng'];
}
 
echo "Latitud: ".$latitud." longitud: ".$longitud;
echo $long = $response_a->results[0]->geometry->location->lng;
?>