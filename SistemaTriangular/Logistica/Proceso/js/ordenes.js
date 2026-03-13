//CARGAR ORDEN
// Abrir modal desde el ícono de la tabla
$("#ordenes").on("click", ".btn-cargar-orden", function (e) {
  e.preventDefault();
  const no = $(this).data("no");
  abrirModalCargar(no);
});

// Si llamás desde un onclick en el ícono, pasá también "this": abrirModalCargar(row.NumerodeOrden, this)
// O mejor: usá delegación y pasá el elemento en el handler.
async function abrirModalCargar(no, el) {
  const modalEl = document.getElementById("modalCargarOrden");
  if (!modalEl) return console.error("No existe #modalCargarOrden");
  const modal = new bootstrap.Modal(modalEl);

  // UI inicial
  $("#cargarOrdenNO").text(no);
  $("#orden_t").val(no);

  const dt = $("#ordenes").DataTable();
  const def = (v, alt = "") => (v !== undefined && v !== null ? v : alt);
  const setVal = (sel, v = "") => $(sel).val(v ?? "");
  const setText = (sel, v = "") => $(sel).text(v ?? "");

  // Loader suave en el modal
  const $body = $(modalEl).find(".modal-body");
  const stopLoader = addLoader($body); // devuelve función para quitar loader

  // Buscar fila en DataTables
  let row = null;
  try {
    if (el) {
      row = dt.row($(el).closest("tr")).data() || null;
    } else {
      const all = dt.rows().data().toArray();
      row = all.find((r) => String(r?.NumerodeOrden) === String(no)) || null;
    }
  } catch (e) {
    // si falló, seguimos con null
  }

  // Prefill con lo que haya en la tabla
  let pre = {
    Patente: def(row?.Dominio),
    Chofer: def(row?.NombreChofer),
    Recorrido: def(row?.Recorrido),
    Kilometros: def(row?.Kilometros),
    Observaciones: def(row?.Observaciones),
    Acompanante: def(row?.NombreChofer2),
    CombustibleSalida: def(row?.CombustibleSalida),
    TarjetaCombustible: def(row?.TarjetaCombustible),
  };

  // Endpoints AJAX
  const fetchKmPorPatente = (patente) => {
    if (!patente) return Promise.resolve(null);
    return $.ajax({
      url: "Proceso/php/ordenes.php",
      type: "POST",
      dataType: "json",
      data: { BuscarKmVehiculo: 1, Patente: String(patente).trim() },
    });
  };

  const fetchTotalRecorrido = (orden) => {
    if (!orden) return Promise.resolve(null);
    return $.ajax({
      url: "Proceso/php/ordenes.php",
      type: "POST",
      dataType: "json",
      data: { MonetizarRecorrido: 1, Orden: String(orden).trim() },
    });
  };

  // Guard para evitar pisar valores si el usuario cambia patente mientras se consulta
  let patenteQueryId = 0;
  async function refreshKmDesdePatente(pat) {
    const myId = ++patenteQueryId;
    try {
      const v = await fetchKmPorPatente(pat);
      // sólo aplica si esta respuesta es la más nueva
      if (myId !== patenteQueryId) return;
      if (v && v.success == 1) {
        if (v.Kilometros != null) setVal("#kilometros_t", v.Kilometros);
        if (v.NivelCombustible != null)
          setVal("#combustible_t", v.NivelCombustible);

        if (Number(v.Aliados) === 0) $("#briefing_checks").show();
        else $("#briefing_checks").hide();

        if (v.Marca || v.Modelo) {
          setText(
            "#datos_vehiculo",
            `${v.Marca ?? ""} ${v.Modelo ?? ""}`.trim(),
          );
        }
      }
    } catch {
      /* no rompe la UI */
    }
  }

  // Seteo inicial en el formulario
  setVal("#patente_t", pre.Patente);
  setVal("#chofer_t", pre.Chofer);
  setVal("#recorrido_t", pre.Recorrido);
  setVal("#kilometros_t", pre.Kilometros);
  setVal("#observaciones_t", pre.Observaciones);
  setVal("#acompanante_t", pre.Acompanante);
  setVal("#combustible_t", pre.CombustibleSalida);
  setVal("#ypf_t", pre.TarjetaCombustible);
  setVal("#total_orden_t", ""); // limpio antes de fetch

  // Disparar consultas en paralelo (vehículo y total recorrido)
  const [vehRes, totRes] = await Promise.allSettled([
    fetchKmPorPatente(pre.Patente),
    fetchTotalRecorrido(no),
  ]);

  if (
    vehRes.status === "fulfilled" &&
    vehRes.value &&
    vehRes.value.success == 1
  ) {
    const veh = vehRes.value;
    if (veh.Kilometros != null) setVal("#kilometros_t", veh.Kilometros);
    if (veh.NivelCombustible != null)
      setVal("#combustible_t", veh.NivelCombustible);
    if (Number(veh.Aliados) === 0) $("#briefing_checks").show();
    else $("#briefing_checks").hide();
    if (veh.Marca || veh.Modelo)
      setText(
        "#datos_vehiculo",
        `${veh.Marca ?? ""} ${veh.Modelo ?? ""}`.trim(),
      );
  }

  if (
    totRes.status === "fulfilled" &&
    totRes.value &&
    totRes.value.success == 1
  ) {
    const t = Number(totRes.value.TotalRecorrido ?? 0);
    const fmtARS = new Intl.NumberFormat("es-AR", {
      style: "currency",
      currency: "ARS",
      minimumFractionDigits: 2,
    });
    let totalFormateado = isFinite(t) ? fmtARS.format(t) : "";
    setVal("#total_orden_t", totalFormateado);
  }

  // Si el usuario cambia la patente, refrescamos km/combustible
  $("#patente_t")
    .off("change.fetchkm blur.fetchkm")
    .on("change.fetchkm blur.fetchkm", async function () {
      const pat = $(this).val().trim();
      if (pat) await refreshKmDesdePatente(pat);
    });

  stopLoader(); // quita loader
  modal.show(); // abre modal al final (mejor percepción)

  // —— Helpers internos —— //
  function addLoader($container) {
    const $wrap = $(
      '<div class="loading-overlay" style="position:relative;"></div>',
    );
    const $spinner = $(`
      <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
           style="background:rgba(255,255,255,.6); z-index:10;">
        <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
      </div>`);
    $wrap.append($spinner);
    $container.css("position", "relative").append($wrap);
    let removed = false;
    return () => {
      if (!removed) {
        $wrap.remove();
        removed = true;
      }
    };
  }
}

