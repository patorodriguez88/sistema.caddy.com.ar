<?php
session_start();
// include("../../ConexionBD.php");

$NombreEmpleado=$_SESSION['NombreEmpleado'];
if ($NombreEmpleado==''){
	$NombreEmpleado=$_GET['NE'];
	$_SESSION['NombreEmpleado']=$_GET['NE'];
}
$id=$_SESSION['idEmpleado'];

function sumarHoras($horas) {
    $total = 0;
    foreach($horas as $h) {
        $parts = explode(":", $h);
        $total += $parts[2] + $parts[1]*60 + $parts[0]*3600;        
    }   
    return gmdate("H:i:s", $total);
}


if (($_SESSION['Nivel']==1)or($_SESSION['Nivel']==2)or($_SESSION['Nivel']==3)or($_SESSION['Nivel']==5)){
echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li><a href='Empleados.php?id=Ver'><span>Datos</span></a></li>";

echo "<li class='active has-sub'><a href='Empleados.php?Horarios=ver'><span>Presentismo</span></a>";
  echo "<ul>";
     echo "<li><a href='https://www.caddy.com.ar/SistemaTriangular/Empleados/Horarios.php'><span>Agregar Horarios</span></a></li>"; 
     echo "<li><a href='Empleados.php?Horarios=ver'><span>Ver Horarios</span></a></li>"; 
echo "</ul>";
  
echo "<li class='active has-sub'><a href='Sanciones.php'><span>Sanciones</span></a>";
  echo "<ul>";
     echo "<li><a href='Sanciones.php?Sanciones=Agregar'><span>Agregar Sancion</span></a></li>"; 
     echo "<li><a href='Sanciones.php'><span>Ver Sanciones</span></a></li>"; 
  // echo "<li><a href='Usuarios.php?Cargar=Si'><span>Prestamos</span></a></li>";
// echo "<li><a href='Usuarios.php?Cargar=Si'><span>Vacaciones</span></a></li>";
echo "</ul>";
  echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";
} 
  //if ($_SESSION['Nivel']==4){ //Administradores con acceso a empleados
// echo "<form class='login' action='' method='get' style='width:150px;float:left;padding:15px;'>";
// echo "<div class='tbHeader3'>$NombreEmpleado</div>";
// 	echo "<div><input class='submit' name='MenuPestana' type='submit' value='Datos' style='float:left;width:150px;padding:10px;'>";
// 	echo "<input class='submit' name='MenuPestana' type='submit' value='Archivos' style='float:left;width:150px;padding:10px;'>";
// 	echo "<input class='submit' name='MenuPestana' type='submit' value='Presentismo'  style='float:left;width:150px;padding:10px;'>";
// 	echo "<input class='submit' name='MenuPestana' type='submit' value='Sanciones'  style='float:left;width:150px;padding:10px;'>";
// 	echo "<input class='submit' name='MenuPestana' type='submit' value='Prestamos'  style='float:left;width:150px;padding:10px;'>";		
// 	echo "<input class='submit' name='MenuPestana' type='submit' value='Vacaciones'  style='float:left;width:150px;padding:10px;'></div>";
// echo "</form>";	
// if ($_GET['MenuPestana']=='Datos'){
// header('location:Datos.php?Empleados=Ver');
// }elseif($_GET['MenuPestana']=='Archivos'){
// header('location:Archivos.php');
// }elseif($_GET['MenuPestana']=='Presentismo'){
// header('location:Presentismo.php');
// }elseif($_GET['MenuPestana']=='Sanciones'){
// header('location:Sanciones.php');
// }elseif($_GET['MenuPestana']=='Prestamos'){
// header('location:Prestamos.php');
// }elseif($_GET['MenuPestana']=='Vacaciones'){
// header('location:Vacaciones.php');
// }
// }  
// $sql=mysql_query("SELECT * FROM Empleados_Horarios WHERE Fecha>='$_POST[desde_t]' AND Fecha<='$_POST[hasta_t]'");
$desde=$_SESSION[fechadesde_t];
$hasta=$_SESSION[fechahasta_t];   

// $desde=$_POST[desde_t];
// $hasta=$_POST[hasta_t];
if(($desde<>'')||($hasta<>'')){
$sqlhoras=mysql_query("SELECT idEmpleado FROM Empleados_Horarios WHERE Fecha>='$desde' AND Fecha<='$hasta' GROUP BY idEmpleado");
echo "<div style='clear: both;'></div>";  
echo "<div style='float:left;margin:0;'>";

echo "<table class='login' style='margin-top:10px;'>";
echo "<caption style='font-size:18px'>Datos Empleados</caption>";
echo "<th>Empleado</th>";
echo "<th>Horas</th>";  
  while($datohoras=mysql_fetch_array($sqlhoras)){
  //ACA SELECCIONO EL NOMBRE DEL EMPLEADO
  $sqlEmpleado=mysql_query("SELECT NombreCompleto FROM Empleados WHERE id='$datohoras[idEmpleado]'");
  $NombreEmpleado=mysql_fetch_array($sqlEmpleado);
  echo "<tr style='font-size:12px'><td style='width:270px;'>$NombreEmpleado[NombreCompleto]</td>";
  //ACA SUMO TODAS LAS HORAS DEL EMPLEADO SELECCIONADO ENTRE FECHAS
  $sqlsumahoras=mysql_query("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(HorasTotales)))as suma FROM Empleados_Horarios 
  WHERE Fecha>='$desde' AND Fecha<='$hasta' AND idEmpleado='$datohoras[idEmpleado]'");

    $sqlsumahorasdato=mysql_fetch_array($sqlsumahoras);
    $horasbd = $sqlsumahorasdato[suma];
  echo "<td>$horasbd</td>";
//     $value_horario   = $sqlsumahorasdato['HorasTotales'];
//     $parts           = explode(':', $value_horario);
//     $resultado       = ($parts[0] + ($parts[1]/6) / 10 . PHP_EOL);
//     $sumaHoras       = $sumaHoras + $resultado;
//     echo gmdate("H:i:s",$sumaHoras);  
//     $parts = explode(":", $horasbd);
//     $total = $parts[2] + $parts[1]*60 + $parts[0]*3600;        
//     echo  gmdate("H:i:s", $horasbd);  
//     echo $sqlsumahorasdato[Total];
  echo "</tr>";

}
//   echo  gmdate("H:i:s", $total);  
// //     unset($total);

echo "</table>";
echo "</div>";
}  

?>	