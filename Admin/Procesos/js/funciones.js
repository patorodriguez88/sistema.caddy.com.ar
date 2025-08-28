function normalizarFormato(numero) {
    // Eliminar guiones existentes y espacios
    numero = numero.replace(/[-\s]/g, '');
  
    // Asegurarse de que el número tiene al menos 9 dígitos
    while (numero.length < 9) {
      numero = '0' + numero;
    }
  
    // Agregar guiones en las posiciones adecuadas
    return numero.replace(/(\d{2})(\d{8})(\d{1})/, '$1-$2-$3');
  }
  
function currencyFormat(num) {
    return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  }
  
$(document).ready(function(){
$("input[name='customRadio1']").click(function(){
var title=$("input[name='customRadio1']:checked").val();
  if (title==1){
     var titlepage= "Libro Iva Compras";
      }else if(title==2){
     var titlepage= "Libro Iva Ventas"; 
      }else if(title==3){
      var titlepage= "Control Ventas"; 
 }

  $('#page-title').html(titlepage);
  $('#page-title0').html(titlepage);
})


  $('#buscar').click(function(){
  var Desde=document.getElementById('fecha_desde').value;

  var Hasta=document.getElementById('fecha_hasta').value;
//   console.log('desde',desde);
//   console.log('desde',hasta);

  var libro=$("input[name='customRadio1']:checked").val();
  
  var datatable = $('#libroiva').DataTable();
    datatable.destroy();

  var datatable1 = $('#libroivaventas').DataTable();
    datatable1.destroy();

    var datatable2 = $('#librocontrolventas').DataTable();
    datatable2.destroy();

if(libro==1){
 document.getElementById('ivacompras').style.display="block"; 
 document.getElementById('ivaventas').style.display="none";
  var nombre='Triangular S.A. Libro Iva Compras';
  $('#page-title').html(nombre);
  $('#page-title0').html(nombre);
  $('#titulo').html(nombre);
}else if(libro == 2){
 document.getElementById('ivaventas').style.display="block"; 
 document.getElementById('ivacompras').style.display="none"; 
  var nombre='Triangular S.A. Libro Iva Ventas';
  $('#page-title').html(nombre);
  $('#page-title0').html(nombre);
  $('#titulo_ventas').html(nombre);
}
    
  var Desde0=Desde.split('-');
  var Desde=Desde0[0]+'-'+Desde0[1]+'-'+Desde0[2];  
  var Hasta0=Hasta.split('-');
  var Hasta=Hasta0[0]+'-'+Hasta0[1]+'-'+Hasta0[2]; 
  
  var Titulo=nombre+' Desde '+Desde0[1]+'/'+Desde0[0]+'/'+Desde0[2]+' Hasta '+Hasta0[1]+'/'+Hasta0[0]+'/'+Hasta0[2];  
    
    if(libro == 1){
        var datatable = $('#libroiva').DataTable({
            dom: 'Bfrtip',
            buttons: [
            'copy', 'csv', 'excel',
              {
                extend: 'pdf',
                text: 'PDF',
                orientation: 'landscape',
                title: Titulo,
                filename: 'LibroIvaCompras', 
                header: true,
                pageSize: 'A4',
//             exportOptions : {
//               columns : [0,1,2,3,4,5,6], 
//                 stripHtml : false 
//                 },
            customize: function (doc) {
                 doc.styles.tableHeader = {
                     fillColor:'#525659',
                     color:'#FFF',
                     fontSize: '7',
                     alignment: 'left',
                     bold: true 
                 }, //para cambiar el backgorud del escabezado
                 doc.defaultStyle.fontSize = 6;
                 doc.pageMargins = [50,50,30,30];//left,top,right,bottom
                 doc.content[1].margin = [ 5, 0, 0, 5] // margenes para la datables
              }
            }
            ,'print'
            ],
            ajax: {
              url:"../Procesos/php/tablas.php",
              data:{'Iva':1,'desde':Desde,'hasta':Hasta},
              type:'post'
              },
              columns: [
                {data:"Fecha",
                render: function (data, type, row) {
                    return row.Fecha;
                }
            
                },
                {data:"RazonSocial",
                render: function (data, type, row) {
                    return '<td>['+row.idCliente+'] '+row.RazonSocial+'<br>CUIT: '+row.Cuit+'</td>';
                }
                },
                // {data:"Cuit"},
                {data:"TipoDeComprobante",
                render: function (data, type, row) {
                    return '<td>'+row.TipoDeComprobante+'<br>Nro: '+row.NumeroComprobante+'</td>';
                }},
                // {data:"NumeroComprobante"},
                {data:"ImporteNeto",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Iva1",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Iva3",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Iva2",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Exento",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"PercepcionIva",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"PercepcionIIBB",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"PercepcionComInd",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Total",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
              ]
           });

    }else if(libro==2){
      
     var datatable1 = $('#libroivaventas').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'pageLength','copy', 'csv', 'excel',
              {
                extend: 'pdf',
                text: 'PDF',
                orientation: 'landscape',
                title: 'Libro Iva Triangular S.A. '+Titulo,
                filename: 'LibroIvaCompras', 
                header: true,
                pageSize: 'A4',
//             exportOptions : {
//               columns : [0,1,2,3,4,5,6], 
//                 stripHtml : false 
//                 },


            // customize: function (doc) {
            //      doc.styles.tableHeader = {
            //          fillColor:'#525659',
            //          color:'#FFF',
            //          fontSize: '7',
            //          alignment: 'left',
            //          bold: true 
            //      }, //para cambiar el backgorud del escabezado
            //      doc.defaultStyle.fontSize = 6;
            //      doc.pageMargins = [50,50,30,30];//left,top,right,bottom
            //      doc.content[1].margin = [ 5, 0, 0, 5] // margenes para la datables
            //   }
            }
            // ,'print'
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
              url:"../Procesos/php/tablas.php",
              data:{'Iva':2,'desde':Desde,'hasta':Hasta},
              type:'post'
              },
              columns: [
                {data:"Fecha"},
                {data:"RazonSocial",
                render: function (data, type, row) {
                    return '<td>['+row.idCliente+'] '+row.RazonSocial+'</td>';
                }
                },
                {data:"Cuit"},
                {data:"TipoDeComprobante"},
                {data:"NumeroComprobante"},
                {data:"ImporteNeto",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                // {data:"Iva1",
                // render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                // {data:"Iva2",
                // render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Iva3",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Exento",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
                {data:"Total",
                render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
              ]
           });
      
    }
    });
});
