<?php
session_start();
// ============================================================
// Bootstrap de sesión (UNIFICADO para todo el sistema Caddy)
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);
    $cookieDomain = $isLocal ? '' : '.caddy.com.ar'; // cubre www.caddy.com.ar y www.sistema.caddy.com.ar

    session_name('CADDYSESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => $cookieDomain ?: null,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Helper: detectar si es AJAX
function es_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
// ¿La sesión está autenticada?
function tieneSesion(): bool
{
    return !empty($_SESSION['Usuario'])
        || !empty($_SESSION['idusuario'])
        || !empty($_SESSION['NCliente']);
}
// ============================================================
// Clase de conexión
// ============================================================
class Conexion
{
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $socket;
    private $conexion;

    public function __construct()
    {
        $datos = $this->cargarDatosConexion();

        $this->server   = $datos['server']   ?? 'localhost';
        $this->user     = $datos['user']     ?? 'root';
        $this->password = $datos['password'] ?? '';
        $this->database = $datos['database'] ?? '';
        $this->port     = isset($datos['port']) ? intval($datos['port']) : 3306;
        $this->socket   = $datos['socket']   ?? null;

        if (($_SERVER['SERVER_NAME'] ?? '') === 'localhost') {
            $this->conexion = new mysqli(
                $this->server,
                $this->user,
                $this->password,
                $this->database,
                $this->port,
                $this->socket
            );
        } else {
            $this->conexion = new mysqli(
                $this->server,
                $this->user,
                $this->password,
                $this->database,
                $this->port
            );
        }

        // ❌ Si falla la conexión → limpiar sesión y redirigir SIN emitir salida previa
        if ($this->conexion->connect_error) {
            // No hagas echo aquí (rompe headers)
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            if (es_ajax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'DB_CONNECT_ERROR']);
                exit;
            }
            // header('Location: /SistemaTriangular/inicio.php');
            exit;
        }

        $this->conexion->set_charset('utf8');
        $_SESSION['server'] = $this->server;
    }

    private function cargarDatosConexion(): array
    {
        $archivo = (($_SERVER['SERVER_NAME'] ?? '') === 'localhost') ? 'config_local' : 'config';
        $path = dirname(__FILE__) . '/' . $archivo;

        if (!file_exists($path)) {
            return $this->failLoginRedirect();
        }

        $json  = file_get_contents($path);
        $datos = json_decode($json, true);

        if (!$datos || !is_array($datos) || !isset($datos[0])) {
            return $this->failLoginRedirect();
        }

        return $datos[0];
    }

    private function failLoginRedirect(): array
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        if (es_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'NO_CONFIG']);
            exit;
        }
        // header('Location: /SistemaTriangular/inicio.php');
        exit;
    }

    public function obtenerConexion(): mysqli
    {
        return $this->conexion;
    }
}

// ============================================================
// Instanciar conexión y chequear sesión
// ============================================================
$miConexion = new Conexion();
$mysqli = $miConexion->obtenerConexion();

// Tiempo máx. de inactividad (90 min)
$tiempoMaximo = 5400;

// Archivos públicos (sin sesión)
$archivoActual = basename($_SERVER['PHP_SELF'] ?? '');
$excepciones = ['conect.php', 'inicio.php', 'pages-recoverpw.html'];

// Validación de sesión
if (!in_array($archivoActual, $excepciones, true)) {

    // Expiración por inactividad
    if (isset($_SESSION['tiempo']) && (time() - $_SESSION['tiempo']) > $tiempoMaximo) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        if (es_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Session-Expired: 1');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'SESSION_EXPIRED']);
            exit;
        }
        // header('Location: /SistemaTriangular/inicio.php');
        exit;
    }

    // Sin login (acepta varias claves de sesión)
    if (!tieneSesion()) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        if (es_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Session-Expired: 1');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error_conexioni' => 'NO_AUTH']);
            exit;
        }
        header('Location: /SistemaTriangular/inicio.php');
        exit;
    }

    // Refrescar timestamp de actividad
    $_SESSION['tiempo'] = time();
}

// Importante: no cierres con "
