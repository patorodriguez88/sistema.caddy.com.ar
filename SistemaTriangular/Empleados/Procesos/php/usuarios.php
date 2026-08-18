<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";
require_once "../../../Menu/php/permisos_menu.php";
header('Content-Type: application/json');

$mysqli = (new Conexion())->obtenerConexion();

function requiereGestionRoles($mysqli)
{
    if (!usuarioPuedeGestionarRoles($mysqli)) {
        echo json_encode(['success' => false, 'error' => 'No tenés permiso para gestionar roles y permisos.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    switch ($accion) {
        case 'listar_roles':
            listarRoles($mysqli);
            break;
        case 'crear_rol':
            requiereGestionRoles($mysqli);
            crearRol($mysqli);
            break;
        case 'eliminar_rol':
            requiereGestionRoles($mysqli);
            eliminarRol($mysqli);
            break;
        case 'listar_permisos':
            listarPermisos($mysqli);
            break;
        case 'crear_permiso':
            requiereGestionRoles($mysqli);
            crearPermiso($mysqli);
            break;
        case 'eliminar_permiso':
            requiereGestionRoles($mysqli);
            eliminarPermiso($mysqli);
            break;
        case 'listar_permisos_rol':
            listarPermisosDeRol($mysqli);
            break;
        case 'asignar_permiso_rol':
            requiereGestionRoles($mysqli);
            asignarPermisoARol($mysqli);
            break;
        case 'listar_usuarios':
            listarUsuarios($mysqli);
            break;
        case 'reenviar_acceso':
            requiereGestionRoles($mysqli);
            reenviarAccesoUsuario($mysqli);
            break;
        case 'desactivar_usuario':
            requiereGestionRoles($mysqli);
            desactivarUsuario($mysqli);
            break;
        case 'obtener_rol_usuario':
            obtenerRolDeUsuario($mysqli);
            break;
        case 'asignar_rol_usuario':
            requiereGestionRoles($mysqli);
            asignarRolAUsuario($mysqli);
            break;
        case 'quitar_rol_usuario':
            requiereGestionRoles($mysqli);
            quitarRolAUsuario($mysqli);
            break;
        case 'listar_roles_permisos':
            listarRolesConPermisos($mysqli);
            break;
        case 'puede_gestionar_roles':
            echo json_encode(['puede' => usuarioPuedeGestionarRoles($mysqli)]);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción inválida']);
    }
}

function listarRoles($mysqli)
{
    $res = $mysqli->query("SELECT * FROM usuarios_roles WHERE Eliminado=0 ORDER BY nombre");
    $roles = [];
    while ($row = $res->fetch_assoc()) {
        $roles[] = $row;
    }
    echo json_encode($roles);
}

function crearRol($mysqli)
{
    $nombre = trim($_POST['nombre'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    if ($nombre === '') {
        echo json_encode(['success' => false, 'error' => 'El nombre del rol no puede estar vacío']);
        return;
    }

    $nombreEsc = $mysqli->real_escape_string($nombre);
    $existe = $mysqli->query("SELECT id FROM usuarios_roles WHERE nombre = '$nombreEsc' AND Eliminado = 0" . ($id ? " AND id <> $id" : ""));
    if ($existe && $existe->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un rol con ese nombre']);
        return;
    }

    if ($id) {
        $mysqli->query("UPDATE usuarios_roles SET nombre = '$nombreEsc' WHERE id = $id");
    } else {
        $mysqli->query("INSERT INTO usuarios_roles (nombre) VALUES ('$nombreEsc')");
    }
    echo json_encode(['success' => true]);
}

function eliminarRol($mysqli)
{
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Rol inválido']);
        return;
    }
    $mysqli->query("UPDATE usuarios_roles SET Eliminado = 1 WHERE id = $id");
    $mysqli->query("UPDATE usuarios SET rol_id = NULL WHERE rol_id = $id");
    echo json_encode(['success' => true]);
}

function listarPermisos($mysqli)
{
    sincronizarPermisosMenu($mysqli, __DIR__ . '/../../../Menu/topnav.html');

    $res = $mysqli->query("SELECT * FROM usuarios_permisos WHERE Eliminado=0 ORDER BY es_sistema DESC, seccion, nombre");
    $permisos = [];
    while ($row = $res->fetch_assoc()) {
        $permisos[] = $row;
    }
    echo json_encode($permisos);
}

function crearPermiso($mysqli)
{
    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre === '') {
        echo json_encode(['success' => false, 'error' => 'El nombre del permiso no puede estar vacío']);
        return;
    }

    $nombreEsc = $mysqli->real_escape_string($nombre);
    $existe = $mysqli->query("SELECT id FROM usuarios_permisos WHERE nombre = '$nombreEsc' AND Eliminado = 0");
    if ($existe && $existe->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un permiso con ese nombre']);
        return;
    }

    $mysqli->query("INSERT INTO usuarios_permisos (nombre, seccion, es_sistema) VALUES ('$nombreEsc', 'Manual', 0)");
    echo json_encode(['success' => true]);
}

function eliminarPermiso($mysqli)
{
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Permiso inválido']);
        return;
    }
    $check = $mysqli->query("SELECT es_sistema FROM usuarios_permisos WHERE id = $id");
    $row = $check ? $check->fetch_assoc() : null;
    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Permiso inexistente']);
        return;
    }
    if (intval($row['es_sistema']) === 1) {
        echo json_encode(['success' => false, 'error' => 'Este permiso es del sistema y no se puede eliminar']);
        return;
    }
    $mysqli->query("UPDATE usuarios_permisos SET Eliminado = 1 WHERE id = $id");
    $mysqli->query("DELETE FROM usuarios_rol_permiso WHERE permiso_id = $id");
    echo json_encode(['success' => true]);
}

