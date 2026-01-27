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
  // alert(i);

  $("#remove_permanent_warning-header-modal").modal("show");

  $("#btn_remove_permanent").click(function () {
    $.ajax({
      data: { EliminarFijo: 1, id: i },
      url: "Proceso/php/recorridos.php",
      type: "post",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success == 1) {
          var datatable = $("#envios_fijos").DataTable();
          datatable.ajax.reload();
          var datatable_1 = $("#recorridos").DataTable();
          datatable_1.ajax.reload();
          $("#remove_permanent_warning-header-modal").modal("hide");
        }
      },
    });
  });
}

function modificar(i, a) {
  // var a=$(this).attr("data-id");
  // alert(a);

  $.ajax({
    data: { ActivarRecorridos: 1, id: i, Activo: a },
    url: "Proceso/php/recorridos.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
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
    success: function (response) {
      $("#standard-modal-rec").modal("show");
      $("#recorrido_ok").css("display", "none");
      $("#recorrido_mod_ok").css("display", "block");

      var jsonData = JSON.parse(response);

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

      var values = jsonData.data[0].DiaSalida.split(",");

      for (var i = 0; i < values.length; i++) {
        $("#dates").append(
          `<option value="${values[i]}"selected>${values[i]}</option>`,
        );
      }

      const select = document.querySelector("#recorrido_guest");
      const option = document.createElement("option");
      option.setAttribute("selected", true);
      const valor = jsonData.data[0].Cliente;
      option.value = valor;
      option.text = `${jsonData.data[0].Cliente} - ${jsonData.data[0].nombrecliente}  (Dir.:${jsonData.data[0].Direccion})`;
      select.appendChild(option);

      const select_service = document.querySelector("#recorrido_service");
      const option_service = document.createElement("option");
      option_service.setAttribute("selected", true);
      const valor_service = jsonData.data[0].CodigoProductos;
      option_service.value = valor_service;
      option_service.text = `${jsonData.data[0].CodigoProductos} - ${jsonData.data[0].Titulo}  $ ${jsonData.data[0].PrecioVenta}`;
      select_service.appendChild(option_service);

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
    },
    url: "Proceso/php/recorridos.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
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
  var guest = $("#recorrido_guest").val();
  var service = $("#recorrido_service").val();
  var color = $("#recorrido_color").val();
  var id = $("#id_mod_rec").val();
  var dias = $("#dates").val();
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
    },
    url: "Proceso/php/recorridos.php",
    type: "post",
    success: function (response) {
      var jsonData = JSON.parse(response);
      if (jsonData.success == 1) {
        var datatable = $("#recorridos").DataTable();
        datatable.ajax.reload();

        $.toast({
          heading: "Listo!",
          text: "Recorrido Modificado",
          position: "bottom-right",
          stack: false,
          icon: "success",
        });
      } else {
        $.toast({
          heading: "Error!",
          text: "El Recorrido no fue modificado",
          position: "bottom-right",
          stack: false,
          icon: "error",
        });
      }

      $("#standard-modal-rec").modal("hide");
    },
  });
});

$("#agregar_rec_btn").click(function () {
  $("#recorrido_mod_ok").css("display", "none");
  $("#recorrido_ok").css("display", "block");
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
    success: function (response) {
      var jsonData = JSON.parse(response);
      $("#recorrido_number").val(jsonData.next_num_rec);
    },
  });
});

//desde aca ia
// Proceso/js/recorridos.js
// Requiere: jQuery, DataTables, Select2, SweetAlert2

// ---------- Utils ----------
function notify(title, text, type) {
  if (
    window.$ &&
    $.NotificationApp &&
    typeof $.NotificationApp.send === "function"
  ) {
    $.NotificationApp.send(title, text, "bottom-right", "#FFF", type || "info");
  } else if (window.Swal && typeof Swal.fire === "function") {
    Swal.fire({ title, text, icon: type || "info" });
  } else {
    console.log(`[${type || "info"}] ${title} - ${text}`);
  }
}

