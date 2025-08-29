// --- Helpers de sesión / manejo de 401 ---
function redirectIf401(xhr) {
  if (xhr && xhr.status === 401) {
    window.location.href = "/SistemaTriangular/inicio.php?expired=1";
    return true;
  }
  return false;
}

function dtAjaxCommon() {
  return {
    type: "post",
    dataSrc: function (json) {
      try {
        if (json && json.ok === false && json.error === "NO_AUTH") {
          window.location.href = "/SistemaTriangular/inicio.php?expired=1";
          return [];
        }
        return (json && json.data) ? json.data : [];
      } catch (e) {
        console.error("DataTables dataSrc parse error", e, json);
        return [];
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  };
}

// Log rápido para verificar cookie en este host
try { console.debug("CADDY cookie presente:", document.cookie.includes("CADDYSESS=")); } catch (e) {}

window.setInterval(function () {
  updateStats();
}, 2000);

function updateStats() {}

$(document).ready(function () {
  $("#mes").html("Panel de Control");
  var datatableTransporte = $("#transporte").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Transporte: 1 }
    }),
    columns: [
      {
        data: "Estado",
        render: function (data, type, row) {
          if (row.Estado == "Cargada") {
            var color = "success";
          } else if (row.Estado == "Alta") {
            var color = "danger";
          }
          return (
            "<td>" +
            "<i class='mdi mdi-circle text-" +
            color +
            "'></i>" +
            "</td>"
          );
        },
      },
      { data: "NumerodeOrden" },
      { data: "Fecha" },
      { data: "Hora" },
      { data: "Patente" },
      {
        data: "NombreChofer",
        render: function (data, type, row) {
          return (
            "<td>" +
            row.NombreChofer +
            "</br>" +
            "<h6 class='text-muted'>" +
            row.NombreChofer2 +
            "</h6>" +
            "</td>"
          );
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            "<td>" +
            row.Recorrido +
            "</br>" +
            "<h6 class='text-muted'>" +
            row.Nombre +
            "</h6>" +
            "</td>"
          );
        },
      },
      { data: "Estado" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            "<td>" +
            "<a data-id='" +
            row.id +
            "' data-toggle='modal' data-target='#full-width-modal_order'><i class='mdi mdi-24px mdi-go-kart-track text-success'></i>" +
            "</td>"
          );
        },
      },
    ],
  });
  //PREVENTA
  $.ajax({
    data: { PreVenta: 1 },
    url: "../Inicio/php/tablasCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success != 0) {
        document.getElementById("preventa").style.display = "block";
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });

  var datatablePreventa = $("#tabla_preventa").DataTable({
    paging: false,
    searching: false,
    //   scrollX: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { PreVenta: 1 }
    }),
    columns: [
      { data: "RazonSocial" },
      { data: "DomicilioOrigen" },
      { data: "Cantidad" },
      {
        data: "id",
        render: function (data, type, row) {
          return (
            "<td>" +
            "<a href='../Ventas/Pendientes.php'><i class='mdi mdi-24px mdi-map-search-outline'></i>" +
            "</td>"
          );
        },
      },
    ],
  });

  var datatable1 = $("#flota").DataTable({
    paging: false,
    searching: false,
    scrollX: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Flota: 1 }
    }),
    columns: [
      { data: "Marca" },
      { data: "Dominio" },
      { data: "Ano" },
      { data: "Kilometros" },
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
    ],
  });
  //LOGISTICA HOJAS DE RUTA ACTIVAS
  var datatableLogistica = $("#logistica").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Logistica: 1 }
    }),
    columns: [
      {
        data: "ColorSistema",
        render: function (data, type, row) {
          return (
            "<td>" +
            "<i class='mdi mdi-24px mdi-truck text-success'></i>" +
            "</td>"
          );
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            "<td>" +
            row.Recorrido +
            "</br>" +
            "<h6 class='text-muted'>" +
            row.Nombre +
            "</h6>" +
            "</td>"
          );
        },
      },
      {
        data: "Marca",
        render: function (data, type, row) {
          return (
            "<td>" +
            row.Dominio +
            "</br>" +
            "<h6 class='text-muted'>" +
            row.Marca +
            "</h6>" +
            "</td>"
          );
        },
      },
      { data: "Chofer" },
      { data: "id" },
      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          //             return "<td>"+
          //                    "<a id='pendmapa' data-rec='" + row.NumerodeOrden + "' data-id='" + row.Recorrido + "' data-fieldname='" + data + "' data-toggle='modal'  data-target='#pendientesmapa'>"+
          //                     "<i class='mdi mdi-24px mdi-map-search-outline'></i></a>"+
          //                    "</td>"
          //                    }

          return (
            "<td>" +
            //                    "<a target='_blank' href='../Servicios/SeguimientoRecorridos.php?Recorrido="+recorrido_utf+"'><i class='mdi mdi-24px mdi-map-search-outline'></i>"+
            "<a href='../Inicio/Cpanel_Original.php?Recorrido=" +
            row.Recorrido +
            "&NO=" +
            row.NumerodeOrden +
            "'><i class='mdi mdi-24px mdi-map-search-outline'></i>" +
            "</td>"
          );
        },
      },
    ],
  });

  var datatableLogistica1 = $("#logistica1").DataTable({
    paging: false,
    searching: false,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Logistica1: 1 }
    }),
    columns: [
      {
        data: "Color",
        render: function (data, type, row) {
          return `<td><i class="mdi mdi-24px mdi-truck" style="color:#${row.Color}"></i></td>`;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return `<td><a>${row.Recorrido}</a></br><small>${row.Nombre}</small></td>`;
        },
      },
      { data: "Zona" },
      { data: "id" },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            "<td>" +
            "<a id='pend' data-id='" +
            row.Recorrido +
            "' data-fieldname='" +
            data +
            "' data-toggle='modal'  data-target='#bs-example-modal-lg'>" +
            "<i class='mdi mdi-24px mdi-file-search-outline'></i></a>" +
            "</td>"
          );
        },
      },
      // {data:"id",
      // render: function (data, type, row) {
      //     return "<td>"+
      //            "<a><i class='mdi mdi-24px mdi-arrow-top-right-thin-circle-outline text-success'></i></a>"+
      //            "</td>"
      //            }
      // },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return (
            "<td>" +
            "<a data-id='" +
            row.Recorrido +
            "' data-fieldname='" +
            data +
            "' data-toggle='modal'  data-target='#deposito-modal'>" +
            "<i class='mdi mdi-24px mdi-download-circle text-danger'></i></a>" +
            "</td>"
          );
        },
      },
    ],
  });

  $.ajax({
    data: { Entregas: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#entregas_dia").html(jsonData.Total + " envíos hoy");
        $("#entregas_dia_flex").html(jsonData.Total_flex + " Flex");
        $("#entregas_dia_simple").html(jsonData.Total_simple + " Simple");

        $("#entregas_mes").html(jsonData.TotalMes + " envíos este mes");

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
          document.getElementById("entregas_porc_color").className =
            "badge badge-success mr-1";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("entregas_porc").className =
            "mdi mdi-arrow-down-bold";
          document.getElementById("entregas_porc_color").className =
            "badge badge-danger mr-1";
        }

        $("#envios_flex").html(jsonData.TotalMes_flex + " Flex");
        $("#envios_simple").html(jsonData.TotalMes_simple + " Simple");

        $("#entregasr_dia").html(jsonData.Totalr + " envíos hoy");
        $("#entregasr_mes").html(jsonData.TotalMesr + " envíos este mes");
        $("#entregasr_porc").html("% " + jsonData.Porcentajer);
        $("#entregasr_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesantr + " )"
        );
        if (jsonData.Tendenciar == 0) {
          document.getElementById("entregasr_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendenciar == 1) {
          document.getElementById("entregasr_porc").className =
            "mdi mdi-arrow-up-bold";
          document.getElementById("entregasr_porc_color").className =
            "badge badge-success mr-1";
        } else if (jsonData.Tendenciar == 2) {
          document.getElementById("entregasr_porc").className =
            "mdi mdi-arrow-down-bold";
          document.getElementById("entregasr_porc_color").className =
            "badge badge-danger mr-1";
        }
      } else {
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });
  $.ajax({
    data: { OC: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#ordenes_de_compra").html(jsonData.Total);
        $("#ordenes_de_compra_estado").html(jsonData.Estado);
      } else {
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });

  $.ajax({
    data: { Clientes: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#clientes_dia").html((jsonData.Total ?? 0) + " activos hoy");
        $("#clientes_mes").html((jsonData.TotalMes ?? 0) + " activos este mes");
        $("#clientes_porc").html("% " + (jsonData.Porcentaje ?? 0));
        $("#clientes_mesant").html(
          "Desde el mes pasado (" + (jsonData.TotalMesant ?? 0) + " )"
        );
        if (jsonData.Tendencia == 0) {
          document.getElementById("clientes_porc").className =
            "mdi-arrow-left-right-bold";
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("clientes_porc").className =
            "mdi mdi-arrow-up-bold";
          document.getElementById("clientes_porc_color").className =
            "badge badge-success mr-1";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("clientes_porc").className =
            "mdi mdi-arrow-down-bold";
          document.getElementById("clientes_porc_color").className =
            "badge badge-danger mr-1";
        }
      } else {
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });

  $.ajax({
    data: { Kilometros: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        $("#kilometros_dia").html(jsonData.Total + " kilómetros hoy");
        $("#kilometros_mes").html(jsonData.TotalMes + " kilómetros este mes");
        $("#kilometros_porc").html("% " + jsonData.Porcentaje);
        $("#kilometros_mesant").html(
          "Desde el mes pasado (" + jsonData.TotalMesant + " Km.)"
        );
        if (jsonData.Tendencia == 0) {
          const iconoKilometros = document.getElementById("kilometros");
          if (iconoKilometros) {
            iconoKilometros.classList.add("mdi-arrow-left-right-bold");
          }
        } else if (jsonData.Tendencia == 1) {
          document.getElementById("kilometros_porc").className =
            "mdi mdi-arrow-up-bold";
          document.getElementById("kilometros_porc_color").className =
            "badge badge-success mr-1";
        } else if (jsonData.Tendencia == 2) {
          document.getElementById("kilometros_porc").className =
            "mdi mdi-arrow-down-bold";
          document.getElementById("kilometros_porc_color").className =
            "badge badge-danger mr-1";
        }
      } else {
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });

  $.ajax({
    data: { Alarmas: 1 },
    url: "../Inicio/php/funcionesCpanel.php",
    type: "post",
    beforeSend: function () {
      //           document.getElementById("spinner").style.display="block";
    },
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        if (jsonData.Licencias != "0") {
          document.getElementById("alerta_licencias").style.display = "block";
          $("#alerta_licencias_label").html(
            "<strong>Atención !</strong> Hay " +
              jsonData.Licencias +
              " Licencias de Conducir de Empleados Vencidas"
          );
        }

        if (jsonData.Seguro != "0") {
          document.getElementById("alerta_seguros").style.display = "block";
          $("#alerta_seguros_label").html(
            "<strong>Alerta !</strong> Hay " +
              jsonData.Seguro +
              " Seguros de Vehículos <strong>Vencidos</strong> Verificar!"
          );
        }
        if (jsonData.Service != "0") {
          document.getElementById("alerta_services").style.display = "block";
          $("#alerta_services_label").html(
            "<strong>Atención !</strong> Hay " +
              jsonData.Service +
              " Services Pendientes"
          );
        }
        if (jsonData.Itv != "0") {
          document.getElementById("alerta_itv").style.display = "block";
          $("#alerta_itv_label").html(
            "<strong>Atención !</strong> Hay " +
              jsonData.Itv +
              " Inspecciones Técnicas (Itv) Pendientes"
          );
        }
      } else {
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });
  // echo json_encode(array('success'=> 1,'Licencias'=>$row[idLicencias],'Service'=>$row[idService],'Seguro'=>$row[idSeguro],'Itv'=>$row[idItv]));
});

$("#deposito-modal").on("show.bs.modal", function (e) {
  let triggerLink = $(e.relatedTarget);
  let id = triggerLink[0].dataset["id"];
  let rec = triggerLink[0].dataset["rec"];

  $("#deposito-modal-body").text(
    "Estas por vaciar el Recorrido  " +
      id +
      ". Se enviaran todos los servicios al recorrido Deposito (Recorrido 80)"
  );
  $("#deposito-modal-ok").click(function () {
    //   alert(id);
    $.ajax({
      data: { VaciarRecorrido: 1, Recorrido: id },
      url: "../Inicio/php/funcionesCpanel.php",
      type: "post",
      beforeSend: function () {
        //           document.getElementById("spinner").style.display="block";
      },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          var datatableLogistica1 = $("#logistica1").DataTable();
          datatableLogistica1.ajax.reload();
          $("#deposito-modal").modal("hide");
          $.NotificationApp.send(
            "Exito !",
            "Se movieron los servicios a Deposito.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
        }
      },
      error: function (xhr) {
        if (redirectIf401(xhr)) return;
        console.error("AJAX error", xhr.status, xhr.responseText);
      }
    });
  });
});

