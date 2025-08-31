<?php
session_start();
$sql=$mysqli->query("SELECT * FROM Vehiculos WHERE Dominio='".$_GET['Dominio']."'");
$datos=$sql->fetch_array(MYSQLI_ASSOC);
$Marca=$datos[Marca];
$Dato=$_GET['Dominio'];
if($_GET['Dominio']==''){
 $Marca='';
$Dato='Todos los Vehiculos'; 
}

echo "<div id='cssmenu'>";
echo "<ul>";
echo "   <li class='active has-sub'><a href='#'><span>Ver Ordenes</span></a>";
echo "      <ul>";
echo "         <li><a href='Logistica.php?Filtro=Alta'><span>Ordenes en Alta</span></a>";
echo "         </li>";
echo "         <li><a href='Logistica.php?Filtro=Cargada'><span>Ordenes Cargadas</span></a>";
echo "         </li>";
echo "         </li>";
echo "         <li><a href='Logistica.php?Filtro=Cerrada'><span>Ordenes Cerradas</span></a>";
echo "         </li>";
echo "         <li><a href='Logistica.php?Filtro=Pendiente'><span>Ordenes Pendientes</span></a>";
echo "         </li>";
echo "      </ul>";
echo "   <li class='active has-sub'><a href='#'><span>Ver Ordenes x Filtro</span></a>";
echo "      <ul>";
echo "         <li><a href='Logistica.php?id=Ver'><span>Ver x Vehiculo</span></a>";
echo "         </li>";
echo "         <li><a href='Logistica.php?xRec=Ver'><span>Ver x Recorrido</span></a>";
echo "         </li>";
echo "         </li>";
echo "         <li><a href='Logistica.php?xFecha=Ver'><span>Ver x Fecha</span></a>";
echo "         </li>";
echo "      </ul>";
echo "   </li>";
echo "   <li class='active has-sub'><a href='LogisticaMapas.php'><span>Ver Mapa</span></a>";
echo "   </li>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";  

?>
