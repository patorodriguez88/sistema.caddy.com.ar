let map;

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

              for (var i = 0; i < objeto_json.length; i ++) {
                var latlong = objeto_json[i][2].split(',');
                myLatLng = {
                    lat: Number(latlong[0]),
                    lng: Number(latlong[1])
                };

                markers[i] = new google.maps.Marker({
                  position: myLatLng,
                  map: map,
                  title: objeto_json[i][0]
                });
               
                var contentString = '<h1 id="firstHeading" class="firstHeading">' +
                    objeto_json[i][0] + '</h1>'+ '<div id="bodyContent">'+
                    '<p><b>' + objeto_json[i][0] + '</b></p><p>' + objeto_json[i][1] +
                    '</p></div>';

                markers[i].infoWindow = new google.maps.InfoWindow({
                  content: contentString
                });

                google.maps.event.addListener(markers[i], 'click', function(){     
                  if(infowindowActivo){
                    infowindowActivo.close();
                  }                 
                  infowindowActivo = this.infoWindow;
                  infowindowActivo.open(map, this);
                });
              }
            }
          };
         
          var myLatLng = {
              lat: 40.417079,
              lng: -3.703892
          };

          var map = new google.maps.Map(divMapa,{
            zoom: 15,
            center: myLatLng
          });

          var tipo = <?php echo $ct; ?>;
          var url = "marcadores.php?tipo="+tipo;
          xhttp.open("POST", url, true);
          xhttp.send(); 


      } 
     
