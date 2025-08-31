<?
require_once('../Google/geolocalizar.php');

function console_log($data) {
    printf('<script>console.log(%s);</script>', json_encode($data));
}

$Direccion="Justiniano Posse 1236, Córdoba, Argentina";
$datosmapa = geolocalizar($Direccion);
                  $latitud = $datosmapa[0];
                  $longitud = $datosmapa[1];
// print $Direccion;
echo $datosmapa[0];
echo $datosmapa[1];
// echo $datosmapa[2];
// print $datosmapa[3];
// print $datosmapa[4];
console_log($datosmapa);


?>

