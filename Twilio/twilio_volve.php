<?php

$sid = 'AC5ed39300253b6be33503b1a3c7461ea8';
$token = '9631b0e28d614e9c0f77fab2c0f2b50c';

$from = 'whatsapp:+15557646145';
$to = 'whatsapp:+5493516151944';
$messagingServiceSid = 'MGf83a812e89a61f055a37b546006a3714'; // el nuevo!

$data = [
    'To' => $to,
    'MessagingServiceSid' => $messagingServiceSid,
    'ContentSid' => $contentSid,
    'ContentVariables' => json_encode($contentVariables)
];


// Este SID lo obtenés al crear la plantilla en Twilio Console
$contentSid = 'HX32fc1305d9224e235b5993cb9187a323';  // <-- Reemplazalo por el real

$contentVariables = [
    "1" => "Pato",
    "2" => "IGALFER",
    "3" => "ABC123"
];

$data = [
    'To' => $to,
    'From' => $from,
    'ContentSid' => $contentSid,
    'ContentVariables' => json_encode($contentVariables)
];

$ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo 'Respuesta de Twilio:<br>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
}

curl_close($ch);
