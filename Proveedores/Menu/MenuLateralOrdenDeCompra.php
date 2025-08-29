<?php
session_start();
echo "<div id='cssmenu'>";
echo "<ul>";
$Pregunta=base64_encode('Cargar');
$Respuesta=base64_encode('Si');
$Pregunta1=base64_encode('Ver');
$Respuesta1=base64_encode('Cargada');
$Respuesta1_1=base64_encode('Aceptada');
$Respuesta1_2=base64_encode('Rechazada');
$Respuesta1_3=base64_encode('Autorizada');
$Respuesta1_4=base64_encode('Cerrada');

$Pregunta2=base64_encode('Ver');
$Respuesta2=base64_encode('Presupuestos');

echo "<li><a href='OrdenDeCompra.php?$Pregunta=$Respuesta'><span>Nueva Orden de Compra</span></a></li>";

echo "   <li class='active has-sub'><a href='#'><span>Ver Orden de Compra</span></a>";
echo "      <ul>";
echo "         <li><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1'><span>Ordenes Cargadas</span></a>";
echo "         <li><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_1'><span>Ordenes Aceptadas</span></a>";
echo "         <li><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_2'><span>Ordenes Rechazadas</span></a>";
echo "         <li><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_3'><span>Ordenes Aprobadas</span></a>";
echo "         <li><a href='OrdenDeCompra.php?$Pregunta1=$Respuesta1_4'><span>Ordenes Cerradas</span></a>";

// echo "            <ul>";
// // echo "              <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
echo "         </li>";
echo "         <li><a href='OrdenDeCompra.php?Ver=Todas'><span>Ver Todas</span></a>";
// // echo "            <ul>";
// // echo "               <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
echo "         </li>";
echo "      </ul>";
echo "   </li>";
echo "<li><a href='OrdenDeCompra.php?$Pregunta2=$Respuesta2'><span>Nuevo Presupuesto</span></a></li>";
// echo "   <li><a href='Compras.php?Pagar=Si'><span>Pagar Factura</span></a></li>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";  



// include_once "../ConexionBD.php";	
// //if ($_SESSION['Nivel']==4){ //Administradores con acceso a empleados
// echo "<form class='tbHeader3' action='' method='get' style='width:150px;float:left;padding:10px;background:#F1F1F1;height:70%;'>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Cargar Factura' style='float:left;width:150px;padding:10px;'></div>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Pago a Cuenta'  style='float:left;width:150px;padding:10px;'></div>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Pagar Factura'  style='float:left;width:150px;padding:10px;'></div>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Modificar Factura' style='float:left;width:150px;padding:10px;'></div>";
// echo "</form>";	

// if ($_GET['MenuPestana']=='Cargar Factura'){
// 	header('location:Compras.php?Cargar=Si');
// }elseif($_GET['MenuPestana']=='Pagar Factura'){
// 	header('location:Compras.php?Pagar=Si');
// }elseif($_GET['MenuPestana']=='Sanciones'){
// 	header('location:Sanciones.php');
// }elseif($_GET['MenuPestana']=='Prestamos'){
// 	header('location:Prestamos.php');
// }elseif($_GET['MenuPestana']=='Vacaciones'){
// 	header('location:Vacaciones.php');
// }elseif($_GET['MenuPestana']=='Pago a Cuenta'){
// 	header('location:Compras2.php?Pagar=Si');
// }
  
