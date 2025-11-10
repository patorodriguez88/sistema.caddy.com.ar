// mapa: orden -> Set de codigos excluidos
const ExcluirPorOrden = new Map();

// helper para obtener el Set de una orden
function getExSet(orden) {
  if (!ExcluirPorOrden.has(orden)) ExcluirPorOrden.set(orden, new Set());
  return ExcluirPorOrden.get(orden);
}
//HANDLER QUE MODIFICA EN TIEMPO REAL LA CANTIDAD DE SERVICIOS DE LA TABLA PRINCIPAL
$(document).on("click", ".svc-exclude", function () {
  const orden = String($(this).data("orden") || "");
  const codigo = String($(this).data("codigo") || "");
  if (!orden || !codigo) return;

  const exSet = getExSet(orden);
  const $tr = $(this).closest("tr");

  if (exSet.has(codigo)) {
    exSet.delete(codigo);
    $tr.removeClass("tr-excluded");
    $(this).attr("title", "Descartar / No enviar");
  } else {
    exSet.add(codigo);
    $tr.addClass("tr-excluded");
    $(this).attr("title", "Incluir nuevamente");
  }

  // 🔥 Actualizar cantidad en la tabla general
  const total = $("#serviciosTBody tr").length;
  const descartados = exSet.size;
  const final = total - descartados;

  const $link = $(`#colectas_tabla .ver-servicios[data-orden="${orden}"]`);
  if ($link.length) {
    // actualizar texto
    $link.text(final.toLocaleString("es-AR"));

    // actualizar data-cantidad del icono enviar
    const $sendIcon = $link.closest("tr").find(".enviar-api");
    $sendIcon.data("cantidad", final);

    // marcar en rojo si difiere de la cantidad original
    const original = parseInt(
      $sendIcon.attr("data-cantidad-original") || total,
      10
    );
    if (final !== original) {
      $link.addClass("text-danger fw-bold");
    } else {
      $link.removeClass("text-danger fw-bold");
    }
  }
});
// === helpers de UI ===
const ToastOK = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3500,
  timerProgressBar: true,
});

function pretty(obj) {
  try {
    return JSON.stringify(obj, null, 2);
  } catch {
    return String(obj);
  }
}

// formatea 'YYYY-MM-DD HH:MM:SS' -> 'DD/MM/YYYY HH:MM'
function fmtTime(ts) {
  if (!ts) return "";
  try {
    const parts = String(ts).trim().split(" ");
    const date = parts[0] || "";
    const time = parts[1] || "";
    const [Y, M, D] = date.split("-");
    const hm = time ? time.substring(0, 5) : "";
    if (Y && M && D) return `${D}/${M}/${Y}${hm ? " " + hm : ""}`;
    return ts;
  } catch (e) {
    return ts;
  }
}

function showSuccess(json, orden) {
  const noRef = json.no_referencia || "—";
  const oib = json.nro_orden_ingreso_bulto || "—";
  const cnt =
    json.transclientes_actualizados ??
    (json.sent?.detalle_orden_ingreso_bulto?.length || "—");

  ToastOK.fire({
    icon: "success",
    title: `Orden ${orden} enviada`,
    html: `<div style="font-size:.9rem;line-height:1.2em">
      <div><b>OIB:</b> ${oib}</div>
      <div><b>Ref.:</b> ${noRef}</div>
      <div><b>Bultos:</b> ${cnt}</div>
    </div>`,
  });
}

function showNotFound404(json, orden) {
  const msg =
    (json && (json.message || json.msg)) ||
    "No se encontraron renglones para enviar.";
  Swal.fire({
    icon: "info",
    title: "Nada para enviar",
    html: `
      <p style="margin:0 0 .5rem">La orden <b>${orden}</b> no tiene renglones en <i>HojaDeRuta</i> con estado <b>Abierto</b> y <b>Eliminado=0</b>.</p>
      <details style="text-align:left;background:#fafafa;border:1px solid #eee;border-radius:8px;padding:.5rem">
        <summary style="cursor:pointer">Ver detalle técnico</summary>
        <pre style="white-space:pre-wrap;margin:.5rem 0 0;font-size:.8rem">${pretty(
          json
        )}</pre>
      </details>
    `,
    confirmButtonText: "Entendido",
  });
}

