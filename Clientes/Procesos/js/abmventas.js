//MODIFICAR GUIA

$('#standard-modal-modificar').on('hide.bs.modal', function (e) {
//CAMBIAR ESTA TABLA POR LA TABLA QUE SE DESEE ACTUALIZAR
var datatable = $('#facturacion_tabla').DataTable();
datatable.ajax.reload(); 
//ESTA TABLA ES LA DE STANDARD-MODAL NO TOCAR  
var table = $('#ventas_tabla').DataTable();
table.destroy();
});

$('#standard-modal-modificar').on('show.bs.modal', function (e) {
  var triggerLink = $(e.relatedTarget);
  var i = triggerLink[0].dataset['id'];
//   var dato= triggerLink[0].dataset['title'];

  $.ajax({
      data:{'BuscarDatos':1,'id':i,'Nivel':1},
      url:'Procesos/php/abmventas.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        
        console.log('Entregado',jsonData.Entregado);

        if(jsonData.Entregado==1){
        
            $('#entregado').prop('checked',true);
            
            document.getElementById('entregado').disabled=true;

        
        }else{

            $('#entregado').prop('checked',false);
        }

       }
    });

var table = $('#ventas_tabla').DataTable();
table.destroy();
$('#id_modificar').val(i);   
$('#standard-modal-modificar').modal('show');
$('#id_trans').val(i);  
$('#myCenterModalLabel_modificar').html('Modificar id # '+i); 

var id= $('#id_modificar').val();  
  var datatable = $('#ventas_tabla').DataTable({
  bFilter: false, 
  bInfo: false,
  paging:false,
  search:false,  
  ajax: {
         url:"Procesos/php/abmventas.php",
         data:{'BuscarDatosVentas':1,'id':id},
         processing: true,
         type:'post'
        },
        columns: [
          {data:"idPedido"},
          {data:"FechaPedido"},
          {data:"Codigo"},
          {data:"Titulo"},
          {data:"Total"},
          {data:"idPedido",
           render: function (data, type, row) {
            return '<td class="table-action">'+
            '<a id="'+row.idPedido+'" onclick="modificarVentas(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>'+
            '<a id="'+row.idPedido+'" data-id="'+row.idPedido+'" data-tabla="ventas" data-toggle="modal" data-target="#warning-modal" class="action-icon"> <i class="mdi mdi-delete text-warning"></i></a>'+
            '<a id="'+row.NumPedido+'" onclick="agregarVentas(this.id);" class="action-icon"> <i class="mdi mdi-plus-circle text-success"></i></a>'+  
            '</td>';
            }
          }
          ]
        });
});

//AGREGAR VENTAS
function agregarVentas(id){

 $('#bs-ventas-modal-lg').modal('show');
 $('#header-ventas-modal').html('Agregar Venta a Codigo # '+id);
 $('#agregarventas_ok').show();
 $('#modificarventas_ok').hide();
 $('#ventas_fecha').css('readonly','false');
  
  $('#agregarventas_ok').click(function(){
   var titulo=$('#ventas_titulo').val();
   var codigoventa=$('#ventas_codigo').val();
   var cantidadventa=$('#ventas_cantidad').val();
   var precioventa = $('#ventas_precio').val();
   var totalventa= $('#ventas_total').val();
   var observacionesventa=$('#ventas_observaciones').val();   
   var fecha=$('#ventas_fecha').val();
   $.ajax({
      data: {'AgregarDatosVentas':1,'codigoseguimiento':id,'tituloventa':titulo,'codigoventa':codigoventa,
             'cantidadventa':cantidadventa,'precioventa':precioventa,'totalventa':totalventa,'observacionesventa':observacionesventa,
             'Fecha':fecha},
      url:'Procesos/php/abmventas.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
          $('#bs-ventas-modal-lg').modal('hide');
          var table = $('#ventas_tabla').DataTable();
          table.ajax.reload(null,false);
          }
       }
       });
 });
}
//MODIFICAR VENTAS
//  $('#modificarventas_ok').click(function(){
//         var idTrans=$('#id_trans').val();
//         var codigo = $('#ventas_codigo').val();         
//         var titulo =$('#ventas_titulo').val();
//         var total = $('#ventas_total').val();
//   $.ajax({
//       data:{'ModificarDatosVentas':1,'idPedido':id,'codigo':codigo,'titulo':titulo,'total':total,'idTrans':idTrans},
//       url:'Procesos/php/abmventas.php',
//       type:'post',
//       success: function(response)
//        {
//         var jsonData = JSON.parse(response);
//          $('#bs-ventas-modal-lg').modal('hide');
          
