<?php
// Globos de "novedad" que cada usuario ve una sola vez (Menu/js/tarea_rapida.js, por ejemplo el del
// ícono de Nueva tarea). Se guarda por usuario en la base, no en el navegador, para que no vuelva a
// aparecer en otra PC o en el celular.
//   GET  ?clave=X           -> {success, mostrar}
//   POST clave=X, visto=1   -> lo marca como visto
include_once __DIR__ . '/../../Conexion/Conexioni.php';
header('Content-Type: application/json; charset=utf-8');

$yo = (int) ($_SESSION['idusuario'] ?? 0);
$clave = substr(preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($_REQUEST['clave'] ?? ''))), 0, 50);
if (!$yo || $clave === '') {
    echo json_encode(['success' => 0, 'mostrar' => false]);
    exit;
}

$mysqli->query("CREATE TABLE IF NOT EXISTS usuarios_avisos_vistos (
    idUsuario INT NOT NULL,
    Clave VARCHAR(50) NOT NULL,
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (idUsuario, Clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $mysqli->prepare("INSERT IGNORE INTO usuarios_avisos_vistos (idUsuario, Clave) VALUES (?, ?)");
    $st->bind_param('is', $yo, $clave);
    $st->execute();
    echo json_encode(['success' => 1]);
    exit;
}

$st = $mysqli->prepare("SELECT 1 FROM usuarios_avisos_vistos WHERE idUsuario = ? AND Clave = ?");
$st->bind_param('is', $yo, $clave);
$st->execute();
echo json_encode(['success' => 1, 'mostrar' => !$st->get_result()->fetch_row()]);
