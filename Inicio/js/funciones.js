$(document).ready(function () {
  function changeNumber() {
    var datatable = $("#desempeno_dia").DataTable();
    datatable.ajax.reload();
  }
  setInterval(changeNumber, 60000);
});
function currencyFormat(num) {
  return "$" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}
$("#cerrar_panel1").click(function () {
  document.getElementById("panel1").style.display = "none";
});

function modificar(i) {
  document.getElementById("panel1").style.display = "block";
  $("#header_panel1").html("RECORRIDO " + i);

  var table = $("#seguimiento").DataTable();
  table.destroy();

  //TABLA PARA SEGUIMIENTO DE RECORRIDOS
  var datatable = $("#seguimiento").DataTable({
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Pendientes: 1, Orden: i },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "id",
        render: function (data, type, row) {
          if (row.Estado == "No se Pudo entregar") {
            var circle = "danger";
          } else if (row.Estado == "Entregado al Cliente") {
            var circle = "success";
          } else {
            var circle = "warning";
          }
          if (row.Retirado == 1) {
            var Destino = row.ClienteDestino;
            var Direccion = row.DomicilioDestino;
          } else {
            var Destino = row.RazonSocial;
            var Direccion = row.DomicilioOrigen;
          }

          return (
            '<td><i class="mdi mdi-18px mdi-circle text-' +
            circle +
            '"></i> ' +
            Destino +
            "</br>" +
            '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">' +
            Direccion +
            "</td>"
          );
        },
      },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
            row.CodigoSeguimiento +
            '"' +
            'data-title="' +
            data.Destino +
            '" data-fieldname="' +
            data +
            '"><b>' +
            row.CodigoSeguimiento +
            "</b></a></td>"
          );
        },
      },
      {
        data: "Retirado",
        render: function (data, type, row) {
          if (row.Estado == "No se Pudo entregar") {
            var color = "danger";
          } else if (row.Estado == "Entregado al Cliente") {
            var color = "success";
          } else {
            var color = "warning";
          }
          if (row.Retirado == 1) {
            return (
              "<td>Entrega" +
              '<h6><span class="badge badge-' +
              color +
              ' text-white">' +
              row.Estado +
              "</span></h6></td>"
            );
          } else {
            return (
              "<td>Retiro" +
              '<h5><span class="badge badge-' +
              color +
              ' text-white">' +
              row.Estado +
              "</span></h5></td>"
            );
          }
        },
      },
      //           {data:"id",
      //            render: function (data, type, row) {
      //                 return '<td class="table-action">'+
      //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
      //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
      //                 '</td>';
      //             }
      //           }
    ],
  });
}

