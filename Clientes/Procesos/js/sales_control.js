conciliacion();

function conciliacion(){
var datos=fechas();
let desde=datos[0];
let hasta=datos[1];
    
    $.ajax({
        data:{'SumaTotales':1,'FechaDesde':desde,'FechaHasta':hasta},
        url:'Procesos/php/sales_control.php',
        type:'post',
        success: function(response)
         {
          var jsonData = JSON.parse(response);
          
          let Neto= '$ ' + parseFloat(jsonData.ImporteNeto).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          let iva= '$ ' + parseFloat(jsonData.Iva).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          let total= '$ ' + parseFloat(jsonData.Total).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          $('#suma_sales_control').html('Importe Neto: '+Neto);  
          $('#fechas').html('Desde '+desde+' Hasta '+hasta);
          $('#suma_total_sales_control').html('Total: '+total);
          $('#suma_iva_sales_control').html('Iva: '+iva);
         }
        });

}

function fechas(){

var fecha=$('#fechas').val();
var partes = fecha.split(' - ');

// Accediendo a cada parte por separado
var fechaInicio = partes[0].trim();
var fechaFin = partes[1].trim();

// Convertir la fecha de inicio al formato deseado
var partesFechaInicio = fechaInicio.split('/');
var fechaInicioFormateada = partesFechaInicio[2] + '-' + partesFechaInicio[0].padStart(2, '0') + '-' + partesFechaInicio[1].padStart(2, '0');

// Convertir la fecha de inicio al formato deseado
var partesFechaFin = fechaFin.split('/');
var fechaFinFormateada = partesFechaFin[2] + '-' + partesFechaFin[0].padStart(2, '0') + '-' + partesFechaFin[1].padStart(2, '0');

  // Devolver un array con los dos datos
  return [fechaInicioFormateada,fechaFinFormateada];

}

function agregarVentas(cadena){
    
    
    var partes = cadena.split('-');

    // Las partes resultantes estarán en el arreglo 'partes'
    console.log(partes);
    
    // Accediendo a cada parte por separado
    var fechaInicio = partes[0].trim();
    var fechaFin = partes[1].trim();
    var idCliente = partes[2].trim();
    var monto = partes[3].trim();
    
    // Convertir la fecha de inicio al formato deseado
    var partesFechaInicio = fechaInicio.split('/');
    var fechaInicioFormateada = partesFechaInicio[2] + '-' + partesFechaInicio[0].padStart(2, '0') + '-' + partesFechaInicio[1].padStart(2, '0');

    // Convertir la fecha de inicio al formato deseado
    var partesFechaFin = fechaFin.split('/');
    var fechaFinFormateada = partesFechaFin[2] + '-' + partesFechaFin[0].padStart(2, '0') + '-' + partesFechaFin[1].padStart(2, '0');

    console.log("Fecha de inicio:", fechaInicio);
    console.log("Fecha de fin:", fechaFin);
    // console.log("Cantidad:", cantidad);
    console.log("Monto:", monto);

    $.ajax({
        data:{'CargarSales':1,'FechaDesde':fechaInicioFormateada,'FechaHasta':fechaFinFormateada,'idCliente':idCliente,'Monto':monto},
        url:'Procesos/php/sales_control.php',
        type:'post',
        success: function(response)
         {
          var jsonData = JSON.parse(response);

          if(jsonData.success==1){ 
            conciliacion();
           var datatable = $('#sales_control').DataTable();
            datatable.ajax.reload();  
            var datatable = $('#sales_view').DataTable();
            datatable.ajax.reload();  
  
            $.toast({
            text: 'Se ha actualizado la tabla Ventas correctamente.',
            icon: 'success',
            heading: 'Exito !',
            bgColor: 'success',
            textColor: 'white',  
            position: 'bottom-right',
            showHideTransition: 'plain'
            
          });
            
        //   $('#warning-modal').modal('hide');
  
          }else{
          $.toast({
            text: 'No pudimos eliminar el registro en la tabla Ventas.',
            icon: 'warning',
            heading: 'Error!',
            bgColor: 'warning',
            textColor: 'white',  
            position: 'bottom-right',
            showHideTransition: 'plain'
            });  
          }
         
      } 
      });




}

$('#fechas').change(function(){
    var datatable = $('#sales_control').DataTable();
    datatable.destroy();
    ver_tabla();   
    conciliacion();

});

function ver_tabla(){

var fecha=$('#fechas').val();
console.log('fechas',fecha);

var datatable = $('#sales_control').DataTable({
    dom: 'Bfrtip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
    ],
    paging: true,
    searching: true,
ajax: {
         url:"Procesos/php/sales_control.php",
         data:{'BuscarVentas':1,'Fecha':fecha},
         processing: true,
         type:'post'
        },
        columns: [
          {data:"idCliente"},  
          {data:"Cliente"},
          {data:"Cantidad"},
          {data:"Neto",
          render: function (data, type, row) {
          return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          }
        },
          {data:"Iva",
          render: function (data, type, row) {
          return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          }
        },
          {data:"Total",
          render: function (data, type, row) {
          return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
          }
        },
          {data:null,
           render: function (data, type, row) {
            var dato=fecha+' - '+row.idCliente+' - '+row.Total;
            return '<td class="table-action">'+
            // '<a id="'+row.idPedido+'" onclick="modificarVentas(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>'+
            // '<a id="'+row.idPedido+'" onclick="eliminarVentas(this.id);" class="action-icon"> <i class="mdi mdi-delete text-warning"></i></a>'+
            '<a id="'+dato+'" onclick="agregarVentas(this.id);" class="action-icon"> <i class="mdi mdi-plus-circle text-success"></i></a>'+  
            '</td>';
            }
          }
          ]
        });

    var datos=fechas();
    let desde=datos[0];
    let hasta=datos[1];

    var datatable = $('#sales_view').DataTable({
        dom: 'Bfrtip',
        buttons: [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        paging: true,
        searching: true,
    ajax: {
             url:"Procesos/php/sales_control.php",
             data:{'VerSales':1,'FechaDesde':desde,'FechaHasta':hasta},
             processing: true,
             type:'post'
            },
            columns: [
              {data:"idCliente"},  
              {data:"Cliente"},              
              {data:"ImporteNeto",
              render: function (data, type, row) {
              return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
              }
            },
              {data:"Iva",
              render: function (data, type, row) {
              return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
              }
            },
              {data:"Total",
              render: function (data, type, row) {
              return '$ ' + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
              }
            },
              {data:null,
               render: function (data, type, row) {
                // var dato=fecha+' - '+row.idCliente+' - '+row.Total;
                var dato='';
                return '<td class="table-action">'+
                // '<a id="'+row.idPedido+'" onclick="modificarVentas(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>'+
                '<a id="'+dato+'" onclick="agregarVentas(this.id);" class="action-icon"> <i class="mdi mdi-plus-circle text-success"></i></a>'+  
                '<a id="'+row.id+'" onclick="eliminarSales(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a>'+
                '</td>';
                }
              }
              ]
            });
    }
    function eliminarSales(id){

        $.ajax({
            data:{'Eliminar':1,'id':id},
            url:'Procesos/php/sales_control.php',
            type:'post',
            success: function(response)
             {
              var jsonData = JSON.parse(response);

                conciliacion();
                var datatable = $('#sales_control').DataTable();
                datatable.ajax.reload();  
                var datatable = $('#sales_view').DataTable();
                datatable.ajax.reload();  

              
             }
            });
    
    }