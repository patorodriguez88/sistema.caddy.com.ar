function BuscarDireccion() {
        var inputstart = document.getElementById('direccion_nc');
        var autocomplete = new google.maps.places.Autocomplete(inputstart, { types: ['geocode','establishment'], componentRestrictions: {country: ['AR']}});
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
//CALCULO DISTANCIA
function distancia(){

  
}


$('#submit').click(function(){
var  datatable = $('#seguimiento').DataTable();
  datatable.ajax.reload();
});
var relacion=$('#relacion_nc').val();
console.log('relacion',relacion);

$(document).ready(function() {
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
         url:"Procesos/php/funciones.php",
         data:{'Importaciones':1,'Relacion':relacion},
         type:'post'
         },
        columns: [
            {data:"NombreCliente",
              render: function (data, type, row) {
                if(row.NombreClienteClientes===null){
                 var a='No Existe en Clientes'; 
                 var tr='text-success';
                }else{
                 var a="Si Existe en Clientes"; 
                var tr='text-danger';
                }
                if(row.Latitud!=''){
                var marker='success';
                var lat=row.Latitud;
                var lng=row.Longitud;  
                }else{
                var marker='muted';  
                var lat='Cordenadas';
                var lat='Error';  
                }
                if(row.Km>100){
                var atencion='Atencion ! '+row.Km;  
                }else{
                atencion=row.Km;  
                }
                  return '<tr class="table-success"><td><span class="'+tr+'"><dt>['+row.idProveedor+'] '+row.NombreCliente+' '+a+'</dt></span></br>'+  
                  '<i class="mdi mdi-18px mdi-map-marker text-'+marker+' "></i><a class="text-muted">'+row.Direccion +' ('+lat+' '+lng+')('+atencion+')</td></tr>';
              }
            },
          {data:"Cantidad"},
          {data:"id",
          render: function (data, type, row) {
            return  '<div class="progress progress-sm">'+
                    '<div class="progress-bar progress-lg bg-info" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>'+
                    '</div>';
            }
          },
          
          {data:"id",
           render: function (data, type, row) {
                  return '<td class="table-action">'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);"class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
                '</td>';
            }
          }
        ],
          select: {
            style:    'os',
            selector: 'td:first-child'
        },
  
  });  




$('#seguimiento').DataTable().on("draw", function(){
$('#outer').hide();
  card();
});

  
});


function card(){
    var relacion=$('#relacion_nc').val();
    $.ajax({
            data:{'Cantidades':1,'Relacion':relacion},
            url:'Procesos/php/funciones.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
            $('#clientesnuevos_card').html(jsonData.noexisten+' Clientes Nuevos');
            $('#clientesexistentes_card').html(jsonData.existen+' Clientes Existentes');
            $('#ventas_card').html(jsonData.todos+' Ventas Nuevas');
            $('#card').show();
            }
        });  
}
 
function modificar(e) {

   $('#id_nc').val(e);
    $.ajax({
      data:{'BuscarDatos':1,'id':e},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
        $('#standard-modal').modal('show');
        $('#myCenterModalLabel').html('Modificar Direccion a '+jsonData.data[0].nombrecliente);  
        $('#direccion_nc').val(jsonData.data[0].Direccion); 
         
       }  
  });   

$('#modificardireccion_ok').click(function(){
  var dir=$('#direccion_nc').val();
  var calle= $('#Calle_nc').val();
  var barrio= $('#Barrio_nc').val();
  var numero= $('#Numero_nc').val();
  var ciudad= $('#ciudad_nc').val();
  var cp= $('#cp_nc').val();
  var id=$('#id_nc').val();
  
  var origen="Reconquista 4986, Cordoba, Argentina";

  $.ajax({
      data:{'BuscarDistancia':1,'origen':origen,'destino':dir},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
      var km=jsonData.distancia/1000;
  
      $.ajax({
          data:{'ActualizarDireccion':1,'Direccion':dir,'id':id,'calle':calle,'barrio':barrio,'numero':numero,'ciudad':ciudad,'cp':cp,'km':km},
          url:'Procesos/php/funciones.php',
          type:'post',
          success: function(response)
           {
            var jsonData = JSON.parse(response);
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();  
           $('#standard-modal').modal('hide');
           }  
        });
      }
    });
});
}

function eliminar(e) { //ELIMINAR
$('#warning-modal-body').html('Realmente eliminaras el registro '+e+ ' ?'); 
$('#id_eliminar').html(e);  
$('#warning-modal').modal('show');
}

$('#warning-modal-ok').click(function(){
  var e=$('#id_eliminar').html();
      $.ajax({
            data:{'EliminarRegistro':1,'id':e},
            url:'Procesos/php/funciones.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
              if(jsonData.success==1){
              $('#warning-modal').modal('hide');  
              $.NotificationApp.send("Registro Borrado !","Se ha borrado el registro correctamente.","bottom-right","#FFFFFF","success");  
              var datatable = $('#seguimiento').DataTable();
              datatable.ajax.reload();  
              card();  
              }else{
              $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
              }
            }
        });  
     });



function vaciar_tabla(){
$.ajax({
      data:{'VaciarTabla':1},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
        if(jsonData.success==1){
        $.NotificationApp.send("Tabla Borrada !","Se han borrado " + jsonData.regborrados + " registros.","bottom-right","#FFFFFF","success");  
        $('#tabla').hide();
        $('#VaciarTabla').hide();
        $('#response').hide();  
        $('#card').hide();   
        $('#outer').show();
        $('#ImportarTabla').hide();
          window.location("https:www.caddy.com.ar/SistemaTriangular/Importar/index.php");
        }else{
        $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
        }
      }
  });  
}
$('#ImportarTabla').click(function(){
  var nuevosclientes=$('#clientesnuevos_card').html();
  var nuevasventas=$('#ventas_card').html();
 $('#importar-modal-body').html('Confirmas Importar la tabla ?'+ 'se crearan '+nuevosclientes+'. Y cargaremos '+ nuevasventas);
 $('#importar-modal').modal('show');
  
});


$('#importar-modal-ok').click(function(){
    var relacion=$('#relacion_nc').val();
    $.ajax({
      data:{'ImportarTabla':1,'Relacion':relacion},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
        if(jsonData.success==1){
        $('#importar-modal').modal('hide');
        $('#success-alert-modal').modal('show');  
        $('#success-info').html('Se ingresaron '+jsonData.importados+' Clientes y se cargaron '+jsonData.preventa+' envios a Preventa');  
        }else{
        $.NotificationApp.send("Error !","No se han ingresado registros a Clientes.","bottom-right","#FFFFFF","danger");    
        }
      }
  });  
});
                          
$('#VaciarTabla').click(function(){
vaciar_tabla();
});