// Submit del modal (validación + POST)
$("#formCargarOrden").on("submit", function (e) {
  e.preventDefault();
  const form = this;
  form.classList.add("was-validated");
  if (!form.checkValidity()) return;

  const payload = {
    Orden: "Cargar",
    orden_t: $("#orden_t").val(),
    patente_t: $("#patente_t").val().trim(),
    chofer_t: $("#chofer_t").val().trim(),
    recorrido_t: $("#recorrido_t").val().trim(),
    kilometros_t: $("#kilometros_t").val().trim(),
    ypf_t: $("#ypf_t").val().trim(),
    combustible_t: $("#combustible_t").val().trim(),
    acompanante_t: $("#acompanante_t").val().trim(),
    observaciones_t: $("#observaciones_t").val().trim(),
    // Briefing (0/1)
    tarjetaverde_t: $("#tarjetaverde_t").is(":checked") ? 1 : 0,
    tarjetaazul_t: $("#tarjetaazul_t").is(":checked") ? 1 : 0,
    seguro_t: $("#seguro_t").is(":checked") ? 1 : 0,
    cubiertas_t: $("#cubiertas_t").is(":checked") ? 1 : 0,
    auxilio_t: $("#auxilio_t").is(":checked") ? 1 : 0,
    patentes_t: $("#patentes_t").is(":checked") ? 1 : 0,
    luzposicion_t: $("#luzposicion_t").is(":checked") ? 1 : 0,
    luzbaja_t: $("#luzbaja_t").is(":checked") ? 1 : 0,
    luzalta_t: $("#luzalta_t").is(":checked") ? 1 : 0,
    luzfreno_t: $("#luzfreno_t").is(":checked") ? 1 : 0,
    gnc_t: $("#gnc_t").is(":checked") ? 1 : 0,
  };

  $("#btnConfirmarCarga").prop("disabled", true);

  $.ajax({
    url: "Proceso/php/ordenes.php",
    type: "POST",
    dataType: "json",
    data: payload,
    success: function (json) {
      if (Number(json.success) === 1) {
        Swal.fire({
          icon: "success",
          title: "Orden cargada",
          text: json.message || "OK",
        });
        $("#modalCargarOrden").modal("hide");
        $("#ordenes").DataTable().ajax.reload(null, false);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al cargar",
          text: json.message || "No se pudo cargar la orden.",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
      });
    },
    complete: function () {
      $("#btnConfirmarCarga").prop("disabled", false);
    },
  });
});

