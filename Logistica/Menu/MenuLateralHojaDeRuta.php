<?php
session_start();
include_once "../Conexion/Conexioni.php";
// include_once "../ConexionBD.php";
?>
</head>
<?
$Recorrido=$_GET['recorrido_t'];	
$Ordenar=$mysqli->query("SELECT Recorrido FROM HojaDeRuta WHERE Estado='Abierto' AND Eliminado='0' GROUP BY Recorrido");
echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li class='active has-sub'><a href=''><span>Recorridos Activos</span></a>";
echo "      <ul>";
while ($file = $Ordenar->fetch_array(MYSQLI_ASSOC)){
$sqlRecorrido=$mysqli->query("SELECT Nombre FROM Recorridos WHERE Numero='$file[Recorrido]'");
$DatoRecorrido=$sqlRecorrido->fetch_array(MYSQLI_ASSOC);
$sqllogistica=$mysqli->query("SELECT NombreChofer FROM Logistica WHERE Recorrido='$file[Recorrido]' AND Estado IN('Alta','Cargada') AND Eliminado=0");
$datosqllogistica=$sqllogistica->fetch_array(MYSQLI_ASSOC);
if($datosqllogistica[NombreChofer]==''){
$Chofer='Pendiente';
}else{
$Chofer=$datosqllogistica[NombreChofer];  
}
echo "<li><a href='HojaDeRuta.php?dato=$file[Recorrido]'><span>Recorrido [$file[Recorrido]] </br>
            $DatoRecorrido[Nombre]</br>
            Chofer: $Chofer</br>
            </span></a>";
echo "</li>";
  }
echo "</ul>";
echo "<li><a href='HojaDeRuta.php?id=Agregar'><span>Agregar Ruta</span></a></li>";
echo "<li><a href='HojaDeRuta.php?id=Agregar&recorrido_t=".$_SESSION['Recorrido']."'><span>Agregar Item</span></a></li>";

echo "<li class='active has-sub'><a href='#'><span>Asignaciones</span></a>";
echo "<ul>";
echo "<li><a href='HojaDeRuta.php?id=AsignacionAgregar'><span>Agregar Fechas</span></a></li>";
echo "<li><a href='HojaDeRuta.php?id=BuscarAsignacion'><span>Buscar x Fecha</span></a></li>";
echo "</ul>";
echo "         </li>";
echo "<li><a target='t_blank' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/mapa.html'><span>Mapa Completo</span></a></li>";
echo "<li><a href='HojaDeRuta.php?id=BuscarAnterior'><span>Buscar Anterior</span></a></li>";
if (($Recorrido<>'')){
echo "<li><a target='t_blank' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/HojaDeRutapdf.php?HR=".$_SESSION['Recorrido']."'><span>Imprimir</span></a></li>";
echo "<li><a href='HojaDeRuta.php?id=EnviarSms'><span>Enviar SMS</span></a></li>";
echo "<li><a href='HojaDeRuta.php?Pestana=Ruta'><span>Ver Ruta</span></a></li>";
echo "<li><a target='t_blank' href='mapa.php'><span>Ver Mapa</span></a></li>";
echo "<li><a href='ordenahojaderuta.php'><span>Ordenar Recorrido</span></a></li>";
echo "<li><a href='HojaDeRuta.php?Eliminarhdr=Si&Recorrido=$Recorrido'><span>Eliminar Hoja de Ruta</span></a></li>";
}

echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";  

$sqlBuscoChofer=$mysqli->query("SELECT NombreChofer,KilometrosRecorridos,Patente FROM Logistica WHERE Estado='Cargada' AND Recorrido='$Recorrido' AND Eliminado=0");
$NombreChofer=$sqlBuscoChofer->fetch_array(MYSQLI_ASSOC);
$sqlRecorridos=$mysqli->query("SELECT Nombre FROM Recorridos WHERE Numero='$Recorrido'");
$DatosRecorridos=$sqlRecorridos->fetch_array(MYSQLI_ASSOC);
$sqlfilas=$mysqli->query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado='0' AND Devuelto='0' AND Seguimiento<>''");	
$numfilas = $sqlfilas->num_rows;

if (($Recorrido<>'')){
echo "<div style='clear: both'></div>";  
echo "<div style='float:left;margin:0;'>";
echo "<table class='login' style='margin-top:10px'>";
echo "<caption style='font-size:15px'>Datos Recorrido</caption>";
echo "<tr style='font-size:12px'><td>Recorrido:</td><td>$DatosRecorridos[Nombre] ($_GET[recorrido_t])</td></tr>";
echo "<tr style='font-size:12px'><td>Responsable:</td><td>$NombreChofer[NombreChofer]</td></tr>"; 
echo "<tr style='font-size:12px'><td>Dominio:</td><td>$NombreChofer[Patente]</td></tr>";
echo "<tr style='font-size:12px'><td>Total Envios</td><td>$numfilas</td></tr>";
echo "</table>";
echo "</div>";  
}


$dato=$_GET['dato'];
if($_GET['Pestana']=='Agregar Item'){
header('location:HojaDeRuta.php?id=Agregar&recorrido_t='.$_SESSION['Recorrido']);  
}elseif($_GET['Pestana']=='EnviarSms'){
header('location:HojaDeRuta.php?id=EnviarSms');  
}elseif($_GET['Pestana']=='Agregar Ruta'){
header('location:HojaDeRuta.php?id=Agregar');  
}elseif($_GET['Pestana']=='Imprimir'){
?><script>window.open('http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/HojaDeRutapdf.php?HR=<? echo $_SESSION['Recorrido'];?>');</script><?
}elseif($_GET['Pestana']=='Buscar Anterior'){
header('location:HojaDeRuta.php?id=BuscarAnterior');  
}elseif($_GET['Pestana']=='Mapa'){
  ?>
<script>
window.open("mapa.html","_blank");
location.href= "HojaDeRuta.php";
</script>';  
<?

// header('location:mapa.html');  
}elseif($_GET['Pestana']=='Ruta'){

$Recorrido=$_SESSION['Recorrido'];	
$Ordenar=$mysqli->query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' ");
  
while($row=$Ordenar->fetch_array(MYSQLI_ASSOC)){
$dato=array($row[Localizacion]); 
}
header('location:HojaDeRuta.php?VerMapa=Si');  
}else{
 $sql=$mysqli->query("SELECT * FROM Recorridos WHERE Numero='$dato'");
   while($Rec= $sql->fetch_array(MYSQLI_ASSOC)){
   $Numero=$Rec[Numero];
   $_SESSION['Recorrido']=$Rec[Numero];  
   header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Rec[Numero]);  
  } 
}

?>