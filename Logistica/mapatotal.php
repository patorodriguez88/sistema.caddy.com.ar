<?php
session_start();
?>
<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>
      Mapa Recorrido <? echo $_SESSION[Recorrido];?>
    </title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
        width: 100%;
        border: #000000 solid 0px;
        margin-top: 10px;"
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #formContent{
        position: relative;
        top: -50px;
        width: 300px;
        margin: 0 auto;
    }
    </style>
  </head>
  <body>
    <div id="map"></div>
  <div id="formContent">
    
    </div>
<script>
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
    
    </script>
    <script>



        function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(-31.4448988, -64.177743),
          zoom: 12
        });
        var infoWindow = new google.maps.InfoWindow;
          
          // Change this depending on the name of your PHP or XML file
          downloadUrl('datosmapatotal.php', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
            Array.prototype.forEach.call(markers, function(markerElem) {
             var recorrido = markerElem.getAttribute('recorrido'); 
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
              var recorrido=
              if(recorrido=='1027'){
              var coloricono='yellow';  
              }else if(recorrido=='1003'){
              var coloricono='blue';    
              }else{
              var coloricono='green';      
              }
              
              var text = document.createElement('text');
              text.textContent = 'Direccion: '+address
              infowincontent.appendChild(text);
              if(retiro==1){
              var anima= google.maps.Animation.DROP;
//               var anima= google.maps.Animation.BOUNCE;
                
              var icon = {
              url: "http://maps.google.com/mapfiles/ms/icons/"+coloricono+".png",
              scaledSize: new google.maps.Size(40, 40), // scaled size
              Origin: new google.maps.Point(0,0), // Origin
              anchor: new google.maps.Point(0, 0), // anchor
              label:posicion
              };
              }else{
              var anima= google.maps.Animation.DROP;

              var icon = customLabel[repe] || {};
              }
              var marker = new google.maps.Marker({
              map: map,
              position: point,
              label: icon.label,
              icon:icon,
              animation:anima
              });
              marker.addListener('click', function() {
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
//                 var ordeno=Number(1);
//                 ordenobase(idhdr,ordeno);
//                 var ordeno=ordeno+Number(1);

              });
            });
          });
        }
      
      var customLabel = {
        1: {label: '1'},
        2: {label: '2'},
        3: {label: '3'},   
        4: {label: '4'},   
        5: {label: '5'},   
        6: {label: '6'},   
        7: {label: '7'},   
        8: {label: '8'},   
        9: {label: '9'},   
        10: {label: '10'},   
        11: {label: '11'},   
        12: {label: '12'},   
        13: {label: '13'},   
        14: {label: '14'},   
        15: {label: '15'},   
        16: {label: '16'},   
        17: {label: '17'},   
        18: {label: '18'},
        19: {label: '19'},
        20: {label: '20'},   
        21: {label: '21'},   
        22: {label: '22'},   
        23: {label: '23'},   
        24: {label: '24'},   
        25: {label: '25'},   
        26: {label: '26'},   
        27: {label: '27'},   
        28: {label: '28'},   
        29: {label: '29'},   
        30: {label: '30'},   
        31: {label: '31'},   
        32: {label: '31'},   
        33: {label: '33'},   
        34: {label: '34'}   
      };


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
//     src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLo_Ys3Waub6KJAAvdEfKQuIVt-xO7qu0&callback=initMap">
    </script>
  </body>
</html>  