function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var a= 'Reconquista 4986,Córdoba, Argentina';
        var b= document.getElementById('start').value;
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
            document.getElementById('kilometros').value = res;
            
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }