<?
session_start();
include("../ConexionBD.php");
if($_GET['viajar']=='si'){
$variable =$_GET['valor[]'];
$sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
mysql_query($sql);
}
if($_POST[idcliente]<>''){
$_SESSION[idcliente]=$_POST[idcliente];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>.::Triangular S.A.::.</title>
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
    <style>  
    #submit,#primerpaso{
    background: none repeat scroll 0 0 #E24F30;
    border: 1px solid #C6C6C6;
    float: right;
    font-weight: bold;
    padding: 8px 26px;
    color:#FFFFFF;
    font-size:12px;
    width:100px;
    }
  
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 50%;
      }

      #right-panel i {
        font-size: 12px;
      }
        #map {
        height: 33%;
        width: 43%;
        float:right;
        position:absolute; 
        top:105px; 
        margin-left:50%;
        z-index:99;   
   }
      #directions-panel {
        margin-top: 40px;
        background-color: #FDFEFE; /*#FFEE77;*/
        padding: 10px;
        overflow: scroll;
        height: 30%;
        width: 95%;
/*      height: 200px; */
        font-size:10px;
      }
    </style>
    <script>

    function verwp(){
    document.getElementById('ver').style.display='block';  
//     document.getElementById('ver1').style.display='block'; 
    }
    </script>
    <script>
    function primerpaso(){
    var cadena=document.getElementById('idcliente').value;
    var datos  = cadena.split(" - ");
    document.getElementById('idcliente_heredado').value=datos[0];
    
    if(cadena!=''){
    document.getElementById('cliente').value=datos[1];
    }else{
    document.getElementById('cliente').value='Consumidor Final';
    }

    document.getElementById('primero').style.display='none';
    document.getElementById('segundo').style.display='block'; 
    document.getElementById('map').style.display='block';  
//     document.getElementById('logo').style.display='none';

    var retiro=document.getElementById('retiro').value;
    var envio=document.getElementById('envio').value;  

    switch (retiro) {
    case 'caddycba':
    document.getElementById('start').value='Reconquista 4986';
    document.getElementById('start').disabled=true;
    document.getElementById('startciudad').value='Cordoba Capital';
    document.getElementById('startciudad').disabled=true;
        break;
    case 'caddyvm':
    document.getElementById('start').value='Villa Maria';
    document.getElementById('start').disabled=true;
    document.getElementById('startciudad').value='Villa Maria';
    document.getElementById('startciudad').disabled=true;
        break;
    case 'caddyr4':
    document.getElementById('start').value='General Paz 1171';
    document.getElementById('start').disabled=true;
    document.getElementById('startciudad').value='Rio Cuarto';
    document.getElementById('startciudad').disabled=true;
        break;
    case 'caddysf':
    document.getElementById('start').value='Iturraspe 100';
    document.getElementById('start').disabled=true;
    document.getElementById('startciudad').value='San Francisco';
    document.getElementById('startciudad').disabled=true;
        break;
    case 'domicilio':
    if(cadena!=''){
    document.getElementById('start').value=datos[2]; 
    document.getElementById('start').disabled=true;
    document.getElementById('startciudad').value=datos[3];
    document.getElementById('startciudad').disabled=true;
    document.getElementById('seleccionretiro').value='Domicilio';
    }else{
    document.getElementById('seleccionretiro').value='Domicilio';
    }
        break;    
    case '':
    document.getElementById('start').value='';  
    }

    switch (envio) {
    case 'caddycba':
    document.getElementById('end').value='Reconquista 4986';
    document.getElementById('end').disabled=true;
    document.getElementById('endciudad').value='Cordoba Capital,1';
    document.getElementById('endciudad').disabled=true;
        break;
    case 'caddyvm':
    document.getElementById('end').value='Villa Maria';
    document.getElementById('end').disabled=true;
    document.getElementById('endciudad').value='Villa Maria,1';
    document.getElementById('endciudad').disabled=true;
        break;
    case 'caddyr4':
    document.getElementById('end').value='General Paz 1171';
    document.getElementById('end').disabled=true;
    document.getElementById('endciudad').value='Rio Cuarto,1';
    document.getElementById('endciudad').disabled=true;
        break;
    case 'caddysf':
    document.getElementById('end').value='Iturraspe 100';
    document.getElementById('end').disabled=true;
    document.getElementById('endciudad').value='San Francisco,1';
    document.getElementById('endciudad').disabled=true;
        break;
    case 'domicilio':
    document.getElementById('seleccionenvio').value='Domicilio';
    document.getElementById('end').value='';  
    }

    }  
    
    </script>

  </head>
  <body>
 <?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
