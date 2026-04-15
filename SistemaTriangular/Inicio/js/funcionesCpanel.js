// ==============================
// FUNCIONES CPANEL - REFACTORIZADO
// ==============================

// ------------------------------
// Helpers sesión / auth
// ------------------------------
function redirectIf401(xhr) {
  if (xhr && xhr.status === 401) {
    // window.location.href = "/SistemaTriangular/inicio.php?expired=1";
    return true;
  }
  return false;
}

function dtAjaxCommon() {
  return {
    type: "post",
    dataSrc: function (json) {
      try {
        if (json && json.ok === false && json.error === "NO_AUTH") {
          // window.location.href = "/SistemaTriangular/inicio.php?expired=1";
          return [];
        }
        return json && json.data ? json.data : [];
      } catch (e) {
        console.error("DataTables dataSrc parse error", e, json);
        return [];
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    },
  };
}

// ------------------------------
// Log rápido cookie
// ------------------------------
try {
  console.debug(
    "CADDY cookie presente:",
    document.cookie.includes("CADDYSESS="),
  );
} catch (e) {}

// ------------------------------
// Helpers dashboard
// ------------------------------
function safeInt(v) {
  const n = parseInt(v, 10);
  return Number.isFinite(n) ? n : 0;
}

function safePct(num, total) {
  if (!total || total <= 0) return 0;
  return Math.round((num * 100) / total);
}

function pctClass(pct) {
  if (pct >= 80) return "text-success";
  if (pct >= 50) return "text-warning";
  return "text-danger";
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setHtml(id, value) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = value;
}

function setWidth(id, value) {
  const el = document.getElementById(id);
  if (el) el.style.width = value + "%";
}

function setPctColor(id, pct) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove("text-success", "text-warning", "text-danger");
  el.classList.add(pctClass(pct));
}

function renderOperativoCard(prefix, total, entregados, pendientes) {
  total = safeInt(total);
  entregados = safeInt(entregados);
  pendientes = safeInt(pendientes);

  const pct = safePct(entregados, total);
  const pendPct = Math.max(0, 100 - pct);

  setText(`op_${prefix}_total`, total);
  setText(`op_${prefix}_entregados`, `${entregados} entregados`);
  setText(`op_${prefix}_pendientes`, `${pendientes} pendientes`);
  setText(`op_${prefix}_pct_label`, `${pct}%`);
  setText(`op_${prefix}_pct_text`, `${pct}% completado`);

  setWidth(`op_${prefix}_bar_ok`, pct);
  setWidth(`op_${prefix}_bar_pend`, pendPct);

  setPctColor(`op_${prefix}_pct_label`, pct);
}

let updatingStats = false;

function updateStats() {
  if (updatingStats) return;
  updatingStats = true;

  $.ajax({
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    dataType: "json",
    data: { DashboardOperativo: 1 },
    success: function (jsonData) {
      if (!jsonData || jsonData.success != 1) return;

      // KPIs superiores
      setText("kpi_pendientes_total", safeInt(jsonData.pendientes_total));
      setText(
        "kpi_pendientes_sin_salir",
        `${safeInt(jsonData.pendientes_sin_salir)} sin salir`,
      );
      setText(
        "kpi_pendientes_en_ruta",
        `${safeInt(jsonData.pendientes_en_ruta)} en ruta`,
      );

      setText("kpi_en_ruta_total", safeInt(jsonData.en_ruta_total));
      setText(
        "kpi_en_ruta_recorridos",
        `${safeInt(jsonData.recorridos_activos)} recorridos activos`,
      );

      setText("kpi_entregados_total", safeInt(jsonData.entregados_total));
      setHtml(
        "kpi_entregados_variacion",
        `${jsonData.entregados_variacion ?? 0}%`,
      );

      setText("kpi_incidencias_total", safeInt(jsonData.incidencias_total));
      setText(
        "kpi_incidencias_detalle",
        `${safeInt(jsonData.ausentes)} ausentes · ${safeInt(jsonData.rechazados)} rechazados · ${safeInt(jsonData.reprogramados)} reprog.`,
      );

      // Operativo del día
      renderOperativoCard(
        "simples",
        jsonData.simples_total,
        jsonData.simples_entregados,
        jsonData.simples_pendientes,
      );

      renderOperativoCard(
        "flex",
        jsonData.flex_total,
        jsonData.flex_entregados,
        jsonData.flex_pendientes,
      );

      renderOperativoCard(
        "meli",
        jsonData.meli_total,
        jsonData.meli_entregados,
        jsonData.meli_pendientes,
      );
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error updateStats", xhr.status, xhr.responseText);
    },
    complete: function () {
      updatingStats = false;
    },
  });
}

