var button_ver = 0;
var state = 0;

// ---------- Ficha del envío: helpers de presentación ----------
function gsEsc(v) {
  return String(v == null ? "" : v)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}
function gsVacio(v) {
  v = String(v == null ? "" : v).trim();
  return v === "" || v === "0" || v === "null" || v === "0000-00-00";
}
function gsFecha(v) {
  var m = String(v || "").match(/^(\d{4})-(\d{2})-(\d{2})/);
  return m ? m[3] + "/" + m[2] + "/" + m[1] : "";
}
// Color de cada estado (mismo criterio en el estado actual, la barra y el historial)
function gsColorEstado(estado) {
  switch (String(estado || "").trim()) {
    case "Entregado al Cliente":
      return "#0acf97";
    case "Devuelto al Cliente":
    case "No se pudo entregar":
    case "No se pudo Retirar":
    case "No se Pudo Retirar":
      return "#fa5c7c";
    case "En Transito":
    case "Cargado en Hoja de Ruta":
    case "Cargado en Hoja De Ruta":
      return "#727cf5";
    case "Retirado del Cliente":
      return "#39afd1";
    default:
      return "#ffbc00";
  }
}
function gsEstadoPill(estado) {
  return estado
    ? '<span class="gs-estado-pill" style="--gs-c:' + gsColorEstado(estado) + '">' + gsEsc(estado) + "</span>"
    : "";
}

// Estado actual del paquete y barra de progreso. Se llama con el estado de TransClientes
// (Seguimiento_Modal) y después con el último movimiento real de Seguimiento (Compruebo), que manda.
function pintarUltimoEstadoBadge(estado) {
  estado = String(estado || "").trim();
  if (!estado || estado === "0") return;
  window.__ultimoEstadoGuia = estado;
  var c = gsColorEstado(estado);

  // Paso alcanzado: 0 En origen · 1 Retirado · 2 En tránsito · 3 Entregado (o el final fallido)
  var paso = 0, fallo = "";
  switch (estado) {
    case "Retirado del Cliente":
      paso = 1;
      break;
    case "No se pudo Retirar":
    case "No se Pudo Retirar":
      paso = 1;
      fallo = "No se pudo retirar";
      break;
    case "En Transito":
    case "Cargado en Hoja de Ruta":
    case "Cargado en Hoja De Ruta":
      paso = 2;
      break;
    case "Entregado al Cliente":
      paso = 3;
      break;
    case "No se pudo entregar":
      paso = 3;
      fallo = "No se pudo entregar";
      break;
    case "Devuelto al Cliente":
      paso = 3;
      fallo = "Devuelto";
      break;
  }
  var pasos = [
    ["En origen", "mdi-store-outline"],
    ["Retirado", "mdi-package-up"],
    ["En tránsito", "mdi-truck-fast-outline"],
    ["Entregado", "mdi-flag-checkered"],
  ];
  if (fallo) pasos[paso] = [fallo, "mdi-close"];

  var html = "";
  pasos.forEach(function (p, i) {
    var cls = i < paso ? "done" : i === paso ? "current" + (i === 3 || fallo ? " final" : "") : "";
    html +=
      '<div class="gs-step ' + cls + '"><span class="gs-step-dot"><i class="mdi ' +
      (i < paso ? "mdi-check" : p[1]) + '"></i></span>' + p[0] + "</div>";
  });
  $("#tracker").css("--gs-c", c).html(html);
  $("#gs_progreso .gs-status").css("--gs-c", c);
  $("#gs_estado").text(estado);
}

// Formatea 'YYYY-MM-DD' (con o sin hora) como 'd.m.yyyy'. Solo para mostrar;
// para ordenar/filtrar DataTables sigue usando el valor crudo.
function fechaDMY(data, type) {
  if (type !== "display" || !data) return data;
  var m = String(data).match(/^(\d{4})-(\d{2})-(\d{2})/);
  return m ? parseInt(m[3], 10) + "." + parseInt(m[2], 10) + "." + m[1] : data;
}

$(document).ready(function () {
  $("#inputcodigo").val("");
  $("#inputcodigoproveedor").val("");
  $("#inputname").val("");
});
// Buscador: se busca por un solo criterio. El selector muestra el campo de ese criterio y vacía los otros.
function gsTipoBusqueda(campo) {
  var $btn = $('.gs-tabs button[data-campo="' + campo + '"]');
  $(".gs-tabs button").removeClass("active");
  $btn.addClass("active");
  $("#inputcodigo, #inputcodigoproveedor, #inputname").val("").prop("hidden", true);
  $("#" + campo).prop("hidden", false).trigger("focus");
  $("#gs_hint").html(
    '<i class="mdi mdi-information-outline"></i> ' + gsEsc($btn.data("hint")) +
      ' · <i class="mdi mdi-keyboard-return"></i> Enter para buscar',
  );
}
$(document).on("click", ".gs-tabs button", function () {
  gsTipoBusqueda($(this).data("campo"));
});

// Envíos consultados hace poco (solo en este navegador): acceso rápido desde la portada.
var GS_RECIENTES_KEY = "caddy_seguimiento_recientes";
function gsLeerRecientes() {
  try {
    return JSON.parse(localStorage.getItem(GS_RECIENTES_KEY) || "[]") || [];
  } catch (e) {
    return [];
  }
}
function gsGuardarReciente(cs, destino, estado) {
  var lista = gsLeerRecientes().filter(function (r) { return r.cs !== cs; });
  lista.unshift({ cs: cs, destino: destino || "", estado: estado || "" });
  try {
    localStorage.setItem(GS_RECIENTES_KEY, JSON.stringify(lista.slice(0, 6)));
  } catch (e) {}
}
function gsPintarRecientes() {
  var lista = gsLeerRecientes();
  $("#gs_recientes").prop("hidden", !lista.length);
  $("#gs_recientes_list").html(
    lista.map(function (r) {
      return (
        '<div class="gs-reciente" role="button" data-cs="' + gsEsc(r.cs) + '" style="--gs-c:' + gsColorEstado(r.estado) + '">' +
        '<span class="gs-reciente-dot" title="' + gsEsc(r.estado) + '"></span>' +
        '<div class="gs-reciente-main"><div class="gs-reciente-code">' + gsEsc(r.cs) + "</div>" +
        '<div class="gs-reciente-sub">' + gsEsc(r.destino || r.estado) + "</div></div></div>"
      );
    }).join(""),
  );
}
$(document).on("click", ".gs-reciente", function () {
  seguimiento(String($(this).data("cs")));
});
$(gsPintarRecientes);
$("#inputcodigo, #inputcodigoproveedor, #inputname").on("keydown", function (e) {
  if (e.key === "Enter") {
    e.preventDefault();
    $("#remito").click();
  }
});

// Modal "Actualizando información" (#info-alert-modal). Bootstrap 5 ignora modal("hide") mientras
// el modal todavía se está animando para abrirse, y acá las respuestas suelen llegar antes de que
// termine: el modal quedaba abierto para siempre. Si se pide cerrar en plena animación, se cierra
// apenas termina de abrirse (shown.bs.modal).
var gsCerrarCargaPendiente = false;
$("#info-alert-modal")
  .on("show.bs.modal", function () {
    gsCerrarCargaPendiente = false;
  })
  .on("shown.bs.modal", function () {
    if (gsCerrarCargaPendiente) {
      gsCerrarCargaPendiente = false;
      $(this).modal("hide");
    }
  });
function gsOcultarCarga() {
  var el = document.getElementById("info-alert-modal");
  var inst = el && window.bootstrap && bootstrap.Modal.getInstance(el);
  if (inst && inst._isTransitioning) {
    gsCerrarCargaPendiente = true;
  } else {
    $("#info-alert-modal").modal("hide");
  }
}

// Las tablas de la ficha son DataTables: se destruyen antes de volver a buscar (si no, la
// segunda búsqueda tira "Cannot reinitialise DataTable" y deja los datos del envío anterior).
function gsResetTablas() {
  ["#search_tabla", "#webhook_tabla"].forEach(
    function (sel) {
      if ($.fn.DataTable.isDataTable(sel)) $(sel).DataTable().clear().destroy();
    },
  );
  $("#gs_ventas, #gs_trans, #gs_historial").empty();
}

