$("#dates").change(function () {
  var array = $("#dates").val();
  console.log(array);
});

var datatable = $("#recorridos").DataTable({
  dom: "Bfrtip",
  buttons: ["pageLength", "copy", "excel", "pdf"],
  destroy: true, // evita doble init al volver a la vista
  processing: true,
  deferRender: true,
  paging: true,
  searching: true,
  responsive: false, // apágalo (no object), así no hay child-rows
  lengthMenu: [
    [10, 25, 50, -1],
    [10, 25, 50, "All"],
  ],
  ajax: {
    url: "Proceso/php/recorridos.php",
    type: "POST",
    data: { Recorridos: 1 },
    dataSrc: "data",
    error: (xhr) => console.error("AJAX ERROR:", xhr.status, xhr.responseText),
  },
  columnDefs: [
    { className: "align-middle", targets: "_all" },
    {
      targets: 0, // Numero
      width: "10px",
      className: "text-center align-middle",
    },
    { orderable: false, targets: [7] },
  ],
  order: [[0, "asc"]],
  language: {
    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
  },
  columns: [
    {
      data: "Numero",
      defaultContent: "",
      render: (d, t, r) =>
        `<span class="${r.Activo == 0 ? "text-muted" : "text-dark"}">${
          d ?? ""
        }</span>`,
    },
    {
      data: "Nombre",
      defaultContent: "",
      render: (d, t, r) => {
        const c = r.Activo == 0 ? "muted" : "success";
        return `<b>${
          d ?? ""
        }</b><br><i class="mdi mdi-18px mdi-map-marker text-${c}"></i> <span class="text-muted">${
          r.Zona ?? ""
        }</span>`;
      },
    },
    {
      data: "Kilometros",
      defaultContent: 0,
      render: (d, t, r) => {
        const c = r.Activo == 0 ? "muted" : "success";

        return `
      <div class="d-flex flex-column lh-sm">
        <div>
          <i class="mdi mdi-18px mdi-map-marker-distance text-${c} me-1"></i>
          <b>${d ?? 0}</b> km
        </div>
        <div class="text-muted small">
          <i class="mdi mdi-18px mdi-cash-marker me-1"></i>
          ${r.Peajes ?? 0} peajes
        </div>
      </div>
    `;
      },
    },
    {
      data: null,
      defaultContent: "",
      render: (d, t, r) => {
        const badge = r.Activo == 0 ? "bg-secondary" : "bg-success";
        return `
          <div class="table-action">
            <b>${r.nombrecliente ?? ""}</b><br>
            <a class="text-primary" data-bs-toggle="modal" data-bs-target="#modal_seguimiento"
               data-id="${r.nombrecliente ?? ""}" data-title="${
                 r.CodigoProductos ?? ""
               }" data-fieldname="${r.CodigoProductos ?? ""}">
               <b>${r.CodigoProductos ?? ""}</b>
            </a><br>
            <span class="badge ${badge}"> $ ${r.PrecioVenta ?? 0}</span>
          </div>`;
      },
    },
    {
      data: "Total",
      defaultContent: 0,
      render: (d, t, r) =>
        `<span style="cursor:pointer" id="${
          r.Numero
        }" onclick="ver_fijos(this.id);" class="badge bg-primary rounded-pill">${
          d ?? 0
        }</span>`,
    },
    {
      data: "DiaSalida",
      defaultContent: "",
      render: (d) => {
        // limpia casos como ",,,,,"
        const dias = String(d || "")
          .split(",")
          .map((s) => s.trim())
          .filter(Boolean);
        return dias.length ? dias.join(", ") : "-";
      },
    },
    {
      data: "Color",
      defaultContent: "",
      render: (d, t, r) => {
        if (r.Activo == 0) return `<i class="mdi mdi-truck text-muted"></i>`;
        const hex = String(d || "").replace(/^#/, "");
        return `<i class="mdi mdi-18px mdi-truck" style="color:#${hex}"></i>`;
      },
    },
    // {
    //   data: "Activo",
    //   defaultContent: 0,
    //   render: (d) => {
    //     const on = Number(d) === 1;
    //     return `<h5><span class="badge ${on ? "bg-success" : "bg-danger"}"><b>${
    //       on ? "Activo" : "Inactivo"
    //     }</b></span></h5>`;
    //   },
    // },
    {
      data: "id",
      orderable: false,
      render: (d, t, r) => {
        const icon =
          r.Activo == 1 ? "mdi-eye text-success" : "mdi-eye-off text-danger";
        return `
          <div class="table-action">
            <a id="${r.id}" onclick="modificar(this.id, ${r.Activo});" class="action-icon"><i class="mdi ${icon}"></i></a>
            <a onclick="showmodal(${r.id});" class="action-icon"><i class="mdi mdi-border-color text-warning"></i></a>
          </div>`;
      },
    },
  ],
});
// por si algo externo abrió childs, cierralos en cada draw
$("#recorridos").on("preDraw.dt", function () {
  $("#recorridos tbody tr.child").remove();
  $("#recorridos tbody tr").removeClass("parent");
});
function ver_fijos(i) {
  $("#bs-fijos-modal-lg").modal("show");

  var datatable = $("#envios_fijos").DataTable();
  datatable.destroy();

  var datatable = $("#envios_fijos").DataTable({
    paging: true,
    searching: true,
    ajax: {
      url: "Proceso/php/recorridos.php",
      data: { VerFijos: 1, id: i },
      processing: true,
      type: "post",
    },
    columns: [
      { data: "nombre1" },
      { data: "nombre2" },
      {
        data: "id",

        render: function (data, type, row) {
          return `<td class="table-action col-xs-3"><a style="cursor:pointer" id="${row.id}" onclick="eliminar_fijo(this.id);" ><i class="mdi mdi-18px mdi-trash-can-outline"></i>`;
        },
      },
    ],
  });
}

function eliminar_fijo(i) {
  $("#remove_permanent_warning-header-modal").modal("show");

  $("#btn_remove_permanent")
    .off("click")
    .on("click", function () {
      $.ajax({
        data: { EliminarFijo: 1, id: i },
        url: "Proceso/php/recorridos.php",
        type: "post",
        dataType: "json",
        success: function (jsonData) {
          if (jsonData.success == 1) {
            $("#envios_fijos").DataTable().ajax.reload();
            $("#recorridos").DataTable().ajax.reload();
            $("#remove_permanent_warning-header-modal").modal("hide");
          }
        },
      });
    });
}

function modificar(i, a) {
  $.ajax({
    data: { ActivarRecorridos: 1, id: i, Activo: a },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      var datatable = $("#recorridos").DataTable();
      datatable.ajax.reload();
      //   $.NotificationApp.send("Exito","Recorrido Activado.","bottom-right","success","success");
      if (a != 1) {
        $.toast({
          heading: "Listo!",
          text: "Recorrido Activado",
          position: "bottom-right",
          stack: false,
          icon: "success",
        });
      } else {
        $.toast({
          heading: "Listo!",
          text: "Recorrido Desactivado",
          position: "bottom-right",
          stack: false,
          icon: "error",
        });
      }
    },
  });
}

