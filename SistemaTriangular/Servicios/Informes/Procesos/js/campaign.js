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
       url:"Procesos/php/campaign.php",
       data:{'Pendientes':1},
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
          
          {data:"RazonSocial",
          render: function (data, type, row) {           
          return '<td><b>'+row.RazonSocial+'</b></td>';  
                //    '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">'+row.Entregados+'</td>';
           }
          },
          {data:"NumeroVenta"},          
          {data:"Cantidad",
          render: function (data, type, row) {
            return `<td class="table-action col-xs-3"><b>${row.Cantidad}</b></td>`;
        }
        },
          {data:"Entregados"},
          {data:"Entregados",
          render: function (data, type, row) {
            var Pendientes=Math.round(Number(row.Cantidad-row.Entregados));
            return '<td class="table-action col-xs-3">'+Pendientes+'</td>';
            }
        
        },         
        {data:"Entregados",
        render: function (data, type, row) {
          var Efectividad=Math.round(Number(row.Entregados/row.Cantidad)*100);
          if(Efectividad==100){
          var color='success';    
          }else if(Efectividad>80){
            color='warning';        
          }else{
            color='danger';  
          }
          return `<td class="table-action col-xs-3"><a class="text-${color}"><b>${Efectividad} %</b></td>`;
          }
      
      },         

          {data:"Total",
          render: function (data, type, row) {
            return `<td class="table-action col-xs-3"><span class="badge badge-dark-lighten">$ ${row.Total}</span></td>`;
          }
        },
        {data:"NumeroVenta",
        render: function (data, type, row) {
            return '<td class="table-action">'+
                '<a data-id="'+row.NumeroVenta+'" data-toggle="modal" data-target="#seguimiento_1-modal-lg" class="action-icon"> <i class="mdi mdi-18px mdi-list-status text-primary"></i></a>'+
                '<a data-id="'+row.NumeroVenta+'" data-tabla="trans"  data-toggle="modal" data-target="#seguimiento_2-modal-lg" class="action-icon"> <i class="mdi mdi-18px mdi-eye-outline text-primary"></i></a>'+
                '</td>';
        }
      },   
         
      ]
});