//          if(jsonData.successventas==1){
           
//           $.toast({
//           text: 'Se ha actualizado la tabla Ventas correctamente.',
//           icon: 'success',
//           heading: 'Exito !',
//           bgColor: 'success',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
  

//          }else{
//           $.toast({
//           text: 'No pudimos actualizar el registro en la tabla Ventas.',
//           icon: 'warning',
//           heading: 'Error!',
//           bgColor: 'warning',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
//          }
//          if(jsonData.successtrans==1){
//         $.toast({
//           text: 'Se ha actualizado la tabla Transacciones correctamente.',
//           icon: 'success',
//           heading: 'Exito !',
//           bgColor: 'success',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
//          }else{
//           $.toast({
//           text: 'No pudimos actualizar el registro en la tabla Transacciones.',
//           icon: 'warning',
//           heading: 'Error!',
//           bgColor: 'warning',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });

//          }
//          if(jsonData.successctasctes==1){
//            $.toast({
//           text: 'Se ha actualizado la tabla Cuentas Corrientes correctamente.',
//           icon: 'success',
//           heading: 'Exito !',
//           bgColor: 'success',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
//          }else{
//           $.toast({
//           text: 'No pudimos actualizar el registro en la tabla Cuentas Corrientes.',
//           icon: 'warning',
//           heading: 'Error!',
//           bgColor: 'warning',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
//          }
//          if(jsonData.successctasctesinsert==1){
//           $.toast({
//           text: 'Se ha insertado un registro en la tabla Cuentas Corrientes correctamente.',
//           icon: 'success',
//           heading: 'Exito !',
//           bgColor: 'success',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           }); 
//          }else{
//           $.toast({
//           text: 'No pudimos insertar el registro en la tabla Cuentas Corrientes.',
//           icon: 'warning',
//           heading: 'Error!',
//           bgColor: 'warning',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });
//          }
//        }
//     });
// });

// function eliminarVentas(idPedido){
// //    alert(a); 
//    $('#warning-modal').modal('show');
//    $('#warning-modal-body').html('Estas por eliminar el Registro de Ventas idPedido: '+idPedido );
//    $('#id_eliminar').val(idPedido);
// //    $('#codigoseguimiento_eliminar').val(cs);  
// $('#warning-modal-ventas-ok').css('display','block');
// $('#warning-modal-ok').css('display','none');

// }

// $('#eliminar_ventas').click(function(){  
  
//     let idPedido=$('#id_eliminar').val();
  
//     $.ajax({
//       data:{'EliminarDatosVentas':1,'idPedido':idPedido},
//       url:'Procesos/php/abmventas.php',
//       type:'post',
//       success: function(response)
//        {
//         var jsonData = JSON.parse(response);
//         if(jsonData.error==401){
//         $('#danger-alert-modal').modal('show');
//         }else{    
//         if(jsonData.success==1){ 
            
//          var datatable = $('#ventas_tabla').DataTable();
//           datatable.ajax.reload();  

//           $.toast({
//           text: 'Se ha actualizado la tabla Ventas correctamente.',
//           icon: 'success',
//           heading: 'Exito !',
//           bgColor: 'success',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
          
//         });
          
//         $('#warning-modal').modal('hide');

//         }else{
//         $.toast({
//           text: 'No pudimos eliminar el registro en la tabla Ventas.',
//           icon: 'warning',
//           heading: 'Error!',
//           bgColor: 'warning',
//           textColor: 'white',  
//           position: 'bottom-right',
//           showHideTransition: 'plain'
//           });  
//         }
//        } 
//     } 
//     });
// });

