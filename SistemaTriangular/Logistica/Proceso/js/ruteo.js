// $('.card-header').click(function(){
// var id = $(this).attr('id');
//   alert(id);                       
// });




// var datatable = $('#seguimiento').DataTable({
//     ajax: {
//          url:"Proceso/php/ruteo.php",
//          data:{'Pendientes':1},
//          processing: true,
//          type:'post'
//          },
//         columns: [
//             {data:"id",
//              render: function (data, type, row) {
//                if(row.Estado=='No se Pudo entregar'){
//                var color='danger';  
//                }else if(row.Estado=='Entregado al Cliente'){
//                var color='success';    
//                }else{
//                var color='warning';      
//                }
//               return '<h4><span class="badge badge-'+color+' text-white">'+row.Posicion+'</span></h4>';
//               }
//             },
//             {data:"id",
//               render: function (data, type, row) {
//                 if(row.Estado=='No se Pudo entregar'){
//                  var circle='danger'; 
//                 }else if(row.Estado=='Entregado al Cliente'){
//                  var circle='success';  
//                 }else{
//                  var circle='warning'; 
//                 }
//                 if(row.Retirado==1){
//                 var Destino=row.ClienteDestino; 
//                 var Direccion=row.DomicilioDestino;  
//                 }else{
//                 var Destino=row.RazonSocial;
//                 var Direccion=row.DomicilioOrigen
//                 }
                
//               return '<td><i class="mdi mdi-18px mdi-circle text-'+circle+'"></i> '+Destino+'</br>'+  
//                      '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">'+Direccion+'</td>';
              
//               }
//             },
//             {data:"CodigoSeguimiento",
//               render: function (data, type, row) {
//                 return '<td class="table-action">'+
//                 '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' + row.CodigoSeguimiento + '"' +
//                 'data-title="' + data.Destino + '" data-fieldname="' + data + '"><b>'+row.CodigoSeguimiento+'</b></a></td>';          
//               }
//             },
//             {data:"Retirado",
//                   render: function (data, type, row) {
//                 if(row.Retirado==1){
//                     return '<td>Entrega</td>';  
//                 }else{
//                     return '<td>Retiro</td>';  
//                   }
//                 }
//             },
//           {data:"id",
//            render: function (data, type, row) {
//                 return '<td class="table-action">'+
//                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
//                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
//                 '</td>';
//             }
//           }
//         ]
//   });
let recorrido;




function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: -31.4448988, lng: -64.177743}
        });
        directionsDisplay.setMap(map);
  
        $('.card-header').click(function(){
        var id = $(this).attr('id');
        recorrido=id;
        calculateAndDisplayRoute1(directionsService, directionsDisplay);
        });
        document.getElementById('actualizar_ok').addEventListener('click', function() {
        calculateAndDisplayRoute(directionsService, directionsDisplay);
        });
      }


//SOLO RENDERIZA
      function calculateAndDisplayRoute1(directionsService, directionsDisplay) {
        var waypts = [];
        $.ajax({
        data: {'Waypoints':1,'Recorrido':recorrido},
        url:'Proceso/php/ruteo.php',
        type:'post',
            success: function(response)
            {
            var jsonData = JSON.parse(response);
            var checkboxArray=jsonData.data;
            $('#encontrados').html('Encontramos '+jsonData.data.length +' direcciones para armar la ruta.'); 
            if(jsonData.err!=0){
                $('#errores').html('Atencion! Hay '+jsonData.err+' direcciones con errores que no se cargaron.');       
            }
            $('#actualizar_ok').html('Cargar Orden al Recorrido #'+recorrido);     
            $('#actualizar_ok').show();
            for (var i = 0; i < checkboxArray.length; i++) {
  //             console.log(checkboxArray[i].Localizacion);
              waypts.push({
                location: checkboxArray[i].Localizacion,
                stopover: true
              });
             }
              
          directionsService.route({
          origin: document.getElementById('start').value,
          destination: document.getElementById('end').value,
          waypoints: waypts,
          optimizeWaypoints: true,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var totalDistance = 0;
            var totalDuration = 0;
            var ordendir=[];
            var ordenid=[];
            
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
              var dato = route.legs[i].duration.value;
              var routeSegment = i + 1;
              var ordendir=route.legs[i].end_address;
              var ordenid=routeSegment;

              var direccion=route.legs[i].end_address;
              var distancia=route.legs[i].distance.value;
              var tiempo=route.legs[i].duration.value;
              var origen=document.getElementById('start').value;
              }
                
            var horas=Math.round((totalDuration /60))+1;
            var horas1=Math.floor(horas/60);
            var minutos=((horas-(horas1*60)));
            
            var res = totalDistance.toString().substr(0, 3);
            $('#titulo-mapa').html('Distancia '+res+' km. Duracion '+ horas1 +'Horas '+ minutos + ' minutos');
          } else {
            window.alert('Directions request failed due to ' + status);
          }
              });
            }
         });
      }






