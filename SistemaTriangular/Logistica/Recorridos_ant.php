<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.triangularlogistica.com.ar/SistemaTriangular/index.php");
}
$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$color2='white'; 
$font2='black';

?>
<!DOCTYPE html>
    <html lang="es">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../ajax.js"></script>
<link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" />
</head>
<!-- <body style="background:> -->
<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php");
include("../Alertas/alertas.html");   
  
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MLRecorridos.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";
//---------------------------------------DESDE ACA AGREGAR CLIENTES------------------------
if ($_GET['Agregar']=='Si'){
$MaxRecorrido= mysql_query("SELECT MAX(Numero) AS Numero FROM Recorridos");
if ($row = mysql_fetch_row($MaxRecorrido)) {
 $MaxNumeroRecorrido = trim($row[0])+1;
 }

echo "<form class='login' action='' method='get' style='width:500px'>";
echo "<div><titulo>Agregar Nuevo Recorrido</titulo></div>";
echo "<div><hr></hr></div>";
echo "<div><label>Numero:</label><input name='Numero_t' type='text' value='$MaxNumeroRecorrido' style='width:300px;'/></div>";
echo "<div><label>Nombre Recorrido:</label><input name='Nombre_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Zona:</label><input name='Zona_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Kilometros:</label><input name='Kilometros_t' type='text' style='width:300px;'/></div>";
echo "<div><label>Peajes:</label><input name='Peajes_t' type='text' style='width:300px;'required/></div>";
$sql=mysql_query("SELECT id,nombrecliente FROM Clientes");
  echo "<div><label>Cliente:</label><select name='Cliente_t' style='width:300px;'/>";
  echo "<option value='$row[Cliente]'>$row[Cliente]</option>";
  while($row2=mysql_fetch_array($sql)){
  echo "<option value='$row2[id]'>$row2[nombrecliente]</option>";
  }  
echo "</select></div>";
  
echo "<div><input class='submit' name='Recorridos' type='submit' value='Agregar'></div></table>";
echo "</form>";
goto a;
}
if ($_GET['Recorridos']=='Agregar'){
$Numero=$_GET['Numero_t'];
$Nombre=$_GET['Nombre_t'];
$Zona=$_GET['Zona_t'];
$Kilometros=$_GET['Kilometros_t'];
$Peajes=$_GET['Peajes_t'];
$Cliente=$_GET['Cliente_t']; 
  
//--------------COMPRUEBA QUE EL NUMERO DE CLIENTE NO ESTE UTILIZADO------------------------------
$sql="SELECT * FROM Recorridos WHERE Numero='$Numero'";
$estructura= mysql_query($sql);
if(mysql_num_rows($estructura)!=0){
		?><script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
        <script language="JavaScript" type="text/javascript">
        alert("EL NUMERO DE RECORRIDO YA EXISTE... EL RECORRIDO NO SE CARGO!!!")
		</script>
		<?
goto a;
//-------------- HASTA ACA COMPRUEBA QUE EL NUMERO DE CLIENTE NO ESTE UTILIZADO------------------------------
}else{	
$sql="INSERT INTO Recorridos(Numero,
Nombre,
Zona,
Kilometros,
Peajes,
Cliente)VALUES('{$Numero}',
'{$Nombre}',
'{$Zona}',
'{$Kilometros}',
'{$Peajes}',
'{$Cliente}')";
mysql_query($sql);
header("location:Recorridos.php?id=Ver");			
}	
}
	//-------------------------------------------------DESDE ACA MODIFICAR CLIENTES----------------------------------	
