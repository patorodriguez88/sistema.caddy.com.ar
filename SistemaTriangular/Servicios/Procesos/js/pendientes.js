var datatable = $("#seguimiento").DataTable({
  dom: "Bfrtip",
  buttons: ["pageLength", "copy", "excel", "pdf"],
  paging: true,
  searching: true,
  lengthMenu: [
    [10, 25, 50, -1],
    [10, 25, 50, "All"],
  ],

  ajax: {
    url: "Procesos/php/pendientes.php",
    data: { Pendientes: 1 },
    processing: true,
    type: "post",
  },
  columns: [
    {
      data: "Fecha",
      render: function (data, type, row) {
        var Fecha = row.Fecha.split("-").reverse().join(".");
        return (
          '<td><span style="display: none;">' +
          row.Fecha +
          "</span>" +
          Fecha +
          "</td>"
        );
      },
    },
    {
      data: "NumeroComprobante",
      render: function (data, type, row) {
        if (row.Wepoint_f != "0000-00-00") {
          var wepoint = `<span class="badge badge-info badge-pill">Wh: ${row.Wepoint_f} ${row.Wepoint_h}</span>`;
        } else {
          wepoint = "";
        }
        return `<td><b>${row.NumeroComprobante}</br><a>${row.Usuario}</a></br>${wepoint}`;
      },
    },
    {
      data: "RazonSocial",
      render: function (data, type, row) {
        if (row.Retirado == 0) {
          var color = "success";
        } else {
          color = "muted";
        }
        return (
          "<td><b>" +
          row.RazonSocial +
          " (" +
          row.CodigoProveedor +
          ")</br>" +
          '<i class="mdi mdi-18px mdi-map-marker text-' +
          color +
          '"></i><a class="text-muted">' +
          row.DomicilioOrigen +
          "</td>"
        );
      },
    },
    {
      data: "DomicilioDestino",
      render: function (data, type, row) {
        if (row.Retirado == 1) {
          var color1 = "success";
        } else {
          color1 = "muted";
        }
        return (
          "<td><b>" +
          row.ClienteDestino +
          "</br>" +
          '<i class="mdi mdi-18px mdi-map-marker text-' +
          color1 +
          '"></i><a class="text-muted">' +
          row.DomicilioDestino +
          "</td>"
        );
      },
    },
    {
      data: "Observaciones",
      render: function (data, type, row) {
        if (row.Notas != null) {
          var textoOriginal = row.Notas;
          var textoConvertido = textoOriginal
            .toLowerCase()
            .replace(/(?:^|\s)\S/g, function (a) {
              return a.toUpperCase();
            });

          return `<td>${row.Observaciones}</br>
                        <a class="text-danger" style="font-size:9px"><b> Nota interna: ${textoConvertido}</b></a>
                </td>`;
        } else {
          return `<td>${row.Observaciones}</td>`;
        }
      },
    },
    {
      data: "CodigoSeguimiento",
      render: function (data, type, row) {
        if (row.Retirado == 1) {
          var color = "success";
          var servicio = "Entrega";
        } else {
          var color = "muted";
          var servicio = "Retiro";
        }
        if (row.Flex == 1) {
          var color_flex = "success";
          var badget = "Flex";
        } else {
          var color_flex = "warning";
          var badget = "Simple";
        }

        return (
          '<td class="table-action col-xs-3">' +
          '<a style="cursor:pointer" class="text-primary"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' +
          row.CodigoSeguimiento +
          '"' +
          'data-title="' +
          data.ClienteDestino +
          '" data-fieldname="' +
          data +
          '"><b>' +
          row.CodigoSeguimiento +
          "</b></a></br>" +
          "<a><b>" +
          servicio +
          "</b></a><br/>" +
          '<span class="badge badge-dark-lighten"> $ ' +
          row.Debe +
          "</span></br>" +
          '<a onclick="filter(' +
          row.id +
          ')" class="badge text-white badge-' +
          color_flex +
          '">' +
          badget +
          "</a>" +
          "</td>"
        );
      },
    },
    //           {data:"Recorrido"},
    {
      data: "Recorrido",
      render: function (data, type, row) {
        if (row.Redespacho == 0) {
          return `<td class="table-action"><a style="cursor:pointer" data-id="${row.CodigoSeguimiento}" id="${row.CodigoSeguimiento}" onclick="modificarrecorrido(this.id);" ><b class="text-primary">${row.Recorrido}</b></a></td>`;
        } else {
          return `<td class="table-action"><a style="cursor:pointer" data-id="${row.CodigoSeguimiento}" id="${row.CodigoSeguimiento}" onclick="modificarrecorrido(this.id);" ><b class="text-primary">${row.Recorrido}</b></a><br/><span class="badge badge-warning text-white"><i class="mdi mdi-alpha-r-box"></i> Redespacho</span></td>`;
        }
      },
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
          '" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>' +
          '<a data-id="' +
          row.id +
          '" id="' +
          row.id +
          '" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a>' +
          "</td>"
        );
      },
    },
  ],
});

