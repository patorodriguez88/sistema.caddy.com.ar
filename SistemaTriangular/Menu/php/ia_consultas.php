<?php
$startTime = microtime(true);
include_once __DIR__ . "/../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . "/ia_comunes.php";

include_once __DIR__ . "/ia_consultas/localidades.php";
include_once __DIR__ . "/ia_consultas/productos.php";
include_once __DIR__ . "/ia_consultas/transclientes.php";
include_once __DIR__ . "/ia_consultas/seguimiento.php";
include_once __DIR__ . "/ia_consultas/logistica.php";

if (isset($_POST['consultas_frecuentes'])) {

    $sql = "
        SELECT 
            pregunta,
            COUNT(*) AS total
        FROM ia_logs
        WHERE success = 1
          AND IFNULL(TRIM(pregunta), '') <> ''
        GROUP BY pregunta
        ORDER BY total DESC
        LIMIT 15
    ";

    $res = $mysqli->query($sql);

    $data = [];

    while ($row = $res->fetch_assoc()) {
        $preguntaLog = trim($row['pregunta']);

        $data[] = [
            'pregunta' => $preguntaLog,
            'texto' => mb_strlen($preguntaLog, 'UTF-8') > 38
                ? mb_substr($preguntaLog, 0, 38, 'UTF-8') . '...'
                : $preguntaLog,
            'total' => (int)$row['total']
        ];
    }

    salir([
        'success' => 1,
        'data' => $data
    ]);
}
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
if (consultarSeguroEntreFechas($mysqli, $contexto)) exit;
if (consultarLocalidades($mysqli, $contexto)) exit;
if (consultarTarifas($mysqli, $contexto)) exit;
if (consultarSeguimientoGeneral($mysqli, $contexto)) exit;
if (consultarLogistica($mysqli, $contexto)) exit;

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta.'
]);
