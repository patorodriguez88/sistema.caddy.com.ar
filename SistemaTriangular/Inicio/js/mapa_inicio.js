$("#full-width-modal_order").on('show.bs.modal', function(e) {
    let triggerLink = $(e.relatedTarget);
    let id = triggerLink[0].dataset['id'];
    // let rec=triggerLink[0].dataset['rec'];
    
    // id=$('#id_logistica').val(id);
console.log('id',id);
    initMap_inicio(id);
});

function initMap_inicio(id) {

    
    
        //ICONO DE COLORES
        function pinSymbol(color) {
            return {
            path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',                  
            fillColor: '#'+color,
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            strokeWeight: 1,
            scale: 1,
            labelOrigin: new google.maps.Point(0, -29),
            };
          } 
          //ORDEN DENTRO DEL ICONO
          function pinLabel(posicion){
              return{                  
                  color: 'white',
                  fontWeight: 'bold',
                  text:posicion                  
              };
          }

    
    const map = new google.maps.Map(document.getElementById("map_inicio"), {
        zoom: 9,
        center: { lat: -31.407295723281997, lng:  -64.18836518176542 },
        mapTypeId: "terrain",

      });
      const flightPlanCoordinates=[];
      const markers=[];

    $.ajax({        
        data:{'Posiciones':1,'id':id},
        url:'../Gestya/Posiciones.php',
        type:'post',
        success: function (respuesta) {
          var jsonData = JSON.parse(respuesta);
            
          for(var i=0;i<jsonData.lat.length;i++){
            flightPlanCoordinates.push({
                lat: Number(jsonData.lat[i]),
                lng: Number(jsonData.lng[i])
              });
          }

          $.ajax({        
            data:{'id':id},
            url:'../Logistica/Mapas/php/datos_controlrecorridos.php',
            type:'post',
            success: function (respuesta) {
            var jsonDataMarkers = JSON.parse(respuesta);

            for(var i=0;i<jsonDataMarkers.lat.length;i++){
                var colorrec=jsonDataMarkers.Color[i];
                var myLatLng = ({
                    lat: Number(jsonDataMarkers.lat[i]),
                    lng: Number(jsonDataMarkers.lng[i])
                  });
                  
                var icono=[];
                if(jsonDataMarkers.Posicion1[i]==1){
                    icono[i]=pinSymbol('68FA13');
                    }else if(jsonDataMarkers.Posicion1[i]==jsonDataMarkers.lat.length){
                    icono[i]=pinSymbol('FA1332');    
                    }else{
                    icono[i]=pinSymbol(jsonDataMarkers.Color[i],0);        
                    }
                
                  var marker = new google.maps.Marker({
                    position: myLatLng,
                    map,
                    label: pinLabel(jsonDataMarkers.Posicion1[i]),
                    title: jsonDataMarkers.Name[i]+' Orden Original: '+jsonDataMarkers.Posicion[i],
                    icon: icono[i]
                  });
    
              }
              $('#header_title_map').html(` <span class="mdi mdi-map-marker" style="color:#${colorrec}"><i class="header-title mb-3 text-muted">  MARKERS </i></span> <span class="mdi mdi-map-marker" style="color:#68FA13"><i class="header-title mb-3 text-muted"> START </i></span> <span class="mdi mdi-map-marker" style="color:#FA1332"> <i class="header-title mb-3 text-muted"> END</i></span>`);                
            }
            });

          const flightPath = new google.maps.Polyline({
            path: flightPlanCoordinates,
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 2,
          });


          flightPath.setMap(map);
          if(jsonData.Recorrido!=null){
          $('#header_flota').html('Recorrido Gestya '+jsonData.Recorrido+' Desde '+jsonData.Desde+' Hasta '+jsonData.Hasta);
          }
        }
      });
  }

window.initMap_inicio = initMap_inicio;