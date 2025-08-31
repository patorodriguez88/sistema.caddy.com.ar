const table = $("#preventa").DataTable({
  paging: false,
  searching: false,
  ajax: { url: "Procesos/php/preventa.php", data: { datos: 1 }, type: "post" },
  columns: [
    {
      data: "RazonSocial",
      render: (d, t, r) =>
        `<b>${r.RazonSocial}</b><br>
         <i class="mdi mdi-18px mdi-map-marker text-success"></i>
         <span class="text-muted">${r.DomicilioOrigen}</span>`,
    },
    {
      data: "idProveedor",
      render: (d, t, r) =>
        `<b>[${r.idProveedor}] ${r.ClienteDestino}</b><br>
         <i class="mdi mdi-18px mdi-map-marker text-success"></i>
         <span class="text-muted">${r.DomicilioDestino} ${r.LocalidadDestino}</span>`,
    },
    { data: "Fecha" },
    { data: "Observaciones" },
    { data: "Precio" },
    { data: "Cantidad" },
    { data: "Total" },
    {
      data: "Recorrido",
      render: (d, t, r) => {
        const label = r.Recorrido ? r.Recorrido : "Ingrese un Recorrido";
        return `<h5><a onclick="modificarrecorrido(${r.id});" class="badge badge-primary">${label}</a></h5>`;
      },
    },
    { data: "Cobranza" },
    {
      data: "id",
      render: (d, t, r) =>
        `<a class='action-icon' onclick='eliminar(${r.id})'><i class='uil-trash-alt'></i></a>`,
    },
    {
      data: "id",
      orderable: false,
      render: (d, t, r) => {
        const checked = r.Recorrido ? "checked" : "";
        // quitá el onclick return true/false que bloquea el click
        return `
          <div class="custom-control custom-checkbox custom-checkbox-success mb-2">
            <input name="checkbox_r" type="checkbox" class="custom-control-input"
                   value="${r.id}" data-id="${r.Recorrido || ""}" id="chk_${
          r.id
        }" ${checked}>
            <label class="custom-control-label" for="chk_${r.id}"></label>
          </div>`;
      },
    },
  ],
  select: { style: "os", selector: "td:not(:last-child)" },
  rowCallback: function (row, data) {
    $(row).find(".custom-control-input").prop("checked", !!data.Recorrido);
  },
});
// Sincroniza el checkbox del encabezado con los de las filas
table.on("draw", function () {
  const allChecked =
    $(table.rows({ search: "applied" }).nodes()).find(
      "input.custom-control-input"
    ).length > 0 &&
    $(table.rows({ search: "applied" }).nodes()).find(
      "input.custom-control-input:checked"
    ).length ===
      $(table.rows({ search: "applied" }).nodes()).find(
        "input.custom-control-input"
      ).length;
  $("#customCheck1").prop("checked", allChecked);
});

$("#customCheck1").on("change", function () {
  const marcar = this.checked;
  $(table.rows({ search: "applied" }).nodes())
    .find("input.custom-control-input")
    .prop("checked", marcar);
});

function revisar(r) {
  if (r) {
    // return true;
  } else {
    // alert(r);
    return false;
  }
}

function eliminar(r) {
  $.ajax({
    data: { EliminarPreventa: 1, id: r },
    type: "POST",
    url: "Procesos/php/preventa.php",
    success: function (response) {
      var datatable = $("#preventa").DataTable();
      datatable.ajax.reload();
      $.NotificationApp.send(
        "Registro Eliminado !",
        "Se ha eliminado el registro de Pre Ventas.",
        "bottom-right",
        "#FFFFFF",
        "success"
      );
    },
  });
}

$("#aceptar_preventas").click(function (e) {
  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  var recorridos = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
      recorridos.push($(this).attr("data-id"));
    }
  });

  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  if (checked != 0) {
    $.ajax({
      data: { recorrido_t: recorridos, id: checked },
      type: "POST",
      url: "AgregarRepoVentaWeb.php",
      beforeSend: function () {
        $("#info-alert-modal").modal("show");
      },
      success: function (response) {
        var datatable = $("#preventa").DataTable();
        datatable.ajax.reload();
        enviar_webhook_woocomerce(checked);
        enviar_webhook_tiendanube(checked);

        $("#info-alert-modal").modal("hide");
      },
    });
    $("#info-alert-modal").modal("hide");
  } else {
    $.NotificationApp.send(
      "No hay Registros Seleccionados !",
      "No se han actualizado registros.",
      "bottom-right",
      "#FFFFFF",
      "danger"
    );
  }
});

