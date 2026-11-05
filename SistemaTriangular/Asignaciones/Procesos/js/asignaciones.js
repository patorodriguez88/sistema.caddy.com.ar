// Asignaciones de diarios: se suben los archivos, se revisa todo sin grabar y se confirma.
(function () {
  "use strict";

  const URL = "Procesos/php/asignaciones.php";
  const fmt = new Intl.NumberFormat("es-AR");
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const ETIQUETA = { ok: "Listo", aviso: "Revisar", duplicado: "Ya cargado", error: "No se carga" };
  let token = null;

  function planilla(relacion, fecha, producto) {
    return `../Logistica/Informes/Asignaciones.php?Relacion=${encodeURIComponent(relacion)}&Fecha=${encodeURIComponent(fecha)}&CodigoProducto=${encodeURIComponent(producto)}`;
  }

  function cargarHistorial() {
    $.post(URL, { accion: "historial" }, null, "json").done(function (r) {
      if (!r || !r.ok) return;
      $("#as-historial tbody").html(
        r.lotes.map((l) => `<tr>
          <td class="text-start">${esc(l.Fecha.split("-").reverse().join("/"))}</td>
          <td class="text-start">${esc(l.Nombre || l.CodigoProducto)}</td>
          <td>${esc(l.Edicion)}</td>
          <td>${fmt.format(l.Kioscos)}</td>
          <td>${fmt.format(l.Ejemplares)}</td>
          <td><a class="btn btn-light btn-sm" target="_blank" rel="noopener" href="${planilla(l.Relacion, l.Fecha, l.CodigoProducto)}"><i class="mdi mdi-printer me-1"></i>Planilla</a></td>
        </tr>`).join("") || '<tr><td colspan="6" class="text-muted">Todavía no hay asignaciones cargadas.</td></tr>'
      );
    });
  }

  function pintar(r) {
    token = r.token;
    if (r.fecha_sugerida) $("#as-fecha").val(r.fecha_sugerida);
    const errorLote = r.lotes.find((l) => l.error);
    $("#as-lotes").html(r.lotes.map((l) => `<div class="alert ${l.error ? "alert-danger" : "alert-light border"} py-2 mb-2">
      <b>${esc(l.nombre || "Producto " + l.producto)}</b> · edición ${esc(l.edicion)} · ${fmt.format(l.kioscos)} kioscos · <b>${fmt.format(l.ejemplares)} ejemplares</b>
      ${l.error ? `<div>${esc(l.error)}</div>` : ""}</div>`).join(""));

    const opciones = (sel) => '<option value="">Elegir recorrido</option>' + r.recorridos.map((x) =>
      `<option value="${x.numero}" ${x.numero === sel ? "selected" : ""}>${x.numero} · ${esc(x.nombre)}</option>`).join("");
    $("#as-tabla tbody").html(r.filas.map((f) => {
      const carga = r.con_envios && f.envio && f.cliente && f.estado !== "duplicado" && f.estado !== "error";
      return `<tr>
        <td class="text-start">${String(f.nro).padStart(4, "0")}</td>
        <td class="text-start">${f.cliente ? `${esc(f.cliente.nombre)}<div class="text-muted small">${esc(f.cliente.direccion)}</div>` : "—"}</td>
        <td>${fmt.format(f.ejemplares)}</td>
        <td>${f.envio ? "$ " + fmt.format(f.envio.importe) : "—"}</td>
        <td class="text-start">${carga ? `<select class="form-select form-select-sm as-rec" data-nro="${f.nro}">${opciones(f.recorrido)}</select>` : "—"}</td>
        <td class="text-start"><span class="as-estado as-${f.estado}">${ETIQUETA[f.estado]}</span>${f.motivo ? `<div class="small text-muted">${esc(f.motivo)}</div>` : ""}</td>
      </tr>`;
    }).join(""));
    $("#as-sin-envios").toggleClass("d-none", r.con_envios);
    $("#as-confirmar").prop("disabled", !!errorLote);
    $("#as-resultado").removeClass("d-none");
  }

  $("#as-previsualizar").on("click", function () {
    const asigna = $("#as-asigna")[0].files[0];
    if (!asigna) {
      Swal.fire("Falta el archivo", "Elegí el archivo de asignaciones (ASIGNA.SQL).", "warning");
      return;
    }
    const fd = new FormData();
    fd.append("accion", "previsualizar");
    fd.append("fecha", $("#as-fecha").val());
    fd.append("asigna", asigna);
    if ($("#as-recorrido")[0].files[0]) fd.append("recorrido", $("#as-recorrido")[0].files[0]);
    const $btn = $(this).prop("disabled", true);
    $.ajax({ url: URL, type: "POST", data: fd, processData: false, contentType: false, dataType: "json" })
      .done(function (r) {
        if (!r || !r.ok) {
          Swal.fire("No se pudo leer", (r && r.error) || "Error desconocido", "error");
          return;
        }
        pintar(r);
      })
      .fail(function (x) { Swal.fire("Error", "No se pudieron procesar los archivos (" + x.status + ").", "error"); })
      .always(function () { $btn.prop("disabled", false); });
  });

  $("#as-cancelar").on("click", function () {
    token = null;
    $("#as-resultado").addClass("d-none");
    $("#as-asigna, #as-recorrido").val("");
  });

  $("#as-confirmar").on("click", function () {
    const fecha = $("#as-fecha").val();
    if (!fecha) {
      Swal.fire("Falta la fecha", "Elegí la fecha de los diarios.", "warning");
      return;
    }
    const recorridos = {};
    let faltan = 0;
    $(".as-rec").each(function () {
      if ($(this).val()) recorridos[$(this).data("nro")] = $(this).val();
      else faltan++;
    });
    if (faltan) {
      Swal.fire("Faltan recorridos", `Elegí el recorrido de ${faltan} kiosco(s).`, "warning");
      return;
    }
    const $btn = $(this).prop("disabled", true);
    $.post(URL, { accion: "confirmar", token: token, fecha: fecha, recorridos: JSON.stringify(recorridos) }, null, "json")
      .done(function (r) {
        if (!r || !r.ok) {
          Swal.fire("No se cargó", (r && r.error) || "Error desconocido", "error");
          return;
        }
        Swal.fire("Listo", `Se cargaron ${r.asignaciones} asignaciones y ${r.envios} envíos en Preventa. Aceptalos desde Preventa para que salgan en la planilla.`, "success");
        $("#as-cancelar").trigger("click");
        cargarHistorial();
      })
      .fail(function (x) { Swal.fire("Error", "No se pudo guardar (" + x.status + ").", "error"); })
      .always(function () { $btn.prop("disabled", false); });
  });

  function cargarProductos() {
    $.post(URL, { accion: "productos" }, null, "json").done(function (r) {
      if (!r || !r.ok) return;
      $("#as-productos tbody").html(
        r.productos.map((p) => `<tr>
          <td class="text-start">${esc(p.CodigoProducto)}</td>
          <td class="text-start">${esc(p.Nombre)}</td>
          <td><button type="button" class="btn btn-light btn-sm as-producto-editar" data-id="${p.id}" data-codigo="${esc(p.CodigoProducto)}" data-nombre="${esc(p.Nombre)}"><i class="mdi mdi-pencil me-1"></i>Editar</button></td>
        </tr>`).join("") || '<tr><td colspan="3" class="text-muted">Todavía no hay productos.</td></tr>'
      );
    });
  }

  function guardarProducto(datos, alGuardar) {
    $.post(URL, Object.assign({ accion: "producto_guardar" }, datos), null, "json")
      .done(function (r) {
        if (!r || !r.ok) {
          Swal.fire("No se guardó", (r && r.error) || "Error desconocido", "error");
          return;
        }
        if (alGuardar) alGuardar();
        cargarProductos();
        cargarHistorial();
      })
      .fail(function (x) { Swal.fire("Error", "No se pudo guardar (" + x.status + ").", "error"); });
  }

  $("#as-producto-agregar").on("click", function () {
    guardarProducto({ codigo: $("#as-producto-codigo").val(), nombre: $("#as-producto-nombre").val() }, function () {
      $("#as-producto-codigo, #as-producto-nombre").val("");
    });
  });

  $(document).on("click", ".as-producto-editar", function () {
    const $b = $(this);
    Swal.fire({
      title: "Producto " + $b.data("codigo"),
      input: "text",
      inputLabel: "Nombre",
      inputValue: String($b.data("nombre")),
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
    }).then(function (r) {
      if (r.isConfirmed) guardarProducto({ id: $b.data("id"), nombre: r.value });
    });
  });

  cargarHistorial();
  cargarProductos();
})();
