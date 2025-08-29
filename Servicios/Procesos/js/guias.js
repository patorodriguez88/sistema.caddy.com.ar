var button_ver = 0;
var state = 0;
var colorestado = "primary";

$(document).ready(function () {
  $("#inputcodigo").val("");
  $("#inputcodigoproveedor").val("");
  $("#inputname").val("");
});
$("#inputname").focus(function () {
  $("#inputname").css("background", "");
  $("#inputcodigo").val("");
  $("#inputcodigo").css("background", "#D5D3D3");
  $("#inputcodigoproveedor").val("");
  $("#inputcodigoproveedor").css("background", "#D5D3D3");
});

$("#inputcodigo").focus(function () {
  $("#inputcodigo").css("background", "");
  $("#inputcodigoproveedor").val("");
  $("#inputcodigoproveedor").css("background", "#D5D3D3");
  $("#inputname").val("");
  $("#inputname").css("background", "#D5D3D3");
});

$("#inputcodigoproveedor").focus(function () {
  $("#inputcodigoproveedor").css("background", "");
  $("#inputcodigo").val("");
  $("#inputcodigo").css("background", "#D5D3D3");
  $("#inputname").val("");
  $("#inputname").css("background", "#D5D3D3");
});

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
            .removeClass("badge-warning")
            .addClass("badge-success");
          $("#" + f).html("Flex");
        } else {
          $("#" + f)
            .removeClass("badge-success")
            .addClass("badge-warning");
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
        2000
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
          $.NotificationApp.send(
            "Exito !",
            "Fecha modificada con éxito !",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
        } else {
          $.NotificationApp.send(
            "Error !",
            "No fue posible actualizar la fecha",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }

        $("#date-modal").modal("hide");
      },
    });
  }
});

function verguia() {
  let id = $("#inputcodigo").val();
  window.open(
    "https://wwwsistemacaddy.com.ar/SistemaTriangular/Servicios/Informes/Remitopdf.php?CS=" +
      id,
    "_blank"
  );
}
function verrotulo() {
  let id = $("#inputcodigo").val();
  window.open(
    "https://www.sistemacaddy.com.ar/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=" +
      id,
    "_blank"
  );
}

// Función para abrir modal, setear código y cargar fotos
function verFotosYSubir(codigo) {
  $("#codigo_foto_actual").val(codigo);
  $("#CodigoSeguimientoHidden").val(codigo);
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
        '<div class="text-danger">Error al cargar las fotos.</div>'
      );
    },
  });
}
function verFotosYSubir(codigo) {
  $("#codigo_foto_actual").val(codigo);
  $("#CodigoSeguimientoHidden").val(codigo);
  $("#codigo_foto_titulo").text(codigo);

  // Mostrar modal
  $("#modal_fotos").modal("show");

  // Cargar fotos existentes
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
        '<div class="text-danger">Error al cargar las fotos.</div>'
      );
    },
  });
}

