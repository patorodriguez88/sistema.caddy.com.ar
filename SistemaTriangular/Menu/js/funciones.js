// $.fn.dataTable.ext.errMode = "none"; // 👈 evitar alertas por defecto
// ✅ Manejo global de 401 con filtros y una sola alerta
(function () {
  let alertando = false;

  $(document).ajaxError(function (event, xhr, settings) {
    if (xhr.status !== 401) return;

    // Solo para llamadas del MISMO origen (evita CORS/terceros)
    const url = new URL(settings.url, location.href);
    if (url.hostname !== location.hostname) return;

    // Confirmar que realmente es sesión expirada (servidor manda header o JSON)
    const xExpired = xhr.getResponseHeader("X-Session-Expired") === "1";
    let payload;
    try {
      payload = xhr.responseJSON || JSON.parse(xhr.responseText);
    } catch (_) {}

    const esNoAuth =
      payload &&
      (payload.error === "NO_AUTH" || payload.error === "SESSION_EXPIRED");

    if (!xExpired && !esNoAuth) return; // no es un 401 de sesión

    if (alertando) return; // antirrebote si hay varias requests fallando a la vez
    alertando = true;

    Swal.fire({
      title: "Sesión expirada",
      text: "Tu sesión ha caducado. Por favor, volvé a iniciar sesión.",
      icon: "warning",
      confirmButtonText: "Aceptar",
    }).then(() => {
      window.location.href = "/SistemaTriangular/inicio.php";
    });
  });
})();

// funcion para los botones de datatable
$.extend(true, $.fn.dataTable.Buttons.defaults, {
  dom: {
    button: {
      className: "btn btn-sm btn-secondary me-1 mt-2",
    },
  },
});

$(document).ready(function () {
  // Carga de menús
  // Cache-busting: evita que un proxy/CDN o el navegador sirvan una versión
  // vieja del menú cuando se actualiza (ej: topnav.html cacheado en sandbox).
  const _menuCacheBust = Date.now();

  $("#menuhyper_head").load("../Menu/head.html?v=" + _menuCacheBust);

  $("#menuhyper_topnav").load("../Menu/topnav.html?v=" + _menuCacheBust, function () {
    cargarConsultasFrecuentesIA();
    // Cambio de tema claro/oscuro
    $("#light-dark-mode").on("click", function () {
      const html = $("html");
      const actual = html.attr("data-bs-theme") || "light";
      const nuevo = actual === "dark" ? "light" : "dark";
      html.attr("data-bs-theme", nuevo);
      $(this).find("i").toggleClass("ri-moon-line ri-sun-line");
      localStorage.setItem("modo-tema", nuevo);
    });

    // Restaurar modo tema guardado
    const guardado = localStorage.getItem("modo-tema");
    if (guardado) {
      $("html").attr("data-bs-theme", guardado);
      const icono = $("#light-dark-mode i");
      icono.removeClass("ri-moon-line ri-sun-line");
      icono.addClass(guardado === "dark" ? "ri-sun-line" : "ri-moon-line");
    }

    // Activar/desactivar pantalla completa
    $(document).on("click", '[data-toggle="fullscreen"]', function (e) {
      e.preventDefault(); // ⚠️ muy importante para evitar el refresh

      const icono = $(this).find("i");
      const isFullScreen =
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;

      if (isFullScreen) {
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
        else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
        else if (document.msExitFullscreen) document.msExitFullscreen();

        icono
          .removeClass("ri-fullscreen-exit-line")
          .addClass("ri-fullscreen-line");
      } else {
        const docElm = document.documentElement;
        if (docElm.requestFullscreen) docElm.requestFullscreen();
        else if (docElm.mozRequestFullScreen) docElm.mozRequestFullScreen();
        else if (docElm.webkitRequestFullscreen)
          docElm.webkitRequestFullscreen();
        else if (docElm.msRequestFullscreen) docElm.msRequestFullscreen();

        icono
          .removeClass("ri-fullscreen-line")
          .addClass("ri-fullscreen-exit-line");
      }
    });
  });
  // footer
  $("#menuhyper_footer").load("../Menu/footer.html", function () {
    $("#footer-year").text(new Date().getFullYear());
  });

  // Usuario logueado
  $.ajax({
    url: "../Menu/php/funciones.php",
    type: "POST",
    data: { Empleados: 1 },
    success: function (response) {
      try {
        const jsonData = JSON.parse(response);
        // if (!verificarSesion(jsonData)) return;

        if (jsonData.success == "1") {
          $("#user_name").html(jsonData.Nombre);
          $("#user_sucursal").html(jsonData.Sucursal);
          $("#user_iniciales").html(jsonData.Avatar);
          $("#user_nivel").html("Nivel " + jsonData.Nivel);
          window.USUARIO_NIVEL = parseInt(jsonData.Nivel || 0);
          // Entorno (sandbox / produccion)
          var entorno = (jsonData.Entorno || "").toString().toLowerCase();
          var $badge = $("#user_entorno_badge");

          if (entorno === "sandbox") {
            $badge
              .text("SANDBOX")
              .removeClass()
              .addClass("badge rounded-pill bg-warning text-dark");
          } else if (entorno === "produccion") {
            $badge
              .text("CONECTADO")
              .removeClass()
              .addClass("badge rounded-pill bg-success text-white");
          } else if (entorno === "local") {
            $badge
              .text("LOCALHOST")
              .removeClass()
              .addClass("badge rounded-pill bg-black text-white mt-1");
          } else {
            $badge
              .text("ENTORNO DESCONOCIDO")
              .removeClass()
              .addClass("badge rounded-pill bg-secondary text-white");
          }

          if (jsonData.Nivel == 1) {
            $("#home_cpaneladmin").css("display", "block");
          } else {
            $("#home_cpaneladmin").css("display", "none");
          }
        }
      } catch (e) {
        console.error("Respuesta inválida:", response);
      }
    },
    error: function (error) {
      console.error("Error en la solicitud AJAX:", error);
    },
  });

  // Integraciones pendientes
  $.ajax({
    data: { IntegracionesPendientes: 1 },
    url: "../Menu/php/menu_integraciones.php",
    type: "POST",
    success: function (response) {
      try {
        var jsonData = JSON.parse(response);
        // if (!verificarSesion(jsonData)) return;

        if (jsonData.success == "1" && jsonData.total != 0) {
          $("#total_menu_integraciones").html(jsonData.total);
          $("#total_menu_integraciones_1").html(jsonData.total);
        }
      } catch (e) {
        console.error("Respuesta inválida:", response);
      }
    },
    error: function (error) {
      console.error("Error en la solicitud AJAX:", error);
    },
  });

  //Pendientes
  $.ajax({
    url: "../Menu/php/funciones.php",
    type: "POST",
    data: { Pendientes: 1 },
    success: function (response) {
      try {
        const jsonData = JSON.parse(response);

        if (jsonData.success == "1" && Array.isArray(jsonData.notificaciones)) {
          const contenedor = $("#contenedorNotificaciones");
          contenedor.empty();

          jsonData.notificaciones.forEach(function (item) {
            contenedor.append(`
              <a href="javascript:void(0);" class="dropdown-item notify-item">
                <div class="notify-icon bg-primary rounded-circle text-center" style="width:36px;height:36px;line-height:36px;font-weight:bold;">
                  ${item.cantidad}
                </div>
                <p class="notify-details">
                  ${item.nombre}
                  <small class="text-muted">${item.mensaje}</small>
                </p>
              </a>
            `);
          });

          // También podés actualizar el badge rojo del ícono
          // $(".noti-icon-badge").text(jsonData.notificaciones.length);
        }
      } catch (e) {
        console.error("Respuesta inválida:", response);
      }
    },
    error: function (error) {
      console.error("Error en la solicitud AJAX:", error);
    },
  });
});

