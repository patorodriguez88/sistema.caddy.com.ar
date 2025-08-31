<?php
ob_start();
session_start();
include_once "../ConexionSmartphone.php";
$user= $_SESSION['NCliente'];
// $user=12;
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
		<link rel="stylesheet" href="../../../assets/css/main.css" />
    <script type="text/javascript" src="../js/ubicacion.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" ></script>
<!--     <script src="miscript.js"></script> -->
    <script src="miscript2.js"></script>

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
    </head>
    <script>
    function verboton(){
    document.getElementById('lista-container').style.display='block';
    document.getElementById('listado').style.display='none';  
    }
  </script>

    <body>
<?
$sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
AND TransClientes.Retirado='1'
AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' 
AND TransClientes.Eliminado='0' AND HojaDeRuta.Eliminado='0' ORDER BY HojaDeRuta.Posicion ASC");   
echo "<form id='listado' class='' action='' method='post'>";
$sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$_SESSION[RecorridoAsignado]'");
$datosql=mysql_fetch_array($sqlRecorridos);                      
if(mysql_num_rows($sqlC)==0){
echo "<h2>No hay Recorridos disponibles</h2>";
goto a;  
}                      

echo "<h3>Recorrido Asignado: $datosql[Nombre] (".$_SESSION['RecorridoAsignado'].")</h3>";
echo "<h3>Paquetes pendientes de entrega (".mysql_numrows($sql).")</h3>";  

        if(mysql_numrows($sql)==0){
        $sqlproximo = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0' AND id<>'$Dato[id]'");
        $datoproximo=mysql_fetch_array($sqlproximo);
          if(mysql_numrows($sqlproximo)<>0){
          echo "<h3>Proximo Recorrido Asignado: Recorrido $datoproximo[Recorrido]</h3>";  
          }
        }              
                      
while($row=mysql_fetch_array($sql)){
$_SESSION[Localizacion]=$row[DomicilioDestino];
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
$sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
$idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' ORDER BY id DESC");
$Seguimiento=mysql_fetch_array($sqlSeguimiento); 

  
if($Seguimiento[Estado]=='No se pudo entregar'){
$ColorBoton='red';
}else{
$ColorBoton='#145A32';  
}  
if($idProveedor[idProveedor]==''){
$Titulo="";  
}else{
$Titulo = "(".$idProveedor[idProveedor].")";	  
}  

  $Titulo ="id.:$row[id] ";
$Titulo .=$row[ClienteDestino];
$Titulo .=" Cant.: ";	
$Titulo .="$row[Cantidad]";	
$Titulo .="";
$Titulo .="";	
$Titulo .="";
echo "<p><button class='button-big' data-user='$row[id]' style='font-size:18px;width:95%;height:50px;margin-bottom:5px;background:$ColorBoton;'>$Titulo</p>";
// echo "<div id='lista-container'></div>";

}
echo "</form>";
      a:
      ?>

      <div id="lista-container"></div>
      
      <div id="response-container"></div>
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
                