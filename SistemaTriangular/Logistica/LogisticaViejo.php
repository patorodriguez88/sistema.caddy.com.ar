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
$Dominio=$_GET['Dominio'];
date_default_timezone_set('Chile/Continental');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script> -->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<!-- <link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" /> -->
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<!-- <script src="ajax.js"></script> -->
	</head>	
  <body>
<script>
function sendForm() {
  var valido = document.getElementById("kmregreso").value; //DEBERIAS REALIZAR LAS VALIDACIONES
  var kmsalida = parseFloat(document.getElementById("kmsalida").value);
  var valido1 = document.getElementById("comp").value; //DEBERIAS REALIZAR LAS VALIDACIONES
  var valido11= parseFloat(valido1);
  var maximo=(valido11*30)/100+(valido11)+(kmsalida);
//    alert(maximo);
  if (valido > maximo) {
  alertify.log("Km Regreso " + valido + " superan los permitidos " +maximo+ " ( km. "+valido1+ "km. +/- 30%)","",0);
  document.getElementById("Orden").disabled=true;
  document.getElementById("Orden").style='opacity:0.5;filter:aplpha(opacity=50)';
  } else {
  document.getElementById("Orden").disabled=false;
  document.getElementById("Orden").style='';
  }
}
</script>
    
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralLogistica.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";
    
if($_GET['id']=='Eliminar'){
$Orden=$_GET['orden_t'];
$sqlEliminar=mysql_query("UPDATE Logistica SET Eliminado='1' WHERE NumerodeOrden='$Orden'");

$DominioEliminar=$_GET['Dominio'];
$sqlActualizar=mysql_query("UPDATE Vehiculos SET Estado='Disponible' WHERE Dominio='$DominioEliminar'");  

header('location:Logistica.php');
}
//-----------------------------------------------DESDE ACA VER ORDENES---------------------------
	if ($_GET['xRec']=='Ver'){
	if($_GET['Recorrido']==''){
		echo "<form class='login' action='' method='GET' style='width:500px' >";
			echo "<div><titulo>Consulta x Recorrido</titulo></div>";
      echo "<div><hr></hr></div>";
			$Grupo="SELECT Recorrido FROM Logistica GROUP BY Recorrido ORDER BY Recorrido ASC";
			$estructura= mysql_query($Grupo);
			echo "<input type='hidden' name='xRec' value='Ver'>";
			echo "<div><label>Recorrido:</label><select name='Recorrido' onchange='submit()' style='float:center;width:390px;' size='1'>";
			while ($row = mysql_fetch_row($estructura)){
			echo "<option value='".$row[0]."'>".$row[0].' '.$row[1]."</option>";
			}
			echo "</select></div>";
		echo "</form>";	
		goto a;
		}

$Recorrido=$_GET['Recorrido'];
$OrdenarxRecorrido=mysql_query("SELECT * FROM Logistica WHERE Recorrido='$Recorrido' AND Eliminado='0' ORDER BY Fecha ASC");
    
echo "<table class='login'>";
echo "<caption>Listado de Ordenes </caption>";
echo "<th>Orden Nº</th>";
echo "<th>Fecha</th>";
echo "<th>Hora</th>";
echo "<th>Patente</th>";
echo "<th>Nombre Chofer</th>";
echo "<th>Acompañante</th>";
echo "<th>Recorrido</th>";
echo "<th>Estado</th>";
echo "<th>Combustible salida</th>";
echo "<th>Km Salida</th>";
echo "<th>Km Regreso</th>";
echo "<th>Km Recorridos</th>";    

		$numfilas =0;
	while($file = mysql_fetch_array($OrdenarxRecorrido)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
		echo "<td>$file[NumerodeOrden]</td>";
		echo "<td>$Fecha1</td>";
		echo "<td>$file[Hora]</td>";
		echo "<td>$file[Patente]</td>";
		echo "<td>$file[NombreChofer]</td>";
		echo "<td>$file[NombreChofer2]</td>";
		echo "<td>$file[Recorrido]</td>";
		echo "<td>$file[Estado]</td>";
		echo "<td>$file[CombustibleSalida]</td>";
	  echo "<td>$file[Kilometros]</td>";	
    echo "<td>$file[KilometrosRegreso]</td>";	
    echo "<td>$file[KilometrosRecorridos]</td>";
		
		$numfilas++; 
		}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
goto a;
}
//--------------------------HASTA ACA PARA VER X RECORRIDO----------------------------	
// -------------------------DESDE ACA VER ORDENES X FECHA-----------------------------
//---------------------------DESDE ACA VER ORDENES---------------------------
		
