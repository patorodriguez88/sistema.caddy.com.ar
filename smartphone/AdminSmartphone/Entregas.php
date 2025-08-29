<?php
ob_start();
session_start();
include_once "../../ConexionBD.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
date_default_timezone_set('Chile/Continental');
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Triangular S.A.</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
<!-- 		<link rel="stylesheet" href="../../../assets/css/main.css" /> -->
    <link rel="stylesheet" href="../assets/css/main.css" />
    <link rel="stylesheet" href="../css/smartphone.css" />
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
									<h1><a href="#" id="logo">Triangular</a></h1>
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
<?		
echo "<form class='feature-image' action='AgregarRepoSmartphone.php' method='post'>";
echo "<header><h2>Ingrese aqui los datos del remito:</h2></header>";
// echo "<form class='login' action='' method='POST'  style='width:450px;';>";
if($_SESSION['ClienteA']==''){
	$Grupo="SELECT NdeCliente,nombrecliente,Smartphones FROM Clientes WHERE Smartphones=1 ORDER BY nombrecliente ASC";
	$estructura= mysql_query($Grupo);
	echo "<h3><label>Cliente Emisor:</label></h3><select name='clienteemisor_t' style='float:center;width:260px;' size='1';>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[0]."'>".$row[1]."</option>";
	}
	echo "</select>";
}else{
	echo "<h3><label>Cliente Emisor:</label></h3>";
	echo "<h2>".$_SESSION['ClienteA']."</h2>";
	echo "<input type='hidden' name='clienteemisor_t' value='".$_SESSION['NClienteA']."'>";
}
if($_SESSION['ClienteB']==''){
	$Grupo="SELECT NdeCliente,nombrecliente,Smartphones FROM Clientes WHERE Smartphones=1 ORDER BY nombrecliente ASC";
	$estructura= mysql_query($Grupo);
	echo "<h3><label>Cliente Receptor:</label></h3><select name='clientereceptor_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[0]."'>".$row[1]."</option>";
	}
	echo "</select>";
}else{
	echo "<h3><label>Cliente Receptor:</label></h3>";
	echo "<h2>".$_SESSION['ClienteB']."</h2>";
	echo "<input type='hidden' name='clientereceptor_t' value='".$_SESSION['NClienteB']."'>";
}
	$Grupo="SELECT * FROM Stock WHERE Cantidad >0 AND Codigo>0 AND Smartphones=1";	
	$estructura= mysql_query($Grupo);
	echo "<h3><label>Servicio:</label></h3><select name='servicio_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	if($_SESSION['Servicio']==''){
		echo "<option value='".$row[0]."'>".$row[2]."</option>";
	}else{
	echo "<option value='".$_SESSION['idServicio']."'>".$_SESSION['Servicio']."</option>";
	echo "<option value='".$row[0]."'>".$row[2]."</option>";
	}	
		}	
	echo "</select>";
if($_SESSION['ClienteB']==''){
echo "<h3><input class='button-big' align='left' name='Valor' type='submit' value='Aceptar''>";
}											

if($_SESSION['ClienteB']!=''){
$BuscaRepoAbierta="SELECT * FROM Ventas WHERE Cliente='".$_SESSION['ClienteA']."' AND terminado='0' AND FechaPedido=curdate() AND Usuario='".$_SESSION['Usuario']."'";
$MuestraRepo=mysql_query($BuscaRepoAbierta);
while($row=mysql_fetch_array($MuestraRepo)){

// echo "<h3> * $row[idPedido] $row[Comentario] $row[Cantidad]</h3>";
// echo"<a class='img' href='AgregarRepo.php?Eliminar=si&id=$row[0]'><img src='../images/botones/eliminar.png' width='5' height='5' border='0' style='float:left;'></a>";
echo"<h3> * $row[idPedido] $row[Comentario] $row[Cantidad] <a href='AgregarRepoSmartphone.php?Eliminar=si&id=$row[idPedido]' style='text-decoration:none;'>Eliminar</a></h3>";

}

echo "<h3>Cantidad:</h3>";
echo "<input type='number' name='cantidad_t' value='' >";
echo "<h3>Factura/Remito/Orden:</h3>";
echo "<input type='text' name='codigoproveedor_t' value='' required>";
echo "<h3>Observaciones:</h3>";
echo "<textarea rows='2' cols='30' name='observaciones_t' value=''></textarea>";
echo "<input type='hidden' name='id' value='".$_GET['id']."'>";
echo "<h3><input class='button-big' align='left' name='Valor' type='submit' value='Aceptar'>";
?>	
<input class="button-big" type="button" value="Cancelar" onClick="location.href='Cpanel.php'"></h3>
<?php
echo "<h3><input class='button-big' align='left' name='CerrarRemito' type='submit' value='Cerrar Remito'></h3>";
echo "</form>";	
}
?>
</section>											
<!-- <div class="row">
<div class="6u 12u(mobile)"> -->

								</div>
							</div>
						</div>
					</div>
				</div>
	
<!-- </center> -->
		<!-- Scripts -->
			<script src="../../../assets/js/jquery.min.js"></script>
			<script src="../../../assets/js/skel.min.js"></script>
			<script src="../../../assets/js/skel-viewport.min.js"></script>
			<script src="../../../assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="../../../assets/js/main.js"></script>
	</body>
</html>
