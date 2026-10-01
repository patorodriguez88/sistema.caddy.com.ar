<?php
// DIAGNÓSTICO TEMPORAL (solo sandbox) - por qué el menú no aparece. Borrar después de usar.
require_once __DIR__ . '/Conexion/sesion.php';
$headersAntes = headers_sent();
iniciarSesionSistema();
header('Content-Type: text/plain; charset=utf-8');

if (stripos($_SERVER['HTTP_HOST'] ?? '', 'sandbox.') !== 0) {
    exit("Solo disponible en sandbox.\n");
}
if (empty($_SESSION['idusuario'])) {
    echo "Sin sesión del sistema en este pedido.\n";
    echo "session_name: " . session_name() . " | status: " . session_status() . " | headers antes: " . ($headersAntes ? 'si' : 'no') . "\n";
    echo "cookies recibidas (solo nombres): " . implode(', ', array_keys($_COOKIE)) . "\n";
    exit;
}

require_once __DIR__ . '/Menu/php/permisos_menu.php';

echo "session_name: " . session_name() . " | status: " . session_status() . "\n";
echo "cookies recibidas (solo nombres): " . implode(', ', array_keys($_COOKIE)) . "\n";
echo "idusuario: " . $_SESSION['idusuario'] . " | Nivel: " . ($_SESSION['Nivel'] ?? '(no)') . " | Usuario: " . ($_SESSION['Usuario'] ?? '(no)') . "\n";

require __DIR__ . '/Conexion/conexion_publica.php';
$base = $mysqli->query("SELECT DATABASE() b")->fetch_assoc()['b'] ?? '?';
echo "base que usa el menú (conexion_publica): $base\n";
$id = intval($_SESSION['idusuario']);
$u = $mysqli->query("SELECT Usuario, NIVEL, ACTIVO, rol_id FROM usuarios WHERE id = $id");
echo "usuario en esa base: " . json_encode($u ? $u->fetch_assoc() : $mysqli->error) . "\n";
$t = $mysqli->query("SHOW TABLES LIKE 'usuarios_rol_permiso'");
echo "tabla usuarios_rol_permiso: " . ($t && $t->num_rows ? 'existe' : 'NO EXISTE') . "\n";

$permisos = obtenerPermisosDelUsuario();
echo "permisos que lee el menú: " . count($permisos) . " | ve todo: " . (veTodoElMenu() ? 'si' : 'no') . "\n";
echo "primeros: " . implode(', ', array_slice($permisos, 0, 5)) . "\n";
echo "secciones visibles: ";
foreach (['Home', 'Servicios', 'Clientes', 'Ventas', 'Admin', 'Logistica', 'Datos'] as $s) {
    echo $s . '=' . (tieneAlgunPermisoSeccion($s) ? 'si' : 'no') . ' ';
}
echo "\n";