function filter(id) {
  // alert(id);
  $.ajax({
    data: { CambiarFlex: 1, id: id },
    type: "POST",
    url: "Procesos/php/pendientes.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
      }
    },
  });
}
$("#entregado").change(function (e) {
  if (this.checked) {
    $("#entregado").val(1);
  } else {
    $("#entregado").val(0);
  }
});

function modificarrecorrido(i) {
  $("#cs_modificar_REC").val(i);
  $.ajax({
    data: { BuscarRecorridos: 1, cs: i },
    type: "POST",
    url: "Procesos/php/pendientes.php",
    success: function (response) {
      $(".selector-recorrido select").html(response).fadeIn();
    },
  });

  $("#myCenterModalLabel_rec").html("Modificar Recorrido a Código " + i);
  $("#standard-modal-rec").modal("show");
}

$("#modificarrecorrido_ok").click(function () {
  var cs = $("#cs_modificar_REC").val();
  var r = $("#recorrido_t").val();
  $.ajax({
    data: { ActualizaRecorrido: 1, r: r, cs: cs },
    type: "POST",
    url: "Procesos/php/pendientes.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
        $("#standard-modal-rec").modal("hide");
        $.NotificationApp.send(
          "Registro Actualizado !",
          "Se ha actualizado el Recorrido.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
      } else {
        $.NotificationApp.send(
          "Registro No Actualizado !",
          "No pudimos actualizar el Recorrido.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
});

function modificar(i) {
  $("#id_modificar").val(i);
  $("#standard-modal").modal("show");
  $("#id_trans").val(i);
  $("#myCenterModalLabel_modificar").html("Modificar id # " + i);
}

$("#standard-modal").on("hide.bs.modal", function (e) {
  //RELOAD TABLA SEGUIMIENTO
  var datatable = $("#seguimiento").DataTable();
  datatable.ajax.reload();
  //RELOAD TABAL VENTAS
  var table = $("#ventas_tabla").DataTable();
  table.destroy();
});

$("#standard-modal").on("show.bs.modal", function (e) {
  var id = $("#id_modificar").val();
  var datatable = $("#ventas_tabla").DataTable({
    bFilter: false,
    bInfo: false,
    paging: false,
    search: false,
    ajax: {
      url: "Procesos/php/funciones.php",
      data: { BuscarDatosVentas: 1, id: id },
      processing: true,
      type: "post",
    },
    columns: [
      { data: "idPedido" },
      { data: "FechaPedido" },
      { data: "Codigo" },
      { data: "Titulo" },
      { data: "Total" },
      {
        data: "idPedido",
        render: function (data, type, row) {
          return (
            '<td class="table-action">' +
            '<a id="' +
            row.idPedido +
            '" onclick="modificarVentas(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>' +
            '<a id="' +
            row.idPedido +
            '" onclick="eliminarVentas(this.id);" class="action-icon"> <i class="mdi mdi-delete text-warning"></i></a>' +
            '<a id="' +
            row.NumPedido +
            '" onclick="agregarVentas(this.id);" class="action-icon"> <i class="mdi mdi-plus-circle text-success"></i></a>' +
            "</td>"
          );
        },
      },
    ],
  });
});

function agregarVentas(id) {
  $("#bs-ventas-modal-lg").modal("show");
  $("#ventas_codigo").val("");
  $("#ventas_titulo").val("");
  $("#ventas_observaciones").val("");
  $("#ventas_cantidad").val(1);
  $("#ventas_precio").val("0");
  $("#ventas_total").val(0);

  //  $('#servicio').find('option').not(':first').reload();
  $("#servicio").val($("#servicio option:first").val());

  $("#header-ventas-modal").html("Agregar Venta a Codigo # " + id);
  $("#agregarventas_ok").show();
  $("#modificarventas_ok").hide();
  $("#ventas_fecha").css("readonly", "false");

  $("#agregarventas_ok").click(function () {
    var titulo = $("#ventas_titulo").val();
    var codigoventa = $("#ventas_codigo").val();
    var cantidadventa = $("#ventas_cantidad").val();
    var precioventa = $("#ventas_precio").val();
    var totalventa = $("#ventas_total").val();
    var observacionesventa = $("#ventas_observaciones").val();
    $.ajax({
      data: {
        AgregarDatosVentas: 1,
        codigoseguimiento: id,
        tituloventa: titulo,
        codigoventa: codigoventa,
        cantidadventa: cantidadventa,
        precioventa: precioventa,
        totalventa: totalventa,
        observacionesventa: observacionesventa,
      },
      url: "Procesos/php/funciones.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          $("#bs-ventas-modal-lg").modal("hide");
          var table = $("#ventas_tabla").DataTable();
          table.ajax.reload();
        }
      },
    });
  });
}

function cargar(id) {
  var dato = { id_servicio: id };
  $.ajax({
    data: dato,
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        document.getElementById("ventas_titulo").value = jsonData.Titulo;
        document.getElementById("ventas_precio").value = jsonData.PrecioVenta;
        document.getElementById("ventas_codigo").value = jsonData.Codigo;
        var cantidad = document.getElementById("ventas_cantidad").value;
        document.getElementById("ventas_total").value =
          jsonData.PrecioVenta * cantidad;
      }
    },
  });
}

