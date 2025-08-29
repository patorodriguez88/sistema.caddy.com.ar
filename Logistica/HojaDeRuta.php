<?php
ob_start();

session_start();
include_once "../Conexion/Conexioni.php";

// include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:https://www.caddy.com.ar");
}
$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';

function geolocalizar($direccion){
    // urlencode codifica datos de texto modificando simbolos como acentos
    $direccion = urlencode($direccion);
    // envio la consulta a Google map api
    $url= "https://maps.google.com/maps/api/geocode/json?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&address={$direccion}";
    // recibo la respuesta en formato Json
    $datosjson = file_get_contents($url);
    // decodificamos los datos Json
    $datosmapa = json_decode($datosjson, true);
    // si recibimos estado o status igual a OK, es porque se encontro la direccion
    if($datosmapa['status']==='OK'){
        // asignamos los datos
        $latitud = $datosmapa['results'][0]['geometry']['location']['lat'];
        $longitud = $datosmapa['results'][0]['geometry']['location']['lng'];
        $localizacion = $datosmapa['results'][0]['formatted_address'];
           // Guardamos los datos en una matriz
            $datosmapa = array();           
                array_push(
                $datosmapa,
                $latitud,
                $longitud,
                $localizacion
                );
            return $datosmapa;
        }
} 

?>
<head>

<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />

  </head>
  <script>
//DIRECCION 
  function initMap(){
        var inputstart = document.getElementById('start');
        var end = document.getElementById('end');
    
        var retiro= document.getElementById('retirado').value;
        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer();

        var centro = new google.maps.LatLng(-31.407639, -64.191600);
        var mapOptions = {
          zoom:9,
          center:centro, 
        }
        var map = new google.maps.Map(document.getElementById('map'), mapOptions);
        
        directionsRenderer.setMap(map);
        directionsRenderer.setPanel(document.getElementById('directionsPanel'));
        if(retiro==0){
        calculateAndDisplayRoute(directionsService, directionsRenderer);  
        }else{
        document.getElementById('map').style.height='100%';
        var address = document.getElementById('end').value;
        var geocoder = new google.maps.Geocoder;  
        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == 'OK') {
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
          } else {
//             alert('Geocode was not successful for the following reason: ' + status);
          }
        });
        }
    
        var autocomplete = new google.maps.places.Autocomplete(inputstart, { types: ['geocode','establishment'], componentRestrictions: {country: ['AR']} });
        autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (place.address_components) {
        var components= place.address_components;
        var ciudad='';
        var provincia='';  
          for (var i = 0, component; component = components[i]; i++) {
            console.log(component);
            if (component.types[0] == 'administrative_area_level_1') {
               provincia=component['long_name'];
              if(provincia!='Córdoba'){
              alertify.error('La Provincia de origen debe ser Córdoba '+ ' no ' + provincia);          
              document.getElementById('start').value = '';
              document.getElementById('start').focus();
              break;  
              }
            }else if (component.types[0] == 'locality') {
              ciudad = component['long_name'];
//               realizaProceso(ciudad);
//               if(document.getElementById('resultado').innerText==0){
//               alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
//               document.getElementById('start').value = '';
//               document.getElementById('start').focus();
//               break;
//               } 
//               document.getElementById('Provincia_t').value = provincia;
//               document.getElementById('Ciudad_t').value = ciudad;
//             }
//             }else if(component.types[0] == 'administrative_area_level_3'){
//              document.getElementById('Barrio_t').value= component['long_name'];   
            }else if(component.types[0] == 'postal_code'){
             document.getElementById('Codigo_Postal_t').value= component['short_name'];   
           }else if(component.types[0] == 'street_number'){
             document.getElementById('numero_t').value= component['long_name'];   
           }else if(component.types[0] == 'route'){
             document.getElementById('calle_t').value= component['long_name'];   
           }
          }
        }
          
        if(retiro==0){
        calculateAndDisplayRoute(directionsService, directionsRenderer);  
        }else{
        document.getElementById('map').style.height='100%';
  
        var address = document.getElementById('end').value;
        var geocoder = new google.maps.Geocoder;  
        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == 'OK') {
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
          } else {
//             alert('Geocode was not successful for the following reason: ' + status);
          }
        });
        }
  
        }); 
        var autocomplete = new google.maps.places.Autocomplete(end, { types: ['geocode','establishment'], componentRestrictions: {country: ['AR']} });
        autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (place.address_components) {
        var components= place.address_components;
        var ciudad='';
        var provincia='';  
          for (var i = 0, component; component = components[i]; i++) {
            console.log(component);
            if (component.types[0] == 'administrative_area_level_1') {
               provincia=component['long_name'];
              if(provincia!='Córdoba'){
              alertify.error('La Provincia de origen debe ser Córdoba '+ ' no ' + provincia);          
              document.getElementById('end').value = '';
              document.getElementById('end').focus();
              break;  
              }
            }else if (component.types[0] == 'locality') {
              ciudad = component['long_name'];
//               realizaProceso(ciudad);
//               if(document.getElementById('resultado').innerText==0){
//               alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
//               document.getElementById('start').value = '';
//               document.getElementById('start').focus();
//               break;
//               } 
//               document.getElementById('Provincia_t').value = provincia;
              document.getElementById('Ciudadorigen_t').value = ciudad;
//             }
//             }else if(component.types[0] == 'administrative_area_level_3'){
//              document.getElementById('Barrio_t').value= component['long_name'];   
            }else if(component.types[0] == 'postal_code'){
             document.getElementById('Codigo_Postalorigen_t').value= component['short_name'];   
           }else if(component.types[0] == 'street_number'){
             document.getElementById('numeroorigen_t').value= component['long_name'];   
           }else if(component.types[0] == 'route'){
             document.getElementById('calleorigen_t').value= component['long_name'];   
           }
          }
        }
          
      if(retiro==0){
        calculateAndDisplayRoute(directionsService, directionsRenderer);  
        }else{
        document.getElementById('map').style.height='100%';
        var address = document.getElementById('end').value;
        var geocoder = new google.maps.Geocoder;  
        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == 'OK') {
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
          } else {
//             alert('Geocode was not successful for the following reason: ' + status);
          }
        });
        }

        }); 
    }
    
function calculateAndDisplayRoute(directionsService, directionsRenderer) {
        var start = document.getElementById('start').value;
        var end = document.getElementById('end').value;
        var request = {
          origin:start,
          destination:end,
          travelMode: 'DRIVING'
        };
        directionsService.route(request, function(response, status) {
          if (status == 'OK') {
            directionsRenderer.setDirections(response);
          }
        });
      }
    
</script>  
<script>
function modificar(){
var n1 = parseFloat(document.MyForm.recorrido_t.value); 
// document.MyForm.orden_t.value=n1;	
}
</script>
  
<?
  if($_GET[Ordenar]=='si'){
  echo "<script>alerta();</script>";
}

echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
  
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
// include("../Menu/MenuGestionLateral.php"); 
include("Menu/MenuLateralHojaDeRuta.php"); 
echo "</div>"; //lateral
echo  "<div id='principal'>";

  
if($_GET[Eliminarhdr]=='Si'){
echo "<form class='login' action='' method='GET' style='width:500px'>";
echo "<div><titulo>Confirma la eliminacion de la Hoja de Ruta del Recorrido $_GET[Recorrido] ?</titulo></div>";
echo "<div><hr/></div>";
echo "<input type='hidden' name='Recorrido' value='$_GET[Recorrido]'>";
echo "<div><input type='submit' name='Eliminarhdr' value='Aceptar'></div>";
echo "<div><input type='submit' name='Eliminarhdr' value='Cancelar'></div>";
echo "</form>";	
goto a;	
}
if($_GET[Eliminarhdr]=='Aceptar'){
  $sqlbuscocodigo=$mysqli->query("SELECT Seguimiento FROM HojaDeRuta WHERE Recorrido='$_GET[Recorrido]' AND Estado='Abierto' AND Eliminado=0 ");

  while($datosql=$sqlbuscocodigo->fetch_array(MYSQLI_ASSOC)){
  $CodigoSeguimientoE=$datosql[Seguimiento];  
  //ELIMINAR DE TRANS CLIENTES
  $sql2=$mysqli->query("UPDATE TransClientes SET Eliminado='1' WHERE CodigoSeguimiento='$CodigoSeguimientoE'"); 
  //ELIMINAR DE VENTAS
  $sql3=$mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$CodigoSeguimientoE'");  
 
  $BuscarTrans=$mysqli->query("SELECT Debe FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimientoE'");
  $DatoBuscarTrans=$BuscarTrans->fetch_array(MYSQLI_ASSOC);
  $DatoBuscarTransFinal=$DatoBuscarTrans[Debe];
  $Buscar=$mysqli->query("SELECT Debe FROM Ctasctes WHERE NumeroVenta='$NumeroVenta'");
  $Dato=$Buscar->fetch_array(MYSQLI_ASSOC);
  $DatoBuscar=$Dato[Debe];
  $Saldo=$DatoBuscar-$DatoBuscarTransFinal;  
  //ACTUALIZO ELSALDO EN CTAS CTES SI EL MISMO TENIA SALDO Y HAY ALGO QUE DESCONTAR
  $sql4=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE NumeroVenta='$NumeroVenta' LIMIT 1");
  //ELIMINAR SEGUIMIENTO
  $sqleliminarseguimiento=$mysqli->query("DELETE FROM `Seguimiento` WHERE CodigoSeguimiento='$CodigoSeguimientoE'");

    //ELIMINAR HOJA DE RUTA
  $sqleliminar=$mysqli->query("UPDATE HojaDeRuta SET Eliminado=1 WHERE Recorrido='$_GET[Recorrido]' AND Estado='Abierto'");
 
  }
    ?><script>alertify.success("Recorrido <? echo $_GET[Recorrido];?> eliminado");</script><?
    ?><script>alertify.success("Saldo actualizado en Ctas Ctes");</script><?
    ?><script>alertify.success("Recorrido <? echo $_GET[Recorrido];?> eliminado de Trans Clientes");</script><?
    ?><script>alertify.success("Recorrido <? echo $_GET[Recorrido];?> eliminado de Ventas");</script><?
    ?><script>alertify.success("Recorrido <? echo $_GET[Recorrido];?> eliminado de Hoja De Ruta");</script><?
    ?><script>alertify.success("Recorrido <? echo $_GET[Recorrido];?> eliminado de Seguimiento");</script><?
  }elseif($_GET[Eliminarhdr]=='Cancelar'){
    ?><script>alertify.error("Recorrido <? echo $_GET[Recorrido];?> no eliminado");</script><?
}  
  
  //AGREGAR ASIGNACION