// ---------- Historial de seguimiento: tabla compacta, en orden cronológico ----------
// Si hay muchos movimientos se ven los últimos GS_HISTORIAL_VISIBLES y los anteriores quedan plegados arriba.
var GS_HISTORIAL_VISIBLES = 8;
function gsCargarHistorial(cs) {
  $("#gs_historial").html('<div class="gs-tl-vacio">Cargando movimientos…</div>');
  $.post("../Funciones/php/tablas.php", { Seguimiento_Tabla: 1, CodigoSeguimiento: cs }, null, "json")
    .done(function (res) {
      var filas = (res && res.data) || [];
      if (!filas.length) {
        $("#gs_historial").html('<div class="gs-tl-vacio">Todavía no hay movimientos de seguimiento.</div>');
        return;
      }
      // El último movimiento manda en el estado actual (al agregar o borrar uno se actualiza solo).
      pintarUltimoEstadoBadge(filas[filas.length - 1].Estado);
      var guion = '<span class="text-muted-dash">—</span>';
      var ocultos = Math.max(0, filas.length - GS_HISTORIAL_VISIBLES);
      var html =
        '<div class="gs-table-wrap"><table class="gs-hist"><thead><tr>' +
        "<th>Fecha</th><th>Estado</th><th>Observaciones</th><th>Usuario</th>" +
        '<th class="num">Rec.</th><th class="num">Orden</th><th></th></tr></thead><tbody>';
      if (ocultos) {
        html += '<tr class="gs-h-mas"><td colspan="7"><button type="button" onclick="$(\'#gs_historial [data-viejo]\').prop(\'hidden\', false); $(this).closest(\'tr\').remove();">' +
          '<i class="mdi mdi-chevron-up"></i> Ver ' + ocultos + " movimiento" + (ocultos > 1 ? "s" : "") + " anterior" + (ocultos > 1 ? "es" : "") + "</button></td></tr>";
      }
      filas.forEach(function (r, i) {
        var ultimo = i === filas.length - 1;
        html +=
          "<tr" + (i < ocultos ? ' data-viejo="1" hidden' : "") + (ultimo ? ' class="gs-h-ultimo"' : "") +
          ' style="--gs-c:' + gsColorEstado(r.Estado) + '">' +
          '<td class="gs-h-fecha"><b>' + gsFecha(r.Fecha) + "</b><small>" + gsEsc(String(r.Hora || "").slice(0, 5)) + "</small></td>" +
          "<td>" + gsEstadoPill(r.Estado) + "</td>" +
          '<td class="gs-h-obs">' + (gsVacio(r.Observaciones) ? guion : gsEsc(r.Observaciones)) + "</td>" +
          '<td class="gs-h-user" title="' + gsEsc(r.Usuario) + '">' + (gsVacio(r.Usuario) ? guion : gsEsc(r.Usuario)) + "</td>" +
          '<td class="num">' + (gsVacio(r.Recorrido) ? guion : gsEsc(r.Recorrido)) + "</td>" +
          '<td class="num">' + (gsVacio(r.NumerodeOrden) ? guion : gsEsc(r.NumerodeOrden)) + "</td>" +
          '<td class="num"><button type="button" class="gs-h-del" title="Eliminar este movimiento" onclick="ver_seguimiento(' +
          parseInt(r.id, 10) + ')"><i class="mdi mdi-trash-can-outline"></i></button></td></tr>';
      });
      $("#gs_historial").html(html + "</tbody></table></div>");
    })
    .fail(function () {
      $("#gs_historial").html('<div class="gs-tl-vacio text-danger">No se pudo cargar el historial.</div>');
    });
}

// ---------- Facturación: ítems de la venta y comprobantes (listas compactas) ----------
function gsPesos(v) {
  var n = parseFloat(v);
  return "$ " + (isNaN(n) ? 0 : n).toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function gsCargarFacturacion(cs) {
  var vacio = function (t) { return '<div class="gs-items-empty">' + t + "</div>"; };
  $("#gs_ventas, #gs_trans").html(vacio("Cargando…"));

  $.post("../Funciones/php/tablas.php", { Aforo_Tabla: 1, CodigoSeguimiento: cs }, null, "json")
    .done(function (res) {
      var filas = (res && res.data) || [];
      if (!filas.length) return $("#gs_ventas").html(vacio("Sin ítems de venta"));
      var total = 0, html = "";
      filas.forEach(function (r) {
        var cant = parseInt(r.Cantidad, 10) || 0;
        var importe = r.Total != null && r.Total !== "" ? parseFloat(r.Total) : (parseFloat(r.Precio) || 0) * cant;
        total += importe || 0;
        // Solo consulta. Las líneas de seguro / cobranza van con Cantidad 0 (no son bultos).
        var qty = cant > 0 ? "× " + cant : '<span title="Línea sin bultos (seguro, cobranza…)">—</span>';
        html +=
          '<div class="gs-item"><div class="gs-item-main"><div class="gs-item-title" title="' + gsEsc(r.Titulo) + '">' + gsEsc(r.Titulo) + "</div>" +
          '<div class="gs-item-sub">Cód. ' + gsEsc(r.Codigo) + " · " + gsFecha(r.FechaPedido) + "</div></div>" +
          '<div class="gs-item-qty">' + qty + "</div>" +
          '<div class="gs-item-amount">' + gsPesos(importe) + "</div></div>";
      });
      if (filas.length > 1) {
        html += '<div class="gs-item gs-item-total"><div class="gs-item-main">Total</div><div class="gs-item-amount">' + gsPesos(total) + "</div></div>";
      }
      $("#gs_ventas").html(html);
    })
    .fail(function () { $("#gs_ventas").html(vacio("No se pudieron cargar los ítems")); });

  $.post("../Funciones/php/tablas.php", { Aforo_Tabla_Trans: 1, CodigoSeguimiento: cs }, null, "json")
    .done(function (res) {
      var filas = (res && res.data) || [];
      if (!filas.length) return $("#gs_trans").html(vacio("Sin comprobantes"));
      $("#gs_trans").html(filas.map(function (r) {
        return '<div class="gs-item"><div class="gs-item-main"><div class="gs-item-title">' + gsEsc(r.TipoDeComprobante) + "</div>" +
          '<div class="gs-item-sub">N° ' + gsEsc(r.NumeroComprobante) + "</div></div>" +
          '<div class="gs-item-amount">' + gsPesos(r.Debe) + "</div></div>";
      }).join(""));
    })
    .fail(function () { $("#gs_trans").html(vacio("No se pudieron cargar los comprobantes")); });
}



function changeservice(f) {
  $.ajax({
    data: { ChangeFlex: 1, id_transclientes: f },
    type: "POST",
    url: "Procesos/php/funciones.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        if (jsonData.value == 1) {
          $("#" + f)
            .removeClass("bg-warning")
            .addClass("bg-success");
          $("#" + f).html("Flex");
        } else {
          $("#" + f)
            .removeClass("bg-success")
            .addClass("bg-warning");
          $("#" + f).html("Simple");
        }
      }
    },
  });
}

function control_password(value) {
  var Fecha_new = $("#date_new").val();

  $.ajax({
    data: { pass: value, ControlPass: 1 },
    type: "POST",
    url: "Procesos/php/funciones.php",
    beforeSend: function () {
      // setting a timeout
      $("#button_date_blocked").removeClass("btn-danger");
      $("#button_date").removeClass("mdi-close");

      setTimeout(
        $("#button_date_blocked").removeClass("btn-danger"),
        $("#button_date").removeClass("mdi-close"),
        $("#button_date").css("display", "inline"),
        2000,
      );
    },
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.Result == 1 && Fecha_new !== "0000-00-00") {
        $("#button_date_blocked").removeClass("btn-danger");
        $("#button_date_blocked").addClass("btn-success");
        $("#button_date").removeClass("mdi-spin mdi-reload");
        $("#button_date").removeClass("mdi-close");
        $("#button_date").addClass("mdi-check");
        button_ver = 1;
      } else {
        $("#button_date_blocked").removeClass("btn-success");
        $("#button_date_blocked").addClass("btn-danger");
        $("#button_date").removeClass("mdi-spin mdi-reload");
        $("#button_date").addClass("mdi-close");
        $("#notification_text_danger").html("Error - Usuario sin Permisos");
        button_ver = 0;
      }
    },
  });
}

