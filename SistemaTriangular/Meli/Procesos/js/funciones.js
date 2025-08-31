

// Realizar una solicitud Ajax para obtener los datos
// $.ajax({
//     data:{'forzador_pending':1},
//     url: "Procesos/php/funciones.php",
//     type: "POST",    
//     success: function (response) {
//         var jsonData = JSON.parse(response);
//         if(jsonData.success==1){
//             if($jsonData.total>0){
//             $.NotificationApp.send("Exito !", "Importación de "+jsonData.total+" Pendientes Exitosa.", "bottom-right", "#FFFFFF", "success");
//             }
//         }else{
         
            
//         }

//     },
//     error: function (error) {

//         console.error("Error en la solicitud Ajax: " + error.statusText);
    
//     }
// });





// $('#forzador-modal').on('shown.bs.modal', function () {

//     // Realizar una solicitud Ajax para obtener los datos
//         $.ajax({
//             data:{'forzador':1},
//             url: "Procesos/php/funciones.php",
//             type: "POST",
//             dataType: "json",
//             success: function (data) {
//                 // Llenar el select con los datos obtenidos
//                 var select = $("#opciones");

//                 $.each(data, function (index, item) {
//                     select.append($('<option>', {
//                         value: item.id,
//                         text: item.nombrecliente
//                     }));
//                     // select.append(option);

                    
//                 });
//             },
//             error: function (error) {

//                 console.error("Error en la solicitud Ajax: " + error.statusText);
            
//             }
//         });

//     });

// $('#button_ok_forzador').click(function(){

//     let customer_id=$('#opciones').val();
    
//     let shipments_id=$('#forzador_shipments_id').val();

//     console.log('ship',shipments_id);

//     if(shipments_id){
//         // Mostrar un mensaje antes de la espera

//         $("#wait_id").removeClass("text-danger").addClass("text-success").html("Comprobando Token de Meli...");

//         $.ajax({
//                 data:{'forzador_api':1,'customer_id':customer_id,'shipments_id':shipments_id},
//                 type: "POST",
//                 url:"Procesos/php/funciones.php",
//                 beforeSend: function(xhr) {
//                     // Agregar una espera de 4 segundos (4000 milisegundos) antes de que se realice la solicitud
//                     setTimeout(function() {
//                         $('#wait_id').html("Actualizando...");
//                         // Aquí puedes realizar cualquier acción que desees después de la espera
//                     }, 4000);
//                 },
//                 success: function(response)
//                 {
//                     var jsonData = JSON.parse(response);
                    
//                     console.log('return',jsonData);

//                     if(jsonData.success==1){

//                         $('#wait_id').html("Token actualizado con exito...");
//                         // console.log('result',jsonData.dato);

//                         $.ajax({
//                             data:{'forzador_api':2,'customer_id':customer_id,'shipments_id':shipments_id},
//                             type: "POST",
//                             url:"Procesos/php/funciones.php",
//                             beforeSend: function(xhr) {
//                                 // Agregar una espera de 4 segundos (4000 milisegundos) antes de que se realice la solicitud
//                                 setTimeout(function() {
//                                     $('#wait_id').html("Actualizando...");
//                                     // Aquí puedes realizar cualquier acción que desees después de la espera
//                                 }, 4000);
//                             },
//                             success: function(response)
//                             {
//                                 var jsonData = JSON.parse(response);
                                
//                                 console.log('return',jsonData);
                    
//                                 if(jsonData.success==1){
                                    
//                                     var datatable = $('#envios').DataTable();
//                                     datatable.ajax.reload();
                                    
//                                     if(jsonData.DATA>5){
                                    
//                                         $.NotificationApp.send("Exito !", "Importación Exitosa.", "bottom-right", "#FFFFFF", "success");
                                    
//                                     }else if(jsonData.DATA==0){

//                                         $.NotificationApp.send("Atención !", 'Error de Consulta, no se cargó', "bottom-right", "#FFFFFF", "warning");   
                                    
//                                     }else if(jsonData.DATA==2){
                                    
//                                         $.NotificationApp.send("Atención !", 'Shipping Id Duplicado, no se cargó', "bottom-right", "#FFFFFF", "warning");   
                                    
//                                     }else if(jsonData.DATA==3){

//                                         $.NotificationApp.send("Atención !", 'Error en C.P., no se cargó', "bottom-right", "#FFFFFF", "warning");   
                                    
//                                     }else if(jsonData.DATA==4){
                                        
//                                         $.NotificationApp.send("Atención !", 'Este envio ya se encuentra entregado, no se cargó', "bottom-right", "#FFFFFF", "warning");   
                                    
//                                     }
                                    
//                                     $('#forzador_shipments_id').val("");

//                                     $('#opciones').val("");

//                                     $('#forzador-modal').modal('hide');
                                    
//                                 }else{
                    
//                                     $("#wait_id").removeClass("text-success").addClass("text-danger").html("Error: Shiping_id no encontrado...");
                                
//                                 }
//                             },function(xhr, status, error) {
//                                 // Manejar errores
//                                 console.error(status, error);
                    
//                             }
                        