function listarPermisosDeRol($mysqli)
{
    $rol_id = intval($_POST['rol_id'] ?? 0);

    $resTodos = $mysqli->query("SELECT id, nombre, seccion, es_sistema FROM usuarios_permisos WHERE Eliminado = 0 ORDER BY es_sistema DESC, seccion, nombre");
    $todos = [];
    while ($row = $resTodos->fetch_assoc()) {
        $todos[] = $row;
    }

    $asignados = [];
    if ($rol_id) {
        $resAsignados = $mysqli->query("SELECT permiso_id FROM usuarios_rol_permiso WHERE rol_id = $rol_id");
        while ($row = $resAsignados->fetch_assoc()) {
            $asignados[] = intval($row['permiso_id']);
        }
    }

    echo json_encode([
        "todos" => $todos,
        "asignados" => $asignados
    ]);
}

function asignarPermisoARol($mysqli)
{
    $idRol = intval($_POST['rol_id'] ?? 0);
    if (!$idRol) {
        echo json_encode(['success' => false, 'error' => 'Seleccioná un rol']);
        return;
    }

    $permisos = json_decode($_POST['permisos'] ?? '[]', true);
    if (!is_array($permisos)) {
        echo json_encode(['success' => false, 'error' => 'Permisos inválidos']);
        return;
    }

    // Reemplaza la asignación completa del rol (evita acumular duplicados y permite desmarcar).
    $mysqli->query("DELETE FROM usuarios_rol_permiso WHERE rol_id = $idRol");

    foreach ($permisos as $permiso_id) {
        $idPermiso = intval($permiso_id);
        if ($idPermiso > 0) {
            $mysqli->query("INSERT INTO usuarios_rol_permiso (rol_id, permiso_id) VALUES ($idRol, $idPermiso)");
        }
    }

    echo json_encode(['success' => true]);
}

function listarUsuarios($mysqli)
{
    $sql = "SELECT u.id, u.Nombre AS nombre, u.Apellido AS apellido, u.Usuario, u.NIVEL,
                   u.NotificacionAccesoEnviada, u.NotificacionAccesoFecha, u.UltimoAcceso,
                   r.id AS rol_id, r.nombre AS rol
            FROM usuarios u
            LEFT JOIN usuarios_roles r ON u.rol_id = r.id AND r.Eliminado = 0
            WHERE u.Activo = 1 AND u.NIVEL IN (1, 2, 7)
            ORDER BY u.NIVEL, u.Nombre, u.Apellido";
    $res = $mysqli->query($sql);
    $usuarios = [];
    $nombresNivel = [1 => 'SuperAdministrador', 2 => 'Administracion', 7 => 'Operaciones'];
    while ($row = $res->fetch_assoc()) {
        $row['nivel_nombre'] = $nombresNivel[intval($row['NIVEL'])] ?? 'Administracion';
        $usuarios[] = $row;
    }
    echo json_encode(['data' => $usuarios]);
}

// Baja logica (Activo=0), nunca DELETE fisico - el id de usuario queda
// referenciado como texto en un monton de tablas historicas (Tesoreria.Usuario,
// Ctasctes.Usuario, etc.), borrar la fila de verdad dejaria esos registros
// sin ese dato. Con Activo=0 el usuario deja de aparecer en listarUsuarios()
// (ya filtra WHERE Activo=1) y no puede loguearse mas, pero la fila y su
// historial quedan intactos - mismo criterio que ValorxKilometro/Servicios
// usan para "eliminar" sin perder trazabilidad.
function desactivarUsuario($mysqli)
{
    $id = intval($_POST['usuario_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Usuario inválido.']);
        return;
    }

    $idPropio = intval($_SESSION['idusuario'] ?? 0);
    if ($id === $idPropio) {
        echo json_encode(['success' => false, 'error' => 'No podés eliminar tu propio usuario.']);
        return;
    }

    $res = $mysqli->query("SELECT id, NIVEL, Activo FROM usuarios WHERE id = $id LIMIT 1");
    $usuario = $res ? $res->fetch_assoc() : null;
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
        return;
    }
    if (intval($usuario['Activo']) === 0) {
        echo json_encode(['success' => false, 'error' => 'Ese usuario ya está eliminado.']);
        return;
    }

    // No dejar el sistema sin ningún SuperAdministrador activo.
    if (intval($usuario['NIVEL']) === 1) {
        $resCount = $mysqli->query("SELECT COUNT(*) AS c FROM usuarios WHERE NIVEL = 1 AND Activo = 1");
        $cantidad = $resCount ? intval($resCount->fetch_assoc()['c']) : 0;
        if ($cantidad <= 1) {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar: es el único SuperAdministrador activo.']);
            return;
        }
    }

    $mysqli->query("UPDATE usuarios SET Activo = 0 WHERE id = $id LIMIT 1");
    echo json_encode(['success' => true]);
}

