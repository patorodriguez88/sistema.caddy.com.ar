<?php
// ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.caddy.com.ar/SistemaTriangular/index.php");
}
$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$color2='white'; 
$font2='black';
$Dominio=$_GET['Dominio'];
// date_default_timezone_set('Chile/Continental');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- <link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'> -->
<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script> -->
<!-- <script type="text/javascript" src="../scripts/jquery.js"></script> -->
<!-- <script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script> -->
<!-- <script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script> -->
<!-- <link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" /> -->
<!-- <script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script> -->
<!-- <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script> -->
<!-- <script src="js/miscript.js"></script> -->
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

<script>
function verificar(){
// alertify.error("SEGURO VENCIDO, IMPOSIBLE SEGUIR"); 
document.getElementById('botonCargar').style.disabled=true;
document.getElementById("botonCargar").style='opacity:0.5;filter:aplpha(opacity=50)';
}    
</script>

<?

echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
include("../Menu/MenuGestion.php"); 
include("AgregarRepo.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralLogistica.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";
    
if($_GET['id']=='Eliminar'){

$Orden=$_GET['orden_t'];
// ELIMINO EN LOGISTICA
$sqlEliminar=mysql_query("UPDATE Logistica SET Eliminado='1' WHERE NumerodeOrden='$Orden'");

//ELIMINO EN CALENDARIO
$sqlEliminar=mysql_query("UPDATE Calendario SET Eliminado='1' WHERE NumerodeOrden='$Orden'");

//ELIMINO EN CTAS CTES
$id_logistica=$_GET['id_Logistica'];
$sqlEliminar=mysql_query("UPDATE Ctasctes SET Eliminado='1' WHERE idLogistica='$id_logistica'");  

// ACTUALIZO EN VEHICULOS
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
        $Grupo="SELECT Logistica.Recorrido,Recorridos.Nombre FROM Logistica INNER JOIN Recorridos ON Recorridos.Numero=Logistica.Recorrido 
        GROUP BY Recorrido ORDER BY Recorrido ASC";
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
			$Grupo="SELECT * FROM Vehiculos WHERE Activo='Si'";
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
    echo "<form class='Caddy' action='' method='GET' style='width:500px' >";
    echo "<div><titulo>Consulta de Ordenes x Fecha</titulo></div>";
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
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:14px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
    $sqlrecorridos=mysql_query("SELECT * FROM Recorridos WHERE Numero='$file[Recorrido]'");
    $datosqlrecorridos=mysql_fetch_array($sqlrecorridos);
    
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
		echo "<td style='width:5%'>$file[NumerodeOrden]</td>";
		echo "<td style='width:10%'>$Fecha1</td>";
		echo "<td style='width:8%'>$file[Hora]</td>";
		echo "<td style='width:8%'>$file[Patente]</td>";
		echo "<td style='width:20%'>$file[NombreChofer]</td>";
		echo "<td style='width:20%'>$file[NombreChofer2]</td>";
		echo "<td style='width:20%'>$file[Recorrido] - $datosqlrecorridos[Nombre]</td>";
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
if($_GET['Filtro']<>''){

	if($_GET['desde_t']==''){
    echo "<form class='Caddy' action='' method='GET' style='width:500px' >";
    echo "<div><titulo>Filtro para Consulta de Orden $_GET[Filtro]</titulo></div>";
    echo "<div><hr></hr></div>";

      $Grupo="SELECT * FROM Vehiculos WHERE Activo='Si'";
			$estructura= mysql_query($Grupo);
      
      echo "<div><label>Dominio:</label><select name='Dominio' style='float:center;width:250px;' size='1'>";
      echo "<option value='Todos'>Seleccionar Todos</option>";
      while ($row = mysql_fetch_array($estructura)){
      echo "<option value='".$row[Dominio]."'>$row[Marca] $row[Modelo] $row[Dominio]</option>";
			}
			echo "</select></div>";

	echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<input type='hidden' name='Filtro' value='$_GET[Filtro]'>";
    echo "<div><input name='294' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</form>";	
		goto a;
    }else{

$Desde=$_GET['desde_t']; 
$Hasta=$_GET['hasta_t'];
$Dominio=$_GET['Dominio'];
$Filtro=$_GET['Filtro'];

  if($_GET['Dominio']=='Todos'){
  $SqlConsulta=mysql_query("SELECT * FROM Logistica WHERE Estado='$Filtro' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Eliminado='0'");
  }else{
  $SqlConsulta=mysql_query("SELECT * FROM Logistica WHERE Estado='$Filtro' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Eliminado='0' AND Patente='$_GET[Dominio]'");
  }    
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';

echo "<table class='login'>";
echo "<caption>Listado de Ordenes $Filtro</caption>";
echo "<th>Id</th>";
echo "<th>Fecha</th>";
echo "<th>Hora</th>";
echo "<th>Patente</th>";
echo "<th>Nombre Chofer</th>";
echo "<th>Servicios</th>";
echo "<th>Recorrido</th>";
echo "<th>Estado</th>";
echo "<th>Comb. Sal.</th>";
echo "<th>Km.</th>";
echo "<th>Total $</th>";    
echo "<th>Editar</th>";
echo "<th>Ver</th>";
if($Filtro=='Cerrada'){
}else{
echo "<th>Eliminar</th>";    
}

  $numfilas =0;

	while($file = mysql_fetch_array($SqlConsulta)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:11px;color:$font1;background:$color1;height:30px;line-height:30px;' >";
	}else{
	echo "<tr align='left' style='font-size:11px;color:$font1;background:$color2;height:30px;line-height:30px;' >";
	}	 
    $sqlrecorridos=mysql_query("SELECT * FROM Recorridos WHERE Numero='$file[Recorrido]'");
    $datosqlrecorridos=mysql_fetch_array($sqlrecorridos);

    $sqlvehiculos=mysql_query("SELECT * FROM Vehiculos WHERE Dominio='$file[Patente]'");
    $datosqlvehiculos=mysql_fetch_array($sqlvehiculos);
    
    //CALCULO $ DE LA ORDEN
    $sqlhdr=mysql_query("SELECT * FROM HojaDeRuta WHERE NumerodeOrden='$file[NumerodeOrden]' AND Eliminado='0'");
    $Total=0;
    while($datosqlhdr=mysql_fetch_array($sqlhdr)){
    $sqltransacciones=mysql_query("SELECT Debe FROM TransClientes WHERE CodigoSeguimiento='$datosqlhdr[Seguimiento]' AND Eliminado='0'");  
    $datotransacciones=mysql_fetch_array($sqltransacciones);
    $Total=$Total+$datotransacciones[Debe];
    }
    
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
        $sql=mysql_query("SELECT COUNT(id)as Total FROM HojaDeRuta WHERE NumerodeOrden='$file[NumerodeOrden]'AND Eliminado='0'");
        $sqltotal=mysql_fetch_array($sql);
		echo "<td>$file[NumerodeOrden]</td>";
		echo "<td>$Fecha1</td>";
		echo "<td>$file[Hora]</td>";
		echo "<td>[$file[Patente]] $datosqlvehiculos[Marca] $datosqlvehiculos[Modelo]</td>";

      if($file['NombreChofer2']==''){
      echo "<td>$file[NombreChofer]</td>";
      }else{
      echo "<td>$file[NombreChofer] | $file[NombreChofer2]</td>";
      }
        echo "<td style='text-align:center'>$sqltotal[Total]</td>";
        echo "<td>[$file[Recorrido]]  $datosqlrecorridos[Nombre]</td>";
        echo "<td>$file[Estado]</td>";
        echo "<td>$file[CombustibleSalida]</td>";
        echo "<td>$file[KilometrosRecorridos]</td>";
        echo "<td>$ $Total</td>";
    
		if ($file[Estado]=='Cerrada'){
		echo "<td align='center'><a></a></td>";
        echo "<td align='center'><a target='_blank' class='img' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ResumenVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
  
// 		echo "<td style='width:8%' align='center'><a></a></td>";
		}elseif($file[Estado]=='Cargada'){
		echo "<td style='width:8%' align='center'><a class='img' href='Logistica.php?id=Cerrar&orden_t=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></a></td>";
// 		echo "<td style='width:8%'></td>";
        echo "<td style='width:8%' align='center'><a target='_blank' class='img' href='http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></a></td>";
		echo "<td align='center'><a class='img' href='Logistica.php?id=Eliminar&orden_t=$file[NumerodeOrden]&Dominio=$file[Patente]&id_Logistica=$file[id]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:left;'></a></td>";

        }elseif($file[Estado]=='Alta'){
		echo "<td style='width:8%' align='center'><a class='img' href='Logistica.php?id=Alta&orden_t=$file[NumerodeOrden]'><img src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:center;'></a></td>";
        echo "<td style='width:8%' align='center'><a target='_blank' class='img' href='http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></a></td>";
		echo "<td align='center'><a class='img' href='Logistica.php?id=Eliminar&orden_t=$file[NumerodeOrden]&Dominio=$file[Patente]&id_Logistica=$file[id]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:left;'></a></td>";
        
        }elseif($file[Estado]=='Pendiente'){
        echo "<td></td>";
        echo "<td style='width:8%' align='center'><a target='_blank' class='img' href='http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></a></td>";
        echo "<td align='center'><a class='img' href='Logistica.php?id=Eliminar&orden_t=$file[NumerodeOrden]&Dominio=$file[Patente]&id_Logistica=$file[id]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
    }
    $TotalAcumulado+=$sqltotal[Total];
	$numfilas++; 
    $TotalFinal+=$Total;
    $TotalKm+=$file[KilometrosRecorridos];
	}

echo "<tfoot>";
echo "<th colspan='14'> $TotalAcumulado envíos. $TotalKm Km. Recorridos";
$saldo=number_format(($TotalFinal/$TotalAcumulado),2,',','.');
$promedioxkm=number_format(($TotalFinal/$TotalKm),2,',','.');  
$TotalFinal=number_format($TotalFinal,2,',','.');    
echo " Total: $ $TotalFinal | Promedio ($ $saldo) x envío. Promedio ($ $promedioxkm) x km. </th>";
    echo "</tfoot>";
echo "</table>";
echo "</div>";    
goto a;
}
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

$type='date';
$Titulo='Agregar Nueva Orden';
$valuebotton='Agregar';    
    
if($_GET[orden_t]<>''){
$sqlverordenes=mysql_query("SELECT * FROM Logistica WHERE NumerodeOrden='$_GET[orden_t]' AND Eliminado=0");
$datosqlverordenes=mysql_fetch_array($sqlverordenes);  
$id=$datosqlverordenes[NumerodeOrden];
$type='text';
$Titulo='Modificar Orden en Alta';
$valuebotton='Modificar';  
  
}
echo "<form class='login' action='' method='get' >";
echo "<div><titulo>$Titulo</titulo></div>";
echo "<div><hr></hr></div>";    
echo "<div><label>Numero de orden:</label><input name='ndeorden_t' type='text' value='$id' style='width:150px;'readonly/></div>";
echo "<div><label>Fecha:</label><input name='fecha_t' type='$type' value='$datosqlverordenes[Fecha]' style='width:150px;' required/></div>";
echo "<div><label>Hora:</label><input name='hora_t' type='time' value='$datosqlverordenes[Hora]' style='width:150px;'/></div>";
echo "<div><label>Controla:</label><input name='controla_t' type='text' value='".$_SESSION['Usuario']."' style='width:150px;'/></div>";
	
    
    $Grupo="SELECT * FROM Vehiculos WHERE Activo='Si'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Patente:</label><select name='patente_t' style='float:right;width:330px;' size='0'>";
	echo "<option value=''> Seleccione un Vehiculo </option>";
    
    $dato_sql_externo='';

    while ($row = mysql_fetch_array($estructura)){

        $sql_externo=mysql_query("SELECT NombreCompleto FROM Empleados WHERE Usuario='$row[id_usuario]' AND Usuario<>'0'");
        $dato_sql_externo=mysql_fetch_array($sql_externo);
        
        if($dato_sql_externo['NombreCompleto']){
         $Vehiculo_propiedad=$dato_sql_externo['NombreCompleto'];   
        }else{
         $Vehiculo_propiedad='Caddy';   
        }
	echo "<option value='".$row[Dominio]."'>".$row[Marca]." ".$row[Modelo]." (".$row[Dominio].") -> ("."".$Vehiculo_propiedad.")</option>";
	}
	
    echo "</select></div>";
	$Grupo="SELECT * FROM Empleados WHERE Puesto='Transportista' AND Inactivo='0'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Chofer Asignado:</label><select name='chofer_t' style='float:right;width:330px;' size='0'>";
	echo "<option value=''>--Seleccione un Chofer--</option>";

    while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[1]."</option>";
	}

    echo "</select></div>";
    echo "<div><label>Acompañante:</label><input name='acompanante_t' type='text' style='width:330px;'/></div>";
	

    $Grupo="SELECT * FROM Recorridos WHERE Activo=1";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Recorrido:</label><select name='recorrido_t' style='float:right;width:330px;' size='0'>";
	echo "<option value=''>--Seleccione un Recorrido--</option>";

    while ($row = mysql_fetch_row($estructura)){
    echo "<option value='".$row[1]."'>[".$row[1]."] ".$row[2]."</option>";
    }

    echo "</select></div>";
    
    echo "<div><label>Nuevo Recorrido:</label><input name='nuevo_recorrido_t' id='nuevo_recorrido_t' type='checkbox' value='Si' style='float:right' checked/></div>";

    echo "<div><input class='submit' name='Orden' type='submit' value='$valuebotton'></div>";
    echo "</form>";
    
    goto a;

}
//HASTA ACA ALTA