function showAjaxError(http, body, orden) {
  // intentamos extraer mensaje legible
  let legible = "";
  try {
    const j = typeof body === "object" ? body : JSON.parse(body);
    legible = j.message || j.msg || "";
    body = j;
  } catch (e) {
    /* dejamos body como vino */
  }
  Swal.fire({
    icon: "error",
    title: "Error al enviar",
    html: `
      <p style="margin:0 0 .5rem">Orden <b>${orden}</b></p>
      <p style="margin:.25rem 0"><b>HTTP:</b> ${http}</p>
      ${legible ? `<p style="margin:.25rem 0">${legible}</p>` : ""}
      <details style="text-align:left;background:#fff8f8;border:1px solid #f5cccc;border-radius:8px;padding:.5rem">
        <summary style="cursor:pointer">Ver respuesta completa</summary>
        <pre style="white-space:pre-wrap;margin:.5rem 0 0;font-size:.8rem">${pretty(
          body
        )}</pre>
      </details>
    `,
    confirmButtonText: "Cerrar",
  });
}

$(function () {
  var datatable = $("#colectas_tabla").DataTable({
    dom: "Bfrtip",
    buttons: ["copy", "csv", "excel", "pdf", "print"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    processing: true, // 👈 va a nivel raíz, no dentro de ajax
    ajax: {
      url: "../Logistica/Proceso/php/wepoint.php",
      type: "POST",
      data: { Colectas: 1 },
      dataSrc: "data", // 👈 esperamos { data: [...] }
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          // preferencia tuya: dd/mm/YYYY y ocultar valor original
          if (type === "display" || type === "filter") {
            var f = (data || "").split("-").reverse().join("/");
            return (
              '<span style="display: none;">' + (data || "") + "</span>" + f
            );
          }
          return data;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var color = Number(row.Retirado) === 0 ? "success" : "muted";
          return (
            "<b>" +
            (row.RazonSocial || "") +
            " (" +
            (row.CodigoProveedor || "") +
            ")</b><br/>" +
            '<i class="mdi mdi-18px mdi-map-marker text-' +
            color +
            '"></i><span class="text-muted">' +
            (row.DomicilioOrigen || "") +
            "</span>"
          );
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          var code = row.CodigoSeguimiento || "";
          var html =
            '<a style="cursor:pointer" data-id="' +
            code +
            '" id="' +
            code +
            '" onclick="modificarrecorrido(this.id);">' +
            '<b class="text-primary">' +
            (data || "") +
            "</b></a>";
          if (Number(row.Redespacho) === 1) {
            // Si preferís el estilo B5 antiguo: 'badge badge-warning text-white'
            html +=
              '<br/><span class="badge bg-warning text-white">' +
              '<i class="mdi mdi-alpha-r-box"></i> Redespacho</span>';
          }
          return html;
        },
      },
      { data: "NumerodeOrden" },
      {
        data: "Cantidad",
        className: "text-end",
        render: function (data, type, row) {
          const n = parseInt(data || 0, 10);
          const mostrado =
            type === "display" || type === "filter"
              ? n.toLocaleString("es-AR")
              : n;
          const orden = row.NumerodeOrden || "";
          // link clickeable que abre el modal
          return `<a href="#" class="link-primary ver-servicios" 
               data-orden="${orden}" 
               title="Ver servicios de la orden ${orden}">
              ${mostrado}
            </a>`;
        },
      },

      {
        // Columna de acción: botón para enviar a WePoint
        data: null,
        orderable: false,
        className: "text-center",
        render: function (data, type, row) {
          const numOrden = row.NumerodeOrden || "";
          const Cantidad = row.Cantidad || 0;
          return `
      <i class="mdi mdi-cloud-upload mdi-18px text-primary enviar-api"
         style="cursor:pointer"
         title="Enviar a WePoint"
         data-orden="${numOrden}"
         data-cantidad="${Cantidad}"></i>`;
        },
      },
    ],
  });
});
// Bootstrap 5 modal helper
function showServiciosModal(orden, items) {
  $("#serviciosOrden").text(orden);
  $("#serviciosVacio").hide();
  const $wrap = $("#serviciosLista");
  $wrap.empty();

  if (!items || !Array.isArray(items) || items.length === 0) {
    $("#serviciosVacio").show();
    return;
  }

  const tableHtml = `
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th style="width:6%">N°</th>
            <th style="width:32%">Origen</th>
            <th style="width:32%">Destino</th>
            <th style="width:20%">Código</th>
           <th style="width:20%">Bultos</th> 
            <th style="width:10%" class="text-center">Acción</th>
          </tr>
        </thead>
        <tbody id="serviciosTBody"></tbody>
      </table>
    </div>`;
  $wrap.append(tableHtml);

  const exSet = getExSet(String(orden));
  const $tb = $("#serviciosTBody");

  items.forEach((it, idx) => {
    const n = idx + 1;
    const origen = it.Origen || "";
    const destino = it.Destino || "";
    const codigo = it.CodigoSeguimiento || "";
    const cantidad = it.Cantidad || 0;
    const isExcluded = exSet.has(codigo);
    const trClass = isExcluded ? "tr-excluded" : "";

    $tb.append(
      `<tr class="${trClass}" data-codigo="${codigo}">
         <td>${n}</td>
         <td><i class="mdi mdi-store-outline me-1"></i>${origen}</td>
         <td><i class="mdi mdi-account-outline me-1"></i>${destino}</td>
         <td><span class="badge bg-primary-subtle text-primary border">${codigo}</span></td>
         <td class="text-center">${cantidad}</td>
         <td class="text-center">
           <i class="mdi mdi-close-circle-outline text-danger svc-exclude"
              style="cursor:pointer"
              title="${
                isExcluded ? "Incluir nuevamente" : "Descartar / No enviar"
              }"
              data-orden="${orden}"
              data-codigo="${codigo}"></i>
         </td>
       </tr>`
    );
  });
}