//CERRAR ORDEN
function cerrarOrden(orden) {
  // $("#form_cerrar_orden_div").css("display", "block");
  $("#modal_cerrar_orden").modal("show");
  $("#fechas_ordenes").css("display", "none");
  // $("#div_ordenes").css("display", "none");
  $("#numero_orden").val(orden);
  $.ajax({
    type: "POST",
    url: "Proceso/php/ordenes.php",
    data: { CerrarOrden: 1, numero_orden: orden },
    dataType: "json",
    success: function (response) {
      if (response.success == 1) {
        $("#controla").val(response.data.Controla);
        $("#vehiculo").val(response.data.Vehiculo);
        $("#chofer").val(response.data.NombreChofer);
        $("#recorrido").val(response.data.Recorrido);
        $("#km_recorrido").val(response.data.KilometrosRecorridos);
        $("#km_salida").val(response.data.KilometrosSalida);
        $("#combustible_salida").val(response.data.CombustibleSalida);
        // ... continuar con los demás campos
      } else {
        $.NotificationApp.send(
          "Atención",
          response.msg,
          "bottom-right",
          "#fff",
          "warning",
        );
      }
    },
    error: function () {
      $.NotificationApp.send(
        "Error",
        "No se pudo consultar la orden",
        "bottom-right",
        "#fff",
        "danger",
      );
    },
  });
}
$(document).ready(function () {
  var today = new Date();

  // Formateamos la fecha de hoy
  var fecha =
    today.getMonth() + 1 + "/" + today.getDate() + "/" + today.getFullYear();

  // Hoy - Hoy
  var fechas = fecha + " - " + fecha;

  // init_datatable(fechas);
  init_datatable();
});

$("#buscar_orden").click(function () {
  $("#fechas_ordenes").css("display", "block");
});

function capitalizar(texto) {
  return texto.toLowerCase().replace(/(^|\s)\S/g, function (letra) {
    return letra.toUpperCase();
  });
}

$("#orden_ok").on("click", function () {
  const chofer_aliado = $("#select_empleados option:selected").data("aliado"); // 0 o 1
  const vehiculo_aliado = $("#select_vehiculo option:selected").data("aliado"); // 0 o 1

  // Validación cruzada
  if (chofer_aliado !== vehiculo_aliado) {
    Swal.fire({
      icon: "warning",
      title: "Verificación de datos",
      text: "Estás asignando un vehículo que no corresponde al tipo de chofer (Empleado / Externo). ¿Querés continuar de todos modos?",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar",
      cancelButtonText: "Revisar",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        guardarOrden(); // Continuar con la carga
      }
      // Si elige revisar, no hace nada
    });
  } else {
    guardarOrden(); // Tipos coinciden, continuamos directo
  }
});

