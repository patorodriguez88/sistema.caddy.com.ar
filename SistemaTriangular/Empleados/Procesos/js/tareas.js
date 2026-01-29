// Reemplaza tu fecha_formato y agrega este helper

function parseDateSafe(raw) {
  if (raw == null) return null;
  let s = String(raw).trim();
  if (!s || s === "0000-00-00") return null;

  // Timestamp numérico
  if (/^\d+$/.test(s)) {
    const n = Number(s);
    // si parece segundos, pásalo a ms
    return new Date(n < 1e12 ? n * 1000 : n);
  }

  // ISO tipo "YYYY-MM-DD" o "YYYY-MM-DD HH:mm:ss"
  if (/^\d{4}-\d{2}-\d{2}(?:\s+\d{2}:\d{2}:\d{2})?$/.test(s)) {
    if (s.includes(" ")) s = s.replace(" ", "T"); // "YYYY-MM-DDTHH:mm:ss"
    return new Date(s);
  }

  // "YYYY/MM/DD"
  if (/^\d{4}\/\d{2}\/\d{2}$/.test(s)) {
    const [y, m, d] = s.split("/").map(Number);
    return new Date(y, m - 1, d);
  }

  // "DD/MM/YYYY"
  if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(s)) {
    const [d, m, y] = s.split("/").map(Number);
    return new Date(y, m - 1, d);
  }

  // "DD-MM-YYYY"
  if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(s)) {
    const [d, m, y] = s.split("-").map(Number);
    return new Date(y, m - 1, d);
  }

  // "2-Apr-2024" (MMM en inglés)
  if (/^\d{1,2}-[A-Za-z]{3}-\d{4}$/.test(s)) {
    // Dejalo a Date, este formato lo soporta en la mayoría de navegadores
    return new Date(s);
  }

  // Último intento: que el motor lo resuelva
  const d = new Date(s);
  return isNaN(d.getTime()) ? null : d;
}

function fecha_formato(
  raw,
  locale = "es-AR",
  opts = { day: "2-digit", month: "short", year: "numeric" },
) {
  const d = parseDateSafe(raw);
  if (!d) return ""; // evita el RangeError
  return d.toLocaleDateString(locale, opts);
}