function ajaxPost(actionData, onDone, onFail) {
  $.ajax({
    url: "Proceso/php/recorridos.php",
    type: "POST",
    dataType: "json",
    data: actionData,
  })
    .done((res) => onDone && onDone(res))
    .fail((xhr) => {
      console.error("AJAX fail", actionData, xhr && xhr.responseText);
      onFail && onFail(xhr);
      notify("Error", "No se pudo procesar la solicitud", "error");
    });
}

// ---------- Carga Selects (sin PHP embebido) ----------
function loadClientesSelect() {
  ajaxPost({ ListarClientes: 1 }, (res) => {
    const $sel = $("#recorrido_guest");
    $sel.empty().append('<option value="">Seleccionar un Cliente</option>');
    (res?.data || []).forEach((c) => {
      $sel.append(
        `<option value="${c.id}">${c.id} - ${c.nombrecliente} (Dir.: ${
          c.Direccion || "-"
        })</option>`,
      );
    });
    $sel.trigger("change");
  });
}

function loadServiciosSelect() {
  ajaxPost({ ListarServicios: 1 }, (res) => {
    const $sel = $("#recorrido_service");
    $sel.empty().append('<option value="">Seleccionar un Servicio</option>');
    (res?.data || []).forEach((s) => {
      $sel.append(
        `<option value="${s.Codigo}">${s.Codigo} - ${s.Titulo} $ ${s.PrecioVenta}</option>`,
      );
    });
    $sel.trigger("change");
  });
}

// ---------- DataTable de recorridos ----------
let dtRecorridos = null;

function renderEstadoBadge(v) {
  const on = String(v).toLowerCase() === "activo" || v === 1;
  return `<span class="badge ${on ? "bg-success" : "bg-secondary"}">${
    on ? "Activo" : "Inactivo"
  }</span>`;
}

function renderColorDot(hex) {
  return `<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:${
    hex || "#999"
  };border:1px solid #ddd"></span>`;
}

function initTablaRecorridos() {
  dtRecorridos = $("#recorridos").DataTable({
    ajax: {
      url: "Proceso/php/recorridos.php",
      type: "POST",
      data: { ListarRecorridos: 1 },
      dataSrc: (res) => res?.data || [],
    },
    destroy: true,
    responsive: true,
    fixedHeader: true,
    columns: [
      { data: "Numero" },
      { data: "Nombre" },
      { data: "Kilometros" },
      { data: "Peajes" },
      { data: "Servicio" },
      { data: "EnviosFijos", defaultContent: 0 },
      {
        data: "DiasSalida",
        render: (d) => (Array.isArray(d) ? d.join(", ") : d || "-"),
      },
      { data: "Color", render: (d) => renderColorDot(d) },
      { data: "Estado", render: renderEstadoBadge },
      {
        data: null,
        orderable: false,
        render: (row) => `
          <div class="btn-group">
            <button class="btn btn-sm btn-primary btn-editar" data-id="${row.id}"><i class="mdi mdi-pencil"></i></button>
            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id}" data-nombre="${row.Nombre}"><i class="mdi mdi-trash-can-outline"></i></button>
            <button class="btn btn-sm btn-info btn-fijos" data-id="${row.id}" data-nombre="${row.Nombre}"><i class="mdi mdi-table-plus"></i></button>
          </div>
        `,
      },
    ],
  });
}

function reloadTabla() {
  dtRecorridos && dtRecorridos.ajax.reload(null, false);
}

// ---------- Modal Alta/Edición ----------
function openModalNuevo() {
  $("#myCenterModalLabel_rec").text("AGREGAR NUEVO RECORRIDO");
  $("#standard-modal-rec-header")
    .removeClass("bg-warning")
    .addClass("bg-success");
  $("#modal-rec-form")[0].reset();
  $("#id_mod_rec").val("");
  $("#recorrido_mod_ok").hide();
  $("#recorrido_ok").show();
  $("#standard-modal-rec").modal("show");
}

