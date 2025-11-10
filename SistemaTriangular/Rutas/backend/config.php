<?php
// backend/config.php

define('GOOGLE_API_KEY', 'AIzaSyCBLB94-APJT3srY0LcIXLWaHV2WscbI80'); // Coloca tu API Key acá
define('API_URL', 'https://routes.googleapis.com/directions/v2:computeRoutes');

// Opcional: Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'n455735_new');
define('DB_USER', 'n455735_prodrig');
define('DB_PASS', 'MacBook@Air');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>