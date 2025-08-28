let fechas;
function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

      

// Re-draw the table when the a date range filter changes
$('#singledaterange').change( function() {

// var fechas=$('#singledaterange').val();
fechas=$('#singledaterange').val();

var datatable = $('#cobranza_integrada').DataTable();
datatable.destroy();

     $.ajax({
      data:{'VerFechas':1,'Fechas':fechas},
      url:'Procesos/php/pagos.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        var Inicio = jsonData.Inicio;
        var Final =jsonData.Final;

        var datatable = $('#cobranza_integrada').DataTable({
          dom: 'Bfrtip',
          buttons: [
            'pageLength','copy', 'csv', 'excel', 'pdf', 'print'
          ],
          lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
          ],
          paging: true,
          searching: true,
          footerCallback: function(row, data, start, end, display) {

            total = this.api()
            //   .column(6) //numero de columna a sumar
              .column(6, {page: 'current'})//para sumar solo la pagina actual
              .data()
              .reduce(function(a, b) {
                return Number(a) + Number(b);
                //                 return parseInt(a) + parseInt(b);
              }, 0);
            var saldo = currencyFormat(total);
            
            $(this.api().column(6).footer()).html(saldo);

          },

    ajax: {
         url:"Procesos/php/cobranza_integrada.php",
         data:{'Pendientes':1,'Inicio':Inicio,'Final':Final},
         processing: true,
         type:'post',
        },
        columns: [
            {data:"FechaPedido",
             render: function (data, type, row) {
              var Fecha=row.FechaPedido.split('-').reverse().join('.');
              return '<td><span style="display: none;">'+row.FechaPedido+'</span>'+Fecha+'</td>';  
              }
            },
            {data:"Usuario"},
            {data:"Cliente"},
            {data:"Titulo",
            render: function (data, type, row) {
                return '<h6 class="font-15 mb-1 fw-normal">'+row.Titulo+'</h6>'+
                       '<span class="text-muted font-11">'+row.NumeroRepo+' '+row.NumPedido+'</span>';
              }
            },
            {data:"Comentario"},
            {data:"CobrarEnvio",
            render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
            },
            {data:"surrender_name",
            render: function (data, type, row) {
                return '<h6 class="font-15 mb-1 fw-normal">'+row.surrender_name+'</h6>'+
                       '<span class="text-muted font-11">'+row.surrender_time+'</span>';
              }
            },
            {data:"idPedido",
            render: function (data, type, row) {
               if(row.surrender_name!=''){
                return `<td class="table-action"><i class="mdi mdi-18px mdi-file-document-outline" onclick="imp(${row.surrender_number});"></i></td>`;
                }else{
                return `<td class="table-action"><input data-id="${row.CobrarEnvio}" id="surrender_checkbox" value="${row.idPedido}" type="checkbox" class"custom-control-input">
                        <td class="table-action"><i class="mdi mdi-18px mdi-pencil text-warning" onclick="change(${row.idPedido},${row.CobrarEnvio});"></i></td>`;
                }
              }
            },

        ]
        });
       }  
      });     
    });