// ===============================
// Google Maps Loader (1 sola vez)
// ===============================
//Reemplazar initMap() por
// ensureGoogleMapsLoaded("initMap_order").then(() => {
//  initMap();
//}).catch((e) => {
//  console.error(e);
//});

window.__gmapsLoaded = false;
window.__gmapsLoadingPromise = null;

window.ensureGoogleMapsLoaded = function () {
  if (window.__gmapsLoaded) return Promise.resolve();
  if (window.__gmapsLoadingPromise) return window.__gmapsLoadingPromise;

  // Si Maps ya fue cargado por un tag estático, sincronizamos el flag y salimos
  if (window.google && window.google.maps) {
    window.__gmapsLoaded = true;
    return Promise.resolve();
  }

  // Callback privado — no pisa funciones del sistema como initMap_order
  const privateCb = "_gmapsLoaderCb";
  window[privateCb] = function () {
    window.__gmapsLoaded = true;
    delete window[privateCb];
  };

  window.__gmapsLoadingPromise = new Promise((resolve, reject) => {
    const s = document.createElement("script");
    s.src =
      "https://maps.googleapis.com/maps/api/js" +
      "?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8" +
      "&region=AR&language=es-419" +
      "&libraries=places" +
      "&callback=" + privateCb +
      "&loading=async" +
      "&v=weekly";

    s.async = true;
    s.defer = true;

    s.onload = function () {
      const check = () =>
        window.__gmapsLoaded ? resolve() : setTimeout(check, 20);
      check();
    };

    s.onerror = function () {
      reject(new Error("No se pudo cargar Google Maps JS"));
    };

    document.head.appendChild(s);
  });

  return window.__gmapsLoadingPromise;
};
let _iaChat = [];
let _iaConsultando = false;

