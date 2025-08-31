function geocodeResult(results, status) {
    // Verificamos el estatus
    if (status == 'OK') {
        // Si hay resultados encontrados, centramos y repintamos el mapa
        // esto para eliminar cualquier pin antes puesto
        // fitBounds acercará el mapa con el zoom adecuado de acuerdo a lo buscado
        map.fitBounds(results[0].geometry.viewport);
        // Dibujamos un marcador con la ubicación del primer resultado obtenido
        var markerOptions = { position: results[0].geometry.location,animation:google.maps.Animation.BOUNCE,labelContent: "A" }
        var marker = new google.maps.Marker(markerOptions);

        marker.setMap(map);
        map.setZoom(12);
        marker.addListener("click", eliminar);
//     }

    function eliminar(){
    marker.setMap(null);  
    }  
    } else {
        // En caso de no haber resultados o que haya ocurrido un error
        // lanzamos un mensaje con el error
        alert("Geocoding no tuvo éxito debido a: " + status);
    }
}


function initialize() {
   initMap();
//    BuscarDireccion();
}


$('#standard-modal-dir').on('show.bs.modal', function (e) {
    
    BuscarDireccion();
});

// $(document).ready(function () {
function BuscarDireccion() {
    
        console.log('arranca','buscarDireccion');

        // const newLocal = 'direccion_nc';
        var inputstart = document.getElementById('direccion_nc');
        // var inputstart = $('#direccion_nc').val();

        console.log('input',inputstart);

        var autocomplete = new google.maps.places.Autocomplete(inputstart, { 
            types: ['geocode','establishment'], 
            componentRestrictions: {
            country: ['AR']
        }});

        autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (place.address_components) {
          var components= place.address_components;
          var ciudad='';
          var provincia='';  
          for (var i = 0, component; component = components[i]; i++) {
//             console.log(component);
            if (component.types[0] == 'administrative_area_level_1'){
               provincia=component['long_name'];
               }
            if(component.types[0] == 'locality') {
               ciudad = component['long_name'];
               document.getElementById('ciudad_nc').value = ciudad;
               }
            if(component.types[0] == 'postal_code'){
               document.getElementById('cp_nc').value= component['short_name'];   
               }
            if(component.types[0] == 'neighborhood'){
                 if(component['long_name']!=null){
                   document.getElementById('Barrio_nc').value= component['long_name']; 
                   }else if(component.types[0] == 'administrative_area_level_2'){
                   document.getElementById('Barrio_nc').value= component['long_name'];   
                  }  
               }
            if(component.types[0] == 'street_number'){
               document.getElementById('Numero_nc').value= component['long_name'];   
               }
            if(component.types[0] == 'route'){
               document.getElementById('Calle_nc').value= component['long_name'];   
                
              }
          }
        }
        }); 
   }


function modificardir(e) {
    $.ajax({
      data:{'BuscarDatosClienteDestino':1,'id':e},
      url:'Proceso/php/pendientes.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);

        $('#standard-modal-dir').modal('show');
        $('#myCenterModalLabel').html('Modificar Direccion a '+jsonData.data[0].ClienteDestino);  
        $('#direccion_nc').val(jsonData.data[0].DomicilioDestino); 
        $('#id_nc').val(jsonData.data[0].idClienteDestino);
        $('#cs_nc').val(jsonData.data[0].CodigoSeguimiento); 
        $('#latitud_nc').val(jsonData.data[0].Latitud); 
        $('#longitud_nc').val(jsonData.data[0].Longitud); 
        $('#observaciones_nc').val(jsonData.data[0].Observaciones);
        // if($('#switch1').is(":checked")){
        console.log('vero',jsonData.data[0].ActivarCoordenadas);
        if(jsonData.data[0].ActivarCoordenadas==1){
            // $('#switch1').prop("checked");
            // $('#switch1'). siblings('input').prop("checked");
            // $('#switch1').attr('checked', 'true');
            $('#switch1').prop('checked', 'true');
            $('#switch1').attr('value','1');
        }else{
            $('#switch1').prop('checked', '');
            $('#switch1').attr('value','0');
        }
        

       }  
  });
}

$('#switch1').click(function(){
    if($('#switch1').is(":checked")){
        $('#latitud_nc').prop('disabled',false);
        $('#longitud_nc').prop('disabled',false);
        $('#google_import_nc').prop('disabled',false);

        $('#latitud_nc').css('background','');
        $('#longitud_nc').css('background','');
        $('#google_import_nc').css('background','');

    }else{

        $('#latitud_nc').prop('disabled',true);
        $('#longitud_nc').prop('disabled',true);
        $('#google_import_nc').prop('disabled',true);

        $('#longitud_nc').css('background','#D5D3D3');
        $('#latitud_nc').css('background','#D5D3D3');
        $('#google_import_nc').css('background','#D5D3D3');
    }
});

