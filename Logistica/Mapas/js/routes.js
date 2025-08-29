
function ordenobase(direccion,orden,minutos){
    // new_direccion=utf8_decode(direccion);
    var dato={
      "Direccion": direccion,
      "Orden": orden,
      "Minutos":minutos
      };
      $.ajax({
      async: false,  
      data: dato,
      url:'ordenar.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
          success: function(response)
          {
//               alert('ok');
//                 var jsonData = JSON.parse(response);
//                 if (jsonData.success == "1")
//                 {
//                 }
//                 else
//                 {
//                     alert('Invalid Credentials!');
//                 }
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
    // var checkboxArray = document.getElementById('waypoints');
    // console.log('ver','1');
    $.ajax({
        // async: false,  
        data: {'Routes':1,'Rec':80},
        type:"POST",
        url:"Mapas/php/routes.php",
            success: function(response)
            {
            var jsonData= JSON.parse(response);

        //         var id= checkboxArray.split(',');

        for (var i = 0; i < jsonData.datos.length; i++) {    
            console.log('ver datos',jsonData.datos[i].Localizacion);
                waypts.push({
                location: jsonData.dato[i].Localizacion,
                stopover: true
                });
        }
            // for (var i = 0; i < checkboxArray.length; i++) {
            // if (checkboxArray.options[i].selected) {
            //     waypts.push({
            //     location: checkboxArray[i].value,
            //     stopover: true
            //     });
            // }
            // }
        directionsService.route({
            // origin: document.getElementById('start').value,
            origin: 'Justiniano Posse 1236, Córdoba Argentina',
            destination:'Justiniano Posse 1236, Córdoba Argentina', 
            // destination: document.getElementById('end').value,
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
                var id=checkboxArray[i].value;
                var datoid=id.split(',');
                

                var horas=Math.round((totalDuration /60))+1;
                // ordenobase(datoid[0],routeSegment,horas);

                // console.log('minutos',horas);  
                summaryPanel.innerHTML += '<b>Ruta Segmento: ' + routeSegment +'</b><br>Desde ';
                summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
                summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
                summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
                summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

                var dato = route.legs[i].duration.value;
                
                }
                
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
    });
}