$("#pendientesmapa").on("show.bs.modal", function (e) {
  let triggerLink = $(e.relatedTarget);
  let id = triggerLink[0].dataset["id"];
  let rec = triggerLink[0].dataset["rec"];

  $("#tabla_pendientesmapa_title").text("Envios Pendientes Recorrido " + id);
  var datatablePendientes = $("#tabla_pendientesmapa").DataTable({
    paging: false,
    searching: false,
    destroy: true,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { PendientesEnRecorrido: 1, Recorrido: id, Orden: rec }
    }),
    columns: [
      { data: "Fecha" },
      { data: "Cliente" },
      { data: "Localizacion" },
      { data: "Ciudad" },
      { data: "Seguimiento" },
      {
        data: "Seguimiento",
        render: function (data, type, row) {
          return (
            "<td>" +
            "<a href='../Servicios/Seguimiento.php?codigoseguimiento_t=" +
            row.Seguimiento +
            "&Continuar=Buscar'><i class='mdi mdi-24px mdi-file-search-outline'></i>" +
            "</td>"
          );
        },
      },
    ],
  });
});

$("#bs-example-modal-lg").on("show.bs.modal", function (e) {
  let triggerLink = $(e.relatedTarget);
  let id = triggerLink[0].dataset["id"];
  $("#myLargeModalLabel").text("Envíos Pendientes Recorrido " + id);
  $("#idRecorridoPendientes").html(id);
  var datatablePendientes = $("#pendientes").DataTable({
    paging: false,
    searching: false,
    destroy: true,
    ajax: Object.assign(dtAjaxCommon(), {
      url: "../Inicio/php/tablasCpanel.php",
      data: { Pendientes: 1, id: id }
    }),
    columns: [
      {
        data: "Fecha",
        render: function (data, type, row) {
          var Fecha = row.Fecha.split("-").reverse().join(".");
          return `<td>${Fecha}</td>`;
        },
      },
      {
        data: "Origen",
        render: function (data, type, row) {
          return `<td>${row.Origen}</td><br/><small class='text-muted'>${row.DomicilioOrigen}</small>`;
        },
      },
      {
        data: "Destino",
        render: function (data, type, row) {
          return `<td>${row.Destino}</td><h6 class='text-muted'>${row.DomicilioDestino}</h6>`;
        },
      },
      {
        data: "Notas",
        render: function (data, type, row) {
          return `<td><i class="text-info mdi mdi-18px mdi-pencil" onclick="notas(${row.id})"></i> <small class="text-info"> ${row.Notas}</small></a></td>`;
        },
      },
      {
        data: "Seguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
            row.Seguimiento +
            '"' +
            'data-title="' +
            data.Destino +
            '" data-fieldname="' +
            data +
            '"><b>' +
            row.Seguimiento +
            "</b></a></td>"
          );
        },
      },
      {
        data: "Seguimiento",
        render: function (data, type, row) {
          // return "<td>"+
          //    "<a href='../Servicios/Seguimiento.php?codigoseguimiento_t=" + row.Seguimiento + "&Continuar=Buscar'><i class='mdi mdi-24px mdi-file-search-outline'></i>"+
          //    "</td>"+
          //    "<td class='table-action'>"+
          //    "<a style='cursor:pointer'  data-toggle='modal' data-target='#rotulos-modal' data-id='" + row.Seguimiento + "' data-title='" + data.Destino + "' data-fieldname='" + data + "'><i class='mdi mdi-24px mdi-printer'></i></a></td>'"+
          //    "<td>"+
          return (
            "<a target='_blank' href='../Servicios/Informes/Remitopdf.php?CS=" +
            row.Seguimiento +
            "'><i class='mdi mdi-24px mdi-file-outline text-success'></i>" +
            "</td>"
          );
        },
      },
      {
        data: "Seguimiento",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a style="cursor:pointer"  data-toggle="modal" data-target="#rotulos-modal" data-id="' +
            row.Seguimiento +
            '"' +
            'data-title="' +
            data.Destino +
            '" data-fieldname="' +
            data +
            '"><i class="mdi mdi-24px mdi-printer"></i></a></td>'
          );
        },
      },
    ],
  });
});

