<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	
<title>.::SISTEMA DE GESTION RRHH::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<?php 
    echo "<div id='contenedor'>"; 
    echo "<div id='cabecera'>"; 
    include("../Menu/MenuGestion.php"); 

    echo "</div>";//cabecera 
    echo "<div id='cuerpo'>"; 
    echo "<div id='lateral'>"; 
    include("Menu/MenuEmpleados.php"); 
    echo "</div>"; //lateral
    echo  "<div id='principal'>";
  
$Distri=$_SESSION['Distribuidora'];
$color='#B8C6DE';
$font='white';
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['Usuario'];
$IdEmpleado=$_SESSION['idEmpleado'];	
$NombreE=$_SESSION['NombreEmpleado'];	
$sql="SELECT * FROM Empleados WHERE id='$IdEmpleado'";
$estructura= mysql_query($sql);
while ($row = mysql_fetch_row($estructura)){
$Nombre=$row[1];
}	
//-------------------DESDE ACA AGREGAR SANCIONES----------------------------------------------------------------
if ($_GET['Sanciones']=='Agregar'){
echo "<form class='login' action='' method='get' style='width:800px'>";
echo "<div><titulo>Agregar Sancion</titulo></div>";
echo "<hr/>";  
echo "<div><label>Fecha:</label><input name='Fecha_a'  type='date' style='width:150px;'required/></div>";
echo "<div><label>Nombre Empleado:</label><select name='Nombre_a' style='width:300px'>"; 
$sqlEmpleados=mysql_query("SELECT NombreCompleto FROM Empleados WHERE Inactivo=0");
  while($row=mysql_fetch_array($sqlEmpleados)){
  echo "<option value='$row[NombreCompleto]'>$row[NombreCompleto]</option>";
    
  }
 echo "</select>";
  echo "</div>";
echo "<div><label>Por medio de la presente y en virtud a la autoridad que me asigna, comunicamos a Ud. que esta empresa ha decidido,
aplicarle una SANCION DICIPLINARIA consistente en un SEVERO APERCIBIMIENTO con constancia escrita en su legajo y confundamento en la grave inconducta
veificada de su parte detallada a continuación:</label></div>";  
echo "<div><label>Escriba aqui el motivo:</label><textarea cols='80' rows='20' name='Motivo_a' style='font-family: sans-serif;font-size:12px'
/></textarea></div>";
echo "<div><label>		Lo invitamos a reflexionar sobre lo ocurrido y le prevenimos que en caso de incurrir en cualquier 
nueva inconducta nos veremos obligados a sancionarlo con mayor severidad. Queda Ud. debidamente notificado y prevenido.</label></div>";  

echo "<div><label>Sanción a aplicar:</label><select style='width:150px;height:25px;float:right;'name='Sancion_a' >";
echo "<option value='Firma Disciplinaria'>Firma Disciplinaria</option>";
echo "<option value='Suspension 1 dia'>Suspension 1 dia</option>";
echo "<option value='Suspension 2 dias'>Suspension 2 dias</option>";
echo "<option value='Suspension 3 dias'>Suspension 3 dias</option>";
echo "</select></div>";
  
  
echo "<div><input class='submit' name='Confirma' type='submit' value='Agregar' ></div></table>";
echo "</form>";
goto a;
}

if ($_GET['Confirma']=='Agregar'){
		$Fecha=$_GET['Fecha_a'];	
		$Nombre=$_GET['Nombre_a'];
		$Motivo=$_GET['Motivo_a'];
		$Sancion=$_GET['Sancion_a'];
	$sql="INSERT INTO Sanciones(Fecha,idEmpleado,Empleado,Motivo,Sancion,Autoriza)VALUES('{$Fecha}','{$IdEmpleado}','{$Nombre}','{$Motivo}','{$Sancion}','{$Usuario}')";
	mysql_query($sql);
	header("location:Sanciones.php");
}	
//------------------------------------------------HASTA ACA AGREGAR SANCIONES------------------------------------------------
	
			$IdEmpleado=$_SESSION['idEmpleado'];
			$ConsultaSanciones="SELECT * FROM Sanciones WHERE idEmpleado='$IdEmpleado'";
			$MuestraConsulta=mysql_query($ConsultaSanciones);
			
			echo "<table class='login' >";
      echo "<caption>Sanciones Aplicadas</caption>";
      echo "<th>Fecha</th>";
			echo "<th>Empleado</th>";
      echo "<th>Motivo</th>";
			echo "<th>Sancion Aplicada</th>";
			echo "<th>Ver</th>";
			while ($row = mysql_fetch_row($MuestraConsulta)){
			echo "<tr align='justify'><td>$row[1]</td><td>$row[3]</td><td align='justify'>$row[4]</td><td>$row[5]</td>";
			echo "<td align='center'><a href='Informes/Sancionpdf.php?id=$row[0]' target='_blank'><input type='image' src='../images/botones/mas.png'  width='15' height='15' border='0' style='float:center;'></td>";
			}
			echo "</tr></table>";
a:
ob_end_flush();
?>
</div>
</body>
</center>
</html>