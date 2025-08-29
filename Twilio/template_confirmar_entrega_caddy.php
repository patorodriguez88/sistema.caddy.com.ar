<?php
include_once(__DIR__ . "/../Conexion/conexion_test.php");
date_default_timezone_set("America/Argentina/Cordoba");

// Twilio credentials
$accountSid = 'AC5ed39300253b6be33503b1a3c7461ea8';
$authToken  = '9631b0e28d614e9c0f77fab2c0f2b50c';

//SANDBOX
// $from       = 'whatsapp:+14155238886'; //sandbox
$contentSid = 'HX32fc1305d9224e235b5993cb9187a323'; //sandbox

//PRODUCCION
$from       = 'whatsapp:+15557646145'; //produccion
// $contentSid = 'HX2d602e14280051336d268635c0ae31c4';//produccion

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensajes'])) {
    $mensajes = json_decode($_POST['mensajes'], true);
    $enviados = 0;
    $errores = [];

    foreach ($mensajes as $msg) {
        $Destino = $msg['Destino'];
        $Origen = $msg['Origen'];
        $CodigoSeguimiento = $msg['CodigoSeguimiento'];

        // Para pruebas siempre usamos el mismo número (temporal)
        $telefono = '3516481380'; //maxi
        // $telefono = '3516151944'; //pato

        $telefono_twilio = '+549' . preg_replace('/[^0-9]/', '', $telefono);
        $to = 'whatsapp:' . $telefono_twilio;

        $variables = [
            "1" => $Destino,
            "2" => $Origen,
            "3" => $CodigoSeguimiento
        ];

        $data = [
            'To'               => $to,
            'From'             => $from,
            'ContentSid'       => $contentSid,
            'ContentVariables' => json_encode($variables),
            'StatusCallback'   => 'https://www.caddy.com.ar/SistemaTriangular/Twilio/twilio_status_callback.php'
        ];

        // 🚫 Evitar reenvío si ya está en ConfirmacionEntrega
        $check = $mysqli->query("SELECT 1 FROM twilio_Conversaciones_wp 
                         WHERE Telefono = '$telefono_twilio' 
                         AND Estado = 'ConfirmacionEntrega' 
                         LIMIT 1");

        if ($check && $check->num_rows > 0) {
            echo json_encode([
                "status" => "omitido",
                "mensaje" => "⚠️ El pedido $CodigoSeguimiento ya fue notificado previamente."
            ]);
            exit;
        }


        // Enviar mensaje por Twilio
        $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $responseData = json_decode($response, true);
        $messageSid = $responseData['sid'];



        // Registrar en BD
        $ahora = date('Y-m-d H:i:s');
        $estado = 'ConfirmacionEntrega';

        // Primer insert/update
        $query = "INSERT INTO twilio_Conversaciones_wp (Telefono, Estado, UltimaActualizacion, CodigoSeguimiento,MessageSid) 
                  VALUES ('$telefono_twilio', '$estado', '$ahora','$CodigoSeguimiento','$messageSid') 
                  ON DUPLICATE KEY UPDATE Estado='$estado', UltimaActualizacion='$ahora', CodigoSeguimiento='$CodigoSeguimiento', MessageSid='$messageSid'";


        // Segundo insert/update
        $stmt = $mysqli->prepare("INSERT INTO twilio_seguimiento 
            (CodigoSeguimiento, Telefono, FechaSolicitada, Estado, UltimaActualizacion) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE Estado = VALUES(Estado), UltimaActualizacion = VALUES(UltimaActualizacion)");

        $accion = "Pendiente";
        $fechaSeleccionada = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt->bind_param("sssss", $CodigoSeguimiento, $telefono_twilio, $fechaSeleccionada, $accion, $hora);
        $stmt->execute();
        $stmt->close();

        if ($mysqli->query($query)) {
            $enviados++;
        } else {
            $errores[] = $CodigoSeguimiento;
        }
    }

    // Respuesta final JSON
    if ($enviados > 0 && empty($errores)) {
        echo json_encode([
            "status" => "ok",
            "mensaje" => "✅ Se notificó correctamente a $enviados cliente(s) vía WhatsApp."
        ]);
    } elseif ($enviados > 0 && !empty($errores)) {
        echo json_encode([
            "status" => "partial",
            "mensaje" => "⚠️ Se enviaron $enviados mensajes, pero hubo errores con: " . implode(", ", $errores)
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "❌ No se pudo enviar ningún mensaje. Reintentar."
        ]);
    }
    exit;
} else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "❌ No se recibieron datos válidos."
    ]);
    exit;
}
