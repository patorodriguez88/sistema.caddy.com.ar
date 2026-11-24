// =====================================================================================================
// PROGRAMACIÓN DE PAGOS A PROVEEDORES (rediseño 24/11/2026, pedido de Patricio)
// - El calendario muestra TODAS las facturas impagas: en la fecha que eligió el operador (relleno) o,
//   si no la movió, en su vencimiento (solo contorno, "sugerida"). El importe es el saldo actual.
// - La lista de la izquierda son las impagas que quedaron en meses anteriores: hay que reubicarlas.
// - Sábado y domingo en gris (no se puede soltar una factura ahí); viernes en verde (día de pago).
// - Clic en un día: panel a la derecha con las facturas de ese día y el total.
// La lógica de fechas y saldos está en Procesos/php/pagos_programados_api.php.
// =====================================================================================================
let calendarioPagos = null;
let facturasPendientesOriginal = [];
$.fn.dataTable.ext.errMode = "none";

const API_PP = "Procesos/php/pagos_programados_api.php";

$(document).ready(function () {
  inicializarCalendarioPagos();
  inicializarArrastreLista();
  cargarFacturasPendientes();
  cargarResumenFechas();

  $("#btn_recargar_facturas").on("click", function () {
    cargarFacturasPendientes();
  });

  $("#btn_recargar_calendario").on("click", function () {
    refrescarTodo();
  });

  $("#buscar_factura").on("keyup", function () {
    filtrarFacturasPendientes($(this).val());
  });
});

function esFinDeSemana(fecha) {
  const d = typeof fecha === "string" ? new Date(fecha + "T00:00:00") : fecha;
  return d.getDay() === 0 || d.getDay() === 6;
}

function inicializarCalendarioPagos() {
  const calendarEl = document.getElementById("calendario_pagos");

  calendarioPagos = new FullCalendar.Calendar(calendarEl, {
    locale: "es",
    initialView: "dayGridMonth",
    firstDay: 1,
    weekends: false, // de lunes a viernes: sábado y domingo no se paga (24/11/2026)
    height: "auto",
    editable: true,
    droppable: true,
    eventStartEditable: true,
    eventDurationEditable: false,
    navLinks: false,
    nowIndicator: true,
    dayMaxEvents: 4, // "+N más" si hay muchas; el detalle completo está en el panel del día

    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,listMonth",
    },

    buttonText: {
      today: "Hoy",
      month: "Mes",
      list: "Lista",
    },

    events: {
      url: API_PP,
      method: "POST",
      extraParams: { accion: "listar_eventos" },
      failure: function () {
        alerta("error", "No se pudieron cargar los pagos.");
      },
    },

    // Viernes (día de pago habitual) en verde pastel.
    dayCellClassNames: function (arg) {
      if (arg.date.getDay() === 5) return ["dia-viernes"];
      return [];
    },

    // No se puede soltar una factura en sábado o domingo (ni desde la lista ni moviéndola).
    eventAllow: function (dropInfo) {
      return !esFinDeSemana(dropInfo.start);
    },

    // Desde la lista de la izquierda (facturas de meses anteriores).
    drop: function (info) {
      quitarModoArrastre();
      const id = info.draggedEl.getAttribute("data-id");
      if (!id) return;
      if (esFinDeSemana(info.dateStr)) {
        alerta("warning", "Sábados y domingos no se paga: elegí un día hábil.");
        return;
      }
      ubicarFactura(id, info.dateStr);
    },

    // Moviéndola dentro del calendario.
    eventDrop: function (info) {
      const fecha = info.event.startStr.substring(0, 10);
      if (esFinDeSemana(fecha)) {
        info.revert();
        return;
      }
      ubicarFactura(info.event.extendedProps.idTransProveedores, fecha, info);
    },

    eventClick: function (info) {
      info.jsEvent.preventDefault();
      mostrarDetalleFactura(info.event);
    },

    // Clic en el día: panel lateral con todo lo de ese día.
    dateClick: function (info) {
      abrirPanelDia(info.dateStr);
    },

    datesSet: function () {
      cargarCards();
      cargarResumenFechas();
    },
  });

  calendarioPagos.render();
}