function htmlToPlainText(html) {
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
}
function modificarTarea(id) {
  $.ajax({
    type: "POST",
    url: "Procesos/php/tareas.php",
    data: { ObtenerTarea: 1, id: id },
    success: function (data) {
      var tarea = JSON.parse(data);
      $("#tarea_id").val(tarea.id);
      $("#tarea_nombre").val(tarea.NombreTarea);
      $("#tarea_puntos").val(tarea.Puntos);
      $("#tarea_completada").prop("checked", tarea.Completada == 1);
      const m = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("modalEditarTarea"),
      );
      m.show();
    },
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Maneja el error al cargar la tarea.
     * Muestra un mensaje de error con un título "Error" y un mensaje "No se pudo cargar la tarea."
     */
    /*******  17d390a2-fc24-4924-aa97-cd5882c2feff  *******/
    error: function () {
      Swal.fire("Error", "No se pudo cargar la tarea.", "error");
    },
  });
}
function guardarCambiosTarea() {
  let id = $("#tarea_id").val();
  let puntos = $("#tarea_puntos").val();
  let completada = $("#tarea_completada").is(":checked") ? 1 : 0;

  $.ajax({
    type: "POST",
    url: "Procesos/php/tareas.php",
    data: {
      ActualizarTarea: 1,
      id: id,
      Puntos: puntos,
      Completada: completada,
    },
    success: function (data) {
      let response = JSON.parse(data);
      if (response.success) {
        $("#modalEditarTarea").modal("hide");
        Swal.fire("¡Actualizado!", "La tarea fue modificada.", "success");
        $("#table_tareas_lista").DataTable().ajax.reload(null, false);
      } else {
        Swal.fire(
          "Error",
          response.message || "No se pudo actualizar la tarea.",
          "error",
        );
      }
    },
    error: function () {
      Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
    },
  });
}
function renderizar_tareas_lista(status) {
  $("#details").css("display", "none");
  $("#tareas").css("display", "none");
  $("#crear_tarea").css("display", "none");
  $("#asana").css("display", "none");
  $("#dashboard_puntos").hide();
  $("#graficos_puntos").hide();
  $("#tareas_lista_puntajes").hide();

  $("#tareas_lista").css("display", "block");

  if ($.fn.DataTable.isDataTable("#table_tareas_lista")) {
    $("#table_tareas_lista").DataTable().clear().destroy();
  }

  nivel(function (nivelUsuario) {
    console.log("nivel usuario", nivelUsuario);
    // Definimos las columnas base
    var columnasBase = [
      {
        data: "FechaCarga",
        render: function (data, type, row) {
          const esAsana =
            row.gid_asana !== null &&
            row.gid_asana !== undefined &&
            row.gid_asana !== "0" &&
            row.gid_asana !== "";

          const sistema = esAsana ? "Asana" : "Hubspot";
          const sistema_color = esAsana ? "dark" : "danger";
          var Fecha = row.FechaCarga.split("-").reverse().join(".");

          return (
            '<span style="display: none;">' +
            row.FechaCarga +
            "</span>" +
            Fecha +
            "<br>" +
            '<span class="badge bg-' +
            sistema_color +
            ' text-white">' +
            sistema +
            "</span>"
          );
        },
      },
      {
        data: "NombreTarea",
        render: function (data, type, row) {
          return `${row.NombreTarea}<br><b class="text-primary">${row.Responsable}</b>`;
        },
      },
      {
        data: "Puntos",

        render: function (data, type, row) {
          return `${row.Puntos} <i class="mdi mdi-pencil mdi-18px text-warning ms-2" style="cursor:pointer;" onclick="modificarTarea(${row.id})"></i>`;
        },
      },
      {
        data: "FechaEntrega",
        render: function (data, type, row) {
          var Fecha = row.FechaEntrega.split("-").reverse().join(".");
          return (
            '<span style="display: none;">' +
            row.FechaEntrega +
            "</span>" +
            Fecha
          );
        },
      },
      {
        data: "Completada",
        render: function (data) {
          if (data == true) {
            return '<span class="badge bg-success text-white">Finalizada</span>';
          } else {
            return '<span class="badge bg-warning text-white">Pendiente</span>';
          }
        },
      },
      {
        data: function (row) {
          let gid = row.gid_asana || row.gid_hubspot;
          let baseUrl = row.gid_asana
            ? "https://app.asana.com/app/asana/-/get_asset?asset_id=" +
              row.gid_asana
            : "https://app.hubspot.com/tasks/23486798/view/all";
          return (
            '<a target="_blank" href="' +
            baseUrl +
            gid +
            '"><i class="mdi mdi-link-variant"></i></a>'
          );
        },
      },
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          if (nivelUsuario != 1) return "";
          return `<i class="mdi mdi-file-document-outline mdi-18px text-warning ms-2" style="cursor:pointer;" onclick="eliminarTarea(${row.id})"></i>`;
        },
      },
    ];
    // Si es nivel 1, agregamos la columna para editar
    // if (nivelUsuario == 1) {
    //   const esAdmin = Number(nivelUsuario) === 1;

    //   $(".col-editar").show();
    //   columnasBase.push({
    //     data: null,
    //     orderable: false,
    //     render: function (data, type, row) {
    //       if (!esAdmin) return "";
    //       return `<i class="mdi mdi-file-document-outline mdi-18px text-warning ms-2" style="cursor:pointer;" onclick="eliminarTarea(${row.id})"></i>`;
    //     },
    //   });
    // }

    // Ahora sí, creamos la tabla
    $("#table_tareas_lista").DataTable({
      paging: false,
      searching: false,
      ajax: {
        url: "Procesos/php/tareas.php",
        data: {
          Tareas_lista: 1,
          Estado: status,
        },
        type: "post",
      },
      columns: columnasBase,
    });
  });
}

function renderizarDashboardPuntos(resumen, evolucionMensual) {
  $("#dashboard_puntos").show();
  $("#graficos_puntos").show();

  let cardsHTML = "";
  resumen.forEach((emp) => {
    const puntos = parseInt(emp.Puntos) || 0;
    const estrellas = Math.min(10, Math.floor(puntos / 10)); // 1 estrella cada 10 puntos

    cardsHTML += `
      <div class="col-md-3">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">${emp.Responsable}</h5>
            <p class="card-text">Puntos: <strong>${puntos}</strong></p>
            <div class="rateit estrellas-amarillas"
              data-rateit-value="${estrellas}"
              data-rateit-ispreset="true"
              data-rateit-readonly="true"
              data-rateit-mode="font"
              data-rateit-starwidth="24"
              data-rateit-starheight="24"
              data-rateit-max="10">
            </div>
          </div>
        </div>
      </div>`;
  });

  $("#dashboard_puntos").html(cardsHTML);

  // Preparar gráfico
  const labels = evolucionMensual.map((e) => e.mes);
  const data = evolucionMensual.map((e) => e.puntos);

  const ctx = document
    .getElementById("grafico_evolucion_puntos")
    .getContext("2d");
  if (window.graficoPuntos) {
    window.graficoPuntos.destroy();
  }
  window.graficoPuntos = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Puntos acumulados",
          data: data,
          backgroundColor: "rgba(54, 162, 235, 0.6)",
          borderColor: "rgba(54, 162, 235, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
  $(".rateit").rateit(); // Esto re-inicializa por si se genera dinámicamente
}
function cargarDashboardPuntos(FechaInicio, FechaFinal) {
  $.post(
    "Procesos/php/tareas.php",
    {
      DashboardPuntos: 1,
      FechaInicio: FechaInicio,
      FechaFinal: FechaFinal,
    },
    function (resp) {
      renderizarDashboardPuntos(resp.resumen, resp.evolucion);
    },
    "json",
  );
}