include("../Alertas/alertas.html");    
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>";
if($_POST[cotizacion]=='Guardar'){
$Fecha=date('Y-m-d'); 

if($NCliente<>''){
$NCliente=$_POST[idcliente_heredado]; 
$sqlcliente=mysql_query("SELECT * FROM Clientes WHERE id='$NCliente'");
$DatosCliente=mysql_fetch_array($sqlcliente);
$NombreCliente=$DatosCliente[nombrecliente];  
}else{
$NombreCliente=$_POST[cliente];  
}  
$Cantidad=$_POST[cantidad];
$Precio=$_POST[precio_cotizacion];  
$Total=$_POST[cantidad]*$_POST[precio_cotizacion];  
$DomicilioDestino=$_POST[final_calle];
$LocalidadDestino=$_POST[final_ciudad]; 
$DomicilioOrigen=$_POST[comienzo_calle];
$LocalidadOrigen=$_POST[comienzo_ciudad];  
$Envio=$_POST[envio];
$Descripcion=$_POST[descripcion]; 
$EntregaEn=$_POST[envio_final]; 
$ProvinciaOrigen='Cordoba';
$ProvinciaDestino='Cordoba';
$Ancho=$_POST[ancho];
$Largo=$_POST[largo];
$Alto=$_POST[alto];
$CambiaLocalidad=$_POST[cambia_localidad];
$PuntoIntermedio=$_POST[punto_intermedio];
$Redespacho=$_POST[requiere_redespacho];
$Observaciones=$_POST[Destalle];  
$Tarifa=$_POST[Tarifa];
  
$sql=mysql_query("INSERT INTO `Cotizaciones`(`Fecha`, `RazonSocial`, `NCliente`,`Cantidad`, `Precio`,Total,`DomicilioDestino`,
`LocalidadDestino`,`DomicilioOrigen`, `LocalidadOrigen`,EntregaEn,Descripcion,ProvinciaOrigen,ProvinciaDestino,Ancho,Largo,Alto,CambiaLocalidad,
PuntoIntermedio,Redespacho,Observaciones,Tarifa) VALUES 
('{$Fecha}','{$NombreCliente}','{$NCliente}','{$Cantidad}','{$Precio}','{$Total}',
'{$DomicilioDestino}','{$LocalidadDestino}','{$DomicilioOrigen}','{$LocalidadOrigen}','{$EntregaEn}',
'{$Descripcion}','{$ProvinciaOrigen}','{$ProvinciaDestino}','{$Ancho}','{$Largo}','{$Alto}','{$CambiaLocalidad}',
'{$PuntoIntermedio}','{$Redespacho}','{$Observaciones}','{$Tarifa}')");
}    
    
