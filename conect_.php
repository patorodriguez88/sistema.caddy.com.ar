<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

date_default_timezone_set('America/Argentina/Buenos_Aires');
$user = ""; // Inicializa la variable con un valor predeterminado
$password = ""; // Inicializa la variable con un valor predeterminado
$fila = "";
unset($_SESSION['tiempo']); // Elimina el índice 'time' de la sesión

if (isset($_SESSION['seluser'])) {
  $seluser = $_SESSION['seluser'];
}

// Valida si 'user' y 'password' están en POST antes de acceder a ellos
if (isset($_POST['user']) && isset($_POST['password'])) {
  $user = $_POST['user'];
  $password = $_POST['password'];
} else {
  // Maneja el caso cuando los datos no están en POST
  echo "Error: El usuario o la contraseña no están definidos.";
  exit(); // Detén la ejecución si los valores no existen
}


require_once "Conexion/Conexioni.php";

$sql = "SELECT * FROM usuarios WHERE Usuario = '$user' AND PASSWORD = '$password' AND Activo='1'";

$rec = $mysqli->query($sql);

echo $rec->num_rows;

if ($rec->num_rows != 0) {

  $fila = $rec->fetch_assoc();
  $_SESSION['userid'] = $fila['id'];
  // $_SESSION['userid'] = $result->idusuario;
  $_SESSION['ingreso'] = $_POST['user'];
  $_SESSION['tiempo'] = time();

  $sql2 = "SELECT * FROM usuarios WHERE Usuario = '$user' and PASSWORD = '$password'";
  $estructura2 = $mysqli->query($sql2);

  // Obtener la dirección IP del cliente
  $ipCliente = $_SERVER['REMOTE_ADDR'];

  $Fecha = date('Y-m-d');
  $Hora = date('H:i');

  $row = $estructura2->fetch_array(MYSQLI_ASSOC);

  $_SESSION['FechaPassword'] = $row['FechaPassword'];
  $_SESSION['NCliente'] = $row['NdeCliente'];
  $_POST['id'] = $row['id'];
  $_SESSION['Nivel'] = $row['NIVEL'];
  $_SESSION['idusuario'] = $row['id'];
  $_SESSION['Direccion'] = $row['Direccion'];
  $_SESSION['NombreUsuario'] = $row['Nombre'];
  $_SESSION['ApellidoUsuario'] = $row['Apellido'];
  $_SESSION['Ciudad'] = $row['Ciudad'];
  $_SESSION['Localidad'] = $row['Localidad'];
  $_SESSION['Sucursal'] = $row['Sucursal'];
  $_SESSION['Usuario'] = $row['Usuario'];

  $userAgent = $_SERVER['HTTP_USER_AGENT'];

  $mysqli->query("INSERT INTO `Ingresos`(`idUsuario`, `Nombre`, `Fecha`, `Hora`, `ip`,`UserAgent`) VALUES ('{$row['id']}','{$row['Usuario']}','{$Fecha}','{$Hora}','{$ipCliente}','{$userAgent}')");



  //   }


  // BUSCO EL VALOR ACTUAL DEL KM
  // $sqlBuscoVariables=$mysqli->query("SELECT Nombre,Valor FROM Variables WHERE Nombre='PrecioKm'");
  // if($Variable=$mysqli->fetch_row($sqlBuscoVariables)){
  // $_SESSION['PrecioKm']=$Variable['Valor'];  
  // }  
  // // // BUSCO EL VALOR DE CAPACIDAD TOTAL DE CARGA
  // $sqlBuscoVariables=$mysqli->query("SELECT Nombre,Valor FROM Variables WHERE Nombre='CapacidadTotalCarga'");

  // $Variable=$sqlBuscoVariables->fetch_row(MYSQLI_ASSOC);

  // $_SESSION['CapacidadTotalCarga']=$Variable['Valor'];  

  // if(($_SESSION['PrecioKm']<>'')||($_SESSION['CapacidadTotalCarga']<>'')){
  // $_SESSION['VariableCalculo']=($_SESSION['PrecioKm']/$_SESSION['CapacidadTotalCarga']);  
  // }

  // // // BUSCO SI HAY ALGUN PRECIO FIJO ASIGNADO AL CLIENTE
  // if($_SESSION['NCliente']<>''){

  // $sqlBuscoServiciosxCliente=$mysqli->query("SELECT Productos.Codigo,Productos.PrecioVenta,ClientesyServicios.Servicio
  //  FROM (ClientesyServicios,Productos)
  //  WHERE ClientesyServicios.Servicio=Productos.Codigo AND ClientesyServicios.NdeCliente=".$_SESSION['NCliente']."");

  // $Servicios=$sqlBuscoServiciosxCliente->fetch_row(MYSQLI_ASSOC);

  // $_SESSION['Servicios']=$Servicios['PrecioVenta'];
  // }
  // // // DESDE ACA BUSCO LAS VARIABLES DE MAX KM Y PRECIO X KM
  // $sqlVariablesxCliente="SELECT MaxKm,PrecioKm FROM ClientesyServicios
  //  WHERE NdeCliente='.$_SESSION[NCliente].'";
  //  $SqlDato=$mysqli->query($sqlVariablesxCliente)or die(mysqli_error());;  

  //  if(mysqli_num_rows($SqlDato)==0){
  // // // $_SESSION['MaxKm']='15';
  //  $_SESSION['PrecioKmCliente']='10';

  // }else{
  // $DatosVariables=mysqli_fetch_array($sqlVariablesxCliente);
  // // // $_SESSION['MaxKm']=$DatosVariables[MaxKm];
  // $_SESSION['PrecioKmCliente']=$DatosVariables[PrecioKm];
  // }  
  // $_SESSION['MaxKm']='500';

  // $cliente=$_SESSION['NCliente'];

  // PATO PRUEBAS  
  if ($_SESSION['Nivel'] == 5) {
    $_SESSION['NumeroRepo'] == '0000';
    // header("location:Inicio/Cpanel.php");
    $_SESSION['Perfil'] = "Administrador";
  }
  // //   //Administrador
  if ($_SESSION['Nivel'] == 1) {

    $_SESSION['NumeroRepo'] == '0000';

    // header("Location: Inicio/Cpanel.php");
    $_SESSION['Perfil'] = "Administrador";
  }
  // // //Administrador
  if ($_SESSION['Nivel'] == 2) {
    $_SESSION['NumeroRepo'] == '0000';
    // header("location:Inicio/Cpanel.php");
    $_SESSION['Perfil'] = "Empleado";
  }
  // // //FLETERO
  if ($_SESSION['Nivel'] == 3) {
    // header("location:smartphone/AdminSmartphone/SistemaTriangular/Cpanel.php");
    $_SESSION['Perfil'] = "Reparto";
  }
  // // //CLIENTES SANDBOX
  if ($_SESSION['Nivel'] == 4) {
    // header("location:Plataforma/Bienvenidos.php");
    $_SESSION['Perfil'] = "Usuario Web";
  }
  // // // CIENTES PRODUCCION
  if ($_SESSION['Nivel'] == 6) {
    // header("location:Plataforma/Bienvenidos.php");
    $_SESSION['Perfil'] = "Usuario Web";
  }
} else {

  // $cuentaerror = $_POST['cuentaerror'];
  $cuentaerror = isset($_POST['cuentaerror']) ? $_POST['cuentaerror'] : 0;
  $_SESSION['ErrIngreso'] = "Su usuario es incorrecto, intente nuevamente.";
  if ($cuentaerror == '') {
    $cuentaerror = 0;
  } else {
    $CEr = $cuentaerror;
    $cuentaerror = ($CEr + 1);
  }
  if ($web == 'si') {
    // header("location:https://www.sistemacaddy.com.ar/login.php?id=erringreso");
  } else {
    // header("location:iniciosesion.php?Usuario=$user&Error=Si&n=$cuentaerror");
  }
}