//BUTTON CAMBIO DE FECHA
$("#button_date_blocked").click(function () {
  let cs = $("#inputcodigo").val();
  let Fecha_new = $("#date_new").val();

  if (button_ver == 1) {
    $.ajax({
      data: { ActualizarFechaServicio: 1, Cs: cs, Fecha_new: Fecha_new },
      type: "POST",
      url: "Procesos/php/funciones.php",
      success: function (response) {
        var jsonData = JSON.parse(response);

        if (jsonData.Result_Fechas === 1) {
          toast("success", "Exito !", "Fecha modificada con éxito !");
        } else {
          toast("error", "Error !", "No fue posible actualizar la fecha");
        }

        $("#date-modal").modal("hide");
      },
    });
  }
});

function verguia() {
  let id = $("#inputcodigo").val();
  window.open(
    "/SistemaTriangular/Servicios/Informes/Remitopdf.php?CS=" +
      id,
    "_blank",
  );
}
function verrotulo() {
  let id = $("#inputcodigo").val();
  window.open(
    "/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=" +
      id,
    "_blank",
  );
}

// Volver del detalle de seguimiento al buscador.
function cerrarSeguimiento() {
  document.getElementById("ficha_seguimiento").style.display = "none";
  $("#row_search").css("display", "none");
  document.getElementById("form_guias").style.display = "";
  gsResetTablas();
  window.__ultimoEstadoGuia = null;
  $("#gs_header, #gs_acciones").prop("hidden", true);
  $("#pagaorigen, #pagadestino").hide();
  $("#title_aforo, #title_aforo_trans").html("");
  gsTipoBusqueda("inputcodigo");
  gsPintarRecientes();
}

// ============================================================
// ROTULO 6x2 cm - impresion directa en Zebra via Browser Print
// ============================================================
var zebraDevice = null;

function zebraSetup() {
  if (typeof BrowserPrint === "undefined") return;
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      zebraDevice = device;
    },
    function () {
      /* sin impresora por defecto: se avisa al abrir el modal */
    },
  );
}
$(function () {
  zebraSetup();
});

// Corta un texto a n caracteres para que entre en la etiqueta.
function _rec(s, n) {
  s = (s == null ? "" : String(s)).trim();
  return s.length > n ? s.slice(0, n - 1) + "…" : s;
}

// Logo Caddy (iso) - GFA, mismo que la etiqueta de colecta de WePoint.
var CADDY_LOGO_ZPL =
  "^FO30,15^GFA,1675,1675,25,,:::::::::::::M0CJ04J01,L07F8003FCI0FE,L0FFC007FE003FF,K01FFE00IF007FF8,K03FFE01IF007FFC,K03IF01IF80IFC,K03IF81IFC0IFE,K07IF83IFC0IFE,K07IFC1IFE0IFE,K03IFE1JF0F01E,K03IFE1JF0E01C,K03JF1JF8703C,K01JF0JF87C78,L0JF87IFC3FF,L0JFC7IFE0FE,L07IFC3IFE01V07,L07IFE3JFX0F,L03IFE1JFX0F,L01JF0JF8W07J0F,L01JF8JFCgH0F,M0JF87IFCgH0F,M0JFC7IFEI0E3C38FC3FE07F8F73F3FE,M07IFC3IFEI0E3E79FF3FF0FFCF7FFBFF,M03IFE1JFI0E3E7BFF3FF9FFEF7FFBFE,M03IFE1JFI0F7E77C73C79E1EF7C78F,M01IFE0JFI0F7E7787BC3DE1E77878F,N0IFE07IFI077FF7FFBC3DC0FF7838F,N0IFE07IFI07F7E7FFBC3DC0FF7838F,N07FFE03IFI07E7E7803C3DE1EF7838F,N07FFE03IFI03E7E7C13C79E1EF7838F,N03FFC01FFEI03E3C3FF3FF9FFEF7878FE,N01FF800FFCI03E3C1FFBFF0FFCF78787F,O0FFI07F8I01C3C0FF3FE07F0778383F,U08Q03C,gM03C,:::,::::::::::::::^FS";

// Datos del servicio para el rotulo (de window.seguimientoData / seguimientoHdr).
function _rotuloDatos() {
  var d = window.seguimientoData || {};
  var hdr = window.seguimientoHdr || {};
  var cs = (window.seguimientoCS || $("#inputcodigo").val() || "").trim();
  var hoy = new Date();
  var p2 = function (n) { return String(n).padStart(2, "0"); };
  return {
    cs: cs,
    cliente: _rec(d.ClienteDestino || "", 26),
    domicilio: _rec(d.DomicilioDestino || "", 30),
    origen: _rec(d.RazonSocial || "", 26),
    recorrido: (d.Recorrido || "").toString().trim(),
    posicion: (hdr.Posicion || hdr.Posicion_retiro || "").toString().trim(),
    cant: (d.Cantidad || "1").toString().trim(),
    fecha: p2(hoy.getDate()) + "/" + p2(hoy.getMonth() + 1) + "/" + hoy.getFullYear(),
  };
}

// Rótulo 6,5 x 3,2 cm @ 203 dpi (520 x 256 pts) - mismo formato que la etiqueta
// de colecta: logo + QR en columna izquierda, texto a la derecha desde X=200.
// FIX (2026-10-29): imprimía un solo rótulo "Bulto: 1/N" con el código sin sufijo. Con más de
// un bulto la app de reparto espera BASE_1, BASE_2... (uno por bulto): ahora sale un rótulo por
// bulto con su sufijo, igual que el panel de Seguimiento (Funciones/js/seguimiento.js).
function _rotuloZPL(x, i, total) {
  var csEtiqueta = total > 1 ? x.cs + "_" + i : x.cs;
  return (
    "^XA^PW520^LL256^LH0,0^CI28" +
    CADDY_LOGO_ZPL +
    "^FO215,10^A0N,20,20^FD" + x.cliente + "^FS" +
    "^FO215,35^A0N,18,18^FD" + x.domicilio + "^FS" +
    "^FO215,57^A0N,18,18^FDId: " + csEtiqueta + "^FS" +
    "^FO215,79^A0N,18,18^FDOrigen: " + x.origen + "^FS" +
    "^FO215,101^A0N,18,18^FDBulto: " + i + "/" + total + "^FS" +
    "^FO215,123^A0N,18,18^FDFecha: " + x.fecha + "^FS" +
    "^FO215,148^A0N,30,30^FDRec: " + (x.recorrido || "-") + "^FS" +
    "^FO215,185^A0N,26,26^FDPos: " + (x.posicion || "-") + "^FS" +
    // FIX (2026-09-14): mismo ajuste que Funciones/js/seguimiento.js - todo
    // el bloque (logo, QR, texto) corrido 15 dots a la derecha, el QR
    // arrancaba pegado al margen izquierdo físico del rótulo (ver ese
    // archivo para el detalle completo).
    "^FO45,74^BQN,2,7^FDQA," + csEtiqueta + "^FS" +
    "^XZ"
  );
}

// FIX (a pedido, 2026-09-16): antes esto abría un modal (preview + botón
// "Imprimir" + estado de la impresora) - un paso manual de más. Ahora es
// una sola acción: intenta mandar el ZPL directo por Zebra, y si no hay
// impresora disponible por cualquier motivo (SDK no instalado, no se
// encontró un dispositivo, o falló el envío) cae automáticamente en abrir
// el PDF del rótulo (verrotulo()) - nunca deja al operador sin nada.
function imprimirRotuloConFallback() {
  var x = _rotuloDatos();
  if (!x.cs) {
    toast("error", "Rótulo", "No hay un código de seguimiento cargado.");
    return;
  }

  var total = Math.max(1, parseInt(x.cant, 10) || 1);

  // Todos los bultos seguidos; si falla alguno se abre una sola vez el PDF (trae todos los bultos).
  function enviarAZebra(device) {
    var listos = 0;
    var fallo = false;
    for (var i = 1; i <= total; i++) {
      device.send(
        _rotuloZPL(x, i, total),
        function () {
          if (++listos === total && !fallo) toast("success", "Rótulo", total > 1 ? "Enviados " + total + " rótulos a la impresora." : "Enviado a la impresora.");
        },
        function () {
          if (fallo) return;
          fallo = true;
          toast("warning", "Rótulo", "No se pudo imprimir por Zebra - se abrió el PDF.");
          verrotulo();
        },
      );
    }
  }

  if (typeof BrowserPrint === "undefined") {
    verrotulo();
    return;
  }
  if (zebraDevice) {
    enviarAZebra(zebraDevice);
    return;
  }
  // Sin dispositivo detectado todavía (recién se abrió la página, o se
  // conectó la impresora después) - un último intento rápido de
  // descubrimiento antes de resignarse al PDF.
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      zebraDevice = device;
      enviarAZebra(device);
    },
    function () {
      verrotulo();
    },
  );
}

