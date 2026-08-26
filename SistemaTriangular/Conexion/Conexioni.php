<?php
// Mismo motivo que en plataforma.caddy.com.ar/Conexion/Conexioni.php: los dos
// sistemas viven en el mismo cPanel y usaban el nombre de cookie por default
// de PHP (PHPSESSID) sin fijar dominio/path - eso puede hacer que el
// navegador mande la misma cookie a ambos subdominios (o que compartan
// session.save_path) y que un login acá termine mezclado con la sesión de
// plataforma.caddy.com.ar, ya que las dos usan las mismas claves de
// $_SESSION (Usuario, NCliente, etc.). Nombre de cookie propio + dominio
// explícito (host-only) corta el cruce sin depender de la config del
// hosting.
session_name('CADDY_SISTEMA_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // host-only: nunca .caddy.com.ar
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Evita que el navegador guarde en caché (o restaure con el botón "atrás"/bfcache)
// una pantalla que requiere sesión — si no, después de un logout o de que la sesión
// venza, "volver" podía mostrar la última vista tal cual quedó, sin volver a validar.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$BASE_PATH = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($BASE_PATH === '' || $BASE_PATH === '.') $BASE_PATH = '';

define('BASE_PATH', $BASE_PATH);

function redirect_login()
{
    // Ruta absoluta a propósito: BASE_PATH es relativo a la carpeta de la página actual
    // (ej. Empleados/), así que "BASE_PATH/inicio.php" apuntaba a un inicio.php que no
    // existe ahí — inicio.php vive únicamente en la raíz de SistemaTriangular.
    header('Location: /SistemaTriangular/inicio.php');
    exit;
}
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

        $this->server   = $datos['server'] ?? 'localhost';
        $this->user     = $datos['user'] ?? 'root';
        $this->password = $datos['password'] ?? '';
        $this->database = $datos['database'] ?? '';
        $this->port     = isset($datos['port']) ? intval($datos['port']) : 3306;
        $this->socket   = $datos['socket'] ?? null;

        if ($_SERVER['SERVER_NAME'] === 'localhost') {
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

        // 🔴 SI FALLA LA CONEXIÓN → REDIRIGE AL LOGIN
        if ($this->conexion->connect_error) {
            echo "❌ Error de conexión: " . $this->conexion->connect_error;
            session_destroy();
            // header("Location: /SistemaTriangular/inicio.php");
            redirect_login();
            exit;
        }

        $this->conexion->set_charset("utf8");

        $_SESSION['server'] = $this->server;
    }

    private function cargarDatosConexion(): array
    {
        // 🔄 NUEVO: detectamos host para decidir entorno
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $httpHost   = $_SERVER['HTTP_HOST']   ?? '';
        $host       = strtolower($httpHost ?: $serverName);

        if ($serverName === 'localhost') {
            $archivo = "config_local";
            if (!defined('ENTORNO')) define('ENTORNO', 'local');
        } elseif (strpos($host, 'sandbox.sistema.caddy.com.ar') !== false) {
            $archivo = "config_sandbox";
            if (!defined('ENTORNO')) define('ENTORNO', 'sandbox');
        } else {
            $archivo = "config";
            if (!defined('ENTORNO')) define('ENTORNO', 'produccion');
        }

        $path = dirname(__FILE__) . "/" . $archivo;

        if (!file_exists($path)) {
            session_destroy();

            redirect_login();
            // header("Location: /SistemaTriangular/inicio.php");
            exit;
        }

        $json  = file_get_contents($path);
        $datos = json_decode($json, true);

        if (!$datos || !is_array($datos) || !isset($datos[0])) {
            session_destroy();
            redirect_login();
            // header("Location: /SistemaTriangular/inicio.php");
            exit;
        }

        return $datos[0];
    }

    public function obtenerConexion(): mysqli
    {
        return $this->conexion;
    }
}


// 🧠 INSTANCIAR CONEXIÓN
$miConexion = new Conexion();
$mysqli = $miConexion->obtenerConexion();

// 🕒 Tiempo máximo de sesión (90 min)
$tiempoMaximo = 5400;

// Archivos que no requieren sesión activa
$archivoActual = basename($_SERVER['PHP_SELF']);
$excepciones = ['conect.php', 'inicio.php', 'pages-recoverpw.html'];

// 🔐 Validación completa de sesión (saltear si es API)
if (!defined('ALLOW_NO_SESSION') || ALLOW_NO_SESSION !== true) {

    // 🔐 Validación completa de sesión
    if (!in_array($archivoActual, $excepciones)) {
        // Sesión expirada por inactividad
        if (isset($_SESSION['tiempo']) && (time() - $_SESSION['tiempo']) > $tiempoMaximo) {
            session_destroy();
            $_SESSION = [];
        }

        // Sin sesión válida
        if (empty($_SESSION['Usuario'])) {
            session_destroy();

            // Si es llamada AJAX
            if (
                !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            ) {
                header('X-Session-Expired: 1');
                http_response_code(401);
                exit;
            }

            // Carga normal
            // redirect_login($);
            redirect_login();
            // header("Location: /SistemaTriangular/inicio.php");
            exit;
        }

        // Si sigue activo, actualizamos el tiempo
        $_SESSION['tiempo'] = time();

        // Contraseña vencida: no dejar pasar a ninguna otra pantalla hasta que la cambie.
        $excepcionesCambioPassword = ['CambiarPasswordObligatorio.php', 'cambiar_password_obligatorio.php'];
        if (!empty($_SESSION['DebeCambiarPassword']) && !in_array($archivoActual, $excepcionesCambioPassword)) {
            header('Location: /SistemaTriangular/CambiarPasswordObligatorio.php');
            exit;
        }
    }
}