if ($_GET['Orden']=='Modificar'){
$Numero=$_GET['ndeorden_t'];
$Fecha=$_GET['fecha_t'];
$Hora=$_GET['hora_t'];
$Controla=$_GET['controla_t'];
$Recorrido=$_GET['recorrido_t'];
$Estado='Alta';
$sql=mysql_query("SELECT Cliente FROM Recorridos WHERE Numero='$Recorrido'");
$ClienteEncontrado=mysql_fetch_array($sql);
$Cliente=$ClienteEncontrado[Cliente]; 
$sql=mysql_query("UPDATE Logistica SET Fecha='$Fecha',Hora='$Hora',Controla='$Controla',Recorrido='$Recorrido',Cliente='$Cliente' WHERE NumerodeOrden='$Numero'");

header('Location:Logistica.php');
}  

if ($_GET['Orden']=='Agregar'){

$Numero=$_GET['ndeorden_t'];
$Fecha=$_GET['fecha_t'];
$Hora=$_GET['hora_t'];
$Controla=$_GET['controla_t'];
$Recorrido=$_GET['recorrido_t'];
$Patente=$_GET['patente_t'];
$Chofer=$_GET['chofer_t'];
$Acompanante=$_GET['acompanante_t'];


if($_GET['nuevo_recorrido_t']=='Si'){

    $SQL=mysql_query("SELECT MAX(Numero)as Numero FROM Recorridos");
    $Recorrido_sql=mysql_fetch_array($SQL);//numero
    $Recorrido=trim($Recorrido_sql['Numero']+1);//numero

    $New_recorrido=date('Y-m-d H:i:s').' '.$Chofer;    //nombre

    $SQL=mysql_query("INSERT INTO `Recorridos`(`Numero`, `Nombre`,`Kilometros`, `Peajes`, `Activo`) VALUES ('{$Recorrido}','{$New_recorrido}','500','0','1')");
    

}else{

    $Recorrido=$_GET['recorrido_t'];

}

$Estado='Alta';

$sql=mysql_query("SELECT Cliente FROM Recorridos WHERE Numero='$Recorrido'");
$ClienteEncontrado=mysql_fetch_array($sql);
$Cliente=$ClienteEncontrado[Cliente]; 
  
	$idKilometros=mysql_query("SELECT * FROM Vehiculos WHERE Dominio='$Patente'");
	$fila1=mysql_fetch_array($idKilometros);
  
  $sqlcontrolo=mysql_query("SELECT id FROM Logistica WHERE Patente='$Patente' AND Eliminado=0 AND Estado IN('Alta','Cargada')");
  if(mysql_num_rows($sqlcontrolo)<>0){
  ?>
    <script>
    alertify.error("Los km no se cargaron porque ya existe otra orden en Alta o Cargada");
    </script>
  <?  
	$Kilometros="";
	$Observaciones="";
	$CombustibleSalida="";
    $Estado='Pendiente';
  }else{
	$Kilometros=$fila1[Kilometros];
	$Observaciones=$fila1[Observaciones];
	$CombustibleSalida=$fila1[NivelCombustible];
  }
   
    $FechaVencSeguro=$fila1[FechaVencSeguro];
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
 ?>
<script type="text/javascript">
  agregar_calendario();
</script>
  <?
$Recorrido=$_GET['recorrido_t'];
// // AGREGAR EN HOJADERUTA  EL NUMERO DE ORDEN A LAS QUE CORRESPONDAN CON EL RECORRIDO Y ESTEN ABIERTAS	
// $sql3="UPDATE HojaDeRuta SET NumeroDeOrden ='$Numero' WHERE Recorrido='$Recorrido' AND Estado='Abierta' AND Eliminado='0'";
// mysql_query($sql3);
  
// // INCLUIR LOS QUE DICEN DEJAR FIJO	
// // $sql4="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Asignado='Dejar Fijo'";
// // $sql42=mysql_query($sql4);
// // $Posicion=0;
// // while ($row = mysql_fetch_array($sql42)){
// // $ejecutar="INSERT INTO HojaDeRuta (`Fecha`, `Hora`, `Recorrido`, `Localizacion`, `Cliente`, `Titulo`, `Observaciones`, `Usuario`, `Asignado`,
// // `Estado`, `NumerodeOrden`, `Posicion`)VALUES ('{$Fecha}','{$Hora}','{$row[Recorrido]}','{$row[Localizacion]}','{$row[Cliente]}','{$row[Titulo]}','{$row[Observaciones]}',
// // '{$row[Usuario]}','Unica Vez','Abierto','{$Numero}','{$Posicion}')";	
// // // mysql_query($ejecutar);
// // $Posicion=$Posicion+1;
// // }	

// $sql2="UPDATE Vehiculos SET Estado ='Alta en recorrido $Recorrido' WHERE Dominio='$Patente'";
// mysql_query($sql2);

// $BuscarPosision=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto'");
// $BP=mysql_fetch_array($BuscarPosision);
// $BP1=trim($BP[Posicion]+1);
  
// // $sql4="UPDATE HojaDeRuta SET NumerodeOrden ='$Numero',Posicion='$BP1' WHERE Recorrido='$Recorrido' AND Estado='Abierto'";
// // mysql_query($sql4);

// $sql5="UPDATE TransClientes SET Transportista ='$Chofer' WHERE Recorrido='$Recorrido' AND Entregado='0' and Eliminado='0'";
// mysql_query($sql5);
	
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
$sql=mysql_query("SELECT * FROM Logistica WHERE NumerodeOrden='$Orden' AND Eliminado=0 AND Estado IN('Alta','Pendiente')");
	if ((mysql_num_rows($sql)==0)){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alertify.error("ERROR: La orden <? echo $Orden;?> ya fue cargada")
		</script>
<?php
   goto a; 
	}
        
        
	$filePatente=$_GET[patente_t];
  
  $file = mysql_fetch_array($sql);
  
  	if ((($file[Estado])=='Pendiente')){
	?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		alertify.error("ERROR: La orden <? echo $Orden;?> esta Pendiente, debe cerrar la orden anterior.")
		</script>
<?php
   goto a; 
	}
//CODIGO DE PRODUCTOS      
  $sql=mysql_query("SELECT CodigoProductos,Cliente FROM Recorridos WHERE Numero='$file[Recorrido]'");
  $fileCodigoProductos=mysql_fetch_array($sql);  
  if($fileCodigoProductos<>0){      
  ?>
  <script>
  $(document).ready(function(){  
  document.getElementById('precioventa_t').style.display='block';
  document.getElementById('codigoservicio_t').style.display='block';
  document.getElementById('nombrecliente_t').style.display='block';
  });
  </script>
  <?
            
//NOMBRE CLIENTE
  $sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE id='$fileCodigoProductos[Cliente]'");
  $fileNombreCliente=mysql_fetch_array($sql);  
//SERVICIO
  $sql=mysql_query("SELECT PrecioVenta FROM Productos WHERE Codigo='$fileCodigoProductos[CodigoProductos]'");
  $fileServicios=mysql_fetch_array($sql);  
  $PrecioVenta='$ '.number_format($fileServicios[PrecioVenta],2,',','.');
  }      
      
$idKilometros=mysql_query("SELECT Marca, Modelo, NivelCombustible FROM Vehiculos WHERE Dominio='".$file[Patente]."'");
$fila=mysql_fetch_array($idKilometros);
$Marca=$fila[Marca];
$Modelo=$fila[Modelo];
$NivelCombustible=$fila[NivelCombustible];
	
echo "<form class='login' action='' method='get' ><div>";
echo "<div><titulo>Cargar orden generada</titulo></div>";
echo "<div><hr></hr></div>";          
echo "<fieldset style='float:left;width:45%;'>";
// echo "<p><button data-user='1'>Usar mis datos</button></p>";
 echo "<div><label>Numero de orden:</label><input name='orden_t' type='text' value='".$file[NumerodeOrden]."' style='width:120px;'/></div>";
echo "<div><label>Fecha:</label><input name='' type='text' value='".$file[Fecha]."' style='width:120px;'/></div>";
echo "<div><label>Hora:</label><input name='' type='text' value='".$file[Hora]."' style='width:120px;'/></div>";
echo "<div><label>Cargó:</label><input name='' type='text' value='".$file[Controla]."' style='width:120px;'/></div>";

if($file['NombreChofer']==''){
$Grupo="SELECT * FROM Empleados WHERE Puesto='Transportista' AND Inactivo='0'";
	$estructura= mysql_query($Grupo);
    echo "<div><label>Chofer Asignado:</label><select name='chofer_t' style='float:right;width:250px;' size='0'>";
	echo "<option value=''>--Seleccione un Chofer--</option>";	
    while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[1]."</option>";
		}
    echo "</select></div>";
}else{
echo "<div><label>Chofer:</label><input name='chofer_t' type='text' value='".$file[NombreChofer]."' style='width:250px;'/></div>";
}

