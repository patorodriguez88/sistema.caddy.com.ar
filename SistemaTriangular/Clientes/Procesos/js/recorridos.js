$('#asign_another_order').click(function(){

    var orden=$('#asign_another_order_input').val();
    
    $.ajax({
        data: {
          'Recorridos_insert': 1,
          'NOrden': orden          
        },
        url: 'Procesos/php/recorridos.php',
        type: 'post',
        beforeSend: function() {
          // $("#buscando").html("Buscando...");
          // alert('enviando...');
        },
        success: function(respuesta) {
          var jsonData = JSON.parse(respuesta);
          if(jsonData.success==1){
              var d=jsonData.data[0];
              console.log(jsonData);
              $('#asign_another_order_row0').css('display','none');
              $('#asign_another_order_row1').css('display','block');
              $('#td_1_row1').html(d.Fecha);
              $('#td_2_row1').html(d.Recorrido);
              $('#td_3_row1').html('Orden: '+d.NumerodeOrden+'</br> Cliente: '+d.Cliente+'</br> '+d.Hora+' - '+d.HoraRetorno);
              $('#asign_another_order_label').html('Fecha: '+d.Fecha+' Recorrido: '+d.Recorrido+' Kilometros: '+d.KilometrosRecorridos);
          }
        }
    });

});

$('#asign_another_order_insert_serv').click(function(){
    // Recorridos_insert_ctasctes
    var orden=$('#asign_another_order_input').val();
    var importe=$('#asign_another_order_input_importe').val();
    var id = document.getElementById('codigo').value;
    // alert(id);
    // alert(orden);

    $.ajax({
        data: {
          'Recorridos_insert_ctasctes': 1,
          'NOrden': orden,
          'Importe': importe,
          'idCliente':id          
        },
        url: 'Procesos/php/recorridos.php',
        type: 'post',
        beforeSend: function() {
          // $("#buscando").html("Buscando...");
          // alert('enviando...');
        },
        success: function(respuesta) {
          var jsonData = JSON.parse(respuesta);
          if(jsonData.success==1){

            $('#bs-example-modal-lg').modal('hide');
            var table_facturacion_proforma_recorridos = $('#recorridos_tabla').DataTable();
            table_facturacion_proforma_recorridos.ajax.reload();
                      
          }
        }
    });



});

$('#ingresar_recorridos').click(function() {
console.log('ahora');
//DESTRUIR TODAS LAS GUIAS
var tabla_asignacion = $('#asignacion_salidas').DataTable();
tabla_asignacion.destroy();

var id = document.getElementById('codigo').value;
        var datatable = $('#asignacion_salidas').DataTable({
          paging: false,
          searching: true,          
          ajax: {
            url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php",
            data: {
              'Recorridos': 1,
              'id': id
            },
            type: 'post'
          },
          columns: [{
              data: "Fecha",
              render: function(data, type, row) {
                var Fecha = row.Fecha.split('-').reverse().join('.');
                return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
              }
            },
            {
              data: "Recorrido"
            },
            {
              data: "NumerodeOrden",
              render: function(data, type, row) {
                return 'Orden: '+row.NumerodeOrden+'</br>'+
                       'Cliente: '+row.nombrecliente+'</br>'+
                       'Km.: '+row.KilometrosRecorridos
              }
            },
            {
              data: "PrecioVenta",
              render: $.fn.dataTable.render.number('.', ',', 2, '$ ')
            },
            {
              data: "id",
              render: function(data, type, row) {
                if (row.PrecioVenta > 0) {
                  return "<td>" +
                    "<a onclick='asigno_rec("+row.id+")'><i class='mdi mdi-18px mdi-cart-plus'></i></a>" +
                    "</td>"
                } else {
                   return `<td></td>`
                }
              }
            }
          ],


        });
    });
    
    function asigno_rec(i){
        var id = document.getElementById('codigo').value;
        $.ajax({
            data: {
              'Recorridos_ctacte': 1,
              'idLogistica': i,
              'idCliente':id
            },
            url: 'Procesos/php/recorridos.php',
            type: 'post',
            beforeSend: function() {
              // $("#buscando").html("Buscando...");
              // alert('enviando...');
            },
            success: function(respuesta) {
              var jsonData = JSON.parse(respuesta);
              if(jsonData.success==1){
                var tabla_asignacion = $('#asignacion_salidas').DataTable();
                tabla_asignacion.ajax.reload(); 
                $.NotificationApp.send("Exito !", "Registro Actualizado.", "bottom-right", "#FFFFFF", "success");                
              }else{
                $.NotificationApp.send("Error !", "No pudimos cargar el registro.", "bottom-right", "#FFFFFF", "danger");
              }
            //   $('#ncomprobante_up_r').val(jsonData.PuntoVenta + '-' + jsonData.NComprobante);
            //   $('#comprobante_up_r').val(jsonData.Comprobante);  
            //   $('#select_up_r').val(jsonData.Comprobante);  
            }
          });  

    //   console.log('id',i);  
    }


    
    $('#bs-example-modal-lg').on('shown.bs.modal',function(){
        $('#asign_another_order_row0').css('display','block');
        $('#asign_another_order_row1').css('display','none');
        $('#td_1_row1').html();
        $('#td_2_row1').html();
        $('#td_3_row1').html();
        $('#asign_another_order_label').html();

    });


//MODIFICAR PRECIO A RECORRIDOS
$("#Modificar_recorrido_boton_ok").click(function() {

    var id = document.getElementById('buscarcliente').value;
    var new_val=$('#modificar_recorridos_value').val();

    console.log('nuevo valor',new_val);

    //Creamos un array que almacenará los valores de los input "checked"
    var checked = [];
    //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
    $("input.custom-control-input:checked").each(function() {
      //Mediante la función push agregamos al arreglo los values de los checkbox
      if ($(this).attr("value") != null) {
        checked.push(($(this).attr("value")));
      }
    });
    
    // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
    var new_val=$('#modificar_recorridos_value').val();

    if (checked != 0) {
      
      var dato = {
        "Modificar_recorrido": 1,
        "id_cliente":id,
        "new_val":new_val,
        "id": checked        
      };
      
      $.ajax({
        data: dato,
        url: 'Procesos/php/recorridos.php',
        type: 'post',
        //         beforeSend: function(){
        //         $("#buscando").html("Buscando...");
        //         },
        success: function(response) {
          var jsonData = JSON.parse(response);

          if (jsonData.success == "1") {
          let tabla_recorridos = $('#recorridos_tabla').DataTable();
              tabla_recorridos.ajax.reload();
              $.NotificationApp.send("Exito !", "Registro Actualizado.", "bottom-right", "#FFFFFF", "success");                
            }else{
              $.NotificationApp.send("Error !", "No pudimos cargar el registro.", "bottom-right", "#FFFFFF", "danger");
            
          }
        }
      });
    }
});