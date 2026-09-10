var button_ver = 0;
var state = 0;
var colorestado = "primary";

// Formatea 'YYYY-MM-DD' (con o sin hora) como 'd.m.yyyy'. Solo para mostrar;
// para ordenar/filtrar DataTables sigue usando el valor crudo.
function fechaDMY(data, type) {
  if (type !== "display" || !data) return data;
  var m = String(data).match(/^(\d{4})-(\d{2})-(\d{2})/);
  return m ? parseInt(m[3], 10) + "." + parseInt(m[2], 10) + "." + m[1] : data;
}

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
            .removeClass("bg-warning")
            .addClass("bg-success");
          $("#" + f).html("Flex");
        } else {
          $("#" + f)
            .removeClass("bg-success")
            .addClass("bg-warning");
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
        2000,
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
          toast("success", "Exito !", "Fecha modificada con éxito !");
        } else {
          toast("error", "Error !", "No fue posible actualizar la fecha");
        }

        $("#date-modal").modal("hide");
      },
    });
  }
});

function verguia() {
  let id = $("#inputcodigo").val();
  window.open(
    "/SistemaTriangular/Servicios/Informes/Remitopdf.php?CS=" +
      id,
    "_blank",
  );
}
function verrotulo() {
  let id = $("#inputcodigo").val();
  window.open(
    "/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=" +
      id,
    "_blank",
  );
}

// Volver del detalle de seguimiento al buscador.
function cerrarSeguimiento() {
  document.getElementById("modal_seguimiento").style.display = "none";
  document.getElementById("form_guias").style.display = "";
  try {
    $("#seguimiento_tabla").DataTable().destroy();
  } catch (e) {}
}

// ============================================================
// ROTULO 6x2 cm - impresion directa en Zebra via Browser Print
// ============================================================
var zebraDevice = null;

function zebraSetup() {
  if (typeof BrowserPrint === "undefined") return;
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      zebraDevice = device;
    },
    function () {
      /* sin impresora por defecto: se avisa al abrir el modal */
    },
  );
}
$(function () {
  zebraSetup();
});

function zebraErr(msg) {
  $("#rotulo_zebra_estado").removeClass("text-muted text-success").addClass("text-danger").html(msg);
}

// Corta un texto a n caracteres para que entre en la etiqueta.
function _rec(s, n) {
  s = (s == null ? "" : String(s)).trim();
  return s.length > n ? s.slice(0, n - 1) + "…" : s;
}

// Logo Caddy (iso) - GFA, mismo que la etiqueta de colecta de WePoint.
var CADDY_LOGO_ZPL =
  "^FO15,15^GFA,1675,1675,25,,:::::::::::::M0CJ04J01,L07F8003FCI0FE,L0FFC007FE003FF,K01FFE00IF007FF8,K03FFE01IF007FFC,K03IF01IF80IFC,K03IF81IFC0IFE,K07IF83IFC0IFE,K07IFC1IFE0IFE,K03IFE1JF0F01E,K03IFE1JF0E01C,K03JF1JF8703C,K01JF0JF87C78,L0JF87IFC3FF,L0JFC7IFE0FE,L07IFC3IFE01V07,L07IFE3JFX0F,L03IFE1JFX0F,L01JF0JF8W07J0F,L01JF8JFCgH0F,M0JF87IFCgH0F,M0JFC7IFEI0E3C38FC3FE07F8F73F3FE,M07IFC3IFEI0E3E79FF3FF0FFCF7FFBFF,M03IFE1JFI0E3E7BFF3FF9FFEF7FFBFE,M03IFE1JFI0F7E77C73C79E1EF7C78F,M01IFE0JFI0F7E7787BC3DE1E77878F,N0IFE07IFI077FF7FFBC3DC0FF7838F,N0IFE07IFI07F7E7FFBC3DC0FF7838F,N07FFE03IFI07E7E7803C3DE1EF7838F,N07FFE03IFI03E7E7C13C79E1EF7838F,N03FFC01FFEI03E3C3FF3FF9FFEF7878FE,N01FF800FFCI03E3C1FFBFF0FFCF78787F,O0FFI07F8I01C3C0FF3FE07F0778383F,U08Q03C,gM03C,:::,::::::::::::::^FS";

