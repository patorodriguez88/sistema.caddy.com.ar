function currencyFormat(num) {
  const n = Number(num) || 0;
  return (
    "$ " +
    n.toLocaleString("es-AR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

let tarjetaSeleccionada = null;
let cuentaSeleccionada = "";
let datatable1 = null;

// Conciliación Bancaria: "corrida" actual (ver ConciliacionBancaria en la
// base) - todo lo que se concilie con "Guardar Conciliación" queda
// linkeado a este id. Se completa al apretar "Aceptar" (abrir_conciliacion)
// y se resetea en "Volver".
let idConciliacionActual = null;
let estadoConciliacionActual = null; // 'Abierta' | 'Cerrada'

// --- RANGO DE FECHAS: usar daterangepicker si lo tenés, o un input simple ---
// Este helper transforma "DD/MM/YYYY" -> "YYYY-MM-DD"
function toYMD(fechaDDMMYYYY) {
  if (!fechaDDMMYYYY) return "";
  const [dd, mm, yyyy] = fechaDDMMYYYY.split("/");
  if (!dd || !mm || !yyyy) return "";
  return `${yyyy}-${mm}-${dd}`;
}

// Devuelve {desde, hasta} en YYYY-MM-DD leyendo el input del daterangepicker
function getFechasYMD() {
  const raw = document.getElementById("singledaterange")?.value || "";
  // Si usás daterangepicker con locale DD/MM/YYYY, suele venir "DD/MM/YYYY - DD/MM/YYYY"
  const partes = raw.split(" - ");
  if (partes.length === 2) {
    return { desde: toYMD(partes[0].trim()), hasta: toYMD(partes[1].trim()) };
  }
  // Si es un único date, lo uso como desde = hasta
  if (raw.includes("/")) {
    const ymd = toYMD(raw.trim());
    return { desde: ymd, hasta: ymd };
  }
  return { desde: "", hasta: "" };
}

// "YYYY-MM-DD HH:MM:SS" (Tesoreria.FechaConciliado, datetime) -> "DD/MM/YYYY HH:MM"
function formatFechaHoraParaUI(valor) {
  if (!valor) return "";
  const [fecha, hora] = String(valor).split(" ");
  const [y, m, d] = (fecha || "").split("-");
  const hm = (hora || "").slice(0, 5);
  return y && m && d ? `${d}/${m}/${y} ${hm}`.trim() : valor;
}

// Para mostrar DD/MM/YYYY en la UI a partir de YYYY-MM-DD o del daterangepicker
function formatFechaParaUI(valor) {
  if (!valor) return "Sin fecha seleccionada";
  if (valor.includes(" - ")) return valor; // ya es el texto del picker
  // "YYYY-MM-DD" -> "DD/MM/YYYY"
  const [y, m, d] = valor.split("-");
  return y && m && d ? `${d}/${m}/${y}` : valor;
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("✅ Script cargado correctamente");

  // FIX: el daterangepicker de #singledaterange se inicializaba solo, por
  // un script global del tema (app.js, cualquier input con
  // data-toggle="date-picker"), SIN pasarle locale.format - la librería
  // arranca en su default MM/DD/YYYY (formato US). Pero toYMD()/
  // getFechasYMD() de este mismo archivo interpretan el texto del input
  // como DD/MM/YYYY (igual que el resto del sistema). Con un día >12 (ej.
  // "26" en 26/08/2026) eso da un mes inválido (26) al leerlo como MM ->
  // parseFechaFlexible() del lado PHP no logra parsear esa fecha -> el
  // filtro de fechas se descartaba en silencio y la consulta traía TODOS
  // los movimientos, no solo los del rango elegido. Se reinicializa acá
  // con el formato correcto, pisando el init automático.
  if (window.jQuery && jQuery.fn.daterangepicker) {
    // FIX (vuelta atrás de un intento anterior): con autoUpdateInput:false
    // + manejo manual de apply/cancel el input se quedaba vacío para
    // siempre en algunos casos ("el rango de fechas me tira todo null") -
    // demasiado manejo custom para algo que la librería ya sabe hacer
    // sola. Se deja autoUpdateInput en su default (true): la librería
    // misma escribe el valor con el formato pedido y dispara el "change"
    // que ya escucha el código de más abajo - menos código propio, menos
    // superficie de bug.
    $("#singledaterange").daterangepicker({
      locale: {
        format: "DD/MM/YYYY",
        separator: " - ",
        applyLabel: "Aplicar",
        cancelLabel: "Cancelar",
        fromLabel: "Desde",
        toLabel: "Hasta",
        customRangeLabel: "Personalizado",
        daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        monthNames: [
          "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
          "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
        ],
      },
      startDate: moment(),
      endDate: moment(),
      cancelClass: "btn-light",
      applyButtonClasses: "btn-success",
    });
  }

  fetch("../Admin/Procesos/php/bancos.php?action=listar")
    .then((r) => r.json())
    .then((data) => {
      if (!data.data || !Array.isArray(data.data)) {
        console.error("❌ La API no devolvió un array válido");
        return;
      }
      const container = document.getElementById("bancos-container");
      if (!container) {
        console.error("❌ No se encontró #bancos-container");
        return;
      }
      container.innerHTML = "";
      // A pedido (2026-09-17, "los bancos están medios feos"): cards más
      // chicas (entran las 4 en una fila en desktop), con ícono e
      // identidad de color por banco en vez del card Bootstrap genérico.
      data.data.forEach((banco) => {
        const card = document.createElement("div");
        card.classList.add("col-sm-6", "col-xl-3", "mb-3");
        const cuentaId = `banco-${String(banco.Cuenta).replace(/\s+/g, "_")}`;
        const { icono, marca } = identidadBanco(banco.NombreCuenta);
        card.innerHTML = `
          <div class="banco-card banco-card--${marca}" id="${cuentaId}" onclick="seleccionarBanco('${banco.Cuenta}')">
            <div class="banco-card-icono"><i class="mdi ${icono}"></i></div>
            <div class="banco-card-info">
              <div class="banco-card-nombre">${banco.NombreCuenta}</div>
              <div class="banco-card-cuenta">Cta. ${banco.Cuenta}</div>
            </div>
          </div>`;
        container.appendChild(card);
      });
    })
    .catch((err) => console.error("❌ Error al obtener bancos:", err));
});

// Identidad visual por banco (a pedido, 2026-09-17: "métele onda, algún
// ícono") - matchea por nombre, con un fallback genérico para cualquier
// cuenta nueva que se agregue después.
function identidadBanco(nombreCuenta) {
  const n = String(nombreCuenta || "").toUpperCase();
  if (n.includes("TARJETA") || n.includes("CREDITO")) {
    return { icono: "mdi-credit-card-outline", marca: "tarjeta" };
  }
  if (n.includes("GALICIA")) {
    return { icono: "mdi-bank", marca: "galicia" };
  }
  if (n.includes("MACRO")) {
    return { icono: "mdi-bank", marca: "macro" };
  }
  return { icono: "mdi-bank-outline", marca: "generico" };
}

function seleccionarBanco(id) {
  if (tarjetaSeleccionada) tarjetaSeleccionada.classList.remove("is-selected");
  tarjetaSeleccionada = document.getElementById(
    `banco-${id.replace(/\s+/g, "_")}`
  );
  tarjetaSeleccionada.classList.add("is-selected");

  const nombreBanco =
    tarjetaSeleccionada.querySelector(".banco-card-nombre").innerText;
  cuentaSeleccionada = tarjetaSeleccionada
    .querySelector(".banco-card-cuenta")
    .innerText.replace("Cta.", "")
    .trim();

  const fechaInput = document.getElementById("singledaterange");
  const fechaFormateada = formatFechaParaUI(fechaInput?.value || "");

  document.getElementById(
    "cuenta-info"
  ).innerHTML = `<strong>${nombreBanco}</strong> - Número de Cuenta: <strong>${cuentaSeleccionada}</strong>`;
  document.getElementById(
    "fecha-info"
  ).innerHTML = `<strong>Fecha Seleccionada:</strong> ${fechaFormateada}`;

  verificarMostrarBoton();
  $("#display-fecha").css("display", "block");
}

function verificarMostrarBoton() {
  const fecha = document.getElementById("singledaterange").value;
  const btn = document.getElementById("btnAceptar");
  if (tarjetaSeleccionada && fecha) btn.style.display = "inline-block";
  else btn.style.display = "none";
}

// Botón volver: además de reiniciar la selección, tiene que volver a
// mostrar los cards de bancos y el selector de fecha, que btnAceptar()
// había ocultado - si no, quedaba "trabado" en la grilla sin forma de
// elegir otro banco sin recargar la página (a pedido, 2026-09-22).
$("#btnVolver").click(function () {
  $("#cuentas-container").show();
  $("#bancos-container, #display-fecha").show();
  $("#conciliacion_bancaria").hide();
  $("#mensajeNoDatos").hide();
  $("#btnGrabarConciliacion").hide();
  $("#btnCerrarConciliacion").hide();
  $("#btnImprimirConciliacion").hide();
  $("#avisoConciliacionCerrada").hide();
  $("#btnVolver").hide();
  idConciliacionActual = null;
  estadoConciliacionActual = null;

  $("#singledaterange").val("");
  $("#cuenta-info").html("<em>Seleccione una cuenta...</em>");
  $("#fecha-info").html("<em>Seleccione un rango de fechas...</em>");
  $(".banco-card").removeClass("is-selected");
  cuentaSeleccionada = "";
  tarjetaSeleccionada = null;

  if (datatable1) {
    datatable1.clear().draw();
  }
  $("#btnAceptar").show();
});

// Construye (o reconstruye) la DataTable
function buildDataTable() {
  if (datatable1) {
    datatable1.destroy();
    datatable1 = null;
  }

  datatable1 = $("#tabla_conciliacion").DataTable({
    dom: "Bfrtip",
    buttons: {
      dom: { button: { className: "" } },
      buttons: [
        dtButtonConfig("pageLength", true),
        dtButtonConfig("copy", true),
        dtButtonConfig("csv", true),
        dtButtonConfig("excel", true),
        dtButtonConfig({
          extend: "pdf",
          orientation: "landscape",
          title: "Conciliación Bancaria",
          filename: "ConciliacionBancariaCaddy",
          header: true,
          pageSize: "A4",
        }),
      ],
    },
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "Todos"],
    ],
    pageLength: -1,
    paging: false,
    searching: true,
    responsive: true,
    autoWidth: false,
    processing: true,
    deferRender: true,
    ajax: {
      url: "../Admin/Procesos/php/bancos.php",
      type: "POST",
      data: function (d) {
        d.action = "consultar_conciliacion";
        d.Cuenta = cuentaSeleccionada;
        const { desde, hasta } = getFechasYMD();
        d.desde = desde;
        d.hasta = hasta;
      },
      dataSrc: function (json) {
        try {
          // fuerza a mostrar la sección cuando hay respuesta
          $("#conciliacion_bancaria").show();
          $("#tabla_conciliacion").show(); // aseguramos tabla visible

          // si vino texto o null, evitá romper y mostrás aviso
          if (!json || typeof json !== "object") {
            console.error("❌ Respuesta no JSON o vacía:", json);
            $("#tabla_conciliacion").hide();
            $("#mensajeNoDatos")
              .show()
              .text("Ocurrió un error al consultar. Ver consola.");
            $("#btnVolver").show();
            $("#btnGrabarConciliacion").hide();
            return [];
          }
          const arr = Array.isArray(json.data) ? json.data : [];
          console.log("✅ Filas recibidas:", arr.length);

          if (arr.length === 0) {
            // $("#tabla_conciliacion").hide();
            $("#mensajeNoDatos")
              .show()
              .text(json.error || "No hay datos disponibles para la consulta.");
            $("#btnVolver").show();
            $("#btnGrabarConciliacion").hide();
          } else {
            // $("#tabla_conciliacion").show();
            $("#mensajeNoDatos").hide();
            // Si la corrida ya está Cerrada, no se puede seguir conciliando.
            $("#btnGrabarConciliacion").toggle(estadoConciliacionActual !== "Cerrada");
            // "Elegir otro Banco" se muestra desde btnAceptar() y queda
            // visible durante toda la pantalla, haya datos o no.
          }
          return arr;
        } catch (e) {
          console.error("❌ Error procesando dataSrc:", e, json);
          //   $("#tabla_conciliacion").hide();
          $("#mensajeNoDatos").show().text("Error procesando la respuesta.");
          $("#btnVolver").show();
          $("#btnGrabarConciliacion").hide();
          return [];
        }
      },

      error: function (xhr, status, error) {
        console.error(
          "❌ AJAX DataTables error:",
          status,
          error,
          xhr.responseText
        );
        // Mostrá el aviso y botón volver
        $("#tabla_conciliacion").hide();
        $("#mensajeNoDatos")
          .show()
          .text("Ocurrió un error al consultar. Ver consola.");
        $("#btnVolver").show();
        $("#btnGrabarConciliacion").hide();
      },
    },
    columns: [
      { data: "Fecha" },
      { data: "Cuenta" },
      {
        data: "Cliente",
        render: function (data, type, row) {
          return row.Cliente || "";
        },
      },
      { data: "Observaciones" },
      { data: "Debe", render: $.fn.dataTable.render.number(".", ",", 2, "$ ") },
      {
        data: "Haber",
        render: $.fn.dataTable.render.number(".", ",", 2, "$ "),
      },
      { data: "NumeroTrans", render: (d) => d || "" },
      {
        data: "Conciliado",
        render: function (data, type, row) {
          // Pedido (Asana, Patricio/Agustina): una vez conciliado, ya NO es
          // un checkbox - queda un ícono fijo (no se puede destildar). El
          // checkbox interactivo solo aparece para lo que todavía no está
          // conciliado, y solo si la corrida sigue Abierta.
          if (Number(data) === 1) {
            const fecha = row.FechaConciliado ? formatFechaHoraParaUI(row.FechaConciliado) : "";
            const usuario = row.UsuarioConciliado || "";
            const titulo = `Conciliado${usuario ? " por " + usuario : ""}${fecha ? " el " + fecha : ""}`;
            // Pedido (Patricio, 2026-09-22): fecha/hora/usuario visibles, no
            // solo como tooltip al pasar el mouse (quedaba escondido).
            const detalle =
              usuario || fecha
                ? `<div style="font-size:9px;color:#8a8398;line-height:1.2;margin-top:2px;">${usuario}${usuario && fecha ? " - " : ""}${fecha}</div>`
                : "";
            return `<span class="badge bg-success" title="${titulo}"><i class="mdi mdi-check-bold"></i> Validado</span>${detalle}`;
          }
          const disabled = estadoConciliacionActual === "Cerrada" ? "disabled" : "";
          return `<input type="checkbox" class="conciliado-checkbox" data-id="${row.id}" ${disabled}>`;
        },
      },
    ],
    footerCallback: function (row, data, start, end, display) {
      const api = this.api();
      const sumCol = (idx) =>
        api
          .column(idx, { page: "current" })
          .data()
          .reduce(
            (a, b) =>
              Number(
                String(a)
                  .toString()
                  .replace(/[^\d.-]/g, "")
              ) +
              Number(
                String(b)
                  .toString()
                  .replace(/[^\d.-]/g, "")
              ),
            0
          );

      const totalDebe = sumCol(4);
      const totalHaber = sumCol(5);

      $(api.column(4).footer()).html(currencyFormat(totalDebe));
      $(api.column(5).footer()).html(currencyFormat(totalHaber));
    },
    initComplete: function () {
      // cuando la tabla ya está en DOM y visible
      this.api().columns.adjust().draw(false);
    },
  });
}

