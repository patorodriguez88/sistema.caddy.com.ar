<?php
// Script temporal ÚNICO (2026-09-16) - mismo patrón que los anteriores:
// a Proveedores en sandbox le faltan columnas que ya tiene producción
// (TareasAsana/TareasAsana_gid/Pago_comprobantes), por eso el warning
// "Undefined array key" que rompía el JSON de Procesos/php/funciones.php.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$tiposEsperados = [
    'TareasAsana' => "TINYINT(1) NOT NULL DEFAULT 0",
    'TareasAsana_gid' => "CHAR(50) NOT NULL DEFAULT ''",
    'Pago_comprobantes' => "INT(5) NOT NULL DEFAULT 15",
];

$resultados = [];
foreach ($tiposEsperados as $col => $tipo) {
    $r = $mysqli->query("SHOW COLUMNS FROM Proveedores LIKE '$col'");
    $existe = $r && $r->num_rows > 0;
    if ($existe) {
        $resultados[$col] = 'ya existía';
        continue;
    }
    $ok = $mysqli->query("ALTER TABLE Proveedores ADD COLUMN `$col` $tipo");
    $resultados[$col] = $ok ? 'agregada' : $mysqli->error;
}

echo json_encode(['resultados' => $resultados]);
