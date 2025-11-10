function renderDonutsPorZona(capital, interior) {
  const estados = ["Entregados", "En tránsito", "Sin movimiento", "Devueltos"];
  const colores = ["#0acf97", "#ffbc00", "#fa5c7c", "#6c757d"];

  const totalCapital = estados.reduce(
    (sum, key) => sum + (parseInt(capital[key]) || 0),
    0
  );
  const totalInterior = estados.reduce(
    (sum, key) => sum + (parseInt(interior[key]) || 0),
    0
  );

  const seriesCapital = estados.map((key) =>
    totalCapital > 0 ? ((parseInt(capital[key]) || 0) * 100) / totalCapital : 0
  );
  const seriesInterior = estados.map((key) =>
    totalInterior > 0
      ? ((parseInt(interior[key]) || 0) * 100) / totalInterior
      : 0
  );

  const options = (series) => ({
    chart: { type: "donut", height: 320 },
    labels: estados,
    series: series.map((s) => parseFloat(s.toFixed(1))),
    colors: colores,
    dataLabels: {
      formatter: (val) => (val > 0 ? `${val.toFixed(1)}%` : ""),
    },
    legend: { position: "bottom" },
    tooltip: {
      y: { formatter: (val) => `${val.toFixed(1)} %` },
    },
  });

  const chartCapital = new ApexCharts(
    document.querySelector("#grafico_capital"),
    options(seriesCapital)
  );
  const chartInterior = new ApexCharts(
    document.querySelector("#grafico_interior"),
    options(seriesInterior)
  );

  chartCapital.render();
  chartInterior.render();
}
function cargarServiciosDashboard(data) {
  const entregados = parseInt(data.entregados) || 0;
  const transito = parseInt(data.transito) || 0;
  const sinmov = parseInt(data.sinmov) || 0;
  const devueltos = parseInt(data.devueltos) || 0;
  const total_capital =
    (data.entregados_capital || 0) +
    (data.transito_capital || 0) +
    (data.sinmov_capital || 0) +
    (data.devueltos_capital || 0);

  const total_interior =
    (data.entregados_interior || 0) +
    (data.transito_interior || 0) +
    (data.sinmov_interior || 0) +
    (data.devueltos_interior || 0);

  $("#card_total_detalle").text(
    `Capital: ${total_capital} | Interior: ${total_interior}`
  );

  const total = total_capital + total_interior;

  $("#card_total").text(total);

  // 🔢 Actualizar tarjetas
  $("#card_entregados").text(entregados);
  $("#card_transito").text(transito);
  $("#card_sinmov").text(sinmov);
  $("#card_devueltos").text(devueltos);
  $("#card_total").text(total);

  // Detalles Capital/Interior
  $("#card_entregados_detalle").text(
    `Capital: ${data.entregados_capital} | Interior: ${data.entregados_interior}`
  );
  $("#card_transito_detalle").text(
    `Capital: ${data.transito_capital} | Interior: ${data.transito_interior}`
  );
  $("#card_sinmov_detalle").text(
    `Capital: ${data.sinmov_capital} | Interior: ${data.sinmov_interior}`
  );
  $("#card_devueltos_detalle").text(
    `Capital: ${data.devueltos_capital} | Interior: ${data.devueltos_interior}`
  );
}
function mostrarTablaServicios(data) {
  const capital = data.Capital;
  const interior = data.Interior;

  const estados = ["Entregados", "EnTransito", "SinMovimiento", "Devueltos"];
  const etiquetas = {
    Entregados: "Entregados",
    EnTransito: "En tránsito",
    SinMovimiento: "Sin movimiento",
    Devueltos: "Devueltos",
  };
  const colores = {
    Entregados: "success",
    EnTransito: "warning",
    SinMovimiento: "danger",
    Devueltos: "secondary",
  };

  let html = "";
  let total_general = 0;
  console.log("Datos recibidos:", data);
  // Calcular total general
  estados.forEach((estado) => {
    total_general +=
      (parseInt(capital[estado]) || 0) + (parseInt(interior[estado]) || 0);
  });

  estados.forEach((estado) => {
    const valor_cap = parseInt(capital[estado]) || 0;
    const valor_int = parseInt(interior[estado]) || 0;
    const total_estado = valor_cap + valor_int;
    const porcentaje =
      total_general > 0 ? ((total_estado / total_general) * 100).toFixed(1) : 0;

    html += `
        <tr>
          <td>${etiquetas[estado]}</td>
          <td class="text-center">${total_estado}</td>
          <td class="text-center">${porcentaje}%</td>
          <td>
            <div class="progress" style="height: 12px;">
              <div class="progress-bar bg-${colores[estado]}" role="progressbar" style="width: ${porcentaje}%"
                aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>
            <small class="text-muted">Capital: ${valor_cap} | Interior: ${valor_int}</small>
          </td>
        </tr>`;
  });

  $("#tabla_servicios_dashboard").html(html);
}
function obtenerEstadoBadge(row) {
  if (parseInt(row.Entregado) === 1) {
    return '<span class="badge bg-success text-white">Entregado</span>';
  }

  if (parseInt(row.Devuelto) === 1) {
    return '<span class="badge bg-secondary text-white">Devuelto</span>';
  }

  const visitas = parseInt(row.Visitas) || 0;

  if (visitas >= 1) {
    return '<span class="badge bg-warning text-dark">En tránsito</span>';
  }

  return '<span class="badge bg-danger text-white">Sin movimiento</span>';
}

