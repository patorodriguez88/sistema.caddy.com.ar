$(document).ready(function () {
  var datatable_desempeno_dia = $("#desempeno_dia").DataTable();
  datatable_desempeno_dia.destroy();

  cpanel();

  //   setInterval(cpanel, 3000); // HABILITAR PARA ACTUALIZAR EN TIEMPO REAL

  $.ajax({
    data: { Best_guest: 1 },
    url: "php/funcionesAdmin.php",
    type: "post",
    beforeSend: function () {
      //      document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      //   alert(jsonData.success);
      if (jsonData.success == "1") {
        console.log("datos", jsonData);
        $("#cliente_1").html(
          jsonData.datos[0].RazonSocial +
            " (" +
            jsonData.datos[0].Total +
            " Envios)"
        );
        $("#cliente_2").html(
          jsonData.datos[1].RazonSocial +
            " (" +
            jsonData.datos[1].Total +
            " Envios)"
        );
        $("#cliente_3").html(
          jsonData.datos[2].RazonSocial +
            " (" +
            jsonData.datos[2].Total +
            " Envios)"
        );
        $("#cliente_4").html(
          jsonData.datos[3].RazonSocial +
            " (" +
            jsonData.datos[3].Total +
            " Envios)"
        );
        $("#cliente_5").html(
          jsonData.datos[4].RazonSocial +
            " (" +
            jsonData.datos[4].Total +
            " Envios)"
        );
        $("#cliente_6").html(
          jsonData.datos[5].RazonSocial +
            " (" +
            jsonData.datos[5].Total +
            " Envios)"
        );

        let Todo =
          Number(jsonData.datos[0].Total) +
          Number(jsonData.datos[1].Total) +
          Number(jsonData.datos[2].Total) +
          Number(jsonData.datos[3].Total) +
          Number(jsonData.datos[4].Total) +
          Number(jsonData.datos[5].Total);

        let newprogress_1 = Math.round((jsonData.datos[0].Total / Todo) * 100);
        let newprogress_2 = Math.round((jsonData.datos[1].Total / Todo) * 100);
        let newprogress_3 = Math.round((jsonData.datos[2].Total / Todo) * 100);
        let newprogress_4 = Math.round((jsonData.datos[3].Total / Todo) * 100);
        let newprogress_5 = Math.round((jsonData.datos[4].Total / Todo) * 100);
        let newprogress_6 = Math.round((jsonData.datos[5].Total / Todo) * 100);

        console.log("valores", Todo);
        $("#theprogressbar_1")
          .attr("aria-valuenow", newprogress_1 + "%")
          .css("width", newprogress_1 + "%");
        $("#cliente_1_total").html(newprogress_1 + " %");
        $("#theprogressbar_2")
          .attr("aria-valuenow", newprogress_2 + "%")
          .css("width", newprogress_2 + "%");
        $("#cliente_2_total").html(newprogress_2 + " %");
        $("#theprogressbar_3")
          .attr("aria-valuenow", newprogress_3 + "%")
          .css("width", newprogress_3 + "%");
        $("#cliente_3_total").html(newprogress_3 + " %");
        $("#theprogressbar_4")
          .attr("aria-valuenow", newprogress_4 + "%")
          .css("width", newprogress_4 + "%");
        $("#cliente_4_total").html(newprogress_4 + " %");
        $("#theprogressbar_5")
          .attr("aria-valuenow", newprogress_5 + "%")
          .css("width", newprogress_5 + "%");
        $("#cliente_5_total").html(newprogress_5 + " %");
        $("#theprogressbar_6")
          .attr("aria-valuenow", newprogress_6 + "%")
          .css("width", newprogress_6 + "%");
        $("#cliente_6_total").html(newprogress_6 + " %");
      }
    },
  });
});

$("#ver_gastos_btn").click(function () {
  var datatable = $("#gastos_detalle").DataTable();
  datatable.destroy();
  $("#gastos-mensuales-modal-lg").modal("show");
});