if ($_GET['id']=='Ver'){
	if($_GET[Dominio]==''){
    		echo "<form class='login' action='' method='GET' style='width:500px' >";
			echo "<div><titulo>Consulta x Vehiculo</titulo></div>";
      echo "<div><hr></hr></div>";
			$Grupo="SELECT * FROM Vehiculos";
			$estructura= mysql_query($Grupo);
			echo "<input type='hidden' name='id' value='Ver'>";
			echo "<div><label>Recorrido:</label><select name='Dominio'  style='float:center;width:390px;' size='1'>";
			while ($row = mysql_fetch_array($estructura)){
			echo "<option value='".$row[Dominio]."'>$row[Marca]  $row[Modelo] $row[Dominio]</option>";
			}
			echo "</select></div>";
		echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</form>";	
		goto a;
    }else{
$Dominio=$_GET[Dominio];
$Desde=$_GET[desde_t];
$Hasta=$_GET[hasta_t];    
$SqlConsulta=mysql_query("SELECT * FROM Logistica WHERE Patente='$Dominio' AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta'");
echo "<table class='login'>";
echo "<caption>Listado de Ordenes </caption>";
echo "<th style='width:5%'>Orden Nº</th>";
echo "<th style='width:10%'>Fecha</th>";
echo "<th style='width:8%'>Hora</th>";
echo "<th style='width:8%'>Patente</th>";
echo "<th style='width:20%'>Nombre Chofer</th>";
echo "<th style='width:20%'>Acompañante</th>";
echo "<th style='width:20%'>Recorrido</th>";
echo "<th style='width:8%'>Estado</th>";
echo "<th style='width:8%'>Comb. Salida</th>";
echo "<th style='width:8%'>Km Salida</th>";
echo "<th style='width:8%'>Km Regreso</th>";
echo "<th style='width:8%'>Km Recorridos</th>";    

    $numfilas =0;
	while($file = mysql_fetch_array($SqlConsulta)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
		echo "<td style='width:5%'>$file[NumerodeOrden]</td>";
		echo "<td style='width:10%'>$Fecha1</td>";
		echo "<td style='width:8%'>$file[Hora]</td>";
		echo "<td style='width:8%'>$file[Patente]</td>";
		echo "<td style='width:20%'>$file[NombreChofer]</td>";
		echo "<td style='width:20%'>$file[NombreChofer2]</td>";
		echo "<td style='width:20%'>$file[Recorrido]</td>";
		echo "<td style='width:8%'>$file[Estado]</td>";
		echo "<td style='width:8%'>$file[CombustibleSalida]</td>";
		echo "<td style='width:8%'>$file[Kilometros]</td>";
		echo "<td style='width:8%'>$file[KilometrosRegreso]</td>";
		echo "<td style='width:8%'>$file[KilometrosRecorridos]</td>";
     
		$numfilas++; 
		}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
echo "</div>";    
goto a;
}
}

    
    
// DESDE ACA X FECHA

 if ($_GET['xFecha']=='Ver'){
	if($_GET[desde_t]==''){
    echo "<form class='login' action='' method='GET' style='width:500px' >";
    echo "<div><titulo>Consulta x Fecha Desde </titulo></div>";
    echo "<div><hr></hr></div>";
		echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<input type='hidden' name='xFecha' value='Ver'>";
    echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</form>";	
		goto a;

    	}else{
$Desde=$_GET[desde_t];
$Hasta=$_GET[hasta_t];    
$SqlConsulta=mysql_query("SELECT * FROM Logistica WHERE Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta'");
echo "<table class='login'>";
echo "<caption>Listado de Ordenes Desde $_GET[desde_t] Hasta $_GET[hasta_t] </caption>";
echo "<th style='width:5%'>Orden Nº</th>";
echo "<th style='width:10%'>Fecha</th>";
echo "<th style='width:8%'>Hora</th>";
echo "<th style='width:8%'>Patente</th>";
echo "<th style='width:20%'>Nombre Chofer</th>";
echo "<th style='width:20%'>Acompañante</th>";
echo "<th style='width:20%'>Recorrido</th>";
echo "<th style='width:8%'>Estado</th>";
echo "<th style='width:8%'>Comb. Salida</th>";
echo "<th style='width:8%'>Km Salida</th>";
echo "<th style='width:8%'>Km Regreso</th>";
echo "<th style='width:8%'>Km Recorridos</th>";    

    $numfilas =0;
	while($file = mysql_fetch_array($SqlConsulta)){
  $sqlRecorrido=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$file[Recorrido]'");
  $datosqlRecorrido=mysql_fetch_array($sqlRecorrido);  

    if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
		echo "<td style='width:5%'>$file[NumerodeOrden]</td>";
		echo "<td style='width:10%'>$Fecha1</td>";
		echo "<td style='width:8%'>$file[Hora]</td>";
		echo "<td style='width:8%'>$file[Patente]</td>";
		echo "<td style='width:20%'>$file[NombreChofer]</td>";
		echo "<td style='width:20%'>$file[NombreChofer2]</td>";
		echo "<td style='width:20%'>($file[Recorrido])$datosqlRecorrido[Nombre]</td>";
		echo "<td style='width:8%'>$file[Estado]</td>";
		echo "<td style='width:8%'>$file[CombustibleSalida]</td>";
		echo "<td style='width:8%'>$file[Kilometros]</td>";
		echo "<td style='width:8%'>$file[KilometrosRegreso]</td>";
		echo "<td style='width:8%'>$file[KilometrosRecorridos]</td>";
     
		$numfilas++; 
		}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
echo "</div>";    
goto a;
}
}

