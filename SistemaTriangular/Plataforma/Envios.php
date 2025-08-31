<?
session_start();
// include("../SeguridadUsuarioWeb.php");
include("../ConexionBD.php");
$sql=mysql_query("SELECT * FROM Clientes WHERE id='$_SESSION[NCliente]'");
$Dato=mysql_fetch_array($sql);

if($_POST[Proceso]=='Confirmar Envio'){
$Debe=$_POST['final'];
  $ClienteDestino=$_POST[clientedestinook];
  $Contacto=$_POST[contacto];
  $Observaciones=$_POST[observaciones];
  $DomicilioDestino=$_POST['end'];
  $Cantidad=$_POST[cantidad];
  $DomicilioOrigen=$_POST[start];
  $idClienteDestino=$_POST[idok];
  $Km=$_POST[kilometros];
  $Entrega='Domicilio';
$sqlMax=mysql_query("SELECT MAX(NumeroVenta)as NumeroVenta FROM PreVenta WHERE Eliminado=0 AND NCliente='$_SESSION[NCliente]'");
if ($row = mysql_fetch_array($sqlMax)) {
 $DatoNV = trim($row[NumeroVenta])+1;
 }
$Codigo='49';  
$Fecha=date('Y-m-d');  
$sql=mysql_query("INSERT INTO `PreVenta`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`,`NumeroComprobante`,`Total`,`ClienteDestino`,
  idClienteDestino,`DomicilioDestino`,`DomicilioOrigen`,`Cantidad`, `Usuario`,NCliente,NumeroVenta,Observaciones,Kilometros,EntregaEn) VALUES 
  ('{$Fecha}','{$Dato[nombrecliente]}','{$Dato[Cuit]}','SOLICITUD WEB','{$Codigo}','{$Debe}','{$ClienteDestino}','{$idClienteDestino}',
  '{$DomicilioDestino}','{$DomicilioOrigen}','{$Cantidad}','{$_SESSION[Usuario]}','{$_SESSION[NCliente]}','{$DatoNV}','{$Observaciones}',
  '{$Km}','{$Entrega}')");  
//               $variable =$_GET['valor[]'];
//               $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
//               mysql_query($sql);
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    
   <title>.::Triangular S.A.::.</title>
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />

<!--     <link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" /> -->
    <script>
        function selectCliente() {
          document.getElementById('ClienteDestino').style.display='inline-block';  
         document.getElementById('clientedestino').style.display='block';
         document.getElementById('endcalle').style.display='block';
         document.getElementById('endnumero').style.display='block';
         document.getElementById('endciudad').style.display='block';
          
          var dir=document.getElementById('cliente').value;
         if(dir!=''){
          var dir= dir.split(','); 
          document.getElementById('clientedestino').value=dir[0];
          document.getElementById('endcalle').value=dir[1];
          document.getElementById('endnumero').value=dir[2];
          document.getElementById('endciudad').value=dir[3];
          document.getElementById('contacto').value=dir[4];
          document.getElementById('endobservaciones').value=dir[5];
          document.getElementById('id').value=dir[6];

           document.getElementById('opcionNuevos').style.display='none';

         }
        }  
</script> 

    <script>
        function foo() {
                 document.getElementById('opcionNuevos').style.display='block';
          
        if(checkbox.checked){

//           document.getElementById('ClienteDestino').style.display='inline-block';  
//         document.getElementById('opcionClientes').style.display='none';

        }
        else {
//         document.getElementById('ClienteDestino').style.display='none';  
         }
        }  
</script> 
    <style>
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
        width: 50%;
      }
      #right-panel {
        float: left;
        width: 48%;
        padding-left: 2%;
      }
      #output {
        font-size: 11px;
      }
    </style>
  </head>
  <body>
    
 <?
include('menu.html');

    
    ?>
   <div id="right-panel">
      <div id="inputs">
 
<!--     <div id="lateral" style="width:40%;"> -->
 
