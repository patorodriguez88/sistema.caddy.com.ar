$(document).ready(function () {
  var today = new Date();
  var fechas =
    today.getMonth() +
    1 +
    "/" +
    today.getDate() +
    "/" +
    today.getFullYear() +
    " - " +
    (today.getMonth() + 1) +
    "/" +
    (today.getDate() + 11) +
    "/" +
    today.getFullYear();
  init_datatable(fechas);
});

$("#buscar_orden").click(function () {
  $("#fechas_ordenes").css("display", "block");
});

function capitalizar(texto) {
  return texto.toLowerCase().replace(/(^|\s)\S/g, function (letra) {
    return letra.toUpperCase();
  });
}

$("#orden_ok").on("click", function () {
  const chofer_aliado = $("#select_empleados option:selected").data("aliado"); // 0 o 1
  const vehiculo_aliado = $("#select_vehiculo option:selected").data("aliado"); // 0 o 1

  // Validación cruzada
  if (chofer_aliado !== vehiculo_aliado) {
    Swal.fire({
      icon: "warning",
      title: "Verificación de datos",
      text: "Estás asignando un vehículo que no corresponde al tipo de chofer (Empleado / Externo). ¿Querés continuar de todos modos?",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar",
      cancelButtonText: "Revisar",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        guardarOrden(); // Continuar con la carga
      }
      // Si elige revisar, no hace nada
    });
  } else {
    guardarOrden(); // Tipos coinciden, continuamos directo
  }
});

function guardarOrden() {
  let recorrido_nombre = $("#orden_recorrido_name").val();
  if (!recorrido_nombre) {
    recorrido_nombre =
      $("#orden_date").val() +
      " - " +
      $("#select_empleados option:selected").text() +
      " - " +
      $("#select_recorridos option:selected").text();
  }

  let datos = {
    alta_orden: 1,
    numero: $("#orden_number").val(),
    fecha: $("#orden_date").val(),
    hora: $("#orden_time").val(),
    controla: $("#orden_controla").val(),
    vehiculo: $("#select_vehiculo").val(),
    chofer: $("#select_empleados").val(),
    acomp: $("#orden_acomp").val(),
    recorrido: $("#select_recorridos").val(),
  };
  // Validar campos obligatorios
  if (
    !datos.numero ||
    !datos.fecha ||
    !datos.hora ||
    !datos.controla ||
    !datos.vehiculo ||
    !datos.chofer ||
    !datos.recorrido
  ) {
    Swal.fire({
      icon: "warning",
      title: "Faltan datos obligatorios",
      text: "Completá todos los campos requeridos antes de continuar.",
    });
    return; // Evita continuar si falta algo
  }
  $.ajax({
    url: "Proceso/php/ordenes.php",
    type: "POST",
    data: datos,
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        Swal.fire("OK", "Orden guardada correctamente", "success");
        $("#orden_alta").modal("hide");
        $("#modal-rec-form")[0].reset();
        $("#ordenes").DataTable().ajax.reload();
      } else {
        Swal.fire(
          "Error",
          respuesta.mensaje || "No se pudo guardar la orden",
          "error"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error AJAX:", error);
      Swal.fire("Error", "Error en la comunicación con el servidor", "error");
    },
  });
}

$("#select_recorridos").on("change", function () {
  const selectedOption = $(this).find("option:selected");
  nombre_recorrido_new();
  // const numero = selectedOption.val();
  // const nombre = selectedOption.text().split("-").slice(1).join("-").trim();
  // const chofer = $("#select_empleados option:selected").text();
  // if (numero) {
  //   const hoy = new Date();
  //   const fecha = hoy
  //     .toLocaleDateString("es-AR")
  //     .split("/")
  //     .map((n) => n.padStart(2, "0"))
  //     .join("-");

  //   const texto = `${fecha} ${chofer} - ${nombre} [${numero}]`;

  //   $("#orden_recorrido_name").attr("placeholder", texto);
  // } else {
  //   $("#orden_recorrido_name").attr(
  //     "placeholder",
  //     "Escriba el nombre del recorrido"
  //   );
  // }
});

function nombre_recorrido_new(recorrido) {
  let recorrido_nombre = recorrido;

  if (!recorrido_nombre) {
    recorrido_nombre =
      $("#orden_date").val() +
      " - " +
      $("#select_empleados option:selected").text() +
      " - " +
      recorrido;

    $("#orden_recorrido_name").val(recorrido_nombre);
  } else {
    $("#orden_recorrido_name").val("");
  }
}

