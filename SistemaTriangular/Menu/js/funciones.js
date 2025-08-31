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
