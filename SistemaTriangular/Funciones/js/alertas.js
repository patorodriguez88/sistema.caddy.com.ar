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
