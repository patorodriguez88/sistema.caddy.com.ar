$(document).ready(function(){

    $(document).ready(function(){
        $("#clientes-saldos").DataTable({
            ajax: {
            url:"https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/clientes_bd.php",
            data:{'Saldos':1},
            type:'post'           
            }
        });
    });


    // var datatable = $('#clientes-saldos').DataTable({
    //     dom: 'Bfrtip',
    //     buttons: [
    //     'copy', 'csv', 'excel', 'pdf', 'print'
    //     ],
    //     ajax: {
    //       url:"https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/clientes_bd.php",
    //       data:{'Saldos':1},
    //       type:'post'
    //       },
    //       columns: [
    //       {data:"id"},  
    //       {data:"nombrecliente"},
    //       {data:"Direccion"}
        //   {data: "Debe",
        //    render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        //   {data:"Haber",
        //    render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        //   {data: "Saldo",
        //    render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        //   {data:"idCliente",  
        //   render: function (data, type, row) {
        //     return "<td>"+
        //        "<a target='_blank' href='Informes/ctacteclientespdf.php?id="+row.idCliente+"'><i class='mdi mdi-24px mdi-folder-download-outline'></i></a>"+
        //        "</td>"
        //     }     
        //   }
    //       ]
    //    });
});