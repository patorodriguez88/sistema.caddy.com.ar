/* ============================================================
   ASOCIAR PAGO A FACTURA (a pedido, 2026-09-16 - tarea Asana de
   Agustina: "no tengo la opción de matchear un pago con la factura
   correspondiente, como sí se puede hacer en el sistema viejo").
   Calca el flujo ya en producción de Clientes/Procesos/js/cargarpago.js
   (función asociar_pago_comprobante_button + tablas tildables), adaptado
   a Proveedores/TransProveedores.
============================================================ */

function formatoMonedaProveedor(valor) {
  valor = Number(valor || 0);
  return "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

let checkedPagosProv = [];
let checkedPagosidProv = [];
let checkedFacturasProv = [];
let checkedFacturasidProv = [];

function limpiarEstadoAsociacionProv() {
  checkedPagosProv = [];
  checkedPagosidProv = [];
  checkedFacturasProv = [];
  checkedFacturasidProv = [];

  $("#footer_total_facturas_prov").html("$ 0.00");
  $("#footer_total_pagos_prov").html("$ 0.00");
  $("#footer_saldo_prov").html("$ 0.00");
}

// Tablas del modal (DataTables). Se leen SIEMPRE con la API para tomar lo tildado en TODAS las
// páginas: antes se usaba $("#tabla ... :checked"), que solo ve la página visible, y lo tildado en
// otra página dejaba de sumar (24/11/2026).
const AP_TABLA_FACT = "#tabla_asociar-pagos_facturas_prov";
const AP_TABLA_PAGO = "#tabla_asociar-pagos_pagos_prov";

// Lo tildado se guarda por id (no se lee del DOM): así no se pierde al reordenar ni al cambiar de página.
const apSelFact = new Set();
const apSelPago = new Set();

function apTabla(sel) {
  return $.fn.DataTable.isDataTable(sel) ? $(sel).DataTable() : null;
}

// Número de comprobante comparable: solo dígitos, sin ceros adelante (y la última parte, sin el punto
// de venta), para reconocer el pago que el sistema viejo grababa con el mismo número de la factura.
function apNumeros(numero) {
  const txt = String(numero || "");
  const total = txt.replace(/\D/g, "").replace(/^0+/, "");
  const ultimo = (txt.split("-").pop() || "").replace(/\D/g, "").replace(/^0+/, "");
  return [total, ultimo].filter((x) => x.length >= 4);
}

function apTildados(t, sel, campoImporte) {
  const out = { total: 0, ids: [], importes: [], numeros: new Set() };
  if (!t) return out;
  t.rows().every(function () {
    const d = this.data();
    if (!sel.has(String(d.id))) return;
    const imp = Number(d[campoImporte] || 0);
    out.total += imp;
    out.ids.push(String(d.id));
    out.importes.push(imp);
    apNumeros(d.NumeroComprobante).forEach((n) => out.numeros.add(n));
  });
  return out;
}

// Pinta en la tabla "t" los renglones que podrían corresponder a lo tildado en la otra:
// verde = mismo importe que lo que falta cubrir (±$1) o mismo número de comprobante; amarillo =
// importe parecido (±2%). No filtra nada (una diferencia de centavos no deja afuera a nadie).
// Devuelve cuántos pintó. Si "reordenar", deja las coincidencias arriba.
function apPintar(t, sel, campoImporte, objetivo, numerosOtro, reordenar) {
  if (!t) return 0;
  let n = 0;
  t.rows().every(function () {
    const node = $(this.node());
    const d = this.data();
    const tildado = sel.has(String(d.id));
    const saldo = Number(d[campoImporte] || 0);
    let nivel = 0;
    if (!tildado && (objetivo > 0.009 || numerosOtro.size)) {
      const dif = Math.abs(saldo - objetivo);
      const mismoNumero = apNumeros(d.NumeroComprobante).some((x) => numerosOtro.has(x));
      if (mismoNumero || (objetivo > 0.009 && dif <= 1)) nivel = 2;
      else if (objetivo > 0.009 && dif <= Math.max(1, objetivo * 0.02)) nivel = 1;
    }
    d._match = nivel;
    if (nivel) n++;
    node.toggleClass("ap-sel", !!tildado).toggleClass("ap-exacto", nivel === 2).toggleClass("ap-cerca", nivel === 1);
  });
  if (reordenar) {
    // solo se invalida la columna oculta de coincidencia (la del tilde no se vuelve a dibujar)
    t.rows().every(function () { t.cell(this.index(), 5).invalidate("data"); });
    t.order(n ? [[5, "desc"], [0, "asc"]] : [[0, "asc"]]).draw();
  }
  return n;
}

function recalcularAsociacionProv(origen) {
  const tf = apTabla(AP_TABLA_FACT);
  const tp = apTabla(AP_TABLA_PAGO);
  const f = apTildados(tf, apSelFact, "SaldoPendiente");
  const p = apTildados(tp, apSelPago, "SaldoDisponible");

  checkedFacturasProv = f.importes;
  checkedFacturasidProv = f.ids;
  checkedPagosProv = p.importes;
  checkedPagosidProv = p.ids;

  $("#footer_total_facturas_prov").html(formatoMonedaProveedor(f.total));
  $("#footer_total_pagos_prov").html(formatoMonedaProveedor(p.total));
  $("#footer_saldo_prov").html(formatoMonedaProveedor(f.total - p.total));

  // Pagos que cubrirían lo que falta de las facturas tildadas, y facturas que cubrirían lo que sobra de
  // los pagos tildados. Solo se reordena la tabla de enfrente (la que no se está tildando).
  const nPagos = f.ids.length
    ? apPintar(tp, apSelPago, "SaldoDisponible", f.total - p.total, f.numeros, origen === "factura")
    : apPintar(tp, apSelPago, "SaldoDisponible", 0, new Set(), origen === "factura");
  const nFact = p.ids.length
    ? apPintar(tf, apSelFact, "SaldoPendiente", p.total - f.total, p.numeros, origen === "pago")
    : apPintar(tf, apSelFact, "SaldoPendiente", 0, new Set(), origen === "pago");

  $("#ap_hint_pagos").text(nPagos ? `${nPagos} posible${nPagos > 1 ? "s" : ""} coincidencia${nPagos > 1 ? "s" : ""} (arriba)` : "");
  $("#ap_hint_facturas").text(nFact ? `${nFact} posible${nFact > 1 ? "s" : ""} coincidencia${nFact > 1 ? "s" : ""} (arriba)` : "");
}

$(document).on("change", ".chk-factura-prov", function () {
  const id = String($(this).attr("data-id"));
  this.checked ? apSelFact.add(id) : apSelFact.delete(id);
  recalcularAsociacionProv("factura");
});
$(document).on("change", ".chk-pago-prov", function () {
  const id = String($(this).attr("data-id"));
  this.checked ? apSelPago.add(id) : apSelPago.delete(id);
  recalcularAsociacionProv("pago");
});

function comprobanteYNumero(row) {
  return (row.TipoDeComprobante || "") + " - " + (row.NumeroComprobante || "");
}

function fechaDMYProv(iso) {
  return iso ? iso.split("-").reverse().join(".") : "";
}

// Fecha: se muestra dd.mm.aaaa pero se ORDENA por la fecha ISO (antes ordenaba el texto dd.mm.aaaa,
// o sea por día del mes, y las fechas quedaban mezcladas).
function apColFecha() {
  return { data: "Fecha", render: (data, type) => (type === "display" || type === "filter" ? fechaDMYProv(data) : data) };
}
function apColMatch() {
  return { data: "_match", visible: false, searchable: false, defaultContent: 0 };
}
function apAsegurarColumnaOculta(sel) {
  const tr = $(sel + " thead tr");
  if (tr.find("th").length === 5) tr.append("<th>coincide</th>");
}
const AP_DT_OPCIONES = {
  destroy: true,
  pageLength: 10,
  lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
  paging: true,
  searching: true, // buscar por número, importe o descripción
  info: true,
  order: [[0, "asc"]],
  language: { search: "Buscar:", lengthMenu: "_MENU_ por página", info: "_START_–_END_ de _TOTAL_", infoEmpty: "", zeroRecords: "Sin resultados", emptyTable: "No hay comprobantes", paginate: { previous: "‹", next: "›" } },
};

$("#asociar_pago_proveedor_button").click(function () {
  const idProveedor = $("#codigo").val();

  if (!idProveedor) {
    toast("error", "Error", "Elegí un proveedor primero.");
    return;
  }

  $("#asociar-pagos-modal-proveedor").modal("show");
  limpiarEstadoAsociacionProv();
  apSelFact.clear();
  apSelPago.clear();
  $("#ap_hint_pagos, #ap_hint_facturas").text("");

  [AP_TABLA_FACT, AP_TABLA_PAGO].forEach(function (sel) {
    if ($.fn.DataTable.isDataTable(sel)) {
      $(sel).DataTable().destroy();
      $(sel + " tbody").empty();
    }
    apAsegurarColumnaOculta(sel);
  });

  $(AP_TABLA_FACT).DataTable(Object.assign({}, AP_DT_OPCIONES, {
    ajax: {
      url: "Procesos/php/pagos.php",
      data: { Asociar_pago_comprobantes: 1, id: idProveedor },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      apColFecha(),
      { data: "NumeroComprobante", render: (data, type, row) => comprobanteYNumero(row) },
      { data: "Descripcion", render: (data) => data || "" },
      { data: "SaldoPendiente", render: (data, type) => (type === "display" ? formatoMonedaProveedor(data) : Number(data)) },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (data, type, row) =>
          '<div class="form-check mb-0">' +
          '<input data-id="' + row.id + '" data-importe="' + row.SaldoPendiente + '" type="checkbox" class="form-check-input chk-factura-prov"' + (apSelFact.has(String(row.id)) ? " checked" : "") + '>' +
          '<label class="form-check-label">&nbsp;</label></div>',
      },
      apColMatch(),
    ],
  }));

  $(AP_TABLA_PAGO).DataTable(Object.assign({}, AP_DT_OPCIONES, {
    ajax: {
      url: "Procesos/php/pagos.php",
      data: { Asociar_pago_pagos: 1, id: idProveedor },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      apColFecha(),
      { data: "NumeroComprobante", render: (data, type, row) => comprobanteYNumero(row) },
      { data: "Descripcion", render: (data) => data || "" },
      { data: "SaldoDisponible", render: (data, type) => (type === "display" ? formatoMonedaProveedor(data) : Number(data)) },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (data, type, row) =>
          '<div class="form-check mb-0">' +
          '<input data-id="' + row.id + '" data-importe="' + row.SaldoDisponible + '" type="checkbox" class="form-check-input chk-pago-prov"' + (apSelPago.has(String(row.id)) ? " checked" : "") + '>' +
          '<label class="form-check-label">&nbsp;</label></div>',
      },
      apColMatch(),
    ],
  }));
});

