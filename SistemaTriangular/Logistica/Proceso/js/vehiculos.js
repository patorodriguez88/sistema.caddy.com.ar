//VERIFICAR EL ARCHIVO

function verificarExistenciaArchivo(archivoURL) {
  return new Promise(function (resolve, reject) {
    fetch(archivoURL, {
      method: "HEAD",
    })
      .then((response) => {
        if (response.status === 200) {
          resolve(1); // El archivo existe, resuelve con 1
        } else if (response.status === 404) {
          resolve(0); // El archivo no existe, resuelve con 0
        } else {
          reject("Error al verificar la existencia del archivo");
        }
      })
      .catch((error) => {
        reject("Error en la solicitud: " + error);
      });
  });
}

function up_services() {
  var id = $("#vehicle_name").html();

  // alert(id)
  $("#service-up-modal").modal("show");
  $("#service-up-modal_vehicle").html(id);

  var cadena = $("#vehicle_km").html();
  var km = cadena.match(/\d+/g);

  $("#service-up-km").val(km);
}

$("#selectOrdenes").change(function () {
  var selectedOrden = $(this).val();
  up_mantenimiento(selectedOrden);
});

function up_mantenimiento(selectedOrden) {
  var id = $("#vehicle_domain").val();
  var vehiculo = $("#vehiculo_name").html();

  $("#mantenimiento-up-modal_vehicle").html(vehiculo);
  $("#mantenimiento-up-modal").modal("show");
  $("#mantenimiento-up-modal_vehicle").html(id);
  $("#mantenimiento_estado").val("pending").css("readonly", true);

  $.ajax({
    url: "Proceso/php/vehiculos.php",
    type: "POST",
    dataType: "json",
    data: { Mantenimiento: 1, dominio: id },
    success: function (data) {
      $("#selectOrdenes").empty();

      // Llenar el select con los resultados de la consulta
      $.each(data, function (index, value) {
        $("#selectOrdenes").append(
          $("<option>", {
            value: value.NumerodeOrden,
            text: value.NumerodeOrden,
          })
        );
      });

      // Seleccionar el último registro por defecto
      if (selectedOrden) {
        $("#selectOrdenes").val(selectedOrden);
      } else {
        var defaultOption = data[data.length - 1];
        $("#selectOrdenes").val(defaultOption.NumerodeOrden);
      }

      // Actualizar los BADGED
      var selectedData = data.find(function (item) {
        return item.NumerodeOrden === $("#selectOrdenes").val();
      });

      var Fecha = selectedData.Fecha.split("-").reverse().join(".");
      $("#mantenimiento_fecha").text(Fecha);
      // var FechaFormateada = selectedData.Fecha.split("-").reverse().join("/");
      $("#mantenimiento-date").val(selectedData.Fecha);
      $("#mantenimiento_recorrido").text(selectedData.Recorrido);
      $("#mantenimiento_chofer").text(selectedData.NombreChofer);
      var primeros20 = selectedData.ObservacionesConcatenadas.substring(0, 20);
      $("#mantenimiento_titulo").val(primeros20);
      $("#mantenimiento_notas").val(selectedData.ObservacionesConcatenadas);
    },
    error: function (xhr, status, error) {
      console.error(error);
      alert("Hubo un error al obtener las órdenes.");
    },
  });
}

function open_mantenimiento_asana(gid_task) {
  $.ajax({
    url: "../Empleados/Procesos/php/asana_api.php",
    type: "POST",
    dataType: "json",
    data: { Task: 1, gid: gid_task },
    success: function (data) {
      // console.log('datos',data);

      var fechaCompleta = data.data.created_at;
      var fecha = new Date(fechaCompleta);
      var soloFecha = fecha.toISOString().split("T")[0];

      $("#mantenimiento-date-asana").val(soloFecha).css("readonly", true);
      $("#mantenimiento-modal-asana").modal("show").css("readonly", true);
      $("#mantenimiento_titulo-asana")
        .val(data.data.name)
        .css("readonly", true);
      $("#mantenimiento_notas-asana")
        .val(data.data.notes)
        .css("readonly", true);
      $("#mantenimiento_estado-asana")
        .val(data.data.memberships[0].section.name)
        .css("readonly", true);
      $("#mantenimiento_estado-asana_badge").removeClass(function (
        index,
        className
      ) {
        return (className.match(/(^|\s)badge-\S+/g) || []).join(" ");
      });

      if (data.data.memberships[0].section.name == "En Progreso") {
        $("#mantenimiento_estado-asana_badge").addClass("badge-warning");
        $("#mantenimiento_estado-asana_badge").html(
          data.data.memberships[0].section.name
        );
      }

      if (data.data.memberships[0].section.name == "Solicitud nueva") {
        $("#mantenimiento_estado-asana_badge").addClass("badge-primary");
        $("#mantenimiento_estado-asana_badge").html(
          data.data.memberships[0].section.name
        );
      }

      if (data.data.memberships[0].section.name == "Finalizado") {
        $("#mantenimiento_estado-asana_badge").addClass("badge-success");
        $("#mantenimiento_estado-asana_badge").html(
          data.data.memberships[0].section.name
        );
      }

      $("#mantenimiento_prioridad-asana")
        .val(data.data.resource_subtype)
        .css("readonly", true);
      $("#mantenimiento_link-asana")
        .attr("href", data.data.permalink_url)
        .css("readonly", true);
    },
  });
}

