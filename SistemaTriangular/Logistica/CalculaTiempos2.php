<?
session_start();
include("../ConexionBD.php");
if($_GET['viajar']=='si'){
              $variable =$_GET['valor[]'];
              $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
              mysql_query($sql);
}

$Datos=$_GET['valor'];
echo "<form class='login' action='VentaMasiva.php?Cargar=Aceptar' method='POST' style='float:center; width:95%;'>";
  
for($i=0;$i < count($Datos);$i++){
echo "<div style='margin-bottom:0px'><label style='font-size:14px'>$Datos[$i]</label>
<input name='cantidad_t[]' size='5' type='text' value='1' style='width:15%'/>";
echo "<select name='producto_t[]' style='width:250px;' size='1'>";
  
}
echo "<div><input name='Cargar' class='bottom' type='submit' value='Aceptar' ></div>";
echo "<div><input name='Cargar' class='bottom' type='submit' value='Cancelar' ></div>";
echo "</form>";
?>
  </body>
</html>