if($_GET['fecha']=="Actualizar"){

  $sql="UPDATE Asignaciones SET Fecha='$_GET[fecha_t]',Relacion='$_GET[relacion]' WHERE CodigoProducto='$_GET[CodigoProducto]' AND Edicion='$_GET[Edicion]' AND Fecha IS NULL";
if($mysqli->query($sql)){
?>
<script>
 alertify.success("Fecha Actualizada en Asignaciones"); 
  </script>  
<?  
}else{
?>
  <script>
 alertify.error("La Fecha no se actualizo en Asignaciones"); 
  </script>  
<?  
}
  goto a;  
}  
if($_GET['id']=='AsignacionAgregar'){	

    $Recorrido=$_POST['recorrido_t'];	

    $_SESSION['Recorrido']=$Recorrido;	

    print_r($Recorrido);

if($_GET['Resultado']=='Fechas'){

    $Ordenar="SELECT *,SUM(Cantidad)as Cantidad,COUNT(*)AS CantidadClientes FROM Asignaciones WHERE Fecha>='$_GET[desde_t]' AND Fecha<='$_GET[hasta_t]' GROUP BY Fecha,CodigoProducto,Edicion";

}else{

    $Ordenar="SELECT *,SUM(Cantidad)as Cantidad,COUNT(*)AS CantidadClientes FROM Asignaciones WHERE Fecha IS NULL GROUP BY Fecha,CodigoProducto,Edicion";
}  

$Consulta=$mysqli->query($Ordenar);

echo "<table class='login'>";
echo "<caption>Agregar fechas en asignaciones</caption>";  
echo "<th>Cliente</th>";
echo "<th>Codigo</th>";
echo "<th>Titulo</th>";  
echo "<th>Edicion</th>";    
echo "<th>Cantidad Puntos</th>";  
echo "<th>Cantidad Productos</th>";  
echo "<th>Fecha</th>";
echo "<th>Imprimir</th>";
echo "<th>Accion</th>";

  while($row=$Consulta->fetch_array(MYSQLI_ASSOC)){
  echo "<tr>";
  $sqltitulo=$mysqli->query("SELECT Nombre,Relacion FROM AsignacionesProductos WHERE CodigoProducto='$row[CodigoProducto]'");
  $datotitulo=$sqltitulo->fetch_array(MYSQLI_ASSOC);
  $sqlrelacion=$mysqli->query("SELECT nombrecliente FROM Clientes WHERE id='$datotitulo[Relacion]'");
  $datorelacion=$sqlrelacion->fetch_array(MYSQLI_ASSOC);
  echo "<td>$datorelacion[nombrecliente]</td>";        
  echo "<td>$row[CodigoProducto]</td>";
  echo "<td>$datotitulo[Nombre]</td>"; 
  echo "<td>$row[Edicion]</td>"; 
  echo "<td>$row[CantidadClientes]</td>"; 
  echo "<td>$row[Cantidad]</td>";    
  echo "<form action='' method='GET'>";
  echo "<input name='relacion' type='hidden' value='$datotitulo[Relacion]'>";  
  echo "<input id='codigo' name='CodigoProducto' type='hidden' value='$row[CodigoProducto]'>";  
  echo "<input id='edicion' name='Edicion' type='hidden' value='$row[Edicion]'>";  
  echo "<td><input id='fecha' name='fecha_t' type='date' value='$row[Fecha]'></td>";   
  echo "<td align='center'><a target='_blank' class='img' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/Asignaciones.php?Relacion=$datotitulo[Relacion]&Fecha=$row[Fecha]&CodigoProducto=$row[CodigoProducto]'><img src='../images/botones/document.png' width='15' height='15' border='0' ></a></td>";
  echo "<td><input type='submit' name='fecha' value='Actualizar'></td>";  
  echo "</form>";  
  echo "</tr>";
}
echo "</table>";  
goto a;
}
if($_GET['id']=='BuscarAsignacion'){	
    echo "<form class='login' action='' method='GET' style='width:500px' >";
    echo "<div><titulo>Buscar Asignaciones x Fecha</titulo></div>";
    echo "<div><hr></hr></div>";
    echo "<input type='hidden' name='Resultado' value='Fechas'>";
    echo "<input type='hidden' name='id' value='AsignacionAgregar'>";
    echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><input name='Buscar' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</form>";	
    goto a;
}  
        if($_GET['sms']=='Enviar'){
          $Recorrido=$_SESSION['Recorrido'];  
        //DESDE ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA 
        $sql=$mysqli->query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Hora>'1' AND Celular<>'' AND Eliminado='0' AND Avisado='0'");
  
        echo "<form class='login' action='HojaDeRuta.php?id=Buscar&recorrido_t=$Recorrido' >";
        echo "<div><table class='login'>";
        echo "<caption>Mensajes Enviados</caption>";
        echo "<th>Usuario</th><th>Estado</th><th>Respuesta</th>";
          while($file=$sql->fetch_array(MYSQLI_ASSOC)){
              if($file[ImporteCobranza]<>0){
              $Cobranza=' El importe a abonar es de $ '.$file[ImporteCobranza];  
              }else{
              $Cobranza='';
              }
        $sqlBuscoCliente=$mysqli->query("SELECT RazonSocial FROM TransClientes WHERE CodigoSeguimiento='$file[Seguimiento]'");
        $sqlRespuesta=$sqlBuscoCliente->fetch_array(MYSQLI_ASSOC);  
        $smsusuario = "SMSDEMO45010"; //usuario de SMS MASIVOS
        $smsclave = "logistica"; //clave de SMS MASIVOS
        $smsnumero = $file[Celular]; //coloca en esta variable el numero (pueden ser varios separados por coma)
        $smstexto= $file[Cliente]. " , Caddy le informa que su pedido de ".$sqlRespuesta[RazonSocial]." sera entregado hoy cerca de las ".$file[Hora]." (+/- 30 m). ".$Cobranza;
       //texto del mensaje (hasta 160 caracteres)
        $smsrespuesta = file_get_contents("https://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );
        //HASTA ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA  
        
        echo "<tr><td>$file[Cliente]</td>";
        $respuesta=utf8_decode($smsrespuesta);
            if(($smsrespuesta)=="OK"){
            $mysqli->query("UPDATE HojaDeRuta SET Avisado='1' WHERE id='$file[id]'");  
            echo "<td>SMS Enviado</td>";  
            echo "<td>Mensaje enviado con exito!</td>";  
            }else{
            echo "<td>SMS No Enviado</td>";  
            echo "<td>$respuesta</td>";  
            } 
        echo "</td></tr>";
        } 
        echo "</div>";  
        echo "<div><input type='submit' value='Aceptar'></div>";
        echo "</form>";
// header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);	
        }
  
  if($_GET['id']=='EnviarSms'){
  $Recorrido=$_SESSION['Recorrido'];  
  $sql=$mysqli->query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Hora>'1' AND Celular<>'' AND Eliminado='0' AND Avisado='0'");
    echo "<form class='login' action='' method='get' style='width:600px'><div>";
    echo "<div><titulo>Enviar Mensaje SMS al Recorrido $Recorrido</titulo></div>";
    echo "<div><hr></hr></div>";
  
    if($sql->num_rows=='0'){
    echo "No existe ningun horario cargado ni celular asignado para el recorrido seleccionado";
      goto a;
  } 
    
    while($file=$sql->fetch_array(MYSQLI_ASSOC)){
//    $Hora=DateTime($file[Hora]);
   $Hora= date($file[Hora]);   
      
    echo "<div><label>Cliente:</label><input name='Cliente_t' type='text' value='$file[Cliente]' style='width:300px;' readonly/></div>";
    echo "<div><label>Celular:</label><input name='Celular_t' type='text' value='$file[Celular]' style='width:300px;' readonly/></div>";
    echo "<div><label>Hora:</label><input name='Hora_t' type='text' value='$Hora' style='width:300px;' readonly/></div>";
    echo "<div><label>Importe a Cobrar:</label><input name='importe_t' type='text' value='$file[ImporteCobranza]' style='width:300px;' readonly/></div>";
      if($file[ImporteCobranza]<>0){
    $Cobranza=' El importe a abonar es de $ '.$file[ImporteCobranza];  
    }else{
    $Cobranza='';
    }
//       echo "<div><label>Mensaje:</label><textarea maxlength='100' name='Mensaje_t' rows='5' cols='80'>".$file[Cliente].", le informamos que su pedido sera entregado hoy cerca de las ".$file[Hora]." (+/- 30 m), le infomaremos cuando sea el proximo en la lista. $Cobranza</textarea></div>";
    echo "<div><hr></hr></div>";
    }
    echo "<div><input class='submit' name='sms' type='submit' value='Enviar'></div>";
    echo "</div></form>";	
    
  } 
  
  
if($_GET['VerMapa']=='Si'){	
?>
<script>
window.open("MapaGoogle.php","_blank");
location.href= "HojaDeRuta.php";
</script>';  
<?
//   header("location:MapaGoogle.php");
}
	
if($_GET['id']=='BuscarAnterior'){	

  if($_GET['desde_t']==''){
// 		$Grupo="SELECT * FROM Recorridos";
// 		$estructura= $mysqli->query($Grupo);
//     echo "<form class='login' action='' method='POST' style='width:500px'>";
// 		echo "<div><titulo>Seleccionar Recorrido</titulo></div>";
//     echo "<div><hr></hr></div>";

    echo "<form class='Caddy' action='' method='GET' style='width:500px'>";
    echo "<div><titulo>Buscar Hoja de Ruta x Fecha</titulo></div>";
//     echo "<div><hr></hr></div>";
    echo "<input type='hidden' name='id' value='BuscarAnterior'>";
    echo "<div><label>Desde</label><input name='desde_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><label>Hasta</label><input name='hasta_t' size='16' type='date' value='' style='float:right' required/></div>";
    echo "<div><input name='BuscarHdeRuta' class='bottom' type='submit' value='Aceptar' ></div>";
    echo "</form>";

    
//     echo "<div><label>Recorrido:</label><select name='recorrido_t' style='float:right;width:310px;' size='0'>";
// 		while ($row = mysql_fetch_row($estructura)){
// 		echo "<option value='".$row[1]."'>".$row[2]."</option>";
// 		}
// 		echo "</select></div>";
// 		echo "<div><input class='submit' name='hojaderuta_t' type='submit' value='BuscarAnterior'></div></table>";
	}else{
	
// if($_POST['hojaderuta_t']=='BuscarAnterior'){	
$Desde=$_GET[desde_t];
$Hasta=$_GET[hasta_t]; 
  
// $Recorrido=$_POST['recorrido_t'];	
// $_SESSION['Recorrido']=$Recorrido;	
$Ordenar="SELECT * FROM Logistica WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Estado='Cerrada' AND Eliminado='0'";
$Consulta=$mysqli->query($Ordenar);
  
echo "<table class='login'>";
// echo "<tr align='center' style='background:$color; color:$font; font-size:8px;'>";
echo "<caption>Listado Hojas de Ruta Cerradas</caption>";
echo "<th>Orden Nº</th>";
echo "<th>Fecha</th>";
echo "<th>Hora</th>";
echo "<th>Vehiculo</th>";
echo "<th>Chofer</th>";
echo "<th>Recorrido</th>";    
echo "<th>Servicios</th>";
echo "<th>Kilometros</th>";
echo "<th>Imprimir</th>";

	$numfilas =0;
	while($file = $Consulta->fetch_array(MYSQLI_ASSOC)){
//   funcion();  
	if($numfilas%2 == 0){
	echo "<tr style='background:$color1;font-size:12px' >";
	}else{
	echo "<tr style='background:$color2;font-size:12px' >";
	}	 
		$Fecha=explode("-",$file[Fecha],3);
		$Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
    $sqlrecorrido=$mysqli->query("SELECT * FROM Recorridos WHERE Numero='$file[Recorrido]'");
    $datorecorrido=$sqlrecorrido->fetch_array(MYSQLI_ASSOC);
    $sqlvehiculo=$mysqli->query("SELECT * FROM Vehiculos WHERE Dominio='$file[Patente]'");
    $datovehiculo=$sqlvehiculo->fetch_array(MYSQLI_ASSOC);
    $sqlservicios=$mysqli->query("SELECT Count(id)as Total FROM HojaDeRuta WHERE NumerodeOrden='$file[NumerodeOrden]'");
    $datoservicios=$sqlservicios->fetch_array(MYSQLI_ASSOC);
    
    if($file[NombreChofer2]==''){
    $Choferes=$file[NombreChofer];
    }else{
    $Choferes=$file[NombreChofer] | $file[NombreChofer2];  
    }
		echo "<td>$file[NumerodeOrden]</td>";
		echo "<td>$Fecha1</td>";
		echo "<td>$file[Hora]</td>";
    echo "<td>$datovehiculo[Dominio] | $datovehiculo[Marca] $datovehiculo[Modelo]</td>";
		echo "<td style='text-transform:capitalize'>$Choferes</td>";
		echo "<td>[$datorecorrido[Numero]] $datorecorrido[Nombre]</td>";
		echo "<td>$datoservicios[Total]</td>";
		echo "<td>$file[KilometrosRecorridos]</td>";
// 		echo "<td>$file[Asignado]</td>";
// 		echo "<td align='center'><a></a></td>";
		echo "<td align='center'><a target='_blank' class='img' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/HojaDeRutaCerradapdf.php?ON=$file[NumerodeOrden]'><img src='../images/botones/mas.png' width='15' height='15' border='0' ></a></td>";
		$numfilas++; 
		}
// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
// echo "</div>";  
goto a;
	}
}	
//---------------------------------DESDE ACA MODIFICAR---------------------------------	
if ($_GET['id']=='Modificar'){
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';
$Recorrido=$_GET['recorrido_t'];
$Row=$_GET['row'];
  
$idOrden= $mysqli->query("SELECT * FROM HojaDeRuta WHERE id='$Row'");
$Datos=$idOrden->fetch_array(MYSQLI_ASSOC);
$Fecha=$Datos[Fecha];
$arrayfecha=explode('-',$Fecha,3);
$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
$sqlBuscoOrigen=$mysqli->query("SELECT RazonSocial,DomicilioOrigen,Retirado,IngBrutosOrigen FROM TransClientes WHERE CodigoSeguimiento='$Datos[Seguimiento]'");
$NombreOrigen=$sqlBuscoOrigen->fetch_array(MYSQLI_ASSOC);

  if($NombreOrigen['Retirado']==0){
    
    $Servicio='Retiro y Entrega';  
    
    }else{
    
    
    $Servicio='Solo Entrega';    
  }
  
echo "<form name='MyForm' class='Caddy' action='' method='get' style='width:50%;float:left'>";
echo "<input type='hidden' value='$NombreOrigen[Retirado]' id='retirado'/>";
echo "<input type='hidden' value='$Datos[id]' name='id_t' />";
echo "<input type='hidden' name='seguimiento_t' value='$Datos[Seguimiento]'>";  
echo "<div><titulo>Modificar ítem de hoja de ruta | $Servicio</titulo></div>";
echo "<hr></hr>";  
echo "<div><label>N. de orden heredada:</label><input name='ndeorden_t' type='text' value='$Datos[NumerodeOrden]' style='width:200px;'/></div>";
echo "<div><label>Fecha:</label><input name='' type='text' value='$Datos[Fecha]' style='width:200px;'readonly/></div>";
echo "<input name='fecha_t' type='hidden' value='$Fecha'/>";
echo "<div><label>Hora:</label><input name='hora_t' type='time' value='$Datos[Hora]' style='width:200px;'/></div>";
echo "<div><label>Importe a Cobrar:</label><input name='importecobranza_t' type='text' value='$Datos[ImporteCobranza]' style='width:200px;'/></div>";  
echo "<div><label>Celular:</label><input name='celular_t' type='number' value='$Datos[Celular]' style='width:400px;'/></div>";
echo "<div><label>Recorrido:</label><select name='recorrido_t' onblur='modificar()' style='width:400px;' />";
echo "<option value='".$_GET['recorrido_t']."'>".$_GET['recorrido_t']."</option>";
$sql=$mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Numero<>".$_GET['recorrido_t']);

while($row=$sql->fetch_array(MYSQLI_ASSOC)){
echo "<option value='$row[Numero]'>(".$row[Numero].")".$row[Nombre]."</option>";
}  
echo "</select></div>";

if($NombreOrigen['Retirado']==0){

echo "<div><label>Posicion de Retiro:</label><input name='orden_retiro_t' type='text' value='".$Datos['Posicion_retiro']."' style='width:400px;'/></div>";

}

echo "<div><label>Posicion de Entrega:</label><input name='orden_t' type='text' value='".$Datos['Posicion']."' style='width:400px;'/></div>";    
echo "<input name='idcliente_t' type='hidden' value='".$Datos[idCliente]."'>";
  
echo "<div><label>Cliente Origen:</label><input name='clienteorigen_t' type='text' value='$NombreOrigen[RazonSocial]' style='width:400px;' readonly/></div>";
echo "<div><label>Direccion Retiro:</label><input name='localizacionorigen_t' id='start' class='form-control' type='text' value='$NombreOrigen[DomicilioOrigen]' style='width:400px;'/></div>";
echo "<input name='idclienteorigen_t' type='hidden' value='$NombreOrigen[IngBrutosOrigen]' />";
echo "<input name='calleorigen_t' id='calleorigen_t'  type='hidden' />";
echo "<input name='numeroorigen_t' id='numeroorigen_t' type='hidden' />";
echo "<input name='Codigo_Postalorigen_t' id='Codigo_Postalorigen_t' type='hidden' />";
echo "<input name='Ciudadorigen_t' id='Ciudadorigen_t' type='hidden' />";

echo "<div><label>Cliente Destino:</label><input name='cliente_t' type='text' value='$Datos[Cliente]' style='width:400px;' readonly/></div>";
echo "<div><label>Direccion Entrega:</label><input name='localizacion_t' id='end' class='form-control' type='text' value='$Datos[Localizacion]' style='width:400px;'/></div>";
echo "<input name='calle_t' id='calle_t'  type='hidden' />";
echo "<input name='numero_t' id='numero_t' type='hidden' />";
echo "<input name='Codigo_Postal_t' id='Codigo_Postal_t' type='hidden' />";
echo "<div><label>Titulo:</label><input name='titulo_t' type='text' value='$Datos[Titulo]' style='width:400px;'/></div>";
echo "<div><label>Ciudad:</label><input name='ciudad_t' type='text' value='$Datos[Ciudad]' style='width:400px;'/></div>";
echo "<div><label>Provincia:</label><input name='provincia_t' type='text' value='$Datos[Provincia]' style='width:400px;'/></div>";
echo "<div><label>Observaciones:</label><textarea rows='5' cols='55' name='observaciones_t'/>$Datos[Observaciones]</textarea></div>";
echo "<div><label>Usuario:</label><input name='usuario_t' type='text' value='".$_SESSION['Usuario']."' style='width:150px;'/></div>";
echo "<div><label>Asignado:</label><input name='asignado_t' type='text' value='$Datos[Asignado]' style='width:150px;' required/></div>";
echo "<div><input class='submit' name='item' type='submit' value='Modificar'></div></table>";
echo "</form>";
  
echo "<div id='map' style='float:left;width:45%;height:30%'></div>";
echo "<div id='directionsPanel' style='float:left;width:45%;height:30%'></div>";  
  
goto a;
}

  
if($_GET['item']=='Modificar'){
$Recorrido=$_GET['recorrido_t'];
$Hora=$_GET['hora_t'];
$Seguimiento=$_GET[seguimiento_t];  
//   SI MODIFICO EL RECORRIDO
  
  if($Recorrido<>$_SESSION['Recorrido']){ 
  $sql=$mysqli->query("SELECT MAX(Posicion)as Posicion FROM HojaDeRuta WHERE Recorrido='".$_GET['recorrido_t']."' AND Estado='Abierto' AND Devuelto='0' AND Eliminado=0 AND Seguimiento<>''");  
  $Dato=$sql->fetch_array(MYSQLI_ASSOC);	
  $Orden = trim($Dato[Posicion])+1;

  // MODIFICO EL RECORRIDO EN TRANSCLIENTES  
  $sqlLogistica=$mysqli->query("SELECT NumerodeOrden,NombreChofer FROM Logistica WHERE Estado IN('Alta','Cargada') AND Recorrido='$Recorrido'");
  $DatoLogistica=$sqlLogistica->fetch_array(MYSQLI_ASSOC);
  
  if($DatoLogistica['NumerodeOrden']<>''){
   $NumerodeOrden=$DatoLogistica['NumerodeOrden']; 
   $Transportista=$DatoLogistica['NombreChofer'];
  }else{
   $NumerodeOrden=0;
   $Transportista='';
  }
  
  $sqlTransClientes=$mysqli->query("UPDATE TransClientes SET Recorrido='$Recorrido',NumerodeOrden='$NumerodeOrden',
  Transportista='$Transportista' WHERE CodigoSeguimiento='$Seguimiento' AND Eliminado='0'");

  
  }else{
  $Orden=$_GET['orden_t'];  
}

$Posicion_retiro=$_GET['orden_retiro_t'];

$Direccion=addslashes($_GET['localizacion_t']);
$Direccion=$_GET['localizacion_t'];  
$Calle=addslashes($_GET['calle_t']);
$Numero=$_GET['numero_t'];
$Cp=$_GET['Codigo_Postal_t'];  
$Ciudad=$_GET['ciudad_t'];
$Provincia=$_GET['provincia_t'];
$Observaciones=$_GET['observaciones_t'];
$id=$_GET['id_t'];
$idCliente=$_GET['idcliente_t'];  
$Celular=$_GET['celular_t'];
$ImporteCobranza=$_GET[importecobranza_t];

$datosmapa = geolocalizar($Direccion);
$latitud = $datosmapa[0];
$longitud = $datosmapa[1];
$localizacion = $datosmapa[2];  

  
$sql=$mysqli->query("UPDATE HojaDeRuta SET Recorrido ='$Recorrido',Localizacion='$Direccion',NumerodeOrden='$NumerodeOrden',
Ciudad='$Ciudad',Provincia='$Provincia',Observaciones='$Observaciones',Posicion='$Orden',Hora='$Hora',Celular='$Celular',ImporteCobranza='$ImporteCobranza',Posicion_retiro='$Posicion_retiro' WHERE id='$id'");
//ACTUALIZO LOS DATOS DEL CLIENTE DESTINO  
$sqlClientes=$mysqli->query("UPDATE Clientes SET Latitud='$latitud',Longitud='$longitud',Calle='$Calle',Numero='$Numero',CodigoPostal='$Cp',Direccion='$Direccion',Ciudad='$Ciudad',Provincia='$Provincia',
Orden='$Orden',Celular='$Celular' WHERE id='$idCliente'"); 

//ACTUALIZO LOS DATOS DEL CLIENTE ORIGEN  
$idClienteOrigen= $_GET[idclienteorigen_t];  
$CalleOrigen=addslashes($_GET[calleorigen_t]);
$NumeroOrigen=$_GET[numeroorigen_t];
$CodigoPostalOrigen=$_GET[Codigo_Postalorigen_t];
$DireccionOrigen=addslashes($_GET['localizacionorigen_t']); 
$CiudadOrigen=$_GET[Ciudadorigen_t];
$ProvinciaOrigen='Córdoba';  
$sqlClientes=$mysqli->query("UPDATE Clientes SET Calle='$CalleOrigen',Numero='$NumeroOrigen',CodigoPostal='$CodigoPostalOrigen',Direccion='$DireccionOrigen',Ciudad='$CiudadOrigen',Provincia='$ProvinciaOrigen'
WHERE id='$idClienteOrigen'"); 
  
//ACTUALIZO TRANSCLIENTES
$sqltransclientes=$mysqli->query("UPDATE TransClientes SET DomicilioOrigen='$DireccionOrigen',DomicilioDestino='$Direccion',LocalidadDestino='$Ciudad' WHERE CodigoSeguimiento='$Seguimiento'");  
// header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);	  
header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$_SESSION['Recorrido']);

}
  
//------------------------------HASTA ACA MODIFICAR-------------------------------------	
//-----------------------------------------------DESDE ACA VER ORDENES---------------------------
if ($_GET['id']=='Ver'){
$Filtro=$_GET['Filtro'];
if($Filtro==''){
$Ordenar1="SELECT * FROM Logistica";
}else{		
$Ordenar1="SELECT * FROM HojaDeRuta WHERE Estado='$Filtro'";
}

$Empleados=$mysqli->query($Ordenar1);
		
echo "<table align='left' border='0' width='75%' style='margin-left:5px'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:8px;'>";
echo "<td colspan='11' style='font-size:22px'>Listado de Ordenes </td></tr>";
echo "<tr align='left' style='background:$color;font-style:Open Sans, sans-serif; color:$font; font-size:12px;'>";
echo "<td>Orden Nº</td>";
echo "<td>Fecha</td>";
echo "<td>Hora</td>";
echo "<td>Patente</td>";
echo "<td>Nombre Chofer</td>";
echo "<td>Acompañante</td>";
echo "<td>Recorrido</td>";
echo "<td>Estado</td>";
echo "<td>Combustible salida</td>";
echo "<td>Modificar</td>";
echo "<td>Imprimir</td></tr>";

	$numfilas =0;
	while($file = $Empleados->fetch_array(MYSQLI_ASSOC)){
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
		if ($file[Estado]=='Cerrada'){
		echo "<td align='center'><a></a></td>";
		}elseif($file[Estado]=='Cargada'){
		echo "<td align='center'><a class='img' href='Logistica.php?id=Cerrar&orden_t=$file[NumerodeOrden]'><img src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
		}elseif($file[Estado]=='Alta'){
		echo "<td align='center'><a class='img' href='Logistica.php?id=Agregar&orden_t=$file[NumerodeOrden]'><img src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
		echo "<td align='center'><a target='_blank' class='img' href='https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=$file[NumerodeOrden]'><img src='../../images/botones/mas.png' width='15' height='15' border='0' style='float:left;'></a></td>";
	
		}	
		$numfilas++; 
		}
echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
goto a;
}
//---------------------------------------------HASTA ACA VER ORDENES-------------------------------	
	
//-------------------------------------DESDE ACA AGREGAR NUEVO ITEM-----------------------------------
	if ($_GET['id']=='Agregar'){
if($_GET['recorrido_t']==''){
		$Grupo="SELECT * FROM Recorridos";
		$estructura= $mysqli->query($Grupo);
		echo "<form class='login' action='' method='get' style='width:500px'>";
		echo "<div><titulo>Seleccione Recorrido</titulo></div>";
    echo "<div><hr></hr></div>";
    echo "<div><label>Recorrido:</label><select name='recorrido_t' style='float:right;width:310px;' size='0'>";
		while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
		echo "<option value='".$row[Numero]."'>".$row[Nombre]."</option>";
		}
		echo "</select></div>";
		echo "<div><input class='submit' name='id' type='submit' value='Agregar'></div></table>";
		goto a;
	}
		
$color='#B8C6DE';
$font='white';
$color1='white';
$color2='#f2f2f2';
$font1='black';
$Recorrido=$_GET['recorrido_t'];
$idOrden= $mysqli->query("SELECT MAX(id) AS id,Fecha FROM HojaDeRuta");
$Datos=$idOrden->fetch_array(MYSQLI_ASSOC);
$Fecha=date('d/m/Y');

$NumerodeOrden=$mysqli->query("SELECT NumerodeOrden,Fecha FROM Logistica WHERE Recorrido='$Recorrido' AND Estado='Cargada'");
$dato=$NumerodeOrden->fetch_array(MYSQLI_ASSOC);
$NOE=$dato[NumerodeOrden];

	if(!isset($NOE)){
	$NOE="Sin Numero Asignado!!";	
	}	
	$Sqlposicion=$mysqli->query("SELECT MAX(Posicion)AS Posicion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto'");
$Dato=$Sqlposicion->fetch_array(MYSQLI_ASSOC);	
$Posicion = trim($Dato[Posicion])+1;
	
echo "<form class='login' action='' method='get' style='width:100%'>";
echo "<div><titulo>Agregar Nuevo ítem a hoja de ruta</titulo></div>";
    echo "<div><hr></hr></div>";
echo "<div><label>Numero de orden heredada:</label><input name='ndeorden_t' type='text' value='$NOE' style='width:150px;'/></div>";
echo "<div><label>Posicion:</label><input name='posicion_t' type='text' value='$Posicion' style='width:150px;'/></div>";
	
echo "<div><label>Fecha:</label><input name='' type='text' value='$Fecha' style='width:150px;'readonly/></div>";
// echo "<input name='fecha_t' type='hidden' value='$Fecha' style='width:150px;'/>";
echo "<div><label>Hora:</label><input name='hora_t' type='time' style='width:150px;'/></div>";
echo "<div><label>Recorrido:</label><input name='recorrido_t' type='text' value='".$_GET['recorrido_t']."' style='width:150px;'readonly/></div>";
//echo "<div><label>Cliente:</label><input name='cliente_t' type='text' value='' style='width:250px;'/></div>";
		$Grupo="SELECT * FROM Clientes WHERE Relacion='ODLS' ORDER BY nombrecliente";
		$estructura= $mysqli->query($Grupo);
		echo "<form class='login' action='' method='get' style='width:500px'>";
		echo "<div><label>Cliente:</label><select name='cliente_t' OnChange='submit()' style='float:right;width:310px;' size='1'>";
		while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
		echo "<option value='".$row[nombrecliente]."'>".$row[nombrecliente]."</option>";
     }
		echo "</select></div>";
   
    echo "<div><label>Direccion:</label><input name='localizacion_t' type='text' value='$row[Direccion]' style='width:250px;'/></div>";
    echo "<div><label>Ciudad:</label><input name='ciudad_t' type='text' value='$row[Ciudad]' style='width:250px;'/></div>";
    echo "<div><label>Provincia:</label><input name='provincia_t' type='text' value='$row[Provincia]' style='width:250px;'/></div>";
    echo "<div><label>Titulo:</label><input name='titulo_t' type='text' value='' style='width:400px;'/></div>";
		echo "<div><label>Recorrido:</label><input name='recorrido_t' type='text' value='$row[Recorrido]' style='width:250px;'/></div>";

    echo "<div><label>Observaciones:</label><textarea rows='10' cols='55' name='observaciones_t'/></textarea></div>";
    echo "<div><label>Usuario:</label><input name='usuario_t' type='text' value='".$_SESSION['Usuario']."' style='width:150px;'/></div>";
    echo "<div><label>Asignado:</label><select name='asignado_t'  style='width:200px;'required/>";
    echo "<option value='Unica Vez'>Unica Vez</option>";
    echo "<option value='Dejar Fijo'>Dejar Fijo</option>";
    echo "</select></div>";
    echo "<div><input class='submit' name='item' type='submit' value='Agregar'></div></table>";
    echo "</form>";
    goto a;
}
if ($_GET['item']=='Agregar'){
$Numero=$_GET['ndeorden_t'];
$Fecha=date('Y-m-d');	
$Hora=$_GET['hora_t'];
$Recorrido=$_GET['recorrido_t'];
$Localizacion=$_GET['localizacion_t'];
$Ciudad=$_GET['ciudad_t'];
$Provincia=$_GET['provincia_t'];
$Cliente=$_GET['cliente_t'];
$Titulo=$_GET['titulo_t'];
$Observaciones=$_GET['observaciones_t'];	
$Usuario=$_GET['usuario_t'];
$Asignado=$_GET['asignado_t'];
$Estado='Abierto';
$Posicion = $_GET['posicion_t'];

	$sql="INSERT INTO HojaDeRuta(
			Fecha,
			Hora,
			Recorrido,
			Localizacion,
            Ciudad,
            Provincia,
			Cliente,
			Titulo,
			Observaciones,
			Usuario,
			Asignado,Estado,NumerodeOrden,Posicion) VALUES (
			'{$Fecha}',
			'{$Hora}',
			'{$Recorrido}',
			'{$Localizacion}',
            '{$Ciudad}',
            '{$Provincia}',
            '{$Cliente}',
			'{$Titulo}',
			'{$Observaciones}',
			'{$Usuario}',
			'{$Asignado}',
			'{$Estado}',
			'{$Numero}',
			'{$Posicion}')";
$mysqli->query($sql);
header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);	
}	
//-------------------------------------------------HASTA ACA ALTA ORDENES DE SALIDA----------------------------------	
	
	if($_GET['id']=='Buscar'){

	if($_GET['accion']=='DejarFijo'){
	$id=$_GET['row'];	
	$sql2="UPDATE HojaDeRuta SET Asignado ='Dejar Fijo' WHERE id='$id'";
	$mysqli->query($sql2);
	}elseif($_GET['accion']=='SacarFijo'){
	$id=$_GET['row'];	
	$sql2="UPDATE HojaDeRuta SET Asignado ='Unica Vez' WHERE id='$id'";
	$mysqli->query($sql2);
	}elseif($_GET['accion']=='Eliminar'){
			$id=$_GET['row'];	
			if($_GET['Asignado']=='Dejar Fijo'){
			$sql2="UPDATE HojaDeRuta SET Estado='Cerrado' WHERE id='$id'";
			$mysqli->query($sql2);
			}else{
			  $sql2="UPDATE HojaDeRuta SET Eliminado=1 WHERE id='$id'";
			  $mysqli->query($sql2);
        $idhojaderuta=$mysqli->query("SELECT Seguimiento FROM HojaDeRuta WHERE id='$id'");
        $datoidhojaderuta=$idhojaderuta->fetch_array(MYSQLI_ASSOC);
        $sql=$mysqli->query("UPDATE TransClientes SET Eliminado=1 WHERE CodigoSeguimiento='$datoidhojaderuta[Seguimiento]'");
        //ELIMINAR DE CTASCTES O DESCONTAR SI EL SALDO ES SUPERIOR SEGUN EL NUMEROVENTA
        $sqlnuevo=$mysqli->query("SELECT Debe,NumeroComprobante FROM TransClientes WHERE CodigoSeguimiento='$datoidhojaderuta[Seguimiento]' AND Eliminado='1'");
        $row=$sqlnuevo->fetch_array(MYSQLI_ASSOC);
        $sqlnuevo=$mysqli->query("SELECT Debe FROM Ctasctes WHERE NumeroVenta='$row[NumeroComprobante]'");
        $rownuevo=$sqlnuevo->fetch_array(MYSQLI_ASSOC);
        if($row[Debe]==$rownuevo[Debe]){
        $sqlnuevo=$mysqli->query("UPDATE Ctasctes SET Eliminado='1' WHERE NumeroVenta='$row[NumeroComprobante]'");  
        }else{
        $sqlnuevo=$mysqli->query("UPDATE Ctasctes SET Debe=Debe-'$row[Debe]' WHERE NumeroVenta='$row[NumeroComprobante]'");    
        }
      header('location:https://www.caddy.com.ar/SistemaTriangular/Logistica/HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);  
			}	
	}elseif($_GET['accion']=='Subir'){
  $Recorrido=$_GET['recorrido_t'];	  
  $VerPosiciones=$mysqli->query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' ORDER BY Posicion ASC ");  
            $i=0;
            while($row=$VerPosiciones->fetch_array(MYSQLI_ASSOC)){
            $OrdenAutomatico="UPDATE HojaDeRuta SET Posicion='$i' WHERE id='$row[id]'";
            $mysqli->query($OrdenAutomatico);
            $i++;    
            }


	
    $posicion=$_GET['posicion'];
    $posicionbuscada=$posicion-1;	
    $id=$_GET['row'];	
    $NuevaPosicion=$posicion+1;	
    $idCliente=$_GET['idCliente'];  
    if($posicion<=26){
    $TramoMapa=1;  
    }elseif($posicion>=27){
    $TramoMapa=2;  
    } 
    
	$Recorrido=$_GET['recorrido_t'];	
    $sql2="UPDATE HojaDeRuta SET Posicion='$posicion',TramoMapa='$TramoMapa' WHERE Posicion='$posicionbuscada'AND Recorrido='$Recorrido'";
	$mysqli->query($sql2);

  $BuscoCLiente=$mysqli->query("SELECT idCliente FROM HojaDeRuta WHERE Posicion='$posicionbuscada' AND Recorrido='$Recorrido'");
  $EncuentroCliente=$BuscoCLiente->fetch_array(MYSQLI_ASSOC);;  
//   $ModificaPosicionClientes="UPDATE Clientes SET Orden='$posicion',Recorrido='$Recorrido' WHERE id='$EncuentroCliente'";  
  $ModificaPosicionClientes="UPDATE Clientes SET Orden='$posicion' WHERE id='$EncuentroCliente[idCliente]'";  
   $mysqli->query($ModificaPosicionClientes);  


  if($posicionbuscada<=26){
  $TramoMapa=1;  
  }elseif($posicionbuscada>=27){
  $TramoMapa=2;  
  } 


  $sql3="UPDATE HojaDeRuta SET Posicion='$posicionbuscada',TramoMapa='$TramoMapa' WHERE id='$id' ";
	$mysqli->query($sql3);
	
//   $ModificaPosicionClientes="UPDATE Clientes SET Orden='$posicionbuscada',Recorrido='$Recorrido' WHERE id='$idCliente'";  
  $ModificaPosicionClientes="UPDATE Clientes SET Orden='$posicionbuscada' WHERE id='$idCliente'";  
  $mysqli->query($ModificaPosicionClientes);  

    
    header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);	

  	
  }elseif($_GET['accion']=='Bajar'){
	$Recorrido=$_GET['recorrido_t'];	
  $posicion=$_GET['posicion'];
	$posicionbuscada=$posicion+1;	
	$id=$_GET['row'];	
	$NuevaPosicion=$posicion-1;	
	
   if($posicion<=26){
  $TramoMapa=1;  
  }elseif($posicion>=27){
  $TramoMapa=2;  
  } 
  $sql2="UPDATE HojaDeRuta SET Posicion='$posicion',TramoMapa='$TramoMapa' WHERE Posicion='$posicionbuscada' AND Recorrido='$Recorrido'";
	$mysqli->query($sql2);
	if($posicionbuscada<=26){
  $TramoMapa=1;  
  }elseif($posicionbuscada>=27){
  $TramoMapa=2;  
  } 
  $sql3="UPDATE HojaDeRuta SET Posicion='$posicionbuscada',TramoMapa='$TramoMapa' WHERE id='$id'";
	$mysqli->query($sql3);
	header('location:HojaDeRuta.php?id=Buscar&recorrido_t='.$Recorrido);	
	
	}
	if($_GET['recorrido_t']==''){
		$Grupo="SELECT * FROM Recorridos";
		$estructura= $mysqli->query($Grupo);
		echo "<form class='login' action='' method='get' style='width:500px'>";
		echo "<div><label>Recorrido:</label><select name='recorrido_t' style='float:right;width:310px;' size='0'>";
		while ($row = $estructura->fetch_array(MYSQLI_ASSOC)){
		echo "<option value='".$row[Numero]."'>".$row[Nombre]."</option>";
		}
		echo "</select></div>";
		echo "<div><input class='submit' name='id' type='submit' value='Buscar'></div></table>";
		goto a;
	}
	
$Recorrido=$_GET['recorrido_t'];	
$_SESSION['Recorrido']=$Recorrido;	
$Ordenar="SELECT Patente,NombreChofer,NombreChofer2 FROM Logistica WHERE Recorrido='$Recorrido' AND Estado<>'Cerrada'";
$sqlRecorrido=$mysqli->query("SELECT Nombre FROM Recorridos WHERE Numero='$Recorrido'");
$DatoRecorrido=$sqlRecorrido->fetch_array(MYSQLI_ASSOC);
    
$Consulta=$mysqli->query($Ordenar);
$DatoLogistica=$Consulta->fetch_array(MYSQLI_ASSOC);
$Chofer=$DatoLogistica['NombreChofer'];
$Acompanante=$DatoLogistica['NombreChofer2'];
$Dato=$DatosLogistica['Patente'];
$Ordenar=$mysqli->query("SELECT * FROM Vehiculos WHERE Dominio='$Dato'");

    
    
while ($file = $Ordenar->fetch_array(MYSQLI_ASSOC)){
echo "<form class='login' action='' method='get' style='width:65%'><div>";
echo "<div><label style='float:center;color:red;font-size:22px'>Hoja de Ruta Recorrido $DatoRecorrido[Nombre] ($Recorrido)</label></div><fieldset style='float:left;width:45%;'>";
echo "<div><label>Marca:</label><input name='marca_t' type='text' value='$file[Marca]' style='width:220px;' readonly/></div>";
echo "<div><label>Modelo:</label><input name='modelo_t' type='text' value='$file[Modelo]' style='width:220px;'readonly/></div>";
echo "<div><label>Dominio:</label><input name='dominio_t' type='text' value='$file[Dominio]' style='width:220px;'readonly/></div></fieldset>";
echo "<fieldset style='float:left;width:45%;margin-left:20px;'>";	
echo "<div><label>Chofer:</label><input name='chofer_t' type='text' value='$Chofer' style='width:180px;'readonly/></div>";
echo "<div><label>Acompañante:</label><input name='acompanante_t' type='text' value='$Acompanante' style='width:180px;'readonly/></div>";
echo "<div><input class='submit' name='id' type='submit' value='Agregar'></div>";
echo "</fieldset>";	
echo "</div></form>";	
}
//--MUESTRA LOS DATOS CARGADOS
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
$ordenar="SELECT HojaDeRuta.* FROM HojaDeRuta INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
WHERE HojaDeRuta.Recorrido='$Recorrido' AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Eliminado='0' AND HojaDeRuta.Devuelto=0 AND HojaDeRuta.Seguimiento<>'' ORDER BY if(TransClientes.Retirado=1,HojaDeRuta.Posicion,HojaDeRuta.Posicion_retiro) ASC";	
$MuestraTrans=$mysqli->query($ordenar);
$numfilas = $MuestraTrans->num_rows;
$_SESSION[numfilas]=$numfilas;
    
if ($numfilas==''){
echo "<table style='margin-top: 250px'><tr><td style='font-size:25px;color:white;'>SISTEMA DE GESTION TRIANGULAR S.A.</td></tr></table>";
goto a;
}	

$Extender='15';		
echo "<div style='overflow:auto; height:auto;'>";    
echo "<table class='login' >";
echo "<caption>Hoja de Ruta Recorrido $DatoRecorrido[Nombre] ($Recorrido)</caption>";
// echo "<th>Bajar</th>";
// echo "<th>Subir</th>";
echo "<th>Posicion</th>";    
echo "<th>Fecha</th>";
echo "<th>Hora</th>";
echo "<th>Servicio</th>";    
echo "<th>Origen</th>";    
echo "<th>N Cliente</th>";
echo "<th>Cliente</th>";
echo "<th>Localizacion</th>";
echo "<th>Ciudad</th>";
echo "<th>Barrio</th>";
echo "<th>Imprimir</th>";    
echo "<th>Editar</th>";
echo "<th>Eliminar</th>";
// echo "</table>";
// echo "<table class='login'>";    
    while($fila = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
    if($numfilas%2 == 0){
					if($fila[Asignado]=='Dejar Fijo'){
					echo "<tr style='background: #66B2FF;font-size:11px' >";
					}else{
					echo "<tr style='background: #f2f2f2;font-size:11px' >";
					}
		}else{
					if($fila[Asignado]=='Dejar Fijo'){
					echo "<tr style='background: #66B2FF;font-size:11px' >";
					}else{
					echo "<tr style='background:$color2;font-size:11px' >";
					}
		}	
 $sqlBuscoNCliente=$mysqli->query("SELECT * FROM Clientes WHERE id='".$fila[idCliente]."'");
 $row=$sqlBuscoNCliente->fetch_row(MYSQLI_ASSOC);  

 $sqlBuscoOrigen=$mysqli->query("SELECT RazonSocial,Retirado,CodigoSeguimiento FROM TransClientes WHERE CodigoSeguimiento='$fila[Seguimiento]'");
 $NombreOrigen=$sqlBuscoOrigen->fetch_array(MYSQLI_ASSOC);  
  
 if($NombreOrigen['Retirado']==1){
    $Posicion_=$fila['Posicion'];
    $Servicio='Entrega';      
  }else{
  $Posicion_=$fila['Posicion_retiro'];
  $Servicio='Retiro';      
  }
    
     
// echo "<td align='center' style='width:6%'><a class='img' href='HojaDeRuta.php?&id=Buscar&accion=Bajar&row=$fila[id]&recorrido_t=$Recorrido&posicion=$fila[Posicion]&idCliente=$fila[idCliente]'><img src='../images/botones/flechaabajo.png' width='15' height='15' border='0' style='float:center;'></a></td>";
// echo "<td align='center' style='width:6%'><a class='img' href='HojaDeRuta.php?&id=Buscar&accion=Subir&row=$fila[id]&recorrido_t=$Recorrido&posicion=$fila[Posicion]&idCliente=$fila[idCliente]'><img src='../images/botones/flechaarriba.png' width='15' height='15' border='0' style='float:center;'></a></td>";
if($fila[KmO]=='0'){
$Color="#E74C3C";  
}else{
$Color=black;  
}
echo "<td style='color:$Color'>".$Posicion_."</td>";
	
$fecha=$fila[Fecha];
$arrayfecha=explode('-',$fecha,3);
    echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
    echo "<td>".$fila[Hora]."</td>";
    echo "<td>".$Servicio."</td>";    
    echo "<td>".$NombreOrigen[RazonSocial]."</td>";    
    echo "<td>".$row[idProveedor]."</td>";
    echo "<td>".$fila[Cliente]."</td>";    
    echo "<td>".$fila[Localizacion]."</td>";
    echo "<td>".$fila[Ciudad]."</td>";
    echo "<td>".$row[Barrio]."</td>";
      // 	echo "<td>".$fila[Observaciones]."</td>";
	$Recorrido=$_GET['recorrido_t'];	
	echo "<td align='center'><a target='_blank' href='https://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Remitopdf2.php?CS=".$NombreOrigen[CodigoSeguimiento]."'><input type='image' src='../images/botones/Factura.png' width='15' height='15' border='0' style='float:center;'></td>";
  echo "<td align='center'><a class='img' href='HojaDeRuta.php?&id=Modificar&row=$fila[id]&recorrido_t=$Recorrido'><img src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:center;'></a></td>";
	echo "<td align='center'><a class='img' href='HojaDeRuta.php?&id=Buscar&accion=Eliminar&row=$fila[id]&recorrido_t=$Recorrido&Asignado=$fila[Asignado]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
// 	echo "<td>$mapa</td>";
      echo "</form>";
 	$numfilas++; 
	}
echo "</tr></table>";
echo "</div></form>";
//-*--HASTA ACA MUESTRA LOS SERVICE DE LA CAMIONETA
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
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&libraries=places&callback=initMap">
</script>