// HASTA ACA X FECHA
  
    
    
    
    
    
if($_GET[Filtro]<>''){
$Filtro=$_GET['Filtro'];
$SqlConsulta=mysql_query("SELECT * FROM Logistica WHERE Estado='$Filtro' AND Eliminado='0'");

// $file=mysql_fetch_array($SqlConsulta);		
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';


echo "<table class='login'>";
echo "<caption>Listado de Ordenes $Filtro</caption>";
echo "<th style='width:5%'>Orden Nº</th>";
echo "<th style='width:10%'>Fecha</th>";
echo "<th style='width:8%'>Hora</th>";
echo "<th style='width:8%'>Patente</th>";
echo "<th style='width:20%'>Nombre Chofer</th>";
echo "<th style='width:20%'>Acompañante</th>";
echo "<th style='width:20%'>Recorrido</th>";
echo "<th style='width:8%'>Estado</th>";
echo "<th style='width:8%'>Comb. Salida</th>";
echo "<th style='width:8%'>Modificar</th>";
echo "<th style='width:8%'>Imprimir</th>";
echo "<th style='width:8%'>Eliminar</th>";    

    $numfilas =0;
	while($file = mysql_fetch_array($SqlConsulta)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
		echo "<td style='width:5%'>$file[NumerodeOrden]</td>";
		echo "<td style='width:10%'>$Fecha1</td>";
		echo "<td style='width:8%'>$file[Hora]</td>";
		echo "<td style='width:8%'>$file[Patente]</td>";
		echo "<td style='width:20%'>$file[NombreChofer]</td>";
		echo "<td style='width:20%'>$file[NombreChofer2]</td>";
		echo "<td style='width:20%'>$file[Recorrido]</td>";
		echo "<td style='width:8%'>$file[Estado]</td>";
		echo "<td style='width:8%'>$file[CombustibleSalida]</td>";
		if ($file[Estado]=='Cerrada'){
		echo "<td style='width:8%' align='center'><a></a></td>";
		echo "<td style='width:8%' align='center'><a></a></td>";
		}elseif($file[Estado]=='Cargada'){
		echo "<td style='width:8%' align='center'><a class='img' href='Logistica.php?id=Cerrar&orden_t=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
// 		echo "<td style='width:8%'></td>";
    echo "<td style='width:8%' align='center'><a target='_blank' class='img' href='http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";

    }elseif($file[Estado]=='Alta'){
		echo "<td style='width:8%' align='center'><a class='img' href='Logistica.php?id=Agregar&orden_t=$file[NumerodeOrden]'><img src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:left;'></a></td>";
    echo "<td style='width:8%' align='center'><a target='_blank' class='img' href='http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
		}	
	  echo "<td align='center'><a class='img' href='Logistica.php?id=Eliminar&orden_t=$file[NumerodeOrden]&Dominio=$file[Patente]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:left;'></a></td>";

		$numfilas++; 
		}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
echo "</div>";    
goto a;
}
  
//---------------------------------------------HASTA ACA VER ORDENES-------------------------------	
	
