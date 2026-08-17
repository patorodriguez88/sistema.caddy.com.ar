var filtro = "";

// Muestra el aviso de "mail obligatorio" cuando se elige un nivel de sistema (1, 2 o 7),
// y oculta los campos que solo aplican a Chofer/Reparto (nivel 3).
$(document).on("change", "#ext_nivel", function () {
  var nivel = $(this).val();
  var esUsuarioSistema = nivel === "1" || nivel === "2" || nivel === "7";
  $("#ext_mail_hint").toggleClass("d-none", !esUsuarioSistema);

  var esChofer = nivel === "3";
  $(".campo-chofer").toggleClass("d-none", !esChofer);
  $("#ext_gruposanguineo, #ext_licencia").prop("required", esChofer);
});
// function cargarUsuariosAsana() {
//   $.ajax({
//     url: "../Asana/usuarios.php",
//     method: "GET",
//     dataType: "json",
//     success: function (response) {
//       if (!response.data) {
//         console.error("Respuesta inválida de Asana");
//         return;
//       }

//       const $select = $("#empleado_id_asana");
//       $select.empty();
//       $select.append('<option value="">Seleccionar empleado</option>');

//       response.data.forEach(function (usuario) {
//         $select.append(`
//                     <option value="${usuario.gid}">
//                         ${usuario.name}
//                     </option>
//                 `);
//       });
//     },
//     error: function (xhr, status, error) {
//       console.error("Error al cargar usuarios Asana:", error);
//     },
//   });
// }
function cargarUsuariosAsana(selectedGid) {
  return $.ajax({
    url: "../Asana/usuarios.php",
    method: "GET",
    dataType: "json",
  }).then(function (response) {
    const $select = $("#empleado_id_asana");
    $select.empty().append('<option value="">Seleccionar empleado</option>');

    (response.data || []).forEach(function (u) {
      $select.append(`<option value="${u.gid}">${u.name}</option>`);
    });

    // ✅ Seleccionar si viene gid
    if (
      selectedGid != null &&
      String(selectedGid).trim() !== "" &&
      String(selectedGid) !== "0"
    ) {
      $select.val(String(selectedGid));
    }
  });
}
// function cargarUsuariosHubspot() {
//   $.ajax({
//     url: "Procesos/php/hubspot_api.php", // <-- poné tu ruta real
//     method: "POST",
//     dataType: "json",
//     data: { Users: 1 },
//     success: function (resp) {
//       if (!resp || !resp.data) {
//         console.error("Respuesta inválida HubSpot:", resp);
//         return;
//       }

//       const $select = $("#empleado_id_hubspot"); // si es <select>
//       $select.empty();
//       $select.append('<option value="">Seleccionar usuario</option>');

//       resp.data.forEach(function (u) {
//         $select.append(`<option value="${u.id}">${u.name}</option>`);
//       });
//     },
//     error: function (xhr) {
//       console.error(
//         "Error al cargar usuarios HubSpot:",
//         xhr.status,
//         xhr.responseText,
//       );
//       Swal.fire("Error", "No se pudieron cargar usuarios de HubSpot.", "error");
//     },
//   });
// }
function cargarUsuariosHubspot(selectedId) {
  return $.ajax({
    url: "Procesos/php/hubspot_api.php",
    method: "POST",
    dataType: "json",
    data: { Users: 1 },
  }).then(function (resp) {
    const $select = $("#empleado_id_hubspot"); // debe ser <select>
    $select.empty().append('<option value="">Seleccionar usuario</option>');

    (resp.data || []).forEach(function (u) {
      $select.append(`<option value="${u.id}">${u.name}</option>`);
    });

    if (
      selectedId != null &&
      String(selectedId).trim() !== "" &&
      String(selectedId) !== "0"
    ) {
      $select.val(String(selectedId));
    }
  });
}
// Función para actualizar la tabla con el filtro actualizado
function actualizarTabla() {
  var datatable_empleados = $("#empleados").DataTable();
  datatable_empleados.ajax.reload(null, false);
}

$("#add-new-modal_cancel").click(function () {
  $("#new_externo")[0].reset();
  $("#ext_nivel").trigger("change");
});

$(document).on("click", "#imprimir", function () {
  var contenido = document.getElementById("full-width-modal").innerHTML;
  var contenidoOriginal = document.body.innerHTML;
  document.body.innerHTML = contenido;
  window.print();
  document.body.innerHTML = contenidoOriginal;
});

