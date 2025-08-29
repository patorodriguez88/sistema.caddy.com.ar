$(document).ready(function () {
  $('#frmExcelImport').on('submit', function (e) {
      e.preventDefault(); // Prevenir el envío normal del formulario

      const formData = new FormData(this); // Recolectar los datos del formulario
     
      $.ajax({
          url: 'Procesos/php/importar.php', // Endpoint para procesar el Excel
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
              // const data = JSON.parse(response); // Parsear la respuesta JSON
              const data = response;
              if (!data.success) {
                  alert(data.error);
                  return;
              }

              // Generar la tabla dinámica
              let tableHtml = `
              <div class="row">
                <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="header-title mt-2">RELACIÓN DE CAMPOS</h4>
                      <table class="table table-centered mb-0" style="font-size:12px" id="mappingTable">
                        <thead>
                          <tr>
                            <th>Título del Excel</th>
                            <th>Campo de la Base de Datos</th>
                          </tr>
                        </thead>
                        <tbody>
              `;

              // Agregar filas dinámicamente
              data.titles.forEach(function (title) {
                  tableHtml += `
                  <tr>
                      <td class="excel-title">${title}</td>
                      <td>
                          <select name="${title}">
                              <option value="">Seleccionar campo</option>
                              ${data.dbFields.map(function (field) {
                                  return `<option value="${field}">${field}</option>`;
                              }).join('')}
                          </select>
                      </td>
                  </tr>
                  `;
              });

              tableHtml += `
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              `;

              $('#tableContainer').html(tableHtml); // Mostrar la tabla generada
          },
          error: function () {
              $('#response').html("Hubo un error al subir el archivo.");
          }
      });
  });

// Buscar Clientes
$.ajax({
  url: 'Procesos/php/funciones_.php', // Archivo PHP
  method: 'POST', // Cambiamos a POST
  dataType: 'json',
  data: { Clientes: 1 }, // Datos enviados en el cuerpo de la solicitud
  success: function (response) {
    
    if (response.success) {
          // Limpiar el select y agregar opciones
          const tarifaSelect = $('#relacion_nc');
          tarifaSelect.empty(); // Limpiar el select
          tarifaSelect.append('<option value="">Seleccionar...</option>');
          
          // Iterar sobre los datos recibidos y agregar las opciones
          response.data.forEach(function (cliente) {
              tarifaSelect.append(
                  `<option value="${cliente.id}">${cliente.nombrecliente}</option>`
              );
          });
      } else {
          alert('Error al cargar los productos: ' + response.error);
      }
  },
  error: function (xhr, status, error) {
      alert('Error en la solicitud AJAX: ' + error);
  }
});

// Buscar Tarifas
$.ajax({
  url: 'Procesos/php/funciones_.php', // Archivo PHP
  method: 'POST', // Cambiamos a POST
  dataType: 'json',
  data: { Tarifas: 1 }, // Datos enviados en el cuerpo de la solicitud
  success: function (response) {
    
    if (response.success) {
          // Limpiar el select y agregar opciones
          const tarifaSelect = $('#tarifa');
          tarifaSelect.empty(); // Limpiar el select
          tarifaSelect.append('<option value="">Seleccionar...</option>');
          
          // Iterar sobre los datos recibidos y agregar las opciones
          response.data.forEach(function (producto) {
              tarifaSelect.append(
                  `<option value="${producto.id}">${producto.Titulo}</option>`
              );
          });
      } else {
          alert('Error al cargar los productos: ' + response.error);
      }
  },
  error: function (xhr, status, error) {
      alert('Error en la solicitud AJAX: ' + error);
  }
});




});

$(document).on("click", "#submitMapping", function () {
  const rows = document.querySelectorAll("#mappingTable tbody tr");
  const mappedData = [];

  // Recolecta los datos mapeados
  rows.forEach((row) => {
      const excelTitle = row.querySelector(".excel-title").textContent; // Título de Excel
      const dbField = row.querySelector("select").value; // Campo de la base de datos seleccionado

      if (dbField) {
          mappedData.push({
              excelTitle: excelTitle,
              dbField: dbField,
          });
      }
  });

  // Validar que hay datos mapeados
  if (mappedData.length === 0) {
      alert("Debes mapear al menos un campo.");
      return;
  }

  // Obtener el archivo Excel seleccionado
  var fileInput = document.getElementById('file');
  var file = fileInput.files[0]; // Obtener el archivo del input


  if (!file) {
      alert("Por favor selecciona un archivo.");
      return;
  }

  // Crear un FormData para el archivo
  const formData = new FormData();
  formData.append("file", file); // Se añade el archivo al FormData
  formData.append("mappedData", JSON.stringify(mappedData)); // mappedData con la relación Excel -> Base de Datos
  const relacionNcValue = $('#relacion_nc').val();
  formData.append('relacion_nc', relacionNcValue);
  
  $.ajax({
    // url: 'Procesos/php/guardardatos.php', // Endpoint para procesar el Excel
    url: 'Procesos/php/importar_update.php', // Endpoint para procesar el Excel
    type: 'POST',
    data: formData,
    contentType: false, // No establecer el tipo de contenido
    processData: false, // No procesar los datos
    success: function (response) {
        // Procesar la respuesta del servidor
        console.log(response);
    },
    error: function () {
        // alert("Hubo un error al subir el archivo.");
    }
  });
});



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
/*
 * @param String name
 * @return String
 */
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
var id=getParameterByName('id');

