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
    $aviso = $('<small id="ventas_curva_aviso" class="d-block text-white-50"></small>').insertAfter("#ventas_total");
  }
  $aviso.text(
    curva && cantidad > 1
      ? "Bonificación por cantidad: " + cantidad + " bultos = " + ventasFactorBultos(cantidad) + " tarifas"
      : "",
  );
}

// Al abrir el modal: si el renglón (o el servicio) lleva curva, y sin aviso viejo.
function ventasCurvaReset(conCurva) {
  ventasConCurva = conCurva == 1 || conCurva === true;
  $("#ventas_curva_aviso").text("");
}

// Solo si cambia el valor (antes recalculaba con solo pasar por el campo)
$(document).on("change", "#ventas_cantidad, #ventas_precio", ventasRecalcular);
