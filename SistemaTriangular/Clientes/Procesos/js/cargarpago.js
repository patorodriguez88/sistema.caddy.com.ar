$(document).on("change", ".dt-checkbox-factura", function () {
  sumar_facturas();
});

$(document).on("change", ".dt-checkbox-pago", function () {
  sumar_pagos();
});
function formatoMonedaAplicacion(valor) {
  valor = Number(valor || 0);
  return (
    "$ " +
    valor
      .toFixed(2)
      .replace(/\d(?=(\d{3})+\.)/g, "$&,")
      .replace(".", ",")
  );
}

function resetFormularioPago() {
  $("#fecha_pago").val(hoyISO());

  $("#importe_efectivo").val(0);
  $("#importe_transferencia").val(0);
  $("#importe_cheque").val(0);
  $("#importe_transferencia_mp").val(0);
  $("#fee_transferencia_mp").val(0);

  $("#numero_transferencia").val("");
  $("#numero_transferencia_mp").val("");
  $("#numero_cheque").val("");
  $("#banco_cheque").val("");
  $("#descripcion_transferencia_mp").val("");

  $("#fecha_transferencia").val("");
  $("#fecha_transferencia_mp").val("");
  $("#fecha_cheque").val("");

  $("#mercadopago_api").hide();
  $("#status_mp").html("");
  $("#confirmarpago_botton").prop("disabled", true);
}

function noComa(e) {
  const key = e.key;
  if (key === ",") {
    e.preventDefault();
  }
}

function formatearARS(valor) {
  valor = valor.replace(/\D/g, "");
  if (valor === "") return "";

  let numero = parseInt(valor, 10) / 100;

  return numero.toLocaleString("es-AR", {
    style: "currency",
    currency: "ARS",
    minimumFractionDigits: 2,
  });
}

