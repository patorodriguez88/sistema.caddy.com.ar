function closeTrackingPanel() {
  const panel = document.getElementById("modal_seguimiento");
  const backdrop = document.getElementById("tracking-panel-backdrop");

  if (!panel) {
    return;
  }

  panel.classList.remove("is-open");
  panel.setAttribute("aria-hidden", "true");
  panel.style.transform = "translateX(100%)";
  document.body.classList.remove("tracking-panel-open");
  if (backdrop) {
    backdrop.classList.remove("is-visible");
    backdrop.style.opacity = "0";
    window.setTimeout(() => backdrop.remove(), 280);
  }
}

function openTrackingPanel(id) {
  const panel = document.getElementById("modal_seguimiento");
  if (!panel || !id) {
    return;
  }

  ensureTrackingPanelStyles();
  panel.classList.add("tracking-panel");
  panel.classList.remove("modal", "fade");

  let backdrop = document.getElementById("tracking-panel-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.id = "tracking-panel-backdrop";
    document.body.appendChild(backdrop);
    backdrop.addEventListener("click", closeTrackingPanel);
  }

  panel.classList.add("is-open");
  panel.setAttribute("aria-hidden", "false");
  panel.style.transform = "translateX(0)";
  document.body.classList.add("tracking-panel-open");
  window.requestAnimationFrame(() => {
    backdrop.classList.add("is-visible");
    backdrop.style.opacity = "1";
  });
  loadTrackingPanel(id);
}

function loadTrackingPanel(id) {
  const panel = $("#modal_seguimiento");
  panel.find("#myCenterModalLabel").html("Seguimiento de código " + id);

  $.ajax({
    data: { Seguimiento_Visitas: 1, CodigoSeguimiento: id },
    type: "POST",
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
         panel.find("#myCenterModalLabel").html(
           "Seguimiento de código " + id + " <span class='badge bg-light text-primary ms-2'>Visitas: " +
             (jsonData.Visitas || 0) +
             "</span>",
         );
      }
    },
  });

  panel.find("#myCenterModalLabel2").html("Movimientos del código " + id);
  $("#cambiar_estado").click(function () {
    $("#codigo_seguimiento").val(id);
    $("#fill-warning-modal").modal("show");
  });

  $("#cambiar_estado_ok").click(function () {
    if ($("#nueva_visita_check").prop("checked")) {
      var check = 1;
    } else {
      check = 0;
    }

    $.ajax({
      data: { CambiarEstado: 1, CodigoSeguimiento: id, ctacte: check },
      type: "POST",
      url: "../Funciones/php/cambiarestado.php",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == 1) {
          $("#fill-warning-modal").modal("hide");
          closeTrackingPanel();
          toast("success", "Registro Actualizado !", "Se ha actualizado el registro correctamente.");
          var tabla = $("#guias_recibidas_tabla").DataTable();
          tabla.ajax.reload();
        }
      },
    });
  });

  $.ajax({
    data: { Seguimiento_Modal: 1, CodigoSeguimiento: id },
    type: "POST",
    url: "../Funciones/php/tablas.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (!jsonData.data || !jsonData.data[0]) {
        return;
      }

      if (jsonData.data[0].Entregado == 1) {
        $("#modal_seguimiento_header")
          .removeClass("bg-primary bg-success text-white")
          .addClass("tracking-delivered");
        $("#modal_seguimiento_content")
          .removeClass("bg-primary bg-success")
          .addClass("tracking-delivered");
      } else {
        $("#modal_seguimiento_header")
          .removeClass("bg-success bg-primary text-white")
          .addClass("tracking-pending");

        $("#modal_seguimiento_content")
          .removeClass("bg-success bg-primary")
          .addClass("tracking-pending");
      }
      console.log("response", jsonData);
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
      //           $('#guia_seguimiento').html(jsonData.data[0].TipoDeComprobante+' | '+jsonData.data[0].NumeroComprobante);
      const guide = jsonData.data[0];
      const trackingCode = encodeURIComponent(guide.CodigoSeguimiento || id);
      $("#tracking-label-link").attr(
        "href",
        "/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=" + trackingCode,
      );
      $("#tracking-guide-link").attr(
        "href",
        "Informes/Remitopdf.php?CS=" + trackingCode,
      );

      // datos para el Rotulo (Zebra) + boton en la barra de acciones
      window.seguimientoData = guide;
      // PHP: array('data'=>$rows, $rows_seguimiento, $row_hdr) -> keys "data",0,1
      window.seguimientoHdr = jsonData[1] || {};
      window.seguimientoCS = String(guide.CodigoSeguimiento || id);
      ensureBrowserPrintSDK();
      if (!document.getElementById("tracking-rotulo-zebra")) {
        $("#tracking-label-link").after(
          '<button type="button" id="tracking-rotulo-zebra" class="btn tracking-panel-action tracking-panel-action-label" onclick="abrirRotuloZebra()">' +
            '<i class="mdi mdi-printer"></i> Rótulo</button>',
        );
      }
      $("#info_guia_seguimiento").html(
        '<div class="tracking-guide-grid">' +
          '<div class="tracking-guide-item"><span>Cantidad</span><strong>' + guide.Cantidad + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Entregar en</span><strong>' + guide.EntregaEn + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Cod. proveedor</span><strong>' + guide.CodigoProveedor + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Valor declarado</span><strong>' + guide.ValorDeclarado + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Cobrar envío</span><strong>' + guide.CobrarEnvio + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Cobrar Caddy</span><strong>' + guide.CobrarCaddy + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Recorrido</span><strong>' + guide.Recorrido + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Transportista</span><strong>' + guide.Transportista + "</strong></div>" +
          '<div class="tracking-guide-item"><span>Kilómetros</span><strong>' + guide.Kilometros + "</strong></div>" +
          '<div class="tracking-guide-note"><span>Observaciones</span><strong>' + guide.Observaciones + "</strong></div>" +
        "</div>",
      );

      if ($.fn.DataTable.isDataTable("#seguimiento_tabla")) {
        $("#seguimiento_tabla").DataTable().destroy();
      }

      var datatable_seguimiento = $("#seguimiento_tabla").DataTable({
        paging: false,
        searching: false,
        ajax: {
          url: "../Funciones/php/tablas.php",
          data: { Seguimiento_Tabla: 1, CodigoSeguimiento: id },
          type: "post",
        },
        columns: [
          {
            data: null,
            title: "Fecha | Hora",
            render: function (_data, _type, row) {
              const dateParts = String(row.Fecha || "").split("-");
              const date = dateParts.length === 3
                ? dateParts[2] + "." + dateParts[1] + "." + dateParts[0].slice(-2)
                : row.Fecha || "";
              const time = String(row.Hora || "").slice(0, 5);
              return date + " " + time;
            },
          },
          { data: "Usuario" },
          { data: "Observaciones" },
          { data: "Estado" },
        ],
      });
    },
    error: function (err) {
      console.log("error", err);
    },
  });
}