// Click en Cantidad
$(document).on("click", ".ver-servicios", function (e) {
  e.preventDefault();
  const orden = $(this).data("orden");
  if (!orden) return;

  // feedback de carga
  $("#serviciosOrden").text(orden);
  $("#serviciosLista").empty();
  $("#serviciosVacio").hide();
  $("#serviciosLoader").show();

  const modalEl = document.getElementById("serviciosModal");
  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  $.ajax({
    url: "../Logistica/Proceso/php/wepoint.php",
    type: "POST",
    data: { ServiciosPorOrden: 1, NumerodeOrden: orden },
    success: function (resp) {
      $("#serviciosLoader").hide();
      let json = resp;
      try {
        if (typeof resp === "string") json = JSON.parse(resp);
      } catch (e) {}
      const items = json && Array.isArray(json.data) ? json.data : [];
      showServiciosModal(orden, items);
    },
    error: function (xhr) {
      $("#serviciosLoader").hide();
      $("#serviciosLista")
        .empty()
        .append(
          `<li class="list-group-item text-danger">
           Error al cargar servicios (HTTP ${xhr.status})
         </li>`
        );
    },
  });
});

$(document).on("click", ".enviar-api", function () {
  const orden = $(this).data("orden");
  const cantidad = $(this).data("cantidad"); // 👈 ya lo tenés en el <i>
  const token = $("#token_wepoint").val(); // o window.WEPOINT_TOKEN
  // calcular cantidad efectiva descontando excluidos para esta orden
  const getExSetSafe = (o) => {
    try {
      if (typeof getExSet === "function") return getExSet(String(o));
    } catch (e) {}
    return new Set(); // fallback si aún no existe el helper
  };
  const exclSet = getExSetSafe(orden);
  const exclCount =
    exclSet && typeof exclSet.size === "number" ? exclSet.size : 0;
  const cantNum = isNaN(parseInt(cantidad, 10)) ? 0 : parseInt(cantidad, 10);
  const cantidadEfectiva = Math.max(0, cantNum - exclCount);
  const pluralBultos = cantidadEfectiva === 1 ? " bulto" : " bultos";
  if (!orden) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se encontró el número de orden",
    });
    return;
  }

  const exSet = getExSet(String(orden));
  const excluirCodigos = Array.from(exSet); // 👈 lo mandamos al backend

  Swal.fire({
    title: "¿Enviar Paquetes a Wepoint?",
    text:
      "Se va a enviar al Warehouse la orden " +
      orden +
      " con " +
      cantidad +
      pluralBultos,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, enviar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#0d6efd",
  }).then((res) => {
    if (!res.isConfirmed) return;

    Swal.fire({
      title: "Procesando…",
      text: "Enviando orden " + orden,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    $.ajax({
      url: "../Logistica/Proceso/php/wepoint.php",
      type: "POST",
      data: {
        Ejecutar: 1,
        token: token,
        NumerodeOrden: orden,
        ExcluirCodigos: excluirCodigos,
      },
      success: function (resp) {
        let json = resp;
        try {
          if (typeof resp === "string") json = JSON.parse(resp);
        } catch (e) {}
        // éxito 2xx
        if (json && json.http_code >= 200 && json.http_code < 300) {
          Swal.close();
          showSuccess(json, orden);
          // opcional: recargar tabla
          if ($.fn.dataTable.isDataTable("#colecta_tabla")) {
            $("#colecta_tabla").DataTable().ajax.reload(null, false);
          }
          return;
        }
        // 404 del backend “no hay renglones…”
        if (json && json.http_code === 404) {
          Swal.close();
          showNotFound404(json, orden);
          return;
        }
        // otros casos
        Swal.close();
        showAjaxError(
          json && json.http_code ? json.http_code : "—",
          json || resp,
          orden
        );
      },
      error: function (xhr) {
        Swal.close();
        // si el backend mandó JSON en responseText, lo mostramos formateado
        let body = xhr.responseText || "";
        showAjaxError(xhr.status, body, orden);
      },
    });
  });
});