// Configuración de Dropzone para que recargue galería tras subir
if (typeof Dropzone !== "undefined") {
  Dropzone.options.dropzoneFotos = {
    init: function () {
      this.on("success", function (file) {
        let codigo = $("#codigo_foto_actual").val();
        // Mensaje de éxito
        $("#fotos").before(
          '<div id="mensaje-exito" class="alert alert-success py-2">Foto subida correctamente ✅</div>'
        );

        if (codigo) {
          verFotosYSubir(codigo); // recarga galería
        }
      });
    },
  };
}
// eliminar una foto\
function eliminarFoto(codigo, nombreArchivo, idHtml) {
  Swal.fire({
    title: "¿Eliminar esta foto?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        type: "POST",
        url: "Procesos/php/eliminar_foto.php",
        data: {
          CodigoSeguimiento: codigo,
          NombreArchivo: nombreArchivo,
        },
        success: function (response) {
          try {
            let res = JSON.parse(response);
            if (res.success) {
              $("#" + idHtml).fadeOut(300, function () {
                $(this).remove();
              });

              Swal.fire({
                title: "¡Eliminada!",
                text: "La foto fue eliminada correctamente.",
                icon: "success",
                timer: 2000,
                showConfirmButton: false,
              });
            } else {
              Swal.fire(
                "Error",
                res.error || "No se pudo eliminar la foto.",
                "error"
              );
            }
          } catch (e) {
            console.error("Error al parsear respuesta:", response);
            Swal.fire("Error", "Respuesta inesperada del servidor.", "error");
          }
        },
        error: function () {
          Swal.fire("Error", "Error de conexión con el servidor.", "error");
        },
      });
    }
  });
}
$("#remito").click(function () {
  let name = $("#inputname").val();

  if (name != "") {
    $("#row_search").css("display", "block");
    $("#form_guias").css("display", "none");

    //TABLA PRE BUSQUEDA
    var datatable_search = $("#search_tabla").DataTable({
      paging: false,
      searching: true,
      ajax: {
        url: "../../Funciones/php/tablas.php",
        data: { Search_Tabla: 1, Variable: name },
        type: "post",
      },
      columns: [
        { data: "Fecha" },
        { data: "CodigoSeguimiento" },
        { data: "RazonSocial" },
        { data: "ClienteDestino" },
        { data: "CodigoProveedor" },
        {
          data: "Estado",

          render: function (data, type, row) {
            colorestado = "warning";

            if (row.Estado == "Entregado al Cliente") {
              colorestado = "success";
            } else if (row.Estado == "Devuelto al Cliente") {
              colorestado = "danger";
            } else if (row.Estado == "En Transito") {
              colorestado = "primary";
            } else if (row.Estado == "En Origen") {
              colorestado = "warning";
            } else if (row.Estado == "A Retirar") {
              colorestado = "warning";
            }
            return (
              '<h5><span class="badge bg-' +
              colorestado +
              ' text-white">' +
              row.Estado +
              "</span></h5>"
            );
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
        beforeSend: function () {
          // setting a timeout
          $("#info-alert-modal").modal("show");
          $("#info-alert-modal-title").html("Buscando");
        },
        success: function (response) {
          var jsonData = JSON.parse(response);

          if (jsonData.success == 1) {
            $("#info-alert-modal").modal("hide");
            seguimiento(jsonData.CodigoSeguimiento);
          } else {
            $("#info-alert-modal").modal("hide");
            $.NotificationApp.send(
              "Error !",
              "No existen datos para el codigo del Proveedor " + idProveedor,
              "bottom-right",
              "#FFFFFF",
              "danger"
            );
          }
        },
      });
    }
  }
});

function seguimiento(cs) {
  var id = cs;
  $("#inputcodigo").val(id);

  if (id != "") {
    $("#row_search").css("display", "none");

    $.ajax({
      data: { Seguimiento_Visitas: 1, CodigoSeguimiento: id },
      type: "POST",
      url: "../Funciones/php/tablas.php",
      beforeSend: function () {
        // setting a timeout
        $("#info-alert-modal").modal("show");
        $("#info-alert-modal-title").html("Buscando Visitas");
      },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == 1) {
          $("#info-alert-modal").modal("hide");
          $("#myCenterModalLabel2").html(
            '<h5>Seguimiento de Codigo: <span class="badge bg-' +
              colorestado +
              ' text-white">' +
              id +
              " </span>" +
              ' Visitas: <span class="badge bg-' +
              colorestado +
              ' text-white"> ' +
              jsonData.Visitas +
              " </span></h5>"
          );
          $("#notas").html("Nota Interna: " + jsonData.Notas);
          document.getElementById("modal_seguimiento").style.display = "block";
          document.getElementById("form_guias").style.display = "none";
          $("#pagina").css("display", "block").addClass("active");
          $(".breadcrumb-item active")
            .removeClass("breadcrumb-item active")
            .addClass("breadcrumb-item");
        } else {
          $("#info-alert-modal").modal("hide");
          $.NotificationApp.send(
            "Error !",
            "No existen datos para el codigo " + id,
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
          $.NotificationApp.send(
            "Error",
            "No existen datos para el codigo " + id,
            "bottom-right",
            "danger",
            "Error"
          );
        }
      },
    });

    $.ajax({
      data: { Seguimiento_Modal: 1, CodigoSeguimiento: id },
      type: "POST",
      url: "../Funciones/php/tablas.php",
      success: function (response) {
        var jsonData = JSON.parse(response);

        if (jsonData.data[0].Entregado == 1) {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-success"
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-success"
          );
        } else {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-primary"
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-primary"
          );
        }

        //TRACKER
        $.ajax({
          data: { Tracker: 1, CodigoSeguimiento: id },
          type: "POST",
          url: "Procesos/php/fotos.php",
          success: function (response) {
            $("#tracker").html(response).fadeIn();
          },
        });

        //ORIGEN
        $("#cliente_origen_seguimiento").html(jsonData.data[0].RazonSocial);
        $("#cliente_origen_direcccion_seguimiento").html(
          jsonData.data[0].DomicilioOrigen +
            "<br>" +
            '<li><p class="mb-0"><span class="font-weight-bold mr-2">Telefono:</span>' +
            jsonData.data[0].TelefonoOrigen +
            "</p></li>"
        );
        //DESTINO
        $("#cliente_destino_seguimiento").html(jsonData.data[0].ClienteDestino);
        $("#cliente_destino_direcccion_seguimiento").html(
          jsonData.data[0].DomicilioDestino +
            "<br>" +
            '<li><p class="mb-0"><span class="font-weight-bold mr-2">Telefono:</span>' +
            jsonData.data[0].TelefonoDestino +
            "</p></li>"
        );
        //GUIA
        $("#header_title_guia_seguimiento").html(
          "Información de la Guia " + jsonData.data[0].NumeroComprobante
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
            jsonData.data[0].ComprobanteF + " " + jsonData.data[0].NumeroF
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
        //DEFINO LA VARIABLE ESTADO Y EL COLOR
        var Estado = jsonData.data[0].Estado;
        if (jsonData.data[0].Estado == "Entregado al Cliente") {
          colorestado = "success";
        } else if (jsonData.data[0].Estado == "Devuelto al Cliente") {
          colorestado = "danger";
        } else if (jsonData.data[0].Estado == "En Transito") {
          colorestado = "primary";
        } else if (jsonData.data[0].Estado == "En Origen") {
          colorestado = "warning";
        } else if (jsonData.data[0].Estado == "A Retirar") {
          colorestado = "warning";
        } else if (jsonData.data[0].Estado == "Cargado en Hoja de Ruta") {
          colorestado = "primary";
        }

        //COMPRUEBO ULTIMO ESTADO
        $.ajax({
          data: { Compruebo: 1, CodigoSeguimiento: id },
          type: "POST",
          url: "../Funciones/php/tablas.php",
          success: function (response) {
            var jsonDataEstado = JSON.parse(response);
            EstadoSeguimiento = jsonDataEstado.data;

            state = jsonDataEstado.data;

            if (jsonDataEstado.data != Estado) {
              $("#alert").css("display", "inline-block");
            } else {
              $("#alert").css("display", "none");
            }
          },
        });

        if (jsonData.Flex == 1) {
          var flex = "Flex";
          var flex_color = "success";
        } else {
          flex = "Simple";
          flex_color = "warning";
        }
        let fechaFormateada = jsonData.data[0].FechaPrometida
          ? jsonData.data[0].FechaPrometida.split("-").reverse().join("/")
          : "";
        $("#info_guia_seguimiento").html(
          '<p class="mb-1"><span style="cursor:pointer" id="' +
            jsonData.data[0].id +
            '" onclick="changeservice(this.id)" class="badge badge-' +
            flex_color +
            '">' +
            flex +
            "</span>" +
            '<p class="mb-1"><b id="fecha_transclientes">Fecha : ' +
            jsonData.data[0].Fecha.split("-").reverse().join("/") +
            '</b> <i id="alert_date" style="" class="mdi mdi-18px mdi-calendar-refresh text-warning ml-2" ></i>  </p>' +
            '</b><p class"mb-1"><b>Fecha Prometida: ' +
            fechaFormateada +
            "</b></p>" +
            '<p class="mb-1"><b id="estado_transclientes">Estado Trans Clientes : ' +
            jsonData.data[0].Estado +
            '</b><i id="alert" style="display:none" class="mdi mdi-18px mdi-spin mdi-alert-octagon text-danger" onclick="alerta_modal();"></i>  </p>' +
            '<p class="mb-1"><b>Codigo Seguimiento : </b>' +
            id +
            "</p>" +
            '<p class="mb-1"><b>Numero de Guia : </b>' +
            jsonData.data[0].NumeroComprobante +
            "</p>" +
            '<p class="mb-1"><b>Cantidad : </b>' +
            jsonData.data[0].Cantidad +
            "</p>" +
            '<p class="mb-1"><b>Entregar En : </b>' +
            jsonData.data[0].EntregaEn +
            "</p>" +
            '<p class="mb-1"><b>Cod. Proveedor : </b>' +
            jsonData.data[0].CodigoProveedor +
            "</p>" +
            '<p class="mb-1"><b>Valor Declarado : </b> $ ' +
            jsonData.data[0].ValorDeclarado +
            "</p>" +
            '<p class="mb-1"><b>Recorrido : </b>' +
            jsonData.data[0].Recorrido +
            "</p>" +
            '<p class="mb-1"><b>Transportista : </b>' +
            jsonData.data[0].Transportista +
            "</p>" +
            '<p class="mb-1"><b>Kilometros : </b>' +
            jsonData.data[0].Kilometros +
            "</p>" +
            //'<p class="mb-1"><b>Forma de Pago : </b>'+ jsonData.data[0].FormaDePago + '  <i class="mdi mdi-reload text-warning"/> </i></p>'+

            '<div class="custom-control custom-switch">' +
            '<input type="checkbox" class="custom-control-input" id="formadepago_c" ' +
            formadepagochecked +
            ' value="' +
            jsonData.data[0].FormaDePago +
            '" ' +
            dis +
            ">" +
            '<label class="custom-control-label" for="formadepago_c"><p class="mb-1"><b id="formadepago_b">Forma De Pago : ' +
            formadepago +
            "</b></p></label>" +
            "</div>" +
            '<div class="custom-control custom-switch">' +
            '<input type="checkbox" class="custom-control-input" id="cobrarenvio_c" ' +
            cobrarenviochecked +
            ' value="' +
            jsonData.data[0].CobrarEnvio +
            '" ' +
            dis +
            ">" +
            '<label class="custom-control-label" for="cobrarenvio_c"><p class="mb-1"><b id="cobrarenvio_b">Cobrar Envio : ' +
            cobrarenvio +
            "</b></p></label>" +
            "</div>" +
            '<div class="custom-control custom-switch">' +
            '<input type="checkbox" class="custom-control-input" id="cobrarcaddy_c" ' +
            cobrarcaddychecked +
            ' value="' +
            jsonData.data[0].CobrarCaddy +
            '" ' +
            dis +
            ">" +
            '<label class="custom-control-label" for="cobrarcaddy_c"><p class="mb-1"><b id="cobrarcaddy_b">Cobrar Caddy : ' +
            cobrarcaddy +
            "</b></p></label>" +
            "</div>" +
            '<div class="custom-control custom-switch">' +
            '<input type="checkbox" class="custom-control-input" id="estadohdr_c" ' +
            estadochecked +
            ' value="' +
            jsonData[1].Estado +
            '" ' +
            dis +
            ">" +
            '<label class="custom-control-label" for="estadohdr_c" ><p class="mb-1"><b id="estadohdr_b">Estado en HDR : ' +
            jsonData[1].Estado +
            "</b></p></label>" +
            "</div>" +
            '<p class="mb-1"><b>Observaciones : </b>' +
            jsonData.data[0].Observaciones +
            "</p>"
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

        $("#btn_corregir").click(function () {
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
                $("#estado_transclientes").html(
                  "Estado Trans Clientes : " + EstadoSeguimiento
                );
                $("#alert").css("display", "none");
              }
            },
          });
        });

        $("#cambiar-modal-close").click(function () {
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
                      ")"
                  );
                } else {
                  Pagador = jsonData.data[0].ClienteDestino;
                  NoPagador = jsonData.data[0].RazonSocial;
                  $("#forma_de_pago_texto_1").html(
                    "Pagador Actual (" +
                      Pagador +
                      ") => Nuevo Pagador (" +
                      NoPagador +
                      ")"
                  );
                }
              }
            },
          });

          $("#forma_de_pago_texto").html("Estas por cambiar de Pagador...");
        });

        $("#cambiar-modal-ok").click(function () {
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
                "Actualizando Forma de Pago..."
              );
            },
            success: function (response) {
              var jsonData = JSON.parse(response);
              console.log("fdp", jsonData);
              if (jsonData.success == 1) {
                if (formadepago_valor == "Origen") {
                  $("#formadepago_c").val("Origen");
                  $("#formadepago_b").html("Forma De Pago : Origen");
                } else {
                  $("#formadepago_c").val("Destino");
                  $("#formadepago_b").html("Forma De Pago : Destino");
                }
                $("#info-alert-modal").modal("hide");
                $.NotificationApp.send(
                  "Exito",
                  "Modificamos Forma De Pago",
                  "success"
                );
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
                if (cobrarenvio_valor == 1) {
                  $("#cobrarcaddy_c").val(cobrarcaddy_valor);
                  $("#cobrarcaddy_b").html("Cobrar Caddy : Si");
                } else {
                  $("#cobrarcaddy_b").html("Cobrar Caddy : No");
                }
                $("#info-alert-modal").modal("hide");
                $.NotificationApp.send(
                  "Exito",
                  "Modificamos Cobrar Caddy",
                  "success"
                );
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
                "Este paquete ya fue devuelto al cliente, realemnte lo incluiras en una hoja de ruta?"
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
                  if (estadohdr_valor == "Abierto") {
                    $("#estadohdr_c").val("Abierto");
                    $("#estadohdr_b").html("Estado en HDR : Abierto");
                  } else {
                    $("#estadohdr_c").val("Cerrado");
                    $("#estadohdr_b").html("Estado en HDR : Cerrado");
                  }

                  $.NotificationApp.send(
                    "Éxito",
                    "Modificamos el estado en Hoja de Ruta",
                    "success"
                  );
                  // actualizar DOM si es necesario
                }
              } catch (e) {
                console.error("Error JSON:", e, response);
              }
            },
            complete: function () {
              // $("#info-alert-modal").modal("hide");
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
                if (cobrarenvio_valor == 1) {
                  $("#cobrarenvio_c").val(cobrarenvio_valor);
                  $("#cobrarenvio_b").html("Cobrar Envio : Si");
                } else {
                  $("#cobrarenvio_b").html("Cobrar Envio : No");
                }
                $("#info-alert-modal").modal("hide");
                $.NotificationApp.send(
                  "Exito",
                  "Modificamos Cobrar Envio",
                  "success"
                );
              }
            },
          });
        });

        //TABLA AFORO TRANSACCIONES CLIENTE (TRANSCLIENTES)
        var datatable_aforo_trans = $("#aforo_tabla_trans").DataTable({
          paging: false,
          searching: false,
          ajax: {
            url: "../Funciones/php/tablas.php",
            data: { Aforo_Tabla_Trans: 1, CodigoSeguimiento: id },
            type: "post",
          },
          columns: [
            { data: "TipoDeComprobante" },
            { data: "NumeroComprobante" },
            { data: "Debe" },
          ],
        });

        //TABLA AFORO
        var datatable_aforo = $("#aforo_tabla").DataTable({
          paging: false,
          searching: false,
          ajax: {
            url: "../Funciones/php/tablas.php",
            data: { Aforo_Tabla: 1, CodigoSeguimiento: id },
            type: "post",
          },
          columns: [
            { data: "FechaPedido" },
            { data: "Codigo" },
            { data: "Titulo" },
            { data: "Cantidad" },
            { data: "Precio" },
          ],
        });

        //TABLA SEGUIMIENTO
        var datatable_seguimiento = $("#seguimiento_tabla").DataTable({
          paging: false,
          searching: false,
          ajax: {
            url: "../Funciones/php/tablas.php",
            data: { Seguimiento_Tabla: 1, CodigoSeguimiento: id },
            type: "post",
          },
          columns: [
            { data: "Fecha" },
            { data: "Hora" },
            { data: "Usuario" },
            { data: "Observaciones" },
            { data: "Estado" },
            { data: "Recorrido" },
            { data: "NumerodeOrden" },
            {
              data: "id",
              render: function (data, type, row) {
                return `<i onclick="ver_seguimiento(${row.id})" class="mdi mdi-trash-can text-danger mdi-18px ms-2" id="${row.id}" style="cursor:pointer;"></i>`;
              },
            },
          ],
        });

        //TABLA WEBHOOK
        var datatable_webhook = $("#webhook_tabla").DataTable({
          paging: false,
          searching: false,
          ajax: {
            url: "../Funciones/php/tablas.php",
            data: { Webhook: 1, CodigoSeguimiento: id },
            type: "post",
          },
          columns: [
            { data: "Fecha" },
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
          data: { MuestroQuicks: 1, CodigoSeguimiento: id },
          type: "POST",
          url: "Procesos/php/fotos.php",
          success: function (response) {
            $("#info-alert-modal").modal("hide");
            $("#quick").html(response).fadeIn();
          },
        });

        $("#info-alert-modal").modal("hide");
      },
      error: function (err) {
        console.log("error", err);
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
  let modal = new bootstrap.Modal(
    document.getElementById("modal_confirmar_eliminacion")
  );
  modal.show();
}

