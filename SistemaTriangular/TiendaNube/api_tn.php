<?php
include_once "../ConexionBD.php";

function fulfill($id_cliente, $order_id, $codigoSeguimiento)
{
    global $mysqli;

    $sql = "SELECT Clientes.user_id_tn, token_tiendanube FROM Clientes WHERE id = '$id_cliente';";
    $res = $mysqli->query($sql);

    if ($res && $res->num_rows > 0) {
        $cliente = $res->fetch_assoc();
        $user_id_tn = $cliente['user_id_tn'];
        $token_tn = $cliente['token_tiendanube'];

        $data = [
            "shipping_tracking_number" => $codigoSeguimiento,
            "shipping_tracking_url" => "https://www.caddy.com.ar/seguimiento.html?codigo=" . $codigoSeguimiento,
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
