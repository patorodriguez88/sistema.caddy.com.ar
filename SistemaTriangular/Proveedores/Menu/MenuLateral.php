<?php
session_start();
echo "<div id='cssmenu'>";
echo "<ul>";
// echo "<li><a href='Compras.php?Cargar=Si'><span>Cargar Factura</span></a></li>";
echo "   <li class='active has-sub'><a href='#'><span>Cargar Factura</span></a>";
echo "      <ul>";
echo "         <li><a href='Compras.php?Cargar=Si&NoOperativo=No'><span>Operativo</span></a>";
// echo "            <ul>";
// // echo "              <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
echo "         </li>";
echo "         <li><a href='Compras.php?Cargar=Si&NoOperativo=Si'><span>No Operativo</span></a>";
// // echo "            <ul>";
// // echo "               <li><a href='#'><span>Sub Product</span></a></li>";
// // echo "               <li class='last'><a href='#'><span>Sub Product</span></a></li>";
// // echo "            </ul>";
echo "         </li>";
echo "      </ul>";
echo "   </li>";
echo "<li><a href='Compras.php?Pagoacuenta=Si'><span>Pago a Cuenta</span></a></li>";
echo "   <li><a href='Compras.php?Pagar=Si'><span>Pagar Factura</span></a></li>";
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
  
