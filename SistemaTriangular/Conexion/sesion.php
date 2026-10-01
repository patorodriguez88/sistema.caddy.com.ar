<?php
// Única forma de abrir la sesión del sistema: la usan el login (conect.php), Conexioni.php y el
// menú (Menu/php/permisos_menu.php).
//
// Nombre de cookie propio + dominio host-only: sistema y plataforma.caddy.com.ar viven en el mismo
// cPanel y con el PHPSESSID por defecto las sesiones se mezclaban.
//
// Tiene que ser la MISMA en todos lados: en 50 pantallas el menú se arma antes de incluir
// Conexioni.php, y el menú abría la sesión con el nombre por defecto (otra sesión, vacía). Por eso
// en esas pantallas el menú no sabía quién estaba logueado: con la regla vieja ("sin rol ve todo")
// Operaciones veía el menú completo en el Panel; el 1/10 con la regla nueva dejó afuera a todos.
//
// La sesión tiene que abrirse ANTES de mandar cualquier HTML: con los headers ya enviados PHP no la
// abre y $_SESSION queda vacío (el menú no sabe quién está logueado). Por eso cada pantalla con menú
// arranca con `require_once .../Conexion/sesion.php; iniciarSesionSistema();` o con Conexioni.php.
// Si una pantalla nueva se olvida, queda registrado en el log del servidor.
function iniciarSesionSistema(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    if (headers_sent($archivo, $linea)) {
        error_log("iniciarSesionSistema(): la sesión se abre tarde (salida empezada en $archivo:$linea) - "
            . ($_SERVER['SCRIPT_NAME'] ?? '') . ' tiene que abrir la sesión antes del HTML');
    }
    session_name('CADDY_SISTEMA_SESSID');
    @session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // host-only: nunca .caddy.com.ar
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @session_start();
}