//MUESTRO LA TABLA
var datatable = $("#empleados").DataTable({
  dom: "Bfrtip",
  buttons: ["pageLength", "copy", "excel", "pdf"],
  paging: true,
  searching: true,
  lengthMenu: [
    [10, 25, 50, -1],
    [10, 25, 50, "All"],
  ],
  ajax: {
    url: "Procesos/php/empleados.php",
    data: function (d) {
      d.Empleados = 1;
    },
    processing: true,
    type: "post",
  },
  columns: [
    { data: "id" },
    {
      data: "NombreCompleto",
      render: function (data, type, row) {
        let vehiculo = "";

        if (row.Marca || row.Modelo || row.Dominio) {
          vehiculo = `${row.Marca || ""} ${row.Modelo || ""} ${row.Dominio || ""}`;
        }
        return `<b>${row.NombreCompleto}</b><br>${vehiculo}`;
      },
      // render: function (data, type, row) {
      //   return (
      //     `<td><b> ${row.NombreCompleto}</b></br></td>` +
      //     `<td> ${row.Marca} ${row.Modelo} ${row.Dominio}</td>`
      //   );
      // },
    },
    {
      data: "Dni",
      render: function (data, type, row) {
        return `<td>${row.Dni}</td>`;
      },
    },
    {
      data: "Telefono",
      render: function (data, type, row) {
        return `<td><i class="mdi mdi-18px mdi-phone text-success"></i> <b>${row.Telefono}</b></td>`;
      },
    },
    //     {data:"Puesto",
    //     render: function (data, type, row) {
    //     return `<span class="badge bg-dark text-white">${row.Puesto}</span></br>`;

    //     }

    //   },
    {
      data: "FechaIngreso",

      render: function (data, type, row) {
        var Fecha = row.FechaIngreso
          ? row.FechaIngreso.split("-").reverse().join(".")
          : "";
        return `<td><b> ${Fecha}</b></br></td>`;
        // `<td><b> ${row.order_id}</b></br></td>`;
      },
    },
    {
      data: "VencimientoLicencia",
      render: function (data, type, row) {
        var FechaVencimientoLicencia = row.VencimientoLicencia
          ? row.VencimientoLicencia.split("-").reverse().join(".")
          : "-";
        return `<td class="table-action col-xs-3"><b> ${FechaVencimientoLicencia}</b></td>`;
      },
    },
    {
      data: "Inactivo",
      render: function (data, type, row) {
        switch (row.Inactivo) {
          case "0":
            var color = "success";
            var text = "Activo";
            break;

          case "1":
            var color = "danger";
            var text = "Inactivo";
            break;

          default:
            var color = "primary";
            break;
        }

        return `<span class="badge bg-${color} text-white">${text}</span>`;
      },
    },
    {
      data: "id",
      render: function (data, type, row) {
        return `<td class="table-action"><a id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-warning"></i></a>`;
      },
    },
  ],
});

//DESEMPEÑO

//BOTON PARA ABRIR EL MODAL DE AGREGAR EMPLEADOS
$("#button_agregar_empleado").on("click", function () {
  $("#NewTaskModalLabel").html("Agregar Nuevo Empleado");
  $("#button_guardar").show();
  $("#alerta").hide();
  $("#button_guardar").hide();
  $("#crear_empleado").show();
  $("#ext_nivel").prop("disabled", false);
  const modalEl = document.getElementById("add-new-modal");
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
  cargarUsuariosAsana();
  cargarUsuariosHubspot(); // <-- acá
});