//tabla HOJAS DE RUTA
// === Tabla: HOJAS DE RUTA (Egresos) ===
$(function () {
  var datatableHR = $("#hojas_ruta_tabla").DataTable({
    dom: "Bfrtip",
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    processing: true, // 👈 va a nivel raíz
    ajax: {
      url: "../Logistica/Proceso/php/wepoint.php",
      type: "POST",
      data: { Colectas_out: 1 },
      dataSrc: "data", // 👈 esperamos { data: [...] }
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          // preferencia tuya: dd/mm/YYYY y ocultar valor original
          if (type === "display" || type === "filter") {
            var f = (data || "").split("-").reverse().join("/");
            return (
              '<span style="display: none;">' + (data || "") + "</span>" + f
            );
          }
          return data;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          const chofer = row.Chofer || "";

          return `Recorrido ${row.Recorrido || ""} </br>
          ${chofer};`;
        },
      },

      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          return `<b class="text-primary">${data || ""}</b>`;
        },
      },

      // Cantidad (link que abre modal por Número de Orden)
      {
        data: "Cantidad",
        className: "text-center",
        render: function (data, type, row) {
          const n = parseInt(data || 0, 10);
          if (type !== "display" && type !== "filter") return n;

          const mostrado = n.toLocaleString("es-AR");
          const orden = row.NumerodeOrden || ""; // 👈 usamos el N° de Orden de la fila
          return `${mostrado}`;
        },
      },
      // Acción: Enviar a WePoint (usa datos del row, NO variables sueltas)
      {
        data: null,
        orderable: false,
        className: "text-center",
        render: function (data, type, row) {
          const ord = row.NumerodeOrden || row.Recorrido || "";

          return `
          <i class="mdi mdi-truck-delivery-outline mdi-18px text-success ver-codigos-orden"
             style="cursor:pointer"
             title="Ver códigos de la orden ${ord}"
             data-orden="${ord}"></i>`;
        },
      },
    ],
  });
});

//TODAS LOS CODIGOS ENVIADOS
$(function () {
  var datatableTD = $("#todas_tabla").DataTable({
    dom: "Bfrtip",
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    processing: true, // 👈 va a nivel raíz
    ajax: {
      url: "../Logistica/Proceso/php/wepoint.php",
      type: "POST",
      data: { Todas: 1 },
      dataSrc: "data", // 👈 esperamos { data: [...] }
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type) {
          if (!data) return "";
          // data viene como '2025-09-17 18:44:49'
          const partes = data.split(" ");
          const f = partes[0].split("-").reverse().join("/"); // 17/09/2025
          const h = partes[1] ? partes[1].substring(0, 5) : ""; // 18:44
          if (type === "display" || type === "filter") {
            return `<span style="display:none;">${partes[0]}</span>${f} ${h}`;
          }
          return data;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          const chofer = row.Chofer || "";

          return `Recorrido ${row.Recorrido || ""} </br>
          ${chofer};`;
        },
      },

      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          return `<b class="text-primary">${data || ""}</b>`;
        },
      },

      // Acción: Enviar a WePoint (usa datos del row, NO variables sueltas)
      { data: "CodigoSeguimiento_enviado" },
      { data: "Estado" },
    ],
  });
});