//ACTUALIZA EL SISTEMA

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        $.ajax({
        data: {'Waypoints':1,'Recorrido':recorrido},
        url:'Proceso/php/ruteo.php',
        type:'post',
            success: function(response)
            {
            var jsonData = JSON.parse(response);
            var checkboxArray=jsonData.data;
//             $('#encontrados').html('Encontramos '+jsonData.data.length +' direcciones para armar la ruta.'); 
            if(jsonData.err!=0){
                $('#errores').html('Atencion! Hay '+jsonData.err+' direcciones con errores que no se cargaron.');       
            }
//             $('#actualizar_ok').html('Cargar Orden al Recorrido #'+recorrido);     
//             $('#actualizar_ok').show();
            for (var i = 0; i < checkboxArray.length; i++) {
  //             console.log(checkboxArray[i].Localizacion);
              waypts.push({
                location: checkboxArray[i].Localizacion,
                stopover: true
              });
             }
              
          directionsService.route({
          origin: document.getElementById('start').value,
          destination: document.getElementById('end').value,
          waypoints: waypts,
          optimizeWaypoints: true,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var totalDistance = 0;
            var totalDuration = 0;
            var ordendir=[];
            var ordenid=[];
            
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              totalDistance += parseFloat(route.legs[i].distance.text);
              totalDuration += route.legs[i].duration.value;
              var dato = route.legs[i].duration.value;
              var routeSegment = i + 1;
              var ordendir=route.legs[i].end_address;
              var ordenid=routeSegment;

              var direccion=route.legs[i].end_address;
              var distancia=route.legs[i].distance.value;
              var tiempo=route.legs[i].duration.value;
              var origen=document.getElementById('start').value;
                          if(route.legs[i].end_address!=origen){
                              var data={
                              "Recorrido": recorrido,  
                              "Direccion": route.legs[i].end_address,
                              "km": route.legs[i].distance.value,
                              "tiempo":route.legs[i].duration.value,
                              "orden":ordenid
                              };
//                               console.log(jsonData);
                            //   $.ajax({
                            //   data: data,
                            //   url:'Calculardistancia.php',
                            //   type:'post',
                            //       success: function(response1)
                            //       {
                            //           var jsonData1 = JSON.parse(response1);
                            //           if (jsonData1.success == "1")
                            //           {
                            //           $.toast({
                            //             heading: 'Success',
                            //             text: 'Procesando  '+jsonData1.id+'.. a posicion '+jsonData1.orden,
                            //             textAlign: 'center',
                            //             position: 'bottom-right',
                            //             icon: 'success'
                            //           })                             
                            //           }
                            //           else
                            //           {
                            //            $.toast({
                            //             heading: 'danger',
                            //             text: 'Error!. Verificar Direcciones',
                            //             textAlign: 'center',
                            //             position: 'bottom-right',
                            //             icon: 'danger'
                            //           })                             
                            //           }
                            //      }
                            // });
                          }
              }
                
//             var horas=Math.round((totalDuration /60))+1;
//             var horas1=Math.floor(horas/60);
//             var minutos=((horas-(horas1*60)));
            
//             var res = totalDistance.toString().substr(0, 3);
//             $('#titulo-mapa').html('Distancia '+res+' km. Duracion '+ horas1 +'Horas '+ minutos + ' minutos');
          } else {
            window.alert('Directions request failed due to ' + status);
          }
              });
            }
         })
      }
