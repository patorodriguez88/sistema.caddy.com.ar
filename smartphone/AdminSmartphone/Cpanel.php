<?php
ob_start();
session_start();
include_once "../ConexionSmartphone.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
date_default_timezone_set('Chile/Continental');
$_SESSION['ClienteA']='';
$_SESSION['ClienteB']='';
$_SESSION['NClienteA']='';
$_SESSION['NClienteB']='';

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Triangular S.A.</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../assets/css/main.css" />
		
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

<!-- <div id="banner"> -->
									<section>
											<header>
												<h2>Sistema de Gestión Logistica</h2>
<!-- 												<h3>Sector de Diarios y Revistas</h3> -->
											</header>
											<p>
											</p>
											<header>
<!-- 												<h3>Sector Automotor y Autopartes</h3> -->
											</header>
											<p>
											</p>
											<header>
<!-- 												<h3>Sector Farmaceútico y Droguerias</h3> -->
											</header>
											<p>
											</p>
									
									</section>
		
<div class="row">
<div class="6u 12u(mobile)">

		</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	
</center>
		<!-- Scripts -->
			<script src="../assets/js/jquery.min.js"></script>
			<script src="../assets/js/skel.min.js"></script>
			<script src="../assets/js/skel-viewport.min.js"></script>
			<script src="../assets/js/util.js"></script>
			
			<script src="../assets/js/main.js"></script>
	</body>
</html>