function initDashboard() {
  $("#mes").html("Panel de Control");
  updateStats();
  window.setInterval(updateStats, 30000);
}

// ------------------------------
// DataTables
// ------------------------------
function initTransporteTable() {
  $("#transporte").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Transporte: 1 },
    }),
    columns: [
      {
        data: "Estado",
        render: function (data, type, row) {
          let color = "secondary";
          if (row.Estado == "Cargada") color = "success";
          else if (row.Estado == "Alta") color = "danger";
          else if (row.Estado == "Pendiente") color = "warning";

          return `<i class="mdi mdi-circle text-${color}"></i>`;
        },
      },
      { data: "NumerodeOrden" },
      {
        data: "Fecha",
        render: function (data, type, row) {
          return `
            ${row.Fecha}<br>
            <small class="text-muted">${row.Hora}</small>
          `;
        },
      },
      { data: "Patente" },
      {
        data: "NombreChofer",
        render: function (data, type, row) {
          return `
            ${row.NombreChofer}<br>
            <small class="text-muted">${row.NombreChofer2 || ""}</small>
          `;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return `
            ${row.Recorrido}<br>
            <small class="text-muted">${row.Nombre || ""}</small>
          `;
        },
      },
      {
        data: "Estado",
        render: function (data, type, row) {
          let bgClass = "bg-secondary";
          if (row.Estado == "Cargada") bgClass = "bg-success";
          else if (row.Estado == "Alta") bgClass = "bg-danger";
          else if (row.Estado == "Pendiente") bgClass = "bg-warning";

          return `<span class="badge ${bgClass}">${row.Estado}</span>`;
        },
      },
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `
            <a data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#full-width-modal_order">
              <i class="mdi mdi-24px mdi-go-kart-track text-success"></i>
            </a>
          `;
        },
      },
    ],
  });
}

function initPreventaCard() {
  $.ajax({
    data: { PreVenta: 1 },
    url: "../Inicio/php/tablasCpanel.php",
    type: "post",
    success: function (response) {
      let jsonData = {};
      try {
        jsonData = JSON.parse(response);
      } catch (e) {
        console.error("JSON parse error PreVenta", e, response);
        return;
      }

      if (jsonData.success != 0) {
        document.getElementById("preventa").style.display = "block";
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    },
  });
}

function initPreventaTable() {
  $("#tabla_preventa").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { PreVenta: 1 },
    }),
    columns: [
      { data: "RazonSocial" },
      { data: "DomicilioOrigen" },
      { data: "Cantidad" },
      {
        data: "id",
        orderable: false,
        render: function () {
          return `
            <a href="../Ventas/Pendientes.php">
              <i class="mdi mdi-24px mdi-map-search-outline"></i>
            </a>
          `;
        },
      },
    ],
  });
}

