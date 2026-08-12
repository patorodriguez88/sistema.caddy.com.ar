<?php
// Archivo: Conexioni_Twilio.php
require_once __DIR__ . '/twilio_db_config.php';
$mysqli = new mysqli(TWILIO_DB_HOST, TWILIO_DB_USER, TWILIO_DB_PASS, TWILIO_DB_NAME);

if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error);
}

// OJO: ¡no pongas $mysqli->close() acá!
