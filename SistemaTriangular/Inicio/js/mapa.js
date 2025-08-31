function initMap() {
    // Map and directions objects
  var infoWindow = new google.maps.InfoWindow;
  var infoWindowv = new google.maps.InfoWindow;
  var service = new google.maps.DirectionsService();
  var directions = new google.maps.DirectionsRenderer({suppressMarkers: true});



    var element=document.getElementById("map");
    var options= {
    zoom: 11,
    center: {lat: -31.4448988, lng: -64.177743 },
    travelMode:'DRIVING'
    };
  
    // Change this depending on the name of your PHP or XML file
          downloadUrl('php/mapaseguimiento.php', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
            var i=0;
            Array.prototype.forEach.call(markers, function(markerElem) {
            var latv=parseFloat(markerElem.getAttribute('latv'));
            var lngv=parseFloat(markerElem.getAttribute('lngv'));
            var lat=parseFloat(markerElem.getAttribute('lat'));
            var lng=parseFloat(markerElem.getAttribute('lng'));          
            var origen=new google.maps.LatLng(latv,lngv);
            var destino=new google.maps.LatLng(lat,lng);  
            var map = new google.maps.Map(element, options);

            directions.setMap(map);

           // Start/Finish icons
           var icons = {
            start: new google.maps.MarkerImage(
             // URL
             '../images/favicon/truck.png',
             // (width,height)
             new google.maps.Size( 54, 42 ),
             // The origin point (x,y)
             new google.maps.Point( 0, 0 ),
             // The anchor point (x,y)
             new google.maps.Point( 22, 32 ),

            ),
            animation: google.maps.Animation.BOUNCE,
            
            end: new google.maps.MarkerImage(
             // URL
             '../images/favicon/iconverde.png',
             // (width,height)
             new google.maps.Size( 54, 42 ),
             // The origin point (x,y)
             new google.maps.Point( 0, 0 ),
             // The anchor point (x,y)
             new google.maps.Point( 22, 32 )
            ),
             way: new google.maps.MarkerImage(
             // URL
             '../images/favicon/iconverde.png',
             // (width,height)
             new google.maps.Size( 54, 42 ),
             // The origin point (x,y)
             new google.maps.Point( 0, 0 ),
             // The anchor point (x,y)
             new google.maps.Point( 22, 32 )
            )
           };
            const waypts = [];
            const checkboxArray = destino;

            for (let i = 0; i < checkboxArray.length; i++) {
              if (checkboxArray.options[i].selected) {
                waypts.push({
                  location: checkboxArray[i].value,
                  stopover: true,
                });
              }  
            }
            service.route({ 
              origin: 'Justiniano Posse 1236, Cordoba',
              destination: 'Reconquista 2479, Cordoba',
              waypoints: waypts,
              optimizeWaypoints: true,
              provideRouteAlternatives: true,
              travelMode: 'DRIVING'
            }, 

            function( response, status ) {
            if ( status == "OK" ) {
            directions.setDirections( response );
            const route = response.routes[0];
            
            var leg = response.routes[0].legs[0];
            makeMarker( leg.way_location, icons.way, "title");  
            makeMarker( leg.start_location, icons.start, "title", icons.animation );
            makeMarker( leg.end_location, icons.end, "Hola" );
            }

            });
            function makeMarker( position, icon, title, animation ) {
             
              new google.maps.Marker({
              position: position,
              map: map,
              icon: icon,
              title: title,
              animation: animation 
              });
            }
          })
       i++;
      });

// var infowincontent = document.createElement('div');
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