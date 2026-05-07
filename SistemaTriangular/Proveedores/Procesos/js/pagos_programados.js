let calendarioPagos = null;
let facturasPendientesOriginal = [];
$.fn.dataTable.ext.errMode = "none";

$(document).ready(function () {
  inicializarCalendarioPagos();
  cargarFacturasPendientes();
  cargarResumenFechas();

  $("#btn_recargar_facturas").on("click", function () {
    cargarFacturasPendientes();
  });

  $("#btn_recargar_calendario").on("click", function () {
    refrescarCalendario();
    cargarResumenFechas();
  });

  $("#buscar_factura").on("keyup", function () {
    filtrarFacturasPendientes($(this).val());
  });
});

function inicializarCalendarioPagos() {
  const calendarEl = document.getElementById("calendario_pagos");

  calendarioPagos = new FullCalendar.Calendar(calendarEl, {
    locale: "es",
    initialView: "dayGridMonth",
    height: "auto",
    editable: true,
    droppable: true,
    eventStartEditable: true,
    eventDurationEditable: false,
    navLinks: true,
    nowIndicator: true,

    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,listMonth",
    },

    buttonText: {
      today: "Hoy",
      month: "Mes",
      week: "Semana",
      list: "Lista",
    },

    events: {
      url: "Procesos/php/pagos_programados_api.php",
      method: "POST",
      extraParams: {
        accion: "listar_eventos",
      },
      failure: function () {
        alerta("error", "No se pudieron cargar los pagos programados.");
      },
    },

    drop: function (info) {
      const el = info.draggedEl;
      const idTransProveedores = el.getAttribute("data-id");
      const saldoPendiente = parseFloat(el.getAttribute("data-saldo") || 0);

      if (!idTransProveedores || saldoPendiente <= 0) {
        alerta(
          "error",
          "La factura seleccionada no tiene saldo pendiente válido.",
        );
        return;
      }

      programarPago(idTransProveedores, info.dateStr, saldoPendiente);
    },

    eventDrop: function (info) {
      const idProgramacion = info.event.id;
      const nuevaFecha = info.event.startStr.substring(0, 10);

      reprogramarPago(idProgramacion, nuevaFecha, info);
    },

    eventClick: function (info) {
      mostrarDetalleProgramacion(info.event);
    },

    datesSet: function () {
      cargarCards();
      cargarResumenFechas();
    },
  });

  calendarioPagos.render();
}

function cargarFacturasPendientes() {
  $("#lista_facturas_pendientes").html(`
    <div class="text-center text-muted p-3">
    <div class="spinner-border spinner-border-sm me-1"></div>
    Cargando facturas...
    </div>
    `);

  $.ajax({
    url: "Procesos/php/pagos_programados_api.php",
    type: "POST",
    dataType: "json",
    data: {
      accion: "listar_facturas_pendientes",
    },
    success: function (response) {
      if (!response.success) {
        alerta(
          "error",
          response.message || "No se pudieron cargar las facturas.",
        );
        return;
      }

      facturasPendientesOriginal = response.data || [];
      renderFacturasPendientes(facturasPendientesOriginal);
      cargarCards();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      alerta("error", "Error de conexión al cargar facturas pendientes.");
    },
  });
}

function renderFacturasPendientes(data) {
  if (!data.length) {
    $("#lista_facturas_pendientes").html(`
    <div class="alert alert-success mb-0">
        <i class="mdi mdi-check-circle-outline me-1"></i>
        No hay facturas pendientes sin programar.
    </div>
    `);
    return;
  }

  let html = "";

  data.forEach(function (row) {
    const saldo = parseFloat(row.SaldoPendiente || 0);
    const debe = parseFloat(row.Debe || 0);
    const pagado = parseFloat(row.Pagado || 0);

    html += `
    <div class="card mb-2 shadow-none border factura-draggable"
        data-id="${row.id}"
        data-title="${escapeHtml(row.RazonSocial)}"
        data-saldo="${saldo}"
        data-event='${JSON.stringify({
          title: row.RazonSocial.substring(0, 10) + "...",
          duration: "01:00",
        })}'>

        <div class="card-body p-2">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mt-0 mb-1 font-14">
                        ${escapeHtml(row.RazonSocial)}
                    </h5>
                    <div class="text-muted factura-small">
                        ${escapeHtml(row.TipoDeComprobante || "")}
                        ${escapeHtml(row.NumeroComprobante || "")}
                    </div>
                </div>

                <div class="text-end">
                    <span class="badge bg-warning text-dark">
                        ${formatoMoneda(saldo)}
                    </span>
                </div>
            </div>

            <div class="mt-2 factura-small">
                <div>
                    <b>Fecha:</b> ${formatoFecha(row.Fecha)}
                </div>
                <div>
                    <b>Total:</b> ${formatoMoneda(debe)}
                    <span class="text-success ms-1">
                        Pagado: ${formatoMoneda(pagado)}
                    </span>
                </div>
                <div class="text-muted">
                    ${escapeHtml(row.Descripcion || row.Concepto || "")}
                </div>
            </div>
        </div>
    </div>
    `;
  });

  $("#lista_facturas_pendientes").html(html);

  document.querySelectorAll(".factura-draggable").forEach(function (el) {
    new FullCalendar.Draggable(el, {
      eventData: function (eventEl) {
        return JSON.parse(eventEl.getAttribute("data-event"));
      },
    });
  });
}