if(id!=''){
    $('#outer').css('display','none');
    // $('#ImportarRecorridos').css('display','block');    
    $('#num_comp').html(id);
    ver_tabla(id);
card(id);    
}else{
    // $('#ImportarRecorridos').css('display','none');    
}

$('#ImportarRecorridos').click(function(){
    var i=getParameterByName('id');
    $.ajax({
        data:{'ImportarRecorridos':1,'nc':i},
        url:'Procesos/php/funciones_.php',
        type:'post',
        success:function(response){
        var jsonData = JSON.parse(response);
        $('#success-alert-modal').modal('show');
        $('#fecha_1').html(jsonData.data[0]);
        $('#fecha_2').html(jsonData.data[1]);
        $('#fecha_3').html(jsonData.data[2]);
        $('#fecha_4').html(jsonData.data[3]);
        $('#fecha_5').html(jsonData.data[4]);
        
        }
    });  
});

$('#Eliminar_lote').click(function(){
    var i=getParameterByName('id');
    $('#warning-modal').modal('show');
    $('#warning-modal-body').html('Estas por eliminar todos los registros del Comprobante '+i);
    $('#warning-modal-ok').click(function(){
    $.ajax({
        data:{'Eliminar_lote':1,'nc':i},
        url:'Procesos/php/funciones_.php',
        type:'post',
        success:function(response){
        var jsonData = JSON.parse(response);
          
            if(jsonData.success==1){
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();                 
            
            
            $('#warning-modal').modal('hide');  
            $.NotificationApp.send("Registro Borrado !","Se ha borrado el registro correctamente.","bottom-right","#FFFFFF","success");  
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();  
            var datatable = $('#seguimiento_group').DataTable();
            datatable.ajax.reload();  
            card();  
            }else{
            $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
            }




        }
    }); 
    }); 
});

var divs = document.getElementsByClassName("badge bg-secondary text-light");
    
    //Recorres la lista de elementos seleccionados
    for (var i=0; i< divs.length; i++) {
        //Añades un evento a cada elemento
        divs[i].addEventListener("click",function() {
           //Aquí la función que se ejecutará cuando se dispare el evento
           var fecha=this.innerHTML; //En este caso alertaremos el texto del cliqueado
           var i=getParameterByName('id');
           $.ajax({
               data:{'ImportarRecorridos_accion':1,'nc':i,'fecha':fecha},
               url:'Procesos/php/funciones_.php',
               type:'post',
               success:function(response){
               var jsonData = JSON.parse(response);

               var datatable = $('#seguimiento').DataTable();
               datatable.ajax.reload();  
               
               console.log('rows',jsonData)

              }
           });  

        });
    }


$('#VerAgrupados').click(function(){
var nc=$('#num_comp').html();

if ($('#tabla_group').is(':hidden')) {
    $('#tabla_group').css('display','block');
    }else{
    $('#tabla_group').css('display','none');    
}

});


$('#submit').click(function(){
// ver_tabla();
});

//TABLA SEGUIMIENTO GROUP
// $(document).ready(function() {
//     var datatable = $('#seguimiento_group').DataTable({
//     paging: false,
//     searching: false,
//     lengthMenu: [
//         [10, 25, 50, -1],
//         [10, 25, 50, 'All']
//       ],
//     ajax: {
//          url:"Procesos/php/funciones_.php",
//          data:{'Importaciones_group':1},
//          type:'post'
//          },
//         columns: [
//           {data:"Fecha"},   
//           {data:"Usuario"},              
//           {data:"RazonSocial"},
//           {data:"NumeroComprobante"},
//           {data:"Total"},
//           {data:"id",
//             render: function (data, type, row) {
//               var num=Number(row.NumeroComprobante);
//               return '<td class="table-action">'+
//             '<a href="?id=' + num + '" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
//             '</td>';
//          }
//         }                              
//         ],
//           select: {
//             style:    'os',
//             selector: 'td:first-child'
//         },
    
