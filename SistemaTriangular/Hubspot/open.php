<?php

// $accessToken = 'pat-na1-af0e5daa-91f3-4bb8-a303-ff3f4bb2a256'; // 🔁 Reemplazá con tu token

$portalId = '23486798'; // Por si querés armar links después
$vendedor = '76302506';
$headers = [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
];

function callHubSpot($url, $headers)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getContactoDeTarea($taskId, $headers)
{
    $asocUrl = "https://api.hubapi.com/crm/v3/objects/tasks/$taskId/associations/contacts";
    $asocData = callHubSpot($asocUrl, $headers);

    if (!empty($asocData['results'][0]['id'])) {
        $contactId = $asocData['results'][0]['id'];
        $contactUrl = "https://api.hubapi.com/crm/v3/objects/contacts/$contactId?properties=firstname,lastname,email";
        $contactData = callHubSpot($contactUrl, $headers);
        $nombre = trim(($contactData['properties']['firstname'] ?? '') . ' ' . ($contactData['properties']['lastname'] ?? ''));
        $email = $contactData['properties']['email'] ?? '';
        return "$nombre &lt;$email&gt;";
    } else {
        return '(sin contacto)';
    }
}

// Buscar tareas
$searchUrl = "https://api.hubapi.com/crm/v3/objects/tasks/search";
$body = [
    "filterGroups" => [[
        "filters" => [[
            "propertyName" => "hubspot_owner_id",
            "operator" => "EQ",
            "value" => $vendedor
        ]]
    ]],
    "properties" => [
        "hs_task_body",
        "hs_task_status",
        "hubspot_owner_id",
        "hs_timestamp",
        "hs_createdate",
        "hs_task_priority"
    ],
    "limit" => 20,
    "sorts" => ["-hs_timestamp"]
];

$ch = curl_init($searchUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo "<h3 style='color:red;'>❌ Error $http_code</h3><pre>$response</pre>";
    exit;
}

$data = json_decode($response, true);

echo "<h2>📋 Tareas con contacto asociado:</h2><ul>";

foreach ($data['results'] as $task) {



    $id = $task['id'];
    $p = $task['properties'];

    $descripcion = $p['hs_task_body'] ?? '(sin descripción)';
    $estado = $p['hs_task_status'] ?? '(sin estado)';
    $dueRaw = $p['hs_timestamp'] ?? null;

    if ($dueRaw) {
        // Si es numérico (timestamp en ms), lo dividimos por 1000
        if (is_numeric($dueRaw)) {
            $dueDate = date('d/m/Y H:i', $dueRaw / 1000);
        } else {
            // Si es texto (ISO 8601), lo parseamos normal
            $dueDate = date('d/m/Y H:i', strtotime($dueRaw));
        }
    } else {
        $dueDate = '(sin fecha)';
    }
    $timestamp = isset($p['hs_timestamp']) ? date('d/m/Y H:i', strtotime($p['hs_timestamp'])) : '(sin timestamp)';
    $owner = $p['hubspot_owner_id'] ?? '(sin dueño)';
    $prioridad = $p['hs_task_priority'] ?? 'N/A';

    $contacto = getContactoDeTarea($id, $headers);

    echo "<li>
        <strong>🆔 ID:</strong> $id<br>
        <strong>📅 Creación:</strong> $timestamp<br>
        <strong>📆 Vence:</strong> $dueDate<br>
        <strong>🔧 Estado:</strong> $estado<br>
        <strong>⚡ Prioridad:</strong> $prioridad<br>
        <strong>👤 Vendedor ID:</strong> $owner<br>
        <strong>👥 Contacto:</strong> $contacto<br>
        <strong>📝 Tarea:</strong><br><pre>$descripcion</pre>
    </li><hr>";
}

echo "</ul>";
