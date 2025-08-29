<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// // Valida si 'user' y 'password' están en POST antes de acceder a ellos
// if (isset($_POST['user']) && isset($_POST['password'])) {

//     require_once "Conexion/Conexioni.php";
//     $miConexion = new Conexion();
//     $mysqli = $miConexion->obtenerConexion();

//     $user = $mysqli->real_escape_string($_POST['user']);
//     $password = $mysqli->real_escape_string($_POST['password']);

//     $sql = "SELECT * FROM usuarios WHERE Usuario = '$user' AND PASSWORD = '$password' AND Activo='1'";

//     $rec = $mysqli->query($sql);

//     if (!$rec) {
//         die("Error en la consulta: " . $mysqli->error);
//     }
// } else {
//     // Maneja el caso cuando los datos no están en POST
//     echo "Error: El usuario o la contraseña no están definidos.";
//     exit(); // Detén la ejecución si los valores no existen
// }

// if ($rec->num_rows != 0) {
//     $fila = $rec->fetch_assoc();

//     $_SESSION['userid'] = $fila['id'];
//     $_SESSION['ingreso'] = $user;
//     $_SESSION['tiempo'] = time();

//     $_SESSION['FechaPassword'] = $fila['FechaPassword'];
//     $_SESSION['NCliente'] = $fila['NdeCliente'];
//     $_SESSION['Nivel'] = $fila['NIVEL'];
//     $_SESSION['idusuario'] = $fila['id'];
//     $_SESSION['Direccion'] = $fila['Direccion'];
//     $_SESSION['NombreUsuario'] = $fila['Nombre'];
//     $_SESSION['ApellidoUsuario'] = $fila['Apellido'];
//     $_SESSION['Ciudad'] = $fila['Ciudad'];
//     $_SESSION['Localidad'] = $fila['Localidad'];
//     $_SESSION['Sucursal'] = $fila['Sucursal'];
//     $_SESSION['Usuario'] = $fila['Usuario'];

//     $_SESSION['NumeroRepo'] = '0000'; // ahora sí bien

//     // Log ingreso
//     $mysqli->query("INSERT INTO `Ingresos`(`idUsuario`, `Nombre`, `Fecha`, `Hora`, `ip`,`UserAgent`) VALUES ('{$fila['id']}','{$fila['Usuario']}','{$Fecha}','{$Hora}','{$ipCliente}','{$userAgent}')");

//     // Perfil
//     switch ($_SESSION['Nivel']) {
//         case 1:
//             $_SESSION['Perfil'] = "Administrador";
//             header("Location: Inicio/Cpanel.php");
//             exit;
//         case 2:
//             $_SESSION['Perfil'] = "Empleado";
//             header("location:Inicio/Cpanel.php");
//             exit;
//         case 3:
//             $_SESSION['Perfil'] = "Reparto";
//             header("location:smartphone/AdminSmartphone/SistemaTriangular/Cpanel.php");
//             exit;
//         case 4:
//             header("location:Plataforma/Bienvenidos.php");
//             exit;
//         case 6:
//             $_SESSION['Perfil'] = "Usuario Web";
//             header("location:Plataforma/Bienvenidos.php");
//             exit;
//     }
// } else {

//     $web = $_POST['web'] ?? 'no';

//     $cuentaerror = isset($_POST['cuentaerror']) ? $_POST['cuentaerror'] : 0;
//     $_SESSION['ErrIngreso'] = "Su usuario es incorrecto, intente nuevamente.";
//     if ($cuentaerror == '') {
//         $cuentaerror = 0;
//     } else {
//         $CEr = $cuentaerror;
//         $cuentaerror = ($CEr + 1);
//     }
//     if ($web == 'si') {
//         // header("location:https://www.sistemacaddy.com.ar/login.php?id=erringreso");
//     } else {
//         // header("location:iniciosesion.php?Usuario=$user&Error=Si&n=$cuentaerror");
//     }
// }