function currencyFormat(num) {
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

function addCommas(nStr) {
  nStr += "";
  x = nStr.split(".");
  x1 = x[0];
  x2 = x.length > 1 ? "." + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "," + "$2");
  }

  return x1 + x2;
}

function mes(m) {
  var meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  //   var inputMes = new Date().getMonth();
  var inputMes = m;
  var numeroMes = Number(inputMes);

  for (var i = 0; i < meses.length; i++) {
    if (i == numeroMes) {
      var mes = meses[i];
    }
  }
  return mes;
}

$("#mes").click(function () {
  $("#meses").modal("show");
  var datatable = $("#clientes").DataTable();
  datatable.destroy();
  var datatable = $("#desempeno_dia").DataTable();
  datatable.destroy();
  $("#mes_clientes").html(m);
});

$("#button_mes_select").click(function () {
  $("#meses").modal("hide");
  cpanel();
  //    grafico();
  var datatable = $("#clientes").DataTable();
  datatable.ajax.reload();
});

function cpanel() {
  var m = $("#mes_select").val();

  if (m == "") {
    var m = new Date().getMonth() + 1;
  }

  var date = new Date(),
    y = date.getFullYear(),
    m = m - 1;
  var firstDay = new Date(y, m, 1);
  var lastDay = new Date(y, m + 1, 0);

  firstDay = moment(firstDay).format("YYYY-MM-DD");
  lastDay = moment(lastDay).format("YYYY-MM-DD");

  // $('#mes').html(firstDay+ ' ' + lastDay);
  $("#mes").html(mes(m) + " " + date.getFullYear());
  $.ajax({
    data: { Gastos: 1, inicio: firstDay, final: lastDay },
    url: "php/funcionesAdmin.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == "1") {
        $("#TotalGastos").html("$ " + addCommas(jsonData.TotalGastos));
        //ENVIOS SIMPLES
        $("#TotalIngresos").html("$ " + addCommas(jsonData.TotalMes));
        //   $('#entregas_mes').html('$ '+addCommas(jsonData.TotalMes)+' este mes');
        $("#entregas_porc").html("% " + jsonData.Porcentaje);
        $("#entregas_mesant").html(
          "Desde el mes pasado ($ " + addCommas(jsonData.TotalMesant + " )")
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("entregas_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("entregas_porc").className =
            "mdi mdi-arrow-up-bold";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("entregas_porc").className =
            "mdi mdi-arrow-down-bold";
        }
        //TOTAL ENVIOS
        $("#resultado_mes").html("$ " + addCommas(jsonData.Resultado));
      }
    },
  });

  var datatable = $("#clientes").DataTable({
    dom: "Bfrtip",
    buttons: ["copy", "csv", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(2) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
          //                 return parseInt(a) + parseInt(b);
        }, 0);
      total1 = this.api()
        .column(3) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
        }, 0);
      var sumadebe = currencyFormat(total);
      var sumahaber = currencyFormat(total1);
      var saldo = currencyFormat(total - total1);
      var saldo1 = Number(total - total1);

      $(this.api().column(2).footer()).html(sumadebe);
      $(this.api().column(3).footer()).html(sumahaber);
    },

    ajax: {
      url: "php/funcionesAdmin.php",
      data: { Clientes: 1, inicio: firstDay, final: lastDay },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "Cliente",
        render: function (data, type, row) {
          return (
            '<td><i class="mdi mdi-18px mdi-account"></i> ' +
            row.Cliente +
            "</br></td>"
          );
        },
      },
      { data: "Cantidad" },
      {
        data: "Total",
        render: function (data, type, row) {
          return addCommas("$ " + row.Total);
        },
      },
      {
        data: "Prom",
        render: function (data, type, row) {
          var prom0 = parseFloat(row.Prom);
          return currencyFormat(prom0);
        },
      },
      {
        data: "idCliente",
        render: function (data, type, row) {
          return (
            '<td><i data-tittle="' +
            row.Cliente +
            '" data-id="' +
            row.idCliente +
            '" data-toggle="modal" data-target="#modal-clientes" class="mdi mdi-18px mdi-chart-areaspline text-success"></i></br></td>'
          );
        },
      },
    ],
  });

  $("#modal-clientes").on("show.bs.modal", function (e) {
    var triggerLink = $(e.relatedTarget);
    var id = triggerLink[0].dataset["id"];
    var title = triggerLink[0].dataset["tittle"];
    $("#modal-clientes-title").html("Ventas Anuales " + title);
    var colors = ["#0acf97"];
    $.ajax({
      data: { VentasClientes: 1, idCliente: id },
      url: "php/funcionesAdmin.php",
      type: "post",
      beforeSend: function () {},
      success: function (response) {
        var jsonData = JSON.parse(response);
        var options = {
          series: [],
          chart: {
            height: 350,
            type: "bar",
          },
          dataLabels: {
            enabled: false,
          },
          colors: colors,
          title: {
            text: "Ventas",
          },
          noData: {
            text: "Loading...",
          },
          xaxis: {
            categories: jsonData.x,
            type: "category",
            tickPlacement: "on",
            labels: {
              rotate: -45,
              rotateAlways: true,
            },
          },
        };
        var chart = new ApexCharts(
          document.querySelector("#chartClientes"),
          options
        );
        chart.render();
        chart.updateSeries([
          {
            name: "Ventas",
            data: jsonData.y,
          },
        ]);
      },
    });
  });

  //TABLA HOJA DE RUTA

  var datatable = $("#desempeno_dia").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "php/tablas.php",
      data: { Servicios: 1 },
      type: "post",
    },
    columns: [
      { data: "NumerodeOrden" },
      { data: "Chofer" },
      { data: "Dominio" },
      { data: "Recorrido" },
      { data: "Total" },
      { data: "Cerradas" },
      {
        data: "Cerradas",
        render: function (data, type, row, meta) {
          return type === "display"
            ? '<div class="progress progress-lg"><div class="progress-bar bg-success" role="progressbar" style="width: ' +
                (row.Cerradas / row.Total) * 100 +
                '%" aria-valuenow="' +
                row.Cerradas +
                '" aria-valuemin="0" aria-valuemax="' +
                row.Total +
                '"></div></div>'
            : data;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a data-id="' +
            row.NumerodeOrden +
            '" id="' +
            row.NumerodeOrden +
            '" onclick="modificar(this.id);" class="action-icon"><i class="mdi mdi-24px mdi-menu"></i>' +
            "</td>"
          );
        },
      },
    ],
  });

  //TABLA PARA SEGUIMIENTO DE RECORRIDOS
  function modificar(i) {
    var datatable = $("#seguimiento").DataTable({
      ajax: {
        url: "php/tablas.php",
        data: { Pendientes: 1, Orden: i },
        processing: true,
        type: "post",
      },
      columns: [
        {
          data: "id",
          render: function (data, type, row) {
            if (row.Estado == "No se Pudo entregar") {
              var circle = "danger";
            } else if (row.Estado == "Entregado al Cliente") {
              var circle = "success";
            } else {
              var circle = "warning";
            }
            if (row.Retirado == 1) {
              var Destino = row.ClienteDestino;
              var Direccion = row.DomicilioDestino;
            } else {
              var Destino = row.RazonSocial;
              var Direccion = row.DomicilioOrigen;
            }

            return (
              '<td><i class="mdi mdi-18px mdi-circle text-' +
              circle +
              '"></i> ' +
              Destino +
              "</br>" +
              '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">' +
              Direccion +
              "</td>"
            );
          },
        },
        {
          data: "CodigoSeguimiento",
          render: function (data, type, row) {
            return (
              '<td class="table-action">' +
              '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
              row.CodigoSeguimiento +
              '"' +
              'data-title="' +
              data.Destino +
              '" data-fieldname="' +
              data +
              '"><b>' +
              row.CodigoSeguimiento +
              "</b></a></td>"
            );
          },
        },
        {
          data: "Retirado",
          render: function (data, type, row) {
            if (row.Estado == "No se Pudo entregar") {
              var color = "danger";
            } else if (row.Estado == "Entregado al Cliente") {
              var color = "success";
            } else {
              var color = "warning";
            }
            if (row.Retirado == 1) {
              return (
                "<td>Entrega" +
                '<h6><span class="badge badge-' +
                color +
                ' text-white">' +
                row.Estado +
                "</span></h6></td>"
              );
            } else {
              return (
                "<td>Retiro" +
                '<h5><span class="badge badge-' +
                color +
                ' text-white">' +
                row.Estado +
                "</span></h5></td>"
              );
            }
          },
        },
        //           {data:"id",
        //            render: function (data, type, row) {
        //                 return '<td class="table-action">'+
        //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
        //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
        //                 '</td>';
        //             }
        //           }
      ],
    });
  }
}
//TABLA GASTOS

