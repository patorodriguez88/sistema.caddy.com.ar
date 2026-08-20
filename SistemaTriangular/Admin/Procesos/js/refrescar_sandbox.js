// SistemaTriangular/Admin/Procesos/js/refrescar_sandbox.js
(function () {
  const URL = "/SistemaTriangular/Admin/Procesos/php/refrescar_sandbox.php";

  function renderResultado(resultado) {
    const filas = resultado
      .map(function (r) {
        const omitidas = r.omitidas
          ? `<div class="text-warning small">Omitidas: ${r.omitidas}</div>`
          : "";
        if (r.ok) {
          return `<tr><td>${r.tabla}</td><td class="text-success">OK</td><td class="text-end">${r.filas}</td><td>${r.filtro || ""}${omitidas}</td></tr>`;
        }
        return `<tr><td>${r.tabla}</td><td class="text-danger">Error</td><td></td><td class="text-danger">${r.error || ""}${omitidas}</td></tr>`;
      })
      .join("");

    return `
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-striped">
          <thead>
            <tr><th>Tabla</th><th>Estado</th><th class="text-end">Filas</th><th>Detalle</th></tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>
      </div>
    `;
  }

  function post(data) {
    return new Promise(function (resolve, reject) {
      $.post(URL, data, resolve, "json").fail(reject);
    });
  }

  // Copia las tablas UNA POR UNA (en vez de un solo request gigante) para
  // que ningun request individual se acerque al timeout del gateway/proxy
  // delante de PHP - eso fue lo que paso antes: el refresco completo corria
  // sincronico y con suficientes datos en produccion tardaba mas que ese
  // limite, cortando la conexion (504) sin terminar de copiar todo y sin
  // avisar que tablas quedaron a mitad de camino.
  async function refrescarSecuencial(periodo) {
    const listado = await post({ action: "listar_tablas" });
    if (!listado || !listado.ok) {
      throw new Error((listado && listado.error) || "No se pudo listar las tablas.");
    }

    const tablas = listado.tablas;
    const resultado = [];

    for (let i = 0; i < tablas.length; i++) {
      const tabla = tablas[i];

      Swal.update({
        html: `Tabla ${i + 1} de ${tablas.length}: <strong>${tabla}</strong>`,
      });

      try {
        const r = await post({ action: "refrescar_tabla", tabla: tabla, periodo: periodo });
        resultado.push(
          r && typeof r === "object"
            ? r
            : { tabla: tabla, ok: false, error: "Respuesta inválida del servidor." },
        );
      } catch (e) {
        resultado.push({ tabla: tabla, ok: false, error: "Falló la solicitud al servidor." });
      }
    }

    return resultado;
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
        html: "Preparando...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: function () {
          Swal.showLoading();
        },
      });

      refrescarSecuencial(periodo)
        .then(function (resultado) {
          const conError = resultado.filter(function (r) {
            return !r.ok;
          }).length;

          Swal.fire({
            title: conError > 0 ? "Terminado con errores" : "Sandbox actualizado",
            icon: conError > 0 ? "warning" : "success",
            html: renderResultado(resultado),
            width: 700,
          });
        })
        .catch(function (e) {
          Swal.fire("Error", (e && e.message) || "Falló la solicitud al servidor.", "error");
        });
    });
  });
})();
