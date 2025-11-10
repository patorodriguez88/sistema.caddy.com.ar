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
    $("#wrapClientes").html(
      '<div class="text-muted small">Cargando clientes…</div>'
    );
    $.post(
      "/SistemaTriangular/Admin/Procesos/php/resultados.php",
      {
        action: "clientes",
        Inicio: Inicio,
        Final: Final,
      },
      function (json) {
        if (!json || !json.ok) {
          $("#wrapClientes").html(
            '<div class="text-danger small">No se pudieron cargar los clientes.</div>'
          );
          return;
        }
        const list = json.clientes || [];
        if (!list.length) {
          $("#wrapClientes").html(
            '<div class="text-muted small">Sin clientes en el período.</div>'
          );
          return;
        }
        const html = list
          .map(function (cod) {
            const id = "cli_" + cod;
            return `
          <div class="form-check">
            <input class="form-check-input chk-cli" type="checkbox" value="${cod}" id="${id}">
            <label class="form-check-label" for="${id}">${cod}</label>
          </div>`;
          })
          .join("");
        $("#wrapClientes").html(html);
        // si está "Todos" marcado, marcar todo
        if ($("#chkTodos").prop("checked")) {
          $(".chk-cli").prop("checked", true);
        }
      },
      "json"
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
          let clientes = [];
          if (!$("#chkTodos").prop("checked")) {
            $(".chk-cli:checked").each(function () {
              clientes.push($(this).val());
            });
          }
          return {
            action: "listar",
            Inicio: Inicio,
            Final: Final,
            clientes: clientes, // array
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
        { data: "CodigoSeguimiento" },
        { data: "CodigoProveedor" },
        { data: "Wepoint_f" },
        { data: "Entregado" },
        { data: "Devuelto" },
        { data: "Facturado" },
        { data: "NumeroF" },

        // Montos con formato $ y 2 decimales
        {
          data: "PrecioPagado_SinIVA",
          render: $.fn.dataTable.render.number(",", ".", 2, "$ "),
        },
        {
          data: "PrecioCobrado_SinIVA",
          render: $.fn.dataTable.render.number(",", ".", 2, "$ "),
        },
        {
          data: "Diferencia_SinIVA",
          render: $.fn.dataTable.render.number(",", ".", 2, "$ "),
        },

        // FechaComprobante
        {
          data: "FechaComprobante",
          render: function (data, type, row) {
            if (!data) return "";
            if (type === "display" || type === "filter") {
              return (
                '<span style="display:none;">' + data + "</span>" + dmy(data)
              );
            }
            return data;
          },
        },
        { data: "NumeroComprobante" },
        { data: "IdEmpleado" },
      ],
      dom: "Bfrtip",
      buttons: [
        {
          extend: "excelHtml5",
          text: '<i class="mdi mdi-file-excel"></i> Exportar Excel',
          className: "btn btn-success btn-sm",
          title: "Resultados_TransClientes_vs_Externos",
          exportOptions: { columns: ":visible" },
        },
      ],
    });
  }

  // Eventos
  $("#chkTodos").on("change", function () {
    const on = $(this).prop("checked");
    $(".chk-cli").prop("checked", on);
  });

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