// Fotos del envío: las trae Procesos/php/fotos.php desde la app de reparto (solo lectura).
function verFotosYSubir(codigo) {
  $("#codigo_foto_titulo").text(codigo);
  $("#modal_fotos").modal("show");

  $.ajax({
    data: { MuestroFotos: 1, CodigoSeguimiento: codigo },
    type: "POST",
    url: "Procesos/php/fotos.php",
    beforeSend: function () {
      $("#fotos").html('<div class="text-muted m-2">Cargando fotos...</div>');
    },
    success: function (response) {
      $("#fotos").html(response);
    },
    error: function () {
      $("#fotos").html(
        '<div class="text-danger">Error al cargar las fotos.</div>',
      );
    },
  });
}
function cleanValue(v) {
  return (v || "").toString().trim();
}

function showError(msg) {
  Swal.fire({
    icon: "error",
    title: "Atención",
    text: msg,
    confirmButtonText: "OK",
  });
}
function showLoading(title, body) {
  // Ojo: en tu HTML no existe #info-alert-modal-title, existe h4 fijo.
  // Así que actualizamos el body y dejamos el h4 como "Actualizando Información".
  $("#info-alert-body").text(body || "No cierres esta ventana.");
  $("#info-alert-modal").modal("show");
}

function hideLoading() {
  gsOcultarCarga();
}

function safeJsonParse(response) {
  try {
    if (typeof response === "object") return response;
    return JSON.parse(response);
  } catch (e) {
    return null;
  }
}

$("#remito").click(function () {
  let name = $("#inputname").val();
  let id = cleanValue($("#inputcodigo").val());
  let idProveedor = cleanValue($("#inputcodigoproveedor").val());

  // ✅ Si los tres están vacíos, no avanzo
  if (!name && !id && !idProveedor) {
    showError(
      "Ingresá un Código de Seguimiento, Código de Proveedor o el Nombre del Cliente.",
    );
    return;
  }

  if (name != "") {
    gsResetTablas();
    $("#row_search").css("display", "block");
    $("#form_guias").css("display", "none");
    $("#title_search").text(name);

    //TABLA PRE BUSQUEDA
    var datatable_search = $("#search_tabla").DataTable({
      paging: false,
      searching: true,
      ajax: {
        // guias.php vive en /SistemaTriangular/Servicios/, asi que la ruta a
        // Funciones/php/ es "../Funciones/...". Con "../../" se iba a /Funciones/
        // (404) y la busqueda por nombre nunca devolvia nada. Mismo patron que
        // el resto de los ajax de este archivo.
        url: "../Funciones/php/tablas.php",
        data: { Search_Tabla: 1, Variable: name },
        type: "post",
      },
      columns: [
        { data: "Fecha", render: fechaDMY },
        { data: "CodigoSeguimiento" },
        { data: "RazonSocial" },
        { data: "ClienteDestino" },
        { data: "CodigoProveedor" },
        {
          data: "Estado",

          render: function (data, type, row) {
            return type === "display" ? gsEstadoPill(row.Estado) : row.Estado;
          },
        },
        {
          data: "CodigoSeguimiento",

          render: function (data, type, row) {
            return (
              '<a role="button" id="' +
              row.CodigoSeguimiento +
              '" onclick="seguimiento(this.id);" class="action-icon"> <i class="mdi mdi-book-search-outline text-success"></i></a>'
            );
          },
        },
      ],
    });
  } else {
    let id = $("#inputcodigo").val();
    if (id != "") {
      seguimiento(id);
    } else {
      let idProveedor = $("#inputcodigoproveedor").val();
      $.ajax({
        data: { Buscar_CodigoProveedor: 1, CodigoProveedor: idProveedor },
        type: "POST",
        url: "../Funciones/php/tablas.php",
        dataType: "text",
        timeout: 20000, // ✅ evita que quede colgado eterno
        beforeSend: function () {
          showLoading("Buscando", "Buscando datos del proveedor...");
        },
        success: function (response) {
          const jsonData = safeJsonParse(response);

          if (!jsonData) {
            showError(
              "Respuesta inválida del servidor. Revisá logs / PHP errors.",
            );
            return;
          }

          const envios = jsonData.envios || [];
          if (jsonData.success == 1 && envios.length > 1) {
            // El número de pedido se repite (mismo cliente o, peor, clientes distintos): que elija.
            elegirEnvioProveedor(idProveedor, envios, jsonData.clientes);
          } else if (jsonData.success == 1 && jsonData.CodigoSeguimiento) {
            seguimiento(jsonData.CodigoSeguimiento);
          } else {
            showError(
              "No existen datos para el código del proveedor: " + idProveedor,
            );
          }
        },
        error: function (xhr, status) {
          const detail =
            xhr && xhr.responseText ? xhr.responseText.substring(0, 300) : "";
          showError(
            "No se pudo consultar. (" +
              status +
              ") " +
              (detail ? "Detalle: " + detail : ""),
          );
        },
        complete: function () {
          hideLoading(); // ✅ se cierra SIEMPRE
        },
      });
    }
  }
});

