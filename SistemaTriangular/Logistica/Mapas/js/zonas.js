$(document).ready(function(){
$.ajax({
        data:{'Limpiar':1},
        type: "POST",
        url: "Mapas/php/zonas.php",
        success: function(response)
        {
        }
    });
  
$.ajax({
        data:{'BuscarRecorridos':1},
        type: "POST",
        url: "Proceso/php/pendientes.php",
        success: function(response)
        {
        $('.selector-recorrido1 select').html(response).fadeIn();
        }
    });
});

let selected=[];

$('#select_rec_mapa').change(function(){
  selected=[];  
    $(this).find('option:selected').each(function(i,e){
        selected.push(e.value);        
    });
      initMap();
  
});

// This example adds a user-editable rectangle to the map.
// When the user changes the bounds of the rectangle,
// an info window pops up displaying the new bounds.
let rectangle;
let map;
let infoWindow;
let milat;
let milng;
let zona
  

function initMap(z) {
var divMapa = document.getElementById('map');
var xhttp;
var resultado = [];
var markers = [];
var co =[];


  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -31.4448988, lng: -64.177743 },
    zoom: 10,
  });

//   const milat=-25.363;
//   const milng=131.044;


//   const myLatLng = { lat: milat, lng: milng };
  
//   new google.maps.Marker({
//     position: myLatLng,
//     map,
//     title: "Hello World!",
//   });


  
  $.ajax({
      data: {'Buscar':1,'zona':z,'rec':selected},
      url:'Mapas/php/zonas.php',
      type:'post',
      success: function(response)
       {
         var jsonData = JSON.parse(response);
//          console.log('veo exito',jsonData.exito);
         $('#cantidad').html(jsonData.Total + ' Servicios dentro de ' + zona);
         const bounds = {
          north:Number(jsonData.LatitudN),
          south:Number(jsonData.LatitudS),
          east: Number(jsonData.LongitudE),
          west: Number(jsonData.LongitudO),
       }
         
  // Define the rectangle and set its editable property to true.
  rectangle = new google.maps.Rectangle({
    bounds: bounds,
    editable: true,
    draggable: true,
  });
  rectangle.setMap(map);
        
    const bounds1 = {
    north: 44.599,
    south: 44.49,
    east: -78.443,
    west: -78.649,
  };      
         
  // Add an event listener on the rectangle.
  rectangle.addListener("bounds_changed", showNewRect);
  // Define an info window on the map.
  infoWindow = new google.maps.InfoWindow();
  rectangle.addListener('click',showNewRect);
       }
  });

  xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        resultado = xhttp.responseText;
        var objeto_json = JSON.parse(resultado);
//         console.log('veo ahora',objeto_json[0]);
//         console.log(objeto_json.data.length);
//         $('#cantidad').html(objeto_json.data.length);
//         $('#header-title2').html(c);
        
        for (var i = 0; i < objeto_json.data.length; i++) {
          
        //ICONO DE COLORES
              function pinSymbol(color) {
                  return {
  //              path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
                  path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
//                   path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
                  fillColor: '#'+color,
                  fillOpacity: 1,
                  strokeColor: '#FFFFFF',
                  strokeWeight: 1,
                  scale: 1,
                  };
                } 
        var icono=pinSymbol(objeto_json[0][i]);      
        var latlong = objeto_json.data[i].coordenadas.split(',');
                myLatLng = {
                    lat: Number(latlong[0]),
                    lng: Number(latlong[1])
                };
          
                var marker= new google.maps.Marker({
                  position: myLatLng,
                  map: map,
                  title: objeto_json.data[i].nombrecliente,
                  icon: icono
                });
               markers.push(marker);
        }
      }
    }
          
  var url = "Mapas/php/datos_zonas.php";
  xhttp.open("POST", url, true);
  xhttp.send(); 

}

/** Show the new coordinates for the rectangle in an info window. */
function showNewRect() {
  const ne = rectangle.getBounds().getNorthEast();
  const sw = rectangle.getBounds().getSouthWest();
  const contentString =
    "<b>"+zona+"<b><br>" +
    "New north-east corner: " +
    ne.lat() +
    ", " +
    ne.lng() +
    "<br>" +
    "New south-west corner: " +
    sw.lat() +
    ", " +
    sw.lng();
  // Set the info window's content and position.
  infoWindow.setContent(contentString);
  infoWindow.setPosition(ne);
  infoWindow.open(map);
  $.ajax({
      data: {'zona':zona,'Subir':1,'nelat':ne.lat(),'nelng':ne.lng(),'swlat':sw.lat(),'swlng':sw.lng(),'rec':selected},
      url:'Mapas/php/zonas.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);

         if(jsonData.success==1){
         
           $('#cantidad').html(jsonData.Total + ' Servicios dentro de ' + zona);

         }
       }
  });
}

$('#agregarzonas').click(function(){
var nombrezona=$('#nombrezona').val();
$.ajax({
      data: {'AgregarZona':1,'nombrezona':nombrezona},
      url:'Mapas/php/zonas.php',
      type:'post',
      success: function(response)
       {
      $('#zona-modal').modal('hide');
      $.NotificationApp.send("Exito !","Se agrego la Zona.!","bottom-right","#FFFFFF","success");       
       }
});  
});

$('.card-header').click(function(){
var id = $(this).attr('id');
initMap(id);
  zona=id;
$('.header-title').html('Zonas google Maps '+zona);  
});

$('#cambiar_recorrido').click(function(){

    $('#renderizar-modal').modal('show');

    $.ajax({
        data:{'BuscarRecorridos':1},
        type: "POST",
        url: "Proceso/php/pendientes.php",
        success: function(response)
        {
        $('.selector-recorrido select').html(response).fadeIn();
        }
    });

});

$('#renderizar_ok').click(function(){
var recnew=$('#recorrido_t').val();
// alert(selected+' '+zona+' '+recnew);  
$.ajax({
        data:{'CambiarRecorridos':1,'Recnew':recnew,'Zona':zona,'Recorridos':selected},
        type: "POST",
        url: "Mapas/php/zonas.php",
        beforeSend: function () {
        $('#renderizar-modal').modal('hide');  
        $("#info-alert-modal").modal('show');
       },
        success: function(response)
        {
         var jsonData = JSON.parse(response);
          if(jsonData.success==1){
          console.log('veamos',jsonData.exito);  
          $("#info-alert-modal").modal('hide');  
          $.NotificationApp.send("Exito !","Se movieron "+jsonData.cuenta+" registros.!","bottom-right","#FFFFFF","success");         
          initMap();  
          }
        }
    });  
});