function ensureTrackingPanelStyles() {
  if (document.querySelector('link[href$="Funciones/css/seguimiento-panel.css"]')) {
    return;
  }

  const stylesheet = document.createElement("link");
  stylesheet.rel = "stylesheet";
  stylesheet.href = "/SistemaTriangular/Funciones/css/seguimiento-panel.css";
  document.head.appendChild(stylesheet);
}

// ============================================================
// ROTULO 6x2 cm - impresion directa en Zebra via Browser Print
// (autonomo: inyecta el SDK y el modal de preview donde haga falta,
//  para no tocar cada pantalla que incluye el panel de seguimiento)
// ============================================================
window.zebraDevice = window.zebraDevice || null;

function ensureBrowserPrintSDK() {
  if (window.__bpLoading || typeof BrowserPrint !== "undefined") {
    if (typeof BrowserPrint !== "undefined" && !window.zebraDevice) zebraSetup();
    return;
  }
  window.__bpLoading = true;
  var base = "/SistemaTriangular/Ticket/zebra/";
  var s1 = document.createElement("script");
  s1.src = base + "BrowserPrint-3.0.216.min.js";
  s1.onload = function () {
    var s2 = document.createElement("script");
    s2.src = base + "BrowserPrint-Zebra-1.0.216.min.js";
    s2.onload = function () { zebraSetup(); };
    document.head.appendChild(s2);
  };
  document.head.appendChild(s1);
}

function zebraSetup() {
  if (typeof BrowserPrint === "undefined") return;
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) { window.zebraDevice = device; },
    function () {},
  );
}

