<?php
// Script de corrección ÚNICA Y TEMPORAL (2026-09-16) - se borra apenas corre bien.
// Recorrido 1258: el chofer reportó no ver 5 colectas. Se confirmó que las
// 5 existen en TransClientes (creadas hoy 08:05 por ngambardella) pero
// NUNCA se les generó la fila en HojaDeRuta - por eso no aparecen como
// card en la app (Paneles filtra por HojaDeRuta, no por TransClientes
// directo). Se insertan las 5 filas faltantes, con los mismos campos y
// criterio que usa Ventas/Procesos/php/ConfirmarVenta.php al confirmar
// una venta nueva.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$filas = [
    // idTransClientes, CodigoSeguimiento, Localizacion, Ciudad, Provincia, Cliente, Usuario, NumerodeOrden, idCliente, NumeroRepo
    [333672, 'PHH2V3ZWL', 'Rivadavia 375, Centro, Ciudad de Córdoba, Córdoba, Argentina', '', 'Córdoba', 'EL Trentino S.A.', 'ngambardella', 17153, 19470, 60947],
    [333673, '9M6B5XMYT', 'Copina 1336, Córdoba, Argentina', '', 'Córdoba', 'Cyma Joyas', 'ngambardella', 17153, 71294, 60948],
    [333674, 'XBXVVBABH', 'Lino Spilimbergo 6392, Ciudad de Córdoba, Provincia de Córdoba', 'Córdoba', 'Córdoba', 'DYNAMIC', 'ngambardella', 17153, 72103, 60949],
    [333675, 'TPH1DMV0A', 'Jerónimo Cortés 23, Córdoba, Provincia de Córdoba, Argentina', '', 'Córdoba', 'VENEX SA', 'ngambardella', 17153, 61733, 60950],
    [333676, 'KRWUWHRVA', 'Avenida Estrada 80, Ciudad de Córdoba, Provincia de Córdoba, Argentina', '', 'Córdoba', 'HENZY', 'ngambardella', 17153, 52483, 60951],
];

$fecha = '2026-09-16';
$recorrido = '1258';

$insert = $mysqli->prepare(
    "INSERT INTO HojaDeRuta (Fecha, Recorrido, Localizacion, Ciudad, Provincia, Pais, Cliente, Titulo, Observaciones, Usuario, Asignado, Estado, NumerodeOrden, Seguimiento, idCliente, NumeroRepo, ImporteCobranza, idTransClientes)
     VALUES (?, ?, ?, ?, ?, 'Argentina', ?, 'Remito', '', ?, 'Unica Vez', 'Abierto', ?, ?, ?, ?, 0, ?)"
);

foreach ($filas as $f) {
    [$idTC, $cs, $localizacion, $ciudad, $provincia, $cliente, $usuario, $numOrden, $idCliente, $numeroRepo] = $f;

    // Verificación defensiva: no duplicar si por algún motivo ya existe.
    $chk = $mysqli->query("SELECT id FROM HojaDeRuta WHERE idTransClientes={$idTC} LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
        echo "SALTEADO {$cs} (idTC={$idTC}): ya tiene HojaDeRuta.\n";
        continue;
    }
    $chkTC = $mysqli->query("SELECT CodigoSeguimiento FROM TransClientes WHERE id={$idTC}")->fetch_assoc();
    if (!$chkTC || $chkTC['CodigoSeguimiento'] !== $cs) {
        echo "ABORTA {$cs}: TransClientes id={$idTC} no coincide (o no existe). No se tocó.\n";
        continue;
    }

    $insert->bind_param(
        'sssssssisiii',
        $fecha, $recorrido, $localizacion, $ciudad, $provincia, $cliente, $usuario, $numOrden, $cs, $idCliente, $numeroRepo, $idTC
    );
    $ok = $insert->execute();
    echo ($ok ? "OK " : "ERROR ") . $cs . " (idTC={$idTC})" . ($ok ? "" : ": " . $insert->error) . "\n";
}

echo "FIN.\n";
