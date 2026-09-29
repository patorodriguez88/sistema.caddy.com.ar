// Importar Recorridos de Dinter (Importar/dinter.php) -> Preventa.
// Paso 1: se sube el archivo y el servidor devuelve el match de cada número de cliente Dinter
// (sin grabar nada). Paso 2: el operador elige el recorrido de Caddy para cada recorrido de
// Dinter, revisa las filas y confirma; el servidor vuelve a validar todo antes de cargar.
(function () {
  "use strict";

  const URL = "Procesos/php/dinter.php";
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });
  const fmt2 = new Intl.NumberFormat("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const ETIQUETA = { ok: "Listo", aviso: "Revisar", duplicado: "No se carga", error: "No encontrado" };

  let token = null;
  let filas = [];

  function hoyMasUno() {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().slice(0, 10);
  }

  function cargarOrigenes() {
    $.post(URL, { accion: "origenes" }, null, "json").done(function (r) {
      if (!r || !r.ok || !r.origenes.length) return;
      $("#di-origen").html(r.origenes.map((o) => `<option value="${o.id}" ${String(o.id) === "36" ? "selected" : ""}>${esc(o.nombrecliente)} (${fmt0.format(o.relacionados)} clientes)</option>`).join(""));
    });
  }

  function previsualizar(archivo) {
    if (!archivo) return;
    if (!$("#di-fecha").val()) {
      Swal.fire("Falta la fecha", "Elegí la fecha de entrega antes de subir el archivo.", "warning");
      $("#di-archivo").val("");
      return;
    }
    const fd = new FormData();
    fd.append("accion", "previsualizar");
    fd.append("origen", $("#di-origen").val());
    fd.append("fecha_entrega", $("#di-fecha").val());
    fd.append("archivo", archivo);
    $("#di-drop-texto").html(`<span class="spinner-border spinner-border-sm me-1"></span>Leyendo <b>${esc(archivo.name)}</b>…`);
    $.ajax({ url: URL, type: "POST", data: fd, processData: false, contentType: false, dataType: "json" })
      .done(function (r) {
        if (!r || !r.ok) {
          Swal.fire("No se pudo leer el archivo", (r && r.error) || "Error desconocido", "error");
          return;
        }
        token = r.token;
        filas = r.filas;
        pintar(r);
      })
      .fail(function (x) { Swal.fire("Error", "No se pudo procesar el archivo (" + x.status + ").", "error"); })
      .always(function () {
        $("#di-drop-texto").html(`<b>${esc(archivo.name)}</b> · elegí otro para reemplazarlo`);
        $("#di-archivo").val("");
      });
  }

  function pintar(r) {
    const cuenta = { ok: 0, aviso: 0, duplicado: 0, error: 0 };
    filas.forEach((f) => cuenta[f.estado]++);
    const kpi = (color, icono, label, valor, pie) => `
      <div class="cf-kpi" style="--cf-accent:${color}"><div class="cf-kpi-label"><i class="mdi ${icono}"></i>${label}</div>
      <div class="cf-kpi-value">${fmt0.format(valor)}</div><div class="cf-kpi-foot">${pie}</div></div>`;
    $("#di-resumen").html(
      kpi("#727cf5", "mdi-file-document-outline", "Filas del archivo", filas.length, "con número de cliente") +
      kpi("#0acf97", "mdi-check-circle-outline", "Listas", cuenta.ok, "matchean con un único cliente") +
      kpi("#ffbc00", "mdi-alert-outline", "Para revisar", cuenta.aviso, "ya tienen envío para esa fecha") +
      kpi("#fa5c7c", "mdi-close-circle-outline", "No se cargan", cuenta.error + cuenta.duplicado, `${cuenta.error} sin cliente · ${cuenta.duplicado} repetidas`)
    );

    const opciones = r.recorridos.map((x) => `<option value="${x.numero}">${x.numero} · ${esc(x.nombre)}</option>`).join("");
    $("#di-grupos").html(r.grupos.map((g) => `
      <div class="di-grupo">
        <span>Recorrido Dinter <b>${esc(g.rec_dinter || "(vacío)")}</b> · ${fmt0.format(g.filas)} filas →</span>
        <select class="form-select form-select-sm di-rec" data-dinter="${esc(g.rec_dinter)}">
          <option value="">Elegí el recorrido de Caddy…</option>${opciones}
        </select>
        <small class="text-muted">${g.sugerido ? "Sugerido por " + esc(g.por) : "Sin sugerencia: elegilo a mano"}</small>
      </div>`).join(""));
    r.grupos.forEach((g) => { if (g.sugerido) $(`.di-rec[data-dinter="${CSS.escape(g.rec_dinter)}"]`).val(String(g.sugerido)); });

    $("#di-tabla tbody").html(filas.map(function (f) {
      const cargable = f.estado === "ok" || f.estado === "aviso";
      const cli = f.cliente
        ? `<b>${esc(f.cliente.nombre)}</b><div class="cp-sub">${esc(f.cliente.direccion)}</div>`
        : `<span class="text-danger">—</span>`;
      return `<tr class="${cargable ? "" : "di-fila-error"}">
        <td class="cf-first"><input type="checkbox" class="form-check-input di-chk" value="${f.linea}" ${f.estado === "ok" ? "checked" : ""} ${cargable ? "" : "disabled"}></td>
        <td>${f.linea}</td>
        <td><b>${esc(f.nro_txt)}</b></td>
        <td>${cli}</td>
        <td class="num">${fmt0.format(f.cantidad)}</td>
        <td class="num">$ ${fmt2.format(f.importe)}</td>
        <td class="num">${esc(f.rec_dinter)}</td>
        <td><span class="di-estado di-${f.estado}">${ETIQUETA[f.estado]}</span>${f.motivo ? `<div class="cp-sub">${esc(f.motivo)}</div>` : ""}</td>
      </tr>`;
    }).join(""));
    $("#di-todos").prop("checked", cuenta.ok > 0);
    $("#di-resultado").removeClass("d-none")[0].scrollIntoView({ behavior: "smooth" });
  }

  function confirmar() {
    const lineas = $(".di-chk:checked").map((_, e) => e.value).get();
    if (!lineas.length) {
      Swal.fire("Nada para cargar", "Tildá al menos una fila.", "info");
      return;
    }
    const recorridos = {};
    let faltaRec = false;
    $(".di-rec").each(function () {
      const d = this.dataset.dinter;
      const usada = filas.some((f) => f.rec_dinter === d && lineas.includes(String(f.linea)));
      if (usada && !this.value) faltaRec = true;
      recorridos[d] = this.value;
    });
    if (faltaRec) {
      Swal.fire("Falta el recorrido", "Elegí el recorrido de Caddy para cada recorrido de Dinter.", "warning");
      return;
    }
    const resumenRec = $(".di-rec").map(function () { return this.value ? `Dinter ${this.dataset.dinter} → Caddy ${$(this).find("option:selected").text()}` : null; }).get().filter(Boolean);
    Swal.fire({
      icon: "question",
      title: `¿Cargar ${lineas.length} envíos en Preventa?`,
      html: `Entrega: <b>${$("#di-fecha").val().split("-").reverse().join("/")}</b><br>${resumenRec.map(esc).join("<br>")}`,
      showCancelButton: true,
      confirmButtonText: "Sí, cargar",
      cancelButtonText: "Revisar",
      showLoaderOnConfirm: true,
      preConfirm: () =>
        $.post(URL, { accion: "confirmar", token, origen: $("#di-origen").val(), fecha_entrega: $("#di-fecha").val(), lineas, recorridos: JSON.stringify(recorridos) }, null, "json")
          .then((r) => { if (!r || !r.ok) throw new Error((r && r.error) || "No se pudo cargar"); return r; })
          .catch((e) => Swal.showValidationMessage(e.message || e.statusText || "No se pudo cargar")),
    }).then(function (res) {
      if (!res.isConfirmed || !res.value) return;
      const r = res.value;
      Swal.fire({
        icon: "success",
        title: `Se cargaron ${r.cargadas} envíos en Preventa`,
        html: (r.salteadas.length ? `<div class="text-start small mb-2"><b>No se cargaron:</b><br>${r.salteadas.map(esc).join("<br>")}</div>` : "") +
          `<a class="btn btn-primary mt-2" href="/SistemaTriangular/Ventas/Pendientes.php">Ir a Preventa</a>`,
        showConfirmButton: false,
        showCloseButton: true,
      });
      limpiar();
    });
  }

  function limpiar() {
    token = null;
    filas = [];
    $("#di-resultado").addClass("d-none");
    $("#di-drop-texto").html("<b>Elegí el archivo</b> o arrastralo acá (.csv o .xlsx)");
  }

  $(function () {
    $("#di-fecha").val(hoyMasUno());
    cargarOrigenes();
    $("#di-archivo").on("change", function () { previsualizar(this.files[0]); });
    const drop = document.getElementById("di-drop");
    ["dragenter", "dragover"].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add("is-over"); }));
    ["dragleave", "drop"].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.remove("is-over"); }));
    drop.addEventListener("drop", (e) => previsualizar(e.dataTransfer.files[0]));
    // Cambiar origen o fecha invalida la vista previa (el match depende de los dos)
    $("#di-origen, #di-fecha").on("change", function () { if (token) { limpiar(); Swal.fire("Volvé a subir el archivo", "Cambiaste el origen o la fecha: hay que revisar de nuevo.", "info"); } });
    $("#di-todos").on("change", function () { $(".di-chk:not(:disabled)").prop("checked", this.checked); });
    $("#di-confirmar").on("click", confirmar);
    $("#di-cancelar").on("click", limpiar);
  });
})();