<!--     <div id='inicio' >     -->
      <form class="login" method="POST" action="" style='margin-top:0px;margin-right:0px;height:90%' >
<!--         <h2>Envio Simple Caddy:</h2> -->
          <div style='width:100%;display: inline-block;'>
            <div style='width:100%;display: inline-block;margin-bottom:15px'>
              <label style='color:#E24F30;margin-bottom:30px'>Origen..</label>
            </div>
              <div style='width: 45%;display: inline-block;margin-bottom:15px'> 
              <input style='margin-bottom:20px;' type="text" name='comienzo' id="startcalle"  value="<? echo $Dato[Calle];?>" placeholder='Calle...'>
              </div>
              <div style='width: 15%;display: inline-block;'>
                <input style='margin-bottom:20px;' type="text" name='comienzo' id="startnumero"  value="<? echo $Dato[Numero];?>" placeholder='Numero'>
              </div>
                <div style='width: 35%;display: inline-block;'>
                <select  id="startciudad" name="startciudad">
                <option value="Cordoba" >Cordoba</option>  
                <?  
                $sqlBuscoLocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE Localidad<>'Capital'");
                while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
                echo "<option value='$Datos[Localidad]'>$Datos[Localidad]</option>";  
                }
                ?>
                </select>
                </div>
          </div>
            <div style='width: 75%;display:block;margin-bottom:15px'>
            <label style='color:#E24F30;margin-bottom:30px'>Destino.. </label>
            </div>
              <div style='width: 97%;display:block;'>
      
<!--       DESDE ACA SELECCIONAR CLIENTES PARA DESTINO   -->
                <div id="opcionClientes">
                <select  id="cliente" name="cliente" onchange="selectCliente()">
                <option value="" >Seleccione un Cliente...</option>  
      <?  
      $sqlBuscoLocalidad=mysql_query("SELECT * FROM Clientes WHERE Relacion='$_SESSION[NCliente]'");
      while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
      echo "<option value='$Datos[nombrecliente],$Datos[Calle],$Datos[Numero],$Datos[Ciudad],$Datos[Contacto],$Datos[Observaciones],$Datos[NdeCliente]' >$Datos[nombrecliente]</option>";  
      }

        ?>
                </select>
                </div>
              </div>  
                <div id='ClienteDestino' style='width: 100%;display: inline-block ;margin-bottom:20px;'>
                <input style='margin-bottom:20px;' type="hidden" id="waypoints" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
                <input type="hidden" name='id' id="id" value="" >
                <input type="hidden" name='clientedestino' id="clientedestino" value="" style='display:none'>
                  <div style='width: 45%;display: inline-block;margin-bottom:20px;'>
                  <input style='margin-bottom:20px;display:none' type="text" name='endcalle' id="endcalle" value=""  placeholder='Calle' onfocus='foo()' required>
                  </div>
                     <div style='width: 10%;display: inline-block;'>
                     <input style='margin-bottom:20px;display:none;' type="text" name='endnumero' id="endnumero"  value="" placeholder='Numero' >
                     </div>
                        <div style='width: 35%;display: inline-block;'>
                        <select  id="endciudad" name="endciudad" style='display:none'>
                        <option value="Cordoba" >Cordoba</option>  
      <?  
      $sqlBuscoLocalidad=mysql_query("SELECT Localidad FROM Localidades WHERE Localidad<>'Capital'");
      while($Datos=mysql_fetch_array($sqlBuscoLocalidad)){
      echo "<option value='$Datos[Localidad]'>$Datos[Localidad]</option>";  
      }
      ?>
                      </select>
                      </div>
                        <div style='width: 100%;display:block;margin-bottom:15px;'>
                        <label style='color:#E24F30;margin-bottom:20px'>Contacto..</label>
                        </div> 
                          <div style='width: 35%;display:block;margin-bottom:15px;'>
                          <input style='margin-bottom:20px;' type="text" name='contacto' id="contacto" value=""  placeholder='Nombre de quien recibe tu envio...'>
                          </div>
                            <div style='width: 100%;display:block;margin-bottom:15px;'>
                            <label style='color:#E24F30;margin-bottom:20px'>Observaciones..</label>
                            </div>
                              <div style='width: 100%;display:block;margin-bottom:15px;'>
                              <input style='margin-bottom:20px;' type="text" name='endobservaciones' id="endobservaciones" value=""  placeholder='Observaciones...'>
                              </div>
                                  <div id="opcionNuevos" style='display:none'>
                                    <input  type="checkbox" id="checkbox"  onchange="foo()">
                                    <label style='color:#E24F30;margin-bottom:20px;'>Agendar como cliente..</label>
                                  </div>

                   </div>
