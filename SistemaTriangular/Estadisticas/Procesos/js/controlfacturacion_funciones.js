$(document).ready(function () {
  function currencyFormat(num) {
    return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
  }

  $("#fac").click(function () {
    var datatable1 = $("#tablactasctes").DataTable();
    datatable1.destroy();

    veoveo(1);
  });

  $("#sfac").click(function () {
    var datatable = $("#tablactasctes").DataTable();
    datatable.destroy();

    veoveo(0);
  });

  //VER QUE ES ESTO
  // $("input[name='customRadio1']").click(function(){

  //   var title=$("input[name='customRadio1']:checked").val();
  //   if (title==1){
  //     var titlepage= "Ventas x Recorridos";
  //     var table = $('#tablarecorridos').DataTable();
  //     table.destroy();
  //     var table = $('#tabladirectos').DataTable();
  //     table.destroy();
  //       }else if(title==2){
  //      var titlepage= "Ventas Directas";
  //     var table = $('#tablarecorridos').DataTable();
  //     table.destroy();
  //     var table = $('#tabladirectos').DataTable();
  //     table.destroy();
  //       }

  //   $('#page-title').html(titlepage);
  //   $('#page-title0').html(titlepage);
  //   $('#page-titlebuscador').html('Buscador');
  // })

  $("#page-titlebuscador").click(function () {
    document.getElementById("buscador").style.display = "block";
  });

  function veoveo(f) {
    // var facturado=$("input[name='customRadio1']:checked").val();
    $("#facturado").val(f);
    var facturado = f;
    var desde = document.getElementById("fecha_desde").value;
    var hasta = document.getElementById("fecha_hasta").value;

    document.getElementById("buscador").style.display = "none";
    document.getElementById("dashboard").style.display = "flex";
    var Desde0 = desde.split("/");
    var Desde = Desde0[2] + "-" + Desde0[0] + "-" + Desde0[1];
    var Hasta0 = hasta.split("/");
    var Hasta = Hasta0[2] + "-" + Hasta0[0] + "-" + Hasta0[1];

    if (facturado == 1) {
      $("#ongoing").removeClass("badge-danger").addClass("badge-success");
      $("#ongoing").html("Facturados");
      var nombre =
        "Servicios Facturados " +
        Desde0[1] +
        "/" +
        Desde0[0] +
        "/" +
        Desde0[2] +
        " Hasta " +
        Hasta0[1] +
        "/" +
        Hasta0[0] +
        "/" +
        Hasta0[2];
    } else if (facturado == 0) {
      $("#ongoing").removeClass("badge-success").addClass("badge-danger");
      $("#ongoing").html("Sin Facturar");
      var nombre =
        "Servicios Sin Facturar " +
        Desde0[1] +
        "/" +
        Desde0[0] +
        "/" +
        Desde0[2] +
        " Hasta " +
        Hasta0[1] +
        "/" +
        Hasta0[0] +
        "/" +
        Hasta0[2];
    }
    $("#page-title").html(nombre);
    $("#page-title0").html(nombre);
    $("#page-title1").html(nombre);
    $("#titulo").html(nombre);

    document.getElementById("buscador").style.display = "none";
    document.getElementById("guias").style.display = "flex";
    var valorSelec = $('input[name="customRadio0"]:checked').val();
    var valorSeleccionado = parseInt(valorSelec, 10);

    generarTabla(f, valorSeleccionado, desde, hasta);
    generarTabla_rec(f, valorSeleccionado, desde, hasta);
  }

  //IMPUT SELECT
  $('input[name="customRadio0"]').change(function () {
    var datatable = $("#tablactasctes").DataTable();
    datatable.destroy();

    var table = $("#tabladirectos").DataTable();
    table.destroy();

    var table = $("#tablarecorridos").DataTable();
    table.destroy();

    var valorSelec = $('input[name="customRadio0"]:checked').val();
    var facturado = $("#facturado").val();

    var desde = document.getElementById("fecha_desde").value;
    var hasta = document.getElementById("fecha_hasta").value;

    // Forzar el valor como un número entero
    var valorSeleccionado = parseInt(valorSelec, 10);
    console.log("Valor seleccionado:", valorSeleccionado);

    if (
      valorSeleccionado === "" ||
      valorSeleccionado === "0" ||
      valorSeleccionado === null
    ) {
      $("#title_guias").html(
        "CONTROL FACTURACION CLIENTES TODOS LOS CICLOS DE FACTURACION"
      );
    } else if (valorSeleccionado === 1) {
      $("#title_guias").html("CONTROL FACTURACION CLIENTES CICLO DIARIO");
    } else if (valorSeleccionado === 2) {
      $("#title_guias").html("CONTROL FACTURACION CLIENTES CICLO QUINCENAL");
    } else if (valorSeleccionado === 3) {
      $("#title_guias").html("CONTROL FACTURACION CLIENTES CICLO MENSUAL");
    }

    generarTabla(facturado, valorSeleccionado, desde, hasta);
    generarTabla_rec(facturado, valorSeleccionado, desde, hasta);
  });

  $("#buscar").click(function () {
    document.getElementById("buscador").style.display = "none";
    document.getElementById("dashboard").style.display = "flex";

    var facturado = $("#facturado").val();

    var desde = document.getElementById("fecha_desde").value;
    var hasta = document.getElementById("fecha_hasta").value;

    document.getElementById("buscador").style.display = "none";
    document.getElementById("dashboard").style.display = "flex";
    var Desde0 = desde.split("/");
    var Desde = Desde0[2] + "-" + Desde0[0] + "-" + Desde0[1];
    var Hasta0 = hasta.split("/");
    var Hasta = Hasta0[2] + "-" + Hasta0[0] + "-" + Hasta0[1];

    if (facturado == 1) {
      var nombre =
        "Servicios Facturados " +
        Desde0[1] +
        "/" +
        Desde0[0] +
        "/" +
        Desde0[2] +
        " Hasta " +
        Hasta0[1] +
        "/" +
        Hasta0[0] +
        "/" +
        Hasta0[2];
      $("#page-title").html(nombre);
      $("#page-title0").html(nombre);
    } else if (facturado == 0) {
      var nombre =
        "Servicios Sin Facturar " +
        Desde0[1] +
        "/" +
        Desde0[0] +
        "/" +
        Desde0[2] +
        " Hasta " +
        Hasta0[1] +
        "/" +
        Hasta0[0] +
        "/" +
        Hasta0[2];
      $("#page-title").html(nombre);
      $("#page-title0").html(nombre);
    }

    // $('#titulo').html(nombre);

    document.getElementById("buscador").style.display = "none";
    document.getElementById("guias").style.display = "flex";

    generarTabla(0, 0, desde, hasta);
    generarTabla_rec(0, 0, desde, hasta);
  });

  //GENERA TABLA
  function generarTabla(f, ciclo, desde, hasta) {
    let cadenaFecha = desde;
    var dia = cadenaFecha.substring(0, 2);
    var mes = cadenaFecha.substring(2, 4);
    var año = cadenaFecha.substring(4);

    // Formatear la fecha como "DD.MM.AAAA"
    let desde_file_name = dia + "." + mes + "." + año;

    let cadenaHasta = hasta;
    var dia_ = cadenaHasta.substring(0, 2);
    var mes_ = cadenaHasta.substring(2, 4);
    var año_ = cadenaHasta.substring(4);

    // Formatear la fecha como "DD.MM.AAAA"
    let hasta_file_name = dia_ + "." + mes_ + "." + año_;

    var facturado = f;
    var ciclo = ciclo;
    var datatable1 = $("#tablactasctes").DataTable({
      dom: "Bfrtip",
      buttons: [
        "copy",
        "csv",
        {
          extend: "pdf",
          text: "PDF",
          orientation: "landscape",
          title: "Titulo",
          filename:
            "Ventas Desde: " + desde_file_name + " Hasta: " + hasta_file_name,
          header: true,
          pageSize: "A4",
          customize: function (doc) {
            (doc.styles.tableHeader = {
              fillColor: "#525659",
              color: "#FFF",
              fontSize: "7",
              alignment: "left",
              bold: true,
            }), //para cambiar el backgorud del escabezado
              (doc.defaultStyle.fontSize = 6);
            doc.pageMargins = [50, 50, 30, 30]; //left,top,right,bottom
            doc.content[1].margin = [5, 0, 0, 5]; // margenes para la datables
          },
        },
        {
          extend: "excel",
          text: "Excel",
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7], // Aquí indicamos las columnas que se exportarán (0 para ID y 1 para Nombre)
          },
        },
        "print",
      ],

      columnDefs: [
        {
          targets: [0], // Índice de la columna "Fecha"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [1], // Índice de la columna "RazonSocial"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [2], // Índice de la columna "ClienteDestino"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [3], // Índice de la columna "CodigoSeguimiento"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [4], // Índice de la columna "Debe"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [5], // Índice de la columna "Debe"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [6], // Índice de la columna "Debe"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
        {
          targets: [7], // Índice de la columna "Debe"
          visible: false, // Hacemos que la columna "Edad" sea invisible en la tabla
          orderable: false, // También deshabilitamos la ordenación en esta columna
          exportable: true, // Evitamos que la columna se exporte a Excel
        },
      ],

      paging: false,
      searching: true,
      //TOTALES
      footerCallback: function (row, data, start, end, display) {
        total = this.api()
          .column(14, { page: "current" }) //para sumar solo la pagina actual
          .data()
          .reduce(function (a, b) {
            return parseInt(a) + parseInt(b);
          }, 0);
        var suma = currencyFormat(total);
        $(this.api().column(14).footer()).html("Total : " + suma);
        $("#title_importe").html("Total: " + suma);
      },

      ajax: {
        url: "../Estadisticas/Procesos/php/tablas.php",
        data: {
          Totales: 3,
          desde: desde,
          hasta: hasta,
          facturado: facturado,
          ciclo: ciclo,
        },
        type: "post",
      },
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            var Fecha = row.Fecha.split("-").reverse().join("/");
            return Fecha;
          },
        },
        {
          data: null,
          render: function (data, type, row) {
            return `<td><b>${row.Pagador}</b></td>`;
          },
        },
        { data: "RazonSocial" },
        { data: "ClienteDestino" },
        { data: "CodigoSeguimiento" },
        {
          data: null,
          render: function (data, type, row) {
            if (row.Flex != 1) {
              return "Simple";
            } else {
              return "Flex";
            }
          },
        },
        { data: "Observaciones" },
        { data: "Debe" },

        { data: "Fecha" },
        {
          data: null,
          render: function (data, type, row) {
            if (row.RazonSocial != null) {
              var clientes = `${row.RazonSocial} -> ${row.ClienteDestino}`;
            } else {
              clientes = `${row.RazonSocial}`;
            }

            return `<td><b>${clientes}</b></td></br><smaill><a class='text-muted'> Pagador: [${row.idCliente}] ${row.Pagador}</a></small></td>`;
          },
        },
        {
          data: "NumeroVenta",
          render: function (data, type, row) {
            // if(row.CodigoSeguimiento!=null){
            //     var cs=`<a>${row.CodigoSeguimiento}</a></td>`;
            // }else{
            //     cs=``;
            // }

            if (row.TipoDeComprobante != null) {
              var tipdecomp = row.TipoDeComprobante;
              var cs = `<a>${row.CodigoSeguimiento}</a></td>`;
            } else {
              tipdecomp = "Rec: ";
              var cs = `<a>Rec: ${row.CodigoSeguimiento}</a></td>`;
            }

            var comp = tipdecomp + " " + row.NumeroVenta;
            return `<td><a>${comp} </a></br> ${cs}`;
          },
        },
        {
          data: "Facturado",
          render: function (data, type, row) {
            const date = new Date();
            var day1 = new Date();
            // var day2 = new Date(row.Vencimiento);
            var day2 = new Date();

            var difference = Math.trunc(day2 - day1);
            days = difference / (1000 * 3600 * 24) + 1;

            // console.log('ciclo',row.CicloFacturacion);

            if (days < 0) {
              var vencida_label = "Vencido";
              var vencida_color = "danger";
            } else {
              vencida_label = "No Vencido";
              vencida_color = "success";
            }
            if (row.Facturado == "1") {
              return `<td><a class='badge badge-success' value='Facturado'>Facturado</a>`;
            } else {
              return (
                `<td><a class='badge badge-danger' value='Sin Facturar'>Sin Facturar</a></td>` +
                `<br><a class='badge badge-${vencida_color}' value='Vencida'>${vencida_label} </a></n></td>`
              );
            }
          },
        },
        { data: "CicloFacturacion" },
        {
          data: "id",
          render: function (data, type, row) {
            const date = new Date();
            var day1 = new Date();
            var day2 = new Date(row.Fecha);

            var difference = Math.trunc(day2 - day1);

            days = difference / (1000 * 3600 * 24) + 1;

            if (Math.trunc(days) > 0) {
              var venc = `Vence en ${Math.trunc(days)} días`;
              var vencida_color = "success";
            } else if (Math.trunc(days) == 0) {
              var venc = `Vence hoy`;
              var vencida_color = "success";
            } else if (Math.trunc(days) < 0) {
              var difference2 = Math.abs(day2 - day1);
              days2 = difference2 / (1000 * 3600 * 24) + 1;
              var venc = `Venció hace ${Math.trunc(days2)} días`;
              var vencida_color = "danger";
            }

            if (row.Flex != 1) {
              var flex = `<br/><td><a class='badge badge-warning text-white'>Simple</a></td>`;
            } else {
              flex = `<br/><td><a class='badge badge-success text-white'>Flex</a></td>`;
            }

            return (
              `<td><a class='badge badge-${vencida_color} text-white'>${venc}</a></td>` +
              flex
            );
            // return `<td><a class='badge badge-${vencida_color} text-white'>${row.CicloFacturacion}</a></td>`+ flex;
          },
        },
        // {data:"Kilometros"},
        {
          data: "Debe",
          render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
        },
        {
          data: "id",
          render: function (data, type, row) {
            return (
              '<td class="table-action">' +
              '<a data-id="' +
              row.id +
              '" id="' +
              row.id +
              '" onclick="ver(this.id);" class="action-icon" style="cursor:pointer"> <i class="mdi mdi-18px mdi-trash-can-outline"></i></a>' +
              "</td>"
            );
          },
        },
      ],
    });

    //BUSCO TOTALES PARA DASHBOARD
    var dato = { Totales: 1, desde: desde, hasta: hasta };
    $.ajax({
      data: dato,
      url: "../Estadisticas/Procesos/php/tablas.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          var suma = currencyFormat(Number(jsonData.total));
          var sumaf = currencyFormat(Number(jsonData.totalf));
          var sumanf = currencyFormat(Number(jsonData.totalnf));
          var afacturar = currencyFormat(
            Number(jsonData.totalnf) - Number(jsonData.totalf)
          );
          $("#total_dashboard").html(suma);
          $("#total_dashboard2").html(jsonData.servicios + " servicios");
          $("#facturadas").html(sumaf);
          $("#facturadas2").html(jsonData.serviciosf + " servicios");
          $("#sinfacturar").html(sumanf);
          $("#sinfacturar2").html(jsonData.serviciosnf + " servicios");
        }
      },
    });
  } //FUNCTION GENERAR TABLA

  function generarTabla_rec(f, ciclo, desde, hasta) {
    var facturado = f;
    var ciclo = ciclo;

    $("#tablarecorridos").DataTable({
      ajax: {
        url: "../Estadisticas/Procesos/php/tablas.php",
        data: {
          Recorridos: 1,
          desde: desde,
          hasta: hasta,
          facturado: facturado,
          ciclo: ciclo,
        },
        type: "post",
      },
      columns: [
        {
          data: "Fecha",
          render: function (data, type, row) {
            var Fecha = row.Fecha.split("-").reverse().join("/");
            return Fecha;
          },
        },
        {
          data: null,
          render: function (data, type, row) {
            return `<td><b>${row.NumerodeOrden}</b></td>`;
          },
        },
        { data: "Cliente" },
        { data: "Patente" },
        { data: "Recorrido" },
        { data: "Kilometros" },
        {
          data: "PrecioVenta",
          render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
        },
      ],
    });
  }
}); //Document Ready

//ELIMINAR REGISTRO
function ver(i) {
  $("#warning-header-modal").modal("show");

  $("#warning-header-modalLabel").html("ELIMINAR REGISTRO " + i);

  $("#warning-body-modal").html("Estas por Eliminar el Registro " + i);

  $("#eliminar_registro").click(function () {
    var dato = { Eliminar: 1, id: i };

    $.ajax({
      data: dato,
      url: "../Estadisticas/Procesos/php/tablas.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          $.NotificationApp.send(
            "Registro Actualizado !",
            "Se ha actualizado el Recorrido.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );

          var datatable1 = $("#tablactasctes").DataTable();
          datatable1.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Registro No Actualizado !",
            "No se pudo eliminar el registro.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
        $("#warning-header-modal").modal("hide");
      },
    });
  });
}
