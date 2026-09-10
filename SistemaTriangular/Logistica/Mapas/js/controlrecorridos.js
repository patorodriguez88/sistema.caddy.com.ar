// ===== Repartidor EN VIVO en el mapa de la orden abierta (solo HojaDeRuta2) =====
// Se activa solo si la pagina prende window.HDR2_LIVE_CHOFER. Poll cada 30 s a
// Mapas/php/ubicacion_orden.php (fuente: UbicacionRepartidor, la misma que
// "Repartidores en Vivo").
var _choferVivoInterval = null;
var _choferVivoMarker = null;
var _choferVivoInfo = null;

function _choferVivoLimpiar() {
    if (_choferVivoInterval) {
        clearInterval(_choferVivoInterval);
        _choferVivoInterval = null;
    }
    if (_choferVivoMarker) {
        _choferVivoMarker.setMap(null);
        _choferVivoMarker = null;
    }
    if (_choferVivoInfo) {
        _choferVivoInfo.close();
        _choferVivoInfo = null;
    }
}

function _choferVivoHace(mins) {
    if (mins === null || mins === undefined) return 'sin senal';
    if (mins < 1) return 'recien';
    if (mins < 60) return 'hace ' + mins + ' min';
    var h = Math.floor(mins / 60);
    return 'hace ' + h + ' h ' + (mins % 60) + ' min';
}

// minutos desde la ultima senal, calculado en el cliente (igual criterio que
// Mapas/js/repartidores_live.js) para no depender del timezone del server.
function _choferVivoMinutos(r) {
    if (r && r.timestamp) {
        var t = new Date(String(r.timestamp).replace(' ', 'T')).getTime();
        if (!isNaN(t)) return Math.floor((Date.now() - t) / 60000);
    }
    return r && r.minutos !== null && r.minutos !== undefined ? r.minutos : null;
}

function _choferVivoActualizar(idLogistica, map) {
    $.ajax({
        url: 'Mapas/php/ubicacion_orden.php',
        data: { id: idLogistica },
        type: 'post',
        dataType: 'json',
        success: function (r) {
            if (!r || r.success !== 1 || r.tienePos !== 1) return;

            var pos = { lat: Number(r.lat), lng: Number(r.lng) };
            var mins = _choferVivoMinutos(r);
            var viejo = mins !== null && mins >= 10;
            var icon = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 9,
                fillColor: viejo ? '#98a6ad' : '#E24F30',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 3,
            };
            var titulo =
                (r.nombre || r.usuario || 'Repartidor') +
                ' - ' + _choferVivoHace(mins);

            if (!_choferVivoMarker) {
                _choferVivoMarker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    icon: icon,
                    zIndex: 9999,
                    title: titulo,
                });
                _choferVivoInfo = new google.maps.InfoWindow();
                _choferVivoMarker.addListener('click', function () {
                    _choferVivoInfo.setContent(
                        '<div style="font-size:12px;line-height:1.4">' +
                        '<b>' + (r.nombre || r.usuario || 'Repartidor') + '</b><br>' +
                        'Recorrido ' + (r.recorrido || '-') + ' &middot; Orden ' + (r.orden || '-') + '<br>' +
                        'Estado: ' + (r.estado || '-') + '<br>' +
                        'Ultima senal: ' + _choferVivoHace(mins) +
                        (r.precision ? ' (&plusmn;' + r.precision + ' m)' : '') +
                        '</div>'
                    );
                    _choferVivoInfo.open(map, _choferVivoMarker);
                });
            } else {
                _choferVivoMarker.setPosition(pos);
                _choferVivoMarker.setIcon(icon);
                _choferVivoMarker.setTitle(titulo);
            }
        },
    });
}

function initMap_order(id) {

    $('#id_logistica').val(id);
    _choferVivoLimpiar();
    
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

    
    const map = new google.maps.Map(document.getElementById("map_order"), {
        zoom: 9,
        center: { lat: -31.407295723281997, lng:  -64.18836518176542 },
        mapTypeId: "terrain",

      });
      const flightPlanCoordinates=[];
      const markers=[];

    // Repartidor en vivo (solo si la pagina lo pidio: HojaDeRuta2).
    if (window.HDR2_LIVE_CHOFER) {
        _choferVivoActualizar(id, map);
        _choferVivoInterval = setInterval(function () {
            _choferVivoActualizar(id, map);
        }, 30000);
    }

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
            url:'Mapas/php/datos_controlrecorridos.php',
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

window.initMap_order = initMap_order;

// al cerrar el modal de la orden, cortar el poll del repartidor en vivo
$(function () {
    $('#full-width-modal_order').on('hidden.bs.modal', _choferVivoLimpiar);
});

$('#full-width-modal_order_button').click(function(){

    var id=$('#id_logistica').val();
    var Recorrido = $("#recorrido").html();
    console.log('id',id);
    $.ajax({
        data:{'Posiciones_order':1,'id':id,'Recorrido':Recorrido},
        url:'Mapas/php/cambiar_posicion.php',
        type:'post',
        success: function (respuesta) {
          var jsonData = JSON.parse(respuesta);
          if(jsonData.resultado==1){
              if(jsonData.modificadas!=0){
            toast("success", "Exito !", "Se reasingaron " +jsonData.modificadas+ " posiciones para el recorrido.!");
            $('#full-width-modal_order').modal('hide');
            // Refresca Roadmap_end para este recorrido y recarga mapa + tabla -
            // antes solo se recargaba la tabla, el mapa quedaba con el orden viejo.
            veo(Recorrido);
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();

            }else{
                toast("error", "Error !", "No se reasingaron posiciones para el recorrido.");
              }
          }
        }
        });
});


