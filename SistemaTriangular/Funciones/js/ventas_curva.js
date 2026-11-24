// Bonificación por cantidad en los modales de ventas (Clientes, Pendientes, Redespachos), 18/11/2026.
// Mismo criterio que Venta Simple y la API: si el servicio se cobra por bulto y bonifica (switch
// "Bonifica por cantidad" en Servicios) y la cantidad es entera, el total es precio x curva:
// 1 = 1, 2 = 1 (2º bonificado), 3 = 1,5, 4 = 2... Si no, precio x cantidad.
// El servidor lo vuelve a calcular al guardar (Funciones/php/bultos.php: ventaTotalCalculado).
// Cada pantalla setea ventasConCurva al abrir el modal (ConCurva del renglón o del servicio elegido).
var ventasConCurva = false;

function ventasFactorBultos(n) {
  return n <= 2 ? 1 : 1 + 0.5 * (n - 2);
}

function ventasRecalcular() {
  var precio = Number(String($("#ventas_precio").val() || "").replace(",", ".")) || 0;
  var cantidad = Number($("#ventas_cantidad").val()) || 0;
  var curva = ventasConCurva && cantidad > 0 && Number.isInteger(cantidad);
  $("#ventas_total").val((precio * (curva ? ventasFactorBultos(cantidad) : cantidad)).toFixed(2));
  var $aviso = $("#ventas_curva_aviso");
  if (!$aviso.length) {
    $aviso = $('<small id="ventas_curva_aviso" class="form-text text-muted d-block"></small>').insertAfter("#ventas_total");
  }
  // La cantidad es de bultos: para una 2da visita no se sube la cantidad (2 bultos = 1 tarifa, no cobraría
  // nada más); se cobra con el botón "Cobrar 2da visita" de Guías a Facturar (24/11/2026).
  $aviso.html(
    curva && cantidad > 1
      ? "Con bonificación: " + cantidad + " bultos = " + String(ventasFactorBultos(cantidad)).replace(".", ",") + " tarifas" +
        "<br><span class='text-danger'>Una 2da visita no se carga subiendo los bultos: usá «Cobrar 2da visita» en Guías a Facturar.</span>"
      : "",
  );
}

// Al abrir el modal: si el renglón (o el servicio) lleva curva, y sin aviso viejo.
function ventasCurvaReset(conCurva) {
  ventasConCurva = conCurva == 1 || conCurva === true;
  $("#ventas_curva_aviso").text("");
  // Servicio que se cobra por bulto (bonifica): el campo dice "Bultos", así no se usa para una 2da visita.
  // Los que van por unidad u hora (Hora Sprinter, Flete interno, Caja fresh) siguen diciendo "Cantidad". 24/11/2026.
  $("#ventas_cantidad_label").text(ventasConCurva ? "Bultos" : "Cantidad");
}

// Solo si cambia el valor (antes recalculaba con solo pasar por el campo)
$(document).on("change", "#ventas_cantidad, #ventas_precio", ventasRecalcular);

// ── Servicio del modal (18/11) ─────────────────────────────────────────────────────────────────────
// Al modificar una venta, el selector "Servicio" queda con el servicio del renglón (idProducto, que
// devuelve el backend) en vez de "Seleccione una Opcion". Mientras se elige por código, cargar() no
// pisa título, precio ni código (ventasEligiendoServicio).
var ventasEligiendoServicio = false;

// select2 dentro del modal (si no, el buscador del desplegable no toma el foco)
function ventasPrepararSelect() {
  var $sel = $("#servicio");
  if (!$.fn.select2 || $sel.data("vmSelect2")) return;
  if ($sel.hasClass("select2-hidden-accessible")) $sel.select2("destroy");
  $sel.select2({ dropdownParent: $("#bs-ventas-modal-lg"), width: "100%" });
  $sel.data("vmSelect2", 1);
}

// Llena la lista la primera vez (el modal compartido, Funciones/php/modal_ventas.php, la trae vacía).
function ventasCargarServicios(url, cb) {
  var $grupo = $("#servicio optgroup");
  if ($grupo.find("option").length || !$grupo.length) {
    ventasPrepararSelect();
    cb && cb();
    return;
  }
  $.post(url, { ListarServicios: 1 }, null, "json")
    .done(function (res) {
      if (!$grupo.find("option").length) {
        (res && res.data ? res.data : []).forEach(function (p) {
          $grupo.append($("<option>").val(p.id).text(p.Titulo + " | $ " + p.PrecioVenta));
        });
      }
    })
    .always(function () {
      ventasPrepararSelect();
      cb && cb();
    });
}

// Deja elegido el servicio (0 o vacío = "Seleccione una Opcion") sin disparar cargar().
function ventasElegirServicio(idProducto) {
  var $sel = $("#servicio");
  var valor = idProducto && $sel.find('option[value="' + idProducto + '"]').length
    ? String(idProducto)
    : $sel.find("option:first").val();
  ventasEligiendoServicio = true;
  try {
    $sel.val(valor).trigger("change");
  } finally {
    ventasEligiendoServicio = false;
  }
}
