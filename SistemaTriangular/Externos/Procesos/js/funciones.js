var filtro = "";

$("#filtro_activos").click(function () {
  filtro = "activo";
  actualizarTabla();
});
$("#filtro_inactivos").click(function () {
  filtro = "inactivo";
  actualizarTabla();
});
$("#filtro_todos").click(function () {
  filtro = "todos";
  actualizarTabla();
});

// Función para actualizar la tabla con el filtro actualizado
function actualizarTabla() {
  var datatable_externos = $("#externos").DataTable();
  datatable_externos.ajax.reload(null, false);
}

$("#add-new-modal_cancel").click(function () {
  $("#new_externo")[0].reset();
  $("#form_multiple-two")[0].reset();
});

$("#form_multiple-two_cancel").click(function () {
  $("#new_externo")[0].reset();
  $("#form_multiple-two")[0].reset();
});

$(document).on("click", "#imprimir", function () {
  // Setear los valores
  const servicios = $("#total_servicios").text().split("<")[0].trim();
  const tarifas = $("#total_servicios").html().split("<br>")[1] || "";
  const subtotal = $("#subtotal_precio").text();
  const iva = $("#iva_precio").text();
  const total = $("#total_final").text().replace("Total Rendición: ", "");
  const observaciones = $("#observaciones_rendicion").val().trim();
  // const contenido = document.querySelector(
  //   "#full-width-modal .modal-body"
  // ).innerHTML;
  const nroOrden = $("#report_id").text().trim();
  const contenido = document.querySelector(
    "#full-width-modal .modal-body",
  ).innerHTML;
  const titulo = `Liquidación Orden #${nroOrden}`;

  const printWindow = window.open("", "_blank", "width=800,height=600");

  printWindow.document.write(`
    <html>
      <head>
        <title>${titulo}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
          body { padding: 20px; font-size: 12px; }
          @media print {
            .text-muted { color: #555 !important; }
            .resumen-imprimir { font-size: 14px; color: #000; page-break-inside: avoid; }
            .resumen-imprimir strong { font-weight: bold; }
            .dataTables_filter { display: none !important; }
          }
        </style>
      </head>
      <body>
      <h3 class="text-center mb-4">${titulo}</h3>
        ${contenido}
        <hr>
      </body>
    </html>
  `);

  printWindow.document.close();

  // Aseguramos que imprima solo cuando el contenido haya cargado
  printWindow.onload = function () {
    printWindow.focus();
    printWindow.print();

    printWindow.onafterprint = function () {
      printWindow.close(); // Cerramos automáticamente después de imprimir
    };
  };
});
//MUESTRO LA TABLA
var datatable = $("#externos").DataTable({
  scrollX: true,
  autoWidth: false,
  responsive: false,
  dom: "Bfrtip",
  buttons: ["pageLength", "copy", "excel", "pdf"],
  paging: true,
  searching: true,
  lengthMenu: [
    [10, 25, 50, -1],
    [10, 25, 50, "All"],
  ],
  ajax: {
    url: "Procesos/php/funciones.php",
    data: function (d) {
      d.Envios = 1;
      d.Filtro = filtro; // Aquí se incluye el valor de filtro en la solicitud AJAX
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
        return (
          `<td class="table-action"><a id="${row.id}" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-account-edit text-success"></i></a>` +
          `<td class="table-action"><a id="${row.NombreCompleto}" onclick="desempeno(this.id,${row.Usuario});" class="action-icon"> <i class="mdi mdi-text-box-search-outline text-success"></i></a>`
        );
      },
    },
  ],
});

//DESEMPEÑO
function desempeno(a, b) {
  $("#desempeno_modal").modal("show");
  $("#desempeno_header").html("Listado de Ordenes de " + a);
  $("#id_desempeno").val(b);
  $("#name_desempeno").val(a);
}

//BOTON PARA ABRIR EL MODAL DE AGREGAR EXTERNOS
$("#button_agregar_externo").click(function () {
  $("#NewTaskModalLabel").html("Agregar Nuevo Repartidor Externo");
  $("#button_continuar").css("display", "inline");
  $("#button_guardar").css("display", "none");
  $("#alerta").css("display", "none");
});

