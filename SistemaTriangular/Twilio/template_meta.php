<?php
include_once(__DIR__ . "/../Conexion/conexion_test.php");
date_default_timezone_set("America/Argentina/Cordoba");

// Meta Credenciales
$url = 'https://graph.facebook.com/v22.0/693760453811414/messages';
$token = 'EAATYDpELZALEBO45ttdniZC7cQiTuCBI1nQ8UAInQP8HHPZAE8k3aCT2FcNZCMOXcRnxJRXJKS47aS2BZBOIDcw78wFrkOLHuSo7kOApEnzQZAPOvciZAfign8mbnClaweHCl6xso0KD11ZCv79UwnIoZAOZBhypBPdYNhyiZAwkrFkEYd9oeLyYwZDZD';

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
        // $telefono = '3516481380'; //maxi
        // $telefono = '3516151944'; //pato
        $telefono = $msg['telefono'];
        $telefono_meta = '54' . $telefono;

        $variables = [
            "1" => $Destino,
            "2" => $Origen,
            "3" => $CodigoSeguimiento
        ];


        // 🚫 Evitar reenvío si ya está en ConfirmacionEntrega
        $check = $mysqli->query("SELECT 1 FROM twilio_Conversaciones_wp 
                         WHERE Telefono = '$telefono_meta' 
                         AND Estado = 'ConfirmacionEntrega' 
                         LIMIT 1");

        if ($check && $check->num_rows > 0) {
            echo json_encode([
                "status" => "omitido",
                "mensaje" => "⚠️ El pedido $CodigoSeguimiento ya fue notificado previamente."
            ]);
            exit;
        }
        $payload = [
            "messaging_product" => "whatsapp",
            "to" => $telefono_meta,
            "type" => "template",
            "template" => [
                "name" => "template_meta",
                "language" => [
                    "code" => "es_AR"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $Destino],
                            ["type" => "text", "text" => $Origen],
                            ["type" => "text", "text" => $CodigoSeguimiento]
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // En lugar de echo directo:
        file_put_contents(__DIR__ . "/log_envios_meta.txt", "HTTP $httpcode\n$response\n\n", FILE_APPEND);

        $responseData = json_decode($response, true);
        $messageSid = $responseData['messages'][0]['id'];

        curl_close($ch);

        // Registrar en BD
        $ahora = date('Y-m-d H:i:s');
        $estado = 'ConfirmacionEntrega';

        // Primer insert/update
        $query = "INSERT INTO twilio_Conversaciones_wp (Telefono, Estado, UltimaActualizacion, CodigoSeguimiento,MessageSid) 
                  VALUES ('$telefono_meta', '$estado', '$ahora','$CodigoSeguimiento','$messageSid') 
                  ON DUPLICATE KEY UPDATE Estado='$estado', UltimaActualizacion='$ahora', CodigoSeguimiento='$CodigoSeguimiento', MessageSid='$messageSid'";


        // Segundo insert/update
        $stmt = $mysqli->prepare("INSERT INTO twilio_seguimiento 
            (CodigoSeguimiento, Telefono, FechaSolicitada, Estado, UltimaActualizacion) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE Estado = VALUES(Estado), UltimaActualizacion = VALUES(UltimaActualizacion)");

        $accion = "Pendiente";
        $fechaSeleccionada = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt->bind_param("sssss", $CodigoSeguimiento, $telefono_meta, $fechaSeleccionada, $accion, $hora);
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