function renderizar_tareas_puntos(fechas) {
  // Mostrar siempre el contenedor con el input de fechas
  $("#details").hide();
  $("#tareas").hide();
  $("#crear_tarea").hide();
  $("#asana").hide();
  $("#tareas_lista_puntajes").show();
  $("#tareas_lista").hide();
  // $("#dashboard_puntos").hide();
  // $("#graficos_puntos").hide();
  // $("#tareas_lista_puntajes").hide();

  // Validación: si no hay fechas, corto antes de ejecutar la tabla
  if (!fechas || !fechas.includes(" - ")) {
    console.warn("No se proporcionó un rango de fechas válido.");
    return;
  }

  // Procesar fechas: dd/mm/yyyy → yyyy-mm-dd
  var partes = fechas.split(" - ");
  var partesInicio = partes[0].split("/");
  var partesFinal = partes[1].split("/");

  var FechaInicio = `${partesInicio[2]}-${partesInicio[0]}-${partesInicio[1]}`;
  var FechaFinal = `${partesFinal[2]}-${partesFinal[0]}-${partesFinal[1]}`;

  var table_contact = $("#table_tareas_lista_puntajes").DataTable();
  table_contact.destroy();

  $("#table_tareas_lista_puntajes").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "Procesos/php/tareas.php",
      type: "post",
      data: {
        Tareas_lista_puntos: 1,
        FechaInicio: FechaInicio,
        FechaFinal: FechaFinal,
      },
    },
    columns: [
      {
        data: "FechaEntrega",
        render: function (data, type, row) {
          if (!row.FechaEntrega) return "";
          var Fecha = row.FechaEntrega.split("-").reverse().join(".");
          return (
            '<span style="display:none;">' +
            row.FechaEntrega +
            "</span>" +
            Fecha
          );
        },
      },
      { data: "Responsable" },
      { data: "Puntos" },
    ],
  });

  // También cargamos el dashboard
  cargarDashboardPuntos(FechaInicio, FechaFinal);
}
$("#api_puntos").click(function () {
  renderizar_tareas_puntos();
});