$("#standard-modal-dir").on("shown.bs.modal",function() {

$('#google_import_nc').val('');
// $('#switch1').val('off');
// $("#switch1").prop('checked','off');
// $('#switch1').removeAttr('checked'); 
// $('#switch1').prop('checked','');
$('#latitud_nc').prop('disabled',true);
$('#longitud_nc').prop('disabled',true);
$('#google_import_nc').prop('disabled',true);
$('#longitud_nc').css('background','#D5D3D3');
$('#latitud_nc').css('background','#D5D3D3');
$('#google_import_nc').css('background','#D5D3D3');
});

$('#google_import_nc').bind("paste",function(e){
	//Ejecutar función
    // access the clipboard using the api
    var pastedData = e.originalEvent.clipboardData.getData('text');
    var lat = pastedData.split(',')[0];
    var lon = Number(pastedData.split(',')[1]);
    $('#latitud_nc').val(lat);
    $('#longitud_nc').val(lon);
});

function servicio_mod(i,v){

    // console.log('Servicio id',i);

    if(v=='0'){
        var nuevo_val_servicio='Entrega';
        var Retirado=1;
    }else{
        var  nuevo_val_servicio='Retiro';
        var Retirado=0;
    }

    $('#servicio_modal').modal('show');
    $('#servicio_modal-body').html('Estas por modificar el servicio '+i+' a '+nuevo_val_servicio);
    $('#servicio_id_trans').val(i);
    $('#servicio_retirado').val(Retirado);

}

$('#ok_servicio_modal').click(function(){
    
    let i=$('#servicio_id_trans').val();
    let Retirado=$('#servicio_retirado').val();
    
    $.ajax({
        data:{'MarcaRetirado':1,'id_trans':i,'Retirado':Retirado},
        url:'Proceso/php/pendientes.php',
        type:'post',
        success: function(response)
         {
          var jsonData = JSON.parse(response);
          if(jsonData.success==1){
            renderizar_datos();
            initMap();  
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload(null,false);

            $.NotificationApp.send("Registro Actualizado !","Se ha actualizado el Servicio.","bottom-right","#FFFFFF","success");      

          }else{
            $.NotificationApp.send("Registro No Actualizado !","No pudimos actualizar el Servicio.","bottom-right","#FFFFFF","danger");
          }
          $('#servicio_modal').modal('hide');
         }
        });
    });



$('#modificardir_ok').click(function(){
  var dir=$('#direccion_nc').val();
  var calle= $('#Calle_nc').val();
  var barrio= $('#Barrio_nc').val();
  var numero= $('#Numero_nc').val();
  var ciudad= $('#ciudad_nc').val();
  var cp= $('#cp_nc').val();
  var id=$('#id_nc').val();
  var cs=$('#cs_nc').val();
  var obs=$('#observaciones_nc').val();
  var lat=$('#latitud_nc').val(); 
  var lon=$('#longitud_nc').val(); 
  
  if($('#switch1').is(":checked")){
    var modificar_lat_lon=1;    
  }else{
    var modificar_lat_lon=0;
  }  
  
  var origen="Reconquista 4986, Cordoba, Argentina";
  
      $.ajax({
          data:{'ActualizarDireccion':1,'modificar_lat_lon_manual':modificar_lat_lon,'Direccion':dir,'id':id,'calle':calle,'barrio':barrio,'numero':numero,'ciudad':ciudad,'cp':cp,'cs':cs,'obs':obs,'lat':lat,'lon':lon},
          url:'Proceso/php/pendientes.php',
          type:'post',
          success: function(response)
           {
            var jsonData = JSON.parse(response);
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();
             
           $('#standard-modal-dir').modal('hide');
           var color=$('#header-title2').html();
             
             initMap(color);  
           }  
        });
 });

