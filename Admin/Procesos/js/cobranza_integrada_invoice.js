function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function currencyFormat(num) {
    return '$ ' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  }
  $(document).ready(function() {

// $("#standard-modal-invoice").on('show.bs.modal', function (e) {
//  let number = $('#myCenterModalLabel_rec').html();
 let number= getParameterByName('id');
 $('#myCenterModalLabel_rec').html('Cobranza Integrada N '+number);
  var datatable = $('#invoice_table').DataTable();
      datatable.destroy();
      $('#NumeroComprobante').html(number);
      
      $(document).ready(function(){
        // let id= $('#myCenterModalLabel_rec').html();
        $.ajax({
            data: {
              'Totales': 1,
              'id': number
            },
            type: "POST",
            url: "../Procesos/php/cobranza_integrada.php",
            success: function(response) {
                var jsonData = JSON.parse(response);
             $('#FechaComprobante').html(jsonData.fecha);
             
             $('#factura_neto').html(currencyFormat(Number(jsonData.cobrado)));
             $('#factura_descuento').html(currencyFormat(Number(jsonData.retenido)));
             $('#factura_total').html(currencyFormat(Number(jsonData.total)));
             $('#factura_codigo').html(jsonData.idcliente);
             $('#factura_razonsocial').html(jsonData.cliente); 
             $('#factura_direccion').html(jsonData.direccion); 
             $('#factura_celular').html(jsonData.telefono);
             $('#factura_email').html(jsonData.mail); 
                if(jsonData.name!=''){
                $('#surrender_name_label').css('display','block');
                $('#surrender_time_label').css('display','block');
                $('#estado').html('Pagado');
                $('#surrender_name').html(jsonData.name);
                $('#surrender_time').html(jsonData.time);
                }
            }
          });
    });
    
        var datatable = $('#invoice_table').DataTable({      
            "info": false,
            "paging": false,
            "searching:": false,
        
        ajax: {
         url:"../Procesos/php/cobranza_integrada.php",
         data:{'Invoice':1,'Number':number},
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
            // {data:"Usuario"},
            {data:"Cliente",
            render: function (data, type, row) {
                return '<h6 class="font-15 mb-1 fw-normal">'+row.Cliente+'</h6>'+
                        '<span class="text-muted font-11">'+row.ClienteDestino+'</span>';
            }
        },
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
            {data:"Total"},
            {data:"idPedido",
            render: function (data, type, row) {
                var numb=row.CobrarEnvio-row.Total;
                return numb.toFixed(2);
                
              }
            },
        ]
        });
    //    });  
       
    //    $("#standard-modal-invoice").on('hidden.bs.modal', function (e) {
    //     var datatable = $('#invoice_table').DataTable();
    //     datatable.ajax.reload();
    //    });     
    });