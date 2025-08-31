var datatable = $("#incrementos_table").DataTable({
  searching: false,
  paging: false,
  info: false,
  ajax: {
    url: "Procesos/php/servicios.php",
    data: { Incrementos: 1 },
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
    { data: "Usuario" },
    {
      data: "Incremento",
      render: function (data, type, row) {
        return `<td>${row.Incremento}%</td>`;
      },
    },
    { data: "Observaciones" },
  ],
});

var datatable = $("#servicios").DataTable({
  dom: "Bfrtip",
  buttons: ["pageLength", "copy", "excel", "pdf"],
  paging: true,
  searching: true,
  lengthMenu: [
    [10, 25, 50, -1],
    [10, 25, 50, "All"],
  ],
  ajax: {
    url: "Procesos/php/servicios.php",
    data: { Servicios: 1 },
    processing: true,
    type: "post",
  },
  columns: [
    {
      data: "Codigo",
      render: function (data, type, row) {
        if (row.Inactivo == 1) {
          var color = "muted";
        } else {
          color = "black";
        }
        return `<td><a class="text-${color}">${row.Codigo}</td>`;
      },
    },
    {
      data: "Titulo",
      render: function (data, type, row) {
        if (row.Inactivo == 1) {
          var color = "muted";
        } else {
          color = "success";
        }
        return `<td><b>${row.Titulo}</br><td><i class='mdi mdi-18px mdi-cube-send text-${color}'></i><a class='text-muted'> ${row.Descripcion}</td>`;
      },
    },
    {
      data: "id",
      render: function (data, type, row) {
        if (row.Inactivo == 1) {
          var color = "muted";
        } else {
          color = "success";
        }
        return `<td><i class="mdi mdi-18px mdi-map-marker-distance text-${color}"></i> <b>${row.Kilometros}</b></td>`;
      },
    },
    {
      data: "Proveedor",
      render: function (data, type, row) {
        if (row.Inactivo == 1) {
          var color = "muted";
        } else {
          color = "success";
        }
        return `<td><i class="mdi mdi-18px mdi-cash-marker text-${color}"></i> <b>${row.Proveedor}</b></td>`;
      },
    },
    {
      data: "PrecioCosto",

      render: function (data, type, row) {
        var numero = row.PrecioVenta / 1.21;
        var neto = numero.toFixed(2);

        return `<td class="table-action col-xs-3"><b> $ ${neto}</b></td>`;
      },
    },
    {
      data: "PrecioVenta",
      render: function (data, type, row) {
        return `<td class="table-action col-xs-3"><b> $ ${row.PrecioVenta}</b></td>`;
      },
    },
    {
      data: "id",
      render: function (data, type, row) {
        if (row.Inactivo == 1) {
          var activo = "danger";
          var activo_label = "Inactivo";
        } else {
          activo = "success";
          activo_label = "Activo";
        }
        return `<span class="badge bg-${activo} text-white">${activo_label}</span>`;
      },
    },
    {
      data: "id",
      render: function (data, type, row) {
        if (row.Inactivo == 0) {
          var activo = "mdi-eye text-success";
        } else {
          var activo = "mdi-eye-off text-danger";
        }
        return (
          `<td class="table-action"><a id="${row.id}" onclick="modificar(this.id,${row.Inactivo});" class="action-icon"> <i class="mdi ${activo}"></i></a>` +
          `<td class="table-action"><a onclick="showmodal(${row.id});" class="action-icon"> <i class="mdi mdi-border-color text-warning"></i></a></td>`
        );
      },
    },
  ],
});

function modificar(i, a) {
  // var a=$(this).attr("data-id");
  // alert(a);

  $.ajax({
    data: { ActivarServicios: 1, id: i, Inactivo: a },
    url: "Procesos/php/servicios.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var datatable = $("#servicios").DataTable();
      datatable.ajax.reload();
      //   $.NotificationApp.send("Exito","Recorrido Activado.","bottom-right","success","success");
      if (a != 0) {
        $.toast({
          heading: "Listo!",
          text: "Servicio Activado",
          position: "bottom-right",
          stack: false,
          icon: "success",
        });
      } else {
        $.toast({
          heading: "Listo!",
          text: "Servicio Desactivado",
          position: "bottom-right",
          stack: false,
          icon: "error",
        });
      }
    },
  });
}

