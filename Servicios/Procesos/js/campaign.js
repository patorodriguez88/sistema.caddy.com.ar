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
            var Fecha=row.FechaEntrega.split('-').reverse().join('.');
            return '<td><span style="display: none;">'+row.FechaEntrega+'</span>'+Fecha+'</td>';  
            }
          },
          
          {data:"RazonSocial",
          render: function (data, type, row) {           
          return '<td><b>'+row.RazonSocial+'</b></td></br>'+
                   '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">'+row.Recorrido+ ' ' +row.RecorridosName +' ' +row.Zona+'</td>';
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
                '<a data-id="'+row.NumeroVenta+'" data-toggle="modal" data-target="#seguimiento_1-modal-lg" class="action-icon"> <i style="cursor:pointer" class="mdi mdi-18px mdi-list-status text-primary"></i></a>'+
                '<a data-id="'+row.NumeroVenta+'" data-tabla="trans"  data-toggle="modal" data-target="#seguimiento_2-modal-lg" class="action-icon"> <i style="cursor:pointer" class="mdi mdi-18px mdi-eye-outline text-primary"></i></a>'+
                '<a href="Informes/campaignpdf?id='+row.NumeroVenta+'" target="_blank" class="action-icon"><i style="cursor:pointer" class="mdi mdi-18px mdi-folder-search-outline text-warning"></i></a>'+
                '</td>';
        }
      },   
         
      ]
});

$('#seguimiento_1-modal-lg').on('show.bs.modal',function(e){

    var datatable_1 = $('#seguimiento_1').DataTable();
    datatable_1.destroy();

    var triggerLink = $(e.relatedTarget);
    var i = triggerLink[0].dataset['id'];
    // var dato= triggerLink[0].dataset['title'];
  

    $('#seguimiento_1-modal-lg').modal('show');    
    $('#myLargeModalLabel').html("Resumen de Estado de Envios Campaña N "+i);

var datatable_1 = $('#seguimiento_1').DataTable({    
bFilter: false, 
bInfo: false,
paging:false,
search:false,  
ajax: {
       url:"Procesos/php/campaign.php",
       data:{'BuscarSeguimiento':1,'id':i},
       processing: true,
       type:'post'
      },
      columns: [
        {data:"Estado",
        render: function (data, type, row) {
          if(row.Estado==''){
            var Estado='Sin Datos';  
          }else{
             Estado=row.Estado; 
          }  
            return `<td class="table-action col-xs-3"><b>${Estado}</b></td>`;
        }
    
    },
        {data:"Cantidad",
        render: function (data, type, row) {
            var Efectividad=Math.round(Number(row.Entregados/row.Cantidad)*100);
            if(row.Estado=='Entregado al Cliente'){
            var color='success';    
            }else if(row.Estado=='Devuelto al Cliente'){
              color='danger';        
            }else{            
              color='warning';  
            }
            return `<td class="table-action col-xs-3"><a class="text-${color}"><b>${row.Cantidad}</b></td>`;
        },
       
    },
        ]
      });
});

$('#seguimiento_2-modal-lg').on('show.bs.modal',function(e){

    var datatable_2 = $('#seguimiento_2').DataTable();
    datatable_2.destroy();

    var triggerLink = $(e.relatedTarget);
    var i = triggerLink[0].dataset['id'];
    // var dato= triggerLink[0].dataset['title'];


    // $('#seguimiento_2-modal-lg').modal('show'); 

    $('#myLargeModalLabel_2').html("Detalle de Envios Campaña N " + i);

    var datatable_2 = $('#seguimiento_2').DataTable({
        bFilter: false,
        bInfo: false,
        paging: false,
        search: false,
        ajax: {
            url: "Procesos/php/campaign.php",
            data: { 'BuscarSeguimientoDetalle': 1, 'id': i },
            processing: true,
            type: 'post'
        },
        columns: [
            { data: "Fecha" },
            { data: "CodigoSeguimiento" },
            { data: "RazonSocial" },
            { data: "ClienteDestino" },
            { data: "Estado" },
            { data: "CodigoProveedor" },
            { data: "FechaEntrega" },
            { data: "Dias" },
        ]
    });
});
