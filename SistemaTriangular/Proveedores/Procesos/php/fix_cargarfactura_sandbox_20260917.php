<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Bug reportado: "Ingresar Comprobante" en Proveedores (sandbox) tira
// SyntaxError (JSON corrupto por un warning/fatal de PHP). Mismo patrón de
// toda esta sesión: a sandbox le faltan columnas que ya tiene producción,
// esta vez en TransProveedores/IvaCompras (las usa cargarfactura()).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$fixes = [
    'TransProveedores' => [
        'gid_asana' => "VARCHAR(50) DEFAULT NULL",
        'InfoABM' => "VARCHAR(150) DEFAULT NULL",
        'img' => "TINYINT(1) DEFAULT 0",
    ],
    'IvaCompras' => [
        'idTransProveedores' => "INT(11) DEFAULT NULL",
        'idCliente' => "INT(11) NOT NULL DEFAULT 0",
        'asana_gid' => "CHAR(20) NOT NULL DEFAULT ''",
    ],
];

$resultados = [];
foreach ($fixes as $tabla => $columnas) {
    foreach ($columnas as $col => $tipo) {
        $r = $mysqli->query("SHOW COLUMNS FROM `$tabla` LIKE '$col'");
        $existe = $r && $r->num_rows > 0;
        if ($existe) {
            $resultados["$tabla.$col"] = 'ya existía';
            continue;
        }
        $ok = $mysqli->query("ALTER TABLE `$tabla` ADD COLUMN `$col` $tipo");
        $resultados["$tabla.$col"] = $ok ? 'agregada' : $mysqli->error;
    }
}

echo json_encode(['resultados' => $resultados]);
