<?php
if($_SESSION['idusuario']==""){
header("location:https://www.caddy.com.ar/SistemaTriangular/smartphone/iniciosesion.php");
}else{
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
if($_SESSION['Usuario']=='pruebafletero'){
  mysql_select_db("dinter6_triangularcopia",$conexion);
}else{
  mysql_select_db("dinter6_triangular",$conexion);
}
 mysql_set_charset("utf8"); 
}  

?>