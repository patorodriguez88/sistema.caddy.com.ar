<?php
// Helpers compartidos para hablar con la API de Meli usando el token propio
// de cada Cliente. Antes el client_id/client_secret de la app de Meli estaba
// repetido en texto plano en orders.php y en refresh_token.php (este ultimo,
// un script de prueba suelto) - se centraliza aca para que exista en un solo
// lugar del repo.

const MELI_CLIENT_ID = '3999751492306746';
const MELI_CLIENT_SECRET = 'w5SMpJwEFlRxuLf5H8hCAyFxutn1jrMr';

/** Refresca el access_token de un cliente y lo persiste en Clientes.
 * Devuelve el nuevo access_token, o null si Meli no pudo renovarlo. */
function meliRefreshAccessToken(mysqli $mysqli, int $customerId, string $refreshToken): ?string
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadolibre.com/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => MELI_CLIENT_ID,
            'client_secret' => MELI_CLIENT_SECRET,
            'refresh_token' => $refreshToken,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $result = json_decode((string)curl_exec($curl), true);
    curl_close($curl);

    if (empty($result['access_token'])) {
        return null;
    }

    $nuevoAccess = (string)$result['access_token'];
    $nuevoRefresh = (string)($result['refresh_token'] ?? $refreshToken);

    $stmt = $mysqli->prepare("UPDATE Clientes SET access_token=?, refresh_token=? WHERE id=?");
    $stmt->bind_param("ssi", $nuevoAccess, $nuevoRefresh, $customerId);
    $stmt->execute();

    return $nuevoAccess;
}

/**
 * Trae el shipment de Meli usando el token guardado del cliente, con refresh
 * automatico si el access_token vencio.
 * Devuelve ['ok'=>true,'shipment'=>array,'customer'=>array] o
 * ['ok'=>false,'error'=>string].
 */
function meliShipmentLookup(mysqli $mysqli, int $customerId, string $shipmentsId): array
{
    $stC = $mysqli->prepare("SELECT id, nombrecliente, user_id, access_token, refresh_token FROM Clientes WHERE id=? LIMIT 1");
    $stC->bind_param("i", $customerId);
    $stC->execute();
    $cliente = $stC->get_result()->fetch_assoc();

    if (!$cliente || empty($cliente['user_id'])) {
        return ['ok' => false, 'error' => 'CLIENTE_SIN_TOKEN'];
    }

    $pedirShipment = function (string $accessToken) use ($shipmentsId) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadolibre.com/shipments/' . $shipmentsId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [$httpCode, json_decode((string)$response, true)];
    };

    [$httpCode, $shipment] = $pedirShipment($cliente['access_token']);

    if ($httpCode === 401) {
        $nuevoAccess = meliRefreshAccessToken($mysqli, (int)$cliente['id'], (string)$cliente['refresh_token']);
        if ($nuevoAccess === null) {
            return ['ok' => false, 'error' => 'REFRESH_TOKEN_FALLO'];
        }
        [$httpCode, $shipment] = $pedirShipment($nuevoAccess);
    }

    if ($httpCode !== 200 || !is_array($shipment) || empty($shipment['id'])) {
        return ['ok' => false, 'error' => 'NO_ENCONTRADO'];
    }

    return ['ok' => true, 'shipment' => $shipment, 'customer' => $cliente];
}

/**
 * Sin saber de antemano de que cliente es el envio: prueba el access_token
 * ACTUAL de todos los clientes con Meli vinculado contra /shipments/{id} en
 * paralelo (curl_multi) y se queda con el primero que responda 200 - Meli
 * devuelve 401/403 para un token que no es dueño (ni comprador) de ese envio,
 * asi que el que "abre" es el cliente origen. No refresca tokens vencidos en
 * esta pasada (seria N refreshes secuenciales, mata la latencia) - si
 * ninguno prende, el que pide la busqueda cae al selector manual, que si
 * refresca.
 * Devuelve ['ok'=>true,'shipment'=>...,'customer'=>...] o
 * ['ok'=>false,'error'=>'NO_ENCONTRADO_EN_NINGUN_CLIENTE'].
 */
function meliShipmentAutoDetect(mysqli $mysqli, string $shipmentsId): array
{
    $res = $mysqli->query("SELECT id, nombrecliente, user_id, access_token, refresh_token FROM Clientes WHERE user_id<>'' AND access_token<>''");
    $clientes = [];
    while ($fila = $res->fetch_assoc()) {
        $clientes[] = $fila;
    }
    if (empty($clientes)) {
        return ['ok' => false, 'error' => 'NO_ENCONTRADO_EN_NINGUN_CLIENTE'];
    }

    $mh = curl_multi_init();
    $handles = [];

    foreach ($clientes as $idx => $cliente) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.mercadolibre.com/shipments/' . $shipmentsId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $cliente['access_token']],
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$idx] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $encontrado = null;
    foreach ($handles as $idx => $ch) {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 200 && $encontrado === null) {
            $shipment = json_decode((string)curl_multi_getcontent($ch), true);
            if (is_array($shipment) && !empty($shipment['id'])) {
                $encontrado = ['shipment' => $shipment, 'customer' => $clientes[$idx]];
            }
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    if ($encontrado === null) {
        return ['ok' => false, 'error' => 'NO_ENCONTRADO_EN_NINGUN_CLIENTE'];
    }

    return ['ok' => true, 'shipment' => $encontrado['shipment'], 'customer' => $encontrado['customer']];
}
