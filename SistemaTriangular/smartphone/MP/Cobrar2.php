<?php
ob_start();
session_start();
// print $_SESSION['Usuario'];
// include_once "../../ConexionBD.php";
include_once "../ConexionSmartphone.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
date_default_timezone_set('America/Argentina/Buenos_Aires');

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Caddy. Yo lo llevo!</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

    <link rel="stylesheet" href="../../../assets/css/main.css" />
    <script type="text/javascript" src="../js/ubicacion.js"></script>



  </head>
	<body class="subpage" onLoad="localize()">
		<div id="page-wrapper">

			<!-- Header -->
				<div id="header-wrapper">
					<header id="header" class="container">
						<div class="row">
							<div class="12u">

								<!-- Logo -->
									<h1><a href="#" id="logo">Caddy. Yo lo llevo! </a></h1>
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
<style>
table.width200,
  table.rwd_auto {border:1px solid #ccc;width:100%;margin:0 0 50px 0}
.width200 th,.rwd_auto th {background:#ccc;padding:5px;text-align:center;}
.width200 td,.rwd_auto td {border-bottom:1px solid #ccc;padding:5px;text-align:center}
.width200 tr:last-child td, .rwd_auto tr:last-child td{border:0}

.rwd {width:100%;overflow:auto;}
.rwd table.rwd_auto {width:auto;min-width:100%}
.rwd_auto th,.rwd_auto td {white-space: nowrap;}

                      </style>                      
                      
<? 
$_SESSION['cdmp']=$_GET[cd]; 
$sql=mysql_query("SELECT Debe FROM TransClientes WHERE CodigoSeguimiento='$_SESSION[cdmp]' AND Eliminado=0");
$datosql=mysql_fetch_array($sql);
                      

?>                     

<form action="/SistemaTriangular/smartphone/MP/procesar_pago.php" method="POST">
<h2>Codigo de Venta: <? echo $_GET[cd];?></h2>
   <script
    src="https://www.mercadopago.com.ar/integrations/v1/web-tokenize-checkout.js"
    data-button-label="Cobrar con Mercado Pago"
    data-public-key="TEST-b4e08010-5aff-4e12-828d-4c2ec6c1a153"
    data-transaction-amount="<? echo $datosql[Debe];?>"
    data-summary-product-label="Servicio de Envio">
  </script>
</form>

</section>											
<!-- <div class="row">
<div class="6u 12u(mobile)"> -->

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
      
<!--       Firma -->
     <script src="firma/firma.js"></script>
     <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.min.js"></script>
   
	</body>
</html>
