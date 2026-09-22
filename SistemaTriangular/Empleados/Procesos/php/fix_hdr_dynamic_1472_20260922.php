<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Reproduce exactamente el INSERT de HojaDeRuta que Ventas/Procesos/php/
// colecta.php::CargarVenta hace normalmente (mismos campos/valores fijos:
// Pais='Argentina', Asignado='Unica Vez', Estado='Abierto') para la
// colecta de DYNAMIC (id 8646) en el recorrido 1472 de Marcelo Peña, que
// quedó con TransClientes+Seguimiento creados pero sin fila en
// HojaDeRuta (mismo bug reportado ayer con 8ASM4FTVD/Oriana).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$codigo_seguimiento = 'RVHHXOAMF';
$recorrido = 1472;
$idTransClientes = 334431;

$out = ['dry_run' => $dry];

$yaExiste = $mysqli->query("SELECT id FROM HojaDeRuta WHERE Seguimiento='{$codigo_seguimiento}' AND Eliminado=0");
$out['ya_existe_antes'] = $yaExiste ? $yaExiste->fetch_all(MYSQLI_ASSOC) : null;

if (!empty($out['ya_existe_antes'])) {
    $out['abortado'] = 'Ya existe una fila HojaDeRuta con ese Seguimiento, no se inserta de nuevo.';
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Datos tomados de TransClientes.id=334431 (fila padre de la colecta),
// confirmados vía diagnóstico (mismos que usaría colecta.php):
$fecha = '2026-09-22';
$domiciliodestino = 'Gobernador Justiniano Posse 1236, Córdoba, Provincia de Córdoba, Argentina';
$localidaddestino = '';
$provinciadestino = 'Córdoba';
$pais = 'Argentina';
$clientedestino = 'Wepoint';
$tipodecomprobante = 'GUIA DE CARGA';
$observaciones = 'Colecta 22-09-2026 (17) envíos';
$usuario = 'ngambardella';
$asignado = 'Unica Vez';
$estado_hdr = 'Abierto';
$nordenlogistica = 17205;
$idclientedestino = 18587;
$telefonodestino = '';
$NRepo = 61181;

$SQL_ORDEN = $mysqli->query("SELECT MAX(Posicion) as Posicion FROM HojaDeRuta WHERE Recorrido='{$recorrido}' AND Estado='Abierto' AND Eliminado='0'");
$DATO_ORDEN = $SQL_ORDEN->fetch_array(MYSQLI_ASSOC);
$orden = trim($DATO_ORDEN['Posicion']) + 1;
$out['posicion_calculada'] = $orden;

$sqlInsert = "INSERT INTO `HojaDeRuta`(
    `Fecha`,`Recorrido`,`Localizacion`,`Ciudad`,`Provincia`,`Pais`,`Cliente`,`Titulo`,`Observaciones`,`Usuario`,
    `Asignado`,`Estado`,`NumerodeOrden`,`Seguimiento`,`idCliente`,`Posicion`,`Celular`,`NumeroRepo`,`idTransClientes`) VALUES (
    '{$fecha}','{$recorrido}','{$domiciliodestino}','{$localidaddestino}','{$provinciadestino}','{$pais}',
    '{$clientedestino}','{$tipodecomprobante}','{$observaciones}','{$usuario}','{$asignado}','{$estado_hdr}','{$nordenlogistica}',
    '{$codigo_seguimiento}','{$idclientedestino}','{$orden}','{$telefonodestino}','{$NRepo}',{$idTransClientes})";

$out['sql'] = $sqlInsert;

if (!$dry) {
    $ok = $mysqli->query($sqlInsert);
    $out['insert_ok'] = (bool)$ok;
    $out['insert_id'] = $ok ? $mysqli->insert_id : null;
    $out['mysqli_error'] = $mysqli->error;
}

$chk = $mysqli->query("SELECT * FROM HojaDeRuta WHERE Seguimiento='{$codigo_seguimiento}' AND Eliminado=0");
$out['resultado_final'] = $chk ? $chk->fetch_all(MYSQLI_ASSOC) : null;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
