<?
session_start();
if(($_SESSION['idusuario']==168)||($_SESSION['idusuario']==197)){
header('Location:https://www.caddy.com.ar/Plataforma/pages-login-2.html');  
}
include("../ConexionBD.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Inicio Sesion</title>
		<meta charset='utf-8' />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
	  </head>
<style>
#containerMensaje {
  width: 100%;
  height: 100%;
  display: block;
  background: rgba(77, 26, 80, 1);
}

#logoWhite {
  width: 25%;
  height: 100%;
  display: block;
  position: absolute;
  top: 25%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: url(../../images/logo_white.svg) center no-repeat;
  background-size: 85%;
}
  
 #mensajeExito {
  width: 50%;
  height: auto;
  display: block;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

#mensajeExito h1 {
  font-family: 'Gotham-Bold';
  font-size: 1.75vw;
  color: rgba(255, 255, 255, 1);
}
 
  
  
  </style>
     <?php
echo "<body>"; 
include('Menu/menu.html');

    //---------------------------------------IMGRESO----------------------------------------------------------------------
?>  
    <div id="containerMensaje">
  	<div id="logoWhite"></div>
    <div id="mensajeExito">
      <h1>Bienvenido a tu panel de control de Caddy.
        </h1>
    </div>
  </div>
</body>
</html>
<?
ob_end_flush();
?>