//ELIMINAR VENTAS
$('#warning-modal').on('show.bs.modal', function (e) {
  var triggerLink = $(e.relatedTarget);
//   console.log('trigger',triggerLink);

  var tabla = triggerLink[0].dataset['tabla'];
  var id = triggerLink[0].dataset['id'];
  var cs = triggerLink[0].dataset['title'];

 if(tabla=='ventas'){ 
 $('#warning-modal-ok').hide();
 $('#warning-modal-ventas-ok').show();
 
 $('#warning-modal-body').html('Estas por eliminar el Registro de Ventas idPedido: '+id);
 $('#id_eliminar').val(id);
 $('#codigoseguimiento_eliminar').val(cs);  
//  $('#warning-modal').modal('show');

//  $('#warning-modal-body').html('Estas por eliminar el Registro '+id+' Codigo de Seguimiento '+cs);
//  $('#id_eliminar').val(id);
 }
 
 if(tabla=='trans'){
 $('#warning-modal-ok').show();
 $('#warning-modal-ventas-ok').hide(); 
 $('#warning-modal-body').html('Estas por eliminar el Registro '+id+' Codigo de Seguimiento '+cs);
 $('#id_eliminar').val(id); 
}

$('#warning-modal-ventas-ok').click(function(){  
  $.ajax({
      data:{'EliminarDatosVentas':1,'idPedido':id},
      url:'Procesos/php/abmventas.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        if(jsonData.error==401){
        $('#danger-alert-modal').modal('show');
        }else{    
        if(jsonData.success==1){ 
         var datatable = $('#ventas_tabla').DataTable();
          datatable.ajax.reload(null,false);  

          $.toast({
          text: 'Se ha actualizado la tabla Ventas correctamente.',
          icon: 'success',
          heading: 'Exito !',
          bgColor: 'success',
          textColor: 'white',  
          position: 'bottom-right',
          showHideTransition: 'plain'
          
        });
          
        $('#warning-modal').modal('hide');

        }else{
        $.toast({
          text: 'No pudimos eliminar el registro en la tabla Ventas.',
          icon: 'warning',
          heading: 'Error!',
          bgColor: 'warning',
          textColor: 'white',  
          position: 'bottom-right',
          showHideTransition: 'plain'
          });  
        }
       } 
    } 
    });
});