// Datos del servicio para el rotulo (de window.seguimientoData / seguimientoHdr).
function _rotuloDatos() {
  var d = window.seguimientoData || {};
  var hdr = window.seguimientoHdr || {};
  var cs = (window.seguimientoCS || $("#inputcodigo").val() || "").trim();
  var hoy = new Date();
  var p2 = function (n) { return String(n).padStart(2, "0"); };
  return {
    cs: cs,
    cliente: _rec(d.ClienteDestino || "", 26),
    domicilio: _rec(d.DomicilioDestino || "", 30),
    origen: _rec(d.RazonSocial || "", 26),
    recorrido: (d.Recorrido || "").toString().trim(),
    posicion: (hdr.Posicion || hdr.Posicion_retiro || "").toString().trim(),
    cant: (d.Cantidad || "1").toString().trim(),
    fecha: p2(hoy.getDate()) + "/" + p2(hoy.getMonth() + 1) + "/" + hoy.getFullYear(),
  };
}

// Rótulo 6,5 x 3,2 cm @ 203 dpi (520 x 256 pts) - mismo formato que la etiqueta
// de colecta: logo + QR en columna izquierda, texto a la derecha desde X=200.
function _rotuloZPL(x) {
  return (
    "^XA^PW520^LL256^LH0,0^CI28" +
    CADDY_LOGO_ZPL +
    "^FO200,10^A0N,20,20^FD" + x.cliente + "^FS" +
    "^FO200,35^A0N,18,18^FD" + x.domicilio + "^FS" +
    "^FO200,57^A0N,18,18^FDId: " + x.cs + "^FS" +
    "^FO200,79^A0N,18,18^FDOrigen: " + x.origen + "^FS" +
    "^FO200,101^A0N,18,18^FDBulto: 1/" + x.cant + "^FS" +
    "^FO200,123^A0N,18,18^FDFecha: " + x.fecha + "^FS" +
    "^FO200,148^A0N,30,30^FDRec: " + (x.recorrido || "-") + "^FS" +
    "^FO200,185^A0N,26,26^FDPos: " + (x.posicion || "-") + "^FS" +
    "^FO30,74^BQN,2,7^FDQA," + x.cs + "^FS" +
    "^XZ"
  );
}

function _rotuloPreviewHTML(x) {
  var esc = function (s) {
    return $("<div>").text(s == null ? "" : s).html();
  };
  var qr = "/SistemaTriangular/Funciones/php/qr.php?s=4&d=" + encodeURIComponent(x.cs);
  return (
    '<img src="' + qr + '" alt="QR" style="position:absolute;left:8px;top:36px;width:84px;height:84px;image-rendering:pixelated;">' +
    '<div style="margin-left:100px;">' +
    '<div style="font-weight:700;font-size:12px;">' + esc(x.cliente) + "</div>" +
    "<div>" + esc(x.domicilio) + "</div>" +
    "<div>Id: " + esc(x.cs) + "</div>" +
    "<div>Origen: " + esc(x.origen) + "</div>" +
    "<div>Bulto: 1/" + esc(x.cant) + "</div>" +
    "<div>Fecha: " + esc(x.fecha) + "</div>" +
    '<div style="font-weight:700;font-size:14px;margin-top:2px;">Rec: ' + esc(x.recorrido || "-") + "</div>" +
    '<div style="font-weight:700;font-size:12px;">Pos: ' + esc(x.posicion || "-") + "</div>" +
    "</div>"
  );
}

function abrirRotuloZebra() {
  var x = _rotuloDatos();
  if (!x.cs) {
    toast("error", "Rótulo", "No hay un código de seguimiento cargado.");
    return;
  }
  $("#rotulo_preview").html(_rotuloPreviewHTML(x));

  if (typeof BrowserPrint === "undefined") {
    zebraErr(
      "No se detectó Zebra Browser Print en esta PC. Instalá la app 'Zebra Browser Print' o usá el botón 'Etiqueta (PDF)'.",
    );
    $("#btn_rotulo_zebra_imprimir").prop("disabled", true);
  } else if (!zebraDevice) {
    // reintento de descubrimiento por si la impresora se conectó después
    zebraSetup();
    setTimeout(function () {
      if (zebraDevice) {
        $("#rotulo_zebra_estado").removeClass("text-danger").addClass("text-success").html("Impresora: " + zebraDevice.name);
        $("#btn_rotulo_zebra_imprimir").prop("disabled", false);
      } else {
        zebraErr("No se encontró ninguna impresora Zebra. Revisá que esté encendida y en Browser Print.");
        $("#btn_rotulo_zebra_imprimir").prop("disabled", true);
      }
    }, 800);
    $("#rotulo_zebra_estado").removeClass("text-danger text-success").addClass("text-muted").html("Buscando impresora…");
  } else {
    $("#rotulo_zebra_estado").removeClass("text-danger").addClass("text-success").html("Impresora: " + zebraDevice.name);
    $("#btn_rotulo_zebra_imprimir").prop("disabled", false);
  }

  $("#modal_rotulo_zebra").modal("show");
}

