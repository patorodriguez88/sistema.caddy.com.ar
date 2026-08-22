function hoyISO() {
  const d = new Date();
  return d.toISOString().split("T")[0]; // YYYY-MM-DD (ideal para <input type="date">)
}
// Extrae los últimos 4 dígitos de un CP tipo "X5028" y los convierte a número.
function getCPnum(cp) {
  if (cp === null || cp === undefined) return null;
  var str = String(cp).trim();
  var m = str.match(/(\d{4})$/);
  return m ? parseInt(m[1], 10) : null;
}
// Devuelve true si el CP NO está entre 5000 y 5023 (interior, fuera de Capital)
function isOutOfCapitalRange(cp) {
  var n = getCPnum(cp);
  if (n === null) return false; // sin CP no alertamos
  return !(n >= 5000 && n <= 5023);
}

function currencyFormat(num) {
  num = Number(num || 0);
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}
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

function ver_aplicaciones(id) {
  if (!id) {
    toast("error", "Error", "No se encontró el comprobante.");
    return;
  }

  $("#aplicacion_comprobante").html("-");
  $("#aplicacion_importe_original").html("$ 0,00");
  $("#aplicacion_importe_aplicado").html("$ 0,00");
  $("#aplicacion_saldo").html("$ 0,00");
  $("#aplicaciones_empty").addClass("d-none");

  if ($.fn.DataTable.isDataTable("#tabla_aplicaciones")) {
    $("#tabla_aplicaciones").DataTable().destroy();
  }

  $("#tabla_aplicaciones tbody").empty();

  $.ajax({
    url: "Procesos/php/cargarpago.php",
    type: "POST",
    dataType: "json",
    data: {
      VerAplicaciones: 1,
      idCtasctes: id,
    },
    success: function (jsonData) {
      if (jsonData.success != 1) {
        alerta(
          "error",
          "Error",
          jsonData.msg || "No se pudieron obtener las aplicaciones.",
        );
        return;
      }

      $("#aplicacion_comprobante").html(jsonData.comprobante || "-");
      $("#aplicacion_importe_original").html(
        formatoMonedaAplicacion(jsonData.importe_original),
      );
      $("#aplicacion_importe_aplicado").html(
        formatoMonedaAplicacion(jsonData.importe_aplicado),
      );
      $("#aplicacion_saldo").html(formatoMonedaAplicacion(jsonData.saldo));

      if (!jsonData.data || jsonData.data.length === 0) {
        $("#aplicaciones_empty").removeClass("d-none");
      }

      $("#tabla_aplicaciones").DataTable({
        destroy: true,
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        data: jsonData.data || [],
        columns: [
          {
            data: "Fecha",
            render: function (data) {
              if (!data) return "";
              return data.split("-").reverse().join("/");
            },
          },
          {
            data: "TipoRelacionado",
            defaultContent: "",
          },
          {
            data: "NumeroRelacionado",
            defaultContent: "",
          },
          {
            data: "Importe",
            render: function (data) {
              return formatoMonedaAplicacion(data);
            },
          },
          {
            data: "Usuario",
            defaultContent: "",
          },
        ],
        language: {
          emptyTable: "No hay aplicaciones registradas",
        },
      });

      const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("modal_aplicaciones"),
      );
      modal.show();
    },
    error: function (xhr) {
      alerta(
        "error",
        "Error del servidor",
        xhr.responseText || "No se pudo consultar el detalle.",
      );
    },
  });
}

$("#asana_gid").change(function () {
  var id = document.getElementById("codigo").value;
  var asana_gid = $("#asana_gid").val();

  if (asana_gid == "0") {
    $("#asana_gid").prop("disabled", true);
  } else {
    $("#asana_gid").prop("disabled", false);
  }

  $.ajax({
    data: {
      Asignar_tareas_asana: 1,
      TareasAsana_gid: asana_gid,
      idCliente: id,
    },
    url: "Procesos/php/tablas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);

      if (jsonData.success == 1) {
        // $('#asana_gid').empty();
        // $('#asana_gid').append('<option value="">Seleccione</option>');
        // obtenerUsuarios();
        toast("success", "Exito !", "Tareas Asignadas Correctamente.");
      }
    },
  });
});

$("#tareas_asana").click(function () {
  var id = document.getElementById("codigo").value;
  $.ajax({
    data: { TareasAsana: 1, idCliente: id },
    url: "Procesos/php/tablas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);

      if (jsonData.success == 1) {
        $("#asana_gid").empty();
        $("#asana_gid").append('<option value="">Seleccione</option>');
        obtenerUsuarios();
        toast("success", "Exito !", "Tareas Asignadas Correctamente.");
      }
    },
  });
});
function obtenerUsuarios() {
  $.ajax({
    data: { Usuarios_asana: 1 },
    url: "Procesos/php/tablas.php",
    type: "POST",
    success: function (data) {
      var opciones = JSON.parse(data);

      opciones.forEach(function (opcion) {
        $("#asana_gid").append(
          '<option value="' +
            opcion.gid_asana +
            '">' +
            opcion.Nombre +
            "</option>",
        );
      });
    },
    error: function () {
      alert("Error al obtener las opciones.");
    },
  });
}

function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
  return results === null
    ? ""
    : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function eliminar_pago(i) {
  $.ajax({
    data: {
      Eliminar_pago_permisos: 1,
    },
    type: "POST",
    url: "Procesos/php/eliminapago.php",
    dataType: "json",
    success: function (jsonData) {
      if (jsonData.success == 1) {
        $("#modal_eliminar_pago_text").html("Estas por eliminar el pago " + i);
        $("#modal_eliminar_pago").modal("show");

        $("#modal_eliminar_pago_aceptar")
          .off("click")
          .on("click", function () {
            $("#modal_eliminar_pago").modal("hide");

            $.ajax({
              data: {
                Eliminar_pago: 1,
                idCtasctes: i,
              },
              type: "POST",
              url: "Procesos/php/eliminapago.php",
              dataType: "json",
              success: function (jsonData) {
                if (jsonData.success == 1) {
                  toast("success", "Éxito", "Pago eliminado correctamente");
                  $("#basic").DataTable().ajax.reload(null, false);
                } else if (jsonData.success == 0) {
                  toast(
                    "error",
                    "Error",
                    jsonData.msg ||
                      jsonData.error ||
                      "No se pudo eliminar el pago",
                  );
                } else if (jsonData.success == 401) {
                  $("#danger-alert-modal").modal("show");
                }
              },
              error: function (xhr) {
                toast(
                  "error",
                  "Error",
                  xhr.responseText || "Error del servidor",
                );
              },
            });
          });
      } else {
        $("#danger-alert-modal").modal("show");
      }
    },
    error: function (xhr) {
      toast("error", "Error", xhr.responseText || "Error del servidor");
    },
  });
}

function notifications(a) {
  $.ajax({
    data: {
      Notificatios: 1,
      id: a,
    },
    type: "POST",
    url: "../Clientes/Procesos/php/invoice.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (
        jsonData &&
        jsonData.data &&
        jsonData.data[0] &&
        jsonData.data[0].Fecha
      ) {
        var Fecha =
          jsonData &&
          jsonData.data &&
          jsonData.data[0] &&
          jsonData.data[0].Fecha
            ? jsonData.data[0].Fecha.split("-").reverse().join(".")
            : "";
        toast("success", "Email enviado el " + Fecha + " a " + jsonData.data[0].Mail, "Se han realizado cambios.");
      } else {
        console.warn("Fecha inválida en notifications:", jsonData);
      }
    },
  });
}

function actualizar_totales(id) {
  //ACTUALIZO LOS TOTALES
  $.ajax({
    data: {
      TotalRemitos: 1,
      id: id,
    },
    url: "../Clientes/Procesos/php/tablas.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        var table = $("#basic").DataTable();
        table.ajax.reload();

        var totalenviados = currencyFormat(Number(jsonData.totalenviados));

        var totalrecibidos = currencyFormat(Number(jsonData.totalrecibidos));

        $("#total_saldo").html(currencyFormat(Number(jsonData.saldo_total)));

        $("#totalenviados_label").html(totalenviados);

        if (jsonData.totalrecibidos) {
          $("#totalrecibidos_label").html(totalrecibidos);
        } else {
          $("#totalrecibidos_label").html(currencyFormat(Number(0)));
        }
      }
    },
  });
}

function eliminar_mvi(i) {
  $.ajax({
    data: {
      MovimientosInternos_eliminar: 1,
      id: i,
    },
    type: "POST",
    url: "../Clientes/Procesos/php/movimientos_internos.php",
    dataType: "json",
    success: function (jsonData) {
      if (jsonData.success == 1) {
        var id = document.getElementById("codigo").value;

        actualizar_totales(id);
        toast("success", "Éxito", "Movimiento interno eliminado correctamente");
      } else {
        toast("error", "Error", "No se pudo eliminar el movimiento interno");
      }
    },
  });
}

//CONTACT
$("#btn_agregar_contacto").click(function () {
  $("#contact-modal").modal("show");
});

$("#perfil_conctact").click(function () {
  var id = document.getElementById("codigo").value;

  $("#table-contact").DataTable({
    destroy: true,
    paging: false,
    searching: false,
    ajax: {
      url: "Procesos/php/tablas.php",
      data: {
        Contact: 1,
        id: id,
      },
      type: "post",
    },
    columns: [
      {
        data: "Nombre",
        render: function (data, type, row) {
          return "<td>" + row.Nombre + " " + row.Apellido + "</td>";
        },
      },
      { data: "email" },
      { data: "Sector" },
      { data: "Telefono" },
      {
        data: "NotifOperativo",
        render: function (data, type, row) {
          return `<div class="form-check form-switch"><input class="form-check-input switch-notif" type="checkbox" data-campo="NotifOperativo" data-id="${row.id}" ${+row.NotifOperativo ? "checked" : ""}></div>`;
        },
      },
      {
        data: "NotifAdministrativo",
        render: function (data, type, row) {
          return `<div class="form-check form-switch"><input class="form-check-input switch-notif" type="checkbox" data-campo="NotifAdministrativo" data-id="${row.id}" ${+row.NotifAdministrativo ? "checked" : ""}></div>`;
        },
      },
      {
        data: "id_hubspot",
        render: function (data, type, row) {
          return `<span class="badge bg-primary">${row.id_hubspot}</span>`;
        },
      },
      {
        data: "id",
        render: function (data, type, row) {
          return (
            `<a href="#" data-id="${row.id}" data-nombre="${row.Nombre}" data-apellido="${row.Apellido}" data-email="${row.email}" data-sector="${row.Sector}" data-telefono="${row.Telefono}" data-notifoperativo="${row.NotifOperativo}" data-notifadministrativo="${row.NotifAdministrativo}" data-bs-toggle="modal" data-bs-target="#contact-modal"><i class="mdi mdi-pencil text-warning"></i></a>` +
            `<a class="ml-2" id="contact-delete" data-id="${row.id}"><i class="mdi mdi-trash-can text-danger"></i></a>`
          );
        },
      },
    ],
  });
});

$("#table-contact").on("change", ".switch-notif", function () {
  var $switch = $(this);
  $.ajax({
    url: "Procesos/php/funciones.php",
    type: "post",
    data: {
      ToggleNotifContacto: 1,
      id_contacto: $switch.data("id"),
      campo: $switch.data("campo"),
      valor: this.checked ? 1 : 0,
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success != 1) {
        $switch.prop("checked", !$switch.prop("checked"));
        toast("error", "Error", "No se pudo actualizar la notificación.");
      }
    },
    error: function () {
      $switch.prop("checked", !$switch.prop("checked"));
      toast("error", "Error", "No se pudo actualizar la notificación.");
    },
  });
});

$(document).on("click", "#contact-delete", function (e) {
  var triggerLink = $(this);
  var dataID = triggerLink.data("id");
  var operationCanceled = false; // Variable para verificar si la operación fue cancelada

  // Mostrar la notificación con el botón "Cancelar"
  toast("warning", "Atención !", `<p>Estas por eliminar el contacto. <a href="#" id="cancel-action" class="alert-link" data-id="${dataID}">Cancelar</a></p>`);

  // Iniciar un temporizador que ejecuta la operación después de 5 segundos (5000 ms)
  var timeout = setTimeout(function () {
    if (!operationCanceled) {
      // Si la operación no fue cancelada, se ejecuta el AJAX
      $.ajax({
        url: "Procesos/php/funciones.php", // Cambia esta URL por la ruta correcta
        method: "POST",
        data: { Eliminar_contacto: 1, id_contacto: dataID },
        success: function (response) {
          // Manejar la respuesta exitosa del servidor
          toast("success", "Éxito", "El contacto ha sido eliminado.");
          $("#table-contact").DataTable().ajax.reload();
        },
        error: function (xhr, status, error) {
          // Manejar el error en la solicitud AJAX
          toast("error", "Error", "No se pudo eliminar el contacto.");
        },
      });
    }
  }, 5000); // Tiempo en milisegundos (5 segundos)

  // Manejador de clic en el botón "Cancelar"
  $(document).on("click", "#cancel-action", function (e) {
    e.preventDefault();

    operationCanceled = true; // Cambiar el estado a cancelado
    clearTimeout(timeout); // Cancelar el temporizador

    // Notificar al usuario que la operación ha sido cancelada
    toast("error", "Cancelado", "Operación de eliminación cancelada.");
  });
});

$("#contact-modal").on("shown.bs.modal", function (e) {
  var triggerLink = $(e.relatedTarget);
  var dataID = triggerLink.data("id");

  if (dataID) {
    $("#contact_modal_modificar_ok").show();
    $("#contact_modal_ok").hide();
    $("#modal-header modal-colored-header").css("bg-warning");
    $("#modal-header modal-colored-header").html("Editar Contacto");

    $("#contact_nombre").val(triggerLink.attr("data-nombre"));
    $("#contact_lastname").val(triggerLink.attr("data-apellido"));
    $("#contact_email").val(triggerLink.attr("data-email"));
    $("#contact_sector").val(triggerLink.attr("data-sector"));
    $("#contact_telefono").val(triggerLink.attr("data-telefono"));
    $("#id_contacto").val(triggerLink.attr("data-id"));
    $("#contact_notif_operativo").prop("checked", +triggerLink.attr("data-notifoperativo") === 1);
    $("#contact_notif_administrativo").prop("checked", +triggerLink.attr("data-notifadministrativo") === 1);
  } else {
    $("#contact_modal_modificar_ok").hide();
    $("#contact_modal_ok").show();
    $("#modal-header modal-colored-header").css("bg-primary");
    $("#modal-header modal-colored-header").html("Agregar Contacto");
    $("#contact_nombre").val("");
    $("#contact_lastname").val("");
    $("#contact_email").val("");
    $("#contact_sector").val("");
    $("#contact_telefono").val("");
    $("#id_contacto").val("");
    $("#contact_notif_operativo").prop("checked", false);
    $("#contact_notif_administrativo").prop("checked", false);
  }
});

//AGREGAR CONTACTO
$("#contact_modal_ok").click(function () {
  var id = document.getElementById("codigo").value;
  var nombre = $("#contact_nombre").val();
  var lastname = $("#contact_lastname").val();
  var email = $("#contact_email").val();
  var sector = $("#contact_sector").val();
  var telefono = $("#contact_telefono").val();
  var web = $("#web").val();
  var company = $("#select2-buscarcliente-container").val();
  var notifOperativo = $("#contact_notif_operativo").is(":checked") ? 1 : 0;
  var notifAdministrativo = $("#contact_notif_administrativo").is(":checked") ? 1 : 0;

  $.ajax({
    data: {
      Agregar_contacto: 1,
      idCliente: id,
      contact_nombre: nombre,
      contact_lastname: lastname,
      contact_email: email,
      contact_sector: sector,
      contact_telefono: telefono,
      contact_website: web,
      contact_company: company,
      contact_notif_operativo: notifOperativo,
      contact_notif_administrativo: notifAdministrativo,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);

      if (jsonData.success == 1) {
        var table_contact = $("#table-contact").DataTable();
        table_contact.ajax.reload();
        $("#contact-modal").modal("hide");
      } else {
        toast("error", "Error", "No se pudo agregar el contacto. " + jsonData.error);
      }
    },
  });
});

//MODIFICAR CONTACTO
$("#contact_modal_modificar_ok").click(function () {
  var id = document.getElementById("codigo").value;
  var nombre = $("#contact_nombre").val();
  var lastname = $("#contact_lastname").val();
  var email = $("#contact_email").val();
  var sector = $("#contact_sector").val();
  var telefono = $("#contact_telefono").val();
  var web = $("#web").val();
  var company = $("#select2-buscarcliente-container").val();
  var id_contacto = $("#id_contacto").val();
  var notifOperativo = $("#contact_notif_operativo").is(":checked") ? 1 : 0;
  var notifAdministrativo = $("#contact_notif_administrativo").is(":checked") ? 1 : 0;

  $.ajax({
    data: {
      Modificar_contacto: 1,
      idCliente: id,
      id_contacto: id_contacto,
      contact_nombre: nombre,
      contact_lastname: lastname,
      contact_email: email,
      contact_sector: sector,
      contact_telefono: telefono,
      contact_website: web,
      contact_notif_operativo: notifOperativo,
      contact_notif_administrativo: notifAdministrativo,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);

      if (jsonData.success == 1) {
        var table_contact = $("#table-contact").DataTable();
        table_contact.ajax.reload();
        $("#contact-modal").modal("hide");
        toast("success", "Exito", "Registro modificado !");
      } else {
        toast("error", "Error", "No se pudo modificar el contacto. " + jsonData.error);
      }
    },
  });
});

