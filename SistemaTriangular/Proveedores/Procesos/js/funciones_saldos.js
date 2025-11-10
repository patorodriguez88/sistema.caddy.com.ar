$(document).ready(function(){
        var datatable = $('#proveedores-saldos').DataTable({
            dom: 'Bfrtip',
            buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            ajax: {
              url:"../Proveedores/Procesos/php/tablas.php",
              data:{'Saldos':1},
              type:'post'
              },
              columns: [
              {data:"idProveedor"},  
              {data:"RazonSocial"},
              {data: "Debe",
               render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
              {data:"Haber",
               render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
              {data: "Saldo",
               render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
              {data:"idProveedor",  
              render: function (data, type, row) {
                return "<td>"+
                   "<a target='_blank' href='Informes/ctacteproveedorpdf.php?id="+row.idProveedor+"'><i class='mdi mdi-24px mdi-folder-download-outline'></i></a>"+
                   "</td>"
                }     
              }
              ]
           });
});