$(document).on("click", "#btn_todas", function () {
  $("#seccion_todas").slideToggle();
  $("#seccion_colectas").slideUp(); // abre/cierra Ingresos
  $("#seccion_colectas_out").slideUp(); // siempre oculta Egresos
});

$(document).on("click", "#btn_ingreso", function () {
  $("#seccion_colectas").slideToggle(); // abre/cierra Ingresos
  $("#seccion_colectas_out").slideUp(); // siempre oculta Egresos
  $("#seccion_todas").slideUp();
});

$(document).on("click", "#btn_egreso", function () {
  $("#seccion_colectas_out").slideToggle(); // abre/cierra Egresos
  $("#seccion_colectas").slideUp(); // siempre oculta Ingresos
  $("#seccion_todas").slideUp();
});

// Detalle de códigos por NÚMERO DE ORDEN (EGRESO) - por pieza
$(document).on("click", ".ver-codigos-orden", function (e) {
  e.preventDefault();
  const orden = $(this).data("orden");
  if (!orden) return;

  $("#egreso_header_badge").text("ORD " + orden);
  $("#tablaCodigosEgreso tbody").empty();
  $("#res_total").text("0");
  $("#res_enviados").text("Enviados: 0");
  $("#res_pendientes").text("Pendientes: 0");

  // abrir modal primero (feedback inmediato)
  $("#modalCodigosEgreso").modal("show");

  $.ajax({
    url: "../Logistica/Proceso/php/wepoint.php",
    method: "POST",
    dataType: "json",
    data: { DetalleEgresoPorOrden: 1, NumerodeOrden: orden },
  })
    .done(function (resp) {
      if (!resp || resp.ok !== true) {
        const msg = (resp && resp.message) || "No se pudo obtener el detalle.";
        $("#tablaCodigosEgreso tbody").html(
          '<tr><td colspan="5" class="text-danger">' + msg + "</td></tr>"
        );
        return;
      }

      const items = (resp.data && resp.data.items) || [];
      // Agrupar por madre: 93G3FXYSI (madre) => piezas [ { codigo_enviado, id_wepoint, estado } ... ]
      const grupos = {};
      items.forEach((it) => {
        const codigo = String(it.codigo_enviado || "");
        // madre = antes del primer "_"; si no tiene "_", la madre es el código mismo
        const madre = codigo.includes("_") ? codigo.split("_")[0] : codigo;
        if (!grupos[madre]) grupos[madre] = [];
        grupos[madre].push({
          codigo_enviado: codigo,
          id_wepoint: +it.id_wepoint || 0,
          estado: it.estado || (it.id_wepoint ? "ENVIADO" : "PENDIENTE"),
          time: it.Time || it.time || "",
        });
      });

      // Pintar resumen (counters)
      const total = resp.total || items.length;
      const listos = resp.listos || items.filter((x) => x.id_wepoint).length;
      const pend = resp.pendientes || total - listos;
      $("#res_total").text(total);
      $("#res_enviados").text("Enviados: " + listos);
      $("#res_pendientes").text("Pendientes: " + pend);

      // Render del cuerpo: filas por madre + subtabla de piezas
      const $tb = $("#tablaCodigosEgreso tbody");
      $tb.empty();

      // === Top toolbar (arriba, al nivel del resumen) ===
      // Si ya existe de una apertura anterior, la removemos para no duplicar
      $("#egresoTopTools").remove();
      const topToolsHtml = `
        <div id="egresoTopTools" class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-2">
          <div class="form-check m-0">
            <input type="checkbox" class="form-check-input me-2" id="sel_all_listos">
            <label for="sel_all_listos" class="form-check-label mb-0">
              Seleccionar todos los <b>listos</b> (con <code>id_wepoint</code>)
            </label>
          </div>
        </div>`;
      // Insertar arriba del listado (antes de la tabla)
      const $tabla = $("#tablaCodigosEgreso");
      $tabla.before(topToolsHtml);

      // === Botón de crear egreso en el footer (junto al Cancelar/Cerrar) ===
      const $footer = $("#modalCodigosEgreso .modal-footer");
      if ($footer.length) {
        // evitar duplicados
        $footer.find("#btn_crear_egreso").remove();
        $footer.prepend(`
          <button type="button" class="btn btn-success enviar-egreso" id="btn_crear_egreso">
            Crear egreso (<span id="sel_count">0</span>)
          </button>
        `);
      }

      Object.keys(grupos).forEach((madre, idx) => {
        const piezas = grupos[madre];
        const cant = piezas.length;
        const enviados = piezas.filter((p) => p.id_wepoint > 0).length;
        const pendientes = cant - enviados;

        // Fila madre (summary) - clickable para expandir/colapsar
        $tb.append(`
          <tr class="mother-row" data-target="#grp_${idx}" style="background:#f6f7f9;">
            <td>${idx + 1}</td>
            <td class="mother-cell">
              <span class="fw-semibold mother-toggle" style="cursor:pointer">${madre}</span>
              <span class="badge bg-secondary ms-2">${cant}</span>
            </td>
            <td class="text-end">
              <div class="form-check d-inline-flex align-items-center m-0">
                <input type="checkbox" class="form-check-input me-2 sel-grupo"
                       data-target="#grp_${idx}_tb" ${
          enviados > 0 ? "" : "disabled"
        }>
                <label class="form-check-label mb-0">Marcar grupo</label>
              </div>
            </td>
            <td>
              <span class="badge bg-success me-1">OK ${enviados}</span>
              <span class="badge bg-warning text-dark">PEND ${pendientes}</span>
            </td>
            <td class="text-muted">—</td>
          </tr>
          <tr class="collapse" id="grp_${idx}">
            <td colspan="5" style="padding:0">
              <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                  <thead>
                    <tr>
                      <th style="width:6%"></th>
                      <th style="width:34%">Pieza</th>
                      <th style="width:18%">id_wepoint</th>
                      <th style="width:18%">Estado</th>
                      <th style="width:24%">Observaciones</th>
                    </tr>
                  </thead>
                  <tbody id="grp_${idx}_tb"></tbody>
                </table>
              </div>
            </td>
          </tr>
        `);

        // Subfilas piezas con checkbox (solo habilitados si id_wepoint > 0)
        const $sub = $tb.find(`#grp_${idx}_tb`);
        piezas.forEach((pz, i) => {
          const checked = pz.id_wepoint > 0 ? "checked" : "";
          const disabled = pz.id_wepoint > 0 ? "" : "disabled";
          const isOk = pz.id_wepoint > 0;
          const timeStr = isOk ? fmtTime(pz.time) : "";
          const badge = isOk
            ? '<div><span class="badge bg-success">ENVIADO</span>' +
              (timeStr
                ? `<div class="small text-muted mt-1">${timeStr}</div>`
                : "") +
              "</div>"
            : '<span class="badge bg-warning text-dark">PENDIENTE</span>';
          $sub.append(`
            <tr>
              <td class="text-center">
                <input type="checkbox" class="form-check-input sel-pieza"
                       data-madre="${madre}"
                       data-codigo="${pz.codigo_enviado}"
                       data-idw="${pz.id_wepoint}"
                       ${checked} ${disabled}>
              </td>
              <td><code>${pz.codigo_enviado}</code></td>
              <td>${pz.id_wepoint || "-"}</td>
              <td>${badge}</td>
              <td></td>
            </tr>
          `);
        });
      });

      // Guardamos en el modal la orden para usar al confirmar egreso
      $("#modalCodigosEgreso").data("orden", orden);
      // actualizar contador inicial (piezas listas vienen marcadas)
      refreshSelCount();

      // Click en la fila madre o en el texto para desplegar hijos
      $(document)
        .off("click.motherToggle")
        .on(
          "click.motherToggle",
          ".mother-row, .mother-row .mother-toggle",
          function (e) {
            // evitar que checkboxes u otros enlaces disparen doble
            if ($(e.target).is("input, label, .sel-grupo")) return;
            const target = $(this).closest(".mother-row").data("target");
            if (target) {
              $(target).collapse("toggle");
            }
          }
        );
    })
    .fail(function (xhr) {
      $("#tablaCodigosEgreso tbody").html(
        '<tr><td colspan="5" class="text-danger">Error de conexión (' +
          xhr.status +
          ").</td></tr>"
      );
    });
});

