<?php
date_default_timezone_set('America/Argentina/Cordoba');

include_once "../../Conexion/Conexioni.php";
if (isset($_POST['Empleados'])) {
    if (!isset($_SESSION['NombreUsuario']) || $_SESSION['NombreUsuario'] == '') {
        // header("location:../../../SistemaTriangular/inicio.php");
        echo json_encode(array("redirect" => 1));
        // exit();
    } else {

        $Entorno = defined('ENTORNO') ? ENTORNO : 'desconocido';
        $NombreUsuario = $_SESSION['NombreUsuario'];
        $ApellidoUsuario = isset($_SESSION['ApellidoUsuario']) ? $_SESSION['ApellidoUsuario'] : '';
        $NombreCompleto = $NombreUsuario . ' ' . $ApellidoUsuario;
        $Avatar = substr($NombreUsuario, 0, 1) . '' . substr($ApellidoUsuario, 0, 1);
        $Server = isset($_SESSION['Server']) ? $_SESSION['Server'] : 'Default';
        $Nivel = isset($_SESSION['Nivel']) ? $_SESSION['Nivel'] : 'Usuario';
        $Sucursal = isset($_SESSION['Sucursal']) ? $_SESSION['Sucursal'] : 'Córdoba';
        $time = $_SESSION['tiempo'] ?? 0;

        // Nombre del rol asignado (Roles y Permisos). Sin rol todavía, se muestra
        // el nivel en palabras para no dejar el espacio vacío.
        $RolNombre = null;
        $idUsuarioSesion = intval($_SESSION['idusuario'] ?? 0);
        if ($idUsuarioSesion > 0) {
            $resRol = $mysqli->query("
                SELECT r.nombre
                FROM usuarios u
                LEFT JOIN usuarios_roles r ON u.rol_id = r.id AND r.Eliminado = 0
                WHERE u.id = $idUsuarioSesion
            ");
            if ($resRol && $resRol->num_rows) {
                $RolNombre = $resRol->fetch_assoc()['nombre'] ?? null;
            }
        }
        if (!$RolNombre) {
            $RolNombre = intval($Nivel) === 1 ? 'SuperAdministrador' : (intval($Nivel) === 2 ? 'Administracion' : ('Nivel ' . $Nivel));
        }

        echo json_encode(array(
            'success' => 1,
            'Nombre' => $NombreCompleto,
            'Sucursal' => $Sucursal,
            'Avatar' => $Avatar,
            'Nivel' => $Nivel,
            'Rol' => $RolNombre,
            'Server' => $Server,
            'Time' => $time,
            'Entorno' => $Entorno
        ));
    }
}

if (isset($_POST["Pendientes"])) {
    $resultado = $mysqli->query("
        SELECT RazonSocial, COUNT(id) as Total, MIN(Fecha) as FechaMinima
        FROM TransClientes
        WHERE Haber = 0 AND Eliminado = 0 AND Entregado = 0 AND Devuelto = 0
        GROUP BY RazonSocial
        ORDER BY Total DESC
    ");

    $notificaciones = [];

    while ($row = $resultado->fetch_assoc()) {
        $fecha = new DateTime($row['FechaMinima']);
        $hoy = new DateTime();
        $dias_atraso = $fecha->diff($hoy)->days;

        $notificaciones[] = [
            'nombre' => $row['RazonSocial'],
            'mensaje' => "$dias_atraso días de atraso",
            'tiempo' => "Pendientes: {$row['Total']}",
            'cantidad' => $row['Total']
        ];
    }

    echo json_encode([
        'success' => "1",
        'notificaciones' => $notificaciones
    ]);
    exit;
}
