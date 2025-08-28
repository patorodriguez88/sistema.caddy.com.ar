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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!--<script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>-->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../ajax.js"></script>
<link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" />
</head>
<script>
    function ver(){
    document.getElementById('recorrido_t').style.display='block';
    document.getElementById('aceptar').style.display='block';    
//     document.getElementById('actualizar').style.display='none';    
    }
</script>
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
if (($_POST[fecha_t]=='')or($_POST[recorrido_t]=='')){

  echo "<form class='login' action='' method='POST' style='width:600px;'><div>";
  echo "<div><label style='float:center;color:red;font-size:22px'>Seleccionar Recorrido</label></div>";
  echo "<hr></hr>";

  if ($_POST['fecha_t']==''){
  echo "<div id='fecha' ><label>Fecha:</label><input type='date' name='fecha_t' onblur='submit()'></div>";
  }else{  
  echo "<div><label>Fecha:</label><input type='text' name='fecha_t' value='$_POST[fecha_t]'></div>";
  echo "<div id='recorrido_t'><label>Numero de Recorrido:</label><select id='recorrido' name='recorrido_t' >";
  $sqlRecorridos=mysql_query("SELECT Recorridos.Nombre,Recorridos.Numero FROM Recorridos,TransClientes WHERE TransClientes.Recorrido=Recorridos.Numero 
  AND TransClientes.FechaEntrega='$_POST[fecha_t]' GROUP BY Recorridos.Numero");  
  echo "<option value=''>--- Seleccione una opción ---</option>";
  echo "<option value='Todos'>Todos los Recorridos</option>";  
  while($row=mysql_fetch_array($sqlRecorridos)){
  echo "<option value='$row[Numero]'>$row[Nombre] ($row[Numero])</option>";
  }  
  echo "</select></div>";
  echo "<div id='aceptar'><input type='submit' name='Ver' value='Aceptar'></div>";
  }
}
if($_POST[Ver]=='Aceptar'){  
$_SESSION[fechamapa]=$_POST[fecha_t];
$_SESSION[recorridomapa]=$_POST[recorrido_t];
if($_POST[recorrido_t]=='Todos'){
// $sqlBuscoChofer=mysql_query("SELECT NombreChofer,KilometrosRecorridos FROM Logistica WHERE Fecha='$_POST[fecha_t]' AND Recorrido='$_POST[recorrido_t]'");
$NombreChofer='Todos';
$_SESSION[Chofer]='Todos';
$Ordenar1="SELECT * FROM TransClientes WHERE FechaEntrega='$_POST[fecha_t]' AND Eliminado=0";
}else{ 
$sqlBuscoChofer=mysql_query("SELECT NombreChofer,KilometrosRecorridos FROM Logistica WHERE Fecha='$_POST[fecha_t]' AND Recorrido='$_POST[recorrido_t]'");
$NombreChofer=mysql_fetch_array($sqlBuscoChofer);
$_SESSION[Chofer]=$NombreChofer[NombreChofer];
$Ordenar1="SELECT * FROM TransClientes WHERE FechaEntrega='$_POST[fecha_t]' AND Recorrido='$_POST[recorrido_t]' AND Eliminado=0";
}
$Stock=mysql_query($Ordenar1);
$numfilas = mysql_num_rows($Stock);
$_SESSION[numfilas]=$numfilas;  
if($numfilas==0){

  ?>
  <script>
  alertify.error("No existen datos para la consulta");
  </script>
  <?  
  goto a;
}	

$color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';
  

echo "<div style='height:590px;overflow:auto'>";
echo "<table class='login'>";
echo "<caption>Control de Recorrido: $_POST[recorrido_t] Fecha $_POST[fecha_t] </caption>";
echo "<th>Fecha</th>";
echo "<th>id Proveedor</th>";
echo "<th>Cliente Origen</th>";
echo "<th>Cliente Destino</th>";
echo "<th>Seguimiento</th>";
echo "<th>Cantidad</th>";
$sqlAsignaciones=mysql_query("SELECT * FROM Asignaciones WHERE Fecha='$_POST[fecha_t]' GROUP BY CodigoProducto");
  while($datosqlasignaciones=mysql_fetch_array($sqlAsignaciones)){
  $CodigoProducto=$datosqlasignaciones[CodigoProducto];  
  $sqlNombreProducto=mysql_query("SELECT Nombre FROM AsignacionesProductos WHERE CodigoProducto='$datosqlasignaciones[CodigoProducto]'");
    
    while($datosqlNombre=mysql_fetch_array($sqlNombreProducto)){
    echo "<th>$datosqlNombre[Nombre]</th>";          
    }
  }
echo "<th>Entregado</th>";
echo "<th>Hora Programada</th>";
echo "<th>Hora Entrega</th>";
echo "<th>Diferencia</th>";
$e=0;
$ne=0;    
$_SESSION[entregados]=0;
$_SESSION[noentregados]=0;  
  

  
  while($row = mysql_fetch_array($Stock)){
//   $sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Observaciones,Estado,Entregado FROM Seguimiento WHERE CodigoSeguimiento = '$row[CodigoSeguimiento]' 
//   AND id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
//   $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  if($numfilas%2 == 0){
	echo "<tr style='background: #f2f2f2;' >";
	}else{
	echo "<tr style='background:$color1;' >";
	}	
$sqlHojaDeRuta=mysql_query("SELECT Fecha,Hora FROM HojaDeRuta WHERE Seguimiento = '$row[CodigoSeguimiento]'");
$resultHojaDeRuta=mysql_fetch_array($sqlHojaDeRuta);

$sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
    
// $sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Entregado FROM Seguimiento WHERE CodigoSeguimiento = '$row[CodigoSeguimiento]' 
// AND id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
$resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
    
$sqlNCliente=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente = '$row[ClienteDestino]'");
$DatoNCliente=mysql_fetch_array($sqlNCliente);
    
    
if($resultSeguimiento[Entregado]==1){
$e=$e+1;  
$Entregado='Si';  
$HoraEntrega=$resultSeguimiento[Hora];
}else{
$ne=$ne+1;  
$Entregado='No';    
$HoraEntrega='';
}
    
// echo "<td>$row[FechaEntrega]</td>";

echo "<td>$row[NumeroComprobante]</td>";
echo "<td>$DatoNCliente[idProveedor]</td>";
echo "<td>$row[RazonSocial]</td>";
echo "<td>$row[ClienteDestino]</td>";
echo "<td>$row[CodigoSeguimiento]</td>";
echo "<td>$row[Cantidad]</td>";
$sqlAsignaciones=mysql_query("SELECT CodigoProducto FROM Asignaciones WHERE Fecha='$_POST[fecha_t]' GROUP BY CodigoProducto");
  while($dato1=mysql_fetch_array($sqlAsignaciones)){
        $sqlAsignaciones=mysql_query("SELECT * FROM Asignaciones WHERE Fecha='$_POST[fecha_t]' AND 
        idProveedor='$DatoNCliente[idProveedor]' AND CodigoProducto='$dato1[CodigoProducto]'");
        echo "<td>";
        while($datosqlAsignaciones=mysql_fetch_array($sqlAsignaciones)){
            echo $datosqlAsignaciones[Cantidad];  
        }
        echo "</td>";
    }
// echo "<td>$Entregado</td>";
// echo "<td>$resultHojaDeRuta[Hora]</td>";

if($resultHojaDeRuta[Fecha]<>$resultSeguimiento[Fecha]){
$color3=red;  
}else{
$color3='';
}
 echo "<td style='color:$color3'>$HoraEntrega</td>";
$HoraSeguimiento= strtotime($resultSeguimiento[Hora]);    
$HoraProgramada=strtotime($resultHojaDeRuta[Hora]);    
$resta = $HoraSeguimiento-$HoraProgramada;
		echo "<td></td>";	    
echo "<input type='hidden' id='waypoints' value='$row[ClienteDestino]'>";	
    $numfilas++; 	

  }
$_SESSION[entregados]=$e;
$_SESSION[noentregados]=$ne;
  
echo "</table>";
  echo "</div>";  
include('mapa.html');  
  goto a;
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