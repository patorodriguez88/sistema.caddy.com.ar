<?php
// session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

if (isset($_POST['Asana'])) {
    if (Get_tasks_from_a_projects() === 0) {
        if (refresh_token() === 1) {
            // Llamar Get_tasks_from_a_projects solo después de actualizar el token
            Get_tasks_from_a_projects();
        };
    }
}

if (isset($_POST['Task'])) {
    if (Get_a_Task($_POST['gid']) === 0) {
        if (refresh_token() === 1) {
            // Llamar Get_tasks_from_a_projects solo después de actualizar el token
            Get_a_Task($_POST['gid']);
        };
    }
}

function refresh_token()
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/-/oauth_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id=1204867479928301&redirect_uri=https%3A%2F%2Fwww.sistema.caddy.com.ar%2FApi%2FAsana%2Frecepcion.php&client_secret=84f466e023db6f9958ddebad539b4df6&refresh_token=' . $row['refresh_token'],
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            // Token de acceso renovado con éxito, almacenar en la base de datos u otro lugar según sea necesario
            $new_token = $data['access_token'];
            $sql = $mysqli->query("UPDATE Api SET token='" . $new_token . "' WHERE id='2'");
            return 1;
        } else {
            // La respuesta no contiene un nuevo token de acceso válido
            echo json_encode(array('error' => 'No se pudo obtener un nuevo token de acceso.'));
        }
    } else {
        // Error al realizar la solicitud CURL
        echo json_encode(array('error' => 'Error al intentar actualizar el token de acceso.'));
    }
}



function Get_tasks_from_a_projects()
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();


    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/projects/1202454550277567/tasks?opt_fields=created_by,due_on,completed,assignee.name,notes,name,tags',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            // 'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    // Decodificar la respuesta JSON
    $response = curl_exec($curl);
    $data = json_decode($response, true);

    // DEBUG: Obtener código de estado HTTP
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // DEBUG OPCIONAL: Mostrar todos los headers devueltos por Asana
    $info = curl_getinfo($curl);

    // Opcional: loguearlo o imprimirlo
    error_log("ASANA HTTP Code: " . $http_code);
    error_log("ASANA Full cURL Info: " . print_r($info, true));

    // Cerrar la sesión cURL
    curl_close($curl);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí


                return 0;
            }
        }
    } else {

        $filteredData = array();

        foreach (($data['data'] ?? []) as $item) {

            // 1) Assignee puede ser null
            $assigneeGid  = $item['assignee']['gid']  ?? null;
            $assigneeName = $item['assignee']['name'] ?? null;

            // 2) Due_on puede ser null
            $dueOn = $item['due_on'] ?? null;

            // 3) Tags puede no existir o ser null
            $tags = (isset($item['tags']) && is_array($item['tags'])) ? $item['tags'] : array();

            // Si no tiene asignado o no está en tu lista, seguir
            $assignee_gids = array('1212926275397398', '1210382139238856', '1210382139238853');
            if (!$assigneeGid || !in_array($assigneeGid, $assignee_gids, true)) {
                continue;
            }

            // Si no tiene due_on, no podés filtrar por mes: saltealo (o decidí incluirlo)
            if (!$dueOn) {
                continue;
            }

            // Validar fecha
            $ts = strtotime($dueOn);
            if ($ts === false) {
                continue;
            }

            $currentMonth = date('m');
            $dueMonth     = date('m', $ts);

            if ($dueMonth !== $currentMonth) {
                continue;
            }

            // Verificar tag "Cargado en Sistema Caddy"
            $tagCargado = '1206857892421559';
            $existeTag = false;

            foreach ($tags as $tag) {
                $tagGid = $tag['gid'] ?? null;
                if ($tagGid === $tagCargado) {
                    $existeTag = true;
                    break;
                }
            }

            if ($existeTag) {
                continue;
            }

            // created_by también puede faltar parcialmente
            $createdByType = $item['created_by']['resource_type'] ?? null;
            $createdByGid  = $item['created_by']['gid'] ?? null;

            $filteredData[] = array(
                'description' => $item['notes'] ?? '',
                'name' => $item['name'] ?? '',
                'gid' => $item['gid'] ?? '',
                'assignee_name' => $assigneeName ?? '',
                'completed' => $item['completed'] ?? false,
                'created_by_resource_type' => $createdByType ?? '',
                'due_on' => $dueOn,
                'created_by_gid' => $createdByGid ?? ''
            );
        }
        // Enviar los datos filtrados como respuesta JSON
        header('Content-Type: application/json');
        echo json_encode(array('data' => $filteredData));
        exit;
    }
}

function Get_a_Task($gid)
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        //   CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/'.$gid.'?opt_fields=created_by,name,resource_subtype,notes,approval_status,created_at,due_on,assignee,completed,permalink_url',
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/' . $gid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $data = json_decode($response, true);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí


                return 0;
            }
        }
    } else {

        echo $response;
    }
}

