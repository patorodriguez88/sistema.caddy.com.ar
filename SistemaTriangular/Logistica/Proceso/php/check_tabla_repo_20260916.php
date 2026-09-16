<?php
// Script temporal ÚNICO (2026-09-16) - chequea si reposiciones_dinter
// existe en esta base (duda de Patricio: ¿hay que crearla en producción?).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$r = $mysqli->query("SHOW TABLES LIKE 'reposiciones_dinter'");
$existiaAntes = $r && $r->num_rows > 0;

$creada = false;
$error = null;
if (!$existiaAntes) {
    $sql = "CREATE TABLE reposiciones_dinter (
      id INT AUTO_INCREMENT PRIMARY KEY,
      CodigoSeguimiento VARCHAR(20) NOT NULL,
      CantidadBultos INT NOT NULL DEFAULT 1,
      Usuario VARCHAR(50) NOT NULL,
      Fecha DATE NOT NULL,
      Hora TIME NOT NULL,
      TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      Eliminado TINYINT(1) NOT NULL DEFAULT 0,
      INDEX idx_codigo (CodigoSeguimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $ok = $mysqli->query($sql);
    $creada = (bool) $ok;
    $error = $ok ? null : $mysqli->error;
}

$r2 = $mysqli->query("SHOW TABLES LIKE 'reposiciones_dinter'");
$existeAhora = $r2 && $r2->num_rows > 0;

echo json_encode(['existia_antes' => $existiaAntes, 'creada_ahora' => $creada, 'error' => $error, 'existe_ahora' => $existeAhora]);
