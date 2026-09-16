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

function recalcularAsociacionProv() {
  checkedFacturasProv = [];
  checkedFacturasidProv = [];
  checkedPagosProv = [];
  checkedPagosidProv = [];

  let totalFacturas = 0;
  let totalPagos = 0;

  $("#tabla_asociar-pagos_facturas_prov .chk-factura-prov:checked").each(function () {
    const importe = Number($(this).attr("data-importe") || 0);
    const id = $(this).attr("data-id");
    checkedFacturasProv.push(importe);
    checkedFacturasidProv.push(id);
    totalFacturas += importe;
  });

  $("#tabla_asociar-pagos_pagos_prov .chk-pago-prov:checked").each(function () {
    const importe = Number($(this).attr("data-importe") || 0);
    const id = $(this).attr("data-id");
    checkedPagosProv.push(importe);
    checkedPagosidProv.push(id);
    totalPagos += importe;
  });

  const saldo = totalFacturas - totalPagos;

  $("#footer_total_facturas_prov").html(formatoMonedaProveedor(totalFacturas));
  $("#footer_total_pagos_prov").html(formatoMonedaProveedor(totalPagos));
  $("#footer_saldo_prov").html(formatoMonedaProveedor(saldo));
}

$(document).on("change", ".chk-factura-prov, .chk-pago-prov", function () {
  recalcularAsociacionProv();
});

function comprobanteYNumero(row) {
  return (row.TipoDeComprobante || "") + " - " + (row.NumeroComprobante || "");
}

function fechaDMYProv(iso) {
  return iso ? iso.split("-").reverse().join(".") : "";
}

$("#asociar_pago_proveedor_button").click(function () {
  const idProveedor = $("#codigo").val();

  if (!idProveedor) {
    toast("error", "Error", "Elegí un proveedor primero.");
    return;
  }

  $("#asociar-pagos-modal-proveedor").modal("show");
  limpiarEstadoAsociacionProv();

  if ($.fn.DataTable.isDataTable("#tabla_asociar-pagos_facturas_prov")) {
    $("#tabla_asociar-pagos_facturas_prov").DataTable().destroy();
    $("#tabla_asociar-pagos_facturas_prov tbody").empty();
  }
  if ($.fn.DataTable.isDataTable("#tabla_asociar-pagos_pagos_prov")) {
    $("#tabla_asociar-pagos_pagos_prov").DataTable().destroy();
    $("#tabla_asociar-pagos_pagos_prov tbody").empty();
  }

  $("#tabla_asociar-pagos_facturas_prov").DataTable({
    destroy: true,
    pageLength: 5,
    lengthMenu: [[5, 10, 20, -1], [5, 10, 20, "Todos"]],
    paging: true,
    searching: false,
    info: false,
    ajax: {
      url: "Procesos/php/pagos.php",
      data: { Asociar_pago_comprobantes: 1, id: idProveedor },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      { data: "Fecha", render: (data, type, row) => fechaDMYProv(row.Fecha) },
      { data: null, render: (data, type, row) => comprobanteYNumero(row) },
      { data: "Descripcion", render: (data) => data || "" },
      { data: "SaldoPendiente", render: (data) => formatoMonedaProveedor(data) },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (data, type, row) =>
          '<div class="form-check mb-0">' +
          '<input data-id="' + row.id + '" data-importe="' + row.SaldoPendiente + '" type="checkbox" class="form-check-input chk-factura-prov">' +
          '<label class="form-check-label">&nbsp;</label></div>',
      },
    ],
  });

  $("#tabla_asociar-pagos_pagos_prov").DataTable({
    destroy: true,
    pageLength: 5,
    lengthMenu: [[5, 10, 20, -1], [5, 10, 20, "Todos"]],
    paging: true,
    searching: false,
    info: false,
    ajax: {
      url: "Procesos/php/pagos.php",
      data: { Asociar_pago_pagos: 1, id: idProveedor },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      { data: "Fecha", render: (data, type, row) => fechaDMYProv(row.Fecha) },
      { data: null, render: (data, type, row) => comprobanteYNumero(row) },
      { data: "Descripcion", render: (data) => data || "" },
      { data: "SaldoDisponible", render: (data) => formatoMonedaProveedor(data) },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (data, type, row) =>
          '<div class="form-check mb-0">' +
          '<input data-id="' + row.id + '" data-importe="' + row.SaldoDisponible + '" type="checkbox" class="form-check-input chk-pago-prov">' +
          '<label class="form-check-label">&nbsp;</label></div>',
      },
    ],
  });
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
