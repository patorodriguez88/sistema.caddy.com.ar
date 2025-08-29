
 function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var e = getParameterByName('id');
var f = getParameterByName('date');
var m = getParameterByName('m');

console.log('encript',m);

$.ajax({
    data:{'BuscarDatos':1,'id':e,'date':f},
    url:'Procesos/php/report_seller.php',
    type:'post',
    success: function(response)
     {
    $('#NumeroComprobante').html(e);
    // var Fecha_report_0=f.reverse();
    var Fecha_report=f.split("-").reverse().join(".");
    $('#titulo').html("Detalle de Envios Fecha " + Fecha_report);
    
    
    var jsonData = JSON.parse(response);
    $('#sub_titulo').html(jsonData.data[0].RecorridosName +' '+jsonData.data[0].Zona);

    $('#ncampaign').html(e);
    $('#total').html(jsonData.data[0].Cantidad);
    $('#clientes').html(jsonData.data[0].Total);
    var Efectividad=Math.round(Number(jsonData.data[0].Entregados/jsonData.data[0].Total)*100);
    $('#efectividad').html(`${Efectividad} %`);
    var Fecha=jsonData.data[0].Fecha.split('-').reverse().join('.');
    $('#FechaComprobante').html(Fecha);
    $('#id_cliente').html(`${jsonData.data[0].idClienteOrigen} - ${jsonData.data[0].ClienteOrigen}`);
    // var FechaPrometida=jsonData.data[0].FechaPrometida.split('-').reverse().join('.');
    var FechaEntrega=jsonData.data[0].FechaEntrega.split('-').reverse().join('.');
    // $('#fecha_prommise').html(FechaPrometida);
    $('#fecha_prommise').html(FechaEntrega);
    $('#FechaEntrega').html(FechaEntrega);
    var sla_days=jsonData.data[0].sla;
    $('#sla').html(sla_days);
    $('#entregados').html(jsonData.data[0].Entregados);
     }     
});


var datatable = $('#seguimiento').DataTable({
    paging:   false,
    ordering: false,
    info:     false,
  ajax: {
       url:"Procesos/php/report_seller.php",
       data:{'Pendientes':1,'id':e,'date':f},
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
          return `<td><b>${row.RazonSocial}</b></br>`+ 
                  '<i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">'+row.DomicilioDestino+'</td>';
           }
          },
          {data:"NumeroVenta",
          render: function (data, type, row) {
            return `<td class="table-action col-xs-3"><b>${row.NumeroVenta}</b></br>`+
                 `<b>${row.CodigoSeguimiento}</b></td>`;
            }    
        },          
          {data:"Cantidad",
          render: function (data, type, row) {
            return `<td class="table-action col-xs-3"><b>${row.Cantidad}</b></td>`;
        }
        },
        {data:"Recorrido",
        render: function (data, type, row) {
            return `<td>${row.Recorrido}</br>`+
                   `<i class="mdi mdi-14px mdi-account text-muted">${row.Usuario}</i></td>`;
            }
        
        },
        {data:"CodigoSeguimiento"},
        {data:"Observaciones",
        render: function (data, type, row) {
            return `<td>${row.Observaciones}  <i id="${row.CodigoSeguimiento}" onclick="ver(this.id)" style="cursor:pointer;text-align: right" class="d-print-none mdi mdi-14px mdi-pencil text-muted alnright"></i></td>`;
            }
        }, 
        {data:"Entregado",
        render: function (data, type, row) {
          var Efectividad=Math.round(Number(row.Entregados/row.Cantidad)*100);
          
          if(row.Entregado==1){
          var color='success';
          var Entregado_text='Entregado';    
          return `<td><span class="badge bg-success text-white" style="bakground:green">${Entregado_text}</span></td>`;           
          }else{
          color='danger';  
          var Entregado_text='No Entregado';
          return `<td><span class="badge bg-danger text-white">${Entregado_text}</span></td>`;  
            }

        //   return `<td><span class="badge bg-${color} text-white">${Entregado_text}</span></td>`;
          }
      
      },  
      {data:null,
      render: function (data, type, row) {
          if(row.Entregado==1){
              var Fecha=row.FechaEntrega.split('-').reverse().join('.');
              var fechaini=moment(row.FechaPrometida);
              var fechafin=moment(row.FechaSeguimiento);
              var dias= fechafin.diff(fechaini, 'days');
              
          }else{
              var date = new Date();
              var result = date.toISOString().split('T')[0];
              Fecha=result.split('-').reverse().join('.');

              var fechaini=moment(row.FechaEntrega);
              var fechafin=moment(date);
              var dias= fechafin.diff(fechaini, 'days');

          }
          sla_days=$('#sla').html();
          if(dias>sla_days){
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

function ver(cs){
    // alert(cs);
    // $('#standard-modal').modal('show');
    // $('#myCenterModalLabel').html('MODIFICAR OBSERVACIONES AL CODIGO '+cs);
    $.ajax({
        data:{'BuscarObservaciones':1,'cs':cs},
        url:'Procesos/php/report_seller.php',
        type:'post',
        success: function(response)
         {
            var jsonData = JSON.parse(response);
            $('#observaciones').val(jsonData.data);
            $('#standard-modal').modal('show');
            $('#myCenterModalLabel').html('MODIFICAR OBSERVACIONES AL CODIGO '+cs);

          $('#modificar_obs_ok').click(function(){
            var obs=$('#observaciones').val();

            $.ajax({
                data:{'ModificarObservaciones':1,'id':jsonData.id,'obs':obs},
                url:'Procesos/php/campaignpdf.php',
                type:'post',
                beforeSend: function() {
                    // setting a timeout
                    $('#standard-modal').modal('hide');    
                    $('#loading').modal('show');
                },
                success: function(response)
                 {
                
                var JsonData = JSON.parse(response);  
                if(JsonData.success=1){
                    var datatable = $('#seguimiento').DataTable();
                    // datatable.ajax.reload().end($('#loading').modal('hide'));
                    datatable.ajax.reload(function() {
                        $('#loading').modal('hide');
                    });
                      
                }else{

                }

                 }
            });    
          });
        }
    });
    
//    alert(id); 
}

// $('#send_mail').click(function(){
//             var user='prodriguez@dintersa.com.ar';
//             var name='name';  
//             var asunto='Recupero de Contraseña !';
//             var mensaje = 'Mensaje';
//             var html='Recupero';
            

//             $.ajax({
//                 data:{'txtEmail':user,'txtName':name,'txtAsunto':asunto,'txtMensa':mensaje,'$txtHtml':html},
//                 url:'https://www.caddy.com.ar/SistemaTriangular/Mail/report_seller.php',
//                 type:'post',
//                 success: function(response1)
//                  {
//                  var jsonData1 = JSON.parse(response1);
//                 if (jsonData1.success == "1")
//                 {  
//                 $('#success-alert-modal').modal('show');   
//                  }else{
//                  alert(jsonData1.error);
//                  }
//                }
//             });
//       }); 