$(document).on("click", "#btn_rotulo_zebra_imprimir", function () {
  if (!zebraDevice) {
    zebraErr("No hay impresora Zebra disponible.");
    return;
  }
  var zpl = _rotuloZPL(_rotuloDatos());
  var $b = $(this).prop("disabled", true);
  zebraDevice.send(
    zpl,
    function () {
      $("#modal_rotulo_zebra").modal("hide");
      $b.prop("disabled", false);
      toast("success", "Rótulo", "Enviado a la impresora.");
    },
    function (err) {
      $b.prop("disabled", false);
      zebraErr("Error al imprimir: " + err);
    },
  );
});

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
        '<div class="text-danger">Error al cargar las fotos.</div>',
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
        '<div class="text-danger">Error al cargar las fotos.</div>',
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
          '<div id="mensaje-exito" class="alert alert-success py-2">Foto subida correctamente ✅</div>',
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
                "error",
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
function cleanValue(v) {
  return (v || "").toString().trim();
}

function showError(msg) {
  Swal.fire({
    icon: "error",
    title: "Atención",
    text: msg,
    confirmButtonText: "OK",
  });
}
function showLoading(title, body) {
  // Ojo: en tu HTML no existe #info-alert-modal-title, existe h4 fijo.
  // Así que actualizamos el body y dejamos el h4 como "Actualizando Información".
  $("#info-alert-body").text(body || "No cierres esta ventana.");
  $("#info-alert-modal").modal("show");
}

function hideLoading() {
  $("#info-alert-modal").modal("hide");
}

function safeJsonParse(response) {
  try {
    if (typeof response === "object") return response;
    return JSON.parse(response);
  } catch (e) {
    return null;
  }
}