$(document).ready(function () {
  var meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  var inputMes = new Date().getMonth();
  var numeroMes = Number(inputMes);

  for (var i = 0; i < meses.length; i++) {
    if (i == numeroMes) {
      var mes = meses[i];
    }
  }

  $("#mes").html("Mes Actual " + mes);
  $("#desempeno1").html("DESEMPEÑO REPARTIDORES MES DE " + mes);
  $("#desempeno2").html("PAQUETES ENTREGADOS X REPARTIDOR MES DE " + mes);

  var datatable = $("#basic").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Empleados: 1 },
      type: "post",
    },
    columns: [
      { data: "NombreCompleto" },
      { data: "Km", render: $.fn.dataTable.render.number(".", ",", 0, "") },
      { data: "Salidas" },
    ],
  });

  var datatable = $("#desempeno").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Empleados2: 1 },
      type: "post",
    },
    columns: [{ data: "Chofer" }, { data: "Total" }],
  });
  //DESEMPENIO PRODUCTIVIDAD
  var datatable_desempenio_productividad = $(
    "#desempeno_productividad"
  ).DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Desempenio_productividad: 1 },
      type: "post",
    },
    columns: [
      {
        data: "Chofer",
        render: function (data, Type, row) {
          if (row.Chofer == null) {
            return "<td>Operador Manual</td>";
          } else {
            return `<td>${row.Chofer}</td>`;
          }
        },
      },
      { data: "Total" },
    ],
  });

  var datatable = $("#desempeno_dia").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Servicios: 1 },
      type: "post",
    },
    columns: [
      { data: "NumerodeOrden" },
      { data: "Chofer" },
      { data: "Dominio" },
      { data: "Recorrido" },
      { data: "Total" },
      { data: "Cerradas" },
      {
        data: "Cerradas",
        render: function (data, type, row, meta) {
          return type === "display"
            ? '<div class="progress progress-lg"><div class="progress-bar bg-success" role="progressbar" style="width: ' +
                (row.Cerradas / row.Total) * 100 +
                '%" aria-valuenow="' +
                row.Cerradas +
                '" aria-valuemin="0" aria-valuemax="' +
                row.Total +
                '"></div></div>'
            : data;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a data-id="' +
            row.NumerodeOrden +
            '" id="' +
            row.NumerodeOrden +
            '" onclick="modificar(this.id);" class="action-icon"><i class="mdi mdi-24px mdi-menu"></i>' +
            "</td>"
          );
        },
      },
    ],
  });

  var datatable = $("#scroll-vertical-datatable").DataTable({
    ajax: {
      url: "../Inicio/php/tablas.php",
      data: { Pendientes: 1, Orden: i },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "id",
        render: function (data, type, row) {
          if (row.Estado == "No se Pudo entregar") {
            var circle = "danger";
          } else if (row.Estado == "Entregado al Cliente") {
            var circle = "success";
          } else {
            var circle = "warning";
          }
          if (row.Retirado == 1) {
            var Destino = row.ClienteDestino;
            var Direccion = row.DomicilioDestino;
          } else {
            var Destino = row.RazonSocial;
            var Direccion = row.DomicilioOrigen;
          }

          return (
            '<td><i class="mdi mdi-18px mdi-circle text-' +
            circle +
            '"></i> ' +
            Destino +
            "</br>" +
            '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">' +
            Direccion +
            "</td>"
          );
        },
      },
      {
        data: "CodigoSeguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
            row.CodigoSeguimiento +
            '"' +
            'data-title="' +
            data.Destino +
            '" data-fieldname="' +
            data +
            '"><b>' +
            row.CodigoSeguimiento +
            "</b></a></td>"
          );
        },
      },
      {
        data: "Retirado",
        render: function (data, type, row) {
          if (row.Estado == "No se Pudo entregar") {
            var color = "danger";
          } else if (row.Estado == "Entregado al Cliente") {
            var color = "success";
          } else {
            var color = "warning";
          }
          if (row.Retirado == 1) {
            return (
              "<td>Entrega" +
              '<h6><span class="badge badge-' +
              color +
              ' text-white">' +
              row.Estado +
              "</span></h6></td>"
            );
          } else {
            return (
              "<td>Retiro" +
              '<h5><span class="badge badge-' +
              color +
              ' text-white">' +
              row.Estado +
              "</span></h5></td>"
            );
          }
        },
      },
      //           {data:"id",
      //            render: function (data, type, row) {
      //                 return '<td class="table-action">'+
      //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
      //                 '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
      //                 '</td>';
      //             }
      //           }
    ],
  });

  var datatable1 = $("#flota").DataTable({
    paging: false,
    searching: false,
    ajax: {
      url: "../Inicio/php/tablas.php",
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

      //             {data:"Cantidad"},

      //             {data:"Cantidad"},
      //             {data:"Precio"},
      //             {data:"Total"},
      //             {data:"Comentario"},
      //             {data:"idPedido",
      //             render: function (data, type, row) {
      //             return "<td>"+
      //                    "<a class='action-icon' onclick='eliminar("+ row.idPedido +")'><i class='uil-trash-alt'></i></a>"+
      //                    "</td>"
      //                    }
      //             }
    ],
  });

  $.ajax({
    data: { Entregas: 1 },
    url: "../Inicio/php/funciones.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#entregas_dia").html(jsonData.Total + " envios hoy");
        $("#entregas_mes").html(jsonData.TotalMes + " envios este mes");
        $("#entregas_porc").html("% " + jsonData.Porcentaje);
        $("#entregas_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesant + " )"
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("entregas_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("entregas_porc").className =
            "mdi mdi-arrow-up-bold";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("entregas_porc").className =
            "mdi mdi-arrow-down-bold";
        }

        $("#entregasr_dia").html(jsonData.Totalr + " envios hoy");
        $("#entregasr_mes").html(jsonData.TotalMesr + " envios este mes");
        $("#entregasr_porc").html("% " + jsonData.Porcentajer);
        $("#entregasr_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesantr + " )"
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("entregasr_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("entregasr_porc").className =
            "mdi mdi-arrow-up-bold";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("entregasr_porc").className =
            "mdi mdi-arrow-down-bold";
        }
      } else {
      }
    },
  });
  //  $.ajax({
  //       data:{'OC':1},
  //       url:'../Inicio/php/funciones.php',
  //       type:'post',
  //         beforeSend: function(){
  // //           document.getElementById("spinner").style.display="block";
  //         },
  //       success: function(response)
  //        {
  //           var jsonData = JSON.parse(response);
  //           if (jsonData.success == "1")
  //           {
  //           $('#ordenes_de_compra').html(jsonData.Total);
  //           $('#ordenes_de_compra_estado').html(jsonData.Estado);

  //           }else{
  //           }
  //        }
  //   });

  $.ajax({
    data: { Clientes: 1 },
    url: "../Inicio/php/funciones.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#clientes_dia").html(jsonData.Total + " activos hoy");
        $("#clientes_mes").html(jsonData.TotalMes + " activos este mes");
        $("#clientes_porc").html("% " + jsonData.Porcentaje);
        $("#clientes_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesant + " )"
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("clientes_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("clientes_porc").className =
            "mdi mdi-arrow-up-bold";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("clientes_porc").className =
            "mdi mdi-arrow-down-bold";
        }
      } else {
      }
    },
  });
  $.ajax({
    data: { Kilometros: 1 },
    url: "../Inicio/php/funciones.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#kilometros_dia").html(jsonData.Total + " kilometros hoy");
        $("#kilometros_mes").html(jsonData.TotalMes + " kilometros este mes");
        $("#kilometros_porc").html("% " + jsonData.Porcentaje);
        $("#kilometros_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesant + " Km.)"
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("kilometros").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("kilometros_porc").className =
            "mdi mdi-arrow-up-bold";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("kilometros_porc").className =
            "mdi mdi-arrow-down-bold";
        }
      } else {
      }
    },
  });
});
