// Bolsines de facturas (Logistica/Bolsines.php). El campo de código está pensado para el lector
// (escribe el código y manda Enter): según el modo, asigna el bolsín al recorrido elegido o lo
// recibe en el depósito, y vuelve a dejar el foco para el siguiente.
(function () {
  "use strict";

  const URL = "Proceso/php/bolsines.php";
  const HORAS_DEMORA = 24; // En viaje hace más de esto: se marca en rojo
  let bolsines = [];

  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const fechaHora = (f) => (f ? f.slice(8, 10) + "/" + f.slice(5, 7) + " " + f.slice(11, 16) : "");
  const modo = () => $("input[name=bo-modo]:checked").val();

  function post(data) {
    return $.ajax({ url: URL, type: "POST", dataType: "json", data }).fail((xhr) => {
      if (xhr.status === 401) location.href = "/SistemaTriangular/inicio.php";
    });
  }

  function resultado(ok, texto) {
    $("#bo-resultado").html(`<div class="alert alert-${ok ? "success" : "danger"} mb-0 py-2"><i class="uil-${ok ? "check-circle" : "exclamation-triangle"} me-1"></i>${esc(texto)}</div>`);
  }

  // Etiquetas en un modal (iframe con BolsinesQR.php); ids vacío = todas.
  // El modal toma el tamaño de lo que muestra: angosto con una etiqueta, ancho con varias, y el
  // alto del contenido (hasta 75% de la pantalla).
  function verQR(ids) {
    const cantidad = ids ? String(ids).split(",").length : bolsines.length;
    $("#bo-qr-dialog")
      .removeClass("modal-lg modal-xl")
      .css("max-width", cantidad === 1 ? "420px" : "")
      .addClass(cantidad === 1 ? "" : cantidad <= 3 ? "modal-lg" : "modal-xl");
    $("#bo-qr-frame").css("height", "320px").attr("src", "BolsinesQR.php?modal=1" + (ids ? "&ids=" + ids : ""));
    $("#bo-modal-qr").modal("show");
  }

  function cargarRecorridos() {
    post({ action: "recorridos" }).done((r) => {
      const opts = ['<option value="">Elegí el recorrido…</option>'].concat(
        (r.data || []).map((x) => `<option value="${esc(x.Numero)}">${esc(x.Numero)} - ${esc(x.Nombre)}</option>`)
      );
      $("#bo-recorrido").html(opts.join(""));
    });
  }

  function listar() {
    post({ action: "listar" }).done((r) => {
      if (!r.success) return resultado(false, r.error);
      bolsines = r.data || [];

      const cuenta = { Disponible: 0, Asignado: 0, "En viaje": 0 };
      bolsines.forEach((b) => (cuenta[b.Estado] = (cuenta[b.Estado] || 0) + 1));
      $("#bo-totales").html(
        Object.keys(cuenta).map((k) => `<span class="badge bo-estado-${k.replace(" ", "-")} me-1 p-2">${k}: ${cuenta[k]}</span>`).join("")
      );

      $("#bo-tabla").html(
        bolsines.length
          ? bolsines
              .map((b) => {
                const demorado = b.Estado === "En viaje" && Number(b.HorasEnEstado) >= HORAS_DEMORA;
                const rec = b.Recorrido ? `${esc(b.Recorrido)} <small class="text-muted">${esc(b.NombreRecorrido || "")}</small>` : "";
                const chofer = b.Estado === "En viaje" ? `${esc(b.Chofer || "")}${b.NumerodeOrden ? ` <small class="text-muted">#${esc(b.NumerodeOrden)}</small>` : ""}` : "";
                return `<tr class="${demorado ? "bo-demorado" : ""}">
                  <td><strong>${esc(b.Codigo)}</strong></td>
                  <td><span class="badge bo-estado-${esc(b.Estado).replace(" ", "-")}">${esc(b.Estado)}</span>${demorado ? ' <small class="text-danger">no volvió</small>' : ""}</td>
                  <td>${rec}</td>
                  <td>${esc(b.Precinto || "")}</td>
                  <td>${chofer}</td>
                  <td>${fechaHora(b.FechaEstado)}</td>
                  <td>${esc(b.Descripcion || "")}</td>
                  <td class="text-end text-nowrap">
                    <button class="btn btn-sm btn-light bo-historial" data-codigo="${esc(b.Codigo)}" title="Historial"><i class="uil-history"></i></button>
                    <button class="btn btn-sm btn-light bo-qr" data-id="${b.id}" title="Imprimir QR"><i class="uil-qrcode-scan"></i></button>
                    ${b.Estado === "Asignado" ? `<button class="btn btn-sm btn-light bo-desasignar" data-codigo="${esc(b.Codigo)}" title="Sacar del recorrido"><i class="uil-times"></i></button>` : ""}
                    ${b.Estado === "En viaje" ? `<button class="btn btn-sm btn-light bo-recibir" data-codigo="${esc(b.Codigo)}" title="Recibir en depósito"><i class="uil-box"></i></button>` : ""}
                    ${b.Estado === "Disponible" ? `<button class="btn btn-sm btn-warning bo-asignar" data-codigo="${esc(b.Codigo)}" title="Asignar a un recorrido"><i class="uil-truck"></i> Asignar</button>` : ""}
                    ${b.Estado === "Disponible" ? `<button class="btn btn-sm btn-light bo-baja" data-codigo="${esc(b.Codigo)}" title="Dar de baja"><i class="uil-trash-alt"></i></button>` : ""}
                  </td>
                </tr>`;
              })
              .join("")
          : '<tr><td colspan="8" class="text-center text-muted">Todavía no hay bolsines. Creá los primeros con "Nuevos bolsines".</td></tr>'
      );

      const dinter = r.dinter || [];
      $("#bo-dinter").html(
        dinter.length
          ? dinter
              .map(
                (d) => `<tr class="${Number(d.Bolsines) === 0 ? "table-danger" : ""}">
                  <td>${esc(d.Recorrido)} <small class="text-muted">${esc(d.Nombre || "")}</small></td>
                  <td class="text-end">${esc(d.Pedidos)}</td>
                  <td class="text-end">${Number(d.Bolsines) === 0 ? '<strong class="text-danger">sin bolsín</strong>' : esc(d.Bolsines)}</td>
                </tr>`
              )
              .join("")
          : '<tr><td colspan="3" class="text-muted">No hay pedidos de Dinter pendientes.</td></tr>'
      );
    });
  }

  // Sin lectora alcanza con el número: "1" o "0001" -> BOL-0001.
  function normalizarCodigo(codigo) {
    codigo = String(codigo || "").trim().toUpperCase();
    return /^\d+$/.test(codigo) ? "BOL-" + codigo.padStart(4, "0") : codigo;
  }

  function procesarCodigo(codigo) {
    codigo = normalizarCodigo(codigo);
    if (!codigo) return;
    const data = { action: modo(), codigo };
    if (data.action === "asignar") {
      data.recorrido = $("#bo-recorrido").val();
      data.precinto = $("#bo-precinto").val().trim();
      if (!data.recorrido) return resultado(false, "Elegí el recorrido antes de escanear.");
    }
    post(data).done((r) => {
      resultado(!!r.success, r.success ? r.msg : r.error);
      if (r.success && data.action === "asignar") $("#bo-precinto").val(""); // el próximo bolsín lleva otro precinto
      listar();
    });
  }

  function accion(action, codigo, pregunta) {
    Swal.fire({ icon: "question", title: codigo, text: pregunta, showCancelButton: true, confirmButtonText: "Sí", cancelButtonText: "Cancelar" }).then((x) => {
      if (!x.isConfirmed) return;
      post({ action, codigo }).done((r) => {
        resultado(!!r.success, r.success ? r.msg : r.error);
        listar();
      });
    });
  }

  $(function () {
    cargarRecorridos();
    listar();
    $("#bo-codigo").focus();

    $("input[name=bo-modo]").on("change", function () {
      const asignar = modo() === "asignar";
      $(".bo-solo-asignar").toggle(asignar);
      $("#bo-codigo-wrap").toggleClass("col-md-5", asignar).toggleClass("col-md-12", !asignar);
      $("#bo-aplicar").text(asignar ? "Asignar" : "Recibir").toggleClass("btn-warning", asignar).toggleClass("btn-success", !asignar);
      $("#bo-codigo").focus();
    });

    function aplicar() {
      procesarCodigo($("#bo-codigo").val());
      $("#bo-codigo").val("").focus();
    }
    $("#bo-codigo").on("keydown", function (e) {
      if (e.key !== "Enter") return;
      e.preventDefault();
      aplicar();
    });
    $("#bo-aplicar").on("click", aplicar);

    // Asignar desde la tabla: usa el recorrido elegido arriba o lo pregunta.
    $(document).on("click", ".bo-asignar", function () {
      const codigo = $(this).data("codigo");
      Swal.fire({
        title: "Asignar " + codigo,
        html:
          '<select id="bo-sw-rec" class="form-select mb-2">' + $("#bo-recorrido").html() + "</select>" +
          '<input id="bo-sw-precinto" type="text" maxlength="30" class="form-control" placeholder="N° de precinto">',
        didOpen: () => $("#bo-sw-rec").val($("#bo-recorrido").val() || ""),
        showCancelButton: true,
        confirmButtonText: "Asignar",
        cancelButtonText: "Cancelar",
        preConfirm: () => {
          const recorrido = $("#bo-sw-rec").val();
          if (!recorrido) return Swal.showValidationMessage("Elegí el recorrido.");
          return { recorrido, precinto: $("#bo-sw-precinto").val().trim() };
        },
      }).then((x) => {
        if (!x.isConfirmed) return;
        post({ action: "asignar", codigo, recorrido: x.value.recorrido, precinto: x.value.precinto }).done((r) => {
          resultado(!!r.success, r.success ? r.msg : r.error);
          listar();
        });
      });
    });

    $(document).on("click", ".bo-desasignar", function () {
      accion("desasignar", $(this).data("codigo"), "¿Sacarlo del recorrido? Queda disponible en el depósito.");
    });
    $(document).on("click", ".bo-recibir", function () {
      accion("recibir", $(this).data("codigo"), "¿Confirmás que volvió al depósito?");
    });
    $(document).on("click", ".bo-baja", function () {
      accion("baja", $(this).data("codigo"), "¿Dar de baja este bolsín? El código no se vuelve a usar.");
    });

    $(document).on("click", ".bo-historial", function () {
      const codigo = $(this).data("codigo");
      post({ action: "historial", codigo }).done((r) => {
        const nombres = { alta: "Alta", asignado: "Asignado", desasignado: "Sacado del recorrido", cargado: "Cargado por el chofer", devuelto: "Recibido en depósito", baja: "Baja" };
        $("#bo-historial-titulo").text("Historial de " + codigo);
        $("#bo-historial").html(
          (r.data || [])
            .map(
              (m) => `<tr><td>${fechaHora(m.Fecha)}</td><td>${esc(nombres[m.Accion] || m.Accion)}</td>
                <td>${m.Recorrido ? esc(m.Recorrido) + ' <small class="text-muted">' + esc(m.NombreRecorrido || "") + "</small>" : ""}</td>
                <td>${esc(m.NumerodeOrden || "")}</td><td>${esc(m.Precinto || "")}</td><td>${esc(m.Usuario)}</td><td>${esc(m.Observaciones || "")}</td></tr>`
            )
            .join("") || '<tr><td colspan="7" class="text-muted">Sin movimientos.</td></tr>'
        );
        $("#bo-modal-historial").modal("show");
      });
    });

    $("#bo-imprimir-todos").on("click", () => verQR(""));
    $(document).on("click", ".bo-qr", function () {
      verQR(String($(this).data("id")));
    });
    $("#bo-qr-frame").on("load", function () {
      try {
        const alto = this.contentWindow.document.documentElement.scrollHeight;
        $(this).css("height", Math.min(alto + 4, Math.round(window.innerHeight * 0.75)) + "px");
      } catch (e) {}
    });
    $("#bo-qr-imprimir").on("click", () => {
      const w = document.getElementById("bo-qr-frame").contentWindow;
      w.focus();
      w.print();
    });

    $("#bo-nuevo").on("click", function () {
      Swal.fire({
        title: "Nuevos bolsines",
        html:
          '<input id="bo-sw-cant" type="number" min="1" max="50" value="1" class="swal2-input" placeholder="Cantidad">' +
          '<input id="bo-sw-desc" type="text" maxlength="100" class="swal2-input" placeholder="Descripción (opcional)">',
        showCancelButton: true,
        confirmButtonText: "Crear",
        cancelButtonText: "Cancelar",
        preConfirm: () => ({ cantidad: $("#bo-sw-cant").val(), descripcion: $("#bo-sw-desc").val() }),
      }).then((x) => {
        if (!x.isConfirmed) return;
        post({ action: "crear", cantidad: x.value.cantidad, descripcion: x.value.descripcion }).done((r) => {
          if (!r.success) return resultado(false, r.error);
          listar();
          verQR(r.ids.join(","));
        });
      });
    });
  });
})();