// ------------------------------------------------------------------------------------------------
// Lista de la izquierda: impagas de meses anteriores para reubicar
// ------------------------------------------------------------------------------------------------
function cargarFacturasPendientes() {
  $("#lista_facturas_pendientes").html(`
    <div class="text-center text-muted p-3">
      <div class="spinner-border spinner-border-sm me-1"></div>
      Cargando facturas...
    </div>`);

  $.ajax({
    url: API_PP,
    type: "POST",
    dataType: "json",
    data: { accion: "listar_facturas_pendientes" },
    success: function (response) {
      if (!response.success) {
        alerta("error", response.message || "No se pudieron cargar las facturas.");
        return;
      }
      facturasPendientesOriginal = response.data || [];
      filtrarFacturasPendientes($("#buscar_factura").val());
      cargarCards();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alerta("error", "Error de conexión al cargar las facturas.");
    },
  });
}

function renderFacturasPendientes(data) {
  if (!data.length) {
    $("#lista_facturas_pendientes").html(`
      <div class="alert alert-success mb-0">
        <i class="mdi mdi-check-circle-outline me-1"></i>
        No quedaron facturas impagas de meses anteriores.
      </div>`);
    return;
  }

  let html = "";
  data.forEach(function (row) {
    const saldo = parseFloat(row.Saldo || row.SaldoPendiente || 0);
    const origen = Number(row.Programada) === 1 ? "programada para" : Number(row.VencimientoEstimado) === 1 ? "vencía (estimado)" : "vencía";
    html += `
    <div class="card mb-2 shadow-none border factura-draggable" data-id="${row.id}" data-saldo="${saldo}">
      <!-- lo que se ve mientras se arrastra: solo proveedor e importe -->
      <div class="factura-chip">${escapeHtml(row.RazonSocial)} · ${formatoMoneda(saldo)}</div>
      <div class="card-body p-2 factura-contenido">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="mt-0 mb-1 font-14">${escapeHtml(row.RazonSocial)}</h5>
            <div class="text-muted factura-small">
              ${escapeHtml(row.TipoDeComprobante || "")} ${escapeHtml(row.NumeroComprobante || "")}
            </div>
          </div>
          <div class="text-end">
            <span class="badge bg-warning text-dark">${formatoMoneda(saldo)}</span>
          </div>
        </div>
        <div class="mt-2 factura-small">
          <div><b>Fecha:</b> ${formatoFecha(row.Fecha)}</div>
          <div class="text-danger fw-bold">${origen} el ${formatoFecha(row.FechaCalendario)}</div>
          ${parseFloat(row.Pagado || 0) > 0 ? `<div><b>Total:</b> ${formatoMoneda(row.Debe)} <span class="text-success ms-1">Pagado: ${formatoMoneda(row.Pagado)}</span></div>` : ""}
          <div class="text-muted">${escapeHtml(row.Descripcion || row.Concepto || "")}</div>
        </div>
      </div>
    </div>`;
  });

  $("#lista_facturas_pendientes").html(html);
}

// Un solo Draggable para toda la lista (sirve aunque se vuelva a dibujar). Al apretar una tarjeta se
// achica a un "chip" (proveedor + importe) para que lo que se arrastra sea chico y se vea en qué día cae.
function inicializarArrastreLista() {
  const cont = document.getElementById("lista_facturas_pendientes");
  new FullCalendar.Draggable(cont, {
    itemSelector: ".factura-draggable",
    eventData: function (el) {
      return { title: el.querySelector(".factura-chip").textContent, create: false };
    },
  });
  $(cont).on("mousedown touchstart", ".factura-draggable", function () {
    $(this).addClass("arrastrando");
  });
  $(document).on("mouseup touchend touchcancel", quitarModoArrastre);
}
function quitarModoArrastre() {
  $(".factura-draggable.arrastrando").removeClass("arrastrando");
}

function filtrarFacturasPendientes(texto) {
  texto = normalizarTexto(texto);
  if (!texto) {
    renderFacturasPendientes(facturasPendientesOriginal);
    return;
  }
  const filtradas = facturasPendientesOriginal.filter(function (row) {
    const cadena = normalizarTexto(`${row.RazonSocial || ""} ${row.NumeroComprobante || ""} ${row.Descripcion || ""} ${row.Concepto || ""}`);
    return cadena.includes(texto);
  });
  renderFacturasPendientes(filtradas);
}