if ($_GET['id']=='Modificar'){
$Recorrido=$_GET['Recorrido'];
$sql="SELECT * FROM Recorridos WHERE Numero='$Recorrido'";
$estructura= mysql_query($sql);
$row = mysql_fetch_array($estructura);
echo "<form class='login' action='' method='get' style='width:600px;'><div>";
echo "<div><label style='float:center;color:red;font-size:22px'>Modificar datos de Recorridos</label></div>";
echo "<div><hr></hr></div>";  
echo "<div><label>Numero de Recorrido:</label><input name='Numero_t' type='text' value='$row[Numero]' readonly></div>";
echo "<div><label>Nombre del Recorrido:</label><input name='Nombre_t' type='text' value='$row[Nombre]' style='width:300px;'/></div>";
echo "<div><label>Zona:</label><input name='Zona_t' type='text' value='$row[Zona]' style='width:300px;'/></div>";
echo "<div><label>Kilometros:</label><input name='Kilometros_t' type='text' value='$row[Kilometros]' style='width:300px;'/></div>";
echo "<div><label>Peajes:</label><input name='Peajes_t' type='text' value='$row[Peajes]' style='width:300px;'/></div>";
$sql=mysql_query("SELECT id,nombrecliente FROM Clientes");
  echo "<div><label>Cliente:</label><select name='Cliente_t' style='width:300px;'/>";
  echo "<option value='$row[Cliente]'>$row[Cliente]</option>";
  while($row2=mysql_fetch_array($sql)){
  echo "<option value='$row2[id]'>($row2[id]) $row2[nombrecliente]</option>";
  }  
echo "</select></div>";
echo "<div><label>Codigo Servicio:</label><select name='CodigoServicio_t'style='width:300px;'>"; 
echo "<option value='$row[CodigoProductos]'> $row[CodigoProductos] </option>";
$sql=mysql_query("SELECT Codigo,Titulo FROM Productos");  
while($row1=mysql_fetch_array($sql)){
echo "<option value='$row1[Codigo]'>$row1[Titulo]</option>";  
}
echo "</select></div>";
  if($row[Activo]==1){
    $Activo_value='checked';
  }else{
    $Activo_value='';
  }
echo "<div><label>Activo:</label><input id='activo_t' name='activo_t' type='checkbox' value='$row[Activo]' style='width:300px;' $Activo_value/></div>";  
echo "<div><input class='submit' name='Recorridos' type='submit' value='Modificar'></div></table>";
echo "</form>";
goto a;
}
	
if ($_GET['Recorridos']=='Modificar'){
$Numero=$_GET['Numero_t'];
$Nombre=$_GET['Nombre_t'];
$Zona=$_GET['Zona_t'];
$Kilometros=$_GET['Kilometros_t'];
$Peajes=$_GET['Peajes_t'];
$Cliente=$_GET['Cliente_t'];
$CodigoProductos=$_GET['CodigoServicio_t'];
$Activo=$_GET['activo_t'];
  
$sql="UPDATE Recorridos SET 
Nombre='$Nombre',
Zona='$Zona',
Kilometros='$Kilometros',
Peajes='$Peajes',
Cliente='$Cliente',
CodigoProductos='$CodigoProductos',
Activo='$Activo'
WHERE Numero='$Numero'";
mysql_query($sql);
  
header("location:Recorridos.php?id=Ver&Modificado=Ok");			
}
//------------------------------------------------HASTA ACA MODIFICAR CLIENTES------------------------------------------------
//------------------------------------------------TABLA Clientes---------------------------------