function modificar(a) {
  $.ajax({
    data: {
      VerExterno: 1,
      id: a,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      $("#NewTaskModalLabel").html("Modificar Datos Repartidor Externo");
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
      ModificarExterno: 1,
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
    url: "Procesos/php/funciones.php",
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
          "success",
        );
        var datatable = $("#externos").DataTable();
        datatable.ajax.reload();
      }
    },
  });
});

$("#crear_externo").click(function () {
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

  //DATOS VEHICULO
  var marca = $("#ext_marca").val();
  var modelo = $("#ext_modelo").val();
  var dominio = $("#ext_dominio").val();
  var motor = $("#ext_motor").val();
  var chasis = $("#ext_chasis").val();
  var ano = $("#ext_ano").val();
  var color = $("#ext_color").val();
  var seguro = $("#ext_seguro").val();
  var seguro_vencimiento = $("#ext_seguro_vencimiento").val();
  var poliza = $("#ext_poliza").val();
  var km = $("#ext_km").val();
  var volumen = $("#ext_volumen").val();
  var peso = $("#ext_peso").val();
  var vehiculo_obs = $("#vehiculo_obs").val();
  var itv_oblea = $("#ext_itv_oblea").val();
  var itv_vencimiento = $("#ext_itv_vencimiento").val();

  $.ajax({
    data: {
      Agregar_externo: 1,
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
      marca: marca,
      modelo: modelo,
      dominio: dominio,
      motor: motor,
      chasis: chasis,
      ano: ano,
      color: color,
      seguro: seguro,
      seguro_vencimiento: seguro_vencimiento,
      poliza: poliza,
      km: km,
      volumen: volumen,
      peso: peso,
      vehiculo_obs: vehiculo_obs,
      itv_oblea: itv_oblea,
      itv_vencimiento: itv_vencimiento,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    beforeSend: function () {},
    success: function (respuesta) {
      var jsonData = JSON.parse(respuesta);
      if (jsonData.vehiculo == 1) {
        var notificacion = "Externo y Vehiculo Cargado al sistema";
      } else {
        var notificacion = "Externo Cargado al sistema";
      }

      if (jsonData.success == 1) {
        $.NotificationApp.send(
          "Exito !",
          notificacion,
          "bottom-right",
          "#FFFFFF",
          "success",
        );
      } else {
        $.NotificationApp.send(
          "Error !",
          "Externo No Cargado al Sistema",
          "bottom-right",
          "#FFFFFF",
          "danger",
        );
      }

      $("#multiple-two").modal("hide");
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
          $("#multiple-two").modal("show");
          $("#add-new-modal").modal("hide");
        }

        form.classList.add("was-validated");
      },
      false,
    );
  });
})();