function seguimiento(cs) {
  var id = cs;
  $("#inputcodigo").val(id);

  if (id != "") {
    gsResetTablas();
    $("#row_search").css("display", "none");

    // FIX (reportado: "queda pensando" al buscar): si esta respuesta venía
    // con un warning de PHP mezclado (JSON inválido) o la request fallaba,
    // el JSON.parse de acá tiraba una excepción sin capturar y el modal
    // "Buscando Visitas" nunca se cerraba - quedaba ahí para siempre. Con
    // dataType:"json" jQuery valida el parseo y cae a error() en vez de
    // reventar en success(); igual se agrega un timeout de respaldo que
    // cierra el modal solo si por lo que sea nada más lo hizo.
    var _modalVisitasTimeout = setTimeout(function () {
      gsOcultarCarga();
    }, 12000);
    $.ajax({
      data: { Seguimiento_Visitas: 1, CodigoSeguimiento: id },
      type: "POST",
      url: "../Funciones/php/tablas.php",
      dataType: "json",
      beforeSend: function () {
        // setting a timeout
        $("#info-alert-modal").modal("show");
        $("#info-alert-modal-title").html("Buscando Visitas");
      },
      success: function (jsonData) {
        clearTimeout(_modalVisitasTimeout);
        if (jsonData.success == 1) {
          gsOcultarCarga();
          var visitas = parseInt(jsonData.Visitas, 10) || 0;
          $("#gs_k_visitas").text(visitas);
          $("#gs_k_visitas_foot").text(
            visitas === 1 ? "intento de entrega" : visitas ? "intentos de entrega" : "todavía sin visitas",
          );
          document.getElementById("ficha_seguimiento").style.display = "block";
          document.getElementById("form_guias").style.display = "none";
          $("#pagina").css("display", "block").addClass("active");
          $(".breadcrumb-item active")
            .removeClass("breadcrumb-item active")
            .addClass("breadcrumb-item");
        } else if (jsonData.preventa) {
          // No es un envío pero sí un pedido que entró: decir en qué quedó
          gsOcultarCarga();
          const p = jsonData.preventa;
          const pedido = (p.Observaciones || "") + (p.idProveedor ? " (nº " + p.idProveedor + ")" : "");
          let estado;
          if (Number(p.Eliminado) === 1) {
            estado = p.EliminoUsuario
              ? "Se <b>eliminó en Pendientes</b> sin aceptarse: lo eliminó <b>" + gsEsc(p.EliminoUsuario) + "</b> el " + gsEsc(fechaDMY(String(p.EliminoFecha).slice(0, 10), "display")) + " a las " + gsEsc(String(p.EliminoFecha).slice(11, 16)) + "."
              : "Se <b>eliminó en Pendientes</b> sin aceptarse (antes del 30/10/2026 no se registraba quién).";
          } else if (Number(p.Cargado) === 1) {
            estado = "Se aceptó, pero el envío no está activo (puede haberse eliminado después).";
          } else {
            estado = "Está en <b>Pendientes</b>, todavía sin aceptar.";
          }
          Swal.fire({
            icon: "info",
            title: "El código " + gsEsc(id) + " no es un envío",
            html:
              "Es un pedido que entró el " + gsEsc(fechaDMY(p.Fecha, "display")) + " de <b>" + gsEsc(p.RazonSocial) + "</b> para <b>" + gsEsc(p.ClienteDestino) + "</b>" +
              (pedido.trim() ? "<br><small>" + gsEsc(pedido) + "</small>" : "") + "<br><br>" + estado,
          });
        } else {
          gsOcultarCarga();
          toast("error", "Error !", "No existen datos para el codigo " + id);
        }
      },
      error: function () {
        clearTimeout(_modalVisitasTimeout);
        gsOcultarCarga();
        toast("error", "Error del servidor", "No se pudo buscar el código. Reintentá de nuevo.");
      },
    });

    $.ajax({
      data: { Seguimiento_Modal: 1, CodigoSeguimiento: id },
      type: "POST",
      url: "../Funciones/php/tablas.php",
      dataType: "json",
      success: function (jsonData) {
        // guardo los datos del servicio para el Rotulo (Zebra)
        window.seguimientoData = jsonData.data[0];
        window.seguimientoHdr = jsonData[1] || {};
        window.seguimientoCS = id;

        if (jsonData.data[0].Entregado == 1) {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-success",
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-success",
          );
        } else {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-primary",
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-primary",
          );
        }

        var d = jsonData.data[0];

        // ENCABEZADO: código, guía y ruta + acciones
        $("#gs_titulo").html(
          '<span class="gs-code">' + gsEsc(id) + "</span>" +
            '<button type="button" class="gs-copy" title="Copiar código" onclick="navigator.clipboard && navigator.clipboard.writeText(\'' +
            gsEsc(id) + "'); toast('success','Copiado','" + gsEsc(id) + "')\"><i class=\"mdi mdi-content-copy\"></i></button>",
        );
        $("#gs_sub").html(
          "Guía " + gsEsc(d.NumeroComprobante) + " · " + gsEsc(d.RazonSocial) +
            ' <i class="mdi mdi-arrow-right"></i> ' + gsEsc(d.ClienteDestino),
        );
        $("#gs_header, #gs_acciones").prop("hidden", false);
        gsGuardarReciente(id, d.ClienteDestino, d.Estado);
        $("#gs_fotos_count").text("…").removeClass("on");

        // ORIGEN / DESTINO
        function gsParte(domicilio, telefono) {
          return (
            '<div class="gs-parte-linea"><i class="mdi mdi-map-marker-outline"></i><span>' + gsEsc(domicilio) + "</span></div>" +
            (gsVacio(telefono)
              ? ""
              : '<div class="gs-parte-linea"><i class="mdi mdi-phone-outline"></i><span>' + gsEsc(telefono) + "</span></div>")
          );
        }
        $("#ficha_cliente_origen_seguimiento").text(d.RazonSocial || "");
        $("#ficha_cliente_origen_direcccion_seguimiento").html(gsParte(d.DomicilioOrigen, d.TelefonoOrigen));
        $("#ficha_cliente_destino_seguimiento").text(d.ClienteDestino || "");
        $("#ficha_cliente_destino_direcccion_seguimiento").html(gsParte(d.DomicilioDestino, d.TelefonoDestino));
        $("#ficha_header_title_guia_seguimiento").text("Guía " + (d.NumeroComprobante || ""));

        // KPIs
        $("#gs_k_fecha").text(gsFecha(d.Fecha) || "–");
        $("#gs_k_fecha_foot").text(gsVacio(d.FechaPrometida) ? "Sin fecha prometida" : "Prometida: " + gsFecha(d.FechaPrometida));
        $("#gs_k_bultos").text(d.Cantidad || "–");
        $("#gs_k_bultos_foot").text(gsVacio(d.EntregaEn) ? " " : "Entrega en " + String(d.EntregaEn).toLowerCase());
        $("#gs_k_recorrido").text(gsVacio(d.Recorrido) ? "Sin asignar" : d.Recorrido);
        $("#gs_k_recorrido_foot").text(
          [gsVacio(d.Transportista) ? "" : d.Transportista, gsVacio(d.Kilometros) ? "" : d.Kilometros + " km"]
            .filter(Boolean)
            .join(" · ") || " ",
        );

        if (jsonData.data[0].CobrarEnvio == 0) {
          var cobrarenvio = "No";
          var cobrarenviochecked = "";
        } else {
          var cobrarenvio = "Si";
          var cobrarenviochecked = "checked";
        }
        if (jsonData.data[0].CobrarCaddy == 0) {
          var cobrarcaddy = "No";
          var cobrarcaddychecked = "";
        } else {
          var cobrarcaddy = "Si";
          var cobrarcaddychecked = "checked";
        }
        if (jsonData[1].Estado == "Abierto") {
          var estadochecked = "checked";
        } else {
          var estadochecked = "";
        }
        // Abrir/cerrar en la Hoja de Ruta es logística: no depende de que esté facturado (solo de entregado)
        var disHdr = jsonData.data[0].Entregado == 1 ? "disabled" : "";

        if (jsonData.data[0].FormaDePago == "Origen") {
          $("#pagaorigen").css("display", "block");
          var formadepago = "Origen";
          var formadepagochecked = "";
        } else {
          $("#pagadestino").css("display", "block");
          var formadepago = "Destino";
          var formadepagochecked = "checked";
        }

        if (jsonData.data[0].Facturado == 1) {
          $("#title_aforo").html(
            jsonData.data[0].ComprobanteF + " " + jsonData.data[0].NumeroF,
          );
        }

        if (
          jsonData.data[0].Facturado == 1 ||
          jsonData.data[0].Entregado == 1
        ) {
          var dis = "disabled";
        } else {
          var dis = "";
        }
        var Estado = jsonData.data[0].Estado;

        // fallback: estado de TransClientes hasta que Compruebo traiga el ultimo real
        pintarUltimoEstadoBadge(Estado);

        //COMPRUEBO ULTIMO ESTADO
        $.ajax({
          data: { Compruebo: 1, CodigoSeguimiento: id },
          type: "POST",
          url: "../Funciones/php/tablas.php",
          success: function (response) {
            var jsonDataEstado = JSON.parse(response);
            EstadoSeguimiento = jsonDataEstado.data;

            state = jsonDataEstado.data;
            pintarUltimoEstadoBadge(state);

            // Sin movimientos de seguimiento no hay nada que corregir: antes mostraba la alerta
            // igual y "Corregir" dejaba el estado de transacciones vacío.
            var incoherente = !gsVacio(state) && state != Estado;
            $("#alert").css("display", incoherente ? "inline-block" : "none");
            $("#gs_estado_sub").html(
              gsVacio(state)
                ? "Sin movimientos de seguimiento: estado de transacciones"
                : incoherente
                  ? '<span class="gs-status-alert" onclick="$(\'#info-alert\').modal(\'show\')">' +
                    '<i class="mdi mdi-alert-octagon"></i> Transacciones dice "' + gsEsc(Estado) + '" · corregir</span>'
                  : "Último movimiento de seguimiento",
            );
          },
        });

        var flex = jsonData.Flex == 1 ? "Flex" : "Simple";
        var warehouse = (function (f, h, st) {
          f = String(f || "").trim();
          if (f === "" || f === "0000-00-00") return "Sin registro";
          h = String(h || "").trim().slice(0, 5);
          st = String(st || "").trim() || "Ingreso";
          return st + " · " + f.split("-").reverse().join(".") + (h ? " " + h : "");
        })(d.Wepoint_f, d.Wepoint_h, d.Wepoint_status);

        function gsDato(label, valor, extra) {
          return (
            '<div class="gs-dato"><span>' + label + "</span><b>" +
            (gsVacio(valor) ? '<span class="text-muted fw-normal">—</span>' : valor) + (extra || "") + "</b></div>"
          );
        }
        function gsSwitch(idc, label, valor, checked, disabled, valorInput) {
          return (
            '<div class="gs-switch' + (disabled ? ' locked" title="Bloqueado: ' + motivoBloqueo + '"' : '"') + ">" +
            '<label for="' + idc + '_c">' + (disabled ? '<i class="mdi mdi-lock-outline"></i> ' : "") + label + "</label>" +
            '<div class="form-check form-switch"><b id="' + idc + '_b">' + valor + "</b>" +
            '<input type="checkbox" role="switch" class="form-check-input" id="' + idc + '_c" ' + checked +
            ' value="' + gsEsc(valorInput) + '" ' + disabled + "></div></div>"
          );
        }
        var valorDeclarado = parseFloat(d.ValorDeclarado);
        var motivoBloqueo =
          d.Facturado == 1 && d.Entregado == 1 ? "el envío ya está facturado y entregado"
            : d.Facturado == 1 ? "el envío ya está facturado" : "el envío ya está entregado";

        $("#ficha_info_guia_seguimiento").html(
          '<div class="gs-chips">' +
            '<span class="gs-chip ' + (jsonData.Flex == 1 ? "flex" : "simple") + '" id="' + d.id +
            '" onclick="changeservice(this.id)" title="Cambiar entre Simple y Flex">' +
            '<i class="mdi mdi-swap-horizontal"></i>' + flex + "</span>" +
            '<span class="gs-chip"><i class="mdi mdi-cash"></i>Paga <span id="gs_chip_paga">' + gsEsc(formadepago) + "</span></span>" +
            "</div>" +
            '<div class="gs-datos">' +
            gsDato("Código", '<span class="gs-code">' + gsEsc(id) + "</span>") +
            gsDato("N° de guía", gsEsc(d.NumeroComprobante)) +
            gsDato("Cód. proveedor", gsEsc(d.CodigoProveedor)) +
            gsDato(
              "Fecha",
              '<span id="fecha_transclientes">' + gsFecha(d.Fecha) + "</span>",
              ' <i id="alert_date" class="mdi mdi-calendar-edit text-warning ms-1" title="Modificar la fecha del envío"></i>',
            ) +
            gsDato(
              "Estado (transacciones)",
              '<span id="estado_transclientes">' + gsEsc(d.Estado) + "</span>",
              ' <i id="alert" style="display:none" class="mdi mdi-alert-octagon text-danger ms-1" title="No coincide con el último movimiento de seguimiento: tocá para corregir"></i>',
            ) +
            gsDato("Valor declarado", isNaN(valorDeclarado) || !valorDeclarado ? "" : "$ " + valorDeclarado.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 })) +
            gsDato("Kilómetros", gsEsc(d.Kilometros)) +
            gsDato("Warehouse (WePoint)", gsEsc(warehouse)) +
            "</div>" +
            (gsVacio(d.Observaciones) ? "" : '<div class="gs-obs"><span>Observaciones</span>' + gsEsc(d.Observaciones) + "</div>") +
            '<div class="gs-seccion">Cobro y hoja de ruta</div>' +
            (dis
              ? '<div class="gs-lock-note"><i class="mdi mdi-lock-outline"></i><span>Forma de pago y cobros no se pueden cambiar: ' +
                motivoBloqueo + ".</span></div>"
              : "") +
            gsSwitch("formadepago", "Forma de pago", gsEsc(formadepago), formadepagochecked, dis, d.FormaDePago) +
            gsSwitch("cobrarenvio", "Cobrar envío al entregar", cobrarenvio, cobrarenviochecked, dis, d.CobrarEnvio) +
            gsSwitch("cobrarcaddy", "Cobrar Caddy", cobrarcaddy, cobrarcaddychecked, dis, d.CobrarCaddy) +
            gsSwitch("estadohdr", "Abierto en hoja de ruta", gsEsc(jsonData[1].Estado || "—"), estadochecked, disHdr, jsonData[1].Estado),
        );

        $("#alert_date").click(function () {
          $("#date-modal").modal("show");
          var fecha_ordenada = jsonData.data[0].Fecha.split("-")
            .reverse()
            .join("/");
          $("#date_current").html("Fecha Actual: " + fecha_ordenada);
        });

        $("#alert").click(function () {
          $("#info-alert").modal("show");
        });

        $("#btn_corregir").off("click").click(function () {
          $.ajax({
            data: {
              Corregir_estado: 1,
              CodigoSeguimiento: id,
              Estado: EstadoSeguimiento,
            },
            type: "POST",
            url: "Procesos/php/fotos.php",
            success: function (response) {
              var jsonData = JSON.parse(response);
              if (jsonData.success == 1) {
                $("#estado_transclientes").text(EstadoSeguimiento);
                $("#alert").css("display", "none");
              }
            },
          });
        });

        // Los botones de los modales fijos se vuelven a enganchar en cada búsqueda: .off() antes, si no
        // quedaban acumulados y "Aceptar" se ejecutaba también sobre el envío abierto antes.
        $("#cambiar-modal-close").off("click").click(function () {
          console.log("solucion", $("#formadepago_c").val());
          if ($("#formadepago_c").val() == "Origen") {
            $("#formadepago_c").prop("checked", null);
          } else {
            $("#formadepago_c").prop("checked", true);
          }
        });

        $("#formadepago_c").change(function () {
          val = $("#formadepago_c").val();
          $("#cambiar-modal").modal("show");

          $.ajax({
            data: { Pagador: 1, CodigoSeguimiento: id },
            type: "POST",
            url: "Procesos/php/fotos.php",
            success: function (response) {
              var jsonDataPagador = JSON.parse(response);
              if (jsonDataPagador.success == 1) {
                console.log("Forma de Pago", jsonDataPagador.FormaDePago);
                if (jsonDataPagador.FormaDePago == "Origen") {
                  Pagador = jsonData.data[0].RazonSocial;
                  NoPagador = jsonData.data[0].ClienteDestino;
                  $("#forma_de_pago_texto_1").html(
                    "Pagador Actual (" +
                      Pagador +
                      ") => Nuevo Pagador (" +
                      NoPagador +
                      ")",
                  );
                } else {
                  Pagador = jsonData.data[0].ClienteDestino;
                  NoPagador = jsonData.data[0].RazonSocial;
                  $("#forma_de_pago_texto_1").html(
                    "Pagador Actual (" +
                      Pagador +
                      ") => Nuevo Pagador (" +
                      NoPagador +
                      ")",
                  );
                }
              }
            },
          });

          $("#forma_de_pago_texto").html("Estas por cambiar de Pagador...");
        });

        $("#cambiar-modal-ok").off("click").click(function () {
          $("#cambiar-modal").modal("hide");
          if (val == "Origen") {
            var formadepago_valor = "Destino";
            $("#pagadestino").css("display", "block");
            $("#pagaorigen").css("display", "none");
            $("#formadepago_c").val("Destino");
          } else {
            $("#pagadestino").css("display", "none");
            $("#pagaorigen").css("display", "block");
            var formadepago_valor = "Origen";
            $("#formadepago_c").val("Origen");
          }
          $.ajax({
            data: {
              FormaDePago: 1,
              CodigoSeguimiento: id,
              FormaDePago_valor: formadepago_valor,
            },
            type: "POST",
            url: "Procesos/php/fotos.php",
            beforeSend: function () {
              // setting a timeout
              $("#info-alert-modal").modal("show");
              $("#info-alert-modal-title").html(
                "Actualizando Forma de Pago...",
              );
            },
            success: function (response) {
              var jsonData = JSON.parse(response);
              console.log("fdp", jsonData);
              if (jsonData.success == 1) {
                $("#formadepago_c").val(formadepago_valor);
                $("#formadepago_b, #gs_chip_paga").text(formadepago_valor);
                gsOcultarCarga();
                toast("success", "Exito", "Modificamos Forma De Pago");
              }
            },
          });
        });

        $("#cobrarcaddy_c").change(function () {
          var val = $("#cobrarcaddy_c").val();

          if (val == 0) {
            var cobrarcaddy_valor = 1;
          } else {
            var cobrarcaddy_valor = 0;
          }

          $.ajax({
            data: {
              CobrarCaddy: 1,
              CodigoSeguimiento: id,
              CobrarCaddy_valor: cobrarcaddy_valor,
            },
            type: "POST",
            url: "Procesos/php/fotos.php",
            beforeSend: function () {
              // setting a timeout
              $("#info-alert-modal").modal("show");
              $("#info-alert-modal-title").html("Actualizando Cobrar Caddy...");
            },
            success: function (response) {
              var jsonData = JSON.parse(response);
              if (jsonData.success == 1) {
                $("#cobrarcaddy_c").val(cobrarcaddy_valor);
                $("#cobrarcaddy_b").text(cobrarcaddy_valor == 1 ? "Si" : "No");
                gsOcultarCarga();
                toast("success", "Exito", "Modificamos Cobrar Caddy");
              }
            },
          });
        });

        $("#estadohdr_c").change(function () {
          var val = $("#estadohdr_c").val();

          if (val == "Abierto") {
            var estadohdr_valor = "Cerrado";
          } else {
            if (state == "Devuelto al Cliente") {
              alert(
                "Este paquete ya fue devuelto al cliente, realemnte lo incluiras en una hoja de ruta?",
              );
            }
            var estadohdr_valor = "Abierto";
          }
          $.ajax({
            data: {
              EstadoHDR: 1,
              CodigoSeguimiento: id,
              EstadoHDR_valor: estadohdr_valor,
            },
            type: "POST",
            url: "Procesos/php/fotos.php",
            beforeSend: function () {},

            success: function (response) {
              try {
                var jsonData = JSON.parse(response);
                if (jsonData.success == 1) {
                  $("#estadohdr_c").val(estadohdr_valor);
                  $("#estadohdr_b").text(estadohdr_valor);

                  toast("success", "Éxito", "Modificamos el estado en Hoja de Ruta");
                  // actualizar DOM si es necesario
                }
              } catch (e) {
                console.error("Error JSON:", e, response);
              }
            },
            complete: function () {
              // gsOcultarCarga();
            },
          });
        });

        $("#cobrarenvio_c").change(function () {
          var val = $("#cobrarenvio_c").val();

          if (val == 0) {
            var cobrarenvio_valor = 1;
          } else {
            var cobrarenvio_valor = 0;
          }

          $.ajax({
            data: {
              CobrarEnvio: 1,
              CodigoSeguimiento: id,
              CobrarEnvio_valor: cobrarenvio_valor,
            },
            type: "POST",
            url: "Procesos/php/fotos.php",
            beforeSend: function () {
              // setting a timeout
              $("#info-alert-modal").modal("show");
              $("#info-alert-modal-title").html("Actualizando Cobrar Envio...");
            },
            success: function (response) {
              var jsonData = JSON.parse(response);
              if (jsonData.success == 1) {
                $("#cobrarenvio_c").val(cobrarenvio_valor);
                $("#cobrarenvio_b").text(cobrarenvio_valor == 1 ? "Si" : "No");
                gsOcultarCarga();
                toast("success", "Exito", "Modificamos Cobrar Envio");
              }
            },
          });
        });

        gsCargarFacturacion(id);

        // REPOSICIONES DINTER (a pedido, 2026-09-16): tabla chica, no hace
        // falta un DataTable completo - se pide una vez y se muestra la
        // card solo si hay algo (la mayoría de los envíos no tienen repo).
        $.ajax({
          url: "../Logistica/Proceso/php/etiquetas_recorrido.php",
          type: "post",
          data: { ReposicionesDeCodigo: 1, CodigoSeguimiento: id },
          success: function (response) {
            var res = typeof response === "string" ? JSON.parse(response) : response;
            var filas = res.data || [];
            if (!filas.length) {
              $("#repo_dinter_col").prop("hidden", true);
              return;
            }
            var html = "";
            filas.forEach(function (r) {
              html +=
                "<tr>" +
                "<td>" + fechaDMY(r.Fecha, "display") + "</td>" +
                "<td>" + (r.Hora || "").substring(0, 5) + "</td>" +
                '<td class="text-center">+' + r.CantidadBultos + "</td>" +
                "<td>" + (r.Usuario || "-") + "</td>" +
                "</tr>";
            });
            $("#repo_dinter_tabla tbody").html(html);
            $("#repo_dinter_col").prop("hidden", false);
          },
          error: function () {
            $("#repo_dinter_col").prop("hidden", true);
          },
        });

        gsCargarHistorial(id);

        //TABLA WEBHOOK
        var datatable_webhook = $("#webhook_tabla").DataTable({
          paging: false,
          searching: false,
          ajax: {
            url: "../Funciones/php/tablas.php",
            data: { Webhook: 1, CodigoSeguimiento: id },
            type: "post",
          },
          // Si el envío no tiene webhooks, no ensuciamos la pantalla: se oculta
          // toda la card. Se muestra sólo cuando hay al menos una notificación.
          drawCallback: function (settings) {
            var hay = settings.json && settings.json.data && settings.json.data.length > 0;
            $("#webhook_card").toggle(!!hay);
          },
          columns: [
            { data: "Fecha", render: fechaDMY },
            { data: "Hora" },
            { data: "User" },
            { data: "Servidor" },
            { data: "Estado" },
            { data: "State" },
            {
              data: "Response",
              render: function (data, type, row) {
                if (row.Response == 200) {
                  var color = "success";
                } else {
                  color = "danger";
                }
                // var Fecha = row.Fecha.split('-').reverse().join('.');
                return (
                  '<td><a class="text-' +
                  color +
                  '">' +
                  row.Response +
                  "</span></td>"
                );
              },
            },
          ],
        });

        $.ajax({
          data: { ContarFotos: 1, CodigoSeguimiento: id },
          type: "POST",
          url: "Procesos/php/fotos.php",
          dataType: "json",
          success: function (res) {
            var n = res && res.total;
            $("#gs_fotos_count")
              .text(n === null || n === undefined ? "?" : n)
              .toggleClass("on", n > 0);
          },
          error: function () {
            $("#gs_fotos_count").text("?");
          },
        });

        gsOcultarCarga();
      },
      error: function (err) {
        console.log("error", err);
        gsOcultarCarga();
        toast("error", "Error del servidor", "No se pudo cargar el detalle del envío. Reintentá de nuevo.");
      },
    });
  } else {
    alert("no hay codigo");
  }
}
let id_seguimiento_a_eliminar = null;
// Al hacer clic en el ícono
function ver_seguimiento(id) {
  id_seguimiento_a_eliminar = id;
  // Un "Entregado" de una orden ya cerrada no se borra: se corrige a "No se pudo entregar"
  // con motivo, para que el repartidor conserve la visita (ver Funciones/php/tablas.php).
  $.post("../Funciones/php/tablas.php", { InfoEliminarSeguimiento: 1, id: id })
    .done(function (r) {
      var info = {};
      try { info = JSON.parse(r); } catch (e) {}
      if (info.requiere_motivo == 1) {
        corregir_entrega(id, info);
      } else {
        new bootstrap.Modal(document.getElementById("modal_confirmar_eliminacion")).show();
      }
    })
    .fail(function () {
      toast("error", "Error!", "No se pudo consultar el movimiento");
    });
}

