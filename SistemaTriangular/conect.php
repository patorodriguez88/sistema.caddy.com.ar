<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Conexión rápida directa (evita Conexioni)
// $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);
// $configFile = $isLocal ? __DIR__ . "/Conexion/config_local" : __DIR__ . "/Conexion/config";

// $dbConf = json_decode(file_get_contents($configFile), true);
// if (!$dbConf || !isset($dbConf[0])) {
//     die("Error: archivo de configuración de DB no encontrado");
// }
$host = strtolower($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
$host = preg_replace('/:\d+$/', '', $host);
$host = preg_replace('/^www\./', '', $host);

$isLocal   = in_array($host, ['localhost', '127.0.0.1']);
$isSandbox = str_starts_with($host, 'sandbox.');
$isProd    = ($host === 'sistema.caddy.com.ar');

if ($isLocal) {
    $configFile = __DIR__ . "/Conexion/config_local";
} elseif ($isSandbox) {
    $configFile = __DIR__ . "/Conexion/config_sandbox";
} else {
    $configFile = __DIR__ . "/Conexion/config";
}

if (!is_file($configFile)) {
    die("Error: archivo de configuración no encontrado: " . basename($configFile));
}

$dbConf = json_decode(file_get_contents($configFile), true);
if (!$dbConf || !isset($dbConf[0])) {
    die("Error: configuración inválida en " . basename($configFile));
}
$dbConf = $dbConf[0];

// Configuración de la base de datos
$socket = null;
$port = null;

if ($isLocal) {
    $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
}
// $dbConf = $dbConf[0];
$mysqli = new mysqli(
    $dbConf['server'] ?? 'localhost',
    $dbConf['user'] ?? 'root',
    $dbConf['password'] ?? '',
    $dbConf['database'] ?? '',
    $port,
    $socket
);

// $mysqli = new mysqli(
//     $dbConf['server'] ?? 'localhost',
//     $dbConf['user'] ?? 'root',
//     $dbConf['password'] ?? '',
//     $dbConf['database'] ?? '',
//     null,
//     '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'
// );

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
date_default_timezone_set('America/Argentina/Buenos_Aires');

$user = ""; // Inicializa la variable con un valor predeterminado
$password = ""; // Inicializa la variable con un valor predeterminado
$fila = "";
$Fecha = date('Y-m-d');
$Hora = date('H:i');
// Obtener la dirección IP del cliente
$ipCliente = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

unset($_SESSION['tiempo']); // Elimina el índice 'time' de la sesión

if (isset($_SESSION['seluser'])) {
    $seluser = $_SESSION['seluser'];
}
// Valida si 'user' y 'password' están en POST antes de acceder a ellos
if (isset($_POST['user']) && isset($_POST['password'])) {

    $user = $mysqli->real_escape_string($_POST['user']);
    $password = $mysqli->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM usuarios WHERE Usuario = '$user' AND PASSWORD = '$password' AND Activo='1' AND NIVEL IN(1,2)";

    $rec = $mysqli->query($sql);

    if (!$rec) {
        die("Error en la consulta: " . $mysqli->error);
    }
} else {
    // Maneja el caso cuando los datos no están en POST
    echo "Error: El usuario o la contraseña no están definidos.";
    exit(); // Detén la ejecución si los valores no existen
}

if ($rec->num_rows != 0) {
    $fila = $rec->fetch_assoc();

    $_SESSION['userid'] = $fila['id'];
    $_SESSION['ingreso'] = $user;
    $_SESSION['tiempo'] = time();

    $_SESSION['FechaPassword'] = $fila['FechaPassword'];
    $_SESSION['NCliente'] = $fila['NdeCliente'];
    $_SESSION['Nivel'] = $fila['NIVEL'];
    $_SESSION['idusuario'] = $fila['id'];
    $_SESSION['Direccion'] = $fila['Direccion'];
    $_SESSION['NombreUsuario'] = $fila['Nombre'];
    $_SESSION['ApellidoUsuario'] = $fila['Apellido'];
    $_SESSION['Ciudad'] = $fila['Ciudad'];
    $_SESSION['Localidad'] = $fila['Localidad'];
    $_SESSION['Sucursal'] = $fila['Sucursal'];
    $_SESSION['Usuario'] = $fila['Usuario'];

    $_SESSION['NumeroRepo'] = '0000'; // ahora sí bien

    // Log ingreso
    // $mysqli->query("INSERT INTO `Ingresos`(`idUsuario`, `Nombre`, `Fecha`, `Hora`, `ip`,`UserAgent`) VALUES ('{$fila['id']}','{$fila['Usuario']}','{$Fecha}','{$Hora}','{$ipCliente}','{$userAgent}')");

    // Perfil
    switch ($_SESSION['Nivel']) {
        case 1:
            $_SESSION['Perfil'] = "Administrador";
            header("Location: Inicio/Cpanel.php");
            exit;
        case 2:
            $_SESSION['Perfil'] = "Empleado";
            header("location:Inicio/Cpanel.php");
            exit;
        case 3:
            $_SESSION['Perfil'] = "Reparto";
            header("location:smartphone/AdminSmartphone/SistemaTriangular/Cpanel.php");
            exit;
        case 4:
            header("location:Plataforma/Bienvenidos.php");
            exit;
        case 6:
            $_SESSION['Perfil'] = "Usuario Web";
            header("location:Plataforma/Bienvenidos.php");
            exit;
    }
} else {

    $web = $_POST['web'] ?? 'no';

    $cuentaerror = isset($_POST['cuentaerror']) ? $_POST['cuentaerror'] : 0;
    $_SESSION['ErrIngreso'] = "Su usuario es incorrecto, intente nuevamente.";
    if ($cuentaerror == '') {
        $cuentaerror = 0;
    } else {
        $CEr = $cuentaerror;
        $cuentaerror = ($CEr + 1);
    }
    if ($web == 'si') {
        header("location:https://www.sistema.caddy.com.ar/login.php?id=erringreso");
    } else {
        header("location:inicio.php?Usuario=$user&Error=Si&n=$cuentaerror");
    }
}