$("#desempeno_button").click(function () {
  $("#desempeno_tabla").css("display", "table");
  var datatable = $("#desempeno_tabla").DataTable();
  datatable.destroy();

  var desde = $("#desempeno_desde").val();
  var hasta = $("#desempeno_hasta").val();
  var desde_ = desde.split("-");
  var Desde = desde_[2] + "-" + desde_[1] + "-" + desde_[0];
  var hasta_ = hasta.split("-");
  var Hasta = hasta_[2] + "-" + hasta_[1] + "-" + hasta_[0];
  var id = $("#id_desempeno").val();

  var datatable = $("#desempeno_tabla").DataTable({
    paging: true,
    searching: true,
    ajax: {
      url: "Procesos/php/funciones.php",
      data: { Desempeno: 1, Desde: Desde, Hasta: Hasta, id: id },
      processing: true,
      type: "post",
    },
    initComplete: function (settings, json) {
      toggleBloqueFactura(json && json.data ? json.data : []);
    },
    drawCallback: function () {
      // Por si cambian filas visibles
      const filas = $("#desempeno_tabla")
        .DataTable()
        .rows({ filter: "applied" })
        .data()
        .toArray();
      toggleBloqueFactura(filas);
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return `<td><b> ${Fecha}</b></br></td>`;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            `<td><b>Recorrido N ${row.Recorrido}</b></br>` +
            `<small class="text-muted">${row.Nombre}</small></td>`
          );
        },
      },
      { data: "NumerodeOrden" },
      { data: "Total" },
      {
        data: null,
        render: function (data, type, row) {
          if (row.Rendicion == 0) {
            if (row.Costo_rendicion != 0) {
              return `<td><span class="badge badge-success">Controlado</span></td>`;
            } else {
              return `<td><span class="badge badge-danger">No Controlado</span></td>`;
            }
          } else {
            return `<td><span class="badge badge-primary">Facturado</span></td>`;
          }
        },
      },
      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          if (row.Costo_rendicion != 0) {
            var reporte_controlado = 1;
          } else {
            var reporte_controlado = 0;
          }
          return `<td class="table-action"><a id="${row.NumerodeOrden}" onclick="report(this.id,${id},'${row.Fecha}',${row.Recorrido},${reporte_controlado});" class="action-icon"> <i class="mdi mdi-text-box-search-outline text-success"></i></a>`;
        },
      },
      {
        data: "Rendicion",
        render: function (data, type, row) {
          const noRendido = row.Rendicion == 0; // comparación flexible
          const Controlado = row.Costo_rendicion != 0;
          const cobranza =
            row.CobrarEnvio !== undefined && row.CobrarEnvio !== null
              ? row.CobrarEnvio
              : 0;
          return noRendido && Controlado
            ? `<input type="checkbox" class="checkbox_factura" data-orden="${row.NumerodeOrden}" data-total="${row.Costo_rendicion}" data-cobranza="${cobranza}">`
            : "";
        },
      },
    ],
  });
  // helper
  function toggleBloqueFactura(registros) {
    // Hay algo facturable si existe al menos una fila con Rendicion == 0 y Costo_rendicion != 0
    const hayFacturable = registros.some(
      (r) => parseInt(r.Rendicion) === 0 && _num(r.Costo_rendicion) > 0,
    );

    if (hayFacturable) {
      $("#bloque_factura").removeClass("d-none");
      // Solo mostrar el formulario cuando haya check tildado (tu change handler ya lo hace)
      $("#formulario_factura").addClass("d-none");
      $("#importe_factura").val("");
    } else {
      $("#bloque_factura").addClass("d-none");
      $("#formulario_factura").addClass("d-none");
      $("#importe_factura").val("");
      // además destildamos cualquier resto
      $(".checkbox_factura").prop("checked", false);
    }
  }
});

