let instanciaModal = new bootstrap.Modal(
  document.getElementById("modalAgregarMulta")
);
function generarColores(cantidad) {
  const colores = [];
  for (let i = 0; i < cantidad; i++) {
    const r = Math.floor(Math.random() * 200 + 30);
    const g = Math.floor(Math.random() * 200 + 30);
    const b = Math.floor(Math.random() * 200 + 30);
    colores.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
  }
  return colores;
}
function renderGraficoBar(canvasId, titulo, dataObj) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  const labels = Object.keys(dataObj);
  const valores = Object.values(dataObj);
  const colores = generarColores(labels.length);

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: titulo,
          data: valores,
          backgroundColor: colores,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
    },
  });
}

function renderGraficoPie(canvasId, titulo, dataObj) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  const labels = Object.keys(dataObj);
  const valores = Object.values(dataObj);
  const colores = generarColores(labels.length);

  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: labels,
      datasets: [
        {
          label: titulo,
          data: valores,
          backgroundColor: colores,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
}
function renderGrafico(canvasId, titulo, dataObj) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: Object.keys(dataObj),
      datasets: [
        {
          label: titulo,
          data: Object.values(dataObj),
          backgroundColor: "#0d6efd",
        },
      ],
    },
  });
}

$(document).ready(function () {
  $.post(
    "../Admin/Procesos/php/multas.php",
    { accion: "resumen_multas" },
    function (res) {
      const r = typeof res === "string" ? JSON.parse(res) : res;

      $("#totalPendientes").text(r.pendientes);
      $("#totalPagadas").text(r.pagadas);
      $("#importePendientes").text(
        "$ " +
          r.importePendientes.toLocaleString("es-AR", {
            minimumFractionDigits: 2,
          })
      );
      $("#importePagadas").text(
        "$ " +
          r.importePagadas.toLocaleString("es-AR", { minimumFractionDigits: 2 })
      );
      $("#cantidadTotal").text(`${r.totalCantidad} multas`);
      $("#totalImporte").text(
        "$ " +
          r.totalImporte.toLocaleString("es-AR", { minimumFractionDigits: 2 })
      );

      renderGraficoBar(
        "graficoPorRepartidor",
        "Multas por repartidor",
        r.porRepartidor
      );
      renderGraficoPie(
        "graficoPorMunicipio",
        "Multas por municipio",
        r.porMunicipio
      );
    }
  );

  $("#btnNuevaMulta").on("click", function () {
    $("#formMulta")[0].reset();
    $("#id_multa").val("");
    instanciaModal.show();
    $("#modalAgregarMulta").modal("show");
  });

  const tablaMultas = $("#tablaMultas").DataTable({
    ajax: {
      url: "../Admin/Procesos/php/multas.php",
      method: "POST",
      data: { accion: "listar_multas" },
      dataSrc: "data",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return (
            '<td><span style="display: none;">' +
            row.Date +
            "</span>" +
            Fecha +
            "</td>"
          );
        },
      },
      { data: "Municipio" },
      { data: "Patente" },
      { data: "Empleado" },
      {
        data: "Importe",
        render: $.fn.dataTable.render.number(",", ".", 2, "$ "),
      },
      { data: "Estado" },
      {
        data: null,
        render: function (data) {
          return `
                <i class="mdi mdi-18px mdi-pencil editar-multa text-warning ml-2" data-id="${data.id}" style="cursor: pointer;"></i>
                <i class="mdi mdi-18px mdi-delete eliminar-multa text-danger ms-2 ml-2" data-id="${data.id}" style="cursor: pointer;"></i>
              `;
        },
      },
    ],
  });

  // Municipios
  $.post(
    "../Admin/Procesos/php/multas.php",
    { accion: "cargar_municipios" },
    function (data) {
      const lista = JSON.parse(data);
      const datalist = $("#localidades");
      datalist.empty();
      lista.forEach((muni) => datalist.append(`<option value="${muni}">`));
    }
  );

  // Vehículos
  $.post(
    "../Admin/Procesos/php/multas.php",
    { accion: "cargar_vehiculos" },
    function (data) {
      const lista = JSON.parse(data);
      const select = $("#patente");
      select.empty().append(`<option value="">Seleccione un vehículo</option>`);
      lista.forEach((item) => {
        select.append(`<option value="${item.value}">${item.label}</option>`);
      });
    }
  );

  // Empleados
  $.post(
    "../Admin/Procesos/php/multas.php",
    { accion: "cargar_empleados" },
    function (data) {
      const lista = JSON.parse(data);
      const select = $("#empleado");
      select.empty().append(`<option value="">Seleccione un empleado</option>`);
      lista.forEach((item) => {
        select.append(`<option value="${item.value}">${item.label}</option>`);
      });
    }
  );
});

