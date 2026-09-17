<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Crea en PRODUCCIÓN las 2 tablas nuevas que ya están probadas en local y
// sandbox, imprescindibles para que el código que se está por mergear a
// main no rompa (reposiciones_dinter la lee hasta la app del repartidor
// en cada carga de tarjetas; TransProveedores_Imputaciones la lee la
// Cuenta Corriente de Proveedores en cada vista).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$resultados = [];

$resultados['reposiciones_dinter'] = $mysqli->query("CREATE TABLE IF NOT EXISTS reposiciones_dinter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  CodigoSeguimiento VARCHAR(20) NOT NULL,
  CantidadBultos INT NOT NULL DEFAULT 1,
  Usuario VARCHAR(50) NOT NULL,
  Fecha DATE NOT NULL,
  Hora TIME NOT NULL,
  TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  Impreso TINYINT(1) NOT NULL DEFAULT 0,
  Impreso_f DATE DEFAULT NULL,
  Impreso_h TIME DEFAULT NULL,
  Impreso_usuario VARCHAR(50) DEFAULT NULL,
  INDEX idx_codigo (CodigoSeguimiento),
  INDEX idx_codigo_impreso (CodigoSeguimiento, Impreso, Eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") ? 'ok' : $mysqli->error;

$resultados['TransProveedores_Imputaciones'] = $mysqli->query("CREATE TABLE IF NOT EXISTS TransProveedores_Imputaciones (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci") ? 'ok' : $mysqli->error;

// Confirmación final: existen de verdad.
$check = [];
foreach (['reposiciones_dinter', 'TransProveedores_Imputaciones'] as $t) {
    $r = $mysqli->query("SHOW TABLES LIKE '$t'");
    $check[$t] = ($r && $r->num_rows > 0);
}

echo json_encode(['resultados' => $resultados, 'existen' => $check]);