// Reenvía el mail de acceso al sistema con una contraseña temporal NUEVA — la
// original no se puede recuperar porque solo se guarda hasheada.
function reenviarAccesoUsuario($mysqli)
{
    $id = intval($_POST['usuario_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Usuario inválido.']);
        return;
    }

    $res = $mysqli->query("SELECT id, Nombre, Mail, Usuario, NIVEL FROM usuarios WHERE id = $id LIMIT 1");
    $usuario = $res ? $res->fetch_assoc() : null;

    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
        return;
    }

    if (!in_array(intval($usuario['NIVEL']), [1, 2, 7], true)) {
        echo json_encode(['success' => false, 'error' => 'Este usuario no es una cuenta de sistema.']);
        return;
    }

    $mail = trim((string)($usuario['Mail'] ?: $usuario['Usuario']));
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'El usuario no tiene un mail válido cargado.']);
        return;
    }

    $passwordTemporalPlano = bin2hex(random_bytes(5));
    $passwordHash = password_hash($passwordTemporalPlano, PASSWORD_DEFAULT);
    $fechaVencida = '2000-01-01';

    $stmt = $mysqli->prepare("UPDATE usuarios SET password_hash=?, FechaPassword=? WHERE id=? LIMIT 1");
    $stmt->bind_param("ssi", $passwordHash, $fechaVencida, $id);
    $stmt->execute();
    $stmt->close();

    // Nueva contraseña temporal = nuevo ciclo de "primer ingreso": vuelve a Pendiente
    // hasta que la persona entre de nuevo (conect.php la reactiva ahí).
    $mysqli->query("UPDATE Empleados SET Inactivo = 1 WHERE Usuario = " . intval($id) . " LIMIT 1");

    require_once __DIR__ . '/../../../Funciones/php/notificar_acceso.php';
    $resultado = notificarAccesoSistema($mysqli, $id, $mail, $usuario['Nombre'], $usuario['Usuario'], $passwordTemporalPlano);

    $enviado = (isset($resultado['success']) && $resultado['success'] == 1);
    echo json_encode([
        'success' => $enviado,
        'error' => $enviado ? null : ($resultado['msg'] ?? 'No se pudo enviar el mail.'),
    ]);
}

function obtenerRolDeUsuario($mysqli)
{
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    if (!$usuario_id) {
        echo json_encode(['rol_id' => null, 'rol' => null]);
        return;
    }
    $res = $mysqli->query("
        SELECT r.id, r.nombre
        FROM usuarios u
        LEFT JOIN usuarios_roles r ON u.rol_id = r.id AND r.Eliminado = 0
        WHERE u.id = $usuario_id
    ");
    $row = $res ? $res->fetch_assoc() : null;
    echo json_encode([
        'rol_id' => $row && $row['id'] ? intval($row['id']) : null,
        'rol' => $row['nombre'] ?? null,
    ]);
}

function asignarRolAUsuario($mysqli)
{
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    $rol_id = intval($_POST['rol_id'] ?? 0);

    if (!$usuario_id || !$rol_id) {
        echo json_encode(['success' => false, 'error' => 'Seleccioná un usuario y un rol']);
        return;
    }

    $mysqli->query("UPDATE usuarios SET rol_id = $rol_id WHERE id = $usuario_id");
    echo json_encode(['success' => true]);
}

function quitarRolAUsuario($mysqli)
{
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    if (!$usuario_id) {
        echo json_encode(['success' => false, 'error' => 'Usuario inválido']);
        return;
    }
    $mysqli->query("UPDATE usuarios SET rol_id = NULL WHERE id = $usuario_id");
    echo json_encode(['success' => true]);
}

function listarRolesConPermisos($mysqli)
{
    $sql = "SELECT r.id, r.nombre AS rol, p.nombre AS permiso
            FROM usuarios_roles r
            LEFT JOIN usuarios_rol_permiso rp ON r.id = rp.rol_id
            LEFT JOIN usuarios_permisos p ON rp.permiso_id = p.id AND p.Eliminado = 0
            WHERE r.Eliminado = 0
            ORDER BY r.nombre";

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