$("#remitos-modal").on("show.bs.modal", function (e) {
  let triggerLink = $(e.relatedTarget);
  let id = triggerLink[0].dataset["id"];

  if (id != null) {
    $("#body-remitos").html("Se imprimirá el Remitorótulo del Código " + id);
    $("#imp_rem").show();
    $("#imp_rem_rec").hide();

    $("#imp_rem").click(function writeToSelectedPrinter(id) {
      alert("ok");
      $("#imp_rem_rec").hide();
    });
  } else {
    var rec = $("#idRecorridoPendientes").html();
    $("#body-remitos").html(
      "Se imprimiran todos los Remitos del recorrido " + rec
    );
    $("#imp_rem").hide();
    $("#imp_rem_rec").show();
  }
});

$("#imp_rem_rec").click(function writeToSelectedPrinter(id) {
  var rec = $("#idRecorridoPendientes").html();
  window.open(
    "http://www.caddy.com.ar/../Ventas/Informes/autoimpresion.php?Recorrido=" +
      rec,
    "_blank"
  );
  // $.ajax({
  //     data:{'RemitosRec':1,'rec':rec},
  //     type: "POST",
  //     url: "../Inicio/php/funciones.php",
  //     success: function(response)
  //     {
  //     var jsonData = JSON.parse(response);

  //     }

  //     });
});
function print_pdf(url) {
  var id = "iframe",
    html =
      '<iframe id="' +
      id +
      '" src="' +
      url +
      '" style="display:none"></iframe>';
  $("#main").append(html);
  $("#" + id).load(function () {
    document.getElementById(id).contentWindow.print();
  });
}

function notas(i) {
  $("#notas-modal").modal("show");
  $("#notas_id").val(i);
  $.ajax({
    data: { VerNotas: 1, id: i },
    type: "POST",
    url: "../Inicio/php/funcionesCpanel.php",
    success: function (response) {
      var jsonData = JSON.parse(response);

      if (jsonData.success == 1) {
        $("#notas_txt").val(jsonData.notas);
      }
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });
}

$("#notas-modal-ok").click(function () {
  var i = $("#notas_id").val();
  var notas = $("#notas_txt").val();

  $.ajax({
    data: { AgregarNotas: 1, id: i, notas: notas },
    type: "POST",
    url: "../Inicio/php/funcionesCpanel.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        // $.NotificationApp('Exito','Cargamos la nota');

        var datatablePendientes = $("#pendientes").DataTable();

        datatablePendientes.ajax.reload();
      } else {
      }
      $("#notas-modal").modal("hide");
    },
    error: function (xhr) {
      if (redirectIf401(xhr)) return;
      console.error("AJAX error", xhr.status, xhr.responseText);
    }
  });
});