$("#btn_un_ctas").click(function () {
  var id = document.getElementById("codigo").value;
  var table_basic = $("#basic").DataTable();
  table_basic.destroy();

  $("#basic").DataTable({
    dom: "Bfrtip",
    buttons: ["copy", "csv", "excel", "pdf", "print"],
    paging: true,
    searching: true,
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(3) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
          //                 return parseInt(a) + parseInt(b);
        }, 0);
      total1 = this.api()
        .column(4) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
        }, 0);
      var sumadebe = currencyFormat(total);
      var sumahaber = currencyFormat(total1);
      var saldo = currencyFormat(total - total1);
      var saldo1 = Number(total - total1);

      $("#saldo_ctacte").html(saldo);

      if (saldo1 == 0) {
        document.getElementById("saldo_ctacte").className = "text-info";
      } else if (saldo1 > 0) {
        document.getElementById("saldo_ctacte").className = "text-danger";
      } else if (saldo1 < 0) {
        document.getElementById("saldo_ctacte").className = "text-warning";
      }
      $(this.api().column(3).footer()).html(sumadebe);
      $(this.api().column(4).footer()).html(sumahaber);
    },
    ajax: {
      url: "../Clientes/Procesos/php/tablas.php",
      data: {
        CtaCteUnificadas: 1,
        id: id,
      },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          if (type === "sort" || type === "type") {
            return row.TimeStamp || row.Fecha;
          }
          if (!row.Fecha) return "";
          var Fecha = row.Fecha.split("-").reverse().join(".");
          var Hora = "";

          if (row.TimeStamp) {
            Hora = row.TimeStamp.split(" ")[1].split(":").slice(0, 2).join(":");
          }

          return Fecha + "<br><small class='text-muted'>" + Hora + "</small>";
        },
      },
      {
        data: "RazonSocial",
      },
      {
        data: "TipoDeComprobante",
        render: function (data, type, row) {
          if (row.TipoDeComprobante === "Recibo de Pago") {
            return (
              row.TipoDeComprobante +
              " " +
              row.NumeroVenta +
              "<br>" +
              "<td>" +
              row.Comentario +
              "</td>"
            );
          } else {
            return row.TipoDeComprobante + " " + row.NumeroFactura;
          }
        },
      },
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
        //asignarpago(); esta en cargarpago.js
        render: function (data, type, row) {
          if (row.Haber > 0) {
            return (
              `<td><a onclick='ver_recibo_modal(${row.id})' title='Recibo' ><i class='mdi mdi-18px mdi-alpha-r-circle text-success'></i></a>` +
              `<a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></a>`
            );
          } else {
            var transformarBtn = "";
            if (row.TipoDeComprobante === "FACTURA PROFORMA") {
              transformarBtn =
                `<a href="javascript:void(0)" onclick="transformarProformaEnFactura(${row.id}, ${row.Debe}, '${row.NumeroFactura}')" title="Transformar en Factura A/B">` +
                `<i class="mdi mdi-18px mdi-file-swap-outline text-primary"></i></a>`;
            }
            return (
              `<td><a target='_blank' href='invoice.php?id=${row.id}' title='Comprobante' >` +
              `<i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
              `<a target='_blank' href='invoice_details.php?id=${row.id}' data-bs-toggle='tooltip' data-bs-placement='right' title='Detalle' data-original-title='Detalle'><i class='mdi mdi-18px mdi-alpha-d-circle text-warning'></i></a>` +
              transformarBtn +
              `</td>`
            );
          }
        },
      },
    ],
  });
});

// TRANSFORMAR FACTURA PROFORMA EN COMPROBANTE FISCAL VALIDO (FACTURA A/B)
function transformarProformaEnFactura(idCtasctes, debe, numeroFacturaProforma) {
  var id = document.getElementById("buscarcliente").value;

  var condivaValue = document.getElementById("nueva_condicion_facturacion").value;
  if (condivaValue == "") {
    condivaValue = document.getElementById("condicion_facturacion").value;
  }
  var esFacturaA = condivaValue == 1;
  var comprobante_tipo = esFacturaA ? "1" : "6";
  var comprobante = esFacturaA ? "FACTURAS A" : "FACTURAS B";

  var neto = debe / 1.21;
  var iva = debe - neto;

  Swal.fire({
    icon: "warning",
    title: "Transformar en " + comprobante,
    html:
      "Se va a emitir ante AFIP una <b>" + comprobante + "</b> real por " +
      currencyFormat(debe) + ", reemplazando la Factura Proforma " +
      (numeroFacturaProforma || "") + ".<br>Esta acción no se puede deshacer.",
    showCancelButton: true,
    confirmButtonText: "Sí, transformar",
    cancelButtonText: "Cancelar",
  }).then(function (result) {
    if (!result.isConfirmed) return;

    var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
    var direccion_f = document.getElementById("direccion_facturacion").value;
    var tipodocumento_f = document.getElementById("tipodocumento_facturacion").value;
    var documento_f = document.getElementById("cuit_facturacion").value;
    var fecha = new Date().toISOString().slice(0, 10);

    var dato = {
      Fecha: fecha,
      razonsocial_f: razonsocial_f,
      direccion_f: direccion_f,
      condiva_f: condivaValue,
      tipodocumento_f: tipodocumento_f,
      documento_f: documento_f,
      Documento: 99,
      ImpTotal: debe.toFixed(2),
      ImpTotalConc: 0,
      ImpNeto: neto.toFixed(2),
      ImpIva: iva.toFixed(2),
      ImpTrib: 0,
      Comprobante_tipo: comprobante_tipo,
      fecha_desde: fecha,
      fecha_hasta: fecha,
    };

    $.ajax({
      data: dato,
      url: "../afip.php/procesos/CreateVoucher.php",
      type: "post",
      beforeSend: function () {
        $("#warning-alert-modal").modal("show");
        $("#warning_text").html("Enviando los datos a AFIP");
      },
      success: function (respuesta) {
        var jsonData;
        try {
          jsonData = $.parseJSON(respuesta);
        } catch (err) {
          $("#warning_mt2_alert").html("Error !");
          $("#warning_text").html("Respuesta inválida de AFIP: " + err.message);
          return;
        }

        if (jsonData.data != 1 || !jsonData.CAE) {
          $("#warning_icono_alert").removeClass("dripicons-warning h1 text-warning").addClass("dripicons-wrong h1 text-danger");
          $("#warning_mt2_alert").html("Error !");
          $("#warning_text").html("Error! Comprobante No Facturado. Error: " + jsonData.error);
          return;
        }

        $("#warning_icono_alert").removeClass("dripicons-warning h1 text-warning").addClass("dripicons-checkmark h1 text-success");
        $("#warning_mt2_alert").html("Exito !");
        $("#warning_text").html("Exito ! Comprobante N " + jsonData.Numero);

        //GUARDO LA TRANSFORMACION EN EL SISTEMA
        $.ajax({
          data: {
            Facturar: 4,
            idCtasctes: idCtasctes,
            id: id,
            Comprobante: comprobante,
            NumeroComprobante: jsonData.Numero,
            PtoVta: jsonData.PtoVta,
            CAE: jsonData.CAE,
            FechaVencimientoCAE: jsonData.VencimientoCAE,
            ImpNeto: neto.toFixed(2),
            ImpIva: iva.toFixed(2),
            ImpTotal: debe.toFixed(2),
          },
          url: "Procesos/php/facturar.php",
          type: "post",
          success: function (respuesta2) {
            var jsonData1 = JSON.parse(respuesta2);
            if (jsonData1.success == 1) {
              toast("success", "Comprobante Generado con Exito !", "Se transformó la Proforma en " + comprobante + ".");
              $("#basic").DataTable().ajax.reload();
            } else {
              toast(
                "error",
                "Error al guardar en el sistema",
                "El comprobante ya se emitió en AFIP con CAE " + jsonData.CAE + " pero no se pudo guardar. Avisá a sistemas con este CAE.",
              );
            }
          },
        });
      },
    });
  });
}

//MODAL RECIBO
let reciboActualId = null;

function ver_recibo_modal(id) {
  reciboActualId = id;
  console.log("ID del recibo a mostrar:", id);

  // Antes esto apuntaba a Informes/recibo.php, un HTML viejo con su propio
  // diseño (reciclado de una plantilla de factura, con bugs propios) - ahora
  // muestra el mismo PDF con la impronta de HdrPdfBase que ya usa el resto
  // de los informes (recibo_pdf.php), igual que la vista previa de facturas.
  const url = "Informes/ver_recibo_pdf.php?id=" + id;

  $("#iframe_recibo").attr("src", url);
  $("#btn_abrir_recibo_nueva_pestana").attr(
    "href",
    "Informes/ver_recibo_pdf.php?id=" + id,
  );

  const modal = new bootstrap.Modal(
    document.getElementById("modal_ver_recibo"),
  );
  modal.show();
}
// MODAL RECIBO IMPRIMIR
$("#btn_imprimir_recibo_modal").on("click", function () {
  const iframe = document.getElementById("iframe_recibo");

  if (iframe && iframe.contentWindow) {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  } else {
    toast("error", "Error", "No se pudo imprimir el recibo.");
  }
});

// --- MODAL GENÉRICO DE DESTINATARIOS (badges) para factura/recibo ---
let _mailDestinatarios = [];
let _mailDestinatariosOnConfirm = null;

function renderMailDestinatariosBadges() {
  var $c = $("#mail_destinatarios_badges");
  $c.empty();
  _mailDestinatarios.forEach(function (email, idx) {
    $c.append(
      $(
        `<span class="badge bg-primary d-inline-flex align-items-center">${email} <i class="mdi mdi-close ms-1" style="cursor:pointer" data-idx="${idx}"></i></span>`,
      ),
    );
  });
}

$(document).on("click", "#mail_destinatarios_badges .mdi-close", function () {
  _mailDestinatarios.splice($(this).data("idx"), 1);
  renderMailDestinatariosBadges();
});

$("#mail_destinatarios_wrapper").on("click", function (e) {
  if (e.target === this) $("#mail_destinatarios_input").trigger("focus");
});

$("#mail_destinatarios_input").on("keydown", function (e) {
  if (e.key !== "Enter" && e.key !== ",") return;
  e.preventDefault();
  var val = $(this).val().trim().replace(/,$/, "");
  var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!val) return;
  if (!regex.test(val)) {
    toast("error", "Correo inválido", "Revisá el formato del correo.");
    return;
  }
  if (_mailDestinatarios.indexOf(val) === -1) {
    _mailDestinatarios.push(val);
    renderMailDestinatariosBadges();
  }
  $(this).val("");
});

function abrirModalDestinatarios(titulo, mailsPrecargados, onConfirm) {
  // Evita apilar este modal sobre el de vista previa (factura/recibo) que lo dispara,
  // porque el backdrop de modales anidados no se renderiza bien en este setup.
  $("#modal_factura_preview").modal("hide");
  $("#modal_ver_recibo").modal("hide");

  _mailDestinatarios = mailsPrecargados.slice();
  _mailDestinatariosOnConfirm = onConfirm;
  $("#modalEnviarMailTitulo").text(titulo);
  $("#mail_destinatarios_input").val("");
  renderMailDestinatariosBadges();
  $("#modal_enviar_mail_destinatarios").modal("show");
}

$("#btn_confirmar_envio_mail").on("click", function () {
  var pendiente = $("#mail_destinatarios_input").val().trim();
  var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (pendiente && regex.test(pendiente) && _mailDestinatarios.indexOf(pendiente) === -1) {
    _mailDestinatarios.push(pendiente);
  }
  if (_mailDestinatarios.length === 0) {
    toast("error", "Error", "Agregá al menos un destinatario.");
    return;
  }
  $("#modal_enviar_mail_destinatarios").modal("hide");
  if (typeof _mailDestinatariosOnConfirm === "function") {
    _mailDestinatariosOnConfirm(_mailDestinatarios.slice());
  }
});

// BOTON ENVIAR RECIBO POR MAIL
$("#btn_enviar_recibo_modal").on("click", function () {
  if (!reciboActualId) {
    toast("error", "Error", "No hay recibo seleccionado.");
    return;
  }

  $.ajax({
    url: "/SistemaTriangular/Clientes/Informes/enviar_recibo_mail.php",
    type: "POST",
    dataType: "json",
    data: {
      ObtenerMailRecibo: 1,
      id: reciboActualId,
    },
    success: function (jsonData) {
      if (jsonData.success != 1) {
        alerta(
          "error",
          "Error",
          jsonData.msg || "No se pudo obtener el mail del cliente.",
        );
        return;
      }

      var mailsPrecargados = (jsonData.mails || []).map(function (m) {
        return m.email;
      });

      abrirModalDestinatarios(
        "Enviar recibo por mail",
        mailsPrecargados,
        function (destinatarios) {
          $.ajax({
            url: "/SistemaTriangular/Clientes/Informes/enviar_recibo_mail.php",
            type: "POST",
            dataType: "json",
            data: {
              EnviarReciboMail: 1,
              id: reciboActualId,
              mailDestino: destinatarios,
            },
            beforeSend: function () {
              toast("info", "Procesando", "Enviando recibo por mail...");
            },
            success: function (jsonData) {
              if (jsonData.success == 1) {
                toast("success", "Perfecto", "Recibo enviado correctamente.");
              } else {
                alerta(
                  "error",
                  "Error",
                  jsonData.msg || "No se pudo enviar el recibo.",
                );
              }
            },
            error: function (xhr) {
              alerta("error", "Error del servidor", xhr.responseText);
            },
          });
        },
      );
    },
    error: function (xhr) {
      alerta("error", "Error del servidor", xhr.responseText);
    },
  });
});

$("#modal_factura_preview").on("hidden.bs.modal", function () {
  $("#iframe_factura_preview").attr("src", "");
  $("#btn_abrir_factura_modal").attr("href", "#");
  facturaActualId = null;
});
//ENVIAR FACTURA POR MAIL
// let facturaActualId =
// new URLSearchParams(window.location.search).get("id") || null;
let facturaActualId = null;
function abrirModalFactura(id) {
  if (!id) {
    toast("error", "Error", "No hay factura seleccionada.");
    return;
  }

  facturaActualId = id;

  const urlFactura = `/SistemaTriangular/Clientes/Informes/ver_factura_pdf.php?id=${id}`;

  $("#iframe_factura_preview").attr("src", urlFactura);
  $("#btn_abrir_factura_modal").attr("href", urlFactura);

  const modalEl = document.getElementById("modal_factura_preview");
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

// BOTON IMPRIMIR FACTURA (antes no tenía handler bindeado, no hacía nada)
$("#btn_imprimir_factura_modal").on("click", function () {
  const iframe = document.getElementById("iframe_factura_preview");

  if (iframe && iframe.contentWindow) {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  } else {
    toast("error", "Error", "No se pudo imprimir la factura.");
  }
});

// BOTON ENVIAR FACTURA POR MAIL
$("#btn_enviar_factura_modal").on("click", function () {
  if (!facturaActualId) {
    toast("error", "Error", "No hay factura seleccionada.");
    return;
  }

  // Se guarda aparte porque abrirModalDestinatarios cierra #modal_factura_preview,
  // y ese cierre resetea facturaActualId a null (ver handler "hidden.bs.modal").
  var idFacturaEnvio = facturaActualId;

  $.ajax({
    url: "/SistemaTriangular/Clientes/Informes/enviar_factura_mail.php",
    type: "POST",
    dataType: "json",
    data: {
      ObtenerMailFactura: 1,
      id: idFacturaEnvio,
    },
    success: function (jsonData) {
      if (jsonData.success != 1) {
        alerta(
          "error",
          "Error",
          jsonData.msg || "No se pudo obtener el mail del cliente.",
        );
        return;
      }

      var mailsPrecargados = (jsonData.mails || []).map(function (m) {
        return m.email;
      });

      abrirModalDestinatarios(
        "Enviar factura por mail",
        mailsPrecargados,
        function (destinatarios) {
          $.ajax({
            url: "/SistemaTriangular/Clientes/Informes/enviar_factura_mail.php",
            type: "POST",
            dataType: "json",
            data: {
              EnviarFacturaMail: 1,
              id: idFacturaEnvio,
              mailDestino: destinatarios,
            },
            beforeSend: function () {
              toast("info", "Procesando", "Enviando factura por mail...");
            },
            success: function (jsonData) {
              if (jsonData.success == 1) {
                toast("success", "Perfecto", "Factura enviada correctamente.");
              } else {
                alerta(
                  "error",
                  "Error",
                  jsonData.msg || "No se pudo enviar la factura.",
                );
              }
            },
            error: function (xhr) {
              alerta("error", "Error del servidor", xhr.responseText);
            },
          });
        },
      );
    },
    error: function (xhr) {
      alerta("error", "Error del servidor", xhr.responseText);
    },
  });
});
$(document).ready(function () {
  $("#switch3").change(function () {
    var switchValue = $(this).is(":checked") ? 1 : 0; // Obtener el valor del switch (1 si está activado, 0 si está desactivado)
    var id = document.getElementById("codigo").value;
    $.ajax({
      type: "POST",
      url: "Procesos/php/funciones.php", // Ruta al script PHP que manejará la solicitud
      data: { Colecta: 1, idCliente: id, switchValue: switchValue }, // Datos a enviar (valor del switch)
      success: function (response) {
        var jsonData = JSON.parse(response);

        if (jsonData.success == 1) {
          toast("success", "Exito !", "Registro Actualizado.");

          if (switchValue == 0) {
            $("#info_facturacion").css("display", "block");
            $("#info_facturacion_text").html(
              "<strong>Info - Facturación:</strong> El cliente <b>no</b> utiliza el servicio de <b>colecta</b>",
            );
          } else {
            $("#info_facturacion").css("display", "block");
            $("#info_facturacion_text").html(
              "<strong>Info - Facturación:</strong> El cliente utiliza el servicio de <b>colecta</b>",
            );
          }
        } else {
          toast("error", "Error !", jsonData.error);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error al enviar el valor del switch a PHP:", error);
      },
    });
  });

  //DESTRUIR TODAS LAS GUIAS
  var tabla_facturacion_proforma = $("#tabla_facturacion_proforma").DataTable();
  tabla_facturacion_proforma.destroy();

  //DESTRUIMOS LA TABLA FACTURACION
  var table_facturacion = $("#facturacion_tabla").DataTable();
  table_facturacion.destroy();

  //DESTRUIMOS LA TABLA BASIC CTA CTE
  var table_basic = $("#basic").DataTable();
  table_basic.destroy();

  //DESTRUIMOS LA TABLA RELACIONES
  var table_relaciones = $("#relaciones_tabla").DataTable();
  table_relaciones.destroy();

  //DESTRUIMOS LA TABLA TARIFAS
  var table_tarifas = $("#tarifas_tabla").DataTable();
  table_tarifas.destroy();

  //DESTUIMOS LA TABLA RECIBIDAS
  var table_recibidas = $("#guias_recibidas_tabla").DataTable();
  table_recibidas.destroy();

  //DESTRUIMOS LA TABLA REMITOS ENVIADOS
  var table_enviadas = $("#guias_enviadas_tabla").DataTable();
  table_enviadas.destroy();
  //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  var table_facturacion_proforma_recorridos = $(
    "#tabla_facturacion_proforma_recorridos",
  ).DataTable();
  table_facturacion_proforma_recorridos.destroy();
  //DESTRUIMOS LA TABLA RECORRIDOS
  //   var tabla_recorridos = $('#recorridos_tabla').DataTable();
  //   tabla_recorridos.destroy();
});

//ERROR 401
$("#danger-alert-modal-button").click(function () {
  $("#danger-alert-modal").modal("hide");
});

//MODIFICAR CICLO FACTURACION
$("#ciclo_facturacion").change(function () {
  var id = document.getElementById("codigo").value;
  var ciclo = document.getElementById("ciclo_facturacion").value;

  $.ajax({
    data: {
      Ciclo_facturacion: 1,
      idCliente: id,
      ciclo: ciclo,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == 1) {
        toast("success", "Exito !", "Registro Actualizado.");
      } else {
        toast("error", "Error !", "Registro No Actualizado.");
      }
    },
  });
});

// MODIFICAR COMPROBANTE CUADRO FACTURACION
$("#modificar_comprobante").click(function () {
  var comp = document.getElementById("comprobante_up").value;
  document.getElementById("comprobante_up_display").style.display = "none";
  document.getElementById("modificar_comprobante").style.display = "none";

  var comp_2 = parseInt($("#tipo_de_factura").val());

  console.log("ver", comp_2);

  if (comp_2 == "3") {
    if (comp == "FACTURAS A") {
      document.getElementById("select_nc_nd_A").style.display = "block";
      document.getElementById("select_nc_nd_B").style.display = "none";
      document.getElementById("selectA").style.display = "none";
      document.getElementById("selectB").style.display = "none";
    } else {
      document.getElementById("select_nc_nd_B").style.display = "block";
      document.getElementById("select_nc_nd_A").style.display = "none";
      document.getElementById("selectA").style.display = "none";
      document.getElementById("selectB").style.display = "none";
    }
  } else {
    if (comp == "FACTURAS A") {
      document.getElementById("selectA").style.display = "block";
      document.getElementById("selectB").style.display = "none";
      document.getElementById("select_nc_nd_A").style.display = "none";
      document.getElementById("select_nc_nd_B").style.display = "none";
    } else {
      document.getElementById("selectB").style.display = "block";
      document.getElementById("selectA").style.display = "none";
      document.getElementById("select_nc_nd_A").style.display = "none";
      document.getElementById("select_nc_nd_B").style.display = "none";
    }
  }
});

//MODIFICAR COMPROBANTE CUADRO FACTURACION X RECORRIDO
$("#modificar_comprobante_r").click(function () {
  var comp = document.getElementById("comprobante_up_r").value;
  document.getElementById("comprobante_up_display_r").style.display = "none";
  document.getElementById("modificar_comprobante_r").style.display = "none";

  if (comp == "FACTURAS A") {
    document.getElementById("selectA_r").style.display = "block";
  } else {
    document.getElementById("selectB_r").style.display = "block";
  }
});

function buscarcomprobante(a) {
  var comp = a;

  $.ajax({
    data: {
      NComprobante: 1,
      tipodecomprobante: comp,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#ncomprobante_up_r").val(
        jsonData.PuntoVenta + "-" + jsonData.NComprobante,
      );
      $("#comprobante_up_r").val(jsonData.Comprobante);
      $("#select_up_r").val(jsonData.Comprobante);
      $("#comprobante_tipo_r").val(comp);

      console.log("veo_comp", comp);

      if (comp == 0) {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton",
        ).style.display = "none";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton",
        ).style.display = "block";
      } else {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton",
        ).style.display = "block";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton",
        ).style.display = "none";
      }
    },
  });
}

//SELECT NOTA DE CREDITO Y DEBITO A

// Evento de delegación para cambios en el select
$(document).on("change", "#comprobantes_cbtasoc", function () {
  var selectedValue = $(this).val();
  var selectedOption = $(this).find("option:selected");

  var numeroDespuesGuion = selectedOption.text().split("-")[1].trim();

  // Obtener los datos adicionales del comprobante seleccionado
  var ImporteNeto = selectedOption.data("importeneto");
  var Iva = selectedOption.data("iva");
  var Total = selectedOption.data("total");

  // console.log('data',selectedOption.data('importeneto'));

  $("#cbteasoc_tipo").val(selectedValue);
  $("#cbteasoc_nro").val(numeroDespuesGuion);
  $("#neto_up").val(ImporteNeto);
  $("#iva_up").val(Iva);
  $("#total_up").val(Total);

  $("#cbteasoc_tipo").val(selectedValue);
  $("#cbteasoc_nro").val(numeroDespuesGuion);
});

// Evento change para el select inicial
$("#comprobante_nc_nd_selectA").change(function () {
  document.getElementById("cbteasoc").style.display = "block";
  var select = $("#comprobantes_cbtasoc");
  select.empty();

  let id_cliente = document.getElementById("codigo").value;
  let comprobante = $(this).val();

  $.ajax({
    data: {
      cbteasoc_comprobantes: 1,
      idCliente: id_cliente,
      comprobante: comprobante,
    },
    url: "Procesos/php/funciones.php",
    type: "POST",
    dataType: "json",
    success: function (data) {
      $.each(data, function (index, option) {
        select.append(
          '<option value="' +
            option.TipoDeComprobante +
            '" data-iva="' +
            option.Iva3 +
            '" data-importeneto="' +
            option.ImporteNeto +
            '" data-total="' +
            option.Total +
            '">' +
            option.NumeroComprobante +
            "</option>",
        );
      });
    },
    error: function (error) {
      console.error("Error en la solicitud Ajax:", error);
    },
  });
});

$("#comprobante_nc_nd_selectB").change(function () {
  document.getElementById("cbteasoc").style.display = "block";
});

$("#comprobante_selectA").change(function () {
  var comp = parseInt($("#comprobante_selectA").val());
  var comp_2 = parseInt($("#tipo_de_factura").val());
  console.log("PRUEBA FACTURACION", comp_2);
  //SI UTILIZO EL MODAL DE GENERAR COMPROBANTE
  if (comp_2 == 3) {
    switch (comp) {
      case 0:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";

        break;
      case 1:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "block";
        document.getElementById("cbteasoc").style.display = "none";

        break;
      case 2:
        document.getElementById("cbteasoc").style.display = "block";
        // console.log('opcion',2);
        break;
      case 3:
        document.getElementById("cbteasoc").style.display = "block";
        // console.log('opcion',3);
        break;
      default:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "block";
        document.getElementById("cbteasoc").style.display = "none";

        break;
    }
  } else {
    console.log("PRUEBA FACTURACION si no es 3", comp);

    switch (comp) {
      case 0:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "block";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";
        break;
      case 1:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById("cbteasoc").style.display = "none";
        break;
      case 2:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById("cbteasoc").style.display = "block";
        break;
      case 3:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById("cbteasoc").style.display = "block";
        break;
      default:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";
        break;
    }
  }

  $("#comprobante_tipo").val(comp);

  $.ajax({
    data: {
      NComprobante: 1,
      tipodecomprobante: comp,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#ncomprobante_up").val(
        jsonData.PuntoVenta + "-" + jsonData.NComprobante,
      );

      var comp_2 = parseInt($("#tipo_de_factura").val());

      if (comp_2 == 3) {
        $("#comprobante_up").val("Seleccione un Comprobante");
      } else {
        $("#comprobante_up").val(jsonData.Comprobante);
      }
    },
  });
});

$("#comprobante_selectB").change(function () {
  var comp = parseInt($("#comprobante_selectB").val());
  var comp_2 = parseInt($("#tipo_de_factura").val());

  //SI UTILIZO EL MODAL DE GENERAR COMPROBANTE
  if (comp_2 == 3) {
    switch (comp) {
      case 0:
        //   console.log('opcion',0);
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";

        break;
      case 1:
        //   console.log('opcion',1);
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "block";
        document.getElementById("cbteasoc").style.display = "none";

        break;
      case 2:
        document.getElementById("cbteasoc").style.display = "block";
        //   console.log('opcion',2);
        break;
      case 3:
        document.getElementById("cbteasoc").style.display = "block";
        //   console.log('opcion',3);
        break;
      default:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "block";
        document.getElementById("cbteasoc").style.display = "none";

        break;
    }
  } else {
    console.log("PRUEBA FACTURACION B", comp);
    switch (comp) {
      case 0: //FACTURAS PROFORMA
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "block";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        break;
      case 1: //FACTURAS A
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";

        break;
      case 6: //FACTURAS B
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";

        break;
      case 3:
        document.getElementById("cbteasoc").style.display = "block";
        break;
      default:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton",
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";
        break;
    }
  }

  $("#comprobante_tipo").val(comp);

  $.ajax({
    data: {
      NComprobante: 1,
      tipodecomprobante: comp,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#ncomprobante_up").val(
        jsonData.PuntoVenta + "-" + jsonData.NComprobante,
      );

      var comp_2 = parseInt($("#tipo_de_factura").val());

      if (comp_2 == 3) {
        $("#comprobante_up").val("Seleccione un Comprobante");
      } else {
        $("#comprobante_up").val(jsonData.Comprobante);
      }
    },
  });
});

// function currencyFormat(num) {
// return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
// }

function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
  return results === null
    ? ""
    : decodeURIComponent(results[1].replace(/\+/g, " "));
}

$(document).on("change", 'select[name="formadepago"]', function (e) {
  console.log("value", this.value);

  if (this.value == "000111400") {
    $("#confirmarpago_botton").prop("disabled", true);
    $("#efectivo").hide();
    $("#cheques").hide();
    $("#transferencia").hide();

    document.getElementById("mercadopago").style.display = "flex";
  } else if (this.value == "000111100") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#transferencia").hide();
    $("#cheques").hide();
    document.getElementById("efectivo").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  } else if (this.value == "000112400") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#efectivo").hide();
    $("#transferencia").hide();
    document.getElementById("cheques").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  } else if (this.value != "") {
    // Cualquier otra forma de pago (cuentas bancarias) se trata como transferencia,
    // así no hay que tocar este JS cada vez que se da de alta un banco nuevo.
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#efectivo").hide();
    $("#cheques").hide();
    document.getElementById("transferencia").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  }
});

$("#modificar_relacion").click(function () {
  document.getElementById("relacion_select").style.display = "block";
  document.getElementById("relacion").style.display = "none";
});

$("#modificar_condicion_facturacion").click(function () {
  document.getElementById("condicion_select").style.display = "block";
  document.getElementById("condicion_div").style.display = "none";
});

$("#nueva_relacion").change(function () {
  if (
    document.getElementById("nueva_relacion").value != "" ||
    document.getElementById("nueva_relacion").value != "Seleccionar Relacion"
  ) {
    var relacion = document.getElementById("nueva_relacion").value;
  } else {
    var relacion = document.getElementById("relacionasignada").value;
  }
  var id = document.getElementById("codigo").value;

  $.ajax({
    data: {
      ConfirmarRelacion: 1,
      id: id,
      relacion: relacion,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == 1) {
        toast("success", "Exito !", "Registro Actualizado.");
      }
    },
  });
});

$(document).on("change", 'input[type="checkbox"]', function (e) {
  if (this.id == "accesoweb") {
    if (this.checked) {
      $("#accesoweb").val(1);
    } else {
      $("#accesoweb").val(0);
    }
  }
  if (this.id == "retira") {
    if (this.checked) {
      $("#retira").val(1);
      $("#retira_label").html("El Cliente Requiere Retiros y Entregas");
    } else {
      $("#retira").val(0);
      $("#retira_label").html("El Cliente Requiere Solo Entregas");
    }
  }
});

//Seleccionas todos los elementos con clase test
var divs = form_clientes.getElementsByClassName("form-control");

//Recorres la lista de elementos seleccionados
for (var i = 0; i < divs.length; i++) {
  //Añades un evento a cada elemento
  divs[i].addEventListener("change", function () {
    toast("info", "Recuerde Guardar el formulario !", "Se han realizado cambios.");
  });
}
//IDEM FORM PARA CUSTOM
var divsC = form_clientes.getElementsByClassName("custom-control-input");

//Recorres la lista de elementos seleccionados
for (var i = 0; i < divsC.length; i++) {
  //Añades un evento a cada elemento
  divsC[i].addEventListener("change", function () {
    toast("info", "Recuerde Guardar el formulario !", "Se han realizado cambios.");
  });
}

var table = $("#basic").DataTable();
table.destroy();

//DESDE ACA OBSERVACIONES EN FACTURAS Y PAGOS
function obs_modify(id) {
  $.ajax({
    data: {
      Comentario_modify: 1,
      idctasctes: id,
    },
    url: "Procesos/php/funciones.php",

    type: "post",

    beforeSend: function () {},
    dataType: "json",
    success: function (jsonData) {
      // var jsonData = JSON.parse(respuesta);
      $("#textarea-comentario").val(jsonData.obs);
      $("#center_modal").modal("show");
      $("#textarea-comentario_id").val(id);
    },
  });
}

$("#textarea-comentario_ok").click(function () {
  let id = $("#textarea-comentario_id").val();
  let com = $("#textarea-comentario").val();

  $.ajax({
    data: {
      Comentario_modify_update: 1,
      idctasctes: id,
      com: com,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == 1) {
        $("#basic").DataTable().ajax.reload(null, false);
      } else {
        console.log("error", jsonData.error);
      }
    },
  });
});

// Se incrementa en cada cambio de cliente; los callbacks de los AJAX que dispara
// el change descartan su respuesta si para cuando llega ya se eligió otro cliente
// (evita que una respuesta vieja pise datos de un cliente más nuevo).
let _clienteChangeSeq = 0;

$("#buscarcliente").change(function () {
  var miClienteChangeSeq = ++_clienteChangeSeq;

  obtenerUsuarios();

  document.getElementById("crearcliente").style.display = "none";
  document.getElementById("relacion_select").style.display = "none";
  document.getElementById("relacion").style.display = "block";
  $("#claveweb_label").prop("readonly", false);

  // Si el modal de "Asociar Pago" quedó abierto de la selección anterior, se cierra:
  // sus checkboxes/arrays internos (checkedPagos, checkedFacturas, etc. en cargarpago.js)
  // quedarían apuntando a facturas/pagos del cliente anterior.
  if ($("#asociar-pagos-modal").hasClass("show")) {
    $("#asociar-pagos-modal").modal("hide");
  }

  // Todos los destroy de abajo estan guardados con isDataTable: si uno solo
  // de ellos tira un error (tabla nunca inicializada aun), corta en seco el
  // resto de este handler -y con eso el fetch/repintado del cliente nuevo-,
  // dejando el resto de las tablas "colgadas" mostrando al cliente anterior
  // (mismo motivo por el que #table-contact y #recorridos_tabla ya lo tenian).

  //DESTRUIR TODAS LAS GUIAS
  if ($.fn.DataTable.isDataTable("#tabla_facturacion_proforma")) {
    $("#tabla_facturacion_proforma").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA FACTURACION
  if ($.fn.DataTable.isDataTable("#facturacion_tabla")) {
    $("#facturacion_tabla").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA BASIC CTA CTE
  if ($.fn.DataTable.isDataTable("#basic")) {
    $("#basic").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA RELACIONES
  if ($.fn.DataTable.isDataTable("#relaciones_tabla")) {
    $("#relaciones_tabla").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA TARIFAS
  if ($.fn.DataTable.isDataTable("#tarifas_tabla")) {
    $("#tarifas_tabla").DataTable().destroy();
  }

  //DESTUIMOS LA TABLA RECIBIDAS
  if ($.fn.DataTable.isDataTable("#guias_recibidas_tabla")) {
    $("#guias_recibidas_tabla").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA REMITOS ENVIADOS
  if ($.fn.DataTable.isDataTable("#guias_enviadas_tabla")) {
    $("#guias_enviadas_tabla").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  if ($.fn.DataTable.isDataTable("#tabla_facturacion_proforma_recorridos")) {
    $("#tabla_facturacion_proforma_recorridos").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA DE CONTACTOS (si no, queda mostrando los del cliente anterior)
  if ($.fn.DataTable.isDataTable("#table-contact")) {
    $("#table-contact").DataTable().destroy();
  }

  //DESTRUIMOS LA TABLA DE RECORRIDOS (mismo motivo)
  if ($.fn.DataTable.isDataTable("#recorridos_tabla")) {
    $("#recorridos_tabla").DataTable().destroy();
  }

  //BRORRAR LOS DATOS DE FECHAS
  $("#min").val("");
  $("#max").val("");

  var id = document.getElementById("buscarcliente").value;

  var dato = {
    Datos: 1,
    id: id,
  };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    dataType: "json",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (jsonData) {
      // var jsonData = JSON.parse(response);
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      if (jsonData.success == "1") {
        document.getElementById("steps").style.display = "flex";
        $("#codigo").val(jsonData.id);

        // "Guías a Facturar", "Remitos Recibidos/Enviados" y "Recorridos" NO
        // se recargan solos con el cliente nuevo (a diferencia de Cta.Cte /
        // Relaciones / Tarifas, que se piden mas abajo en este mismo success).
        // Es a proposito: al cambiar de cliente estas tablas deben quedar
        // vacias (ya se destruyeron mas arriba) hasta que el usuario pida una
        // busqueda nueva a mano (clickeando esa pestaña o, en "Guías a
        // Facturar", el boton Filtro) - no se auto-completan solas.

        $("#razonsocial").val(jsonData.RazonSocial);
        $("#direccion").val(jsonData.direccion);
        $("#localidad").val(jsonData.localidad);
        $("#provincia").val(jsonData.provincia);
        $("#codigopostal").val(jsonData.codigopostal);
        $("#telefono").val(jsonData.telefono);
        $("#celular").val(jsonData.celular);
        $("#contacto").val(jsonData.contacto);
        $("#iva").val(jsonData.iva);
        $("#cuit").val(jsonData.Cuit);
        $("#rubro").val(jsonData.Rubro);
        $("#condicion").val(jsonData.Condicion);
        $("#email").val(jsonData.Mail);
        $("#web").val(jsonData.Web);
        $("#observaciones").val(jsonData.Observaciones);
        $("#horario_entrega_cliente").val(jsonData.HorarioEntregaSolicitado);
        $("#ingresosbrutos").val(jsonData.IngresosBrutos);
        $("#relacionasignada").val(jsonData.RelacionAsignada);
        $("#relacionasignada_label").val(jsonData.RelacionAsignada_label);
        //FACTURACION
        $("#razonsocial_facturacion").val(jsonData.RazonSocial_f);
        $("#direccion_facturacion").val(jsonData.Direccion_f);
        $("#tipodocumento_facturacion").val(jsonData.TipoDocumento_f);
        $("#cuit_facturacion").val(jsonData.Cuit_f);
        $("#condicion_facturacion").val(jsonData.CondicionAnteIva_f);
        $("#ciclo_facturacion_label").html(jsonData.CicloFacturacion);
        $("#observaciones_facturacion").val(jsonData.Observaciones_f);
        //ASANA
        if (jsonData.TareasAsana == 1) {
          $("#tareas_asana").prop("checked", true);
        } else {
          $("#tareas_asana").prop("checked", false);
        }

        $("#asana_gid").val(jsonData.TareasAsana_gid).trigger("change.select2");

        // INFO COLECTAS
        if (jsonData.Colecta == 1) {
          $("#info_facturacion").css("display", "block");
          $("#info_facturacion_text").html(
            "<strong>Info - Facturación:</strong> El cliente utiliza el servicio de <b>colecta</b>",
          );
        } else {
          $("#info_facturacion").css("display", "block");
          $("#info_facturacion_text")
            .css("display", "block")
            .html(
              "<strong>Info - Facturación:</strong> El cliente <b>no</b> utiliza el servicio de <b>colecta</b>",
            );
        }

        //INTEGRACIONES
        console.log("user_id", jsonData.user_id);
        //SEGURO
        $("#sure_perc").val(jsonData.sure_perc);
        $("#sure_min").val(jsonData.sure_min);

        if (jsonData.user_id > 0) {
          // document.getElementById("meli_switch").checked = true;
          $("#meli_text").html("Está conectado via API a Meli");
          $("#meli_user_id").val(jsonData.user_id);
        } else {
          $("#meli_text").html("No está conectado a Meli");
          // document.getElementById("meli_switch").checked = false;
        }

        if (jsonData.AccesoWeb == 1) {
          document.getElementById("accesoweb").checked = true;
          $("#claveweb_label").get(0).type = "password";
        } else {
          document.getElementById("accesoweb").checked = false;
        }
        if (jsonData.Retira == 1) {
          document.getElementById("retira").checked = true;
          $("#retira_label").html("El Cliente Requiere Solo Entregas");
        } else {
          document.getElementById("retira").checked = false;
          $("#retira_label").html("El Cliente Requiere Retiros y Entregas");
        }
        var id = document.getElementById("codigo").value;

        $("#basic").DataTable({
          dom: "Bfrtip",
          buttons: ["copy", "csv", "excel", "pdf", "print"],
          paging: true,
          searching: true,
          footerCallback: function (row, data, start, end, display) {
            total = this.api()
              .column(3) //numero de columna a sumar
              //.column(1, {page: 'current'})//para sumar solo la pagina actual
              .data()
              .reduce(function (a, b) {
                return Number(a) + Number(b);
                //                 return parseInt(a) + parseInt(b);
              }, 0);
            total1 = this.api()
              .column(4) //numero de columna a sumar
              //.column(1, {page: 'current'})//para sumar solo la pagina actual
              .data()
              .reduce(function (a, b) {
                return Number(a) + Number(b);
              }, 0);
            var sumadebe = currencyFormat(total);
            var sumahaber = currencyFormat(total1);
            var saldo = currencyFormat(total - total1);
            var saldo1 = Number(total - total1);

            $("#saldo_ctacte").html(saldo);

            if (saldo1 == 0) {
              document.getElementById("saldo_ctacte").className = "text-info";
            } else if (saldo1 > 0) {
              document.getElementById("saldo_ctacte").className = "text-danger";
            } else if (saldo1 < 0) {
              document.getElementById("saldo_ctacte").className =
                "text-warning";
            }
            $(this.api().column(3).footer()).html(sumadebe);
            $(this.api().column(4).footer()).html(sumahaber);
          },
          ajax: {
            url: "../Clientes/Procesos/php/tablas.php",
            data: {
              CtaCte: 1,
              id: id,
            },
            type: "post",
          },
          columns: [
            {
              data: "Fecha",
              render: function (data, type, row) {
                if (type === "sort" || type === "type") {
                  var hora = row.TimeStamp
                    ? row.TimeStamp.split(" ")[1]
                    : "00:00:00";
                  return row.Fecha + " " + hora;
                }
                if (!row.Fecha) return "";
                var Fecha = row.Fecha.split("-").reverse().join(".");
                var Hora = "";

                if (row.TimeStamp) {
                  Hora = row.TimeStamp.split(" ")[1]
                    .split(":")
                    .slice(0, 2)
                    .join(":");
                }

                return (
                  Fecha + "<br><small class='text-muted'>" + Hora + "</small>"
                );
              },
            },
            {
              data: "RazonSocial",
            },
            {
              data: "TipoDeComprobante",
              render: function (data, type, row) {
                var comprobante = "";

                if (
                  row.TipoDeComprobante === "Recibo de Pago" ||
                  row.TipoDeComprobante === "MOVIMIENTO INTERNO"
                ) {
                  comprobante = row.TipoDeComprobante + " " + row.NumeroVenta;
                } else {
                  comprobante = row.TipoDeComprobante + " " + row.NumeroFactura;
                }

                if (row.TipoDeComprobante == "MOVIMIENTO INTERNO") {
                  return `${comprobante}<br><small class="mr-2 text-muted">${row.Comentario || ""}</small>`;
                } else {
                  return `${comprobante}<br><small class="mr-2 text-muted"><a id="${row.id}" onclick="obs_modify(this.id)"><i class='mdi mdi-14px mdi-pencil text-warning'></i> ${row.Comentario || ""}</a></small>`;
                }
              },
            },
            {
              data: "Debe",
              render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
            },
            {
              data: "Haber",
              render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
            },

            {
              data: "EstadoAplicacion",
              render: function (data, type, row) {
                let badgeClass = "bg-light text-dark";
                let texto = "S/D";

                if (data === "PENDIENTE") {
                  badgeClass = "bg-danger";
                  texto = "Pendiente";
                } else if (data === "PARCIAL") {
                  badgeClass = "bg-warning text-dark";
                  texto = "Parcial";
                } else if (data === "IMPUTADA") {
                  badgeClass = "bg-success";
                  texto = "Imputada";
                } else if (data === "DISPONIBLE") {
                  badgeClass = "bg-info";
                  texto = "Disponible";
                } else if (data === "APLICADO") {
                  badgeClass = "bg-secondary";
                  texto = "Aplicado";
                }

                return `
                <span 
                  class="badge ${badgeClass}" 
                  style="cursor:pointer"
                  onclick="ver_aplicaciones(${row.id})"
                  title="Ver aplicaciones"
                >
                  ${texto}
                </span>
              `;
              },
            },
            {
              data: "id",
              render: function (data, type, row) {
                if (row.TipoDeComprobante != "MOVIMIENTO INTERNO") {
                  if (row.Haber > 0) {
                    if (row.idNotifications == 0) {
                      return (
                        `<a onclick='ver_recibo_modal(${row.id})' title='Recibo'><i class='mdi mdi-18px mdi-alpha-r-circle text-success'></i></a>` +
                        `<a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></a>`
                      );
                    } else {
                      return (
                        `<a onclick='ver_recibo_modal(${row.id})' title='Recibo'><i class='mdi mdi-18px mdi-alpha-r-circle text-success mr-2'></i></a>` +
                        `<a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger mr-2'></i></a>`
                      );
                    }
                  } else {
                    if (row.idNotifications == 0) {
                      return (
                        `<a onclick='abrirModalFactura(${row.id})' title='Comprobante'><i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
                        `<a target='_blank' href='invoice_details.php?id=${row.id}' title='Detalle'><i class='mdi mdi-18px mdi-alpha-d-circle text-warning'></i></a>`
                      );
                    } else {
                      return (
                        `<a onclick='abrirModalFactura(${row.id})' title='Comprobante'><i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
                        `<a target='_blank' href='invoice_details.php?id=${row.id}' title='Detalle'><i class='mdi mdi-18px mdi-alpha-d-circle text-warning mr-2'></i></a>`
                      );
                    }
                  }
                } else {
                  return `<a onclick='eliminar_mvi(${row.id})' class='action-icon'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></a>`;
                }
              },
            },
          ],
        });

        //TABLA RELACIONES
        $("#relaciones_tabla").DataTable({
          dom: "Bfrtip",
          buttons: ["copy", "excel", "pdf"],
          paging: true,
          searching: true,
          ajax: {
            url: "../Clientes/Procesos/php/tablas.php",
            data: {
              Relaciones: 1,
              id: id,
            },
            type: "post",
          },
          columns: [
            {
              data: "idProveedor",
            },
            {
              data: "nombrecliente",
            },
            {
              data: "Direccion",
            },
            {
              data: "Celular",
            },
            {
              data: "AdminEnvios",
              render: function (data, type, row) {
                if (row.AdminEnvios === 0) {
                  return (
                    '<input type="checkbox" class="editor-active" data-id="' +
                    row.id +
                    '" />'
                  );
                }
                return (
                  '<input type="checkbox" class="editor-active" data-id="' +
                  row.id +
                  '" checked>'
                );
              },
              className: "dt-body-center",
            },
          ],
          select: {
            style: "os",
            selector: "td:not(:last-child)", // no row selection on last column
          },
          rowCallback: function (row, data) {
            // Set the checked state of the checkbox in the table
            $("input.editor-active", row).prop(
              "checked",
              data.AdminEnvios == 1,
            );
          },
        });
        $("#relaciones_tabla").on(
          "change",
          "input.editor-active",
          function (e) {
            e.preventDefault();
            var elemento = e.target;
            var dataID = elemento.getAttribute("data-id");
            if (elemento.checked) {
              var select = 1;
            } else {
              select = 0;
            }

            $.ajax({
              data: {
                AdminEnvios: 1,
                id: dataID,
                Select: select,
              },
              url: "Procesos/php/funciones.php",
              type: "post",
              success: function (response) {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1") {
                  toast("success", "Registro Actualizado !", "Se han realizado cambios.");
                } else {
                  toast("error", "Ocurrio un Error !", "No se realizaron cambios.");
                }
              },
            });
          },
        );
        //TABLA TARIFAS
        // .off() antes de .on(): este bloque corre en cada cambio de cliente (dentro
        // del success de Datos:1); sin esto, cada cambio apilaba un handler más sobre
        // el anterior, y al abrir la pestaña Tarifas se disparaban todos juntos, cada
        // uno con el id del cliente con el que se registró.
        $("#botontarifas")
          .off("click.tarifas")
          .on("click.tarifas", function () {
          //                   document.getElementById('agregar_botton').style.display='block';
          document.getElementById("guardar_botton").style.display = "none";
          document.getElementById("eliminar_botton").style.display = "none";

          document.getElementById("cargarpago_botton").style.display = "none";
          document.getElementById(
            "generar_comprobante_afip_button",
          ).style.display = "none";
          document.getElementById(
            "asociar_pago_comprobante_button",
          ).style.display = "none";

          document.getElementById("descuento_botton").style.display = "none";
          var table_tarifas = $("#tarifas_tabla").DataTable();
          table_tarifas.destroy();
          var datatable_tarifas = $("#tarifas_tabla").DataTable({
            //                   dom: 'Bfrtip',
            //                   buttons: [
            //                   'copy', 'excel', 'pdf'
            //                   ],
            paging: true,
            searching: true,
            ajax: {
              url: "../Clientes/Procesos/php/tablas.php",
              data: {
                Tarifas: 1,
                id: id,
              },
              type: "post",
            },
            columns: [
              {
                data: "Titulo",
              },
              {
                data: "MaxKm",
              },
              {
                data: "PrecioPlano",
                render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
              },
              {
                data: "id",
                render: function (data, type, row) {
                  return (
                    '<td> <a id="cleartarifa" value="' +
                    row.id +
                    '" class="action-icon"><i class="mdi mdi-delete"></i></a></td>'
                  );
                },
              },
            ],
          });
        });

        $("#tarifas_tabla")
          .off("click.tarifas", "a.action-icon")
          .on("click.tarifas", "a.action-icon", function (e) {
          var idClientesyServicios = e.currentTarget.attributes[1].value;
          $.ajax({
            data: {
              ClearTarifa: 1,
              id: idClientesyServicios,
            },
            url: "Procesos/php/funciones.php",
            type: "post",
            success: function (response1) {
              var jsonData = JSON.parse(response1);
              if (jsonData.success == "1") {
                toast("success", "Perfecto", "Pago registrado correctamente");
              } else {
                toast(
                  "error",
                  "Ocurrio un Error !",
                  "No se realizaron cambios.",
                );
              }
            },
          });
        });

        $("#telefono_contacto").html(" Telefono: " + jsonData.celular);
        $("#mail_contacto").html(" Mail: " + jsonData.Mail);
        $("#contacto_contacto").html(" Contacto: " + jsonData.contacto);

        // Si el usuario está parado en Contacto o Recorridos al cambiar de cliente,
        // esas tablas ya se destruyeron más arriba: hay que recargarlas ahora que
        // #codigo tiene el id nuevo, si no quedan en blanco hasta que se reabra la pestaña.
        if ($("#contact").hasClass("active")) {
          $("#perfil_conctact").trigger("click");
        }
        if ($("#recorridos").hasClass("active")) {
          $("#recorridos_boton").trigger("click");
        }
      } else {
      }
    },
  });

  $.ajax({
    data: {
      Tablero: 1,
      id: id,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response1) {
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      var jsonData = JSON.parse(response1);
      if (jsonData.success == "1") {
        var PromedioMensual = currencyFormat(Number(jsonData.PromedioMensual));
        $("#ventas_mes").html(PromedioMensual);

        var ComprasAno = currencyFormat(Number(jsonData.ComprasAno));
        $("#ventas_ano").html(ComprasAno);
        var ComprasAnoAntT = jsonData.ComprasAnoAntT.toFixed(2);
        $("#ventas_ano_ant").html(ComprasAnoAntT);

        //SALDO
        var Saldo = currencyFormat(Number(jsonData.Saldo));

        //ELIMINO ESTO PORQUE ME ESTABA TRAYENDO SOLO TRANS CLIENTES Y NO RECORRIDOS.
        $("#saldo").html(Saldo);

        // $("#total_saldo").html(Saldo);

        //FECHA
        if (jsonData.UltFacFecha) {
          var Fecha = jsonData.UltFacFecha.split("-").reverse().join(".");
          $("#fecha").html(Fecha);
        } else {
          $("#fecha").html("Sin fecha");
        }
        var Debe = currencyFormat(Number(jsonData.UltFacDebe));
        $("#debe").html(Debe);

        var PenUltFacDebe = jsonData.PenUltFacDebe;
        if (PenUltFacDebe > 0) {
          document.getElementById("tipo").className = "mdi mdi-arrow-up-bold";
        }
        $("#tipo").html(PenUltFacDebe.toFixed(2));

        $("#numero").html("Desde el Ult. Comp.");

        var PromedioMensualAnt = jsonData.PromedioMensualAnt;
        $("#ventas_mes_ant").html(PromedioMensualAnt.toFixed(2));

        if (jsonData.FechaUltPago) {
          var FechaUltPago = jsonData.FechaUltPago.split("-")
            .reverse()
            .join(".");
          $("#fecha_ult_pago").html("Últ. Pago el " + FechaUltPago);
        } else {
          $("#fecha_ult_pago").html("Sin Pagos");
        }

        var ComprasMes = currencyFormat(Number(jsonData.ComprasMesAnt));
        var UltPago = currencyFormat(Number(jsonData.UltPago));
        $("#importe_ult_pago").html(UltPago);

        if (jsonData.Saldo > 0) {
          document.getElementById("card_saldo").className =
            "card widget-flat bg-danger text-white";
        } else if (jsonData.Saldo == 0) {
          document.getElementById("card_saldo").className =
            "card widget-flat bg-success text-white";
        } else if (jsonData.Saldo < 0) {
          document.getElementById("card_saldo").className =
            "card widget-flat bg-warning text-white";
        }
      }
    },
  });

  $.ajax({
    data: {
      Usuario: 1,
      id: id,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    dataType: "json",

    success: function (jsonData) {
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      if (
        jsonData &&
        Array.isArray(jsonData.data) &&
        jsonData.data.length > 0 &&
        jsonData.data[0].Pass != null
      ) {
        $("#claveweb_label").val(jsonData.data[0].Pass);
      } else {
        $("#claveweb_label").val("");
        console.warn("No se encontraron datos de usuario:", jsonData);
      }
    },
  });

  $.ajax({
    data: {
      TotalRemitos: 1,
      id: id,
    },
    url: "../Clientes/Procesos/php/tablas.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      if (jsonData.success == 1) {
        var totalenviados = currencyFormat(Number(jsonData.totalenviados));

        var totalrecibidos = currencyFormat(Number(jsonData.totalrecibidos));

        $("#total_saldo").html(currencyFormat(Number(jsonData.saldo_total)));

        $("#totalenviados_label").html(totalenviados);

        if (jsonData.totalrecibidos) {
          $("#totalrecibidos_label").html(totalrecibidos);
        } else {
          $("#totalrecibidos_label").html(currencyFormat(Number(0)));
        }
      }
    },
  });

  //SELECT TIPO DE CONDICION DE IVA
  $.ajax({
    data: {
      TipoDeResponsable: 1,
      id: id,
    },
    type: "POST",
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      $(".selector-condicion select").html(response).fadeIn();
    },
  });

  //SELECT TIPO DE DOCUMENTO
  $.ajax({
    data: {
      TipoDeDocumento: 1,
      id: id,
    },
    type: "POST",
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      if (miClienteChangeSeq !== _clienteChangeSeq) return;
      $(".selector-tipodocumento select").html(response).fadeIn();
    },
  });
});

