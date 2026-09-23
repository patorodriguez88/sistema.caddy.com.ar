<?php
define('ALLOW_NO_SESSION', true);
header('Content-Type: application/json; charset=utf-8');
$wsdl = __DIR__ . '/../../../afip.php/src/Afip_res/ws_sr_padron_a5-production.wsdl';
$resultado = ['existe' => file_exists($wsdl)];
if ($resultado['existe']) {
    $resultado['mtime'] = date('Y-m-d', filemtime($wsdl));
    $contenido = file_get_contents($wsdl);
    preg_match_all('/<(?:wsdl:)?operation name="([^"]+)"/', $contenido, $m);
    $resultado['operaciones'] = array_values(array_unique($m[1]));
    // Location del endpoint declarado en el WSDL
    preg_match('/soap:address location="([^"]+)"/', $contenido, $loc);
    $resultado['soap_location'] = $loc[1] ?? null;
}
echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
