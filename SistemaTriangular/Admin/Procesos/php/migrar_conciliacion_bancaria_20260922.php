<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Migracion para "Conciliacion Bancaria" (Asana, pedido de Patricio +
// Agustina):
//  1) Tesoreria.NumeroTrans: int(11) -> varchar(30). Hasta ahora solo
//     guardaba el numero de transferencia (siempre numerico); ahora el
//     campo "Numero de Referencia" se muestra para CUALQUIER forma de
//     pago (Proveedores), y puede no ser puramente numerico.
//  2) Tesoreria.FechaConciliado: date -> datetime NULL. Hace falta guardar
//     la HORA en que se concilio, no solo la fecha (hoy ademas es NOT NULL
//     sin default, lo que fuerza un valor "0000-00-00" implicito cuando no
//     se pasa - se pasa a NULL para que represente de verdad "todavia no
//     conciliado").
//  3) Tesoreria.UsuarioConciliado: varchar(27) NOT NULL -> NULL (mismo
//     motivo que el punto anterior, sin cambiar el largo).
//  4) Tesoreria.idConciliacionBancaria: nueva columna (INT NULL) - linkea
//     cada fila conciliada a la "corrida" de conciliacion a la que
//     pertenece (ver tabla nueva ConciliacionBancaria), en vez de un
//     simple flag suelto.
//  5) Tabla nueva ConciliacionBancaria: la "corrida" en si (cuenta +
//     rango + saldo inicial/final + estado Abierta/Cerrada + quien/cuando
//     la abrio y cerro).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$resultado = ['dry_run' => $dry];

function colInfo($mysqli, $tabla, $col) {
    $res = $mysqli->query("SHOW COLUMNS FROM `{$tabla}` LIKE '{$col}'");
    return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
}

$numeroTransAntes = colInfo($mysqli, 'Tesoreria', 'NumeroTrans');
$fechaConciliadoAntes = colInfo($mysqli, 'Tesoreria', 'FechaConciliado');
$usuarioConciliadoAntes = colInfo($mysqli, 'Tesoreria', 'UsuarioConciliado');
$idConciliacionAntes = colInfo($mysqli, 'Tesoreria', 'idConciliacionBancaria');
$tablaConciliacionAntes = $mysqli->query("SHOW TABLES LIKE 'ConciliacionBancaria'")->num_rows > 0;

$resultado['antes'] = [
    'NumeroTrans' => $numeroTransAntes['Type'] ?? null,
    'FechaConciliado' => $fechaConciliadoAntes['Type'] ?? null,
    'UsuarioConciliado' => $usuarioConciliadoAntes['Type'] ?? null,
    'idConciliacionBancaria_existe' => $idConciliacionAntes !== null,
    'tabla_ConciliacionBancaria_existe' => $tablaConciliacionAntes,
];

if (!$dry) {
    $errores = [];

    if (($numeroTransAntes['Type'] ?? '') !== 'varchar(30)') {
        $mysqli->query("ALTER TABLE Tesoreria MODIFY NumeroTrans VARCHAR(30) NULL DEFAULT NULL");
        if ($mysqli->error) $errores['NumeroTrans'] = $mysqli->error;
    }
    if (($fechaConciliadoAntes['Type'] ?? '') !== 'datetime') {
        $mysqli->query("ALTER TABLE Tesoreria MODIFY FechaConciliado DATETIME NULL DEFAULT NULL");
        if ($mysqli->error) $errores['FechaConciliado'] = $mysqli->error;
    }
    if ($usuarioConciliadoAntes && $usuarioConciliadoAntes['Null'] === 'NO') {
        $mysqli->query("ALTER TABLE Tesoreria MODIFY UsuarioConciliado VARCHAR(27) NULL DEFAULT NULL");
        if ($mysqli->error) $errores['UsuarioConciliado'] = $mysqli->error;
    }
    if ($idConciliacionAntes === null) {
        $mysqli->query("ALTER TABLE Tesoreria ADD COLUMN idConciliacionBancaria INT NULL DEFAULT NULL AFTER UsuarioConciliado");
        if ($mysqli->error) $errores['idConciliacionBancaria'] = $mysqli->error;
    }
    if (!$tablaConciliacionAntes) {
        $mysqli->query("
            CREATE TABLE ConciliacionBancaria (
                id INT AUTO_INCREMENT PRIMARY KEY,
                Cuenta VARCHAR(20) NOT NULL,
                Desde DATE NOT NULL,
                Hasta DATE NOT NULL,
                SaldoInicial DECIMAL(14,2) NOT NULL DEFAULT 0,
                SaldoFinal DECIMAL(14,2) NULL,
                Estado VARCHAR(10) NOT NULL DEFAULT 'Abierta' COMMENT 'Abierta | Cerrada',
                UsuarioApertura VARCHAR(50) NOT NULL,
                FechaApertura DATETIME NOT NULL,
                UsuarioCierre VARCHAR(50) NULL,
                FechaCierre DATETIME NULL,
                Observaciones VARCHAR(255) NULL,
                KEY idx_cuenta (Cuenta),
                KEY idx_estado (Estado)
            )
        ");
        if ($mysqli->error) $errores['ConciliacionBancaria'] = $mysqli->error;
    }

    if ($errores) {
        $resultado['errores'] = $errores;
    }
}

$numeroTransDespues = colInfo($mysqli, 'Tesoreria', 'NumeroTrans');
$fechaConciliadoDespues = colInfo($mysqli, 'Tesoreria', 'FechaConciliado');
$usuarioConciliadoDespues = colInfo($mysqli, 'Tesoreria', 'UsuarioConciliado');
$idConciliacionDespues = colInfo($mysqli, 'Tesoreria', 'idConciliacionBancaria');
$tablaConciliacionDespues = $mysqli->query("SHOW TABLES LIKE 'ConciliacionBancaria'")->num_rows > 0;

$resultado['despues'] = [
    'NumeroTrans' => $numeroTransDespues['Type'] ?? null,
    'FechaConciliado' => $fechaConciliadoDespues['Type'] ?? null,
    'UsuarioConciliado' => $usuarioConciliadoDespues['Type'] ?? null,
    'idConciliacionBancaria_existe' => $idConciliacionDespues !== null,
    'tabla_ConciliacionBancaria_existe' => $tablaConciliacionDespues,
];

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