//TOTALES
function actualizarTotales() {
  const tabla = $("#reporte_tabla").DataTable();
  const data = tabla.rows().data().toArray();

  let totalServicios = data.length;
  let totalConIVA = 0;
  let totalCobranza = 0;

  const tarifasContadas = {}; // {1: 3, 2: 5, ...}

  data.forEach((row) => {
    const id = parseInt(row.TarifaID);
    const precio = parseFloat(row.Precio || 0);
    const cobrar = parseFloat(row.CobrarEnvio || 0);
    totalCobranza += cobrar;

    totalConIVA += precio;

    if (!tarifasContadas[id]) {
      tarifasContadas[id] = 1;
    } else {
      tarifasContadas[id]++;
    }
  });

  const subtotal = totalConIVA + totalCobranza;
  // const iva = totalConIVA + totalCobranza - subtotal;

  const formatoARS = new Intl.NumberFormat("es-AR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  // Construir HTML dinámico para desglose de tarifas
  let htmlTarifas = "";
  Object.keys(tarifasContadas)
    .sort((a, b) => a - b)
    .forEach((id) => {
      const nombre =
        data.find((r) => parseInt(r.TarifaID) === parseInt(id))?.NombreTarifa ||
        `Tarifa ${id}`;
      htmlTarifas += `<small class="text-muted d-block">${nombre}: ${tarifasContadas[id]}</small>`;
    });

  $("#total_servicios").html(`${totalServicios}<br>${htmlTarifas}`);
  $("#total_cobranza").html(`$ ${formatoARS.format(totalCobranza)}`);
  $("#total_servicios_pesos").html(`${formatoARS.format(totalConIVA)}`);
  $("#subtotal_precio").text(`$ ${formatoARS.format(subtotal)}`);
  // $("#iva_precio").text(`$ ${formatoARS.format(iva)}`);
  $("#total_final").text(
    `Total Rendición: $ ${formatoARS.format(totalConIVA + totalCobranza)}`,
  );
}
//FUNCION CUADRO RESUMEN

function construirResumenPorFecha() {
  const $thead = $("#tabla_resumen_fecha thead");
  $thead.empty(); // para asegurarse

  const tabla = $("#reporte_tabla").DataTable();
  const data = tabla.rows().data().toArray();

  const resumen = {}; // { fecha: {TarifaID: cantidad, ...} }
  const tarifasUsadas = new Set();
  const nombresTarifas = {};

  data.forEach((row) => {
    const tid = parseInt(row.TarifaID);
    if (!nombresTarifas[tid]) nombresTarifas[tid] = row.NombreTarifa;
  });

  data.forEach((row) => {
    const fecha = row.Fecha;
    const tarifa = parseInt(row.TarifaID);

    tarifasUsadas.add(tarifa);
    if (!resumen[fecha]) resumen[fecha] = {};
    if (!resumen[fecha][tarifa]) resumen[fecha][tarifa] = 1;
    else resumen[fecha][tarifa]++;
  });

  const columnasTarifa = Array.from(tarifasUsadas).sort((a, b) => a - b);

  // Construcción de encabezado
  $thead.html(
    "<tr><th>Fecha</th>" +
      columnasTarifa
        .map((t) => `<th>${nombresTarifas[t] || "Tarifa " + t}</th>`)
        .join("") +
      "</tr>",
  );
  // Cuerpo de tabla
  const $tbody = $("#tabla_resumen_fecha tbody");
  $tbody.empty();

  Object.keys(resumen)
    .sort()
    .forEach((fecha) => {
      const fila = resumen[fecha];
      let htmlFila = `<tr><td><b>${fecha
        .split("-")
        .reverse()
        .join("/")}</b></td>`;
      columnasTarifa.forEach((id) => {
        htmlFila += `<td>${fila[id] || 0}</td>`;
      });
      htmlFila += "</tr>";
      $tbody.append(htmlFila);
    });
}

function marcarDuplicados() {
  const codigos = {};
  $(".codigo-seguimiento").each(function (i, el) {
    const codigo = $(el).data("codigo");
    if (!codigos[codigo]) {
      codigos[codigo] = [];
    }
    codigos[codigo].push(el);
  });

  Object.values(codigos).forEach((elementos) => {
    if (elementos.length > 1) {
      elementos.forEach((el) => {
        $(el).append(" <span class='text-warning'>⚠️</span>");
      });
    }
  });
}
function formatearFechaDMY(fechaStr) {
  if (!fechaStr || typeof fechaStr !== "string") return "";
  const partes = fechaStr.split("-");
  if (partes.length !== 3) return fechaStr;
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}
function report(a, b, c, d, f) {
  const fechaFormateada = formatearFechaDMY(c);
  $("#report_fechaS").html(fechaFormateada);

  if (f === 1) {
    $("report_status")
      .html("Controlado")
      .removeClass("badge-danger")
      .addClass("badge-success");
  } else {
    $("report_status")
      .html("No Controlado")
      .removeClass("badge-success")
      .addClass("badge-danger");
  }

  $("#full-width-modal").modal("show");
  $("#reporte_header").html($("#desempeno_header").html());
  $("#report_name").html(
    $("#name_desempeno")
      .val()
      .replace(/(?:^|\s)\S/g, function (a) {
        return a.toUpperCase();
      }),
  );
  $("#report_id").html(a);
  $("#report_fecha").html(c);
  $("#report_recorrido").html(d);

  var datatable = $("#reporte_tabla").DataTable();
  datatable.destroy();

  var datatable = $("#reporte_tabla").DataTable({
    paging: false,
    // pageLength: 17,
    searching: true,
    ajax: {
      url: "Procesos/php/funciones.php",
      data: { Reporte: 1, NOrden: a, id: b, controlado: f },
      processing: true,
      type: "post",
    },
    drawCallback: function () {
      marcarDuplicados(); // <-- se asegura que se ejecute cada vez que se dibuje
    },
    initComplete: function (settings, json) {
      actualizarTotales();
      construirResumenPorFecha();

      const registros = json.data || [];
      const hayPendientes = registros.some((row) => parseInt(row.idPago) === 0);

      if (!hayPendientes) {
        $("#generar_liquidacion").hide(); // Oculta el botón
        $(".editar-tarifa").hide(); // Oculta los íconos lápiz
      } else {
        $("#generar_liquidacion").show();
      }
    },
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return `<td><b> ${Fecha}</b></br></td>`;
        },
      },
      { data: "RazonSocial" },
      {
        data: "ClienteDestino",
        render: function (data, type, row) {
          return `<td><b> ${row.ClienteDestino}</b></br>${row.DomicilioDestino} ${row.LocalidadDestino}</td></td>`;
        },
      },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row, meta) {
          const codigo = (data || "").toUpperCase().replace(/\s+/g, "").trim();
          return `<span class="codigo-seguimiento" data-codigo="${codigo}">${codigo}</span>`;
        },
      },
      {
        data: "Estado",
        render: function (data, type, row) {
          if (row.Estado == "Entregado al Cliente") {
            var badge = `<span class="badge badge-success text-white">Entregado al Cliente</span>`;
          } else if (row.Estado == "No se pudo entregar") {
            badge = `<span class="badge badge-danger text-white">No se Pudo entregar</span>`;
          } else {
            badge = `<span class="badge badge-warning text-white">${row.Estado}</span>`;
          }

          if (row.idPago == 0) {
            return (
              badge +
              `<br>` +
              `<span class="badge badge-outline-danger">No Liquidada</span>`
            );
          } else {
            return (
              badge +
              `<br>` +
              `<span class="badge badge-outline-success">Liquidada</span>`
            );
          }
        },
      },
      {
        data: "infoABM",
        render: function (data, type, row) {
          let badge = "";
          switch (parseInt(row.TarifaID)) {
            case 1:
              badge = `<span class="badge bg-success text-white">${row.NombreTarifa}</span>`;
              break;
            case 2:
              badge = `<span class="badge bg-warning text-white">${row.NombreTarifa}</span>`;
              break;
            case 3:
              badge = `<span class="badge bg-primary text-white">${row.NombreTarifa}</span>`;
              break;
            case 4:
              badge = `<span class="badge bg-danger text-white">${row.NombreTarifa}</span>`;
              break;
            default:
              badge = `<span class="badge bg-secondary text-white">Tarifa ${row.NombreTarifa}</span>`;
          }

          return `${badge}<br><small>${parseFloat(row.Kilometros).toFixed(
            2,
          )} km</small><br>${row.infoABM}`;
        },
      },
      {
        data: "Precio",
        className: "editable-precio text-center",
        render: function (data, type, row) {
          // aseguramos que 'data' sea numérico
          let precio = isNaN(parseFloat(data)) ? 0 : parseFloat(data);
          let obs = row.ObservacionManual ? row.ObservacionManual : "";

          // aseguramos que 'CobrarEnvio' sea numérico
          let cobrar = isNaN(parseFloat(row.CobrarEnvio))
            ? 0
            : parseFloat(row.CobrarEnvio);

          return `
    <span>$${precio.toFixed(2)}</span>
    ${
      cobrar > 0
        ? `<div class="text-primary"><small>+ Cobranza: $${cobrar.toFixed(
            2,
          )}</small></div>`
        : ""
    }
    ${obs ? `<br><small class="text-muted">${obs}</small>` : ""}
  `;
        },
      },
      {
        data: null,
        className: "text-center accion-editar",
        render: function (data, type, row) {
          if (row.idPago == 0) {
            return `
            <i class="mdi mdi-pencil editar-tarifa me-2" 
              data-id="${row.CodigoSeguimiento}" 
              style="cursor:pointer; color:goldenrod; font-size:18px;" 
              title="Editar tarifa"></i>


            <i class="mdi mdi-cash-remove no-pagar-tarifa" 
              data-id="${row.CodigoSeguimiento}" 
              style="cursor:pointer; color:crimson; font-size:18px;" 
              title="Marcar como NO PAGAR"></i>
            
            <i class="mdi mdi-trash-can eliminar-renglon me-2" 
              data-id="${row.CodigoSeguimiento}" 
              style="cursor:pointer; color:red; font-size:18px;" 
              title="Eliminar de esta rendición"></i>

          `;
          } else {
            return ""; // No mostrar lápiz si ya fue liquidado
          }
        },
      },
    ],
  });
}
//BOTON NO PAGAR
$("#reporte_tabla tbody").on("click", ".no-pagar-tarifa", function () {
  const tabla = $("#reporte_tabla").DataTable();
  const fila = tabla.row($(this).parents("tr"));
  const row = fila.data();

  const usuarioControl = $("#desempeno_header").text().trim(); // o cualquier otro lugar

  row.Precio = 0;
  row.ObservacionManual = `SIN TARIFA - ${usuarioControl}`;
  row.ModificadoManualmente = true;

  fila.node().classList.add("table-warning");
  fila.data(row).invalidate();

  actualizarTotales();
});