// ------------------------------------------------------------------------------------------------
// Ubicar / quitar
// ------------------------------------------------------------------------------------------------
function ubicarFactura(idTransProveedores, fecha, infoDrop) {
  $.ajax({
    url: API_PP,
    type: "POST",
    dataType: "json",
    data: { accion: "ubicar_factura", idTransProveedores: idTransProveedores, fecha: fecha },
    success: function (response) {
      if (!response.success) {
        if (infoDrop) infoDrop.revert();
        alerta("error", response.message || "No se pudo ubicar la factura.");
        return;
      }
      toast("success", response.message || "Pago ubicado.");
      refrescarTodo();
    },
    error: function (xhr) {
      if (infoDrop) infoDrop.revert();
      console.error(xhr.responseText);
      alerta("error", "Error al ubicar la factura.");
    },
  });
}

function quitarProgramacion(idProgramacion) {
  $.ajax({
    url: API_PP,
    type: "POST",
    dataType: "json",
    data: { accion: "eliminar_programacion", idProgramacion: idProgramacion },
    success: function (response) {
      if (!response.success) {
        alerta("error", response.message || "No se pudo quitar la fecha.");
        return;
      }
      toast("success", response.message || "La factura volvió a su vencimiento.");
      refrescarTodo();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alerta("error", "Error al quitar la fecha.");
    },
  });
}

function mostrarDetalleFactura(event) {
  const p = event.extendedProps || {};
  const programada = p.estado === "PROGRAMADO";
  Swal.fire({
    title: p.RazonSocial || event.title,
    html: `
      <div class="text-start">
        <p class="mb-1"><b>Comprobante:</b> ${escapeHtml(p.TipoDeComprobante || "")} ${escapeHtml(p.NumeroComprobante || "")}</p>
        <p class="mb-1"><b>Fecha del comprobante:</b> ${formatoFecha(p.Fecha)}</p>
        <p class="mb-1"><b>Vencimiento:</b> ${formatoFecha(p.Vencimiento)}${Number(p.VencimientoEstimado) === 1 ? " <small class='text-muted'>(estimado con los días de pago)</small>" : ""}</p>
        <p class="mb-1"><b>En el calendario:</b> ${formatoFecha(event.startStr.substring(0, 10))}
          <span class="badge ${programada ? "bg-info" : "bg-light text-dark border"}">${programada ? "elegida por el operador" : "por vencimiento"}</span></p>
        <p class="mb-1"><b>Total:</b> ${formatoMoneda(p.Debe)} · <b>Pagado:</b> ${formatoMoneda(p.Pagado)} · <b>Saldo:</b> ${formatoMoneda(p.importe_programado)}</p>
        ${p.Descripcion ? `<p class="mb-1 text-muted">${escapeHtml(p.Descripcion)}</p>` : ""}
        <hr class="my-2"><small class="text-muted">Para moverla, arrastrala a otro día. Cuando asocies el pago en
        Proveedores (Asociar pagos) desaparece sola del calendario.</small>
      </div>`,
    icon: "info",
    confirmButtonText: "Cerrar",
    showDenyButton: programada && !!p.idProgramacion,
    denyButtonText: "Volver al vencimiento",
    denyButtonColor: "#fa5c7c",
  }).then(function (r) {
    if (r.isDenied) quitarProgramacion(p.idProgramacion);
  });
}

