<?php
// Script temporal ÚNICO (2026-09-18) - se borra apenas corre bien.
// Tarea Asana "Corregir visualización de asientos contables al imprimir"
// (bug secundario, sin relación con el cruce ya corregido): Tesoreria.
// NombreCuenta es varchar(35), y PlanDeCuentas.NombreCuenta (el nombre
// real/completo de cada cuenta) es varchar(50), con nombres reales de
// hasta 45 caracteres - al guardar, MySQL truncaba en silencio (modo no
// estricto) cualquier nombre de cuenta más largo que 35 caracteres. Ej:
// "COMISIONES E IMPUESTOS DE TARJETAS" en vez de "...DE CREDITO".
//
// Fix:
// 1) Se ensancha Tesoreria.NombreCuenta a varchar(60) (deja margen sobre
//    los 45 caracteres reales más largos hoy en PlanDeCuentas) - evita que
//    vuelva a pasar.
// 2) Se corrigen los registros históricos ya truncados: se detectan filas
//    activas donde el nombre guardado es exactamente de 35 caracteres Y es
//    un prefijo exacto del nombre real (PlanDeCuentas) para ese código de
//    cuenta - sin ambigüedad, siempre la misma cuenta/nombre real.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;

$resultado = ['dry_run' => $dry];

// --- Paso 1: estado actual de la columna ---
$colAntes = $mysqli->query("SHOW COLUMNS FROM Tesoreria LIKE 'NombreCuenta'")->fetch_assoc();
$resultado['columna_antes'] = $colAntes['Type'] ?? null;

// --- Paso 2: detectar filas truncadas (solo para preview / conteo) ---
$sqlDetectar = "
SELECT t.Cuenta, t.NombreCuenta AS guardado, p.NombreCuenta AS nombre_real, COUNT(*) AS filas
FROM Tesoreria t
JOIN PlanDeCuentas p ON p.Cuenta = t.Cuenta
WHERE t.Eliminado = 0
  AND t.NombreCuenta <> p.NombreCuenta
  AND p.NombreCuenta LIKE CONCAT(t.NombreCuenta, '%')
  AND CHAR_LENGTH(t.NombreCuenta) = 35
GROUP BY t.Cuenta, t.NombreCuenta, p.NombreCuenta
ORDER BY filas DESC";
$r = $mysqli->query($sqlDetectar);
$grupos = [];
$totalFilas = 0;
while ($fila = $r->fetch_assoc()) {
    $grupos[] = $fila;
    $totalFilas += (int)$fila['filas'];
}
$resultado['grupos_encontrados'] = $grupos;
$resultado['total_filas_truncadas'] = $totalFilas;

if (!$dry) {
    // Paso 1 real: ensanchar la columna.
    $mysqli->query("ALTER TABLE Tesoreria MODIFY NombreCuenta VARCHAR(60) NULL");
    $colDespues = $mysqli->query("SHOW COLUMNS FROM Tesoreria LIKE 'NombreCuenta'")->fetch_assoc();
    $resultado['columna_despues'] = $colDespues['Type'] ?? null;

    // Paso 2 real: corregir los datos históricos truncados.
    $infoNota = "Corregido nombre de cuenta truncado (bug varchar(35) - tarea Asana) el " . date('d-m-Y H:i');
    $infoNotaEsc = $mysqli->real_escape_string($infoNota);
    $sqlUpdate = "
        UPDATE Tesoreria t
        JOIN PlanDeCuentas p ON p.Cuenta = t.Cuenta
        SET t.NombreCuenta = p.NombreCuenta,
            t.InfoABM = CONCAT(IFNULL(t.InfoABM,''), ' | {$infoNotaEsc}')
        WHERE t.Eliminado = 0
          AND t.NombreCuenta <> p.NombreCuenta
          AND p.NombreCuenta LIKE CONCAT(t.NombreCuenta, '%')
          AND CHAR_LENGTH(t.NombreCuenta) = 35";
    $mysqli->query($sqlUpdate);
    $resultado['filas_actualizadas'] = $mysqli->affected_rows;
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
