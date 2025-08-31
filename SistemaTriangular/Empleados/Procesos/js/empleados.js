var filtro = "";

// Función para actualizar la tabla con el filtro actualizado
function actualizarTabla() {
  var datatable_empleados = $("#empleados").DataTable();
  datatable_empleados.ajax.reload(null, false);
}

$("#add-new-modal_cancel").click(function () {
  $("#new_externo")[0].reset();
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
        return (
          `<td><b> ${row.NombreCompleto}</b></br></td>` +
          `<td> ${row.Marca} ${row.Modelo} ${row.Dominio}</td>`
        );
      },
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
        var Fecha = row.FechaIngreso.split("-").reverse().join(".");
        return `<td><b> ${Fecha}</b></br></td>`;
        // `<td><b> ${row.order_id}</b></br></td>`;
      },
    },
    {
      data: "VencimientoLicencia",
      render: function (data, type, row) {
        var FechaVencimientoLicencia = row.VencimientoLicencia.split("-")
          .reverse()
          .join(".");
        return `<td class="table-action col-xs-3"><b> ${FechaVencimientoLicencia}</b></td>`;
      },
    },
    {
      data: "Observaciones",
      render: function (data, type, row) {
        return `<td><a style="font-size:8px">${row.Observaciones} </a></td>`;
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
        return `<td class="table-action"><a id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-account-edit text-success"></i></a>`;
      },
    },
  ],
});

//DESEMPEÑO

//BOTON PARA ABRIR EL MODAL DE AGREGAR EMPLEADOS
$("#button_agregar_externo").click(function () {
  $("#NewTaskModalLabel").html("Agregar Nuevo Empleado");
  //   $("#button_continuar").css("display", "inline");
  $("#button_guardar").css("display", "inline");
  $("#alerta").css("display", "none");
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

      var FechaNa = jsonData.data[0].FechaNacimiento.split("-");
      var FechaNac = FechaNa[1] + "/" + FechaNa[2] + "/" + FechaNa[0];

      var FechaIng = jsonData.data[0].FechaIngreso.split("-");
      var FechaIngreso = FechaIng[1] + "/" + FechaIng[2] + "/" + FechaIng[0];

      var FechaLic = jsonData.data[0].VencimientoLicencia.split("-");
      var FechaLicencia = FechaLic[1] + "/" + FechaLic[2] + "/" + FechaLic[0];

      $("#ext_nac").val(FechaNac);
      $("#ext_ing").val(FechaIngreso);
      $("#ext_licencia").val(FechaLicencia);
      $("#ext_gruposanguineo").val(jsonData.data[0].GrupoSanguineo);
      $("#ext_phone_emergency").val(jsonData.data[0].TelefonoEmergencia);
      $("#ext_obs").val(jsonData.data[0].Observaciones);
      $("#ext_cp").val(jsonData.data[0].CodigoPostal);
      $("#ext_telefono").val(jsonData.data[0].Telefono);
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

  $.ajax({
    data: {
      ModificarEmpleado: 1,
      id_externo: id,
      nombre: nombre,
      dni: dni,
      domicilio: domicilio,
      city: city,
      state: state,
      nac: nac,
      ing: ing,
      licencia: licencia,
      gruposanguineo: gruposanguineo,
      phone_emergency: phone_emergency,
      codigopostal: codigopostal,
      obs: obs,
      telefono: telefono,
    },
    url: "Procesos/php/empleados.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.success == 1) {
        $("#add-new-modal").modal("hide");
        $.NotificationApp.send(
          "Exito !",
          "Registro Actualizado",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        var datatable = $("#empleados").DataTable();
        datatable.ajax.reload();
      }
    },
  });
});

$("#crear_empleado").click(function () {
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

  $.ajax({
    data: {
      Agregar_empleado: 1,
      nombre: nombre,
      dni: dni,
      domicilio: domicilio,
      city: city,
      state: state,
      nac: nac,
      ing: ing,
      licencia: licencia,
      gruposanguineo: gruposanguineo,
      phone_emergency: phone_emergency,
      codigopostal: codigopostal,
      obs: obs,
      telefono: telefono,
    },
    url: "Procesos/php/empleados.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      var notificacion = "Empleado Cargado al sistema";

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Exito !",
          notificacion,
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error !",
          "Externo No Cargado al Sistema",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }

      $("#new_externo")[0].reset();
    },
  });
});

// Ejemplo de JavaScript inicial para deshabilitar el envío de formularios si hay campos no válidos
(function () {
  "use strict";

  // Obtener todos los formularios a los que queremos aplicar estilos de validación de Bootstrap personalizados
  var forms = document.querySelectorAll(".needs-validation");

  // Bucle sobre ellos y evitar el envío
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          event.preventDefault();
          $("#add-new-modal").modal("hide");
        }

        form.classList.add("was-validated");
      },
      false
    );
  });
})();