//ELEIMINAR PROVISORIAMENT SOLO EN EL FRONT
$("#reporte_tabla tbody").on("click", ".eliminar-renglon", function () {
  const tabla = $("#reporte_tabla").DataTable();
  const fila = tabla.row($(this).parents("tr"));
  const row = fila.data();

  Swal.fire({
    title: "¿Eliminar este envío?",
    html: `
    <b>Código:</b> ${row.CodigoSeguimiento}<br>
    <small class="text-muted">Este envío será excluido de la rendición actual pero no se eliminará de la base de datos.</small>
  `,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      fila.remove().draw(); // elimina visualmente la fila
      actualizarTotales(); // recalcula totales abajo
      marcarDuplicados();
      construirResumenPorFecha();
    }
  });
});

$("#reporte_tabla tbody").on(
  "click",
  ".editar-tarifa, .guardar-tarifa",
  function () {
    const tabla = $("#reporte_tabla").DataTable();
    const fila = tabla.row($(this).parents("tr"));
    const row = fila.data();
    const $tdPrecio = $(this).closest("tr").find("td.editable-precio");
    const $tdAccion = $(this).closest("td");

    if ($(this).hasClass("editar-tarifa")) {
      if (row.idPago != 0) return; // no editar si ya fue liquidado

      const obsActual = row.ObservacionManual || "";

      $tdPrecio.html(`
      <input type="number" class="form-control form-control-sm input-precio" 
             value="${parseFloat(row.Precio).toFixed(2)}" 
             step="0.01" 
             style="width: 90px; font-size: 11px;" />
      <textarea class="form-control form-control-sm mt-1 input-observacion"
                data-codigo="${row.CodigoSeguimiento}"
                rows="1"
                style="font-size:11px;"
                placeholder="Observaciones...">${obsActual}</textarea>
    `);

      $tdAccion.html(`
      <i class="mdi mdi-content-save guardar-tarifa" 
         style="cursor:pointer; color:green; font-size:18px;"></i>
    `);
    } else if ($(this).hasClass("guardar-tarifa")) {
      const nuevoPrecio = parseFloat(
        $(this).closest("tr").find(".input-precio").val(),
      );
      const nuevaObs = $(this)
        .closest("tr")
        .find(".input-observacion")
        .val()
        .trim();

      if (!isNaN(nuevoPrecio)) {
        let precioOriginal = parseFloat(row.Precio).toFixed(2);
        let precioNuevo = parseFloat(nuevoPrecio).toFixed(2);
        let obsOriginal = (row.ObservacionManual || "").trim();

        const hayCambio =
          precioOriginal !== precioNuevo || obsOriginal !== nuevaObs;

        if (hayCambio) {
          row.Precio = parseFloat(precioNuevo);
          row.ObservacionManual = nuevaObs;
          row.ModificadoManualmente = true;
          fila.node().classList.add("table-warning");
        } else {
          fila.node().classList.remove("table-warning");
        }

        fila.data(row).invalidate(); // actualiza la fila
        let cobrarHTML = "";
        if (parseFloat(row.CobrarEnvio || 0) > 0) {
          cobrarHTML = `<div class="text-primary"><small>+ Cobranza: $${parseFloat(
            row.CobrarEnvio,
          ).toFixed(2)}</small></div>`;
        }

        $tdPrecio.html(`
        <span>$${precioNuevo}</span>
        ${cobrarHTML}
        ${nuevaObs ? `<br><small class="text-muted">${nuevaObs}</small>` : ""}
      `);

        $tdAccion.html(`
        <i class="mdi mdi-pencil editar-tarifa" 
           data-id="${row.CodigoSeguimiento}" 
           style="cursor:pointer; color:goldenrod; font-size:18px;"></i>
      `);

        actualizarTotales();
      }
    }
  },
);

