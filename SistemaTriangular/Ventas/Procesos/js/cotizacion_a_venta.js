// Venta Simple desde una cotización (Ventas/Ventas?cotizacion=ID).
// Lo abre el botón "Generar venta" del Cotizador de Envíos. Precarga lo que se
// puede y deja que el operador revise y confirme con el circuito normal de
// Venta Simple (el precio se puede modificar). Al confirmar, confirmar_venta.js
// manda cotizacion_id y ConfirmarVenta.php le graba el código de seguimiento.
(function () {
  "use strict";

  var params = new URLSearchParams(window.location.search);
  var cotId = parseInt(params.get("cotizacion") || "0", 10);
  if (!cotId) return;

  var cot = null;

  function el(id) { return document.getElementById(id); }
  function money(n) {
    return "$ " + Number(n || 0).toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  function aviso(tipo, titulo, msg) {
    if (typeof toast === "function") toast(tipo, titulo, msg);
  }
  function setVal(id, v) { var e = el(id); if (e) e.value = v == null ? "" : v; }

  // "Gdor. Justiniano Posse 1236, X5016 Córdoba, Argentina" -> calle, número, CP
  function partirDireccion(texto) {
    var primera = String(texto || "").split(",")[0].trim();
    var m = primera.match(/^(.*?)\s+(\d+)\s*$/);
    var cp = (String(texto || "").match(/\b[A-Z]?(\d{4})[A-Z]{0,3}\b/) || [])[1] || "";
    return { calle: m ? m[1] : primera, numero: m ? m[2] : "", cp: cp };
  }

  // Abre el modal de "Crear cliente" (Origen o Destino) y lo completa con la cotización
  function crearCliente(tipo) {
    var boton = el(tipo === "Origen" ? "crearorigen" : "creardestino");
    if (!boton || !cot) return;
    boton.click(); // abre el modal y limpia los campos (handler de funciones.js)
    var dir = tipo === "Origen" ? cot.origen : cot.destino;
    var partes = partirDireccion(dir.texto);
    setTimeout(function () {
      if (tipo === "Origen") {
        setVal("nombrecliente_nc", cot.nombre);
        setVal("email_nc", cot.email);
        setVal("celular_nc", cot.telefono);
        setVal("telefono_nc", cot.telefono);
      }
      setVal("direccion_nc", dir.texto);
      setVal("Calle_nc", partes.calle);
      setVal("Numero_nc", partes.numero);
      setVal("cp_nc", partes.cp);
      setVal("ciudad_nc", dir.localidad);
      setVal("observaciones_nc", "Cotización N° " + cot.id);
      var nombre = el("nombrecliente_nc");
      if (nombre && tipo === "Destino") {
        nombre.placeholder = "Nombre del destinatario";
        nombre.focus();
      }
    }, 150);
  }

  // Elige un cliente existente en el select2 de origen (dispara oculto_origen)
  function elegirOrigen(cliente) {
    var $sel = $("#id_origen");
    if (!$sel.length) return;
    if (!$sel.find('option[value="' + cliente.id + '"]').length) {
      $sel.append(new Option(cliente.nombre, cliente.id, true, true));
    }
    $sel.val(String(cliente.id)).trigger("change");
  }

  function origenElegido() {
    return !!((el("id_origen") && el("id_origen").value) || (el("id_origen2") && el("id_origen2").value));
  }

  // Servicio de la tarifa cotizada + precio cotizado (editable por el operador)
  function aplicarServicioYPrecio() {
    if (!cot) return;
    if (!origenElegido()) {
      aviso("info", "Cotización", "Primero elegí o creá el cliente de origen.");
      return;
    }
    var intentos = 0;
    (function esperarOpciones() {
      // las opciones de #servicio se cargan por AJAX al abrir la pantalla
      if (cot.servicio && !$("#servicio option[value='" + cot.servicio.id + "']").length && intentos++ < 20) {
        setTimeout(esperarOpciones, 250);
        return;
      }
      if (cot.servicio && $("#servicio option[value='" + cot.servicio.id + "']").length) {
        $("#servicio").val(String(cot.servicio.id)).trigger("change"); // cargar(): precio de lista y código
      }
      setVal("comentario", "Cotización N° " + cot.id);
      // cargar() pisa el precio con el de lista: se aplica el cotizado después
      setTimeout(function () {
        setVal("cantidad", 1);
        setVal("precioventa", cot.total);
        setVal("total", cot.total);
        if (cot.servicio) {
          aviso("success", "Cotización", "Precio cotizado aplicado (" + money(cot.total) + "). Podés modificarlo antes de agregar.");
        } else {
          aviso("info", "Cotización", "Elegí el servicio: el precio cotizado (" + money(cot.total) + ") ya está cargado.");
        }
      }, 900);
    })();
  }

  function renderBanner() {
    var form = el("VentaSimple");
    if (!form) return;
    var div = document.createElement("div");
    div.id = "cotizacion_banner";
    div.className = "alert " + (cot.vendida ? "alert-warning" : "alert-info") + " py-2 px-3 mb-3";
    var html =
      '<div class="d-flex flex-wrap align-items-center gap-2 mb-1"><b><i class="mdi mdi-file-document-outline"></i> Venta desde la cotización N° ' + cot.id + "</b>" +
      (cot.titulo ? '<span class="text-muted">' + esc(cot.titulo) + "</span>" : "") + "</div>" +
      '<div class="small">' +
      "<b>" + esc(cot.nombre) + "</b>" + (cot.email ? " · " + esc(cot.email) : "") + (cot.telefono ? " · " + esc(cot.telefono) : "") + "<br>" +
      "Origen: " + esc(cot.origen.texto) + "<br>" +
      "Destino: " + esc(cot.destino.texto) + "<br>" +
      "Total cotizado: <b>" + money(cot.total) + "</b>" +
      (cot.servicio ? " · Tarifa " + esc(cot.servicio.titulo) : "") +
      (cot.bultos > 1 ? " · " + cot.bultos + " bultos" : "") +
      (cot.valor_declarado > 0 ? " · Valor declarado " + money(cot.valor_declarado) : "") +
      "</div>";
    if (cot.vendida) {
      html += '<div class="mt-2"><b>Esta cotización ya se convirtió en venta: ' + esc(cot.vendida) + ".</b> No generes otra.</div>";
    } else {
      html +=
        '<div class="d-flex flex-wrap gap-2 mt-2">' +
        (cot.cliente
          ? '<span class="badge bg-success align-self-center">Origen: cliente existente ' + esc(cot.cliente.nombre) + "</span>"
          : '<button type="button" class="btn btn-sm btn-outline-primary" id="cav_origen">1. Crear cliente origen con estos datos</button>') +
        '<button type="button" class="btn btn-sm btn-outline-primary" id="cav_destino">' + (cot.cliente ? "1" : "2") + ". Crear destino con la dirección cotizada</button>" +
        '<button type="button" class="btn btn-sm btn-primary" id="cav_servicio">' + (cot.cliente ? "2" : "3") + ". Cargar servicio y precio cotizado</button>" +
        "</div>" +
        '<div class="small text-muted mt-1">Revisá los datos, agregá el servicio a la venta y confirmá como siempre. El precio se puede modificar.</div>';
    }
    div.innerHTML = html;
    form.insertBefore(div, form.children[1] || null);

    var hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.id = "cotizacion_id";
    hidden.value = cot.vendida ? "" : String(cot.id);
    form.appendChild(hidden);

    if (el("cav_origen")) el("cav_origen").addEventListener("click", function () { crearCliente("Origen"); });
    if (el("cav_destino")) el("cav_destino").addEventListener("click", function () { crearCliente("Destino"); });
    if (el("cav_servicio")) el("cav_servicio").addEventListener("click", aplicarServicioYPrecio);
  }

  $(function () {
    $.getJSON("Procesos/php/cotizacion_a_venta.php", { id: cotId })
      .done(function (res) {
        if (!res || !res.ok) {
          aviso("error", "Cotización", (res && res.error) || "No se pudo cargar la cotización.");
          return;
        }
        cot = res;
        renderBanner();
        if (cot.vendida) return;
        if (!el("observaciones").value) setVal("observaciones", "Cotización N° " + cot.id);
        // funciones.js limpia id_origen al cargar la pantalla: se elige después
        if (cot.cliente) setTimeout(function () { elegirOrigen(cot.cliente); }, 700);
      })
      .fail(function () { aviso("error", "Cotización", "No se pudo cargar la cotización N° " + cotId + "."); });
  });
})();