//MODIFICAR TOTAL DESDE CANTIDAD
$("#ventas_cantidad").blur(function () {
  var precio = parseFloat(document.getElementById("ventas_precio").value);
  var cantidad = Number(document.getElementById("ventas_cantidad").value);
  var resultado = precio * cantidad;
  document.getElementById("ventas_total").value = resultado;
  //  document.getElementById('ventas_total').value=precio*cantidad;
});

//MODIFICAR TOTAL DESDE PRECIO
$("#ventas_precio").blur(function () {
  var precio = parseFloat(document.getElementById("ventas_precio").value);
  var cantidad = Number(document.getElementById("ventas_cantidad").value);
  var resultado = precio * cantidad;
  document.getElementById("ventas_total").value = resultado;
  //   console.log('resultado',precio*cantidad);
});

function eliminarVentas(id) {
  $("#warning-modal").modal("show");
  $("#warning-modal-ok").hide();
  $("#warning-modal-ventas-ok").show();

  $("#warning-modal-body").html("Estas por eliminar el Registro " + id);
  $("#id_eliminar").val(id);
  // $('#codigoseguimiento_eliminar').val(jsonData.CodigoSeguimiento);

  $("#warning-modal-ventas-ok").click(function () {
    $.ajax({
      data: { EliminarDatosVentas: 1, idPedido: id },
      url: "Procesos/php/funciones.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == 1) {
          var datatable = $("#ventas_tabla").DataTable();
          datatable.ajax.reload();

          $.toast({
            text: "Se ha actualizado la tabla Ventas correctamente.",
            icon: "success",
            heading: "Exito !",
            bgColor: "success",
            textColor: "white",
            position: "bottom-right",
            showHideTransition: "plain",
          });

          $("#warning-modal").modal("hide");
        } else {
          $.toast({
            text: "No pudimos eliminar el registro en la tabla Ventas.",
            icon: "warning",
            heading: "Error!",
            bgColor: "warning",
            textColor: "white",
            position: "bottom-right",
            showHideTransition: "plain",
          });
        }
      },
    });
  });
}

function modificarVentas(id) {
  $.ajax({
    data: { BuscarDatosVentas: 1, idPedido: id },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#ventas_fecha").val(jsonData.data[0].FechaPedido);
      $("#ventas_codigo").val(jsonData.data[0].Codigo);
      $("#ventas_titulo").val(jsonData.data[0].Titulo);
      $("#ventas_cantidad").val(jsonData.data[0].Cantidad);
      $("#ventas_precio").val(jsonData.data[0].Precio);
      $("#ventas_total").val(jsonData.data[0].Total);
      $("#bs-ventas-modal-lg").modal("show");
      $("#standard-modal").modal("hide");
      $("#header-ventas-modal").html("Modificar Venta # " + id);
      $("#idPedido").val(id);
    },
  });
}

