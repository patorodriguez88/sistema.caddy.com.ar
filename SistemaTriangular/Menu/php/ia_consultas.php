<?php
$startTime = microtime(true);
include_once __DIR__ . "/../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . "/ia_herramientas.php";
include_once __DIR__ . "/ia_claude_cliente.php";

$configClaude = __DIR__ . "/../../Conexion/claude_config.php";
if (is_file($configClaude)) {
    include_once $configClaude;
}

// El asistente es solo para SuperAdministrador (Nivel 1). Se valida acá también, no
// solo ocultando el botón en el topbar, para que no se pueda pegar directo al endpoint.
if (intval($_SESSION['Nivel'] ?? 0) !== 1) {
    http_response_code(403);
    echo json_encode(['success' => 0, 'msg' => 'El asistente está disponible solo para SuperAdministrador.']);
    exit;
}

function logIA($mysqli, $data)
{
    $stmt = $mysqli->prepare("
        INSERT INTO ia_logs (pregunta, respuesta, success, modulo, usuario, fecha, tiempo_ms)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) return;

    $pregunta = $data['pregunta'] ?? '';
    $respuesta = $data['respuesta'] ?? '';
    $success = isset($data['success']) ? (int)$data['success'] : 0;
    $modulo = $data['modulo'] ?? '';
    $usuario = $_SESSION['Usuario'] ?? 'sistema';
    $fecha = date('Y-m-d H:i:s');
    $tiempo = $data['tiempo'] ?? 0;

    $stmt->bind_param("ssisssi", $pregunta, $respuesta, $success, $modulo, $usuario, $fecha, $tiempo);
    $stmt->execute();
    $stmt->close();
}

function salir($arr)
{
    global $mysqli, $startTime;

    $tiempo = isset($startTime) ? round((microtime(true) - $startTime) * 1000) : 0;

    logIA($mysqli, [
        'pregunta' => $_POST['pregunta'] ?? '',
        'respuesta' => $arr['respuesta'] ?? ($arr['msg'] ?? ''),
        'success' => $arr['success'] ?? 0,
        'modulo' => $arr['modulo'] ?? 'general',
        'tiempo' => $tiempo
    ]);

    echo json_encode($arr);
    exit;
}

if (isset($_POST['consultas_frecuentes'])) {
    $sql = "
        SELECT MIN(pregunta) AS pregunta, COUNT(*) AS total
        FROM ia_logs
        WHERE success = 1 AND IFNULL(TRIM(pregunta), '') <> ''
        GROUP BY LOWER(TRIM(pregunta))
        ORDER BY total DESC
        LIMIT 15
    ";
    $res = $mysqli->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $preguntaLog = trim($row['pregunta']);
        $data[] = [
            'pregunta' => $preguntaLog,
            'texto' => mb_strlen($preguntaLog, 'UTF-8') > 55 ? mb_substr($preguntaLog, 0, 55, 'UTF-8') . '...' : $preguntaLog,
            'total' => (int)$row['total']
        ];
    }
    echo json_encode(['success' => 1, 'data' => $data]);
    exit;
}

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';

if ($pregunta === '') {
    salir(['success' => 0, 'msg' => 'Pregunta vacía.']);
}

if (!is_file($configClaude)) {
    salir(['success' => 0, 'msg' => 'El asistente todavía no está configurado (falta Conexion/claude_config.php).']);
}

// Historial reciente que manda el frontend, para que se puedan hacer preguntas de
// seguimiento ("¿y el mes pasado?") sin repetir todo el contexto.
$historialCrudo = isset($_POST['historial']) ? json_decode($_POST['historial'], true) : [];
if (!is_array($historialCrudo)) $historialCrudo = [];

$nivel = intval($_SESSION['Nivel'] ?? 0);
$nombreUsuario = trim(($_SESSION['NombreUsuario'] ?? '') . ' ' . ($_SESSION['ApellidoUsuario'] ?? ''));
$hoy = date('Y-m-d');

$diasEs = ['Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miércoles', 'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sábado', 'Sunday' => 'domingo'];
$diaSemanaEs = $diasEs[date('l', strtotime($hoy))] ?? '';

$systemPrompt = <<<PROMPT
Sos el Asistente Caddy, un asistente interno del sistema de gestión logística de Caddy (transporte y logística, Córdoba, Argentina).

TU ÚNICO PROPÓSITO es responder preguntas sobre los datos operativos y comerciales de ESTE sistema (envíos, seguimiento, clientes, ventas, tarifas, localidades de cobertura, repartidores, seguros/valor declarado, logística), usando EXCLUSIVAMENTE las herramientas (tools) que tenés disponibles. No tenés otra capacidad: no podés navegar internet, no podés ejecutar código, no podés generar contenido que no sea a partir de datos reales devueltos por las herramientas.

Si te preguntan algo que no tiene que ver con este sistema (charla general, otros temas, pedidos de código, opiniones, etc.), respondé amablemente que solo podés ayudar con consultas del sistema de Caddy, sin intentar responder la pregunta original.

Reglas importantes:
- Nunca inventes números. Todo dato cuantitativo tiene que salir de una llamada a una herramienta.
- Si una herramienta te devuelve "error" o "No disponible para tu nivel de acceso", comunicáselo al usuario tal cual, no lo reemplaces por un número inventado.
- Para preguntas de fechas relativas ("hoy", "el mes pasado", "esta semana", "abril"), calculá vos mismo el rango YYYY-MM-DD antes de llamar la herramienta. Hoy es {$hoy} ({$diaSemanaEs}).
- Si falta información para elegir un cliente (varios coinciden), usá buscar_cliente primero y preguntale al usuario cuál es, no asumas.
- Respondé siempre en español rioplatense, tono profesional pero cercano, conciso (esto se ve en un panel de chat chico, no hagas respuestas kilométricas).
- Podés usar HTML simple para dar formato: <strong>, <br>, <ul><li>. No uses markdown (nada de **, #, etc.) porque no se renderiza acá.
- Los montos en pesos ya te llegan formateados desde las herramientas (ej. "$ 1.234,56") — usalos tal cual, no los reformatees.
- El usuario que te consulta es {$nombreUsuario} (Nivel {$nivel} — Nivel 1 es SuperAdministrador, Nivel 2 es Administración). Algunas herramientas ya filtran datos sensibles (facturación) según ese nivel automáticamente, no hace falta que lo menciones salvo que la herramienta te avise que no está disponible.
PROMPT;

$messages = [];

// Reconstruir historial (últimos intercambios reales, no errores) para dar contexto.
foreach ($historialCrudo as $item) {
    if (!isset($item['pregunta'], $item['respuesta'])) continue;
    $messages[] = ['role' => 'user', 'content' => (string)$item['pregunta']];
    $messages[] = ['role' => 'assistant', 'content' => strip_tags((string)$item['respuesta'])];
}

$messages[] = ['role' => 'user', 'content' => $pregunta];

try {
    $resultado = iaConversarConClaude($mysqli, $systemPrompt, $messages, $nivel);

    salir([
        'success' => 1,
        'respuesta' => $resultado['texto'],
        'modulo' => $resultado['herramientas_usadas'][0] ?? 'general'
    ]);
} catch (IAClaudeException $e) {
    salir(['success' => 0, 'msg' => 'No pude conectar con el asistente: ' . $e->getMessage()]);
} catch (Throwable $e) {
    salir(['success' => 0, 'msg' => 'Ocurrió un error inesperado consultando al asistente.']);
}
