<?php
// Copiar este archivo como config.php y completar con los valores reales.
// config.php está en .gitignore — nunca se sube al repositorio.

define('GOOGLE_API_KEY', 'TU_GOOGLE_API_KEY_AQUI');
define('API_URL', 'https://routes.googleapis.com/directions/v2:computeRoutes');

// Opcional: Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'TU_BASE_DE_DATOS');
define('DB_USER', 'TU_USUARIO_DB');
define('DB_PASS', 'TU_PASSWORD_DB');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