<!--       <div align='center' style='width:500px;'><hr/></div> -->
                      <div style='width: 25%;display: block;'>
                          <label style='color:#E24F30'>Cantidad..</label>
                          <input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidad" value="1"  placeholder='Cantidad de paquetes...'>
                      </div>

<!--       <h1 style='margin-top:29px'>Dimensiones:</h1>  -->
<!--       <div><hr/></div> -->
<?
if($_SESSION[Servicios]<>''){ 
  
?>
   <label style='color:#E24F30'>Servicio..</label>
     <input type="hidden" id="servicios" value="1">   
      <div style='width: 25%;display: inline-block;'>
       <select  id="dimensiones" name="dimensiones">
        <option value="" >Seleccione un servicio.</option>  
<?  
$sqlBuscoServiciosxCliente=mysql_query("SELECT Productos.Titulo,Productos.Codigo,Productos.PrecioVenta,ClientesyServicios.Servicio 
FROM (ClientesyServicios,Productos)
WHERE ClientesyServicios.Servicio=Productos.Codigo");
while($Servicios=mysql_fetch_array($sqlBuscoServiciosxCliente)){
echo "<option value='$Servicios[PrecioVenta]'>$Servicios[Titulo]  ( $  $Servicios[PrecioVenta] )</option>";  
}
?>
      </select>
      </div>
<?
}else{
  
?>  
      <input type="hidden" id="servicios" value="0">   
      <label style='color:#E24F30'>Dimensiones..</label>
      <select  id="dimensiones" name="dimensiones">
      <option value="" >Seleccione una opcion</option>  
      <option value="1">Paquete Chico hasta 20 cm x 20 cm x 20 cm</option>  
      <option value="2">Paquete Mediano hasta 50 cm x 50 cm x 50 cm</option>  
      <option value="3">Paquete Grande hasta 100 cm x 100 cm x 100 cm</option>  
      </select>
<?
}
        ?>
    <input name="Subimos" value="Aceptar" id="submit">
    </form>
<!--       </div> -->
        </div> <!-- fin lateral --> 
    </div>
<!--      <div id="otrolado"> -->
<!--     <div id='resultado'>     -->
<!--   <form  class='login' method='POST' action="" > -->
<!--       <h2>Resultado de su cotizacion:</h2> -->
      <div id='map' ></div>
<!--       </form> -->
<!--         <div id="directions-panel" ></div> -->
<!--        </div>   -->
<!--     </div> -->
    <div style='display:none'>
      <form  class='login' method='POST' action="" >
      <h2>Confirmar Envio:</h2>
        <input type="text" id="final" value="" style="font-size:20px;" placeholder=''>
       <input value="Seguir" id="submit2" style="box-shadow: inset 0px 1px 0px 0px #45D6D6;
      background-color: #E24F30;
      border: 1px solid #E24F30;
      display: inline-block;
      cursor: pointer;
      color: #FFFFFF;
      font-family: 'Open Sans Condensed', sans-serif;
      font-size: 14px;
      padding: 8px 18px;
      text-decoration: none;
      text-transform: uppercase;
      " >
    </form>
  </div>

  <div id='checkout' style='display:none' >    
      <form  class='login' method='POST' action="" style='width:850px;float:left;margin-left:300px;margin-right:15px;'>
      <h2>Checkout:</h2>
      <label style='color:#E24F30'>Desde: </label><input style='margin-bottom:20px;' type="text" name="start" id="startok"  value="">
      
        <label id='labelway' style='color:#E24F30;display:none;'>Punto Intermedio..</label><input style='margin-bottom:20px;' type="hidden" id="waypointsok" value=""  placeholder='Punto Intermedio: Direccion, Ciudad' >
      <label style='color:#E24F30'>Hasta..</label><input style='margin-bottom:20px;' type="text" name='end' id="endok" value="<? echo $b;?>"  placeholder='Final: Direccion, Ciudad'>
      <input type="hidden" name='idok' id="idok" value="">
        <label style='color:#E24F30'>Cliente..</label><input style='margin-bottom:20px;' type="text" name='clientedestinook' id="clientedestinook" value=""  placeholder='Nombre de quien recibe tu envio...'>
        <label style='color:#E24F30'>Contacto..</label><input style='margin-bottom:20px;' type="text" name='contacto' id="contactook" value=""  placeholder='Nombre de quien recibe tu envio...'>
      <label style='color:#E24F30'>Cantidad..</label><input style='margin-bottom:20px;' type="number" name='cantidad' id="cantidadok" value=""  placeholder='Nombre de quien recibe tu envio...'>
      <label style='color:#E24F30'>Dimensiones..</label><input style='margin-bottom:20px;' type="text" name='dimensiones' id="dimensionesok" value=""  placeholder='Dimensiones del paquete a enviar...'>
      <label style='color:#E24F30'>Observaciones..</label><input style='margin-bottom:20px;' type="text" name='observacionesok' id="observacionesok" value=""  placeholder='Observaciones...'>
      <label style='color:#E24F30'>Kilometros..</label><input style='margin-bottom:20px;' type="text" name='kilometros' id="kilometros" value=""  placeholder='Observaciones...'>
      
        <label style='color:#E24F30'>Total Cotizacion..</label><input style='margin-bottom:20px;' type="text" name='final' id="finalok" value=""  placeholder='Dimensiones del paquete a enviar...'>
      <input type='submit' name='Proceso' value="Confirmar Envio" style="float:left;">
      </form>
  </div>
  
      <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: -31.4448988, lng: -64.177743}
        });
        
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            infoWindow.setPosition(pos);
            infoWindow.setContent('Location found.');
            infoWindow.open(map);
            map.setCenter(pos);
          }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
          });
        
        
        directionsDisplay.setMap(map);
        
        document.getElementById('submit2').addEventListener('click', function() {
        document.getElementById('startok').value=document.getElementById('startcalle').value+ '   '  + document.getElementById('startnumero').value;
        document.getElementById('endok').value=document.getElementById('endcalle').value+ '   '  + document.getElementById('endnumero').value;
        document.getElementById('contactook').value=document.getElementById('contacto').value;
        document.getElementById('clientedestinook').value=document.getElementById('clientedestino').value;
        document.getElementById('observacionesok').value=document.getElementById('endobservaciones').value;

          document.getElementById('idok').value=document.getElementById('id').value;
        document.getElementById('dimensionesok').value=document.getElementById('dimensiones').value;
        document.getElementById('cantidadok').value=document.getElementById('cantidad').value;
        document.getElementById('finalok').value=document.getElementById('final').value;
        document.getElementById('resultado').style.display='none';
        document.getElementById('checkout').style.display='block';
        });
        
        document.getElementById('submit').addEventListener('click', function() {
        if(document.getElementById('startcalle').value == ""){
        alert('Seleccione donde buscaremos el paquete...');
        initMap.finish();  
        }
        if(document.getElementById('endcalle').value == ""){
        alert('Seleccione el destino del paquete...');
        initMap.finish();  
        }
        if(document.getElementById('dimensiones').value == ""){
        alert('Seleccione las dimensiones del paquete...');
        initMap.finish();  
        }
        if(document.getElementById('endciudad').value == ""){
        alert('Seleccione la ciudad de destino...');
        initMap.finish();  
        }
          
        calculateAndDisplayRoute(directionsService, directionsDisplay);
        document.getElementById('resultado').style.display='block';
//         document.getElementById('inicio').style.display='none';
//            document.location.href = "Calculador.php#resultado";

        });
        
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        
        
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        var dato1 = document.getElementById('waypoints').value;
//         for (var i = 0; i < checkboxArray.length; i++) {
          if (dato1 != '') {
            waypts.push({
              location: document.getElementById('waypoints').value,
              stopover: true
            });
  
          }
