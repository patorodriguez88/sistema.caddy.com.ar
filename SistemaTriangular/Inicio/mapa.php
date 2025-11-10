<?
session_start();
?>
<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <meta charset="utf-8">	

    <title>
      Rec: <? echo $_SESSION[Recorrido];?>
    </title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
        width: 50%;
        border: #000000 solid 0px;
        margin-top: 3px;
        right:0px;
        position:absolute;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
/*         height: 100%;
        margin: 0;
        padding: 0; */
      }
/*       #formContent{
        position: relative;
        top: -50px;
        width: 300px;
        margin: 0 auto;
    } */
    </style>
  </head>
  <body>
    <div id="map"></div>
<!--   <div id="formContent">
    
    </div> -->
<!-- <script>
  function ordenobase(id,orden){
      var dato={
        "Id": id,
        "Orden": orden
        };
        $.ajax({
        async: false,  
        data: dato,
        url:'ordenarmapa.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
            success: function(response)
            {
//             var label='1';
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
    
    </script> -->
    <script>
    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(-31.4448988, -64.177743),
          zoom: 12
        });
        var infoWindow = new google.maps.InfoWindow;
          
          // Change this depending on the name of your PHP or XML file
          downloadUrl('datosmapa.php', function(data) {
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
              
              var text = document.createElement('text');
              text.textContent = 'Direccion: '+address
              infowincontent.appendChild(text);
              
              var proximo = document.createElement('div');
              proximo.style.backgroundColor = '#fff';
              proximo.style.border = '0px solid #fff';
              proximo.style.borderRadius = '3px';
              proximo.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximo.style.cursor = 'pointer';
              proximo.style.marginBottom = '22px';
              proximo.style.textAlign = 'center';
              proximo.title = '';
              infowincontent.appendChild(proximo);
              infowincontent.appendChild(document.createElement('br'));

              var proximoText = document.createElement('div');
              proximoText.style.color = 'rgb(253,254,254)';
              proximoText.style.fontFamily = 'Roboto,Arial,sans-serif';
              proximoText.style.fontSize = '16px';
              proximoText.style.lineHeight = '38px';
              proximoText.style.padding = '8px';
              proximoText.innerHTML = 'Cambiar a Posicion... ';
              proximoText.style.backgroundColor= 'rgb(77,26,80)';
              proximo.appendChild(proximoText);
              
              var proximo1 = document.createElement('input');
              proximo1.style.placeholder='Escriba aqui el recorrido';
              proximo1.style.width='95%';
              proximo1.style.height='50px';
              proximo1.style.fontSize = '20px';
              proximo1.style.backgroundColor = '#fff';
              proximo1.style.border = '0px solid #fff';
              proximo1.style.borderRadius = '3px';
              proximo1.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
              proximo1.style.cursor = 'pointer';
              proximo1.style.marginBottom = '22px';
              proximo1.style.textAlign = 'center';
              proximo1.title = '';
              infowincontent.appendChild(proximo1);
              infowincontent.appendChild(document.createElement('br'));
              
              if(retiro==1){
              var anima= google.maps.Animation.DROP;
                var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/blue.png",
                scaledSize: new google.maps.Size(40, 40), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
                labelOrigin: new google.maps.Point(19, 15),
                  label: {
                  color: 'white',
                  fontWeight: 'bold',
                  text:posicion,
                  },
                };
              }else{
                var icon = {
                url: "http://maps.google.com/mapfiles/ms/icons/red.png",
                scaledSize: new google.maps.Size(40, 40), // scaled size
                Origin: new google.maps.Point(0,0), // Origin
                anchor: new google.maps.Point(0, 0), // anchor
//                 size: new google.maps.Size(32, 38),
                labelOrigin: new google.maps.Point(19, 15),
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
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
  </body>
</html>  