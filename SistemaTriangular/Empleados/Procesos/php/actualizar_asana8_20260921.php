<?php
// Script temporal ÚNICO (2026-09-21) - se borra apenas corre bien.
// Intenta editar el story/comentario original (sólo funciona si el token
// es dueño de ese comentario); si Asana lo rechaza (comentario de otra
// persona), responde con un comentario nuevo en el mismo hilo.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$taskGid = '1217112614281359';
$storyGid = '1218596361159072';

$textoEdicion = <<<TXT
Por favor, en el presupuesto que genera de manera automatica el sistema, en el apartado de Servicio FLEX, en la parte de OPERACION dice  "LUNEA A VIERNES HABILES. INGRESO DE ENVIOS HASTA LAS 18HS, PARA LA DISTRIBUCION AL DIA HABIL SIGUIENTE", modificar para que diga "LUNES A VIERNES HABILES. COLECTA DE 12 A 14HS." Y en la ventade de ENTREGAS: que diga "DE 15 A 21 HS". Gracias

---
RESUELTO (18/09): se actualizó la plantilla del presupuesto FLEX (PropuestaFlexPdf.php).
- Operación: "Lunes a viernes habiles. Colecta de 12 a 14 hs."
- Ventana de entrega: "De 15 a 21 hs."
Ya está en producción, el próximo presupuesto FLEX que se genere sale con estos horarios.
TXT;

$textoComentarioNuevo = 'Resuelto: se actualizó la plantilla del presupuesto FLEX según lo pedido.

- Operación: "Lunes a viernes habiles. Colecta de 12 a 14 hs."
- Ventana de entrega: "De 15 a 21 hs."

Ya está en producción, el próximo presupuesto FLEX que se genere sale con estos horarios.';

function asanaCall(string $url, string $token, string $method = 'GET', $body = null) {
    $curl = curl_init();
    $headers = ['accept: application/json', 'authorization: Bearer ' . $token];
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
    ];
    if ($body !== null) {
        $headers[] = 'content-type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($curl, $opts);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return [$httpCode, json_decode($response, true)];
}

function tokenExpirado($data): bool {
    if (!isset($data['errors'])) return false;
    foreach ($data['errors'] as $e) {
        if (strpos($e['message'] ?? '', 'expired') !== false) return true;
    }
    return false;
}

$row = $mysqli->query("SELECT token, refresh_token FROM Api WHERE id=2")->fetch_assoc();
$token = $row['token'];

function refrescarSiHaceFalta(&$token, $data, $mysqli, $refreshToken) {
    if (!tokenExpirado($data)) return false;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://app.asana.com/-/oauth_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id=1204867479928301&redirect_uri=https%3A%2F%2Fwww.sistema.caddy.com.ar%2FApi%2FAsana%2Frecepcion.php&client_secret=84f466e023db6f9958ddebad539b4df6&refresh_token=' . $refreshToken,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $refreshResp = curl_exec($curl);
    curl_close($curl);
    $refreshData = json_decode($refreshResp, true);
    if (isset($refreshData['access_token'])) {
        $token = $refreshData['access_token'];
        $mysqli->query("UPDATE Api SET token='" . $mysqli->real_escape_string($token) . "' WHERE id=2");
        return true;
    }
    return false;
}

// 1) Intento editar el story original
$editUrl = "https://app.asana.com/api/1.0/stories/$storyGid";
list($code, $editResp) = asanaCall($editUrl, $token, 'PUT', ['data' => ['text' => $textoEdicion]]);
if (tokenExpirado($editResp) && refrescarSiHaceFalta($token, $editResp, $mysqli, $row['refresh_token'])) {
    list($code, $editResp) = asanaCall($editUrl, $token, 'PUT', ['data' => ['text' => $textoEdicion]]);
}

$resultado = ['edicion_http_code' => $code, 'edicion_resp' => $editResp];

if ($code >= 200 && $code < 300) {
    $resultado['modo'] = 'editado_directamente';
} else {
    // 2) Fallback: comentario nuevo en el mismo hilo
    $commentUrl = "https://app.asana.com/api/1.0/tasks/$taskGid/stories";
    list($code2, $commentResp) = asanaCall($commentUrl, $token, 'POST', ['data' => ['text' => $textoComentarioNuevo]]);
    if (tokenExpirado($commentResp) && refrescarSiHaceFalta($token, $commentResp, $mysqli, $row['refresh_token'])) {
        list($code2, $commentResp) = asanaCall($commentUrl, $token, 'POST', ['data' => ['text' => $textoComentarioNuevo]]);
    }
    $resultado['modo'] = 'comentario_nuevo_fallback';
    $resultado['comentario_http_code'] = $code2;
    $resultado['comentario_resp'] = $commentResp;
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