$("#generar_liquidacion").click(function () {
  var tabla = $("#reporte_tabla").DataTable();
  var datos = tabla.rows().data().toArray();
  var nrendicion = $("#report_id").html();
  var id_externo = $("#id_desempeno").val();
  var observaciones = $("#observaciones_rendicion").val().trim();
  var datosFinales = datos.map(function (row) {
    var input = $(`input[data-codigo='${row.CodigoSeguimiento}']`);

    if (input.length > 0) {
      row.Precio = parseFloat(input.val());
      row.ModificadoManualmente = true;
    }

    // ObservacionManual ya está en row porque se guardó en el click del ícono
    row.ObservacionManual = row.ObservacionManual || "";

    return row;
  });

  console.log(datosFinales);

  // Calculamos totales
  var totalServicios = datosFinales.length;
  var totalImporte = datosFinales.reduce(
    (acum, row) =>
      acum + (parseFloat(row.Precio || 0) + parseFloat(row.CobrarEnvio || 0)),
    0,
  );

  const formatoARS = new Intl.NumberFormat("es-AR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  // Confirmación visual
  Swal.fire({
    title: "¿Confirmar Rendición?",
    html: `
      <b>Se rendirán ${totalServicios} servicios</b><br>
      <b>Total:</b> $ ${formatoARS.format(totalImporte)}
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Confirmar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "Procesos/php/funciones.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          Rendicion: 1,
          rendicion: datosFinales,
          nrendicion: nrendicion,
          id_externo: id_externo,
          Observaciones: observaciones,
          Total_rendicion: totalImporte,
        }),
        success: function (r) {
          Swal.fire("Rendición generada", "", "success");
          $("#full-width-modal").modal("hide");
          var datatable = $("#desempeno_tabla").DataTable();
          datatable.ajax.reload();
        },
        error: function () {
          Swal.fire("Error", "No se pudo guardar la rendición", "error");
        },
      });
    }
  });
});

// Cargar tipos de comprobante
function cargarTiposComprobante() {
  $.post(
    "Procesos/php/funciones.php",
    { TiposComprobante: 1 },
    function (respuesta) {
      const tipos = JSON.parse(respuesta);
      const $select = $("#tipo_comprobante");
      $select.empty().append('<option value="">Seleccione...</option>');
      tipos.forEach((t) => {
        $select.append(`<option value="${t.Codigo}">${t.Descripcion}</option>`);
      });
    },
  );
}

function _num(v) {
  if (v === null || v === undefined) return 0;
  // Convert to string and remove currency/symbols except digits, dot, comma, minus
  let s = String(v)
    .trim()
    .replace(/[^0-9.,-]/g, "");
  if (s === "") return 0;

  const hasComma = s.indexOf(",") !== -1;
  const hasDot = s.indexOf(".") !== -1;

  // Decide decimal separator: if both exist, the rightmost of them is the decimal
  let decimalSep = null;
  if (hasComma && hasDot) {
    decimalSep = s.lastIndexOf(",") > s.lastIndexOf(".") ? "," : ".";
  } else if (hasComma) {
    decimalSep = ",";
  } else if (hasDot) {
    decimalSep = ".";
  }

  if (decimalSep) {
    // Remove thousands (the other separator), normalize decimal to '.'
    const thousands = decimalSep === "," ? /\./g : /,/g;
    s = s.replace(thousands, "");
    s = s.replace(decimalSep, ".");
  }

  // If only digits (posible centavos), keep heuristic: values >= 10000 are likely cents
  if (/^[0-9]+$/.test(s)) {
    let nInt = parseInt(s, 10);
    if (!isNaN(nInt) && nInt >= 10000) {
      return nInt / 100; // centavos -> pesos
    }
    return nInt;
  }

  const n = parseFloat(s);
  return isNaN(n) ? 0 : n;
}

$(document).on("change", ".checkbox_factura", function () {
  let total = 0;

  // Si los checkboxes están en la tabla de desempeño (listado de órdenes),
  // usamos el total por orden que viene en data-total (Costo_rendicion) y data-cobranza
  const $closestTable = $(this).closest("table");
  const tableId = $closestTable.attr("id");

  if (tableId === "desempeno_tabla") {
    $(".checkbox_factura:checked").each(function () {
      const t = _num($(this).data("total"));
      const c = _num($(this).data("cobranza"));
      total += t + c;
    });
  } else if (tableId === "reporte_tabla") {
    // Si los checkboxes estuvieran dentro del detalle (reporte_tabla),
    // sumamos Precio + CobrarEnvio de cada fila seleccionada
    const tabla = $("#reporte_tabla").DataTable();
    $(".checkbox_factura:checked").each(function () {
      const tr = $(this).closest("tr");
      const row = tabla.row(tr).data() || {};
      total += _num(row.Precio) + _num(row.CobrarEnvio);
    });
  } else {
    // Fallback: si no podemos determinar, sumamos por data-total
    $(".checkbox_factura:checked").each(function () {
      total += _num($(this).data("total"));
    });
  }

  if ($(".checkbox_factura:checked").length > 0) {
    $("#formulario_factura").removeClass("d-none");
    $("#importe_factura").val(total.toFixed(2));
  } else {
    $("#formulario_factura").addClass("d-none");
    $("#importe_factura").val("");
  }

  const seleccionados = $(".checkbox_factura:checked").length;
  if (seleccionados > 0) {
    $("#formulario_factura").removeClass("d-none");
    $("#importe_factura").val(total.toFixed(2));
  } else {
    $("#formulario_factura").addClass("d-none");
    $("#importe_factura").val("");
  }
});

// Al abrir el modal de desempeño, de entrada oculto el bloque hasta que cargue la tabla
$("#desempeno_modal").on("show.bs.modal", function () {
  $("#bloque_factura").addClass("d-none");
  $("#formulario_factura").addClass("d-none");
  $("#importe_factura").val("");
});

// Llamamos a cargar tipos al mostrar el modal
$("#desempeno_modal").on("shown.bs.modal", function () {
  cargarTiposComprobante();
});

$("#confirmar_factura").click(function () {
  const ordenes = []; // acumulamos los checkboxes
  $(".checkbox_factura:checked").each(function () {
    ordenes.push($(this).data("orden"));
  });
  const numeroControl = $("#numero_control").val().padStart(4, "0");
  const numeroFactura = $("#numero_factura").val().padStart(12, "0");
  const tipoComprobante = $("#tipo_comprobante").val();
  const importe = parseFloat($("#importe_factura").val());
  const fecha = $("#fecha_factura").val(); // formato yyyy-mm-dd

  if (!fecha) {
    Swal.fire("Falta la fecha", "Ingresá la fecha de la factura", "error");
    return;
  }
  if (ordenes.length === 0) {
    Swal.fire("Atención", "Seleccioná al menos una orden", "warning");
    return;
  }

  if (!numeroFactura || !numeroControl || !tipoComprobante) {
    Swal.fire("Faltan datos", "Completá todos los campos", "error");
    return;
  }

  // Enviar los datos por AJAX
  $.ajax({
    url: "Procesos/php/funciones.php",
    type: "POST",
    data: {
      ConfirmarFactura: 1,
      Ordenes: ordenes,
      NumeroControl: numeroControl,
      NumeroFactura: numeroFactura,
      TipoComprobante: tipoComprobante,
      Importe: importe,
      FechaControl: fecha,
    },
    success: function (resp) {
      Swal.fire("Éxito", "Factura registrada correctamente", "success");
      // Limpiar y cerrar si querés
      $("#bloque_factura").addClass("d-none");
      $(".checkbox_factura").prop("checked", false);
    },
    error: function () {
      Swal.fire("Error", "No se pudo registrar la factura", "error");
    },
  });
});
