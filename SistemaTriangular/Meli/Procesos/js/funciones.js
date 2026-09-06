

// Realizar una solicitud Ajax para obtener los datos
// $.ajax({
//     data:{'forzador_pending':1},
//     url: "Procesos/php/funciones.php",
//     type: "POST",    
//     success: function (response) {
//         var jsonData = JSON.parse(response);
//         if(jsonData.success==1){
//             if($jsonData.total>0){
//             toast("success", "Exito !", "Importación de "+jsonData.total+" Pendientes Exitosa.");
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
                                    
//                                         toast("success", "Exito !", "Importación Exitosa.");
                                    
//                                     }else if(jsonData.DATA==0){

//                                         toast("warning", "Atención !", 'Error de Consulta, no se cargó');   
                                    
//                                     }else if(jsonData.DATA==2){
                                    
//                                         toast("warning", "Atención !", 'Shipping Id Duplicado, no se cargó');   
                                    
//                                     }else if(jsonData.DATA==3){

//                                         toast("warning", "Atención !", 'Error en C.P., no se cargó');   
                                    
//                                     }else if(jsonData.DATA==4){
                                        
//                                         toast("warning", "Atención !", 'Este envio ya se encuentra entregado, no se cargó');   
                                    
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

// ============================================================
// CARGAR POR CODIGO (Meli): buscar un shipment puntual con el token
// del cliente origen y, si el operador confirma, cargarlo a Importaciones
// igual que si hubiese entrado por la importacion automatica de ordenes
// (BuscarOrdenes en orders.php). Reemplaza al viejo "Forzador Meli"
// (comentado arriba), que dependia de dos servicios externos que ya no
// controlamos (notifications.travelsupport.tur.ar y caddy.com.ar/api) -
// esta version pega directo a la API de Meli con el token propio del
// cliente, mismo mecanismo que ya usa la importacion automatica.
// ============================================================

// Traduce el codigo de error del backend a algo accionable - antes todo lo
// que no fuera YA_CARGADO/NO_ENCONTRADO caia en un generico "no se pudo
// buscar", y CLIENTE_SIN_TOKEN vs REFRESH_TOKEN_FALLO son causas bien
// distintas para el operador (una es "elegiste mal el cliente", la otra es
// "hay que re-vincular Meli con ese cliente").
function motivoForzadorError(jsonData) {
  switch (jsonData.error) {
    case "YA_CARGADO":
      return "Este shipments_id ya está cargado en Importaciones.";
    case "NO_ENCONTRADO":
      return "No se encontró ese envío en Meli para el cliente elegido (¿es el cliente correcto?).";
    case "CLIENTE_SIN_TOKEN":
      return "Ese cliente no tiene un token de Meli vinculado.";
    case "REFRESH_TOKEN_FALLO":
      return "El token de Meli de ese cliente venció y no se pudo renovar - hay que re-vincular la cuenta de Meli desde la ficha del cliente.";
    default:
      return jsonData.message || "No se pudo buscar el envío.";
  }
}

function resetForzadorModal() {
  $("#forzador_card").hide();
  $("#forzador_alert_error").hide().text("");
  $("#forzador_alert_success").hide().text("");
  $("#wait_id").text("");
  $("#button_ok_forzador").prop("disabled", true);
}

$("#forzador-modal").on("shown.bs.modal", function () {
  resetForzadorModal();
  $("#forzador_shipments_id").val("").trigger("focus");

  var $sel = $("#opciones");
  if ($sel.data("select2")) {
    $sel.select2("destroy");
  }
  $sel.empty();
  // Elegir el cliente es obligatorio: existe meliShipmentAutoDetect() en
  // meli_api.php para probar el token de todos los clientes en paralelo,
  // pero eso son ~56 llamados a Meli por cada escaneo - probamos la idea y
  // se descartó por gasto de recursos innecesario, se pide el cliente
  // primero como cualquier busqueda con token puntual.
  $sel.append($("<option>", { value: "", text: "Seleccioná un cliente..." }));
  $sel.select2({ dropdownParent: $("#forzador-modal"), width: "100%" });

  $.ajax({
    data: { MeliClientesToken: 1 },
    url: "Procesos/php/funciones.php",
    type: "POST",
    dataType: "json",
    success: function (data) {
      $.each(data, function (index, item) {
        $sel.append($("<option>", { value: item.id, text: item.nombrecliente }));
      });
      $sel.val("").trigger("change");
    },
    error: function (error) {
      console.error("Error al traer clientes con Meli:", error.statusText);
    },
  });
});

