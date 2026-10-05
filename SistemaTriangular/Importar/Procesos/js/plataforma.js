// Importaciones de Plataforma (Importar/plataforma.php).
// Lista las filas de Importaciones que los clientes subieron en Plataforma y no se confirmaron
// (o que subió el operador a nombre de un cliente que no usa Plataforma).
// El operador puede corregirlas y las importa de a una por la API (POST /servicios con el token
// del cliente); cada fila muestra el código de seguimiento o el motivo exacto del rechazo.
(function () {
  "use strict";

  const URL = "Procesos/php/plataforma.php";
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  let filas = [];
  const estado = {}; // id -> {ok, msg, codigo}

  function filtros() {
    return { ncliente: $("#pl-cliente").val(), dias: $("#pl-dias").val() };
  }

  function cargarClientes() {
    const actual = $("#pl-cliente").val();
    $.post(URL, { accion: "clientes", dias: $("#pl-dias").val() }, null, "json").done(function (r) {
      if (!r || !r.ok) return;
      const total = r.clientes.reduce((a, c) => a + Number(c.pendientes), 0);
      $("#pl-cliente").html(
        `<option value="">Todos (${fmt0.format(total)})</option>` +
          r.clientes.map((c) => `<option value="${esc(c.NCliente)}">${esc(c.RazonSocial)} · Nº ${esc(c.NCliente)} (${fmt0.format(c.pendientes)})</option>`).join("")
      );
      $("#pl-cliente").val(actual);
      if ($("#pl-cliente").val() == null) $("#pl-cliente").val("");
    });
  }

  function cargar() {
    $.post(URL, Object.assign({ accion: "listar" }, filtros()), null, "json")
      .done(function (r) {
        filas = r && r.ok ? r.filas : [];
        pintar();
      })
      .fail(() => Swal.fire("Error", "No se pudieron cargar las importaciones.", "error"));
  }

  function chipEstado(id) {
    const e = estado[id];
    if (!e) return `<span class="pl-estado pl-pend">Pendiente</span>`;
    if (e.ok) return `<span class="pl-estado pl-ok"><i class="mdi mdi-check"></i>${esc(e.codigo)}</span>`;
    return `<span class="pl-estado pl-error"><i class="mdi mdi-alert-circle-outline"></i>${esc(e.msg)}</span>`;
  }

  function pintar() {
    const $tb = $("#pl-tabla tbody");
    if (!filas.length) {
      $tb.html(`<tr><td colspan="12" class="text-center text-muted py-4">No hay importaciones pendientes con este filtro.</td></tr>`);
    } else {
      $tb.html(
        filas
          .map((f) => {
            const hecho = estado[f.id] && estado[f.id].ok;
            const medidas = `${f.Length || "–"}×${f.Width || "–"}×${f.Height || "–"} cm · ${f.Weight || "–"} kg${Number(f.Flex) === 1 ? " · <b>Flex</b>" : ""}`;
            return `<tr data-id="${f.id}">
              <td class="cf-first"><input type="checkbox" class="form-check-input pl-chk" ${hecho ? "disabled" : ""}></td>
              <td>${esc(f.Fecha)}<div class="text-muted small">${esc((f.Hora || "").slice(0, 5))}</div></td>
              <td>${esc(f.RazonSocial)}<div class="text-muted small">${esc(f.Usuario)}</div></td>
              <td>${esc(f.ClienteDestino)}<div class="text-muted small">${esc(f.Celular)}</div></td>
              <td>${esc(f.DomicilioDestino)}<div class="text-muted small">${esc(f.LocalidadDestino)}</div></td>
              <td>${esc(f.cpdestino)}</td>
              <td class="num">${fmt0.format(f.Cantidad || 1)}</td>
              <td>${medidas}</td>
              <td class="num">$ ${fmt0.format(f.ValorDeclarado || 0)}</td>
              <td class="num">${Number(f.Cobranza) > 0 ? "$ " + fmt0.format(f.Cobranza) : "–"}</td>
              <td class="pl-est">${chipEstado(f.id)}</td>
              <td class="acc">${
                hecho
                  ? ""
                  : `<button class="btn btn-sm btn-light pl-editar" title="Corregir"><i class="mdi mdi-pencil"></i></button>
                     <button class="btn btn-sm btn-light text-danger pl-descartar" title="Descartar"><i class="mdi mdi-delete"></i></button>`
              }</td>
            </tr>`;
          })
          .join("")
      );
    }
    $("#pl-todos").prop("checked", false);
    $("#pl-pie").text(filas.length ? `${fmt0.format(filas.length)} envío(s) pendiente(s).` : "");
    actualizarBoton();
  }

  function seleccionados() {
    return $(".pl-chk:checked")
      .map((_, el) => Number($(el).closest("tr").data("id")))
      .get();
  }

  function actualizarBoton() {
    const n = seleccionados().length;
    $("#pl-importar").prop("disabled", n === 0).html(`<i class="mdi mdi-check"></i> Importar seleccionados${n ? " (" + n + ")" : ""}`);
  }

  async function importar() {
    const ids = seleccionados();
    if (!ids.length) return;
    const conf = await Swal.fire({
      title: `¿Importar ${ids.length} envío(s)?`,
      text: "Se dan de alta por la API a nombre de cada cliente, con su tarifa.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Importar",
      cancelButtonText: "Cancelar",
    });
    if (!conf.isConfirmed) return;

    $("#pl-importar, #pl-refrescar").prop("disabled", true);
    let ok = 0;
    for (let i = 0; i < ids.length; i++) {
      const id = ids[i];
      $(`#pl-tabla tr[data-id="${id}"] .pl-est`).html(`<span class="pl-estado pl-pend"><i class="mdi mdi-loading mdi-spin"></i>Importando ${i + 1}/${ids.length}…</span>`);
      let r;
      try {
        r = await $.post(URL, { accion: "importar", id }, null, "json");
      } catch (e) {
        r = { ok: false, msg: "Error de comunicación con el servidor" };
      }
      estado[id] = r && r.ok ? { ok: true, codigo: r.codigo } : { ok: false, msg: (r && r.msg) || "No se pudo importar" };
      if (estado[id].ok) ok++;
      pintarFila(id);
    }
    $("#pl-refrescar").prop("disabled", false);
    actualizarBoton();
    cargarClientes();

    const errores = ids.length - ok;
    Swal.fire({
      icon: errores ? (ok ? "warning" : "error") : "success",
      title: errores ? `${ok} de ${ids.length} importado(s)` : "Importación completa",
      text: errores ? "Los que fallaron muestran el motivo en la columna Estado: corregilos con el lápiz y volvé a importarlos." : "Ya están en Preventa con su código de seguimiento.",
    });
  }

  // Repinta sólo la fila (para no perder los tildes del resto mientras se importa).
  function pintarFila(id) {
    const $tr = $(`#pl-tabla tr[data-id="${id}"]`);
    $tr.find(".pl-est").html(chipEstado(id));
    if (estado[id] && estado[id].ok) {
      $tr.find(".pl-chk").prop({ checked: false, disabled: true });
      $tr.find(".acc").empty();
    }
  }

  function editar(id) {
    const f = filas.find((x) => Number(x.id) === id);
    if (!f) return;
    const $form = $("#pl-form");
    $form.find("[name]").each(function () {
      const n = this.name;
      $(this).val(n === "id" ? f.id : f[n] == null ? "" : f[n]);
    });
    bootstrap.Modal.getOrCreateInstance(document.getElementById("pl-modal")).show();
  }

  $("#pl-form").on("submit", function (e) {
    e.preventDefault();
    const datos = $(this).serializeArray().reduce((a, x) => ((a[x.name] = x.value), a), { accion: "guardar" });
    $.post(URL, datos, null, "json").done(function (r) {
      if (!r || !r.ok) {
        Swal.fire("No se guardó", (r && r.msg) || "Error al guardar", "error");
        return;
      }
      const i = filas.findIndex((x) => Number(x.id) === Number(datos.id));
      if (i >= 0 && r.fila) filas[i] = Object.assign(filas[i], r.fila);
      delete estado[datos.id];
      bootstrap.Modal.getOrCreateInstance(document.getElementById("pl-modal")).hide();
      pintar();
    });
  });

  $("#pl-tabla").on("click", ".pl-editar", function () {
    editar(Number($(this).closest("tr").data("id")));
  });

  $("#pl-tabla").on("click", ".pl-descartar", async function () {
    const id = Number($(this).closest("tr").data("id"));
    const f = filas.find((x) => Number(x.id) === id) || {};
    const conf = await Swal.fire({
      title: "¿Descartar este envío?",
      text: `${f.ClienteDestino || ""} — no se va a importar y el cliente deja de verlo como pendiente.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Descartar",
      cancelButtonText: "Cancelar",
    });
    if (!conf.isConfirmed) return;
    $.post(URL, { accion: "descartar", id }, null, "json").done(function () {
      filas = filas.filter((x) => Number(x.id) !== id);
      pintar();
      cargarClientes();
    });
  });

  $("#pl-tabla").on("change", ".pl-chk", actualizarBoton);
  $("#pl-todos").on("change", function () {
    $(".pl-chk:not(:disabled)").prop("checked", this.checked);
    actualizarBoton();
  });
  $("#pl-cliente").on("change", cargar);
  $("#pl-dias").on("change", function () {
    cargarClientes();
    cargar();
  });
  $("#pl-refrescar").on("click", function () {
    cargarClientes();
    cargar();
  });
  $("#pl-importar").on("click", importar);

  // ---- Subir Excel a nombre de un cliente ----
  let tBuscar = null;
  $("#pl-buscar").on("input", function () {
    $("#pl-ncliente").val("");
    $("#pl-cli-info").text("");
    const q = this.value.trim();
    clearTimeout(tBuscar);
    if (q.length < 2) return $("#pl-sugerencias").addClass("d-none");
    tBuscar = setTimeout(function () {
      $.post(URL, { accion: "buscar_cliente", q }, null, "json").done(function (r) {
        const cs = (r && r.clientes) || [];
        $("#pl-sugerencias")
          .html(
            cs.length
              ? cs
                  .map(
                    (c) => `<button type="button" class="list-group-item list-group-item-action" data-id="${c.id}" data-nombre="${esc(c.nombrecliente)}" data-usuarios="${c.usuarios}">
                      <b>${esc(c.nombrecliente)}</b> <span class="text-muted">· Nº ${c.id}</span>
                      ${Number(c.usuarios) ? "" : '<span class="badge bg-warning-subtle text-warning ms-1">sin usuario de Plataforma</span>'}
                      <div class="small text-muted">${esc(c.Direccion)}</div></button>`
                  )
                  .join("")
              : `<div class="list-group-item text-muted">Sin resultados</div>`
          )
          .removeClass("d-none");
      });
    }, 250);
  });

  $("#pl-sugerencias").on("click", "[data-id]", function () {
    const $b = $(this);
    $("#pl-ncliente").val($b.data("id"));
    $("#pl-buscar").val($b.data("nombre"));
    $("#pl-sugerencias").addClass("d-none");
    $("#pl-cli-info").html(
      Number($b.data("usuarios"))
        ? ""
        : '<span class="text-warning">Este cliente no tiene usuario de Plataforma: sin usuario la API no puede darle de alta envíos. Creale uno en la ficha del cliente &gt; Accesos web.</span>'
    );
  });

  $(document).on("click", function (e) {
    if (!$(e.target).closest("#pl-buscar, #pl-sugerencias").length) $("#pl-sugerencias").addClass("d-none");
  });

  $("#pl-subir").on("submit", function (e) {
    e.preventDefault();
    if (!$("#pl-ncliente").val()) return Swal.fire("Falta el cliente", "Buscá y elegí el cliente de la lista.", "warning");
    const fd = new FormData(this);
    fd.append("accion", "subir");
    const $btn = $("#pl-subir-btn").prop("disabled", true);
    $.ajax({ url: URL, method: "POST", data: fd, processData: false, contentType: false, dataType: "json" })
      .done(function (r) {
        const avisos = (r && r.avisos) || [];
        const lista = avisos.length ? `<ul class="text-start small mt-2">${avisos.map((a) => `<li>${esc(a)}</li>`).join("")}</ul>` : "";
        if (!r || !r.ok) {
          Swal.fire({ icon: "error", title: "No se cargó", html: esc((r && r.msg) || "Error al subir la planilla") + lista });
          return;
        }
        Swal.fire({ icon: avisos.length ? "warning" : "success", title: `${r.insertados} envío(s) cargado(s)`, html: "Quedaron en la lista de pendientes: revisalos e importalos." + lista });
        $("#pl-archivo").val("");
        const nc = $("#pl-ncliente").val();
        cargarClientes();
        setTimeout(function () {
          $("#pl-cliente").val(nc);
          if ($("#pl-cliente").val() == null) $("#pl-cliente").val("");
          cargar();
        }, 400);
      })
      .fail(() => Swal.fire("Error", "No se pudo subir la planilla.", "error"))
      .always(() => $btn.prop("disabled", false));
  });

  cargarClientes();
  cargar();
})();
