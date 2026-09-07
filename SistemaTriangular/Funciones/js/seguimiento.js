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