function modificar(a) {
  $.ajax({
    data: {
      VerEmpleado: 1,
      id: a,
    },
    url: "Procesos/php/empleados.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#NewTaskModalLabel").html("Modificar Datos Empleado");
      $("#add-new-modal").modal("show");
      if (jsonData.data[0].Inactivo == 1) {
        $("#alerta").css("display", "block");
      } else {
        $("#alerta").css("display", "none");
      }
      $("#button_continuar").css("display", "none");
      $("#crear_empleado").hide();

      $("#button_guardar").css("display", "inline");
      $("#button_volver").css("display", "none");
      $("#ext_usuario_app").val(jsonData.data[0].Usuario);
      $("#ext_pass_app").val(jsonData.data[0].PASSWORD);

      $("#ext_id").val(a);
      $("#ext_name").val(jsonData.data[0].NombreCompleto);
      $("#ext_dni").val(jsonData.data[0].Dni);
      $("#ext_domicilio").val(jsonData.data[0].Domicilio);
      $("#ext_city").val(jsonData.data[0].Localidad);
      $("#ext_state").val(jsonData.data[0].Provincia);

      // var FechaNa = jsonData.data[0].FechaNacimiento.split("-");
      // var FechaNac = FechaNa[1] + "/" + FechaNa[2] + "/" + FechaNa[0];

      // var FechaIng = jsonData.data[0].FechaIngreso.split("-");
      // var FechaIngreso = FechaIng[1] + "/" + FechaIng[2] + "/" + FechaIng[0];

      // var FechaLic = jsonData.data[0].VencimientoLicencia.split("-");
      // var FechaLicencia = FechaLic[1] + "/" + FechaLic[2] + "/" + FechaLic[0];

      // $("#ext_nac").val(FechaNac);
      $("#ext_nac").val(jsonData.data[0].FechaNacimiento);
      $("#ext_ing").val(jsonData.data[0].FechaIngreso);
      $("#ext_licencia").val(jsonData.data[0].VencimientoLicencia);
      $("#ext_gruposanguineo").val(jsonData.data[0].GrupoSanguineo);
      $("#ext_phone_emergency").val(jsonData.data[0].TelefonoEmergencia);
      $("#ext_obs").val(jsonData.data[0].Observaciones);
      $("#ext_cp").val(jsonData.data[0].CodigoPostal);
      $("#ext_telefono").val(jsonData.data[0].Telefono);

      // El Nivel no se puede cambiar desde acá (es otra decisión aparte), pero lo
      // dejamos seleccionado para que se vean/oculten bien los campos correctos.
      $("#ext_nivel").val(jsonData.data[0].NIVEL).prop("disabled", true).trigger("change");
      $("#ext_mail").val(jsonData.data[0].Mail);

      const gidAsana = jsonData.data?.[0]?.gid_asana ?? 0;
      const idHub = jsonData.data?.[0]?.gid_hubspot ?? 0;

      // Cargan el combo y dejan seleccionado lo que ya tiene
      cargarUsuariosAsana(gidAsana);
      cargarUsuariosHubspot(idHub);
    },
  });
}
//BUTTON GUARDAR
$("#button_guardar").click(function () {
  var id = $("#ext_id").val();
  var nombre = $("#ext_name").val();
  var dni = $("#ext_dni").val();
  var domicilio = $("#ext_domicilio").val();
  var city = $("#ext_city").val();
  var state = $("#ext_state").val();
  var nac = $("#ext_nac").val();
  var ing = $("#ext_ing").val();
  var licencia = $("#ext_licencia").val();
  var gruposanguineo = $("#ext_gruposanguineo").val();
  var phone_emergency = $("#ext_phone_emergency").val();
  var obs = $("#ext_obs").val();
  var codigopostal = $("#ext_cp").val();
  var telefono = $("#ext_telefono").val();
  var mail = $("#ext_mail").val();
  var asana_gid = $("#empleado_id_asana").val();
  var hubspot_gid = $("#empleado_id_hubspot").val();

  $.ajax({
    url: "Procesos/php/empleados.php",
    type: "post",
    dataType: "json",
    data: {
      ModificarEmpleado: 1,
      id_externo: id,
      nombre,
      dni,
      domicilio,
      city,
      state,
      nac,
      ing,
      licencia,
      gruposanguineo,
      phone_emergency,
      codigopostal,
      obs,
      telefono,
      mail,
      asana_gid,
      hubspot_gid,
    },
    success: function (jsonData) {
      if (jsonData && jsonData.success == 1) {
        const modalEl = document.getElementById("add-new-modal");
        const modal =
          bootstrap.Modal.getInstance(modalEl) ||
          bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
        Swal.fire({
          icon: "success",
          title: "¡Éxito!",
          text: xhr.responseText
            ? xhr.responseText.substring(0, 300)
            : "Registro actualizado correctamente.",
        });

        $("#empleados").DataTable().ajax.reload(null, false);
        return;
      }

      // ✅ Si el backend devuelve {success:0, field, message}
      Swal.fire({
        icon: "warning",
        title: "Faltan datos",
        text: jsonData?.message || jsonData?.error || "No se pudo actualizar.",
      });
    },
    error: function (xhr) {
      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: xhr.responseText
          ? xhr.responseText.substring(0, 300)
          : "Error inesperado.",
      });
    },
  });
});

