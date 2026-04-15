// SistemaTriangular/Admin/Procesos/js/resultados.js
(function () {
  const $tabla = $("#tablaResultados");
  let dt;

  // Fecha por defecto: primer y último día del mes actual
  function defaultFechas() {
    const hoy = new Date();
    const first = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const last = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    $("#fdesde").val(first.toISOString().slice(0, 10));
    $("#fhasta").val(last.toISOString().slice(0, 10));
  }
  function formatearMoneda(valor) {
    valor = parseFloat(valor || 0);
    return (
      "$ " +
      valor.toLocaleString("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    );
  }
  function formatearMoneda(valor) {
    valor = parseFloat(valor || 0);
    return (
      "$ " +
      valor.toLocaleString("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    );
  }

  function formatearPorcentaje(valor) {
    if (valor === null || valor === undefined || isNaN(valor)) return "—";
    return (
      Number(valor).toLocaleString("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }) + "%"
    );
  }

  function calcularRentabilidad(pagado, cobrado) {
    pagado = parseFloat(pagado);
    cobrado = parseFloat(cobrado);

    if (isNaN(cobrado) || cobrado === 0) return null;
    if (isNaN(pagado)) pagado = 0;

    return ((cobrado - pagado) / cobrado) * 100;
  }

  function claseResultado(valor) {
    valor = parseFloat(valor);
    if (isNaN(valor)) return "text-muted";
    if (valor > 0) return "text-success";
    if (valor < 0) return "text-danger";
    return "text-muted";
  }

  function actualizarResumenTabla() {
    if (!dt) return;

    let totalPagado = 0;
    let totalCobrado = 0;
    let totalDiferencia = 0;

    dt.rows({ search: "applied" }).every(function () {
      const row = this.data();
      if (!row) return;

      const pagado = parseFloat(row.PrecioPagado_SinIVA);
      const cobrado = parseFloat(row.PrecioCobrado_SinIVA);
      const diferencia = parseFloat(row.Diferencia_SinIVA);

      if (!isNaN(pagado)) totalPagado += pagado;
      if (!isNaN(cobrado)) totalCobrado += cobrado;
      if (!isNaN(diferencia)) totalDiferencia += diferencia;
    });

    const rentabilidadTotal = calcularRentabilidad(totalPagado, totalCobrado);
    const clase = claseResultado(totalDiferencia);

    $("#card_total_pagado").text(formatearMoneda(totalPagado));
    $("#card_total_cobrado").text(formatearMoneda(totalCobrado));

    $("#card_total_diferencia")
      .removeClass("text-success text-danger text-muted")
      .addClass(clase)
      .text(formatearMoneda(totalDiferencia));

    $("#card_total_rentabilidad_pct")
      .removeClass("text-success text-danger text-muted")
      .addClass(clase)
      .text(formatearPorcentaje(rentabilidadTotal));

    $("#total_pagado").text(formatearMoneda(totalPagado));
    $("#total_cobrado").text(formatearMoneda(totalCobrado));

    $("#total_diferencia")
      .removeClass("text-success text-danger text-muted")
      .addClass(clase)
      .text(formatearMoneda(totalDiferencia));

    $("#total_rentabilidad_pct")
      .removeClass("text-success text-danger text-muted")
      .addClass(clase)
      .text(formatearPorcentaje(rentabilidadTotal));
  }
  function dmy(iso) {
    if (!iso) return "";
    // iso: YYYY-MM-DD
    const p = iso.split(" ")[0].split("-");
    if (p.length !== 3) return iso;
    return [p[2], p[1], p[0]].join("/");
  }

  // Cargar checklist de clientes
  function cargarClientes() {
    const Inicio = $("#fdesde").val();
    const Final = $("#fhasta").val();

    $("#fcliente").html('<option value="">Cargando clientes...</option>');

    $.post(
      "/SistemaTriangular/Admin/Procesos/php/resultados.php",
      {
        action: "clientes",
        Inicio: Inicio,
        Final: Final,
      },
      function (json) {
        if (!json || !json.ok) {
          $("#fcliente").html(
            '<option value="">No se pudieron cargar</option>',
          );
          return;
        }

        const list = json.clientes || [];
        let html = '<option value="">Todos los clientes</option>';

        if (!list.length) {
          html = '<option value="">Sin clientes en el período</option>';
        } else {
          html += list
            .map(function (cli) {
              return `<option value="${cli.CodigoProveedor}">${cli.Nombre}</option>`;
            })
            .join("");
        }

        $("#fcliente").html(html);

        if ($.fn.select2) {
          $("#fcliente").select2("destroy");
          $("#fcliente").select2({
            width: "100%",
            placeholder: "Buscar cliente...",
            allowClear: true,
          });
        }
      },
      "json",
    );
  }

  // Init DataTable
  function initDT() {
    dt = $tabla.DataTable({
      processing: true,
      serverSide: false, // cargamos todo via AJAX simple
      searching: true,
      lengthChange: true,
      pageLength: 25,
      order: [[0, "desc"]],
      ajax: {
        url: "/SistemaTriangular/Admin/Procesos/php/resultados.php",
        type: "POST",
        data: function (d) {
          const Inicio = $("#fdesde").val();
          const Final = $("#fhasta").val();
          const cliente = $("#fcliente").val();
          return {
            action: "listar",
            Inicio: Inicio,
            Final: Final,
            cliente: cliente,
          };
        },
        dataSrc: function (json) {
          if (!json || !json.ok) return [];
          return json.data || [];
        },
      },
      columns: [
        // Fecha: mostrar dd/mm/YYYY y ocultar original en un span (tu estilo)
        {
          data: "Fecha",
          render: function (data, type, row) {
            if (type === "display" || type === "filter") {
              return (
                '<span style="display:none;">' +
                (data || "") +
                "</span>" +
                dmy(data)
              );
            }
            return data;
          },
        },
        { data: "NombreCliente" },
        { data: "CodigoSeguimiento" },
        // { data: "CodigoProveedor" },

        // { data: "Wepoint_f" },
        {
          data: null,
          title: "Estado",
          render: function (data, type, row) {
            if (type !== "display") {
              let estadoEntrega = "";
              if (parseInt(row.Devuelto) === 1) {
                estadoEntrega = "Devuelto";
              } else if (parseInt(row.Entregado) === 1) {
                estadoEntrega = "Entregado";
              } else {
                estadoEntrega = "No Entregado";
              }

              let estadoFactura =
                parseInt(row.Facturado) === 1 ? "Facturado" : "No Facturado";

              return estadoEntrega + " " + estadoFactura;
            }

            let badgeEntrega = "";
            if (parseInt(row.Devuelto) === 1) {
              badgeEntrega = '<span class="badge bg-danger">Devuelto</span>';
            } else if (parseInt(row.Entregado) === 1) {
              badgeEntrega = '<span class="badge bg-success">Entregado</span>';
            } else {
              badgeEntrega =
                '<span class="badge bg-warning text-dark">No Entregado</span>';
            }

            let badgeFactura =
              parseInt(row.Facturado) === 1
                ? '<span class="badge bg-info">Facturado</span>'
                : '<span class="badge bg-secondary">No Facturado</span>';

            return `
              <div class="d-flex flex-column gap-1">
                <div>${badgeEntrega}</div>
                <div>${badgeFactura}</div>
              </div>
            `;
          },
        },
        // { data: "NumeroF" },

        // Montos con formato $ y 2 decimales
        {
          data: "PrecioCobrado_SinIVA",
          render: function (data, type, row) {
            if (type !== "display") return data;
            return formatearMoneda(data);
          },
        },
        {
          data: "PrecioPagado_SinIVA",
          render: function (data, type, row) {
            if (type !== "display") return data;
            return formatearMoneda(data);
          },
        },
        {
          data: "Diferencia_SinIVA",
          render: function (data, type, row) {
            if (type !== "display") return data;

            const valor = parseFloat(data);
            const clase = claseResultado(valor);

            return `<span class="${clase} fw-semibold">${formatearMoneda(valor)}</span>`;
          },
        },
        {
          data: null,
          title: "Rentab. %",
          render: function (data, type, row) {
            const pagado = parseFloat(row.PrecioPagado_SinIVA);
            const cobrado = parseFloat(row.PrecioCobrado_SinIVA);
            const pct = calcularRentabilidad(pagado, cobrado);

            if (type !== "display") {
              return pct === null ? "" : pct;
            }

            const clase = claseResultado(row.Diferencia_SinIVA);

            return `<span class="${clase} fw-semibold">${formatearPorcentaje(pct)}</span>`;
          },
        },
      ],
      dom: "Bfrtip",
      buttons: [
        {
          extend: "excelHtml5",
          text: '<i class="mdi mdi-file-excel-outline"></i> Exportar Excel',
          className: "btn btn-success btn-sm ms-0 mt-3",
          title: "Resultados_Caddy_" + new Date().toISOString().slice(0, 10),
          exportOptions: { columns: ":visible" },
        },
      ],
    });
    dt.on("draw", function () {
      actualizarResumenTabla();
    });
  }

  // Eventos
  // $("#chkTodos").on("change", function () {
  //   const on = $(this).prop("checked");
  //   $(".chk-cli").prop("checked", on);
  // });

  $("#btnBuscar").on("click", function () {
    if (dt) dt.ajax.reload();
  });

  // Reload clientes cuando cambian fechas
  $("#fdesde, #fhasta").on("change", function () {
    cargarClientes();
  });

  // Start
  defaultFechas();
  cargarClientes();
  initDT();
})();
