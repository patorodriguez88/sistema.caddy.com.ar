        function initMap() {
          var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(-31.4448988, -64.177743),
          zoom: 10
        });
        var infoWindow = new google.maps.InfoWindow;
          // Change this depending on the name of your PHP or XML file
          downloadUrl('Mapas/php/datos_maparecorridos.php', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
            Array.prototype.forEach.call(markers, function(markerElem) {
              var Origen = markerElem.getAttribute('Origen');
              var Color = markerElem.getAttribute('Color');
              var name = markerElem.getAttribute('name');
              var address = markerElem.getAttribute('address');
              var recorrido = markerElem.getAttribute('recorrido');
              var Precio = markerElem.getAttribute('Precio');
              var point = new google.maps.LatLng(
                  parseFloat(markerElem.getAttribute('lat')),
                  parseFloat(markerElem.getAttribute('lng')));
              var infowincontent = document.createElement('div');
              
              var strong = document.createElement('strong');
              strong.textContent = 'Recorrido '+recorrido
              infowincontent.appendChild(strong);
              infowincontent.appendChild(document.createElement('br'));

              var origen = document.createElement('text');
              origen.textContent = 'Origen:'+ Origen
              infowincontent.appendChild(origen);
              infowincontent.appendChild(document.createElement('br'));


              var Name = document.createElement('text');
              Name.textContent = 'Destino:'+ name
              infowincontent.appendChild(Name);
              infowincontent.appendChild(document.createElement('br'));

              var text = document.createElement('text');
              text.textContent = address
              infowincontent.appendChild(text);
              infowincontent.appendChild(document.createElement('br'));

              var precio = document.createElement('precio');
              precio.textContent = 'Total Envío: $ '+Precio
              infowincontent.appendChild(precio);
              
              if(Color==''){
              Color='090e0f';  
              }
              function pinSymbol(color) {
                  return {
                   path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                  fillColor: '#'+color,
                  fillOpacity: 1,
                  strokeColor: '#000',
                  strokeWeight: 0,
                  scale: 1,                  
                  };
                }
              var iconBase= pinSymbol(Color);


            //   var posicion=0;
            //   var iconBase = {
            //     url: "http://maps.google.com/mapfiles/ms/icons/blue.png",
            //     scaledSize: new google.maps.Size(50, 50), // scaled size
            //     Origin: new google.maps.Point(0,0), // Origin
            //     anchor: new google.maps.Point(0, 0), // anchor
            //     labelOrigin: new google.maps.Point(24, 15),
            //       label: {
            //       color: Color,
            //       fontWeight: 'bold',
            //       text:posicion,
            //       },
            //     };




              var marker = new google.maps.Marker({
                map: map,
                position: point,
                label: '0',
                icon: iconBase,
                animation: google.maps.Animation.DROP,
              });
              marker.addListener('click', function() {
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
              });
            });
          });
        }
      function downloadUrl(url, callback) {
        var request = window.ActiveXObject ?
            new ActiveXObject('Microsoft.XMLHTTP') :
            new XMLHttpRequest;
            request.onreadystatechange = function() {
          if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request, request.status);
          }
        };
        request.open('GET', url, true);
        request.send(null);
      }
      function doNothing() {}
