function resetFormularioPago() {
  // Fecha
  $("#fecha_pago").val(hoyISO());

  // Importes
  $("#importe_efectivo").val(0);
  $("#importe_transferencia").val(0);
  $("#importe_cheque").val(0);
  $("#importe_transferencia_mp").val(0);
  $("#fee_transferencia_mp").val(0);

  // Números / descripciones
  $("#numero_transferencia").val("");
  $("#numero_transferencia_mp").val("");
  $("#numero_cheque").val("");
  $("#banco_cheque").val("");
  $("#descripcion_transferencia_mp").val("");

  // Fechas asociadas
  $("#fecha_transferencia").val("");
  $("#fecha_transferencia_mp").val("");
  $("#fecha_cheque").val("");

  // UI
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
      beforeSend: function () {},
      success: function (respuesta) {
        var jsonData = JSON.parse(respuesta);

        console.log("success", jsonData.success);

        if (jsonData.success === undefined) {
          //compruebo que el pago no este cargado

          if (jsonData.data.collector_id != undefined) {
            document.getElementById("mercadopago_api").style.display = "flex";

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
  var id = document.getElementById("codigo").value;
  var formadepago = document.getElementById("formadepago").value;
  var formadepagofecha = document.getElementById("fecha_pago").value;

  console.log("formadepago", formadepago);

  if (formadepago == "000111400") {
    console.log("formadepago enviada", formadepago);
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

      beforeSend: function () {},

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
    if (formadepago == "000111200") {
      var importe = document.getElementById("importe_transferencia").value;
      var numerotrans = document.getElementById("numero_transferencia").value;
      var fechatrans = document.getElementById("fecha_transferencia").value;
    }
    // BANCO GALICIA CTA 3553
    if (formadepago == "000111210") {
      var importe = document.getElementById("importe_transferencia").value;
      var numerotrans = document.getElementById("numero_transferencia").value;
      var fechatrans = document.getElementById("fecha_transferencia").value;
    }

    if (formadepago == "000111100") {
      var importe = document.getElementById("importe_efectivo").value;
    }
    if (formadepago == "000112400") {
      var importe = document.getElementById("importe_cheque").value;
      var numerocheque = document.getElementById("numero_cheque").value;
      var fechacheque = document.getElementById("fecha_cheque").value;
      var banco = document.getElementById("banco_cheque").value;
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

          beforeSend: function () {},

          success: function (jsonData) {
            if (jsonData.success == "1") {
              $("#standard-modal").modal("hide"); //cerrar standard-modal
              toast("success", "Éxito", "Pago cargado con éxito");
              $("#basic").DataTable().ajax.reload();
            } else {
              toast("error", "Error", "Pago no cargado");
            }
          },

          error: function (xhr) {
            toast("error", "Error del servidor", xhr.responseText);
          },

          complete: function () {},
        });
      } else {
        toast("error", "Error!", "El importe no puede ser 0 ni nulo.");
      }
    } else {
      toast("error", "Error!", "La Forma de Pago no puede ser nula.");
    }
  }
});

$("#asociar_pago_comprobante_button").click(function () {
  $("#asociar-pagos-modal").modal("show");

  var idCliente = document.getElementById("codigo").value;

  var datatable = $("#tabla_asociar-pagos_facturas").DataTable({
    pageLength: 5,
    lengthMenu: [
      [5, 10, 20, -1],
      [5, 10, 20, "Todos"],
    ],
    paging: true,
    searching: false,
    bInfo: false,
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(3) //numero de columna a sumar
        .data()
        .reduce(function (a, b) {
          return parseInt(a) + parseInt(b);
        }, 0);
    },
    ajax: {
      url: "Procesos/php/cargarpago.php",
      data: { Asociar_pago_comprobantes: 1, id: idCliente },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span style="display: none;">' +
            row.Fecha +
            "</span>" +
            Fecha +
            "</td>"
          );
        },
      },
      {
        data: "TipoDeComprobante",
        render: function (data, type, row) {
          return `${row.TipoDeComprobante} - ${row.NumeroVenta}`;
        },
      },
      {
        data: "Comentario",
        render: function (data, type, row) {
          return "<td style='max-width: 15px;'>" + row.Comentario + "</td>";
        },
      },
      {
        data: "Debe",
        render: function (data, type, row) {
          let valor = Number(row.Debe);
          let val =
            "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
          return val;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<td class="dtr-control dt-checkboxes-cell">' +
            '<div class="form-check"><input data-id="' +
            row.id +
            '" value="' +
            row.Debe +
            '" type="checkbox" class="form-check-input dt-checkboxes" onclick="sumar_facturas()" >' +
            '<label class="form-check-label">&nbsp;</label></div></td>'
          );
        },
      },
    ],
  });

  var datatable = $("#tabla_asociar-pagos_pagos").DataTable({
    pageLength: 5,
    lengthMenu: [
      [5, 10, 20, -1],
      [5, 10, 20, "Todos"],
    ],
    paging: true,
    searching: false,
    bInfo: false,
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(3) //numero de columna a sumar
        .data()
        .reduce(function (a, b) {
          return parseInt(a) + parseInt(b);
        }, 0);
    },
    ajax: {
      url: "Procesos/php/cargarpago.php",
      data: { Asociar_pago_pagos: 1, id: idCliente },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span style="display: none;">' +
            row.Fecha +
            "</span>" +
            Fecha +
            "</td>"
          );
        },
      },
      {
        data: "TipoDeComprobante",
        render: function (data, type, row) {
          return `${row.TipoDeComprobante} - ${row.NumeroVenta}`;
        },
      },
      {
        data: "Comentario",
        render: function (data, type, row) {
          return "<td style='max-width: 15px;'>" + row.Comentario + "</td>";
        },
      },
      {
        data: "Haber",
        render: function (data, type, row) {
          let valor = Number(row.Haber);
          let val =
            "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
          return val;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<td class="dtr-control dt-checkboxes-cell">' +
            '<div class="form-check"><input data-id="' +
            row.id +
            '" value="' +
            row.Haber +
            '" type="checkbox" class="form-check-input dt-checkboxes" onclick="sumar_pagos()" >' +
            '<label class="form-check-label">&nbsp;</label></div></td>'
          );
        },
      },
    ],
  });
});
let checkedPagos = [];
let checkedPagosid = [];
let checkedFacturas = [];
let checkedFacturasid = [];