function showmodal(i) {
  $("#id_mod_rec").val(i);

  $.ajax({
    data: { Rec_datos: 1, Rec: i },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $("#standard-modal-rec").modal("show");
      $("#recorrido_ok").addClass("d-none");
      $("#recorrido_mod_ok").removeClass("d-none");
      // Obtén la referencia al elemento del interruptor
      var switchElement = document.getElementById("fijo_switch");

      if (jsonData.data[0].Fijo == 0) {
        // Cambia el estado del interruptor a "false"
        switchElement.checked = false;
      } else {
        // Cambia el estado del interruptor a "false"
        switchElement.checked = true;
      }

      $("#recorrido_number").val(jsonData.data[0].Numero);
      $("#recorrido_name").val(jsonData.data[0].Nombre);
      $("#recorrido_zone").val(jsonData.data[0].Zona);
      $("#recorrido_km").val(jsonData.data[0].Kilometros);
      $("#recorrido_toll").val(jsonData.data[0].Peajes);

      $("#standard-modal-rec-header").removeClass(
        "modal-header modal-colored-header bg-success",
      );
      $("#standard-modal-rec-header").addClass(
        "modal-header modal-colored-header bg-warning",
      );
      $("#myCenterModalLabel_rec").html(
        "MODIFICAR RECORRIDO NUMERO " + jsonData.data[0].Numero,
      );

      var values = String(jsonData.data[0].DiaSalida || "")
        .split(",")
        .map((v) => v.trim())
        .filter(Boolean);

      $("#dates").val(values).trigger("change");
      $("#recorrido_guest")
        .val(jsonData.data[0].Cliente || "")
        .trigger("change");
      $("#recorrido_service")
        .val(jsonData.data[0].CodigoProductos || "")
        .trigger("change");

      $("#recorrido_color").val("#" + jsonData.data[0].Color);
    },
  });
}

//ACEPTAR AGREGAR RECORRIDO