//-------------------------------------DESDE ACA ALTA ORDEN DE SALIDA------------------------------------
	if ($_GET['id']=='Alta'){
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';

$idOrden= mysql_query("SELECT MAX(NumerodeOrden) AS id FROM Logistica");
if ($row = mysql_fetch_row($idOrden)) {
 $id = trim($row[0])+1;
 }	
echo "<form class='login' action='' method='get' >";
echo "<div><titulo>Agregar Nueva Orden</titulo></div>";
echo "<div><hr></hr></div>";    
echo "<div><label>Numero de orden:</label><input name='ndeorden_t' type='text' value='$id' style='width:150px;'/></div>";
echo "<div><label>Fecha:</label><input name='fecha_t' type='date' value='' style='width:150px;' required/></div>";
echo "<div><label>Hora:</label><input name='hora_t' type='time' style='width:150px;'/></div>";
echo "<div><label>Controla:</label><input name='controla_t' type='text' value='".$_SESSION['Usuario']."' style='width:150px;'/></div>";
	
  $Grupo="SELECT * FROM Vehiculos WHERE Estado='Disponible'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Patente:</label><select name='patente_t' style='float:right;width:330px;' size='0'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[3]."'>".$row[1]." ".$row[2]." (".$row[3].")</option>";
	}
	echo "</select></div>";
	$Grupo="SELECT * FROM Empleados WHERE Puesto='Transportista' AND Inactivo='0'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Chofer Asignado:</label><select name='chofer_t' style='float:right;width:330px;' size='0'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[1]."</option>";
		}
echo "</select></div>";
echo "<div><label>Acompañante:</label><input name='acompanante_t' type='text' style='width:330px;'/></div>";
	$Grupo="SELECT * FROM Recorridos";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Recorrido:</label><select name='recorrido_t' style='float:right;width:330px;' size='0'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[2]." (".$row[1].")</option>";
		}
echo "</select></div>";
echo "<div><input class='submit' name='Orden' type='submit' value='Agregar'></div>";
echo "</form>";
goto a;
}
if ($_GET['Orden']=='Agregar'){
$Numero=$_GET['ndeorden_t'];
$Fecha=$_GET['fecha_t'];
$Hora=$_GET['hora_t'];
$Controla=$_GET['controla_t'];
$Patente=$_GET['patente_t'];
$Chofer=$_GET['chofer_t'];
$Acompanante=$_GET['acompanante_t'];
$Recorrido=$_GET['recorrido_t'];
$Estado='Alta';

$sql=mysql_query("SELECT Cliente FROM Recorridos WHERE Numero='$Recorrido'");
$ClienteEncontrado=mysql_fetch_array($sql);
$Cliente=$ClienteEncontrado[Cliente]; 
  
	$idKilometros=mysql_query("SELECT * FROM Vehiculos WHERE Dominio='$Patente'");
	$fila1=mysql_fetch_array($idKilometros);
	$Kilometros=$fila1[Kilometros];
	$Observaciones=$fila1[Observaciones];
	$FechaVencSeguro=$fila1[FechaVencSeguro];
	$CombustibleSalida=$fila1[NivelCombustible];
  
  $idFechaVencReg=mysql_query("SELECT VencimientoLicencia,Usuario FROM Empleados WHERE NombreCompleto='$Chofer'");
	$fila=mysql_fetch_array($idFechaVencReg);
	$FechaVencRegistro=$fila[VencimientoLicencia];
  $IdUsuarioChofer=$fila[Usuario];
	
	$sql="INSERT INTO Logistica(
			NumerodeOrden,
			Fecha,
			Hora,
			Controla,
			Patente,
			Kilometros,
			NombreChofer,
			NombreChofer2,
			Recorrido,
			FechaVencRegistro,
			FechaVencSeguro,
			Observaciones,
			Estado,CombustibleSalida,idUsuarioChofer,Cliente) VALUES (
			'{$Numero}',
			'{$Fecha}',
			'{$Hora}',
			'{$Controla}',
			'{$Patente}',
			'{$Kilometros}',
			'{$Chofer}',
			'{$Acompanante}',
			'{$Recorrido}',
			'{$FechaVencRegistro}',
			'{$FechaVencSeguro}',
			'{$Observaciones}',
			'{$Estado}',
			'{$CombustibleSalida}',
      '{$IdUsuarioChofer}',
      '{$Cliente}')";
mysql_query($sql);
$Recorrido=$_GET['recorrido_t'];
// AGREGAR EN HOJADERUTA  EL NUMERO DE ORDEN A LAS QUE CORRESPONDAN CON EL RECORRIDO Y ESTEN ABIERTAS	
$sql3="UPDATE HojaDeRuta SET NumeroDeOrden ='$Numero' WHERE Recorrido='$Recorrido' AND Estado='Abierta' AND Eliminado='0'";
mysql_query($sql3);
  
