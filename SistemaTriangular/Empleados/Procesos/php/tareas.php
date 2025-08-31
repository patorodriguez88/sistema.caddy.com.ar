<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('../../../Conexion/Conexioni.php');
include_once('asana_api.php');

date_default_timezone_set('America/Argentina/Cordoba');
$Usuario = $_SESSION['Usuario'];
$Nivel = $_SESSION['Nivel'];

if (isset($_POST['ObtenerTarea'])) {
    $id = intval($_POST['id']);
    $sql = $mysqli->query("SELECT * FROM Tareas WHERE id = $id");
    $tarea = $sql->fetch_assoc();
    echo json_encode($tarea);
    exit;
}
if (isset($_POST['ActualizarTarea'])) {
    $id = intval($_POST['id']);
    $puntos = intval($_POST['Puntos']);
    $completada = intval($_POST['Completada']);
    $usuario_id = $_SESSION['idusuario']; // o como se llame tu variable de sesión

    // Traer datos actuales de la tarea
    $sql = $mysqli->query("SELECT * FROM Tareas WHERE id = $id");
    $tarea = $sql->fetch_assoc();
    // $yaFinalizada = (intval($tarea['Completada']) === "true");
    $yaFinalizada = ($tarea['Estado'] === 'true'); // ahora sí devuelve booleano correctamente

    // Actualizar tarea
    $update = $mysqli->query("UPDATE Tareas SET Puntos = $puntos, Estado = $completada WHERE id = $id");

    if ($update) {
        // Si se está finalizando por primera vez → insertar en PuntosEmpleados
        if ($completada === 1 && !$yaFinalizada) {

            if (!empty($tarea['gid_asana'])) {
                $sql_empleados = $mysqli->query("SELECT id FROM usuarios WHERE gid_asana = " . $tarea['Responsable_gid'] . "");
            } else {
                $sql_empleados = $mysqli->query("SELECT id FROM usuarios WHERE gid_hubspot = " . $tarea['Responsable_gid'] . "");
            }
            $row_empleado = $sql_empleados->fetch_assoc();
            // $empleado_id = intval($sql_empleados['id']); // reemplazá el campo si se llama distinto
            $row_empleado = $sql_empleados->fetch_assoc();
            $empleado_id = intval($row_empleado['id']); // ✅ BIEN

            $motivo = "Tarea completada: " . $mysqli->real_escape_string($tarea['NombreTarea']);
            $fecha = date("Y-m-d H:i:s");

            $insert = $mysqli->query("INSERT INTO PuntosEmpleados (usuario_id, puntos, motivo, fecha, registrado_por,tarea_id)
          VALUES ($empleado_id, $puntos, '$motivo', '$fecha', $usuario_id, $id)");

            if (!$insert) {
                echo json_encode(["success" => false, "message" => "Error al registrar los puntos."]);
                exit;
            }
        }

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar la tarea."]);
    }

    exit;
}
if (isset($_POST['Nivel'])) {

    echo json_encode(array('Nivel' => $Nivel));
}

//FUNCIONES //NO ACTIVO
// function contarArchivos($id){

// // Vemos si existe la carpeta
// $folder_path = '../../Tareas/'.$id;

// // Escanea el directorio y obtén una lista de archivos y directorios
// $archivos = scandir($folder_path);

// if ($archivos !== false) {
//     // Resta 2 para eliminar las referencias '.' y '..' que se incluyen en el resultado de scandir
//     $numArchivos = count($archivos) - 2;

//     } else {

//     $numArchivos=0;
// }

// return $numArchivos;

// }

function fecha_formato($fechaStr)
{
    // Convertir la cadena de fecha a un objeto DateTime
    $fecha = new DateTime($fechaStr);

    // Configurar el idioma español para el formato de fecha
    setlocale(LC_TIME, 'es_ES.UTF-8');

    // Formatear la fecha
    // $fechaFormateada = strftime('%e de %B de %Y', $fecha->getTimestamp());
    $fechaFormateada = date('j \d\e F \d\e Y', $fecha->getTimestamp());
    return $fechaFormateada;
}


function NombreUsuario($usuario)
{

    global $mysqli;
    $sql = $mysqli->query("SELECT CONCAT(Nombre,' ',Apellido)as Nombre FROM usuarios WHERE usuario='$usuario'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $Usuario = $row['Nombre'];

    return $Usuario;
}
//NO ACTIVO
function folder_exists_on_server($folder_path)
{

    return is_dir($folder_path);
}

// BUCAR TOTAL ARCHIVOS
function file_exists_on_server($git_asana)
{
    // return file_exists($file_path);

    $array_json = Get_attachments($git_asana);

    // Decodificar la cadena JSON
    $array_data = json_decode($array_json, true);

    // Obtener el valor de "num_elements"
    $num_elements = isset($array_data['num_elements']) ? $array_data['num_elements'] : 0;

    return $num_elements;
}

function list_files_in_folder($git_asana)
{
    $array_json = Get_attachments($git_asana);

    // Decodificar la cadena JSON
    $array_data = json_decode($array_json, true);

    if (isset($array_data['data'])) {
        foreach ($array_data['data'] as $element) {
            // echo "gid: " . $element['gid'] . "<br>";
            // echo "name: " . $element['name'] . "<br>";
            // echo "resource_type: " . $element['resource_type'] . "<br>";
            // echo "resource_subtype: " . $element['resource_subtype'] . "<br>";
            // echo "connected_to_app: " . ($element['connected_to_app'] ? 'true' : 'false') . "<br><br>";

            $file = $element['name'];
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $element_gid = $element['gid'];

            echo '<div class="card mb-0 shadow-none border">';
            echo '<div class="p-2">';
            echo '<div class="row align-items-center">';

            echo '<div class="col-auto">';
            echo    '<div class="avatar-sm">';
            echo    '<span class="avatar-title rounded">' . $extension . '</span>';
            echo    '</div>';
            echo '</div>';
            echo '<div class="col ps-0">';
            echo    '<a href="javascript:void(0);" class="text-muted fw-bold">' . $file . '</a>';
            echo    '<p class="mb-0">7.05 MB</p>';
            echo '</div>';
            echo '<div class="col-auto">';
            // echo    '<a onclick="eliminar_archivo(\''.$file.'\')" class="btn btn-link btn-lg text-muted">';
            // echo    '<i class="dripicons-trash"></i>';
            // echo    '</a>';
            echo    '<a href="https://app.asana.com/app/asana/-/get_asset?asset_id=' . $element_gid . '" class="btn btn-link btn-lg text-muted">';
            echo    '<i class="dripicons-download"></i>';
            echo    '</a>';
            echo '</div>';

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
}


//NO ACTIVO
function list_files_in_folder_NA($folder_path)
{
    // Verificar si la carpeta existe
    if (is_dir($folder_path)) {
        // Abrir el directorio
        if ($handle = opendir($folder_path)) {
            // Recorrer todos los archivos en la carpeta
            while (false !== ($file = readdir($handle))) {

                // Obtener la extensión del archivo
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                // Excluir archivos especiales . y ..
                if ($file != "." && $file != "..") {
                    // Mostrar el nombre del archivo dentro del HTML
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $file . '"');

                    echo '<div class="card mb-0 shadow-none border">';
                    echo '<div class="p-2">';
                    echo '<div class="row align-items-center">';

                    echo '<div class="col-auto">';
                    echo    '<div class="avatar-sm">';
                    echo    '<span class="avatar-title rounded">' . $extension . '</span>';
                    echo    '</div>';
                    echo '</div>';
                    echo '<div class="col ps-0">';
                    echo    '<a href="javascript:void(0);" class="text-muted fw-bold">' . $file . '</a>';
                    echo    '<p class="mb-0">7.05 MB</p>';
                    echo '</div>';
                    echo '<div class="col-auto">';
                    echo    '<a onclick="eliminar_archivo(\'' . $file . '\')" class="btn btn-link btn-lg text-muted">';
                    echo    '<i class="dripicons-trash"></i>';
                    echo    '</a>';
                    echo    '<a href="https:www.sistemacaddy.com.ar/SistemaTriangular/' . $folder_path . '/' . $file . '" class="btn btn-link btn-lg text-muted">';
                    echo    '<i class="dripicons-download"></i>';
                    echo    '</a>';
                    echo '</div>';

                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            // Cerrar el directorio
            closedir($handle);
        }
    } else {
        echo "La carpeta no existe.";
    }
}

function reducirTexto($texto, $longitudMaxima = 150)
{
    // Verificar si el texto es más largo que la longitud máxima
    if (strlen($texto) > $longitudMaxima) {
        // Cortar el texto a la longitud máxima y agregar "..."
        $textoReducido = substr($texto, 0, $longitudMaxima) . '...';
    } else {
        // Si el texto es igual o más corto que la longitud máxima, no se hace ningún cambio
        $textoReducido = $texto;
    }
    return $textoReducido;
}

//TAREAS DETALLE
if (isset($_POST['Tareas_detalle'])) {

    $sql = $mysqli->query("SELECT * FROM Tareas WHERE id='" . $_POST['id'] . "'");
    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

//TAREAS LISTA
if (isset($_POST['Tareas_lista'])) {

    Actualizar_Task();

    if ($Nivel == 1) {

        if (isset($_POST['Estado']) && $_POST['Estado'] !== '') {

            $sql = $mysqli->query("SELECT * FROM Tareas WHERE Estado='" . $_POST['Estado'] . "'");
        } else {

            $sql = $mysqli->query("SELECT * FROM Tareas");
        }
    } elseif ($Nivel == 2) {

        if ($_POST['Estado']) {

            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "' AND Tareas.Estado='" . $_POST['Estado'] . "'");
        } else {
            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "'");
        }
    }

    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

if (isset($_POST['Tareas_comentarios_total'])) {

    $sql = $mysqli->query("SELECT COUNT(id)as Total FROM Tareas_comentarios WHERE idTarea='" . $_POST['id'] . "'");

    $row = $sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('Total' => $row['Total']));
}

if (isset($_POST['Tareas_comentarios'])) {
    // Obtener los datos de los comentarios
    $datos = Get_storys($_POST['gid_asana']);
    echo $datos['data'][0];
}

if (!isset($_POST['Tareas_comentarios']) || $_POST['Tareas_comentarios'] == 2) {

    // Obtener los datos de los comentarios
    $datos = isset($_POST['gid_asana']) ? Get_storys($_POST['gid_asana']) : array();
    //  echo $datos;

    // Verificar si se obtuvieron datos correctamente
    if (isset($datos['data'])) {
        foreach ($datos['data'] as $index => $row) {
            // Convertir la fecha y hora del comentario
            $objetoTiempo = strtotime($row['fechaHora']);
            // Formatear el objeto de tiempo como 'hh:mm hs'
            $horaFormateada = date('h:i', $objetoTiempo) . ' hs';
            // Imprimir el comentario en HTML

?>
            <div class="d-flex align-items-start mt-3">
                <a class="pe-3" href="#"></a>
                <div class="w-100 overflow-hidden">
                    <h5 class="mt-0"><?php echo $row['usuario']; ?><a class="text-muted h6"><?php echo ' ' . $row['fechaHora'] . ' ' . $horaFormateada; ?></a></h5>
                    <?php echo $row['comentario']; ?>
                </div>
            </div>
    <?php
        }
    }
}

//MOSTRAR ARCHIVOS
if (isset($_POST['Tareas_archivos'])) {

    $gid_asana = $_POST['gid_asana'];

    list_files_in_folder($gid_asana);
}

//NO ACTIVO
// if($_POST['Tareas_archivos']==1){

//     $id=$_POST['id'];

//     // Vemos si existe la carpeta
//     $folder_path = '../../Tareas/'.$id;

//     if (folder_exists_on_server($folder_path)) {

//         // Llamar a la función para listar los archivos en la carpeta
//         list_files_in_folder($folder_path);

//     } else {

//     }

// }


if (isset($_POST['Usuarios'])) {

    $sql = $mysqli->query("SELECT id,CONCAT(Nombre,' ',Apellido)as Nombre,Usuario FROM usuarios where Nivel =2 AND Activo=1 AND Estado='Activo'");

    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode($rows);
}


if (isset($_POST['Subir_comentario'])) {

    $id = $_POST['id'];
    $Comentario = $_POST['Comentario'];
    $Fecha = date('Y-m-d');
    $Hora = date('H:i');
    $gid_asana = $_POST['gid'];

    //primero creo el comentario en asana
    if (Create_story($gid_asana, $id, $Comentario)) {

        echo json_encode(array('success' => 1));
    } else {

        echo json_encode(array('success' => 0));
    }


    // $sql = $mysqli->prepare("INSERT INTO `Tareas_comentarios`(`idTarea`, `Usuario`, `Comentario`,`Fecha`,`Hora`,`gid_asana`) VALUES (?,?,?,?,?,?)");

    // // Verificar si la preparación de la consulta fue exitosa
    // if ($sql) {
    //     // Asociar los parámetros de la sentencia preparada con los valores proporcionados
    //     $sql->bind_param("issssi", $id, $Usuario, $Comentario,$Fecha,$Hora,$gid);

    //     // Ejecutar la consulta
    //     $sql->execute();

    //     // Verificar si la consulta se ejecutó correctamente
    //     if ($sql->affected_rows > 0) {

    //         echo json_encode(array('success'=>1));

    //     } else {

    //         echo json_encode(array('success'=>0,'error'=>'Error al insertar el comentario.'.$mysqli->error));
    //     }

    // } else {
    //     // Si la preparación de la consulta falla, mostrar un mensaje de error
    //     echo json_encode(array('success'=>0,'error'=>'Error al preparar la consulta.'));
    // }

}


if (isset($_POST['Crear_Tareas'])) {

    // Obtener los valores de $_POST
    $Titulo = $_POST['Titulo'];
    $Descripcion = $_POST['Descripcion'];
    $FechaCarga = $_POST['Fecha_carga'];
    $FechaEntrega = $_POST['Fecha_entrega'];
    $Puntos = $_POST['Puntos'];
    $UsuarioResponsable = $_POST['Usuario'];
    $Estado = $_POST['completado'];
    $gid_asana = $_POST['gid_asana'];
    $creador = $_POST['creador'];
    $Responsable_gid = $_POST['responsable_gid'];
    $Repetir = "0";
    $Peridodicidad = "0";
    $gid_hubspot = $_POST['gid_hubspot'];
    // Fecha en formato "2-Apr-2024"
    $fecha_original = $FechaEntrega;

    // Convertir a objeto DateTime
    // $fecha_objeto = DateTime::createFromFormat('j-M-Y', $fecha_original);

    // Obtener la fecha formateada
    // $fecha_formateada = $fecha_objeto->format('Y-m-d');


    $fecha_objeto = DateTime::createFromFormat('Y-m-d', $FechaEntrega);

    if (!$fecha_objeto) {
        echo json_encode(array(
            'success' => 0,
            'error' => 'Formato de fecha inválido: ' . $FechaEntrega
        ));
        exit();
    }

    $fecha_formateada = $fecha_objeto->format('Y-m-d');




    //Creao la tarea
    if ($gid_asana == '') {

        $Projects = '1202454550277567'; //TRIANGULAR S.A.
        $workspaces = '734348733635084'; //DINTER
        $gid_asana = Create_task($Projects, $Titulo, $Descripcion, $fecha_formateada, $Responsable_gid, $workspaces);
    }


    // Primera consulta preparada para obtener el nombre
    $sql_nombre = $mysqli->prepare("SELECT CONCAT(usuarios.Nombre,' ',usuarios.Apellido) AS Nombre FROM usuarios WHERE gid_asana = ? ");
    $sql_nombre->bind_param("i", $creador);
    $sql_nombre->execute();
    $sql_nombre->bind_result($nombre);
    $sql_nombre->fetch(); // Obtener el resultado
    $sql_nombre->close();
    // Segunda consulta preparada para insertar en la tabla Tareas
    $sql_insert = $mysqli->prepare("INSERT INTO `Tareas`(`NombreTarea`, `Descripcion`, `UsuarioCarga`, `Responsable`,`Responsable_gid`, `FechaCarga`, `FechaEntrega`,`Puntos`, `Estado`, `gid_asana`, `Creado`, `Repetir`, `gid_hubspot` ) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Verificar si la preparación de la consulta fue exitosa
    if ($sql_insert) {
        // Asociar los parámetros de la sentencia preparada con los valores proporcionados
        $sql_insert->bind_param("ssssissisisii", $Titulo, $Descripcion, $Usuario, $UsuarioResponsable, $Responsable_gid, $FechaCarga, $FechaEntrega, $Puntos, $Estado, $gid_asana, $nombre, $Repetir, $gid_hubspot);

        // Ejecutar la consulta
        $sql_insert->execute();

        // Verificar si la consulta se ejecutó correctamente
        if ($sql_insert->affected_rows > 0) {

            //Verificar si la consulta incluye el gid de asana para actualizar en asana la etiqueta
            if ($gid_asana) {

                $idTarea = $mysqli->insert_id;
                $sql_insert->close();

                Add_a_tag_to_Task($gid_asana);
                //Cargo los comentarios.
                // Get_storys($gid_asana,$idTarea);

                echo json_encode(array('success' => 1));
            }

            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error al insertar la tarea.' . $mysqli->error));
        }
    } else {
        // Si la preparación de la consulta falla, mostrar un mensaje de error
        echo json_encode(array('success' => 0, 'error' => 'Error al preparar la consulta.' . $mysqli->error));
    }
}




if (isset($_POST['Tareas'])) {

    Actualizar_Task();

    if ($Nivel == 1) {

        if (isset($_POST['Estado'])) {

            $sql = $mysqli->query("SELECT * FROM Tareas WHERE Estado='" . $_POST['Estado'] . "'");
        } else {

            $sql = $mysqli->query("SELECT * FROM Tareas");
        }
    } elseif ($Nivel == 2) {

        if ($_POST['Estado']) {

            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "' AND Tareas.Estado='" . $_POST['Estado'] . "'");
        } else {
            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "'");
        }
    }

    ?>
    <div class="row">
        <?php

        while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

            $hoy = new DateTime();
            $fechaCarga = new DateTime($row['FechaCarga']);
            $fechaFinal = new DateTime($row['FechaEntrega']);

            $diasFaltantes = $hoy->diff($fechaFinal)->days;
            $porcentaje = 0;

            // Verificar si la fecha final está en el futuro o en el pasado
            if ($fechaFinal > $hoy) {
                $leyenda_dias = "Vence en ";
            } elseif ($fechaFinal < $hoy) {

                if ($row['Estado'] == 'false') {

                    $leyenda_dias = "Venció hace ";
                    //ACTUALIZO PUNTOS
                    $Puntos = ($diasFaltantes * -1);
                    $mysqli->query("UPDATE Tareas SET Puntos='" . $Puntos . "' WHERE id='" . $row['id'] . "' ");
                } else {

                    $leyenda_dias = "";
                }
            } elseif ($fechaFinal == $hoy) {
                $leyenda_dias = "Hoy es el día ! ";
            }


            // Calcular el porcentaje de progreso basado en la cantidad de días restantes
            if ($diasFaltantes >= 30) {
                $porcentaje = 100;
            } elseif ($diasFaltantes >= 20 && $diasFaltantes <= 29) {
                $porcentaje = 80;
            } elseif ($diasFaltantes >= 10 && $diasFaltantes <= 19) {
                $porcentaje = 50;
            } elseif ($diasFaltantes >= 5 && $diasFaltantes <= 9) {
                $porcentaje = 20;
            } elseif ($diasFaltantes >= 1 && $diasFaltantes <= 4) {
                $porcentaje = 10;
            }

            $descripcion = reducirTexto($row['Descripcion']);
            $sql_count = $mysqli->query("SELECT COUNT(id)as Total FROM Tareas_comentarios WHERE idTarea='" . $row['id'] . "'");
            $row_count = $sql_count->fetch_array(MYSQLI_ASSOC);
            $claseStatus = '';

            $status = $row['Estado'];
            if ($status == 'false') {
                $claseStatus = 'badge bg-danger text-white'; // Clase para status "Finished"
                $claseRibbon = 'ribbon-danger';
                $namestatus = 'Pendiente';
            } elseif ($status == 'true') {
                $claseStatus = 'badge bg-success text-white'; // Clase para status "InProgress"
                $claseRibbon = 'ribbon-success';
                $namestatus = 'Finalizado';
            } else {
                $claseRibbon = 'ribbon-warning';
            }
            $UsuarioCarga = NombreUsuario($row['UsuarioCarga']);
            $Responsable = $row['Responsable'];

        ?>

            <!-- <div class="row"> -->
            <div class="col-md-6 col-xxl-3 w-25">
                <!-- project card -->
                <div class="card d-block ribbon-box">
                    <div class="card-body">
                        <div class="ribbon <?php echo $claseRibbon; ?> float-right"><i class="mdi mdi-format-list-bulleted-type mr-1"></i><?php echo $row['Puntos']; ?> Puntos</div>

                        <!-- project title-->
                        <h4 class="mt-0">
                            <span class="badge badge-outline-dark"><?php echo $row['id']; ?></span>
                            <a onclick="ver_detalle(<?php echo $row['id']; ?>)" style="cursor:pointer" class="text-title"><?php echo $row['NombreTarea']; ?></a>
                        </h4>

                        <span class="badge <?php echo $claseStatus; ?>"><?php echo $namestatus; ?></span>

                        <p class="text-muted font-13 my-3 tareas_description"><?php echo $descripcion; ?><a onclick="ver_detalle(<?php echo $row['id']; ?>)" style="cursor:pointer" class="fw-bold text-muted">view more</a>
                        </p>

                        <!-- project detail-->
                        <p class="mb-1">
                            <span class="pe-2 text-nowrap mb-2 d-inline-block">
                                <i class="mdi mdi-format-list-bulleted-type text-muted"></i>
                                <b><?php echo $row['Puntos']; ?></b> Puntos
                            </span>
                            <span class="text-nowrap mb-2 d-inline-block">
                                <i class="mdi mdi-comment-multiple-outline text-muted"></i>
                                <b><?php echo $row_count['Total']; ?></b> Comentarios
                            </span>
                            <span class="text-nowrap mb-2 d-inline-block">
                                <i class="mdi mdi-file-table text-muted"></i>
                                <b><?php echo file_exists_on_server($row['gid_asana']); ?></b> Archivos
                            </span>

                        </p>
                        <div id="tooltip-container">
                            <p class="mb-1 fw-bold">
                                Solicita: <b><?php echo $row['Creado']; ?></b>
                            </p>
                            <p class="mb-1 fw-bold">
                                Responsable: <b><?php echo $Responsable; ?></b>
                            </p>
                            <p class="mb-1 fw-bold">
                                Cargó en Caddy: <b><?php echo $UsuarioCarga; ?></b>
                            </p>

                            <p class="mb-1 fw-bold">
                                Fecha de Entrega: <b><?php echo fecha_formato($row['FechaEntrega']); ?></b>
                            </p>

                            <p class="mb-1 text-warning">
                                <?php echo $row['Modificar_Fecha_obs']; ?>
                            </p>
                            <a target="_blank" href="https://app.asana.com/0/1202454550277567/<?php echo $row['gid_asana']; ?>">Ver en Asana</a>

                        </div>
                    </div> <!-- end card-body-->
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item p-3">
                            <!-- project progress-->
                            <p class="mb-2 fw-bold"><?php echo $leyenda_dias; ?> <span class="float-end"><?php echo $diasFaltantes; ?> días</span></p>
                            <div class="progress progress-sm">
                                <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $porcentaje; ?>">

                                </div><!-- /.progress-bar -->
                            </div><!-- /.progress -->
                        </li>
                    </ul>
                </div> <!-- end card-->
            </div> <!-- end col -->
            <!-- </div> -->
        <?php

        }
        ?>
    </div>
<?php
}

if (isset($_POST['Tareas_kanban'])) {

    if ($Nivel == 1) {

        if ($_POST['Estado']) {

            $sql = $mysqli->query("SELECT * FROM Tareas WHERE Estado='" . $_POST['Estado'] . "'");
        } else {

            $sql = $mysqli->query("SELECT * FROM Tareas");
        }
    } elseif ($Nivel == 2) {

        if ($_POST['Estado']) {

            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "' AND Tareas.Estado='" . $_POST['Estado'] . "'");
        } else {
            $sql = $mysqli->query("SELECT Tareas.* FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "'");
        }
    }


?>

    <div class="row">
        <div class="col-12">
            <div class="board">
                <div class="tasks" data-plugin="dragula" data-containers='["task-list-one", "task-list-two", "task-list-three", "task-list-four"]'>
                    <h5 class="mt-0 task-header">TODO (3)</h5>
                    <?php
                    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

                    ?>
                        <div id="task-list-one" class="task-list-items">
                            <?php
                            if ($row['Estado'] == true) {
                            ?>

                                <!-- Task Item -->
                                <div class="card mb-0">
                                    <div class="card-body p-3">
                                        <small class="float-end text-muted">18 Jul 2018</small>
                                        <span class="badge bg-danger">High</span>

                                        <h5 class="mt-2 mb-2">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#task-detail-modal" class="text-body">iOS App home page</a>
                                        </h5>

                                        <p class="mb-0">
                                            <span class="pe-2 text-nowrap mb-2 d-inline-block">
                                                <i class="mdi mdi-briefcase-outline text-muted"></i>
                                                iOS
                                            </span>
                                            <span class="text-nowrap mb-2 d-inline-block">
                                                <i class="mdi mdi-comment-multiple-outline text-muted"></i>
                                                <b>74</b> Comments
                                            </span>
                                        </p>

                                        <div class="dropdown float-end">
                                            <a href="#" class="dropdown-toggle text-muted arrow-none" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical font-18"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-pencil me-1"></i>Edit</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-delete me-1"></i>Delete</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-plus-circle-outline me-1"></i>Add People</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-exit-to-app me-1"></i>Leave</a>
                                            </div>
                                        </div>

                                        <p class="mb-0">
                                            <img src="assets/images/users/avatar-2.jpg" alt="user-img" class="avatar-xs rounded-circle me-1" />
                                            <span class="align-middle">Robert Carlile</span>
                                        </p>
                                    </div> <!-- end card-body -->
                                </div>

                            <?php
                            }
                            ?>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- dragula js-->
    <script src="../../../hyper/dist/saas/assets/js/vendor/dragula.min.js"></script>
    <!-- demo js -->
    <script src="../../../hyper/dist/saas/assets/js/ui/component.dragula.js"></script>

<?php

}


if (isset($_POST['Eliminar_archivo'])) {

    $ruta_archivo = $_POST['Ruta'];

    // Verificar si el archivo existe antes de intentar eliminarlo
    if (file_exists('../../Tareas/' . $ruta_archivo)) {
        // Intentar borrar el archivo
        if (unlink('../../Tareas/' . $ruta_archivo)) {

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => 'No se pudo borrar el archivo.'));
        }
    } else {

        echo json_encode(array('success' => 0, 'error' => 'El archivo no existe.'));
    }
}
//BUSCO NOMBRE USUARIO
if (isset($_POST['NombreUsuario'])) {

    $Sistema = $_POST['Sistema'];

    if ($_POST['gid']) {
        if ($Sistema === "asana") {
            $sql = $mysqli->query("SELECT id,CONCAT(Nombre,' ',Apellido)as Nombre,Usuario FROM usuarios where Nivel =2 AND Activo=1 AND Estado='Activo' AND gid_asana='" . $_POST['gid'] . "'");
        } else if ($Sistema === "hubspot") {
            $sql = $mysqli->query("SELECT id,CONCAT(Nombre,' ',Apellido)as Nombre,Usuario FROM usuarios where Nivel =2 AND Activo=1 AND Estado='Activo' AND gid_hubspot='" . $_POST['gid'] . "'");
        }
    } else {

        $sql = $mysqli->query("SELECT CONCAT(Nombre,' ',Apellido)as Nombre,Usuario FROM usuarios WHERE usuario='" . $_POST['usuario'] . "'");
    }

    $row = $sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success' => 1, 'NombreUsuario' => $row['Nombre'], 'Usuario' => $row['Usuario']));
}

