// Preventa (Ventas/Pendientes.php)
// Datos y acciones: Procesos/php/preventa.php. Aceptar: ../AgregarRepoVentaWeb.php (crea venta,
// guía, hoja de ruta y seguimiento). Se acepta de a tandas chicas y después se consulta
// EstadoAceptacion para mostrar qué quedó cargado y con qué código (antes el cartel de
// "Actualizando" se cerraba enseguida y no se sabía qué pasó). Las preventas sin un recorrido
// válido de Caddy no se pueden aceptar hasta asignarles uno.
(function () {
  "use strict";

  const URL = "Procesos/php/preventa.php";
  const TANDA = 10;
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const num = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };
  const plural = (n, uno, varios) => `${fmt0.format(n)} ${n === 1 ? uno : varios}`;
  const fecha = (f) => (f && f !== "0000-00-00" ? String(f).slice(0, 10).split("-").reverse().join("/") : "");
  const SIN = "__sin__";

  let filas = [];
  let recorridos = [];
  const seleccion = new Set();
  const filtro = { texto: "", origen: "", rec: "" };

  function post(data) {
    return $.ajax({ url: URL, type: "POST", data, dataType: "json" });
  }

  // Clave de recorrido de una fila: su número si es un recorrido válido de Caddy, o SIN
  const recKey = (f) => (f.RecorridoNombre ? String(f.Recorrido) : SIN);

  // ---------- Carga ----------
  function cargar() {
    $("#pv-actualizar .mdi").addClass("mdi-spin");
    return post({ datos: 1 })
      .done(function (r) {
        filas = (r && r.data) || [];
        const ids = new Set(filas.map((f) => String(f.id)));
        [...seleccion].forEach((id) => { if (!ids.has(id)) seleccion.delete(id); });
        $("#pv-sub").text(`Pedidos que esperan ser aceptados · actualizado ${new Date().toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" })}`);
        pintarTodo();
      })
      .fail(() => $("#pv-tabla tbody").html('<tr><td class="cf-loading" colspan="9">No se pudo cargar la preventa. Probá con Actualizar.</td></tr>'))
      .always(() => $("#pv-actualizar .mdi").removeClass("mdi-spin"));
  }

  function pintarTodo() {
    pintarKpis();
    pintarFiltros();
    pintarGrupos();
    pintarTabla();
  }

  function pintarKpis() {
    const bultos = filas.reduce((a, f) => a + num(f.Cantidad), 0);
    const clientes = {};
    filas.forEach((f) => (clientes[f.RazonSocial] = (clientes[f.RazonSocial] || 0) + 1));
    const top = Object.entries(clientes).sort((a, b) => b[1] - a[1]).slice(0, 2).map(([c, n]) => `${esc(c)} (${n})`).join(" · ");
    $("#kpi-total").text(fmt0.format(filas.length));
    $("#kpi-total-pie").text(plural(bultos, "bulto", "bultos"));
    $("#kpi-clientes").text(fmt0.format(Object.keys(clientes).length));
    $("#kpi-clientes-pie").html(top || "&nbsp;");
    const sin = filas.filter((f) => recKey(f) === SIN).length;
    $("#kpi-sinrec").text(fmt0.format(sin)).toggleClass("cf-neg", sin > 0);
    $("#kpi-dup").text(fmt0.format(filas.filter((f) => num(f.Duplicados) > 0).length));
  }

  function pintarFiltros() {
    const origenes = [...new Set(filas.map((f) => f.RazonSocial))].sort();
    $("#pv-f-origen").html('<option value="">Todos los clientes</option>' + origenes.map((o) => `<option value="${esc(o)}">${esc(o)}</option>`).join("")).val(filtro.origen);
    const recs = grupos().map((g) => `<option value="${esc(g.key)}">${g.key === SIN ? "Sin recorrido válido" : esc(g.key + " · " + g.nombre)} (${g.filas.length})</option>`);
    $("#pv-f-rec").html('<option value="">Todos los recorridos</option>' + recs.join("")).val(filtro.rec);
    if (filtro.origen && !origenes.includes(filtro.origen)) filtro.origen = "";
  }

  function grupos() {
    const m = new Map();
    filas.forEach((f) => {
      const k = recKey(f);
      if (!m.has(k)) m.set(k, { key: k, nombre: f.RecorridoNombre || "", filas: [] });
      m.get(k).filas.push(f);
    });
    // Primero los que no tienen recorrido (hay que resolverlos), después por cantidad
    return [...m.values()].sort((a, b) => (a.key === SIN ? -1 : b.key === SIN ? 1 : b.filas.length - a.filas.length));
  }

  function pintarGrupos() {
    const gs = grupos();
    if (!gs.length) {
      $("#pv-grupos").html('<div class="cf-loading">No hay pedidos en Preventa. 🎉</div>');
      return;
    }
    $("#pv-grupos").html(gs.map(function (g) {
      const clientes = {};
      g.filas.forEach((f) => (clientes[f.RazonSocial] = (clientes[f.RazonSocial] || 0) + 1));
      const chips = Object.entries(clientes).map(([c, n]) => `<span class="cp-chip">${esc(c)} <b>${n}</b></span>`).join("");
      const sin = g.key === SIN;
      return `<div class="pv-grupo ${sin ? "pv-grupo-sin" : ""} ${filtro.rec === g.key ? "activo" : ""}">
        <div class="pv-grupo-head">
          <div><div class="pv-grupo-num">${sin ? "Sin recorrido válido" : "Recorrido " + esc(g.key)}</div>
               <div class="pv-grupo-nombre">${sin ? "Asignales un recorrido para poder aceptarlos" : esc(g.nombre)}</div></div>
          <div class="pv-grupo-n">${fmt0.format(g.filas.length)}<small>pedidos</small></div>
        </div>
        <div class="pv-grupo-clientes">${chips}</div>
        <div class="pv-grupo-acc">
          <button type="button" class="btn btn-sm btn-light pv-g-ver" data-key="${esc(g.key)}"><i class="mdi mdi-eye-outline"></i> Ver</button>
          ${sin
            ? `<button type="button" class="btn btn-sm btn-danger pv-g-asignar" data-key="${esc(g.key)}"><i class="mdi mdi-map-marker-plus-outline"></i> Asignar recorrido</button>`
            : `<button type="button" class="btn btn-sm btn-success pv-g-aceptar" data-key="${esc(g.key)}"><i class="mdi mdi-check-all"></i> Aceptar ${fmt0.format(g.filas.length)}</button>`}
        </div>
      </div>`;
    }).join(""));
  }

  function visibles() {
    const t = filtro.texto;
    return filas.filter((f) => {
      if (filtro.origen && f.RazonSocial !== filtro.origen) return false;
      if (filtro.rec && recKey(f) !== filtro.rec) return false;
      if (!t) return true;
      return [f.RazonSocial, f.ClienteDestino, f.DomicilioDestino, f.LocalidadDestino, f.idProveedor, f.Observaciones, f.NumeroVenta, f.order_id, f.shipments_id]
        .join(" ").toLowerCase().includes(t);
    });
  }

  function pintarTabla() {
    const vs = visibles();
    const hayFiltro = !!(filtro.texto || filtro.origen || filtro.rec);
    $("#pv-limpiar").toggleClass("d-none", !hayFiltro);
    $("#pv-conteo").text(hayFiltro ? `${vs.length} de ${filas.length}` : plural(filas.length, "pedido", "pedidos"));
    $("#pv-tabla tbody").html(vs.length ? vs.map(function (f) {
      const id = String(f.id);
      const valido = recKey(f) !== SIN;
      const tags = [];
      if (num(f.order_id) > 0 || num(f.shipments_id) > 0) tags.push(`<span class="pv-tag ml" title="Envío ${esc(f.shipments_id)}">Mercado Libre</span>`);
      if (num(f.Cobranza) > 0) tags.push(`<span class="pv-tag cobro">Cobrar $ ${fmt0.format(num(f.Cobranza))}</span>`);
      if (num(f.Duplicados) > 0) tags.push(`<span class="pv-tag dup" title="Hay otra preventa del mismo cliente para el mismo destinatario">Posible duplicado</span>`);
      if (f.TipoDeComprobante) tags.push(`<span class="pv-tag">${esc(f.TipoDeComprobante)}</span>`);
      const valor = num(f.Total) > 0 ? `$ ${fmt0.format(num(f.Total))}` : num(f.ValorDeclarado) > 0 ? `<span class="text-muted" title="Valor declarado">VD $ ${fmt0.format(num(f.ValorDeclarado))}</span>` : "–";
      const hora = (f.Hora || "").slice(0, 5);
      return `<tr class="${seleccion.has(id) ? "sel" : ""}" data-id="${id}">
        <td class="cf-first pv-chk-col"><input type="checkbox" class="form-check-input pv-chk" value="${id}" ${seleccion.has(id) ? "checked" : ""}></td>
        <td class="text-start pv-cli" title="${esc(f.DomicilioOrigen)}"><b>${esc(f.RazonSocial)}</b><div class="cp-sub">${esc(f.DomicilioOrigen)}</div></td>
        <td class="text-start"><b>${f.idProveedor ? `<span class="text-muted">[${esc(f.idProveedor)}]</span> ` : ""}${esc(f.ClienteDestino) || '<span class="text-danger">Sin destinatario</span>'}</b>
          <div class="cp-sub">${esc(f.DomicilioDestino)} ${esc(f.LocalidadDestino)}</div>
          ${f.Observaciones ? `<div class="pv-obs">${esc(f.Observaciones)}</div>` : ""}<div>${tags.join("")}</div></td>
        <td>${fecha(f.Fecha)}<div class="cp-sub">${esc(hora)}</div></td>
        <td>${fecha(f.FechaEntrega) || '<span class="text-muted">–</span>'}</td>
        <td>${fmt0.format(num(f.Cantidad))}</td>
        <td>${valor}</td>
        <td><button type="button" class="pv-rec ${valido ? "" : "invalido"}" data-id="${id}" title="Cambiar recorrido">
          ${valido ? `${esc(f.Recorrido)} <small>${esc(f.RecorridoNombre)}</small>` : `<i class="mdi mdi-alert-outline"></i> ${f.Recorrido ? esc(f.Recorrido) + " (no existe)" : "Asignar"}`}</button></td>
        <td><button type="button" class="btn btn-sm btn-light text-danger pv-eliminar" data-id="${id}" title="Eliminar"><i class="mdi mdi-trash-can-outline"></i></button></td>
      </tr>`;
    }).join("") : `<tr><td class="cf-loading" colspan="9">${filas.length ? "No hay pedidos con esos filtros." : "No hay pedidos en Preventa."}</td></tr>`);
    const idsVis = vs.map((f) => String(f.id));
    $("#pv-todos").prop("checked", idsVis.length > 0 && idsVis.every((id) => seleccion.has(id)));
    pintarBarra();
  }

  function pintarBarra() {
    const sel = filas.filter((f) => seleccion.has(String(f.id)));
    const sinRec = sel.filter((f) => recKey(f) === SIN).length;
    $("#pv-sel-n").text(sel.length);
    $("#pv-sel-detalle").text(sinRec ? `· ${plural(sinRec, "sin recorrido válido", "sin recorrido válido")}` : "");
    $("#pv-barra").toggleClass("visible", sel.length > 0);
  }

  // ---------- Recorrido ----------
  function cargarRecorridos() {
    if (recorridos.length) return $.Deferred().resolve().promise();
    return post({ ListaRecorridos: 1 }).done((r) => { recorridos = (r && r.recorridos) || []; });
  }

  function elegirRecorrido(ids, titulo) {
    cargarRecorridos().done(function () {
      const opciones = recorridos.map((r) => `<option value="${r.Numero}">${r.Numero} · ${esc(r.Nombre)}</option>`).join("");
      Swal.fire({
        title: titulo,
        html: `<input type="search" class="form-control pv-filtro-rec" id="sw-filtro" placeholder="Buscar recorrido por número o nombre">
               <select class="form-select" id="sw-rec" size="8">${opciones}</select>`,
        showCancelButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        focusConfirm: false,
        didOpen: () => {
          const $f = $("#sw-filtro"), $s = $("#sw-rec");
          $f.trigger("focus").on("input", function () {
            const q = this.value.toLowerCase();
            $s.find("option").each(function () { this.hidden = q && !this.textContent.toLowerCase().includes(q); });
            const primera = $s.find("option:not([hidden])").first().val();
            if (primera) $s.val(primera);
          });
          $s.on("dblclick", () => Swal.clickConfirm());
        },
        showLoaderOnConfirm: true,
        preConfirm: () => {
          const r = $("#sw-rec").val();
          if (!r) { Swal.showValidationMessage("Elegí un recorrido"); return false; }
          return post({ ActualizaRecorrido_all: 1, r, id: ids })
            .then((res) => { if (!res || res.success != 1) throw new Error((res && res.error) || "No se pudo actualizar"); return res; })
            .catch((e) => Swal.showValidationMessage(e.message || "No se pudo actualizar"));
        },
      }).then(function (res) {
        if (!res.isConfirmed) return;
        toast("success", "Recorrido actualizado", plural(res.value.actualizados, "pedido", "pedidos") + ` al recorrido ${res.value.Recorrido}.`);
        cargar();
      });
    });
  }

  // ---------- Eliminar ----------
  function eliminar(ids) {
    Swal.fire({
      icon: "warning",
      title: ids.length === 1 ? "¿Eliminar este pedido de Preventa?" : `¿Eliminar ${ids.length} pedidos de Preventa?`,
      text: "No se crea ninguna venta. El pedido deja de aparecer acá.",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#fa5c7c",
      showLoaderOnConfirm: true,
      preConfirm: () =>
        post({ Eliminar_all: 1, id: ids })
          .then((r) => { if (!r || r.success != 1) throw new Error((r && r.error) || "No se pudo eliminar"); return r; })
          .catch((e) => Swal.showValidationMessage(e.message || "No se pudo eliminar")),
    }).then(function (res) {
      if (!res.isConfirmed) return;
      ids.forEach((id) => seleccion.delete(String(id)));
      toast("success", "Eliminados", plural(res.value.actualizados, "pedido eliminado", "pedidos eliminados") + ".");
      cargar();
    });
  }

  // ---------- Aceptar ----------
  function aceptar(ids) {
    const sel = filas.filter((f) => ids.includes(String(f.id)));
    const validas = sel.filter((f) => recKey(f) !== SIN);
    const sinRec = sel.length - validas.length;
    if (!validas.length) {
      Swal.fire("Falta el recorrido", "Ninguno de los pedidos elegidos tiene un recorrido válido. Asignales uno primero.", "warning");
      return;
    }
    const porRec = {};
    validas.forEach((f) => (porRec[f.Recorrido + " · " + f.RecorridoNombre] = (porRec[f.Recorrido + " · " + f.RecorridoNombre] || 0) + 1));
    Swal.fire({
      icon: "question",
      title: `¿Aceptar ${plural(validas.length, "pedido", "pedidos")}?`,
      html: `Se crean la venta, la guía, la hoja de ruta y el seguimiento de cada uno.<br><br>` +
        Object.entries(porRec).map(([r, n]) => `Recorrido <b>${esc(r)}</b>: ${n}`).join("<br>") +
        (sinRec ? `<br><br><span class="text-danger">${plural(sinRec, "pedido queda afuera", "pedidos quedan afuera")} por no tener recorrido válido.</span>` : ""),
      showCancelButton: true,
      confirmButtonText: "Sí, aceptar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#0acf97",
    }).then((r) => { if (r.isConfirmed) procesar(validas); });
  }

  async function procesar(lista) {
    const total = lista.length;
    Swal.fire({
      title: "Aceptando pedidos…",
      html: `<div class="pv-progreso"><div id="pv-prog"></div></div><div id="pv-prog-txt">0 de ${total}</div>`,
      allowOutsideClick: false,
      showConfirmButton: false,
    });
    const aceptadas = [];
    const fallidas = [];
    for (let i = 0; i < total; i += TANDA) {
      const tanda = lista.slice(i, i + TANDA);
      const ids = tanda.map((f) => String(f.id));
      try {
        await $.ajax({ url: "AgregarRepoVentaWeb.php", type: "POST", data: { id: ids, recorrido_t: tanda.map((f) => String(f.Recorrido)) } });
      } catch (e) {
        // Aunque el alta devuelva error, se verifica abajo qué quedó cargado
      }
      try {
        const est = await post({ EstadoAceptacion: 1, id: ids });
        (est.estado || []).forEach((e) => (e.aceptada ? aceptadas : fallidas).push(e));
      } catch (e) {
        tanda.forEach((f) => fallidas.push({ id: f.id, destino: f.ClienteDestino, codigo: "" }));
      }
      const hechos = Math.min(i + TANDA, total);
      $("#pv-prog").css("width", Math.round((hechos * 100) / total) + "%");
      $("#pv-prog-txt").text(`${hechos} de ${total}`);
    }

    if (aceptadas.length) {
      const ids = aceptadas.map((e) => String(e.id));
      try { enviar_webhook_woocomerce(ids); } catch (e) { console.error(e); }
      try { enviar_webhook_tiendanube(ids); } catch (e) { console.error(e); }
    }
    lista.forEach((f) => seleccion.delete(String(f.id)));

    const lineaCodigo = (e) => `<a href="javascript:void(0)" data-tracking-panel data-id="${esc(e.codigo)}"><b>${esc(e.codigo)}</b></a> ${esc(e.destino)}`;
    Swal.fire({
      icon: fallidas.length ? (aceptadas.length ? "warning" : "error") : "success",
      title: fallidas.length ? `Se aceptaron ${aceptadas.length} de ${total}` : `Se aceptaron los ${total} pedidos`,
      html: `<div class="pv-resultado">` +
        (aceptadas.length ? `<b>Aceptados:</b><br>${aceptadas.map(lineaCodigo).join("<br>")}` : "") +
        (fallidas.length ? `<br><br><b class="text-danger">No se pudieron aceptar (siguen en Preventa):</b><br>${fallidas.map((e) => esc(e.destino || "Pedido " + e.id)).join("<br>")}` : "") +
        `</div>`,
      confirmButtonText: "Listo",
    });
    cargar();
  }

  // ---------- Eventos ----------
  $(function () {
    cargar();
    setInterval(() => { if (!document.hidden && !seleccion.size && !Swal.isVisible()) cargar(); }, 60000);

    $("#pv-actualizar").on("click", cargar);
    $("#pv-buscar").on("input", function () { filtro.texto = this.value.trim().toLowerCase(); pintarTabla(); });
    $("#pv-f-origen").on("change", function () { filtro.origen = this.value; pintarTabla(); });
    $("#pv-f-rec").on("change", function () { filtro.rec = this.value; pintarGrupos(); pintarTabla(); });
    $("#pv-limpiar").on("click", function () {
      filtro.texto = filtro.origen = filtro.rec = "";
      $("#pv-buscar").val(""); $("#pv-f-origen, #pv-f-rec").val("");
      pintarGrupos(); pintarTabla();
    });

    $("#pv-tabla").on("change", ".pv-chk", function () {
      this.checked ? seleccion.add(this.value) : seleccion.delete(this.value);
      $(this).closest("tr").toggleClass("sel", this.checked);
      const idsVis = visibles().map((f) => String(f.id));
      $("#pv-todos").prop("checked", idsVis.every((id) => seleccion.has(id)));
      pintarBarra();
    });
    $("#pv-todos").on("change", function () {
      visibles().forEach((f) => (this.checked ? seleccion.add(String(f.id)) : seleccion.delete(String(f.id))));
      pintarTabla();
    });
    $("#pv-tabla").on("click", ".pv-rec", function () { elegirRecorrido([this.dataset.id], "Recorrido del pedido"); });
    $("#pv-tabla").on("click", ".pv-eliminar", function () { eliminar([this.dataset.id]); });

    $("#pv-grupos").on("click", ".pv-g-ver", function () {
      filtro.rec = filtro.rec === this.dataset.key ? "" : this.dataset.key;
      $("#pv-f-rec").val(filtro.rec);
      pintarGrupos(); pintarTabla();
      if (filtro.rec) document.getElementById("pv-tabla").scrollIntoView({ behavior: "smooth", block: "start" });
    });
    $("#pv-grupos").on("click", ".pv-g-aceptar", function () {
      const k = this.dataset.key;
      aceptar(filas.filter((f) => recKey(f) === k).map((f) => String(f.id)));
    });
    $("#pv-grupos").on("click", ".pv-g-asignar", function () {
      const ids = filas.filter((f) => recKey(f) === SIN).map((f) => String(f.id));
      elegirRecorrido(ids, `Recorrido para ${plural(ids.length, "pedido", "pedidos")} sin recorrido`);
    });

    $("#pv-sel-limpiar").on("click", () => { seleccion.clear(); pintarTabla(); });
    $("#pv-sel-eliminar").on("click", () => eliminar([...seleccion]));
    $("#pv-sel-rec").on("click", () => elegirRecorrido([...seleccion], `Recorrido para ${plural(seleccion.size, "pedido", "pedidos")}`));
    $("#pv-sel-aceptar").on("click", () => aceptar([...seleccion]));
  });
})();
