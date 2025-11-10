<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.caddy.com.ar/SistemaTriangular/index.php");
}
$Empresa=$_SESSION['ClienteActivo'];
$Cuit=$_SESSION['CuitActivo'];	
$id=$_SESSION['idClienteActivo'];	

$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$color2='white'; 
$font2='black';


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>.::Triangular S.A.::.</title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../ajax.js"></script>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" />        
</head>
  <script>
  function veotarifa(a){
  var dim= a.split(','); 
  document.getElementById('tarifa').value=dim[1];
  document.getElementById('km').value=dim[2];  
  }
  </script>
  <?php
echo "<body style='background:".$_SESSION['ColorFondo']."'>";
echo "<div id='contenedor'>";
include("../Alertas/alertas.html");
include("../Menu/MenuGestion.php"); 
echo "<div id='cuerpo'>";
echo "<div id='lateral'>"; 
include("../Ventas/Menu/MenuLateralVentas.php");   
echo "</div>"; //lateral
echo  "<div id='principal' style='height:100%'>";
if($_GET[carga]=='ok'){
  ?>
  <script>
  alertify.success('Cargado ok!');
  </script>
  <?
  }elseif($_GET[carga]=='null'){
  ?>
  <script>
  alertify.error('No se cargo la tarifa...');
  </script>
  <?
  }

  if($_POST[Actualizar]=='Aceptar'){
  $Servicio=explode(',',$_POST[servicio],3);
  $id=$_POST[id];
  $sql="UPDATE `ClientesyServicios` SET Servicio='$Servicio[0]',MaxKm='$_POST[maxkm_t]',PrecioPlano='$_POST[plano_t]' WHERE id='$id'";
  if(mysql_query($sql)){
  header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?carga=ok');    
  }else{
  header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?carga=null');    
  }
}

if($_GET[Agregar]=='Si'){
  echo "<form action='' class='Caddy' style='width:60%'>";
  echo "<div><titulo>Agregar Tarifa</titulo></div>"; 
  echo "<hr></hr>";  
  echo "<div><label>Servicio:</label><select name='servicio' style='width:500px' Onchange='veotarifa(this.value)'>";
  $sqlservicio=mysql_query("SELECT * FROM Productos WHERE Grupo='Web'");
  echo "<option value=''>Seleccionar Tarifa</option>";  
  while($row=mysql_fetch_array($sqlservicio)){
  echo "<option value='$row[id],$row[PrecioVenta],$row[Kilometros]'>$row[Titulo] $row[Descripcion] ($ $row[PrecioVenta])</option>";  
  }
  echo "</select>";
  echo "</div>";
  echo "<div><label>Máximo de Km.:</label><input type='text' id='km' value='' name='km'/></div>";
  echo "<div><label>Precio Plano:</label><input id='tarifa' type='text' value='' name='tarifa'/></div>";
  echo "<div><input class='submit' type='submit' value='Agregar' name='nuevatarifa'/></div>";
  echo "</form>";  
  goto a;
}

if($_GET[nuevatarifa]=='Agregar'){
$Servicio=explode(',',$_GET[servicio],3);
  
  $sql="INSERT INTO `ClientesyServicios`(`NdeCliente`, `Servicio`, `MaxKm`,`PrecioPlano`) VALUES 
  ($id,$Servicio[0],'{$_GET[km]}','{$_GET[tarifa]}')";
  if(mysql_query($sql)){
  header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?carga=ok');    
  }else{
  header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?carga=null');    
  }
  goto a;
  
} 
  
  if($_GET[Eliminar]=='Si'){
    $id=$_GET[id];
    mysql_query("DELETE FROM `ClientesyServicios` WHERE id='$id'");  
    header('location:https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php');
    goto a;
  }
  if($_GET[Editar]=='Si'){
  $sql=mysql_query("SELECT * FROM ClientesyServicios WHERE id='$_GET[id]'");
  $datosql=mysql_fetch_array($sql);  
  $sqlcliente=mysql_query("SELECT nombrecliente FROM Clientes WHERE id='$datosql[NdeCliente]'");
  $datoCliente=mysql_fetch_array($sqlcliente);  

  echo "<form action='' class='Caddy' style='width:60%' method='POST'>";
  echo "<div><titulo>Editar Tarifa</titulo></div>"; 
  echo "<hr></hr>";
  echo "<input type='hidden' name='id' value='$_GET[id]'>";
  echo "<div><label>Nombre Cliente:</label><input type='text' value='$datoCliente[nombrecliente]' name='' readonly/></div>";
  echo "<div><label>Servicio:</label><select name='servicio' style='width:500px' Onchange='veotarifa(this.value)'>";
  echo "<option value='$datosql[Servicio]'>Servicio Actual: $datosql[Servicio]</option>";  
  $sqlservicio=mysql_query("SELECT * FROM Productos WHERE Grupo='Web'");
  while($row=mysql_fetch_array($sqlservicio)){
  echo "<option value='$row[id],$row[PrecioVenta],$row[Kilometros]'>$row[Titulo] $row[Descripcion] ($ $row[PrecioVenta])</option>";  
  }
  echo "</select>";
  echo "</div>";

  echo "<div><label>Máximo de Km.:</label><input type='text' value='$datosql[MaxKm]' name='maxkm_t'/></div>";
  echo "<div><label>Precio Plano:</label><input type='text' value='$datosql[PrecioPlano]' name='plano_t'/></div>";
  echo "<div><input class='submit' type='submit' value='Aceptar' name='Actualizar'/></div>";
  echo "</form>";  
  goto a;
}
echo "<table class='login'>";
echo "<caption>Tarifas Asignadas</caption>";
echo "<th>Cliente</th>";
echo "<th>Tarifa</th>";
echo "<th>Maximo Km.</th>";
echo "<th>Precio Plano</th>";
echo "<th>Editar</th>";
echo "<th>Eliminar</th>";

$sql=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$id'");
while($datos=mysql_fetch_array($sql)){
$sqlservicios=mysql_query("SELECT * FROM Productos WHERE id='$datos[Servicio]'");
$datoservicio=mysql_fetch_array($sqlservicios);  
echo "<tr>";
  echo "<td>$Empresa</td>";
  echo "<td>$datoservicio[Titulo]</td>";
  echo "<td>$datos[MaxKm]</td>";
  echo "<td>$datos[PrecioPlano]</td>";
	echo "<td align='center'><a href='https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?Editar=Si&id=$datos[id]'><input type='image' src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:center;'></td>";
	echo "<td align='center'><a href='https://www.caddy.com.ar/SistemaTriangular/Clientes/ClientesyServicios.php?Eliminar=Si&id=$datos[id]'><input type='image' src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></td>";

}  
echo "</tr></table>";
	
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
  
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>