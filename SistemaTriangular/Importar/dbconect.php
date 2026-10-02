<?php
session_start();
if($_SESSION['Usuario']==''){
header('Location:/SistemaTriangular/inicio.php');  
}
// simple conexion a la base de datos
function connect(){
$db_server   = 'localhost';
$db_name     = 'dinter6_triangular';
$db_username = 'dinter6_usuarioweb';
$db_password = 'usuarioelectronico'; 

	return new mysqli($db_server,$db_username,$db_password,$db_name);
}
$con = connect();
if (!$con->set_charset("utf8")) {//asignamos la codificación comprobando que no falle
       die("Error cargando el conjunto de caracteres utf8");
}



?>