function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(-31.4448988, -64.177743),
          zoom: 12
        });
        var infoWindow = new google.maps.InfoWindow;
          
          // Change this depending on the name of your PHP or XML file
          downloadUrl('Mapas/php/datos_asignacion.php', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
            Array.prototype.forEach.call(markers, function(markerElem) {
             var idhdr = markerElem.getAttribute('idhdr');
              var name = markerElem.getAttribute('name');
              var posicion = markerElem.getAttribute('posicion');
              var address = markerElem.getAttribute('address');
              var type = markerElem.getAttribute('type');
              var ncliente = markerElem.getAttribute('ncliente');
              var clienteorigen=markerElem.getAttribute('clienteorigen');
              var clientedestino=markerElem.getAttribute('clientedestino');
              var repe=markerElem.getAttribute('resultado');
              var retiro=markerElem.getAttribute('retiro');
              var CodigoSeguimiento = markerElem.getAttribute('CodigoSeguimiento');
              
              var point = new google.maps.LatLng(
                  parseFloat(markerElem.getAttribute('lat')),
                  parseFloat(markerElem.getAttribute('lng')));
 
              var infowincontent = document.createElement('div');
              
              var Posicion = document.createElement('strong');
              Posicion.textContent = 'Orden: '+ posicion
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
              
              var proximo1 = document.createElement('input');
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
              
              
              if(retiro==1){
              var anima= google.maps.Animation.DROP;
                var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/blue.png",
                scaledSize: new google.maps.Size(50, 50), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
                labelOrigin: new google.maps.Point(24, 15),
                  label: {
                  color: 'white',
                  fontWeight: 'bold',
                  text:posicion,
                  },
                };
              }else{
                var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/red.png",
                scaledSize: new google.maps.Size(50, 50), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
//                 size: new google.maps.Size(32, 38),
                labelOrigin: new google.maps.Point(24, 15),
                  label: {
                  color: 'white',
                  fontWeight: 'bold',
                  text:posicion,
                  },
                };

              }
              var markers = [];
              var marker = new google.maps.Marker({
              map: map,
              position: point,
              label: icon.label,
              icon:icon,
              animation:anima
              });
              markers.push(marker);

              
              //CAMBIAR RECORRIDO
              proximoRecorrido.addEventListener('click', function() {
              var reconew=proximoRecorrido1.value;
              var dato={
              "Reconew": reconew,  
              "CodigoSeguimiento": CodigoSeguimiento,
              };
              $.ajax({
              data:dato,
              url: 'Proceso/cambiarecorrido.php',
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
              
              proximo.addEventListener('click', function() {
              var posicionnew=proximo1.value;
              var dato={
              "Posicion": posicionnew,  
              "Idhdr": idhdr,
              };
              $.ajax({
              data:dato,
              url: 'Proceso/cambiarposicion.php',
              type: 'post',
              success: function(response)
              {
              var jsonData = JSON.parse(response);
              if (jsonData.resultado == "1")
              {
              for(var i=0; i< markers.length; i++){
              markers[i].setMap(null);
              markers[i].setMap(map);
              markers[i].setAnimation(google.maps.Animation.DROP);
              markers[i].label={
              color: 'white',
              fontWeight: 'bold',
              text:jsonData.Posicionnew
              };
              }
                infoWindow.close();  
              }else{
              alert('Invalid Credentials!');
              }
             }
            });  
           });
              
                marker.addListener('click', function() {
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
              });
            });
          });
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