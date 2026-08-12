<?php
// Conexión a la base para pantallas públicas (sin sesión iniciada), como recuperación
// de contraseña. Mismo criterio de detección de entorno que usa conect.php.

$host = strtolower($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
$host = preg_replace('/:\d+$/', '', $host);
$host = preg_replace('/^www\./', '', $host);

$isLocal = in_array($host, ['localhost', '127.0.0.1']);
$isSandbox = str_starts_with($host, 'sandbox.');

if ($isLocal) {
    $configFile = __DIR__ . "/config_local";
} elseif ($isSandbox) {
    $configFile = __DIR__ . "/config_sandbox";
} else {
    $configFile = __DIR__ . "/config";
}

if (!is_file($configFile)) {
    die("Error: archivo de configuración no encontrado: " . basename($configFile));
}

$dbConf = json_decode(file_get_contents($configFile), true);
if (!$dbConf || !isset($dbConf[0])) {
    die("Error: configuración inválida en " . basename($configFile));
}
$dbConf = $dbConf[0];

$socket = $isLocal ? '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock' : null;

$mysqli = new mysqli(
    $dbConf['server'] ?? 'localhost',
    $dbConf['user'] ?? 'root',
    $dbConf['password'] ?? '',
    $dbConf['database'] ?? '',
    null,
    $socket
);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
