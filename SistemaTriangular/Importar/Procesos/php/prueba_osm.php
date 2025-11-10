<?php
// test_osm.php
header('Content-Type: text/html; charset=utf-8');
require_once "../../../Google/funciones.php";

echo "<h2>🗺️ Test función OSM</h2>";




$dir = $_GET['direccion'] ?? 'Gobernador Justiniano Posse 1236';
$loc = $_GET['localidad'] ?? 'Córdoba';
$prov = $_GET['provincia'] ?? 'Córdoba';

echo "<form method='get'>
    <label>Dirección:</label> <input type='text' name='direccion' value='" . htmlspecialchars($dir) . "'><br><br>
    <label>Localidad:</label> <input type='text' name='localidad' value='" . htmlspecialchars($loc) . "'><br><br>
    <label>Provincia:</label> <input type='text' name='provincia' value='" . htmlspecialchars($prov) . "'><br><br>
    <button type='submit'>Buscar</button>
</form><hr>";

if ($dir) {
    $res = google_normalizar_direccion($dir, $loc, $prov);
    echo "<pre>";
    print_r($res);
    echo "</pre>";
}
?>s