//CARGAR ASANA
function cargar_asana(gid) {
  console.log("gid:", gid);

  $.ajax({
    url: "Procesos/php/asana_api.php",
    type: "POST",
    data: { Task: 1, gid: gid },
    dataType: "json", // 👈 evita JSON.parse manual
    success: function (resp) {
      if (!resp || !resp.data) {
        console.warn("Respuesta sin data:", resp);
        return;
      }
      const d = resp.data;

      // Ocultar otras vistas
      $("#tareas, #details, #asana").hide();

      // Mostrar modal (Bootstrap 5)
      const modalEl = document.getElementById("crearTareaModal"); // 👈 asegurate que el ID exista en el HTML
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();

      // Completar campos con null-safety
      $("#crear_tarea_titulo")
        .val(d.name ?? "")
        .prop("readonly", true);
      $("#crear_tarea_descripcion")
        .val(d.notes ?? "")
        .prop("readonly", true);

      // Fecha de carga -> d.created_at viene tipo "2025-03-19T12:34:56.000Z"
      const created = (d.created_at || "").split("T")[0] || "";
      $("#crear_tarea_fecha_carga").val(created).prop("readonly", true);

      // due_on puede venir null
      $("#crear_tarea_fecha_entrega")
        .val(d.due_on ?? "")
        .prop("readonly", true);

      $("#crear_tarea_puntos").val(""); // si no aplica
      $("#crear_tarea_usuario_asana").show().prop("readonly", true);

      // Mostrar campo/valor de Asana
      $("#div_id_hubspot").hide();
      $("#div_id_asana").show();
      $("#gid_asana").val(d.gid ?? "");

      // Responsable
      const assigneeGid = d.assignee?.gid ?? d.assignee_id ?? "";
      let nombreUsuario = "";
      try {
        // Si NombreUsuario es sync; si es async, adaptalo con await/Promises
        nombreUsuario = NombreUsuario("asana", assigneeGid) || "";
      } catch (e) {
        console.warn("NombreUsuario falló:", e);
      }
      $("#crear_tarea_usuario_asana").val(nombreUsuario).prop("readonly", true);
      $("#crear_tarea_usuario_badge").text(assigneeGid);

      // Completada / Creador
      $("#crear_tarea_completada")
        .val(String(d.completed ?? false))
        .prop("readonly", true);
      $("#crear_tarea_creador")
        .val(d.created_by?.gid ?? "")
        .prop("readonly", true);
    },
    error: function (xhr) {
      console.error("AJAX error:", xhr.status, xhr.responseText);
      // Si ves que responseText empieza con "<", el backend está mandando warnings/HTML.
      // Asegurate en asana_api.php de hacer:
      //   ini_set('display_errors', 0);
      //   header('Content-Type: application/json; charset=UTF-8');
      //   echo json_encode([...]);
    },
  });
}
// CARGAR HUBSPOT
function cargar_hubspot(gid) {
  console.log("HubSpot gid", gid);

  $.ajax({
    data: { Task: 1, gid: gid },
    url: "Procesos/php/hubspot_api.php", // o un nuevo archivo si preferís separarlo
    type: "POST",
    dataType: "json",
    success: function (jsonData) {
      // var jsonData = JSON.parse(data);
      if (jsonData && jsonData.data) {
        console.log("HubSpot datos", jsonData);

        $("#tareas").hide();
        $("#details").hide();
        $("#asana").hide();

        // $("#crear_tarea").show();

        const modalEl = document.getElementById("crearTareaModal"); // 👈 asegurate que el ID exista en el HTML
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        // Título y descripción
        $("#crear_tarea_titulo").val(jsonData.data.name).prop("readonly", true);

        // Luego al mostrarlo:
        $("#crear_tarea_descripcion")
          .val(htmlToPlainText(jsonData.data.body))
          .prop("readonly", true);
        // Fecha de creación (no siempre disponible)
        let fechaCarga = jsonData.data.created_at;
        let fecha = fechaCarga.split("T")[0];
        $("#crear_tarea_fecha_carga").val(fecha).prop("readonly", true);

        // Fecha de entrega
        let fechaEntrega = jsonData.data.due_on;
        let FechaEntrega = fechaEntrega.split("T")[0];

        $("#crear_tarea_fecha_entrega")
          .val(FechaEntrega ?? "")
          .prop("readonly", true);

        $("#crear_tarea_puntos").val("");
        $("#crear_tarea_usuario_asana").show().prop("readonly", true);

        // Guardar el ID
        $("#div_id_hubspot").show();
        $("#div_id_asana").hide();
        $("#gid_hubspot").val(jsonData.data.gid);

        // Asignado (como string visible)
        $("#crear_tarea_usuario_badge").html(jsonData.data.assignee_id ?? "");
        $("#crear_tarea_completada")
          .val(jsonData.data.completed)
          .prop("readonly", true);

        $("#crear_tarea_creador")
          .val(jsonData.data.creator_id ?? "")
          .prop("readonly", true);
        let nombre_usuario_tarea = NombreUsuario(
          "hubspot",
          jsonData.data.assignee_id,
        );

        $("#crear_tarea_usuario_asana")
          .val(nombre_usuario_tarea ?? "")
          .prop("readonly", true);
      } else {
        console.log("HubSpot datos", jsonData);
      }
    },
  });
}
function NombreUsuario(sistena, gid) {
  $.ajax({
    data: { NombreUsuario: 1, gid: gid, Sistema: sistena },
    url: "Procesos/php/tareas.php",
    type: "POST",
    dataType: "json",
    success: function (jsonData) {
      // var jsonData = JSON.(data);
      // $('#crear_tarea_usuario_badge').html(jsonData.Usuario).prop('readonly', true);
      $("#crear_tarea_usuario_asana").val(jsonData.NombreUsuario);
    },
  });
}

function obtenerUsuarios(gid) {
  $.ajax({
    data: { Usuarios: 1, gid: gid },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var opciones = JSON.parse(data);
      $("#crear_tarea_usuario_badge").html(opciones.Usuario);
      return opciones.Nombre;
      // opciones.forEach(function(opcion) {
      //     // $('#crear_tarea_usuario').append('<option value="' + opcion.Usuario + '">' + opcion.Nombre + '</option>');
      //     console.log('opciones',opcion.Nombre);
      //     return opcion.Nombre;
      // });
    },
    error: function () {
      alert("Error al obtener las opciones.");
    },
  });
}

function zeroFill(number, width) {
  // Convierte el número a una cadena
  var str = number.toString();
  // Agrega ceros a la izquierda hasta alcanzar la anchura deseada
  while (str.length < width) {
    str = "0" + str;
  }
  return str;
}

function nivel(callback) {
  console.log("nivel aca");
  $.ajax({
    data: { Nivel: 1 },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      callback(jsonData.Nivel); // Llamar al callback con el valor de Nivel
    },
    error: function () {
      alert("Error al cargar el contenido.");
    },
  });
}

