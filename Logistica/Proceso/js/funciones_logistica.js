var datatable = $('#seguimiento').DataTable({
    ajax: {
         url:"Procesos/php/servicios.php",
         data:{'Pendientes':1},
         processing: true,
         type:'post'
         },
        columns: [
            {data:"Fecha",
             render: function (data, type, row) {
              var Fecha=row.Fecha.split('-').reverse().join('.');
              return '<td><span style="display: none;">'+row.Fecha+'</span>'+Fecha+'</td>';  
              }
            },
            {data:"NumeroComprobante"},
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
            if(row.Retirado==1){
            var color1='success';  
            }else{
            color1='muted';    
            }  
            return '<td><b>'+row.ClienteDestino+'</br>'+  
                     '<i class="mdi mdi-18px mdi-map-marker text-'+color1+'"></i><a class="text-muted">'+row.DomicilioDestino+'</td>';
              }
            },
            {data:"CodigoSeguimiento",
            render: function (data, type, row) {
              if(row.Retirado==1){
            var color='success';
            var servicio='Entrega';    
            }else{
            var color='muted';    
            var servicio='Origen';  
            }  
                return '<td class="table-action">'+
                '<a>'+row.CodigoSeguimiento+'</a><br/>'+
                '<a><b>'+servicio+'</b></a>'+
                '</td>';
              }
            },
//           {data:"Recorrido"},
            {data:"Recorrido",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a style="cursor:pointer" data-id="' + row.CodigoSeguimiento + '" id="'+row.CodigoSeguimiento+'" onclick="modificarrecorrido(this.id);" ><b class="text-primary">'+row.Recorrido+'</b></a>'+
                '</td>';
             }
            },
            {data:"id",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
                '</td>';
              }
            },
           
        ]
});

$('#entregado').change(function(e) {
    if(this.checked) {
        $('#entregado').val(1);
        }else{
        $('#entregado').val(0);
        } 
});


function modificarrecorrido(i){
$('#cs_modificar_REC').val(i); 
$.ajax({
        data:{'BuscarRecorridos':1,'cs':i},
        type: "POST",
        url: "Procesos/php/pendientes.php",
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
        url: "Procesos/php/pendientes.php",
        success: function(response)
        {
         var jsonData=JSON.parse(response);
          if(jsonData.success==1){
         var datatable = $('#seguimiento').DataTable();
         datatable.ajax.reload(); 
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
      url:'Procesos/php/funciones.php',
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
            url:'Procesos/php/funciones.php',
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