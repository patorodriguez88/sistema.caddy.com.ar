<?php
// Establecer la zona horaria
date_default_timezone_set("America/Argentina/Cordoba");
include_once(__DIR__ . "/../Conexion/conexion_test.php");

header('Content-Type: application/json');

// Leer el cuerpo JSON
$data = json_decode(file_get_contents("php://input"), true);

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($data["CodigoSeguimiento"], $data["Nombre"], $data["Telefono"])
) {
    $codigo = $data["CodigoSeguimiento"];
    $nombre = $data["Nombre"];
    $telefono = $data["Telefono"];

    $accountSid = 'AC5ed39300253b6be33503b1a3c7461ea8';
    $authToken = '9631b0e28d614e9c0f77fab2c0f2b50c';
    $from = 'whatsapp:+14155238886'; // Número de Twilio
    $to = "whatsapp:$telefono"; // Ahora usa el teléfono dinámicamente

    // Cuerpo del mensaje armado correctamente
    $body = "¡Hola *$nombre*! 👋 Somos Caddy 🚚

Tu pedido *$codigo* está listo para ser entregado. ¿Cómo querés recibirlo?

1️⃣ Hoy en el horario habitual  
2️⃣ Reprogramar la entrega  
3️⃣ Cancelar pedido  

Por favor, respondé con el número de opción. Gracias ✨";

    $url = "https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json";

    $data = [
        'From' => $from,
        'To' => $to,
        'Body' => $body
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo json_encode(["success" => false, "error" => $err]);
    } else {
        echo json_encode(["success" => true, "response" => json_decode($response, true)]);
        // Incluir conexión a la base de datos

        // ✅ Solo si se envió exitosamente el WhatsApp, registramos el estado en Conversaciones_wp
        $estado = 'ConfirmacionEntrega';

        $telefonoLimpio = str_replace('whatsapp:', '', $telefono);
        $telefonoCorto = '+' . $telefono; // si $telefono es 5493516151944

        $query = "
            INSERT INTO twilio_Conversaciones_wp (Telefono, Estado, UltimaActualizacion) 
            VALUES ('$telefonoCorto', '$estado', NOW()) 
            ON DUPLICATE KEY UPDATE Estado='$estado', UltimaActualizacion=NOW()
        ";

        if ($mysqli->query($query)) {
            echo "✅ Estado actualizado correctamente en Conversaciones_wp.";
        } else {
            echo "❌ Error al actualizar Conversaciones_wp: " . $mysqli->error;
        }
    }
} else {
    echo json_encode(["success" => false, "error" => "Faltan parámetros o no es POST"]);
}