$("#modificarventas_ok").click(function () {
  var idPedido = $("#idPedido").val();
  var idTrans = $("#id_trans").val();
  var codigo = $("#ventas_codigo").val();
  var precio = $("#ventas_precio").val();
  var titulo = $("#ventas_titulo").val();
  var total = $("#ventas_total").val();
  var cantidad = $("#ventas_cantidad").val();
  var observaciones = $("#ventas_observaciones").val();

  $.ajax({
    data: {
      ModificarDatosVentas: 1,
      idPedido: idPedido,
      codigo: codigo,
      titulo: titulo,
      total: total,
      idTrans: idTrans,
      precio: precio,
      cantidad: cantidad,
      observaciones: observaciones,
    },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#bs-ventas-modal-lg").modal("hide");

      if (jsonData.successventas == 1) {
        $.toast({
          text: "Se ha actualizado la tabla Ventas correctamente.",
          icon: "success",
          heading: "Exito !",
          bgColor: "success",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      } else {
        $.toast({
          text: "No pudimos actualizar el registro en la tabla Ventas.",
          icon: "warning",
          heading: "Error!",
          bgColor: "warning",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      }
      if (jsonData.successtrans == 1) {
        $.toast({
          text: "Se ha actualizado la tabla Transacciones correctamente.",
          icon: "success",
          heading: "Exito !",
          bgColor: "success",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      } else {
        $.toast({
          text: "No pudimos actualizar el registro en la tabla Transacciones.",
          icon: "warning",
          heading: "Error!",
          bgColor: "warning",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      }
      if (jsonData.successctasctes == 1) {
        $.toast({
          text: "Se ha actualizado la tabla Cuentas Corrientes correctamente.",
          icon: "success",
          heading: "Exito !",
          bgColor: "success",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      } else {
        console.log("error", jsonData.successctasctes);
        $.toast({
          text: "No pudimos actualizar el registro en la tabla Cuentas Corrientes.",
          icon: "warning",
          heading: "Error!",
          bgColor: "warning",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      }
      if (jsonData.successctasctesinsert == 1) {
        $.toast({
          text: "Se ha insertado un registro en la tabla Cuentas Corrientes correctamente.",
          icon: "success",
          heading: "Exito !",
          bgColor: "success",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      } else {
        $.toast({
          text: "No pudimos insertar el registro en la tabla Cuentas Corrientes.",
          icon: "warning",
          heading: "Error!",
          bgColor: "warning",
          textColor: "white",
          position: "bottom-right",
          showHideTransition: "plain",
        });
      }
    },
  });
});

$("#bs-ventas-modal-lg").on("hide.bs.modal", function () {
  // document.getElementById("miForm").reset();
  $("#standard-modal").modal("show");
  var datatable = $("#ventas_tabla").DataTable();
  datatable.ajax.reload();
});

$("#modificardireccion_ok").click(function () {
  var entregado = $("#entregado").val();
  var Fecha = $("#fecha_receptor").val();
  var hora = $("#hora_receptor").val();
  var i = $("#id_modificar").val();
  var obs = $("#observaciones_receptor").val();
  $("#myCenterModalLabel").html("Modificar id # " + i);

  if (entregado == 1) {
    $.ajax({
      data: {
        Actualiza: 1,
        id: i,
        entregado: entregado,
        Fecha: Fecha,
        Hora: hora,
        Observaciones: obs,
      },
      url: "Procesos/php/pendientes.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        $.NotificationApp.send(
          "Registro Actualizado !",
          "Se ha actualizado la tabla Clientes correctamente.",
          "bottom-right",
          "#FFFFFF",
          "success"
        );
        var datatable = $("#seguimiento").DataTable();
        datatable.ajax.reload();
        $("#standard-modal").modal("hide");
        $("#form")[0].reset();
      },
    });
  } else {
    $.NotificationApp.send(
      "Presione Entregado !",
      "No se realizaron cambios.",
      "bottom-right",
      "#FFFFFF",
      "warning"
    );
  }
});

function eliminar(e) {
  $.ajax({
    data: { BuscarDatos: 1, id: e },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#warning-modal-body").html(
        "Estas por eliminar el Registro " +
          e +
          " Origen " +
          jsonData.RazonSocial
      );
      $("#id_eliminar").val(e);
      $("#codigoseguimiento_eliminar").val(jsonData.CodigoSeguimiento);
      $("#warning-modal").modal("show");
    },
  });
}

$("#warning-modal-ok").click(function () {
  var id = $("#id_eliminar").val();
  var cs = $("#codigoseguimiento_eliminar").val();
  $.ajax({
    data: { EliminarRegistro: 1, id: id, CodigoSeguimiento: cs },
    url: "Procesos/php/funciones.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#warning-modal").modal("hide");
      if (jsonData.success == 1) {
        if (jsonData.hojaderuta == 1) {
          $.NotificationApp.send(
            "Registro Borrado !",
            "Se ha borrado el registro en Hoja de Ruta correctamente.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
          var datatable = $("#seguimiento").DataTable();
          datatable.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Error !",
            "No se han realizado cambios en Hoja de Ruta.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
        if (jsonData.transclientes == 1) {
          $.NotificationApp.send(
            "Registro Borrado !",
            "Se ha borrado el registro en Trans Clientes correctamente.",
            "bottom-right",
            "#FFFFFF",
            "success"
          );
          var datatable = $("#seguimiento").DataTable();
          datatable.ajax.reload();
        } else {
          $.NotificationApp.send(
            "Error !",
            "No se han realizado cambios en Trans Clientes.",
            "bottom-right",
            "#FFFFFF",
            "danger"
          );
        }
      } else {
        $.NotificationApp.send(
          "Error !",
          "No se han realizado cambios.",
          "bottom-right",
          "#FFFFFF",
          "danger"
        );
      }
    },
  });
});