if (isset($_POST['Totales'])) {
    if ($Nivel == 1) {
        // Realizar la consulta SQL para contar el total de registros en cada estado
        $sql_totales = $mysqli->query("SELECT COUNT(id) as Total, SUM(Puntos)as Total_Puntos FROM Tareas");
        $sql_finalizado = $mysqli->query("SELECT COUNT(id) as total_finalizado, SUM(Puntos)as total_finalizado_puntos FROM Tareas WHERE Estado = 'true'");
        $sql_pendientes = $mysqli->query("SELECT COUNT(id) as total_pendientes, SUM(Puntos)as total_pendientes_puntos FROM Tareas WHERE Estado = 'false'");
    } else {
        // Realizar la consulta SQL para contar el total de registros en cada estado
        $sql_totales = $mysqli->query("SELECT COUNT(id) as Total, SUM(Puntos)as Total_Puntos FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "'");
        $sql_finalizado = $mysqli->query("SELECT COUNT(id) as total_finalizado, SUM(Puntos)as total_finalizado_puntos FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "' AND Tareas.Estado='true'");
        $sql_pendientes = $mysqli->query("SELECT COUNT(id) as total_pendientes, SUM(Puntos)as total_pendientes_puntos FROM Tareas INNER JOIN usuarios ON usuarios.gid_asana = Tareas.Responsable_gid WHERE usuarios.Usuario='" . $Usuario . "' AND Tareas.Estado='false'");
    }
    // Obtener los totales de cada estado
    $row_totales = $sql_totales->fetch_assoc();
    $row_finalizado = $sql_finalizado->fetch_assoc();
    $row_pendientes = $sql_pendientes->fetch_assoc();

    // Calcular la productividad
    $productividad = 0;
    if ($row_totales['Total'] != 0) {
        $productividad = ($row_finalizado['total_finalizado'] / $row_totales['Total']) * 100;
    }

    echo json_encode(array(
        'Totales' => $row_totales['Total'],
        'Finalizados' => $row_finalizado['total_finalizado'],
        'Pendientes' => $row_pendientes['total_pendientes'],
        'Productividad' => round($productividad, 2),
        'Totales_Puntos' => $row_totales['Total_Puntos'],
        'Totales_Finalizados' => $row_finalizado['total_finalizado_puntos'],
        'Totales_Pendientes' => $row_pendientes['total_pendientes_puntos'] // Redondear la productividad a 2 decimales
    ));
}

if (isset($_POST['FinalizarTarea'])) {
    // Preparar la consulta SQL
    $sql = $mysqli->prepare("UPDATE Tareas SET Estado='Finalizado' WHERE id=?");

    // Verificar si la preparación de la consulta tuvo éxito
    if ($sql) {
        // Vincular parámetros y ejecutar la consulta
        $sql->bind_param("i", $_POST['id']);
        if ($sql->execute()) {
            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0, 'error' => $mysqli->error));
        }
        // Cerrar la consulta preparada
        $sql->close();
    } else {

        echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    }
}

