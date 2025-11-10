
var optimizar=false;

function encode_utf8(s) {
    return unescape(encodeURIComponent(s));
  }
  
$('#routes').click(function(){
    initMap_routes();    
}
);

$('#points').click(function(){
    var Recorrido=$('#recorrido').html();
    veo(Recorrido);
    $('#routes').css('display','block');
    $('#points').css('display','none');
    $('#route_header').css('display','none');    
});

var checkbox = document.getElementById('optimizar');
checkbox.addEventListener("change", validaCheckbox, false);

function validaCheckbox()
{
  var checked = checkbox.checked;
  if(checked){
      optimizar=true;
      initMap_routes();
      $('#optimizar_ok').css('display','inline-block');      
  }else{
    optimizar=false;
    initMap_routes();      
    $('#optimizar_ok').css('display','none');
  }
}

function initMap_routes() {
    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;
    var map = new google.maps.Map(document.getElementById('map'),{
      zoom: 7,
      center: {lat: -31.4448988, lng: -64.177743}
    });
    
    directionsDisplay.setMap(map);

    calculateAndDisplayRoute(directionsService, directionsDisplay,optimizar);

  }

  function calculateAndDisplayRoute(directionsService, directionsDisplay,optimizar) {
    var waypts = [];
    var segmento=[];
    var id_hdr=[];
    var entrega=[];
    var duration=[];

    $.ajax({
        data: {'Routes':1},
        type:"POST",
        url:"Mapas/php/routes.php",
    
        success: function(response)
        {
            var jsonData= JSON.parse(response);            

            for (var i = 0; i < jsonData.dato.length; i++) {    
                    waypts.push({
                    location: jsonData.dato[i].coordenadas,
                    stopover: true
                    });

                    id_hdr.push(jsonData.dato[i].idHojaderuta);
                    entrega.push(jsonData.dato[i].Entrega);
            }
            
            directionsService.route({                
                origin: 'Justiniano Posse 1236, Córdoba Argentina',
                destination:'Reconquista 4968, Córdoba Argentina', 
                waypoints: waypts,
                optimizeWaypoints: optimizar,
                travelMode: 'DRIVING',
                drivingOptions: {
                    departureTime: new Date(Date.now()),  // for the time N milliseconds from now.
                    trafficModel: 'optimistic'
                  }
                }, 
                function(response, status) {
                if (status === 'OK') {
                    directionsDisplay.setDirections(response);
                    var route = response.routes[0];
                    // var summaryPanel = document.getElementById('directions-panel');
                    var summaryPanel=$('#route_km');
                    summaryPanel.innerHTML = '';
                    // For each route, display summary information.
                    var totalDistance = 0;
                    var totalDuration = 0;    

                    for (var i = 0; i < route.legs.length; i++) {
                    var routeSegment = i + 1;
                    totalDistance += parseFloat(route.legs[i].distance.text);
                    totalDuration += route.legs[i].duration.value;
                    
                    // var id=route.legs[i].steps[i];
                    
                    var horas=Math.round((totalDuration /60))+1;
                    
                    // console.log('id',route.legs[i].end_address+'->'+routeSegment+' Duration '+route.legs[i].duration.text+' Distance '+route.legs[i].distance.text);
                    var minutos=Math.round((route.legs[i].duration.value /60)+1);
                    
                    var horas=Math.round((route.legs[i].duration.value /60))+1;
                    var horas1=Math.floor(horas/60);
                    var minutos=((horas-(horas1*60)));
                    
                    var currentDateObj = new Date();
                    var numberOfMlSeconds = currentDateObj.getTime();
                    var addMlSeconds = 60 * route.legs[i].duration.value;

                    if(i==0){
                    var newDateObj = new Date(numberOfMlSeconds + addMlSeconds);
                    }else{
                     var newDateObj = new Date(numberOfMlSeconds + addMlSeconds);    
                     var numberOfMlSeconds = newDateObj.getTime();
                     var newDateObj = new Date(numberOfMlSeconds + addMlSeconds);   
                    }

                    duration.push(route.legs[i].duration.value);
                    segmento.push(route.legs[i].end_address);
                    // id_hdr.push(route.legs[i].waypoint_order);
                }
                
                console.log('pato',duration);
                console.log('id hdr',id_hdr);
                console.log('response',route.waypoint_order);

                // duration.push()

                $('#optimizar_ok').click(function(){

                    $.ajax({
                        data: {'Routes_order':1,'waypoint_order':route.waypoint_order,'Duration':duration,'segmento':segmento,'id_hdr':id_hdr,'entrega':entrega},
                        type:"POST",
                        url:"Mapas/php/routes.php",
                    
                        success: function(response)
                        {
                            var jsonData= JSON.parse(response);  
                            
                            if(jsonData.success==1){
                            
                                $.NotificationApp.send("Registro Actualizado !","Se ha actualizado el Recorrido.","bottom-right","#FFFFFF","success");
                            
                            }
                        }
                    });

                })

                    // var summaryPanel = document.getElementById('directions-panel');
                    // var summaryPanel=$('#route_km');

                    var horas=Math.round((totalDuration /60))+1;
                    var horas1=Math.floor(horas/60);
                    var minutos=((horas-(horas1*60)));
                    var res = totalDistance.toFixed(2);
                    $('#route_km').html('<b> Distancia Total: ' + res + ' km.</b>');
                    $('#route_time').html('<b> Duracion Total: '+horas1 +' Horas ' + minutos + ' minutos' + '</b>');
                    // summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><br><b> Duracion Total: ';
                    // summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos' + '</b>';

                    $('#routes').css('display','none');
                    $('#points').css('display','block');
                    $('#route_header').css('display','inline');
                

                } else {
                    $('#alert_route').css('display','block');
                    $('#alert_route_header').html('El armado de la ruta falló');
                    $('#alert_route_text').html(status);
                    
                    var Recorrido=$('#recorrido').html();
                    veo(Recorrido);                
                    
                }
                });
  

        }
    });
  }
    