?>
  <div id='primero'>
  <form  class='login' method='POST' action='' style='width:50%;height:50%;float:center;background:white;border-top: 3px red solid;z-index:1'>
  <titulo>Cotizador Caddy:</titulo>
  <div>
  <hr/>
  </div>
  <div><label>Cliente:</label><input type='text' name='idcliente' list='idcliente' placeholder="Comience a escribir el nombre...">
  <datalist id='idcliente'>
  <select name=''>
  <?
  $sql=mysql_query("SELECT Clientes.id,Clientes.nombrecliente,Clientes.Direccion,Localidades.Localidad From (Clientes,Localidades) WHERE 
  Clientes.Ciudad=Localidades.Localidad ORDER BY Clientes.nombrecliente");
  echo "<option value=''>Seleccione un Cliente</option>";
  while($Cliente=mysql_fetch_array($sql)){
  echo "<option value='$Cliente[id] - $Cliente[nombrecliente]'></option>";
  }
  ?> 
  </select>
  </datalist>
  </div>  
  <div><label>Donde retiramos el envio:</label><select id='retiro' name='retiro' style='height:30px'>
  <option value='domicilio'>Retiro en Domicilio</option>
  <option value='caddycba'>Sucursal Caddy Cordoba</option> 
  <option value='caddyvm'>Sucursal Caddy Villa Maria</option> 
  <option value='caddyr4'>Sucursal Caddy Rio Cuarto</option> 
  <option value='caddysf'>Sucursal Caddy San Francisco</option> 
    </select></div>  
    <div><label>Donde entregamos el envio:</label><select id='envio' name='envio' style='height:30px'>
    <option value='domicilio'selected>Entrega en Domicilio</option>
    <option value='caddycba'>Sucursal Caddy Cordoba</option> 
    <option value='caddyvm'>Sucursal Caddy Villa Maria</option> 
    <option value='caddyr4'>Sucursal Caddy Rio Cuarto</option> 
    <option value='caddysf'>Sucursal Caddy San Francisco</option> 
    </select></div>  
    <div><input value='Aceptar' id="PrimerPaso" Onclick='primerpaso()' style='cursor:pointer'></div>
    </form>  
    </div>
      <div id='segundo' style='display:none'>   
      <form  class='Caddy' method='POST' action='' style='width:80%;float:left;'>
      <titulo>Cotizador Caddy:</titulo>
      <div>
      <hr/>
      </div>
      <div style="width:40%"><label>Retiro en :</label><input type='text' id='seleccionretiro' name='seleccionretiro' style='width:200px;' readonly></div>
      <div style="width:40%"><label>Envio a :</label><input type='text' id='seleccionenvio' name='seleccionenvio' style='width:200px;' readonly></div>
      <div style="width:40%"><label>Cliente:</label><input type='text' id='cliente' name='cliente' style='width:200px;'> </div>
      <input id='idcliente_heredado' type='hidden' name='idcliente_heredado'>
        
      <div style="width:40%"><label>Comienzo:</label><input type="text" class="form-control" name='comienzocalle' id="start" style='width:370px;' placeholder='Calle Numero'></div>
      <input type='hidden' name='comienzociudad' id="startciudad" style='width:200px;'>
      <div style="width:40%"><label>Mostrar Punto Intermedio...</label><input type='checkbox' name='wp' id='wp' Onclick='verwp()'></div>
      <div id='ver' style='display:none;width:40%'><label>Parada:</label><input type="text" class="form-control" id="waypoints" value="" style='width:370px;' placeholder='Calle Numero'></div>
      <input type='hidden' id="waypointsciudad" value="" style='width:200px;'>
      <div style="width:40%"><label>Final:</label><input type="text"  class="form-control" name='finalcalle' id="end" value="<? echo $b;?>" style='width:370px;' placeholder='Calle Numero'></div>
      <input type="hidden" name='finalciudad' class="form-control" id="endciudad" value="" style='width:200px;' >
      <input type="hidden" name='web' id="web" value="" >
      <fieldset style='float:left;width:45%;'>  
      <titulo>Especificaciones:  <span  id="resultado" ></span></titulo>
      <div><hr/></div>
      <div style="width:95%"><label>Descripcion:</label><input type="text" name='descripcion' class="controls" id="descripcion" style='width:300px;' placeholder='Descripcion'></div>
      <div style="width:95%"><label>Valor Declarado:</label><input type="number" name='valordeclarado' class="controls" id="valordeclarado" style='width:80px;' value='1'placeholder='$ 1.00'></div>
      <div style="width:95%"><label>Cantidad:</label><input type="number" name='cantidad' id="cantidad" style='width:80px;' value='1' placeholder='Cantidad'></div>
      <div style="width:95%"><label>Peso</label><label style='color:gray;font-size:16px;margin-left:10px'> | Max. 500 kg. :</label><input type="number" name="peso" id="peso" style='width:80px;' value='1' placeholder='Peso'></div>
      <div style="width:95%"><label>Ancho</label><label style='color:gray;font-size:16px;margin-left:10px'> | Max. 200 cm. :</label><input type="number" name='ancho' id="ancho" style='width:80px;' min='1' max='200' placeholder='Cm' onblur='verificar(this.id,this.value,this.max)'></div>
      <div style="width:95%"><label>Largo</label><label style='color:gray;font-size:16px;margin-left:10px'> | Max. 200 cm. :</label><input type="number" name='largo' id="largo" style='width:80px;' placeholder='Cm' max='200'  onblur='verificar(this.id,this.value,this.max)'></div>
      <div style="width:95%"><label>Alto</label><label style='color:gray;font-size:16px;margin-left:10px'> | Max. 200 cm. :</label><input type="number"  name='alto' id="alto" style='width:80px;' placeholder='Cm' max='200' onblur='verificar(this.id,this.value,this.max)'></div>
      <input type='hidden' name='precio_cotizacion' id='costo'>
      <input type='hidden' name='comienzo_calle' id='comienzo_calle'>   
      <input type='hidden' name='comienzo_ciudad' id='comienzo_ciudad'>   
      <input type='hidden' name='final_calle' id='final_calle'>   
      <input type='hidden' name='final_ciudad' id='final_ciudad'>
      <input type='hidden' name='retiro_final' id='retiro_final'>   
      <input type='hidden' name='cambia_localidad' id='cambia_localidad'>   
      <input type='hidden' name='punto_intermedio' id='punto_intermedio'>   
      <input type='hidden' name='requiere_redespacho' id='requiere_redespacho'>
      <input type='hidden' name='tarifa' id='tarifa'>   
      <input type='hidden' name='detalle' id='detalle'>
  
      <div><input style='cursor:pointer' value='Aceptar' id="submit" readonly></div>
      </fieldset>
      <fieldset style='float:left;width:55%;'>  
      <titulo>Resultado:</titulo>
      <div><hr/></div>
      <div id="directions-panel" name="observaciones"></div>
      <div><input style='cursor:pointer' type='submit' name='cotizacion' value='Guardar'></div>
      </fieldset>   
      </form>
</div> 
    
    <div id='map' style='display:none'></div>
    </div>
    </div>
    </div>
</div>
<script>
function verificar(name,value,max){
  var valor=parseFloat(value);
  var maximo=parseFloat(max);  

  if(valor>maximo){
  document.getElementById(name).value=0;
  alertify.error('Maximo ' + max + ' cm. ' + name);
  }  
}
</script>
<script>

  function realizaProceso(a){
    var result;
    var dato={
        "localidadorigen": a,  
        };
        
        $.ajax({
        async: false,  
        data: dato,
        url:'localidades.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (respuesta) {
          $("#resultado").html(respuesta);
        }
        });
  }

