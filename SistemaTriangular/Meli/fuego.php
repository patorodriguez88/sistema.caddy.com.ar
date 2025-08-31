<?php
// Datos necesarios
$accessToken = 'APP_USR-3999751492306746-012712-0c546617f75952facbffca5df23c2788-360837373'; // Reemplaza con tu token de acceso
$sellerId = '360837373'; // Reemplaza con el ID del vendedor

// URL para obtener órdenes pagadas
$url = "https://api.mercadolibre.com/orders/search?seller={$sellerId}&shipping.status=ready_to_ship";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken"
]);

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "Error en la solicitud: " . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Verificar si hay resultados
if (isset($data['results']) && count($data['results']) > 0) {
    echo "Órdenes con códigos postales entre 5000 y 5024:<br><br>"; // Cambié a <br> para navegador
    
    foreach ($data['results'] as $order) {
        echo "Orden ID: " . $order['id'] . "<br>";
        
        // Consultar información de envío si existe el campo shipping_id
        if (isset($order['shipping']['id'])) {
            $shippingId = $order['shipping']['id'];
            $shippingUrl = "https://api.mercadolibre.com/shipments/{$shippingId}?access_token={$accessToken}";
            
            $chShipping = curl_init();
            curl_setopt($chShipping, CURLOPT_URL, $shippingUrl);
            curl_setopt($chShipping, CURLOPT_RETURNTRANSFER, true);
            
            $shippingResponse = curl_exec($chShipping);
            curl_close($chShipping);
            
            $shippingData = json_decode($shippingResponse, true);
            if (isset($shippingData['receiver_address']['zip_code'])) {
                $zipCode = $shippingData['receiver_address']['zip_code'];
                // Filtrar si el código postal está entre 5000 y 5024
                // if ($zipCode >= 5000 && $zipCode <= 5024) {
                    // Imprimir información solicitada
                    echo "Nombre del destinatario: " . $shippingData['receiver_name'] . "<br>";
                    echo "Shipping ID: " . $shippingId . "<br>";
                    echo "Dirección: " . $shippingData['receiver_address']['address_line'] . "<br>";
                // } else {
                    // echo "Código Postal fuera del rango (5000-5024): " . $zipCode . "<br>";
                // }
            } else {
                echo "Información de envío no disponible.<br>";
            }
        } else {
            echo "Sin información de envío.<br>";
        }
        
        // Salto de página entre resultados (cambiado para navegador)
        echo "<br>---------------------------<br><br>";
    }
} else {
    echo "No se encontraron órdenes.<br>";
}