// Normalizar formato del número
function normalizarFormato(numero) {
  // Eliminar guiones existentes y espacios
  numero = numero.replace(/[-\s]/g, "");

  // Asegurarse de que el número tiene al menos 9 dígitos
  while (numero.length < 9) {
    numero = "0" + numero;
  }

  // Agregar guiones en las posiciones adecuadas
  return numero.replace(/(\d{2})(\d{8})(\d{1})/, "$1-$2-$3");
}

// Formatear como moneda
function currencyFormat(num) {
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

function ver(id, name) {
  var desde = $("#fecha_desde").val();
  var hasta = $("#fecha_hasta").val();

  $("#myLargeModalLabel").html("Control de Facturación " + name);
  $("#modal-comprobacion").modal("show");
  var datatable = $("#facturacion_comprobacion").DataTable();
  datatable.destroy();

  datatable = $("#facturacion_comprobacion").DataTable({
    pageLength: 50,
    paging: true,
    searching: true,
    ajax: {
      url: "Procesos/php/facturacion.php",
      data: { Facturacion_comprueba: 1, id: id, Desde: desde, Hasta: hasta },
      type: "post",
      dataSrc: function (json) {
        // Aquí obtienes el dato 'Colecta'
        var colecta = json.Colecta;
        console.log("Colecta:", colecta);
        // Mostrar colecta en el DOM según sea necesario
        if (colecta == 0) {
          colecta = '<span class="ml-3 badge badge-danger">Sin Colecta</span>';
        } else {
          colecta = '<span class="ml-3 badge badge-success">Con Colecta</span>';
        }
        $("#colectaDisplay").html(colecta);

        // Procesar los NoEntregados y asignarlos correctamente
        var noEntregadosMap = {};
        for (var i = 0; i < json.NoEntregados.length; i++) {
          var item = json.NoEntregados[i];
          if (!noEntregadosMap[item.Fecha]) {
            noEntregadosMap[item.Fecha] = [];
          }
          if (
            noEntregadosMap[item.Fecha].indexOf(item.CodigoSeguimiento) === -1
          ) {
            noEntregadosMap[item.Fecha].push(item.CodigoSeguimiento);
          }
        }

        // Asignar NoEntregados a cada fila de data basado en Fecha
        for (var j = 0; j < json.data.length; j++) {
          var row = json.data[j];
          var uniqueNoEntregados = [];
          if (noEntregadosMap[row.Fecha]) {
            uniqueNoEntregados = noEntregadosMap[row.Fecha];
          }
          row.NoEntregados = uniqueNoEntregados; // Asignar el array de códigos únicos o un array vacío si no hay datos
        }

        // Retornar los datos para la DataTable
        return json.data;
      },
      error: function (xhr, error, thrown) {
        console.error("Error al obtener datos:", error);
        alert("Hubo un problema al cargar los datos de facturación");
      },
    },
    columns: [
      {
        data: null,
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return `<td>${Fecha}</td>`;
        },
      },
      { data: "Cantidad" },
      { data: "CantidadFlex" },
      { data: "CantidadNoFlexWepoint" },
      {
        data: "Debe",
        render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
      },
      {
        data: "Condicion",
        render: function (data, type, row, meta) {
          if (row.Condicion === "true") {
            return '<i class="mdi mdi-18px mdi-checkbox-marked-circle text-success"></i>';
          } else {
            return '<i class="mdi mdi-18px mdi-close-circle text-danger"></i>';
          }
        },
      },
      {
        data: "NoEntregados",
        render: function (data, type, row) {
          if (data && data.length > 0) {
            return data.join(", "); // Personalizar la renderización de 'NoEntregados' según sea necesario
          } else {
            return ""; // Manejar el caso donde NoEntregados es un array vacío o undefined
          }
        },
      },
    ],
  });

  // Botón para copiar códigos "No Entregados"
  $("#copyNoEntregados").on("click", function () {
    var data = datatable.rows().data(); // Obtener todos los datos de las filas
    var noEntregados = [];

    // Recorrer los datos y obtener los códigos "NoEntregados"
    data.each(function (row) {
      var noEntregadosFila = row.NoEntregados.map(function (codigo) {
        return "[" + row.idCliente + "] " + codigo;
      });
      noEntregados = noEntregados.concat(noEntregadosFila);
    });

    // Convertir el array en una cadena separada por saltos de línea
    var textToCopy = noEntregados.join("\n");

    // Copiar al portapapeles usando navigator.clipboard
    navigator.clipboard
      .writeText(textToCopy)
      .then(function () {
        alert('Códigos "No Entregados" copiados al portapapeles');
      })
      .catch(function (err) {
        console.error("No se pudo copiar al portapapeles: ", err);
        alert("Hubo un problema al copiar al portapapeles");
      });
  });
}

$(document).ready(function () {
  var datatable1 = $("#facturacion").DataTable();
  datatable1.destroy();

  function ver_tabla(desde, hasta) {
    $("#row-facturacion").css("display", "block");
    $("#facturacion").DataTable({
      dom: "Bfrtip",
      pageLength: 50,
      buttons: [
        "pageLength",
        "copy",
        "csv",
        "excel",
        {
          extend: "pdf",
          text: "PDF",
          orientation: "landscape",
          title: "Control Ventas Triangular S.A.",
          filename: "ControlVentas",
          header: true,
          pageSize: "A4",
        },
      ],
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ],
      paging: true,
      searching: true,
      footerCallback: function (row, data, start, end, display) {
        var total = this.api()
          .column(2, { page: "current" }) // Sumar solo la página actual
          .data()
          .reduce(function (a, b) {
            return Number(a) + Number(b);
          }, 0);
        var saldo = currencyFormat(total);
        $(this.api().column(2).footer()).html(saldo);
      },
      ajax: {
        url: "Procesos/php/facturacion.php",
        data: { Facturacion: 1, Desde: desde, Hasta: hasta },
        type: "post",
      },
      columns: [
        {
          data: null,
          render: function (data, type, row) {
            return "<td>[" + row.idCliente + "] <b>" + row.Pagador + "</td>";
          },
        },
        {
          data: "CicloFacturacion",
          render: function (data, type, row, meta) {
            if (row.CicloFacturacion === "Mensual") {
              return (
                `<span class="badge badge-success" style="cursor:pointer">` +
                row.CicloFacturacion +
                `</span>`
              );
            } else if (row.CicloFacturacion === "Quincenal") {
              return (
                `<span class="badge badge-warning" style="cursor:pointer">` +
                row.CicloFacturacion +
                `</span>`
              );
            }
          },
        },
        {
          data: "Debe",
          render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
        },
        {
          data: "Cantidad",
        },
        {
          data: null,
          render: function (data, type, row, meta) {
            return `<span class="badge badge-success" style="cursor:pointer" onclick="ver(${row.idCliente},'${row.Pagador}')">Comprobar</span>`;
          },
        },
      ],
    });
  }

  $("#buscar").click(function () {
    $("#buscador").css("display", "none");

    var desde = $("#fecha_desde").val();
    var hasta = $("#fecha_hasta").val();

    ver_tabla(desde, hasta);
  });
});