//         }
//           function displayRoute(origin, destination, service, display) {
          directionsService.route({
          origin: document.getElementById('startcalle').value + document.getElementById('startnumero').value + ',' +
          document.getElementById('startciudad').value,
          destination: document.getElementById('endcalle').value + document.getElementById('endnumero').value + ',' +
          document.getElementById('endciudad').value,
          waypoints: waypts,
          optimizeWaypoints: true,
          provideRouteAlternatives: true,
          travelMode: 'DRIVING'
        }, function(response, status) {
          
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var summaryPanel = document.getElementById('directions-panel');
            summaryPanel.innerHTML = '';
            // For each route, display summary information.
            var totalDistance = 0;
            var totalDuration = 0;
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
        
              summaryPanel.innerHTML += '<b style="background:#DFIJEJ;font-weight: bold;">Envio: ' + routeSegment +
                  '</b><br>Desde ';
              summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
//               summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
//               summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
//               summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

              var dato = route.legs[i].duration.value;
            
            }
            
            var summaryPanel = document.getElementById('directions-panel');
            
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            var dimensiones = document.getElementById('dimensiones').value;
            var cantidad = document.getElementById('cantidad').value;
            
            var dimen=1;
            if(dimensiones == 1){
            var dimen = 120;
            var titulodimensiones="Chico";  
            var txtdimensiones="Maximo de 20 cm de Alto x 20 cm de Ancho x 20 cm de largo";  
            }
            if(dimensiones== 2){
            var dimen= 150;  
            var titulodimensiones="Mediano";  
            var txtdimensiones="Maximo de 50 cm de Alto x 50 cm de Ancho x 50 cm de largo";  
            }
            if(dimensiones== 3){
            var dimen= 180;  
            var titulodimensiones="Grande";  
            var txtdimensiones="Maximo de 80 cm de Alto x 80 cm de Ancho x 80 cm de largo";  
            }
            
            var res = totalDistance;
            document.getElementById('kilometros').value=res;
            
            var servicio=document.getElementById('servicios').value;
            var maxKmCliente= <? echo $_SESSION['MaxKm'];?>

            if(res < maxKmCliente){
            var costo= dimen * cantidad;
            }else{
            var costokm= (res-10) * <? echo $_SESSION['PrecioKmCliente'];?>  
            var costo= costokm + dimen * cantidad; 
            }
            
            var iva= costo * 1.21;
            var costoconiva=costo +(costo * 1.21);
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><br><b> Duracion Total: ';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos </b><br>';
            summaryPanel.innerHTML += '<br></br><b> Dimensiones:  ' + titulodimensiones + ' </b>';
            summaryPanel.innerHTML += '<br></br><b> ' + txtdimensiones + ' </b>';
            
            
            var costoconDecimal = costo.toFixed(2);
            var ivaconDecimal = iva.toFixed(2);
            var costoconivaconDecimal = costoconiva.toFixed(2);

//             summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Costo Total: $ ' + costo + ' </b>';            
//             document.getElementById('total').value='Precio Envio: $ ' + costoconDecimal ;
//             document.getElementById('iva').value='Impuestos (Iva): $ ' + ivaconDecimal;
            document.getElementById('final').value=costoconDecimal;
          } else {
            window.alert('Verifique las direcciones cargadas ' + status);
            document.getElementById('resultado').style.display='none';
            document.getElementById('inicio').style.display='block';

          }
        });
        
        
        
      }
    </script>
    <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
  <?
  a:
  ?>
  </body>
</html>