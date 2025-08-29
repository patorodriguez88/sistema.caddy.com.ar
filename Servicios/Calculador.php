<?
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    
   <title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />

<style>  
  #submit{
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
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
/*       html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }*/
      #map { 
        height: 800px;
        float: right;
        width: 780px;
/*         margin-left:305px; */
      }
      #right-panel {
        margin: 20px;
        border-width: 2px;
        width: 20%;
        height: 400px;
        float: left;
        text-align: left;
        padding-top: 0;
      }
      #directions-panel {
        margin-top: 10px;
        background-color: #AED6F1; /*#FFEE77;*/
        padding: 10px;
        overflow: scroll;
        height: 300px;
      }
    </style>
  </head>
  <body>
 <?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 

echo "</div>";
echo  "<div id='principal'>";    


    
    ?>
    <form  class='login' method='POST' action='' style='width:270px;float:left;'>
    <titulo>Cotizador Caddy:</titulo>
      <div>
        <hr/>
      </div>
      
      <div><label>Comienzo:</label><input type="text" name='comienzo' id="start" style='width:250px;' value="<? echo $_GET[Comienzo];?>" placeholder='Direccion, Ciudad'></div>
<?
$Ordenar1="SELECT * FROM TransClientes WHERE Fecha='2019-06-07' AND Recorrido='8' LIMIT 14";
$Stock=mysql_query($Ordenar1);
  while($row = mysql_fetch_array($Stock)){
echo "<input type='text' id='waypoints' value='$row[DomicilioDestino].",".$row[LocalidadDestino]' >";
  
  }
      ?>
      <div><label>Final:</label><input type="text" name='final' id="end" value="<? echo $_GET[end];?>" style='width:250px;' placeholder='Direccion, Ciudad'></div>
      <titulo>Dimensiones:</titulo>
      <div><hr/></div>
      <div><label>Ancho:</label><input type="number" id="ancho" style='width:50px;' placeholder='Cm'></div>
      <div><label>Largo:</label><input type="number" id="largo" style='width:50px;' placeholder='Cm'></div>
      <div><label>Alto:</label><input type="number"  id="alto" style='width:50px;' placeholder='Cm'></div>
      <div><input value='Aceptar' id="submit" readonly></div>
    <div id="directions-panel"></div>
  </form>
 <div id='map'></div>
    </div>
    </div>
    </div>
</div>


      <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: -31.4448988, lng: -64.177743}
 
        });
        directionsDisplay.setMap(map);

//         document.getElementById('submit').addEventListener('click', function() {
          calculateAndDisplayRoute(directionsService, directionsDisplay);
//         });
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        for (var i = 0; i < checkboxArray.length; i++) {
            waypts.push({
              location: checkboxArray[i].value,
              stopover: true
            });
        }

        directionsService.route({
          origin: document.getElementById('start').value,
          destination: document.getElementById('end').value,
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
//               <? 
//               $variable =$_GET['valor'];
//               $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
//               mysql_query($sql);
//               ?>
            
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
            var costo= res * <? echo $_SESSION['PrecioKm'];?>;
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><br><b> Duracion Total: ';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos </b><br>';
            summaryPanel.innerHTML += '<br></br><b> Dimensiones:  ' + dimensiones + ' </b>'; 
            
            summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Costo Total: $ ' + costo + ' </b>';            
         
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
//  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBCJKhy2hH628zG2uwaLmEnqpNEADVh1OI&callback=initMap">
    </script>
  </body>
</html>