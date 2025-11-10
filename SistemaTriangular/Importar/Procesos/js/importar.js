// ==============================
// importar.js
// ==============================
let tablaPendientes = null;

function initTablaPendientes() {
  tablaPendientes = $("#tablaPendientes").DataTable({
    ajax: { url: "Procesos/php/importar_pending.php", dataSrc: "data" },
    processing: true,
    responsive: true,
    pageLength: 25,
    order: [],
    columns: [
      { data: "id", width: "70px" },
      { data: "origen" },
      {
        data: null,
        render: (row) => {
          // Variables básicas
          const marker = row.geo_ok ? "success" : "danger"; // verde/rojo
          const tr = row.geo_ok ? "text-success" : "text-danger";
          const lat = row.lat || ""; // si después guardás coordenadas
          const lng = row.lng || "";
          const dir = row.direccion || "";
          const cliente = row.destino || "(sin nombre)";
          const idProv = row.idProveedor || "";

          // Armamos el bloque visual (similar a tu versión previa)
          return `
        <tr class="table-light">
          <td>
            <span class="${tr}">
              <dt>[${idProv}] ${cliente}</dt>
            </span>
            <br>
            <i class="mdi mdi-18px mdi-map-marker text-${marker}"></i>
            <a class="text-muted">${dir} (${lat} ${lng}) </a>
          </td>
        </tr>
      `;
        },
      },
      { data: "fechahora" },
      { data: "observ", width: "20%" },
      {
        data: "km",
        className: "text-end",
        render: (v) => (isFinite(v) ? Number(v).toFixed(2) : ""),
      },
      { data: "cantidad", className: "text-end" },
      {
        data: null,
        className: "text-end",
        render: (row) => {
          const p = isFinite(row.valorDeclarado)
            ? Number(row.valorDeclarado).toFixed(2)
            : "0.00";
          return `${p}`;
        },
      },
    ],
  });
  window.tablaPendientesDT = tablaPendientes;
}

$(initTablaPendientes);
// Helper global: obtener/crear DataTable de pendientes sin romper si no existe
function getTablaPendientes() {
  // Si ya existe referencia y la instancia sigue viva
  if (
    window.tablaPendientesDT &&
    $.fn.dataTable.isDataTable("#tablaPendientes")
  ) {
    return window.tablaPendientesDT;
  }

  // Si no hay tabla en el DOM, no inicializamos
  if (!$("#tablaPendientes").length) return null;

  // Si la inicializó otro script, tomarla
  if ($.fn.dataTable.isDataTable("#tablaPendientes")) {
    window.tablaPendientesDT = $("#tablaPendientes").DataTable();
    return window.tablaPendientesDT;
  }

  // Inicialización oficial
  window.tablaPendientesDT = $("#tablaPendientes").DataTable({
    ajax: {
      url: "Procesos/php/importar_pending.php", // ⬅️ si usás otro nombre, cambialo acá
      dataSrc: "data",
    },
    processing: true,
    responsive: true,
    pageLength: 25,
    order: [],
    columns: [
      { data: "id", width: "70px" },
      { data: "origen" },
      { data: "destino" },
      { data: "fechahora", render: (v) => v || "" },
      { data: "observ", width: "20%", render: (v) => v || "" },
      {
        data: "km",
        className: "text-end",
        render: (v) => (isFinite(v) ? Number(v).toFixed(2) : ""),
      },
      {
        data: "cantidad",
        className: "text-end",
        render: (v) => (isFinite(v) ? v : ""),
      },
      {
        data: null,
        className: "text-end",
        render: (row) => {
          const p = row.valorDeclarado;
          return `${p}`;
        },
      },
    ],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-AR.json",
    },
  });

  return window.tablaPendientesDT;
}

