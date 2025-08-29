<?php
include_once(__DIR__ . "/../Conexion/conexion_test.php");
date_default_timezone_set("America/Argentina/Cordoba");
function limpiarTelefonoArgentino($telefono)
{
    // 1. Quitar todo lo que no sea número
    $telefono = preg_replace('/\D/', '', $telefono);

    // 2. Si empieza con 549 y tiene un 15 en el medio (ej: 549351152704154)
    if (preg_match('/^549(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2]; // queda 3512704154
    }

    // 3. Si empieza con 54 y tiene 15 en el medio (ej: 54351152704154)
    if (preg_match('/^54(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2];
    }

    // 4. Si empieza directamente con 15 (ej: 15152704154)
    if (preg_match('/^15(\d{6,8})$/', $telefono, $m)) {
        return $m[1]; // lo dejamos sin el 15
    }

    // 5. Si empieza con código de área y 15 (ej: 351152704154)
    if (preg_match('/^(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2]; // queda 3512704154
    }

    // 6. Si empieza con 549 y NO tiene 15
    if (strpos($telefono, '549') === 0) {
        return substr($telefono, 3); // quita el 549
    }

    // 7. Si empieza con 54
    if (strpos($telefono, '54') === 0) {
        return substr($telefono, 2); // quita el 54
    }

    // 8. Si ya está limpio
    return $telefono;
}

// Twilio Credenciales
$twilioSid = 'AC5ed39300253b6be33503b1a3c7461ea8';
$twilioToken = '37fa64bf87cd68806b0c036b55cb30e5'; // ⚠️ Reemplazá esto
$twilioFrom = 'whatsapp:+15557476821'; // Número de Twilio en producción
// $twilioFrom = 'whatsapp:+16672567796'; // Número de Twilio en producción
$templateSid = 'HXc357bd3fa55dd93ba666961bc90a72be'; // ID de la plantilla Twilio

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensajes'])) {
    $mensajes = json_decode($_POST['mensajes'], true);
    $enviados = 0;
    $errores = [];

    foreach ($mensajes as $msg) {
        $Destino = $msg['Destino'];
        $Origen = $msg['Origen'];
        $CodigoSeguimiento = $msg['CodigoSeguimiento'];

        $telefono = $msg['telefono']; //ACTIVAR PARA ENVIOS

        // $telefono = '351156151944';// NUMERO DE PRUEBA

        $telefono = limpiarTelefonoArgentino($telefono);

        $telefonoCompleto = '549' . $telefono;

        // Validar que no se haya enviado antes
        $check = $mysqli->query("SELECT 1 FROM twilio_Conversaciones_wp 
            WHERE Telefono = '$telefonoCompleto' 
            AND Estado = 'ConfirmacionEntrega' LIMIT 1");

        if ($check && $check->num_rows > 0) {
            echo json_encode([
                "status" => "omitido",
                "mensaje" => "⚠️ El pedido $CodigoSeguimiento ya fue notificado previamente."
            ]);
            exit;
        }

        // Armar variables para la plantilla
        $variables = [
            "1" => $Destino,
            "2" => $Origen,
            "3" => $CodigoSeguimiento
        ];
        $contentVars = json_encode($variables);

        $postFields = http_build_query([
            'To' => "whatsapp:+$telefonoCompleto",
            'From' => $twilioFrom,
            'ContentSid' => $templateSid,
            'ContentVariables' => $contentVars
        ]);

        // Enviar mensaje
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_USERPWD => "{$twilioSid}:{$twilioToken}",
        ]);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        file_put_contents(__DIR__ . "/log_envios_twilio.txt", "HTTP $httpcode $response", FILE_APPEND);

        $responseData = json_decode($response, true);
        $messageSid = isset($responseData['sid']) ? $responseData['sid'] : null;

        // Guardar en BD si hubo respuesta válida
        $ahora = date('Y-m-d H:i:s');
        $estado = 'ConfirmacionEntrega';

        $query = "INSERT INTO twilio_Conversaciones_wp (Telefono, Estado, UltimaActualizacion, CodigoSeguimiento, MessageSid) 
                  VALUES ('$telefono', '$estado', '$ahora', '$CodigoSeguimiento', '$messageSid') 
                  ON DUPLICATE KEY UPDATE Estado='$estado', UltimaActualizacion='$ahora', CodigoSeguimiento='$CodigoSeguimiento', MessageSid='$messageSid'";

        $accion = "Pendiente";
        $fechaSeleccionada = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt = $mysqli->prepare("INSERT INTO twilio_seguimiento 
            (CodigoSeguimiento, Telefono, FechaSolicitada, Estado, UltimaActualizacion) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE Estado = VALUES(Estado), UltimaActualizacion = VALUES(UltimaActualizacion)");

        $stmt->bind_param("sssss", $CodigoSeguimiento, $telefono, $fechaSeleccionada, $accion, $hora);
        $stmt->execute();
        $stmt->close();

        if ($mysqli->query($query)) {
            $enviados++;
        } else {
            $errores[] = $CodigoSeguimiento;
        }
    }

    // Resultado final
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