//ELIMINAR TAREA
if (isset($_POST['Eliminar_tarea'])) {

    // Preparar la consulta SQL
    $query = "DELETE FROM Tareas WHERE id=?";

    // Preparar la declaración SQL
    if ($stmt = $mysqli->prepare($query)) {
        // Vincular parámetros a la declaración preparada
        $stmt->bind_param("i", $id);

        $id = $_POST['id_tarea'];

        if ($stmt->execute()) {

            if ($_POST['gid_asana']) {


                Delete_a_tag_to_Task($_POST['gid_asana']);
            }

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => $mysqli->error));
        }
        // Cerrar la consulta preparada
        $stmt->close();
    } else {

        echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    }
}

if (isset($_POST['Actualizar_tarea'])) {

    $Puntos = $_POST['Puntos'];

    $id = $_POST['id_tarea'];

    $Modificar_Fecha = $_POST['Modificar_Fecha'];

    if ($Modificar_Fecha == true) {

        $MF = 1;
    } else {

        $MF = 0;
    }

    // Preparar la consulta SQL
    $sql = $mysqli->prepare("UPDATE Tareas SET Puntos= ?,Modificar_Fecha_entrega= ? WHERE id= ? ");

    // Verificar si la preparación de la consulta tuvo éxito
    if ($sql) {
        // Vincular parámetros y ejecutar la consulta
        $sql->bind_param("iii", $Puntos, $MF, $id);

        if ($sql->execute()) {

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => $mysqli->error));
        }

        // Cerrar la consulta preparada
        $sql->close();
    } else {

        echo json_encode(array('success' => 0, 'error' => $mysqli->error));
    }
}

