<?php
session_start();
include_once "conexionmy.php";
$seluser=$_SESSION['seluser'];
$user= $_POST['user'];
$password= $_POST['password'];

$sql2 = "SELECT * FROM usuarios WHERE Usuario = '$user' AND PASSWORD = '$password'";
// $sql2 = "SELECT * FROM usuarios";

$estructura2= mysql_query($sql2);
$num=mysql_num_rows($estructura2);
if (mysql_num_rows($estructura2)<>''){

			while ($row = mysql_fetch_row($estructura2)){
				 $_SESSION['NCliente']= $row[6];
				 $_POST['id']= $row[8];
				 $_SESSION['Nivel']= $row[4];
				 $_SESSION['idusuario']=$row[0];
				 $_SESSION['Direccion']=$row[8];
				 $_SESSION['NombreUsuario']=$row[1];
				 $_SESSION['Ciudad']=$row[10];
				 $_SESSION['Localidad']=$row[9];
				 $_SESSION['Distribuidora']=$row[11];
				 }

				//Administrador
			if ($_SESSION['Nivel']==1){
			header('Location:ZonaClientes/Cpanel.php');
			$_SESSION['Perfil']="Administrador";
			}
			//kiosco
			if ($_SESSION['Nivel']==2){
			header('Location:ZonaClientes/Cpanel.php');
			$_SESSION['Perfil']="Kiosco";
			}

			}else{
$usuario=$_GET['Usuario'];
$_SESSION['seluser']="Email";
print $num;
// header("location:index.html");

$_SESSION['ErrIngreso']="Su usuario es incorrecto, intente nuevamente.";
}
?>