function modificarrecorrido(i) {
  $("#cs_modificar_REC").val(i);
  $.ajax({
    data: { BuscarRecorridos: 1, cs: i },
    type: "POST",
    url: "Procesos/php/preventa.php",
    success: function (response) {
      $(".selector-recorrido select").html(response).fadeIn();
    },
  });

  $("#myCenterModalLabel_rec").html("Modificar Recorrido a Código " + i);
  $("#standard-modal-rec").modal("show");
  $("#modificarrecorrido_ok").css("display", "block");
  $("#modificarrecorrido_all_ok").css("display", "none");
}

// DESDE ACA ELIMINAR _ ALL

$("#eliminar_recorrido_all").click(function () {
  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];

  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
    }
  });

  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  if (checked != 0) {
    // modificarrecorrido();
    $("#myCenterModalLabel_rec").html(
      "Eliminar " + checked.length + " registros seleccionados"
    );
    $("#standard-modal-rec").modal("show");
    $("#modificarrecorrido_ok").css("display", "none");
    $("#modificarrecorrido_all_ok").css("display", "none");
    $("#query_selector_recorrido_t").css("display", "none");
    $("#eliminarrecorrido_all_ok").css("display", "block");
    $(".modal-header.modal-colored-header.bg-primary").removeClass(
      "bg-primary"
    );
    $(".modal-header.modal-colored-header").addClass("bg-danger");
  }

  //BOTON ACEPTAR ELIMINAR _ ALL
  $("#eliminarrecorrido_all_ok")
    .off("click")
    .on("click", function () {
      $.ajax({
        data: { Eliminar_all: 1, id: checked },
        type: "POST",
        url: "Procesos/php/preventa.php",
        success: function (response) {
          var jsonData = JSON.parse(response);
          if (jsonData.success == 1) {
            var datatable = $("#preventa").DataTable();
            datatable.ajax.reload();
            $("#standard-modal-rec").modal("hide");
            $.NotificationApp.send(
              "Registros Eliminados !",
              "Se han Eliminado todos los registros seleccionados.",
              "bottom-right",
              "#FFFFFF",
              "success"
            );
          } else {
            $.NotificationApp.send(
              "Registro No Eliminados !",
              "No pudimos eliminar los registros seleccionados.",
              "bottom-right",
              "#FFFFFF",
              "danger"
            );
          }
        },
      });
    });
});
// DESDE ACA MODIFICAR _ ALL

$("#modificar_recorrido_all").click(function () {
  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  var recorridos = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function () {
    //Mediante la función push agregamos al arreglo los values de los checkbox
    if ($(this).attr("value") != null) {
      checked.push($(this).attr("value"));
      recorridos.push($(this).attr("data-id"));
    }
  });

  // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo

  if (checked != 0) {
    modificarrecorrido();
    $("#myCenterModalLabel_rec").html(
      "Cambiar de Recorrido " + checked.length + " registros seleccionados"
    );
    $("#standard-modal-rec").modal("show");
    $("#modificarrecorrido_ok").css("display", "none");
    $("#eliminarrecorrido_all_ok").css("display", "none");
    $("#query_selector_recorrido_t").css("display", "block");

    $("#modificarrecorrido_all_ok").css("display", "block");
    $(".modal-header.modal-colored-header.bg-danger").removeClass("bg-danger");
    $(".modal-header.modal-colored-header").addClass("bg-primary");
  }

  //BOTON GUARDAR CAMBIOS EN MODIFICAR RECORRIDOS _ ALL
  $("#modificarrecorrido_all_ok")
    .off("click")
    .on("click", function () {
      // Obtengo el recorrdido seleccionado
      var r = $("#recorrido_t").val();
      $.ajax({
        data: { ActualizaRecorrido_all: 1, r: r, id: checked },
        type: "POST",
        url: "Procesos/php/preventa.php",
        success: function (response) {
          var jsonData = JSON.parse(response);
          if (jsonData.success == 1) {
            var datatable = $("#preventa").DataTable();
            datatable.ajax.reload();
            $("#standard-modal-rec").modal("hide");
            $.NotificationApp.send(
              "Registros Actualizados !",
              "Se ha actualizado al nuevo recorrido todos los registros seleccionados.",
              "bottom-right",
              "#FFFFFF",
              "success"
            );
          } else {
            $.NotificationApp.send(
              "Registro No Actualizado !",
              "No pudimos actualizar los Recorridos.",
              "bottom-right",
              "#FFFFFF",
              "danger"
            );
          }
        },
      });
    });
});

$("#modificarrecorrido_ok").click(function () {
  var cs = $("#cs_modificar_REC").val();
  var r = $("#recorrido_t").val();

  $.ajax({
    data: { ActualizaRecorrido: 1, r: r, id: cs },
    type: "POST",
    url: "Procesos/php/preventa.php",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var datatable = $("#preventa").DataTable();
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