if ($_GET['id']=='Ver'){
  if($_GET[Modificado]=="Ok"){
  ?>
  <script>alertify.success("Recorrido Modificado");</script>
  <?
  }
$Ordenar1="SELECT * FROM Recorridos";
$Stock=mysql_query($Ordenar1);
$numfilas = mysql_num_rows($Stock);
	
$color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';
echo "<table class='login'>";
echo "<caption>Listado de Recorridos</caption>";
echo "<th>Numero</th>";
echo "<th>Nombre</th>";
echo "<th>Zona</th>";
echo "<th>Kilometros</th>";
echo "<th>Peajes</th>";
echo "<th>Cliente</th>";  
echo "<th>Codigo Servicio</th>";
echo "<th>Editar</th>";

  while($row = mysql_fetch_array($Stock)){
$sqlCliente=mysql_query("SELECT nombrecliente FROM Clientes WHERE id='$row[Cliente]'");
$datosqlCliente=mysql_fetch_array($sqlCliente);  
   
	if($numfilas%2 == 0){
	echo "<tr style='background: #f2f2f2;' >";
	}else{
	echo "<tr style='background:$color1;' >";
	}	
echo "<td>$row[Numero]</td>";
echo "<td>$row[Nombre]</td>";
echo "<td>$row[Zona]</td>";
echo "<td>$row[Kilometros]</td>";
echo "<td>$row[Peajes]</td>";
echo "<td>$datosqlCliente[nombrecliente]</td>";    
echo "<td>$row[CodigoProductos]</td>";
		if ($_SESSION['Nivel']==1){
		echo "<td align='center'><a class='img' href='Recorridos.php?id=Modificar&Recorrido=$row[Numero]'><img src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:left;'></a></td>";
		}else{
		echo "<td></td>";	
		}
	$numfilas++; 	
}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
goto a;
}
		//-------------------------------HASTA ACA TABLA Clientes-------------------------

// if ($_GET['Clientes']=='Buscar'){	
// $IdCliente=$_GET['IdCliente'];
// $_SESSION['IdCliente']=$_GET['IdCliente'];	
// $sql="SELECT * FROM Clientes WHERE NdeCliente='$IdCliente'";
// $estructura= mysql_query($sql);
// while ($row = mysql_fetch_row($estructura)){
// //echo "<form class='login' action='' method='get' style='width:900px'>";
// $_SESSION['IdCliente']=$row[0];
// echo "<table style='width:500px;float:center;'>";
// echo "<tr><td class='tbHeader2' colspan='8' style='margin-bottom:20px;background:white;color:black;text-align:center' >Panel de CLientes</td></tr>";
// echo "<tr><td colspan='8'>";
// echo "</td></tr>";
// echo "<tr>";
// echo "<td class='tbHeader2'><label>Numero:</label></td><td class='tbHeader3' style='min-width:80px'><label>$row[1]</label></td>";
// echo "<td class='tbHeader2'><label>Direccion:</label></td><td class='tbHeader3' style='min-width:100px'><label>$row[17]</label></td>";
// echo "<td class='tbHeader2'><label>E-Mail:</label></td><td class='tbHeader3'style='min-width:100px'><label>$row[7]</label></td>";
// echo "<td class='tbHeader2'><label>Recorrido:</label></td><td class='tbHeader3'style='min-width:100px'><label>$row[29]</label></td>";
// echo "</tr>";	
// echo "<tr>";
// echo "<td class='tbHeader2'><label>Nombre:</label></td><td class='tbHeader3'><label>$row[2]</label></td>";
// echo "<td class='tbHeader2'><label>C.P:</label></td><td class='tbHeader3'><label>$row[11]</label></td>";
// echo "<td class='tbHeader2'><label>Telefono:</label></td><td class='tbHeader3'><label>$row[12]</label></td>";
// echo "<td class='tbHeader2'><label>Pagina Web:</label></td><td class='tbHeader3'><label>$row[18]</label></td>";
// echo "</tr>";	
// echo "<tr>";
// echo "<td class='tbHeader2'><label>Dni:</label></td><td class='tbHeader3'><label>$row[3]</label></td>";
// echo "<td class='tbHeader2'><label>Ciudad:</label></td><td class='tbHeader3'><label>$row[8]</label></td>";
// echo "<td class='tbHeader2'><label>Celular:</label></td><td class='tbHeader3'><label>$row[15]</label></td>";
// echo "<td class='tbHeader2'><label>Usuario:</label></td><td class='tbHeader3'><label>$row[26]</label></td>";
// echo "</tr>";	
// echo "<tr>";
// echo "<td class='tbHeader2'><label>Situacion Fiscal:</label></td><td class='tbHeader3'><label>$row[21]</label></td>";
// echo "<td class='tbHeader2'><label>Pais:</label></td><td class='tbHeader3'><label>$row[10]</label></td>";
// echo "<td class='tbHeader2'><label>Cuit:</label></td><td class='tbHeader3'><label>$row[24]</label></td>";
// echo "<td class='tbHeader2'><label>Clave:</label></td><td class='tbHeader3'><label>$row[27]</label></td>";
// echo "</tr>";	
// echo "</table>";
// }
//--------------------------DESDE ACA PARA COMENTARIOS-----------------------------------	
// echo "<form class='login' action='' method='get' style='width:800px'>";
// echo "<div><label>Agregar Comentario:</label></div>";
// echo "<div><label><input name='Novedad_a' type='text' style='width:750px;'/></div>";
// echo "<div><input class='submit' name='Novedades' type='submit' value='Agregar'></div></table>";
// echo "</form>";
//--------------------------HASTA ACA PARA COMENTARIOS-----------------------------------	

