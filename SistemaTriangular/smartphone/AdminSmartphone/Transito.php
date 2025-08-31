<?php
ob_start();
session_start();
include_once "../../ConexionBD.php";
// print $_SESSION[NombreUsuario];
// if($_SESSION['NombreUsuario']==''){
// header("location:https://www.caddy.com.ar/SistemaTriangular/smartphone/iniciosesion.php");  
// }
date_default_timezone_set('Chile/Continental');
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$sqlC = mysql_query("SELECT * FROM Logistica WHERE NombreChofer='".$_SESSION['NombreUsuario']."' AND Estado='Cargada' AND Eliminado=0");
$Dato=mysql_fetch_array($sqlC);
$_SESSION['RecorridoAsignado']=$Dato[Recorrido];
$Recorrido=$_SESSION['RecorridoAsignado'];

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Triangular S.A.</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="../../../assets/css/main.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
  
  </head>
	<body class="subpage">
		<div id="page-wrapper">

			<!-- Header -->
				<div id="header-wrapper">
					<header id="header" class="container">
						<div class="row">
							<div class="12u">

								<!-- Logo -->
									<h1><a href="#" id="logo">Caddy Yo lo llevo!</a></h1>
						<?php
								include('../MenuSmartphone/MenuPrincipal.php');
								?>

							</div>
						</div>
					</header>
				</div>

			<!-- Content -->
				<div id="content-wrapper">
					<div id="content">
						<div class="container">
							<div class="row">
								<div class="12u">

									<!-- Main Content -->
										<section>
<!-- <script>
        function alerta()
        {
//         var mensaje;
        var opcion = confirm("ESTE REMITO NO ES DE TU RECORRIDO, DESEAS CONTINUAR ?");
        if (opcion == true) {
//         mensaje = "Has clickado OK";
        } else {
//         mensaje = "Has clickado Cancelar";
        location.href ="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/Transito.php";
        }
//         document.getElementById("ejemplo").innerHTML = mensaje;
        }
</script> -->
<?                      
                      
if($_POST['Valor']=='Aceptar'){
  
$Recorrido=$_SESSION['RecorridoAsignado'];
  if($Recorrido==''){
    
  ?>
  <script language="JavaScript" type="text/javascript">
  alert("NO TENES NINGUN RECORRIDO ASINGADO, NO SE PUEDE CARGAR CODIGOS DE REPARTO.");
  location.href ="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/Cpanel.php";
  </script>                      
  <?
  }
  
$CodigoSeguimiento=$_POST['codigo_t'];    
$Vacio=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND TipoDeComprobante='Remito' AND Eliminado=0");    
$datos=mysql_fetch_array($Vacio);
		
  if(mysql_num_rows($Vacio)==0){
    ?>
		<script language="JavaScript" type="text/javascript">
			alert("ERROR EL REMITO NO EXISTE");
      location.href ="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/Cpanel.php";
		</script>
		<?
    }elseif($datos[Entregado]==1){
			?>
		<script language="JavaScript" type="text/javascript">
    alert("EL REMITO <? echo $CodigoSeguimiento; ?> YA FUE ENTREGADO");
    location.href ="https://www.caddy.com.ar/SistemaTriangular/smartphone/AdminSmartphone/Cpanel.php";
   </script>
		<?
		}


//INGRESAR DATOS EN TABLA SEGUIMIENTO
$fecha=date('Y-m-d');
$Sucursal=$_SESSION['Sucursal'];
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$CodigoSeguimiento=$_POST['codigo_t'];
$Nombre=$datos[ClienteDestino];
$Direccion=$datos[DomicilioDestino];
$Sucursal=$_SESSION[Sucursal];
$Dni=$datos[DocumentoDestino];      
if($_POST['observaciones_t']==''){
$Observaciones="Yo lo llevo!";  
}else{
$Observaciones=$_POST['observaciones_t'];			
}      
$Transportista=$_SESSION['NombreUsuario'];
      
$sql3="INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`,`Entregado`, `Estado`,`Observaciones`,
NombreCompleto,Dni,Destino)
VALUES ('{$fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','0','En Transito','{$Observaciones}','{$Nombre}'
,'{$Dni}','{$Direccion}')";
mysql_query($sql3);
//ACTUALIZA TRANSCLIENTES CON EL USUARIO ACTUAL
$sql4="UPDATE `TransClientes` SET `Recorrido`='$Recorrido',`Transportista`='$Transportista' WHERE CodigoSeguimiento='$CodigoSeguimiento'";  
mysql_query($sql4);
		
header('location:Cpanel.php');
  }      

a:    
echo "<form class='feature-image' action='' method='post' style='widht:100%'>";
echo "<h2>Indicaremos que este Remito fue cargado por vos en tu camioneta, Ingrese aqui los datos del seguimiento:</h2>";
echo "<h3>Codigo de Seguimiento:</h3>";
echo "<h2><input type='text' name='codigo_t' value='$_GET[Codigo]'/></h2>";
echo "<h3>Observaciones:</h3>";
echo "<textarea rows='2' cols='40' name='observaciones_t' value=''></textarea>";
echo "<h3><input class='button-big' align='left' name='Valor' type='submit' value='Aceptar'>";
?>
<input class="button-big"  type="button" value="Cancelar" onClick="location.href='Cpanel.php'"></h3>
<?php
echo "</form>";	
z:
  ?>
</section>											

								</div>
							</div>
						</div>
					</div>
				</div>
		<!-- Scripts -->
			<script src="../../../assets/js/jquery.min.js"></script>
			<script src="../../../assets/js/skel.min.js"></script>
			<script src="../../../assets/js/skel-viewport.min.js"></script>
			<script src="../../../assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="../../../assets/js/main.js"></script>
	</body>
</html>