</script>
      <script>
 
      function initMap() {
        
        //START
        var inputstart = document.getElementById('start');
        var autocomplete = new google.maps.places.Autocomplete(inputstart);
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
              realizaProceso(ciudad);
              if(document.getElementById('resultado').innerText==0){
              alertify.error('La Localidad de origen '+ ciudad +' no se encuentra a nuestro alcance, analice redespacho');          
              document.getElementById('start').value = '';
              document.getElementById('start').focus();
              break;
              }  
              document.getElementById('startciudad').value = ciudad;
             }
            }
        }
        }); 
        
        //WAYPOINTS
        var inputwaypoints = document.getElementById('waypoints');
        var autocompletew = new google.maps.places.Autocomplete(inputwaypoints);
        autocompletew.addListener('place_changed', function() {
        var placew = autocompletew.getPlace();
        if (placew.address_components) {
        var componentsw= placew.address_components;
        var ciudadw='';
        var provinciaw='';  
            for (var i = 0, componentw; componentw = componentsw[i]; i++) {
            console.log(componentw);
            if (componentw.types[0] == 'administrative_area_level_1') {
                provinciaw=componentw['long_name'];
                if(provinciaw!='Córdoba'){
                alertify.error('La Provincia Intermedia debe ser Córdoba '+ ' no ' + provinciaw);          
                document.getElementById('waypoints').value = '';
                document.getElementById('waypoints').focus();
                break;
                }
            }else if(componentw.types[0] == 'locality') {
                ciudadw = componentw['long_name'];
              realizaProceso(ciudadw);
              if(document.getElementById('resultado').innerText==0){
                alertify.error('La Localidad intermedia '+ ciudadw +' no se encuentra a nuestro alcance, analice redespacho');          
                document.getElementById('waypoints').value = '';
                document.getElementById('waypoints').focus();
                break;
                }
                document.getElementById('waypointsciudad').value = ciudadw;
                }  
             }
           }
        }); 

        //HASTA
        var inputend = document.getElementById('end');
        var autocompleteend = new google.maps.places.Autocomplete(inputend);
        autocompleteend.addListener('place_changed', function() {
        var placeend = autocompleteend.getPlace();
        if (placeend.address_components) {
        var componentsend= placeend.address_components;
        var ciudadend='';
        var provinciaend='';  
            for (var i = 0, componentend; componentend = componentsend[i]; i++) {
            console.log(componentend);
            if (componentend.types[0] == 'administrative_area_level_1') {
                provinciaend=componentend['long_name'];
                if(provinciaend!='Córdoba'){
                alertify.error('La Provincia de destino debe ser Córdoba ' + ' no ' + provinciaend);          
                document.getElementById('end').value = '';
                document.getElementById('end').focus();
                break;
                }
            }else if (componentend.types[0] == 'locality') {
                ciudadend = componentend['long_name'];
                realizaProceso(ciudadend);
                if(document.getElementById('resultado').innerText==0){
                alertify.error('La Localidad de destino '+ ciudadend +' no se encuentra a nuestro alcance, analice redespacho');          
                document.getElementById('end').value = '';
                document.getElementById('end').focus();
                break;
                }
                document.getElementById('endciudad').value = ciudadend;
                }  
             }
           }
        }); 
        
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: -31.4448988, lng: -64.177743}
 
        });
        directionsDisplay.setMap(map);

        document.getElementById('submit').addEventListener('click', function() {
          
          calculateAndDisplayRoute(directionsService, directionsDisplay);
        });
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        //VERIFICO SI LA LOCALIDAD TIENE TILDADO WEB QUE SON A LAS QUE LLEGAMOS CON CADDY
        var String = document.getElementById('endciudad').value;
        var Parts = String.split(",");
        var part1 = Parts[0]; // 123
        var part2 = Parts[1]; // 654321