function openModalEditar(id) {
  ajaxPost({ ObtenerRecorrido: 1, id: id }, (res) => {
    const r = res?.data;
    if (!r) return notify("Atención", "No se encontró el recorrido", "warning");
    $("#myCenterModalLabel_rec").text("MODIFICAR RECORRIDO");
    $("#standard-modal-rec-header")
      .removeClass("bg-success")
      .addClass("bg-warning");
    $("#id_mod_rec").val(r.id || "");
    $("#recorrido_number").val(r.Numero || "");
    $("#recorrido_name").val(r.Nombre || "");
    $("#recorrido_zone").val(r.Zona || "");
    $("#recorrido_km").val(r.Kilometros || "");
    $("#recorrido_toll").val(r.Peajes || "");
    $("#recorrido_color").val(r.Color || "#999999");
    $("#recorrido_guest")
      .val(r.idCliente || "")
      .trigger("change");
    $("#recorrido_service")
      .val(r.CodigoServicio || "")
      .trigger("change");
    // días salida
    if (Array.isArray(r.DiasSalida)) {
      $("#dates").val(r.DiasSalida).trigger("change");
    }
    // fijo switch
    $("#fijo_switch").prop("checked", !!r.Fijo);
    $("#recorrido_ok").hide();
    $("#recorrido_mod_ok").show();
    $("#standard-modal-rec").modal("show");
  });
}

function recogerFormRecorrido() {
  const dias = $("#dates").val() || [];
  return {
    Numero: $("#recorrido_number").val().trim(),
    Nombre: $("#recorrido_name").val().trim(),
    Zona: $("#recorrido_zone").val().trim(),
    Kilometros: $("#recorrido_km").val().trim(),
    Peajes: $("#recorrido_toll").val().trim(),
    Color: $("#recorrido_color").val(),
    idCliente: $("#recorrido_guest").val() || "",
    CodigoServicio: $("#recorrido_service").val() || "",
    DiasSalida: dias, // array
    Fijo: $("#fijo_switch").is(":checked") ? 1 : 0,
  };
}

// Guardar nuevo
$("#recorrido_ok").on("click", function () {
  const payload = recogerFormRecorrido();
  if (!payload.Numero || !payload.Nombre)
    return notify(
      "Faltan datos",
      "Número y Nombre son obligatorios",
      "warning",
    );
  ajaxPost({ CrearRecorrido: 1, ...payload }, (res) => {
    if (res?.ok) {
      notify("Éxito", "Recorrido creado", "success");
      $("#standard-modal-rec").modal("hide");
      reloadTabla();
    } else {
      notify("Error", res?.msg || "No se pudo crear", "error");
    }
  });
});

// Guardar edición
$("#recorrido_mod_ok").on("click", function () {
  const id = $("#id_mod_rec").val();
  if (!id) return notify("Error", "ID inválido", "error");
  const payload = recogerFormRecorrido();
  ajaxPost({ ModificarRecorrido: 1, id, ...payload }, (res) => {
    if (res?.ok) {
      notify("Éxito", "Recorrido actualizado", "success");
      $("#standard-modal-rec").modal("hide");
      reloadTabla();
    } else {
      notify("Error", res?.msg || "No se pudo actualizar", "error");
    }
  });
});

// Abrir modal nuevo
$("#agregar_rec_btn").on("click", function () {
  openModalNuevo();
});

// Click editar / eliminar / fijos en tabla
$(document).on("click", ".btn-editar", function () {
  openModalEditar($(this).data("id"));
});

$(document).on("click", ".btn-eliminar", function () {
  const id = $(this).data("id");
  const nombre = $(this).data("nombre");
  $("#id_eliminar").val(id);
  $("#warning-modal-body").html(`¿Eliminar el recorrido <b>${nombre}</b>?`);
  $("#warning-modal").modal("show");
});

$("#warning-modal-ok").on("click", function () {
  const id = $("#id_eliminar").val();
  ajaxPost({ EliminarRecorrido: 1, id }, (res) => {
    if (res?.ok) {
      notify("Eliminado", "El recorrido fue eliminado", "success");
      $("#warning-modal").modal("hide");
      reloadTabla();
    } else {
      notify("Error", res?.msg || "No se pudo eliminar", "error");
    }
  });
});