if($file['Patente']==''){
  $Grupo="SELECT * FROM Vehiculos WHERE Activo='Si'";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Vehiculo:</label><select id='patente' name='patente_t' style='float:right;width:250px;' size='0' >";
	echo "<option data-patente='3' value=''>--Seleccione un Vehiculo--</option>";
  while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[3]."'>".$row[1]." ".$row[2]." (".$row[3].")</option>";
	}
	echo "</select></div>";
}else{
echo "<div><label>Vehiculo:</label><input name='' type='text' value='$Marca $Modelo' style='width:250px;' readonly/></div>";
echo "<div><label>Patente:</label><input name='patente_t' type='text' value='".$file[Patente]."' style='width:120px;' readonly/></div>";
echo "<div><label>Kilometros:</label><input name='kilometros_t' type='text' value='".$file[Kilometros]."' style='width:120px;'/></div>";
}
$FechaSeguro0=explode('-',$file[FechaVencSeguro],3);
$FechaSeguro=$FechaSeguro0[2].'/'.$FechaSeguro0[1].'/'.$FechaSeguro0[0];

if($file['FechaVencSeguro']<date('Y-m-d')){
?>
<!-- <script>
document.getElementById('botonCargar').style.disabled=true;
  
alertify.error("SEGURO VENCIDO IMPOSIBLE SEGUIR");
document.getElementById("botonCargar").style='opacity:0.5;filter:aplpha(opacity=50)';
// document.getElementsByName('Orden100').disabled=true;
</script>
 -->
<?    
}
echo "<div><label>Acompañante:</label><input name='acompanante_t' type='text' style='width:250px;'/></div>";
echo "<div><label>Kilometros:</label><input id='km' name='kilometros_t' type='text' value='".$file[Kilometros]."' style='width:120px;'/></div>";
echo "<div><label>Recorrido:</label><input name='recorrido_t' type='text' value='".$file[Recorrido]."' style='width:250px;'/></div>";
echo "<div><label>Tarjeta Verde:</label><input name='tarjetaverde_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Tarjeta Azul:</label><input name='tarjetaazul_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Comprobante Seguro: (Vence: $FechaSeguro)</label><input name='seguro_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div id='codigoservicio_t' style='display:none'><label>Codigo de Servicio:</label><input name='codigoservicio_t'  type='text' value='$fileCodigoProductos[CodigoProductos]' readonly ></div>";
echo "<div id='nombrecliente_t' style='display:none'><label>Cliente Solicitante:</label><input name='nombrecliente_t' type='text' value='$fileNombreCliente[nombrecliente]' readonly></div>";
echo "<div id='precioventa_t' style='display:none'><label>Precio Servicio:</label><input name='precioventa_t'  type='text' value='$PrecioVenta' readonly></div></fieldset>";
echo "<fieldset style='float:left;width:45%;margin-left:15px;'>";