function ensureRotuloZebraModal() {
  if (document.getElementById("modal_rotulo_zebra")) return;

  // estilos: (1) el modal por ENCIMA del panel de seguimiento deslizante;
  // (2) que los 4 botones del panel entren en una fila.
  if (!document.getElementById("rotulo-zebra-css")) {
    var st = document.createElement("style");
    st.id = "rotulo-zebra-css";
    st.textContent =
      "#modal_rotulo_zebra{z-index:20060!important;}" +
      ".modal-backdrop.rotulo-zebra-back{z-index:20050!important;}" +
      "#modal_seguimiento.tracking-panel .modal-footer{grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:.35rem!important;}" +
      "#modal_seguimiento.tracking-panel .tracking-panel-action,#modal_seguimiento.tracking-panel .tracking-panel-close{font-size:.58rem!important;padding:.4rem .1rem!important;line-height:1.05;}" +
      "#modal_seguimiento.tracking-panel .tracking-panel-action i,#modal_seguimiento.tracking-panel .tracking-panel-close i{margin-right:.12rem!important;}";
    document.head.appendChild(st);
  }

  var html =
    '<div id="modal_rotulo_zebra" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
    '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
    '<div class="modal-header"><h5 class="modal-title">Imprimir Rótulo (Zebra)</h5>' +
    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>' +
    '<div class="modal-body">' +
    '<p class="text-muted small mb-2">Vista previa de lo que se va a imprimir en la Zebra:</p>' +
    '<div id="rotulo_preview" style="width:390px;height:192px;max-width:100%;border:1px solid #333;border-radius:3px;padding:8px 10px;font-family:\'DejaVu Sans Mono\',Consolas,monospace;font-size:11px;line-height:1.3;background:#fff;color:#000;position:relative;overflow:hidden;margin:0 auto;"></div>' +
    '<div id="rotulo_zebra_estado" class="small mt-2 text-muted"></div>' +
    "</div>" +
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>' +
    '<button id="btn_rotulo_zebra_imprimir" type="button" class="btn btn-primary"><i class="mdi mdi-printer me-1"></i>Imprimir</button>' +
    "</div></div></div></div>";
  document.body.insertAdjacentHTML("beforeend", html);

  // el backdrop de Bootstrap tambien por encima del panel deslizante
  $("#modal_rotulo_zebra").on("shown.bs.modal", function () {
    $(".modal-backdrop").last().addClass("rotulo-zebra-back");
  });
}

function _recRot(s, n) {
  s = (s == null ? "" : String(s)).trim();
  return s.length > n ? s.slice(0, n - 1) + "…" : s;
}

// Logo Caddy (iso) - GFA, mismo que usa la etiqueta de colecta de WePoint.
var CADDY_LOGO_ZPL =
  "^FO15,15^GFA,1675,1675,25,,:::::::::::::M0CJ04J01,L07F8003FCI0FE,L0FFC007FE003FF,K01FFE00IF007FF8,K03FFE01IF007FFC,K03IF01IF80IFC,K03IF81IFC0IFE,K07IF83IFC0IFE,K07IFC1IFE0IFE,K03IFE1JF0F01E,K03IFE1JF0E01C,K03JF1JF8703C,K01JF0JF87C78,L0JF87IFC3FF,L0JFC7IFE0FE,L07IFC3IFE01V07,L07IFE3JFX0F,L03IFE1JFX0F,L01JF0JF8W07J0F,L01JF8JFCgH0F,M0JF87IFCgH0F,M0JFC7IFEI0E3C38FC3FE07F8F73F3FE,M07IFC3IFEI0E3E79FF3FF0FFCF7FFBFF,M03IFE1JFI0E3E7BFF3FF9FFEF7FFBFE,M03IFE1JFI0F7E77C73C79E1EF7C78F,M01IFE0JFI0F7E7787BC3DE1E77878F,N0IFE07IFI077FF7FFBC3DC0FF7838F,N0IFE07IFI07F7E7FFBC3DC0FF7838F,N07FFE03IFI07E7E7803C3DE1EF7838F,N07FFE03IFI03E7E7C13C79E1EF7838F,N03FFC01FFEI03E3C3FF3FF9FFEF7878FE,N01FF800FFCI03E3C1FFBFF0FFCF78787F,O0FFI07F8I01C3C0FF3FE07F0778383F,U08Q03C,gM03C,:::,::::::::::::::^FS";