function _iaChatRender() {
  const $h = $("#ia_chat_historial");
  if (!$h.length) return;

  if (!_iaChat.length && !_iaConsultando) {
    $h.html(`<div class="text-center text-muted py-4" id="ia_chat_empty">
      <i class="ri-sparkling-line d-block mb-1" style="font-size:26px;"></i>
      <small>Hacé tu primera consulta</small>
    </div>`);
    return;
  }

  let html = "";
  _iaChat.forEach(function (item) {
    html += `
      <div class="d-flex justify-content-end">
        <div class="bg-primary text-white rounded-3 px-3 py-2"
             style="max-width:70%;font-size:13px;word-break:break-word;">
          ${escapeHtml(item.pregunta)}
        </div>
      </div>
      <div class="d-flex justify-content-start mb-1">
        <div class="border rounded-3 px-3 py-2 ${item.esError ? "border-danger text-danger" : "bg-light"}"
             style="max-width:90%;font-size:13px;word-break:break-word;">
          ${item.esError ? escapeHtml(item.respuesta) : item.respuesta}
          ${!item.esError && item.detalle ? `<hr class="my-1"><small class="text-muted">${item.detalle}</small>` : ""}
        </div>
      </div>`;
  });

  if (_iaConsultando) {
    html += `<div class="d-flex justify-content-start">
      <div class="bg-light border rounded-3 px-3 py-2" style="font-size:13px;">
        <span class="spinner-border spinner-border-sm me-1"></span>Consultando...
      </div>
    </div>`;
  }

  $h.html(html);
  $h.scrollTop($h[0].scrollHeight);
}

$(document).on("click", ".ia-pregunta-rapida", function () {
  const pregunta = $(this).data("pregunta") || "";
  $("#ia_consulta_texto").val(pregunta);
  $("#btn_ia_consultar").trigger("click");
});

$(document).on("click", "#btn_ia_limpiar_historial", function () {
  _iaChat = [];
  _iaChatRender();
});

$(document).on("click", "#btn_ia_consultar", function () {
  const pregunta = $("#ia_consulta_texto").val().trim();

  if (!pregunta) {
    Swal.fire("Atención", "Escribí una pregunta.", "warning");
    return;
  }

  $("#ia_consulta_texto").val("");
  _iaConsultando = true;
  _iaChatRender();

  $.ajax({
    url: "../Menu/php/ia_consultas.php",
    type: "POST",
    dataType: "json",
    data: { pregunta: pregunta },
    success: function (resp) {
      _iaConsultando = false;
      if (!resp || resp.success != 1) {
        _iaChat.push({
          pregunta: pregunta,
          respuesta: resp && resp.msg ? resp.msg : "No se pudo procesar la consulta.",
          detalle: null,
          esError: true,
        });
      } else {
        _iaChat.push({
          pregunta: pregunta,
          respuesta: resp.respuesta,
          detalle: resp.detalle || null,
          esError: false,
        });
        cargarConsultasFrecuentesIA();
      }
      _iaChatRender();
    },
    error: function (xhr) {
      console.log(xhr.responseText);
      _iaConsultando = false;
      _iaChat.push({
        pregunta: pregunta,
        respuesta: "Error consultando el asistente.",
        detalle: null,
        esError: true,
      });
      _iaChatRender();
    },
  });
});

$(document).on("keydown", "#ia_consulta_texto", function (e) {
  if (e.ctrlKey && e.key === "Enter") {
    $("#btn_ia_consultar").trigger("click");
  }
});

$(document).on("click", ".ia-cliente-opcion", function () {
  const cliente = $(this).data("cliente") || "";
  const preguntaActual = $("#ia_consulta_texto").val().trim();

  if (!cliente) return;

  $("#ia_consulta_texto").val(`${preguntaActual} cliente_exacto:${cliente}`);
  $("#btn_ia_consultar").trigger("click");
});
function cargarConsultasFrecuentesIA() {
  $.ajax({
    url: "../Menu/php/ia_consultas.php",
    type: "POST",
    dataType: "json",
    data: {
      consultas_frecuentes: 1,
    },
    success: function (res) {
      if (!res.success || !res.data || !res.data.length) {
        $("#ia_consultas_frecuentes").html(
          `<span class="text-muted small">Todavía no hay consultas frecuentes.</span>`,
        );

        return;
      }

      let html = "";

      res.data.forEach(function (item) {
        html += `
          <button
            type="button"
            class="btn btn-light border text-muted ia-tag-consulta"
            style="font-size: 11px; padding: 2px 7px; line-height: 1.4;"
            data-pregunta="${escapeHtml(item.pregunta)}"
            title="${escapeHtml(item.pregunta)}">
            ${escapeHtml(item.texto)}
            <span class="badge bg-secondary ms-1" style="font-size: 10px;">${item.total}</span>
          </button>
        `;
      });

      $("#ia_consultas_frecuentes").html(html);
    },
  });
}

$(document).on("click", ".ia-tag-consulta", function () {
  $("#ia_consulta_texto").val($(this).data("pregunta"));
  $("#btn_ia_consultar").trigger("click");
});

$(document).on("show.bs.offcanvas", "#ia-consultas-offcanvas", function () {
  cargarConsultasFrecuentesIA();
});
function escapeHtml(text) {
  return $("<div>")
    .text(text || "")
    .html();
}