$(function () {
  // ===========================
  // Select2: buscador de cliente origen
  // ===========================
  $("#cliente_relacion").select2({
    theme: "bootstrap-5",
    width: "100%",
    placeholder:
      $("#cliente_relacion").data("placeholder") || "Buscar cliente...",
    minimumInputLength: 2,
    ajax: {
      url: "/SistemaTriangular/Datos/Procesos/php/clientes.php",
      dataType: "json",
      delay: 250,
      data: (params) => ({ q: params.term || "" }),
      processResults: (data) =>
        Array.isArray(data) ? { results: data } : data,
      cache: true,
      transport: function (params, success, failure) {
        const req = $.ajax(params);
        req.then(success);
        req.fail((xhr) => {
          console.error("Select2 AJAX error", xhr.status, xhr.responseText);
          failure();
        });
        return req;
      },
    },
    templateResult: (item) => (item.loading ? item.text : `${item.text}`),
    templateSelection: (item) => item?.text ?? item?.id ?? "",
  });

  // Guardar id seleccionado en hidden
  $("#cliente_relacion").on("select2:select", function (e) {
    $("#relacion_id").val(e.params.data.id || "");
  });

  // ===========================
  // Inicializar (si existe) la tabla de pendientes
  // ===========================
  getTablaPendientes();

  // ===========================
  // Submit del formulario de importación
  // ===========================
  $("#formImportExcel").on("submit", function (e) {
    e.preventDefault();
    const $btn = $("#btnImport");
    $btn.prop("disabled", true);

    const file = $("#excel")[0]?.files?.[0];
    if (!file) {
      Swal.fire(
        "Falta el archivo",
        "Seleccioná un .xlsx para continuar.",
        "warning"
      );
      $btn.prop("disabled", false); // ⬅️ re-habilitar acá

      return;
    }

    const fd = new FormData(this); // incluye excel + relacion_id

    $("#importProgressWrap").show();
    $("#importProgress").css("width", "0%").text("0%");

    $.ajax({
      url: "Procesos/php/importar.php",
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      xhr: function () {
        const xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
          xhr.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
              const pct = Math.round((evt.loaded * 100) / evt.total);
              $("#importProgress")
                .css("width", pct + "%")
                .text(pct + "%");
            }
          });
        }
        return xhr;
      },
      success: function (resp) {
        $("#importProgressWrap").hide();

        if (resp && resp.ok) {
          const errs = Array.isArray(resp.errors) ? resp.errors : [];
          const htmlErrs = errs
            .slice(0, 10)
            .map((e) => `<li>${e}</li>`)
            .join("");

          Swal.fire({
            icon: "success",
            title: "Importación completada",
            html: `
              <div class="text-start">
                <p><b>Insertados:</b> ${resp.inserted || 0}</p>
                <p><b>Ignorados:</b> ${resp.skipped || 0}</p>
                ${
                  errs.length
                    ? `<p class="mb-1"><b>Errores (${errs.length}):</b></p><ul>${htmlErrs}</ul>`
                    : ""
                }
              </div>
            `,
            width: 600,
          });

          // 🔄 Refresh seguro de la tabla (sólo si existe en este HTML)
          const dt = getTablaPendientes();
          if (dt) dt.ajax.reload(null, false);
        } else {
          // ⬇️ NUEVO: mostrar faltantes y headers detectados si vienen
          const faltan = Array.isArray(resp?.faltan) ? resp.faltan : [];
          const detectadas = resp?.detectadas
            ? JSON.stringify(resp.detectadas, null, 2)
            : "";
          const headersNorm = Array.isArray(resp?.headers_norm)
            ? resp.headers_norm.join(", ")
            : "";

          const detalle = [
            resp?.msg || "No se pudo procesar el Excel.",
            faltan.length ? `<p><b>Faltan:</b> ${faltan.join(", ")}</p>` : "",
            headersNorm
              ? `<p><b>Headers normalizados:</b><br><code>${headersNorm}</code></p>`
              : "",
            detectadas
              ? `<details style="text-align:left"><summary>Mapa detectado</summary><pre>${detectadas}</pre></details>`
              : "",
          ].join("");

          Swal.fire("Error", detalle, "error");

          // Swal.fire(
          //   "Error",
          //   (resp && resp.msg) || "No se pudo procesar el Excel.",
          //   "error"
          // );
        }
      },
      complete: function () {
        $("#importProgressWrap").hide();
        $btn.prop("disabled", false); // re-habilitar pase lo que pase
      },
      error: function (xhr) {
        $("#importProgressWrap").hide();
        Swal.fire(
          "Error de servidor",
          xhr.responseText || "Revisá el log del servidor.",
          "error"
        );
      },
    });
  });
});
// Confirmar importación
$(document).on("click", "#btnConfirmarImport", function () {
  Swal.fire({
    icon: "question",
    title: "Confirmar importación",
    html: "Se crearán clientes faltantes y se generarán registros en <b>PreVenta</b> a partir de las filas pendientes.",
    showCancelButton: true,
    confirmButtonText: "Sí, confirmar",
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) return;

    $.ajax({
      url: "Procesos/php/importar_pending.php",
      type: "POST",
      dataType: "json",
      data: { accion: "confirmar" }, // ó { ConfirmarImportacion: 1 }
      success: (resp) => {
        if (resp?.ok) {
          const errs = (resp.errores || [])
            .slice(0, 10)
            .map((e) => `<li>${e}</li>`)
            .join("");
          Swal.fire({
            icon: "success",
            title: "Confirmación completa",
            html: `
              <div class="text-start">
                <p><b>Confirmados:</b> ${resp.confirmados || 0}</p>
                <p><b>Clientes creados:</b> ${resp.clientes_creados || 0}</p>
                ${
                  errs
                    ? `<p class="mb-1"><b>Errores:</b></p><ul>${errs}</ul>`
                    : ""
                }
              </div>
            `,
            width: 600,
          });
          const dt = getTablaPendientes();
          if (dt) dt.ajax.reload(null, false);
        } else {
          Swal.fire("Error", resp?.msg || "No se pudo confirmar.", "error");
        }
      },
      error: (xhr) => {
        Swal.fire(
          "Error de servidor",
          xhr.responseText || "Revisá el log.",
          "error"
        );
      },
    });
  });
});

// Eliminar pendientes
$(document).on("click", "#btnEliminarPendientes", function () {
  Swal.fire({
    icon: "warning",
    title: "Eliminar pendientes",
    html: "Se marcarán como <b>Eliminado=1</b> todas las filas pendientes.",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) return;

    $.ajax({
      url: "Procesos/php/importar_pending.php",
      type: "POST",
      dataType: "json",
      data: { accion: "eliminar" }, // ó { EliminarImportacion: 1 }
      success: (resp) => {
        if (resp?.ok) {
          Swal.fire(
            "Listo",
            `Filas afectadas: ${resp.afectadas || 0}`,
            "success"
          );
          const dt = getTablaPendientes();
          if (dt) dt.ajax.reload(null, false);
        } else {
          Swal.fire("Error", resp?.msg || "No se pudo eliminar.", "error");
        }
      },
      error: (xhr) => {
        Swal.fire(
          "Error de servidor",
          xhr.responseText || "Revisá el log.",
          "error"
        );
      },
    });
  });
});

$(document).on("click", "#excel", function (e) {
  const origen = $("#relacion_id").val();
  if (!origen || origen === "0") {
    e.preventDefault();
    Swal.fire({
      icon: "warning",
      title: "Seleccioná un origen",
      text: "Antes de subir un archivo, elegí el cliente de origen.",
      confirmButtonColor: "#3085d6",
    });
    return false;
  }
});
