// var datatable = $('#seguimiento').DataTable();
// datatable.destroy();
let fechas;
function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

// Re-draw the table when the a date range filter changes
$('#singledaterange').change( function() {

// var fechas=$('#singledaterange').val();
fechas=$('#singledaterange').val();

var datatable = $('#seguimiento').DataTable();
datatable.destroy();
// alert(fechas);
     $.ajax({
      data:{'VerFechas':1,'Fechas':fechas},
      url:'Procesos/php/pagos.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        var Inicio = jsonData.Inicio;
        var Final =jsonData.Final;

        var datatable = $('#seguimiento').DataTable({
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
         url:"Procesos/php/pagos.php",
         data:{'Pendientes':1,'Inicio':Inicio,'Final':Final},
         processing: true,
         type:'post',
        },
        columns: [
            {data:"Fecha",
             render: function (data, type, row) {
              var Fecha=row.Fecha.split('-').reverse().join('.');
              return '<td><span style="display: none;">'+row.Fecha+'</span>'+Fecha+'</td>';  
              }
            },
            {data:"Usuario"},
            {data:"FormaDePago"},
            {data:"NombreCuenta"},
            {data:"NumeroComprobante"},
            {data:"RazonSocial"},
            {data:"Haber",
            render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
            },//           {data:"Recorrido"},
            {data:"id",
           render: function (data, type, row) {
                return `<td class="table-action"><a data-id="${row.id}" id="${row.id}" onclick="#" class="action-icon disabled"> <i class="mdi mdi-pencil text-muted"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(${row.id});" class="action-icon"> <i class="mdi mdi-delete text-muted"></i></a></td>`;
              }
            },
           
            ]
        });

       }  
      });     
});
function eliminar(i){
    alert(i);
}


