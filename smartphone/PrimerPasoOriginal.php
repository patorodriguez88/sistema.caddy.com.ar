<?php
session_start();
ob_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
if($_POST['user']=='pruebafletero'){
mysql_select_db("dinter6_triangularcopia",$conexion);  
}else{
mysql_select_db("dinter6_triangular",$conexion);
}

mysql_set_charset("utf8"); 

if ($_POST['user']==''){
header('location:iniciosesion.php');	
}
$seluser=$_SESSION['seluser'];
$user= $_POST['user'];
$password= $_POST['password'];

function verificar_login($user,$password,&$result) {
    
	$sql = "SELECT * FROM usuarios WHERE Usuario = '$user' and PASSWORD = '$password'";
	$rec = mysql_query($sql);
    $count = 0;
 
    while($row = mysql_fetch_object($rec)){
        $count++;
        $result = $row;
  	}
    if($count == 1){
    return 1;
    }else{
    return 0;
    }
}
if(verificar_login($_POST['user'],$_POST['password'],$result) == 1){
$_SESSION['userid'] = $result->idusuario;
$_SESSION['ingreso']=$_POST['user'];

}else{
$usuario=$_GET['Usuario'];
$_SESSION['seluser']="Email";
$_SESSION['Nivel']="";
	
header("location:iniciosesion.php?Usuario=$usuario");
  
$_SESSION['ErrIngreso']="Su usuario es incorrecto, intente nuevamente.";

	if ($_SESSION['CuentaError']==''){
		$_SESSION['CuentaError']=0;
	}{
	$CEr=$_SESSION['CuentaError'];
	$_SESSION['CuentaError']=($CEr+1);
	}

}

$sql2 = mysql_query("SELECT * FROM usuarios WHERE Usuario = '$user' and PASSWORD = '$password'");
$row = mysql_fetch_array($sql2);
	 $_SESSION['NCliente']= $row[NdeCliente];
	 $_POST['id']= $row[NdeCliente];
	 $_SESSION['Nivel']=$row[NIVEL];
	 $_SESSION['idusuario']=$row[id];
	 $_SESSION['Direccion']=$row[Direccion];
	 $_SESSION['NombreUsuario']=$row[Nombre];
	 $_SESSION['Ciudad']=$row[Ciudad];
	 $_SESSION['Localidad']=$row[Localidad];
	 $_SESSION['Distribuidora']=$row[Sucursal];
   $_SESSION['Usuario']=$row[Usuario];
   $_SESSION['Sucursal']=$row[Sucursal]; 

$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='$row[id]' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$_SESSION['RecorridoAsignado']=$Dato[Recorrido];

//Administrador
if ($_SESSION['Nivel']==1){
header("location:../smartphone/AdminSmartphone/Cpanel.php");
$_SESSION['Perfil']="Administrador";
}
// kiosco
if ($_SESSION['Nivel']==2){
header('location:../smartphone/AdminSmartphone/Cpanel.php');
$_SESSION['Perfil']="Kiosco";
// kiosco
}
if($_SESSION['Nivel']==3){
header("location:../smartphone/AdminSmartphone/Cpanel.php");
$_SESSION['Perfil']="Recorrido";
}
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
b:
ob_end_flush();