$("#claveweb_button").click(function () {
  $("#claveweb_button2").show();
  $("#claveweb_button").hide();
  $("#claveweb_label").prop("type", "text");
});
$("#claveweb_button2").click(function () {
  $("#claveweb_button").show();
  $("#claveweb_button2").hide();
  $("#claveweb_label").prop("type", "password");
});

$("#agregar_botton").click(function () {
  var razonsocial = document.getElementById("razonsocial").value;
  var dir = document.getElementById("direccion").value;
  var loc = document.getElementById("localidad").value;
  var prov = document.getElementById("provincia").value;
  var cp = document.getElementById("codigopostal").value;
  var tel = document.getElementById("telefono").value;
  var cel = document.getElementById("celular").value;
  var contacto = document.getElementById("contacto").value;
  var condicion = document.getElementById("condicion").value;
  var iva = document.getElementById("iva").value;
  var cuit = document.getElementById("cuit").value;
  var rubro = document.getElementById("rubro").value;
  var email = document.getElementById("email").value;
  var web = document.getElementById("web").value;
  var obs = document.getElementById("observaciones").value;
  var ib = document.getElementById("ingresosbrutos").value;
  var comb = document.getElementById("Retira").value;
  var vehi = document.getElementById("accesoweb").value;
  var ctaas = document.getElementById("relacionasignada").value;

  var dato = {
    Agregar: 1,
    razonsocial: razonsocial,
    dire: dir,
    loc: loc,
    prov: prov,
    cp: cp,
    tel: tel,
    cel: cel,
    contacto: contacto,
    condicion: condicion,
    iva: iva,
    cuit: cuit,
    rubro: rubro,
    email: email,
    web: web,
    obs: obs,
    ib: ib,
    comb: comb,
    vehi: vehi,
    ctaas: ctaas,
  };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toast("success", "Listo!", "Creamos el Proveedor");
      } else if (jsonData.success == "2") {
        toast("error", "Error!", "El Nombre, Direccion o Cuit, ya existen en el sistema");
      } else if (jsonData.success == "3") {
        toast("error", "Error!", "El nombre no puede ser NULL");
      }
    },
  });
});

