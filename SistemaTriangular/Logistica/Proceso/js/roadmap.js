$(document).ready(function(){

    $('#buscador').css('display','block');
    $('#tabla_roadmap').css('display','none');
    $('#widgets').css('display','none');
    $('#widgets1').css('display','none');
});

$('#buscar').click(function(){
    $('#buscador').css('display','none');
    $('#tabla_roadmap').css('display','block');
    $('#widgets').css('display','flex');
    $('#widgets1').css('display','flex');
    var norden=$('#simpleinput').val();

    $.ajax({
        data:{'DatosLogistica':1,'NOrden':norden},
        type: "POST",
        url: "Proceso/php/roadmap.php",
        beforeSend: function() {
        // setting a timeout
        // $('#info-alert-modal').modal('show');
        },
        success: function(response)
        {
        var jsonData= JSON.parse(response);
        $('#name_logistic').html(jsonData.data[0].NombreChofer);
        $('#name2_logistic').html(jsonData.data[0].NombreChofer2);
        $('#number_orders_logistic').html(jsonData[0]);
        
        console.log(jsonData[0]);

        }
    });

    
    $('#roadmap_header').html('ROADMAP ORDEN N '+norden);
var datatable = $('#roadmap').DataTable({
    // dom: 'Bfrtip',
    // buttons: ['pageLength', 'copy', 'excel', 'pdf'],
    paging: true,
    searching: true,
    // lengthMenu: [
    //   [10, 25, 50, -1],
    //   [10, 25, 50, 'All']
    // ],

  ajax: {
       url:"Proceso/php/roadmap.php",
       data:{'Roadmap':1,'NOrden':norden},
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
          
          {data:"Hora",
          render: function (data, type, row) {           
          return '<td><b>'+row.Hora+'</b></td>';  
                //    '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">'+row.Entregados+'</td>';
           }
          },
          {data:"Recorrido"},                  
          {data:"Cliente",
          render: function (data, type, row) {
            
            return '<td class="table-action col-xs-3"><b>'+row.Cliente+'</b></br>'+
                   '<td class="table-action col-xs-3 text-muted">'+row.Localizacion+'</br>';
            }
        
        
        },
          {data:"Estado",
          render: function (data, type, row) {
            if(row.Estado=='Abierto'){
            var color='success';
            }else{
            color='warning';    
            };
            return `<td class="table-action col-xs-3"><span class="badge badge-${color}">${row.Estado}</span></td>`;
            }
        
        },         
        {data:"Estado",
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

          {data:"Seguimiento",
          render: function (data, type, row) {
            return `<td class="table-action col-xs-3"><span class="badge badge-dark-lighten">$ ${row.Total}</span></td>`;
          }
        },
        {data:"idTransClientes",
        render: function (data, type, row) {
            return '<td class="table-action">'+
                '<a data-id="'+row.idTransClientes+'" data-toggle="modal" data-target="#seguimiento_1-modal-lg" class="action-icon"> <i class="mdi mdi-18px mdi-list-status text-primary"></i></a>'+
                '<a data-id="'+row.idTransClientes+'" data-tabla="trans"  data-toggle="modal" data-target="#seguimiento_2-modal-lg" class="action-icon"> <i class="mdi mdi-18px mdi-eye-outline text-primary"></i></a>'+
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
       url:"Proceso/php/roadmap.php",
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
            url: "Proceso/php/roadmap.php",
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

});