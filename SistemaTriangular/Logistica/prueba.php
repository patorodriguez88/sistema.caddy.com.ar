<?
session_start();
include_once "../Conexion/Conexioni.php";
require_once('../Google/geolocalizar.php');


$dir="San Diego 5123, Córdoba, Córdoba Argentina";

$datosmapa = geolocalizar($dir);
                  $latitud = $datosmapa[0];
                  $longitud = $datosmapa[1];

print 'latitud  '.$latitud;
print '  longitud  '.$longitud;

?>