//                         });
                        
//                         // if(jsonData.dato==0){/

//                             // $('.alert.alert-danger').css('display','block');


//                         // }

//                     }else{

//                         $("#wait_id").removeClass("text-success").addClass("text-danger").html("Error: Token no actualizado...");
                    
//                     }
//                 },function(xhr, status, error) {
//                     // Manejar errores
//                     console.error(status, error);

//                 }
//             });
        
//         }else{

//             $('.alert.alert-danger').css('display','block');

//         }

// });

//ELIMINAR
function showmodal(i){
    
$('#warning-modal').modal('show');
$('#id_eliminar').val(i);
$('#warning-modal-body').html('Estas por eliminar el id '+i);

$('#warning-modal-ok').click(function(){

    $.ajax({
        data:{'EliminarImportacion':1,'id':i},
        type: "POST",
        url:"Procesos/php/funciones.php",
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            
            if(jsonData.success==1){
            
            var datatable = $('#envios').DataTable();
            datatable.ajax.reload();
        
            $.NotificationApp.send("Exito !", "Importación Eliminada.", "bottom-right", "#FFFFFF", "success");
            
            }else{
                
                $.NotificationApp.send("Error !", "Hubo un Problema al eliminar la Importación.", "bottom-right", "#FFFFFF", "danger");  
            
            }
            $('#warning-modal').modal('hide');
        }
    });

});
}

//CARGAR PREVENTAS
function cargar(i){

    $.ajax({
        data:{'CargarPreVenta':1,'id_importaciones':i},
        type: "POST",
        url:"Procesos/php/funciones.php",
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            
            if(jsonData.success==1){
    
            var datatable = $('#envios').DataTable();
            datatable.ajax.reload();
    
            $.NotificationApp.send("Exito !", "Pre Venta cargada.", "bottom-right", "#FFFFFF", "success");

            }else{
            
            $.NotificationApp.send("Error !", "Hubo un Problema al cargar la Pre Venta.", "bottom-right", "#FFFFFF", "danger");  
            
            }
        }
    });
}

$(document).ready(function() {
    
    //MUESTRO LA TABLA
    var datatable = $('#envios').DataTable({
        dom: 'Bfrtip',
        buttons: ['pageLength', 'copy', 'excel', 'pdf'],
        paging: true,
        searching: true,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, 'All']
        ],
      ajax: {
           url:"Procesos/php/funciones.php",
           data:{'Envios':1},
           processing: true,
           type:'post',
          },
          columns: [
            {data:"RazonSocial",
              render: function (data,type,row){
                var Fecha = row.Fecha.split('-').reverse().join('.');
                // return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
    
              return `<td><b> ${row.RazonSocial}</b></br></td>`+
                     `<td> ${row.date_created}</br></td>`;
              }
            
              },
            {data:"shipments_id",
            render: function (data,type,row){
                return `<td><b> ${row.shipments_id}</b></br></td>`+
                        `<td><b> ${row.order_id}</b></br></td>`;
                }
            },
              {data:"DomicilioDestino",
              render: function (data, type, row) { 
                 return `<td><b>${row.ClienteDestino}</b></br>${row.DomicilioDestino}</td>`;
    
                }
              },
            //   {data:"Telefono",
            //   render: function (data, type, row) {             
            //        return `<td><i class="mdi mdi-18px mdi-phone text-success"></i> <b>${row.Celular}</b></td>`;
            //     }
                {data:"tracking_method",
                render: function (data, type, row) {             
                return `<span class="badge bg-dark text-white">${row.tracking_method}</span></br>`+
                       `<span class="badge bg-dark text-white">${row.agency_description}</span></br>`;
                }  
    
              },
              {data:"Cantidad",
              render: function (data, type, row) {
                  return `<td class="table-action col-xs-3"><b> ${row.Cantidad}</b></td>`;
                }
              },
              {data:"Precio",
              render: function (data, type, row) {
                   return `<td><a style="font-size:8px">Precio: $ ${row.Precio} </a></br></td>`+
                          `<td style="font-size:12px">Total: $ ${row.Total} </td>`;
                }   
            },
              {data:"Status",
             render: function (data, type, row) {
                 
                switch (row.Status) {
                    case 'delivered':
                        var color='success';
                        break;
    
                    case 'cancelled':
                        var color='danger';
                        break;
                    
                    case 'pending':
                        var color='warning';
                        break;
    
                    default:
                        var color='primary';
                        break;
                }
                 
                return `<span class="badge bg-${color} text-white">${row.Status}</span></br>`+
                       `<span class="badge bg-dark text-white">${row.logistic_type}</span></br>`;
                }
              },
              {data:"id",
             render: function (data, type, row) {
                  return `<td class="table-action"><a id="${row.id}" onclick="cargar(this.id,${row.id});" class="action-icon"> <i class="mdi mdi-truck-check-outline text-success"></i></a>`+
                         `<td class="table-action"><a onclick="showmodal(${row.id});" class="action-icon"> <i class="mdi mdi-trash-can-outline text-danger"></i></a></td>`;
                }
              },
             
          ]
    });
});