$("#agregar_orden").click(function () {
  $("#orden_alta").modal("show");
  // Cargar recorridos solo cuando se abre el modal
  $.ajax({
    url: "Proceso/php/ordenes.php",
    method: "POST",
    data: { BuscoRecorridos: 1 },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        // Fecha en formato dd/mm/yyyy
        const hoy = new Date();
        const fechaFormateada = hoy.toISOString().split("T")[0]; // formato: "2025-06-17"
        $("#orden_date").val(fechaFormateada);

        // Hora en formato HH:MM
        $("#orden_time").val(
          hoy.toLocaleTimeString("es-AR", {
            hour: "2-digit",
            minute: "2-digit",
          })
        );

        //usuario
        $("#orden_controla").val(respuesta.usuario);

        //numero de orden
        $("#orden_number").val(respuesta.proximo_id_logistica);
        let $select = $("#select_recorridos");
        $select
          .empty()
          .append('<option value="">Seleccione un recorrido</option>');

        respuesta.recorridos.forEach(function (recorrido) {
          $select.append(
            `<option value="${recorrido.Numero}">Recorrido ${recorrido.Numero} - ${recorrido.Nombre}</option>`
          );
        });
        nombre_recorrido_new();
      }
    },
  });

  $.ajax({
    url: "Proceso/php/ordenes.php",
    type: "POST",
    data: { BuscoEmpleados: 1 },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        let empleados = respuesta.empleados;
        let empleadosHTML = '<optgroup label="Empleados">';
        let aliadosHTML = '<optgroup label="Externos">';

        empleados.forEach(function (e) {
          let option = `<option value="${e.id}" data-aliado="${
            e.Aliados
          }">${capitalizar(e.NombreCompleto)}</option>`;
          if (e.Aliados == 1) {
            aliadosHTML += option;
          } else {
            empleadosHTML += option;
          }
        });

        empleadosHTML += "</optgroup>";
        aliadosHTML += "</optgroup>";

        const primerOpcion = '<option value="">Seleccione un empleado</option>';
        $("#select_empleados")
          .html(primerOpcion + empleadosHTML + aliadosHTML)
          .trigger("change");
      }
    },
  });
  // BUSCO VEHICULOS
  $.ajax({
    url: "Proceso/php/ordenes.php",
    method: "POST",
    data: { BuscoVehiculos: 1 },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        let $select = $("#select_vehiculo");
        $select
          .empty()
          .append('<option value="">Seleccione un Vehiculo</option>');

        let caddyGroup = $('<optgroup label="Vehiculo de Caddy"></optgroup>');
        let externosGroup = $(
          '<optgroup label="Vehiculo de Externos"></optgroup>'
        );

        respuesta.vehiculos.forEach(function (vehiculo) {
          let marca = capitalizar(vehiculo.Marca);
          let modelo = capitalizar(vehiculo.Modelo);
          let nombre = `${marca} ${modelo} ${vehiculo.Dominio}`;
          let option = `<option value="${vehiculo.id}" data-aliado="${vehiculo.Aliados}">${nombre}</option>`;

          if (vehiculo.Aliados == 1) {
            externosGroup.append(option);
          } else {
            caddyGroup.append(option);
          }
        });

        $select.append(caddyGroup).append(externosGroup).trigger("change");
      }
    },
  });
});

$("#singledaterange").change(function () {
  fechas = $("#singledaterange").val();
  // console.log(fechas);

  if (fechas == null) {
    var fechas1 =
      new Date().getUTCMonth() +
      1 +
      "/" +
      new Date().getUTCDate() +
      "/" +
      new Date().getUTCFullYear();
    var fechas = fechas1 + " - " + fechas1;
  }
  var datatable = $("#ordenes").DataTable();
  datatable.destroy();
  init_datatable(fechas);
});