function renderizar_tareas(status) {
  $.ajax({
    data: { Tareas: 1, Estado: status },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      $("#tareas").html(data);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
}

function renderizar_tareas_(status) {
  $.ajax({
    data: { Tareas_kanban: 1, Estado: status },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      $("#tareas").html(data);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
}

function renderizar_totales() {
  $.ajax({
    data: { Totales: 1 },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      // Si alguno de los datos es null, conviértelo en 0
      var totales = jsonData.Totales !== null ? jsonData.Totales : 0;
      var finalizados =
        jsonData.Finalizados !== null ? jsonData.Finalizados : 0;
      var pendientes = jsonData.Pendientes !== null ? jsonData.Pendientes : 0;
      var productividad =
        jsonData.Productividad !== null ? jsonData.Productividad : 0;

      var totales_puntos =
        jsonData.Totales_Puntos !== null ? jsonData.Totales_Puntos : 0;
      var finalizados_puntos =
        jsonData.Totales_Finalizados !== null
          ? jsonData.Totales_Finalizados
          : 0;
      var pendientes_puntos =
        jsonData.Totales_Pendientes !== null ? jsonData.Totales_Pendientes : 0;

      // Mostrar los datos en tu HTML
      $("#total_tareas").html(totales);
      $("#total_finalizadas").html(finalizados);
      $("#total_pendientes").html(pendientes);
      $("#total_productividad").html(`${productividad}%`);
      $("#total_tareas_puntos").html(totales_puntos + " Puntos");
      $("#total_finalizadas_puntos").html(finalizados_puntos + " Puntos");
      $("#total_pendientes_puntos").html(pendientes_puntos + " Puntos");
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
}

function renderizar_comentarios(gid_asana) {
  $.ajax({
    data: { Tareas_comentarios: 1, gid_asana: gid_asana },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      $("#comments").html(data);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });

  $.ajax({
    data: { Tareas_comentarios_total: 1, id: gid_asana },
    url: "Procesos/php/tareas.php",
    type: "POST",
    dataType: "json",
    success: function (jsonData) {
      var jsonData = JSON.parse(jsonData);
      $("#comments_title").html("Comentarios (" + jsonData.Total + ")");
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
}

function renderizar_archivos(id) {
  $.ajax({
    data: { Tareas_archivos: 1, gid_asana: id },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      $("#archivos").html(data);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
}

$("#tareas_lista_puntajes_fechas").on(
  "apply.daterangepicker",
  function (ev, picker) {
    var rango = $(this).val(); // formato tipo: "01/04/2025 - 20/04/2025"
    console.log("Nuevo rango seleccionado:", rango);

    // Acá recargás la tabla
    renderizar_tareas_puntos(rango);
  },
);

$("#marcar_finalizada").click(function () {
  var id = $("#id_tarea").html();
  $.ajax({
    data: { FinalizarTarea: 1, id: id },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      if (jsonData.success == 1) {
        $("#tareas").css("display", "block");
        $("#details").css("display", "none");

        $("#tarea_finalizada").modal("show");

        renderizar_tareas();
        renderizar_totales();
      } else {
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
    },
  });
});

$("#filtro_todos").click(function () {
  var elemento = document.getElementById("tareas");

  if (elemento.style.display === "block") {
    renderizar_tareas();
  } else {
    renderizar_tareas_lista();
  }
});

$("#filtro_en_proceso").click(function () {
  var status = "false";
  var elemento = document.getElementById("tareas");

  if (elemento.style.display === "block") {
    renderizar_tareas(status);
  } else {
    renderizar_tareas_lista(status);
  }
});

$("#filtro_finalizado").click(function () {
  var status = "true";
  var elemento = document.getElementById("tareas");

  if (elemento.style.display === "block") {
    renderizar_tareas(status);
  } else {
    renderizar_tareas_lista(status);
  }
});

$(document).ready(function () {
  // VERIFICAR NIVEL

  // $("#button_crear_tarea").hide(); //POR AHORA NO PERMITO CREAR TAREAS PORQUE ESTO SERA UNICAMENTE DESDE ASANA
  // Llamar a la función nivel y usar el callback
  nivel(function (nivel) {
    // console.log('nivel', nivel);

    if (nivel != 1) {
      $("#button_crear_tarea").hide();
      $("#api_asana").hide();
      $("#api_puntos").hide();
    } else {
      // $('#button_crear_tarea').show();
      $("#api_asana").show();
      $("#api_puntos").show();
    }
  });
  const standardModalEl = document.getElementById("standard-modal");
  const standardModal = bootstrap.Modal.getOrCreateInstance(standardModalEl);

  const detalleModalEl = document.getElementById("detalleTareaModal");
  const detalleModal = bootstrap.Modal.getOrCreateInstance(detalleModalEl);
  //   renderizar_tareas();

  renderizar_tareas_lista();
  renderizar_totales();
  obtenerUsuarios();
});

$("#subir_comentario").click(function () {
  var id = $("#id_tarea").html();
  var id_tarea = id.replace(/^#0*/, "");
  var comentario = $("#comentario-textarea").val();
  var gid_asana = $("#gid_asana_details").html();

  if (comentario) {
    $.ajax({
      data: {
        Subir_comentario: 1,
        id: id_tarea,
        Comentario: comentario,
        gid: gid_asana,
      },
      url: "Procesos/php/tareas.php",
      type: "POST",
      success: function (data) {
        $("#comentario-textarea").val("");
        renderizar_comentarios(gid_asana);
      },
      error: function () {
        alert("Error al obtener las opciones.");
      },
    });
  } else {
    $("#comentario-textarea").css("border", "1px solid red");
  }
});

$("#crear_tarea_usuario").change(function () {
  var valorSeleccionado = $(this).val();
  // console.log('Valor seleccionado:', valorSeleccionado);
  $("#crear_tarea_usuario_badge").html(valorSeleccionado);
  // Aquí puedes ejecutar cualquier otra acción que desees con el valor seleccionado
});

$("#button_crear_tarea").click(function () {
  $("#tareas").css("display", "none");
  $("#details").css("display", "none");
  $("#crear_tarea").css("display", "block");

  $("#crear_tarea_titulo").val("");
  $("#crear_tarea_descripcion").val("");
  $("#crear_tarea_fecha_carga").val("");
  $("#crear_tarea_fecha_entrega").val("");
  $("#crear_tarea_puntos").val("");
  $("#crear_tarea_usuario").val("");
});

function ver_detalle(id) {
  console.log("id", id);
  var numRellenado = zeroFill(id, 4);

  $("#id_tarea").html("#" + numRellenado);
  $("#details").css("display", "block");
  $("#tareas").css("display", "none");

  // IMPORTANT: return the AJAX promise so callers can use .done/.fail
  return $.ajax({
    data: { Tareas_detalle: 1, id: id },
    url: "Procesos/php/tareas.php",
    type: "POST",
    dataType: "json",
  })
    .done(function (jsonData) {
      // Si la respuesta no contiene data válida, abortar temprano
      if (!jsonData || !jsonData.data || !jsonData.data[0]) {
        console.warn("Respuesta inválida en ver_detalle:", jsonData);
        return;
      }

      const d0 = jsonData.data[0];

      // Si la fecha de entrega ya pasó, deshabilitar puntos
      if (d0.FechaEntrega && new Date(d0.FechaEntrega) < new Date()) {
        $("#details_puntos").prop("disabled", true);
      } else {
        $("#details_puntos").prop("disabled", false);
      }

      $("#gid_asana_details").html(d0.gid_asana || "");
      $("#details_title").html(d0.NombreTarea || "");
      $("#details_description").html(d0.Descripcion || "");

      if (d0.FechaCarga) {
        $("#details_fecha_carga").html(fecha_formato(d0.FechaCarga));
      }

      if (d0.FechaEntrega) {
        $("#details_fecha_entrega").html(fecha_formato(d0.FechaEntrega));
      }

      $("#details_puntos").val(d0.Puntos || "");

      // Evalúa si el valor es 1 y devuelve true, de lo contrario, devuelve false
      var idchecked = d0.Modificar_Fecha_entrega ? true : false;
      $("#details_modificar_fecha").prop("checked", idchecked);

      $("#participantes").html(
        "Tarea creada por <b> " +
          (d0.UsuarioCarga || "") +
          "</b> asignada a <b>" +
          (d0.Responsable || "") +
          "</b>",
      );

      // Render auxiliares
      if (d0.gid_asana) {
        renderizar_comentarios(d0.gid_asana);
        renderizar_archivos(d0.gid_asana);
      }
    })
    .fail(function (xhr) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ha ocurrido un error al cargar el contenido.",
      });
      console.error("ver_detalle AJAX error:", xhr.status, xhr.responseText);
    });
}

$(".dripicons-checklist").click(function () {
  $("#details").css("display", "none");
  $("#tareas").css("display", "none");
  $("#crear_tarea").css("display", "none");
  $("#asana").css("display", "none");
  $("#tareas_lista").css("display", "block");

  renderizar_tareas_lista();
});

$(".dripicons-view-apps").click(function () {
  $("#details").css("display", "none");
  $("#crear_tarea").css("display", "none");
  $("#asana").css("display", "none");
  $("#tareas_lista").css("display", "none");
  $("#tareas").css("display", "block");

  renderizar_tareas();
});

$("#api_asana").click(function () {
  console.log("Cargando tareas de Asana y HubSpot...");

  $(
    "#details, #tareas, #tareas_lista, #crear_tarea, #tareas_lista_puntajes",
  ).hide();
  $("#asana").show();

  if ($.fn.DataTable.isDataTable("#table_asana")) {
    $("#table_asana").DataTable().clear().destroy();
  }

  Promise.all([
    $.ajax({
      url: "Procesos/php/asana_api.php",
      type: "post",
      dataType: "json",
      data: { Asana: 1 },
    }),
    $.ajax({
      url: "Procesos/php/hubspot_api.php",
      type: "post",
      dataType: "json",
      data: { Hubspot: 1 },
    }),
  ])
    .then(function ([asanaRes, hubspotRes]) {
      console.log("Asana RAW:", asanaRes);

      const asanaData = (asanaRes.data || []).map((t) => ({
        ...t,
        source: "Asana",
      }));
      const hubspotData = (hubspotRes.data || []).map((t) => ({
        ...t,
        source: "HubSpot",
      }));

      const combined = asanaData.concat(hubspotData);

      $("#table_asana").DataTable({
        paging: false,
        searching: true,
        order: [[4, "asc"]],
        data: combined,
        columns: [
          { data: "name", title: "Nombre", defaultContent: "" },
          { data: "assignee_name", title: "Responsable", defaultContent: "" },
          {
            data: "completed",
            title: "Completada",
            render: (data) => (data ? "Sí" : "No"),
          },
          {
            data: "created_by_resource_type",
            title: "Tipo",
            defaultContent: "",
          },
          {
            data: "due_on",
            title: "Fecha Vencimiento",
            render: function (data) {
              if (!data) return "";
              // para YYYY-MM-DD esto es suficiente (sin Date() para evitar TZ issues)
              const parts = data.split("-");
              if (parts.length !== 3) return data;
              return `<span style="display:none">${data}</span>${parts[2]}/${parts[1]}/${parts[0]}`;
            },
          },
          {
            data: "source",
            title: "Origen",
            render: (data) =>
              data === "Asana"
                ? '<span class="badge bg-info text-white">Asana</span>'
                : '<span class="badge bg-warning text-dark">HubSpot</span>',
          },
          {
            data: null,
            title: "Acciones",
            render: (row) => {
              const gid = row.gid || "";
              const onclick =
                row.source === "Asana"
                  ? `cargar_asana('${gid}')`
                  : `cargar_hubspot('${gid}')`;
              return `<a style="cursor:pointer" onclick="${onclick}" class="action-icon">
                        <i class="mdi mdi-source-branch-plus text-success"></i>
                      </a>`;
            },
          },
        ],
      });
    })
    .catch(function (err) {
      console.error("Error en Promise.all:", err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo cargar Asana/HubSpot. Mirá consola (Network).",
      });
    });
});

$("#crear_tarea_cnl").click(function () {
  $("#tareas").css("display", "block");
  $("#crear_tarea").css("display", "none");
  $("#asana").css("display", "none");
});

//CREAR TAREA
$("#crear_tarea_ok").on("click", function (e) {
  e.preventDefault();

  const $btn = $(this);
  $btn.prop("disabled", true);

  const payload = {
    Crear_Tareas: 1,
    Titulo: $("#crear_tarea_titulo").val(),
    Descripcion: $("#crear_tarea_descripcion").val(),
    Fecha_carga: $("#crear_tarea_fecha_carga").val(),
    Fecha_entrega: $("#crear_tarea_fecha_entrega").val(),
    Puntos: $("#crear_tarea_puntos").val(),
    Usuario: $("#crear_tarea_usuario_badge").html() || "",
    gid_asana: $("#gid_asana").val() || "",
    completado: $("#crear_tarea_completada").val() || "",
    creador: $("#crear_tarea_creador").val() || "",
    responsable_gid: $("#crear_tarea_usuario_badge").html() || "",
    responsable: $("#crear_tarea_usuario_asana").val() || "",
    gid_hubspot: $("#gid_hubspot").val() || "",
  };

  // Confirmación previa (opcional)
  Swal.fire({
    title: "¿Confirmar creación?",
    text: "Se guardará la nueva tarea con los datos ingresados.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, crear tarea",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#d33",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) {
      $btn.prop("disabled", false);
      return;
    }

    Swal.fire({
      title: "Creando tarea...",
      text: "Por favor espere unos segundos",
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    $.ajax({
      url: "Procesos/php/tareas.php",
      type: "POST",
      data: payload,
      dataType: "json",
      success: function (resp) {
        Swal.close(); // cierra el "loading"

        if (resp && resp.success == 1) {
          bootstrap.Modal.getOrCreateInstance(
            document.getElementById("crearTareaModal"),
          ).hide();

          $("#tareas").show();
          $("#crear_tarea").hide();
          renderizar_tareas_lista();

          Swal.fire({
            icon: "success",
            title: "Tarea creada",
            text: "La tarea se creó correctamente.",
            timer: 2000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al crear tarea",
            text: resp && resp.error ? resp.error : "No se cargó la tarea.",
            confirmButtonColor: "#d33",
          });
        }
      },
      error: function (xhr) {
        Swal.close();
        const respuesta = (xhr.responseText || "").trim().slice(0, 300);
        Swal.fire({
          icon: "error",
          title: "Error del servidor",
          html: `<pre style="text-align:left;white-space:pre-wrap;">${respuesta}</pre>`,
          confirmButtonColor: "#d33",
        });
        console.error("AJAX error", xhr.status, xhr.responseText);
      },
      complete: function () {
        $btn.prop("disabled", false);
      },
    });
  });
});
const standardModalEl = document.getElementById("standard-modal");
const standardModal = bootstrap.Modal.getOrCreateInstance(standardModalEl);
$("#details_delete").click(function () {
  var id = $("#id_tarea").html();
  var id_tarea = id.replace(/^#0*/, "");

  standardModal.show();

  $("#standard-modal-title").html(
    "Estas por eliminar la tarea " +
      id_tarea +
      " del sistema, esto no la eliminara de asana pero si le sacará la etiqueta de Cargado en el Sistema de Caddy, deseas continuar?.",
  );
  $("#standard-modal-ok").hide();
  $("#standard-modal-details-ok").show();
});

$("#standard-modal-details-ok").click(function () {
  var gid = $("#gid_asana_details").html();
  var id = $("#id_tarea").html();
  var id_tarea = id.replace(/^#0*/, "");

  $.ajax({
    data: { Eliminar_tarea: 1, id_tarea: id_tarea, gid_asana: gid },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);

      if (jsonData.success == 1) {
        standardModal.hide();
        bootstrap.Modal.getOrCreateInstance(
          document.getElementById("detalleTareaModal"),
        ).hide();

        $("#details").css("display", "none");

        renderizar_tareas();

        renderizar_totales();

        Swal.fire("¡Exito!", "Tarea eliminada correctamente.!", "success");
      } else {
      }
    },
    error: function () {
      alert("Error al cargar el contenido.");
    },
  });
});

$("#details_ok").click(function () {
  var id = $("#id_tarea").html();
  var id_tarea = id.replace(/^#0*/, "");
  var isChecked = $("#details_modificar_fecha").prop("checked");

  var puntos = $("#details_puntos").val();

  $.ajax({
    data: {
      Actualizar_tarea: 1,
      id_tarea: id_tarea,
      Puntos: puntos,
      Modificar_Fecha: isChecked,
    },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      if (jsonData.success == 1) {
        $("#tareas").css("display", "block");
        $("#details").css("display", "none");
        renderizar_tareas();
        renderizar_totales();
      }
    },
  });
});

$("#crear_tarea_usuario_asana").click(function () {
  if (
    $("#crear_tarea_usuario_asana").val() == null ||
    $("#crear_tarea_usuario_asana").val() == ""
  ) {
    $.ajax({
      data: { Tareas_radios: 1 },
      url: "Procesos/php/tareas.php",
      type: "POST",
      dataType: "json",
      success: function (data) {
        const m = bootstrap.Modal.getOrCreateInstance(
          document.getElementById("bs-example-modal-sm"),
        );
        m.show();
        // Insertar los radio buttons en el contenedor
        var radioButtonsContainer = $("#radioButtonsContainer");
        radioButtonsContainer.empty();

        $.each(data, function (index, item) {
          var radioHtml =
            '<div class="custom-control custom-radio">' +
            '<input type="radio" id="customRadio' +
            (index + 1) +
            '" name="customRadio" class="custom-control-input" value="' +
            item.gid_asana +
            '">' +
            '<label class="custom-control-label" for="customRadio' +
            (index + 1) +
            '">' +
            item.Nombre +
            "</label>" +
            "</div>";
          radioButtonsContainer.append(radioHtml);
          $("#crear_tarea_usuario_badge").html(item.Nombre);
        });
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
  }
});

$("#bs-example-modal-sm-ok").click(function () {
  var selectedGidAsana = $('input[name="customRadio"]:checked').val();
  var selectedGidName = $('input[name="customRadio"]:checked')
    .siblings(".custom-control-label")
    .html();
  if (selectedGidAsana) {
    $("#crear_tarea_usuario_asana").val(selectedGidAsana);
    $("#crear_tarea_usuario_badge").html(selectedGidName);

    bootstrap.Modal.getOrCreateInstance(
      document.getElementById("bs-example-modal-sm"),
    ).hide();
    // Mostrar el valor de gid_asana
    // alert("El valor de gid_asana es: " + selectedGidAsana);
  } else {
    alert("Por favor selecciona un radio button primero.");
  }
});

function eliminarTarea(id) {
  $("#tareas, #asana, #crear_tarea").hide();

  ver_detalle(id)
    .done(function () {
      const modalDetalle = document.getElementById("detalleTareaModal"); // <-- ID del modal
      const modalDetalle_ = bootstrap.Modal.getOrCreateInstance(modalDetalle);
      modalDetalle_.show();
    })
    .fail(function (xhr) {
      console.error("Error cargando detalle:", xhr.responseText);
      Swal.fire("Error", "No se pudo cargar el detalle de la tarea.", "error");
    });
}