$(document).on("click", ".enviar-egreso", function () {
  const orden = $(this).data("orden") || $("#modalCodigosEgreso").data("orden");
  const token = $("#token_wepoint").val();
  if (!orden || !token) {
    Swal.fire({ icon: "error", title: "Falta token u orden" });
    return;
  }

  // Juntar ids_bulto tildados (solo piezas con id_wepoint)
  const ids = [];
  $(".sel-pieza:checked").each(function () {
    const idw = parseInt($(this).data("idw"), 10);
    if (idw > 0) ids.push(idw);
  });

  if (ids.length === 0) {
    Swal.fire({
      icon: "info",
      title: "Nada para egresar",
      text: "No hay piezas listas (id_wepoint).",
    });
    return;
  }

  Swal.fire({
    title: "Crear egreso",
    text: `Se crearán egresos para ${ids.length} pieza(s).`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: `Crear egreso (${ids.length})`,
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) return;

    Swal.fire({
      title: "Enviando…",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });
    $.ajax({
      url: "../Logistica/Proceso/php/wepoint.php",
      type: "POST",
      dataType: "json",
      data: {
        CrearEgreso: 1,
        token: token,
        NumerodeOrden: orden,
        ids_bulto: JSON.stringify(ids), // 👈 mandamos SOLO las piezas elegidas
      },
      success: function (resp) {
        Swal.close();
        if (resp && resp.ok) {
          Swal.fire({
            icon: "success",
            title: "Egreso creado",
            timer: 1600,
            showConfirmButton: false,
          });
          // refrescos opcionales
          $("#hojas_ruta_tabla").DataTable().ajax.reload(null, false);
          $("#todas_tabla").DataTable().ajax.reload(null, false);
          $("#modalCodigosEgreso").modal("hide");
        } else {
          const http = (resp && resp.http_code) || "—";
          const body = resp || {};
          showAjaxError(http, body, orden);
        }
      },
      error: function (xhr) {
        Swal.close();
        showAjaxError(xhr.status, xhr.responseText || "", orden);
      },
    });
  });
});