$("#guardar_botton").click(function () {
  var id = document.getElementById("buscarcliente").value;
  var dir = document.getElementById("direccion").value;
  var piso = document.getElementById("pisodepto").value;
  var loc = document.getElementById("localidad").value;
  var prov = document.getElementById("provincia").value;
  var cp = document.getElementById("codigopostal").value;
  var tel = document.getElementById("telefono").value;
  var cel = document.getElementById("celular").value;
  var cel2 = document.getElementById("celular2").value;
  var contacto = document.getElementById("contacto").value;
  var condicion = document.getElementById("condicion").value;
  var cuit = document.getElementById("cuit").value;
  var rubro = document.getElementById("rubro").value;
  var email = document.getElementById("email").value;
  var web = document.getElementById("web").value;
  var obs = document.getElementById("observaciones").value;
  var horario = document.getElementById("horario_entrega_cliente").value;
  var retiro = document.getElementById("retira").value;
  //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var direccion_f = document.getElementById("direccion_facturacion").value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion",
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion",
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;
  var observaciones_f = document.getElementById(
    "observaciones_facturacion",
  ).value;

  var dato = {
    Actualizar: 1,
    id: id,
    dir: dir,
    piso: piso,
    loc: loc,
    prov: prov,
    cp: cp,
    tel: tel,
    cel: cel,
    cel2: cel2,
    contacto: contacto,
    condicion: condicion,
    cuit: cuit,
    rubro: rubro,
    email: email,
    web: web,
    obs: obs,
    horario: horario,
    retiro: retiro,
    razonsocial_f: razonsocial_f,
    direccion_f: direccion_f,
    condiva_f: condiva_f,
    tipodocumento_f: tipodocumento_f,
    documento_f: documento_f,
    cai_f: cai_f,
    observaciones_f: observaciones_f,
  };

  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        Swal.fire({
          title: "Listo!",
          text: "Datos Guardados",
          icon: "success",
          timer: 1500,
        });
        document.getElementById("nueva_condicion_facturacion").style.display =
          "none";
        document.getElementById("condicion_facturacion").style.display =
          "block";
        document.getElementById("condicion_facturacion").value = condiva_f;
      } else {
      }
    },
  });
});

//ELIMINAR CLIENTE
$("#eliminar_botton").click(function () {
  var id = document.getElementById("buscarcliente").value;

  console.log("id cliente eliminar botton", id);

  var total_saldo = $("#total_saldo").html();

  if (total_saldo == "$0.00") {
    $("#modal_eliminar_cliente").modal("show");
    $("#modal_eliminar_cliente_text").text(
      "Estas por eliminar el cliente " + id,
    );
  } else {
    toast("error", "Error !", "No se puede eliminar ya que el saldo es " + total_saldo);
  }
});