// ------------------------------------------------------------------------------------------------
// Panel del día (offcanvas a la derecha)
// ------------------------------------------------------------------------------------------------
function abrirPanelDia(fecha) {
  const eventos = calendarioPagos
    .getEvents()
    .filter((e) => e.startStr.substring(0, 10) === fecha)
    .sort((a, b) => String(a.extendedProps.RazonSocial || "").localeCompare(String(b.extendedProps.RazonSocial || "")));

  const d = new Date(fecha + "T00:00:00");
  const titulo = d.toLocaleDateString("es-AR", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
  $("#panel_dia_titulo").text(titulo.charAt(0).toUpperCase() + titulo.slice(1));

  let total = 0;
  let html = "";
  if (esFinDeSemana(fecha)) {
    html += `<div class="alert alert-secondary py-2 small">Sábado o domingo: no se paga.</div>`;
  }
  if (!eventos.length) {
    html += `<div class="text-muted">No hay facturas para pagar este día.</div>`;
  } else {
    html += `<div class="list-group list-group-flush">`;
    eventos.forEach(function (e) {
      const p = e.extendedProps;
      total += parseFloat(p.importe_programado || 0);
      html += `
        <a href="#" class="list-group-item list-group-item-action px-0 panel-dia-item" data-id="${e.id}">
          <div class="d-flex justify-content-between">
            <b class="me-2">${escapeHtml(p.RazonSocial)}</b>
            <span class="text-nowrap">${formatoMoneda(p.importe_programado)}</span>
          </div>
          <div class="small text-muted">${escapeHtml(p.TipoDeComprobante || "")} ${escapeHtml(p.NumeroComprobante || "")}</div>
          <div class="small">
            ${p.estado === "PROGRAMADO" ? '<span class="badge bg-info">Elegida</span>' : '<span class="badge bg-light text-dark border">Por vencimiento</span>'}
            ${Number(p.vencida) === 1 ? '<span class="badge bg-danger ms-1">Vencida</span>' : ""}
          </div>
        </a>`;
    });
    html += `</div>`;
  }
  $("#panel_dia_lista").html(html);
  $("#panel_dia_total").text(formatoMoneda(total));
  $("#panel_dia_cantidad").text(eventos.length === 1 ? "1 factura" : `${eventos.length} facturas`);

  bootstrap.Offcanvas.getOrCreateInstance(document.getElementById("panel_dia")).show();
}

$(document).on("click", ".panel-dia-item", function (e) {
  e.preventDefault();
  const ev = calendarioPagos.getEventById($(this).data("id"));
  if (ev) mostrarDetalleFactura(ev);
});

// ------------------------------------------------------------------------------------------------
// Tarjetas y resumen
// ------------------------------------------------------------------------------------------------
function cargarCards() {
  $.ajax({
    url: API_PP,
    type: "POST",
    dataType: "json",
    data: { accion: "cards" },
    success: function (response) {
      if (!response.success) return;
      $("#total_sin_programar").text(formatoMoneda(response.data.total_sin_programar || 0));
      $("#cantidad_sin_programar").text(response.data.cantidad_sin_programar || 0);
      $("#total_programado_mes").text(formatoMoneda(response.data.total_programado_mes || 0));
      $("#total_semana").text(formatoMoneda(response.data.total_semana || 0));
      $("#total_vencido").text(formatoMoneda(response.data.total_vencido || 0));
    },
  });
}

function cargarResumenFechas() {
  if ($.fn.DataTable.isDataTable("#tabla_resumen_fechas")) {
    $("#tabla_resumen_fechas").DataTable().ajax.reload(null, false);
    return;
  }

  $("#tabla_resumen_fechas").DataTable({
    paging: false,
    searching: false,
    info: false,
    ordering: true,
    ajax: {
      url: API_PP,
      type: "POST",
      data: { accion: "resumen_fechas" },
      dataSrc: function (json) {
        if (!json || json.success !== true) return [];
        return json.data || [];
      },
    },
    columns: [
      {
        data: "fecha_promesa",
        render: function (data, type) {
          if (!data) return "";
          return type === "display" || type === "filter" ? formatoFecha(data) : data;
        },
      },
      { data: "cantidad" },
      { data: "total", className: "text-end", render: (data) => formatoMoneda(data) },
      {
        data: "estado",
        render: function (data) {
          if (data === "VENCIDO") return `<span class="badge bg-danger">Vencido</span>`;
          if (data === "HOY") return `<span class="badge bg-warning text-dark">Hoy</span>`;
          return `<span class="badge bg-info">A pagar</span>`;
        },
      },
    ],
    footerCallback: function (row, data) {
      let total = 0;
      data.forEach((item) => (total += parseFloat(item.total || 0)));
      $(this.api().column(2).footer()).html(formatoMoneda(total));
    },
  });
}

function refrescarTodo() {
  if (calendarioPagos) calendarioPagos.refetchEvents();
  cargarFacturasPendientes();
  cargarCards();
  cargarResumenFechas();
}

// Compatibilidad: otras partes llamaban a refrescarCalendario().
function refrescarCalendario() {
  refrescarTodo();
}

// ------------------------------------------------------------------------------------------------
// Utilidades
// ------------------------------------------------------------------------------------------------
function formatoMoneda(valor) {
  valor = parseFloat(valor || 0);
  return valor.toLocaleString("es-AR", { style: "currency", currency: "ARS", minimumFractionDigits: 2 });
}

function formatoFecha(fecha) {
  if (!fecha) return "";
  const partes = String(fecha).substring(0, 10).split("-");
  if (partes.length !== 3) return fecha;
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function normalizarTexto(texto) {
  return String(texto || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[̀-ͯ]/g, "");
}

function escapeHtml(texto) {
  return String(texto || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