$("#colap").click(function () {
  $(".left-side-menu").addClass("leftSidebarCondensed", "false");
});

$("#bs-example-modal-lg").on("hidden.bs.modal", function () {
  var datatable = $("#seguir_table").DataTable();

  datatable.destroy();
});

function rotulo(i) {
  let href = "https://www.caddy.com.ar/report/rotulo?id=" + i;

  window.open(href, "_blank");
}

function seguir(i) {
  $("#bs-example-modal-lg").modal("show");
  $("#myLargeModalLabel").html("Seguimiento del Código " + i);

  var datatable = $("#seguir_table").DataTable({
    paging: false,
    searching: false,
    bInfo: false,
    columnDefs: [
      {
        targets: "_all",
        sortable: false,
      },
    ],
    ajax: {
      url: "Procesos/php/dashboard_plataforma.php",
      data: { Seguir: 1, CodSeguimiento: i },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span style="display: none;">' +
            row.Fecha +
            "</span>" +
            Fecha +
            "</td>"
          );
        },
      },
      { data: "Usuario" },
      { data: "Estado" },
      { data: "Observaciones" },
      { data: "Visitas" },
    ],
  });
}

$(document).ready(function () {
  $.ajax({
    type: "POST",
    url: "Procesos/php/dashboard_plataforma.php",
    data: { ListarClientes: 1 },
    dataType: "json",
    success: function (data) {
      if (Array.isArray(data)) {
        data.forEach(function (cliente) {
          $("#select_cliente").append(
            `<option value="${cliente.id}">${cliente.nombre}</option>`
          );
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("Error cargando clientes:", error);
    },
  });
  $("#tabla_basic").css("display", "none");
});

let fechas;
function currencyFormat(num) {
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

// Re-draw the table when the a date range filter changes
$("#singledaterange").change(function () {
  // var fechas=$('#singledaterange').val();
  let fechas = $("#singledaterange").val();
  let idCliente = $("#select_cliente").val();

  var datatable = $("#basic").DataTable();
  datatable.destroy();
  // alert(fechas);
  $.ajax({
    data: { VerFechas: 1, Fechas: fechas },
    url: "Procesos/php/dashboard_plataforma.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var Inicio = jsonData.Inicio;
      var Final = jsonData.Final;

      $("#title_envios").html("MIS ENVIOS DESDE " + Inicio + " HASTA " + Final);

      var datatable = $("#basic").DataTable({
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "print"],
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "All"],
        ],
        paging: true,
        searching: true,
        footerCallback: function (row, data, start, end, display) {
          total = this.api()
            //   .column(6) //numero de columna a sumar
            .column(6, { page: "current" }) //para sumar solo la pagina actual
            .data()
            .reduce(function (a, b) {
              return Number(a) + Number(b);
              //                 return parseInt(a) + parseInt(b);
            }, 0);
          var saldo = currencyFormat(total);

          $(this.api().column(6).footer()).html(saldo);
        },

        ajax: {
          url: "Procesos/php/dashboard_plataforma.php",
          data: {
            Pendientes: 1,
            Inicio: Inicio,
            Final: Final,
            idCliente: idCliente,
          },
          type: "post",
        },
        columns: [
          {
            data: "Fecha",
            render: function (data, type, row) {
              var Fecha = row.Fecha.split("-").reverse().join(".");
              return (
                '<td><span style="display: none;">' +
                row.Fecha +
                "</span>" +
                Fecha +
                "</td>"
              );
            },
          },
          {
            data: "NumeroComprobante",
            render: function (data, type, row) {
              return (
                "<td>" +
                row.NumeroComprobante +
                "</br>" +
                row.CodigoSeguimiento +
                "</td>"
              );
            },
          },
          { data: "RazonSocial" },
          {
            data: "ClienteDestino",
            render: function (data, type, row) {
              if (row.Entregado == 0) {
                var circle = "danger";
              } else {
                var circle = "success";
              }

              return (
                '<td><i class="mdi mdi-18px mdi-circle text-' +
                circle +
                '"></i> ' +
                row.ClienteDestino +
                "</br>" +
                '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">' +
                row.DomicilioDestino +
                "</td>"
              );
            },
          },
          // {data:"CodigoSeguimiento"},
          { data: "CodigoProveedor" },
          {
            data: "id",
            render: function (data, type, row) {
              return obtenerEstadoBadge(row);
            },
          },
          { data: "Cantidad" },
          { data: "Debe" },
          {
            data: "id",
            render: function (data, type, row) {
              return (
                '<td><i id="' +
                row.CodigoSeguimiento +
                '" class="mdi mdi-24px mdi-card-search text-primary"  style="cursor:point;" onclick="seguir(this.id);"></i></td>'
              );
            },
          },
        ],
      });
      //grafico
      $.ajax({
        url: "Procesos/php/dashboard_plataforma.php",
        type: "POST",
        data: {
          DashboardServicios: true,
          Inicio: Inicio,
          Final: Final,
          idCliente: idCliente,
        },
        success: function (response) {
          const datos = JSON.parse(response);
          mostrarTablaServicios(datos);
          // Sumamos los valores para total general
          const entregados =
            parseInt(datos.Capital.Entregados) +
            parseInt(datos.Interior.Entregados);
          const transito =
            parseInt(datos.Capital.EnTransito) +
            parseInt(datos.Interior.EnTransito);
          const sinmov =
            parseInt(datos.Capital.SinMovimiento) +
            parseInt(datos.Interior.SinMovimiento);
          const devueltos =
            parseInt(datos.Capital.Devueltos) +
            parseInt(datos.Interior.Devueltos);

          renderDonutsPorZona(datos.Capital, datos.Interior); // 👈 ahora esta es la única que renderiza gráficamente
          // Usamos una sola función que recibe todo
          cargarServiciosDashboard({
            entregados: entregados,
            transito: transito,
            sinmov: sinmov,
            devueltos: devueltos,
            entregados_capital: parseInt(datos.Capital.Entregados),
            entregados_interior: parseInt(datos.Interior.Entregados),
            transito_capital: parseInt(datos.Capital.EnTransito),
            transito_interior: parseInt(datos.Interior.EnTransito),
            sinmov_capital: parseInt(datos.Capital.SinMovimiento),
            sinmov_interior: parseInt(datos.Interior.SinMovimiento),
            devueltos_capital: parseInt(datos.Capital.Devueltos),
            devueltos_interior: parseInt(datos.Interior.Devueltos),
          });
        },
      });
    },
  });
  $("#dashboard_grafic").css("display", "block");
  $("#tabla_basic").css("display", "block");
});