$("#asociar-pagos-modal-prov-ok").click(function () {
  if (checkedFacturasidProv.length === 0) {
    toast("error", "Error", "Seleccioná al menos una factura.");
    return;
  }
  if (checkedPagosidProv.length === 0) {
    toast("error", "Error", "Seleccioná al menos un pago.");
    return;
  }

  $.ajax({
    data: {
      Asociar_pagos: 1,
      Pagosid: checkedPagosidProv,
      Facturasid: checkedFacturasidProv,
    },
    url: "Procesos/php/pagos.php",
    type: "post",
    dataType: "json",
    beforeSend: function () {
      toast("info", "Procesando", "Asociando pagos y facturas...");
    },
    success: function (jsonData) {
      if (jsonData.success == 1) {
        toast("success", "Perfecto", jsonData.msg || "Asociación realizada.");
        $("#asociar-pagos-modal-proveedor").modal("hide");

        if ($.fn.DataTable.isDataTable("#basic")) {
          $("#basic").DataTable().ajax.reload(null, false);
        }
      } else {
        toast("error", "Error", jsonData.msg || "No se pudo asociar.");
      }
    },
    error: function (xhr) {
      toast("error", "Error del servidor", xhr.responseText);
    },
  });
});

/* ============================================================
   VER APLICACIONES de un comprobante puntual (factura o pago) - se
   dispara desde el badge de estado en la grilla de Cuenta Corriente
   (Proveedores/Procesos/js/funciones.js).
============================================================ */
function ver_aplicaciones_proveedor(id) {
  if (!id) {
    toast("error", "Error", "No se encontró el comprobante.");
    return;
  }

  $("#aplicacion_prov_comprobante").html("-");
  $("#aplicacion_prov_importe_original").html("$ 0,00");
  $("#aplicacion_prov_importe_aplicado").html("$ 0,00");
  $("#aplicacion_prov_saldo").html("$ 0,00");
  $("#aplicaciones_prov_empty").addClass("d-none");

  if ($.fn.DataTable.isDataTable("#tabla_aplicaciones_proveedor")) {
    $("#tabla_aplicaciones_proveedor").DataTable().destroy();
  }
  $("#tabla_aplicaciones_proveedor tbody").empty();

  $.ajax({
    url: "Procesos/php/pagos.php",
    type: "POST",
    dataType: "json",
    data: { VerAplicacionesProveedor: 1, idTransProveedores: id },
    success: function (jsonData) {
      if (jsonData.success != 1) {
        toast("error", "Error", jsonData.msg || "No se pudieron obtener las aplicaciones.");
        return;
      }

      $("#aplicacion_prov_comprobante").html(jsonData.comprobante || "-");
      $("#aplicacion_prov_importe_original").html(formatoMonedaProveedor(jsonData.importe_original));
      $("#aplicacion_prov_importe_aplicado").html(formatoMonedaProveedor(jsonData.importe_aplicado));
      $("#aplicacion_prov_saldo").html(formatoMonedaProveedor(jsonData.saldo));

      if (!jsonData.data || jsonData.data.length === 0) {
        $("#aplicaciones_prov_empty").removeClass("d-none");
      }

      $("#tabla_aplicaciones_proveedor").DataTable({
        destroy: true,
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        data: jsonData.data || [],
        columns: [
          { data: "Fecha", render: (data) => (data ? data.split("-").reverse().join("/") : "") },
          { data: "TipoRelacionado", defaultContent: "" },
          { data: "NumeroRelacionado", defaultContent: "" },
          { data: "Importe", render: (data) => formatoMonedaProveedor(data) },
          { data: "Usuario", defaultContent: "" },
        ],
        language: { emptyTable: "No hay aplicaciones registradas" },
      });

      const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("modal_aplicaciones_proveedor"));
      modal.show();
    },
    error: function (xhr) {
      toast("error", "Error del servidor", xhr.responseText);
    },
  });
}