if (isset($_POST['Tareas_radios'])) {

    // Consulta para obtener datos de la base de datos
    $sql = "SELECT gid_asana,CONCAT(usuarios.Nombre,' ',usuarios.Apellido) AS Nombre FROM usuarios WHERE gid_asana<>0";
    $result = $mysqli->query($sql);

    // Crear array para almacenar los datos
    $data = array();

    // Verificar si hay resultados y almacenarlos en el array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Convertir el array a formato JSON y devolverlo
    echo json_encode($data);
}

if (isset($_POST['Tareas_lista_puntos'])) {
    $FechaInicio = $_POST['FechaInicio'];
    $FechaFinal = $_POST['FechaFinal'];
    // Consulta para obtener datos de la base de datos
    // $sql = "SELECT FechaEntrega,Responsable,SUM(Puntos)as Puntos FROM `Tareas` WHERE FechaEntrega >='$FechaInicio' AND FechaEntrega<='$FechaFinal' GROUP BY Responsable;";
    $sql = "SELECT FechaEntrega,Responsable,Puntos FROM `Tareas` WHERE FechaEntrega >='$FechaInicio' AND FechaEntrega<='$FechaFinal' GROUP BY Responsable;";
    $result = $mysqli->query($sql);

    // Crear array para almacenar los datos
    $data = array();

    // Verificar si hay resultados y almacenarlos en el array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Convertir el array a formato JSON y devolverlo
    echo json_encode(array('data' => $data));
}

if (isset($_POST['DashboardPuntos'])) {
    $fechaInicio = $_POST['FechaInicio'] ?? null;
    $fechaFinal = $_POST['FechaFinal'] ?? null;

    // Total por empleado en el rango
    $query1 = "SELECT Responsable, SUM(Puntos) as Puntos 
               FROM Tareas 
               WHERE FechaEntrega BETWEEN '$fechaInicio' AND '$fechaFinal'
               GROUP BY Responsable";

    $res1 = $mysqli->query($query1);
    $resumen = [];

    while ($r = $res1->fetch_assoc()) {
        $resumen[] = $r;
    }

    // Puntos mensuales (últimos 6 meses)
    $query2 = "SELECT DATE_FORMAT(FechaEntrega, '%Y-%m') as mes, SUM(Puntos) as puntos 
               FROM Tareas 
               GROUP BY mes 
               ORDER BY mes DESC 
               LIMIT 6";

    $res2 = $mysqli->query($query2);
    $evolucion = [];

    while ($r = $res2->fetch_assoc()) {
        $evolucion[] = $r;
    }

    echo json_encode([
        "resumen" => $resumen,
        "evolucion" => array_reverse($evolucion) // del más viejo al más nuevo
    ]);
    exit;
}