function corregir_entrega(id, info) {
  Swal.fire({
    icon: "warning",
    title: "Corregir entrega",
    html:
      "Esta entrega es de la orden <b>" + info.orden + "</b>, que ya está cerrada.<br>" +
      "No se borra: queda como <b>No se pudo entregar</b> a nombre de <b>" + info.usuario +
      "</b> (" + info.fecha + " " + info.hora + "), para que su visita quede registrada en la rendición, " +
      "y el envío sale de la orden para poder rutearlo de nuevo.",
    input: "textarea",
    inputLabel: "¿Por qué no se entregó?",
    inputPlaceholder: "Ej: nadie en domicilio",
    inputValidator: (v) => (!v || !v.trim() ? "Indicá el motivo" : undefined),
    showCancelButton: true,
    confirmButtonText: "Corregir",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#fa5c7c",
  }).then(function (res) {
    if (!res.isConfirmed) return;
    $.post("../Funciones/php/tablas.php", { EliminarSeguimiento: 1, id: id, Motivo: res.value.trim() })
      .done(function (r) {
        var jsonData = {};
        try { jsonData = JSON.parse(r); } catch (e) {}
        if (jsonData.success == 1) {
          toast("success", "Listo!", "Entrega corregida a No se pudo entregar");
          gsCargarHistorial(window.seguimientoCS);
        } else {
          toast("error", "Error!", jsonData.error || "No se pudo corregir la entrega");
        }
      })
      .fail(function () {
        toast("error", "Error!", "No se pudo corregir la entrega");
      });
  });
}

