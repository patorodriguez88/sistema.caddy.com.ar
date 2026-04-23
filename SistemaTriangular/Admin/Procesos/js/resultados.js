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
  function badgeTipoLiquidacion(tipo) {
    if (!tipo) return "-";

    const t = String(tipo).toUpperCase();

    if (t.indexOf("NO ENTREGADO") !== -1) {
      return '<span class="badge bg-warning text-dark">No Entregado</span>';
    }
    if (t.indexOf("DEVOLUCION") !== -1 || t.indexOf("DEVUELTO") !== -1) {
      return '<span class="badge bg-danger">Devolución</span>';
    }
    if (t.indexOf("ENTREGA") !== -1) {
      return '<span class="badge bg-success">Entrega</span>';
    }
    if (t.indexOf("COLECTA") !== -1) {
      return '<span class="badge bg-info">Colecta</span>';
    }

    return '<span class="badge bg-secondary">' + tipo + "</span>";
  }
  function renderTablaCompras(compras) {
    if (!compras || !compras.length) {
      return '<div class="text-muted">Sin pagos al externo registrados.</div>';
    }

    let totalPagado = 0;

    const filas = compras
      .map(function (c, i) {
        const pagado = parseFloat(c.PrecioPagado || 0);
        totalPagado += isNaN(pagado) ? 0 : pagado;

        return `
        <tr>
          <td>${i + 1}</td>
          <td>${c.Repartidor || "-"}</td>
          <td>${badgeTipoLiquidacion(c.TipoLiquidacion)}</td>
          <td class="text-end">${formatearMoneda(c.PrecioPagado)}</td>
          <td>${c.NumeroComprobante || "-"}</td>
          <td>${dmy(c.FechaComprobante || "")}</td>
          <td>${dmy(c.FechaRendido || "")}</td>
        </tr>
      `;
      })
      .join("");

    return `
    <div class="table-responsive">
      
      <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size: 0.60rem;">
      <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Repartidor</th>
            <th>Tipo</th>
            <th class="text-end">Pagado</th>
            <th>Comprobante</th>
            <th>Fecha Comp.</th>
            <th>Fecha Rendido</th>
          </tr>
        </thead>
        <tbody>
          ${filas}
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Total pagado</th>
            <th class="text-end text-danger">${formatearMoneda(totalPagado)}</th>
            <th colspan="3"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  `;
  }
  function renderDetalleResultado(resp) {
    const venta = resp.venta || {};
    const compras = resp.compras || [];
    const resumen = resp.resumen || {};

    const claseResultadoFinal = claseResultado(resumen.Resultado);
    const claseRentabilidad = claseResultado(resumen.Resultado);

    return `
    <div class="mb-4">
      <h6 class="text-uppercase text-muted">Proceso de venta</h6>
      <div class="border rounded p-3" style="font-size: 0.65rem;">

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Código</span>
          <span>${venta.CodigoSeguimiento || "-"}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Cliente</span>
          <span class="text-end ms-3">${venta.NombreCliente || "-"}</span>
        </div>
        
        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Numero de Orden</span>
          <span>${venta.NumerodeOrden || "-"}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Recorrido</span>
          <span>${venta.Recorrido || "-"}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Origen cobrado</span>
          <span class="${venta.OrigenCobrado === "PRORRATEO_RECORRIDO" ? "text-info fw-semibold" : "text-muted"}">
            ${venta.OrigenCobrado || "-"}
          </span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Facturado</span>
          <span>${parseInt(venta.Facturado, 10) === 1 ? "Sí" : "No"}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Número factura</span>
          <span>${venta.NumeroF || "-"}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Fecha factura</span>
          <span>${dmy(venta.Fecha || "")}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">Importe neto</span>
          <span>${formatearMoneda(venta.NetoSinIVA)}</span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-semibold">IVA</span>
          <span>${formatearMoneda(venta.IVA)}</span>
        </div>

        <div class="d-flex justify-content-between py-1">
          <span class="fw-semibold">Total</span>
          <span class="fw-bold">${formatearMoneda(venta.TotalConIVA)}</span>
        </div>

      </div>
    </div>

    <div class="mb-4">
      <h6 class="text-uppercase text-muted">Proceso de compra / pagos al externo</h6>
      ${renderTablaCompras(compras)}
    </div>

    <div>
      <h6 class="text-uppercase text-muted">Resultado del servicio</h6>
      <div class="border rounded p-3">
        <div><strong>Total cobrado neto:</strong> ${formatearMoneda(resumen.TotalCobradoNeto)}</div>
        <div><strong>Total pagado:</strong> ${formatearMoneda(resumen.TotalPagado)}</div>
        <div><strong>Resultado:</strong> <span class="${claseResultadoFinal} fw-semibold">${formatearMoneda(resumen.Resultado)}</span></div>
        <div><strong>Rentabilidad:</strong> <span class="${claseRentabilidad} fw-semibold">${formatearPorcentaje(resumen.Rentabilidad)}</span></div>
      </div>
    </div>
  `;
  }
  function abrirDetalleResultado(codigoSeguimiento) {
    $.post(
      "/SistemaTriangular/Admin/Procesos/php/resultados.php",
      {
        action: "detalle",
        Inicio: $("#fdesde").val(),
        Final: $("#fhasta").val(),
        CodigoSeguimiento: codigoSeguimiento,
      },
      function (json) {
        if (!json || !json.ok) {
          $("#detalleResultadoContenido").html(
            '<div class="text-danger">No se pudo cargar el detalle.</div>',
          );
          return;
        }

        $("#detalleResultadoContenido").html(renderDetalleResultado(json));

        const offcanvasEl = document.getElementById(
          "offcanvasDetalleResultado",
        );
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        offcanvas.show();
      },
      "json",
    );
  }
  $("#tablaResultados tbody").on("click", "tr", function () {
    if (!dt) return;

    $("#tablaResultados tbody tr").removeClass("table-active");
    $(this).addClass("table-active");

    const data = dt.row(this).data();
    if (!data || !data.CodigoSeguimiento) return;

    abrirDetalleResultado(data.CodigoSeguimiento);
  });
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
  function cargarRepartidores() {
    const Inicio = $("#fdesde").val();
    const Final = $("#fhasta").val();

    $("#frepartidor").html(
      '<option value="">Cargando repartidores...</option>',
    );

    $.post(
      "/SistemaTriangular/Admin/Procesos/php/resultados.php",
      {
        action: "repartidores",
        Inicio: Inicio,
        Final: Final,
      },
      function (json) {
        if (!json || !json.ok) {
          $("#frepartidor").html(
            '<option value="">No se pudieron cargar</option>',
          );
          return;
        }

        const list = json.repartidores || [];
        let html = '<option value="">Todos los repartidores</option>';

        if (!list.length) {
          html = '<option value="">Sin repartidores en el período</option>';
        } else {
          html += list
            .map(function (rep) {
              return `<option value="${rep.id}">${rep.Nombre}</option>`;
            })
            .join("");
        }

        $("#frepartidor").html(html);

        if ($.fn.select2) {
          $("#frepartidor").select2("destroy");
          $("#frepartidor").select2({
            width: "100%",
            placeholder: "Buscar repartidor...",
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
          const repartidor = $("#frepartidor").val();
          return {
            action: "listar",
            Inicio: Inicio,
            Final: Final,
            cliente: cliente,
            repartidor: repartidor,
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
          render: function (data, type) {
            if (type !== "display") return data;
            return formatearMoneda(data);
          },
        },
        {
          data: "PrecioPagado_SinIVA",
          render: function (data, type) {
            if (type !== "display") return data;
            return formatearMoneda(data);
          },
        },
        {
          data: "Diferencia_SinIVA",
          render: function (data, type) {
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
    cargarRepartidores();
  });

  // Start
  defaultFechas();
  cargarClientes();
  cargarRepartidores();
  initDT();
})();
