// Admin/Cheques.php - listado de cheques de terceros y propios (solo lectura).

const ESTADOS = {
  1: ["Recibido", "Entregado a proveedor"],
  0: ["Pendiente de débito", "Debitado"],
};

let chTerceros = 1;
let chFilas = [];
let tablaCheques = null;

function chMoneda(n) {
  const num = Number(n) || 0;
  return "$ " + num.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function chFecha(f) {
  if (!f) return "";
  const [a, m, d] = f.split("-");
  return `${d}/${m}/${a}`;
}

function chFiltradas() {
  const estado = $("#ch-estado").val();
  return estado ? chFilas.filter((r) => r.Estado === estado) : chFilas;
}

function chPintarTotales() {
  const $t = $("#ch-totales").empty();
  ESTADOS[chTerceros].forEach((estado) => {
    const filas = chFilas.filter((r) => r.Estado === estado);
    const total = filas.reduce((s, r) => s + (Number(r.Importe) || 0), 0);
    $t.append(
      `<span class="badge bg-light text-dark p-2 ms-1">${estado}: ${filas.length} · ${chMoneda(total)}</span>`
    );
  });
}

function chPintarTabla() {
  if (tablaCheques) {
    tablaCheques.clear().rows.add(chFiltradas()).draw();
    return;
  }
  tablaCheques = $("#tabla-cheques").DataTable({
    data: chFiltradas(),
    dom: "Bfrtip",
    buttons: buildDtButtons(["pageLength", "copy", "excel", "print"]),
    order: [[0, "desc"]],
    pageLength: 25,
    columns: [
      { data: "FechaCobro", render: (d, type) => (type === "display" ? chFecha(d) : d || "") },
      { data: "Banco" },
      { data: "NumeroCheque" },
      { data: "Proveedor" },
      { data: "Importe", className: "text-end", render: (d, type) => (type === "display" ? chMoneda(d) : Number(d) || 0) },
      {
        data: "Estado",
        render: (d) => {
          const verde = d === "Entregado a proveedor" || d === "Debitado";
          return `<span class="badge ${verde ? "bg-success" : "bg-secondary"}">${d}</span>`;
        },
      },
      { data: "Asiento" },
    ],
  });
}

function chCargar() {
  $("#ch-col-proveedor").text(chTerceros ? "Recibido de / Entregado a" : "Proveedor");
  const $estado = $("#ch-estado").empty().append('<option value="">Todos</option>');
  ESTADOS[chTerceros].forEach((e) => $estado.append(`<option value="${e}">${e}</option>`));

  $.ajax({
    url: "Procesos/php/cheques.php",
    type: "POST",
    dataType: "json",
    data: { terceros: chTerceros },
  })
    .done(function (r) {
      chFilas = (r && r.data) || [];
      chPintarTotales();
      chPintarTabla();
    })
    .fail(function () {
      chFilas = [];
      chPintarTotales();
      chPintarTabla();
      if (typeof toast === "function") toast("error", "Error", "No se pudieron cargar los cheques");
    });
}

$(function () {
  $(".nav-tabs .nav-link").on("click", function (e) {
    e.preventDefault();
    $(".nav-tabs .nav-link").removeClass("active");
    $(this).addClass("active");
    chTerceros = Number($(this).data("terceros"));
    chCargar();
  });
  $("#ch-estado").on("change", chPintarTabla);
  chCargar();
});
