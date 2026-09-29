// Panel de Control (Inicio/Cpanel.php) — pantalla de inicio del sistema.
// Indicadores: php/funcionesCpanel.php (DashboardOperativo, en vivo, cada 30 s). Las
// definiciones de Simples/Flex/MELI y qué se excluye están documentadas en ese endpoint.
// Tablas: php/tablasCpanel.php (Transporte, Logistica, Logistica1, Flota, PreVenta,
// Pendientes), cada 60 s. Acciones: notas internas y "vaciar recorrido" (funcionesCpanel.php).
(function () {
  "use strict";

  const URL_FUNC = "php/funcionesCpanel.php";
  const URL_TABLAS = "php/tablasCpanel.php";
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });

  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const num = (v) => { const n = parseInt(v, 10); return Number.isFinite(n) ? n : 0; };
  const fecha = (f) => (f ? String(f).split("-").reverse().join("/") : "");
  const plural = (n, uno, varios) => `${fmt0.format(n)} ${n === 1 ? uno : varios}`;
  const vacio = (cols, texto) => `<tr><td class="cf-loading" colspan="${cols}">${texto}</td></tr>`;

  // Sesión vencida: funciones.js ya muestra el aviso global ante un 401
  function post(url, data) {
    return $.ajax({ url, type: "POST", data, dataType: "json" });
  }
  function filas(res) {
    return res && Array.isArray(res.data) ? res.data : [];
  }

  // ---------- Encabezado ----------
  function pintarFecha(hora) {
    const hoy = new Date().toLocaleDateString("es-AR", { weekday: "long", day: "numeric", month: "long" });
    $("#cp-fecha").text(`${hoy.charAt(0).toUpperCase() + hoy.slice(1)} · actualizado ${hora} · se actualiza solo`);
  }

  // ---------- Indicadores ----------
  function cargarIndicadores() {
    return post(URL_FUNC, { DashboardOperativo: 1 }).done(function (d) {
      if (!d || d.success != 1) return;
      pintarFecha(d.hora || new Date().toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" }));

      // En vivo
      $("#kpi_en_ruta_total").text(fmt0.format(num(d.en_ruta_total)));
      $("#kpi_en_ruta_foot").text(`Envíos pendientes en ${plural(num(d.recorridos_activos), "recorrido cargado", "recorridos cargados")}`);

      $("#kpi_entregados_total").text(fmt0.format(num(d.entregados_total)));
      let piEnt = `Ayer a esta hora: <b>${fmt0.format(num(d.entregados_ayer))}</b>`;
      if (d.entregados_variacion !== null && d.entregados_variacion !== undefined) {
        const v = num(d.entregados_variacion);
        piEnt += ` <span class="cf-delta ${v >= 0 ? "up" : "down"}"><i class="mdi mdi-arrow-${v >= 0 ? "up" : "down"}"></i>${Math.abs(v)}%</span>`;
      }
      $("#kpi_entregados_foot").html(piEnt);

      const inc = num(d.incidencias_total);
      $("#kpi_incidencias_total").text(fmt0.format(inc)).toggleClass("cf-neg", inc > 0);
      $("#kpi_incidencias_foot").text(
        `${plural(num(d.no_entregados), "no entregado", "no entregados")} · ${plural(num(d.rechazados), "rechazado", "rechazados")} · ${plural(num(d.no_retirados), "no retirado", "no retirados")}`
      );

      // Colectas: estado del retiro + bultos escaneados en el cliente
      $("#kpi_colectas_total").text(fmt0.format(num(d.colectas_total)));
      const partes = [];
      if (num(d.colectas_a_retirar)) partes.push(`<b>${fmt0.format(num(d.colectas_a_retirar))}</b> a retirar`);
      if (num(d.colectas_en_camino)) partes.push(`<b>${fmt0.format(num(d.colectas_en_camino))}</b> en camino`);
      if (num(d.colectas_en_deposito)) partes.push(`<b>${fmt0.format(num(d.colectas_en_deposito))}</b> en depósito`);
      const bultos = num(d.colectas_bultos), esc2 = num(d.colectas_escaneados);
      let piCol = partes.join(" · ") || "Sin colectas hoy";
      if (bultos) {
        piCol += `<br>${fmt0.format(esc2)} de ${plural(bultos, "bulto escaneado", "bultos escaneados")}`;
        if (esc2 < bultos) piCol += ` <span class="cf-delta down" title="Bultos de colectas de hoy sin escanear en el cliente">${fmt0.format(bultos - esc2)} sin escanear</span>`;
      }
      $("#kpi_colectas_foot").html(piCol);

      // Operativo del día (MELI está dentro de Flex)
      // MELI es parte de Flex: se informa como badge dentro de la tarjeta Flex
      const meliPend = num(d.meli_pendientes);
      $("#cp-meli-pend").toggleClass("d-none", meliPend === 0).text(`${fmt0.format(meliPend)} MELI`);

      ["simples", "flex"].forEach(function (t) {
        const ent = num(d[t + "_entregados"]), pend = num(d[t + "_pendientes"]), total = ent + pend;
        const pct = total ? Math.round((ent * 100) / total) : 0;
        const $it = $(`.cp-op-item[data-tipo="${t}"]`);
        $it.find(".cp-op-ent").text(fmt0.format(ent));
        $it.find(".cp-op-total").text(fmt0.format(total));
        $it.find(".cp-op-pend").text(fmt0.format(pend));
        $it.find(".cp-op-pct").text(total ? pct + "%" : "–");
        $it.find(".cp-op-bar > div").css("width", pct + "%");
      });

      // Todavía no salieron (va abajo, junto a la tabla por recorrido)
      let sinSalir = `<b>${fmt0.format(num(d.pendientes_sin_salir))}</b> envíos todavía no salieron`;
      if (num(d.pendientes_atrasados) > 0) {
        sinSalir += ` <span class="cf-delta down" title="Paradas abiertas de envíos con más de 30 días que no salieron">+${fmt0.format(num(d.pendientes_atrasados))} de más de 30 días</span>`;
      }
      $("#cp-sin-salir").html(sinSalir);
    });
  }

  // ---------- Preventa ----------
  function cargarPreventa() {
    return post(URL_TABLAS, { PreVenta: 1 }).done(function (res) {
      const data = filas(res);
      const total = data.reduce((a, r) => a + num(r.Cantidad), 0);
      $("#cp-preventa").toggleClass("d-none", total === 0);
      $("#cp-preventa-count").toggleClass("d-none", total === 0).text(total);
      if (!total) return;
      $("#cp-preventa-titulo").text(`${plural(total, "pedido espera", "pedidos esperan")} en Preventa para ser aceptado${total === 1 ? "" : "s"}`);
      $("#cp-preventa-detalle").html(data.map((r) => `<span class="cp-chip">${esc(r.RazonSocial)} <b>${num(r.Cantidad)}</b></span>`).join(""));
    });
  }

  // ---------- Tablas ----------
  const ESTADO_ORDEN = { Cargada: "success", Alta: "danger", Pendiente: "warning" };

  function cargarTransporte() {
    return post(URL_TABLAS, { Transporte: 1 }).done(function (res) {
      const data = filas(res);
      $("#cp-transporte tbody").html(data.length ? data.map(function (r) {
        const color = ESTADO_ORDEN[r.Estado] || "secondary";
        const choferes = [r.NombreChofer, r.NombreChofer2].filter(Boolean).map(esc).join("<br><small class='text-muted'>") + (r.NombreChofer2 ? "</small>" : "");
        return `<tr>
          <td class="cf-first"><b>${esc(r.Recorrido)}</b><div class="cp-sub">${esc(r.Nombre)}</div></td>
          <td>${esc(r.NumerodeOrden)}</td>
          <td>${fecha(r.Fecha)}<div class="cp-sub">${esc(String(r.Hora || "").slice(0, 5))}</div></td>
          <td><span class="cp-patente">${esc(r.Patente)}</span></td>
          <td class="text-start">${choferes}</td>
          <td><span class="cp-estado cp-estado-${color}">${esc(r.Estado)}</span></td>
          <td>${botonVer(r.Recorrido, r.Nombre)}</td>
        </tr>`;
      }).join("") : vacio(7, "No hay órdenes de salida abiertas."));
    });
  }

  function botonVer(recorrido, nombre) {
    return `<button type="button" class="btn btn-sm btn-light cp-ver" data-recorrido="${esc(recorrido)}" data-nombre="${esc(nombre || "")}">
      <i class="mdi mdi-format-list-bulleted"></i> Ver envíos</button>`;
  }

  function cargarHojasDeRuta() {
    return post(URL_TABLAS, { Logistica: 1 }).done(function (res) {
      const data = filas(res);
      $("#cp-hdr tbody").html(data.length ? data.map((r) => `<tr>
          <td class="cf-first"><b>${esc(r.Recorrido)}</b><div class="cp-sub">${esc(r.Nombre)}</div></td>
          <td><span class="cp-patente">${esc(r.Dominio)}</span><div class="cp-sub">${esc(r.Marca)}</div></td>
          <td class="text-start">${esc(r.Chofer)}</td>
          <td><span class="cp-pill">${fmt0.format(num(r.id))}</span></td>
          <td>${botonVer(r.Recorrido, r.Nombre)}</td>
        </tr>`).join("") : vacio(5, "No hay hojas de ruta con paradas abiertas."));
    });
  }

  function cargarPendientesPorRecorrido() {
    return post(URL_TABLAS, { Logistica1: 1 }).done(function (res) {
      const data = filas(res);
      $("#cp-pendientes-rec tbody").html(data.length ? data.map((r) => `<tr>
          <td class="cf-first"><span class="cp-dot" style="background:#${esc(String(r.Color || "adb5bd").replace("#", ""))}"></span><b>${esc(r.Recorrido)}</b><div class="cp-sub">${esc(r.Nombre)}</div></td>
          <td class="text-start">${esc(r.Zona)}</td>
          <td><span class="cp-pill">${fmt0.format(num(r.id))}</span></td>
          <td class="text-nowrap">${botonVer(r.Recorrido, r.Nombre)}
            ${String(r.Recorrido) === "80" ? "" : `<button type="button" class="btn btn-sm btn-light text-danger cp-vaciar" data-recorrido="${esc(r.Recorrido)}" data-cantidad="${num(r.id)}" title="Mandar todo al Depósito (recorrido 80)"><i class="mdi mdi-tray-arrow-down"></i></button>`}</td>
        </tr>`).join("") : vacio(4, "No hay envíos pendientes."));
    });
  }

  function cargarFlota() {
    return post(URL_TABLAS, { Flota: 1 }).done(function (res) {
      const data = filas(res);
      const color = (e) => (e === "Disponible" ? "success" : e === "En Taller" || e === "Otro" ? "warning" : "danger");
      $("#cp-flota tbody").html(data.length ? data.map((r) => `<tr>
          <td class="cf-first">${esc(r.Marca)}</td>
          <td><span class="cp-patente">${esc(r.Dominio)}</span></td>
          <td>${esc(r.Ano)}</td>
          <td>${fmt0.format(num(r.Kilometros))} km</td>
          <td><span class="cp-estado cp-estado-${color(r.Estado)}">${esc(r.Estado)}</span></td>
        </tr>`).join("") : vacio(5, "No hay vehículos operativos."));
    });
  }

  // ---------- Modal de envíos pendientes ----------
  let recorridoModal = null;
  let nombreModal = "";

  function abrirPendientes(recorrido, nombre) {
    recorridoModal = recorrido;
    nombreModal = nombre || "";
    $("#cp-modal-titulo").text(`Envíos pendientes · Recorrido ${recorrido}`);
    $("#cp-modal-sub").text(nombre || "");
    $("#cp-imprimir-remitos").attr("href", "/SistemaTriangular/Servicios/Informes/Remitopdf.php?Recorrido=" + encodeURIComponent(recorrido));
    $("#cp-tabla-pendientes tbody").html(vacio(5, "Cargando…"));
    // focus:false: si no, el modal atrapa el foco y no deja escribir en el cuadro de la nota (SweetAlert)
    bootstrap.Modal.getOrCreateInstance(document.getElementById("cp-modal-pendientes"), { focus: false }).show();
    cargarPendientesModal();
  }

  function cargarPendientesModal() {
    return post(URL_TABLAS, { Pendientes: 1, id: recorridoModal }).done(function (res) {
      const vistos = {};
      const data = filas(res).filter((r) => (vistos[r.Seguimiento] ? false : (vistos[r.Seguimiento] = true)));
      $("#cp-modal-sub").text([nombreModal, data.length ? plural(data.length, "envío", "envíos") : ""].filter(Boolean).join(" · "));
      $("#cp-tabla-pendientes tbody").html(data.length ? data.map((r) => `<tr>
          <td class="cf-first"><a href="javascript:void(0)" data-tracking-panel data-id="${esc(r.Seguimiento)}" class="cp-codigo">${esc(r.Seguimiento)}</a></td>
          <td>${fecha(r.Fecha)}</td>
          <td class="text-start">${esc(r.Origen)}<div class="cp-sub">${esc(r.DomicilioOrigen)}</div></td>
          <td class="text-start">${esc(r.Destino)}<div class="cp-sub">${esc(r.DomicilioDestino)}</div></td>
          <td class="text-start cp-nota">
            <button type="button" class="btn btn-sm btn-link p-0 cp-nota-editar" data-id="${num(r.id)}" data-nota="${esc(r.Notas || "")}" title="Editar nota interna"><i class="mdi mdi-pencil-outline"></i></button>
            <span>${esc(r.Notas || "")}</span>
          </td>
        </tr>`).join("") : vacio(5, "Este recorrido no tiene envíos pendientes."));
    });
  }

  // Nota interna (solo para operaciones)
  function editarNota(id, notaActual) {
    Swal.fire({
      title: "Nota interna",
      text: "Solo la ve el sector operaciones.",
      input: "textarea",
      inputValue: notaActual,
      inputAttributes: { maxlength: 225, placeholder: "Máximo 225 caracteres" },
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      showLoaderOnConfirm: true,
      preConfirm: (texto) =>
        post(URL_FUNC, { AgregarNotas: 1, id, notas: texto })
          .then((r) => { if (!r || r.success != 1) throw new Error((r && r.error) || "No se pudo guardar"); })
          .catch((e) => Swal.showValidationMessage(e.message || "No se pudo guardar")),
    }).then((r) => { if (r.isConfirmed) cargarPendientesModal(); });
  }

  // Mandar todos los envíos pendientes de un recorrido al Depósito (recorrido 80)
  function vaciarRecorrido(recorrido, cantidad) {
    Swal.fire({
      icon: "warning",
      title: `¿Vaciar el recorrido ${recorrido}?`,
      html: `Se van a mover <b>${plural(cantidad, "envío pendiente", "envíos pendientes")}</b> al <b>Depósito (recorrido 80)</b>.`,
      showCancelButton: true,
      confirmButtonText: "Sí, mover al Depósito",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#fa5c7c",
      showLoaderOnConfirm: true,
      preConfirm: () =>
        post(URL_FUNC, { VaciarRecorrido: 1, Recorrido: recorrido })
          .then((r) => { if (!r || r.success != 1) throw new Error((r && r.error) || "No se pudo mover"); })
          .catch((e) => Swal.showValidationMessage(e.message || "No se pudo mover")),
    }).then(function (r) {
      if (!r.isConfirmed) return;
      Swal.fire({ icon: "success", title: "Listo", text: `Los envíos del recorrido ${recorrido} pasaron al Depósito.`, timer: 2200, showConfirmButton: false });
      refrescarTodo();
    });
  }

  // ---------- Ciclo ----------
  function refrescarTablas() {
    cargarPreventa();
    cargarTransporte();
    cargarHojasDeRuta();
    cargarPendientesPorRecorrido();
    cargarFlota();
  }
  function refrescarTodo() {
    cargarIndicadores();
    refrescarTablas();
  }

  $(function () {
    refrescarTodo();
    setInterval(function () { if (!document.hidden) cargarIndicadores(); }, 30000);
    setInterval(function () { if (!document.hidden) refrescarTablas(); }, 60000);
    document.addEventListener("visibilitychange", function () { if (!document.hidden) refrescarTodo(); });

    $(document).on("click", ".cp-ver", function () { abrirPendientes(this.dataset.recorrido, this.dataset.nombre); });
    $(document).on("click", ".cp-vaciar", function () { vaciarRecorrido(this.dataset.recorrido, num(this.dataset.cantidad)); });
    $(document).on("click", ".cp-nota-editar", function () { editarNota(num(this.dataset.id), this.dataset.nota || ""); });
  });
})();