function sumar_facturas() {
  var oTable = $("#tabla_asociar-pagos_facturas").dataTable();

  var allPages = oTable.fnGetNodes();

  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  var importes = Number(0);

  //Recorremos todos los input checkbox que se encuentren "checked"
  $("input.form-check-input:checked", allPages).each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
      checkedFacturas.push($(this).attr("value"));
      checkedFacturasid.push($(this).attr("data-id"));

      importes = importes + Number($(this).attr("value"));
    } else {
      importes = Number(0);
    }

    let valor = Number(importes);
    let val = "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

    $("#footer_total_anticipos").html(val);
    $("#total_anticipos_control").val(valor);
    var valor_comprobantes = $("#footer_total_input").val();

    var footer_saldo = Number(valor_comprobantes - valor);
    console.log("Final", footer_saldo.toFixed(2));

    if (checked.length > 1 && footer_saldo < 0) {
      // $(this).prop('checked',false);

      let valor_ant = Number(valor_comprobantes);
      let val_ant =
        "$ " + valor_ant.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

      // $('#footer_total_anticipos').html(val_ant);
      let valor_saldo = Number(footer_saldo.toFixed(2));
      let val_saldo =
        "$ " + valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

      $("#footer_saldo").html(val_saldo);
      $("#footer_total_saldo").val(valor_saldo);
    } else {
      let valor_saldo = Number(footer_saldo.toFixed(2));
      let val_saldo =
        "$ " + valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

      $("#footer_saldo").html(val_saldo);
      $("#footer_total_saldo").val(valor_saldo);
    }
  });
  if (checked.length == 0) {
    let valor = Number(0);
    let val = "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    $("#footer_total_anticipos").html(val);
    $("#total_anticipos_control").val(0);
    let valor_comprobantes = Number($("#footer_total_input").val());
    let val_comprobantes =
      "$ " +
      valor_comprobantes.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    $("#footer_saldo").html(val_comprobantes);
    $("#footer_total_saldo").val(valor_comprobantes);
    checkedFacturas.length = 0;
    checkedFacturasid.length = 0;
  }
}

function sumar_pagos() {
  var oTable = $("#tabla_asociar-pagos_pagos").dataTable();

  var allPages = oTable.fnGetNodes();

  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];

  var importes = Number(0);

  //Recorremos todos los input checkbox que se encuentren "checked"
  $("input.form-check-input:checked", allPages).each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
      checkedPagos.push($(this).attr("value"));
      checkedPagosid.push($(this).attr("data-id"));

      importes = importes + Number($(this).attr("value"));
    } else {
      importes = Number(0);
    }

    let valor = Number(importes);
    let val = "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

    $("#footer_total").html(val);
    // $('#total_anticipos_control').val(valor);
    // var valor_comprobantes=$('#footer_total_input').val();
    var valor_comprobantes = $("#total_anticipos_control").val();
    var footer_saldo = Number(valor_comprobantes - valor);
    console.log("Final", footer_saldo.toFixed(2));

    if (checked.length > 1 && footer_saldo < 0) {
      // $(this).prop('checked',false);

      let valor_ant = Number(valor_comprobantes);
      let val_ant =
        "$ " + valor_ant.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

      // $('#footer_total_anticipos').html(val_ant);
      let valor_saldo = Number(footer_saldo.toFixed(2));
      let val_saldo =
        "$ " + valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
      $("#footer_saldo").html(val_saldo);
      $("#footer_total_saldo").val(valor_saldo);
    } else {
      let valor_saldo = Number(footer_saldo.toFixed(2));
      let val_saldo =
        "$ " + valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");

      $("#footer_saldo").html(val_saldo);
      $("#footer_total_saldo").val(valor_saldo);
    }
  });

  if (checked.length == 0) {
    let valor = Number(0);
    let val = "$ " + valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    $("#footer_total").html(val);
    // $('#total_anticipos_control').val(0);
    let valor_comprobantes = Number($("#footer_total_input").val());
    let val_comprobantes =
      "$ " +
      valor_comprobantes.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    $("#footer_saldo").html(val_comprobantes);
    $("#footer_total_saldo").val(valor_comprobantes);
    checkedPagos.length = 0;
    checkedPagosid.length = 0;
  }
}

$("#asociar-pagos-modal-ok").click(function () {
  console.log("cheched_pagos", checkedPagos);
  console.log("cheched_pagos id", checkedPagosid);
  console.log("cheched_facturas", checkedFacturas);
  console.log("cheched_facturas id", checkedFacturasid);

  $.ajax({
    data: {
      Asociar_pagos: 1,
      Pagosid: checkedPagosid,
      Facturasid: checkedFacturasid,
      Pagos: checkedPagos,
      Facturas: checkedFacturas,
    },
    url: "Procesos/php/cargarpago.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
    },
  });
});
