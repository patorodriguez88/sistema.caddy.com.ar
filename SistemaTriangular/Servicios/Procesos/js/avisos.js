var datatable = $('#seguimiento').DataTable({
    dom: 'Bfrtip',
    buttons: ['pageLength', 'copy', 'excel', 'pdf'],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All']
    ],

  ajax: {
       url:"Procesos/php/avisos.php",
       data:{'Avisos':1},
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
          {data:"idCliente"},
          {data:"NombreCliente"},
          {data:"NombreReceptor",
          render: function (data, type, row) { 
          return '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">'+row.NombreReceptor+'</td>';
                 
            }
          },
          {data:"TipoDeAviso",
          render: function (data, type, row) { 
          return '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a>'+row.TipoDeAviso+'</td>';
                   
            }
          },
          {data:"Mail",
          render: function (data, type, row) {
              if(row.TipoDeAviso=='Mail'){
               var dato_contacto=row.Mail;   
              }else if(row.TipoDeAviso=='SMS'){
                dato_contacto=row.Celular;
              }
               return '<td class="table-action">'+
               '<a style="cursor:pointer"><b class="text-primary">'+dato_contacto+'</b></a>'+
               '</td>';
            }
           },         
          {data:"AvisoEnRecepcion"},
          {data:"AvisoEnEnvios"},
          {data:"AvisoEnService"},
          {data:"Condicion",
          render: function (data, type, row) {
            return '<td class="table-action col-xs-3">'+
                   '<a class="text-success"><b>' + row.Condicion + '</b></a></br></td>';
          }
        },
      ]
});
