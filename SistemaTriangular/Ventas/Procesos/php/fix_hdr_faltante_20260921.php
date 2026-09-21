<?php
// Script temporal ÚNICO (2026-09-21) - se borra apenas corre bien.
// Reportado por Patricio: colecta 8ASM4FTVD (El Trentino, idColecta 8623,
// Recorrido 1384 = Oriana) generó TransClientes+Seguimiento OK pero sin
// fila en HojaDeRuta (Ventas/Procesos/php/colecta.php::CargarVenta) - no
// aparece en su hoja de ruta. Se inserta la fila faltante con los mismos
// valores que el proceso normal hubiera generado (mismo patrón/columnas
// que el INSERT de colecta.php).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$codigo = '8ASM4FTVD';

$resultado = ['dry_run' => $dry];

$stmt = $mysqli->prepare(
    "SELECT id, Fecha, Recorrido, DomicilioDestino, LocalidadDestino, ProvinciaDestino,
            ClienteDestino, TipoDeComprobante, Observaciones, Usuario, NumerodeOrden,
            idClienteDestino, TelefonoDestino, NumeroVenta
     FROM TransClientes WHERE CodigoSeguimiento = ? AND Eliminado = 0 LIMIT 1"
);
$stmt->bind_param('s', $codigo);
$stmt->execute();
$tc = $stmt->get_result()->fetch_assoc();
$resultado['transclientes'] = $tc;

$chk = $mysqli->prepare("SELECT COUNT(*) AS c FROM HojaDeRuta WHERE Seguimiento = ?");
$chk->bind_param('s', $codigo);
$chk->execute();
$existentes = (int) $chk->get_result()->fetch_assoc()['c'];
$resultado['filas_hdr_antes'] = $existentes;

if (!$tc) {
    $resultado['error'] = 'No se encontró TransClientes para ese código';
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($existentes > 0) {
    $resultado['nota'] = 'Ya existe una fila en HojaDeRuta, no se hace nada';
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Posición: siguiente dentro de las filas 'Abierto' de ese Recorrido (mismo
// criterio que colecta.php).
$posStmt = $mysqli->prepare(
    "SELECT MAX(Posicion) AS p FROM HojaDeRuta WHERE Recorrido = ? AND Estado = 'Abierto' AND Eliminado = 0"
);
$posStmt->bind_param('s', $tc['Recorrido']);
$posStmt->execute();
$posRow = $posStmt->get_result()->fetch_assoc();
$posicion = (int) ($posRow['p'] ?? 0) + 1;
$resultado['posicion_calculada'] = $posicion;

if (!$dry) {
    $insert = $mysqli->prepare(
        "INSERT INTO HojaDeRuta
            (Fecha, Recorrido, Localizacion, Ciudad, Provincia, Pais, Cliente, Titulo, Observaciones,
             Usuario, Asignado, Estado, NumerodeOrden, Seguimiento, idCliente, Posicion, Celular, NumeroRepo, idTransClientes)
         VALUES (?, ?, ?, ?, ?, 'Argentina', ?, ?, ?, ?, 'Unica Vez', 'Abierto', ?, ?, ?, ?, ?, ?, ?)"
    );
    $insert->bind_param(
        'sssssssssssiisii',
        $tc['Fecha'],
        $tc['Recorrido'],
        $tc['DomicilioDestino'],
        $tc['LocalidadDestino'],
        $tc['ProvinciaDestino'],
        $tc['ClienteDestino'],
        $tc['TipoDeComprobante'],
        $tc['Observaciones'],
        $tc['Usuario'],
        $tc['NumerodeOrden'],
        $codigo,
        $tc['idClienteDestino'],
        $posicion,
        $tc['TelefonoDestino'],
        $tc['NumeroVenta'],
        $tc['id']
    );
    $ok = $insert->execute();
    $resultado['insert_ok'] = $ok;
    $resultado['insert_error'] = $ok ? null : $insert->error;
    $resultado['nuevo_id'] = $ok ? $mysqli->insert_id : null;
}

$chk2 = $mysqli->prepare("SELECT id, Fecha, Recorrido, Estado, NumerodeOrden, Posicion, Seguimiento FROM HojaDeRuta WHERE Seguimiento = ?");
$chk2->bind_param('s', $codigo);
$chk2->execute();
$resultado['filas_hdr_despues'] = $chk2->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