function imp(i){
    window.open('https://www.caddy.com.ar/SistemaTriangular/Admin/Informes/invoice_cobranza_integrada?id='+i,'t_blank');
    // $('#myCenterModalLabel_rec').html(i);
    // $('#standard-modal-invoice').modal('show');

    // $('#myCenterModalLabel_rec').html();  
//    alert(i); 
}
      $(document).on('change', 'input[type="checkbox"]', function(e) {
        var total=Number($('#cobranza_integrada_header').html());
        if (this.id == "surrender_checkbox") {
            e.preventDefault();
            var elemento = e.target;
            
            var dataID = elemento.getAttribute('data-id');
            if(this.checked){
            total=total+Number(dataID);
            }else{
            total=total-Number(dataID);    
            }

            $('#cobranza_integrada_header').html(total);  
            if(total!=0){
                $('#cobranza_integrada_clear').prop('disabled',false);
                $('#cobranza_integrada_report').prop('disabled',false);  
            }else{
                $('#cobranza_integrada_clear').prop('disabled',true); 
                $('#cobranza_integrada_report').prop('disabled',true);  
            } 
        }
      });
      
    $('#cobranza_integrada_clear').click(function(){
    var datatable = $('#cobranza_integrada').DataTable();
    datatable.ajax.reload();
    $('#cobranza_integrada_header').html(0);  
    $('#cobranza_integrada_clear').prop('disabled',true);
    $('#cobranza_integrada_report').prop('disabled',true);  
    });
    
    $('#cobranza_integrada_report').click(function(){
        $('#standard-modal').modal('show');
        // $('#standard-modal-invoice').modal('show');
     });

    $('#generar_informe_ok').click(function(){    
    //Creamos un array que almacenará los valores de los input "checked"
    var checked = [];
    
    //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
    $('#surrender_checkbox:checked').each(function(){
        // $("input.custom-control-input:checked").each(function() {    
        //Mediante la función push agregamos al arreglo los values de los checkbox
        if ($(this).attr("value") != null) {
        checked.push(($(this).attr("value")));
        }
    });
    // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

    if (checked != 0) {
        let nombre = $("#nombre_receptor").val();
        let dni = $('#dni_receptor').val();
        let obs = $('#observaciones_receptor').val();
        let fecha = $('#fecha_receptor').val();
        let hora = $('#hora_receptor').val();

        var dato = {
        "Cobranza_Integrada": 1,
        "id": checked,
        "nombre":nombre,
        "dni":dni,
        "obs":obs,
        "fecha":fecha,
        "hora":hora
        };

        $.ajax({
            data: dato,
            url: 'Procesos/php/cobranza_integrada.php',
            type: 'post',
            success: function(response) {
              var jsonData = JSON.parse(response);
              $('#myCenterModalLabel_rec').html(jsonData.surrender_number);
              $('#NumeroComprobante').html(jsonData.surrender_number);
              $('#FechaComprobante').html(fecha);

              $('#standard-modal-invoice').modal('show');
              $('#standard-modal').modal('hide');
              
              
            //   if (jsonData.success == "1") {
                console.log('ver',jsonData.surrender_number);
                // $.NotificationApp.send("Registro Actualizado !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");
            //   } else {
                // $.NotificationApp.send("Ocurrio un Error !", "No se realizaron cambios.", "bottom-right", "#FFFFFF", "danger");
            //   }
            }
          });
        }

    });


function change(id,imp){

    $('#label_change_import').html(id);

    $('#modal_change_import').modal('show');

    $('#number_change_import').val(imp);

    // $('#change_ok').click(function(){
        
        // let val=Number($('#number_change_import').val());
        // let val1=val.toString().replace(/\./g,'');
        // let val2 = val1.toString().replace(/\,/g,'.');
        // alert(val);

    // });
}

    
    $('#btn_ok_change_import').click(function(){
    
    let id=$('#label_change_import').html();   

    var importe=$('#number_change_import').val();

    console.log('id',id);
    console.log('valor',importe);
// id 122824
// valor 66359.97
    $.ajax({
        data: {'Change_import':1,'id':id,'Importe':importe},
        url: 'Procesos/php/cobranza_integrada.php',
        type: 'post',
        success: function(response) {
        var jsonData = JSON.parse(response);
        
        if(jsonData.success==1){
            
            $('#modal_change_import').modal('hide');
            
            var datatable = $('#cobranza_integrada').DataTable();
            datatable.ajax.reload();
            $.NotificationApp.send("Registro Actualizado !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");

        } else {

            $.NotificationApp.send("Ocurrio un Error !", "No se realizaron cambios.", "bottom-right", "#FFFFFF", "danger");

        }

        }    
    });

    })