echo "<div><label>Cubiertas:</label><input name='cubiertas_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Auxilio:</label><input name='auxilio_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Chapas Patentes:</label><input name='patentes_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Posicion:</label><input name='luzposicion_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Luces Bajas:</label><input name='luzbaja_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Altas:</label><input name='luzalta_t' type='checkbox' value='Si' style='float:right'/></div>";
echo "<div><label>Luces Frenos:</label><input name='luzfreno_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>GNC Funcionando:</label><input name='gnc_t' type='checkbox' value='Si' style='float:right' /></div>";
echo "<div><label>Tarjeta de Combustible:</label><input name='ypf_t' type='checkbox' value='Si' style='float:right' /></div>";
        
echo "<div style='margin-bottom:10%'><label>Nivel Tanque de Combustible:</label><input name='combustible_t' type='text' value='".$fila[NivelCombustible]."' style='float:right;width:50px' /></div>";
echo "<div><label>Observaciones:</label><textarea rows='15' cols='75' name='observaciones_t'>$file[Observaciones]</textarea></div>";
echo "<div><input class='submit' id='botonCargar' name='Orden' type='submit' value='Cargar'></div></table>";
echo "</form>";
goto a;
	
		}
}

if ($_GET['Orden']=='Cargar'){
	$idKilometros=mysql_query("SELECT * FROM Vehiculos WHERE Dominio='$Patente'");
	$fila1=mysql_fetch_array($idKilometros);
    $sqlcontrolo=mysql_query("SELECT id FROM Logistica WHERE Patente='$Patente' AND (Estado='Alta' OR Estado='Cargada' OR Estado='Pendiente') AND Eliminado=0");
  if(mysql_num_rows($sqlcontrolo)>1){
  ?>
    <script>
    alertify.error("Los km no se cargaron porque ya existe otra orden en Alta o Cargada");
    </script>
  <?  
	$Kilometros="";
	$Observaciones="";
	$CombustibleSalida="";
  }else{
	$Kilometros=$fila1['Kilometros'];
	$Observaciones=$fila1['Observaciones'];
	$CombustibleSalida=$fila1['NivelCombustible'];
  }
  $Orden=$_GET['orden_t'];

  //CONSIGO EL VALOR DEL RECORRIDO
  $valor_rec=mysql_query("SELECT Productos.PrecioVenta as PrecioVenta FROM Recorridos 
  INNER JOIN Logistica ON Recorridos.Numero = Logistica.Recorrido 
  INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo 
  WHERE Logistica.NumerodeOrden='$Orden'");
  $fila_valor_rec=mysql_fetch_array($valor_rec);
  $PrecioVenta=$fila_valor_rec['PrecioVenta'];

  $Chofer=$_GET['chofer_t'];
  $FechaVencSeguro=$fila1['FechaVencSeguro'];
  $idFechaVencReg=mysql_query("SELECT VencimientoLicencia,Usuario FROM Empleados WHERE NombreCompleto='$Chofer'");
  $fila=mysql_fetch_array($idFechaVencReg);
  $FechaVencRegistro=$fila['VencimientoLicencia'];
  $IdUsuarioChofer=$fila['Usuario'];
  	
    $Kilometros=$_GET['kilometros_t'];
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
    $TarjetaCombustible=$_GET['ypf_t'];  
    $Patente=$_GET['patente_t'];
    $Acompanante=$_GET['acompanante_t'];   
    $Combustible=$_GET['combustible_t'];	

	$sql="UPDATE Logistica SET 
    Patente='$Patente',
    Kilometros='$Kilometros',
    NombreChofer='$Chofer',
    idUsuarioChofer='$IdUsuarioChofer',
    NombreChofer2='$Acompanante',
    Recorrido='$Recorrido',
    TarjetaVerde='$TarjetaVerde',
    ComprobanteSeguro='$ComprobanteSeguro',
    Cubiertas='$Cubiertas',
    Auxilio='$Auxilio',
    ChapasPatentes='$Patentes',
    LucesPosicion='$LuzPosicion',
    LucesBajas='$LuzBaja',
    LucesAltas='$LuzAlta',
    LucesFreno='$LuzFreno',
    GNCFuncionando='$Gnc',
    TarjetaCombustible='$TarjetaCombustible',
    Observaciones='$Observaciones',
    Estado='$Estado',
    TotalFacturado='$PrecioVenta',
    CombustibleSalida='$Combustible' WHERE NumerodeOrden='$Orden' AND Eliminado=0 LIMIT 1";

    mysql_query($sql);

$sql2="UPDATE Vehiculos SET Estado ='Alta en recorrido $Recorrido',Observaciones ='$Observaciones' WHERE Dominio='$Patente' LIMIT 1";
mysql_query($sql2);

//BUSCO EL TOTAL DE PAQUETES VALORIZADOS
$sql_total_paq=mysql_query("SELECT SUM(Debe)as Total_paq FROM TransClientes WHERE Recorrido='$Recorrido' AND Entregado='0' AND Eliminado='0' AND Devuelto='0' AND Haber='0'");
$dato_total_paq=mysql_fetch_array($sql_total_paq);

//ENVIO MAIL 
$to='prodriguez@caddy.com.ar';
$subject='Nuevo Recorrido Cargado';
         
$mensaje ="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='7' style='font-size:22px'>Nuevo Recorrido</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
    <td>Recorrido</td>
	<td>Vehiculo</td>
    <td>Chofer</td>
	<td>Importe Recorrido</td>
    <td>Importe Paquetes</td></tr>";

    $mensaje .="<tr align='left' style='font-size:12px;'>
    <td>".date('Y-m-d')."</td>
    <td>".date('hh:mm')."</td>
    <td>".$Recorrido."</td>
    <td>".utf8_decode($Patente)."</td>
    <td>".utf8_decode($Chofer)."</td>
    <td>".$PrecioVenta."</td>
    <td>".$dato_total_paq[Total_paq]."</td></tr>";



//Envio en formato HTM
$headers = "MIME-Version: 1.0\r\n"; 
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
$headers .= 'From: hola@caddy.com.ar' ."\r\n";

mail($to,$subject,$mensaje,$headers);

// INCLUIR LOS QUE DICEN DEJAR FIJO	
$sqlrecorrido=mysql_query("SELECT Cliente FROM Recorridos WHERE Numero='$Recorrido'");
  if(mysql_num_rows($sqlrecorrido)!=''){
    $datorecorrido=mysql_fetch_array($sqlrecorrido);
    $clienteorigen_t=$datorecorrido[Cliente];
    //IDENTIFICO SI EXISTEN CLIENTES FIJOS EN EL RECORRIDO
            $sql4=mysql_query("SELECT * FROM Roadmap WHERE Recorrido='$Recorrido' AND Asignado='Dejar Fijo' AND Eliminado=0");
            if(mysql_num_rows($sql4)!=''){
            // PRIMERO IDENTIFICO A QUE CLIENTE PERTENECE LA HOJA DE RUTA PARA LUEGO CARGARLO COMO EMISOR
              $Posicion=0;
              while ($row = mysql_fetch_array($sql4)){
              $Posicion=$Posicion+1;

              cargaventa($row[idClienteDestino],$clienteorigen_t,$Recorrido);
              
              }
            }
      }else{
  }


// AGREGAR EN HOJADERUTA  EL NUMERO DE ORDEN A LAS QUE CORRESPONDAN CON EL RECORRIDO Y ESTEN ABIERTAS	
$sql3="UPDATE HojaDeRuta SET NumerodeOrden ='$Orden' WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado='0'";
mysql_query($sql3);



// $BuscarPosision=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto'");
// $BP=mysql_fetch_array($BuscarPosision);
// $BP1=trim($BP[Posicion]+1);
// $sql4="UPDATE HojaDeRuta SET NumerodeOrden ='$Numero',Posicion='$BP1' WHERE Recorrido='$Recorrido' AND Estado='Abierto'";
// mysql_query($sql4);
$sqlfechaorden=mysql_query("SELECT id,Fecha FROM Logistica WHERE NumerodeOrden='$Orden' AND Eliminado=0");
$datofechaorden=mysql_fetch_array($sqlfechaorden);
  
$sql5="UPDATE TransClientes SET Transportista ='$Chofer', FechaEntrega='$datofechaorden[Fecha]',NumerodeOrden='$Orden' WHERE Recorrido='$Recorrido' AND Entregado='0' AND Eliminado='0' AND Devuelto='0' AND Haber='0'";
mysql_query($sql5);

//CODIGO DE PRODUCTOS      
  $sql=mysql_query("SELECT CodigoProductos,Cliente FROM Recorridos WHERE Numero='$Recorrido'");
  $fileCodigoProductos=mysql_fetch_array($sql);  
  if($fileCodigoProductos<>0){      
//NOMBRE CLIENTE
  $sql=mysql_query("SELECT nombrecliente,Cuit FROM Clientes WHERE id='$fileCodigoProductos[Cliente]'");
  $fileNombreCliente=mysql_fetch_array($sql);  
//SERVICIO
  $sql=mysql_query("SELECT PrecioVenta FROM Productos WHERE Codigo='$fileCodigoProductos[CodigoProductos]'");
  $fileServicios=mysql_fetch_array($sql);  
  
  if($fileServicios[PrecioVenta]<>0){
    $Tipo='RECORRIDO '.$Recorrido;
    $Num=$_GET['orden_t'];  
    $ObservacionesCtaCte='ORDEN N '.$Num.' RECORRIDO '.$Recorrido;
  //SI EL RECORRIDO TIENE CARGADO EL SERVICIO CARGO EN LA CUENTA CORRIENTE DEL CLIENTE EL IMPORTE
    $FechaHoy= date("Y-m-d");	  
    $sqlctacte=mysql_query("INSERT INTO `Ctasctes`(`Fecha`,`RazonSocial`,`Cuit`,`TipoDeComprobante`,`NumeroVenta`,`Debe`, `Usuario`,`idCliente`,`Observaciones`,`FacturacionxRecorrido`,idLogistica) VALUES 
    ('{$FechaHoy}','{$fileNombreCliente[nombrecliente]}','{$fileNombreCliente[Cuit]}','{$Tipo}','{$Num}','{$fileServicios[PrecioVenta]}','{$_SESSION['Usuario']}','{$fileCodigoProductos[Cliente]}','{$ObservacionesCtaCte}','1','{$datofechaorden[id]}')");
    
    }      
  } 
  $idKilometros=mysql_query("SELECT Marca, Modelo, NivelCombustible FROM Vehiculos WHERE Dominio='".$file[Patente]."'");
	$fila=mysql_fetch_array($idKilometros);
	$Marca=$fila[Marca];
	$Modelo=$fila[Modelo];
	$NivelCombustible=$fila[NivelCombustible];
  
  
//   DESDE ACA PARA CARGAR EN SEGUIMIENTO EL ENVIO QUE SE ENCUENTRE EN ESTA HOJA DE RUTA
$Sqlbuscarenvios=mysql_query("SELECT * FROM TransClientes WHERE Recorrido='$Recorrido' AND Entregado='0' AND Eliminado='0' AND Devuelto='0' AND Haber='0'");

while($row=mysql_fetch_array($Sqlbuscarenvios)){
 // CARGO EL SEGUIMIENTO 
$CodigoSeguimiento=$row['CodigoSeguimiento'];
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];	
$Sucursal=$_SESSION['Sucursal'];
$Observaciones='Cargado en la Hoja de Ruta';	
$Entregado='0';
// $Estado='En Transito';
  if($row[Retirado]==0){
  $Estado='A Retirar';    
  }else{
  $Estado='En Transito';    
  }
//AGREGO EL ESTADO A TRANSCLIENTES  
$sqlEstado="UPDATE TransClientes SET Estado='$Estado' WHERE id='$row[id]'";
mysql_query($sqlEstado);

  
$Localizacion=$row[DomicilioDestino].", ".$row[LocalidadDestino];  
$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Destino,Recorrido,NumerodeOrden)
VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Localizacion}','$Recorrido','$Orden')";
// VERIFICO QUE NO ESTE CARGADO YA EN SEGUIMIENTO
$sqlBuscaidem=mysql_query("SELECT id FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Estado='$Estado' AND Observaciones='$Observaciones'");
$Datoidem=mysql_fetch_array($sqlBuscaidem);
if($Datoidem[id]==''){  
mysql_query($sql);
}

//BUSCO Y ME ASEGURO QUE TODOS LOS ENVIOS QUE ESTEN SIN ENTREGAR EN TRANS CLIENTES QUEDEN ABIERTOS EN HOJA DE RUTA Y CON EL RECORRIDO CORRESPONDIENTE
$sqlHojaDeRuta=mysql_query("SELECT Estado FROM HojaDeRuta WHERE Eliminado=0 AND idTransClientes='$row[id]'");
$datosqlhojaderuta=mysql_fetch_array($sqlHojaDeRuta);
if($datosqlhojaderuta[Estado]=='Cerrado'){
mysql_query("UPDATE HojaDeRuta SET Estado='Abierto',Recorrido='$Recorrido',NumeroDeOrden ='$Orden' WHERE Eliminado=0 AND idTransClientes='$row[id]'");  
}

//INGRESO EN ROADMAP
if($row['Retirado']==1){
$Localizacion_roadmap=$row['DomicilioDestino'];  
$Ciudad_roadmap=$row['LogalidadDestino'];
$Provincia_roadmap=$row['ProvinciaDestino'];  
$NombreCliente_roadmap=$row['ClienteDestino'];
$idCliente_roadmap=$row['idClienteDestino'];

}else{
$Localizacion_roadmap=$row['DomicilioOrigen'];    
$Ciudad_roadmap=$row['LogalidadOrigen'];  
$Provincia_roadmap=$row['ProvinciaOrigen'];
$NombreCliente_roadmap=$row['ClienteOrigen'];
$idCliente_roadmap=$row['ingBrutosOrigen'];
}

$TipoDeComprobante_roadmap=$row[TipoDeComprobante];
$Pais='Argentina';
$Observaciones_roadmap=$row[Observaciones];
$Estado_roadmap='Abierto';

$IngresaRoadmap=mysql_query("INSERT INTO `Roadmap`(`Fecha`,`Recorrido`, `Localizacion`, `Ciudad`,
`Provincia`,`Pais`,`Cliente`, `Titulo`, `Observaciones`,`Usuario`, `Estado`,
`NumerodeOrden`,`Seguimiento`,`idCliente`,`NumeroRepo`,`ImporteCobranza`,`idTransClientes`)
 VALUES ('{$Fecha}','{$Recorrido}','{$Localizacion_roadmap}','{$Ciudad_roadmap}','{$Provincia_roadmap}','{$Pais}',
'{$NombreCliente_roadmap}','{$TipoDeComprobante_roadmap}','{$Observaciones_roadmap}','{$Usuario}','{$Estado_roadmap}',
'{$Orden}','{$CodigoSeguimiento}','{$row[id]}','{$NumeroRepo}','{$importevalorcobro}','{$row[id]}')");

//WEBHOOK
//DATOS
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$state=$Estado;
$sql=mysql_query("SELECT ingBrutosOrigen,idClienteDestino,CodigoProveedor FROM TransClientes WHERE id='$row[id]");
$idCliente=mysql_fetch_array($sql);
$idClienteOrigen=$idCliente['ingBrutosOrigen'];
$idClienteDestino=$idCliente['idClienteDestino'];

if($idCliente['CodigoProveedor']<>''){
$codigo=$idCliente['CodigoProveedor'];
//CLIENTE ORIGEN
$sql=mysql_query("SELECT Webhook FROM Clientes WHERE id='$idClienteOrigen'");
$Webhook=mysql_fetch_array($sql);

if($Webhook['Webhook']==1){
    //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
    $sql=mysql_query("SELECT * FROM Webhook WHERE idCliente='$idClienteOrigen'");
    if($sql_webhook=mysql_fetch_array($sql)){
      $Servidor=$sql_webhook['Endpoint'];
      $Token=$sql_webhook['Token'];  

      $newstatedate = $Fecha.'T'.$Hora.'Z';

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $Servidor,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "new_state": "'.$state.'", 
    "new_state_date": "'.$newstatedate.'", 
    "package_code": "'.$codigo.'" 
    }',
    CURLOPT_HTTPHEADER => array(
    'x-clicoh-token: '.$Token.'',
    'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    // Comprueba el código de estado HTTP
    if (!curl_errno($curl)) {
    switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
    case 200: 
    $Response=200; # OK
    // break;
    default:
    $Response=$http_code;
    // echo 'Unexpected HTTP code: ', $http_code, "\n";
    }
    }

    curl_close($curl);

    $postfields=$state.' '.$newstatedate.' '.$codigo;

    $sql=mysql_query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
    ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");

    }
}
//end if Cliente Origen

//CLIENTE DESTINO
$sql=mysql_query("SELECT Webhook FROM Clientes WHERE id='$idClienteDestino'");
$Webhook=mysql_fetch_array($sql);
if($Webhook['Webhook']==1){
    //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
    
    $sql=mysql_query("SELECT * FROM Webhook WHERE idCliente='$idClienteDestino'");
    if($sql_webhook=mysql_fetch_array($sql)){
      $Servidor=$sql_webhook['Endpoint'];
      $Token=$sql_webhook['Token'];      
      $newstatedate = $Fecha.'T'.$Hora.'Z';
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://sandbox-api.clicoh.com/api/v1/caddy/webhook/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "new_state": "'.$newstate.'", 
        "new_state_date": "'.$newstatedate.'", 
        "package_code": "'.$codigo.'" 
    }',
      CURLOPT_HTTPHEADER => array(
        'x-clicoh-token: '.$Token.'',
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    // Comprueba el código de estado HTTP
    if (!curl_errno($curl)) {
        switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
          case 200: 
            $Response=200; # OK
            // break;
          default:
          $Response=$http_code;
            // echo 'Unexpected HTTP code: ', $http_code, "\n";
        }
      }
    
    curl_close($curl);
    
    $postfields=$state.' '.$newstatedate.' '.$codigo;
    
    $sql=mysql_query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
        ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
        }
        }
    //end if Cliente Destino
    }
    //END CODIGO PROVEEDOR
  

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
echo "<div><label>Cargo combustible:</label><input type='text' value='' name='carga_t' style='width:50px;' placeholder='litros' ></div>";
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
echo "<div><label>Observaciones:</label><textarea rows='10' cols='55' name='observacionesregreso_t'>".$file[Observaciones]."</textarea></div>";
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
	
    
    //OBTENGO EL VALOR TOTAL DEL RECORRIDO (VALOR DEL RECORRIDO + VALOR DE TODOS LOS SERVICIOS CON EL N DE ORDEN)
    $valor_rec=mysql_query("SELECT Productos.PrecioVenta as PrecioVenta FROM Recorridos 
    INNER JOIN Logistica ON Recorridos.Numero = Logistica.Recorrido 
    INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo 
    WHERE Logistica.NumerodeOrden='$Orden' AND Logistica.Eliminado=0");
    $fila_valor_rec=mysql_fetch_array($valor_rec);    

    $sql_TotalRecorrido=mysql_query("SELECT SUM(Debe)as Total FROM TransClientes WHERE NumerodeOrden='$Orden' AND Eliminado=0");
    $Resultado_TotalRecorrido=mysql_fetch_array($sql_TotalRecorrido);
    $TotalRecorrido=$Resultado_TotalRecorrido['Total']+$fila_valor_rec['PrecioVenta'];
    

	$sql="UPDATE Logistica SET 
	KilometrosRegreso='$KilometrosRegreso',
	ObservacionesCierre='$ObservacionesRegreso',
	Estado='$Estado',
	FechaRetorno='$FechaRetorno',
	HoraRetorno='$HoraRetorno',
	KilometrosRecorridos='$KilometrosRecorridos', 
	CombustibleRegreso='$CombustibleRegreso',Consumo='$Consumo',CargaLitros='$Carga',TotalRecorrido='$TotalRecorrido' 
    WHERE NumerodeOrden='$Orden' LIMIT 1;";

mysql_query($sql);
  
$sql2="UPDATE Vehiculos SET Estado ='Disponible', Kilometros='$KilometrosRegreso', NivelCombustible='$CombustibleRegreso2' WHERE Dominio='$Patente' LIMIT 1";
mysql_query($sql2);
  
//CERRAR ROADMAP DEL RECORRIDO, PASA EL ESTADO DE ABIERTO A CERRADO
$sql3="UPDATE Roadmap SET Estado ='Cerrado' WHERE Recorrido='$Recorrido' AND NumerodeOrden='$Orden' AND Eliminado='0'";
mysql_query($sql3);

//PASO RECORRIDO DE PENDIENTE A ALTA EN CASO QUE EXISTA
$sqlidpendiente=mysql_query("SELECT MIN(id)as id FROM Logistica WHERE Estado='Pendiente' AND Patente='$Patente' AND Eliminado=0");
$idPendiente=mysql_fetch_array($sqlidpendiente);  
$sql3="UPDATE Logistica SET Estado ='Alta', Kilometros='$KilometrosRegreso', CombustibleSalida='$CombustibleRegreso' WHERE id='$idPendiente[id]'";
mysql_query($sql3);

//COMPRUEBO SI EL RECORRIDO TIENE ASOCIADO UN SERVICIO
$sql_activo=mysql_query("SELECT Productos.PrecioVenta as PrecioVenta,Fijo FROM `Recorridos` INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo WHERE Recorridos.Numero='$Recorrido'");
$dato_activo=mysql_fetch_array($sql_activo);

//SI EL SERVICIO TIENE $ LO DEJO ACTIVO EN 1 SINO LO PONGO ACTIVO EN 0 (OSEA INACTIVO)
if($dato_activo['PrecioVenta']>0){
    

}else{

  if($dato_activo['Fijo']=='0'){

    $sql_inactivar=mysql_query("UPDATE Recorridos SET Activo=0 WHERE Recorridos.Numero='$Recorrido' LIMIT 1");  
  
  }

} 

header("location:Logistica.php");			
}
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
    
// ob_end_flush();	
?>	
</div>
</body>
<script src="Proceso/js/funciones_calendar.js"></script>


<script>

// var checkbox = document.getElementsByName('recorrido_t_2');
// checkbox.addEventListener("change", validaCheckbox, false);

// function validaCheckbox()
// {
//   var checked = checkbox.checked;

//   if(checked){
//     alert('checked');
//     document.getElementsByName('recorrido_t_2').disabled=true;
    
//   }else{
//     alert('no checked');
//     document.getElementsByName('recorrido_t_2').disabled=false;
  
//  }
// }
</script>

</center>
</html>