$("#btn_confirmar_eliminacion").click(function () {
  if (!id_seguimiento_a_eliminar) return;

  $("#id_seguimiento_a_eliminar").html(id_seguimiento_a_eliminar);

  $.ajax({
    data: { EliminarSeguimiento: 1, id: id_seguimiento_a_eliminar },
    type: "POST",
    // guias.php vive en /SistemaTriangular/Servicios/, asi que la ruta a
    // Funciones/php/ es "../Funciones/..." (con "../../" se iba a /Funciones/,
    // fuera de SistemaTriangular, y la request daba 404 -> el borrado no
    // hacia nada porque JSON.parse tiraba sobre el HTML del error).
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        toast("success", "Listo!", "Seguimiento Eliminado");
        gsCargarHistorial(window.seguimientoCS);
      } else {
        toast("error", "Error!", "No se pudo eliminar el seguimiento");
      }
    },
    error: function () {
      toast("error", "Error!", "No se pudo eliminar el seguimiento");
    },
  });
});

// Modal "Agregar movimiento": estados como opciones con su color; arranca limpio cada vez.
var GS_ESTADOS_REGISTRO = [
  "A Retirar", "En Origen", "Retirado del Cliente",
  "No se pudo Retirar", "Cargado en Hoja De Ruta", "En Transito",
  "Entregado al Cliente", "No se pudo entregar", "Devuelto al Cliente",
];
$("#enter_registration").click(function () {
  $("#gs_reg_codigo").text(window.seguimientoCS || "");
  $("#gs_reg_estados").html(
    GS_ESTADOS_REGISTRO.map(function (e) {
      return '<button type="button" class="gs-estado-opt" data-estado="' + gsEsc(e) + '" style="--gs-c:' + gsColorEstado(e) + '">' + gsEsc(e) + "</button>";
    }).join(""),
  );
  $("#enter_registration_state").val("").trigger("change");
  $("#enter_registration_obs").val("");
  $("#enter_registration_user").val("").trigger("change");
  $("#fecha_entrega, #hora_entrega").val("");
  $("#enter_registration_save").prop("disabled", false);
  $("#enter_registration_seguimiento-modal").modal("show");
});
$(document).on("click", "#gs_reg_estados .gs-estado-opt", function () {
  $("#gs_reg_estados .gs-estado-opt").removeClass("active");
  $(this).addClass("active");
  $("#enter_registration_state").val($(this).data("estado")).trigger("change");
});