$(document).ready(function () {

var datatable = $('#seguimiento').DataTable({
  dom: 'Bfrtip',
  buttons: ['pageLength'],
  paging: true,
  searching: true,
  lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
    ajax: {
         url:"Proceso/php/pendientes.php",
         data:{'Pendientes':1},
         processing: true,
         type:'post'
         },
        columns: [
            {data:"Posicion",
        render: function(data,type,row){

            if(row.HdrEstado=='Abierto'){
             var colororden="success";    
             }else{
            var colororden="danger";        
            }   

         if(row.Retirado==1){

            return  `<div class="avatar-xs"><span class="avatar-title bg-${colororden} rounded ">${row.Posicion}</span></div></div>`;    
         
        }else{       
            
            return `<div class="btn-group mb-2">`+
                   `<div class="avatar-xs"><span class="avatar-title bg-warning rounded mr-1">${row.Posicion_retiro}</span></div>`+
                   `<div class="avatar-xs ml-1"><span class="avatar-title bg-${colororden} rounded ">${row.Posicion}</span></div></div>`;    
            }
         }
        },
            {data:"Fecha",
             render: function (data, type, row) {
            //    console.log([0].Latitud);
              var Fecha=row.Fecha.split('-').reverse().join('.');
              return '<td><span style="display: none;">'+row.Fecha+'</span>'+Fecha+'</td>';  
              }
            },            
            {data:"RazonSocial",
            render: function (data, type, row) { 
            
                if(row.Retirado==0){              
                var color='success';  
                }else{
                color='muted';    
                }


           return '<td><b>'+row.RazonSocial+'</br>'+  
                     '<i class="mdi mdi-18px mdi-map-marker text-'+color+'"></i><a class="text-muted">'+row.DomicilioOrigen+'</td>';
              }
            },
            {data:"DomicilioDestino",
            render: function (data, type, row) { 
            if(row.Latitud){
                if((row.Latitud=='-31.41972520387455')&&(row.Longitud=='-64.18901825595384')){
                    var color1='danger';
                }else{
                    if(row.Retirado==1){
                    color1='success';  
                    }else{
                    color1='muted';    
                    }
                }
            }else{
                color1='danger';
            }  
            return '<td><b>'+row.ClienteDestino+'</br>'+ 
                     '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificardir(this.id);"class="action-icon">'+
                     '<i class="mdi mdi-18px mdi-map-marker text-'+color1+'"></i><a class="text-muted">'+row.DomicilioDestino+'</td>';
              }
            },
            {data:"LocalidadDestino"},
            {data:"CodigoSeguimiento",
            render: function (data, type, row) {
              if(row.Retirado==1){
            var color='success';
            var servicio='Entrega';    
            }else{
            var color='warning';    
            var servicio='Retiro';  
            }  
            if(row.Retirado==1){

                return `<td class="table-action"><a>${row.NumeroComprobante}</a><br/><a>${row.CodigoSeguimiento}</a><br/><a><b><a value='${servicio}' href='#' class='badge badge-${color} mb-1 mt-1' style='font-size:10px' onclick='servicio_mod(${row.id},${row.Retirado})'>${servicio}</a></b></a><br/><a href='#' class='badge badge-success' style='font-size:10px'>${row.Hora}</a></td></td>`;
            
            }else{
            
                return `<td class="table-action"><a>${row.NumeroComprobante}</a><br/><a>${row.CodigoSeguimiento}</a><br/><a><b><a value='${servicio}' href='#' class='badge badge-${color} mb-1 mt-1' style='font-size:10px' onclick='servicio_mod(${row.id},${row.Retirado})'>${servicio}</a></b></a><br/><a href='#' class='badge badge-warning' style='font-size:10px'>${row.Hora_retiro}</a><br/><a href='#' class='badge badge-success mt-1' style='font-size:10px'>${row.Hora}</a></td></td>`;
            }
            
            
            }
            },
            {data:"Recorrido",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                  '<a style="cursor:pointer" data-id="' + row.CodigoSeguimiento + '" id="'+row.CodigoSeguimiento+'" onclick="modificarrecorrido(this.id);" ><b class="text-primary">'+row.Recorrido+'</b></a>'+
                '</td>';
             }
            },
            {data:"id",
           render: function (data, type, row) {
               let myLatLng = row.Latitud+","+row.Longitud;

                return `<td class="table-action d-print-none mt-4"><a data-id="${myLatLng}" id="${myLatLng}" onclick="ubicacion(this.id);" class="action-icon"> <i class="mdi mdi-18px mdi-map-marker text-danger"></i></a><a data-id="${row.id}" id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-primary"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a></td>`;
                // return `<td class="table-action d-print-none mt-4"><a data-id="${row.DomicilioDestino}" id="${row.DomicilioDestino}" onclick="ubicacion(this.id);" class="action-icon"> <i class="mdi mdi-18px mdi-map-marker text-danger"></i></a><a data-id="${row.id}" id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-primary"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a></td>`;
              }
            },
           
        ]
    });
});

$('#entregado').change(function(e) {
    if(this.checked) {
        $('#entregado').val(1);
        }else{
        $('#entregado').val(0);
        } 
});

function ubicacion(i){
    // Obtenemos la dirección y la asignamos a una variable
var address = i;
// Creamos el Objeto Geocoder
var geocoder = new google.maps.Geocoder();
// Hacemos la petición indicando la dirección e invocamos la función
// geocodeResult enviando todo el resultado obtenido
geocoder.geocode({ 'address': address}, geocodeResult);

//     var latitudReal = -27.798521169850478;
//     var longitudReal = -63.683109002298416;
//     var markerPosicionReal = new google.maps.Marker({
//         position: {
//           lat: latitudReal,
//           lng: longitudReal
//         },
//         title: "Mi actual ubicación"
//     });
//     markerPosicionReal.setMap(map);
//     // Si quieres centrar el mapa en el nuevo marker:
//     map.setCenter(markerPosicionReal.getPosition());
}

