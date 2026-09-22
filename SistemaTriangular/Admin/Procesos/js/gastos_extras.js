// Admin/GastosExtras.php - gastos "de gestión" fuera del circuito contable
// formal (no tocan Tesoreria), sólo impactan el Cuadro de Resultados.

const CATEGORIA_LABEL = {
  Personal: "Personal",
  Logistica: "Logística",
  Generales: "Generales",
  Financieros: "Financieros/Impuestos",
};

function formatearMoneda(n) {
  const num = Number(n) || 0;
  return "$" + num.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function primerDiaDelMes() {
  const d = new Date();
  return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10);
}
function hoyISO() {
  return new Date().toISOString().slice(0, 10);
}

let tablaGastosExtras = null;

function pintarTotales(totales) {
  const $cont = $("#ge-totales");
  $cont.empty();
  Object.keys(CATEGORIA_LABEL).forEach((cat) => {
    const total = (totales && totales[cat]) || 0;
    $cont.append(`
      <div class="col-auto">
        <span class="badge badge-categoria-${cat} p-2">
          ${CATEGORIA_LABEL[cat]}: ${formatearMoneda(total)}
        </span>
      </div>
    `);
  });
}

function cargarListado() {
  const desde = $("#ge-desde").val();
  const hasta = $("#ge-hasta").val();
  if (!desde || !hasta) return;

  $.ajax({
    url: "Procesos/php/gastos_extras.php",
    type: "POST",
    dataType: "json",
    data: { action: "listar", desde, hasta },
  })
    .done(function (r) {
      if (!r || r.success != 1) {
        Swal.fire("Error", (r && r.error) || "No se pudo cargar el listado", "error");
        return;
      }
      pintarTotales(r.total_por_categoria);

      if (tablaGastosExtras) {
        tablaGastosExtras.destroy();
        $("#tabla-gastos-extras tbody").empty();
      }
      const filas = (r.data || [])
        .map(
          (g) => `
        <tr data-id="${g.id}">
          <td>${g.Fecha.split("-").reverse().join("/")}</td>
          <td><span class="badge badge-categoria-${g.Categoria}">${CATEGORIA_LABEL[g.Categoria] || g.Categoria}</span></td>
          <td>${$("<div>").text(g.Descripcion).html()}</td>
          <td>${formatearMoneda(g.Importe)}</td>
          <td>${$("<div>").text(g.Observaciones || "").html()}</td>
          <td>${g.Usuario}</td>
          <td>
            <button class="btn btn-sm btn-light btn-editar-gasto" data-id="${g.id}" title="Editar"><i class="uil-edit-alt"></i></button>
            <button class="btn btn-sm btn-light text-danger btn-eliminar-gasto" data-id="${g.id}" title="Eliminar"><i class="uil-trash-alt"></i></button>
          </td>
        </tr>
      `
        )
        .join("");
      $("#tabla-gastos-extras tbody").html(filas);
      tablaGastosExtras = $("#tabla-gastos-extras").DataTable({
        order: [],
      });
      window.__gastosExtrasData = {};
      (r.data || []).forEach((g) => (window.__gastosExtrasData[g.id] = g));
    })
    .fail(function () {
      Swal.fire("Error de red", "No se pudo conectar con el servidor.", "error");
    });
}

function abrirModalNuevo() {
  $("#ge-modal-titulo").text("Nuevo Gasto Extra");
  $("#ge-id").val("");
  $("#ge-fecha").val(hoyISO());
  $("#ge-categoria").val("Personal");
  $("#ge-descripcion").val("");
  $("#ge-importe").val("");
  $("#ge-observaciones").val("");
  new bootstrap.Modal(document.getElementById("modal-gasto-extra")).show();
}

function abrirModalEditar(id) {
  const g = window.__gastosExtrasData && window.__gastosExtrasData[id];
  if (!g) return;
  $("#ge-modal-titulo").text("Editar Gasto Extra");
  $("#ge-id").val(g.id);
  $("#ge-fecha").val(g.Fecha);
  $("#ge-categoria").val(g.Categoria);
  $("#ge-descripcion").val(g.Descripcion);
  $("#ge-importe").val(g.Importe);
  $("#ge-observaciones").val(g.Observaciones || "");
  new bootstrap.Modal(document.getElementById("modal-gasto-extra")).show();
}

$(document).ready(function () {
  $("#ge-desde").val(primerDiaDelMes());
  $("#ge-hasta").val(hoyISO());
  cargarListado();

  $("#ge-buscar").on("click", cargarListado);
  $("#ge-nuevo").on("click", abrirModalNuevo);

  $(document).on("click", ".btn-editar-gasto", function () {
    abrirModalEditar($(this).data("id"));
  });

  $(document).on("click", ".btn-eliminar-gasto", function () {
    const id = $(this).data("id");
    Swal.fire({
      icon: "warning",
      title: "¿Eliminar este gasto extra?",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#fa5c7c",
    }).then((res) => {
      if (!res.isConfirmed) return;
      $.ajax({
        url: "Procesos/php/gastos_extras.php",
        type: "POST",
        dataType: "json",
        data: { action: "eliminar", id },
      }).done((r) => {
        if (r && r.success == 1) {
          cargarListado();
        } else {
          Swal.fire("Error", (r && r.error) || "No se pudo eliminar", "error");
        }
      });
    });
  });

  $("#ge-guardar").on("click", function () {
    const id = $("#ge-id").val();
    const payload = {
      action: id ? "editar" : "agregar",
      id: id,
      fecha: $("#ge-fecha").val(),
      categoria: $("#ge-categoria").val(),
      descripcion: $("#ge-descripcion").val().trim(),
      importe: $("#ge-importe").val(),
      observaciones: $("#ge-observaciones").val().trim(),
    };
    if (!payload.fecha || !payload.descripcion || !payload.importe) {
      Swal.fire("Faltan datos", "Completá fecha, descripción e importe.", "warning");
      return;
    }
    $.ajax({
      url: "Procesos/php/gastos_extras.php",
      type: "POST",
      dataType: "json",
      data: payload,
    })
      .done((r) => {
        if (r && r.success == 1) {
          bootstrap.Modal.getInstance(document.getElementById("modal-gasto-extra")).hide();
          cargarListado();
        } else {
          Swal.fire("Error", (r && r.error) || "No se pudo guardar", "error");
        }
      })
      .fail(() => {
        Swal.fire("Error de red", "No se pudo conectar con el servidor.", "error");
      });
  });
});