// Los selects de Estado y Usuario se auto-inicializan como select2 al cargar
// la pagina (app.js, [data-toggle="select2"]), sin dropdownParent. Select2
// por defecto cuelga su desplegable (con el buscador) de <body>, AFUERA del
// modal - el focus-trap de Bootstrap 5 (que fuerza el foco de vuelta al
// modal si detecta que se va a un elemento que no es descendiente suyo) le
// saca el foco al buscador apenas se lo toca: la lista se ve bien, pero no
// se puede escribir para filtrar. Se reinicializan apuntando el dropdown al
// propio modal cada vez que se abre.
$("#enter_registration_seguimiento-modal").on("shown.bs.modal", function () {
  $(this)
    .find("select.select2")
    .each(function () {
      const $sel = $(this);
      if ($sel.data("select2")) {
        $sel.select2("destroy");
      }
      $sel.select2({ dropdownParent: $("#enter_registration_seguimiento-modal"), width: "100%" });
    });
});

$("#enter_registration_state").change(function () {
  const st = $("#enter_registration_state").val();
  const conRepartidor = st == "Entregado al Cliente" || st == "No se pudo entregar";

  if (conRepartidor) {
    $("#enter_registration_user_id").css("display", "block");
    $("#enter_registration_datetime").css("display", "flex");
    // Prellenar fecha/hora con ahora (el operador ajusta si el movimiento fue antes)
    if (!$("#fecha_entrega").val()) {
      const n = new Date();
      const p = (x) => String(x).padStart(2, "0");
      $("#fecha_entrega").val(`${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`);
      $("#hora_entrega").val(`${p(n.getHours())}:${p(n.getMinutes())}`);
    }
    // Cargar usuarios sólo una vez
    if ($("#enter_registration_user optgroup option").length === 0) {
      $.ajax({
        data: { usuarios_registration: 1 },
        url: "Procesos/php/funciones.php",
        type: "POST",
        dataType: "json",
        success: function (data) {
          var $optgroup = $("#enter_registration_user optgroup");
          $.each(data, function (index, user) {
            $optgroup.append($("<option>", { value: user.id, text: user.text }));
          });
          $("#enter_registration_user").trigger("change");
        },
        error: function (error) {
          console.error("Error al obtener usuarios:", error);
        },
      });
    }
  } else {
    $("#enter_registration_user_id").css("display", "none");
    $("#enter_registration_datetime").css("display", "none");
  }
});

//AGREGAR REGISTROS EN TABLA SEGUIMIENTO
$("#enter_registration_save").click(function () {
  let state = $("#enter_registration_state").val();
  let id = window.seguimientoCS;
  let obs = $("#enter_registration_obs").val();
  let user = $("#enter_registration_user").val();
  let fecha_entrega = $("#fecha_entrega").val();
  let hora_entrega = $("#hora_entrega").val();
  const conRepartidor = state == "Entregado al Cliente" || state == "No se pudo entregar";
  if (!state) {
    toast("error", "Falta el estado", "Elegí el estado del movimiento.");
    return;
  }
  if (conRepartidor && !user) {
    toast("error", "Falta el repartidor", "Elegí el repartidor titular del movimiento.");
    return;
  }
  var $btnGuardar = $(this).prop("disabled", true);
  $.ajax({
    data: {
      enter_registration: 1,
      CodigoSeguimiento: id,
      state: state,
      obs: obs,
      user: user,
      fecha_entrega: fecha_entrega,
      hora_entrega: hora_entrega,
    },
    type: "POST",
    url: "Procesos/php/funciones.php",
    complete: function () {
      $btnGuardar.prop("disabled", false);
    },
    error: function () {
      toast("error", "Error", "No se pudo guardar el movimiento. Probá de nuevo.");
    },
    success: function (response) {
      var jsonData = safeJsonParse(response) || {};
      if (jsonData.success != 1) {
        toast("error", "No se pudo guardar", jsonData.error || "Respuesta inesperada del servidor.");
        return;
      }
      if (jsonData.success == 1) {
        toast("success", "Listo", "Movimiento agregado: " + state);
        $("#estado_transclientes").text(state);
        $("#enter_registration_seguimiento-modal").modal("hide");
        gsCargarHistorial(window.seguimientoCS);
        var hdr = jsonData.estadohdr == "Abierto" ? "Abierto" : "Cerrado";
        $("#estadohdr_c").val(hdr).prop("checked", hdr == "Abierto");
        $("#estadohdr_b").text(hdr);

        $.ajax({
          data: { Webhook: 1, state: state, cs: id },
          type: "POST",
          url: "Procesos/php/webhook.php",
          success: function (response) {
            var jsonData = JSON.parse(response);
            console.log(
              "idOrigen",
              jsonData.idOrigen,
              "idDestino",
              jsonData.idDestino,
              "codigo",
              jsonData.codigo,
              "new",
              jsonData.new,
            );
          },
        });
      }
    },
  });
});

// Número de pedido repetido: lista para elegir el envío, con el cliente de origen bien a la vista.
function elegirEnvioProveedor(nro, envios, clientes) {
  if (typeof hideLoading === "function") hideLoading();
  var esc = function (t) { return $("<div>").text(t == null ? "" : t).html(); };
  var aviso = clientes > 1
    ? '<div class="alert alert-warning py-2 text-start"><i class="mdi mdi-alert"></i> <b>Atención:</b> el número de pedido <b>' + esc(nro) +
      "</b> está en envíos de <b>" + clientes + " clientes distintos</b>. Fijate el origen antes de seguir.</div>"
    : '<div class="alert alert-info py-2 text-start">El número de pedido <b>' + esc(nro) + "</b> está en " + envios.length + " envíos.</div>";
  var filas = envios.map(function (e) {
    var f = String(e.Fecha || "").split("-").reverse().join("/");
    return '<button type="button" class="list-group-item list-group-item-action text-start sw-envio-prov" data-cs="' + esc(e.CodigoSeguimiento) + '">' +
      "<div class=\"d-flex justify-content-between\"><b>" + esc(e.RazonSocial) + "</b><small>" + esc(f) + "</small></div>" +
      "<small>" + esc(e.CodigoSeguimiento) + " · " + esc(e.ClienteDestino) + " · " + esc(e.Estado || (Number(e.Entregado) ? "Entregado" : "Pendiente")) + "</small></button>";
  }).join("");
  Swal.fire({
    title: "Número de pedido repetido",
    html: aviso + '<div class="list-group">' + filas + "</div>",
    showConfirmButton: false,
    showCancelButton: true,
    cancelButtonText: "Cancelar",
    width: 560,
    didOpen: function () {
      $(".sw-envio-prov").on("click", function () {
        Swal.close();
        seguimiento($(this).data("cs"));
      });
    },
  });
}
