$(document).ready(function () {
  // Helper: Title Case español con excepciones
  function toTitleCaseEs(str = "") {
    const small = [
      "de",
      "del",
      "la",
      "las",
      "el",
      "los",
      "y",
      "e",
      "a",
      "al",
      "en",
      "por",
      "para",
      "con",
      "o",
      "u",
    ];
    return String(str)
      .toLowerCase()
      .split(/\s+/)
      .map((w, i) => {
        const ww = w.normalize("NFD").replace(/\p{Diacritic}/gu, ""); // conserva acentos visuales al capitalizar
        if (i > 0 && small.includes(w)) return w; // minúscula si no es la primera
        return w.charAt(0).toUpperCase() + w.slice(1);
      })
      .join(" ");
  }

  const dt = $("#tablaLocalidades").DataTable({
    ajax: {
      url: "Proceso/php/localidades.php", // ✅ ruta corregida
      type: "POST",
      dataType: "json",
      cache: false,
      data: { Localidades: 1 },
      dataSrc: function (json) {
        if (!json || !Array.isArray(json.data)) {
          console.error("Respuesta JSON inválida para DataTables:", json);
          return [];
        }
        return json.data;
      },
      error: function (xhr) {
        console.error("AJAX ERROR:", xhr.status, xhr.responseText);
      },
    },

    // 8 columnas: ID, Localidad, Provincia, Recorrido, Km, Web, Cp, Modificar
    // ... dentro de columns:
    columns: [
      { data: "id", defaultContent: "" },
      {
        data: "Localidad",
        render: function (val, type) {
          if (type !== "display") return val; // no tocar orden/búsqueda
          return toTitleCaseEs(val);
        },
      },
      {
        data: "Provincia",
        render: function (val, type) {
          if (type !== "display") return val;
          return toTitleCaseEs(val);
        },
      },
      { data: "Recorrido", defaultContent: "" },
      { data: "Km", defaultContent: "" },
      {
        data: "Web",
        defaultContent: 0,
        className: "text-center",
        render: function (val) {
          return String(val) === "1" || val === 1
            ? '<i class="mdi mdi-check-circle text-success"></i>'
            : '<i class="mdi mdi-close-circle text-danger"></i>';
        },
      },
      { data: "Cp", defaultContent: "" },

      // ✅ NUEVA COLUMNA "Días" con badges
      {
        data: "DiaSalida",
        defaultContent: "",
        className: "text-wrap",
        render: function (val, type) {
          if (type !== "display") return val || "";
          const dias = (val || "")
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean);
          if (!dias.length) return "<span class='text-muted'>—</span>";

          // Opcional: abreviar (Lun, Mar, Mié, ...)
          const abbr = {
            Lunes: "Lun",
            Martes: "Mar",
            Miércoles: "Mié",
            Jueves: "Jue",
            Viernes: "Vie",
            Sábado: "Sáb",
            Domingo: "Dom",
          };

          return dias
            .map(
              (d) =>
                `<span class="badge bg-secondary me-1 mb-1">${
                  abbr[d] || d
                }</span>`
            )
            .join("");
        },
      },

      {
        data: null,
        orderable: false,
        className: "text-center",
        defaultContent: "",
        render: function (data, type, row) {
          return `
        <a href="#" class="text-warning ms-2 btn-edit" title="Editar" data-id="${row.id}">
          <i class="mdi mdi-pencil mdi-18px"></i>
        </a>`;
        },
      },
    ],
    columnDefs: [
      { targets: 1, responsivePriority: 1 }, // Localidad
      { targets: -1, responsivePriority: 1 }, // Modificar
      { targets: 7, responsivePriority: 2 }, // Días
      { targets: [4, 5, 6], responsivePriority: 3 }, // Km, Web, Cp
    ],

    order: [[1, "asc"]],
    responsive: true,
    pageLength: -1,
    lengthMenu: [[-1], ["Todos"]],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
  });

  // Abrir modal NUEVA
  $("#btnAgregarLocalidad").on("click", function () {
    $("#formNuevaLocalidad")[0].reset();
    const modal = new bootstrap.Modal(
      document.getElementById("modalNuevaLocalidad")
    );
    modal.show();
  });

  // Guardar NUEVA
  $("#formNuevaLocalidad").on("submit", function (e) {
    e.preventDefault();

    const payload = {
      AgregarLocalidad: 1,
      Localidad: $("#nuevo_Localidad").val(),
      Provincia: $("#nuevo_Provincia").val(),
      Recorrido: $("#nuevo_Recorrido").val(),
      Km: $("#nuevo_Km").val(),
      Cp: $("#nuevo_Cp").val(),
      Web: $("#nuevo_Web").is(":checked") ? 1 : 0, // ✅ booleano 0/1
      DiaSalida: ($("#nuevo_DiaSalida").val() || []).join(","), // ✅ múltiple → string
    };

    $.ajax({
      url: "Proceso/php/localidades.php",
      type: "POST",
      data: payload,
      dataType: "json",
      success: function (resp) {
        if (resp && resp.ok) {
          $("#modalNuevaLocalidad").modal("hide");
          $("#formNuevaLocalidad")[0].reset();
          dt.ajax.reload(null, false);
        } else {
          console.error("Error de negocio:", resp);
          alert(resp?.msg || "No se pudo crear la localidad.");
        }
      },
      error: function (xhr) {
        console.error("AJAX SAVE ERROR:", xhr.status, xhr.responseText);
        alert("Error de servidor al guardar.");
      },
    });
  });

  // Abrir modal EDITAR (hoy edita: Localidad, Provincia, Recorrido, Km)
  $(document).on("click", ".btn-edit", function (e) {
    e.preventDefault();
    const rowData = dt.row($(this).closest("tr")).data();
    if (!rowData) return;

    $("#edit_id").val(rowData.id);
    $("#edit_Localidad").val(rowData.Localidad || "");
    $("#edit_Provincia").val(rowData.Provincia || "");
    $("#edit_Recorrido").val(rowData.Recorrido || "");
    $("#edit_Km").val(rowData.Km || "");
    $("#edit_Cp").val(rowData.Cp || "");

    // Web 0/1
    $("#edit_Web").prop(
      "checked",
      String(rowData.Web) === "1" || rowData.Web === 1
    );

    // Días múltiples: "Lunes,Martes" -> selecciona opciones
    const dias = (rowData.DiaSalida || "")
      .split(",")
      .map((s) => s.trim())
      .filter(Boolean);
    $("#edit_DiaSalida").val(dias);

    const modal = new bootstrap.Modal(
      document.getElementById("modalEditarLocalidad")
    );
    modal.show();
  });

  $("#formEditarLocalidad").on("submit", function (e) {
    e.preventDefault();

    const payload = {
      ActualizarLocalidad: 1,
      id: $("#edit_id").val(),
      Localidad: $("#edit_Localidad").val(),
      Provincia: $("#edit_Provincia").val(),
      Recorrido: $("#edit_Recorrido").val(),
      Km: $("#edit_Km").val(),
      Cp: $("#edit_Cp").val(),
      Web: $("#edit_Web").is(":checked") ? 1 : 0,
      DiaSalida: ($("#edit_DiaSalida").val() || []).join(","),
    };

    $.ajax({
      url: "Proceso/php/localidades.php",
      type: "POST",
      data: payload,
      dataType: "json",
      success: function (resp) {
        if (resp && resp.ok) {
          $("#modalEditarLocalidad").modal("hide");
          dt.ajax.reload(null, false);
        } else {
          console.error("Error de negocio (editar):", resp);
          alert(resp?.msg || "No se pudo actualizar la localidad.");
        }
      },
      error: function (xhr) {
        console.error("AJAX UPDATE ERROR:", xhr.status, xhr.responseText);
        alert("Error de servidor al actualizar.");
      },
    });
  });

  // Botón Descargar Excel con opciones
  $(document).on("click", "#btnExportarExcel", function (e) {
    e.preventDefault();

    Swal.fire({
      title: "¿Qué localidades querés exportar?",
      html: `
      <div class="d-flex flex-column align-items-center mt-3">
        <button id="btnSoloCaddy" class="swal2-confirm btn btn-success w-75 mb-2">
          <i class="mdi mdi-truck me-1"></i> Solo visitadas por Caddy
        </button>
        <button id="btnTodas" class="swal2-confirm btn btn-info w-75">
          <i class="mdi mdi-earth me-1"></i> Todas las localidades
        </button>
      </div>
    `,
      showConfirmButton: false,
      showCancelButton: true,
      cancelButtonText: "Cancelar",
      focusConfirm: false,
      customClass: {
        popup: "p-4 rounded-3",
      },
      didOpen: () => {
        // Acciones al hacer clic en las opciones
        $("#btnSoloCaddy").on("click", function () {
          Swal.close();
          window.location.href =
            "/sistema.caddy.com.ar/SistemaTriangular/Logistica/Proceso/php/export_localidades.php?solo_web=1";
        });
        $("#btnTodas").on("click", function () {
          Swal.close();
          window.location.href =
            "/sistema.caddy.com.ar/SistemaTriangular/Logistica/Proceso/php/export_localidades.php?solo_web=0";
        });
      },
    });
  });
});