// Aceptar: oculta selección, abre (o recupera) la corrida de conciliación
// para esta cuenta+rango, y recién ahí crea la DataTable.
document.getElementById("btnAceptar").addEventListener("click", function () {
  $("#conciliacion_bancaria").show();
  $("#tabla_conciliacion").show();

  const hiddenTable = document.getElementById("conciliacion_bancaria");
  hiddenTable.style.display = "block";
  //   $("#cuentas-container").hide();
  $("#bancos-container, #display-fecha, #btnAceptar").hide(); // oculto cards/fecha/botón
  $("#conciliacion_bancaria").show(); // muestro la tabla
  // "Elegir otro Banco" queda visible durante toda esta pantalla, haya o
  // no datos (antes solo aparecía si la consulta venía vacía/con error).
  $("#btnVolver").show();
  const rawFecha = $("#singledaterange").val();
  if (!cuentaSeleccionada || !rawFecha) {
    $("#mensajeNoDatos")
      .show()
      .text("Seleccioná una cuenta y un rango de fechas.");
    return;
  }

  const { desde, hasta } = getFechasYMD();
  const btnEl = this;

  $.ajax({
    url: "../Admin/Procesos/php/bancos.php",
    type: "POST",
    data: { action: "abrir_conciliacion", cuenta: cuentaSeleccionada, desde, hasta },
    dataType: "json",
    success: function (res) {
      if (res && res.success && res.conciliacion) {
        idConciliacionActual = res.conciliacion.id;
        estadoConciliacionActual = res.conciliacion.Estado;
        actualizarBotonesSegunEstado();
      } else {
        idConciliacionActual = null;
        estadoConciliacionActual = null;
        $("#mensajeNoDatos")
          .show()
          .text((res && res.error) || "No se pudo abrir la conciliación.");
      }
      buildDataTable(); // ← crea la tabla ya con idConciliacionActual resuelto
      btnEl.style.display = "none";
    },
    error: function () {
      idConciliacionActual = null;
      estadoConciliacionActual = null;
      $("#mensajeNoDatos").show().text("No se pudo abrir la conciliación. Ver consola.");
      buildDataTable();
      btnEl.style.display = "none";
    },
  });
});