$("#modal_eliminar_cliente_aceptar").click(function () {
  var id = document.getElementById("codigo").value;

  $.ajax({
    data: {
      Eliminar_cliente: 1,
      id: id,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        $("#modal_eliminar_cliente").modal("hide");
        toast("error", "Exito !", "Se elimino el cliente " + id);

        setTimeout(function () {}, 3000);

        location.reload();
      }
    },
  });
});
function control_facturacion(id, el, ev) {
  if (ev) ev.stopPropagation();

  $.ajax({
    url: "Procesos/php/funciones.php",
    type: "POST",
    dataType: "json",
    data: {
      ControlFacturacion: 1,
      idTransClientes: id,
    },
    beforeSend: function () {
      $(el).css("pointer-events", "none").css("opacity", "0.6");
    },
    success: function (jsonData) {
      if (parseInt(jsonData.success, 10) === 1) {
        const estado = parseInt(jsonData.estado, 10);
        const $icon = $(el).find("i");

        if (estado === 1) {
          $icon
            .removeClass("mdi-check-circle-outline text-muted")
            .addClass("mdi-check-circle text-success");
        } else {
          $icon
            .removeClass("mdi-check-circle text-success")
            .addClass("mdi-check-circle-outline text-muted");
        }
      } else {
        toast("error", "Error", jsonData.error || "No se pudo actualizar.");
      }
    },
    error: function (xhr) {
      toast("error", "Error", xhr.responseText || "Error del servidor");
    },
    complete: function () {
      $(el).css("pointer-events", "").css("opacity", "");
    },
  });
}
//MODIFICAR EL SERVICIO DE SIMPLE A FLEX O VICEVERSA
function change_service(id) {
  $.ajax({
    data: {
      CambiarServicio: 1,
      idTransClientes: id,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var table = $("#facturacion_tabla").DataTable();
        table.ajax.reload(null, false);
      }
    },
  });
}

//FACTURACION TODOS
$("#botonfacturacion").click(function () {
  document.getElementById("guardar_botton").style.display = "none";
  document.getElementById("eliminar_botton").style.display = "none";
  document.getElementById("cargarpago_botton").style.display = "none";
  document.getElementById("generar_comprobante_afip_button").style.display =
    "none";
  document.getElementById("asociar_pago_comprobante_button").style.display =
    "none";

  var id = document.getElementById("codigo").value;

  $("#filtro").click(function () {
    var id = document.getElementById("codigo").value;
    var desde = $("#min").val();
    var hasta = $("#max").val();
    // console.log('desde',desde);
    // console.log('hasta',hasta);
    var datatable_facturacion = $("#facturacion_tabla").DataTable();
    datatable_facturacion.destroy();

    var table_facturacion_proforma_recorridos = $(
      "#tabla_facturacion_proforma_recorridos",
    ).DataTable();
    table_facturacion_proforma_recorridos.destroy();

    var datatable_facturacion = $("#facturacion_tabla").DataTable({
      dom: "Bfrtip",
      buttons: ["pageLength", "copy", "excel", "pdf"],
      paging: false,
      searching: true,
      footerCallback: function (row, data, start, end, display) {
        total = this.api()
          .column(5) //numero de columna a sumar
          //.column(1, {page: 'current'})//para sumar solo la pagina actual
          .data()
          .reduce(function (a, b) {
            return Number(a) + Number(b);
          }, 0);
        var saldo = currencyFormat(total);
        var sumadebe = currencyFormat(total);
        $(this.api().column(5).footer()).html(sumadebe);

        //             $('#saldo_ctacte').html(saldo);
      },
      ajax: {
        url: "../Clientes/Procesos/php/tablas.php",
        data: {
          Facturacion: 1,
          id: id,
          desde: desde,
          hasta: hasta,
        },
        type: "post",
      },
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            if (!data) return "";

            if (type === "sort" || type === "type") {
              return data;
            }

            var fechaHtml =
              '<span style="display: none;">' +
              data +
              "</span>" +
              data.split("-").reverse().join(".");

            if (row.Devuelto == 1) {
              fechaHtml +=
                '<br><span class="badge bg-danger rounded-pill">Devuelto</span>';
            }

            return fechaHtml;
          },
        },
        {
          data: "TipoDeComprobante",
          render: function (data, type, row) {
            if (row.Entregado == 1) {
              var entregado =
                '<span class="badge bg-success rounded-pill">Entregado</span>';
            } else {
              var entregado =
                '<span class="badge bg-danger rounded-pill">No Entregado</span>';
            }
            if (row.FormaDePago == "Origen") {
              return (
                '<span class="badge bg-secondary">' +
                row.TipoDeComprobante +
                " " +
                row.NumeroComprobante +
                "</span></br>" +
                '<span class="badge badge-outline-primary rounded-pill">' +
                row.FormaDePago +
                "</span> " +
                entregado
              );
            } else {
              return (
                '<span class="badge bg-secondary">' +
                row.TipoDeComprobante +
                " " +
                row.NumeroComprobante +
                "</span></br>" +
                '<span class="badge badge-outline-secondary rounded-pill">' +
                row.FormaDePago +
                "</span> " +
                entregado
              );
            }
          },
        },
        {
          data: "ClienteDestino",
          render: function (data, type, row) {
            if (row.Retirado == 0) {
              var color = "success";
            } else {
              color = "muted";
            }
            return (
              "<td><b>" +
              row.ClienteDestino +
              "</br>" +
              '<i class="mdi mdi-18px mdi-map-marker text-' +
              color +
              '"></i><a class="text-muted">' +
              row.DomicilioDestino +
              "</td>"
            );
          },
        },
        {
          data: "CodigoProveedor",
          render: function (data, type, row) {
            if (row.CodigoProveedor == "") {
              var dato = "S/D";
              var color = "muted";
            } else {
              var dato = row.CodigoProveedor;
            }
            //   console.log('HABER',getParameterByName('token'));

            //   if(getParameterByName('token')===null){
            return (
              '<td class="table-action">' +
              '<a style="cursor:pointer"  data-bs-toggle="modal" data-bs-target="#standard-modal-codcliente" data-id="' +
              row.CodigoSeguimiento +
              '"' +
              'data-title="' +
              dato +
              '" data-fieldname="' +
              data +
              '"><b class="text-' +
              color +
              '">' +
              dato +
              "</b></a></td>"
            );
          },
        },

        {
          data: "CodigoSeguimiento",

          render: function (data, type, row) {
            if (row.Flex == 1) {
              var servicio =
                '<span style="cursor:pointer" onclick="change_service(' +
                row.id +
                ')" class="badge bg-success">Flex</span>';
            } else {
              servicio =
                '<span style="cursor:pointer" onclick="change_service(' +
                row.id +
                ')" class="badge bg-warning text-white">Simple</span>';
            }
            // Cantidad de visitas (0, 1 o 2 - tope 2) calculada en el
            // backend sobre el historial de Seguimiento para este remito.
            if (row.CantidadVisitas > 1) {
              var visitas = "2 Visitas";
            } else if (row.CantidadVisitas == 1) {
              visitas = "1 Visita";
            } else {
              visitas = "";
            }
            return (
              '<td class="table-action">' +
              '<a style="cursor:pointer"  data-bs-toggle="modal" data-bs-target="#modal_seguimiento" data-id="' +
              row.CodigoSeguimiento +
              '"' +
              'data-title="' +
              data.ClienteDestino +
              '" data-fieldname="' +
              data +
              '"><b>' +
              row.CodigoSeguimiento +
              "</b></a><br>" +
              servicio +
              (visitas
                ? '<br><span class="badge bg-dark">' + visitas + "</span>"
                : "") +
              "</td>"
            );
          },
        },
        {
          data: "Debe",
          render: function (data, type, row) {
            var formatted = $.fn.dataTable.render
              .number(".", ",", 2, "$ ")
              .display(data);
            if (type !== "display" && type !== "filter") {
              return formatted;
            }
            // Alerta: 2 visitas pero solo 1 servicio facturable en Ventas
            // (calculado en el backend), y el cliente esta fuera de Capital
            // (CP 5000-5023) - suele indicar que falta cargar la segunda
            // visita antes de facturar.
            if (
              Number(row.AlertaActualizar) === 1 &&
              isOutOfCapitalRange(row.CodigoPostal)
            ) {
              var cpnum = getCPnum(row.CodigoPostal);
              var title = "Revisar: 2 visitas y 1 servicio en Ventas";
              if (cpnum !== null) title += " · CP " + cpnum + " fuera de 5000–5023";
              return (
                '<span class="text-danger fw-bold" title="' +
                title +
                '">' +
                formatted +
                "</span>"
              );
            }
            return formatted;
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return (
              '<td class="table-action">' +
              '<a data-id="' +
              row.id +
              '" data-bs-toggle="modal" data-bs-target="#standard-modal-modificar" class="action-icon">' +
              '<i class="mdi mdi-pencil text-warning"></i></a>' +
              '<a data-id="' +
              row.id +
              '" data-tabla="trans" data-title="' +
              row.CodigoSeguimiento +
              '" data-bs-toggle="modal" data-bs-target="#warning-modal" class="action-icon">' +
              '<i class="mdi mdi-trash-can text-danger"></i></a>' +
              '<a href="javascript:void(0)" onclick="control_facturacion(' +
              row.id +
              ', this, event)" class="action-icon" title="Control de facturación">' +
              '<i class="mdi ' +
              (parseInt(row.Control_facturacion, 10) === 1
                ? "mdi-check-circle text-success"
                : "mdi-check-circle-outline text-muted") +
              '"></i></a>' +
              "</td>"
            );
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return (
              '<div class="custom-control custom-checkbox custom-checkbox-success mb-1">' +
              '<input type="checkbox" value="' +
              row.id +
              '" class="custom-control-input" data-id="' +
              row.id +
              '" id="' +
              row.id +
              '" checked>' +
              '<label class="custom-control-label" for="' +
              row.id +
              '">&nbsp;</label>'
            );
          },
          className: "dt-body-center",
          // Sin esto, un click en el checkbox (dentro del <th> de esta
          // columna) tambien dispara el orden-por-columna nativo de
          // DataTables y reordena/"esconde" filas al tocar "marcar todos".
          orderable: false,
        },
      ],
      select: {
        style: "os",
        selector: "td:not(:last-child)", // no row selection on last column
      },
    });

    $("#recorridos_tabla").on(
      "change",
      'input[name="checkbox_r"]',
      function (e) {
        e.preventDefault();
        var elemento = e.target;
        var dataID = elemento.getAttribute("data-id");
        console.log("valor", elemento);
        if (elemento.checked) {
          var select = 1;
        } else {
          select = 0;
        }
      },
    );
  }); //filtro
});

$("#standard-modal-codcliente").on("show.bs.modal", function (e) {
  var triggerLink = $(e.relatedTarget);
  var cs = triggerLink[0].dataset["id"];
  var dato = triggerLink[0].dataset["title"];
  $("#cs_codigocliente").val(cs);
  $("#myCenterModalLabel_codcliente").html("MODIFICAR CODIGO CLIENTE # " + cs);
  if (dato == "S/D") {
    $("#codigocliente_t").prop("placeholder", "S/D");
  } else {
    $("#codigocliente_t").val(dato);
  }
});

$("#modificarcodigocliente_ok").click(function () {
  var codcliente = $("#codigocliente_t").val();
  var codigos = $("#cs_codigocliente").val();
  if (codcliente != "") {
    $.ajax({
      data: {
        CodigoCliente: 1,
        CS: codigos,
        Dato: codcliente,
      },
      url: "Procesos/php/funciones.php",
      type: "post",
      //         beforeSend: function(){
      //         $("#buscando").html("Buscando...");
      //         },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == 1) {
          toast("success", "Registro Actualizado !", "Se han realizado cambios.");
          $("#codigocliente_t").val("");
          var table = $("#facturacion_tabla").DataTable();
          table.ajax.reload();
          $("#standard-modal-codcliente").modal("hide");
        } else {
          toast("error", "Ocurrio un Error !", "No se realizaron cambios.");
        }
      },
    });
  }
});

//FACTURACION RECORRIDOS

$("#recorridos_boton").click(function () {
  var id = document.getElementById("codigo").value;
  console.log("id", id);
  var datatable_recorridos = $("#recorridos_tabla").DataTable();
  datatable_recorridos.destroy();

  var datatable_recorridos = $("#recorridos_tabla").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength", "copy", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(3) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return parseInt(a) + parseInt(b);
        }, 0);
      var saldo = currencyFormat(total);
      var sumadebe = currencyFormat(total);
      $(this.api().column(3).footer()).html(sumadebe);

      //             $('#saldo_ctacte').html(saldo);
    },
    ajax: {
      url: "../Clientes/Procesos/php/tablas.php",
      data: {
        FacturacionRecorridos: 1,
        id: id,
      },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          if (!row.Fecha) return "";

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
      },
      {
        data: "Observaciones",
      },
      {
        data: "Nombre",
        render: function (data, type, row) {
          return (
            `<td class="form-control">${row.Nombre}</td></br>` +
            `<label class="text-muted"" >${row.Hora} - ${row.HoraRetorno}</label>`
          );
        },
      },
      {
        data: "Debe",
        render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
      },
      {
        data: "id",
        render: function (data, type, row) {
          return `<td><a onclick='eliminar_elemento_rec(${row.id})' class='action-icon'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></a></td>`;
        },
      },

      {
        data: null,
        render: function (data, type, row) {
          return (
            '<div class="custom-control custom-checkbox">' +
            '<input type="checkbox" value="' +
            row.id +
            '" class="custom-control-input" data-id="' +
            row.id +
            '" id="' +
            row.id +
            '" name="checkbox_r"/>' +
            '<label class="custom-control-label" for="' +
            row.id +
            '">&nbsp;</label>' +
            "</div>"
          );
        },
        className: "dt-body-center",
      },
    ],

    select: {
      style: "os",
      selector: "td:not(:last-child)", // no row selection on last column
    },
  });

  $("#recorridos_tabla").on("change", 'input[name="checkbox_r"]', function (e) {
    e.preventDefault();
    var elemento = e.target;
    var dataID = elemento.getAttribute("data-id");
    // console.log('valor', elemento);
    if (elemento.checked) {
      var select = 1;
    } else {
      select = 0;
    }
  });
});

//ELIMINO EL REGISTRO DE CTAS CTES
function eliminar_elemento_rec(i) {
  // console.log(i);
  $("#ctasctes_warning-modal").modal("show");

  $("#ctasctes_warning-modal-ok").click(function () {
    $.ajax({
      data: {
        Eliminar_Recorridos_ctacte: 1,
        id: i,
      },
      url: "Procesos/php/recorridos.php",
      type: "post",
      beforeSend: function () {
        // $("#buscando").html("Buscando...");
        // alert('enviando...');
      },
      success: function (respuesta) {
        var jsonData = JSON.parse(respuesta);
        if (jsonData.success == 1) {
          var tabla_asignacion = $("#recorridos_tabla").DataTable();
          tabla_asignacion.ajax.reload(null, false);
          toast("success", "Exito !", "Registro Actualizado.");
        } else {
          toast("error", "Error !", "No pudimos cargar el registro.");
        }
        $("#ctasctes_warning-modal").modal("hide");
      },
    });
  });
}

// function eliminar_ctacte_rec(i){
// console.log('eliminaria',i);
// }
//   REMITOS RECIBIDOS
$("#guias_recibidas_boton").click(function () {
  var id = document.getElementById("codigo").value;
  //DESTRUIMOS LA TABLA FACTURACION
  var table_recibidas = $("#guias_recibidas_tabla").DataTable();
  table_recibidas.destroy();

  var datatable_recibidas = $("#guias_recibidas_tabla").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength", "copy", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(5) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
        }, 0);
      var saldo = currencyFormat(total);
      var sumadebe = currencyFormat(total);
      $(this.api().column(5).footer()).html(sumadebe);

      //             $('#saldo_ctacte').html(saldo);
    },
    ajax: {
      url: "../Clientes/Procesos/php/tablas.php",
      data: {
        Recibidas: 1,
        id: id,
      },
      type: "post",
    },

    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          if (!row.Fecha) return "";
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span class="d-print-none" style="display: none;">' +
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
          if (row.Entregado == 1) {
            var entregado =
              '<span class="badge bg-success rounded-pill">Entregado</span></h6>';
          } else {
            var entregado =
              '<span class="badge bg-danger rounded-pill">No Entregado</span></h6>';
          }

          if (row.Facturado == 1) {
            var facturado =
              row.Facturado + " " + row.ComprobanteF + " " + row.NumeroF;
            var facturado_color = "dark";
          } else {
            var facturado = "Sin Facturar";
            var facturado_color = "warning";
          }

          if (row.FormaDePago == "Origen") {
            return (
              '<h6><span class="badge bg-secondary mb-1">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-primary rounded-pill">' +
              row.FormaDePago +
              "</span> " +
              entregado +
              '<h6><span class="badge badge-' +
              facturado_color +
              '">' +
              facturado +
              "</span></h6>"
            );
          } else {
            return (
              '<h6><span class="badge bg-secondary">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-secondary rounded-pill">' +
              row.FormaDePago +
              "</span> " +
              entregado +
              '<h6><span class="badge badge-' +
              facturado_color +
              '">' +
              facturado +
              "</span></h6>"
            );
          }
        },
      },
      {
        data: "RazonSocial",
      },
      {
        data: "DomicilioOrigen",
      },
      {
        data: "Observaciones",
        render: function (data, type, row) {
          if (row.Observaciones != null) {
            var Observaciones_substr = row.Observaciones.substr(0, 20);
          } else {
            Observacioens_substr = "";
          }
          return (
            '<a id="seguimiento_modal" data-id=' +
            row.CodigoSeguimiento +
            ">" +
            Observaciones_substr +
            "</a>"
          );
        },
      },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer"  data-bs-toggle="modal" data-bs-target="#modal_seguimiento" data-id="' +
            row.CodigoSeguimiento +
            '"' +
            'data-title="' +
            data.ClienteDestino +
            '" data-fieldname="' +
            data +
            '"><b>' +
            row.CodigoSeguimiento +
            "</b></a></td>"
          );
        },
      },
      {
        data: "Debe",
        render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
      },
    ],
  });
});
//   REMITOS ENVIADOS
$("#guias_enviadas_boton").click(function () {
  var id = document.getElementById("codigo").value;

  //DESTRUIMOS LA TABLA REMITOS ENVIADOS
  var table_enviadas = $("#guias_enviadas_tabla").DataTable();
  table_enviadas.destroy();

  var datatable_enviadas = $("#guias_enviadas_tabla").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength", "copy", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    footerCallback: function (row, data, start, end, display) {
      total = this.api()
        .column(5) //numero de columna a sumar
        //.column(1, {page: 'current'})//para sumar solo la pagina actual
        .data()
        .reduce(function (a, b) {
          return Number(a) + Number(b);
        }, 0);
      var saldo = currencyFormat(total);
      var sumadebe = currencyFormat(total);
      $(this.api().column(5).footer()).html(sumadebe);
    },
    ajax: {
      url: "../Clientes/Procesos/php/tablas.php",
      data: {
        Enviadas: 1,
        id: id,
      },
      type: "post",
    },

    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          if (!row.Fecha) return "";
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span class="d-print-none" style="display: none;">' +
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
          if (row.Entregado == 1) {
            var entregado =
              '<span class="badge bg-success rounded-pill">Entregado</span></h6>';
          } else {
            var entregado =
              '<span class="badge bg-danger rounded-pill">No Entregado</span></h6>';
          }

          if (row.Facturado == 1) {
            var facturado = row.ComprobanteF + " " + row.NumeroF;
            var facturado_color = "dark";
          } else {
            var facturado = "Sin Facturar";
            var facturado_color = "warning";
          }

          if (row.FormaDePago == "Origen") {
            return (
              '<h6><span class="badge bg-secondary text-light mb-1">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-primary rounded-pill">' +
              row.FormaDePago +
              "</span> " +
              entregado +
              '<h6><span class="badge badge-' +
              facturado_color +
              '">' +
              facturado +
              "</span></h6>"
            );
          } else {
            return (
              '<span class="badge badge-outline-warning rounded-pill">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-secondary rounded-pill">' +
              row.FormaDePago +
              "</span> " +
              entregado +
              '<h6><span class="badge badge-' +
              facturado_color +
              '">' +
              facturado +
              "</span></h6"
            );
          }
        },
      },
      {
        data: "ClienteDestino",
      },
      {
        data: "DomicilioDestino",
      },
      {
        data: "Observaciones",
        render: function (data, type, row) {
          if (row.Observaciones != null) {
            var Observaciones_substr = row.Observaciones.substr(0, 20);
          } else {
            Observacioens_substr = "";
          }
          return (
            '<a id="seguimiento_modal" data-id=' +
            row.CodigoSeguimiento +
            ">" +
            Observaciones_substr +
            "</a>"
          );
        },
      },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer" class="text-primary" data-bs-toggle="modal" data-bs-target="#modal_seguimiento" data-id="' +
            row.CodigoSeguimiento +
            '"' +
            'data-title="' +
            row.ClienteDestino +
            '" data-fieldname="' +
            data +
            '"><b>' +
            row.CodigoSeguimiento +
            "</b></a></td>"
          );
        },
      },
      {
        data: "Debe",
        render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
      },
    ],
  });
});

