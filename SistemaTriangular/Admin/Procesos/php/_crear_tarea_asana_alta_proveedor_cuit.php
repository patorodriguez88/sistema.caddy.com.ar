<?php
// Script temporal (se borra tras usarse): crea la tarea en Asana (proyecto
// Sistema > Mejoras) documentando la feature "Nuevo Proveedor por CUIT".
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$CLIENT_ID = '1204867479928301';
$CLIENT_SECRET = '84f466e023db6f9958ddebad539b4df6';
$WORKSPACE_GID = '734348733635084';
$PROJECT_SISTEMA_GID = '750173072472604';
$SECTION_MEJORAS_GID = '1202052366128757';

function asanaRequest($token, $method, $url, $body = null) {
    $ch = curl_init($url);
    $headers = ['Authorization: Bearer ' . $token, 'Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($resp, true)];
}

$res = $mysqli->query("SELECT token, refresh_token FROM Api WHERE id = 2 LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;
if (!$row) {
    echo json_encode(['error' => 'No se encontro la fila de Api id=2']);
    exit;
}
$token = $row['token'];

// Pruebo con el token actual; si esta vencido, refresco.
$test = asanaRequest($token, 'GET', 'https://app.asana.com/api/1.0/users/me');
if ($test['code'] !== 200) {
    $ch = curl_init('https://app.asana.com/-/oauth_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $CLIENT_ID,
        'client_secret' => $CLIENT_SECRET,
        'refresh_token' => $row['refresh_token'],
    ]));
    $refreshResp = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if (!isset($refreshResp['access_token'])) {
        echo json_encode(['error' => 'No se pudo refrescar el token', 'detalle' => $refreshResp]);
        exit;
    }
    $token = $refreshResp['access_token'];
    $mysqli->query("UPDATE Api SET token = '" . $mysqli->real_escape_string($token) . "' WHERE id = 2");
}

$descripcion = <<<TXT
Alta de proveedor por CUIT, con consulta automática a ARCA (ex-AFIP).

Pedido: al cargar un proveedor nuevo, poder ingresar solo el CUIT y que el sistema traiga automáticamente los datos fiscales (razón social, domicilio, condición de IVA), para evitar errores de tipeo en la carga manual.

Qué se hizo:
- "Agregar Proveedor" ahora abre un modal pidiendo el CUIT primero.
- Se verifica contra nuestra base de Proveedores: si el CUIT ya existe, avisa y ofrece ir directo a esa ficha (evita duplicados).
- Si no existe, consulta el padrón de ARCA (servicio ws_sr_constancia_inscripcion, mismo certificado que ya usamos para facturar) y precarga el formulario de siempre con razón social, domicilio fiscal completo, localidad, provincia, código postal y condición de IVA estimada.
- Si ARCA no tiene el CUIT o la consulta falla, igual abre el formulario vacío con el CUIT puesto - nunca bloquea la carga manual.
- Validador visual del formulario completo (Bootstrap is-valid/is-invalid): marca en rojo los campos obligatorios que falten y en verde lo que ya está completo, en vivo mientras se completa y al intentar guardar.
- De paso se corrigieron 3 bugs de raíz encontrados en el camino:
  1. El WSDL local del servicio de padrón estaba desactualizado (el servicio de ARCA se renombró y cambió de método a getPersona_v2 en feb-2026) - se reemplazó por el WSDL vivo actual.
  2. El alta/edición de proveedores armaba el SQL interpolando los datos del formulario sin escapar - un campo con una comilla (ej. una dirección con apóstrofe) rompía todo el guardado en silencio, sin ningún error visible. Causa real de que a Agustina se le "perdiera" el alta de MEREB CAROLINA BENITA. Se pasó a prepared statements.
  3. El botón "Guardar" nunca detectaba correctamente "no hay proveedor seleccionado" (comparaba contra "0", pero el placeholder del selector vale el texto "Seleccionar Proveedor") - por eso una alta nueva podía mostrar "Guardado" sin haber creado nada.

Certificado: se reutilizó el mismo certificado de facturación electrónica (alias "Caddy2022" en ARCA) - solo hubo que autorizarle el servicio adicional "Consulta de constancia de inscripción" desde el Administrador de Relaciones de Clave Fiscal.
TXT;

$taskBody = [
    'data' => [
        'name' => 'Alta de proveedor por CUIT: consulta automática a ARCA (padrón)',
        'notes' => $descripcion,
        'projects' => [$PROJECT_SISTEMA_GID],
        'workspace' => $WORKSPACE_GID,
    ],
];

$create = asanaRequest($token, 'POST', 'https://app.asana.com/api/1.0/tasks', $taskBody);

if ($create['code'] >= 300 || !isset($create['body']['data']['gid'])) {
    echo json_encode(['error' => 'No se pudo crear la tarea', 'detalle' => $create]);
    exit;
}

$taskGid = $create['body']['data']['gid'];

// La muevo a la sección "Mejoras"
$moveBody = ['data' => ['task' => $taskGid]];
$move = asanaRequest($token, 'POST', "https://app.asana.com/api/1.0/sections/{$SECTION_MEJORAS_GID}/addTask", $moveBody);

echo json_encode([
    'success' => true,
    'task_gid' => $taskGid,
    'url' => "https://app.asana.com/1/{$WORKSPACE_GID}/project/{$PROJECT_SISTEMA_GID}/task/{$taskGid}",
    'movido_a_mejoras' => $move['code'] < 300,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