// Muestra/oculta los botones de Guardar/Cerrar/Imprimir según el estado
// de la corrida actual (Abierta permite seguir conciliando y cerrar;
// Cerrada solo permite imprimir), y el cartel fijo de "Cerrada".
function actualizarBotonesSegunEstado() {
  const cerrada = estadoConciliacionActual === "Cerrada";
  $("#btnCerrarConciliacion").toggle(!cerrada && !!idConciliacionActual);
  $("#btnImprimirConciliacion").toggle(!!idConciliacionActual);
  $("#avisoConciliacionCerrada").toggle(cerrada);
}

// Si cambian filtros después, recargá (si ya existe tabla)
$("#singledaterange").on("change", function () {
  // FIX (reportado, 2026-09-17: "seleccioné la fecha 16 pero muestra como
  // seleccionada 17/9"): "Fecha Seleccionada" solo se actualizaba adentro
  // de seleccionarBanco() (al elegir la cuenta) - si primero se elegía el
  // banco (quedaba con la fecha default de "hoy") y DESPUÉS se cambiaba el
  // rango de fechas, el resumen nunca se refrescaba y mostraba la fecha
  // vieja aunque el input ya tuviera la nueva. Se actualiza acá también.
  const fechaFormateada = formatFechaParaUI(this.value || "");
  $("#fecha-info").html(`<strong>Fecha Seleccionada:</strong> ${fechaFormateada}`);

  if (datatable1) datatable1.ajax.reload(null, false);
  $("#btnAceptar").show();
});

