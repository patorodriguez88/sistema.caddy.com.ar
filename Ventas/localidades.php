<?
session_start();
include("../ConexionBD.php");
$LocalidadOrigen=$_POST[localidadorigen];  

if($LocalidadOrigen=='Córdoba'){
$LocalidadA='Cordoba Capital';  
}else{
$LocalidadA=$LocalidadOrigen;    
}

$WebA='';
$sql=mysql_query("SELECT Localidad,Web FROM Localidades WHERE Localidad = '$LocalidadA'");
$row=mysql_fetch_array($sql);
$WebA=$row[Web];
$resultado=$WebA;
$resultado=1;
echo $resultado;
?>