function Add_a_tag_to_Task($gid)
{

    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/' . $gid . '/addTag',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '
{
  "data": {
    "tag": "1206857892421559"
  }
}
',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            'content-type: application/json',
            'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $data = json_decode($response, true);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí

                refresh_token();

                Add_a_tag_to_Task($gid);
            }
        }
    }
}



function Delete_a_tag_to_Task($gid)
{

    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/' . $gid . '/removeTag',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '
{
  "data": {
    "tag": "1206857892421559"
  }
}
',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            'content-type: application/json',
            'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($response, true);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí

                refresh_token();

                Delete_a_tag_to_Task($gid);
            }
        }
    }
}


function Get_storys($gid)
{
    // $gid='1206694976609220';
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/' . $gid . '/stories',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            // 'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    // echo $response;
    $data = json_decode($response, true);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí

                refresh_token();
            }
        }
    } else {

        $filteredData = array();

        // Iterar sobre los comentarios del JSON
        foreach ($data['data'] as $comentario) {
            // Verificar si el comentario es de tipo "comment"
            if ($comentario['type'] === "comment") {
                // Obtener los datos del comentario
                $usuario = $comentario['created_by']['name'];
                $comentarioTexto = $comentario['text'];
                $fechaHora = date('Y-m-d H:i:s', strtotime($comentario['created_at'])); // Convertir la fecha y hora del comentario
                $gidComentario = $comentario['gid'];
                $horaFormateada = '';

                // Agregar el elemento al array de datos filtrados
                $filteredData[] = array(
                    'usuario' => $usuario,
                    'comentario' => $comentarioTexto,
                    'fechaHora' => $fechaHora
                );

?>
                <div class="d-flex align-items-start mt-3">
                    <a class="pe-3" href="#"></a>
                    <div class="w-100 overflow-hidden">
                        <h5 class="mt-0"><?php echo $usuario; ?><a class="text-muted h6"><?php echo ' ' . $fechaHora . ' ' . $horaFormateada; ?></a></h5>
                        <?php echo $comentarioTexto; ?>
                    </div>
                </div>
<?php



            }
        }

        // header('Content-Type: application/json');
        // echo json_encode(array('data'=>$filteredData));


    }
}




function Create_story($gid, $idTarea, $Comentario)
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks/' . $gid . '/stories',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '
{
  "data": {
    "text": "' . $Comentario . '"
  }
}
',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            'content-type: application/json',
            'Cookie: logged_out_uuid=cace6c97059bfa210280defcb87436ff; xsrf_token=83a4b2500da12bff706091ef95762094%3A1710511978035'
        ),
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_code == 200) {
        // Decodificar la respuesta JSON
        $data = json_decode($response, true);
    } elseif ($http_code == 401) {
        // Token expirado, refrescar el token y volver a intentar la llamada
        refresh_token();
        Create_story($gid, $idTarea, $Comentario);
    } else {
        // Error al obtener los datos desde la API
        echo json_encode(array('error' => 'Error al obtener los datos desde la API.'));
    }
}

