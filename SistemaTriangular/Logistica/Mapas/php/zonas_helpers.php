<?php
// Funciones puras compartidas por zonas.php (y quien mas las necesite en este
// subsistema) - sin dispatch de $_POST, seguro de requerir desde varios
// archivos sin efectos secundarios ni headers duplicados.

// Ray-casting - puerto directo del pointInPolygon() que ya existe en
// Mapas/js/zonas.js (mismo algoritmo, mismo criterio: un punto sobre el
// borde puede dar cualquiera de los dos resultados, no importa para este uso).
function puntoEnPoligono(array $punto, array $poligono): bool
{
    $x = $punto['lng'];
    $y = $punto['lat'];
    $inside = false;
    $n = count($poligono);

    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = $poligono[$i]['lng'];
        $yi = $poligono[$i]['lat'];
        $xj = $poligono[$j]['lng'];
        $yj = $poligono[$j]['lat'];

        $intersect = (($yi > $y) !== ($yj > $y))
            && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
        if ($intersect) {
            $inside = !$inside;
        }
    }

    return $inside;
}

// Bounding box de Argentina - mismo criterio que $isValidCoord en
// Planificador/php/planificador.php y Mapas/php/orden_automatico.php,
// duplicado aca en vez de acoplar Zonas a esos archivos.
function esCoordenadaValida($lat, $lng): bool
{
    return $lat !== 0.0 && $lng !== 0.0
        && $lat <= -21.0 && $lat >= -55.0 && $lng <= -53.0 && $lng >= -75.0;
}
