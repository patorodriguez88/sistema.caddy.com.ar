// Datos > Tarifas Externos: catalogo + timeline de precios con vigencia.
const TE_URL = "Procesos/php/tarifas_externos.php";

function teMoney(n) {
  const v = parseFloat(n);
  if (!isFinite(v)) return "$ 0,00";
  return "$ " + v.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function teFechaDMY(iso) {
  if (!iso) return "";
  const p = String(iso).slice(0, 10).split("-");
  return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
}
function teHoyISO() {
  const d = new Date();
  return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
}

let tablaTarifas = null;

function cargarTarifas() {
  if ($.fn.DataTable.isDataTable("#tabla_tarifas")) {
    $("#tabla_tarifas").DataTable().ajax.reload(null, false);
    return;
  }
  tablaTarifas = $("#tabla_tarifas").DataTable({
    paging: false,
    info: false,
    autoWidth: false,
    order: [[0, "asc"]],
    language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
    ajax: { url: TE_URL, type: "post", data: { Listar: 1 } },
    columns: [
      { data: "id" },
      { data: "Nombre" },
      {
        data: "PrecioVigente",
        className: "text-end fw-bold",
        render: (d) => teMoney(d),
      },
      {
        data: "VigenteDesde",
        render: function (d, t, row) {
          let h = d ? teFechaDMY(d) : '<span class="text-muted">—</span>';
          if (row.ProximoCambio) {
            h += `<br><span class="tarifa-prox text-warning"><i class="mdi mdi-clock-outline"></i> cambia el ${teFechaDMY(row.ProximoCambio)}</span>`;
          }
          return h;
        },
      },
      {
        data: "Observaciones",
        render: (d) => (d ? `<span class="text-muted">${$("<div>").text(d).html()}</span>` : ""),
      },
      {
        data: null,
        orderable: false,
        className: "text-nowrap",
        render: function (d, t, row) {
          return (
            `<button class="btn btn-sm btn-outline-secondary te-editar" data-id="${row.id}" title="Editar nombre/obs"><i class="mdi mdi-pencil"></i></button> ` +
            `<button class="btn btn-sm btn-outline-primary te-historial" data-id="${row.id}" data-nombre="${$("<div>").text(row.Nombre).html()}" title="Precios / vigencia"><i class="mdi mdi-cash-multiple"></i> Precios <span class="badge bg-secondary">${row.NPrecios}</span></button>`
          );
        },
      },
    ],
  });
}

$(document).ready(function () {
  cargarTarifas();

  // ---- nueva tarifa ----
  $("#btn_nueva_tarifa").on("click", function () {
    $("#modal_tarifa_titulo").text("Nueva tarifa");
    $("#tarifa_id").val("");
    $("#tarifa_nombre").val("");
    $("#tarifa_obs").val("");
    $("#tarifa_precio_ini").val("");
    $("#tarifa_vig_ini").val(teHoyISO());
    $("#tarifa_bloque_precio_inicial").show();
    $("#modal_tarifa").modal("show");
  });

  // ---- editar tarifa ----
  $("#tabla_tarifas tbody").on("click", ".te-editar", function () {
    const row = tablaTarifas.row($(this).closest("tr")).data();
    $("#modal_tarifa_titulo").text("Editar tarifa #" + row.id);
    $("#tarifa_id").val(row.id);
    $("#tarifa_nombre").val(row.Nombre);
    $("#tarifa_obs").val(row.Observaciones || "");
    $("#tarifa_bloque_precio_inicial").hide();
    $("#modal_tarifa").modal("show");
  });

  $("#btn_guardar_tarifa").on("click", function () {
    const id = $("#tarifa_id").val();
    const payload = {
      GuardarTarifa: 1,
      id: id || 0,
      Nombre: $("#tarifa_nombre").val().trim(),
      Observaciones: $("#tarifa_obs").val().trim(),
    };
    if (!id) {
      payload.PrecioInicial = $("#tarifa_precio_ini").val();
      payload.VigenciaInicial = $("#tarifa_vig_ini").val();
    }
    $.post(TE_URL, payload, null, "json").done(function (r) {
      if (r.success == 1) {
        $("#modal_tarifa").modal("hide");
        cargarTarifas();
        Swal.fire({ icon: "success", title: r.msg, timer: 1200, showConfirmButton: false });
      } else {
        Swal.fire({ icon: "warning", title: "No se guardó", text: r.msg || "" });
      }
    });
  });

  // ---- historial de precios ----
  $("#tabla_tarifas tbody").on("click", ".te-historial", function () {
    const id = $(this).data("id");
    const nombre = $(this).data("nombre");
    $("#historial_idtarifa").val(id);
    $("#historial_nombre").text(nombre);
    $("#nuevo_precio").val("");
    $("#nuevo_vigencia").val(teHoyISO());
    $("#nuevo_obs").val("");
    cargarHistorial(id);
    $("#modal_historial").modal("show");
  });

  function cargarHistorial(id) {
    $.post(TE_URL, { Historial: 1, id: id }, null, "json").done(function (r) {
      const rows = (r && r.data) || [];
      if (!rows.length) {
        $("#historial_lista").html('<tr><td colspan="5" class="text-center text-muted">Sin precios cargados.</td></tr>');
        return;
      }
      $("#historial_lista").html(
        rows
          .map(function (p) {
            return (
              `<tr class="${p.rige_hoy ? "htp-vig" : ""}">` +
              `<td>${teFechaDMY(p.VigenciaDesde)} ${p.rige_hoy ? '<span class="badge bg-success">rige hoy</span>' : p.vigente == 0 ? '<span class="badge bg-warning text-dark">a futuro</span>' : ""}</td>` +
              `<td class="text-end fw-bold">${teMoney(p.Precio)}</td>` +
              `<td>${$("<div>").text(p.Usuario || "").html()}</td>` +
              `<td class="text-muted">${$("<div>").text(p.Observaciones || "").html()}</td>` +
              `<td><button class="btn btn-sm btn-outline-danger te-del-precio" data-id="${p.id}"><i class="mdi mdi-delete"></i></button></td>` +
              `</tr>`
            );
          })
          .join("")
      );
    });
  }

  $("#btn_agregar_precio").on("click", function () {
    const idTarifa = $("#historial_idtarifa").val();
    const precio = $("#nuevo_precio").val();
    const vig = $("#nuevo_vigencia").val();
    if (!precio || parseFloat(precio) <= 0 || !vig) {
      Swal.fire({ icon: "warning", title: "Cargá precio (> 0) y fecha de vigencia." });
      return;
    }
    $.post(
      TE_URL,
      { GuardarPrecio: 1, idTarifa: idTarifa, Precio: precio, VigenciaDesde: vig, Observaciones: $("#nuevo_obs").val().trim() },
      null,
      "json"
    ).done(function (r) {
      if (r.success == 1) {
        $("#nuevo_precio").val("");
        $("#nuevo_obs").val("");
        cargarHistorial(idTarifa);
        cargarTarifas();
        Swal.fire({ icon: "success", title: r.msg, timer: 1100, showConfirmButton: false });
      } else {
        Swal.fire({ icon: "warning", title: "No se guardó", text: r.msg || "" });
      }
    });
  });

  $("#historial_lista").on("click", ".te-del-precio", function () {
    const idPrecio = $(this).data("id");
    const idTarifa = $("#historial_idtarifa").val();
    Swal.fire({
      icon: "warning",
      title: "Borrar este precio",
      text: "Se recalcula el precio vigente de la tarifa.",
      showCancelButton: true,
      confirmButtonText: "Sí, borrar",
      cancelButtonText: "Cancelar",
    }).then(function (res) {
      if (!res.isConfirmed) return;
      $.post(TE_URL, { EliminarPrecio: 1, id: idPrecio }, null, "json").done(function (r) {
        if (r.success == 1) {
          cargarHistorial(idTarifa);
          cargarTarifas();
        } else {
          Swal.fire({ icon: "warning", title: "No se pudo borrar", text: r.msg || "" });
        }
      });
    });
  });
});
