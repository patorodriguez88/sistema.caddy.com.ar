<!DOCTYPE html>
<html>
  <head>
    <title>Recorridos | Caddy</title>
    
<!--     <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script> -->
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>


    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
     #map {
        height: 100%;
        width: 100%;
        border: #000000 solid 0px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <h1>Mis mapas</h1>
  <div id="map"></div>
    <script>
      
function initMap() {
    var divMapa = document.getElementById('map');
    var xhttp;
    var resultado = [];
    var markers = [];
    var infowindowActivo = false;
    var infoWindow = new google.maps.InfoWindow;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      var map = new google.maps.Map(document.getElementById("map"), {
    center: new google.maps.LatLng(-31.4448988, -64.177743),
    zoom: 10,
    });  
      
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        resultado = xhttp.responseText;
        var objeto_json = JSON.parse(resultado);
        
        for (var i = 0; i < objeto_json.data.length; i++) {
                var latlong = objeto_json.data[i].coordenadas.split(',');
                myLatLng = {
                    lat: Number(latlong[0]),
                    lng: Number(latlong[1])
                };
              var idhdr = objeto_json.data[i].id;
              var name = objeto_json.data[i].Cliente;
              var posicion = objeto_json.data[i].Posicion;
              var address = objeto_json.data[i].Localizacion;
              var type = "";
              var ncliente = "";
              var clienteorigen="";
              var clientedestino=objeto_json.data[i].Cliente;
              var repe="";
              var retiro="";
              var CodigoSeguimiento = objeto_json.data[i].Seguimiento;
          
              var infowincontent = document.createElement('div');
              
              var Posicion = document.createElement('strong');
              Posicion.textContent = 'Orden: '+ posicion +' id:'+ idhdr
              infowincontent.appendChild(Posicion);
              infowincontent.appendChild(document.createElement('br'));
              
              var clienteOrigen = document.createElement('strong');
              clienteOrigen.textContent = 'Origen: '+ clienteorigen
              infowincontent.appendChild(clienteOrigen);
              infowincontent.appendChild(document.createElement('br'));

              var clienteDestino = document.createElement('strong');
              clienteDestino.textContent = 'Destino: '+ clientedestino +'(id:'+ ncliente + ')' 
              infowincontent.appendChild(clienteDestino);
              infowincontent.appendChild(document.createElement('br'));
              
              var Repe = document.createElement('strong');
              Repe.textContent = 'Total: '+ repe
              infowincontent.appendChild(Repe);
              infowincontent.appendChild(document.createElement('br'));
              
              var text = document.createElement('strong');
              text.textContent = 'Direccion: '+address
              text.style.marginBottom='10px';
              infowincontent.appendChild(text);

              var codigo = document.createElement('strong');
              codigo.textContent = 'Codigo Seguimiento: '+CodigoSeguimiento
              codigo.style.marginBottom='10px';
              infowincontent.appendChild(document.createElement('br'));
              infowincontent.appendChild(codigo);
              
              var proximo = document.createElement('div');
              proximo.style.backgroundColor = '#fff';
              proximo.style.border = '0px solid #fff';
              proximo.style.borderRadius = '3px';
              proximo.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximo.style.cursor = 'pointer';
              proximo.style.marginBottom = '5px';
              proximo.style.marginTop = '10px';
              proximo.style.textAlign = 'center';
              proximo.title = '';
              infowincontent.appendChild(proximo);
              infowincontent.appendChild(document.createElement('br'));

              var proximoText = document.createElement('div');
              proximoText.style.color = 'rgb(253,254,254)';
              proximoText.style.fontFamily = 'Roboto,Arial,sans-serif';
              proximoText.style.width='95%';
              proximoText.style.fontSize = '16px';
              proximoText.style.lineHeight = '28px';
              proximoText.style.padding = '8px';
              proximoText.style.marginBottom = '5px';
              proximoText.innerHTML = 'Cambiar a Posicion... ';
              proximoText.style.backgroundColor= 'rgb(77,26,80)';
              proximo.appendChild(proximoText);
              
              var id_hdr= document.createElement('div');
              id_hdr.innerHTML="<input type='hidden' value='"+idhdr+"' id='hdr_id' />";
              infowincontent.appendChild(id_hdr);

              var proximo1 = document.createElement('div');
              proximo1.innerHTML='<input type="number" value="" id="proximo1_id" />';
              proximo1.style.placeholder='Escriba aqui el recorrido';
              proximo1.style.width='95%';
              proximo1.style.height='30px';
              proximo1.style.fontSize = '20px';
              proximo1.style.backgroundColor = '#fff';
              proximo1.style.border = '0px solid #fff';
              proximo1.style.borderRadius = '3px';
              proximo1.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximo1.style.cursor = 'pointer';
              proximo1.style.marginBottom = '5px';
              proximo1.style.marginTop = '2px';
              proximo1.style.textAlign = 'center';
              proximo1.title = '';
              infowincontent.appendChild(proximo1);
              infowincontent.appendChild(document.createElement('br'));
              
              
              //DESDE ACA EL BOTON PARA CAMBIO DE RECORRIDO
               var proximoRecorrido = document.createElement('div');
              proximoRecorrido.style.backgroundColor = '#fff';
              proximoRecorrido.style.border = '0px solid #fff';
              proximoRecorrido.style.borderRadius = '3px';
              proximoRecorrido.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximoRecorrido.style.cursor = 'pointer';
              proximoRecorrido.style.marginBottom = '5px';
              proximoRecorrido.style.textAlign = 'center';
              proximoRecorrido.title = '';
              infowincontent.appendChild(proximoRecorrido);
              infowincontent.appendChild(document.createElement('br'));
              
               var proximoRecorridoText = document.createElement('div');
              proximoRecorridoText.style.color = 'rgb(253,254,254)';
              proximoRecorridoText.style.fontFamily = 'Roboto,Arial,sans-serif';
              proximoRecorridoText.style.fontSize = '16px';
              proximoRecorridoText.style.lineHeight = '28px';
              proximoRecorridoText.style.padding = '8px';
              proximoRecorridoText.innerHTML = 'Cambiar a Recorrido... ';
              proximoRecorridoText.style.backgroundColor= 'rgb(226,79,48)';
              proximoRecorrido.appendChild(proximoRecorridoText);
              
              var proximoRecorrido1 = document.createElement('input');
              proximoRecorrido1.style.placeholder='Escriba aqui el recorrido';
              proximoRecorrido1.style.width='95%';
              proximoRecorrido1.style.height='30px';
              proximoRecorrido1.style.fontSize = '20px';
              proximoRecorrido1.style.backgroundColor = '#fff';
              proximoRecorrido1.style.border = '0px solid #fff';
              proximoRecorrido1.style.borderRadius = '3px';
              proximoRecorrido1.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximoRecorrido1.style.cursor = 'pointer';
              proximoRecorrido1.style.marginBottom = '5px';
              proximoRecorrido1.style.textAlign = 'center';
              proximoRecorrido1.title = '';
              infowincontent.appendChild(proximoRecorrido1);
              infowincontent.appendChild(document.createElement('br'));        
          
              var anima= google.maps.Animation.DROP;
              var contentString = infowincontent;

              var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/blue.png",
                scaledSize: new google.maps.Size(43, 40), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
                labelOrigin: new google.maps.Point(21, 13),
                  label: {
                  color: 'white',
                  fontWeight: 'bold',
                  text: posicion
                  },
                };
                
               
                // marcador 1

                var marker = new google.maps.Marker({
                position: myLatLng,
                optimized: false,
                icon: icon,
                label: icon.label,
                title: 'Derecho 01',
                map: map
                });
                markers.push(marker);
          
//                 google.maps.event.addListener(marker, 'click', function(){     
//                   if(infowindowActivo){
//                     infowindowActivo.close();
//                   }                 
//                   infowindowActivo = this.infoWindow;
//                   infowindowActivo.open(map, this);
//                 });
          
             
               proximo.addEventListener('click', function() {
                 for(i=0; i< markers.length; i++){
                 iniciarSemaforo(i);
                 }
               });
                                  
                                        
//                 var posicionnew=document.getElementById('proximo1_id').value;
//                 var idposicionnew=document.getElementById('hdr_id').value;
//                  var dato={
//                   "Posicion": posicionnew,  
//                   "Idhdr": idposicionnew,
//                   };
//                   $.ajax({
//                   data:dato,
//                   url: 'https://www.caddy.com.ar/SistemaTriangular/Logistica/Proceso/cambiarposicion.php',
//                   type: 'post',
//                   success: function(response)
//                   {
//                   var jsonData = JSON.parse(response);
//                   if (jsonData.resultado == "1")
//                   {

//                  for(var i=0; i < markers.length; i++){ 
//                       markers[i].setMap(null);
//                       markers[i].setMap(map);
//                       markers[i].setAnimation(google.maps.Animation.DROP);
//                       markers[i].label={
//                       color: 'white',
//                       fontWeight: 'bold',
//                       text:jsonData.Posicionnew
//                     };
                   
//                   }
//                     infowindowActivo.close();  
//                   }else{
//                   alert('Invalid Credentials!');
//                   }
//                  }
//                 });  
//                });
          
             
             //CAMBIAR RECORRIDO
            proximoRecorrido.addEventListener('click', function() {
            var reconew=proximoRecorrido1.value;
            var dato={
            "Reconew": reconew,  
            "CodigoSeguimiento": CodigoSeguimiento,
            };
            $.ajax({
            data:dato,
            url: 'https://www.caddy.com.ar/SistemaTriangular/Logistica/Proceso/cambiarecorrido.php',
            type: 'post',
            success: function(response)
            {
            var jsonData = JSON.parse(response);
//                 alert(jsonData.resultado);
            if (jsonData.resultado == "1")
            {
                for(var i=0; i< markers.length; i++){
                markers[i].icon=pinSymbol(jsonData.Colornew);  
                markers[i].setMap(null);
                markers[i].setMap(map);
                markers[i].setAnimation(google.maps.Animation.DROP);
                markers[i].label={
                color: 'gray',
                fontWeight: 'bold',
                text:jsonData.NombreRec
                };
                }
              infoWindow.close();  
            }else{
            alert('Seleccione al menos un Recorrido!');
            }
           }
          });  
         });
          
        google.maps.event.addListener(marker, 'click', function(){     
                  if(infowindowActivo){
                    infowindowActivo.close();
                  }                 
                  infowindowActivo = this.infoWindow;
                  infowindowActivo.open(map, this);
                });
          
         marker.infoWindow = new google.maps.InfoWindow({
                  content: contentString
                });

  
        //ICONO DE COLORES
        function pinSymbol(color) {
            return {
//              path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
            path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
//                   path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
            fillColor: '#'+color,
            fillOpacity: 1,
            strokeColor: '#000',
            strokeWeight: 1,
            scale: 1,
            };
          }
              
          
         
          
       }
      }
      
      
                    function iniciarSemaforo(index){
                setTimeout(function(){         markers[index].icon="http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
                markers[index].setMap(null);
                markers[index].setMap(map);
                  setTimeout(function(){
                         markers[index].icon="http://maps.google.com/mapfiles/ms/icons/red-dot.png";
                markers[index].setMap(null);
                markers[index].setMap(map);                    
                },2000);                      
                },2000);
              }                                        


      
      
      
      
    }
    
      var myLatLng = {
              lat: -31.4448988,
              lng: -64.177743
          };

    var url = "marcadores.php";
    xhttp.open("POST", url, true);
    xhttp.send(); 


}


    </script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap&libraries=&v=weekly"
      async></script>
  </body>
</html>
