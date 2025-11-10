<?
session_start();
include("../ConexionBD.php");
// if($_GET['viajar']=='si'){
//               $variable =$_GET['valor[]'];
//               $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
//               mysql_query($sql);
// }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <script type="text/javascript" src="../../js/jquery.js"></script>

    <meta charset="utf-8">
    <title>Mapa Hoja de Ruta Triangular</title>
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
        float: left;
        width: 70%;
        height: 100%;
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
        height: 500px;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <div id="right-panel">
    <div>
    <b>Comienzo:</b>
    <select id="start">
      <option value="Reconquista 4986, Córdoba, Argentina">Caddy</option>
<!--       <option value="Boston, MA">Boston, MA</option>
      <option value="New York, NY">New York, NY</option>
      <option value="Miami, FL">Miami, FL</option> -->
    </select>
    <br>
    <b>Waypoints:</b> <br>
    <i>(Ctrl+Click or Cmd+Click for multiple selection)</i> <br>
<?
  
$Recorrido=$_SESSION['Recorrido'];
$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion ";
$result = mysql_query($query);
      
echo "<select multiple id='waypoints'>";
while ($row = @mysql_fetch_array($result)){
// $localiza=$row[Localizacion].",".$row[Ciudad];  
// echo "<input type='hidden' value='$localiza' id='waypoints[]'>";
  echo "<option value='$row[Localizacion],$row[Ciudad]'>$row[Localizacion]</option>";

}
  echo "</select>";
?>
       <br>
    <b>Final:</b>
<?
$query="SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion";
$result = mysql_query($query);
echo"<select id='end'>";
// echo"<option value='Justiniano Posse 1236, Córdoba, Argentina'>Triangular S.A.</option>";
while ($row = @mysql_fetch_array($result)){
echo "<option value='$row[Localizacion].",".$row[Ciudad]'>$row[Localizacion]</option>";
}
echo "</select>";
?>
      <br>
      <input type="submit" id="submit">
      <input type="submit" id="ordenar" value="Ordenar Sistema">
    </div>
    <div id="directions-panel"></div>
    </div>
    <script>
      function distancia(id,km,tiempo){
        var dato={
        "id": id,
        "km": km,
        "tiempo":tiempo
        };
        $.ajax({
        data: dato,
        url:'Calculardistancia.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
            success: function(response)
            {
//               alert('ok');
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
                  alert('cargado ok');
                }
                else
                {
                    alert('Invalid Credentials!');
                }
           }
      }); 
    }

        

      function initMap() {
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
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
//         var id= checkboxArray.split(',');
        
        for (var i = 0; i < checkboxArray.length; i++) {
          if (checkboxArray.options[i].selected) {
            waypts.push({
              location: checkboxArray[i].value,
              stopover: true
            });
          }
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
            var ordendir=[];
            var ordenid=[];
            
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
              var id=checkboxArray[i].value;
//               var datoid=id.split(',');
//               ordenobase(datoid[0],routeSegment);
//               alert(datoid[0]);
              //               console.log(route.legs[i]);
              
//               console.log(route.legs[i].end_address+' -> '+routeSegment);
              summaryPanel.innerHTML += '<b>Ruta Segmento: ' + routeSegment +'</b><br>Desde ';
              summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
              summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
              summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
              summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

              var dato = route.legs[i].duration.value;
            
            }
            
//              $('#ordenar').click(function(){
//                for(var i=0;i<route.legs.length;i++){
//                  var routeSegment = i + 1;
//                totalDistance += parseFloat(route.legs[i].distance.text);
//               totalDuration += route.legs[i].duration.value;
//               var id=checkboxArray[i].value;
//               var ordendir=route.legs[i].end_address;
//               var ordenid=routeSegment;
//                  console.log(ordendir+'->'+ordenid);
//                }
//              alert(ordendir+'->'+ordenid); 
//             });
            
            var summaryPanel = document.getElementById('directions-panel');
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            
            
//             var res = totalDistance.toString().substr(0, 3);
            var res = totalDistance;
         
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><br><b> Duracion Total: ';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos' + '</b>';
         
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
  </body>
</html>