//ELIMINAR DATOS DE TRANSCLIENTES
    $('#warning-modal-ok').click(function(){
    //   var id=$('#id_eliminar').val();
    //   var cs=$('#codigoseguimiento_eliminar').val();
      $.ajax({
            data:{'EliminarRegistro':1,'id':id,'CodigoSeguimiento':cs},
            url:'Procesos/php/abmventas.php',
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
               
               var datatable = $('#facturacion_tabla').DataTable();
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

});

//MODIFICAR VENTAS

function modificarVentas(IDPEDIDO){

    $.ajax({
      data:{'BuscarDatosVentas':1,'idPedido':IDPEDIDO},
      url:'Procesos/php/abmventas.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
         $('#ventas_fecha').val(jsonData.data[0].FechaPedido);
         $('#ventas_codigo').val(jsonData.data[0].Codigo);         
         $('#ventas_titulo').val(jsonData.data[0].Titulo);
         $('#ventas_precio').val(jsonData.data[0].Precio);
         $('#ventas_cantidad').val(jsonData.data[0].Cantidad);
         $('#ventas_total').val(jsonData.data[0].Total);
         $('#bs-ventas-modal-lg').modal('show');
         $('#header-ventas-modal').html('Modificar Venta # '+IDPEDIDO);
         $('#id_ventas').val(IDPEDIDO);
         $('#ventas_observaciones').val(jsonData.data[0].Comentario);
         $('#agregarventas_ok').hide();
         $('#modificarventas_ok').show();        
       }  
      });
  
    }

  $('#modificarventas_ok').click(function(){
        var idTrans=$('#id_trans').val();
        var codigo = $('#ventas_codigo').val();         
        var titulo =$('#ventas_titulo').val();
        var total = $('#ventas_total').val();
        var cantidad = $('#ventas_cantidad').val();
        var precio = $('#ventas_precio').val();
        var IDPEDIDO=$('#id_ventas').val();
        var comentario=$('#ventas_observaciones').val();
        var fecha=$('#ventas_fecha').val();
        $.ajax({
            data:{'ModificarDatosVentas':1,'idPedido':IDPEDIDO,'codigo':codigo,'titulo':titulo,'total':total,'idTrans':idTrans,'cantidad':cantidad,'precio':precio,'comentario':comentario,'fecha':fecha},
            url:'Procesos/php/abmventas.php',
            type:'post',
            success: function(response)
            {
                var jsonData = JSON.parse(response);
                $('#bs-ventas-modal-lg').modal('hide');

                if(jsonData.successventas==1){
                
                $.toast({
                text: 'Se ha actualizado la tabla Ventas correctamente.',
                icon: 'success',
                heading: 'Exito !',
                bgColor: 'success',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
        
                }else{
                $.toast({
                text: 'No pudimos actualizar el registro en la tabla Ventas.',
                icon: 'warning',
                heading: 'Error!',
                bgColor: 'warning',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
                }
                if(jsonData.successtrans==1){
                $.toast({
                text: 'Se ha actualizado la tabla Transacciones correctamente.',
                icon: 'success',
                heading: 'Exito !',
                bgColor: 'success',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
                }else{
                $.toast({
                text: 'No pudimos actualizar el registro en la tabla Transacciones.',
                icon: 'warning',
                heading: 'Error!',
                bgColor: 'warning',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });

                }
                if(jsonData.successctasctes==1){
                $.toast({
                text: 'Se ha actualizado la tabla Cuentas Corrientes correctamente.',
                icon: 'success',
                heading: 'Exito !',
                bgColor: 'success',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
                }else{
                $.toast({
                text: 'No pudimos actualizar el registro en la tabla Cuentas Corrientes.',
                icon: 'warning',
                heading: 'Error!',
                bgColor: 'warning',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
                }
                if(jsonData.successctasctesinsert==1){
                $.toast({
                text: 'Se ha insertado un registro en la tabla Cuentas Corrientes correctamente.',
                icon: 'success',
                heading: 'Exito !',
                bgColor: 'success',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                }); 
                }else{
                $.toast({
                text: 'No pudimos insertar el registro en la tabla Cuentas Corrientes.',
                icon: 'warning',
                heading: 'Error!',
                bgColor: 'warning',
                textColor: 'white',  
                position: 'bottom-right',
                showHideTransition: 'plain'
                });
                }
            }
            });
        });

//FUNCION AL CERRAR EL FORM DE MODIFICAR VENTAS
$('#bs-ventas-modal-lg').on('hide.bs.modal', function () {
var datatable = $('#ventas_tabla').DataTable();
datatable.ajax.reload(null,false);  
$('#form_bs-ventas-modal-lg')[0].reset();  
});


//FUNCION CANTIDAD
$('#ventas_cantidad').blur(function(){
 var precio = Number(document.getElementById('ventas_precio').value); 
 var cantidad = document.getElementById('ventas_cantidad').value; 
  document.getElementById('ventas_total').value=precio*cantidad; 
})
$('#ventas_precio').blur(function(){
 var precio = Number(document.getElementById('ventas_precio').value); 
 var cantidad = document.getElementById('ventas_cantidad').value; 
  document.getElementById('ventas_total').value=precio*cantidad; 
})
//FUNCION CARGAR
function cargar(id){
//   alert(id);
  var dato={"id_servicio":id};    
  $.ajax({
      data: dato,
      url:'Procesos/php/abmventas.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
           document.getElementById('ventas_titulo').value=jsonData.Titulo; 
           document.getElementById('ventas_precio').value=jsonData.PrecioVenta;
           document.getElementById('ventas_codigo').value=jsonData.Codigo;
           var cantidad=document.getElementById('ventas_cantidad').value;
           document.getElementById('ventas_total').value=jsonData.PrecioVenta*cantidad; 
           }
        }  
  });
}

//FUNCION CERRAR MODAL OK 
$('#modificardireccion_ok').click(function(){
    
    var switchElement = document.getElementById('entregado');
    var labelElement = document.getElementById('entregado_t_label');
    // Verificar si el switch está activo
    var entregado = switchElement.checked ? labelElement.getAttribute('data-on-label') : labelElement.getAttribute('data-off-label');

    var Fecha=$('#fecha_receptor').val();
    var hora=$('#hora_receptor').val();
    var i=$('#id_modificar').val();
    var obs=$('#observaciones_receptor').val();  
     $('#myCenterModalLabel').html('Modificar id # '+i); 
  
    if(entregado==1){  
    $.ajax({
        data:{'Actualiza':1,'id':i,'entregado':entregado,'Fecha':Fecha,'Hora':hora,'Observaciones':obs},
        url:'Procesos/php/abmventas.php',
        type:'post',
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            $.NotificationApp.send("Registro Actualizado !","Se ha actualizado la tabla Clientes correctamente.","bottom-right","#FFFFFF","success");    
        var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload(null,false);  
        $('#standard-modal-modificar').modal('hide'); 
        $('#form')[0].reset();
        }  
        });
    }else{
        $.NotificationApp.send("Presione Entregado !","No se realizaron cambios.","bottom-right","#FFFFFF","warning");    
    }
});

