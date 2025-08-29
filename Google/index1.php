<!DOCTYPE html>
<html>
  <head>
    <title>Mapa Mptril</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
     #map {
        height: 100%;
        width: 100%;
        border: #000000 solid 0px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <h1>Mis mapas</h1>
    <button onclick="btnOnClick()">SEMAFORO</button>
    <div id="map"></div>
    <script>
        
       
function initMap() {
    var divMapa = document.getElementById('map');
    var xhttp;
    var resultado = [];
    var markers = [];
    var infowindowActivo = false;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
    
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        resultado = xhttp.responseText;
        var objeto_json = JSON.parse(resultado);
//         console.log(objeto_json.data.length);
        for (var i = 0; i < objeto_json.data.length; i++) {
                var latlong = objeto_json.data[i].coordenadas.split(',');
                myLatLng = {
                    lat: Number(latlong[0]),
                    lng: Number(latlong[1])
                };
          
                markers[i] = new google.maps.Marker({
                  position: myLatLng,
                  map: map,
                  title: objeto_json.data[i].nombrecliente
                  
                });
               
                var contentString = '<h1 id="firstHeading" class="firstHeading">' +
                    objeto_json.data[i].nombrecliente + '</h1>'+ '<div id="bodyContent">'+
                    '<p><b>' + objeto_json.data[i].nombrecliente + '</b></p><p>' + objeto_json.data[i].Direccion +
                    '</p></div>';

                markers[i].infoWindow = new google.maps.InfoWindow({
                  content: contentString
                });
                  var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/blue.png",
                scaledSize: new google.maps.Size(40, 40), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
                labelOrigin: new google.maps.Point(19, 15),
                  label: {
                  color: 'white',
                  fontWeight: 'bold',
                  text:'',
                  },
                };


                google.maps.event.addListener(markers[i], 'click', function(){     
                  if(infowindowActivo){
                    infowindowActivo.close();
                  }                 
                  infowindowActivo = this.infoWindow;
                  infowindowActivo.open(map, this);
                });

        }
        
      }
    }
    
     var myLatLng = {
              lat: -31.4448988,
              lng: -64.177743
          };


    var url = "marcadores.php";
    xhttp.open("POST", url, true);
    xhttp.send(); 
  
  map = new google.maps.Map(document.getElementById("map"), {
    center: new google.maps.LatLng(-31.4448988, -64.177743),
    zoom: 10,
  });
}

    </script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap&libraries=&v=weekly"
      async></script>
  </body>
</html>

