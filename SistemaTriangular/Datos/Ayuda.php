<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$Usuario=$_SESSION[Usuario];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Caddy ::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
  <style>
    #h1{
    display: block;
    font-size: 2em;
    margin-block-start: 0.67em;
    margin-block-end: 0.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;  
    }
    #h1{
    display: block;
    font-size: 2em;
    margin-block-start: 0.67em;
    margin-block-end: 0.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;  
    }
 
  </style>
 <?php
include("../Alertas/alertas.html");
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
  include("../Menu/MenuGestion.php"); 
  echo "</div>";//cabecera 
     echo "<div id='cuerpo' style='width:60%'>"; 
    echo "<div id='lateral'>"; 
    echo "</div>"; //lateral
   echo "<div id='principal' style='width:60%'>";
  ?>
  <h1 id='h1'>
    Ayuda 
  </h1>
  <h2 id='h2'><a href='#Procedimientos'>A. Procedimientos</a></h2><br></br>
   <h2><a href='#Servicios'>B. Servicios</a></h2><br></br>
   <h2><a href='#CLientes'>C. Clientes</a></h2><br></br>
   <h2><a href='#Proveedores'>D. Proveedores</a></h2>
     <h3><a href='#OrdenDeCompra'>D.1. Orden de compra</a></h3> 
   <br></br>   
   <h2><a href='#Ventas'>E. Ventas</a></h2><br></br>
   <h2><a href='#Admin'>F. Admin</a></h2><br></br>
   <h2><a href='#Empleados'>G. Empleados</a></h2><br></br>
   <h2><a href='#Logistica'>H. Logistica</a></h2><br></br>
   <h2><a href='#Datos'>I. Datos</a></h2><br></br>

<p id='#OrdenDeCompra'>
  <h4>Nueva Orden de Compra</h4>
  <br></br>  
  Para generar una nueva OC es necesario completar los siguientes campos, Titulo, Tipo de Orden, Motivo, Precio Estimado, Fecha de la Compra y Observaciones <br>
  Titulo: Hace referencia al titulo de la OC.<br>
    Tipo de Orden: El tipo de Orden corresponde al tipo de ceunta contable en la cual se asignara contablemente la compra en el proceso de carga de los comprobantes.<br>
    Para agregar opciones en Tipo de Orden puede hacerlo desde la seccion de Admin/Plan de Cuentas, seleccionar la cuenta que se quiere agregar, seleccionar Modificar, y marcar la casilla de Utilizar para Orden de Compra.
  </p>
      
  </div>
  </html> 