//         alert (part2);
        
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        var dato1 = document.getElementById('waypoints').value;
//         for (var i = 0; i < checkboxArray.length; i++) {
          if (dato1 != '') {
          var  puntointermedio=1.5;
          var  puntointermediotxt='Si';  

            waypts.push({
              location: document.getElementById('waypoints').value + "," + document.getElementById('waypointsciudad').value,
              stopover: true
            });
          }else{
          var  puntointermedio=1;
          var  puntointermediotxt='No';  
          }
//         }
        
        directionsService.route({
          origin: document.getElementById('start').value + "," + document.getElementById('startciudad').value,
          destination: document.getElementById('end').value + "," + document.getElementById('endciudad').value,
          waypoints: waypts,
          
          optimizeWaypoints: true,
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
        
              summaryPanel.innerHTML += '<b>Ruta Segmento: ' + routeSegment +
                  '</b><br>Desde ';
              summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
              summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
              summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
              summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

              var dato = route.legs[i].duration.value;
//               window.location.href = window.location.href + "?viajar=si&" + "valor=" + dato;
//               $variable =$_GET['valor'];
//               $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
//               mysql_query($sql);
            
            }
            
            var summaryPanel = document.getElementById('directions-panel');
            
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            
            var ancho = document.getElementById('ancho').value;
            var largo = document.getElementById('largo').value;
            var alto = document.getElementById('alto').value;
            var dimensiones = ancho*largo*alto;
//             var res = totalDistance.toString().substr(0, 4);
            var res = totalDistance;
            var A=document.getElementById('startciudad').value;
            var B=document.getElementById('waypointsciudad').value;
            var C=document.getElementById('endciudad').value;

//             if(datoajax==0){  
//             var redespacho='*';
//             var redespacholeyenda='* ATENCION: REQUIERE REDESPACHO';  
//             var costo= 'Verificar';
//             }else{
            var redespacho='';
            var redespacholeyenda='';
            var varpeso=document.getElementById('peso').value;
              //DESDE ACA CALCULO EL VALOR X PESO DE LA ENCOMIENDA
//               PRIMERO CLASIFICO POR DIMENSIONES
              //DEFINO VARIABLES
            var costo=0;
            var pesoextra=0;
            var vxma=0.0015;//60% DEL VALOR MAS ALTO DE TABLA
            var vxmb=0.0020;//60% DEL VALOR MAS ALTO DE TABLA
            var vxmc=0.0026;//60% DEL VALOR MAS ALTO DE TABLA

     if(dimensiones<4860 && varpeso<2){
                  if(res<25){
                  costo=150;
                  var tarifa='1 | A';  
                  }else if(res<50){
                  costo=200;  
                  var tarifa='1 | B';  
                  }else if(res>51){
                  costo=250;
                  var tarifa='1 | C';  
                  }
               }else if(dimensiones<5460 && varpeso<4){
                  if(res<25){  
                  costo=180;
                  var tarifa='2 | A';  
  
                  }else if(res<50){
                  costo=240; 
                  var tarifa='2 | B';  

                  }else if(res>51){
                  costo=300; 
                  var tarifa='2 | C';  

                  }
               }else if(dimensiones<18375 && varpeso<10){
                if(res<25){  
                costo=220; 
                var tarifa='3 | A';  

                }else if(res<50){
                costo=295;  
                var tarifa='3 | B';  

                }else if(res>51){
                costo=365;    
                var tarifa='3 | C';  

                }
               }else if(dimensiones<22050 && varpeso<=15){ //tarifa 4
                if(res<25){  
                costo=250;  
                var tarifa='4 | A';  

                }else if(res<335){
                costo=335;  
                var tarifa='4 | B';  

                }else if(res>420){
                costo=420;    
                var tarifa='4 | C';  

                }
               }else if(dimensiones<42875 && varpeso<=20){ //TARIFA 5
                  if(res<25){  
                  costo=300;  
                  var tarifa='5 | A';  

                  }else if(res<335){
                  costo=400;  
                  var tarifa='5 | B';  

                  }else if(res>51){
                  costo=500;    
                  var tarifa='5 | C';  

                  }
               }else if(dimensiones<64000 && varpeso<=25){
                  if(res<25){  
                  costo=350; 
                  var tarifa='6 | A';  

                  }else if(res<50){
                  costo=465;  
                  var tarifa='6 | B';  

                  }else if(res>51){
                  costo=585;    
                  var tarifa='6 | C';  

                  }
               }else if(dimensiones<80000 && varpeso<=25){
                  if(res<25){  
                  costo=400;  
                  var tarifa='7 | A';  

                  }else if(res<50){
                  costo=535;  
                  var tarifa='7 | B';  

                  }else if(res>51){
                  costo=665;    
                  var tarifa='7 | C';  

                  }
               }else if(dimensiones<99000 && varpeso<=25){
                  if(res<25){  
                  costo=500;  
                  var tarifa='8 | A';  

                  }else if(res<50){
                  costo=665;  
                  var tarifa='8 | B';  

                  }else if(res>51){
                  costo=850;    
                  var tarifa='8 | C';  

                  }
                      
                 }else if(dimensiones<=99000 && varpeso>=25){
                   // UTILIZO LOS VALORES DE LA TARIFA 6 PORQUE AHI CAMBIA EL PESO
                     pesoextra = (varpeso - 25)*5;// variable de peso extra  
                     var pesoextratxt='Si | ' + (varpeso -25); 
                    if(res<25){
                        if((dimensiones*vxma)<250){
                          costo=250 + pesoextra;  
                          }else{
                          costo=(dimensiones*vxma) + pesoextra;
                          }
                       var tarifa='Extra Peso | A';  
                    }else if(res<50){
                          if((dimensiones*vxma)<465){
                            costo=465 + pesoextra;  
                            }else{
                            costo= (dimensiones*vxma) + pesoextra;
                            }
                     var tarifa='Extra Peso | B';  
                    }else if(res>51){
                          if((dimensiones*vxma)<585){
                            costo=585 + pesoextra;  
                            }else{
                            costo=(dimensiones*vxma) + pesoextra;
                            }
                     var tarifa='Extra Peso | C';  
                    }
                  }else if(dimensiones>99000 && varpeso<=25){
               // UTILIZO LOS VALORES DE LA TARIFA 8 PORQUE SUPERA EL MAXIMO DE DIMENSIONES DE TABLA
 
                    if(res<25){
                      costo=(dimensiones*vxma);  
                      if(costo<500){
                      costo=500;  
                      }
                      var tarifa='Extra Dimensiones | A';  
                      }else if(res<50){
                      costo=(dimensiones*vxmb);
                      if(costo<665){
                      costo=500;  
                      }
                      var tarifa='Extra Dimensiones | B';  
                      }else if(res>51){
                      costo=(dimensiones*vxmc);  
                      if(costo<850){
                      costo=850;  
                      }
                      var tarifa='Extra Dimensiones | C';  
                      }
                    
                  }else if(dimensiones>99000 && varpeso>=26){
                     pesoextra = (varpeso - 25)*5;// variable de peso extra  
                     var pesoextratxt='Si | ' + (varpeso -25); 
                            if(res<25){
                            costo= (dimensiones*vxma) + pesoextra;
                            var tarifa='Extra Total | A';  
                            }else if(res<50){
                            costo= (dimensiones*vxmb) + pesoextra;
                            var tarifa='Extra Total | B';  
                            }else if(res>51){
                            costo= (dimensiones*vxmc) + pesoextra;
                            var tarifa='Extra Total | C';  
                            }
                    }
                  
              
              
              var retiro=document.getElementById('seleccionretiro').value;
              if(retiro='Domicilio'){
              var varretiro=0;//ACA CARGO UN VALOR POR RETIRAR A DOMICILIO  
              }
          
            // SI HAY CAMBIO DE LOCALIDAD SE INCREMENTA UN VALOR FIJO 
            var a= document.getElementById('startciudad').value;
            var b= part1;
            var c= document.getElementById('waypointsciudad').value;
            
            if(puntointermedio!=1){ //EXISTE WAYPOINTS

              if(a===b && a===c && b===c){ 
                var incxcambioloc=0;
                var incxcambioloctxt='No';
                }else{
                var incxcambioloc=150;
                var incxcambioloctxt='Si | $ 150';   
                }
            }else{ // NO EXISTEN WAYPOINTS
                if(a==b){ 
                var incxcambioloc=0;
                var incxcambioloctxt='No';
                }else{
                var incxcambioloc=150;
                var incxcambioloctxt='Si | $ 150';   
               }
             }            

            var cantidad=document.getElementById('cantidad').value;
            var valordeclarado=document.getElementById('valordeclarado').value;

            if(valordeclarado<=50){
            var seguro=Number(50);  
            }else{
            var seguro=Number(valordeclarado*0.009);  
            }
            var segurotxt=(new Intl.NumberFormat("de-DE").format(valordeclarado*0.009));  

            var costo1=costo;
            //DESCUENTO X CANTIDAD
            if(cantidad>1){
              for (var i = 2; i <= cantidad; i++) {
              var descuento=costo-((costo*60)/100);
              costo1=costo1+descuento;
              }
            }else{
            var costo1=(costo*cantidad);
            }
//             var costototal=(costo1+incxcambioloc+seguro);
            var costototal=Number(costo1)+Number(incxcambioloc)+seguro;

            document.getElementById('costo').value=costo;
            document.getElementById('comienzo_calle').value=document.getElementById('start').value;
            document.getElementById('comienzo_ciudad').value=document.getElementById('startciudad').value;
            document.getElementById('final_calle').value=document.getElementById('end').value;
            document.getElementById('final_ciudad').value=document.getElementById('endciudad').value;
            document.getElementById('retiro_final').value=document.getElementById('retiro').value;
            
            var tarifacantidad=(new Intl.NumberFormat("de-DE").format(costo*cantidad));
            var formato=(new Intl.NumberFormat("de-DE").format(costototal*puntointermedio));
            var totaldescuento=(new Intl.NumberFormat("de-DE").format(costo-costo1));

            if(puntointermedio==1){
            var puntointermedioformato='';
            }else{
            var puntointermedioformato=(new Intl.NumberFormat("de-DE").format((costo1+incxcambioloc)/2));
            }
            
            
            document.getElementById('cambia_localidad').value=incxcambioloc;
            document.getElementById('punto_intermedio').value=puntointermedioformato;
            document.getElementById('requiere_redespacho').value=redespacho;

            var descripcion=document.getElementById('descripcion').value;
            
            summaryPanel.innerHTML += '<b> Descripción: ' + descripcion +'</br>';
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><b> Duración Total:';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos </br>';
            summaryPanel.innerHTML += '<b> Dimensiones:  ' + dimensiones/1000000 + ' mts.3 '; 
            summaryPanel.innerHTML += '| Peso (Max.:25kg):  ' + varpeso + ' Extra: ' + pesoextratxt + ' kg. </br>'; 
            summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Tarifa ' + tarifa + ' | Composición:  </br>';
            summaryPanel.innerHTML += '<b> Tarifa ' + tarifa +':  $ ' + costo + '</br>';
            summaryPanel.innerHTML += '<b> Seguro (Valor Declarado: $ '+ valordeclarado +'): $ ' + segurotxt +' </br>'; 
            summaryPanel.innerHTML += '<b> Cantidad:  ' + cantidad +'</br>'; 
            summaryPanel.innerHTML += '<b> Tarifa x ('+ cantidad + ') :  $ ' + tarifacantidad +'</br>'; 
            summaryPanel.innerHTML += '<b> Descuento x Cant.: $ ' + totaldescuento +'</br>';
            summaryPanel.innerHTML += '<b> Retiro:  ' + varretiro +'</br>';
            summaryPanel.innerHTML += '<b> Punto Intermedio:  ' + puntointermediotxt +' '+ puntointermedioformato +'</br>'; 
            summaryPanel.innerHTML += '<b> Cambia Localidad:  ' + incxcambioloctxt +'</br>'; 
            summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Costo Total: $ ' + formato +' </b></br>';    
            document.getElementById('tarifa').value=tarifa;
            document.getElementById('detalle').value=summaryPanel.innerHTML.text;
//             summaryPanel.innerHTML += '<br></br><b style="font-size:18px;color:red">' + redespacholeyenda + ' </b>';            
            } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap">
    </script>
  </body>
</html>