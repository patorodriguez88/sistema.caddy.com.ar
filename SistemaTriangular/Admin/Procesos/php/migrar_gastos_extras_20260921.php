<?php
// Script temporal ÚNICO (2026-09-21) - se borra apenas corre bien.
// Migración para "Gastos Extras": tabla nueva (totalmente separada de
// Tesoreria/PlanDeCuentas/IvaCompras, no puede aparecer en Mayor de
// Cuentas ni Libro de IVA) + columna de permiso en usuarios (mismo patrón
// que usuarios.PuedeEliminarPagos - ver commit 2dafc48b) para restringir
// la pantalla a usuario por usuario, sin tocar Nivel ni roles.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$resultado = ['dry_run' => $dry];

$tablaExiste = $mysqli->query("SHOW TABLES LIKE 'GastosExtras'")->num_rows > 0;
$columnaExiste = $mysqli->query("SHOW COLUMNS FROM usuarios LIKE 'PuedeGestionarGastosExtras'")->num_rows > 0;

$resultado['antes'] = [
    'tabla_GastosExtras_existe' => $tablaExiste,
    'columna_usuarios_existe' => $columnaExiste,
];

if (!$dry) {
    if (!$tablaExiste) {
        $mysqli->query("
            CREATE TABLE GastosExtras (
                id INT AUTO_INCREMENT PRIMARY KEY,
                Fecha DATE NOT NULL,
                Categoria VARCHAR(20) NOT NULL COMMENT 'Personal | Logistica | Generales | Financieros',
                Descripcion VARCHAR(200) NOT NULL,
                Importe DECIMAL(12,2) NOT NULL,
                Observaciones VARCHAR(200) NULL,
                Usuario VARCHAR(50) NOT NULL,
                Eliminado TINYINT(1) NOT NULL DEFAULT 0,
                InfoABM VARCHAR(200) NULL,
                TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_fecha (Fecha),
                KEY idx_categoria (Categoria)
            )
        ");
        if ($mysqli->error) {
            $resultado['error_tabla'] = $mysqli->error;
        }
    }
    if (!$columnaExiste) {
        $mysqli->query("ALTER TABLE usuarios ADD COLUMN PuedeGestionarGastosExtras TINYINT(1) NOT NULL DEFAULT 0");
        if ($mysqli->error) {
            $resultado['error_columna'] = $mysqli->error;
        }
    }

    $mysqli->query("UPDATE usuarios SET PuedeGestionarGastosExtras = 1 WHERE Usuario IN ('aoviedo','coviedo')");
    $resultado['usuarios_actualizados'] = $mysqli->affected_rows;
}

$resultado['despues'] = [
    'tabla_GastosExtras_existe' => $mysqli->query("SHOW TABLES LIKE 'GastosExtras'")->num_rows > 0,
    'columna_usuarios_existe' => $mysqli->query("SHOW COLUMNS FROM usuarios LIKE 'PuedeGestionarGastosExtras'")->num_rows > 0,
];

// OJO: en un dry-run ANTES de aplicar la migración la columna todavía no
// existe, así que esta consulta solo puede correr si ya existe (columna
// creada en un run anterior) o si acabamos de aplicarla (!$dry) - si no,
// mysqli (modo estricto por default en PHP 8) tira excepción no capturada
// y la request muere con 500 antes de imprimir nada.
if ($columnaExiste || !$dry) {
    $chk = $mysqli->query("SELECT id, Usuario, PuedeGestionarGastosExtras FROM usuarios WHERE Usuario IN ('aoviedo','coviedo')");
    $resultado['usuarios_habilitados'] = $chk ? $chk->fetch_all(MYSQLI_ASSOC) : null;
} else {
    $resultado['usuarios_habilitados'] = null; // columna todavía no existe (dry-run inicial)
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
