<?
session_start();
// include("../ConexionBD.php");
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangularcopia",$conexion);  

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script src="js/miscript.js"></script>

    <meta charset="utf-8">
    <title>Mapa Hoja de Ruta Triangular</title>
  </head>
  <body>
    <div id="map"></div>
    <div id="right-panel">
    <div>
    <b>Comienzo:</b>
  <input type="tet" id="start" value="Reconquista 4986, Córdoba, Argentina">
    <br>
       <br>
    <b>Final:</b>
  <input type="tet" id="end" value="<? echo $_GET[d];?>">
      <br>
      <input type="submit" id='submit' >
    </div>
    <div id="directions-panel"></div>
    </div>
    <script>
var a='Reconquista 4986, Cordoba';
var b=document.getElementById('end').value;
}
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
//         document.getElementById('submit').addEventListener('click', function() {
          calculateAndDisplayRoute(directionsService, directionsDisplay);
//         });
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {

        directionsService.route({
          origin: a,
          destination: b,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            // For each route, display summary information.
            var totalDistance = 0;
            var totalDuration = 0;
            
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
              var dato = route.legs[i].duration.value;
              var km=route.legs[i].distance.text;
              var tiempo=route.legs[i].duration.text;
            }
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            var res = totalDistance;
            var id=4;
            distancia(id,km,tiempo);
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