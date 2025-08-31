<?php	
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script>
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";
   
echo "<form class='login' action='subir.php' method='POST' style='width:50%' enctype='multipart/form-data'>";
echo "<div><titulo>Cargar Factura de Venta AFIP:</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><input type='file' name='imagen' id='imagen' /></div>";
echo "<div><label>Seleccionar Carpeta Destino:</label><select name='carpeta'>";
echo "<option value='FacturasCompra'>Facturas Compras</option>";
echo "<option value='FacturasVenta'>Facturas Ventas</option>";
echo "</select></div>";
echo "<div><input type='submit' name='subir' value='Subir'/></div>";
echo "</form>";
?>
</center>
</body>
<?
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
?>