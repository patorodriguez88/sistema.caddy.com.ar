<?php
// Archivo: Conexioni_Twilio.php
$mysqli = new mysqli("localhost", "n455735_prodrig", "MacBook@Air", "n455735_new");

if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error);
}

// OJO: ¡no pongas $mysqli->close() acá!