$("#recorrido_ok").click(function () {
  var number = $("#recorrido_number").val();
  var name = $("#recorrido_name").val();
  var zone = $("#recorrido_zone").val();
  var km = $("#recorrido_km").val();
  var toll = $("#recorrido_toll").val();
  var guest = $("#recorrido_guest").val();
  var service = $("#recorrido_service").val();
  var color = $("#recorrido_color").val();
  var fijo = $("#fijo_switch").is(":checked") ? 1 : 0;
  $.ajax({
    data: {
      AgregarRecorridos: 1,
      name: name,
      zone: zone,
      km: km,
      toll: toll,
      guest: guest,
      service: service,
      color: color,
      number: number,
      fijo: fijo,
    },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      var datatable = $("#recorridos").DataTable();
      datatable.ajax.reload();
      $("#standard-modal-rec").modal("hide");
    },
  });
});

//ACEPTAR MODIFICAR RECORRIDO

$("#recorrido_mod_ok").click(function () {
  var number = $("#recorrido_number").val();
  var name = $("#recorrido_name").val();
  var zone = $("#recorrido_zone").val();
  var km = $("#recorrido_km").val();
  var toll = $("#recorrido_toll").val();
  var guest = $("#recorrido_guest").val() || 0;
  var service = $("#recorrido_service").val() || 0;
  var color = $("#recorrido_color").val();
  var id = $("#id_mod_rec").val();
  var dias = $("#dates").val();
  var fijo = $("#fijo_switch").is(":checked") ? 1 : 0;
  $.ajax({
    data: {
      ModificarRecorridos: 1,
      id: id,
      name: name,
      zone: zone,
      km: km,
      toll: toll,
      guest: guest,
      service: service,
      color: color,
      number: number,
      dias: dias,
      fijo: fijo,
    },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      if (jsonData.success == 1) {
        var datatable = $("#recorridos").DataTable();
        datatable.ajax.reload();
        toast("success", "Éxito", "Recorrido modificado");
      } else {
        toast("error", "Error", "El Recorrido no fue modificado");
      }

      $("#standard-modal-rec").modal("hide");
    },
  });
});

$("#agregar_rec_btn").click(function () {
  $("#dates").val(null).trigger("change");

  $("#recorrido_mod_ok").addClass("d-none");
  $("#recorrido_ok").removeClass("d-none");
  $(".form-control").val("");
  $("#standard-modal-rec-header").removeClass(
    "modal-header modal-colored-header bg-warning",
  );
  $("#standard-modal-rec-header").addClass(
    "modal-header modal-colored-header bg-success",
  );
  $("#myCenterModalLabel_rec").html("AGREGAR NUEVO RECORRIDO");

  $("#recorrido_guest").prop("selected", false).trigger("change");
  $("#recorrido_service").prop("selected", false).trigger("change");

  $.ajax({
    data: { Rec_num: 1 },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $("#recorrido_number").val(jsonData.next_num_rec);
    },
  });
});
$(document).ready(function () {
  if ($.fn.select2) {
    $("#dates").select2({
      width: "100%",
      placeholder: "Elegí uno o más días",
      allowClear: true,
      dropdownParent: $("#standard-modal-rec"),
    });

    $("#recorrido_guest").select2({
      width: "100%",
      dropdownParent: $("#standard-modal-rec"),
    });

    $("#recorrido_service").select2({
      width: "100%",
      dropdownParent: $("#standard-modal-rec"),
    });
  }

  cargarClientes();
  cargarServicios();
});
function cargarClientes() {
  $.ajax({
    data: { ListarClientes: 1 },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $("#recorrido_guest").empty();
      $("#recorrido_guest").append(
        `<option value="">Seleccionar un Cliente</option>`,
      );

      if (jsonData.data) {
        $.each(jsonData.data, function (i, item) {
          $("#recorrido_guest").append(
            `<option value="${item.id}">${item.id} - ${item.nombrecliente} (Dir.: ${item.Direccion || ""})</option>`,
          );
        });
      }

      $("#recorrido_guest").trigger("change");
    },
  });
}

function cargarServicios() {
  $.ajax({
    data: { ListarServicios: 1 },
    url: "Proceso/php/recorridos.php",
    type: "post",
    dataType: "json",
    success: function (jsonData) {
      $("#recorrido_service").empty();
      $("#recorrido_service").append(
        `<option value="">Seleccionar un Servicio</option>`,
      );

      if (jsonData.data) {
        $.each(jsonData.data, function (i, item) {
          $("#recorrido_service").append(
            `<option value="${item.Codigo}">${item.Codigo} - ${item.Titulo} $ ${item.PrecioVenta}</option>`,
          );
        });
      }

      $("#recorrido_service").trigger("change");
    },
  });
}
