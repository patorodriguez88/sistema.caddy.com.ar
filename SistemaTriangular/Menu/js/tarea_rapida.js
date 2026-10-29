// Nueva tarea desde cualquier pantalla: ícono al lado de la campanita (Menu/head.html) que abre un
// panel a la derecha. Usa la misma API que la pantalla de Tareas (Empleados/Procesos/php/tareas_api.php:
// init para proyectos y usuarios, guardar_tarea para crearla, con el mail al responsable y las
// @menciones). JS sin jQuery: head.html se incluye antes que vendor.min.js en todas las páginas.
(function () {
  "use strict";

  const API = "/SistemaTriangular/Empleados/Procesos/php/tareas_api.php";
  const panel = document.getElementById("tarea-rapida-offcanvas");
  if (!panel) return;
  const $ = (id) => document.getElementById(id);
  let datos = null; // respuesta de init (se pide una vez, al abrir el panel)

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  }


  function api(params) {
    const body = new FormData();
    Object.keys(params).forEach((k) => body.append(k, params[k]));
    // X-Requested-With: así Conexioni.php responde 401 (y no redirige al login) si la sesión venció.
    return fetch(API, { method: "POST", body, credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } })
      .then((r) => r.text().then((texto) => ({ r, texto })))
      .then(({ r, texto }) => {
        if (r.status === 401 || r.redirected || /^\s*</.test(texto)) {
          location.href = "/SistemaTriangular/inicio.php";
          throw new Error("Tu sesión venció: volvé a ingresar.");
        }
        try {
          return JSON.parse(texto);
        } catch (e) {
          throw new Error("El servidor no respondió bien (HTTP " + r.status + (texto ? "" : ", respuesta vacía") + "). Avisale a sistemas.");
        }
      });
  }

  function mensaje(tipo, html) {
    $("tr_mensaje").innerHTML = html ? '<div class="alert alert-' + tipo + ' py-2 mb-0">' + html + "</div>" : "";
  }

  function cargarCombos() {
    // Solo proyectos donde puedo crear: los de todos y los que participo.
    const proyectos = (datos.proyectos || []).filter((p) => p.todos || p.participo);
    // El último que usé; si tengo uno solo, ese.
    const ultimo = localStorageGet("tr_proyecto") || (proyectos.length === 1 ? String(proyectos[0].id) : "");
    // data-color / data-ini: los usa SelectAsana (fecha_asana.js) para el cuadradito y el avatar
    $("tr_proyecto").innerHTML =
      '<option value=""></option>' +
      proyectos.map((p) => '<option value="' + p.id + '" data-color="' + esc(p.color) + '"' + (String(p.id) === ultimo ? " selected" : "") + ">" + esc(p.nombre) + "</option>").join("");
    $("tr_responsable").innerHTML = (datos.usuarios || [])
      .map((u) => '<option value="' + u.id + '" data-ini="' + esc(u.iniciales) + '"' + (u.id === datos.yo ? " selected" : "") + ">" + esc(u.nombre) + (u.id === datos.yo ? " (yo)" : "") + "</option>")
      .join("");
    refrescarSelects();
  }

  function refrescarSelects() {
    [selResp, selProy].forEach((s) => s && s.refrescar());
  }

  function localStorageGet(k) {
    try { return localStorage.getItem(k) || ""; } catch (e) { return ""; }
  }
  function localStorageSet(k, v) {
    try { localStorage.setItem(k, v); } catch (e) {}
  }

  function limpiar() {
    $("tr_titulo").value = "";
    $("tr_descripcion").value = "";
    $("tr_vence").value = "";
    $("tr_repetir").value = "";
    $("tr_rep_cada").value = 1;
    $("tr_rep_dias").value = "";
    if (fecha) fecha.refrescar();
    if (datos) $("tr_responsable").value = String(datos.yo);
    refrescarSelects();
    autoAlto();
  }

  // El título crece con el texto (como en el panel de Tareas)
  function autoAlto() {
    const t = $("tr_titulo");
    t.style.height = "auto";
    t.style.height = t.scrollHeight + "px";
  }
  $("tr_titulo").addEventListener("input", autoAlto);
  $("tr_titulo").addEventListener("keydown", (e) => {
    if (e.key === "Enter") { e.preventDefault(); $("tr_descripcion").focus(); }
  });

  panel.addEventListener("show.bs.offcanvas", function () {
    mensaje("", "");
    if (datos) return;
    $("tr_guardar").disabled = true;
    mensaje("light", '<i class="mdi mdi-loading mdi-spin"></i> Cargando proyectos…');
    api({ accion: "init" })
      .then((r) => {
        if (!r.success) throw new Error(r.error || "No se pudieron cargar los proyectos.");
        datos = r;
        cargarCombos();
        mensaje("", "");
        $("tr_guardar").disabled = false;
      })
      .catch((e) => mensaje("danger", esc(e.message)));
  });
  panel.addEventListener("shown.bs.offcanvas", () => $("tr_titulo").focus());
  panel.addEventListener("hide.bs.offcanvas", () => [fecha, selResp, selProy].forEach((c) => c && c.cerrar()));

  // Fecha de entrega + Repetir estilo Asana (fecha_asana.js, cargado antes que este archivo)
  // ---------- Globo de novedad (una sola vez por usuario, Menu/php/avisos.php) ----------
  const AVISO = "icono_tareas";
  function marcarAvisoVisto() {
    const body = new FormData();
    body.append("clave", AVISO);
    fetch("/SistemaTriangular/Menu/php/avisos.php", { method: "POST", body, credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } }).catch(() => {});
  }
  function mostrarGlobo() {
    const icono = $("tarea_rapida_toggle");
    if (!icono || !icono.offsetParent) return; // ícono oculto (pantalla chica, etc.)
    const st = document.createElement("style");
    st.textContent = `
      .tr-globo { position: fixed; z-index: 1990; width: 300px; background: #313a46; color: #fff; border-radius: 12px; padding: 14px 16px 12px; box-shadow: 0 14px 34px -10px rgba(0,0,0,.45); font-size: .86rem; line-height: 1.45; animation: trGlobo .25s ease-out; }
      .tr-globo::before { content: ""; position: absolute; top: -7px; left: var(--tr-flecha, 24px); width: 14px; height: 14px; background: #313a46; transform: rotate(45deg); border-radius: 2px; }
      .tr-globo b { display: block; font-size: .95rem; margin-bottom: 4px; }
      .tr-globo-acc { display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; }
      .tr-globo-acc button { border: 0; border-radius: 8px; padding: 5px 12px; font-size: .82rem; font-weight: 600; }
      .tr-globo-probar { background: #727cf5; color: #fff; }
      .tr-globo-ok { background: rgba(255,255,255,.12); color: #fff; }
      .tr-pulso { animation: trPulso 1.4s ease-out 3; border-radius: 50%; }
      @keyframes trGlobo { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
      @keyframes trPulso { 0% { box-shadow: 0 0 0 0 rgba(114,124,245,.55); } 100% { box-shadow: 0 0 0 14px rgba(114,124,245,0); } }`;
    document.head.appendChild(st);
    const g = document.createElement("div");
    g.className = "tr-globo";
    g.setAttribute("role", "dialog");
    g.innerHTML =
      "<b>Nuevo: tareas desde cualquier pantalla</b>" +
      "Con este ícono creás una tarea sin salir de donde estás: le ponés título, responsable y fecha, y si querés que se repita. " +
      "Al responsable le llega un mail y la ve en Tareas." +
      '<div class="tr-globo-acc"><button type="button" class="tr-globo-ok">Entendido</button><button type="button" class="tr-globo-probar">Probar ahora</button></div>';
    document.body.appendChild(g);
    const r = icono.getBoundingClientRect();
    const left = Math.max(8, Math.min(r.left + r.width / 2 - 30, window.innerWidth - 308));
    g.style.top = r.bottom + 10 + "px";
    g.style.left = left + "px";
    g.style.setProperty("--tr-flecha", r.left + r.width / 2 - left - 7 + "px");
    icono.classList.add("tr-pulso");
    const cerrar = () => {
      g.remove();
      icono.classList.remove("tr-pulso");
      marcarAvisoVisto();
    };
    g.querySelector(".tr-globo-ok").addEventListener("click", cerrar);
    g.querySelector(".tr-globo-probar").addEventListener("click", () => {
      cerrar();
      icono.click();
    });
    icono.addEventListener("click", cerrar, { once: true });
  }
  window.addEventListener("load", function () {
    fetch("/SistemaTriangular/Menu/php/avisos.php?clave=" + AVISO, { credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } })
      .then((r) => (r.ok ? r.json() : null))
      .then((r) => r && r.mostrar && setTimeout(mostrarGlobo, 800))
      .catch(() => {});
  });

  const fecha = window.FechaAsana
    ? FechaAsana.montar({ boton: $("tr_vence_btn"), fecha: $("tr_vence"), tipo: $("tr_repetir"), cada: $("tr_rep_cada"), dias: $("tr_rep_dias") })
    : null;
  // Responsable y Proyecto: lista con buscador estilo Asana sobre los <select>
  const selResp = window.SelectAsana ? SelectAsana.montar({ boton: $("tr_resp_btn"), select: $("tr_responsable"), vacio: "Sin responsable", tipo: "persona" }) : null;
  const selProy = window.SelectAsana ? SelectAsana.montar({ boton: $("tr_proy_btn"), select: $("tr_proyecto"), vacio: "Elegí el proyecto", tipo: "proyecto" }) : null;

  $("tr_form").addEventListener("submit", function (e) {
    e.preventDefault();
    const titulo = $("tr_titulo").value.trim();
    const idProyecto = $("tr_proyecto").value;
    if (!titulo) return mensaje("warning", "Poné un título a la tarea.");
    if (!idProyecto) return mensaje("warning", "Elegí el proyecto.");
    $("tr_guardar").disabled = true;
    api({
      accion: "guardar_tarea",
      titulo,
      idProyecto,
      idResponsable: $("tr_responsable").value,
      vence: $("tr_vence").value,
      descripcion: $("tr_descripcion").value.trim(),
      repetirTipo: $("tr_repetir").value, // al completarla se crea la siguiente (tareas_api.php)
      repetirCada: $("tr_rep_cada").value,
      repetirDias: $("tr_rep_dias").value,
    })
      .then((r) => {
        if (!r.success) throw new Error(r.error || "No se pudo crear la tarea.");
        localStorageSet("tr_proyecto", idProyecto);
        const resp = (datos.usuarios || []).find((u) => String(u.id) === $("tr_responsable").value);
        const para = resp && resp.id !== datos.yo ? " y le avisamos a " + esc(resp.nombre) : "";
        mensaje("success", '<i class="mdi mdi-check-circle"></i> Tarea creada' + para + '. <a href="/SistemaTriangular/Empleados/Tareas.php?tarea=' + r.id + '">Abrirla</a>');
        limpiar();
        $("tr_titulo").focus();
      })
      .catch((e) => mensaje("danger", esc(e.message)))
      .finally(() => ($("tr_guardar").disabled = false));
  });
})();
