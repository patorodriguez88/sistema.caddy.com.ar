<?php
// if($_SESSION[IdVehiculo]<>''){
// echo "<form style='margin-left:5px;width:200px;height:100px;background:white;border-top:2px solid red;float:left' onclick='ver()' type='button' >";
// echo "<div style='margin-top:20px'><label>Vehiculo de Flota</label></div>";
// echo "<div style='margin-top:20px;color:#A6ACAF'><label>$_SESSION[IdVehiculo]</label></div>";
// echo "</form>";
// }
echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li><a href='Vehiculos.php?id=Agregar'><span>Agregar Vehiculo</span></a></li>";
echo "<li class='active has-sub'><a href='#'><span>Servicio Tecnico</span></a>";
  echo "<ul>";
    echo "<li><a href='Vehiculos.php?id=AgregarService'><span>Agregar Servicio</span></a></li>";
    echo "<li><a href='Vehiculos.php?id=VerService'><span>Ver Servicios</span></a></li>";
echo "</ul>";
echo "</li>";
echo "<li class='active has-sub'><a href='#'><span>Multas e Infracciones</span></a>";
  echo "<ul>";
    echo "<li><a href='Vehiculos.php?id=VerMultas'><span>Ver Multas e Infracciones</span></a></li>";
echo "</ul>";
echo "</li>";

echo "<li><a href='Informes/Vehiculospdf.php' target='_tblank'><span>Imprimir</span></a></li>";
echo "<li><a href='Vehiculos.php?id=Ver'><span>Ver Flota</span></a></li>";
// echo "   <li class='active has-sub'><a href=''><span>Ver Ordenes</span></a>";
// echo "      <ul>";
// echo "         <li class='has-sub'><a href='Logistica.php?id=Ver&Filtro=Alta&Dominio='.$Dominio.'><span>Ver Altas</span></a>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Logistica.php?id=Ver&Filtro=Cargada&Dominio='.$Dominio.'><span>Ver Cargadas</span></a>";
// echo "         </li>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Logistica.php?id=Ver&Filtro=Cerrada&Dominio='.$Dominio.'><span>Ver Cerradas</span></a>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Logistica.php?id=Ver'><span>Ver Todas</span></a>";
// echo "         </li>";
// echo "         <li class='has-sub'><a href='Logistica.php?xRec=Ver'><span>Ver x Recorrido</span></a>";
// echo "         </li>";

// echo "      </ul>";
// echo "   </li>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>"; 

// echo "<form class='login' action='' method='get' style='height:70%;width:150px;float:left;padding:15px;background:#5D6D7E'>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Agregar Service' style='float:left;width:150px;padding:10px;'></div>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Agregar Vehiculo' style='float:left;width:150px;padding:10px;'></div>";
// echo "<div><input class='submit' name='MenuPestana' type='submit' value='Imprimir' style='float:left;width:150px;padding:10px;'></div>";
// echo "</form>";	

// if ($_GET['MenuPestana']=='Imprimir'){
// }elseif($_GET['MenuPestana']=='Agregar Vehiculo'){
// header('location:Vehiculos.php?id=Agregar');
// }elseif($_GET['MenuPestana']=='Agregar Service'){
// header('location:Vehiculos.php?id=AgregarService');
// }

?>