//BOTONES
$("#botondatos").click(function () {
  document.getElementById("guardar_botton").style.display = "inline";
  document.getElementById("eliminar_botton").style.display = "inline";
  document.getElementById("cargarpago_botton").style.display = "none";
  document.getElementById("generar_comprobante_afip_button").style.display =
    "none";
  document.getElementById("asociar_pago_comprobante_button").style.display =
    "none";
  document.getElementById("debitocredito_botton").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
});
$("#botontablero").click(function () {
  document.getElementById("guardar_botton").style.display = "none";
  document.getElementById("eliminar_botton").style.display = "none";
  document.getElementById("cargarpago_botton").style.display = "none";
  document.getElementById("generar_comprobante_afip_button").style.display =
    "none";
  document.getElementById("asociar_pago_comprobante_button").style.display =
    "none";
  document.getElementById("debitocredito_botton").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
});

$("#botoncta").click(function () {
  document.getElementById("guardar_botton").style.display = "none";
  document.getElementById("eliminar_botton").style.display = "none";
  document.getElementById("cargarpago_botton").style.display = "inline";
  document.getElementById("generar_comprobante_afip_button").style.display =
    "inline";
  document.getElementById("asociar_pago_comprobante_button").style.display =
    "inline";
  document.getElementById("debitocredito_botton").style.display = "inline";
  document.getElementById("descuento_botton").style.display = "none";
});

$("#botonrelacion").click(function () {
  document.getElementById("guardar_botton").style.display = "none";
  document.getElementById("eliminar_botton").style.display = "none";
  document.getElementById("cargarpago_botton").style.display = "none";
  document.getElementById("generar_comprobante_afip_button").style.display =
    "none";
  document.getElementById("asociar_pago_comprobante_button").style.display =
    "none";
  document.getElementById("debitocredito_botton").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  var id = document.getElementById("codigo").value;
  $.ajax({
    data: {
      AdminEnvios: 1,
      id: id,
    },
    url: "Procesos/php/tablas.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#admin_envios").html(jsonData.data);
      }
    },
  });
});

//GENERAR COMPROBANTE NC/ND AFIP
// Reconstruye las opciones de "Tipo de Comprobante a Generar" segun la letra
// (A o B) que corresponda. OJO: Select2 no filtra options con el atributo
// "hidden" (eso solo tapa la opcion nativa, no la de la lista de Select2),
// asi que hay que sacar/poner las <option> del DOM realmente para que dejen
// de listarse.
function renderTipoNcNd(esFacturaA) {
  var $tipo = $("#ncnd_comprobante_tipo");
  var valorPrevio = $tipo.val();

  $tipo.empty().append('<option value="">Seleccione una Opción</option>');

  if (esFacturaA) {
    $tipo.append(
      '<optgroup label="Responsable Inscripto (A)">' +
        '<option value="3">NOTA DE CRÉDITO A</option>' +
        '<option value="2">NOTA DE DÉBITO A</option>' +
        "</optgroup>",
    );
  } else {
    $tipo.append(
      '<optgroup label="Consumidor Final / Monotributo (B)">' +
        '<option value="8">NOTA DE CRÉDITO B</option>' +
        '<option value="7">NOTA DE DÉBITO B</option>' +
        "</optgroup>",
    );
  }

  if (valorPrevio && $tipo.find('option[value="' + valorPrevio + '"]').length) {
    $tipo.val(valorPrevio).trigger("change");
  } else {
    $tipo.val("").trigger("change");
  }
}

// Servicios de la factura elegida en "Comprobante a Corregir", para el picker
// de "Servicios específicos" de la NC. Se recarga cada vez que cambia el
// comprobante o el tipo, así siempre refleja lo que sigue vinculado hoy
// (si ya se liberó algo con una NC previa, no vuelve a aparecer).
var ncndServiciosDisponibles = [];
// Ids tildados (como string) - aparte del array de arriba porque la tabla
// pagina, y el checkbox de una fila que no está en la página actual no
// existe en el DOM (DataTables solo renderiza la página visible).
var ncndServiciosSeleccionados = new Set();

function esNotaDeCredito() {
  var v = $("#ncnd_comprobante_tipo").val();
  return v === "3" || v === "8";
}

// Alcance elegido en el modal, o "ninguno" (Ajuste manual) si no aplica -
// factura_afip.js usa esto para armar el payload que se manda a facturar.php.
function obtenerAlcanceNc() {
  if (!esNotaDeCredito()) return "ninguno";
  return $('input[name="ncnd_alcance"]:checked').val() || "ninguno";
}

// Ids de TransClientes a liberar segun el alcance elegido - factura_afip.js
// los manda como 'servicios_liberar[]' cuando el alcance es "parcial".
function obtenerServiciosALiberar() {
  var alcance = obtenerAlcanceNc();
  if (alcance === "completa") {
    return ncndServiciosDisponibles.map(function (s) {
      return s.id;
    });
  }
  if (alcance === "parcial") {
    return Array.from(ncndServiciosSeleccionados);
  }
  return [];
}

function aplicarImporteNc(importe) {
  importe = Number(importe) || 0;
  var neto = importe / 1.21;
  var iva = importe - neto;
  $("#ncnd_neto").val(neto.toFixed(2));
  $("#ncnd_iva").val(iva.toFixed(2));
  $("#ncnd_total").val(importe.toFixed(2));
}

function recalcularImporteNcPorServiciosTildados() {
  var total = 0;
  ncndServiciosDisponibles.forEach(function (s) {
    if (ncndServiciosSeleccionados.has(String(s.id))) {
      total += Number(s.Debe);
    }
  });
  aplicarImporteNc(total);
}

// Pinta el picker de servicios (si corresponde) y recalcula Neto/IVA/Total
// segun el alcance elegido (completa = todos, parcial = los tildados).
function renderServiciosNcYRecalcular() {
  var alcance = $('input[name="ncnd_alcance"]:checked').val();

  if (alcance === "parcial") {
    $("#ncnd_servicios_row").show();

    if ($.fn.DataTable.isDataTable("#tabla_ncnd_servicios")) {
      $("#tabla_ncnd_servicios").DataTable().destroy();
      $("#tabla_ncnd_servicios tbody").empty();
    }

    $("#ncnd_servicios_check_all").prop("checked", false);

    $("#tabla_ncnd_servicios").DataTable({
      data: ncndServiciosDisponibles,
      pageLength: 10,
      lengthChange: false,
      language: { emptyTable: "No hay servicios disponibles para liberar." },
      columns: [
        {
          data: "id",
          orderable: false,
          className: "text-center",
          render: function (id) {
            var marcado = ncndServiciosSeleccionados.has(String(id)) ? "checked" : "";
            return '<input type="checkbox" class="ncnd-servicio-check" value="' + id + '" ' + marcado + ">";
          },
        },
        {
          data: "Fecha",
          render: function (data) {
            return data ? data.split("-").reverse().join("/") : "";
          },
        },
        { data: "CodigoSeguimiento", defaultContent: "" },
        { data: "ClienteDestino", defaultContent: "" },
        {
          data: "Debe",
          className: "text-end",
          render: function (data) {
            return currencyFormat(data);
          },
        },
      ],
    });

    $("#ncnd_neto, #ncnd_iva, #ncnd_total").prop("readonly", true);
    recalcularImporteNcPorServiciosTildados();
  } else if (alcance === "completa") {
    $("#ncnd_servicios_row").hide();
    $("#ncnd_neto, #ncnd_iva, #ncnd_total").prop("readonly", true);
    var total = ncndServiciosDisponibles.reduce(function (acc, s) {
      return acc + Number(s.Debe);
    }, 0);
    aplicarImporteNc(total);
  } else {
    $("#ncnd_servicios_row").hide();
    $("#ncnd_neto, #ncnd_iva, #ncnd_total").prop("readonly", false);
  }
}

// Muestra/oculta el bloque de alcance (solo tiene sentido para Notas de
// Crédito, con un comprobante ya elegido).
function actualizarAlcanceNcVisibilidad() {
  var idFacturado = $("#ncnd_comprobante_asociado").val();
  if (esNotaDeCredito() && idFacturado) {
    $("#ncnd_alcance_row").show();
  } else {
    $("#ncnd_alcance_row, #ncnd_servicios_row").hide();
    $("#ncnd_neto, #ncnd_iva, #ncnd_total").prop("readonly", false);
  }
}

// Trae los servicios todavia vinculados a la factura elegida y habilita o
// no las opciones de liberacion segun corresponda (facturas x Recorrido, o
// sin servicios individuales, fuerzan Ajuste manual).
function cargarServiciosNcFactura(idFacturado) {
  ncndServiciosDisponibles = [];
  ncndServiciosSeleccionados = new Set();

  $.ajax({
    data: { ServiciosFactura: 1, idFacturado: idFacturado },
    url: "Procesos/php/tablas.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      ncndServiciosDisponibles = jsonData.data || [];
      var esRecorrido = !!jsonData.esRecorrido;

      if (esRecorrido || !ncndServiciosDisponibles.length) {
        $("#ncnd_alcance_completa, #ncnd_alcance_parcial").prop("disabled", true);
        $("#ncnd_alcance_manual").prop("checked", true).prop("disabled", false);
        $("#ncnd_alcance_aviso")
          .text(
            esRecorrido
              ? "Esta factura fue generada por Recorrido - la liberación automática de servicios no está soportada, use Ajuste Manual."
              : "Esta factura no tiene servicios individuales vinculados - use Ajuste Manual.",
          )
          .show();
      } else {
        $("#ncnd_alcance_completa, #ncnd_alcance_parcial").prop("disabled", false);
        $("#ncnd_alcance_aviso").hide();
      }

      renderServiciosNcYRecalcular();
    },
    error: function () {
      toast("error", "Error", "No se pudo cargar la lista de servicios.");
    },
  });
}

$('input[name="ncnd_alcance"]').on("change", function () {
  renderServiciosNcYRecalcular();
});

// Delegado porque DataTables regenera las filas al cambiar de página - el
// checkbox de una fila puntual no existe en el DOM hasta que se dibuja.
$("#tabla_ncnd_servicios").on("change", ".ncnd-servicio-check", function () {
  var id = String($(this).val());
  if (this.checked) {
    ncndServiciosSeleccionados.add(id);
  } else {
    ncndServiciosSeleccionados.delete(id);
  }
  recalcularImporteNcPorServiciosTildados();
});

// "Marcar todos" tilda/destilda los servicios de TODAS las páginas, no solo
// la visible - por eso actualiza el Set directo y fuerza un redraw.
$("#ncnd_servicios_check_all").on("change", function () {
  var marcar = this.checked;
  ncndServiciosDisponibles.forEach(function (s) {
    if (marcar) {
      ncndServiciosSeleccionados.add(String(s.id));
    } else {
      ncndServiciosSeleccionados.delete(String(s.id));
    }
  });
  if ($.fn.DataTable.isDataTable("#tabla_ncnd_servicios")) {
    $("#tabla_ncnd_servicios").DataTable().rows().invalidate().draw(false);
  }
  recalcularImporteNcPorServiciosTildados();
});

$("#ncnd_comprobante_tipo").on("change", function () {
  actualizarAlcanceNcVisibilidad();

  var idFacturado = $("#ncnd_comprobante_asociado").val();
  if (esNotaDeCredito() && idFacturado) {
    cargarServiciosNcFactura(idFacturado);
  } else if (!esNotaDeCredito()) {
    var importe = Number($("#ncnd_comprobante_asociado").find(":selected").attr("data-importe"));
    if (importe) {
      aplicarImporteNc(importe);
    }
  }
});

// El modal #ncnd-modal se abre solo (data-bs-toggle/data-bs-target en el boton).
// Al abrirse, reseteamos el formulario y cargamos los comprobantes fiscales
// del cliente para elegir contra cual se emite la Nota de Crédito/Débito.
$("#ncnd-modal").on("show.bs.modal", function () {
  // Si el modal de Facturación (selección de remitos) sigue abierto, lo cerramos:
  // la NC/ND es un ajuste manual independiente y no debe quedar apilada arriba de él.
  if ($("#Facturacion_recorridos_modal").hasClass("show")) {
    $("#Facturacion_recorridos_modal").modal("hide");
  }

  var id = document.getElementById("buscarcliente").value;

  // Restringe el Tipo de Comprobante a Generar segun la condicion fiscal del
  // cliente (misma regla que se usa para elegir FACTURAS A vs B al facturar):
  // condicion 1 = Responsable Inscripto -> A, cualquier otro valor -> B.
  var condivaValue = document.getElementById("nueva_condicion_facturacion").value;
  if (condivaValue === "") {
    condivaValue = document.getElementById("condicion_facturacion").value;
  }
  var esFacturaA = condivaValue == 1;
  renderTipoNcNd(esFacturaA);

  $("#ncnd_fecha").val(new Date().toISOString().slice(0, 10));
  $("#ncnd_neto, #ncnd_iva, #ncnd_total").val("").prop("readonly", false);
  $("#ncnd_observaciones").val("");

  ncndServiciosDisponibles = [];
  ncndServiciosSeleccionados = new Set();
  $("#ncnd_alcance_completa").prop("checked", true);
  $("#ncnd_alcance_completa, #ncnd_alcance_parcial").prop("disabled", false);
  $("#ncnd_servicios_check_all").prop("checked", false);
  $("#ncnd_alcance_aviso").hide();
  $("#ncnd_alcance_row, #ncnd_servicios_row").hide();
  if ($.fn.DataTable.isDataTable("#tabla_ncnd_servicios")) {
    $("#tabla_ncnd_servicios").DataTable().destroy();
    $("#tabla_ncnd_servicios tbody").empty();
  }

  var $select = $("#ncnd_comprobante_asociado");
  $select.empty().append('<option value="">Cargando comprobantes...</option>');

  $.ajax({
    data: {
      FacturasCliente: 1,
      id: id,
    },
    url: "Procesos/php/tablas.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $select.empty();
      if (!jsonData.data || jsonData.data.length === 0) {
        $select.append(
          '<option value="">Este cliente no tiene comprobantes fiscales emitidos</option>',
        );
        return;
      }
      $select.append(
        '<option value="">Seleccione el comprobante emitido que quiere corregir</option>',
      );
      jsonData.data.forEach(function (row) {
        var partes = (row.NumeroFactura || "").split("-");
        var ptovta = partes.length > 1 ? parseInt(partes[0], 10) : 2;
        var nro = partes.length > 1 ? parseInt(partes[1], 10) : parseInt(partes[0], 10);
        var tipoN = row.TipoDeComprobante === "FACTURAS A" ? "1" : "6";
        var fechaFmt = row.Fecha ? row.Fecha.split("-").reverse().join("/") : "";
        var label =
          row.TipoDeComprobante +
          " " +
          row.NumeroFactura +
          " - " +
          fechaFmt +
          " - " +
          currencyFormat(row.Debe);

        $("<option>")
          .val(row.id)
          .text(label)
          .attr("data-tipo-n", tipoN)
          .attr("data-ptovta", ptovta)
          .attr("data-nro", nro)
          .attr("data-importe", row.Debe)
          .appendTo($select);
      });
    },
    error: function () {
      $select.empty().append('<option value="">No se pudo cargar la lista de comprobantes</option>');
    },
  });
});

// Autocompleta neto/iva/total (21%) al elegir el comprobante asociado, editable despues.
// Ademas, una NC/ND solo puede emitirse con la misma letra que el comprobante que corrige
// (AFIP no permite mezclar NC/ND A contra Factura B ni viceversa), asi que se re-restringe
// el Tipo de Comprobante a Generar segun la letra real del comprobante elegido.
$("#ncnd_comprobante_asociado").on("change", function () {
  var $selected = $(this).find(":selected");
  var tipoN = $selected.attr("data-tipo-n");

  if (tipoN) {
    renderTipoNcNd(tipoN == "1");
  }

  actualizarAlcanceNcVisibilidad();

  var idFacturado = $(this).val();
  if (esNotaDeCredito() && idFacturado) {
    cargarServiciosNcFactura(idFacturado);
    return;
  }

  // Nota de Débito (o nada elegido todavia): importe fijo de la factura, como antes.
  ncndServiciosDisponibles = [];
  var importe = Number($selected.attr("data-importe"));
  if (!importe) return;
  aplicarImporteNc(importe);
});

//CARGAR PAGO
$("#cargarpago_botton").click(function () {
  // document.getElementById('form_pago').style.display="block";
  //SELECT TIPO DE CONDICION DE IVA
  $.ajax({
    data: {
      FormaDePago: 1,
    },
    type: "POST",
    url: "../Funciones/php/tablas.php",

    success: function (response) {
      $(".selector-formadepago select")
        .html(response)
        .val("")
        .trigger("change")
        .fadeIn();
    },
  });
});

$("#debitocredito_botton").click(function () {
  $("#modal_movimientos_internos").modal("show");
});

$("#modal_movimientos_internos").on("show.bs.modal", function (e) {
  $("#form_mi")[0].reset();
  $("#fecha_movimientos_internos").val(hoyISO());
  //BUSCO EL ULTIMO COMPROBANTE DE MOVIMIENTOS INTERNOS
  $.ajax({
    data: {
      MovimientosInternos: 1,
    },
    type: "POST",
    url: "../Clientes/Procesos/php/movimientos_internos.php",
    dataType: "json",
    success: function (jsonData) {
      $("#comprobante_movimientos_internos").val(jsonData.dato);
      var table = $("#basic").DataTable();
      table.ajax.reload(null, false);
    },
  });
});

//MOVIMIENTOS INTERNOS

$("#confirmar_movimientos_internos_botton").click(function () {
  //AGREGAR MOVIMIENTO EN MOVIMIENTOS INTERNOS
  var imp = $("#importe_movimientos_internos").val();
  var obs = $("#obs_movimientos_internos").val();
  var id = $("#codigo").val();
  var cuit = $("#cuit_facturacion").val();
  var razonsocial = $("#razonsocial").val();
  var fecha = $("#fecha_movimientos_internos").val();
  $.ajax({
    data: {
      MovimientosInternos_agregar: 1,
      importe: imp,
      obs: obs,
      id_cliente: id,
      Cuit: cuit,
      RazonSocial: razonsocial,
      Fecha: fecha,
      Obs: obs,
    },
    type: "POST",
    url: "../Clientes/Procesos/php/movimientos_internos.php",
    dataType: "json",
    success: function (jsonData) {
      if (jsonData.success == 1) {
        //ACTUALIZO LOS TOTALES
        actualizar_totales(id);

        var table = $("#basic").DataTable();
        table.ajax.reload();
        $("#modal_movimientos_internos").modal("hide");
      }
    },
  });
});