// Guardar conciliación
// FIX (Asana, pedido de Patricio/Agustina): ya no hay checkboxes tildados
// de items YA conciliados (esos ahora renderizan como ícono fijo, sin
// checkbox) - lo que junta este selector es SOLO lo nuevo que se está por
// conciliar. El backend (grabar_conciliacion) también dejó de resetear
// todo el rango a 0 antes de marcar: ahora únicamente agrega.
$("#btnGrabarConciliacion").click(function () {
  if (!idConciliacionActual) {
    alert("No se pudo determinar la conciliación (recargá la página e intentá de nuevo).");
    return;
  }

  const idsConciliados = [];
  $("#tabla_conciliacion tbody .conciliado-checkbox:checked").each(function () {
    const id = $(this).data("id");
    if (id) idsConciliados.push(parseInt(id, 10));
  });

  if (idsConciliados.length === 0) {
    alert("No hay registros nuevos tildados para conciliar.");
    return;
  }

  $.ajax({
    url: "../Admin/Procesos/php/bancos.php",
    type: "POST",
    data: {
      action: "grabar_conciliacion",
      idConciliacion: idConciliacionActual,
      ids: idsConciliados,
    },
    success: function (response) {
      console.log("Respuesta del servidor:", response);
      if (response && response.success) {
        $("#success-alert-modal").modal("show");
      } else {
        alert((response && response.error) || "No se pudo guardar la conciliación.");
      }
      if (datatable1) datatable1.ajax.reload(null, false);
    },
    error: function (xhr, status, error) {
      console.error("Error al guardar conciliación:", error);
      alert("Error al guardar la conciliación.");
    },
  });
});

