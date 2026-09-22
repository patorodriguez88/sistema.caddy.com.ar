<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Tabla para loguear (en la base, no en el filesystem del hosting, al que
// no tenemos acceso fácil) cualquier excepción de mysqli que se dispare
// dentro de colecta.php::CargarVenta - root cause real de que las colectas
// de Oriana (8ASM4FTVD, 21/9) y Dynamic (RVHHXOAMF, 22/9) quedaran con
// TransClientes+Seguimiento creados pero sin HojaDeRuta: mysqli en modo
// estricto (default desde PHP 8.1, nunca se configuró distinto en
// Conexioni.php) tira excepción en vez de devolver false ante un error de
// query - el "if (!$resultado)" que ya existía nunca se llegaba a evaluar,
// la request moría con 500 en blanco antes de loguear ni avisar nada.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$resultado = ['dry_run' => $dry];

$tablaExiste = $mysqli->query("SHOW TABLES LIKE 'ErroresColecta'")->num_rows > 0;
$resultado['antes'] = ['tabla_existe' => $tablaExiste];

if (!$dry && !$tablaExiste) {
    $mysqli->query("
        CREATE TABLE ErroresColecta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            idColecta INT NULL,
            CodigoSeguimiento VARCHAR(10) NULL,
            idTransClientes INT NULL,
            Recorrido INT NULL,
            Paso VARCHAR(50) NULL COMMENT 'en que INSERT/query fallo',
            MysqlErrno INT NULL,
            MysqlError VARCHAR(500) NULL,
            Query_ TEXT NULL,
            KEY idx_codigo (CodigoSeguimiento)
        )
    ");
    if ($mysqli->error) { $resultado['error_tabla'] = $mysqli->error; }
}

$resultado['despues'] = ['tabla_existe' => $mysqli->query("SHOW TABLES LIKE 'ErroresColecta'")->num_rows > 0];
echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
