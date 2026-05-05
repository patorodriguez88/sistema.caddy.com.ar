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
// $(document).ajaxComplete(function (event, xhr, settings) {
//   const sessioExpirada = xhr.status;
//   console.log("sesionExpirada", sessioExpirada);

//   if (sessioExpirada == 401) {
//     // window.location.href = "/SistemaTriangular/inicio.php";
//     // exit();
//     Swal.fire({
//       title: "Sesión expirada",
//       text: "Tu sesión ha caducado. Por favor, volvé a iniciar sesión.",
//       icon: "warning",
//       confirmButtonText: "Aceptar",
//     }).then(() => {
//       window.location.href = "/SistemaTriangular/inicio.php";
//       exit();
//     });
//   }
// });
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
  cargarConsultasFrecuentesIA();
  $("#menuhyper_head").load("../Menu/head.html");

  $("#menuhyper_topnav").load("../Menu/topnav.html", function () {
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

window.ensureGoogleMapsLoaded = function (callbackName) {
  if (window.__gmapsLoaded) return Promise.resolve();

  if (window.__gmapsLoadingPromise) return window.__gmapsLoadingPromise;

  // nombre de callback global que Google llamará cuando termine
  const cb = callbackName || "onGoogleMapsReady";

  window[cb] = function () {
    window.__gmapsLoaded = true;
  };

  window.__gmapsLoadingPromise = new Promise((resolve, reject) => {
    const s = document.createElement("script");
    s.src =
      "https://maps.googleapis.com/maps/api/js" +
      "?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk" +
      "&region=AR&language=es-419" +
      "&libraries=places" +
      "&callback=" +
      cb +
      "&v=weekly";

    s.async = true;
    s.defer = true;

    s.onload = function () {
      // Ojo: Google llama callback; pero por si tarda 1 tick:
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
$(document).on("click", ".ia-pregunta-rapida", function () {
  const pregunta = $(this).data("pregunta") || "";
  $("#ia_consulta_texto").val(pregunta);
  $("#btn_ia_consultar").trigger("click");
});

$(document).on("click", "#btn_ia_consultar", function () {
  const pregunta = $("#ia_consulta_texto").val().trim();

  if (!pregunta) {
    Swal.fire("Atención", "Escribí una pregunta.", "warning");
    return;
  }

  $("#ia_consulta_respuesta").html(`
    <div class="text-muted">
      <span class="spinner-border spinner-border-sm me-1"></span>
      Consultando...
    </div>
  `);

  $.ajax({
    url: "../Menu/php/ia_consultas.php",
    type: "POST",
    dataType: "json",
    data: {
      pregunta: pregunta,
    },
    success: function (resp) {
      if (!resp || resp.success != 1) {
        $("#ia_consulta_respuesta").html(`
          <div class="text-danger">
            ${resp && resp.msg ? resp.msg : "No se pudo procesar la consulta."}
          </div>
        `);
        return;
      }

      $("#ia_consulta_respuesta").html(`
        <div class="fw-semibold mb-2">Respuesta</div>
        <div>${resp.respuesta}</div>
        ${
          resp.detalle
            ? `<hr><small class="text-muted">${resp.detalle}</small>`
            : ""
        }
      `);
    },
    error: function (xhr) {
      console.log(xhr.responseText);
      $("#ia_consulta_respuesta").html(`
        <div class="text-danger">Error consultando el asistente.</div>
      `);
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
      if (!res.success || !res.data || !res.data.length) return;

      let html = "";

      res.data.forEach(function (item) {
        html += `
          <button 
            type="button"
            class="btn btn-sm btn-light border text-muted ia-tag-consulta"
            data-pregunta="${escapeHtml(item.pregunta)}"
            title="${escapeHtml(item.pregunta)}">
            ${escapeHtml(item.texto)}
            <span class="badge bg-secondary ms-1">${item.total}</span>
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
function escapeHtml(text) {
  return $("<div>")
    .text(text || "")
    .html();
}