// === Selección masiva y contador ===
function refreshSelCount() {
  const selected = $(".sel-pieza:checked").length;
  $("#sel_count").text(selected);
}

// Seleccionar / deseleccionar todos los listos del modal
$(document).on("change", "#sel_all_listos", function () {
  const checked = this.checked;
  // solo marcar los que están habilitados (id_wepoint > 0)
  $(".sel-pieza:not(:disabled)").prop("checked", checked);
  refreshSelCount();
});

// Selección por grupo
$(document).on("change", ".sel-grupo", function () {
  const target = $(this).data("target");
  const checked = this.checked;
  if (target) {
    $(`${target} .sel-pieza:not(:disabled)`).prop("checked", checked);
    refreshSelCount();
  }
});

// Cuando cambia cualquier checkbox de pieza, refrescar contador
$(document).on("change", ".sel-pieza", function () {
  // si al destildar alguno, desmarcamos "seleccionar todos" si estaba activo
  if (!this.checked) {
    $("#sel_all_listos").prop("checked", false);
  }
  refreshSelCount();
});

// Al cerrar el modal, limpiar selección masiva y contador (opcional)
$(document).on("hidden.bs.modal", "#modalCodigosEgreso", function () {
  $("#sel_all_listos").prop("checked", false);
  $("#sel_count").text("0");
});

(function () {
  /*************  ✨ Windsurf Command ⭐  *************/
  /**
   * Carga resumen de WePoint con información de cantidad de ingresos y egresos realizados,
   * así como cantidad de pendientes sin salida.
   * Se utiliza para rellenar los contadores de la pantalla principal.
   */
  /*******  1813e648-00d9-49bd-852a-8fd997686f2c  *******/
  function cargarResumenWepoint() {
    if (typeof $ === "undefined") return;
    $.ajax({
      url: "Proceso/php/wepoint.php",
      method: "POST",
      dataType: "json",
      data: { ResumenWepoint: 1 },
    }).done(function (resp) {
      if (!resp || resp.ok === false) return;
      if (typeof resp.total_ingresos !== "undefined")
        $("#cnt_ingresos").text(resp.total_ingresos);
      if (typeof resp.total_egresos !== "undefined")
        $("#cnt_egresos").text(resp.total_egresos);
      if (typeof resp.pendientes_in_sin_out !== "undefined")
        $("#cnt_pendientes").text(resp.pendientes_in_sin_out);
    });
  }
  $(document).ready(cargarResumenWepoint);
})();
