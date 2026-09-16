<?php
// Script temporal ÚNICO (2026-09-16) - se borra apenas corre bien.
// Crea la tabla TransProveedores_Imputaciones (feature "Asociar Pago" -
// tarea Asana de Agustina). Mismo diseño que Ctasctes_Imputaciones
// (Clientes), adaptado a Proveedores.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$sql = "CREATE TABLE IF NOT EXISTS TransProveedores_Imputaciones (
  id INT(11) NOT NULL AUTO_INCREMENT,
  idProveedor INT(11) NOT NULL,
  idMovimientoOrigen INT(11) NOT NULL,
  idMovimientoDestino INT(11) NOT NULL,
  TipoOrigen ENUM('FACTURA','ND','OTRO') NOT NULL,
  TipoDestino ENUM('PAGO','NC','ANTICIPO','RETENCION') NOT NULL,
  Importe DECIMAL(12,2) NOT NULL,
  Fecha DATETIME NOT NULL,
  Usuario VARCHAR(100) DEFAULT NULL,
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_proveedor (idProveedor),
  KEY idx_mov_origen (idMovimientoOrigen),
  KEY idx_mov_destino (idMovimientoDestino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$ok = $mysqli->query($sql);

$colsCheck = $mysqli->query("SHOW COLUMNS FROM TransProveedores LIKE 'img'");
$tieneImg = $colsCheck && $colsCheck->num_rows > 0;

$alterOk = null;
$alterError = null;
if (!$tieneImg) {
    // Mismo gap que había en el dump local: el código (funciones.js,
    // "row.img==1") ya espera esta columna. Se agrega si falta, sin tocar
    // nada más.
    $alterOk = $mysqli->query("ALTER TABLE TransProveedores ADD COLUMN img TINYINT(1) DEFAULT 0");
    if (!$alterOk) $alterError = $mysqli->error;
    $colsCheck2 = $mysqli->query("SHOW COLUMNS FROM TransProveedores LIKE 'img'");
    $tieneImg = $colsCheck2 && $colsCheck2->num_rows > 0;
}

echo json_encode([
    'success' => $ok ? 1 : 0,
    'error' => $ok ? null : $mysqli->error,
    'TransProveedores_tiene_img' => $tieneImg,
    'alter_ejecutado' => $alterOk !== null,
    'alter_error' => $alterError,
]);
