// SistemaTriangular/Admin/Procesos/js/valorxkilometro.js
(function () {
  const $tabla = $("#tablaValorxKilometro");
  let dt;
  const URL = "/SistemaTriangular/Admin/Procesos/php/valorxkilometro.php";

  function formatearMoneda(valor) {
    valor = parseFloat(valor || 0);
    return (
      "$ " +
      valor.toLocaleString("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    );
  }

  function abrirModal(data) {
    $("#vkm_id").val(data ? data.id : "");
    $("#vkm_segmento").val(data ? data.Segmento : "");
    $("#vkm_nombre").val(data ? data.Nombre : "");
    $("#vkm_valorkm").val(data ? data.ValorKm : "");
    $("#vkm_activo").prop("checked", data ? parseInt(data.Activo) === 1 : true);

    $("#modalValorxKilometroLabel").text(
      data ? "Editar segmento" : "Agregar segmento",
    );

    const modal = bootstrap.Modal.getOrCreateInstance(
      document.getElementById("modalValorxKilometro"),
    );
    modal.show();
  }

  function guardar() {
    const payload = {
      action: "guardar",
      id: $("#vkm_id").val(),
      Segmento: $("#vkm_segmento").val(),
      Nombre: $("#vkm_nombre").val(),
      ValorKm: $("#vkm_valorkm").val(),
      Activo: $("#vkm_activo").is(":checked") ? 1 : 0,
    };

    if (!payload.Segmento || !payload.Nombre) {
      Swal.fire("Faltan datos", "Completá Segmento y Nombre.", "warning");
      return;
    }

    $.post(URL, payload, function (json) {
      if (!json || !json.ok) {
        Swal.fire("Error", (json && json.error) || "No se pudo guardar.", "error");
        return;
      }

      bootstrap.Modal.getInstance(
        document.getElementById("modalValorxKilometro"),
      ).hide();

      Swal.fire({
        icon: "success",
        title: "Guardado",
        timer: 1200,
        showConfirmButton: false,
      });

      dt.ajax.reload();
    }, "json");
  }

  function toggleActivo(id, activoActual) {
    const nuevoActivo = parseInt(activoActual) === 1 ? 0 : 1;
    const accionTexto = nuevoActivo === 1 ? "reactivar" : "desactivar";

    Swal.fire({
      title: `¿Confirmás ${accionTexto} este segmento?`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí",
      cancelButtonText: "Cancelar",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      $.post(
        URL,
        { action: "toggle", id: id, Activo: nuevoActivo },
        function (json) {
          if (!json || !json.ok) {
            Swal.fire("Error", (json && json.error) || "No se pudo actualizar.", "error");
            return;
          }
          dt.ajax.reload();
        },
        "json",
      );
    });
  }

  function initDT() {
    dt = $tabla.DataTable({
      processing: true,
      serverSide: false,
      searching: true,
      lengthChange: true,
      pageLength: 25,
      order: [[0, "asc"]],
      ajax: {
        url: URL,
        type: "POST",
        data: { action: "listar" },
        dataSrc: function (json) {
          if (!json || !json.ok) return [];
          return json.data || [];
        },
      },
      columns: [
        { data: "Segmento" },
        { data: "Nombre" },
        {
          data: "ValorKm",
          render: function (data, type) {
            if (type !== "display") return data;
            return formatearMoneda(data);
          },
        },
        {
          data: "Activo",
          render: function (data, type) {
            if (type !== "display") return data;
            return parseInt(data) === 1
              ? '<span class="badge bg-success">Activo</span>'
              : '<span class="badge bg-secondary">Inactivo</span>';
          },
        },
        {
          data: null,
          orderable: false,
          render: function (data, type, row) {
            return `
              <button class="btn btn-sm btn-outline-primary btn-editar" data-id="${row.id}">
                <i class="mdi mdi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger btn-toggle" data-id="${row.id}" data-activo="${row.Activo}">
                <i class="mdi ${parseInt(row.Activo) === 1 ? "mdi-close" : "mdi-check"}"></i>
              </button>
            `;
          },
        },
      ],
    });

    $tabla.on("click", ".btn-editar", function () {
      const row = dt.row($(this).closest("tr")).data();
      abrirModal(row);
    });

    $tabla.on("click", ".btn-toggle", function () {
      toggleActivo($(this).data("id"), $(this).data("activo"));
    });
  }

  $("#btnNuevoSegmento").on("click", function () {
    abrirModal(null);
  });

  $("#btnGuardarSegmento").on("click", guardar);

  initDT();
})();
