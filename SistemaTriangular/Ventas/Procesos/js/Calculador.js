
var retiro=document.getElementById('retiro').value;
var envio=document.getElementById('envio').value;  


    
function verificar(name,value,max){
  var valor=parseFloat(value);
  var maximo=parseFloat(max);  

  if(valor>maximo){
  document.getElementById(name).value=0;
  alertify.error('Maximo ' + max + ' cm. ' + name);
  }  
}

  function realizaProceso(a){
    var result;
    var dato={
        "localidadorigen": a,  
        };
        
        $.ajax({
        async: false,  
        data: dato,
        url:'../php/localidades.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (respuesta) {
          $("#resultado").html(respuesta);
        }
        });
  }
 
      function _googleAcDebounce(inputEl, opciones) {
        var cfg = Object.assign(
          { fields: ['address_components'], componentRestrictions: { country: 'AR' },
            types: ['geocode','establishment'], debounce: 400, minLength: 3, onSelect: null },
          opciones || {}
        );
        var svc = new google.maps.places.AutocompleteService();
        var placeSvc = new google.maps.places.PlacesService(document.createElement('div'));
        var token = new google.maps.places.AutocompleteSessionToken();
        var timer = null;
        var wrapper = inputEl.parentElement;
        if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
        var ul = document.createElement('ul');
        ul.style.cssText = 'position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;' +
          'overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;' +
          'background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);';
        wrapper.appendChild(ul);
        function close() { ul.style.display = 'none'; }
        function selectPlace(placeId, description) {
          inputEl.value = description; close();
          placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function(place, status) {
            token = new google.maps.places.AutocompleteSessionToken();
            if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
          });
        }
        inputEl.addEventListener('input', function() {
          clearTimeout(timer);
          var val = this.value.trim();
          if (val.length < cfg.minLength) { close(); return; }
          var snap = val;
          timer = setTimeout(function() {
            svc.getPlacePredictions(
              { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
              function(predictions, status) {
                ul.innerHTML = '';
                if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
                predictions.forEach(function(p) {
                  var li = document.createElement('li');
                  li.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                  li.textContent = p.description;
                  li.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                  li.addEventListener('mouseout', function() { this.style.background = ''; });
                  li.addEventListener('mousedown', function(e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
                  ul.appendChild(li);
                });
                ul.style.display = 'block';
              }
            );
          }, cfg.debounce);
        });
        inputEl.addEventListener('blur', function() { setTimeout(close, 200); });
      }

      function _validarCiudadCordoba(place, inputEl, ciudadHiddenId, label) {
        if (!place || !place.address_components) return false;
        var provincia = '', ciudad = '';
        place.address_components.forEach(function(c) {
          if (c.types[0] === 'administrative_area_level_1') provincia = c.long_name;
          if (c.types[0] === 'locality') ciudad = c.long_name;
        });
        if (provincia !== 'Córdoba') {
          alertify.error('La Provincia ' + label + ' debe ser Córdoba, no ' + provincia);
          inputEl.value = ''; inputEl.focus(); return false;
        }
        realizaProceso(ciudad);
        if (document.getElementById('resultado').innerText == 0) {
          alertify.error('La Localidad ' + label + ' ' + ciudad + ' no se encuentra a nuestro alcance, analice redespacho');
          inputEl.value = ''; inputEl.focus(); return false;
        }
        document.getElementById(ciudadHiddenId).value = ciudad;
        return true;
      }

      function initMap() {

        //START
        var inputstart = document.getElementById('start');
        _googleAcDebounce(inputstart, {
          onSelect: function(place) { _validarCiudadCordoba(place, inputstart, 'startciudad', 'de origen'); }
        });

        //WAYPOINTS
        var inputwaypoints = document.getElementById('waypoints');
        _googleAcDebounce(inputwaypoints, {
          onSelect: function(place) { _validarCiudadCordoba(place, inputwaypoints, 'waypointsciudad', 'intermedia'); }
        });

        //HASTA
        var inputend = document.getElementById('end');
        _googleAcDebounce(inputend, {
          onSelect: function(place) { _validarCiudadCordoba(place, inputend, 'endciudad', 'de destino'); }
        }); 
        
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
        //VERIFICO SI LA LOCALIDAD TIENE TILDADO WEB QUE SON A LAS QUE LLEGAMOS CON CADDY
        var String = document.getElementById('endciudad').value;
        var Parts = String.split(",");
        var part1 = Parts[0]; // 123
        var part2 = Parts[1]; // 654321
//         alert (part2);
        
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        var dato1 = document.getElementById('waypoints').value;
//         for (var i = 0; i < checkboxArray.length; i++) {
          if (dato1 != '') {
          var  puntointermedio=1.5;
          var  puntointermediotxt='Si';  

            waypts.push({
              location: document.getElementById('waypoints').value + "," + document.getElementById('waypointsciudad').value,
              stopover: true
            });
          }else{
          var  puntointermedio=1;
          var  puntointermediotxt='No';  
          }
//         }
        
        directionsService.route({
          origin: document.getElementById('start').value + "," + document.getElementById('startciudad').value,
          destination: document.getElementById('end').value + "," + document.getElementById('endciudad').value,
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
        
              summaryPanel.innerHTML += '<b>Ruta Segmento: ' + routeSegment +
                  '</b><br>Desde ';
              summaryPanel.innerHTML += route.legs[i].start_address + ' hasta ';
              summaryPanel.innerHTML += route.legs[i].end_address + '<br>Total Segmento: ';
              summaryPanel.innerHTML += route.legs[i].distance.text + '<br>Duracion: ';
              summaryPanel.innerHTML += route.legs[i].duration.text + '<br><br>';

              var dato = route.legs[i].duration.value;
//               window.location.href = window.location.href + "?viajar=si&" + "valor=" + dato;
//               $variable =$_GET['valor'];
//               $sql="INSERT INTO Datos(Observaciones)VALUES('{$variable}')"; 
//               mysql_query($sql);
            
            }
            
            var summaryPanel = document.getElementById('directions-panel');
            
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            
            var ancho = document.getElementById('ancho').value;
            var largo = document.getElementById('largo').value;
            var alto = document.getElementById('alto').value;
            var dimensiones = ancho*largo*alto;
//             var res = totalDistance.toString().substr(0, 4);
            var res = totalDistance;
            var A=document.getElementById('startciudad').value;
            var B=document.getElementById('waypointsciudad').value;
            var C=document.getElementById('endciudad').value;

//             if(datoajax==0){  
//             var redespacho='*';
//             var redespacholeyenda='* ATENCION: REQUIERE REDESPACHO';  
//             var costo= 'Verificar';
//             }else{
            var redespacho='';
            var redespacholeyenda='';
            var varpeso=document.getElementById('peso').value;
              //DESDE ACA CALCULO EL VALOR X PESO DE LA ENCOMIENDA
//               PRIMERO CLASIFICO POR DIMENSIONES
              //DEFINO VARIABLES
            var costo=0;
            var pesoextra=0;
            var vxma=0.0015;//60% DEL VALOR MAS ALTO DE TABLA
            var vxmb=0.0020;//60% DEL VALOR MAS ALTO DE TABLA
            var vxmc=0.0026;//60% DEL VALOR MAS ALTO DE TABLA

     if(dimensiones<4860 && varpeso<2){
                  if(res<25){
                  costo=150;
                  var tarifa='1 | A';  
                  }else if(res<50){
                  costo=200;  
                  var tarifa='1 | B';  
                  }else if(res>51){
                  costo=250;
                  var tarifa='1 | C';  
                  }
               }else if(dimensiones<5460 && varpeso<4){
                  if(res<25){  
                  costo=180;
                  var tarifa='2 | A';  
  
                  }else if(res<50){
                  costo=240; 
                  var tarifa='2 | B';  

                  }else if(res>51){
                  costo=300; 
                  var tarifa='2 | C';  

                  }
               }else if(dimensiones<18375 && varpeso<10){
                if(res<25){  
                costo=220; 
                var tarifa='3 | A';  

                }else if(res<50){
                costo=295;  
                var tarifa='3 | B';  

                }else if(res>51){
                costo=365;    
                var tarifa='3 | C';  

                }
               }else if(dimensiones<22050 && varpeso<=15){ //tarifa 4
                if(res<25){  
                costo=250;  
                var tarifa='4 | A';  

                }else if(res<335){
                costo=335;  
                var tarifa='4 | B';  

                }else if(res>420){
                costo=420;    
                var tarifa='4 | C';  

                }
               }else if(dimensiones<42875 && varpeso<=20){ //TARIFA 5
                  if(res<25){  
                  costo=300;  
                  var tarifa='5 | A';  

                  }else if(res<335){
                  costo=400;  
                  var tarifa='5 | B';  

                  }else if(res>51){
                  costo=500;    
                  var tarifa='5 | C';  

                  }
               }else if(dimensiones<64000 && varpeso<=25){
                  if(res<25){  
                  costo=350; 
                  var tarifa='6 | A';  

                  }else if(res<50){
                  costo=465;  
                  var tarifa='6 | B';  

                  }else if(res>51){
                  costo=585;    
                  var tarifa='6 | C';  

                  }
               }else if(dimensiones<80000 && varpeso<=25){
                  if(res<25){  
                  costo=400;  
                  var tarifa='7 | A';  

                  }else if(res<50){
                  costo=535;  
                  var tarifa='7 | B';  

                  }else if(res>51){
                  costo=665;    
                  var tarifa='7 | C';  

                  }
               }else if(dimensiones<99000 && varpeso<=25){
                  if(res<25){  
                  costo=500;  
                  var tarifa='8 | A';  

                  }else if(res<50){
                  costo=665;  
                  var tarifa='8 | B';  

                  }else if(res>51){
                  costo=850;    
                  var tarifa='8 | C';  

                  }
                      
                 }else if(dimensiones<=99000 && varpeso>=25){
                   // UTILIZO LOS VALORES DE LA TARIFA 6 PORQUE AHI CAMBIA EL PESO
                     pesoextra = (varpeso - 25)*5;// variable de peso extra  
                     var pesoextratxt='Si | ' + (varpeso -25); 
                    if(res<25){
                        if((dimensiones*vxma)<250){
                          costo=250 + pesoextra;  
                          }else{
                          costo=(dimensiones*vxma) + pesoextra;
                          }
                       var tarifa='Extra Peso | A';  
                    }else if(res<50){
                          if((dimensiones*vxma)<465){
                            costo=465 + pesoextra;  
                            }else{
                            costo= (dimensiones*vxma) + pesoextra;
                            }
                     var tarifa='Extra Peso | B';  
                    }else if(res>51){
                          if((dimensiones*vxma)<585){
                            costo=585 + pesoextra;  
                            }else{
                            costo=(dimensiones*vxma) + pesoextra;
                            }
                     var tarifa='Extra Peso | C';  
                    }
                  }else if(dimensiones>99000 && varpeso<=25){
               // UTILIZO LOS VALORES DE LA TARIFA 8 PORQUE SUPERA EL MAXIMO DE DIMENSIONES DE TABLA
 
                    if(res<25){
                      costo=(dimensiones*vxma);  
                      if(costo<500){
                      costo=500;  
                      }
                      var tarifa='Extra Dimensiones | A';  
                      }else if(res<50){
                      costo=(dimensiones*vxmb);
                      if(costo<665){
                      costo=500;  
                      }
                      var tarifa='Extra Dimensiones | B';  
                      }else if(res>51){
                      costo=(dimensiones*vxmc);  
                      if(costo<850){
                      costo=850;  
                      }
                      var tarifa='Extra Dimensiones | C';  
                      }
                    
                  }else if(dimensiones>99000 && varpeso>=26){
                     pesoextra = (varpeso - 25)*5;// variable de peso extra  
                     var pesoextratxt='Si | ' + (varpeso -25); 
                            if(res<25){
                            costo= (dimensiones*vxma) + pesoextra;
                            var tarifa='Extra Total | A';  
                            }else if(res<50){
                            costo= (dimensiones*vxmb) + pesoextra;
                            var tarifa='Extra Total | B';  
                            }else if(res>51){
                            costo= (dimensiones*vxmc) + pesoextra;
                            var tarifa='Extra Total | C';  
                            }
                    }
                  
              
              
              var retiro=document.getElementById('seleccionretiro').value;
              if(retiro='Domicilio'){
              var varretiro=0;//ACA CARGO UN VALOR POR RETIRAR A DOMICILIO  
              }
          
            // SI HAY CAMBIO DE LOCALIDAD SE INCREMENTA UN VALOR FIJO 
            var a= document.getElementById('startciudad').value;
            var b= part1;
            var c= document.getElementById('waypointsciudad').value;
            
            if(puntointermedio!=1){ //EXISTE WAYPOINTS

              if(a===b && a===c && b===c){ 
                var incxcambioloc=0;
                var incxcambioloctxt='No';
                }else{
                var incxcambioloc=150;
                var incxcambioloctxt='Si | $ 150';   
                }
            }else{ // NO EXISTEN WAYPOINTS
                if(a==b){ 
                var incxcambioloc=0;
                var incxcambioloctxt='No';
                }else{
                var incxcambioloc=150;
                var incxcambioloctxt='Si | $ 150';   
               }
             }            

            var cantidad=document.getElementById('cantidad').value;
            var valordeclarado=document.getElementById('valordeclarado').value;
            var seguro=(new Intl.NumberFormat("de-DE").format(valordeclarado*0.009));

            if(valordeclarado<=50){
            var seguro=Number(50);  
            }else{
            var seguro=Number(valordeclarado*0.009);  
            }
            var segurotxt=(new Intl.NumberFormat("de-DE").format(valordeclarado*0.009));  
            
            var costo1=costo;
            //DESCUENTO X CANTIDAD
            if(cantidad>1){
              for (var i = 2; i <= cantidad; i++) {
              var descuento=costo-((costo*60)/100);
              costo1=costo1+descuento;
              }
            }else{
            var costo1=(costo*cantidad);
            }
//             var costototal=(costo1+incxcambioloc+seguro);
            var costototal=Number(costo1)+Number(incxcambioloc)+seguro;

            document.getElementById('costo').value=costo;
            document.getElementById('comienzo_calle').value=document.getElementById('start').value;
            document.getElementById('comienzo_ciudad').value=document.getElementById('startciudad').value;
            document.getElementById('final_calle').value=document.getElementById('end').value;
            document.getElementById('final_ciudad').value=document.getElementById('endciudad').value;
            document.getElementById('retiro_final').value=document.getElementById('retiro').value;
            
            var tarifacantidad=(new Intl.NumberFormat("de-DE").format(costo*cantidad));
            var formato=(new Intl.NumberFormat("de-DE").format(costototal*puntointermedio));
            var totaldescuento=(new Intl.NumberFormat("de-DE").format(costo-costo1));

            if(puntointermedio==1){
            var puntointermedioformato='';
            }else{
            var puntointermedioformato=(new Intl.NumberFormat("de-DE").format((costo1+incxcambioloc)/2));
            }
            
            
            document.getElementById('cambia_localidad').value=incxcambioloc;
            document.getElementById('punto_intermedio').value=puntointermedioformato;
            document.getElementById('requiere_redespacho').value=redespacho;

            var descripcion=document.getElementById('descripcion').value;
            
            summaryPanel.innerHTML += '<b> Descripción: ' + descripcion +'</br>';
            summaryPanel.innerHTML += '<b> Distancia Total: ' + res + ' km.</b><b> Duración Total:';
            summaryPanel.innerHTML += horas1 +' Horas ' + minutos + ' minutos </br>';
            summaryPanel.innerHTML += '<b> Dimensiones:  ' + dimensiones/1000000 + ' mts.3 '; 
            summaryPanel.innerHTML += '| Peso (Max.:25kg):  ' + varpeso + ' Extra: ' + pesoextratxt + ' kg. </br>'; 
            summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Tarifa ' + tarifa + ' | Composición:  </br>';
            summaryPanel.innerHTML += '<b> Tarifa ' + tarifa +':  $ ' + costo + '</br>';
            summaryPanel.innerHTML += '<b> Seguro (Valor Declarado: $ '+ valordeclarado +'): $ ' + segurotxt +' </br>'; 
            summaryPanel.innerHTML += '<b> Cantidad:  ' + cantidad +'</br>'; 
            summaryPanel.innerHTML += '<b> Tarifa x ('+ cantidad + ') :  $ ' + tarifacantidad +'</br>'; 
            summaryPanel.innerHTML += '<b> Descuento x Cant.: $ ' + totaldescuento +'</br>';
            summaryPanel.innerHTML += '<b> Retiro:  ' + varretiro +'</br>';
            summaryPanel.innerHTML += '<b> Punto Intermedio:  ' + puntointermediotxt +' '+ puntointermedioformato +'</br>'; 
            summaryPanel.innerHTML += '<b> Cambia Localidad:  ' + incxcambioloctxt +'</br>'; 
            summaryPanel.innerHTML += '<br></br><b style="font-size:20px;"> Costo Total: $ ' + formato +' </b></br>';    
            document.getElementById('tarifa').value=tarifa;
            document.getElementById('detalle').value=summaryPanel.innerHTML.text;
//             summaryPanel.innerHTML += '<br></br><b style="font-size:18px;color:red">' + redespacholeyenda + ' </b>';            
            } else {
            window.alert('Verificar Direcciones ' + status);
          }
        });
      }
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap">
    </script>