// INCLUIR LOS QUE DICEN DEJAR FIJO	
$sql4="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Asignado='Dejar Fijo'";
$sql42=mysql_query($sql4);
$Posicion=0;
while ($row = mysql_fetch_array($sql42)){
$ejecutar="INSERT INTO HojaDeRuta (`Fecha`, `Hora`, `Recorrido`, `Localizacion`, `Cliente`, `Titulo`, `Observaciones`, `Usuario`, `Asignado`,
`Estado`, `NumerodeOrden`, `Posicion`)VALUES ('{$Fecha}','{$Hora}','{$row[Recorrido]}','{$row[Localizacion]}','{$row[Cliente]}','{$row[Titulo]}','{$row[Observaciones]}',
'{$row[Usuario]}','Unica Vez','Abierto','{$Numero}','{$Posicion}')";	
// mysql_query($ejecutar);
$Posicion=$Posicion+1;
}	

$sql2="UPDATE Vehiculos SET Estado ='Alta en recorrido $Recorrido' WHERE Dominio='$Patente'";
mysql_query($sql2);

$BuscarPosision=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto'");
$BP=mysql_fetch_array($BuscarPosision);
$BP1=trim($BP[Posicion]+1);
  
// $sql4="UPDATE HojaDeRuta SET NumerodeOrden ='$Numero',Posicion='$BP1' WHERE Recorrido='$Recorrido' AND Estado='Abierto'";
// mysql_query($sql4);

$sql5="UPDATE TransClientes SET Transportista ='$Chofer' WHERE Recorrido='$Recorrido' AND Entregado='0' and Eliminado='0'";
mysql_query($sql5);
	
?><script>window.open('https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=<? echo $Numero;?>');</script><?
// header("location:Logistica.php");
}	
//-------------------------------------------------HASTA ACA ALTA ORDENES DE SALIDA----------------------------------	
if ($_GET['id']=='Agregar'){
			if($_GET['orden_t']==''){
			echo "<form class='login' action='' method='get' style='width:500px'>";
			echo "<div><titulo>Buscar Orden</titulo></div>";
      echo "<div><hr></hr></div>";  
			echo "<div><label>Numero de orden:</label><input name='orden_t' type='text' style='width:150px;'></div>";
			echo "<div><input class='submit' name='id' type='submit' value='Agregar'></div></table>";
			goto a;
}else{
$Orden=$_GET['orden_t'];	
$sql="SELECT * FROM Logistica WHERE NumerodeOrden='$Orden'";
$estructura= mysql_query($sql);
	if (!(mysql_num_rows($estructura))){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alert("ERROR: LA ORDEN NO FUE DADA DE ALTA O YA SE ENCUENTRA CERRADA")
		</script>
		<?php
	}	

				while ($file = mysql_fetch_array($estructura)){
	$idKilometros=mysql_query("SELECT Marca, Modelo, NivelCombustible FROM Vehiculos WHERE Dominio='".$file[Patente]."'");
	$fila=mysql_fetch_array($idKilometros);
	$Marca=$fila[Marca];
	$Modelo=$fila[Modelo];
	$NivelCombustible=$fila[NivelCombustible];
	
echo "<form class='login' action='' method='get' ><div>";
echo "<div><titulo>Cargar orden generada</titulo></div>";
echo "<div><hr></hr></div>";          
echo "<fieldset style='float:left;width:45%;'>";
echo "<div><label>Numero de orden:</label><input name='orden_t' type='text' value='".$file[NumerodeOrden]."' style='width:120px;'/></div>";
echo "<div><label>Fecha:</label><input name='' type='text' value='".$file[Fecha]."' style='width:120px;'/></div>";
echo "<div><label>Hora:</label><input name='' type='text' value='".$file[Hora]."' style='width:120px;'/></div>";
echo "<div><label>Cargó:</label><input name='' type='text' value='".$file[Controla]."' style='width:120px;'/></div>";
echo "<div><label>Vehiculo:</label><input name='' type='text' value='$Marca $Modelo' style='width:250px;'/></div>";
echo "<div><label>Patente:</label><input name='patente_t' type='text' value='".$file[Patente]."' style='width:120px;'/></div>";
echo "<div><label>Kilometros:</label><input name='kilometros_t' type='text' value='".$file[Kilometros]."' style='width:120px;'/></div>";
echo "<div><label>Chofer:</label><input name='' type='text' value='".$file[NombreChofer]."' style='width:250px;'/></div>";
echo "<div><label>Acompañante:</label><input name='acompanante_t' type='text' value='".$file[NombreChofer2]."' style='width:250px;'/></div>";
echo "<div><label>Recorrido:</label><input name='recorrido_t' type='text' value='".$file[Recorrido]."' style='width:250px;'/></div>";
echo "<div><label>Tarjeta Verde:</label><input name='tarjetaverde_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Tarjeta Azul:</label><input name='tarjetaazul_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Comprobante Seguro:</label><input name='seguro_t' type='checkbox' value='Si' style='float:right'/></div></fieldset>";
echo "<fieldset style='float:left;width:45%;margin-left:15px;'>";

echo "<div><label>Cubiertas:</label><input name='cubiertas_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Auxilio:</label><input name='auxilio_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Chapas Patentes:</label><input name='patentes_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Posicion:</label><input name='luzposicion_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Luces Bajas:</label><input name='luzbaja_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Altas:</label><input name='luzalta_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Frenos:</label><input name='luzfreno_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>GNC Funcionando:</label><input name='gnc_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Nivel Tanque de Combustible:</label><input name='combustible_t' type='text' value='".$fila[NivelCombustible]."' style='float:right;width:50px' /></div>";
echo "<div><label>Observaciones:</label><textarea rows='6' cols='45' name='observaciones_t'>$file[Observaciones]</textarea></div>";
echo "<div><input class='submit' name='Orden' type='submit' value='Cargar'></div></table>";
echo "</form>";
goto a;
	}
		}
			}