function filtrarFacturasPendientes(texto) {
  texto = normalizarTexto(texto);

  if (!texto) {
    renderFacturasPendientes(facturasPendientesOriginal);
    return;
  }

  const filtradas = facturasPendientesOriginal.filter(function (row) {
    const cadena = normalizarTexto(
      `${row.RazonSocial || ""} ${row.NumeroComprobante || ""} ${row.Descripcion || ""} ${row.Concepto || ""}`,
    );

    return cadena.includes(texto);
  });

  renderFacturasPendientes(filtradas);
}

function programarPago(idTransProveedores, fechaPromesa, importeProgramado) {
  Swal.fire({
    title: "Programar pago",
    html: `
    <div class="text-start">
        <label class="form-label">Fecha de promesa</label>
        <input type="date" id="swal_fecha_promesa" class="form-control" value="${fechaPromesa}">

        <label class="form-label mt-2">Importe programado</label>
        <input type="number" id="swal_importe_programado" class="form-control" step="0.01" value="${importeProgramado.toFixed(2)}">

        <label class="form-label mt-2">Observación</label>
        <textarea id="swal_observacion" class="form-control" rows="2"></textarea>
    </div>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Programar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#0acf97",
    cancelButtonColor: "#fa5c7c",
    preConfirm: function () {
      const fecha = $("#swal_fecha_promesa").val();
      const importe = parseFloat($("#swal_importe_programado").val() || 0);
      const observacion = $("#swal_observacion").val();

      if (!fecha) {
        Swal.showValidationMessage("Debe indicar una fecha.");
        return false;
      }

      if (importe <= 0) {
        Swal.showValidationMessage("El importe debe ser mayor a cero.");
        return false;
      }

      return {
        fecha_promesa: fecha,
        importe_programado: importe,
        observacion: observacion,
      };
    },
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: "Procesos/php/pagos_programados_api.php",
      type: "POST",
      dataType: "json",
      data: {
        accion: "programar_pago",
        idTransProveedores: idTransProveedores,
        fecha_promesa: result.value.fecha_promesa,
        importe_programado: result.value.importe_programado,
        observacion: result.value.observacion,
      },
      success: function (response) {
        if (!response.success) {
          alerta("error", response.message || "No se pudo programar el pago.");
          refrescarCalendario();
          return;
        }

        toast("success", "Pago programado correctamente.");
        cargarFacturasPendientes();
        refrescarCalendario();
        cargarResumenFechas();
      },
      error: function (xhr) {
        console.error(xhr.responseText);
        alerta("error", "Error al programar el pago.");
        refrescarCalendario();
      },
    });
  });
}

function reprogramarPago(idProgramacion, nuevaFecha, info) {
  $.ajax({
    url: "Procesos/php/pagos_programados_api.php",
    type: "POST",
    dataType: "json",
    data: {
      accion: "reprogramar_pago",
      idProgramacion: idProgramacion,
      fecha_promesa: nuevaFecha,
    },
    success: function (response) {
      if (!response.success) {
        info.revert();
        alerta("error", response.message || "No se pudo reprogramar el pago.");
        return;
      }

      toast("success", "Pago reprogramado correctamente.");
      cargarCards();
      cargarResumenFechas();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      info.revert();
      alerta("error", "Error al reprogramar el pago.");
    },
  });
}

function mostrarDetalleProgramacion(event) {
  const props = event.extendedProps || {};

  Swal.fire({
    title: props.RazonSocial || event.title,
    html: `
        <div class="text-start">
        <p><b>Fecha programada:</b> ${formatoFecha(event.startStr.substring(0, 10))}</p>
        <p><b>Comprobante:</b> ${escapeHtml(props.TipoDeComprobante || "")} ${escapeHtml(props.NumeroComprobante || "")}</p>
        <p><b>Importe programado:</b> ${formatoMoneda(props.importe_programado || 0)}</p>
        <p><b>Estado:</b> ${escapeHtml(props.estado || "")}</p>
        <p><b>Observación:</b> ${escapeHtml(props.observacion || "-")}</p>
        </div>
        `,
    icon: "info",
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: "Cerrar",
    denyButtonText: "Quitar programación",
    cancelButtonText: "Marcar pagado",
    confirmButtonColor: "#727cf5",
    denyButtonColor: "#fa5c7c",
    cancelButtonColor: "#0acf97",
  }).then(function (result) {
    if (result.isDenied) {
      eliminarProgramacion(event.id);
    }

    if (result.dismiss === Swal.DismissReason.cancel) {
      marcarProgramacionPagada(event.id);
    }
  });
}

function eliminarProgramacion(idProgramacion) {
  Swal.fire({
    title: "¿Quitar programación?",
    text: "La factura volverá a quedar disponible para programar.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, quitar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#fa5c7c",
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: "Procesos/php/pagos_programados_api.php",
      type: "POST",
      dataType: "json",
      data: {
        accion: "eliminar_programacion",
        idProgramacion: idProgramacion,
      },
      success: function (response) {
        if (!response.success) {
          alerta(
            "error",
            response.message || "No se pudo quitar la programación.",
          );
          return;
        }

        toast("success", "Programación eliminada.");
        cargarFacturasPendientes();
        refrescarCalendario();
        cargarResumenFechas();
      },
      error: function (xhr) {
        console.error(xhr.responseText);
        alerta("error", "Error al quitar la programación.");
      },
    });
  });
}

function marcarProgramacionPagada(idProgramacion) {
  Swal.fire({
    title: "¿Marcar como pagado?",
    text: "Esto solo cambia el estado de la programación. No registra un pago contable.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Marcar pagado",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#0acf97",
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: "Procesos/php/pagos_programados_api.php",
      type: "POST",
      dataType: "json",
      data: {
        accion: "marcar_pagado",
        idProgramacion: idProgramacion,
      },
      success: function (response) {
        if (!response.success) {
          alerta(
            "error",
            response.message || "No se pudo actualizar el estado.",
          );
          return;
        }

        toast("success", "Programación marcada como pagada.");
        refrescarCalendario();
        cargarResumenFechas();
        cargarCards();
      },
      error: function (xhr) {
        console.error(xhr.responseText);
        alerta("error", "Error al marcar como pagado.");
      },
    });
  });
}

function cargarCards() {
  $.ajax({
    url: "Procesos/php/pagos_programados_api.php",
    type: "POST",
    dataType: "json",
    data: {
      accion: "cards",
    },
    success: function (response) {
      if (!response.success) return;

      $("#total_sin_programar").text(
        formatoMoneda(response.data.total_sin_programar || 0),
      );
      $("#cantidad_sin_programar").text(
        response.data.cantidad_sin_programar || 0,
      );
      $("#total_programado_mes").text(
        formatoMoneda(response.data.total_programado_mes || 0),
      );
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
      url: "Procesos/php/pagos_programados_api.php",
      type: "POST",
      data: {
        accion: "resumen_fechas",
      },
      dataSrc: function (json) {
        console.log("Resumen fechas:", json);

        if (!json || json.success !== true) {
          console.error("Respuesta inválida resumen_fechas:", json);
          return [];
        }

        return json.data || [];
      },
    },
    columns: [
      {
        data: "fecha_promesa",
        render: function (data) {
          if (!data) return "";
          return `<span style="display:none;">${data}</span>${formatoFecha(data)}`;
        },
      },
      { data: "cantidad" },
      {
        data: "total",
        className: "text-end",
        render: function (data) {
          return formatoMoneda(data);
        },
      },
      {
        data: "estado",
        render: function (data) {
          if (data === "VENCIDO") {
            return `<span class="badge bg-danger">Vencido</span>`;
          }

          if (data === "HOY") {
            return `<span class="badge bg-warning text-dark">Hoy</span>`;
          }

          return `<span class="badge bg-info">Programado</span>`;
        },
      },
    ],
    footerCallback: function (row, data) {
      let total = 0;

      data.forEach(function (item) {
        total += parseFloat(item.total || 0);
      });

      $(this.api().column(2).footer()).html(formatoMoneda(total));
    },
  });
}

function refrescarCalendario() {
  if (calendarioPagos) {
    calendarioPagos.refetchEvents();
  }

  cargarCards();
}

function formatoMoneda(valor) {
  valor = parseFloat(valor || 0);

  return valor.toLocaleString("es-AR", {
    style: "currency",
    currency: "ARS",
    minimumFractionDigits: 2,
  });
}

function formatoFecha(fecha) {
  if (!fecha) return "";

  const partes = fecha.substring(0, 10).split("-");
  if (partes.length !== 3) return fecha;

  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function normalizarTexto(texto) {
  return String(texto || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}

function escapeHtml(texto) {
  return String(texto || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
