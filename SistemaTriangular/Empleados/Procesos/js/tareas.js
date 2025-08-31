function htmlToPlainText(html) {
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
}
function modificarTarea(id) {
  $.ajax({
    type: "POST",
    url: "../Empleados/Procesos/php/tareas.php",
    data: { ObtenerTarea: 1, id: id },
    success: function (data) {
      var tarea = JSON.parse(data);
      $("#tarea_id").val(tarea.id);
      $("#tarea_nombre").val(tarea.NombreTarea);
      $("#tarea_puntos").val(tarea.Puntos);
      $("#tarea_completada").prop("checked", tarea.Completada == 1);
      $("#modalEditarTarea").modal("show");
    },
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
    url: "../Empleados/Procesos/php/tareas.php",
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
          "error"
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

  var table_contact = $("#table_tareas_lista").DataTable();

  table_contact.destroy();

  // $("#table_tareas_lista").DataTable({
  //   paging: false,
  //   searching: false,
  //   ajax: {
  //     url: "../Empleados/Procesos/php/tareas.php",
  //     data: {
  //       Tareas_lista: 1,
  //       Estado: status,
  //     },
  //     type: "post",
  //   },
  //   columns: [
  //     {
  //       data: "FechaCarga",

  //       render: function (data, type, row) {
  //         var Fecha = row.FechaCarga.split("-").reverse().join(".");
  //         return (
  //           '<td><span style="display: none;">' +
  //           row.FechaCarga +
  //           "</span>" +
  //           Fecha +
  //           "</td>"
  //         );
  //       },
  //     },
  //     { data: "NombreTarea" },
  //     { data: "Puntos" },
  //     {
  //       data: "FechaEntrega",
  //       render: function (data, type, row) {
  //         var Fecha = row.FechaEntrega.split("-").reverse().join(".");
  //         return (
  //           '<td><span style="display: none;">' +
  //           row.FechaEntrega +
  //           "</span>" +
  //           Fecha +
  //           "</td>"
  //         );
  //       },
  //     },
  //     {
  //       data: "Completada",
  //       render: function (data) {
  //         if (data == true) {
  //           return '<span class="badge badge-success text-white">Finalizada</span>';
  //         } else {
  //           return '<span class="badge badge-warning text-white">Pendinete</span>';
  //         }
  //       },
  //     },
  //     {
  //       data: function (row) {
  //         let gid = row.gid_asana || row.gid_hubspot;
  //         let baseUrl = row.gid_asana
  //           ? "https://app.asana.com/app/asana/-/get_asset?asset_id=" +
  //             row.gid_asana
  //           : "https://app.hubspot.com/tasks/23486798/view/all";

  //         return (
  //           '<a target="_blank" href="' +
  //           baseUrl +
  //           gid +
  //           '"><i class="mdi mdi-link-variant"></i></a>'
  //         );
  //       },
  //     },
  //   ],
  // });
  nivel(function (nivelUsuario) {
    // Definimos las columnas base
    var columnasBase = [
      {
        data: "FechaCarga",
        render: function (data, type, row) {
          var Fecha = row.FechaCarga.split("-").reverse().join(".");
          return (
            '<span style="display: none;">' + row.FechaCarga + "</span>" + Fecha
          );
        },
      },
      { data: "NombreTarea" },
      { data: "Puntos" },
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
            return '<span class="badge badge-success text-white">Finalizada</span>';
          } else {
            return '<span class="badge badge-warning text-white">Pendiente</span>';
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
    ];

    // Si es nivel 2, agregamos la columna para editar
    if (nivelUsuario == 1) {
      $(".col-editar").show();
      columnasBase.push({
        data: null,
        render: function (data, type, row) {
          return (
            '<i class="mdi mdi-pencil mdi-18px text-warning ms-2" style="cursor:pointer;" onclick="modificarTarea(' +
            row.id +
            ')"></i>'
          );
        },
        orderable: false,
      });
    } else {
      $(".col-editar").hide();
    }

    // Ahora sí, creamos la tabla
    $("#table_tareas_lista").DataTable({
      paging: false,
      searching: false,
      ajax: {
        url: "../Empleados/Procesos/php/tareas.php",
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
    "../Empleados/Procesos/php/tareas.php",
    {
      DashboardPuntos: 1,
      FechaInicio: FechaInicio,
      FechaFinal: FechaFinal,
    },
    function (resp) {
      renderizarDashboardPuntos(resp.resumen, resp.evolucion);
    },
    "json"
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
      url: "../Empleados/Procesos/php/tareas.php",
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
          var Fecha = row.FechaEntrega.split("-").reverse().join(".");
          return (
            '<td><span style="display: none;">' +
            row.FechaEntrega +
            "</span>" +
            Fecha +
            "</td>"
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
  console.log("gid", gid);
  $.ajax({
    data: { Task: 1, gid: gid },
    url: "Procesos/php/asana_api.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      if (jsonData) {
        console.log("datos", jsonData);
        $("#tareas").css("display", "none");
        $("#details").css("display", "none");
        $("#asana").css("display", "none");

        $("#crear_tarea").css("display", "block");

        $("#crear_tarea_titulo").val(jsonData.data.name).prop("readonly", true);
        $("#crear_tarea_descripcion")
          .val(jsonData.data.notes)
          .prop("readonly", true);
        var FechaCarga = jsonData.data.created_at;
        var fecha = FechaCarga.split("T")[0];
        $("#crear_tarea_fecha_carga").val(fecha).prop("readonly", true);
        $("#crear_tarea_fecha_entrega")
          .val(jsonData.data.due_on)
          .prop("readonly", true);
        $("#crear_tarea_puntos").val("");
        $("#crear_tarea_usuario_asana").show().prop("readonly", true);

        $("#div_id_hubspot").hide();
        $("#div_id_asana").show();
        $("#gid_asana").val(jsonData.data.gid);

        // NombreUsuario(jsonData.data.assignee.gid);
        let nombre_usuario_tarea = NombreUsuario(
          "asana",
          jsonData.data.assignee_id
        );

        $("#crear_tarea_usuario_asana")
          .val(nombre_usuario_tarea ?? "")
          .prop("readonly", true);

        $("#crear_tarea_completada")
          .val(jsonData.data.completed)
          .prop("readonly", true);
        $("#crear_tarea_creador")
          .val(jsonData.data.created_by.gid)
          .prop("readonly", true);
        // console.log('responsable_gid',jsonData.data.assignee.gid);
        $("#crear_tarea_usuario_badge").html(jsonData.data.assignee.gid);
      }
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
    success: function (data) {
      var jsonData = JSON.parse(data);
      if (jsonData && jsonData.data) {
        console.log("HubSpot datos", jsonData);

        $("#tareas").hide();
        $("#details").hide();
        $("#asana").hide();

        $("#crear_tarea").show();

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
          jsonData.data.assignee_id
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
    success: function (data) {
      var jsonData = JSON.parse(data);
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

//ELIMINAR ARCHIVO
// function eliminar_archivo(file){

//     $("#standard-modal-details-ok").hide();
//     $("#standard-modal-ok").show();
//     $('#standard-modal').modal('show');
//     $('#standard-modal-title').html('Estas por eliminar el archivo '+file+' del servidor, esta acción no se puede deshacer, deseas continuar?.');
//     $('#standard-modal-archivo').val(file);

// }

//CONFIRMAR ELIMINAR ARCHIVO
// $("#standard-modal-ok").click(function(){

//     var file=$('#standard-modal-archivo').val();
//     var carpeta=$('#id_tarea').html();
//     var ruta=carpeta+'/'+file;
//     var id = $('#id_tarea').html();

//     $.ajax({
//         data:{'Eliminar_archivo':1,'Ruta':ruta},
//         url: 'Procesos/php/tareas.php',
//         type: 'POST',
//         success: function(data) {
//         var jsonData = JSON.parse(data);

//             if(jsonData.success==1){
//                 $('#standard-modal').modal('hide');
//                 renderizar_archivos(id);
//                 $.NotificationApp.send("Exito !", "Archivo eliminado correctamente.", "bottom-right", "#FFFFFF", "danger");
//             }else{

//             }
//         },
//         error: function() {
//             alert('Error al cargar el contenido.');
//         }
//     });
// });

// document.querySelector('.attach-button').addEventListener('click', function() {
// document.querySelector('.input-file').click();
// });

// var inputFile = document.querySelector('.input-file');

// Agregar un controlador de eventos para el evento change
// inputFile.addEventListener('change', function() {
//     var id = $('#id_tarea').html();
//     var archivoInput = document.querySelector('.input-file');
//     var archivo = archivoInput.files[0]; // Aquí se corrige la referencia al input file
//     var formData = new FormData();
//     formData.append('archivo', archivo);
//     formData.append('nombre_carpeta', id);

//     var xhr = new XMLHttpRequest();
//     xhr.open('POST', 'Procesos/php/upload.php', true);

//     xhr.onload = function () {
//         if (xhr.status === 200) {

// $.NotificationApp.send("Exito !", "Archivo subido correctamente.", "bottom-right", "#FFFFFF", "success");
// renderizar_archivos(id);
// Puedes realizar acciones adicionales aquí, como cerrar la ventana modal
// } else {

// $.NotificationApp.send("Error !", "Ha ocurrido un error al subir el archivo.", "bottom-right", "#FFFFFF", "danger");

// }
// };

// xhr.send(formData);
// });

function fecha_formato(fechaStr) {
  // Convertir la cadena de fecha a un objeto Date
  var fecha = new Date(fechaStr);

  // Opciones de formato para la fecha
  var opcionesFormato = {
    day: "2-digit", // Día con dos dígitos (01, 02, ..., 31)
    month: "long", // Nombre completo del mes (Enero, Febrero, ..., Diciembre)
    year: "numeric", // Año con cuatro dígitos (2024)
  };

  // Formatear la fecha utilizando Intl.DateTimeFormat
  var formatoFecha = new Intl.DateTimeFormat("es-ES", opcionesFormato);
  var fechaFormateada = formatoFecha.format(fecha);

  return fechaFormateada;
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
    },
  });

  $.ajax({
    data: { Tareas_comentarios_total: 1, id: gid_asana },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      $("#comments_title").html("Comentarios (" + jsonData.Total + ")");
    },
    error: function () {
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
  }
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
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
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
  // $('#button_crear_tarea').hide();//POR AHORA NO PERMITO CREAR TAREAS PORQUE ESTO SERA UNICAMENTE DESDE ASANA
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

  $.ajax({
    data: { Tareas_detalle: 1, id: id },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);

      if (new Date(jsonData.data[0].FechaEntrega) < new Date()) {
        $("#details_puntos").prop("disabled", true);
      } else {
        $("#details_puntos").prop("disabled", false);
      }

      // console.log('jsonData',jsonData);
      $("#gid_asana_details").html(jsonData.data[0].gid_asana);
      $("#details_title").html(jsonData.data[0].NombreTarea);
      $("#details_description").html(jsonData.data[0].Descripcion);

      if (jsonData.data[0].FechaCarga) {
        var FechaCarga = fecha_formato(jsonData.data[0].FechaCarga);
        $("#details_fecha_carga").html(FechaCarga);
      }

      if (jsonData.data[0].FechaEntrega) {
        var FechaEntrega = fecha_formato(
          '"' + jsonData.data[0].FechaEntrega + "'"
        );
        console.log(jsonData.data[0].FechaEntrega);
        $("#details_fecha_entrega").html(FechaEntrega);
        // $('#details_fecha_entrega').html(jsonData.data[0].FechaEntrega);
      }

      $("#details_puntos").val(jsonData.data[0].Puntos);

      // Evalúa si el valor es 1 y devuelve true, de lo contrario, devuelve false
      var idchecked = jsonData.data[0].Modificar_Fecha_entrega;

      $("#details_modificar_fecha").prop("checked", idchecked);

      $("#participantes").html(
        "Tarea creada por <b> " +
          jsonData.data[0].UsuarioCarga +
          "</b>" +
          " asignada a <b>" +
          jsonData.data[0].Responsable +
          "</b>"
      );

      renderizar_comentarios(jsonData.data[0].gid_asana);
      console.log("gid", jsonData.data[0].gid_asana);
      renderizar_archivos(jsonData.data[0].gid_asana);
    },
    error: function () {
      $.NotificationApp.send(
        "Error !",
        "Ha ocurrido un error al cargar el contenido.",
        "bottom-right",
        "#FFFFFF",
        "danger"
      );
    },
  });

  // var gid_asana=$('#gid_asana_details').html();
  // // renderizar_comentarios(gid_asana);
  // console.log('gid',gid_asana);
  // renderizar_archivos(id);
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

// $("#api_asana").click(function () {
//   $("#details").css("display", "none");
//   $("#tareas").css("display", "none");
//   $("#tareas_lista").css("display", "none");
//   $("#crear_tarea").css("display", "none");
//   $("#asana").css("display", "block");

//   var datatable = $("#table_asana").DataTable();
//   datatable.destroy();

//   var datatable = $("#table_asana").DataTable({
//     paging: false,
//     searching: true,
//     ajax: {
//       url: "Procesos/php/asana_api.php",
//       data: {
//         Asana: 1,
//       },
//       type: "post",
//     },
//     columns: [
//       { data: "name" },

//       { data: "assignee_name" },
//       {
//         data: "completed",
//         render: function (data) {
//           return data ? "Sí" : "No"; // Convertir booleano a texto
//         },
//       },
//       {
//         data: "created_by_resource_type",
//         render: function (data) {
//           return data === "user" ? "Usuario" : "Otro"; // Convertir a texto legible
//         },
//       },
//       { data: "due_on" },
//       {
//         data: null,
//         render: function (data, type, row) {
//           return (
//             '<a style="cursor:pointer" onclick="cargar_asana(' +
//             data["gid"] +
//             ')" class="action-icon"> <i class="mdi mdi-source-branch-plus text-success"></i></a>'
//           );
//         },
//       },
//     ],
//     debug: true,
//   });
// });

$("#api_asana").click(function () {
  $(
    "#details, #tareas, #tareas_lista, #crear_tarea",
    "#tareas_lista_puntajes"
  ).hide();

  $("#asana").show();

  var datatable = $("#table_asana").DataTable();
  datatable.destroy();

  Promise.all([
    $.post("Procesos/php/asana_api.php", { Asana: 1 }),
    $.post("Procesos/php/hubspot_api.php", { Hubspot: 1 }),
  ]).then(function ([asanaRes, hubspotRes]) {
    const asanaData = asanaRes.data.map((t) => ({
      ...t,
      source: "Asana",
    }));
    const hubspotData = hubspotRes.data.map((t) => ({
      ...t,
      source: "HubSpot",
    }));

    const combined = asanaData.concat(hubspotData);

    $("#table_asana").DataTable({
      paging: false,
      searching: true,
      order: [[4, "asc"]], // orden por fecha ascendente
      data: combined,
      columns: [
        { data: "name", title: "Nombre" },
        { data: "assignee_name", title: "Responsable" },
        {
          data: "completed",
          title: "Completada",
          render: (data) => (data ? "Sí" : "No"),
        },
        { data: "created_by_resource_type", title: "Tipo" },
        {
          data: "due_on",
          title: "Fecha Vencimiento",
          render: function (data) {
            if (!data) return "";
            const fecha = new Date(data);
            const dia = String(fecha.getDate()).padStart(2, "0");
            const mes = String(fecha.getMonth() + 1).padStart(2, "0");
            const año = fecha.getFullYear();
            return `<span style="display:none">${data}</span>${dia}/${mes}/${año}`;
          },
        },
        {
          data: "source",
          title: "Origen",
          render: (data) =>
            data === "Asana"
              ? '<span class="badge badge-info">Asana</span>'
              : '<span class="badge badge-warning">HubSpot</span>',
        },
        {
          data: null,
          title: "Acciones",
          render: (data) => {
            const onclick =
              data.source === "Asana"
                ? `cargar_asana('${data.gid}')`
                : `cargar_hubspot('${data.gid}')`;
            return (
              `<a style="cursor:pointer" onclick="${onclick}" class="action-icon">` +
              `<i class="mdi mdi-source-branch-plus text-success"></i></a>`
            );
          },
        },
      ],
    });
  });
});

$("#crear_tarea_cnl").click(function () {
  $("#tareas").css("display", "block");
  $("#crear_tarea").css("display", "none");
  $("#asana").css("display", "none");
});

//CREAR TAREA
$("#crear_tarea_ok").click(function () {
  var titulo = $("#crear_tarea_titulo").val();
  var descripcion = $("#crear_tarea_descripcion").val();
  var fecha_carga = $("#crear_tarea_fecha_carga").val();
  var fecha_entrega = $("#crear_tarea_fecha_entrega").val();
  var puntos = $("#crear_tarea_puntos").val();
  var usuario = $("#crear_tarea_usuario_badge").html();
  var gid_asana = $("#gid_asana").val(); // PARA HUBSPOT?
  var completado = $("#crear_tarea_completada").val();
  var creador = $("#crear_tarea_creador").val();
  var responsable = $("#crear_tarea_usuario_asana").val(); // PARA HUBSPOT?
  var responsable_gid = $("#crear_tarea_usuario_badge").html();
  var gid_hubspot = $("#gid_hubspot").val();

  $.ajax({
    data: {
      Crear_Tareas: 1,
      Titulo: titulo,
      Descripcion: descripcion,
      Fecha_carga: fecha_carga,
      Fecha_entrega: fecha_entrega,
      Puntos: puntos,
      Usuario: usuario,
      gid_asana: gid_asana,
      completado: completado,
      creador: creador,
      responsable_gid: responsable_gid,
      responsable: responsable,
      gid_hubspot: gid_hubspot,
    },
    url: "Procesos/php/tareas.php",
    type: "POST",
    success: function (data) {
      var jsonData = JSON.parse(data);
      if (jsonData.success == 1) {
        $("#tareas").css("display", "block");
        $("#crear_tarea").css("display", "none");
        renderizar_tareas_lista();

        $.NotificationApp.send(
          "Exito !",
          "Tarea creada correctamente.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error !",
          "No se cargó la Tarea.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
    error: function () {
      alert("Error al cargar el contenido.557");
    },
  });
});

$("#details_delete").click(function () {
  var id = $("#id_tarea").html();
  var id_tarea = id.replace(/^#0*/, "");

  $("#standard-modal").modal("show");
  $("#standard-modal-title").html(
    "Estas por eliminar la tarea" +
      id_tarea +
      " del sistema, esto no la eliminara de asana pero si le sacará la etiqueta de Cargado en el Sistema de Caddy, deseas continuar?."
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
        $("#standard-modal").modal("hide");

        $("#details").css("display", "none");

        renderizar_tareas();

        renderizar_totales();

        $.NotificationApp.send(
          "Exito !",
          "Tarea eliminada correctamente.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
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
        $("#bs-example-modal-sm").modal("show");
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

    $("#bs-example-modal-sm").modal("hide");

    // Mostrar el valor de gid_asana
    // alert("El valor de gid_asana es: " + selectedGidAsana);
  } else {
    alert("Por favor selecciona un radio button primero.");
  }
});