function initFlotaTable() {
  $("#flota").DataTable({
    paging: false,
    searching: false,
    scrollX: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Flota: 1 },
    }),
    columns: [
      { data: "Marca" },
      { data: "Dominio" },
      { data: "Ano" },
      { data: "Kilometros" },
      {
        data: "Estado",
        render: function (data, type, row) {
          let color = "danger";
          if (row.Estado == "Disponible") color = "success";
          else if (row.Estado == "Otro" || row.Estado == "En Taller")
            color = "warning";

          return `<i class="mdi mdi-circle text-${color}"></i> ${row.Estado}`;
        },
      },
    ],
  });
}

function initLogisticaTable() {
  $("#logistica").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Logistica: 1 },
    }),
    columns: [
      {
        data: "ColorSistema",
        render: function () {
          return `<i class="mdi mdi-24px mdi-truck text-success"></i>`;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return `
            ${row.Recorrido}<br>
            <small class="text-muted">${row.Nombre || ""}</small>
          `;
        },
      },
      {
        data: "Marca",
        render: function (data, type, row) {
          return `
            ${row.Dominio}<br>
            <small class="text-muted">${row.Marca || ""}</small>
          `;
        },
      },
      { data: "Chofer" },
      { data: "id" },
      {
        data: "NumerodeOrden",
        orderable: false,
        render: function (data, type, row) {
          return `
            <a href="../Inicio/Cpanel_Original.php?Recorrido=${row.Recorrido}&NO=${row.NumerodeOrden}">
              <i class="mdi mdi-24px mdi-map-search-outline"></i>
            </a>
          `;
        },
      },
    ],
  });
}

function initLogistica1Table() {
  $("#logistica1").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Logistica1: 1 },
    }),
    columns: [
      {
        data: "Color",
        render: function (data, type, row) {
          return `<i class="mdi mdi-24px mdi-truck" style="color:#${row.Color}"></i>`;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return `
            ${row.Recorrido}<br>
            <small>${row.Nombre || ""}</small>
          `;
        },
      },
      { data: "Zona" },
      { data: "id" },
      {
        data: "Recorrido",
        orderable: false,
        render: function (data, type, row) {
          return `
            <a href="javascript:void(0);"
               class="btn-ver-pendientes"
               data-id="${row.Recorrido}"
               data-fieldname="${data}"
               data-bs-toggle="modal"
               data-bs-target="#bs-example-modal-lg">
              <i class="mdi mdi-24px mdi-file-search-outline"></i>
            </a>
          `;
        },
      },
      {
        data: "Recorrido",
        orderable: false,
        render: function (data, type, row) {
          return `
            <a data-id="${row.Recorrido}"
               data-fieldname="${data}"
               data-bs-toggle="modal"
               data-bs-target="#deposito-modal">
              <i class="mdi mdi-24px mdi-download-circle text-danger"></i>
            </a>
          `;
        },
      },
    ],
  });
}

function initTables() {
  initTransporteTable();
  initPreventaCard();
  initPreventaTable();
  initFlotaTable();
  initLogisticaTable();
  initLogistica1Table();
}

