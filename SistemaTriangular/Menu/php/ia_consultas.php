<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . "/../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . "/ia_comunes.php";

include_once __DIR__ . "/ia_consultas/localidades.php";
include_once __DIR__ . "/ia_consultas/productos.php";
include_once __DIR__ . "/ia_consultas/transclientes.php";
include_once __DIR__ . "/ia_consultas/seguimiento.php";
include_once __DIR__ . "/ia_consultas/logistica.php";

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

if ($pregunta === '') {
    salir(['success' => 0, 'msg' => 'Pregunta vacía.']);
}

$q = normalizarTexto($pregunta);

$contexto = [
    'pregunta' => $pregunta,
    'q' => $q
];

if (consultarCodigoSeguimiento($mysqli, $contexto)) exit;
if (consultarVentasCliente($mysqli, $contexto)) exit;
if (consultarLocalidades($mysqli, $contexto)) exit;
if (consultarTarifas($mysqli, $contexto)) exit;
if (consultarSeguimientoGeneral($mysqli, $contexto)) exit;
if (consultarLogistica($mysqli, $contexto)) exit;

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta.'
]);