//     });  
    
    
    
    
//     $('#seguimiento_group').DataTable().on("draw", function(){
//     // $('#outer').hide();
//     // card();
//     });
    
    
//     });

function ver_tabla(i){
// TABLA SEGUIMIENTO
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
     url:"Procesos/php/funciones_.php",
     data:{'Importaciones':1,'nc':i},
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
              return '<tr class="table-success"><td><span class="'+tr+'"><td>['+row.idProveedor+'] '+row.NombreCliente+' '+a+'</td></span></br>'+  
              '<i class="mdi mdi-18px mdi-map-marker text-'+marker+' "></i><a class="text-muted">'+row.Direccion +' ('+lat+' '+lng+')('+atencion+')</td></tr>';
          }
        },
      {data:"Cantidad"},
      {data:"Total"},
      {data:"Recorrido"},
      {data:"FechaEntrega"},
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
            // '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);"class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
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
// $('#outer').hide();
card();
});


});
}

function card(i){
$.ajax({
        data:{'Cantidades':1,'nc':i},
        url:'Procesos/php/funciones_.php',
        type:'post',
        success:function(response){
        var jsonData = JSON.parse(response);
        $('#clientesnuevos_card').html(jsonData.todos+' Registros');
        // $('#clientesexistentes_card').html(jsonData.existen+' Clientes Existentes');
        // $('#ventas_card').html(jsonData.todos+' Ventas Nuevas');
        $('#card').show();
        }
    });  
}

function modificar(e) {

$('#id_nc').val(e);
$.ajax({
  data:{'BuscarDatos':1,'id':e},
  url:'Procesos/php/funciones_.php',
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
  url:'Procesos/php/funciones_.php',
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

// function eliminar(e) { //ELIMINAR
// $('#warning-modal-body').html('Realmente eliminaras el registro '+e+ ' ?'); 
// $('#id_eliminar').html(e);  
// $('#warning-modal').modal('show');
// }

// $('#warning-modal-ok').click(function(){
// var e=$('#id_eliminar').html();
//   $.ajax({
//         data:{'EliminarRegistro':1,'id':e},
//         url:'Procesos/php/funciones_.php',
//         type:'post',
//         success:function(response){
//         var jsonData = JSON.parse(response);
//           if(jsonData.success==1){
//           $('#warning-modal').modal('hide');  
//           $.NotificationApp.send("Registro Borrado !","Se ha borrado el registro correctamente.","bottom-right","#FFFFFF","success");  
//           var datatable = $('#seguimiento').DataTable();
//           datatable.ajax.reload();  
//           card();  
//           }else{
//           $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
//           }
//         }
//     });  
//  });



// function vaciar_tabla(){
// $.ajax({
//   data:{'VaciarTabla':1},
//   url:'Procesos/php/funciones_.php',
//   type:'post',
//   success:function(response){
//   var jsonData = JSON.parse(response);
//     if(jsonData.success==1){
//     $.NotificationApp.send("Tabla Borrada !","Se han borrado " + jsonData.regborrados + " registros.","bottom-right","#FFFFFF","success");  
//     $('#tabla').hide();
//     $('#VaciarTabla').hide();
//     $('#response').hide();  
//     $('#card').hide();   
//     $('#outer').show();
//     $('#ImportarTabla').hide();
//       window.location("https:www.caddy.com.ar/SistemaTriangular/Importar/index.php");
//     }else{
//     $.NotificationApp.send("Error !","No se han realizado cambios.","bottom-right","#FFFFFF","danger");    
//     }
//   }
// });  
// }
// $('#ImportarTabla').click(function(){
// var nuevosclientes=$('#clientesnuevos_card').html();
// var nuevasventas=$('#ventas_card').html();
// $('#importar-modal-body').html('Confirmas Importar la tabla ?'+ 'se crearan '+nuevosclientes+'. Y cargaremos '+ nuevasventas);
// $('#importar-modal').modal('show');

// });

// $('#importar-modal-ok').click(function(){
// $.ajax({
//   data:{'ImportarTabla':1},
//   url:'Procesos/php/funciones_.php',
//   type:'post',
//   success:function(response){
//   var jsonData = JSON.parse(response);
//     if(jsonData.success==1){
//     $('#importar-modal').modal('hide');
//     $('#success-alert-modal').modal('show');  
//     $('#success-info').html('Se ingresaron '+jsonData.importados+' Clientes y se cargaron '+jsonData.preventa+' envios a Preventa');  
//     }else{
//     $.NotificationApp.send("Error !","No se han ingresado registros a Clientes.","bottom-right","#FFFFFF","danger");    
//     }
//   }
// });  
// });
                      
// $('#VaciarTabla').click(function(){
// vaciar_tabla();
// });