// ------------------------------
// AJAX varios
// ------------------------------
function initOrdenesCompra() {
  $.ajax({
    data: { OC: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    success: function (response) {
      let jsonData = {};
      try {
        jsonData = JSON.parse(response);
      } catch (e) {
        console.error("JSON parse error OC", e, response);
        return;
      }

      if (jsonData.success == "1") {
        $("#ordenes_de_compra").html(jsonData.Total);
        $("#ordenes_de_compra_estado").html(jsonData.Estado);
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    },
  });
}

// ------------------------------
// Modales
// ------------------------------
function bindDepositoModal() {
  $("#deposito-modal").on("show.bs.modal", function (e) {
    let triggerLink = $(e.relatedTarget);
    let id = triggerLink.data("id");

    $("#deposito-modal-body").text(
      "Estas por vaciar el Recorrido " +
        id +
        ". Se enviaran todos los servicios al recorrido Deposito (Recorrido 80)",
    );

    $("#deposito-modal-ok")
      .off("click")
      .on("click", function () {
        $.ajax({
          data: { VaciarRecorrido: 1, Recorrido: id },
          url: "../Inicio/php/funcionesCpanel.php",
          type: "post",
          success: function (response) {
            let jsonData = {};
            try {
              jsonData = JSON.parse(response);
            } catch (e) {
              console.error("JSON parse error VaciarRecorrido", e, response);
              return;
            }

            if (jsonData.success == "1") {
              $("#logistica1").DataTable().ajax.reload();
              $("#deposito-modal").modal("hide");

              if ($.NotificationApp && $.NotificationApp.send) {
                $.NotificationApp.send(
                  "Exito !",
                  "Se movieron los servicios a Deposito.",
                  "bottom-right",
                  "#FFFFFF",
                  "success",
                );
              }
            }
          },
          error: function (xhr) {
            if (redirectIf401(xhr)) return;
            console.error("AJAX error", xhr.status, xhr.responseText);
          },
        });
      });
  });
}

function bindPendientesMapaModal() {
  $("#pendientesmapa").on("show.bs.modal", function (e) {
    let triggerLink = $(e.relatedTarget);
    let id = triggerLink.data("id");
    let rec = triggerLink.data("rec");

    $("#tabla_pendientesmapa_title").text("Envios Pendientes Recorrido " + id);

    $("#tabla_pendientesmapa").DataTable({
      paging: false,
      searching: false,
      destroy: true,
      ajax: Object.assign(dtAjaxCommon(), {
        url: "../Inicio/php/tablasCpanel.php",
        data: { PendientesEnRecorrido: 1, Recorrido: id, Orden: rec },
      }),
      columns: [
        { data: "Fecha" },
        { data: "Cliente" },
        { data: "Localizacion" },
        { data: "Ciudad" },
        { data: "Seguimiento" },
        {
          data: "Seguimiento",
          orderable: false,
          render: function (data, type, row) {
            return `
              <a href="../Servicios/Seguimiento.php?codigoseguimiento_t=${row.Seguimiento}&Continuar=Buscar">
                <i class="mdi mdi-24px mdi-file-search-outline"></i>
              </a>
            `;
          },
        },
      ],
    });
  });
}

function bindPendientesRecorridoModal() {
  $("#bs-example-modal-lg").on("show.bs.modal", function (e) {
    let triggerLink = $(e.relatedTarget);
    let id = triggerLink.data("id");

    $("#myLargeModalLabel").text("Envíos Pendientes Recorrido " + id);
    $("#idRecorridoPendientes").html(id);

    $("#pendientes").DataTable({
      paging: false,
      searching: false,
      destroy: true,
      ajax: Object.assign(dtAjaxCommon(), {
        url: "../Inicio/php/tablasCpanel.php",
        data: { Pendientes: 1, id: id },
      }),
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            return row.Fecha ? row.Fecha.split("-").reverse().join(".") : "";
          },
        },
        {
          data: "Origen",
          render: function (data, type, row) {
            return `
              ${row.Origen || ""}<br>
              <small class="text-muted">${row.DomicilioOrigen || ""}</small>
            `;
          },
        },
        {
          data: "Destino",
          render: function (data, type, row) {
            return `
              ${row.Destino || ""}<br>
              <small class="text-muted">${row.DomicilioDestino || ""}</small>
            `;
          },
        },
        {
          data: "Notas",
          render: function (data, type, row) {
            return `
              <i class="text-info mdi mdi-18px mdi-pencil" onclick="notas(${row.id})"></i>
              <small class="text-info">${row.Notas || ""}</small>
            `;
          },
        },
        {
          data: "Seguimiento",
          render: function (data, type, row) {
            return `
              <a style="cursor:pointer"
                 data-bs-toggle="modal"
                 data-bs-target="#modal_seguimiento"
                 data-id="${row.Seguimiento}">
                <b>${row.Seguimiento}</b>
              </a>
            `;
          },
        },
        {
          data: "Seguimiento",
          orderable: false,
          render: function (data, type, row) {
            return `
              <a target="_blank" href="../Servicios/Informes/Remitopdf.php?CS=${row.Seguimiento}">
                <i class="mdi mdi-24px mdi-file-outline text-success"></i>
              </a>
            `;
          },
        },
        {
          data: "Seguimiento",
          orderable: false,
          render: function (data, type, row) {
            return `
              <a style="cursor:pointer"
                 data-bs-toggle="modal"
                 data-bs-target="#rotulos-modal"
                 data-id="${row.Seguimiento}">
                <i class="mdi mdi-24px mdi-printer"></i>
              </a>
            `;
          },
        },
      ],
    });
  });
}

