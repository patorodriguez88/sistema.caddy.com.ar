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
      data.data.forEach((banco) => {
        const card = document.createElement("div");
        card.classList.add("col-md-4", "mb-3");
        const cuentaId = `banco-${String(banco.Cuenta).replace(/\s+/g, "_")}`;
        card.innerHTML = `
          <div class="card shadow-sm border-primary banco-card" id="${cuentaId}" onclick="seleccionarBanco('${banco.Cuenta}')">
            <div class="card-body">
              <h5 class="card-title">${banco.NombreCuenta}</h5>
              <p class="card-text"><strong>Número de Cuenta:</strong> <span>${banco.Cuenta}</span></p>
            </div>
          </div>`;
        container.appendChild(card);
      });
    })
    .catch((err) => console.error("❌ Error al obtener bancos:", err));
});

function seleccionarBanco(id) {
  if (tarjetaSeleccionada)
    tarjetaSeleccionada.classList.remove("bg-warning", "text-white");
  tarjetaSeleccionada = document.getElementById(
    `banco-${id.replace(/\s+/g, "_")}`
  );
  tarjetaSeleccionada.classList.add("bg-warning", "text-white");

  const nombreBanco =
    tarjetaSeleccionada.querySelector(".card-title").innerText;
  cuentaSeleccionada = tarjetaSeleccionada
    .querySelector(".card-text span")
    .innerText.trim();

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

// Botón volver
$("#btnVolver").click(function () {
  $("#cuentas-container").show();
  $("#conciliacion_bancaria").hide();
  $("#mensajeNoDatos").hide();
  $("#btnGrabarConciliacion").hide();
  $("#btnVolver").hide();

  $("#singledaterange").val("");
  $("#cuenta-info").html("<em>Seleccione una cuenta...</em>");
  $("#fecha-info").html("<em>Seleccione un rango de fechas...</em>");
  $(".banco-card").removeClass("bg-warning text-white");
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
    buttons: [
      "pageLength",
      "copy",
      "csv",
      "excel",
      {
        extend: "pdf",
        text: "PDF",
        orientation: "landscape",
        title: "Conciliación Bancaria",
        filename: "ConciliacionBancariaCaddy",
        header: true,
        pageSize: "A4",
      },
    ],
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
            $("#mensajeNoDatos").show();
            $("#btnVolver").show();
            $("#btnGrabarConciliacion").hide();
          } else {
            // $("#tabla_conciliacion").show();
            $("#mensajeNoDatos").hide();
            $("#btnGrabarConciliacion").show();
            $("#btnVolver").hide();
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
      {
        data: "Conciliado",
        render: function (data, type, row) {
          const checked = Number(data) === 1 ? "checked" : "";
          return `<input type="checkbox" class="conciliado-checkbox" data-id="${row.id}" ${checked}>`;
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

// Aceptar: oculta selección y crea DataTable
document.getElementById("btnAceptar").addEventListener("click", function () {
  $("#conciliacion_bancaria").show();
  $("#tabla_conciliacion").show();

  const hiddenTable = document.getElementById("conciliacion_bancaria");
  hiddenTable.style.display = "block";
  //   $("#cuentas-container").hide();
  $("#bancos-container, #display-fecha, #btnAceptar").hide(); // oculto cards/fecha/botón
  $("#conciliacion_bancaria").show(); // muestro la tabla
  const rawFecha = $("#singledaterange").val();
  if (!cuentaSeleccionada || !rawFecha) {
    $("#mensajeNoDatos")
      .show()
      .text("Seleccioná una cuenta y un rango de fechas.");
    return;
  }

  buildDataTable(); // ← crea la tabla aquí
  this.style.display = "none";
});

// Si cambian filtros después, recargá (si ya existe tabla)
$("#singledaterange").on("change", function () {
  if (datatable1) datatable1.ajax.reload(null, false);
  $("#btnAceptar").show();
});

// Guardar conciliación
$("#btnGrabarConciliacion").click(function () {
  const idsConciliados = [];
  $("#tabla_conciliacion tbody .conciliado-checkbox:checked").each(function () {
    const id = $(this).data("id");
    if (id) idsConciliados.push(parseInt(id, 10));
  });

  if (idsConciliados.length === 0) {
    alert("No hay registros conciliados para guardar.");
    return;
  }

  const { desde, hasta } = getFechasYMD();

  $.ajax({
    url: "../Admin/Procesos/php/bancos.php",
    type: "POST",
    data: {
      action: "grabar_conciliacion",
      cuenta: cuentaSeleccionada,
      desde,
      hasta,
      ids: idsConciliados,
    },
    success: function (response) {
      console.log("Respuesta del servidor:", response);
      $("#success-alert-modal").modal("show");
      // Si querés refrescar sin recargar la página:
      if (datatable1) datatable1.ajax.reload(null, false);
    },
    error: function (xhr, status, error) {
      console.error("Error al guardar conciliación:", error);
      alert("Error al guardar la conciliación.");
    },
  });
});