$("#gastos-mensuales-modal-lg").on("show.bs.modal", function (e) {
  var m = $("#mes_select").val();

  if (m == "") {
    var m = new Date().getMonth() + 1;
  }
  var date = new Date(),
    y = date.getFullYear(),
    m = m - 1;
  var firstDay = new Date(y, m, 1);
  var lastDay = new Date(y, m + 1, 0);

  firstDay = moment(firstDay).format("YYYY-MM-DD");
  lastDay = moment(lastDay).format("YYYY-MM-DD");
  $("#sub_header_gastos").html("Desde " + firstDay + " Hasta " + lastDay);

  var datatable = $("#gastos_detalle").DataTable({
    //   dom: 'Bfrtip',
    //   buttons: ['copy', 'csv', 'excel', 'pdf'],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(3) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
        }, 0);

      var sumadebe = currencyFormat(total);
      $(this.api().column(3).footer()).html(sumadebe);
    },

    ajax: {
      url: "php/funcionesAdmin.php",
      data: { GastosDetalles: 1, inicio: firstDay, final: lastDay },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "MuestraGastos",
        render: function (data, type, row) {
          if (row.MuestraGastos == 1) {
            var dato = "checked";
          } else {
            var dato = "";
          }
          return (
            '<td><input id="' +
            row.Cuenta +
            '" onclick="muestragastos(this.id);" type="checkbox" ' +
            dato +
            "></td>"
          );
        },
      },
      {
        data: "Cuenta",
        render: function (data, type, row) {
          return (
            '<td><i class="mdi mdi-18px mdi-account"></i> ' +
            row.Cuenta +
            "</br></td>"
          );
        },
      },
      { data: "NombreCuenta" },
      {
        data: "Debe",
        render: function (data, type, row) {
          var prom0 = parseFloat(row.Debe);
          return currencyFormat(prom0);
        },
      },
    ],
  });
});
$("#gastos-mensuales-modal-lg").on("hidden.bs.modal", function (e) {
  var datatable = $("#clientes").DataTable();
  datatable.destroy();

  var datatable_desempeno_dia = $("#desempeno_dia").DataTable();
  datatable_desempeno_dia.destroy();

  cpanel();
});
function muestragastos(i) {
  $.ajax({
    data: { MuestraGastos: 1, Cuenta: i },
    url: "php/funcionesAdmin.php",
    type: "post",
    beforeSend: function () {
      //      document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      //   alert(jsonData.success);
      if (jsonData.success == "1") {
        //   cpanel();
        // var datatable = $('#gastos_detalle').DataTable();
        // datatable.ajax.reload();
      }
    },
  });
}
