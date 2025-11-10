let fechas;
$('#singledaterange').change( function() {
 fechas=$('#singledaterange').val();
$.ajax({
    data:{'VerFechas':1,'Fechas':fechas},
    url:'Procesos/php/sla.php',
    type:'post',
    success: function(response)
     {
      var jsonData = JSON.parse(response);
      var Inicio = jsonData.Inicio;
      var Final =jsonData.Final;

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
            url:"Procesos/php/sla.php",
            data:{'Sla':1,'Inicio':Inicio,'Final':Final},
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
                {data:"NumeroComprobante",
                render: function (data, type, row) {
                    if(row.Retirado==1){
                var color='success';
                var servicio='Entrega';    
                }else{
                var color='muted';    
                var servicio='Retiro';  
                }  
                return '<td class="table-action col-xs-3">'+
                '<td class="form-fontrol" >'+row.NumeroComprobante + '</td></br>' +
                '<a style="cursor:pointer" class="text-primary"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' + row.CodigoSeguimiento + '"' +
                'data-title="' + data.ClienteDestino + '" data-fieldname="' + data + '"><b>' + row.CodigoSeguimiento + '</b></a></br></td>';
                
                }
            
            },
                {data:"RazonSocial",
                render: function (data, type, row) { 
                if(row.Retirado==0){
                var color='success';  
                }else{
                color='muted';    
                }  
                return '<td><b>'+row.RazonSocial+' ('+row.CodigoProveedor +')</br>'+  
                        '<i class="mdi mdi-18px mdi-map-marker text-'+color+'"></i><a class="text-muted">'+row.DomicilioOrigen+'</td>';
                    }
                },
                {data:"DomicilioDestino",
                render: function (data, type, row) { 
                if(row.Retirado==1){
                var color1='success';  
                }else{
                color1='muted';    
                }  
                return '<td><b>'+row.ClienteDestino+'</br>'+  
                        '<i class="mdi mdi-18px mdi-map-marker text-'+color1+'"></i><a class="text-muted">'+row.DomicilioDestino+'</td>';
                    }
                },
                {data:"Recorrido",
                render: function (data, type, row) {
                    if(row.Redespacho==0){
                    return `<td class="table-action"><a data-id="${row.CodigoSeguimiento}" id="${row.CodigoSeguimiento}" ><b class="text-primary">${row.Recorrido}</b></a></td>`;
                    }else{
                    return `<td class="table-action"><a data-id="${row.CodigoSeguimiento}" id="${row.CodigoSeguimiento}" ><b class="text-primary">${row.Recorrido}</b></a><br/><span class="badge badge-warning text-white"><i class="mdi mdi-alpha-r-box"></i> Redespacho</span></td>`;   
                    }
                }
                },
        
                {data:"CodigoSeguimiento",
                render: function (data, type, row) {
                    if(row.Entregado==1){
                var color='success';
                var servicio='Entregado';    
                }else{
                var color='danger';    
                var servicio='No Entregado';  
                }  
                    return '<td class="table-action col-xs-3">'+
                    '<span class="badge badge-'+color+'">'+servicio+'</span></br>'+
                    '<span class="badge badge-dark-lighten"> $ '+row.Debe+'</span>'+  
                    '</td>';
                    }
                },
        
                {data:"Fecha",
                render: function (data, type, row) {
                    if(row.Entregado==1){
                        var Fecha=row.FechaEntrega.split('-').reverse().join('.');
                        var fechaini=moment(row.Fecha);
                        var fechafin=moment(row.FechaEntrega);
                        var dias= fechafin.diff(fechaini, 'days');
                        
                    }else{
                        var date = new Date();
                        var result = date.toISOString().split('T')[0];
                        Fecha=result.split('-').reverse().join('.');

                        var fechaini=moment(row.Fecha);
                        var fechafin=moment(date);
                        var dias= fechafin.diff(fechaini, 'days');

                    }
                    if(dias>2){
                        var sla='Imperfecto';
                        color='danger';
                    }else{
                        var sla='Perfecto';
                        color='success';
                    }
                    return '<td><span style="display: none;">'+row.FechaEntrega+'</span>'+Fecha+'</td></br>'+
                           '<td class="text-'+color+'">SLA '+dias+' Dias</td></br>'+
                           '<a class="text-'+color+'"><b>'+sla+'</b></a>';
                    }
                },
                
            ]
        });
    }
});
});

$('#entregado').change(function(e) {
  if(this.checked) {
      $('#entregado').val(1);
      }else{
      $('#entregado').val(0);
      } 
});



$('#standard-modal').on('show.bs.modal', function (e) {
var id= $('#id_modificar').val();  
var datatable = $('#ventas_tabla').DataTable({
bFilter: false, 
bInfo: false,
paging:false,
search:false,  
ajax: {
       url:"Procesos/php/funciones.php",
       data:{'BuscarDatosVentas':1,'id':id},
       processing: true,
       type:'post'
      },
      columns: [
        {data:"idPedido"},
        {data:"FechaPedido"},
        {data:"Codigo"},
        {data:"Titulo"},
        {data:"Total"},
        {data:"idPedido",
         render: function (data, type, row) {
          return '<td class="table-action">'+
          '<a id="'+row.idPedido+'" onclick="modificarVentas(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>'+
          '<a id="'+row.idPedido+'" onclick="eliminarVentas(this.id);" class="action-icon"> <i class="mdi mdi-delete text-warning"></i></a>'+
          '<a id="'+row.NumPedido+'" onclick="agregarVentas(this.id);" class="action-icon"> <i class="mdi mdi-plus-circle text-success"></i></a>'+  
          '</td>';
          }
        }
        ]
      });
});