$("#btn_confirmar_eliminacion").click(function () {
  if (!id_seguimiento_a_eliminar) return;

  $("#id_seguimiento_a_eliminar").html(id_seguimiento_a_eliminar);

  $.ajax({
    data: { EliminarSeguimiento: 1, id: id_seguimiento_a_eliminar },
    type: "POST",
    url: "../../Funciones/php/tablas.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Listo!",
          "Seguimiento Eliminado",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        var datatable_seguimiento = $("#seguimiento_tabla").DataTable();
        datatable_seguimiento.ajax.reload();
      } else {
        $.NotificationApp.send(
          "Error!",
          "No se pudo eliminar el seguimiento",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
});

$("#enter_registration").click(function () {
  const newLocal = "show";
  $("#enter_registration_seguimiento-modal").modal(newLocal);

  // Inicializar el select2
  $("#enter_registration_user").select2();
});

$("#enter_registration_state").change(function () {
  if ($("#enter_registration_state").val() == "Entregado al Cliente") {
    $("#enter_registration_user_id").css("display", "block");
    // Hacer la solicitud Ajax para obtener usuarios
    $.ajax({
      data: { usuarios_registration: 1 },
      url: "Procesos/php/funciones.php",
      type: "POST",
      dataType: "json",
      success: function (data) {
        // Agregar opciones al select
        var $optgroup = $("#enter_registration_user optgroup");
        $.each(data, function (index, user) {
          var $option = $("<option>", { value: user.id, text: user.text });
          $optgroup.append($option);
        });

        // Actualizar el select2 después de agregar opciones
        $("#enter_registration_user").trigger("change");
      },
      error: function (error) {
        console.error("Error al obtener usuarios:", error);
      },
    });
  } else {
    $("#enter_registration_user_id").css("display", "none");
  }
});

//AGREGAR REGISTROS EN TABLA SEGUIMIENTO
$("#enter_registration_save").click(function () {
  let state = $("#enter_registration_state").val();
  let id = $("#inputcodigo").val();
  let obs = $("#enter_registration_obs").val();
  let user = $("#enter_registration_user").val();
  console.log("usuario", $("#enter_registration_user").val());
  $.ajax({
    data: {
      enter_registration: 1,
      CodigoSeguimiento: id,
      state: state,
      obs: obs,
      user: user,
    },
    type: "POST",
    url: "Procesos/php/funciones.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        $("#estado_transclientes").html("Estado Trans Clientes : " + state);
        $("#enter_registration_seguimiento-modal").modal("hide");
        var datatable_seguimiento = $("#seguimiento_tabla").DataTable();
        datatable_seguimiento.ajax.reload();
        console.log("hdr", jsonData.estadohdr);
        if (jsonData.estadohdr == "Abierto") {
          $("#estadohdr_c").val("Abierto");
          $("#estadohdr_b").html("Estado en HDR : Abierto");
        } else {
          $("#estadohdr_c").val("Cerrado");
          $("#estadohdr_b").html("Estado en HDR : Cerrado");
        }

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
              jsonData.new
            );
          },
        });
      }
    },
  });
});