// FACTURAR REMITOS

// Selección capturada al apretar "Facturar" / "Facturar Recorridos".
// Se reutiliza tal cual hasta el confirmar final: NO se vuelve a escanear
// el DOM en ese momento, para que un redibujado de la tabla en el medio
// (filtro, reload, etc.) no pueda desincronizar el total ya calculado de
// los remitos que realmente se adjuntan a la factura.
var remitosSeleccionadosFacturar = [];
var recorridosSeleccionadosFacturar = [];

// Checkbox "marcar todos" del header de #facturacion_tabla. Delegado en
// document porque las filas (tbody) se reemplazan en cada ajax.reload().
$(document).on("change", "#customCheck1", function () {
  var marcarTodos = $(this).prop("checked");
  $("#facturacion_tabla tbody input.custom-control-input").prop(
    "checked",
    marcarTodos,
  );
});

$("#facturar_boton").click(function () {
  var id = document.getElementById("buscarcliente").value;
  document.getElementById("Facturacion_recorridos_button").style.display =
    "none";
  //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  var table = $("#tabla_facturacion_proforma").DataTable();
  table.destroy();

  document.getElementById("nota_proforma").style.display = "flex";

  //Creamos un array que almacenará los valores de los input "checked"
  remitosSeleccionadosFacturar = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      remitosSeleccionadosFacturar.push($(this).attr("value"));
    }
  });
  var checked = remitosSeleccionadosFacturar;
  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  if (checked != 0) {
    document.getElementById("descuento_botton").style.display = "flex";
    var dato = {
      Datos: 1,
      id: id,
    };
    $.ajax({
      data: dato,
      url: "Procesos/php/funciones.php",
      type: "post",
      //         beforeSend: function(){
      //         $("#buscando").html("Buscando...");
      //         },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          document.getElementById("factura_primerpaso").style.display = "none";
          document.getElementById("factura_proforma").style.display = "block";
          document.getElementById("row_tabla_facturacion").style.display =
            "block";
          document.getElementById("row_tabla_recorridos").style.display =
            "none";

          $("#factura_titulo").html("FACTURA PROFORMA");
          $("#NumeroComprobante").html(jsonData.NextProforma);
          $("#factura_titulo2").html("FACTURA PROFORMA");
          $("#factura_codigo").html(jsonData.id);
          $("#factura_razonsocial").html(jsonData.RazonSocial);
          $("#factura_direccion").html(jsonData.direccion);
          $("#factura_localidad").html(jsonData.localidad);
          $("#factura_provincia").html(jsonData.provincia);
          $("#factura_celular").html(jsonData.celular);
          $("#factura_cuit").html(jsonData.Cuit);
          $("#factura_condicion").html(jsonData.Condicion);
          $("#factura_email").html(jsonData.Mail);
          $("#factura_ingresosbrutos").html(jsonData.IngresosBrutos);
        }
      },
    });

    //TABLA FACTURACION PROFORMA REMITOS
    var datatable_facturacion = $("#tabla_facturacion_proforma").DataTable({
      paging: false,
      searching: false,
      footerCallback: function (row, data, start, end, display) {
        total = this.api()
          .column(5, {
            page: "current",
          }) //numero de columna a sumar
          //.column(1, {page: 'current'})//para sumar solo la pagina actual
          .data()
          .reduce(function (a, b) {
            return Number(a) + Number(b);
          }, 0);
        var saldo = currencyFormat(total);
        var sumadebe = currencyFormat(total / 1.21);
        $(this.api().column(5).footer()).html(sumadebe);

        //TOTALES
        var neto = total / 1.21;
        var iva = Number(total - neto);
        $("#factura_neto").html(currencyFormat(neto));
        $("#factura_iva").html(currencyFormat(iva));
        $("#factura_total").html(saldo);
        $("#factura_neto_f").val(neto.toFixed(2));
        $("#factura_iva_f").val(iva.toFixed(2));
        $("#factura_total_f").val(total.toFixed(2));

        //INGRESO DATOS EN CUADRO FINAL DE FACTURACION
        $("#neto_up").val(neto.toFixed(2));
        $("#iva_up").val(iva.toFixed(2));
        $("#total_up").val(total.toFixed(2));
      },
      ajax: {
        url: "../Clientes/Procesos/php/tablas.php",
        data: {
          FacturacionProforma: 1,
          id: id,
          Remitos: checked,
        },
        type: "post",
      },
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            if (!row.Fecha) return "";
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
          data: "CodigoSeguimiento",
        },
        {
          data: "TipoDeComprobante",
          render: function (data, type, row) {
            return row.TipoDeComprobante + " " + row.NumeroComprobante;
          },
        },
        {
          data: "ClienteDestino",
        },
        {
          data: "CodigoProveedor",
        },
        {
          data: "Debe",
          render: function (data, type, row) {
            var DebeNeto = row.Debe / 1.21;
            return currencyFormat(DebeNeto);
          },
        },
      ],
    });

    //FECHAS
    $.ajax({
      data: {
        Fechas: 1,
        Remitos: checked,
      },
      url: "Procesos/php/funciones.php",
      type: "post",
      //         beforeSend: function(){
      //         $("#buscando").html("Buscando...");
      //         },
      success: function (response) {
        var jsonData = JSON.parse(response);
        $("#desde_f").html(jsonData.Desde);
        $("#hasta_f").html(jsonData.Hasta);
      },
    });
  } else {
    toast("error", "Error !", "No hay Remitos Seleccionados. No se puede avanzar.");
  }
});

// FACTURAR REMITOS DETALLE

$("#facturar_detalle_boton").click(function () {
  $.ajax({
    data: { Empresa: 1 },
    url: "../../Funciones/php/datosempresa.php",
    type: "post",
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#Emp_RazonSocial_detalle").html(jsonData.data[0].RazonSocial);
      $("#Emp_NombreComercial_detalle").html(jsonData.data[0].NombreComercial);
      $("#Emp_Direccion_detalle").html(jsonData.data[0].Direccion);
      $("#Emp_Cuit_detalle").html(jsonData.data[0].Cuit);
      $("#Emp_Telefono_detalle").html(jsonData.data[0].Telefono);
      $("#Emp_Mail_detalle").html(jsonData.data[0].Mail);
      $("#Emp_Web_detalle").html(jsonData.data[0].Web);
      $("#Emp_IngresosBrutos_detalle").html(jsonData.data[0].IngresosBrutos);
      $("#Emp_InicioActividades_detalle").html(
        jsonData.data[0].InicioActividades,
      );
    },
  });

  var id = document.getElementById("buscarcliente").value;
  document.getElementById("Facturacion_recorridos_button").style.display =
    "none";
  //DESTRUIMOS LA TABLA FACTURACION PROFORMA DETALLE
  var table = $("#tabla_facturacion_proforma_detalle").DataTable();
  table.destroy();

  document.getElementById("nota_proforma").style.display = "flex";

  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
    }
  });
  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  if (checked != 0) {
    // Antes se ocultaba al entrar al Detalle - Administración pedía
    // aplicar el descuento revisando el detalle línea por línea y el botón
    // desaparecía justo ahí, así que ahora se mantiene visible.
    document.getElementById("descuento_botton").style.display = "flex";
    var dato = {
      Datos: 1,
      id: id,
    };
    $.ajax({
      data: dato,
      url: "Procesos/php/funciones.php",
      type: "post",
      //         beforeSend: function(){
      //         $("#buscando").html("Buscando...");
      //         },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          document.getElementById("factura_primerpaso").style.display = "none";
          document.getElementById("factura_proforma_detalle").style.display =
            "block";

          document.getElementById(
            "row_tabla_facturacion_detalle",
          ).style.display = "block";
          document.getElementById("row_tabla_recorridos").style.display =
            "none";

          $("#factura_detalle_titulo").html("DETALLE FACTURA PROFORMA");
          $("#NumeroComprobante_detalle").html(jsonData.NextProforma);
          $("#factura_detalle_titulo2").html("DETALLE FACTURA PROFORMA");
          $("#factura_codigo_detalle").html(jsonData.id);
          $("#factura_razonsocial_detalle").html(jsonData.RazonSocial);
          $("#factura_direccion_detalle").html(jsonData.direccion);
          $("#factura_localidad_detalle").html(jsonData.localidad);
          $("#factura_provincia_detalle").html(jsonData.provincia);
          $("#factura_celular_detalle").html(jsonData.celular);
          $("#factura_cuit_detalle").html(jsonData.Cuit);
          $("#factura_condicion_detalle").html(jsonData.Condicion);
          $("#factura_email_detalle").html(jsonData.Mail);
          $("#factura_ingresosbrutos_detalle").html(jsonData.IngresosBrutos);
        }
      },
    });

    // ver esto!!!

    //TABLA FACTURACION PROFORMA REMITOS
    var datatable_facturacion = $(
      "#tabla_facturacion_proforma_detalle",
    ).DataTable({
      paging: false,
      searching: false,
      footerCallback: function (row, data, start, end, display) {
        total = this.api()
          .column(5, {
            page: "current",
          }) //numero de columna a sumar
          //.column(1, {page: 'current'})//para sumar solo la pagina actual
          .data()
          .reduce(function (a, b) {
            return Number(a) + Number(b);
          }, 0);
        var saldo = currencyFormat(total);
        var sumadebe = currencyFormat(total / 1.21);
        $(this.api().column(5).footer()).html(sumadebe);
        //TOTALES
        var neto = total / 1.21;
        var iva = Number(total - neto);
        $("#factura_neto_detalle").html(currencyFormat(neto));
        $("#factura_iva_detalle").html(currencyFormat(iva));
        $("#factura_total_detalle").html(saldo);
        $("#factura_neto_f_detalle").val(neto.toFixed(2));
        $("#factura_iva_f_detalle").val(iva.toFixed(2));
        $("#factura_total_f_detalle").val(total.toFixed(2));

        //INGRESO DATOS EN CUADRO FINAL DE FACTURACION
        $("#neto_up_detalle").val(neto.toFixed(2));
        $("#iva_up_detalle").val(iva.toFixed(2));
        $("#total_up_detalle").val(total.toFixed(2));
      },
      ajax: {
        url: "/SistemaTriangular/Clientes/Procesos/php/tablas.php",
        data: {
          FacturacionProformaDetalle: 1,
          id: id,
          Remitos: checked,
        },
        type: "post",
      },
      columns: [
        {
          data: "FechaPedido",
          render: function (data, type, row) {
            var Fecha = row.FechaPedido.split("-").reverse().join(".");
            return (
              '<td><span style="display: none;">' +
              row.FechaPedido +
              "</span>" +
              Fecha +
              "</td>"
            );
          },
        },
        { data: "NumPedido" },
        {
          data: "Titulo",
          render: function (data, type, row) {
            return `<td>${row.Titulo}</br>${row.NumeroRepo}`;
          },
        },
        { data: "ClienteDestino" },
        { data: "Comentario" },
        {
          data: "Total",
          render: function (data, type, row) {
            var DebeNeto = row.Total / 1.21;
            return currencyFormat(DebeNeto);
          },
        },
      ],
    });
  } else {
    toast("error", "Error !", "No hay Remitos Seleccionados. No se puede avanzar.");
  }
});

// FACTURAR RECORRIDOS
$("#facturar_recorridos_boton").click(function () {
  document.getElementById("info-header-modal_button").style.display = "none";

  var id = document.getElementById("buscarcliente").value;
  //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  var tablerecorridos = $("#tabla_facturacion_proforma_recorridos").DataTable();
  tablerecorridos.destroy();

  document.getElementById("nota_proforma").style.display = "flex";

  //Creamos un array que almacenará los valores de los input "checked"
  recorridosSeleccionadosFacturar = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $('input[name="checkbox_r"]:checked').each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      recorridosSeleccionadosFacturar.push($(this).attr("value"));
    }
  });
  var checked_r = recorridosSeleccionadosFacturar;
  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
  //   console.log('veo', checked_r);
  if (checked_r != 0) {
    document.getElementById("descuento_botton").style.display = "flex";
    var dato = {
      Datos: 1,
      id: id,
    };
    $.ajax({
      data: dato,
      url: "Procesos/php/funciones.php",
      type: "post",
      //         beforeSend: function(){
      //         $("#buscando").html("Buscando...");
      //         },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          document.getElementById("factura_primerpaso").style.display = "none";
          document.getElementById("factura_proforma").style.display = "block";
          //   document.getElementById('tabla_facturacion_proforma').style.display = "none";
          //   document.getElementById('tabla_facturacion_proforma_recorridos').style.display = "block";

          document.getElementById("row_tabla_facturacion").style.display =
            "none";
          document.getElementById("row_tabla_recorridos").style.display =
            "block";

          $("#factura_titulo").html("FACTURA PROFORMA");
          $("#factura_titulo2").html("FACTURA PROFORMA");

          $("#factura_codigo").html(jsonData.id);
          $("#factura_razonsocial").html(jsonData.RazonSocial);
          $("#factura_direccion").html(jsonData.direccion);
          $("#factura_localidad").html(jsonData.localidad);
          $("#factura_provincia").html(jsonData.provincia);
          $("#factura_celular").html(jsonData.celular);
          $("#factura_cuit").html(jsonData.Cuit);
          $("#factura_condicion").html(jsonData.Condicion);
          $("#factura_email").html(jsonData.Mail);
          $("#factura_ingresosbrutos").html(jsonData.IngresosBrutos);
        }
      },
    });

    //TABLA FACTURACION PROFORMA RECORRIDOS
    var datatable_facturacion_recorridos = $(
      "#tabla_facturacion_proforma_recorridos",
    ).DataTable({
      paging: false,
      searching: false,
      footerCallback: function (row, data, start, end, display) {
        total = this.api()
          .column(4, {
            page: "current",
          }) //numero de columna a sumar
          //.column(1, {page: 'current'})//para sumar solo la pagina actual
          .data()
          .reduce(function (a, b) {
            return Number(a) + Number(b);
          }, 0);
        var saldo = currencyFormat(total);
        var sumadebe = currencyFormat(total / 1.21);
        $(this.api().column(4).footer()).html(sumadebe);
        //TOTALES
        var neto = total / 1.21;
        var iva = Number(total - neto);
        $("#factura_neto").html(currencyFormat(neto));
        $("#factura_iva").html(currencyFormat(iva));
        $("#factura_total").html(saldo);
        $("#factura_neto_f").val(neto.toFixed(2));
        $("#factura_iva_f").val(iva.toFixed(2));
        $("#factura_total_f").val(total.toFixed(2));

        //INGRESO DATOS EN CUADRO FINAL DE FACTURACION
        $("#neto_up_r").val(neto.toFixed(2));
        $("#iva_up_r").val(iva.toFixed(2));
        $("#total_up_r").val(total.toFixed(2));
      },
      ajax: {
        url: "/SistemaTriangular/Clientes/Procesos/php/tablas.php",
        data: {
          FacturacionProformaRecorridos: 1,
          id: id,
          Remitos: checked_r,
        },
        type: "post",
      },
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            if (!row.Fecha) return "";
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
            return row.TipoDeComprobante;
          },
        },
        {
          data: "NumeroVenta",
        },
        {
          data: "Observaciones",
        },
        {
          data: "Debe",
          render: function (data, type, row) {
            var DebeNeto = row.Debe / 1.21;
            return currencyFormat(DebeNeto);
          },
        },
      ],
    });
    //FECHAS
    $.ajax({
      data: {
        FechasRecorridos: 1,
        Remitos: checked_r,
      },
      url: "Procesos/php/funciones.php",
      type: "post",
      dataType: "json",
      success: function (jsonData) {
        $("#desde_f").html(jsonData.Desde);
        $("#hasta_f").html(jsonData.Hasta);
      },
    });
  } else {
    toast("error", "Error !", "No hay Remitos Seleccionados. No se puede avanzar.");
  }
});

//BOTON CANCELAR BOTON FACTR
$("#cancelarfactura_boton_r").click(function () {
  //VACIO LOS ARRAY
  recorridosSeleccionadosFacturar = [];
  document.getElementById("factura_primerpaso").style.display = "flex";
  document.getElementById("factura_proforma").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  //   var tablerecorridos = $('#tabla_facturacion_proforma_recorridos').DataTable();
  //   tablerecorridos.ajax.reload();
});

$("#cancelarfactura_boton").click(function () {
  //VACIO LOS ARRAY
  remitosSeleccionadosFacturar = [];
  recorridosSeleccionadosFacturar = [];
  document.getElementById("factura_primerpaso").style.display = "flex";
  document.getElementById("factura_proforma").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  //   var table = $('#tabla_facturacion_proforma').DataTable();
  //   table.ajax.reload();
});

$("#cancelarfactura_detalle_boton").click(function () {
  //VACIO LOS ARRAY
  remitosSeleccionadosFacturar = [];
  recorridosSeleccionadosFacturar = [];
  document.getElementById("factura_primerpaso").style.display = "flex";
  document.getElementById("factura_proforma_detalle").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  //   var table = $('#tabla_facturacion_proforma').DataTable();
  //   table.ajax.reload();
});

//AL ABRIR EL MODAL FACTURACION
$("#info-header-modal").on("show.bs.modal", function (e) {
  //INDICO QUE EL TIPO DE FACTURA ES 1 Y NO 3 (ND/NC)
  $("#tipo_de_factura").val(1);
  document.getElementById("cbteasoc").style.display = "none";
  //APAGO EL SELECT DE NC/ND POR LAS DUDAS ESTE ABIERTO
  document.getElementById("select_nc_nd_B").style.display = "none";
  document.getElementById("select_nc_nd_A").style.display = "none";

  //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var documento_f = document.getElementById("cuit_facturacion").value;

  console.log("razon social", razonsocial_f);

  if (razonsocial_f == null || razonsocial_f == "") {
    $("#alert_facturacion").css("display", "block");

    document.getElementById("confirmarfactura_boton").style.display = "block";
    document.getElementById("confirmarfactura_boton").style.display = "none";
    $("#alert_facturacion_label").html(
      "La Razon Social no puede ser Null. Agregue una Razon Social desde Datos | Datos Facturación.",
    );
  } else if (documento_f == null || documento_f == "") {
    document.getElementById("confirmarfactura_boton").style.display = "block";
    document.getElementById("confirmarfactura_boton").style.display = "none";
    $("#alert_facturacion_label").html(
      "El Cuit no puede ser Null. Agregue un Cuit válido desde Datos | Datos Facturación.",
    );
  }

  var direccion_f = document.getElementById("direccion_facturacion").value;

  var cai_f = document.getElementById("cai_facturacion").value;

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion",
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var id = document.getElementById("buscarcliente").value;
  var neto = document.getElementById("factura_neto_f").value;
  var iva = document.getElementById("factura_iva_f").value;
  var total = document.getElementById("factura_total_f").value;
  var sitfiscal = document.getElementById("condicion").value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion",
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }
  //1 FACTURAS A
  //6 FACTURAS B

  if (condiva_f == 1 || condiva_f == 6) {
    document.getElementById("confirmarfactura_AFIP_boton").style.display =
      "block";
    document.getElementById("confirmarfactura_boton").style.display = "none";
    document.getElementById(
      "confirmar_generar_comprobante_AFIP_boton",
    ).style.display = "none";
  } else {
    document.getElementById("confirmarfactura_AFIP_boton").style.display =
      "none";
    document.getElementById("confirmarfactura_boton").style.display = "block";
    document.getElementById(
      "confirmar_generar_comprobante_AFIP_boton",
    ).style.display = "none";
  }

  $.ajax({
    data: {
      NComprobante: 1,
      tipodecomprobante: condiva_f,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#ncomprobante_up").val(
        jsonData.PuntoVenta + "-" + jsonData.NComprobante,
      );

      var partesFecha = jsonData.Fecha.split("-");

      // Crear un nuevo formato de fecha
      var fechaFormateada =
        partesFecha[2] + "/" + partesFecha[1] + "/" + partesFecha[0];

      $("#last_date").html("Ult. Fecha: " + fechaFormateada);

      var comp_2 = parseInt($("#tipo_de_factura").val());

      $("#comprobante_up").val(jsonData.Comprobante);

      $("#select_up").val(jsonData.Comprobante);

      $("#comprobante_tipo").val(condiva_f); //ELIMINE ESTO PORQUE ME INDICABA QUE SIEMPRE ERA 1
    },
  });
  $("#cuit_up").val(documento_f);
  $("#razonsocial_up").val(razonsocial_f);
});

