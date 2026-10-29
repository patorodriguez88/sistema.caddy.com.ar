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
    const ultimo = localStorageGet("tr_proyecto");
    $("tr_proyecto").innerHTML =
      '<option value="">Elegí el proyecto…</option>' +
      proyectos.map((p) => '<option value="' + p.id + '"' + (String(p.id) === ultimo ? " selected" : "") + ">" + esc(p.nombre) + "</option>").join("");
    $("tr_responsable").innerHTML = (datos.usuarios || [])
      .map((u) => '<option value="' + u.id + '"' + (u.id === datos.yo ? " selected" : "") + ">" + esc(u.nombre) + (u.id === datos.yo ? " (yo)" : "") + "</option>")
      .join("");
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
  }

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
  panel.addEventListener("hide.bs.offcanvas", () => fecha && fecha.cerrar());

  // Fecha de entrega + Repetir estilo Asana (fecha_asana.js, cargado antes que este archivo)
  const fecha = window.FechaAsana
    ? FechaAsana.montar({ boton: $("tr_vence_btn"), fecha: $("tr_vence"), tipo: $("tr_repetir"), cada: $("tr_rep_cada"), dias: $("tr_rep_dias") })
    : null;

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