// ---------- Servicios Fijos (modal bs-fijos-modal-lg) ----------
$(document).on("click", ".btn-fijos", function () {
  const idRec = $(this).data("id");
  $("#bs-fijos-modal-lg").data("idRec", idRec).modal("show");
  listarFijos(idRec);
  cargarServiciosUnicos(idRec); // carga el select de “Seleccione un Servicio”
});

function listarFijos(idRec) {
  ajaxPost({ ListarFijos: 1, idRecorrido: idRec }, (res) => {
    const tbody = $("#envios_fijos tbody");
    tbody.empty();
    (res?.data || []).forEach((r) => {
      tbody.append(`<tr>
        <td>${r.Origen || "-"}</td>
        <td>${r.Destino || "-"}</td>
        <td><button class="btn btn-xs btn-danger btn-fijo-del" data-id="${
          r.id
        }"><i class="mdi mdi-trash-can-outline"></i></button></td>
      </tr>`);
    });
  });
}

// Rellena el select del modal con servicios “única vez” del recorrido
function cargarServiciosUnicos(idRec) {
  ajaxPost({ ListarServiciosUnicos: 1, idRecorrido: idRec }, (res) => {
    const $sel = $("#bs-fijos-modal-lg select.select2");
    $sel.empty().append('<option value="">Seleccionar Servicio</option>');
    (res?.data || []).forEach((s) => {
      const servicio = s.Retirado == 0 ? "Retiro" : "Entrega";
      $sel.append(
        `<option value="${s.id}">Origen: ${s.Origen} Destino: <b>${s.Destino}</b> $ ${s.Debe} ${s.EntregaEn} ${servicio}</option>`,
      );
    });
    $sel.trigger("change");
  });
}

// Agregar a fijos (icono plus del modal)
$("#sumar, .mdi-table-plus").on("click", function () {
  const idRec = $("#bs-fijos-modal-lg").data("idRec");
  const idServicio = $("#bs-fijos-modal-lg select.select2").val();
  if (!idRec || !idServicio)
    return notify("Atención", "Seleccione un Servicio", "warning");
  ajaxPost({ AgregarFijo: 1, idRecorrido: idRec, idServicio }, (res) => {
    if (res?.ok) {
      notify("Éxito", "Servicio fijo agregado", "success");
      listarFijos(idRec);
      reloadTabla();
    } else {
      notify("Error", res?.msg || "No se pudo agregar", "error");
    }
  });
});

// Eliminar fijo (modal “remove_permanent_warning-header-modal”)
let _fijoAEliminar = null;
$(document).on("click", ".btn-fijo-del", function () {
  _fijoAEliminar = $(this).data("id");
  $("#remove_permanent_warning-header-modal").modal("show");
});

$("#btn_remove_permanent").on("click", function () {
  if (!_fijoAEliminar) return;
  ajaxPost({ EliminarFijo: 1, id: _fijoAEliminar }, (res) => {
    if (res?.ok) {
      notify("Eliminado", "Servicio fijo eliminado", "success");
      $("#remove_permanent_warning-header-modal").modal("hide");
      const idRec = $("#bs-fijos-modal-lg").data("idRec");
      listarFijos(idRec);
      reloadTabla();
    } else {
      notify("Error", res?.msg || "No se pudo eliminar", "error");
    }
  });
});

$("#btn_not_remove_permanent").on("click", function () {
  _fijoAEliminar = null;
  $("#remove_permanent_warning-header-modal").modal("hide");
});

// ---------- Ready ----------
$(function () {
  // Enlazar Select2 si está cargado
  if ($.fn.select2) {
    $("#recorrido_guest, #recorrido_service, #dates").select2({
      width: "100%",
    });
  }
  loadClientesSelect();
  loadServiciosSelect();
  initTablaRecorridos();
});