if ($_GET['Orden']=='Cargar'){
$Orden=$_GET['orden_t'];	
$Kilometros=$_GET['kilometros_t'];
$Acompanante=$_GET['acompanante_t'];
$Recorrido=$_GET['recorrido_t'];
$TarjetaVerde=$_GET['tarjetaverde_t'];
$TarjetaAzul=$_GET['tarjetaazul_t'];
$ComprobanteSeguro=$_GET['seguro_t'];
$Cubiertas=$_GET['cubiertas_t'];
$Auxilio=$_GET['auxilio_t'];
$Patentes=$_GET['patentes_t'];
$LuzPosicion=$_GET['luzposicion_t'];
$LuzBaja=$_GET['luzbaja_t'];
$LuzAlta=$_GET['luzalta_t'];
$LuzFreno=$_GET['luzfreno_t'];
$Gnc=$_GET['gnc_t'];
$Observaciones=$_GET['observaciones_t'];
$Estado='Cargada';	
$Patente=$_GET['patente_t'];
$Combustible=$_GET['combustible_t'];	
	$sql="UPDATE Logistica SET 
	Kilometros='$Kilometros',
	NombreChofer2='$Acompanante',
	Recorrido='$Recorrido',
	TarjetaVerde='$TarjetaVerde',
	ComprobanteSeguro='$ComprobanteSeguro',
	Cubiertas='$Cubiertas',
	Auxilio='$Auxilio',
	ChapasPatentes='$Patente',
	LucesPosicion='$LuzPosicion',
	LucesBajas='$LuzBaja',
	LucesAltas='$LuzAlta',
	LucesFreno='$LuzFreno',
	GNCFuncionando='$Gnc',
	Observaciones='$Observaciones',
	Estado='$Estado',
	CombustibleSalida='$Combustible' WHERE NumerodeOrden='$Orden';";
mysql_query($sql);

$sql2="UPDATE Vehiculos SET Observaciones ='$Observaciones' WHERE Dominio='$Patente'";
mysql_query($sql2);

//   DESDE ACA PARA CARGAR EN SEGUIMIENTO EL ENVIO QUE SE ENCUENTRE EN ESTA HOJA DE RUTA
$Sqlbuscarenvios=mysql_query("SELECT * FROM TransClientes WHERE Recorrido='$Recorrido' AND Entregado='0' AND Eliminado='0'");

while($row=mysql_fetch_array($Sqlbuscarenvios)){
 // CARGO EL SEGUIMIENTO 
$CodigoSeguimiento=$row[CodigoSeguimiento];
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];	
$Sucursal=$_SESSION['Sucursal'];
$Observaciones='Cargado en el Vehiculo para entrega';	
$Entregado='0';
$Estado='En Transito';
$Localizacion=$row[DomicilioDestino].", ".$row[LocalidadDestino];  
$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Destino)
VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Localizacion}')";
// VERIFICO QUE NO ESTE CARGADO YA EN SEGUIMIENTO
$sqlBuscaidem=mysql_query("SELECT id FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Observaciones='$Observaciones'");
$Datoidem=mysql_fetch_array($sqlBuscaidem);
if($Datoidem[id]==''){  
mysql_query($sql);
}  
//   BUSCO DATOS DEL CLIENTE MAIL Y NUMERO DE CLIENTE SI EL CLIENTE TIENE AVISOS EN ENVIO Y LA CONDICION ES TODOS LOS REMITOS
$SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE NombreCliente ='$row[RazonSocial]' AND AvisoEnEnvios='1' AND Condicion='TR' 
AND TipoDeAviso='Mail' ORDER BY Mail");
$SqlResult=mysql_fetch_array($SqlBuscaMail);