function guardarOrden() {
  let recorrido_nombre = $("#orden_recorrido_name").val();
  if (!recorrido_nombre) {
    recorrido_nombre =
      $("#orden_date").val() +
      " - " +
      $("#select_empleados option:selected").text() +
      " - " +
      $("#select_recorridos option:selected").text();
  }

  const datos = {
    alta_orden: 1,
    numero: $("#orden_number").val(),
    fecha: $("#orden_date").val(),
    hora: $("#orden_time").val(),
    controla: $("#orden_controla").val(),
    vehiculo: $("#select_vehiculo").val(),
    chofer: $("#select_empleados").val(),
    acomp: $("#orden_acomp").val(),
    recorrido: $("#select_recorridos").val(),
    recorrido_nombre: recorrido_nombre, // 👈 si el backend lo guarda, mándalo
    fijo: $("#fijo_switch").is(":checked") ? 1 : 0,
  };

  // Validación rápida
  for (const k of [
    "numero",
    "fecha",
    "hora",
    "controla",
    "vehiculo",
    "chofer",
    "recorrido",
  ]) {
    if (!datos[k]) {
      Swal.fire({
        icon: "warning",
        title: "Faltan datos obligatorios",
        text: "Completá todos los campos requeridos antes de continuar.",
      });
      return;
    }
  }

  $.ajax({
    url: "Proceso/php/ordenes.php",
    type: "POST",
    data: datos,
    dataType: "json",
    timeout: 15000,
    success: function (respuesta) {
      // Por si el servidor devolvió string (hostings raros)
      if (typeof respuesta === "string") {
        try {
          respuesta = JSON.parse(respuesta);
        } catch (e) {}
      }
      if (respuesta && respuesta.success) {
        Swal.fire("OK", "Orden de Salida generada correctamente", "success");
        $("#orden_alta").modal("hide");
        $("#modal-rec-form")[0]?.reset?.();
        if ($.fn.DataTable.isDataTable("#ordenes")) {
          $("#ordenes").DataTable().ajax.reload(null, false);
        }
      } else {
        Swal.fire(
          "Error",
          (respuesta && (respuesta.mensaje || respuesta.error)) ||
            "No se pudo guardar la orden",
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX ERROR =>", {
        status,
        error,
        code: xhr.status,
        response: xhr.responseText,
      });
      Swal.fire(
        "Error",
        `Fallo la comunicación (${xhr.status} - ${status}).\n${(
          xhr.responseText || ""
        ).slice(0, 300)}`,
        "error",
      );
    },
  });
}

$("#orden_date").on("change", function () {
  nombre_recorrido_new();
}); // orden_date

$("#select_empleados").on("change", function () {
  nombre_recorrido_new();
}); // select_empleados

$("#select_vehiculo").on("change", function () {
  nombre_recorrido_new();
});

$("#select_recorridos").on("change", function () {
  const selectedOption = $(this).find("option:selected");
  nombre_recorrido_new();
});

function nombre_recorrido_new(recorrido) {
  if (!recorrido) {
    recorrido = $("#select_recorridos option:selected").val();
  }
  let recorrido_nombre = recorrido;

  console.log("recorrido_nombre:", recorrido_nombre);

  // if (!recorrido_nombre) {
  recorrido_nombre =
    $("#orden_date").val() +
    " - " +
    $("#select_empleados option:selected").text() +
    " - " +
    recorrido;

  $("#orden_recorrido_name").val(recorrido_nombre);
  // } else {
  //   $("#orden_recorrido_name").val("");
  // }
}

