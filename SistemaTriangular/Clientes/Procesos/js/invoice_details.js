//DETALLE DE COMPOSICION DE FACTURA
var idCtaCte = getParameterByName('id');
var datatable_facturacion = $('#tabla_facturacion_proforma_detalle').DataTable({
    paging: false,
    searching: false,
    footerCallback: function(row, data, start, end, display) {
      total = this.api()
        .column(5, {
          page: 'current'
        }) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function(a, b) {
          return Number(a) + Number(b);
        }, 0);
      var saldo = currencyFormat(total);
      var sumadebe = currencyFormat(total / 1.21);
      $(this.api().column(5).footer()).html(sumadebe);
      //TOTALES
      var neto = total / 1.21;
      var iva = Number(total - neto);
      $('#factura_neto').html(currencyFormat(neto));
      $('#factura_iva').html(currencyFormat(iva));
      $('#factura_total').html(saldo);
      $('#factura_neto_f').val(neto.toFixed(2));
      $('#factura_iva_f').val(iva.toFixed(2));
      $('#factura_total_f').val(total.toFixed(2));

      //INGRESO DATOS EN CUADRO FINAL DE FACTURACION        
      $('#neto_up').val(neto.toFixed(2));
      $('#iva_up').val(iva.toFixed(2));
      $('#total_up').val(total.toFixed(2));
    },
    ajax: {
      url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/invoice.php",
      data: {
        'FacturacionProformaDetalle': 1,
        "idCtaCte": idCtaCte          
      },
      type: 'post'
    },

    

    columns: [{
        data: "FechaPedido",
        render: function(data, type, row) {
          var Fecha = row.FechaPedido.split('-').reverse().join('.');
          return '<td><span style="display: none;">' + row.FechaPedido + '</span>' + Fecha + '</td>';

        }
      },
      {data: "NumPedido"},
      {data: "Titulo",
        render: function(data, type, row) {
          return `<td>${row.Titulo}</br>${row.NumeroRepo}`;
        }
      },
      {data: "ClienteDestino"},
      {data: "Comentario"},    
      {
        data: "Total",
        render: function(data, type, row) {
          var DebeNeto = row.Total / 1.21;
          return currencyFormat(DebeNeto);
        }
      }
    ],
  });
