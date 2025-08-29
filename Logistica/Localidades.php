<?php
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
<script>
function recorrido(){
var rec =document.getElementById('web').checked;
//   alert(rec);
  if(rec===true){
  document.getElementById('recorrido').style.display='block';  
  }else{
  document.getElementById('recorrido').style.display='none';    
  }
}
  </script>  
<?php
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>";
include("Menu/MLLocalidades.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";
$color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';
$color2='#CCD1D1';
if($_GET['Accion']==Buscar){
$LocalidadSeleccionada=$_GET['Localidad'];
$Grupo="SELECT * FROM Localidades WHERE Localidad='$LocalidadSeleccionada'";
$estructura= mysql_query($Grupo);

  //MODIFICAR LOCALIDAD
  
echo "<form action='' class='Caddy' style='width:600px' >";
while($row = mysql_fetch_array($estructura)){
echo "<div><label style='float:center;color:red;font-size:22px'>Modificar Localidad</label></div>";
echo "<div><label>Id</label><input type='text' value='$row[id]' name='idlocalidad_t' style='float:center;width:300px;'></div>";
echo "<div><label>Localidad</label><input type='text' value='$row[Localidad]' name='localidad_t' style='float:center;width:300px;'></div>";
echo "<div><label>Provincia</label><input type='text' value='$row[Provincia]' name='provincia_t' style='float:center;width:300px;'></div>";
if($row[Web]==1){
$dato='display';  
$check='checked';
}else{
$dato='none';  
$check='';
}
echo "<div><label>Llegamos con Recorrido de Caddy:</label><input id='web' type='checkbox' style='width:250px' name='web_t' onclick='recorrido()' $check></div>";	
  $Grupo="SELECT * FROM Recorridos WHERE Numero <>'$row[Recorrido]' ORDER BY Numero ASC";
	$estructura= mysql_query($Grupo);
	echo "<div id='recorrido' style='display:$dato'><label>Recorrido:</label><select name='recorrido_t' style='float:center;width:310px;' size='1'>";
		
	  if($row[Recorrido]<>''){
		echo "<option value='$row[Recorrido]'>".$row[Recorrido]."</option>";
		}
  	echo "<option value=''>--Seleccione Recorrido--</option>";
		while ($Dato = mysql_fetch_array($estructura)){
		echo "<option value='".$Dato[Numero]."'>".$Dato[Numero]."/".$Dato[Nombre]."</option>";
	}
echo "</select></div>";
echo "<div><input type='submit' value='Modificar' name='Accion' style='width:100px' ></div>";
echo "<div><input type='submit' value='Volver' name='Accion'  style='width:100px'></div>";

	echo "</form>";	
}	
goto a;
}
if($_GET['Agregar']=='Localidad'){
echo "<form action='' class='Caddy' style='width:600px' method='GET'>";
echo "<div><titulo>Agregar Localidad</titulo></div>";
echo "<div><hr></hr><div>";
echo "<div><label>Localidad</label><input type='text' value='' name='localidad_t' style='width:250px' required></div>";
echo "<div><label>Provincia</label><input type='text' value='Cordoba'  style='width:250px' name='provincia_t' required></div>";
echo "<div><label>Llegamos con Recorrido de Caddy:</label><input id='web' type='checkbox' style='width:250px' name='web_t' onclick='recorrido()'></div>";
	$Grupo="SELECT * FROM Recorridos ORDER BY Numero ASC";
	$estructura= mysql_query($Grupo);
	echo "<div id='recorrido' style='display:none'><label>Recorrido:</label><select name='recorrido_t' style='float:center;width:310px;' size='1'>";
	  if($row[Recorrido]<>''){
		echo "<option value='$row[Recorrido]'>".$row[Recorrido]."</option>";
		}
		while ($Dato = mysql_fetch_array($estructura)){
		echo "<option value='".$Dato[Numero]."'>".$Dato[Numero]."/".$Dato[Nombre]."</option>";
	}
echo "</select></div>";
echo "<div><input type='submit' value='Agregar' name='Accion' style='width:100px' ></div>";
echo "</form>";	
goto a;
}

if($_GET['Accion']=='Agregar'){
$Localidad=$_GET['localidad_t'];
$Provincia=$_GET['provincia_t'];
$Recorrido=$_GET['recorrido_t'];
$Sucursal=$_SESSION['Sucursal'];
if($_GET['web_t']=='on'){
  $Web=1;  
}else{
  $Web=0;  
}
$sql="INSERT INTO Localidades(Localidad,Provincia,Recorrido,Web)VALUES('{$Localidad}','{$Provincia}','{$Recorrido}','{$Web}')";
mysql_query($sql);
} 

if($_GET['Accion']=='Modificar'){
$idLocalidad=$_GET['idlocalidad_t'];	
$Localidad=$_GET['localidad_t'];
$Provincia=$_GET['provincia_t'];
$Recorrido=$_GET['recorrido_t'];
if($_GET['web_t']=='on'){
  $Web=1;  
}else{
  $Web=0;  
}
$sql="UPDATE Localidades SET Localidad='$Localidad',Provincia='$Provincia',Recorrido='$Recorrido',Web='$Web' WHERE id='$idLocalidad'";
mysql_query($sql);
}

$Grupo="SELECT * FROM Localidades ORDER BY Localidad ASC";
$estructura= mysql_query($Grupo);

echo "<table class='header' >";
echo "<caption>Localidades</caption>";
echo "<td style='width:10%'>id</td>";
echo "<td style='width:30%'>Localidad</td>";
echo "<td style='width:20%'>Provincia</td>";
echo "<td style='width:20%'>Recorrido</td>";
echo "<td style='width:20%'>Modificar</td>";
echo "</table>";
echo "<div style='overflow:auto;height:100%'>";

echo "<table class='login'>";
$numfilas='0';
while($row = mysql_fetch_array($estructura)){
if($numfilas%2 == 0){
	echo "<tr style='background:$color1;' >";
	}else{
	echo "<tr style='background:$color2;' >";
	}	
		
echo "<td style='width:20%'>$row[id]</td>";
echo "<td style='width:30%'>$row[Localidad]</td>";
echo "<td style='width:20%'>$row[Provincia]</td>";
echo "<td style='width:20%'>$row[Recorrido]</td>";
echo "<td style='width:20%' align='center'><a class='img' href='Localidades.php?Accion=Buscar&Localidad=$row[Localidad]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
	$numfilas++; 
}

// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
?>
</div>
</body>
</center>