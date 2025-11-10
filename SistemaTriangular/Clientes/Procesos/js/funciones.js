function currencyFormat(num) {
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
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
        $.NotificationApp.send(
          "Exito !",
          "Tareas Asignadas Correctamente.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
        $.NotificationApp.send(
          "Exito !",
          "Tareas Asignadas Correctamente.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
            "</option>"
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
    url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/eliminapago.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        $("#modal_eliminar_pago").modal("show");

        $("#modal_eliminar_pago_text").html("Estas por eliminar el pago " + i);

        $("#modal_eliminar_pago_aceptar").click(function () {
          $("#modal_eliminar_pago").modal("hide");

          $.ajax({
            data: {
              Eliminar_pago: 1,
              idCtasctes: i,
            },
            type: "POST",
            url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/eliminapago.php",
            success: function (response) {
              var jsonData = JSON.parse(response);

              if (jsonData.success == 1) {
                $.NotificationApp.send(
                  "Exito !",
                  "Registro Eliminado.",
                  "bottom-right",
                  "#FFFFFF",
                  "success"
                );
                var table = $("#basic").DataTable();
                table.ajax.reload();
              } else if (jsonData.success == 0) {
                $.NotificationApp.send(
                  "Error !",
                  jsonData.msg,
                  "bottom-right",
                  "#FFFFFF",
                  "dange"
                );
              } else if (jsonData.success == 401) {
                $("#danger-alert-modal").modal("show");
              }

              // console.log('idTransClientes',jsonData.idTransClientes);
              // console.log('idTesoreria',jsonData.idTesoreria);
            },
          });
        });
      } else {
        $("#danger-alert-modal").modal("show");
      }
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
    url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/invoice.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var Fecha = jsonData.data[0].Fecha.split("-").reverse().join(".");

      $.NotificationApp.send(
        "Email enviado el " + Fecha + " a " + jsonData.data[0].Mail,
        "Se han realizado cambios.",
        "bottom-right",
        "#FFFFFF",
        "success"
      );
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
    url: "../Procesos/php/movimientos_internos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        var id = document.getElementById("codigo").value;

        actualizar_totales(id);

        $.NotificationApp.send(
          "Exito !",
          "Registro Eliminado.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error !",
          "Registro No Eliminado.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
}

//CONTACT
$("#perfil_conctact").click(function () {
  // var table_contact = $('#table-contact').DataTable();

  // table_contact.destroy();

  var id = document.getElementById("codigo").value;

  $("#table-contact").DataTable({
    destroy: true,
    paging: false,
    searching: false,
    ajax: {
      url: "../Procesos/php/tablas.php",
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
        data: "id_hubspot",
        render: function (data, type, row) {
          return `<span class="badge badge-primary">${row.id_hubspot}</span>`;
        },
      },
      {
        data: "id",
        render: function (data, type, row) {
          // return `<td><a href="#" data-id="${row.id}" data-toggle="modal" data-target="#contact-modal"><i class="mdi mdi-pencil"></i></a></td>`;
          return (
            `<a href="#" data-id="${row.id}" data-nombre="${row.Nombre}" data-apellido="${row.Apellido}" data-email="${row.email}" data-sector="${row.Sector}" data-telefono="${row.Telefono}" data-toggle="modal" data-target="#contact-modal"><i class="mdi mdi-pencil text-warning"></i></a>` +
            `<a class="ml-2" id="contact-delete" data-id="${row.id}"><i class="mdi mdi-trash-can text-danger"></i></a>`
          );
        },
      },
    ],
  });
});

$(document).on("click", "#contact-delete", function (e) {
  var triggerLink = $(this);
  var dataID = triggerLink.data("id");
  var operationCanceled = false; // Variable para verificar si la operación fue cancelada

  // Mostrar la notificación con el botón "Cancelar"
  $.NotificationApp.send(
    "Atención !",
    `<p>Estas por eliminar el contacto. <a href="#" id="cancel-action" class="alert-link" data-id="${dataID}">Cancelar</a></p>`,
    "bottom-right",
    "#FFFFFF",
    "warning"
  );

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
          $.NotificationApp.send(
            "Éxito",
            "El contacto ha sido eliminado.",
            "bottom-right",
            "#28a745",
            "success"
          );
          $("#table-contact").DataTable().ajax.reload();
        },
        error: function (xhr, status, error) {
          // Manejar el error en la solicitud AJAX
          $.NotificationApp.send(
            "Error",
            "No se pudo eliminar el contacto.",
            "bottom-right",
            "#dc3545",
            "danger"
          );
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
    $.NotificationApp.send(
      "Cancelado",
      "Operación de eliminación cancelada.",
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
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
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);

      if (jsonData.success == 1) {
        var table_contact = $("#table-contact").DataTable();
        table_contact.ajax.reload();
      } else {
        $.NotificationApp.send(
          "Error",
          "No se pudo agregar el contacto. ".jsonData.error,
          "bottom-right",
          "#dc3545",
          "danger"
        );
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

  $.ajax({
    data: {
      Modificar_contacto: 1,
      idCliente: id,
      contact_nombre: nombre,
      contact_lastname: lastname,
      contact_email: email,
      contact_sector: sector,
      contact_telefono: telefono,
      contact_website: web,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);

      if (jsonData.success == 1) {
        var table_contact = $("#table-contact").DataTable();
        table_contact.ajax.reload();
        $.NotificationApp.send(
          "Exito",
          "Registro modificado !",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error",
          "No se pudo modificar el contacto. " + jsonData.error,
          "bottom-right",
          "#dc3545",
          "danger"
        );
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
              `<td><a target='_blank' href='Informes/recibo.php?id=${row.id}' title='Recibo' ><i class='mdi mdi-18px mdi-alpha-r-circle text-success'></i></a>` +
              `<a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></a>`
            );
          } else {
            return (
              `<td><a target='_blank' href='invoice.php?id=${row.id}' title='Comprobante' >` +
              `<i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
              `<a target='_blank' href='invoice_details.php?id=${row.id}' data-bs-toggle='tooltip' data-bs-placement='right' title='Detalle' data-original-title='Detalle'><i class='mdi mdi-18px mdi-alpha-d-circle text-warning'></i></a></td>`
            );
          }
        },
      },
      // //   {data:null,
      // //     render: function (data,type,row){
      // //       if(row.Debe>0){

      // //         return '<td class="dtr-control dt-checkboxes-cell">'+
      // //                '<div class="form-check"><input data-id="'+row.id+'" value="'+row.Debe+'" type="checkbox" class="form-check-input dt-checkboxes" >'+
      // //               '<label class="form-check-label">&nbsp;</label></div></td>';

      // //         }else{

      // //        return '<td></td>';

      // //       }
      // //     }
      // }
    ],
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
        var jsonData = JSON.parse(respuesta);

        if (jsonData.success == 1) {
          $.NotificationApp.send(
            "Exito !",
            "Registro Actualizado.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );

          if (switchValue == 0) {
            $("#info_facturacion").css("display", "block");
            $("#info_facturacion_text").html(
              "<strong>Info - Facturación:</strong> El cliente <b>no</b> utiliza el servicio de <b>colecta</b>"
            );
          } else {
            $("#info_facturacion").css("display", "block");
            $("#info_facturacion_text").html(
              "<strong>Info - Facturación:</strong> El cliente utiliza el servicio de <b>colecta</b>"
            );
          }
        } else {
          $.NotificationApp.send(
            "Error !",
            jsonData.error,
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
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
    "#tabla_facturacion_proforma_recorridos"
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
        $.NotificationApp.send(
          "Exito !",
          "Registro Actualizado.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error !",
          "Registro No Actualizado.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
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
        jsonData.PuntoVenta + "-" + jsonData.NComprobante
      );
      $("#comprobante_up_r").val(jsonData.Comprobante);
      $("#select_up_r").val(jsonData.Comprobante);
      $("#comprobante_tipo_r").val(comp);

      console.log("veo_comp", comp);

      if (comp == 0) {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton"
        ).style.display = "none";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton"
        ).style.display = "block";
      } else {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton"
        ).style.display = "block";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton"
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
            "</option>"
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
          "confirmar_generar_comprobante_AFIP_boton"
        ).style.display = "none";
        document.getElementById("cbteasoc").style.display = "none";

        break;
      case 1:
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "none";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
        jsonData.PuntoVenta + "-" + jsonData.NComprobante
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
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
        ).style.display = "none";
        break;
      case 1: //FACTURAS A
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton"
        ).style.display = "none";

        break;
      case 6: //FACTURAS B
        document.getElementById("confirmarfactura_AFIP_boton").style.display =
          "block";
        document.getElementById("confirmarfactura_boton").style.display =
          "none";
        document.getElementById(
          "confirmar_generar_comprobante_AFIP_boton"
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
          "confirmar_generar_comprobante_AFIP_boton"
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
        jsonData.PuntoVenta + "-" + jsonData.NComprobante
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

var id = document.getElementById("codigo").value;

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

var prodId = getParameterByName("id");
var seguimiento_modal = getParameterByName("#");

$(document).on("change", 'select[name="formadepago"]', function (e) {
  console.log("value", this.value);

  if (this.value == "000111400") {
    $("#confirmarpago_botton").prop("disabled", true);
    $("#efectivo").hide();
    $("#cheques").hide();
    $("#transferencia").hide();

    document.getElementById("mercadopago").style.display = "flex";
  }
  if (this.value == "000111200") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#efectivo").hide();
    $("#cheques").hide();
    document.getElementById("transferencia").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  }

  if (this.value == "000111210") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#efectivo").hide();
    $("#cheques").hide();
    document.getElementById("transferencia").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  }

  if (this.value == "000111100") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#transferencia").hide();
    $("#cheques").hide();
    document.getElementById("efectivo").style.display = "flex";
    $("#confirmarpago_botton").prop("disabled", false);
  }
  if (this.value == "000112400") {
    $("#mercadopago").hide();
    $("#mercadopago_api").hide();
    $("#efectivo").hide();
    $("#transferencia").hide();
    document.getElementById("cheques").style.display = "flex";
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
        $.NotificationApp.send(
          "Exito !",
          "Registro Actualizado.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
    $.NotificationApp.send(
      "Recuerde Guardar el formulario !",
      "Se han realizado cambios.",
      "bottom-right",
      "#FFFFFF",
      "info"
    );
  });
}
//IDEM FORM PARA CUSTOM
var divsC = form_clientes.getElementsByClassName("custom-control-input");

//Recorres la lista de elementos seleccionados
for (var i = 0; i < divsC.length; i++) {
  //Añades un evento a cada elemento
  divsC[i].addEventListener("change", function () {
    $.NotificationApp.send(
      "Recuerde Guardar el formulario !",
      "Se han realizado cambios.",
      "bottom-right",
      "#FFFFFF",
      "info"
    );
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
    beforeSend: function () {
      // $("#buscando").html("Buscando...");
      // alert('enviando...');
    },
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
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

$("#buscarcliente").change(function () {
  obtenerUsuarios();

  document.getElementById("crearcliente").style.display = "none";
  document.getElementById("relacion_select").style.display = "none";
  document.getElementById("relacion").style.display = "block";
  $("#claveweb_label").prop("readonly", false);
  $("#buscarcliente").prop("disabled", true);

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
    "#tabla_facturacion_proforma_recorridos"
  ).DataTable();
  table_facturacion_proforma_recorridos.destroy();

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
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("steps").style.display = "flex";
        $("#codigo").val(jsonData.id);
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
        }

        $("#asana_gid").val(jsonData.TareasAsana_gid).trigger("change.select2");

        // INFO COLECTAS
        if (jsonData.Colecta == 1) {
          $("#info_facturacion").css("display", "block");
          $("#info_facturacion_text").html(
            "<strong>Info - Facturación:</strong> El cliente utiliza el servicio de <b>colecta</b>"
          );
        } else {
          $("#info_facturacion").css("display", "block");
          $("#info_facturacion_text")
            .css("display", "block")
            .html(
              "<strong>Info - Facturación:</strong> El cliente <b>no</b> utiliza el servicio de <b>colecta</b>"
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

        var datatable = $("#basic").DataTable({
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
              data: "RazonSocial",
            },
            {
              data: "TipoDeComprobante",
              render: function (data, type, row) {
                if (
                  row.TipoDeComprobante === "Recibo de Pago" ||
                  row.TipoDeComprobante === "MOVIMIENTO INTERNO"
                ) {
                  var comprobante =
                    row.TipoDeComprobante + " " + row.NumeroVenta;
                } else {
                  var comprobante =
                    row.TipoDeComprobante + " " + row.NumeroFactura;
                }

                if (row.TipoDeComprobante == "MOVIMIENTO INTERNO") {
                  return `${row.TipoDeComprobante} ${row.NumeroVenta}<br><small class="mr-2 text-muted"> ${row.Comentario} </small>`;
                } else {
                  return `${comprobante}<br><small class="mr-2 text-muted"><a id="${row.id}" onclick="obs_modify(this.id)"><i class='mdi mdi-14px mdi-pencil-outline text-muted'>  ${row.Comentario} </i></a></small>`;
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
              render: function (data, type, row) {
                if (row.TipoDeComprobante != "MOVIMIENTO INTERNO") {
                  //RECIBO DE PAGO
                  if (row.Haber > 0) {
                    if (row.idNotifications == 0) {
                      return (
                        `<td><a target='_blank' href='Informes/recibo.php?id=${row.id}' title='Recibo' ><i class='mdi mdi-18px mdi-alpha-r-circle text-success mr-1'></i></a>` +
                        `<a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger'></i></td>`
                      );
                    } else {
                      return `<td><a target='_blank' href='Informes/recibo.php?id=${row.id}' title='Recibo' ><i class='mdi mdi-18px mdi-alpha-r-circle text-success mr-2'></i></a><a onclick='eliminar_pago(${row.id})'><i class='mdi mdi-18px mdi-trash-can text-danger mr-2'></i></a><a onclick=''><i class='mdi mdi-18px mdi-email-check text-success'></i></a></td>`;
                    }
                    //FACTURA
                  } else {
                    if (row.idNotifications == 0) {
                      return (
                        `<td><a target='_blank' href='invoice.php?id=${row.id}' title='Comprobante' ><i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
                        `<a target='_blank' href='invoice_details.php?id=${row.id}' data-bs-toggle='tooltip' data-bs-placement='right' title='Detalle' data-original-title='Detalle'>` +
                        `<i class='mdi mdi-18px mdi-alpha-d-circle text-warning'></i></a></td>`
                      );
                    } else {
                      return (
                        `<td><a target='_blank' href='invoice.php?id=${row.id}' title='Comprobante' ><i class='mdi mdi-18px mdi-alpha-p-circle mr-2'></i></a>` +
                        `<a target='_blank' href='invoice_details.php?id=${row.id}' data-bs-toggle='tooltip' data-bs-placement='right' title='Detalle' data-original-title='Detalle'>` +
                        `<i class='mdi mdi-18px mdi-alpha-d-circle text-warning mr-2'></i></a><a onclick='notifications(${row.idNotifications})'><i class='mdi mdi-18px mdi-email-check text-success'></i></a></td></td>`
                      );
                    }
                  }
                } else {
                  return `<td><a onclick='eliminar_mvi(${row.id})' class='action-icon'> <i class='mdi mdi-18px mdi-trash-can text-danger'></i></td>`;
                }
              },
            },
          ],
        });

        //TABLA RELACIONES
        var datatable_relaciones = $("#relaciones_tabla").DataTable({
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
              data.AdminEnvios == 1
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
                  $.NotificationApp.send(
                    "Registro Actualizado !",
                    "Se han realizado cambios.",
                    "bottom-right",
                    "#FFFFFF",
                    "success"
                  );
                } else {
                  $.NotificationApp.send(
                    "Ocurrio un Error !",
                    "No se realizaron cambios.",
                    "bottom-right",
                    "#FFFFFF",
                    "danger"
                  );
                }
              },
            });
          }
        );
        //TABLA TARIFAS
        $("#botontarifas").click(function () {
          //                   document.getElementById('agregar_botton').style.display='block';
          document.getElementById("guardar_botton").style.display = "none";
          document.getElementById("eliminar_botton").style.display = "none";

          document.getElementById("cargarpago_botton").style.display = "none";
          document.getElementById(
            "generar_comprobante_afip_button"
          ).style.display = "none";
          document.getElementById(
            "asociar_pago_comprobante_button"
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

        $("#tarifas_tabla").on("click", "a.action-icon", function (e) {
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
                $.NotificationApp.send(
                  "Registro Eliminado !",
                  "Se han realizado cambios.",
                  "bottom-right",
                  "#FFFFFF",
                  "success"
                );
              } else {
                $.NotificationApp.send(
                  "Registro No Eliminado !",
                  "No se han realizado cambios.",
                  "bottom-right",
                  "#FFFFFF",
                  "waring"
                );
              }
            },
          });
        });

        $("#telefono_contacto").html(" Telefono: " + jsonData.celular);
        $("#mail_contacto").html(" Mail: " + jsonData.Mail);
        $("#contacto_contacto").html(" Contacto: " + jsonData.contacto);
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
        var Fecha = jsonData.UltFacFecha.split("-").reverse().join(".");
        $("#fecha").html(Fecha);
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

        var FechaUltPago = jsonData.FechaUltPago.split("-").reverse().join(".");
        if (!jsonData.FechaUltPago) {
          $("#fecha_ult_pago").html("Sin Pagos");
        } else {
          $("#fecha_ult_pago").html("Últ. Pago el " + FechaUltPago);
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
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.data[0].Pass != null) {
        $("#claveweb_label").val(jsonData.data[0].Pass);

        //                   $.NotificationApp.send("Registro Actualizado !","Se han realizado cambios.","bottom-right","#FFFFFF","success");
      } else {
        $("#claveweb_label").val("");
        //                   $.NotificationApp.send("Ocurrio un Error !","No se realizaron cambios.","bottom-right","#FFFFFF","danger");
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
    success: function (response) {
      var jsonData = JSON.parse(response);

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
        $.NotificationApp.send(
          "Listo!",
          "Creamos el Proveedor",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else if (jsonData.success == "2") {
        $.NotificationApp.send(
          "Error!",
          "El Nombre, Direccion o Cuit, ya existen en el sistema",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      } else if (jsonData.success == "3") {
        $.NotificationApp.send(
          "Error!",
          "El nombre no puede ser NULL",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
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
  var retiro = document.getElementById("retira").value;
  //FACTURACION
  var razonsocial_f = document.getElementById("razonsocial_facturacion").value;
  var direccion_f = document.getElementById("direccion_facturacion").value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion"
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion"
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;
  var observaciones_f = document.getElementById(
    "observaciones_facturacion"
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
        $.NotificationApp.send(
          "Listo!",
          "Datos Guardados",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
      "Estas por eliminar el cliente " + id
    );
  } else {
    $.NotificationApp.send(
      "Error !",
      "No se puede eliminar ya que el saldo es " + total_saldo,
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
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
        $.NotificationApp.send(
          "Exito !",
          "Se elimino el cliente " + id,
          "bottom-right",
          "#FFFFFF",
          "danger"
        );

        setTimeout(function () {}, 3000);

        location.reload();
      }
    },
  });
});

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
      "#tabla_facturacion_proforma_recorridos"
    ).DataTable();
    table_facturacion_proforma_recorridos.destroy();

    var datatable_facturacion = $("#facturacion_tabla").DataTable({
      dom: "Bfrtip",
      buttons: ["pageLength", "copy", "excel", "pdf"],
      paging: false,
      searching: true,
      //   lengthMenu: [
      //     [10, 25, 50, -1],
      //     [10, 25, 50, 'All']
      //   ],
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
            if (row.Entregado == 1) {
              var entregado =
                '<span class="badge badge-success badge-pill">Entregado</span>';
            } else {
              var entregado =
                '<span class="badge badge-danger badge-pill">No Entregado</span>';
            }
            if (row.FormaDePago == "Origen") {
              return (
                '<span class="badge badge-secondary">' +
                row.TipoDeComprobante +
                " " +
                row.NumeroComprobante +
                "</span></br>" +
                '<span class="badge badge-outline-primary badge-pill">' +
                row.FormaDePago +
                "</span> " +
                entregado
              );
            } else {
              return (
                '<span class="badge badge-secondary">' +
                row.TipoDeComprobante +
                " " +
                row.NumeroComprobante +
                "</span></br>" +
                '<span class="badge badge-outline-secondary badge-pill">' +
                row.FormaDePago +
                "</span> " +
                entregado
              );
            }
          },
        },
        //               {data: "Observaciones",
        //               render: function (data, type, row) {
        //                 if(row.Observaciones!=""){
        //                 $('#observaciones_body').html(row.Observaciones);
        //                   console.log('row',row);
        //                   return '<a href="#" data-toggle="modal" data-target="#bs-example-modal-sm" class="badge badge-info badge-pill">Observaciones</a>';
        //                 }else{
        //                   return '';
        //                   }
        //                 }
        //               },
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
              '<a style="cursor:pointer"  data-toggle="modal" data-target="#standard-modal-codcliente" data-id="' +
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
            // }else{

            // return '<td></td>';

            // }
          },
        },

        {
          data: "CodigoSeguimiento",

          render: function (data, type, row) {
            if (row.Flex == 1) {
              var servicio =
                '<span style="cursor:pointer" onclick="change_service(' +
                row.id +
                ')" class="badge badge-success">Flex</span>';
            } else {
              servicio =
                '<span style="cursor:pointer" onclick="change_service(' +
                row.id +
                ')" class="badge badge-warning text-white">Simple</span>';
            }
            return (
              '<td class="table-action">' +
              '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
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
              "</td>"
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
            return (
              '<td class="table-action">' +
              '<a data-id="' +
              row.id +
              '" data-toggle="modal" data-target="#standard-modal-modificar" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>' +
              '<a data-id="' +
              row.id +
              '" data-tabla="trans" data-title="' +
              row.CodigoSeguimiento +
              '" data-toggle="modal" data-target="#warning-modal" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a>' +
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
        },
      ],
      select: {
        style: "os",
        selector: "td:not(:last-child)", // no row selection on last column
      },
      rowCallback: function (row, data) {
        // Set the checked state of the checkbox in the table
        $("input.custom-control-input", row).prop("checked");
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
      }
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
          $.NotificationApp.send(
            "Registro Actualizado !",
            "Se han realizado cambios.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
          $("#codigocliente_t").val("");
          var table = $("#facturacion_tabla").DataTable();
          table.ajax.reload();
          $("#standard-modal-codcliente").modal("hide");
        } else {
          $.NotificationApp.send(
            "Ocurrio un Error !",
            "No se realizaron cambios.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
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
          return `<td><a onclick='eliminar_elemento_rec(${row.id})' class='action-icon'><i class='mdi mdi-18px mdi-trash-can-outline'></i></a></td>`;
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
    rowCallback: function (row, data) {
      // Set the checked state of the checkbox in the table
      $('input[name="checkbox_r"]', row).prop("checked", data.id == 1);
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
          $.NotificationApp.send(
            "Exito !",
            "Registro Actualizado.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
        } else {
          $.NotificationApp.send(
            "Error !",
            "No pudimos cargar el registro.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
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
              '<span class="badge badge-success badge-pill">Entregado</span></h6>';
          } else {
            var entregado =
              '<span class="badge badge-danger badge-pill">No Entregado</span></h6>';
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
              '<h6><span class="badge badge-secondary mb-1">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-primary badge-pill">' +
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
              '<h6><span class="badge badge-secondary">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-secondary badge-pill">' +
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
            '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
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
              '<span class="badge badge-success badge-pill">Entregado</span></h6>';
          } else {
            var entregado =
              '<span class="badge badge-danger badge-pill">No Entregado</span></h6>';
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
              '<span class="badge badge-outline-primary badge-pill">' +
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
              '<span class="badge badge-outline-warning badge-pill">R:' +
              row.NumeroComprobante +
              "</span></br>" +
              '<span class="badge badge-outline-secondary badge-pill">' +
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
            '<a style="cursor:pointer" class="text-primary" data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
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
$("#generar_comprobante_afip_button").click(function () {
  $("#tipo_de_factura").val(3);

  $("#info-header-modal").modal("show");
  $("#confirmarfactura_AFIP_boton").css("display", "none");
  $("#confirmar_generar_comprobante_AFIP_boton").css("display", "block");

  $("#cbteasoc").css("display", "block");
  $("#alert_facturacion_1").css("display", "block");
  $("#comprobante_up").css("display", "block");
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
    url: "../SistemaTriangular/Funciones/php/tablas.php",
    success: function (response) {
      $(".selector-formadepago select").html(response).fadeIn();
    },
  });
});

$("#debitocredito_botton").click(function () {
  $("#modal_movimientos_internos").modal("show");
});

$("#modal_movimientos_internos").on("show.bs.modal", function (e) {
  $("#form_mi")[0].reset();

  //BUSCO EL ULTIMO COMPROBANTE DE MOVIMIENTOS INTERNOS
  $.ajax({
    data: {
      MovimientosInternos: 1,
    },
    type: "POST",
    url: "../Clientes/Procesos/php/movimientos_internos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#comprobante_movimientos_internos").val(jsonData.dato);
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
    success: function (response) {
      var jsonData = JSON.parse(response);

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

$("#facturar_boton").click(function () {
  var id = document.getElementById("buscarcliente").value;
  document.getElementById("Facturacion_recorridos_button").style.display =
    "none";
  //DESTRUIMOS LA TABLA FACTURACION PROFORMA
  var table = $("#tabla_facturacion_proforma").DataTable();
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
    $.NotificationApp.send(
      "Error !",
      "No hay Remitos Seleccionados. No se puede avanzar.",
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
  }
});

// FACTURAR REMITOS DETALLE

$("#facturar_detalle_boton").click(function () {
  $.ajax({
    data: { Empresa: 1 },
    url: "https://www.caddy.com.ar/SistemaTriangular/Funciones/php/datosempresa.php",
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
        jsonData.data[0].InicioActividades
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
    document.getElementById("descuento_botton").style.display = "none";
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
            "row_tabla_facturacion_detalle"
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
      "#tabla_facturacion_proforma_detalle"
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
        url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php",
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
    $.NotificationApp.send(
      "Error !",
      "No hay Remitos Seleccionados. No se puede avanzar.",
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
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
  var checked_r = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $('input[name="checkbox_r"]:checked').each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked_r.push($(this).attr("value"));
    }
  });
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
      "#tabla_facturacion_proforma_recorridos"
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
        url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php",
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
      //               select: {
      //                   style: 'os',
      //                   selector: 'td:not(:last-child)' // no row selection on last column
      //               },
      //               rowCallback: function ( row, data ) {
      //                   // Set the checked state of the checkbox in the table
      //               $('input.editor-active1', row).prop( 'checked', data.id == 1 );
      //               }
    });
    //FECHAS
    $.ajax({
      data: {
        FechasRecorridos: 1,
        Remitos: checked_r,
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
    $.NotificationApp.send(
      "Error !",
      "No hay Remitos Seleccionados. No se puede avanzar.",
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
  }
});

//BOTON CANCELAR BOTON FACTR
$("#cancelarfactura_boton_r").click(function () {
  //VACIO LOS ARRAY
  var checked_r = [];
  document.getElementById("factura_primerpaso").style.display = "flex";
  document.getElementById("factura_proforma").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  //   var tablerecorridos = $('#tabla_facturacion_proforma_recorridos').DataTable();
  //   tablerecorridos.ajax.reload();
});

$("#cancelarfactura_boton").click(function () {
  //VACIO LOS ARRAY
  var checked = [];
  var checked_r = [];
  document.getElementById("factura_primerpaso").style.display = "flex";
  document.getElementById("factura_proforma").style.display = "none";
  document.getElementById("descuento_botton").style.display = "none";
  //   var table = $('#tabla_facturacion_proforma').DataTable();
  //   table.ajax.reload();
});

$("#cancelarfactura_detalle_boton").click(function () {
  //VACIO LOS ARRAY
  var checked = [];
  var checked_r = [];
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
      "La Razon Social no puede ser Null. Agregue una Razon Social desde Datos | Datos Facturación."
    );
  } else if (documento_f == null || documento_f == "") {
    document.getElementById("confirmarfactura_boton").style.display = "block";
    document.getElementById("confirmarfactura_boton").style.display = "none";
    $("#alert_facturacion_label").html(
      "El Cuit no puede ser Null. Agregue un Cuit válido desde Datos | Datos Facturación."
    );
  }

  var direccion_f = document.getElementById("direccion_facturacion").value;

  var cai_f = document.getElementById("cai_facturacion").value;

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion"
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var id = document.getElementById("buscarcliente").value;
  var neto = document.getElementById("factura_neto_f").value;
  var iva = document.getElementById("factura_iva_f").value;
  var total = document.getElementById("factura_total_f").value;
  var sitfiscal = document.getElementById("condicion").value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion"
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
      "confirmar_generar_comprobante_AFIP_boton"
    ).style.display = "none";
  } else {
    document.getElementById("confirmarfactura_AFIP_boton").style.display =
      "none";
    document.getElementById("confirmarfactura_boton").style.display = "block";
    document.getElementById(
      "confirmar_generar_comprobante_AFIP_boton"
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
        jsonData.PuntoVenta + "-" + jsonData.NComprobante
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
      "confirmarfacturaxrecorrido_AFIP_boton"
    ).style.display = "none";
    document.getElementById("confirmarfacturaxrecorrido_boton").style.display =
      "block";
  } else {
    document.getElementById(
      "confirmarfacturaxrecorrido_AFIP_boton"
    ).style.display = "block";
    document.getElementById("confirmarfacturaxrecorrido_boton").style.display =
      "none";
  }

  var tipodocumento_f = document.getElementById(
    "tipodocumento_facturacion"
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
      "nueva_condicion_facturacion"
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
        jsonData.PuntoVenta + "-" + jsonData.NComprobante
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
          "confirmarfacturaxrecorrido_AFIP_boton"
        ).style.display = "block";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton"
        ).style.display = "none";
      } else {
        document.getElementById(
          "confirmarfacturaxrecorrido_AFIP_boton"
        ).style.display = "none";
        document.getElementById(
          "confirmarfacturaxrecorrido_boton"
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
    "tipodocumento_facturacion"
  ).value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;

  //CUADRO FACTURACION
  var fecha = document.getElementById("fecha_up").value;
  var comprobante = document.getElementById("comprobante_up").value;
  var observaciones_ctasctes = document.getElementById(
    "observaciones_ctasctes"
  ).value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion"
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    if ($(this).attr("value") != null) {
      //Mediante la función push agregamos al arreglo los values de los checkbox
      checked.push($(this).attr("value"));
    }
  });
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
        $.NotificationApp.send(
          "Comprobante Generado con Exito !",
          "Se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
      } else if (jsonData1.success == 0) {
        $.NotificationApp.send(
          "Error al Intentar Generar el Comprobante !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      } else if (jsonData1.success == 3) {
        $.NotificationApp.send(
          "Error en el Codigo de Afip del Cliente !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
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
    "#tabla_facturacion_proforma_recorridos"
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
    "tipodocumento_facturacion"
  ).value;
  var comprobante = document.getElementById("select_up_r").value;
  var documento_f = document.getElementById("cuit_facturacion").value;
  var cai_f = document.getElementById("cai_facturacion").value;
  var observaciones_ctasctes = document.getElementById(
    "observaciones_ctasctes"
  ).value;

  if (document.getElementById("nueva_condicion_facturacion").value != "") {
    var condiva_f = document.getElementById(
      "nueva_condicion_facturacion"
    ).value;
  } else {
    var condiva_f = document.getElementById("condicion_facturacion").value;
  }

  //   //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    if ($(this).attr("value") != null) {
      //Mediante la función push agregamos al arreglo los values de los checkbox
      checked.push($(this).attr("value"));
    }
  });

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
        $.NotificationApp.send(
          "Comprobante Generado con Exito !",
          "Se procesaron " + jsonData1.cuento + " recorridos.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else if (jsonData1.success == 0) {
        $.NotificationApp.send(
          "Error al Intentar Generar el Comprobante !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      } else if (jsonData1.success == 3) {
        $.NotificationApp.send(
          "Error en el Codigo de Afip del Cliente !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
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
    url: "https://www.caddy.com.ar/SistemaTriangular/Ventas/Procesos/php/funciones.php",
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
            " cargados en el sistema, verifique !"
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
    url: "https://www.caddy.com.ar/SistemaTriangular/Ventas/Procesos/php/crearcliente.php",
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
        $.NotificationApp.send(
          "Cliente " + NombreCliente + " creado con éxito !",
          "Se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        $("#nuevocliente-modal-lg").modal("hide");
      } else {
        $.NotificationApp.send(
          "Error: El cliente no se creo !",
          "Se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
        $("#nuevocliente-modal-lg").modal("hide");
      }
    },
  });
});

function BuscarDireccion() {
  var inputstart = document.getElementById("direccion_nc");
  var autocomplete = new google.maps.places.Autocomplete(inputstart, {
    types: ["geocode", "establishment"],
    componentRestrictions: {
      country: ["AR"],
    },
  });
  autocomplete.addListener("place_changed", function () {
    var place = autocomplete.getPlace();
    if (place.address_components) {
      var components = place.address_components;
      var ciudad = "";
      var provincia = "";
      for (var i = 0, component; (component = components[i]); i++) {
        //             console.log(component);
        if (component.types[0] == "administrative_area_level_1") {
          provincia = component["long_name"];
        }
        if (component.types[0] == "locality") {
          ciudad = component["long_name"];
          document.getElementById("ciudad_nc").value = ciudad;
        }
        if (component.types[0] == "postal_code") {
          document.getElementById("cp_nc").value = component["short_name"];
        }
        if (component.types[0] == "neighborhood") {
          if (component["long_name"] != null) {
            document.getElementById("Barrio_nc").value = component["long_name"];
          } else if (component.types[0] == "administrative_area_level_2") {
            document.getElementById("Barrio_nc").value = component["long_name"];
          }
        }
        if (component.types[0] == "street_number") {
          document.getElementById("Numero_nc").value = component["long_name"];
        }
        if (component.types[0] == "route") {
          document.getElementById("Calle_nc").value = component["long_name"];
        }
      }
    }
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
