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

      // datos para el Rotulo 6x2 (Zebra) + boton en la barra de acciones
      window.seguimientoData = guide;
      window.seguimientoCS = String(guide.CodigoSeguimiento || id);
      ensureBrowserPrintSDK();
      if (!document.getElementById("tracking-rotulo-zebra")) {
        $("#tracking-label-link").after(
          '<button type="button" id="tracking-rotulo-zebra" class="btn tracking-panel-action tracking-panel-action-label" onclick="abrirRotuloZebra()">' +
            '<i class="mdi mdi-printer"></i> Rótulo 6x2 (Zebra)</button>',
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
  var html =
    '<div id="modal_rotulo_zebra" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
    '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
    '<div class="modal-header"><h5 class="modal-title">Imprimir Rótulo 6&times;2 cm</h5>' +
    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>' +
    '<div class="modal-body">' +
    '<p class="text-muted small mb-2">Vista previa de lo que se va a imprimir en la Zebra:</p>' +
    '<div id="rotulo_preview" style="width:360px;height:120px;max-width:100%;border:1px solid #333;border-radius:3px;padding:6px 8px;font-family:\'DejaVu Sans Mono\',Consolas,monospace;font-size:11px;line-height:1.25;background:#fff;color:#000;position:relative;overflow:hidden;margin:0 auto;"></div>' +
    '<div id="rotulo_zebra_estado" class="small mt-2 text-muted"></div>' +
    "</div>" +
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>' +
    '<button id="btn_rotulo_zebra_imprimir" type="button" class="btn btn-primary"><i class="mdi mdi-printer me-1"></i>Imprimir</button>' +
    "</div></div></div></div>";
  document.body.insertAdjacentHTML("beforeend", html);
}

function _recRot(s, n) {
  s = (s == null ? "" : String(s)).trim();
  return s.length > n ? s.slice(0, n - 1) + "…" : s;
}

function _rotuloDatos() {
  var d = window.seguimientoData || {};
  var cs = (window.seguimientoCS || "").trim();
  return {
    cs: cs,
    cliente: _recRot(d.ClienteDestino || "", 30),
    domicilio: _recRot(d.DomicilioDestino || "", 34),
    localidad: _recRot(d.LocalidadDestino || d.CiudadDestino || "", 30),
    recorrido: (d.Recorrido || "").toString().trim(),
    origen: _recRot(d.RazonSocial || "", 24),
    guia: (d.NumeroComprobante || "").toString().trim(),
    cant: (d.Cantidad || "1").toString().trim(),
  };
}

function _rotuloZPL(x) {
  return (
    "^XA^PW480^LL160^LH0,0^CI28" +
    "^FO8,6^A0N,26,26^FD" + x.cliente + "^FS" +
    "^FO8,36^A0N,20,20^FD" + x.domicilio + "^FS" +
    "^FO8,60^A0N,20,20^FD" + x.localidad + "^FS" +
    "^FO8,88^A0N,24,24^FDRec: " + (x.recorrido || "-") + "^FS" +
    "^FO8,116^A0N,18,18^FD" + x.origen + (x.guia ? "  G:" + x.guia : "") + "^FS" +
    "^FO8,136^A0N,18,18^FDCS: " + x.cs + "   Cant: " + x.cant + "^FS" +
    "^FO350,14^BQN,2,5^FDQA," + x.cs + "^FS^XZ"
  );
}

function _rotuloPreviewHTML(x) {
  var esc = function (s) { return $("<div>").text(s == null ? "" : s).html(); };
  return (
    '<div style="font-weight:700;font-size:13px;">' + esc(x.cliente) + "</div>" +
    "<div>" + esc(x.domicilio) + "</div>" +
    "<div>" + esc(x.localidad) + "</div>" +
    '<div style="font-weight:700;margin-top:2px;">Rec: ' + esc(x.recorrido || "-") + "</div>" +
    '<div style="font-size:10px;">' + esc(x.origen) + (x.guia ? "  G:" + esc(x.guia) : "") + "</div>" +
    '<div style="font-size:10px;">CS: ' + esc(x.cs) + "   Cant: " + esc(x.cant) + "</div>" +
    '<div style="position:absolute;top:8px;right:8px;width:56px;height:56px;border:1px solid #999;display:flex;align-items:center;justify-content:center;font-size:9px;color:#666;text-align:center;">QR<br>' + esc(x.cs) + "</div>"
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