// COMPRUEBA QUE NO SE DUPLIQUE EL MAIL  
if($SqlResult[Mail]==$MailSeleccionado){
$MailSeleccionado='';  
}else{
$MailSeleccionado=$SqlResult[Mail];    
}  

  
$NombreCliente=$row[RazonSocial];  
$SqlBuscaDestino=mysql_query("SELECT nombrecliente,idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]'");
$SqlResultD=mysql_fetch_array($SqlBuscaDestino);
  
// DESDE ACA ENVIA EL MAIL
$asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 
	$mensaje ="<html><body><strong>Seguimiento de envio N $row[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido esta en camino:<br><b>";
	
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
  <td>Destino</td>
	<td>Sucursal</td>
	<td>Estado</td></tr>";
 $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento'");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=mysql_fetch_array($SqlSeguimiento)){
 
   $Fecha=explode('-',$row1[Fecha],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
  $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1[Hora]."</td>
  <td>".utf8_decode($row1[Destino])."</td>
  <td>".utf8_decode($row1[Sucursal])."</td>
  <td>".$row1[Estado]."</td></tr>";
 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

mail($MailSeleccionado,$asunto,$mensaje,$headers);
}
	header("location:Logistica.php");			
}
//------------------------------------------------HASTA ACA CARGAR ORDENES GENERDAS-----------------------------------------------
	if ($_GET['id']=='Cerrar'){
			if($_GET['orden_t']==''){
				
			echo "<form class='login' action='' method='get' style='width:500px'>";
			echo "<div><titulo>Cerrar Orden</titulo></div>";
      echo "<div><hr></hr></div>";  
			echo "<div><label>Numero de orden:</label><input name='orden_t' type='text' style='width:100px;'></div>";
			echo "<div><input class='submit' name='id' type='submit' value='Cerrar'></div></table>";
			goto a;
}else{
$Orden=$_GET['orden_t'];				
$Orden=$_GET['orden_t'];	
$sql="SELECT * FROM Logistica WHERE NumerodeOrden='$Orden' AND Estado='Cargada' AND Eliminado='0'";
$estructura= mysql_query($sql) or die(mysql_error());
// $numerofilas = mysql_num_rows($estructura);	
	if (!(mysql_num_rows($estructura))){
	?>
	<script>
	  alertify.alert("LA ORDEN SE ENCUENTRA SIN CARGAR O YA SE ENCUENTRA CERRADA", 
                   function () {alertify.success("Intentanlo nuevamente");
		      });
    </script>
    <?php
	}	
				
while ($file = mysql_fetch_array($estructura)){
$Fecha=explode("-",$file[Fecha],3);
$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
  $sqlkm=mysql_query("SELECT Kilometros FROM Recorridos WHERE Numero='$file[Recorrido]' ");
  $KmRecorrido=mysql_result($sqlkm,0);
  
echo "<form id='myForm' class='login' action='' method='get' style='width:650px'>";
echo "<div><titulo>Cerrar Orden</titulo></div>";
echo "<div><hr></hr></div>";
 
echo "<div><label>Fecha de Alta:</label><input  type='text' value='$Fecha1' style='width:150px;' readonly/></div>";
echo "<div><label>Numero de orden:</label><input name='orden_t' type='text' value='$Orden' style='width:150px;'/></div>";
echo "<div><label>Controla:</label><input name='controla_t' type='text' value='".$_SESSION['Usuario']."' style='width:150px;'/></div>";
echo "<div><label>Vehiculo:</label><input name='patente_t' type='text' value='".$file[Patente]."' style='width:150px;'/></div>";
echo "<div><label>Chofer:</label><input name='chofer_t' type='text' value='".$file[NombreChofer]."' style='width:250px;'readonly/></div>";
echo "<div><label>Acompañante:</label><input name='chofer_t' type='text' value='".$file[NombreChofer2]."' style='width:250px;'readonly/></div>";
echo "<div><label>Recorrido:</label><input name='recorrido_t' type='text' value='".$file[Recorrido]."' style='width:250px;'readonly/></div>";
echo "<div><label>Kilometros segun Recorrido:</label><input type='text' id='comp' value='$KmRecorrido' readonly></div>"; 
  echo "<div><label>Kilometros Salida:</label><input id='kmsalida' name='kmsalida_t' type='text' value='".$file[Kilometros]."' style='width:150px;'/></div>";
echo "<div><label>Combustible Salida:</label><input name='combustiblesalida_t' type='text' value='".$file[CombustibleSalida]."' style='width:150px;' readonly/></div>";
echo "<div><label>Fecha Retorno:</label><input name='fecharetorno_t' type='date' value='' style='width:150px;'/></div>";
echo "<div><label>Hora Retorno:</label><input name='horaretorno_t' type='time' style='width:150px;'/></div>";
echo "<div><label>Kilometros Regreso:</label><input id='kmregreso' name='kilometrosregreso_t' type='text' onBlur='sendForm()' style='width:150px;' required/></div>";
echo"<div><label>Cargo combustible:</label><input type='text' value='' name='carga_t' style='width:50px;' placeholder='litros' ></div>";
echo "<div><label>Nivel Tanque de Combustible:</label><select name='combustibleregreso_t' />";
				echo "<option value='0'>Vacio</option>";
					echo "<option value='1'>1/8</option>";
					echo "<option value='2'>2/8</option>";
					echo "<option value='3'>3/8</option>";
					echo "<option value='4'>4/8</option>";
					echo "<option value='5'>5/8</option>";
					echo "<option value='6'>6/8</option>";
					echo "<option value='7'>7/8</option>";
					echo "<option value='8'>8/8</option></select></div>";
echo "<div><label>Observaciones:</label><textarea rows='10' cols='55' name='observacionesregreso_t'></textarea></div>";
echo "<div><input class='submit' id='Orden' name='Orden' type='submit' value='Cerrar'></div></table>";
echo "</form>";
}
goto a;
}
}
if ($_GET['Orden']=='Cerrar'){	
$Orden=$_GET['orden_t'];	
$Patente=$_GET['patente_t'];	
$KilometrosRegreso=$_GET['kilometrosregreso_t'];
$FechaRetorno=$_GET['fecharetorno_t'];
$HoraRetorno=$_GET['horaretorno_t'];
$ObservacionesRegreso=$_GET['observacionesregreso_t'];
$KilometrosSalida=$_GET['kmsalida_t'];
$KilometrosRecorridos=$KilometrosRegreso-$KilometrosSalida;
$Recorrido=$_GET['recorrido_t'];
  
$idKilometros=mysql_query("SELECT CapacidadTanque FROM Vehiculos WHERE Dominio='$Patente'");
	$fila=mysql_fetch_array($idKilometros);
	$Capacidad=$fila[CapacidadTanque]/8;
		
$CombustibleSalida=$_GET['combustiblesalida_t'];	
$CombustibleRegreso=$_GET['combustibleregreso_t'];
if($CombustibleRegreso==0){	
$CombustibleRegreso2="Vacio";
}elseif($CombustibleRegreso==1){
$CombustibleRegreso2="1/8";
}elseif($CombustibleRegreso==2){
$CombustibleRegreso2="2/8";
}elseif($CombustibleRegreso==3){
$CombustibleRegreso2="3/8";
}elseif($CombustibleRegreso==4){
$CombustibleRegreso2="4/8";
}elseif($CombustibleRegreso==5){
$CombustibleRegreso2="5/8";
}elseif($CombustibleRegreso==6){
$CombustibleRegreso2="6/8";
}elseif($CombustibleRegreso==7){
$CombustibleRegreso2="7/8";
}elseif($CombustibleRegreso==8){
$CombustibleRegreso2="8/8";
}
	
$Carga=$_GET['carga_t'];	
$Consumo=$Capacidad*(($CombustibleSalida+$Carga)-$CombustibleRegreso);
$Estado='Cerrada';	
// print $Consumo;
	
	if ($KilometrosRegreso<$KilometrosSalida){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alert("ERROR: LOS KM DE REGRESO NO PUEDEN SER INFERIORES A LOS DE SALIDA, LA ORDEN NO SE CARGO")
		</script>
		<?php
	goto a;
	}	
	
	$sql="UPDATE Logistica SET 
	KilometrosRegreso='$KilometrosRegreso',
	ObservacionesCierre='$ObservacionesRegreso',
	Estado='$Estado',
	FechaRetorno='$FechaRetorno',
	HoraRetorno='$HoraRetorno',
	KilometrosRecorridos='$KilometrosRecorridos', 
	CombustibleRegreso='$CombustibleRegreso',Consumo='$Consumo',CargaLitros='$Carga' WHERE NumerodeOrden='$Orden';";
mysql_query($sql);
  
$sql2="UPDATE Vehiculos SET Estado ='Disponible', Kilometros='$KilometrosRegreso', NivelCombustible='$CombustibleRegreso2' WHERE Dominio='$Patente'";
mysql_query($sql2);
  
//CERRAR LA HOJA DE RUTA DEL RECORRIDO, PASA EL ESTADO DE ABIERTO A CERRADO
// $sql3="UPDATE HojaDeRuta SET Estado ='Cerrado' WHERE Recorrido='$Recorrido'";
// mysql_query($sql3);

header("location:Logistica.php");			

}
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