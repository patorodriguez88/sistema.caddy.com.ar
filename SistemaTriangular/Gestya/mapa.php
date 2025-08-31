<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <meta charset="utf-8">	
    <style>
       /* Set the size of the div element that contains the map */
        #map {
          height: 100%;
          width: 100%;
          border: #000000 solid 1px;
          border:0px;
          z-index:0;
          position:absolute;
        }
    </style>
  </head>
  <body>
    <!--The div element for the map -->
    <div id="map"></div>
    <script>

      // Initialize and add the map
    function initMap() {
    var dominio= '<? echo $_GET['pat'];?>';
    var dato={
        "Dominio": dominio,  
        };
        
        $.ajax({
        data: dato,
        url:'Request.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (respuesta) {
          var jsonData = JSON.parse(respuesta);
          if (jsonData.success == "1")
          {
          var latitud=Number(jsonData.latitud);
          var longitud=Number(jsonData.longitud); 
          
          var uluru = {
               lat: latitud, 
               lng: longitud
            }; 
        // The map, centered at Uluru
          var map = new google.maps.Map(
          document.getElementById('map'), {zoom: 12, center: uluru});
        // The marker, positioned at Uluru
          var marker = new google.maps.Marker({position: uluru, map: map});
          }
        }
      });
    }
    </script>
    <!--Load the API from the specified URL
    * The async attribute allows the browser to render the page while the API loads
    * The key parameter will contain your own API key (which is not needed for this tutorial)
    * The callback parameter executes the initMap() function
    -->
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
  </body>
</html>  