$("#remito").click(function () {
  let name = $("#inputname").val();
  let id = cleanValue($("#inputcodigo").val());
  let idProveedor = cleanValue($("#inputcodigoproveedor").val());

  // ✅ Si los tres están vacíos, no avanzo
  if (!name && !id && !idProveedor) {
    showError(
      "Ingresá un Código de Seguimiento, Código de Proveedor o el Nombre del Cliente.",
    );
    return;
  }

  if (name != "") {
    $("#row_search").css("display", "block");
    $("#form_guias").css("display", "none");

    //TABLA PRE BUSQUEDA
    var datatable_search = $("#search_tabla").DataTable({
      paging: false,
      searching: true,
      ajax: {
        // guias.php vive en /SistemaTriangular/Servicios/, asi que la ruta a
        // Funciones/php/ es "../Funciones/...". Con "../../" se iba a /Funciones/
        // (404) y la busqueda por nombre nunca devolvia nada. Mismo patron que
        // el resto de los ajax de este archivo.
        url: "../Funciones/php/tablas.php",
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
        dataType: "text",
        timeout: 20000, // ✅ evita que quede colgado eterno
        beforeSend: function () {
          showLoading("Buscando", "Buscando datos del proveedor...");
        },
        success: function (response) {
          const jsonData = safeJsonParse(response);

          if (!jsonData) {
            showError(
              "Respuesta inválida del servidor. Revisá logs / PHP errors.",
            );
            return;
          }

          if (jsonData.success == 1 && jsonData.CodigoSeguimiento) {
            seguimiento(jsonData.CodigoSeguimiento);
          } else {
            showError(
              "No existen datos para el código del proveedor: " + idProveedor,
            );
          }
        },
        error: function (xhr, status) {
          const detail =
            xhr && xhr.responseText ? xhr.responseText.substring(0, 300) : "";
          showError(
            "No se pudo consultar. (" +
              status +
              ") " +
              (detail ? "Detalle: " + detail : ""),
          );
        },
        complete: function () {
          hideLoading(); // ✅ se cierra SIEMPRE
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
              " </span></h5>",
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
          toast("error", "Error !", "No existen datos para el codigo " + id);
          toast("error", "Error", "No existen datos para el codigo " + id);
        }
      },
    });

    $.ajax({
      data: { Seguimiento_Modal: 1, CodigoSeguimiento: id },
      type: "POST",
      url: "../Funciones/php/tablas.php",
      success: function (response) {
        var jsonData = JSON.parse(response);

        // guardo los datos del servicio para el Rotulo (Zebra)
        window.seguimientoData = jsonData.data[0];
        window.seguimientoHdr = jsonData[1] || {};
        window.seguimientoCS = id;

        if (jsonData.data[0].Entregado == 1) {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-success",
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-success",
          );
        } else {
          $("#modal_seguimiento_header").prop(
            "class",
            "modal-header modal-colored-header bg-primary",
          );
          $("#modal_seguimiento_content").prop(
            "class",
            "modal-content bg-primary",
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
            "</p></li>",
        );
        //DESTINO
        $("#cliente_destino_seguimiento").html(jsonData.data[0].ClienteDestino);
        $("#cliente_destino_direcccion_seguimiento").html(
          jsonData.data[0].DomicilioDestino +
            "<br>" +
            '<li><p class="mb-0"><span class="font-weight-bold mr-2">Telefono:</span>' +
            jsonData.data[0].TelefonoDestino +
            "</p></li>",
        );
        //GUIA
        $("#header_title_guia_seguimiento").html(
          "Información de la Guia " + jsonData.data[0].NumeroComprobante,
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
            jsonData.data[0].ComprobanteF + " " + jsonData.data[0].NumeroF,
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

            '<div class="form-check form-switch mb-1">' +
            '<input type="checkbox" role="switch" class="form-check-input" id="formadepago_c" ' +
            formadepagochecked +
            ' value="' +
            jsonData.data[0].FormaDePago +
            '" ' +
            dis +
            ">" +
            '<label class="form-check-label" for="formadepago_c"><p class="mb-1"><b id="formadepago_b">Forma De Pago : ' +
            formadepago +
            "</b></p></label>" +
            "</div>" +
            '<div class="form-check form-switch mb-1">' +
            '<input type="checkbox" role="switch" class="form-check-input" id="cobrarenvio_c" ' +
            cobrarenviochecked +
            ' value="' +
            jsonData.data[0].CobrarEnvio +
            '" ' +
            dis +
            ">" +
            '<label class="form-check-label" for="cobrarenvio_c"><p class="mb-1"><b id="cobrarenvio_b">Cobrar Envio : ' +
            cobrarenvio +
            "</b></p></label>" +
            "</div>" +
            '<div class="form-check form-switch mb-1">' +
            '<input type="checkbox" role="switch" class="form-check-input" id="cobrarcaddy_c" ' +
            cobrarcaddychecked +
            ' value="' +
            jsonData.data[0].CobrarCaddy +
            '" ' +
            dis +
            ">" +
            '<label class="form-check-label" for="cobrarcaddy_c"><p class="mb-1"><b id="cobrarcaddy_b">Cobrar Caddy : ' +
            cobrarcaddy +
            "</b></p></label>" +
            "</div>" +
            '<div class="form-check form-switch mb-1">' +
            '<input type="checkbox" role="switch" class="form-check-input" id="estadohdr_c" ' +
            estadochecked +
            ' value="' +
            jsonData[1].Estado +
            '" ' +
            dis +
            ">" +
            '<label class="form-check-label" for="estadohdr_c" ><p class="mb-1"><b id="estadohdr_b">Estado en HDR : ' +
            jsonData[1].Estado +
            "</b></p></label>" +
            "</div>" +
            '<p class="mb-1"><b>Observaciones : </b>' +
            jsonData.data[0].Observaciones +
            "</p>",
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
                  "Estado Trans Clientes : " + EstadoSeguimiento,
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
                      ")",
                  );
                } else {
                  Pagador = jsonData.data[0].ClienteDestino;
                  NoPagador = jsonData.data[0].RazonSocial;
                  $("#forma_de_pago_texto_1").html(
                    "Pagador Actual (" +
                      Pagador +
                      ") => Nuevo Pagador (" +
                      NoPagador +
                      ")",
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
                "Actualizando Forma de Pago...",
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
                toast("success", "Exito", "Modificamos Forma De Pago");
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
                toast("success", "Exito", "Modificamos Cobrar Caddy");
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
                "Este paquete ya fue devuelto al cliente, realemnte lo incluiras en una hoja de ruta?",
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

                  toast("success", "Éxito", "Modificamos el estado en Hoja de Ruta");
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
                toast("success", "Exito", "Modificamos Cobrar Envio");
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
            { data: "Fecha", render: fechaDMY },
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
          // Si el envío no tiene webhooks, no ensuciamos la pantalla: se oculta
          // toda la card. Se muestra sólo cuando hay al menos una notificación.
          drawCallback: function (settings) {
            var hay = settings.json && settings.json.data && settings.json.data.length > 0;
            $("#webhook_card").toggle(!!hay);
          },
          columns: [
            { data: "Fecha", render: fechaDMY },
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
    document.getElementById("modal_confirmar_eliminacion"),
  );
  modal.show();
}

$("#btn_confirmar_eliminacion").click(function () {
  if (!id_seguimiento_a_eliminar) return;

  $("#id_seguimiento_a_eliminar").html(id_seguimiento_a_eliminar);

  $.ajax({
    data: { EliminarSeguimiento: 1, id: id_seguimiento_a_eliminar },
    type: "POST",
    // guias.php vive en /SistemaTriangular/Servicios/, asi que la ruta a
    // Funciones/php/ es "../Funciones/..." (con "../../" se iba a /Funciones/,
    // fuera de SistemaTriangular, y la request daba 404 -> el borrado no
    // hacia nada porque JSON.parse tiraba sobre el HTML del error).
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        toast("success", "Listo!", "Seguimiento Eliminado");
        var datatable_seguimiento = $("#seguimiento_tabla").DataTable();
        datatable_seguimiento.ajax.reload();
      } else {
        toast("error", "Error!", "No se pudo eliminar el seguimiento");
      }
    },
    error: function () {
      toast("error", "Error!", "No se pudo eliminar el seguimiento");
    },
  });
});

$("#enter_registration").click(function () {
  const newLocal = "show";
  $("#enter_registration_seguimiento-modal").modal(newLocal);
});

// Los selects de Estado y Usuario se auto-inicializan como select2 al cargar
// la pagina (app.js, [data-toggle="select2"]), sin dropdownParent. Select2
// por defecto cuelga su desplegable (con el buscador) de <body>, AFUERA del
// modal - el focus-trap de Bootstrap 5 (que fuerza el foco de vuelta al
// modal si detecta que se va a un elemento que no es descendiente suyo) le
// saca el foco al buscador apenas se lo toca: la lista se ve bien, pero no
// se puede escribir para filtrar. Se reinicializan apuntando el dropdown al
// propio modal cada vez que se abre.
$("#enter_registration_seguimiento-modal").on("shown.bs.modal", function () {
  $(this)
    .find("select.select2")
    .each(function () {
      const $sel = $(this);
      if ($sel.data("select2")) {
        $sel.select2("destroy");
      }
      $sel.select2({ dropdownParent: $("#enter_registration_seguimiento-modal"), width: "100%" });
    });
});

$("#enter_registration_state").change(function () {
  const st = $("#enter_registration_state").val();
  const conRepartidor = st == "Entregado al Cliente" || st == "No se pudo entregar";

  if (conRepartidor) {
    $("#enter_registration_user_id").css("display", "block");
    $("#enter_registration_datetime").css("display", "flex");
    // Prellenar fecha/hora con ahora (el operador ajusta si el movimiento fue antes)
    if (!$("#fecha_entrega").val()) {
      const n = new Date();
      const p = (x) => String(x).padStart(2, "0");
      $("#fecha_entrega").val(`${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`);
      $("#hora_entrega").val(`${p(n.getHours())}:${p(n.getMinutes())}`);
    }
    // Cargar usuarios sólo una vez
    if ($("#enter_registration_user optgroup option").length === 0) {
      $.ajax({
        data: { usuarios_registration: 1 },
        url: "Procesos/php/funciones.php",
        type: "POST",
        dataType: "json",
        success: function (data) {
          var $optgroup = $("#enter_registration_user optgroup");
          $.each(data, function (index, user) {
            $optgroup.append($("<option>", { value: user.id, text: user.text }));
          });
          $("#enter_registration_user").trigger("change");
        },
        error: function (error) {
          console.error("Error al obtener usuarios:", error);
        },
      });
    }
  } else {
    $("#enter_registration_user_id").css("display", "none");
    $("#enter_registration_datetime").css("display", "none");
  }
});

//AGREGAR REGISTROS EN TABLA SEGUIMIENTO
$("#enter_registration_save").click(function () {
  let state = $("#enter_registration_state").val();
  let id = $("#inputcodigo").val();
  let obs = $("#enter_registration_obs").val();
  let user = $("#enter_registration_user").val();
  let fecha_entrega = $("#fecha_entrega").val();
  let hora_entrega = $("#hora_entrega").val();
  const conRepartidor = state == "Entregado al Cliente" || state == "No se pudo entregar";
  if (conRepartidor && !user) {
    toast("error", "Falta el repartidor", "Elegí el repartidor titular del movimiento.");
    return;
  }
  $.ajax({
    data: {
      enter_registration: 1,
      CodigoSeguimiento: id,
      state: state,
      obs: obs,
      user: user,
      fecha_entrega: fecha_entrega,
      hora_entrega: hora_entrega,
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
              jsonData.new,
            );
          },
        });
      }
    },
  });
});
