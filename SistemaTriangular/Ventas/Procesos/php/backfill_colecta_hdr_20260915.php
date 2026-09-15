<?php
// Script de backfill ÚNICO Y TEMPORAL (2026-09-15) - se borra apenas corre bien.
// Repara 5 colectas de HOY (recorridos 1470 y 1500) cuyo padre quedó creado en
// TransClientes pero SIN fila en HojaDeRuta (el INSERT fallaba en silencio -
// ver fix en colecta.php, misma fecha). Sin esa fila, la colecta es invisible
// para la app de reparto. Toca EXCLUSIVAMENTE los 5 ids listados abajo.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$filas = [
    // idTransClientes, Recorrido, Posicion
    ['id' => 333370, 'recorrido' => 1470, 'posicion' => 1],
    ['id' => 333371, 'recorrido' => 1470, 'posicion' => 2],
    ['id' => 333375, 'recorrido' => 1470, 'posicion' => 3],
    ['id' => 333435, 'recorrido' => 1470, 'posicion' => 4],
    ['id' => 333347, 'recorrido' => 1500, 'posicion' => 6],
];

$pais = 'Argentina';
$asignado = 'Unica Vez';
$estado_hdr = 'Abierto';

foreach ($filas as $f) {
    $idTC = $f['id'];

    // Ya tiene HojaDeRuta? (idempotente - no duplica si se corre 2 veces)
    $chk = $mysqli->query("SELECT id FROM HojaDeRuta WHERE idTransClientes={$idTC} LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
        echo "SKIP idTransClientes={$idTC}: ya tiene HojaDeRuta.\n";
        continue;
    }

    $st = $mysqli->prepare("SELECT Fecha, Recorrido, DomicilioDestino, LocalidadDestino, ProvinciaDestino,
        ClienteDestino, TipoDeComprobante, Observaciones, Usuario, NumerodeOrden, CodigoSeguimiento,
        idClienteDestino, TelefonoDestino, NumeroComprobante
        FROM TransClientes WHERE id=? AND Eliminado=0 LIMIT 1");
    $st->bind_param('i', $idTC);
    $st->execute();
    $tc = $st->get_result()->fetch_assoc();

    if (!$tc) {
        echo "ERROR idTransClientes={$idTC}: no se encontró la fila (o está Eliminado).\n";
        continue;
    }

    $ins = $mysqli->prepare("INSERT INTO `HojaDeRuta`(
        `Fecha`,`Recorrido`,`Localizacion`,`Ciudad`,`Provincia`,`Pais`,`Cliente`,`Titulo`,`Observaciones`,`Usuario`,
        `Asignado`,`Estado`,`NumerodeOrden`,`Seguimiento`,`idCliente`,`Posicion`,`Celular`,`NumeroRepo`,`idTransClientes`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $ins->bind_param(
        'ssssssssssssisiissi',
        $tc['Fecha'],
        $tc['Recorrido'],
        $tc['DomicilioDestino'],
        $tc['LocalidadDestino'],
        $tc['ProvinciaDestino'],
        $pais,
        $tc['ClienteDestino'],
        $tc['TipoDeComprobante'],
        $tc['Observaciones'],
        $tc['Usuario'],
        $asignado,
        $estado_hdr,
        $tc['NumerodeOrden'],
        $tc['CodigoSeguimiento'],
        $tc['idClienteDestino'],
        $f['posicion'],
        $tc['TelefonoDestino'],
        $tc['NumeroComprobante'],
        $idTC
    );

    if ($ins->execute()) {
        echo "OK idTransClientes={$idTC} (Seguimiento={$tc['CodigoSeguimiento']}, Recorrido={$f['recorrido']}) -> HojaDeRuta id=" . $mysqli->insert_id . "\n";
    } else {
        echo "ERROR idTransClientes={$idTC}: " . $mysqli->error . "\n";
    }
}

echo "FIN.\n";