function showmodal(i) {
  $("#id_mod_serv").val(i);
  $.ajax({
    data: { Serv_datos: 1, Serv: i },
    url: "Procesos/php/servicios.php",
    type: "post",
    success: function (response) {
      $("#standard-modal-rec").modal("show");
      $("#servicio_ok").css("display", "none");
      $("#servicio_mod_ok").css("display", "block");

      var jsonData = JSON.parse(response);

      $("#myCenterModalLabel_rec").html(
        "MODIFICAR SERVICIO NUMERO " + jsonData.data[0].Codigo
      );
      $("#myCenterModalLabel_rec_1").html(
        "Actualizacion de Datos del Servicio"
      );
      $("#myCenterModalLabel_rec_2").html(
        "Modifique el servicio que será utilizado en ventas"
      );

      $("#servicio_number").val(jsonData.data[0].Codigo);
      $("#servicio_name").val(jsonData.data[0].Titulo);
      $("#servicio_zone").val(jsonData.data[0].Descripcion);
      $("#servicio_km").val(jsonData.data[0].Kilometros);

      //IVA
      // let neto=Number($('#servicio_neto').val());
      let alicuota = jsonData.data[0].Iva;

      if (alicuota == "1.21") {
        // var iva=21;
        var neto = Math.round(
          Number(jsonData.data[0].PrecioVenta) / Number(jsonData.data[0].Iva)
        );
      } else if (jsonData.data[0].Iva == 0) {
        // var iva=0;
        var neto = Number(jsonData.data[0].PrecioVenta);
      }

      let iva = Number(neto * alicuota - neto);
      let valor = Number(neto + iva);

      $("#servicio_iva").val(iva.toFixed(2));
      $("#servicio_precio").val(valor.toFixed(2));

      $("#servicio_neto").val(neto);
      $("#servicio_alicuotaiva").val(jsonData.data[0].Iva);

      // $('#servicio_iva').val(iva);
      $("#servicio_costo").val(jsonData.data[0].PrecioCosto);

      $("#servicio_precio").val(jsonData.data[0].PrecioVenta);
      $("#servicio_descripcion").val(jsonData.data[0].Descripcion);

      $("#standard-modal-rec-header").removeClass(
        "modal-header modal-colored-header bg-success"
      );
      $("#standard-modal-rec-header").addClass(
        "modal-header modal-colored-header bg-warning"
      );
    },
  });
}

$("#servicio_neto").change(function () {
  let neto = Number($("#servicio_neto").val());
  let alicuota = $("#servicio_alicuotaiva").val();

  if (alicuota == "0") {
    var iva = Number(0);
  } else {
    var iva = Number(neto * alicuota - neto);
  }

  let valor = Number(neto + iva);

  $("#servicio_iva").val(iva.toFixed(2));
  $("#servicio_precio").val(valor.toFixed(2));
});

$("#servicio_alicuotaiva").change(function () {
  let neto = Number($("#servicio_neto").val());
  let alicuota = $("#servicio_alicuotaiva").val();

  if (alicuota == "0") {
    var iva = Number(0);
  } else {
    var iva = Number(neto * alicuota - neto);
  }

  let valor = Number(neto + iva);

  $("#servicio_iva").val(iva.toFixed(2));
  $("#servicio_precio").val(valor.toFixed(2));
});

//ACEPTAR AGREGAR SERVICIO

$("#servicio_ok").click(function () {
  var number = $("#servicio_number").val();
  var name = $("#servicio_name").val();
  var descripcion = $("#servicio_descripcion").val();
  var km = $("#recorrido_km").val();
  var precio = $("#servicio_precio").val();
  var alicuota = $("#servicio_alicuotaiva").val();
  var costo = $("#servicio_costo").val();

  $.ajax({
    data: {
      AgregarServicios: 1,
      name: name,
      descripcion: descripcion,
      km: km,
      precio: precio,
      costo: costo,
      number: number,
      alicuota: alicuota,
    },
    url: "Procesos/php/servicios.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      var datatable = $("#servicios").DataTable();
      datatable.ajax.reload();
      $("#standard-modal-rec").modal("hide");
    },
  });
});

//ACEPTAR MODIFICAR RECORRIDO

