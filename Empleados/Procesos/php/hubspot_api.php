<?php
include_once('../../../Conexion/Conexioni.php');
$accessToken = 'pat-na1-af0e5daa-91f3-4bb8-a303-ff3f4bb2a256'; // 🔁 Reemplazá con tu token
if (isset($_POST['Task'])) {

    Get_a_Task($_POST['gid'], $accessToken);
}

function Get_a_Task($gid, $token)
{
    $url = "https://api.hubapi.com/crm/v3/objects/tasks/$gid?properties=subject,hs_task_body,hs_task_subject,hs_task_status,hs_timestamp,hubspot_owner_id,hs_createdate";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $hubspotData = json_decode($response, true);
    if (!isset($hubspotData['properties'])) {
        echo json_encode(["error" => "No se encontró la tarea"]);
        return;
    }

    $props = $hubspotData['properties'];

    $name = $props['hs_task_subject'] ?? $props['subject'] ?? 'Sin título';
    $body = $props['hs_task_body'] ?? '';
    $completed = $props['hs_task_status'] ?? 'UNKNOWN';
    $due_on = $props['hs_timestamp'] ?? '';
    $created_at = $props['hs_createdate'] ?? '';
    $assignee = $props['hubspot_owner_id'] ?? '';

    echo json_encode([
        "data" => [
            "gid" => $gid,
            "name" => $name,
            "body" => $body,
            "completed" => $completed,
            "due_on" => $due_on,
            "created_at" => $created_at,
            "assignee_id" => $assignee,
            "creator_id" => null // HubSpot no expone creator directamente
        ]
    ]);
}



if (isset($_POST['Hubspot'])) {

    // 1. Obtener nombres de los dueños
    function obtenerNombresDeUsuarios($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.hubapi.com/crm/v3/owners');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $nombres = [];

        foreach ($data['results'] as $owner) {
            $nombres[$owner['id']] = $owner['firstName'] . ' ' . $owner['lastName'];
        }

        return $nombres;
    }

    $owners = obtenerNombresDeUsuarios($accessToken);

    $gidsExistentes = [];
    $res = $mysqli->query("SELECT gid_hubspot FROM Tareas WHERE gid_hubspot IS NOT NULL");
    while ($row = $res->fetch_assoc()) {
        $gidsExistentes[$row['gid_hubspot']] = true;
    }

    // 3. Obtener tareas con paginación automática
    $baseUrl = 'https://api.hubapi.com/crm/v3/objects/tasks';
    $params = [
        'properties' => 'subject,hs_task_body,hs_task_subject,hs_task_status,hs_timestamp,hubspot_owner_id',
        'limit' => 100
    ];

    $after = null;
    $allTasks = [];

    do {
        $url = $baseUrl . '?' . http_build_query($params);
        if ($after) {
            $url .= '&after=' . urlencode($after);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!isset($data['results'])) break;

        $allTasks = array_merge($allTasks, $data['results']);
        $after = $data['paging']['next']['after'] ?? null;
    } while ($after);

    // 4. Armar resultados excluyendo tareas ya existentes
    $filteredData = [];

    foreach ($allTasks as $task) {
        $gid = $task['id'];

        // Si ya existe en la base, no lo mostramos
        if (isset($gidsExistentes[$gid])) {
            continue;
        }

        $owner_id = $task['properties']['hubspot_owner_id'] ?? '';
        $due_on = $task['properties']['hs_timestamp'] ?? '';
        $status = $task['properties']['hs_task_status'] ?? '';
        $name = $task['properties']['hs_task_subject']
            ?? $task['properties']['subject']
            ?? $task['properties']['hs_task_body']
            ?? 'Sin título';

        $filteredData[] = [
            'name' => $name,
            'assignee_name' => $owners[$owner_id] ?? $owner_id,
            'completed' => $status,
            'created_by_resource_type' => 'HubSpot',
            'due_on' => $due_on,
            'gid' => $gid,
        ];
    }

    // 5. Devolver resultado final
    header('Content-Type: application/json');
    echo json_encode(['data' => $filteredData]);
}
