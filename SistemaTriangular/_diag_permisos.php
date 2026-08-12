<?php
// Diagnóstico temporal — borrar del servidor después de usarlo.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Menu/php/permisos_menu.php';

header('Content-Type: text/plain; charset=utf-8');

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

echo "\ntieneMenuPermiso('Home', 'Panel 1') = " . (tieneMenuPermiso('Home', 'Panel 1') ? 'true' : 'false') . "\n";
echo "tieneMenuPermiso('Logistica', 'Ordenes de Salida') = " . (tieneMenuPermiso('Logistica', 'Ordenes de Salida') ? 'true' : 'false') . "\n";