$("#service-up-modal-btn-ok").click(function () {
  var id = $("#vehicle_domain").val();
  var fecha = $("#service-up-date").val();
  var km = $("#service-up-km").val();
  var place = $("#service-up-place").val();
  var costo = $("#service-up-costo").val();
  var obs = $("#service-up-obs").val();

  $.ajax({
    url: "Proceso/php/vehiculos.php",
    type: "POST",
    dataType: "json",
    data: {
      Service_new: 1,
      dominio: id,
      fecha: fecha,
      km: km,
      place: place,
      costo: costo,
      obs: obs,
    },
    success: function (data) {
      if (data.success == 1) {
        $.NotificationApp.send(
          "Exito!",
          "Se ha cargado el registro.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Error!",
          "No se ha cargado el registro error: " + data.error,
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
});

$("#mantenimiento_btn_ok").click(function () {
  var id = $("#vehicle_domain").val();
  var fecha = $("#mantenimiento-date").val();
  var titulo = $("#mantenimiento_titulo").val();
  var nota = $("#mantenimiento_notas").val();
  var estado = $("#mantenimiento_estado").val();
  var prioridad = $("#mantenimiento_prioridad").val();
  var orden = $("#selectOrdenes").val();

  $.ajax({
    url: "Proceso/php/vehiculos.php",
    type: "POST",
    dataType: "json",
    data: {
      Agregar_Mantenimiento: 1,
      dominio: id,
      fecha: fecha,
      titulo: titulo,
      nota: nota,
      estado: estado,
      prioridad: prioridad,
      orden: orden,
    },
    success: function (data) {
      var jsonData = data;

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Exito!",
          "Se ha cargado el registro.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        $("#vehicle_breafing").css("display", "block");

        var datatable = $("#table_breafing").DataTable();

        datatable.destroy();

        access_breafing();
        // datatable.ajax.reload();
      } else {
        $.NotificationApp.send(
          "Error!",
          "No se pudo cargar el registro error: " + jsonData.error,
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
});

function comprueba_imagen(id) {
  // Uso de la función para verificar la existencia de un archivo
  verificarExistenciaArchivo("Images/" + id + ".jpg")
    .then(function (resultado) {
      // Obtener el elemento <img> por su ID
      var imagen = $("#vehicle_image");

      if (resultado == 1) {
        // Nueva ruta de la imagen
        var nuevaRuta = "Images/" + id + ".jpg";
      } else {
        // Nueva ruta de la imagen
        var nuevaRuta = "Images/LogoCaddy.png";
      }
      // Modificar el atributo src
      imagen.attr("src", nuevaRuta);
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

$("#vehicles-up-modal").on("hidden.bs.modal", function () {
  var id = $("#vehicle_domain").val();
  comprueba_imagen(id);
});

//ABRIR MODAL FOTO VEHICULO
$("#vehicle_image").click(function () {
  $("#vehicles-up-modal").modal("show");
  $("#vehicle_up_domain_value").val(2);
  $("#standard-modalLabel").html("Seleccoinar una fotografia del Vehículo");
});

//ABRIR MODAL CARGAR IMPUESTO
function up_access_tax() {
  var fechaActual = new Date().toISOString().split("T")[0];
  document.getElementById("vehicles-up-tax-fecha").value = fechaActual;

  $("#vehicles_form")[0].reset();

  var id = $("#vehicle_domain").val();

  $("#vehicles-up-tax").modal("show");
  $("#vehicles-up-tax_vehicle").html(id);
  $("#vehicles-up-tax-modalLabel").html("Agregar Impuestos Vehículo " + id);
  $("#vehicles_up_tax_modify").val(0);

  console.log("ver ", $("#vehicles_up_tax_modify").val());
}

$("#vehicles-up-tax-impuesto").change(function () {
  var impuestoValor = $(this).find("option:selected").text();
  var dominio = $("#vehicle_domain").val();

  $.ajax({
    url: "Proceso/php/vehiculos.php",
    type: "POST",
    data: { Buscar_Mes: 1, Impuesto: impuestoValor, Dominio: dominio },
    success: function (data) {
      var jsonData = JSON.parse(data);

      if (jsonData.success == 1) {
        if (jsonData.mes) {
          $("#vehicles-up-tax-mes").val(jsonData.mes);
        } else {
          var mes_actual = new Date().getMonth() + 1;
          $("#vehicles-up-tax-mes").val(mes_actual);
        }

        if (jsonData.anio) {
          $("#vehicles-up-tax-anio").val(jsonData.anio);
        } else {
          var anio_actual = new Date().getFullYear();
          $("#vehicles-up-tax-anio").val(anio_actual);
        }
      }
    },
  });
});

function vehicles_up_tax_total() {
  var importe = $("#vehicles-up-tax-importe").val();
  var multa = $("#vehicles-up-tax-multa").val();
  var recargo = $("#vehicles-up-tax-recargo").val();
  var descuento = $("#vehicles-up-tax-descuento").val();
  /*sumar impuestos*/
  var total =
    parseFloat(importe) +
    parseFloat(multa) +
    parseFloat(recargo) -
    parseFloat(descuento);
  /*dar formato de moneda a total*/
  var formateado = new Intl.NumberFormat("es-AR", {
    style: "currency",
    currency: "ARS",
    currencyDisplay: "symbol",
  }).format(total);

  $("#vehicles-up-tax-total").html("Total: " + formateado);
}

//CARGAR TAX
$("#vehicles-up-tax-btn-ok").click(function () {
  var dominio = $("#vehicles-up-tax_vehicle").html();
  var cuenta = $("#vehicles-up-tax-impuesto option:selected").text();
  var Numero_cuenta = $("#vehicles-up-tax-impuesto").val();
  var Numero_cuenta_modify = $("#vehicles-up-tax-impuesto-label-cuenta").val();
  var NAsiento = $("#vehicles-up-tax-Asiento").val();
  var fecha = $("#vehicles-up-tax-fecha").val();
  var mes = $("#vehicles-up-tax-mes").val();
  var anio = $("#vehicles-up-tax-anio").val();
  var vencimiento = $("#vehicles-up-tax-vencimiento").val();
  var observaciones = $("#vehicles-up-tax-obs").val();
  var importe = $("#vehicles-up-tax-importe").val();
  var multa = $("#vehicles-up-tax-multa").val();
  var recargo = $("#vehicles-up-tax-recargo").val();
  var descuento = $("#vehicles-up-tax-descuento").val();
  var referencia = $("#vehicles-up-tax-referencia").val();
  var id = $("#vehicles_up_tax_modify").val();

  if ($("#vehicles_up_tax_modify").val() == 0) {
    $.ajax({
      data: {
        Tax_insert: 1,
        Dominio: dominio,
        Cuenta: cuenta,
        Importe: importe,
        Fecha: fecha,
        Dominio: dominio,
        Mes: mes,
        Anio: anio,
        Vencimiento: vencimiento,
        Pagado: 0,
        Observaciones: observaciones,
        NCuenta: Numero_cuenta,
        Multa: multa,
        Recargo: recargo,
        Descuento: descuento,
        Referencia: referencia,
      },
      type: "POST",
      url: "Proceso/php/vehiculos.php",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == true) {
          $.NotificationApp.send(
            "Exito!",
            "Se ha cargado el registro.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );

          var datatable = $("#table_tax").DataTable();
          datatable.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Error!",
            "No se pudo cargar el registro.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
      },
    });
  } else {
    $.ajax({
      data: {
        Tax_modify: 1,
        Dominio: dominio,
        Cuenta: cuenta,
        Importe: importe,
        Fecha: fecha,
        Dominio: dominio,
        Mes: mes,
        Anio: anio,
        Vencimiento: vencimiento,
        Pagado: 0,
        Observaciones: observaciones,
        NCuenta: Numero_cuenta_modify,
        Multa: multa,
        Recargo: recargo,
        Descuento: descuento,
        Referencia: referencia,
        Id: id,
        NAsiento: NAsiento,
      },
      type: "POST",
      url: "Proceso/php/vehiculos.php",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == true) {
          $.NotificationApp.send(
            "Exito!",
            "Se modifico el registro.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );

          var datatable = $("#table_tax").DataTable();
          datatable.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Error!",
            "No se pudo modificar el registro.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
      },
    });
  }
});

//ABRIR MODAL SUBIR ARCHIVO TITULO
function up_access_services() {
  $("#vehicles-up-modal").modal("show");
}

//ABRIR MODAL SUBIR ARCHIVO SEGURO
function up_access_services_sure() {
  $("#vehicles-up-modal_sure").modal("show");
}
//AL ABRIR LA VENTANA DEL SUBIR ARCHIVO
$("#vehicles-up-modal_sure").on("show.bs.modal", function () {
  let dominio = $("#vehicle_domain").val();
  console.log("hola mundo", dominio);
  cargarDatosSeguro(dominio);
});

//AL CERRAR LA VENTANA DE SUBIR ARCHIVO
$("#vehicles-up-modal_sure").on("hidden.bs.modal", function () {
  var id = $("#vehicle_domain").val();
  open_vehicle(id);
});

//ACCESOS

function access_services() {
  var id = $("#vehicle_domain").val();

  console.log("ver", id);

  $("#access_services").addClass("bg-primary-lighten");
  $("#access_qualification").removeClass("bg-primary-lighten");
  $("#access_sure").removeClass("bg-primary-lighten");
  $("#access_fines").removeClass("bg-primary-lighten");
  $("#vehicle_service").css("display", "block");
  $("#vehicle_fines").css("display", "none");
  $("#vehicle_breafing").css("display", "none");
  $("#row_contenedor_qualification").css("display", "none");
  $("#row_contenedor_sure").css("display", "none");
  $("#access_breafing").removeClass("bg-primary-lighten");
  $("#table_fines").DataTable().destroy();
  $("#table_service").DataTable().destroy();

  //TABLA SERVICE

  var datatable_service = $("#table_service").DataTable({
    paging: false,
    searching: false,
    dom: "Bfrtip",
    buttons: ["excel", "pdf"],
    ajax: {
      url: "Proceso/php/vehiculos.php",
      data: { Service: 1, Patent: id },
      type: "post",
    },
    columnDefs: [
      {
        targets: 0, // Indica que la ordenación personalizada se aplicará a la primera columna (0-indexed)
        type: "date-eu", // Utilizar el tipo de fecha 'date-eu' para el ordenamiento personalizado
        render: function (data, type, row) {
          // Mantener el formato original de la fecha para ordenamiento
          return row.FechaServiceRealizado;
        },
      },
    ],
    columns: [
      {
        data: "FechaServiceRealizado",
        render: function (data, type, row) {
          var Fecha = row.FechaServiceRealizado.split("-").reverse().join(".");
          return (
            '<td class="display:none" value="' +
            row.FechaServiceRealizado +
            '">' +
            Fecha +
            "</td>"
          );
        },
      },
      { data: "LugarService" },
      { data: "kmReales" },
      { data: "ServiceRealizado" },
      { data: "Observaciones" },
      { data: "CostoService" },
      {
        data: "Estado",
        render: function (data, type, row) {
          if (row.Estado == "Disponible") {
            var color = "success";
          } else if (row.Estado == "Otro" || row.Estado == "En Taller") {
            var color = "warning";
          } else {
            var color = "danger";
          }
          return (
            "<td>" +
            "<i class='mdi mdi-circle text-" +
            color +
            "'></i>" +
            row.Estado +
            "</td>"
          );
        },
      },
      {
        data: "Accion",
        render: function (data, type, row) {
          return `<td><a class='action-icon'> <i class='mdi mdi-14px mdi-eye text-success'></i></a>
                    <a href='javascript:void(0);' class='action-icon'> <i class='mdi mdi-14px mdi-square-edit-outline'></i></a>
                    <a class='action-icon'> <i class='mdi mdi-14px mdi-delete text-danger'></i></a>
                    </td>`;
        },
      },
    ],
  });
}

function access_qualification() {
  var id = $("#vehicle_domain").val();

  $("#row_contenedor_sure").css("display", "none");

  $("#access_qualification").addClass("bg-primary-lighten");
  $("#access_services").removeClass("bg-primary-lighten");
  $("#access_sure").removeClass("bg-primary-lighten");
  $("#access_fines").removeClass("bg-primary-lighten");
  $("#access_breafing").removeClass("bg-primary-lighten"); //MANTENIMIENTO

  $("#vehicle_service").css("display", "none");
  $("#vehicle_breafing").css("display", "none");

  // Uso de la función para verificar la existencia de un archivo
  verificarExistenciaArchivo("Titulos/" + id + ".pdf")
    .then(function (resultado) {
      if (resultado == 1) {
        var iframe = $("<iframe>", {
          src: "Titulos/" + id + ".pdf", // Reemplaza con la URL de tu archivo PDF
          width: "1070",
          height: "1000",
          frameborder: "0",
        });

        // Agregar el iframe al elemento contenedor

        $("#row_contenedor_qualification").css("display", "block");
        $("#vehicle_up_domain_value").val(1);
        $("#contenedor_qualification").append(iframe);
      }
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function cargarDatosSeguro(dominio) {
  $.post(
    "Proceso/php/vehiculos.php",
    { accion: "obtener_datos_seguro", dominio },
    function (data) {
      $("#inputNumeroPoliza").val(data.NumeroPoliza);
      $("#inputTelefonoSeguro").val(data.TelefonoSeguro);
      $("#inputFechaVencSeguro").val(data.FechaVencSeguro);
    },
    "json"
  );
}

function access_sure() {
  $("#contenedor_sure").empty();
  var id = $("#vehicle_domain").val();

  $("#row_contenedor_qualification").css("display", "none");

  $("#access_services").removeClass("bg-primary-lighten");

  $("#access_sure").addClass("bg-primary-lighten");
  $("#access_qualification").removeClass("bg-primary-lighten");
  $("#access_services").removeClass("bg-primary-lighten");
  $("#access_fines").removeClass("bg-primary-lighten");
  $("#access_breafing").removeClass("bg-primary-lighten"); //MANTENIMIENTO
  $("#vehicle_service").css("display", "none");
  $("#vehicle_fines").css("display", "none");
  $("#vehicle_breafing").css("display", "none");
  // Agregar una cadena de consulta única al nombre del archivo
  var url = "Polizas/" + id + ".pdf?timestamp=" + new Date().getTime();

  // Uso de la función para verificar la existencia de un archivo
  verificarExistenciaArchivo("Polizas/" + id + ".pdf")
    .then(function (resultado) {
      console.log("Resultado: " + resultado);

      if (resultado == 1) {
        var iframe = $("<iframe>", {
          src: url, // Reemplaza con la URL de tu archivo PDF
          width: "1070",
          height: "1000",
          frameborder: "0",
        });

        // Agregar el iframe al elemento contenedor
        $("#row_contenedor_sure").css("display", "block");
        $("#contenedor_sure").append(iframe);
      }
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function formatearFecha(fechaISO) {
  if (!fechaISO) return "-";
  const partes = fechaISO.split("-");
  return partes.reverse().join("/");
}

function access_breafing() {
  const id = $("#vehicle_domain").val();

  // Ocultar otras secciones
  $("#vehicle_service").hide();
  $("#vehicle_fines").hide();
  $("#vehicle_tax").hide();
  $("#row_contenedor_qualification").hide();
  $("#row_contenedor_sure").hide();

  // Limpiar botones activos
  $(
    "#access_services, #access_qualification, #access_sure, #access_fines, #access_tax"
  ).removeClass("bg-primary-lighten");

  // Mostrar sección breafing
  $("#vehicle_breafing").show();
  $("#access_breafing").addClass("bg-primary-lighten");

  // Destruir DataTables anteriores si existen
  if ($.fn.DataTable.isDataTable("#table_breafing")) {
    $("#table_breafing").DataTable().destroy();
  }

  // Pedir los datos del breafing al backend
  $.post(
    "Proceso/php/vehiculos.php",
    { Breafing: 1, Patent: id },
    function (response) {
      let data = {};

      try {
        data = JSON.parse(response);
      } catch (e) {
        console.error("Respuesta inválida del backend", response);
        return;
      }

      // Cargar spans con los datos de logística (si existen)
      if (
        data.logistica &&
        Array.isArray(data.logistica) &&
        data.logistica.length > 0
      ) {
        const ultima = data.logistica[0]; // Usamos el primer registro

        $("#mantenimiento_fecha_obs").text(formatearFecha(ultima.Fecha));
        $("#mantenimiento_recorrido_obs").text(
          "Recorrido " + ultima.Recorrido || "Sin datos"
        );
        $("#mantenimiento_chofer_obs").text(
          ultima.NombreChofer || "Sin asignar"
        );
      } else {
        $("#mantenimiento_fecha_obs").text("Sin registros");
        $("#mantenimiento_recorrido_obs").text("-");
        $("#mantenimiento_chofer_obs").text("-");
      }

      // Inicializar tabla breafing con los datos de mantenimiento
      $("#table_breafing").DataTable({
        paging: false,
        searching: false,
        ajax: {
          url: "Proceso/php/vehiculos.php",
          type: "POST",
          data: { Breafing: 1, Patent: id },
          dataSrc: "mantenimiento",
        },
        columns: [
          {
            data: "Fecha",
            render: function (data, type, row) {
              const FechaVisible = row.Fecha.split("-").reverse().join(".");
              return `<span style="display:none;">${row.Fecha}</span>${FechaVisible}`;
            },
          },
          { data: "Titulo" },
          { data: "Notas" },
          { data: "Prioridad" },
          { data: "Estado" },
          { data: "NombreChofer" },
          { data: "Usuario" },
          {
            data: null,
            render: function (data, type, row) {
              const editar = row.gid_task
                ? `<a onclick='open_mantenimiento_asana(${row.gid_task})' class='action-icon'>
                   <i class='mdi mdi-14px mdi-square-edit-outline text-warning'></i>
                 </a>`
                : "";

              const eliminar = `<a class='action-icon'>
                                <i class='mdi mdi-14px mdi-delete text-danger'></i>
                              </a>`;

              return editar + eliminar;
            },
          },
        ],
      });
    }
  );
}

// function access_breafing() {
//   var id = $("#vehicle_domain").val();

//   // console.log('ver',id);
//   $("#vehicle_service").css("display", "none");
//   $("#access_services").removeClass("bg-primary-lighten");
//   $("#access_qualification").removeClass("bg-primary-lighten");
//   $("#access_sure").removeClass("bg-primary-lighten");
//   $("#access_fines").removeClass("bg-primary-lighten");
//   $("#vehicle_fines").css("display", "none");
//   $("#table_fines").DataTable().destroy();
//   $("#table_service").DataTable().destroy();
//   $("#row_contenedor_qualification").css("display", "none");
//   $("#row_contenedor_sure").css("display", "none");
//   $("#vehicle_tax").css("display", "none"); //IMPUESTOS
//   $("#access_tax").removeClass("bg-primary-lighten"); //BUTTON IMPUESTOS

//   $("#vehicle_breafing").css("display", "block");
//   $("#access_breafing").addClass("bg-primary-lighten");

//   $("#mantenimiento_fecha_obs").text(formatearFecha(ultima.Fecha));
//   $("#mantenimiento_recorrido_obs").text(ultima.Recorrido || "Sin datos");
//   $("#mantenimiento_chofer_obs").text(ultima.NombreChofer || "Sin asignar");
//   //TABLA BREAFING
//   var datatable_breafing = $("#table_breafing").DataTable();
//   datatable_breafing.destroy();

//   // $("#table_breafing").DataTable({
//   //   paging: false,
//   //   searching: false,
//   //   ajax: {
//   //     url: "Proceso/php/vehiculos.php",
//   //     data: { Breafing: 1, Patent: id },
//   //     type: "post",
//   //   },
//   //   columns: [
//   //     {
//   //       data: "Fecha",
//   //       render: function (data, type, row) {
//   //         var Fecha = row.Fecha.split("-").reverse().join(".");
//   //         return (
//   //           '<td class="display:none" value="' +
//   //           row.Fecha +
//   //           '">' +
//   //           Fecha +
//   //           "</td>"
//   //         );
//   //       },
//   //     },
//   //     { data: "Titulo" },
//   //     { data: "Notas" },
//   //     { data: "Prioridad" },
//   //     { data: "Estado" },
//   //     { data: "NombreChofer" },
//   //     { data: "Usuario" },
//   //     {
//   //       data: "Accion",
//   //       render: function (data, type, row) {
//   //         if (row.gid_task) {
//   //           return `<td>
//   //                       <a onclick='open_mantenimiento_asana(${row.gid_task})' class='action-icon'> <i class='mdi mdi-14px mdi-square-edit-outline'></i></a>
//   //                       <a class='action-icon'> <i class='mdi mdi-14px mdi-delete text-danger'></i></a></td>`;
//   //         } else {
//   //           return `<td><a class='action-icon'> <i class='mdi mdi-14px mdi-delete text-danger'></i></a></td>`;
//   //         }
//   //       },
//   //     },
//   //   ],
//   // });
//   $("#table_breafing").DataTable({
//     paging: false,
//     searching: false,
//     ajax: {
//       url: "Proceso/php/vehiculos.php",
//       data: { Breafing: 1, Patent: id },
//       type: "post",
//       dataSrc: "mantenimiento", // 👈 si estás devolviendo { mantenimiento: [...] }
//     },
//     columns: [
//       {
//         data: "Fecha",
//         render: function (data, type, row) {
//           let FechaVisible = row.Fecha.split("-").reverse().join(".");
//           return `<span style="display:none;">${row.Fecha}</span>${FechaVisible}`;
//         },
//       },
//       { data: "Titulo" },
//       { data: "Notas" },
//       { data: "Prioridad" },
//       { data: "Estado" },
//       { data: "NombreChofer" },
//       { data: "Usuario" },
//       {
//         data: null,
//         render: function (data, type, row) {
//           let editar = row.gid_task
//             ? `<a onclick='open_mantenimiento_asana(${row.gid_task})' class='action-icon'>
//                  <i class='mdi mdi-14px mdi-square-edit-outline text-warning'></i>
//                </a>`
//             : "";

//           let eliminar = `<a class='action-icon'>
//                             <i class='mdi mdi-14px mdi-delete text-danger'></i>
//                           </a>`;

//           return editar + eliminar;
//         },
//       },
//     ],
//   });
//   // Escuchar el evento 'draw.dt' para obtener el total de registros después de que la tabla se haya dibujado
//   // table.on('draw.dt', function() {
//   //     var totalRegistros = table.page.info().recordsTotal;
//   //     $('#access_breafing_total').html(totalRegistros);
//   // });
// }

// MULTAS

$("#access_fines").click(function () {
  $("#access_fines").addClass("bg-primary-lighten");
  $("#access_qualification").removeClass("bg-primary-lighten");
  $("#access_sure").removeClass("bg-primary-lighten");
  $("#access_services").removeClass("bg-primary-lighten");
  $("#access_tax").removeClass("bg-primary-lighten");
  $("#access_breafing").removeClass("bg-primary-lighten"); //MANTENIMIENTO

  $("#vehicle_service").css("display", "none");

  $("#vehicle_tax").css("display", "none");
  $("#row_contenedor_sure").css("display", "none");
  $("#row_contenedor_qualification").css("display", "none");

  $("#vehicle_fines").css("display", "block");

  $("#table_service").DataTable().destroy();
  $("#table_fines").DataTable().destroy();

  var table_fines = $("#table_fines").DataTable();
  table_fines.destroy();

  //TABLA SERVICE
  var id = $("#vehicle_domain").val();

  $("#table_fines").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "Proceso/php/vehiculos.php",
      data: { Fines: 1, Patent: id },
      type: "post",
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return '<td value="' + row.Fecha + '">' + Fecha + "</td>";
        },
      },
      { data: "Municipio" },
      // {data:"Patente"},
      { data: "Vencimiento" },
      { data: "Importe" },
      { data: "Numero" },
      { data: "Motivo" },
      { data: "Estado" },
      { data: "Empleado" },
      {
        data: null,
        render: function (data, type, row) {
          return `<td><a class='action-icon'> <i class='mdi mdi-14px mdi-eye text-success'></i></a>
                    <a href='javascript:void(0);' class='action-icon'> <i class='mdi mdi-14px mdi-square-edit-outline'></i></a>
                    <a class='action-icon'> <i class='mdi mdi-14px mdi-delete text-danger'></i></a>
                    </td>`;
        },
      },
    ],
  });
});

//IMPUESTOS
$("#access_tax").click(function () {
  $("#access_fines").removeClass("bg-primary-lighten");
  $("#access_qualification").removeClass("bg-primary-lighten");
  $("#access_sure").removeClass("bg-primary-lighten");
  $("#access_services").removeClass("bg-primary-lighten");
  $("#access_breafing").removeClass("bg-primary-lighten"); //MANTENIMIENTO

  $("#access_tax").addClass("bg-primary-lighten");

  $("#vehicle_service").css("display", "none");
  $("#vehicle_fines").css("display", "none");
  $("#vehicle_breafing").css("display", "none"); //MANTENIMIENTO

  $("#vehicle_tax").css("display", "block");

  $("#table_service").DataTable().destroy();
  $("#table_fines").DataTable().destroy();
  $("#table_tax").DataTable().destroy();

  $("#row_contenedor_qualification").css("display", "none");
  $("#row_contenedor_sure").css("display", "none");

  var table_tax = $("#table_tax").DataTable();
  table_tax.destroy();

  //TABLA IMPUESTOS
  var id = $("#vehicle_domain").val();

  $("#table_tax").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "Proceso/php/vehiculos.php",
      data: { Tax: 1, Patent: id },
      type: "post",
    },
    columns: [
      {
        data: "Impuesto",
        render: function (data, type, row) {
          if (row.Impuesto == "IMPUESTO AUTOMOTOR MUNICIPAL") {
            var texto = "IMP.MUNICIPAL";
          } else {
            texto = "IMP.PROVINCIAL";
          }
          return texto;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return row.Anio + "/" + row.Mes;
        },
      },
      {
        data: "Vencimiento",
        render: function (data, type, row) {
          var Fecha = row.Vencimiento.split("-").reverse().join("/");
          return '<td value="' + row.Vencimiento + '">' + Fecha + "</td>";
        },
      },
      { data: "Importe" },
      { data: "Descuento" },
      { data: "Recargo" },
      { data: "Multa" },
      { data: "Total" },
      {
        data: "Pagado",
        render: function (data, type, row) {
          if (row.Pagado == 1) {
            return "<span class='badge badge-success'>Pagado</span>";
          } else {
            return "<span class='badge badge-danger'>Pendiente</span>";
          }
        },
      },
      { data: "Referencia" },
      {
        data: null,
        render: function (data, type, row) {
          return `<td>
                    <a href='javascript:void(0);' class='action-icon'> <i onclick='tax_modify(${row.id})' class='mdi mdi-square-edit-outline text-warning'></i></a>
                    <a class='action-icon'> <i onclick='tax_pay(${row.id})' class='mdi mdi-cash text-success'></i></a>
                    <a class='action-icon'> <i onclick='tax_delete(${row.id})' class='mdi mdi-delete text-danger'></i></a>                    
                    </td>`;
        },
      },
    ],
  });
});

//CIERRE VENTANA VEHICULOS
$("#vehicle_close").click(function () {
  $("#vehicle").css("display", "none");

  $("#panel_flota").css("display", "block");

  $("#table_service").DataTable().destroy();
});

function open_vehicle(v) {
  comprueba_imagen(v);
  $("#row_contenedor_qualification").css("display", "none");
  $("#row_contenedor_sure").css("display", "none");

  //oculto las tablas
  $("#vehicle_fines").css("display", "none");
  $("#vehicle_service").css("display", "none");
  $("#vehicle_breafing").css("display", "none");
  $("#vehicle_tax").css("display", "none");

  $("#vehicle_domain").val(v); //PARA MODAL VEHICULOS
  $("#vehicle_up_domain").val(v); //PARA MODAL TITULO
  $("#vehicle_up_domain_sure").val(v); //PARA MODAL SEGURO

  // $('#vehicle_service').DataTable().ajax.reload();
  $("#table_service").DataTable().destroy();
  $("#table_fines").DataTable().destroy();
  $("#table_tax").DataTable().destroy();

  $.ajax({
    data: { Search_vehicle: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      $("#vehicle").css("display", "block");

      $("#panel_flota").css("display", "none");

      $("#vehicle_name").html(
        `${jsonData.data[0].Marca} ${jsonData.data[0].Modelo} (${jsonData.data[0].Color}) ${jsonData.data[0].Dominio}`
      );

      $("#vehicle_year").html(`Modelo ${jsonData.data[0].Ano}`);

      if (jsonData.data[0].Estado == "Disponible") {
        var color = "success";
      } else if (
        jsonData.data[0].Estado == "Otro" ||
        jsonData.data[0].Estado == "En Taller"
      ) {
        var color = "warning";
      } else {
        var color = "danger";
      }

      // Formatear el número con separadores de miles y decimales personalizados
      var opciones = {
        useGrouping: true, // Usar separadores de miles
        minimumFractionDigits: 0, // Mostrar al menos 2 decimales
        maximumFractionDigits: 0, // Mostrar como máximo 2 decimales
        style: "decimal", // Puedes usar 'currency' para monedas u otras opciones
      };

      $("#vehicle_status").html(
        "<span class='badge badge-" +
          color +
          "-lighten'>" +
          jsonData.data[0].Estado +
          "</span>"
      );
      $("#vehicle_engine_number").html(`Motor ${jsonData.data[0].Motor}`);
      $("#vehicle_chassis").html(`Chasis ${jsonData.data[0].Chasis}`);
      $("#vehicle_obs").html(jsonData.data[0].Observaciones);
      $("#vehicle_km").html(
        jsonData.data[0].Kilometros.toLocaleString(undefined, opciones) + " Km."
      );
      $("#vehicle_sure").html(jsonData.data[0].Seguro);
      $("#vehicle_sure_number").html(jsonData.data[0].NumeroPoliza);
      $("#vehicle_sure_phone").html(jsonData.data[0].TelefonoSeguro);
    },
  });

  //TOTALES

  $.ajax({
    data: { Service: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      console.log("json", jsonData);

      $("#access_services_total").html(jsonData.data.length);

      if (jsonData.data != 0) {
        $("#access_services_icon")
          .removeClass("text-primary")
          .addClass("text-success");
      } else {
        $("#access_services_icon")
          .removeClass("text-primary")
          .removeClass("text-success");
      }
    },
  });

  //MANTENIMIENTO
  $.ajax({
    data: { Breafing: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var logistica = jsonData.logistica || [];

      if (logistica.length > 0) {
        const ultima = logistica[0];

        $("#mantenimiento_fecha_obs").text(formatearFecha(ultima.Fecha));
        $("#mantenimiento_recorrido_obs").text(
          "Recorrido " + (ultima.Recorrido || "Sin datos")
        );
        $("#mantenimiento_chofer_obs").text(
          ultima.NombreChofer || "Sin asignar"
        );
      } else {
        $("#mantenimiento_fecha_obs").text("Sin registros");
        $("#mantenimiento_recorrido_obs").text("-");
        $("#mantenimiento_chofer_obs").text("-");
      }
    },
  });
  // $.ajax({
  //   data: { Breafing: 1, Patent: v },
  //   type: "POST",
  //   url: "Proceso/php/vehiculos.php",
  //   success: function (response) {
  //     var jsonData = JSON.parse(response);

  //     console.log("json", jsonData.data);

  //     $("#access_breafing_total").html(jsonData.data.length);

  //     if (jsonData.data != 0) {
  //       $("#access_breafing_icon")
  //         .removeClass("text-primary")
  //         .addClass("text-success");
  //     } else {
  //       $("#access_breafing_icon")
  //         .removeClass("text-primary")
  //         .removeClass("text-success");
  //     }
  //   },
  // });

  //MULTAS

  $.ajax({
    data: { Fines: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      console.log("json", jsonData);

      $("#access_fines_total").html(jsonData.data.length);

      if (jsonData.data != 0) {
        $("#access_fines_icon")
          .removeClass("text-primary")
          .addClass("text-success");
      } else {
        $("#access_fines_icon")
          .removeClass("text-primary")
          .removeClass("text-success");
      }
    },
  });

  //TITULO

  $.ajax({
    data: { Qualification: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      console.log("json", jsonData);

      $("#access_qualification_total").html(jsonData.data);
      if (jsonData.data != 0) {
        $("#access_qualification_icon")
          .removeClass("text-primary")
          .addClass("text-success");
      } else {
        $("#access_qualification_icon")
          .removeClass("text-primary")
          .removeClass("text-success");
      }
    },
  });

  //SEGURO

  $.ajax({
    data: { Sure: 1, Patent: v },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      console.log("json", jsonData);

      $("#access_sure_total").html(jsonData.data);

      if (jsonData.data != 0) {
        $("#access_sure_icon")
          .removeClass("text-primary")
          .addClass("text-success");
      } else {
        $("#access_sure_icon")
          .removeClass("text-primary")
          .removeClass("text-success");
      }
    },
  });
}

function tax_delete(id) {
  $("#danger-header-modal_tax_delete").modal("show");
  $("#danger-header-modalLabel").html("Eliminar Impuesto");
  $("#danger-header-modal_tax_delete .modal-body").html(
    "Estas por eliminar el id " +
      id +
      " esto reversará un asiento en tesoreria y eliminara el registro en la tabla impuestos."
  );

  $("#danger-header-modal_tax_delete_btn_ok").click(function () {
    $.ajax({
      data: { Tax_delete: 1, id: id },
      type: "POST",
      url: "Proceso/php/vehiculos.php",
      success: function (response) {
        var jsonData = JSON.parse(response);

        if (jsonData.success == 1) {
          $.NotificationApp.send(
            "Exito!",
            "Se ha eliminado el registro.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );

          var datatable = $("#table_tax").DataTable();
          datatable.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Error!",
            "No se ha eliminado el registro.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
      },
    });

    $("#danger-header-modal_tax_delete").modal("hide");
  });
}

function tax_modify(id_tax) {
  var id = $("#vehicle_domain").val();

  $("#vehicles_up_tax_modify").val(id_tax);

  $.ajax({
    data: { Tax_data: 1, id_tax: id_tax },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      // console.log('valor',$('#vehicles_up_tax_modify').val());

      var jsonData = JSON.parse(response);
      // console.log('aca',jsonData.data[0]);
      // console.log('ver',$("#vehicles-up-tax-impuesto").length);

      $("#vehicles-up-tax-impuesto-label").css("display", "show");

      $("#vehicles-up-tax-impuesto-label").val(jsonData.data[0].Impuesto);
      $("#vehicles-up-tax-impuesto-label-cuenta").val(jsonData.data[0].Cuenta);

      $("#vehicles-up-tax").modal("show");

      $("#vehicles-up-tax-fecha")
        .val(jsonData.data[0].Fecha)
        .css("enabled", "false");
      $("#vehicles-up-tax-mes")
        .val(jsonData.data[0].Mes)
        .css("enabled", "false");
      $("#vehicles-up-tax-anio")
        .val(jsonData.data[0].Anio)
        .css("enabled", "false");
      $("#vehicles-up-tax-multa").val(jsonData.data[0].Multa);
      $("#vehicles-up-tax-importe").val(jsonData.data[0].Importe);
      $("#vehicles-up-tax-recargo").val(jsonData.data[0].Recargo);
      $("#vehicles-up-tax-referencia").val(jsonData.data[0].Referencia);
      $("#vehicles-up-tax-obs").val(jsonData.data[0].Observaciones);
      $("#vehicles-up-tax-vencimiento").val(jsonData.data[0].Vencimiento);
      $("#vehicles-up-tax-Asiento").val(jsonData.data[0].NumeroAsiento);

      var total = jsonData.data[0].Total;
      /*dar formato de moneda a total*/
      var formateado = new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        currencyDisplay: "symbol",
      }).format(total);

      $("#vehicles-up-tax-total").html("Total: " + formateado);

      $("#vehicles-up-tax_vehicle").html(id);
      $("#vehicles-up-tax-icon").html(
        "Si existen modificaciones con el asiento orignal, se modificarán los registros."
      );

      $("#vehicles-up-tax-modalLabel").html("Impuestos Vehículo " + id);
      $("#vehicles-up-tax-impuesto").hide();
    },
  });
}

function tax_pay(id_tax) {
  $.ajax({
    data: { cuadro_forma_de_pago: 1 },
    type: "POST",
    url: "Proceso/php/vehiculos.php",
    success: function (response) {
      $("#cuadro_forma_de_pago").html(response);
    },
  });

  $("#modal_cargar_pagos").modal("show");
}

$(document).ready(function () {
  var datatable1 = $("#flota").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "Proceso/php/vehiculos.php",
      data: { Flota: 1 },
      type: "post",
    },
    columns: [
      { data: "Marca" },
      { data: "Dominio" },
      { data: "Ano" },
      {
        data: "Kilometros",
        render: $.fn.dataTable.render.number(".", ",", 0, ""),
      },
      {
        data: "Estado",
        render: function (data, type, row) {
          if (row.Estado == "Disponible") {
            var color = "success";
          } else if (row.Estado == "Otro" || row.Estado == "En Taller") {
            var color = "warning";
          } else {
            var color = "danger";
          }
          return (
            "<td>" +
            "<i class='mdi mdi-circle text-" +
            color +
            "'></i>" +
            row.Estado +
            "</td>"
          );
        },
      },
      {
        data: "Estado",
        render: function (data, type, row) {
          var v = '"' + row.Dominio + '"';
          return `<td><a style='cursor:pointer' onclick='open_vehicle(${v})' class='action-icon'> <i class='mdi mdi-square-edit-outline text-success'></i></a></td>`;
        },
      },
    ],
  });
});

$("#vehicles-up-modal_sure_ok").on("click", function () {
  let datos = {
    action: "guardar_seguro",
    dominio: $("#vehicle_up_domain_sure").val(),
    NumeroPoliza: $("#inputNumeroPoliza").val(),
    TelefonoSeguro: $("#inputTelefonoSeguro").val(),
    FechaVencSeguro: $("#inputFechaVencSeguro").val(),
  };

  $.ajax({
    url: "../Logistica/Proceso/php/vehiculos.php",
    type: "POST",
    data: datos,
    success: function (response) {
      if (response === "ok") {
        $("#vehicles-up-modal_sure").modal("hide");
        toastr.success("Seguro actualizado correctamente");

        // 👇 Volvés a cargar la tabla
        cargarTablaSeguros(); // Asegurate de tener esta función
      } else {
        toastr.error("Error al guardar los datos del seguro");
        console.error(response);
      }
    },
    error: function (xhr) {
      toastr.error("Error de conexión al servidor");
      console.error(xhr.responseText);
    },
  });
});