// Cerrar conciliación: bloquea la corrida completa, no se puede volver a
// tocar (ver bancos.php::cerrar_conciliacion).
$("#btnCerrarConciliacion").click(function () {
  if (!idConciliacionActual) return;

  const cerrar = () => {
    $.ajax({
      url: "../Admin/Procesos/php/bancos.php",
      type: "POST",
      data: { action: "cerrar_conciliacion", idConciliacion: idConciliacionActual },
      dataType: "json",
      success: function (res) {
        if (res && res.success) {
          estadoConciliacionActual = "Cerrada";
          actualizarBotonesSegunEstado();
          if (datatable1) datatable1.ajax.reload(null, false);
          if (window.Swal) {
            Swal.fire("Listo", "Conciliación cerrada correctamente.", "success");
          } else {
            alert("Conciliación cerrada correctamente.");
          }
        } else {
          alert((res && res.error) || "No se pudo cerrar la conciliación.");
        }
      },
      error: function () {
        alert("No se pudo cerrar la conciliación. Ver consola.");
      },
    });
  };

  if (window.Swal) {
    Swal.fire({
      icon: "question",
      title: "¿Cerrar conciliación?",
      text: "Una vez cerrada, no se va a poder conciliar ni destildar nada más de este período.",
      showCancelButton: true,
      confirmButtonText: "Sí, cerrar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#1c8f61",
    }).then((r) => {
      if (r.isConfirmed) cerrar();
    });
  } else if (confirm("¿Cerrar conciliación? Una vez cerrada no se puede modificar.")) {
    cerrar();
  }
});

// Imprimir: abre el PDF de la corrida actual en una pestaña nueva.
$("#btnImprimirConciliacion").click(function () {
  if (!idConciliacionActual) return;
  window.open(
    "../Admin/Informes/ConciliacionBancariaPdf.php?id=" + encodeURIComponent(idConciliacionActual),
    "_blank"
  );
});