$("#crear_empleado").on("click", function (e) {
  e.preventDefault();

  // Tomo el form para validar (ajustá el selector si tu form tiene otro id)
  var form =
    document.querySelector("#new_externo") ||
    document.querySelector(".needs-validation");

  if (!form) {
    Swal.fire({
      icon: "error",
      title: "Error de configuración",
      text: "No encuentro el formulario (#new_externo o .needs-validation).",
    });
    return;
  }

  // Activo estilos de Bootstrap
  form.classList.add("was-validated");

  // Si hay campos inválidos -> Swal con el primero que falle
  if (!form.checkValidity()) {
    var firstInvalid = form.querySelector(":invalid");
    var label = "";

    if (firstInvalid) {
      // intenta encontrar label asociado
      if (firstInvalid.id) {
        var lbl = document.querySelector(
          'label[for="' + firstInvalid.id + '"]',
        );
        if (lbl) label = lbl.innerText.replace(":", "").trim();
      }
      if (!label)
        label =
          firstInvalid.getAttribute("name") ||
          firstInvalid.id ||
          "un campo obligatorio";
    }

    Swal.fire({
      icon: "warning",
      title: "Faltan datos",
      text: "Revisá: " + label,
      confirmButtonText: "Ok",
    });

    firstInvalid && firstInvalid.focus();
    return;
  }

  // Datos
  var payload = {
    Agregar_empleado: 1,
    nombre: $("#ext_name").val(),
    dni: $("#ext_dni").val(),
    domicilio: $("#ext_domicilio").val(),
    city: $("#ext_city").val(),
    state: $("#ext_state").val(),
    nac: $("#ext_nac").val(),
    ing: $("#ext_ing").val(),
    lic: $("#ext_licencia").val(),
    gruposanguineo: $("#ext_gruposanguineo").val(),
    phone_emergency: $("#ext_phone_emergency").val(),
    obs: $("#ext_obs").val(),
    codigopostal: $("#ext_cp").val(),
    telefono: $("#ext_telefono").val(),
    asana_gid: $("#empleado_id_asana").val(),
    hubspot_gid: $("#empleado_id_hubspot").val(),
    nivel: $("#ext_nivel").val(),
    mail: $("#ext_mail").val(),
  };

  var esUsuarioSistema =
    payload.nivel === "1" || payload.nivel === "2" || payload.nivel === "7";
  if (esUsuarioSistema && !payload.mail) {
    Swal.fire({
      icon: "warning",
      title: "Falta el mail",
      text: "Para crear un usuario de SuperAdministrador/Administracion/Operaciones necesitás cargar un mail (ese mail va a ser su usuario de acceso, y ahí se manda la contraseña temporal).",
    });
    $("#ext_mail").focus();
    return;
  }

  // Loading Swal
  Swal.fire({
    title: "Guardando empleado...",
    text: "Un segundo",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: "Procesos/php/empleados.php",
    type: "post",
    dataType: "json", // <-- clave: evita JSON.parse manual
    data: payload,
    success: function (jsonData) {
      if (jsonData && jsonData.success == 1) {
        var texto = "Empleado cargado al sistema";
        var icono = "success";

        if (jsonData.es_usuario_sistema) {
          if (jsonData.mail_enviado) {
            texto =
              "Empleado cargado. Le mandamos la contraseña temporal a " +
              jsonData.mail_destino +
              " — el sistema le va a pedir cambiarla apenas inicie sesión.";
          } else {
            texto =
              "Empleado cargado, pero no pudimos mandarle el mail con la contraseña temporal a " +
              jsonData.mail_destino +
              ". Quedó pendiente de notificación — podés reenviarlo desde Usuarios.";
            icono = "warning";
          }
        }
        Swal.fire({
          icon: icono,
          title: "¡Éxito!",
          text: texto,
          confirmButtonText: "Ok",
        }).then(() => {
          // Cerrar modal (Bootstrap 5)
          var modalEl = document.getElementById("add-new-modal");
          if (modalEl) {
            var modal =
              bootstrap.Modal.getInstance(modalEl) ||
              new bootstrap.Modal(modalEl);
            modal.hide();
          }

          // El backdrop de Bootstrap a veces queda pegado (pantalla oscurecida/borrosa
          // que no responde) cuando el modal se cierra justo después de un SweetAlert
          // superpuesto — se fuerza la limpieza para no depender de esa carrera.
          $(".modal-backdrop").remove();
          $("body").removeClass("modal-open").css("padding-right", "");

          // Reset form
          $("#new_externo")[0].reset();
          $("#ext_nivel").trigger("change");
          form.classList.remove("was-validated");

          // Recargar la tabla para que aparezca el empleado recién creado
          if ($.fn.DataTable.isDataTable("#empleados")) {
            $("#empleados").DataTable().ajax.reload(null, false);
          }
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text:
            jsonData && jsonData.error
              ? jsonData.error
              : "Externo no cargado al sistema",
        });
      }
    },
    error: function (xhr) {
      // Si el PHP devuelve HTML/Warnings, lo mostramos acotado
      var msg = "No se pudo guardar. Revisá empleados.php.";
      if (xhr && xhr.responseText) {
        msg = xhr.responseText.substring(0, 300); // recorte
      }

      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: msg,
      });
    },
  });
});
