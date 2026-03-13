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
