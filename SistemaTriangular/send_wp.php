<?php
require '../twilio-php-main/src/Twilio/autoload.php';

use Twilio\Rest\Client;

// Your Account SID and Auth Token from twilio.com/console
// In production, these should be environment variables. E.g.:
// $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

// A Twilio number you own with SMS capabilities
$twilio_number = "whatsapp:+14155238886";

$client = new Client($account_sid, $auth_token);
$client->messages->create(
    // Where to send a text message (your cell phone?)
    'whatsapp:+5493516151944',
    array(
        'from' => $twilio_number,
        'body' => 'I sent this message in under 10 minutes!'
    )
);
