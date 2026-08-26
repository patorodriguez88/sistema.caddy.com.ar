var myToast = null;

function crearToastCaja(texto = "$ 0,00", color = "#dc3545") {
  if (myToast) {
    myToast.reset();
  }

  myToast = $.toast({
    heading: "Saldo Movimientos Seleccionados",
    text: texto,
    hideAfter: false,
    allowToastClose: false,
    bgColor: color,
    textColor: "#ffffff",
    position: "bottom-right",
  });
}

$(document).ready(function () {
  crearToastCaja();
});
function formatearMoneda(num) {
  num = parseFloat(num) || 0;
  return (
    "$ " +
    num.toLocaleString("es-AR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

function seleccionarTodo(checked) {
  $("#seguimiento tbody input[type='checkbox']").each(function () {
    this.checked = checked;
  });
  sumar();
}

function sumar() {
  var total = 0;

  $("#seguimiento tbody input[type='checkbox']:checked").each(function () {
    total += parseFloat($(this).data("total")) || 0;
  });

  myToast.update({
    text: formatearMoneda(total),
    bgColor: total !== 0 ? "#198754" : "#dc3545",
  });

  if ($("#seguimiento tbody input[type='checkbox']:checked").length === 0) {
    $("#cierre_add").addClass("disabled");
  } else {
    $("#cierre_add").removeClass("disabled");
  }
}

function currencyFormat(num) {
  return formatearMoneda(num);
}

function parseImporte(valor) {
  if (typeof valor === "string") {
    valor = valor
      .replace(/\$/g, "")
      .replace(/\./g, "")
      .replace(",", ".")
      .trim();
  }
  return parseFloat(valor) || 0;
}
$(document).on("click", ".cerrar-modal-informe", function () {
  $("#modal_informe_cierre").modal("hide");
});

// PDF con el diseño del sistema (membrete, card de datos, footer paginado),
// mismo patrón que window.imprimirAsiento() en contabilidad.js — antes era
// window.print() sobre el modal, sin membrete ni paginado.
var idCajaInformeActual = null;

$(document).on("click", "#btn_imprimir_informe_cierre", function () {
  if (!idCajaInformeActual) return;
  window.open(
    "../Admin/Informes/CierreCajapdf.php?idCaja=" +
      encodeURIComponent(idCajaInformeActual),
    "_blank",
  );
});

function verInformeCierre(idCaja) {
  idCajaInformeActual = idCaja;
  // ocultar toast flotante
  if (myToast) {
    myToast.reset();
    myToast = null;
  }

  $("#modal_informe_cierre").modal("show");

  $("#informe_cierre_contenido").html(`
    <div class="text-center py-4">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="mt-2">Cargando informe...</div>
    </div>
  `);

  $.ajax({
    url: "Procesos/php/caja.php",
    type: "POST",
    dataType: "json",
    data: {
      VerInformeCierre: 1,
      idCaja: idCaja,
    },
    success: function (resp) {
      if (!resp || resp.error) {
        $("#informe_cierre_contenido").html(`
          <div class="alert alert-danger mb-0">
            ${resp && resp.message ? resp.message : "No se pudo obtener el informe del cierre."}
          </div>
        `);
        return;
      }

      var cierre = resp.cierre || {};
      var movimientos = resp.movimientos || [];

      var htmlMovimientos = "";
      var totalDebe = 0;
      var totalHaber = 0;

      if (movimientos.length > 0) {
        movimientos.forEach(function (item) {
          var debe = parseFloat(item.Debe || 0);
          var haber = parseFloat(item.Haber || 0);

          totalDebe += debe;
          totalHaber += haber;

          var fecha = item.Fecha
            ? item.Fecha.split("-").reverse().join("/")
            : "";
          var fechaCheque =
            item.FechaCheque && item.FechaCheque !== "0000-00-00"
              ? item.FechaCheque.split("-").reverse().join("/")
              : "";

          htmlMovimientos += `
            <tr>
              <td>${fecha}</td>
              <td>${item.Cuenta || ""}</td>
              <td>${item.NombreCuenta || ""}</td>
              <td>${item.Observaciones || ""}</td>
              <td>${item.FormaDePago || ""}</td>
              <td>${item.NumeroCheque || ""}</td>
              <td>${fechaCheque}</td>
              <td class="text-end">${formatearMoneda(debe)}</td>
              <td class="text-end">${formatearMoneda(haber)}</td>
            </tr>
          `;
        });
      } else {
        htmlMovimientos = `
          <tr>
            <td colspan="9" class="text-center text-muted">No se encontraron movimientos asociados a este cierre.</td>
          </tr>
        `;
      }

      var fechaCierre = cierre.Date
        ? cierre.Date.split("-").reverse().join("/")
        : "";
      var diferenciaClass =
        parseFloat(cierre.Diferencia || 0) === 0 ? "success" : "danger";

      var html = `
  <div id="area_imprimible_informe_cierre">
    <div class="informe-cierre-header">
      <div class="informe-cierre-header-left">
        <h4>Cierre de Caja #${cierre.id || ""}</h4>
        <small>Informe detallado del cierre conciliado</small>
      </div>
      <div class="informe-cierre-header-right">
        <div><strong>Fecha:</strong> ${fechaCierre}</div>
        <div><strong>Hora:</strong> ${cierre.TimeStamp || ""}</div>
        <div><strong>Usuario:</strong> ${cierre.Usuario || ""}</div>
      </div>
    </div>

    <div class="informe-resumen-grid mb-4">
      <div class="informe-card">
        <div class="informe-card-label">Saldo anterior</div>
        <div class="informe-card-value">${formatearMoneda(cierre.SaldoAnterior || 0)}</div>
      </div>

      <div class="informe-card">
        <div class="informe-card-label">Mov. conciliados</div>
        <div class="informe-card-value">${formatearMoneda(cierre.MovConciliados || 0)}</div>
      </div>

      <div class="informe-card">
        <div class="informe-card-label">Saldo final</div>
        <div class="informe-card-value">${formatearMoneda(cierre.SaldoFinal || 0)}</div>
      </div>

      <div class="informe-card">
        <div class="informe-card-label">Caja física</div>
        <div class="informe-card-value">${formatearMoneda(cierre.SaldoActual || 0)}</div>
      </div>

      <div class="informe-card informe-card-${diferenciaClass}">
        <div class="informe-card-label">Diferencia</div>
        <div class="informe-card-value text-${diferenciaClass}">
          ${formatearMoneda(cierre.Diferencia || 0)}
        </div>
      </div>

      <div class="informe-card">
        <div class="informe-card-label">Caja ID</div>
        <div class="informe-card-value">${cierre.id || ""}</div>
      </div>
    </div>

    <div class="card border shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0">Detalle de movimientos conciliados en Tesorería</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Cuenta</th>
                <th>Nombre Cuenta</th>
                <th>Observaciones</th>
                <th>Forma Pago</th>
                <th>N° Cheque</th>
                <th>Fecha Cheque</th>
                <th class="text-end">Debe</th>
                <th class="text-end">Haber</th>
              </tr>
            </thead>
            <tbody>
              ${htmlMovimientos}
            </tbody>
            <tfoot>
              <tr class="table-secondary">
                <th colspan="7" class="text-end">Totales</th>
                <th class="text-end">${formatearMoneda(totalDebe)}</th>
                <th class="text-end">${formatearMoneda(totalHaber)}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
`;

      $("#informe_cierre_contenido").html(html);
    },
    error: function (xhr, status, error) {
      $("#informe_cierre_contenido").html(`
        <div class="alert alert-danger mb-0">
          Error al cargar el informe: ${error}
        </div>
      `);
    },
  });
}
// destruir solo si existe
if ($.fn.DataTable.isDataTable("#seguimiento")) {
  $("#seguimiento").DataTable().destroy();
}

if ($.fn.DataTable.isDataTable("#cierre_caja")) {
  $("#cierre_caja").DataTable().destroy();
}

// TABLA CIERRES
var tablaCierres = $("#cierre_caja").DataTable({
  paging: false,
  searching: true,
  ajax: {
    url: "Procesos/php/caja.php",
    data: { VerFechas: 1 },
    processing: true,
    type: "post",
    dataSrc: "data",
  },
  footerCallback: function () {
    var api = this.api();

    if (!api.column(6).footer()) return;

    var total = api
      .column(6, { page: "current" })
      .data()
      .reduce(function (a, b) {
        return parseImporte(a) + parseImporte(b);
      }, 0);

    $(api.column(6).footer()).html(currencyFormat(total));
  },
  columns: [
    { data: "id" },
    {
      data: "Fecha",
      render: function (data, type, row) {
        var fecha = (row.Date || row.Fecha || "")
          .split("-")
          .reverse()
          .join(".");
        return (
          '<span style="display:none;">' +
          (row.Date || row.Fecha || "") +
          "</span>" +
          fecha +
          "<br>" +
          (row.Usuario || "")
        );
      },
    },
    {
      data: "SaldoAnterior",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "MovConciliados",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "SaldoFinal",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "SaldoActual",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "Diferencia",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "TimeStamp",
      render: function (data) {
        // Fecha ya la muestra la columna "Fecha" - acá solo interesa la hora,
        // mostrar el TimeStamp completo (fecha+hora) hacía la columna
        // innecesariamente ancha.
        var partes = (data || "").split(" ");
        return partes.length > 1 ? partes[1].substring(0, 5) : data || "";
      },
    },
    {
      data: "id",
      orderable: false,
      render: function (data, type, row) {
        return `<a href="javascript:void(0);" data-id="${row.id}" onclick="verInformeCierre(${row.id});" class="action-icon">
            <i class="mdi mdi-cloud-print-outline text-success mdi-18px ms-2"></i>
          </a>`;
      },
    },
  ],
});

// TABLA MOVIMIENTOS
var tablaSeguimiento = $("#seguimiento").DataTable({
  paging: false,
  searching: true,
  ajax: {
    url: "Procesos/php/caja.php",
    data: { Pendientes: 1 },
    processing: true,
    type: "post",
    dataSrc: "data",
  },
  footerCallback: function () {
    var api = this.api();

    if (!api.column(6).footer() || !api.column(7).footer()) return;

    var totalDebe = api
      .column(6, { page: "current" })
      .data()
      .reduce(function (a, b) {
        return parseImporte(a) + parseImporte(b);
      }, 0);

    var totalHaber = api
      .column(7, { page: "current" })
      .data()
      .reduce(function (a, b) {
        return parseImporte(a) + parseImporte(b);
      }, 0);

    $(api.column(6).footer()).html(currencyFormat(totalDebe));
    $(api.column(7).footer()).html(currencyFormat(totalHaber));
  },
  columns: [
    {
      data: "Fecha",
      render: function (data, type, row) {
        var fecha = (row.Fecha || "").split("-").reverse().join(".");
        return (
          '<span style="display:none;">' + (row.Fecha || "") + "</span>" + fecha
        );
      },
    },
    { data: "Usuario" },
    { data: "FormaDePago" },
    { data: "NombreCuenta" },
    { data: "Observaciones" },
    { data: "RazonSocial" },
    {
      data: "Debe",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "Haber",
      render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
    },
    {
      data: "id",
      orderable: false,
      render: function (data, type, row) {
        var total = (parseFloat(row.Debe) || 0) - (parseFloat(row.Haber) || 0);

        return `<div class="form-check">
                  <input 
                    data-id="${row.id}" 
                    data-total="${total}" 
                    type="checkbox" 
                    onclick="sumar()" 
                    class="form-check-input dt-checkboxes"
                  >
                  <label class="form-check-label">&nbsp;</label>
                </div>`;
      },
    },
  ],
});

// select all
$(document).on("change", "#selectAllMovimientos", function () {
  seleccionarTodo(this.checked);
});

// abrir modal
$("#cierre_add").on("click", function () {
  if ($(this).hasClass("disabled")) return;
  $("#modal_cierre_caja").modal("show");
});

// guardar cierre
$("#cerrar_caja_ok").on("click", function () {
  let fecha_ = $("#date_cierre_caja").val();
  let fecha = fecha_.split("-").reverse().join("-");
  let saldoUltimo = $("#saldo_ant_cierre_caja_number").val();
  let saldoActual = $("#saldo_actual_cierre_caja").val();
  let diferencia = $("#saldo_dif_cierre_caja_number").val();
  let saldoFinal = $("#saldo_conciliar_number").val();
  let movConciliados = $("#movimientos_cierre_caja_number").val();

  let ids = [];
  $("#seguimiento tbody input[type='checkbox']:checked").each(function () {
    ids.push($(this).data("id"));
  });

  $.ajax({
    data: {
      Agregar_cierre: 1,
      Fecha: fecha,
      MovConciliados: movConciliados,
      SaldoUltimo: saldoUltimo,
      SaldoActual: saldoActual,
      Diferencia: diferencia,
      ids: ids,
      SaldoFinal: saldoFinal,
    },
    url: "Procesos/php/caja.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      if (jsonData.success == 1) {
        $("#modal_cierre_caja").modal("hide");
        tablaCierres.ajax.reload();
        tablaSeguimiento.ajax.reload();

        $("#cierre_add").addClass("disabled");

        myToast.update({
          text: "$ 0,00",
          bgColor: "#dc3545",
        });

        $("#selectAllMovimientos").prop("checked", false);
      }
    },
  });
});

// al abrir modal
$("#modal_cierre_caja").on("show.bs.modal", function () {
  $.ajax({
    data: { Ver_datos: 1 },
    url: "Procesos/php/caja.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      let date = (jsonData.Date || "").split("-").reverse().join("-");
      let saldoAnterior = parseFloat(jsonData.Saldo) || 0;

      $("#saldo_ant_cierre_caja").val(formatearMoneda(saldoAnterior));
      $("#saldo_ant_cierre_caja_number").val(saldoAnterior);
      $("#date_last_cierre_caja").val(date);

      let total = 0;
      $("#seguimiento tbody input[type='checkbox']:checked").each(function () {
        total += parseFloat($(this).data("total")) || 0;
      });

      $("#movimientos_cierre_caja").val(formatearMoneda(total));
      $("#movimientos_cierre_caja_number").val(total);

      let saldoConciliar = saldoAnterior + total;
      $("#saldo_conciliar_number").val(saldoConciliar);
      $("#saldo_conciliar").val(formatearMoneda(saldoConciliar));
    },
  });
});

// calcular diferencia
function comprobar_diferencia(valor) {
  let saldo = parseFloat($("#saldo_conciliar_number").val()) || 0;
  let actual = parseFloat(valor) || 0;
  let dif = actual - saldo;

  $("#saldo_dif_cierre_caja").val(formatearMoneda(dif));
  $("#saldo_dif_cierre_caja_number").val(dif);
  $("#cerrar_caja_ok").prop("disabled", false);
}

// reset modal
$("#modal_cierre_caja").on("hidden.bs.modal", function () {
  $(this)
    .find("form")
    .each(function () {
      this.reset();
    });

  $("#saldo_conciliar_number").val("");
  $("#saldo_dif_cierre_caja").val("");
  $("#saldo_dif_cierre_caja_number").val("");
  $("#cerrar_caja_ok").prop("disabled", true);
});
