// Validación visible de Venta Simple (estilo Bootstrap: campo en rojo + mensaje).
// Antes, varios <select required> están ocultos por select2: si uno quedaba vacío
// el navegador frenaba el envío EN SILENCIO ("An invalid form control is not
// focusable") y el operador veía que "no pasaba nada". Ahora el form tiene
// novalidate y este validador corre ANTES que confirmar_venta.js (listener en
// fase de captura): si falta algo, no se envía y se marca qué falta.
(function () {
  "use strict";

  var form = document.getElementById("VentaSimple");
  if (!form) return;

  // Reglas: [id del campo, mensaje, (opcional) id alternativo que también vale]
  var REGLAS = [
    ["id_origen", "Elegí o creá el cliente de origen.", "id_origen2"],
    ["id_destino", "Elegí o creá el cliente de destino.", "id_destino2"],
    ["formadepago_t", "Elegí la forma de pago."],
    ["cobranzadelenvio_t", "Elegí la modalidad de cobro."],
    ["entregaen_t", "Elegí si se entrega en sucursal o domicilio."],
    ["retiro_t", "Elegí si es retiro y entrega o solo entrega."],
    ["recorrido_t", "Elegí el recorrido."],
  ];

  var estilos = document.createElement("style");
  estilos.textContent =
    ".select2-container.vv-invalido .select2-selection{border-color:#fa5c7c!important;box-shadow:0 0 0 .15rem rgba(250,92,124,.25)}" +
    ".vv-invalido-tabla{outline:2px solid #fa5c7c;outline-offset:2px;border-radius:4px}";
  document.head.appendChild(estilos);

  function valor(id) {
    var e = document.getElementById(id);
    if (!e) return "";
    var v = (e.value || "").trim();
    return v === "NULL" ? "" : v; // "Modalidad de Cobro" arranca con value="NULL"
  }

  // El control que ve el operador: el contenedor de select2 si existe, si no el propio campo
  function visible(e) {
    var s2 = e.nextElementSibling && e.nextElementSibling.classList.contains("select2") ? e.nextElementSibling : null;
    return s2 || e;
  }

  function esVisible(e) {
    var v = visible(e);
    return !!(v.offsetWidth || v.offsetHeight || v.getClientRects().length);
  }

  function marcar(e, mensaje) {
    var v = visible(e);
    e.classList.add("is-invalid");
    if (v !== e) v.classList.add("vv-invalido");
    var fb = v.parentNode.querySelector(".vv-feedback[data-para='" + e.id + "']");
    if (!fb) {
      fb = document.createElement("div");
      fb.className = "invalid-feedback d-block vv-feedback";
      fb.setAttribute("data-para", e.id);
      v.insertAdjacentElement("afterend", fb);
    }
    fb.textContent = mensaje;
  }

  function desmarcar(e) {
    if (!e) return;
    var v = visible(e);
    e.classList.remove("is-invalid");
    v.classList.remove("vv-invalido");
    var fb = v.parentNode && v.parentNode.querySelector(".vv-feedback[data-para='" + e.id + "']");
    if (fb) fb.remove();
  }

  function cantidadServicios() {
    if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable("#basic")) {
      return $("#basic").DataTable().rows().count();
    }
    var filas = document.querySelectorAll("#basic tbody tr");
    return Array.prototype.filter.call(filas, function (tr) { return !tr.querySelector(".dataTables_empty"); }).length;
  }

  function validar() {
    var faltan = [];
    var primero = null;
    REGLAS.forEach(function (r) {
      var e = document.getElementById(r[0]);
      if (!e) return;
      var alt = r[2] ? document.getElementById(r[2]) : null;
      var ok = valor(r[0]) !== "" || (alt && valor(r[2]) !== "");
      // un campo oculto (ej. se creó el cliente con el modal y se muestra el alternativo) no bloquea por sí mismo
      var campo = esVisible(e) ? e : (alt && esVisible(alt) ? alt : null);
      if (ok) { desmarcar(e); if (alt) desmarcar(alt); return; }
      if (!campo) return;
      marcar(campo, r[1]);
      faltan.push(r[1]);
      if (!primero) primero = visible(campo);
    });

    var tabla = document.getElementById("basic");
    if (cantidadServicios() === 0) {
      faltan.push("Agregá al menos un servicio a la venta (botón Agregar).");
      if (tabla) tabla.classList.add("vv-invalido-tabla");
      if (!primero) primero = document.getElementById("servicio") ? visible(document.getElementById("servicio")) : tabla;
    } else if (tabla) {
      tabla.classList.remove("vv-invalido-tabla");
    }

    if (faltan.length) {
      if (primero && primero.scrollIntoView) primero.scrollIntoView({ behavior: "smooth", block: "center" });
      var html = "<ul style='text-align:left;margin:0'>" + faltan.map(function (f) { return "<li>" + f + "</li>"; }).join("") + "</ul>";
      if (window.Swal) {
        Swal.fire({ icon: "warning", title: "Faltan datos para confirmar", html: html });
      } else if (typeof toast === "function") {
        toast("error", "Faltan datos", faltan.join(" "));
      }
    }
    return faltan.length === 0;
  }

  // Sin novalidate, el navegador cortaba el envío antes de que llegue a nadie
  form.setAttribute("novalidate", "novalidate");

  // Fase de captura: corre antes que el submit de confirmar_venta.js
  form.addEventListener("submit", function (ev) {
    if (!validar()) {
      ev.preventDefault();
      ev.stopImmediatePropagation();
    }
  }, true);

  // Al corregir un campo, se le saca el rojo
  REGLAS.forEach(function (r) {
    [r[0], r[2]].forEach(function (id) {
      if (!id) return;
      var e = document.getElementById(id);
      if (!e) return;
      var limpiar = function () { if (valor(id) !== "") { desmarcar(document.getElementById(r[0])); if (r[2]) desmarcar(document.getElementById(r[2])); } };
      e.addEventListener("change", limpiar);
      if (window.jQuery) $(e).on("select2:select change.select2", limpiar);
    });
  });
  if (window.jQuery) {
    $("#basic").on("draw.dt", function () {
      if (cantidadServicios() > 0) document.getElementById("basic").classList.remove("vv-invalido-tabla");
    });
  }
})();