// 🔐 Captura global de errores AJAX (fuera del document.ready)
$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
  console.warn("Respuesta inesperada:", jqxhr.responseText);
  try {
    const res = JSON.parse(jqxhr.responseText);
    if (res.error === "sesion_expirada") {
      Swal.fire({
        title: "Sesión expirada",
        text: "Tu sesión ha caducado. Serás redirigido al inicio.",
        icon: "warning",
        confirmButtonText: "Aceptar",
      }).then(() => {
        window.location.href = "/SistemaTriangular/inicio.php";
      });
    }
  } catch (e) {
    // Si no es JSON (error común, otro problema del servidor)
    console.error("Error inesperado en la respuesta AJAX:", jqxhr.responseText);
  }
});

$("#formMulta").on("submit", function (e) {
  e.preventDefault();

  const esEdicion = $("#id_multa").val() !== "";
  const accion = esEdicion ? "editar_multa" : "agregar_multa";
  const datos = $(this).serialize() + "&accion=" + accion;

  $.ajax({
    url: "../Admin/Procesos/php/multas.php",
    method: "POST",
    data: datos,
    success: function (res) {
      let r = typeof res === "string" ? JSON.parse(res) : res;

      if (r.success) {
        // Cerrá el modal primero
        if (instanciaModal) instanciaModal.hide();

        // Limpiá el formulario inmediatamente
        $("#formMulta")[0].reset();
        $("#id_multa").val("");

        // Esperá que se cierre visualmente y después mostrá el Swal
        setTimeout(() => {
          Swal.fire(
            "Éxito",
            esEdicion
              ? "Multa actualizada correctamente"
              : "Multa registrada correctamente",
            "success"
          ).then(() => {
            $("#tablaMultas").DataTable().ajax.reload();
          });
        }, 300); // 👈 este valor depende de tu animación (300ms es Bootstrap default)
      } else {
        Swal.fire("Error", r.error || "No se pudo guardar la multa", "error");
      }
    },
    error: function () {
      Swal.fire("Error", "Error al conectar con el servidor", "error");
    },
  });
});

// ✏️ Editar multa
$("#tablaMultas").on("click", ".editar-multa", function () {
  const id = $(this).data("id");

  $.post(
    "../Admin/Procesos/php/multas.php",
    { accion: "obtener_multa", id },
    function (res) {
      const r = typeof res === "string" ? JSON.parse(res) : res;
      // if (!verificarSesion(r)) return;

      // completar formulario
      $("#id_multa").val(r.id);
      $("#fecha").val(r.Fecha);
      $("#fechainfraccion").val(r.FechaInfraccion);
      $("#vencimiento").val(r.Vencimiento);
      $("#municipio").val(r.Municipio);
      $("#patente").val(r.Patente);
      $("#empleado").val(`${r.idEmpleado},${r.Empleado}`);
      $("#importe").val(r.Importe);
      $("#estado").val(r.Estado);
      $("#numero").val(r.Numero);
      $("#motivo").val(r.Motivo);

      instanciaModal.show();
    }
  );
});

// 🗑️ Eliminar multa
$("#tablaMultas").on("click", ".eliminar-multa", function () {
  const id = $(this).data("id");

  Swal.fire({
    title: "¿Eliminar multa?",
    text: "Esta acción marcará la multa como eliminada.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "../Admin/Procesos/php/multas.php",
        { accion: "eliminar_multa", id },
        function (res) {
          const r = typeof res === "string" ? JSON.parse(res) : res;
          // if (!verificarSesion(r)) return;

          if (r.success) {
            Swal.fire("Eliminado", "La multa fue eliminada.", "success");
            $("#tablaMultas").DataTable().ajax.reload();
          } else {
            Swal.fire(
              "Error",
              r.error || "No se pudo eliminar la multa",
              "error"
            );
          }
        }
      );
    }
  });
});
