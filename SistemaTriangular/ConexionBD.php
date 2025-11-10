<?php
// ob_start();
if ($_SESSION['Usuario']=='prodriguez'){
$tiempo=300000;  
}else{
$tiempo=5400;
}

if (!isset($_SESSION['tiempo'])) {
    $_SESSION['tiempo']=time();
}
else if (time() - $_SESSION['tiempo'] > $tiempo) {
    session_destroy();
    /* Aquí redireccionas a la url especifica */
    header("Location:https://www.caddy.com.ar/iniciosesion.php");
    die();  
}
$_SESSION['tiempo']=time(); //Si hay actividad seteamos el valor al tiempo actual

// if($_SESSION['FechaPassword']>date()){
// header("location:http://www.caddy.com.ar/SistemaTriangular/Inicio/Usuarios.php");  
// }
if($_SESSION['idusuario']==""){
header("location:https://www.caddy.com.ar/iniciosesion.php");
}else{
 $conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
//  if (($_SESSION['Nivel']=='5')){
 
 if (($_SESSION['Usuario']=='prueba')){
   mysql_select_db("dinter6_triangularcopia",$conexion);
 }else{
  mysql_select_db("dinter6_triangular",$conexion);  
 } 
 mysql_set_charset("utf8"); 
?>
<style type="text/css">
body {  
background: none repeat scroll 0 0 #F1F1F1; 
}
</style>
<?php
}
// ob_end_flush();
?>