$("#agregar_orden").click(function () {
  $("#orden_alta").modal("show");
  // Cargar recorridos solo cuando se abre el modal
  $.ajax({
    url: "Proceso/php/ordenes.php",
    method: "POST",
    data: { BuscoRecorridos: 1 },
    dataType: "json",
    success: function (respuesta) {
      console.log(respuesta);
      //usuario
      $("#orden_controla").val(respuesta.usuario);

      //numero de orden
      $("#orden_number").val(respuesta.proximo_id_logistica);
      $("#select_recorridos_text").text(
        "Próximo Recorrido: " + respuesta.proximo_recorrido,
      );

      //recorrido
      if (respuesta.success) {
        // Fecha en formato dd/mm/yyyy
        const hoy = new Date();
        const fechaFormateada = hoy.toISOString().split("T")[0]; // formato: "2025-06-17"
        $("#orden_date").val(fechaFormateada);

        // Hora en formato HH:MM
        $("#orden_time").val(
          hoy.toLocaleTimeString("es-AR", {
            hour: "2-digit",
            minute: "2-digit",
          }),
        );

        let $select = $("#select_recorridos");
        $select
          .empty()
          .append('<option value="">Seleccione un recorrido</option>');

        respuesta.recorridos.forEach(function (recorrido) {
          $select.append(
            `<option value="${recorrido.Numero}">Recorrido ${recorrido.Numero} - ${recorrido.Nombre}</option>`,
          );
        });
        // nombre_recorrido_new();
      } else {
        $("#select_recorridos")
          .append(
            $("<option>", {
              value: respuesta.proximo_recorrido,
              text: "Recorrido " + respuesta.proximo_recorrido,
            }),
          )
          .val(respuesta.proximo_recorrido)
          .trigger("change");
        nombre_recorrido_new(respuesta.proximo_recorrido);
      }
    },
  });

  $.ajax({
    url: "Proceso/php/ordenes.php",
    type: "POST",
    data: { BuscoEmpleados: 1 },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        let empleados = respuesta.empleados;
        let empleadosHTML = '<optgroup label="Empleados">';
        let aliadosHTML = '<optgroup label="Externos">';

        empleados.forEach(function (e) {
          let option = `<option value="${e.id}" data-aliado="${
            e.Aliados
          }">${capitalizar(e.NombreCompleto)}</option>`;
          if (e.Aliados == 1) {
            aliadosHTML += option;
          } else {
            empleadosHTML += option;
          }
        });

        empleadosHTML += "</optgroup>";
        aliadosHTML += "</optgroup>";

        const primerOpcion = '<option value="">Seleccione un empleado</option>';
        $("#select_empleados")
          .html(primerOpcion + empleadosHTML + aliadosHTML)
          .trigger("change");
      }
    },
  });
  // BUSCO VEHICULOS
  $.ajax({
    url: "Proceso/php/ordenes.php",
    method: "POST",
    data: { BuscoVehiculos: 1 },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.success) {
        let $select = $("#select_vehiculo");
        $select
          .empty()
          .append('<option value="">Seleccione un Vehiculo</option>');

        let caddyGroup = $('<optgroup label="Vehiculo de Caddy"></optgroup>');
        let externosGroup = $(
          '<optgroup label="Vehiculo de Externos"></optgroup>',
        );

        respuesta.vehiculos.forEach(function (vehiculo) {
          let marca = capitalizar(vehiculo.Marca);
          let modelo = capitalizar(vehiculo.Modelo);
          let nombre = `${marca} ${modelo} ${vehiculo.Dominio}`;
          let option = `<option value="${vehiculo.id}" data-aliado="${vehiculo.Aliados}">${nombre}</option>`;

          if (vehiculo.Aliados == 1) {
            externosGroup.append(option);
          } else {
            caddyGroup.append(option);
          }
        });

        $select.append(caddyGroup).append(externosGroup).trigger("change");
      }
    },
  });
});

$("#singledaterange").change(function () {
  fechas = $("#singledaterange").val();
  // console.log(fechas);

  if (fechas == null) {
    var fechas1 =
      new Date().getUTCMonth() +
      1 +
      "/" +
      new Date().getUTCDate() +
      "/" +
      new Date().getUTCFullYear();
    var fechas = fechas1 + " - " + fechas1;
  }
  var datatable = $("#ordenes").DataTable();
  datatable.destroy();
  init_datatable(fechas);
});