//Actualiza todos los task 
function Actualizar_Task()
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();


    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/projects/1202454550277567/tasks?opt_fields=created_by,due_on,completed,assignee.name,notes,name,tags',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
        ),
    ));

    // Decodificar la respuesta JSON
    $response = curl_exec($curl);
    $data = json_decode($response, true);

    // Cerrar la sesión cURL
    curl_close($curl);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí

                return 0;
            }
        }
    } else {

        $filteredData = array();

        foreach (($data['data'] ?? []) as $index => $item) {

            $currentMonth = date('m');

            // due_on puede no existir o ser null
            $dueOn = $item['due_on'] ?? null;
            if (!$dueOn) {
                continue; // sin fecha -> no entra al filtro del mes
            }

            $ts = strtotime($dueOn);
            if ($ts === false) {
                continue; // fecha inválida
            }

            $dueMonth = date('m', $ts);

            if ($dueMonth !== $currentMonth) {
                continue;
            }

            // tags puede no existir o no ser array
            $tags = (isset($item['tags']) && is_array($item['tags'])) ? $item['tags'] : array();

            $existeTag = false;
            foreach ($tags as $tag) {
                if (($tag['gid'] ?? '') === '1206857892421559') {
                    $existeTag = true;
                    break;
                }
            }

            if (!$existeTag) {
                continue;
            }

            // assignee puede ser null
            $responsable = $item['assignee']['name'] ?? '';

            $gid = $item['gid'] ?? '';
            if ($gid === '') continue;

            $sql = $mysqli->query("SELECT Modificar_Fecha_entrega,FechaEntrega FROM Tareas WHERE gid_asana='" . $mysqli->real_escape_string($gid) . "' LIMIT 1");
            $row = $sql ? $sql->fetch_assoc() : null;

            // Si no existe en DB, evitá warning
            if (!$row) {
                continue;
            }

            // AUTORIZO A MODIFICAR FECHA
            if ((int)$row['Modificar_Fecha_entrega'] === 1) {

                $Modifica_Fecha_obs = ($row['FechaEntrega'] !== $dueOn)
                    ? "Se modifico la Fecha de Entrega para " . $dueOn
                    : "";

                $mysqli->query(
                    "UPDATE `Tareas` SET 
            `NombreTarea`='" . $mysqli->real_escape_string($item['name'] ?? '') . "',
            `Descripcion`='" . $mysqli->real_escape_string($item['notes'] ?? '') . "',
            `Responsable`='" . $mysqli->real_escape_string($responsable) . "',
            `FechaEntrega`='" . $mysqli->real_escape_string($dueOn) . "',
            `Estado`='" . ($item['completed'] ? 'true' : 'false') . "',
            `Modificar_Fecha_obs`='" . $mysqli->real_escape_string($Modifica_Fecha_obs) . "'
            WHERE `gid_asana`='" . $mysqli->real_escape_string($gid) . "'"
                );
            } else {

                $Modifica_Fecha_obs = ($row['FechaEntrega'] !== $dueOn)
                    ? "Se solicito modificar la Fecha de Entrega para " . $dueOn
                    : "";

                $mysqli->query(
                    "UPDATE `Tareas` SET 
            `NombreTarea`='" . $mysqli->real_escape_string($item['name'] ?? '') . "',
            `Descripcion`='" . $mysqli->real_escape_string($item['notes'] ?? '') . "',
            `Responsable`='" . $mysqli->real_escape_string($responsable) . "',
            `Estado`='" . ($item['completed'] ? 'true' : 'false') . "',
            `Modificar_Fecha_obs`='" . $mysqli->real_escape_string($Modifica_Fecha_obs) . "'
            WHERE `gid_asana`='" . $mysqli->real_escape_string($gid) . "'"
                );
            }
        }
    }
}

//ATACHMENT

function Get_attachments($gid)
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);


    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/attachments?parent=' . $gid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
        ),
    ));

    $response = curl_exec($curl);
    $data = json_decode($response, true);

    curl_close($curl);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí
                //  return 0;
                refresh_token();
                Get_attachments($gid);
            }
        }
    } else {

        // Construir la respuesta
        $response_array = array();
        $response_array['num_elements'] = count($data['data']);
        $response_array['data'] = $data['data'];

        // Devolver la respuesta JSON
        return json_encode($response_array);
    }
}

// //ATACHMENT
// $projects='1207004592303956';
// $name='prueba';
// $notes='notas';
// $due_on='2024-04-09';
// $assignee='1207004401160014';
// $workspace='734348733635084';

// $dato=Create_task($projects,$name,$notes,$due_on,$assignee,$workspace);
// echo $dato;

function Create_task($projects, $name, $notes, $due_on, $assignee, $workspace)
{

    global $mysqli;

    $name = $name;
    $notes = $notes;
    $due_on = $due_on;
    $assignee = $assignee;
    $projects = $projects;
    $workspace = $workspace;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/tasks',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '
{
  "data": {
    "name": "' . $name . '",
    "projects": [
        "' . $projects . '" 
      ],
    "notes": "' . $notes . '",
    "resource_subtype": "default_task",
    "approval_status": "pending",
    "assignee_status": "today",
    "completed": false,
    "due_on": "' . $due_on . '",
    "assignee": "' . $assignee . '",
    "workspace": "' . $workspace . '"
  }
}
',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer ' . $row['token'],
            'content-type: application/json',
            'Cookie: logged_out_uuid=7506d9bcb5c21ac1d8dd4bea39be7167'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $data = json_decode($response, true);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí

                refresh_token();
                // Get_attachments($gid);
                Create_task($projects, $name, $notes, $due_on, $assignee, $workspace);
            }
        }
    } else {

        return $data['data']['gid'];
    }
}

function cargarUsuariosAsana()
{
    global $mysqli;

    $sql = $mysqli->query("SELECT * FROM Api WHERE id='2'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.asana.com/api/1.0/users',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer  ' . $row['token'],
        )
    ));


    $response = curl_exec($curl);
    $data = json_decode($response, true);

    curl_close($curl);

    if (isset($data['errors']) && count($data['errors']) > 0) {
        foreach ($data['errors'] as $error) {
            // Verificar si el mensaje de error indica un token expirado
            if (strpos($error['message'], 'The bearer token has expired') !== false) {
                // Manejar el caso de token expirado aquí
                //  return 0;
                refresh_token();
                cargarUsuariosAsana();
            }
        }
    } else {

        // Construir la respuesta
        $response_array = array();
        $response_array['num_elements'] = count($data['data']);
        $response_array['data'] = $data['data'];

        // Devolver la respuesta JSON
        return json_encode($response_array);
    }
}
?>