$sqlNovedades="SELECT * FROM ClientesNovedades WHERE Cliente='$IdCliente' ORDER BY Fecha DESC, Hora DESC";
$estructuranovedades= mysql_query($sqlNovedades);

	
while ($row = mysql_fetch_row($estructuranovedades)){
		if ($row[2]==date("Y-m-d")){
		$Color='#99CC00';
		}else{
		$Color='#FF6600';
		}

	echo "<form class='login' action='' method='get' style='width:800px;background:$Color;color:white;'>";
	echo "<div><label style='float:left'>$row[2] / $row[3] / $row[4]:  </label><label>$row[5]</label></div>";
	echo "</form>";
}
	goto a;
// }

// if ($_GET['id']=='Buscar'){
// echo "<form class='login' action='' method='get' style='width:500px'>";
// echo "<div><label>Agregar Nuevo Empleado</label></div>";
// echo "<div><label>Nombre Completo:</label><input name='Nombre_a' type='text' style='width:300px;'/></div>";
// echo "<div><label>Dirección:</label><input name='Direccion_a' type='text' style='width:300px;'/></div>";
// echo "<div><label>Fecha de Alta:</label><input name='FechadeAlta_a'  type='date' style='width:100px;'/></div>";
// echo "<div><label>Fecha de Nacimiento:</label><input name='FechaDeNacimiento_a' type='date' style='width:100px;'/></div>";
// echo "<div><label>Estado Civil:</label><select style='width:110px;height:25px;float:right;'name='EstadoCivil_a' >";
// echo "<option value='Casado'>Casado</option>";
// echo "<option value='Juntado'>Juntado</option>";
// echo "<option value='Soltero'>Soltero</option>";
// echo "<option value='Separado'>Separado</option>";
// echo "<option value='Divorciado'>Divorciado</option>";
// echo "</select></div>";
// echo "<div><label>Dni:</label><input name='Dni_a' type='text' style='width:100px;'/></div>";
// echo "<div><label>Cuit:</label><input name='Cuil_a' type='text' style='width:100px;'/></div>";
// echo "<div><label>Ciudad:</label><input name='Ciudad_a' type='text' style='width:100px;'/></div>";	
// echo "<div><label>C.P.:</label><input name='CP_a' type='text' style='width:100px;'/></div>";	
// echo "<div><label>Telefono:</label><input name='Telefono_a' type='text' style='width:200px;'/></div>";
// echo "<div><label>Celular:</label><input name='Celular_a' type='text' style='width:200px;'/></div>";
// echo "<div><label>Mail:</label><input name='Mail_a' type='email' style='width:200px;'/></div>";
// echo "<div><label>Puesto:</label><input name='Puesto_a' type='text' style='width:200px;'/></div>";
// echo "<div><label>Cta.Sueldo:</label><input name='CtaSueldo_a' type='text' style='width:200px;'/></div>";
// echo "<div><label>Activo:</label><select name='Activo_a'>";
// echo "<option value='Si'>Si</option>";
// echo "<option value='No'>No</option></Select></div>";
// echo "<div><input class='submit' name='Empleados' type='submit' value='Aceptar'></div></table>";
// echo "</form>";
goto a;
// }
	
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
  
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>