function init_datatable(fechas) {
  $("#div_ordenes").css("display", "block");
  var new_date = fechas.split("-");
  var new_date1 = new_date[0].split("/");
  var new_date2 = new_date1[1] + "/" + new_date1[0] + "/" + new_date1[2];

  var new_date_1 = new_date[1].split("/");
  var new_date_2 = new_date_1[1] + "/" + new_date_1[0] + "/" + new_date_1[2];

  $("#header_ordenes").html(
    "Ordenes de Salida Fechas Desde " + new_date2 + " Hasta " + new_date_2
  );

  var datatable = $("#ordenes").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength", "copy", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],

    ajax: {
      url: "Proceso/php/ordenes.php",
      data: { Logistica: 1, Fechas: fechas },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          return `<h6 class="muted">${row.NumerodeOrden}</h6>`;
        },
      },
      {
        data: "Fecha",
        render: function (data, type, row) {
          let fecha = row.Fecha.split("-").reverse().join("/");
          return (
            `<td>${fecha}</td>` +
            `<h6 class="muted">S:${row.Hora}</br>R:${row.HoraRetorno}</h6>`
          );
        },
      },
      {
        data: "NombreChofer",
        render: function (data, type, row) {
          return `<td>${row.NombreChofer}</td><h6 class="muted">${row.NombreChofer2}</h6>`;
        },
      },
      {
        data: "Patente",
        render: function (data, type, row) {
          return `<td>${row.Marca} ${row.Modelo} ${row.Dominio}</td>`;
        },
      },
      {
        data: "Recorrido",

        render: function (type, data, row) {
          return `<td><small>${row.Recorrido}</small></br><h6>${row.Nombre}</h6></td>`;
        },
      },
      {
        data: "Kilometros",
        render: function (data, type, row) {
          return (
            `<td>${row.KilometrosRecorridos} Km.</td>` +
            `<h6 class="muted">${row.Kilometros} - ${row.KilometrosRegreso}</h6>`
          );
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var formatoMoneda = row.TotalRecorrido.toLocaleString("es-AR", {
            style: "currency",
            currency: "US",
          });

          if (row.Facturado == 1) {
            var facturado = `Facturado`;
            var color = "success";
          } else {
            facturado = "No Facturado";
            color = "danger";
          }

          return (
            '<td><h5><span class="badge badge-' +
            color +
            '"> <b>' +
            facturado +
            "</b></span></h5></tr>$ " +
            formatoMoneda +
            "</td>"
          );
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          if (row.Estado == "Cerrada") {
            var estado = "danger";
          } else if (row.Estado == "Alta" || row.Estado == "Pendiente") {
            var estado = "warning";
          } else if (row.Estado == "Cargada") {
            var estado = "success";
          }
          return `<td><h5><span class="badge badge-${estado}"> <b>${row.Estado}</b></span></h5></td>`;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          if (row.Estado == "Cerrada") {
            return `<td><a target="_blank" href="https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ResumenVehiculospdf.php?NO=${row.NumerodeOrden}"><i class='mdi mdi-18px mdi-file-chart-outline'></i></a></td>`;
          } else {
            return `
            <td>
              <a target="_blank" href="http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=${row.NumerodeOrden}" title="Ver Orden">
                <i class='mdi mdi-18px mdi-file-chart-outline ms-2'></i>
              </a>
              <a href="#" onclick="cargarOrden('${row.NumerodeOrden}');" title="Cargar Orden">
                <i class='mdi mdi-18px mdi-upload ms-2 text-success'></i>
              </a>
              <a href="#" onclick="cerrarOrden('${row.NumerodeOrden}');" title="Cerrar Orden">
                <i class='mdi mdi-18px mdi-lock-check ms-2 text-warning text-white'></i>
              </a>
            </td>`;
            // return `<td><a target="_blank" href="http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=${row.NumerodeOrden}"><i class='mdi mdi-18px mdi-file-chart-outline'></i></a></td>`;
          }
        },
      },
    ],
  });
}

function cargarOrden(orden) {
  window.location.href = "Logistica.php?id=Cargar&orden_t=" + orden;
}
function cerrarOrden(orden) {
  $("#form_cerrar_orden_div").css("display", "block");
  $("#fechas_ordenes").css("display", "none");
  $("#div_ordenes").css("display", "none");
  $("#numero_orden").val(orden);
  $.ajax({
    type: "POST",
    url: "Proceso/php/ordenes.php",
    data: { CerrarOrden: 1, numero_orden: orden },
    dataType: "json",
    success: function (response) {
      if (response.success == 1) {
        $("#controla").val(response.data.Controla);
        $("#vehiculo").val(response.data.Vehiculo);
        $("#chofer").val(response.data.NombreChofer);
        $("#recorrido").val(response.data.Recorrido);
        $("#km_recorrido").val(response.data.KilometrosRecorridos);
        $("#km_salida").val(response.data.KilometrosSalida);
        $("#combustible_salida").val(response.data.CombustibleSalida);
        // ... continuar con los demás campos
      } else {
        $.NotificationApp.send(
          "Atención",
          response.msg,
          "bottom-right",
          "#fff",
          "warning"
        );
      }
    },
    error: function () {
      $.NotificationApp.send(
        "Error",
        "No se pudo consultar la orden",
        "bottom-right",
        "#fff",
        "danger"
      );
    },
  });
}
