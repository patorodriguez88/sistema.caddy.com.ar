<?php
// Diagnóstico temporal — borrar del servidor después de usarlo.
header('Content-Type: text/plain; charset=utf-8');

echo "Cookie PHPSESSID recibida: " . ($_COOKIE['PHPSESSID'] ?? '(NO LLEGO NINGUNA COOKIE)') . "\n";

$resultado = session_start();
var_dump($resultado);
echo "session_start() devolvio: " . ($resultado ? 'true' : 'false') . "\n";
echo "session_id() despues de iniciar: " . session_id() . "\n";
echo "session_status(): " . session_status() . " (1=disabled, 2=none, 3=active)\n\n";

require_once __DIR__ . '/Menu/php/permisos_menu.php';

echo "Usuario (sesion): " . ($_SESSION['Usuario'] ?? '(vacio)') . "\n";
echo "idusuario (sesion): " . ($_SESSION['idusuario'] ?? '(vacio)') . "\n";
echo "Nivel (sesion): " . ($_SESSION['Nivel'] ?? '(vacio)') . "\n\n";

$permisos = obtenerPermisosDelUsuario();

if ($permisos === null) {
    echo "obtenerPermisosDelUsuario() devolvio: NULL (sin rol asignado -> ve todo el menu)\n";
} else {
    echo "obtenerPermisosDelUsuario() devolvio " . count($permisos) . " permisos:\n";
    foreach ($permisos as $p) {
        echo "  - $p\n";
    }
}
