document.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);

  if (params.get("Error") === "Si") {
    Swal.fire({
      icon: "error",
      title: "Error de acceso",
      text: "Tu sesión ha expirado o no tenés permisos para acceder a esta sección.",
      confirmButtonText: "Aceptar",
    }).then(() => {
      // Limpiamos la URL sin recargar la página
      params.delete("Error");
      const newUrl =
        window.location.pathname +
        (params.toString() ? "?" + params.toString() : "");
      window.history.replaceState({}, document.title, newUrl);
    });
  }
});
//FUNCION TOAST

// toast("error", "Error", "No se pudo procesar el pago");
// toast("warning", "Atención", "El importe es incorrecto");
// toast("success", "Perfecto", "Pago registrado correctamente");

function toast(tipo, titulo, mensaje) {
  Swal.fire({
    toast: true,
    position: "bottom-end",
    icon: tipo,
    title: titulo,
    text: mensaje,
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });
}
// FUNCTION ALERTAS
// alerta("error", "Error!", jsonData.data.message);
// alerta("success", "Guardado", "El registro se guardó correctamente");
// alerta("warning", "Atención!", "El importe no puede ser 0 ni nulo");
// alerta(
//   "info",
//   "Información",
//   "El pago se registró pero no se pudo enviar el email de notificación",
// );
// alerta(
//   "question",
//   "¿Estás seguro?",
//   "¿Deseas eliminar este registro? Esta acción no se puede deshacer.",
// );
function alerta(tipo, titulo, mensaje) {
  Swal.fire({
    icon: tipo,
    title: titulo,
    html: mensaje,
    confirmButtonText: "Aceptar",
    confirmButtonColor: "#10c469",
  });
}

// FUNCIONES PARA EL MODAL GENERICO DE "CARGANDO..." (#info-alert-modal,
// reusado en HojaDeRuta2/Zonas/etc con su propio -title dentro de cada
// pagina) - Bootstrap 5 ignora modal("hide") si todavia esta a mitad de la
// transicion de modal("show") (chequea un flag interno _isTransitioning).
// En pedidos que resuelven muy rapido (ej. localhost, sin latencia real de
// red) el success/error de un $.ajax puede llegar y llamar hide() antes de
// que termine de animarse el show(), y el modal queda trabado para siempre
// con el spinner girando - nunca pasaba con pedidos mas lentos (ej. Routes
// API de Google) porque para cuando llegaba la respuesta el show() ya habia
// terminado hace rato. Se corrige esperando el evento shown.bs.modal antes
// de ocultar si el modal todavia no termino de mostrarse.
//
// mostrarModalCarga("#info-alert-modal", "Cargando...");
// ocultarModalCarga("#info-alert-modal");
function mostrarModalCarga(selector, titulo) {
  if (titulo) {
    $(selector + "-title").html(titulo);
  }
  $(selector).modal("show");
}

function ocultarModalCarga(selector) {
  const $el = $(selector);
  const el = $el.get(0);
  if (!el) return;
  if (el.classList.contains("show")) {
    $el.modal("hide");
  } else {
    $el.one("shown.bs.modal", function () {
      $el.modal("hide");
    });
  }
}

// FIX: Bootstrap 5 no maneja bien un modal abierto arriba de otro (ej. el par
// "Modificar Guia" / "Modificar Venta" en Clientes.php) - cada modal nuevo y
// su backdrop se agregan con el mismo z-index base, asi que el backdrop del
// modal de ADENTRO puede terminar quedando ARRIBA del contenido del modal de
// AFUERA sin tapar visualmente nada raro, pero el click cae en ese backdrop
// en vez del boton (ej. "Guardar Cambios" no hacia nada). Se sube el z-index
// de cada modal nuevo y su backdrop segun cuantos modales ya estan abiertos,
// para que el ultimo en abrirse siempre quede arriba de todo.
$(document).on("show.bs.modal", ".modal", function () {
  var zIndex = 1055 + 20 * $(".modal:visible").length;
  $(this).css("z-index", zIndex);
  setTimeout(function () {
    $(".modal-backdrop")
      .not(".modal-stack")
      .css("z-index", zIndex - 5)
      .addClass("modal-stack");
  }, 0);
});