function modificarrecorrido(i){
$('#cs_modificar_REC').val(i); 
$.ajax({
        data:{'BuscarRecorridos':1,'cs':i},
        type: "POST",
        url: "Proceso/php/pendientes.php",
        success: function(response)
        {
        $('.selector-recorrido select').html(response).fadeIn();
        }
    });

$('#myCenterModalLabel_rec').html('Modificar Recorrido a Código '+i);   
$('#standard-modal-rec').modal('show');
  
}

$('#modificarrecorrido_ok').click(function(){

  var cs=$('#cs_modificar_REC').val();
  var r = $('#recorrido_t').val();
  
  $.ajax({
        data:{'ActualizaRecorrido':1,'r':r,'cs':cs},
        type: "POST",
        url: "Proceso/php/pendientes.php",
        success: function(response)
        {
         var jsonData=JSON.parse(response);
          if(jsonData.success==1){
         var datatable = $('#seguimiento').DataTable();
         datatable.ajax.reload(); 
        initMap();    
        $('#standard-modal-rec').modal('hide');
        $.NotificationApp.send("Registro Actualizado !","Se ha actualizado el Recorrido.","bottom-right","#FFFFFF","success");      
        }else{
        $.NotificationApp.send("Registro No Actualizado !","No pudimos actualizar el Recorrido.","bottom-right","#FFFFFF","danger");        
        }
       }
    });

  
});

function modificar(i){
$('#id_modificar').val(i);   
$('#standard-modal').modal('show'); 
 $('#myCenterModalLabel').html('Modificar id # '+i); 
}


$('#modificardireccion_ok').click(function(){
var entregado=$('#entregado').val();
var Fecha=$('#fecha_receptor').val();
var hora=$('#hora_receptor').val();
var i=$('#id_modificar').val();
var obs=$('#observaciones_receptor').val();  
  $('#myCenterModalLabel').html('Modificar id # '+i); 
  
if(entregado==1){  
  $.ajax({
      data:{'Actualiza':1,'id':i,'entregado':entregado,'Fecha':Fecha,'Hora':hora,'Observaciones':obs},
      url:'Procesos/php/pendientes.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        $.NotificationApp.send("Registro Actualizado !","Se ha actualizado la tabla Clientes correctamente.","bottom-right","#FFFFFF","success");    
       var datatable = $('#seguimiento').DataTable();
        datatable.ajax.reload();  
       $('#standard-modal').modal('hide'); 
     $('#form')[0].reset();
       }  
      });
   }else{
     $.NotificationApp.send("Presione Entregado !","No se realizaron cambios.","bottom-right","#FFFFFF","warning");    
   }
});

function eliminar(e) {
       $.ajax({
      data:{'BuscarDatos':1,'id':e},
      url:'Proceso/php/pendientes.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
       $('#warning-modal-body').html('Estas por eliminar el Registro '+e+ ' Origen '+jsonData.RazonSocial);
       $('#id_eliminar').val(e);
       $('#codigoseguimiento_eliminar').val(jsonData.CodigoSeguimiento);  
       $('#warning-modal').modal('show');
       }  
      });
   }
    $('#warning-modal-ok').click(function(){
      var id=$('#id_eliminar').val();
      var cs=$('#codigoseguimiento_eliminar').val();
      $.ajax({
            data:{'EliminarRegistro':1,'id':id,'CodigoSeguimiento':cs},
            url:'Proceso/php/pendientes.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
              $('#warning-modal').modal('hide');
              if(jsonData.success==1){
               if(jsonData.hojaderuta==1){ 
                $.NotificationApp.send("Registro Borrado !","Se ha borrado el registro en Hoja de Ruta correctamente.","bottom-right","#FFFFFF","success");  
               var datatable = $('#seguimiento').DataTable();
                datatable.ajax.reload();   
               }else{
                $.NotificationApp.send("Error !","No se han realizado cambios en Hoja de Ruta.","bottom-right","#FFFFFF","danger");       
               } 
               if(jsonData.transclientes==1){
               $.NotificationApp.send("Registro Borrado !","Se ha borrado el registro en Trans Clientes correctamente.","bottom-right","#FFFFFF","success");  
               var datatable = $('#seguimiento').DataTable();
               datatable.ajax.reload();  
               }else{
               $.NotificationApp.send("Error !","No se han realizado cambios en Trans Clientes.","bottom-right","#FFFFFF","danger");       
               } 
              }else{
              $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
              }
            }
        });  
     });