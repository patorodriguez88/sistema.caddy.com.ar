// Logística > Wepoint: panel en vivo. Cada 20 segundos manda la cola a Wepoint (por si quedó algo) y
// refresca los indicadores y la pestaña abierta. Nada de esto frena el escaneo en CrossDocking.
(function () {
  const URL_WEPOINT = "Proceso/php/wepoint.php";
  const CADA_MS = 20000;
  const MODALIDADES = {
    crossdock: ["Crossdock", "bg-info"],
    transito: ["Tránsito", "bg-warning"],
    almacenamiento: ["Almacenamiento", "bg-primary"],
  };

  let datos = { cola: [], en_deposito: [], salieron: [] };
  let vista = "cola";

  const esc = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
  const num = (n) => Number(n || 0).toLocaleString("es-AR");
  const fechaHora = (f) => {
    if (!f) return "—";
    const [d, h] = String(f).split(/[ T]/);
    return d.split("-").reverse().join("/") + (h ? " " + h.slice(0, 5) : "");
  };
  const hoy = () => new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
  const modalidad = (m) => {
    const [texto, clase] = MODALIDADES[m] || [m || "—", "bg-secondary"];
    return `<span class="badge ${clase}">${esc(texto)}</span>`;
  };
  const coincide = (fila, campos) => {
    const q = ($("#wp-buscar").val() || "").toLowerCase().trim();
    return !q || campos.some((c) => String(fila[c] || "").toLowerCase().includes(q));
  };
  const vacio = (cols, texto) => `<tr><td colspan="${cols}" class="text-center text-muted py-4">${texto}</td></tr>`;

  function pintarCola() {
    const filas = datos.cola.filter((f) => coincide(f, ["serie", "Origen", "Destino", "recorrido"]));
    $("#wp-cola").html(
      filas.length
        ? filas
            .map(
              (f) => `
          <tr>
            <td>${fechaHora(f.creado_en)}</td>
            <td>${f.tipo === "salida" ? '<span class="badge bg-success-lighten text-success">Salida</span>' : '<span class="badge bg-primary-lighten text-primary">Ingreso</span>'}</td>
            <td><code>${esc(f.serie)}</code></td>
            <td>${esc(f.Origen)}<div class="text-muted small">${esc(f.Destino)}</div></td>
            <td>${esc(f.recorrido || "")}${f.chofer ? `<div class="text-muted small">${esc(f.chofer)}</div>` : ""}</td>
            <td>${
              f.estado === "error"
                ? `<span class="text-danger">${esc(f.ultimo_error || "Error")}</span><div class="text-muted small">Intento ${f.intentos}, próximo ${fechaHora(f.proximo_intento)}</div>`
                : '<span class="text-muted">Enviando…</span>'
            }</td>
          </tr>`
            )
            .join("")
        : vacio(6, datos.cola.length ? "Nada coincide con la búsqueda." : "Todo lo escaneado ya está confirmado por Wepoint.")
    );
  }

  function pintarDeposito() {
    const filas = datos.en_deposito.filter((f) => coincide(f, ["nro_serie", "dueno", "destinatario", "modalidad"]));
    $("#wp-deposito").html(
      filas.length
        ? filas
            .map((b) => {
              const vencido = b.modalidad === "transito" && b.transito_vence && new Date(b.transito_vence.replace(" ", "T")) < new Date();
              return `
          <tr>
            <td><code>${esc(b.nro_serie)}</code></td>
            <td>${esc(b.dueno)}</td>
            <td>${esc(b.destinatario)}</td>
            <td>${modalidad(b.modalidad)}</td>
            <td>${fechaHora(b.ingreso)}</td>
            <td class="${vencido ? "text-danger fw-semibold" : ""}">${b.modalidad === "transito" ? fechaHora(b.transito_vence) + (vencido ? " · vencido" : "") : "—"}</td>
          </tr>`;
            })
            .join("")
        : vacio(6, datos.en_deposito.length ? "Nada coincide con la búsqueda." : "Wepoint no tiene bultos de Caddy en el depósito.")
    );
  }

  function pintarSalieron() {
    const filas = datos.salieron.filter((f) => coincide(f, ["nro_serie", "dueno", "destinatario", "retiro"]));
    $("#wp-salieron").html(
      filas.length
        ? filas
            .map(
              (b) => `
          <tr>
            <td><code>${esc(b.nro_serie)}</code></td>
            <td>${esc(b.dueno)}</td>
            <td>${esc(b.destinatario)}</td>
            <td>${modalidad(b.modalidad)}</td>
            <td>${fechaHora(b.salida)}</td>
            <td>${
              b.estado === "Retirado en depósito"
                ? `Retiró ${esc(b.retiro || "")}`
                : b.egreso
                ? `${esc(b.egreso.recorrido)} <span class="text-muted small">${esc(b.egreso.nro_orden_egreso_bulto)}</span>`
                : "Con el transportista"
            }</td>
          </tr>`
            )
            .join("")
        : vacio(6, datos.salieron.length ? "Nada coincide con la búsqueda." : "No salió nada en esas fechas.")
    );
  }

  function pintar() {
    ({ cola: pintarCola, en_deposito: pintarDeposito, salieron: pintarSalieron })[vista]();
  }

  function cargarCola() {
    return $.post(URL_WEPOINT, { ColaWepoint: 1 }, null, "json").done((r) => {
      datos.cola = r.data || [];
      const errores = datos.cola.filter((f) => f.estado === "error").length;
      const hoyOk = r.confirmados_hoy || {};
      $("#kpi-cola").text(num(datos.cola.length)).toggleClass("text-danger", errores > 0);
      $("#kpi-cola-detalle").text(
        errores ? `${num(errores)} con error · se reintenta solo` : `hoy confirmados: ${num(hoyOk.ingreso)} ingresos, ${num(hoyOk.salida)} salidas`
      );
      $("#wp-badge-cola").text(datos.cola.length || "");
    });
  }

  function cargarDeposito() {
    return $.post(URL_WEPOINT, { BultosWepoint: 1, vista: "en_deposito" }, null, "json")
      .done((r) => {
        datos.en_deposito = r.bultos || [];
        const pm = (r.resumen && r.resumen.por_modalidad) || {};
        $("#kpi-deposito").text(num(r.resumen ? r.resumen.total : 0));
        $("#kpi-deposito-detalle").text(
          `${num(pm.crossdock)} crossdock · ${num(pm.transito)} tránsito · ${num(pm.almacenamiento)} almacenamiento`
        );
        $("#kpi-vencido").text(num(r.resumen ? r.resumen.transito_vencido : 0));
      })
      .fail(() => {
        $("#kpi-deposito").text("?");
        $("#kpi-deposito-detalle").text("Wepoint no responde");
        if (vista === "en_deposito") $("#wp-deposito").html(vacio(6, "Wepoint no responde. Se vuelve a intentar en unos segundos."));
      });
  }

  function cargarSalieron() {
    const desde = $("#wp-desde").val() || hoy();
    const hasta = $("#wp-hasta").val() || desde;
    return $.post(URL_WEPOINT, { BultosWepoint: 1, vista: "salieron", desde, hasta }, null, "json").done((r) => {
      datos.salieron = r.bultos || [];
      if (desde === hoy() && hasta === hoy()) $("#kpi-salieron").text(num(datos.salieron.length));
    });
  }

  function cargarSalieronHoy() {
    if ($("#wp-desde").val() === hoy() && $("#wp-hasta").val() === hoy()) return cargarSalieron();
    return $.post(URL_WEPOINT, { BultosWepoint: 1, vista: "salieron", desde: hoy(), hasta: hoy() }, null, "json").done((r) =>
      $("#kpi-salieron").text(num((r.bultos || []).length))
    );
  }

  let actualizando = false;
  function actualizar() {
    if (actualizando || document.hidden) return;
    actualizando = true;
    // Primero manda lo que haya quedado en la cola, después lee el estado
    $.post(URL_WEPOINT, { ProcesarCola: 1 }).always(() => {
      $.when(cargarCola(), cargarDeposito(), vista === "salieron" ? cargarSalieron() : cargarSalieronHoy()).always(() => {
        actualizando = false;
        pintar();
        $("#wp-actualizado").text("actualizado " + new Date().toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit", second: "2-digit" }));
      });
    });
  }

  $(function () {
    $("#wp-desde, #wp-hasta").val(hoy());
    actualizar();
    setInterval(actualizar, CADA_MS);
    document.addEventListener("visibilitychange", () => !document.hidden && actualizar());

    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function () {
      vista = $(this).data("vista");
      pintar();
    });
    $("#wp-buscar").on("input", pintar);
    $("#wp-desde, #wp-hasta").on("change", () => cargarSalieron().always(pintar));
    $("#wp-reintentar").on("click", function () {
      const $btn = $(this).prop("disabled", true);
      $.post(URL_WEPOINT, { ReintentarCola: 1 }, null, "json")
        .done((r) =>
          Swal.fire({
            icon: r.errores ? "warning" : "success",
            title: r.ocupado ? "Ya se estaba enviando" : "Reintento terminado",
            text: r.ocupado ? "Hay un envío en curso; en unos segundos se actualiza." : `Confirmados: ${r.ingresos} ingresos y ${r.salidas} salidas. Con error: ${r.errores}.`,
            timer: 3000,
          })
        )
        .always(() => {
          $btn.prop("disabled", false);
          $.when(cargarCola(), cargarDeposito()).always(pintar);
        });
    });
  });
})();
