// Logística > Wepoint > "Para enviar a Wepoint": colectas y envíos con destino Wepoint que
// todavía no se avisaron. Al abrir una se ven los envíos que llegan al depósito, se elige qué
// hace Wepoint con cada uno y se manda el aviso previo (Proceso/php/wepoint.php, Ejecutar).
(function () {
  const URL_WEPOINT = "Proceso/php/wepoint.php";
  const MODALIDADES = {
    transito: "Tránsito",
    crossdock: "Crossdock",
    almacenamiento: "Almacenamiento",
  };

  const TIPOS = {
    Colecta: "bg-primary-lighten text-primary",
    Servicio: "bg-secondary-lighten text-secondary",
    CrossDocking: "bg-warning-lighten text-warning",
  };

  let pendientes = [];
  let actual = null; // colecta abierta en el modal

  const esc = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
  const fecha = (f) => (f ? String(f).slice(0, 10).split("-").reverse().join("/") : "");
  const num = (n) => Number(n || 0).toLocaleString("es-AR");

  function cargar() {
    $("#wp-ingresos").html('<tr><td colspan="8" class="text-center text-muted py-4">Cargando…</td></tr>');
    $.post(URL_WEPOINT, { IngresosPendientes: 1, dias: $("#wp-dias").val() }, null, "json")
      .done((r) => {
        pendientes = (r && r.data) || [];
        pintar();
      })
      .fail((xhr) => {
        $("#wp-ingresos").html(
          `<tr><td colspan="8" class="text-center text-danger py-4">No se pudo cargar (HTTP ${xhr.status})</td></tr>`
        );
      });
  }

  function pintar() {
    const q = ($("#wp-buscar").val() || "").toLowerCase().trim();
    const filas = pendientes.filter(
      (p) => !q || [p.Origen, p.Codigo, p.Recorrido, p.Domicilio].some((v) => String(v || "").toLowerCase().includes(q))
    );

    const piezas = pendientes.reduce((t, p) => t + Number(p.Piezas || 0), 0);
    $("#cnt_para_avisar").text(num(pendientes.length));
    $("#cnt_para_avisar_piezas").text(pendientes.length ? `${num(piezas)} piezas` : "nada pendiente");
    $("#wp-badge-ingreso").text(pendientes.length || "");

    if (!filas.length) {
      $("#wp-ingresos").html(
        `<tr><td colspan="8" class="text-center text-muted py-4">${
          pendientes.length ? "Nada coincide con la búsqueda." : "No hay nada pendiente de avisar a Wepoint en este período."
        }</td></tr>`
      );
      return;
    }

    $("#wp-ingresos").html(
      filas
        .map(
          (p) => `
        <tr data-id="${p.id}">
          <td>${fecha(p.Fecha)}</td>
          <td>
            <span class="badge ${TIPOS[p.tipo] || "bg-secondary-lighten text-secondary"} me-1" ${p.tipo === "CrossDocking" ? 'title="Escaneado en CrossDocking pero Wepoint no lo recibió"' : ""}>${p.tipo}</span>
            <strong>${esc(p.Origen)}</strong>
            <div class="text-muted small">${esc(p.Domicilio)}</div>
          </td>
          <td><code>${esc(p.Codigo)}</code></td>
          <td>${esc(p.Recorrido)}</td>
          <td>${p.NumerodeOrden ? p.NumerodeOrden : '<span class="text-muted">sin orden</span>'}</td>
          <td class="text-end">${num(p.Envios)}</td>
          <td class="text-end">${num(p.Piezas)}</td>
          <td class="text-end"><button type="button" class="btn btn-sm btn-primary wp-abrir" data-id="${p.id}">Revisar y enviar</button></td>
        </tr>`
        )
        .join("")
    );
  }

  function abrir(id) {
    actual = pendientes.find((p) => Number(p.id) === Number(id));
    if (!actual) return;

    $("#wp-envio-titulo").text(`Enviar a Wepoint · ${actual.tipo} ${actual.idColecta || actual.Codigo}`);
    $("#wp-envio-subtitulo").text(`${actual.Origen} · ${fecha(actual.Fecha)} · Recorrido ${actual.Recorrido}`);
    $("#wp-envios").empty();
    $("#wp-envio-resumen").text("");
    $("#wp-modalidad-todos").val("");
    $("#wp-todos").prop("checked", true);
    $("#wp-envio-cargando").show();
    $("#wp-enviar").prop("disabled", true);
    bootstrap.Modal.getOrCreateInstance(document.getElementById("wp-modal-envio")).show();

    $.post(URL_WEPOINT, { IngresoDetalle: 1, id: actual.id }, null, "json")
      .done((r) => {
        $("#wp-envio-cargando").hide();
        $("#wp-envios").html(
          (r.data || [])
            .map(
              (e) => `
            <tr data-id="${e.id}" data-piezas="${e.Piezas}">
              <td><input type="checkbox" class="form-check-input wp-incluir" checked></td>
              <td><code>${esc(e.Codigo)}</code>${e.Flex ? ' <span class="badge bg-success-lighten text-success">Flex</span>' : ""}</td>
              <td>${esc(e.Destino)}<div class="text-muted small">${esc(e.Domicilio)}</div></td>
              <td class="text-end">${num(e.Piezas)}</td>
              <td>
                <select class="form-select form-select-sm wp-modalidad">
                  ${Object.entries(MODALIDADES)
                    .map(([v, t]) => `<option value="${v}" ${v === e.Modalidad ? "selected" : ""}>${t}</option>`)
                    .join("")}
                </select>
              </td>
            </tr>`
            )
            .join("")
        );
        resumir();
      })
      .fail((xhr) => {
        $("#wp-envio-cargando").hide();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) || `HTTP ${xhr.status}`;
        $("#wp-envios").html(`<tr><td colspan="5" class="text-danger">No se pudo cargar: ${esc(msg)}</td></tr>`);
      });
  }

  function resumir() {
    const incluidos = $("#wp-envios tr").filter((_, tr) => $(tr).find(".wp-incluir").is(":checked"));
    const piezas = incluidos.toArray().reduce((t, tr) => t + Number($(tr).data("piezas") || 0), 0);
    const porModalidad = {};
    incluidos.each((_, tr) => {
      const m = $(tr).find(".wp-modalidad").val();
      porModalidad[m] = (porModalidad[m] || 0) + 1;
    });
    const detalle = Object.entries(porModalidad)
      .map(([m, n]) => `${n} ${MODALIDADES[m].toLowerCase()}`)
      .join(", ");
    $("#wp-envio-resumen").text(
      incluidos.length ? `${num(incluidos.length)} envíos, ${num(piezas)} piezas (${detalle})` : "No hay envíos seleccionados"
    );
    $("#wp-enviar").prop("disabled", !incluidos.length);
    $("#wp-envios tr").each((_, tr) => $(tr).toggleClass("wp-excluido", !$(tr).find(".wp-incluir").is(":checked")));
  }

  function enviar() {
    if (!actual) return;
    const modalidades = {};
    const excluir = [];
    $("#wp-envios tr").each((_, tr) => {
      const id = $(tr).data("id");
      if ($(tr).find(".wp-incluir").is(":checked")) modalidades[id] = $(tr).find(".wp-modalidad").val();
      else excluir.push(id);
    });

    const $btn = $("#wp-enviar").prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Enviando…');
    $.post(URL_WEPOINT, { Ejecutar: 1, id: actual.id, Modalidades: JSON.stringify(modalidades), Excluir: JSON.stringify(excluir) }, null, "json")
      .done((r) => {
        bootstrap.Modal.getInstance(document.getElementById("wp-modal-envio")).hide();
        const rechazados = (r.rechazados || []).map((x) => `${esc(x.codigo_seguimiento)}: ${esc(x.motivo)}`);
        Swal.fire({
          icon: rechazados.length ? "warning" : "success",
          title: "Avisado a Wepoint",
          html:
            `Orden de ingreso <strong>${esc(r.nro_orden_ingreso_bulto)}</strong> (${esc(r.referencia)}) con ${num(r.piezas)} piezas.` +
            (rechazados.length ? `<div class="text-start small mt-2">No se pudieron avisar:<br>${rechazados.join("<br>")}</div>` : ""),
        });
        cargar();
        if (typeof cargarResumenWepoint === "function") cargarResumenWepoint();
        if ($.fn.dataTable.isDataTable("#todas_tabla")) $("#todas_tabla").DataTable().ajax.reload(null, false);
      })
      .fail((xhr) => {
        const r = xhr.responseJSON || {};
        const errores = r.errors ? Object.values(r.errors).flat().slice(0, 5).map(esc).join("<br>") : "";
        Swal.fire({
          icon: "error",
          title: "Wepoint no recibió el aviso",
          html: `${esc(r.message || `HTTP ${xhr.status}`)}${errores ? `<div class="text-start small mt-2">${errores}</div>` : ""}`,
        });
      })
      .always(() => $btn.html('<i class="uil-cloud-upload"></i> Enviar a Wepoint').prop("disabled", false));
  }

  function mostrarModo(modo) {
    $(`#wp-modo-${modo}`).prop("checked", true);
    $("#wp-aviso-operativo").toggle(modo === "operativo");
  }

  function cambiarModo() {
    const modo = $('input[name="wp-modo"]:checked').val();
    const operativo = modo === "operativo";
    Swal.fire({
      icon: "question",
      title: operativo ? "¿Pasar a modo operativo?" : "¿Pasar a modo manual?",
      text: operativo
        ? "Cada bulto que se escanee en CrossDocking se da de alta y se recibe en Wepoint en el momento (Flex como crossdock, el resto en tránsito)."
        : "El escaneo en CrossDocking deja de avisar a Wepoint: los envíos se revisan y se mandan desde esta pantalla.",
      showCancelButton: true,
      confirmButtonText: "Sí, cambiar",
      cancelButtonText: "Cancelar",
    }).then((r) => {
      if (!r.isConfirmed) {
        mostrarModo(operativo ? "manual" : "operativo");
        return;
      }
      $.post(URL_WEPOINT, { ModoIngreso: 1, modo }, null, "json").done((resp) => mostrarModo(resp.modo));
    });
  }

  $(function () {
    cargar();
    $.post(URL_WEPOINT, { ModoIngreso: 1 }, null, "json").done((r) => mostrarModo(r.modo));
    $('input[name="wp-modo"]').on("change", cambiarModo);
    $("#wp-dias").on("change", cargar);
    $("#wp-refrescar").on("click", cargar);
    $("#wp-buscar").on("input", pintar);
    $(document).on("click", "#wp-ingresos tr[data-id]", function () {
      abrir($(this).data("id"));
    });
    $(document).on("change", ".wp-incluir, .wp-modalidad", resumir);
    $("#wp-todos").on("change", function () {
      $(".wp-incluir").prop("checked", this.checked);
      resumir();
    });
    $("#wp-modalidad-todos").on("change", function () {
      if (this.value) $(".wp-modalidad").val(this.value);
      resumir();
    });
    $("#wp-enviar").on("click", enviar);
    // Las tablas de egresos e historial se arman ocultas: al mostrar la pestaña se reacomodan
    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", () => $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust());
  });
})();
