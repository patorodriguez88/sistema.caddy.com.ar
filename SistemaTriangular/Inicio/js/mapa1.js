function initMap() {
  const directionsService = new google.maps.DirectionsService();
  const directionsRenderer = new google.maps.DirectionsRenderer();
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 6,
    center: { lat: -31.4448988, lng: -64.177743 },
  });

  calculateAndDisplayRoute(directionsService, directionsRenderer);
directionsRenderer.setMap(map);
}

function calculateAndDisplayRoute(directionsService, directionsRenderer) {

 $.ajax({
      data:{'Entregas':1},
      url:'php/mapaseguimiento.php',
      type:'post',
        beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
        },
      success: function(response)
       {
      var jsonData = JSON.parse(response);
  var waypts = response;
//      var waypts = [{ location: 
//                 { lat: -31.4027024, lng: -64.1959444 },
//                 stopover: true },
//                { location: 
//                 { lat: -31.397775, lng: -64.1555288 }, 
//                 stopover: true },
//                { location: 
//                 { lat: -31.3983146, lng: -64.251513 },
//                 stopover: true }];

    directionsService.route(
    {
      origin: 'justiniano posse 1235, Cordoba',
      destination:'andres lamas 2489, Cordoba',
      waypoints: waypts,
      optimizeWaypoints: true,
      travelMode: google.maps.TravelMode.DRIVING,
    },
    (response, status) => {
      if (status === "OK" && response) {
        directionsRenderer.setDirections(response);
        const route = response.routes[0];
//         const summaryPanel = document.getElementById("directions-panel");
//         summaryPanel.innerHTML = "";

        // For each route, display summary information.
//         for (let i = 0; i < route.legs.length; i++) {
//           const routeSegment = i + 1;
//           summaryPanel.innerHTML +=
//             "<b>Route Segment: " + routeSegment + "</b><br>";
//           summaryPanel.innerHTML += route.legs[i].start_address + " to ";
//           summaryPanel.innerHTML += route.legs[i].end_address + "<br>";
//           summaryPanel.innerHTML += route.legs[i].distance.text + "<br><br>";
//         }
      } else {
        window.alert("Directions request failed due to " + status);
      }
    }
  );
    }
  });
  
}
            
