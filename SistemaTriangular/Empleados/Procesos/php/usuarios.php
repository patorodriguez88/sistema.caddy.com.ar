<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json');

$mysqli = (new Conexion())->obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    switch ($accion) {
        case 'listar_roles':
            listarRoles($mysqli);
            break;
        case 'crear_rol':
            crearRol($mysqli);
            break;
        case 'listar_permisos':
            listarPermisos($mysqli);
            break;
        case 'crear_permiso':
            crearPermiso($mysqli);
            break;
        case 'asignar_permiso_rol':
            asignarPermisoARol($mysqli);
            break;
        case 'listar_usuarios':
            listarUsuarios($mysqli);
            break;
        case 'asignar_rol_usuario':
            asignarRolAUsuario($mysqli);
            break;
        case 'eliminar_rol':
            eliminarRol($mysqli);
            break;
        case 'listar_roles_permisos':
            listarRolesConPermisos($mysqli);
            break;
        case 'listar_permisos_rol':
            listarPermisosDeRol($mysqli);
            break;
    }
}

function listarPermisosDeRol($mysqli)
{
    $rol_id = intval($_POST['rol_id']);

    $resTodos = $mysqli->query("SELECT id, nombre FROM usuarios_permisos WHERE Eliminado = 0");
    $todos = [];
    while ($row = $resTodos->fetch_assoc()) {
        $todos[] = $row;
    }

    $resAsignados = $mysqli->query("SELECT permiso_id FROM usuarios_rol_permiso WHERE rol_id = $rol_id");
    $asignados = [];
    while ($row = $resAsignados->fetch_assoc()) {
        $asignados[] = intval($row['permiso_id']);
    }

    echo json_encode([
        "todos" => $todos,
        "asignados" => $asignados
    ]);
}
function eliminarRol($mysqli)
{
    $id = intval($_POST['id']);
    $mysqli->query("UPDATE usuarios_roles SET Eliminado = 1 WHERE id = $id");
    echo json_encode(['success' => true]);
}
function listarRoles($mysqli)
{
    $res = $mysqli->query("SELECT * FROM usuarios_roles WHERE Eliminado=0");
    $roles = [];
    while ($row = $res->fetch_assoc()) {
        $roles[] = $row;
    }
    echo json_encode($roles);
}

function crearRol($mysqli)
{
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $mysqli->query("INSERT INTO usuarios_roles (nombre) VALUES ('$nombre')");
    echo json_encode(['success' => true]);
}

function listarPermisos($mysqli)
{
    $res = $mysqli->query("SELECT * FROM usuarios_permisos WHERE Eliminado=0");
    $permisos = [];
    while ($row = $res->fetch_assoc()) {
        $permisos[] = $row;
    }
    echo json_encode($permisos);
}

function crearPermiso($mysqli)
{
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $mysqli->query("INSERT INTO usuarios_permisos (nombre) VALUES ('$nombre')");
    echo json_encode(['success' => true]);
}


function listarUsuarios($mysqli)
{
    $sql = "SELECT u.id, u.Nombre AS nombre, u.Apellido AS apellido, u.Usuario, r.nombre AS rol
            FROM usuarios u
            LEFT JOIN usuarios_roles r ON u.rol_id = r.id
            WHERE u.Activo = 1 AND Nivel <> 4";
    $res = $mysqli->query($sql);
    $usuarios = [];
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }
    echo json_encode(['data' => $usuarios]);
}

function asignarRolAUsuario($mysqli)
{
    $usuario_id = intval($_POST['usuario_id']);
    $rol_id = intval($_POST['rol_id']);
    $sql = "UPDATE usuarios SET rol_id = $rol_id WHERE id = $usuario_id";
    $mysqli->query($sql);
    echo json_encode(['success' => true]);
}
function asignarPermisoARol($mysqli)
{
    $idRol = intval($_POST['rol_id']);
    $permisos = json_decode($_POST['permisos'], true); // ✅ viene como JSON string

    if (!is_array($permisos)) {
        echo json_encode(['success' => false, 'error' => 'Permisos inválidos']);
        return;
    }

    foreach ($permisos as $permiso_id) {
        $idPermiso = intval($permiso_id);
        $mysqli->query("INSERT INTO usuarios_rol_permiso (rol_id, permiso_id) VALUES ($idRol, $idPermiso)");
    }

    echo json_encode(['success' => true]);
}
function listarRolesConPermisos($mysqli)
{
    $sql = "SELECT r.id, r.nombre AS rol, p.nombre AS permiso
            FROM usuarios_roles r
            LEFT JOIN usuarios_rol_permiso rp ON r.id = rp.rol_id
            LEFT JOIN usuarios_permisos p ON rp.permiso_id = p.id
            WHERE r.Eliminado = 0
            ORDER BY r.id";

    $res = $mysqli->query($sql);
    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        if (!isset($datos[$id])) {
            $datos[$id] = [
                'rol' => $row['rol'],
                'permisos' => []
            ];
        }
        if ($row['permiso']) {
            $datos[$id]['permisos'][] = $row['permiso'];
        }
    }

    echo json_encode(['data' => array_values($datos)]);
}
