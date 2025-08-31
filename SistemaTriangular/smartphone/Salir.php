<?
session_start();
$_SESSION = array(); 
// unset($_SESSION['NCliente']);
// unset($_POST['id']);
// unset($_SESSION['Nivel']);
// unset($_SESSION['idusuario']);
// unset($_SESSION['Direccion']);
// unset($_SESSION['NombreUsuario']);
// unset($_SESSION['Ciudad']);
// unset($_SESSION['Localidad']);
// unset($_SESSION['Sucursal']);
// unset($_SESSION['Usuario']);
session_destroy();

header('location:https://www.caddy.com.ar/reparto');	 
?>