function _rotuloDatos() {
  var d = window.seguimientoData || {};
  var hdr = window.seguimientoHdr || {};
  var cs = (window.seguimientoCS || "").trim();
  var hoy = new Date();
  var p2 = function (n) { return String(n).padStart(2, "0"); };
  return {
    cs: cs,
    cliente: _recRot(d.ClienteDestino || "", 26),
    domicilio: _recRot(d.DomicilioDestino || "", 30),
    origen: _recRot(d.RazonSocial || "", 26),
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
  var esc = function (s) { return $("<div>").text(s == null ? "" : s).html(); };
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
  ensureRotuloZebraModal();
  ensureBrowserPrintSDK();
  var x = _rotuloDatos();
  if (!x.cs) {
    if (window.toast) toast("error", "Rótulo", "No hay un código de seguimiento cargado.");
    return;
  }
  $("#rotulo_preview").html(_rotuloPreviewHTML(x));

  var $est = $("#rotulo_zebra_estado");
  var $btn = $("#btn_rotulo_zebra_imprimir");
  if (typeof BrowserPrint === "undefined") {
    $est.removeClass("text-success").addClass("text-danger").html(
      "No se detectó Zebra Browser Print en esta PC. Instalá la app 'Zebra Browser Print' o usá 'Ver etiqueta' (PDF).",
    );
    $btn.prop("disabled", true);
    // por si el SDK todavia estaba cargando
    setTimeout(function () {
      if (typeof BrowserPrint !== "undefined") { zebraSetup(); _rotuloEstadoImpresora(); }
    }, 1200);
  } else if (!window.zebraDevice) {
    zebraSetup();
    $est.removeClass("text-danger text-success").addClass("text-muted").html("Buscando impresora…");
    setTimeout(_rotuloEstadoImpresora, 900);
  } else {
    _rotuloEstadoImpresora();
  }

  $("#modal_rotulo_zebra").modal("show");
}

function _rotuloEstadoImpresora() {
  var $est = $("#rotulo_zebra_estado");
  var $btn = $("#btn_rotulo_zebra_imprimir");
  if (window.zebraDevice) {
    $est.removeClass("text-danger text-muted").addClass("text-success").html("Impresora: " + window.zebraDevice.name);
    $btn.prop("disabled", false);
  } else {
    $est.removeClass("text-success text-muted").addClass("text-danger").html(
      "No se encontró ninguna impresora Zebra. Revisá que esté encendida y en Browser Print.",
    );
    $btn.prop("disabled", true);
  }
}

$(document).on("click", "#btn_rotulo_zebra_imprimir", function () {
  if (!window.zebraDevice) return;
  var zpl = _rotuloZPL(_rotuloDatos());
  var $b = $(this).prop("disabled", true);
  window.zebraDevice.send(
    zpl,
    function () {
      $("#modal_rotulo_zebra").modal("hide");
      $b.prop("disabled", false);
      if (window.toast) toast("success", "Rótulo", "Enviado a la impresora.");
    },
    function (err) {
      $b.prop("disabled", false);
      $("#rotulo_zebra_estado").removeClass("text-success").addClass("text-danger").html("Error al imprimir: " + err);
    },
  );
});

document.addEventListener(
  "click",
  function (event) {
    const trigger = event.target.closest(
      '[data-tracking-panel], [data-bs-target="#modal_seguimiento"], [data-target="#modal_seguimiento"]',
    );
    if (!trigger) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    openTrackingPanel(trigger.dataset.id || trigger.getAttribute("data-id"));
  },
  true,
);

$(document).on("click", '#modal_seguimiento [data-bs-dismiss="modal"], #modal_seguimiento [data-panel-close]', function (event) {
  event.preventDefault();
  closeTrackingPanel();
});

$(document).on("keydown", function (event) {
  if (event.key === "Escape") {
    closeTrackingPanel();
  }
});

$(window).on("beforeunload", function () {
  const table = $("#seguimiento_tabla");
  if ($.fn.DataTable.isDataTable(table)) {
    table.DataTable().destroy();
  }
});
