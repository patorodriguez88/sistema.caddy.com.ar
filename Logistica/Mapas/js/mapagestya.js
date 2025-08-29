
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
        
        
        for (var i = 0; i < objeto_json.data.length; i++) {
          console.log(objeto_json.data[i].latitud);
                
                myLatLng = {
                    lat: Number(objeto_json.data[i].latitud),
                    lng: Number(objeto_json.data[i].longitud)
                };
              

                markers[i] = new google.maps.Marker({
                  position: myLatLng,
                  map: map,
                  icon:'../images/favicon/truck copia.png',
                  title: objeto_json.data[i].alias
                });
               
                var contentString = '<h3>' +
                    objeto_json.data[i].alias + '</h3>'+ '<div id="bodyContent">'+
                    '<p><b>' + objeto_json.data[i].nombre + '</b></p><p>' + objeto_json.data[i].velocidad +
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
    }
    
     var myLatLng = {
              lat: -31.4448988,
              lng: -64.177743
          };

    var url = "Mapas/php/mapagestya.php";
    xhttp.open("POST", url, true);
    xhttp.send(); 
  
  map = new google.maps.Map(document.getElementById("map"), {
    center: new google.maps.LatLng(-31.4448988, -64.177743),
    zoom: 10,
  });
}
// setInterval(initMap, 60000); // HABILITAR PARA ACTUALIZAR EN TIEMPO REAL 60000 = 1 MINUTO
