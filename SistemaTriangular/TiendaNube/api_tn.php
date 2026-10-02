<?php
// Antes incluía ../ConexionBD.php (mysql_connect, no existe en PHP 8): la venta se
// cargaba pero el aviso de despacho a Tienda Nube nunca salía (error 500 al final).
require_once __DIR__ . '/../Conexion/Conexioni.php';

function fulfill($id_cliente, $order_id, $codigoSeguimiento)
{
    global $mysqli;

    $st = $mysqli->prepare("SELECT user_id_tn, token_tiendanube FROM Clientes WHERE id = ?");
    $idCliente = (int) $id_cliente;
    $st->bind_param('i', $idCliente);
    $st->execute();
    $res = $st->get_result();

    if ($res && $res->num_rows > 0) {
        $cliente = $res->fetch_assoc();
        $user_id_tn = $cliente['user_id_tn'];
        $token_tn = $cliente['token_tiendanube'];

        $data = [
            "shipping_tracking_number" => $codigoSeguimiento,
            "shipping_tracking_url" => "https://web.caddy.com.ar/seguimiento.html?codigo=" . $codigoSeguimiento,
            "notify_customer" => true
        ];

        $curl = curl_init("https://api.tiendanube.com/v1/{$user_id_tn}/orders/{$order_id}/fulfill");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authentication: bearer ' . $token_tn,
                'User-Agent: Caddy Logistics (1579)',
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }
}
