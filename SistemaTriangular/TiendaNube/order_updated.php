<?php
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Filtramos por fulfillment_status
if (isset($data['fulfillment_status']) && $data['fulfillment_status'] === 'ready_to_ship') {
    // Guardamos log o ejecutamos acción
    file_put_contents('logs/empaquetado.log', date('Y-m-d H:i:s') . " - Pedido empaquetado: " . $data['id'] . PHP_EOL, FILE_APPEND);

    // Aquí podés integrar a tu sistema logístico, por ejemplo:
    // enviarPedidoEmpaquetado($data);
}

http_response_code(200);
echo "OK";