$("#servicio_mod_ok").click(function () {
  var id = $("#id_mod_serv").val();
  var name = $("#servicio_name").val();
  var alicuota = $("#servicio_alicuotaiva").val();

  var km = $("#servicio_km").val();
  var number = $("#servicio_number").val();
  var descripcion = $("#servicio_descripcion").val();
  var precio = $("#servicio_precio").val();
  var preciocosto = $("#servicio_costo").val();

  $.ajax({
    data: {
      ModificarServicios: 1,
      id: id,
      name: name,
      km: km,
      number: number,
      descripcion: descripcion,
      precio: precio,
      preciocosto: preciocosto,
      alicuota: alicuota,
    },
    url: "Procesos/php/servicios.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var datatable = $("#servicios").DataTable();
        datatable.ajax.reload();

        $.toast({
          heading: "Listo!",
          text: "Servicio Modificado",
          position: "bottom-right",
          stack: false,
          icon: "success",
        });
      } else {
        $.toast({
          heading: "Error!",
          text: "El Servicio no fue modificado",
          position: "bottom-right",
          stack: false,
          icon: "error",
        });
      }

      $("#standard-modal-rec").modal("hide");
    },
  });
});

$("#agregar_prod_btn").click(function () {
  $("#servicio_mod_ok").css("display", "none");
  $("#servicio_ok").css("display", "block");
  $(".form-control").val("");
  $("#standard-modal-rec-header").removeClass(
    "modal-header modal-colored-header bg-warning"
  );
  $("#standard-modal-rec-header").addClass(
    "modal-header modal-colored-header bg-success"
  );
  $("#myCenterModalLabel_rec").html("AGREGAR NUEVO SERVICIO");
  $("#myCenterModalLabel_rec_1").html("Agregue un nuevo Servicio");
  $("#myCenterModalLabel_rec_2").html(
    "Agregue un Servicio para poder utilizar en ventas."
  );

  $("#recorrido_guest").prop("selected", false).trigger("change");
  $("#recorrido_service").prop("selected", false).trigger("change");

  $.ajax({
    data: { Prod_num: 1 },
    url: "Procesos/php/servicios.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#servicio_number").val(jsonData.next_num_prod);
    },
  });
});

$("#btn_acept_inc").click(function () {
  let number = $("#number_inc").val();
  let obs = $("#obs_inc").val();
  let clave = $("#clave_inc").val();

  if (clave == "4986") {
    $.ajax({
      data: { Incrementar_valores: 1, Incremento: number, Observaciones: obs },
      url: "Procesos/php/servicios.php",
      type: "post",
      beforeSend: function (xhr) {
        $("#btn_loading_inc").css("display", "block");
        $("#btn_acept_inc").css("display", "none");
        $("#btn_close_int").css("display", "none");
      },
      success: function (response) {
        var jsonData = JSON.parse(response);

        if (jsonData.success == 1) {
          $("#staticBackdrop").modal("hide");

          var datatable = $("#servicios").DataTable();
          datatable.ajax.reload();

          Swal.fire({
            icon: "success",
            title: "Exito",
            text: "Se actualizaron los importes.",
            confirmButtonColor: "#FF1356",
          });
        } else if (jsonData.success == 2) {
          $("#btn_loading_inc").css("display", "none");
          $("#btn_acept_inc").css("display", "block");
          $("#btn_close_int").css("display", "block");
          // alert('revise el valor del incremento');
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Revise el valor del incremento.",
            confirmButtonColor: "#FF1356",
          });
        } else {
          // alert('no listo');
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se actualizaron los importes.",
            confirmButtonColor: "#dc3545", // rojo Bootstrap
          });
        }
      },
    });
  } else {
    // alert('Error de Autorizacion');
    Swal.fire({
      icon: "error",
      title: "Error de Autorización",
      text: "No tenés permisos para realizar esta acción.",
      confirmButtonColor: "#dc3545", // rojo Bootstrap
    });
  }
});

$("#staticBackdrop").on("hidden.bs.modal", function () {
  $("#clave_inc").val("");
  $("#number_inc").val("");
  $("#obs_inc").val("");

  $("#btn_loading_inc").css("display", "none");
  $("#btn_acept_inc").css("display", "block");
  $("#btn_close_int").css("display", "block");
});
