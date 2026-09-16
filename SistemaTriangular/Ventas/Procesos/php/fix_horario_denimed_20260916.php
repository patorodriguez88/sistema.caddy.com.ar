<?php
// Script de corrección ÚNICA Y TEMPORAL (2026-09-16) - se borra apenas corre bien.
// El operador cargó "hasta las 17hs" para DENIMED (Av. La Voz del Interior
// 8851) pero hay varios Clientes duplicados en esa dirección - el pedido
// de HOY (TransClientes id=333679, Recorrido 1227) usa puntualmente
// idClienteDestino=72661, que quedó sin el horario. Se completa a mano.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$idCliente = 72661;
$chk = $mysqli->query("SELECT id, nombrecliente, Direccion, HorarioEntregaHasta FROM Clientes WHERE id={$idCliente}")->fetch_assoc();
if (!$chk || stripos($chk['nombrecliente'], 'denimed') === false) {
    echo "ABORTA: Clientes id={$idCliente} no es DENIMED (o no existe). No se tocó nada.\n";
    exit;
}
if ($chk['HorarioEntregaHasta'] !== null) {
    echo "ABORTA: ya tiene HorarioEntregaHasta={$chk['HorarioEntregaHasta']}. No se tocó, revisar a mano.\n";
    exit;
}

$r = $mysqli->query("UPDATE Clientes SET HorarioEntregaHasta='17:00:00' WHERE id={$idCliente} LIMIT 1");
echo "Clientes id={$idCliente} ({$chk['nombrecliente']}, {$chk['Direccion']}): " . ($r ? "OK ({$mysqli->affected_rows} fila)" : "ERROR " . $mysqli->error) . "\n";
echo "FIN.\n";
