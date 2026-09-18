<?php
// Script temporal ÚNICO (2026-09-18) - se borra apenas corre bien.
// Pedido de Operaciones (vía Patricio): poner como "no controlado" las
// órdenes 17053 y 17046 para que se puedan volver a controlar.
//
// Ambas están hoy Costo_rendicion != 0 (= "controlado" para la pantalla
// Externos > Envíos) Y con las 54 filas de Externos_rendicion en
// Rendido=1 (liquidadas hoy 18/09 10:35/10:39 por mquinteros) - el código
// protege explícitamente las filas Rendido=1 de re-precio/edición, así
// que para que Operaciones pueda controlarlas de nuevo de verdad hace
// falta revertir las dos cosas (confirmado con Patricio).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$ordenes = [17053, 17046];
$in = implode(',', $ordenes);

$resultado = ['dry_run' => $dry];

$resultado['logistica_antes'] = [];
$r = $mysqli->query("SELECT id, NumerodeOrden, Costo_rendicion, Observaciones_rendicion FROM Logistica WHERE NumerodeOrden IN ($in)");
while ($f = $r->fetch_assoc()) $resultado['logistica_antes'][] = $f;

$resultado['rendicion_antes'] = [];
$r2 = $mysqli->query("SELECT idRendicion, COUNT(*) AS filas, SUM(Rendido) AS rendidas FROM Externos_rendicion WHERE idRendicion IN ($in) GROUP BY idRendicion");
while ($f = $r2->fetch_assoc()) $resultado['rendicion_antes'][] = $f;

if (!$dry) {
    $nota = "Reabierto para nuevo control (pedido de Operaciones vía Asana) el " . date('d-m-Y H:i');
    $notaEsc = $mysqli->real_escape_string($nota);

    $mysqli->query("UPDATE Logistica SET Costo_rendicion = 0.00, Observaciones_rendicion = '{$notaEsc}' WHERE NumerodeOrden IN ($in) AND Eliminado = 0");
    $resultado['logistica_actualizadas'] = $mysqli->affected_rows;

    $mysqli->query("UPDATE Externos_rendicion SET Rendido = 0, FechaRendido = NULL, UsuarioRendido = NULL, Observaciones = '{$notaEsc}' WHERE idRendicion IN ($in)");
    $resultado['rendicion_actualizadas'] = $mysqli->affected_rows;
}

$resultado['logistica_despues'] = [];
$r3 = $mysqli->query("SELECT id, NumerodeOrden, Costo_rendicion, Observaciones_rendicion FROM Logistica WHERE NumerodeOrden IN ($in)");
while ($f = $r3->fetch_assoc()) $resultado['logistica_despues'][] = $f;

$resultado['rendicion_despues'] = [];
$r4 = $mysqli->query("SELECT idRendicion, COUNT(*) AS filas, SUM(Rendido) AS rendidas FROM Externos_rendicion WHERE idRendicion IN ($in) GROUP BY idRendicion");
while ($f = $r4->fetch_assoc()) $resultado['rendicion_despues'][] = $f;

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
