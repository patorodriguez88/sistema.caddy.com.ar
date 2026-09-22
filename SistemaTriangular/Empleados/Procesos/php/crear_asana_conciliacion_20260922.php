<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$workspaceGid = '734348733635084';

$apiRow = $mysqli->query("SELECT * FROM Api WHERE id=2 LIMIT 1")->fetch_assoc();
$token = $apiRow['token'] ?? '';
$refreshToken = $apiRow['refresh_token'] ?? '';

function asanaCall($method, $url, $token, $payload = null) {
    $ch = curl_init($url);
    $headers = ['Authorization: Bearer ' . $token];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($payload !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $headers;
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true)];
}

function refrescarToken($mysqli, $refreshToken) {
    $ch = curl_init('https://app.asana.com/-/oauth_token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => '1204867479928301',
            'client_secret' => '84f466e023db6f9958ddebad539b4df6',
            'redirect_uri' => 'https://www.sistema.caddy.com.ar/Api/Asana/recepcion.php',
            'refresh_token' => $refreshToken,
        ]),
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($body, true);
    if (!empty($data['access_token'])) {
        $newToken = $mysqli->real_escape_string($data['access_token']);
        $mysqli->query("UPDATE Api SET token='{$newToken}' WHERE id=2");
        return $data['access_token'];
    }
    return null;
}

$out = [];

// 1) Buscar el proyecto "Sistema" en el workspace
[$code, $projects] = asanaCall('GET', "https://app.asana.com/api/1.0/workspaces/{$workspaceGid}/projects?opt_fields=name&limit=100", $token);
if ($code === 401) {
    $newToken = refrescarToken($mysqli, $refreshToken);
    if ($newToken) {
        $token = $newToken;
        [$code, $projects] = asanaCall('GET', "https://app.asana.com/api/1.0/workspaces/{$workspaceGid}/projects?opt_fields=name&limit=100", $token);
    }
}
$out['projects_http'] = $code;

$projectGid = null;
foreach (($projects['data'] ?? []) as $p) {
    if (trim($p['name']) === 'Sistema') {
        $projectGid = $p['gid'];
        break;
    }
}
$out['project_gid'] = $projectGid;

if (!$projectGid) {
    $out['error'] = 'No se encontro el proyecto Sistema';
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2) Buscar la seccion "Mejoras"
[$code2, $sections] = asanaCall('GET', "https://app.asana.com/api/1.0/projects/{$projectGid}/sections?opt_fields=name", $token);
$out['sections_http'] = $code2;
$sectionGid = null;
foreach (($sections['data'] ?? []) as $s) {
    if (stripos($s['name'], 'Mejora') !== false) {
        $sectionGid = $s['gid'];
        break;
    }
}
$out['section_gid'] = $sectionGid;
$out['sections'] = $sections['data'] ?? [];

// 3) Crear la tarea
$nombre = 'Conciliación Bancaria: comprobante en pagos a proveedores + auditoría + bloqueo + impresión/cierre';

$notas = "Pedido de Patricio (varios puntos juntos):\n\n"
    . "1) Agregar un campo \"Número de Comprobante\" al cargar un PAGO en Proveedores (hoy solo existe \"Número de Cheque\", que se muestra/oculta según la forma de pago - para transferencia/efectivo no hay ningún número que quede guardado). Ese número tiene que llegar hasta Tesoreria para poder verlo/matchear en Conciliación Bancaria.\n"
    . "   - Tesoreria YA tiene una columna \"NumeroTrans\" que hoy se usa en otros circuitos (ej. cobranza por Mercado Pago) exactamente con este propósito - lo lógico es reusar esa columna para proveedores en vez de crear una nueva, y mostrarla como columna en la grilla de Conciliación Bancaria.\n\n"
    . "2) En Conciliación Bancaria: cuando se concilia un ítem, guardar fecha+hora y quién concilió.\n"
    . "   - Ya existe parcialmente: Tesoreria.FechaConciliado y Tesoreria.UsuarioConciliado se completan al grabar (Admin/Procesos/php/bancos.php, action grabar_conciliacion), pero FechaConciliado hoy es solo FECHA (date('Y-m-d')), sin hora. Falta agregar la hora.\n\n"
    . "3) Que una conciliación ya hecha no se pueda destildar / eliminar - pasar de checkbox a un ícono de \"validado\" fijo, para evitar errores.\n"
    . "   - IMPORTANTE, encontrado al revisar el código actual: hoy hay un bug de diseño relacionado. Cada vez que se aprieta \"Guardar Conciliación\", el backend primero pone TODOS los registros conciliados del rango de fechas/cuenta en Conciliado=0, y recién después marca en 1 los que están tildados en ese momento en pantalla. Si un ítem ya conciliado no está entre los tildados actuales (se destildó sin querer, o directamente no está en la página/filtro actual), queda DEconciliado sin ningún aviso. Esto ya es, en los hechos, el problema que preocupa - conviene resolverlo de raíz, no solo agregar el ícono.\n\n"
    . "4) Poder imprimir la conciliación, y poder \"cerrarla\".\n\n"
    . "Cómo se hace esto en un sistema contable profesional (Oracle Cash Management / SAP FI-BL, mismo criterio en cualquier ERP serio) - para tenerlo como referencia al diseñar la solución acá:\n\n"
    . "- La conciliación se arma como una CORRIDA/LOTE con estado propio (ej. Abierta -> Cerrada), no como un tilde suelto por fila. Se define cuenta + período (o saldo inicial/final del resumen bancario) y se trabaja adentro de esa corrida.\n"
    . "- El matcheo ideal es automático por importe + fecha + número de comprobante/referencia (por eso el punto 1 - sin número de comprobante, conciliar es \"a ojo\" por monto y fecha, con mucho margen de error), con conciliación manual como excepción para lo que no matchea solo.\n"
    . "- Una vez conciliado un ítem, es INMUTABLE dentro de esa corrida: no se destilda. Si hay que revertir un error, es una acción aparte y explícita (\"desconciliar\"), nunca un simple destilde, y también queda auditada (quién, cuándo, por qué).\n"
    . "- Al CERRAR la corrida completa, todo el lote queda bloqueado (no se puede tocar ningún ítem de ese período sin reabrir la corrida entera, acción que normalmente requiere permisos especiales).\n"
    . "- La corrida cerrada genera un COMPROBANTE/REPORTE imprimible (PDF): saldo inicial, ítems conciliados, saldo final, y aparte el listado de partidas pendientes (cheques no cobrados, depósitos en tránsito) que se arrastran a la corrida siguiente.\n\n"
    . "Aplicado a este sistema, la propuesta sería: agregar una tabla \"ConciliacionBancaria\" (cabecera: Cuenta, Desde, Hasta, SaldoInicial, SaldoFinal, Estado Abierta/Cerrada, UsuarioCierre, FechaCierre), que cada fila de Tesoreria conciliada quede linkeada a esa cabecera (no solo con un flag suelto), que \"Guardar Conciliación\" solo AGREGUE conciliados nuevos (nunca destilde los ya guardados), un botón \"Cerrar Conciliación\" que bloquee la corrida, y un botón \"Imprimir\" que genere el PDF de esa corrida ya cerrada.";

$data = ['data' => [
    'name' => $nombre,
    'notes' => $notas,
    'projects' => [$projectGid],
]];
if ($sectionGid) {
    $data['data']['memberships'] = [['project' => $projectGid, 'section' => $sectionGid]];
}

[$code3, $res] = asanaCall('POST', "https://app.asana.com/api/1.0/tasks", $token, $data);
$out['create_http'] = $code3;
$out['created_task'] = $res;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
