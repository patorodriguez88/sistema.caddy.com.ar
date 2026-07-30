// SistemaTriangular/Admin/Procesos/js/refrescar_sandbox.js
(function () {
  const URL = "/SistemaTriangular/Admin/Procesos/php/refrescar_sandbox.php";

  function renderResultado(resultado) {
    const filas = resultado
      .map(function (r) {
        if (r.ok) {
          return `<tr><td>${r.tabla}</td><td class="text-success">OK</td><td class="text-end">${r.filas}</td><td>${r.filtro || ""}</td></tr>`;
        }
        return `<tr><td>${r.tabla}</td><td class="text-danger">Error</td><td></td><td class="text-danger">${r.error || ""}</td></tr>`;
      })
      .join("");

    return `
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-striped">
          <thead>
            <tr><th>Tabla</th><th>Estado</th><th class="text-end">Filas</th><th>Filtro aplicado</th></tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>
      </div>
    `;
  }

  $("#btnRefrescarSandbox").on("click", function () {
    const periodo = $("#periodoRefrescoSandbox").val();
    const periodoTexto = $("#periodoRefrescoSandbox option:selected").text();

    Swal.fire({
      title: "¿Refrescar sandbox con datos de producción?",
      html: `Esto va a <strong>reemplazar todos los datos</strong> de esta base (sandbox) con una copia de producción (${periodoTexto.toLowerCase()} para las tablas con fecha). Cualquier dato de prueba cargado solo acá se pierde.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, refrescar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#d33",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: "Refrescando...",
        html: "Puede tardar unos segundos, no cierres esta pantalla.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: function () {
          Swal.showLoading();
        },
      });

      $.post(URL, { action: "refrescar", periodo: periodo }, function (json) {
        if (!json || !json.ok) {
          Swal.fire(
            "Error",
            (json && json.error) || "No se pudo refrescar sandbox.",
            "error",
          );
          return;
        }

        const conError = json.resultado.filter(function (r) {
          return !r.ok;
        }).length;

        Swal.fire({
          title: conError > 0 ? "Terminado con errores" : "Sandbox actualizado",
          icon: conError > 0 ? "warning" : "success",
          html: renderResultado(json.resultado),
          width: 700,
        });
      }, "json").fail(function () {
        Swal.fire("Error", "Falló la solicitud al servidor.", "error");
      });
    });
  });
})();