function bindRemitosModal() {
  $("#remitos-modal").on("show.bs.modal", function (e) {
    let triggerLink = $(e.relatedTarget);
    let id = triggerLink.data("id");

    if (id != null && id !== "") {
      $("#body-remitos").html("Se imprimirá el Remitorótulo del Código " + id);
      $("#imp_rem").show();
      $("#imp_rem_rec").hide();

      $("#imp_rem")
        .off("click")
        .on("click", function () {
          alert("ok");
          $("#imp_rem_rec").hide();
        });
    } else {
      let rec = $("#idRecorridoPendientes").html();
      $("#body-remitos").html(
        "Se imprimiran todos los Remitos del recorrido " + rec,
      );
      $("#imp_rem").hide();
      $("#imp_rem_rec").show();
    }
  });

  $("#imp_rem_rec")
    .off("click")
    .on("click", function () {
      let rec = $("#idRecorridoPendientes").html();
      window.open(
        "http://www.caddy.com.ar/../Ventas/Informes/autoimpresion.php?Recorrido=" +
          rec,
        "_blank",
      );
    });
}

function bindNotasModal() {
  $("#notas-modal-ok")
    .off("click")
    .on("click", function () {
      let i = $("#notas_id").val();
      let notas = $("#notas_txt").val();

      $.ajax({
        data: { AgregarNotas: 1, id: i, notas: notas },
        type: "POST",
        url: "../Inicio/php/funcionesCpanel.php",
        success: function (response) {
          let jsonData = {};
          try {
            jsonData = JSON.parse(response);
          } catch (e) {
            console.error("JSON parse error AgregarNotas", e, response);
            return;
          }

          if (jsonData.success == 1) {
            $("#pendientes").DataTable().ajax.reload();
          }

          $("#notas-modal").modal("hide");
        },
        error: function (xhr) {
          if (redirectIf401(xhr)) return;
          console.error("AJAX error", xhr.status, xhr.responseText);
        },
      });
    });
}

function bindModals() {
  bindDepositoModal();
  bindPendientesMapaModal();
  bindPendientesRecorridoModal();
  bindRemitosModal();
  bindNotasModal();
}

// ------------------------------
// Helpers globales usados desde HTML
// ------------------------------
function print_pdf(url) {
  var id = "iframe";
  var html =
    '<iframe id="' + id + '" src="' + url + '" style="display:none"></iframe>';
  $("#main").append(html);
  $("#" + id).on("load", function () {
    document.getElementById(id).contentWindow.print();
  });
}

function notas(i) {
  $("#notas-modal").modal("show");
  $("#notas_id").val(i);

  $.ajax({
    data: { VerNotas: 1, id: i },
    type: "POST",
    url: "../Inicio/php/funcionesCpanel.php",
    success: function (response) {
      let jsonData = {};
      try {
        jsonData = JSON.parse(response);
      } catch (e) {
        console.error("JSON parse error VerNotas", e, response);
        return;
      }

      if (jsonData.success == 1) {
        $("#notas_txt").val(jsonData.notas);
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    },
  });
}

// ------------------------------
// Init general
// ------------------------------
$(document).ready(function () {
  initDashboard();
  initTables();
  initOrdenesCompra();
  bindModals();
});