function init_datatable(fechas = "") {
  $("#div_ordenes").css("display", "block");

  if (fechas && fechas.trim() !== "") {
    var new_date = fechas.split(" - ");
    var new_date1 = new_date[0].split("/");
    var new_date2 = new_date1[1] + "/" + new_date1[0] + "/" + new_date1[2];

    var new_date_1 = new_date[1].split("/");
    var new_date_2 = new_date_1[1] + "/" + new_date_1[0] + "/" + new_date_1[2];

    $("#header_ordenes").html(
      "Ordenes de Salida Fechas Desde " + new_date2 + " Hasta " + new_date_2,
    );
  } else {
    fechas = "";
    $("#header_ordenes").html("Órdenes de Salida Pendientes o Cargadas");
  }

  $("#div_ordenes").css("display", "block");

  if ($.fn.DataTable.isDataTable("#ordenes")) {
    $("#ordenes").DataTable().destroy();
  }

  $("#ordenes").DataTable({
    dom: "Bfrtip",
    buttons: ["pageLength", "copy", "excel", "pdf"],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],

    ajax: {
      url: "Proceso/php/ordenes.php",
      data: { Logistica: 1, Fechas: fechas },
      processing: true,
      type: "post",
    },
    columns: [
      {
        data: "NumerodeOrden",
        render: function (data, type, row) {
          return `${row.NumerodeOrden}`;
        },
      },
      {
        data: "Fecha",
        render: function (data, type, row) {
          let fecha = row.Fecha ? row.Fecha.split("-").reverse().join("/") : "";
          return `${fecha}<br>S:${row.Hora || ""}<br>R:${row.HoraRetorno || ""}`;
        },
      },
      {
        data: "NombreChofer",
        render: function (data, type, row) {
          return `${row.NombreChofer || ""}<br>${row.NombreChofer2 || ""}`;
        },
      },
      {
        data: "Patente",
        render: function (data, type, row) {
          return `${row.Marca || ""} ${row.Modelo || ""} ${row.Dominio || ""}`;
        },
      },
      {
        data: "Recorrido",
        render: function (data, type, row) {
          return `<small>${row.Recorrido || ""}</small><br>${row.Nombre || ""}`;
        },
      },
      {
        data: "Kilometros",
        render: function (data, type, row) {
          return `${row.KilometrosRecorridos || 0} Km.<br><span class="text-muted">${row.Kilometros || 0} - ${row.KilometrosRegreso || 0}</span>`;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var totalRecorrido = parseFloat(row.TotalRecorrido || 0);
          var formatoMoneda = totalRecorrido.toLocaleString("es-AR", {
            style: "currency",
            currency: "ARS",
          });

          var facturado = row.Facturado == 1 ? "Facturado" : "No Facturado";
          var color = row.Facturado == 1 ? "success" : "danger";

          return `<h6><span class="badge bg-${color}"><b>${facturado}</b></span></h6>${formatoMoneda}`;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var estado = "secondary";

          if (row.Estado == "Cerrada") {
            estado = "danger";
          } else if (row.Estado == "Alta" || row.Estado == "Pendiente") {
            estado = "warning";
          } else if (row.Estado == "Cargada") {
            estado = "success";
          }

          return `<h6><span class="badge bg-${estado}"><b>${row.Estado || ""}</b></span></h6>`;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          if (row.Estado == "Cerrada") {
            return `<a target="_blank" href="https://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ResumenVehiculospdf.php?NO=${row.NumerodeOrden}">
                      <i class="mdi mdi-18px mdi-file-chart-outline"></i>
                    </a>`;
          } else {
            return `
              <a target="_blank" href="http://www.caddy.com.ar/SistemaTriangular/Logistica/Informes/ControldeVehiculospdf.php?NO=${row.NumerodeOrden}" title="Ver Orden">
                <i class="mdi mdi-18px mdi-file-chart-outline ms-2"></i>
              </a>
              <a href="#" class="btn-cargar-orden" data-no="${row.NumerodeOrden}" title="Cargar Orden">
                <i class="mdi mdi-18px mdi-upload ms-2 text-success"></i>
              </a>
              <a href="#" onclick="cerrarOrden('${row.NumerodeOrden}'); return false;" title="Cerrar Orden">
                <i class="mdi mdi-18px mdi-lock-check ms-2 text-danger"></i>
              </a>
            `;
          }
        },
      },
    ],
  });
}

$(document).on("click", "#btn_edit_recorrido", function () {
  const input = $("#orden_recorrido_name");
  const icon = $(this).find("i");

  if (input.prop("readonly")) {
    input.prop("readonly", false).focus();
    icon
      .removeClass("mdi-pencil text-warning")
      .addClass("mdi-check text-success");
  } else {
    input.prop("readonly", true);
    icon
      .removeClass("mdi-check text-success")
      .addClass("mdi-pencil text-warning");
  }
});
