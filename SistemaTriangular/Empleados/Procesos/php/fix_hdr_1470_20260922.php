<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Repara el mismo bug de siempre (TransClientes+Seguimiento creados, sin
// HojaDeRuta) pero esta vez para LAS 3 colectas de hoy en el recorrido
// 1470 de Franco Sanchez (incluye VENEX, reportado por Sanchez con "0/0"
// en warehouse y por Patricio con el recorrido sin aparecer en Hoja de
// Ruta): a diferencia del fix de ayer/hoy con Dynamic, acá se reconstruye
// cada fila leyendo directo su propia TransClientes (misma correspondencia
// de columnas que usa colecta.php) en vez de tipear los valores a mano.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$codigos = ['MMTMCQDBK', 'VLYKSWT56', 'N53W9MXVG'];
$pais = 'Argentina';
$asignado = 'Unica Vez';
$estado_hdr = 'Abierto';

$out = ['dry_run' => $dry, 'items' => []];

foreach ($codigos as $codigo) {
    $item = ['codigo' => $codigo];
    try {

    $yaExiste = $mysqli->query("SELECT id FROM HojaDeRuta WHERE Seguimiento='{$codigo}' AND Eliminado=0");
    $item['ya_existe'] = $yaExiste ? $yaExiste->fetch_all(MYSQLI_ASSOC) : null;
    if (!empty($item['ya_existe'])) {
        $item['accion'] = 'omitido (ya existe)';
        $out['items'][] = $item;
        continue;
    }

    $tc = $mysqli->query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='{$codigo}' AND Eliminado=0 ORDER BY id DESC LIMIT 1");
    $row = $tc ? $tc->fetch_assoc() : null;
    if (!$row) {
        $item['accion'] = 'ERROR: no se encontro TransClientes';
        $out['items'][] = $item;
        continue;
    }

    $recorrido = intval($row['Recorrido']);
    $SQL_ORDEN = $mysqli->query("SELECT MAX(Posicion) as Posicion FROM HojaDeRuta WHERE Recorrido='{$recorrido}' AND Estado='Abierto' AND Eliminado='0'");
    $DATO_ORDEN = $SQL_ORDEN->fetch_array(MYSQLI_ASSOC);
    $orden = trim($DATO_ORDEN['Posicion']) + 1;
    $item['posicion_calculada'] = $orden;

    $fecha = $row['Fecha'];
    $domiciliodestino = $mysqli->real_escape_string($row['DomicilioDestino']);
    $localidaddestino = $mysqli->real_escape_string($row['LocalidadDestino']);
    $provinciadestino = $mysqli->real_escape_string($row['ProvinciaDestino']);
    $clientedestino = $mysqli->real_escape_string($row['ClienteDestino']);
    $tipodecomprobante = $mysqli->real_escape_string($row['TipoDeComprobante']);
    $observaciones = $mysqli->real_escape_string($row['Observaciones']);
    $usuario = $mysqli->real_escape_string($row['Usuario']);
    $nordenlogistica = intval($row['NumerodeOrden']);
    $idclientedestino = intval($row['idClienteDestino']);
    $telefonodestino = $mysqli->real_escape_string($row['TelefonoDestino']);
    $NRepo = intval($row['NumeroVenta']);
    $idTransClientes = intval($row['id']);

    $sqlInsert = "INSERT INTO `HojaDeRuta`(
        `Fecha`,`Recorrido`,`Localizacion`,`Ciudad`,`Provincia`,`Pais`,`Cliente`,`Titulo`,`Observaciones`,`Usuario`,
        `Asignado`,`Estado`,`NumerodeOrden`,`Seguimiento`,`idCliente`,`Posicion`,`Celular`,`NumeroRepo`,`idTransClientes`) VALUES (
        '{$fecha}','{$recorrido}','{$domiciliodestino}','{$localidaddestino}','{$provinciadestino}','{$pais}',
        '{$clientedestino}','{$tipodecomprobante}','{$observaciones}','{$usuario}','{$asignado}','{$estado_hdr}','{$nordenlogistica}',
        '{$codigo}','{$idclientedestino}','{$orden}','{$telefonodestino}','{$NRepo}',{$idTransClientes})";

    $item['sql'] = $sqlInsert;

    if (!$dry) {
        $ok = $mysqli->query($sqlInsert);
        $item['insert_ok'] = (bool)$ok;
        $item['insert_id'] = $ok ? $mysqli->insert_id : null;
        $item['mysqli_error'] = $mysqli->error;
    }

    } catch (\Throwable $e) {
        $item['exception'] = $e->getMessage();
    }
    $out['items'][] = $item;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
