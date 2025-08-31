<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.sistemacaddy.com.ar");
}
// print $_SESSION['NCliente'];
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- <html xmlns="http://www.w3.org/1999/xhtml"><head> -->
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
	<script>
    function subir(){
      var c = document.getElementsByName('cargar');
      var x = document.getElementsByName('recorrido_t[]');
      var i;
      for (i = 0; i < x.length; i++) {
        if (x[i].value!=0){
        c[i].style.display='block'; 
        }else{
        c[i].style.display='none';  
        }
       }
    }
    </script>

<?php 
include("../Alertas/alertas.html");
include("../Menu/MenuGestion.php"); 
include("../Menu/MenuDerecha.php"); 
echo "<div id='cuerpo'>";


echo "<center>";
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
 
if($_GET['Eliminar']=='Si'){
$CS=$_GET['CS'];  
header('location:VentanaEliminar.php?CS='.$CS);
}
//MUESTRA LA PREVENTA
$ColorPreventa='#FF4000';
$font='white';
$color2='white';
$font2='black';

$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['Usuario'];
$ColorPreventa2='#FA8258';
$font='white';

$sql=mysql_query("SELECT COUNT(id)as id FROM Seguimiento WHERE Entregado=1");
$datosql=mysql_fetch_array($sql);
print $datosql[id];

a:
ob_end_flush();
?> 
</div>
</div>
</body>
</center>
</html>