function formatoMoneda(valor) {
  valor = Number(valor || 0);
  return "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

$(".input-moneda").on("input", function () {
  let valor = $(this).val();
  let limpio = valor.replace(/\D/g, "");
  $(this).val(formatearARS(limpio));
});

$("#standard-modal").on("show.bs.modal", function () {
  resetFormularioPago();
});

$("#search_mp").click(function () {
  let op = $("#noperacion_mp").val();

  $("#noperacion_mp").val(op);

  if (op != "") {
    $.ajax({
      data: {
        BuscarOperacion_mp: 1,
        NOperacion: op,
      },
      url: "Procesos/php/buscar_pago_mp.php",
      type: "post",
      success: function (respuesta) {
        var jsonData = JSON.parse(respuesta);

        if (jsonData.success === undefined) {
          if (jsonData.data.collector_id != undefined) {
            $("#mercadopago_api").css("display", "flex");

            $("#fee_transferencia_mp").val(jsonData.data.fee_details[0].amount);
            $("#numero_transferencia_mp").val(jsonData.data.collector_id);
            $("#importe_transferencia_mp").val(
              jsonData.data.transaction_amount,
            );

            var fecha = jsonData.data.date_approved;
            let fecha_transferencia = fecha.substr(0, 10);

            $("#fecha_transferencia_mp").val(fecha_transferencia);
            $("#status_mp").html(jsonData.data.status);
            $("#descripcion_transferencia_mp").val(jsonData.data.description);
            $("#confirmarpago_botton").prop("disabled", false);
          } else {
            alerta("error", "Error!", jsonData.data.message);
          }
        }
      },
    });
  }
});

$("#confirmarpago_botton").click(function () {
  var id = $("#codigo").val();
  var formadepago = $("#formadepago").val();
  var formadepagofecha = $("#fecha_pago").val();

  if (formadepago == "000111400") {
    var fee = $("#fee_transferencia_mp").val();
    var fecha_mp = $("#fecha_transferencia_mp").val();
    var importe = $("#importe_transferencia_mp").val();
    var id_mp = $("#numero_transferencia_mp").val();
    var obs = $("#descripcion_transferencia_mp").val();

    $.ajax({
      data: {
        CargarPago_mp: 1,
        id: id,
        formadepago: formadepago,
        formadepagofecha: formadepagofecha,
        importe: importe,
        fee_mp: fee,
        fecha_mp: fecha_mp,
        id_mp: id_mp,
        obs: obs,
      },
      url: "Procesos/php/cargarpago_mp.php",
      type: "post",
      dataType: "json",
      success: function (jsonData) {
        if (jsonData.success == 1) {
          toast("success", "Éxito", "Pago cargado con éxito");
        } else {
          alerta("error", "Error", "Pago no cargado");
        }
      },
      error: function (xhr) {
        alerta("error", "Error del servidor", xhr.responseText);
      },
    });
  } else {
    var importe = 0;
    var numerotrans = "";
    var fechatrans = "";
    var numerocheque = "";
    var fechacheque = "";
    var banco = "";

    if (formadepago == "000111200" || formadepago == "000111210") {
      importe = $("#importe_transferencia").val();
      numerotrans = $("#numero_transferencia").val();
      fechatrans = $("#fecha_transferencia").val();
    }

    if (formadepago == "000111100") {
      importe = $("#importe_efectivo").val();
    }

    if (formadepago == "000112400") {
      importe = $("#importe_cheque").val();
      numerocheque = $("#numero_cheque").val();
      fechacheque = $("#fecha_cheque").val();
      banco = $("#banco_cheque").val();
    }

    if (formadepago != "") {
      if (importe != 0) {
        $.ajax({
          data: {
            CargarPago: 1,
            id: id,
            formadepago: formadepago,
            formadepagofecha: formadepagofecha,
            importe: importe,
            numerocheque: numerocheque,
            fechacheque: fechacheque,
            banco: banco,
            numerotrans: numerotrans,
            fechatrans: fechatrans,
          },
          url: "Procesos/php/cargarpago.php",
          type: "post",
          dataType: "json",
          success: function (jsonData) {
            if (jsonData.success == "1" || jsonData.success == 1) {
              $("#standard-modal").modal("hide");
              toast("success", "Éxito", "Pago cargado con éxito");
              $("#basic").DataTable().ajax.reload(null, false);
            } else {
              toast("error", "Error", "Pago no cargado");
            }
          },
          error: function (xhr) {
            toast("error", "Error del servidor", xhr.responseText);
          },
        });
      } else {
        toast("error", "Error!", "El importe no puede ser 0 ni nulo.");
      }
    } else {
      toast("error", "Error!", "La Forma de Pago no puede ser nula.");
    }
  }
});

/* =========================================================
   ASOCIACION PAGOS / FACTURAS
========================================================= */

let checkedPagos = [];
let checkedPagosid = [];
let checkedFacturas = [];
let checkedFacturasid = [];

function limpiarEstadoAsociacion() {
  checkedPagos = [];
  checkedPagosid = [];
  checkedFacturas = [];
  checkedFacturasid = [];

  $("#footer_total_anticipos").html("$ 0.00");
  $("#footer_total").html("$ 0.00");
  $("#footer_saldo").html("$ 0.00");
  $("#footer_total_input").val(0);
  $("#footer_total_saldo").val(0);
  $("#total_anticipos_control").val(0);
}

function recalcularAsociacion() {
  checkedFacturas = [];
  checkedFacturasid = [];
  checkedPagos = [];
  checkedPagosid = [];

  let totalFacturas = 0;
  let totalPagos = 0;

  $("#tabla_asociar-pagos_facturas .chk-factura:checked").each(function () {
    const importe = Number($(this).attr("data-importe") || 0);
    const id = $(this).attr("data-id");

    checkedFacturas.push(importe);
    checkedFacturasid.push(id);
    totalFacturas += importe;
  });

  $("#tabla_asociar-pagos_pagos .chk-pago:checked").each(function () {
    const importe = Number($(this).attr("data-importe") || 0);
    const id = $(this).attr("data-id");

    checkedPagos.push(importe);
    checkedPagosid.push(id);
    totalPagos += importe;
  });

  const saldo = totalFacturas - totalPagos;

  $("#footer_total_anticipos").html(formatoMoneda(totalFacturas));
  $("#footer_total").html(formatoMoneda(totalPagos));
  $("#footer_saldo").html(formatoMoneda(saldo));
  $("#footer_total_input").val(totalPagos);
  $("#footer_total_saldo").val(saldo);
  $("#total_anticipos_control").val(totalFacturas);
}

$(document).on("change", ".chk-factura, .chk-pago", function () {
  recalcularAsociacion();
});

$("#asociar_pago_comprobante_button").click(function () {
  $("#asociar-pagos-modal").modal("show");

  const idCliente = $("#codigo").val();

  limpiarEstadoAsociacion();

  if ($.fn.DataTable.isDataTable("#tabla_asociar-pagos_facturas")) {
    $("#tabla_asociar-pagos_facturas").DataTable().destroy();
    $("#tabla_asociar-pagos_facturas tbody").empty();
  }

  if ($.fn.DataTable.isDataTable("#tabla_asociar-pagos_pagos")) {
    $("#tabla_asociar-pagos_pagos").DataTable().destroy();
    $("#tabla_asociar-pagos_pagos tbody").empty();
  }

  $("#tabla_asociar-pagos_facturas").DataTable({
    destroy: true,
    pageLength: 5,
    lengthMenu: [
      [5, 10, 20, -1],
      [5, 10, 20, "Todos"],
    ],
    paging: true,
    searching: false,
    info: false,
    ajax: {
      url: "Procesos/php/cargarpago.php",
      data: {
        Asociar_pago_comprobantes: 1,
        id: idCliente,
      },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          const fecha = row.Fecha
            ? row.Fecha.split("-").reverse().join(".")
            : "";
          return (
            '<span style="display:none;">' +
            (row.Fecha || "") +
            "</span>" +
            fecha
          );
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            (row.TipoDeComprobante || "") +
            " - " +
            (row.NumeroVenta || row.NumeroFactura || "")
          );
        },
      },
      {
        data: "Comentario",
        render: function (data) {
          return data || "";
        },
      },
      {
        data: "SaldoPendiente",
        render: function (data) {
          const valor = Number(data || 0);
          return formatoMoneda(valor);
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return (
            '<div class="form-check mb-0">' +
            '<input data-id="' +
            row.id +
            '" data-importe="' +
            row.SaldoPendiente +
            '" type="checkbox" class="form-check-input chk-factura">' +
            '<label class="form-check-label">&nbsp;</label>' +
            "</div>"
          );
        },
      },
    ],
  });

  $("#tabla_asociar-pagos_pagos").DataTable({
    destroy: true,
    pageLength: 5,
    lengthMenu: [
      [5, 10, 20, -1],
      [5, 10, 20, "Todos"],
    ],
    paging: true,
    searching: false,
    info: false,
    ajax: {
      url: "Procesos/php/cargarpago.php",
      data: {
        Asociar_pago_pagos: 1,
        id: idCliente,
      },
      type: "post",
      dataSrc: "data",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          const fecha = row.Fecha
            ? row.Fecha.split("-").reverse().join(".")
            : "";
          return (
            '<span style="display:none;">' +
            (row.Fecha || "") +
            "</span>" +
            fecha
          );
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            (row.TipoDeComprobante || "") +
            " - " +
            (row.NumeroVenta || row.NumeroFactura || "")
          );
        },
      },
      {
        data: "Comentario",
        render: function (data) {
          return data || "";
        },
      },
      {
        data: "SaldoDisponible",
        render: function (data) {
          const valor = Number(data || 0);
          return formatoMoneda(valor);
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return (
            '<div class="form-check mb-0">' +
            '<input data-id="' +
            row.id +
            '" data-importe="' +
            row.SaldoDisponible +
            '" type="checkbox" class="form-check-input chk-pago">' +
            '<label class="form-check-label">&nbsp;</label>' +
            "</div>"
          );
        },
      },
    ],
  });
});

$("#asociar-pagos-modal-ok").click(function () {
  if (checkedFacturasid.length === 0) {
    toast("error", "Error", "Seleccioná al menos una factura.");
    return;
  }

  if (checkedPagosid.length === 0) {
    toast("error", "Error", "Seleccioná al menos un pago.");
    return;
  }

  $.ajax({
    data: {
      Asociar_pagos: 1,
      Pagosid: checkedPagosid,
      Facturasid: checkedFacturasid,
    },
    url: "Procesos/php/cargarpago.php",
    type: "post",
    dataType: "json",
    beforeSend: function () {
      toast("info", "Procesando", "Asociando pagos y facturas...");
    },
    success: function (jsonData) {
      if (jsonData.success == 1) {
        toast("success", "Perfecto", jsonData.msg || "Asociación realizada.");
        $("#asociar-pagos-modal").modal("hide");

        if ($.fn.DataTable.isDataTable("#basic")) {
          $("#basic").DataTable().ajax.reload(null, false);
        }
      } else {
        alerta("error", "Error", jsonData.msg || "No se pudo asociar.");
      }
    },
    error: function (xhr) {
      alerta(
        "error",
        "Error del servidor",
        xhr.responseText || "Error inesperado",
      );
    },
  });
});
