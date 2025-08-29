<?php
include_once "../Conexion/Conexioni.php";

// ORIGEN
$sqlOrigen = $mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1) as Direccion FROM Clientes WHERE id='$_POST[origen]'");
$ResultadoOrigen = $sqlOrigen->fetch_array(MYSQLI_ASSOC);
$Origenpost = $ResultadoOrigen['Direccion'];

// DESTINO
$sqlDestino = $mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1) as Direccion FROM Clientes WHERE id='$_POST[destino]'");
$ResultadoDestino = $sqlDestino->fetch_array(MYSQLI_ASSOC);
$Destinopost = $ResultadoDestino['Direccion'];

$Key = 'AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk'; // Tu API KEY

$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Destino = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);

$Modo = "driving";
$Lenguaje = "es-ES";
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$Origen&destinations=$Destino&mode=$Modo&language=$Lenguaje&key=$Key";

$json = file_get_contents($urlPush);
$obj = json_decode($json, true);

// Verificar que existan los datos antes de usarlos
$element = $obj['rows'][0]['elements'][0] ?? null;

if ($element && isset($element['distance']) && isset($element['duration'])) {
    $result = $element['distance']['value'];
    $result2 = $element['distance']['text'];
    $resultduration = $element['duration']['text'];
    $resultduration2 = $element['duration']['value'];

    echo json_encode([
        'success' => 1,
        'distancia' => $result,
        'origen' => $Origen,
        'destino' => $Destino,
        'duration' => $resultduration,
        'distanciat' => $result2,
        'duration2' => $resultduration2
    ]);
} else {
    // Si no hay datos válidos
    echo json_encode([
        'success' => 0,
        'error' => 'No se pudo calcular la distancia o duración entre origen y destino.',
        'origen' => $Origen,
        'destino' => $Destino,
        'google_response' => $obj
    ]);
}