$("#button_buscar_forzador").click(function () {
  var customer_id = $("#opciones").val();
  var raw = $("#forzador_shipments_id").val();

  resetForzadorModal();

  if (!customer_id) {
    $("#forzador_alert_error").text("Elegi el cliente origen primero.").show();
    return;
  }
  if (!raw) {
    $("#forzador_alert_error").text("Escaneá o escribí el codigo del envio.").show();
    return;
  }

  $("#wait_id").removeClass("text-danger").addClass("text-success").text("Buscando en Meli...");

  $.ajax({
    data: { MeliForzarBuscar: 1, customer_id: customer_id, raw: raw },
    type: "POST",
    url: "Procesos/php/funciones.php",
    dataType: "json",
    success: function (jsonData) {
      $("#wait_id").text("");

      if (jsonData.success == 1) {
        var d = jsonData.data;
        $("#fc_nombre").text(d.nombre || "-");
        $("#fc_telefono").text(d.telefono || "-");
        $("#fc_direccion").text(d.direccion || "-");
        $("#fc_ciudad").text(d.ciudad || "-");
        $("#fc_cp").text(d.cp || "-");
        $("#fc_estado").text(d.estado || "-");
        $("#fc_logistic").text(d.logistic_type || "-");
        $("#fc_shipment_id").text(d.shipments_id || "-");
        $("#fc_valor").text(d.valor_declarado || "0");
        $("#forzador_card").show();
        $("#button_ok_forzador").prop("disabled", false);
      } else {
        var motivo = motivoForzadorError(jsonData);
        $("#forzador_alert_error").text(motivo).show();
      }
    },
    error: function (xhr) {
      $("#wait_id").text("");
      $("#forzador_alert_error").text("Error de conexión buscando en Meli. Probá de nuevo.").show();
      console.error("MeliForzarBuscar error:", xhr.status, xhr.responseText);
    },
  });
});

$("#button_ok_forzador").click(function () {
  var customer_id = $("#opciones").val();
  var raw = $("#forzador_shipments_id").val();

  $("#wait_id").removeClass("text-danger").addClass("text-success").text("Importando...");
  $(this).prop("disabled", true);

  $.ajax({
    data: { MeliForzarConfirmar: 1, customer_id: customer_id, raw: raw },
    type: "POST",
    url: "Procesos/php/funciones.php",
    dataType: "json",
    success: function (jsonData) {
      $("#wait_id").text("");

      if (jsonData.success == 1) {
        toast("success", "Éxito!", "Envío importado correctamente.");
        var datatable = $("#envios").DataTable();
        datatable.ajax.reload();
        $("#forzador-modal").modal("hide");
      } else {
        var motivo =
          jsonData.error === "YA_CARGADO"
            ? "Este shipments_id ya se cargó (lo importó otro operador mientras tanto)."
            : motivoForzadorError(jsonData);
        $("#forzador_alert_error").text(motivo).show();
        $("#button_ok_forzador").prop("disabled", false);
      }
    },
    error: function (xhr) {
      $("#wait_id").text("");
      $("#forzador_alert_error").text("Error de conexión importando el envío. Probá de nuevo.").show();
      $("#button_ok_forzador").prop("disabled", false);
      console.error("MeliForzarConfirmar error:", xhr.status, xhr.responseText);
    },
  });
});

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
        
            toast("success", "Exito !", "Importación Eliminada.");
            
            }else{
                
                toast("error", "Error !", "Hubo un Problema al eliminar la Importación.");  
            
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
    
            toast("success", "Exito !", "Pre Venta cargada.");

            }else{
            
            toast("error", "Error !", "Hubo un Problema al cargar la Pre Venta.");  
            
            }
        }
    });
}

$(document).ready(function() {
    
    //MUESTRO LA TABLA
    var datatable = $('#envios').DataTable({
        dom: 'Bfrtip',
        buttons: buildDtButtons(['pageLength', 'copy', 'excel', 'pdf']),
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
                         `<td class="table-action"><a onclick="showmodal(${row.id});" class="action-icon"> <i class="mdi mdi-trash-can text-danger"></i></a></td>`;
                }
              },
             
          ]
    });
});