//ULTIMO PASO CUADRO DE FACTURACION X RECORRIDO

$("#Facturacion_recorridos_modal").on("show.bs.modal", function (e) {
  console.log("ver", $("#comprobante_tipo_r").val());

  if ($("#comprobante_tipo_r").val() == 0) {
    document.getElementById(
      "confirmarfacturaxrecorrido_AFIP_boton",
    ).style.display = "none";
    document.getElementById("confirmarfacturaxrecorrido_boton").style.display =
      "block";
  } else {
    document.getElementById(
      "confirmarfacturaxrecorrido_AFIP_boton",
    ).style.display = "block";
    document.getElementById("confirmarfacturaxrecorrido_boton").style.display =
      "none";
  }

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion",
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var id = document.getElementById("buscarcliente").value;
  var neto = document.getElementById("factura_neto_f").value;
  var iva = document.getElementById("factura_iva_f").value;
  var total = document.getElementById("factura_total_f").value;
  var sitfiscal = document.getElementById("condicion").value;

  //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var direccion_f = document.getElementById("direccion_facturacion").value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion",
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  //BUSCO EL N DE COMPROBANTE
  $.ajax({
    data: {
      NComprobante: 1,
      tipodecomprobante: condiva_f,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);

      $("#ncomprobante_up_r").val(
        jsonData.PuntoVenta + "-" + jsonData.NComprobante,
      );
      $("#comprobante_up_r").val(jsonData.Comprobante);
      $("#select_up_r").val(jsonData.Comprobante);

      var partesFecha = jsonData.Fecha.split("-");

      // Crear un nuevo formato de fecha
      var fechaFormateada =
        partesFecha[2] + "/" + partesFecha[1] + "/" + partesFecha[0];

      $("#last_date_r").html("Ult. Fecha: " + fechaFormateada);

      if (jsonData.Comprobante !== "FACTURA PROFORMA") {
        $("#comprobante_tipo_r").val(1);

        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton",
        ).style.display = "block";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton",
        ).style.display = "none";
      } else {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton",
        ).style.display = "none";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton",
        ).style.display = "block";
      }
    },
  });

  $("#cuit_up_r").val(documento_f);
  $("#razonsocial_up_r").val(razonsocial_f);
});

//CNL FACTURA
$("#confirmarfactura_boton_cnl").click(function () {
  $("#info-header-modal").modal("toggle");
  var table_facturacion_proforma = $("#tabla_facturacion_proforma").DataTable();
  table_facturacion_proforma.destroy();
});

//CONFIRMAR FACTURA PROFORMA
$("#confirmarfactura_boton").click(function () {
  var fecha = document.getElementById("fecha_up").value;
  var id = document.getElementById("buscarcliente").value;
  var neto = document.getElementById("factura_neto_f").value;
  var iva = document.getElementById("factura_iva_f").value;
  var total = document.getElementById("factura_total_f").value;
  var sitfiscal = document.getElementById("condicion").value;

  console.log("valor", "llego");

  //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var direccion_f = document.getElementById("direccion_facturacion").value;
  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion",
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;

  //CUADRO FACTURACION
  var fecha = document.getElementById("fecha_up").value;
  var comprobante = document.getElementById("comprobante_up").value;
  var observaciones_ctasctes = document.getElementById(
    "observaciones_ctasctes",
  ).value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion",
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  // Reusamos la selección capturada al apretar "Facturar", NO volvemos a
  // escanear el DOM acá: si la tabla se redibujó en el medio, un re-escaneo
  // podría no coincidir con el total ya calculado (factura "pelada").
  var checked = remitosSeleccionadosFacturar;
  if (!checked || checked.length === 0) {
    toast("error", "Error !", "No hay Remitos Seleccionados. No se puede facturar.");
    return;
  }
  var ncomp = $("#ncomprobante_up").val();
  console.log("ids", ncomp);
  //   $('#NumeroComprobante').val();

  //  var dato = {
  //     'razonsocial_f': razonsocial_f,
  //     'direccion_f': direccion_f,
  //     'condiva_f': condiva_f,
  //     'tipodocumento_f': tipodocumento_f,
  //     'documento_f': documento_f,
  //     'Documento': 99,
  //     'ImpTotal': total,
  //     'ImpTotalConc': 0,
  //     'ImpNeto': neto,
  //     'ImpIva': iva,
  //     'ImpTrib': 0,
  //     'Comprobante':comprobante
  //   };

  //   $.ajax({
  //     data: dato,
  //     url: '../afip.php/procesos/salto.php',
  //     type: 'post',
  //     success: function(respuesta) {
  //       var jsonData = JSON.parse(respuesta);
  //       if (jsonData.CAE != '') {
  //         document.getElementById('datos_cae').style.display = "block";
  //         //FACTURO EN EL SISTEMA

  var datofacturasistema = {
    fecha: fecha,
    razonsocial_f: razonsocial_f,
    direccion_f: direccion_f,
    condiva_f: condiva_f,
    tipodocumento_f: tipodocumento_f,
    documento_f: documento_f,
    Documento: 99,
    ImpTotal: total,
    ImpTotalConc: 0,
    ImpNeto: neto,
    ImpIva: iva,
    ImpTrib: 0,
    Facturar: 1,
    Remitos: checked,
    id: id,
    condicion: sitfiscal,
    NumeroComprobante: ncomp,
    Comprobante: comprobante,
    Observaciones_ctasctes: observaciones_ctasctes,
  };
  $.ajax({
    data: datofacturasistema,
    url: "Procesos/php/facturar.php",
    type: "post",
    success: function (respuesta) {
      var jsonData1 = JSON.parse(respuesta);
      if (jsonData1.success == 1) {
        document.getElementById("datos_cae").style.display = "block";
        toast("success", "Comprobante Generado con Exito !", "Se han realizado cambios.");
        //DESTRUIMOS LA TABLA FACTURACION
        var table = $("#tabla_facturacion_proforma").DataTable();
        table.destroy();

        var tabla_facturacion = $("#facturacion_tabla").DataTable();
        tabla_facturacion.ajax.reload();

        //ACTUALIZO LA TABLA CTA CTE
        var tabla_ctacte = $("#basic").DataTable();
        tabla_ctacte.ajax.reload();

        document.getElementById("factura_primerpaso").style.display = "block";
        document.getElementById("factura_proforma").style.display = "none";

        if (jsonData1.idFacturado) {
          abrirModalFactura(jsonData1.idFacturado);
        }
      } else if (jsonData1.success == 0) {
        toast("error", "Error al Intentar Generar el Comprobante !", "No se han realizado cambios.");
      } else if (jsonData1.success == 3) {
        toast("error", "Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.");
      }
      //   }
      // });
      //   }
    },
  });
});

//CNL FACTURA X RECORRIDO
$("#confirmarfactura_boton_cnl_r").click(function () {
  $("#Facturacion_recorridos_modal").modal("toggle");
  var table_facturacion_proforma_recorridos = $(
    "#tabla_facturacion_proforma_recorridos",
  ).DataTable();
  table_facturacion_proforma_recorridos.destroy();
});

//CONFIRMAR FACTURACION X RECORRIDO
$("#confirmarfacturaxrecorrido_boton").click(function () {
  var id = document.getElementById("buscarcliente").value;
  var neto = document.getElementById("factura_neto_f").value;
  var iva = document.getElementById("factura_iva_f").value;
  var total = document.getElementById("factura_total_f").value;
  var sitfiscal = document.getElementById("condicion").value;

  //   //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var direccion_f = document.getElementById("direccion_facturacion").value;

  //   //CUADRO FACTURACION
  var fecha = document.getElementById("fecha_up_r").value;
  var condiva_f = document.getElementById("comprobante_up_r").value;

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion",
  ).value;
  var comprobante = document.getElementById("select_up_r").value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;
  var observaciones_ctasctes = document.getElementById(
    "observaciones_ctasctes",
  ).value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion",
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  // Reusamos la selección capturada al apretar "Facturar Recorridos", NO
  // volvemos a escanear el DOM acá (y NO usamos el selector genérico
  // custom-control-input, que también matchea la tabla de remitos).
  var checked = recorridosSeleccionadosFacturar;
  if (!checked || checked.length === 0) {
    toast("error", "Error !", "No hay Recorridos Seleccionados. No se puede facturar.");
    return;
  }

  var ncomp = $("#ncomprobante_up_r").val();
  //   var dato = {
  //     'razonsocial_f': razonsocial_f,
  //     'direccion_f': direccion_f,
  //     'condiva_f': condiva_f,
  //     'tipodocumento_f': tipodocumento_f,
  //     'documento_f': documento_f,
  //     'Documento': 99,
  //     'ImpTotal': total,
  //     'ImpTotalConc': 0,
  //     'ImpNeto': neto,
  //     'ImpIva': iva,
  //     'ImpTrib': 0
  //   };

  //   $.ajax({
  //     data: dato,
  //     //               url:'../afip.php/procesos/CreateVoucher.php', //HABILITAR PARA FACTURA AFIP
  //     url: '../afip.php/procesos/salto.php',
  //     type: 'post',
  //     success: function(respuesta) {
  //       var jsonData = JSON.parse(respuesta);
  //       if (jsonData.CAE != '') {
  //         document.getElementById('datos_cae').style.display = "block";
  //         $('#CAE').html(jsonData.CAE);
  //         //               var FechaVenc=jsonData.VencimientoCAE.split('-').reverse().join('/');//HABILITAR PARA FACTURA AFIP
  //         //               $('#VencimientoCAE').html(FechaVenc);//HABILITAR PARA FACTURA AFIP
  //         //               $('#NumeroComprobante').html(jsonData.Numero);  //HABILITAR PARA FACTURA AFIP

  //         //FACTURO EN EL SISTEMA
  var datofacturasistema = {
    fecha: fecha,
    razonsocial_f: razonsocial_f,
    direccion_f: direccion_f,
    condiva_f: condiva_f,
    tipodocumento_f: tipodocumento_f,
    documento_f: documento_f,
    Documento: 99,
    ImpTotal: total,
    ImpTotalConc: 0,
    ImpNeto: neto,
    ImpIva: iva,
    ImpTrib: 0,
    Facturar: 2,
    Remitos: checked,
    id: id,
    condicion: sitfiscal,
    NumeroComprobante: ncomp,
    Comprobante: comprobante,
    Observaciones_ctasctes: observaciones_ctasctes,
  };
  $.ajax({
    data: datofacturasistema,
    url: "Procesos/php/facturar.php",
    type: "post",
    success: function (respuesta) {
      var jsonData1 = JSON.parse(respuesta);
      if (jsonData1.success == 1) {
        //REFRESCAMOS LA TABLA FACTURACION X RECORRIDO
        //   var tabla_recorridos = $('#recorridos_tabla').DataTable();
        //   tabla_recorridos.ajax.reload();
        //ACTUALIZO LA TABLA CTA CTE
        var tabla_ctacte = $("#basic").DataTable();
        tabla_ctacte.ajax.reload();

        document.getElementById("factura_primerpaso").style.display = "block";
        document.getElementById("factura_proforma").style.display = "none";
        toast("success", "Comprobante Generado con Exito !", "Se procesaron " + jsonData1.cuento + " recorridos.");

        if (jsonData1.idFacturado) {
          abrirModalFactura(jsonData1.idFacturado);
        }
      } else if (jsonData1.success == 0) {
        toast("error", "Error al Intentar Generar el Comprobante !", "No se han realizado cambios.");
      } else if (jsonData1.success == 3) {
        toast("error", "Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.");
      }
    },
  });
  //       }
  //     }

  //   });
});

function ComprobarNombre(n) {
  $.ajax({
    data: {
      ComprobarNombre: 1,
      Nombre: n,
    },
    url: "../Ventas/Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("errorname").style.display = "none";
      } else {
        document.getElementById("errorname").style.display = "block";
        $("#errorname_label").html(
          "Ya existen " +
            jsonData.num +
            " " +
            n +
            " cargados en el sistema, verifique !",
        );
      }
    },
  });
}

$("#AgregarCliente").click(function () {
  var nombre = document.getElementById("nombrecliente_nc").value;
  var email = document.getElementById("email_nc").value;
  var direccion = document.getElementById("direccion_nc").value;
  var telefono = document.getElementById("telefono_nc").value;
  var celular = document.getElementById("celular_nc").value;
  var relacion = document.getElementById("relacion_nc").value;
  var cp = document.getElementById("cp_nc").value;
  var ciudad = document.getElementById("ciudad_nc").value;
  var observaciones = document.getElementById("observaciones_nc").value;
  var calle = document.getElementById("Calle_nc").value;
  var numero = document.getElementById("Numero_nc").value;
  var barrio = document.getElementById("Barrio_nc").value;

  var dato = {
    nombrecliente: nombre,
    Direccion: direccion,
    Telefono: telefono,
    Celular: celular,
    Mail: email,
    Relacion: relacion,
    CodigoPostal: cp,
    Ciudad: ciudad,
    Calle: calle,
    Numero: numero,
    Barrio: barrio,
    Observaciones: observaciones,
  };

  $.ajax({
    data: dato,
    url: "../Ventas/Procesos/php/crearcliente.php",
    type: "post",
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == "1") {
        var NombreCliente = jsonData.NombreCliente;
        var id = jsonData.id;
        toast("success", "Cliente " + NombreCliente + " creado con éxito !", "Se han realizado cambios.");
        $("#nuevocliente-modal-lg").modal("hide");
      } else {
        toast("error", "Error: El cliente no se creo !", "Se han realizado cambios.");
        $("#nuevocliente-modal-lg").modal("hide");
      }
    },
  });
});

function _googleAcDebounce(inputEl, opciones) {
  var cfg = Object.assign(
    { fields: ["address_components"], componentRestrictions: { country: "AR" },
      types: ["geocode", "establishment"], debounce: 400, minLength: 3, onSelect: null },
    opciones || {}
  );
  var svc = new google.maps.places.AutocompleteService();
  var placeSvc = new google.maps.places.PlacesService(document.createElement("div"));
  var token = new google.maps.places.AutocompleteSessionToken();
  var timer = null;
  var wrapper = inputEl.parentElement;
  if (getComputedStyle(wrapper).position === "static") wrapper.style.position = "relative";
  var ul = document.createElement("ul");
  ul.style.cssText = "position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;" +
    "overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;" +
    "background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);";
  wrapper.appendChild(ul);
  function close() { ul.style.display = "none"; }
  function selectPlace(placeId, description) {
    inputEl.value = description; close();
    placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function (place, status) {
      token = new google.maps.places.AutocompleteSessionToken();
      if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
    });
  }
  inputEl.addEventListener("input", function () {
    clearTimeout(timer);
    var val = this.value.trim();
    if (val.length < cfg.minLength) { close(); return; }
    var snap = val;
    timer = setTimeout(function () {
      svc.getPlacePredictions(
        { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
        function (predictions, status) {
          ul.innerHTML = "";
          if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
          predictions.forEach(function (p) {
            var li = document.createElement("li");
            li.style.cssText = "padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;";
            li.textContent = p.description;
            li.addEventListener("mouseover", function () { this.style.background = "#f5f5f5"; });
            li.addEventListener("mouseout", function () { this.style.background = ""; });
            li.addEventListener("mousedown", function (e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
            ul.appendChild(li);
          });
          ul.style.display = "block";
        }
      );
    }, cfg.debounce);
  });
  inputEl.addEventListener("blur", function () { setTimeout(close, 200); });
}

function BuscarDireccion() {
  var inputstart = document.getElementById("direccion_nc");
  if (!inputstart) return;
  _googleAcDebounce(inputstart, {
    onSelect: function (place) {
      if (!place || !place.address_components) return;
      place.address_components.forEach(function (c) {
        var t = c.types[0];
        if (t === "locality") document.getElementById("ciudad_nc").value = c.long_name;
        else if (t === "postal_code") document.getElementById("cp_nc").value = c.short_name;
        else if (t === "neighborhood") document.getElementById("Barrio_nc").value = c.long_name;
        else if (t === "street_number") document.getElementById("Numero_nc").value = c.long_name;
        else if (t === "route") document.getElementById("Calle_nc").value = c.long_name;
      });
    },
  });
}

function vercomprobante() {
  //   alert('ok');
  //   var id = document.getElementById('buscarcliente').value;
  //   document.getElementById('Facturacion_recorridos_button').style.display="none";
  //   //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  //   var table = $('#tabla_facturacion_proforma').DataTable();
  //   table.destroy();

  //   document.getElementById('nota_proforma').style.display = "flex";

  //   //Creamos un array que almacenará los valores de los input "checked"
  //   var checked = [];
  //   //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  //   $("input.custom-control-input:checked").each(function() {
  //     //Mediante la función push agregamos al arreglo los values de los checkbox
  //     if ($(this).attr("value") != null) {
  //       checked.push(($(this).attr("value")));
  //     }
  //   });
  //   // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  //   if (checked != 0) {
  //     document.getElementById('descuento_botton').style.display = 'flex';
  //     var dato = {
  //       "Datos": 1,
  //       "id": id
  //     };
  //     $.ajax({
  //       data: dato,
  //       url: 'Procesos/php/funciones.php',
  //       type: 'post',
  //       //         beforeSend: function(){
  //       //         $("#buscando").html("Buscando...");
  //       //         },
  //       success: function(response) {
  //         var jsonData = JSON.parse(response);
  //         if (jsonData.success == "1") {
  //           document.getElementById('factura_primerpaso').style.display = "none";
  document.getElementById("factura_proforma").style.display = "block";
  //           document.getElementById('row_tabla_facturacion').style.display = "block";
  //           document.getElementById('row_tabla_recorridos').style.display = "none";

  //           $('#factura_titulo').html('FACTURA PROFORMA');
  //           $('#factura_titulo2').html('FACTURA PROFORMA');
  //           $('#factura_codigo').html(jsonData.id);
  //           $('#factura_razonsocial').html(jsonData.RazonSocial);
  //           $('#factura_direccion').html(jsonData.direccion);
  //           $('#factura_localidad').html(jsonData.localidad);
  //           $('#factura_provincia').html(jsonData.provincia);
  //           $('#factura_celular').html(jsonData.celular);
  //           $('#factura_cuit').html(jsonData.Cuit);
  //           $('#factura_condicion').html(jsonData.Condicion);
  //           $('#factura_email').html(jsonData.Mail);
  //           $('#factura_ingresosbrutos').html(jsonData.IngresosBrutos);
  //         }
  //       }
  //     });
  //   }
}

// $("#miarchivo").dropzone({ url: "../../FacturasVenta/" });
