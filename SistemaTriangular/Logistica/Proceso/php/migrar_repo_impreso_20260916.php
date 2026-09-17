<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Agrega a reposiciones_dinter (sandbox) las columnas Impreso/Impreso_f/h/usuario
// que necesita el nuevo flujo de "Cantidad Repo pendiente de imprimir".
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$r = $mysqli->query("SHOW COLUMNS FROM reposiciones_dinter LIKE 'Impreso'");
$existe = $r && $r->num_rows > 0;

$resultado = 'ya existía';
if (!$existe) {
    $ok = $mysqli->query("ALTER TABLE reposiciones_dinter
        ADD COLUMN Impreso TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN Impreso_f DATE DEFAULT NULL,
        ADD COLUMN Impreso_h TIME DEFAULT NULL,
        ADD COLUMN Impreso_usuario VARCHAR(50) DEFAULT NULL,
        ADD INDEX idx_codigo_impreso (CodigoSeguimiento, Impreso, Eliminado)");
    $resultado = $ok ? 'agregada' : $mysqli->error;
}

echo json_encode(['resultado' => $resultado]);
