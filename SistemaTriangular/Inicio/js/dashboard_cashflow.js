const meses = [
  "ene",
  "feb",
  "mar",
  "abr",
  "may",
  "jun",
  "jul",
  "ago",
  "sep",
  "oct",
  "nov",
  "dic",
];

const hoy = new Date();
let headers = [];

// Generar últimos 12 meses desde hoy
for (let i = 11; i >= 0; i--) {
  let fecha = new Date(hoy.getFullYear(), hoy.getMonth() - i, 1);
  let mesAbrev = meses[fecha.getMonth()];
  let anio = fecha.getFullYear();
  headers.push(`${mesAbrev}-${String(anio).slice(-2)}`);
}

// Insertar encabezados
window.addEventListener("DOMContentLoaded", function () {
  const thead = document.querySelector("#cashflow-meses");
  if (thead) {
    thead.innerHTML =
      `<tr class="bg-dark text-white">
           <th class="text-center fs-5 py-2" colspan="${headers.length + 1}">
             <i class="mdi mdi-cash-multiple me-2"></i>Cashflow Caddy - Ultimos 12 Meses
           </th>
         </tr>
         <tr>
           <th></th>` +
      headers.map((m) => `<th>${m}</th>`).join("") +
      `</tr>`;
  }
});

function cargarCashflow(anio) {
  $.ajax({
    url: "../Inicio/php/dashboard_cashflow.php",
    type: "POST",
    data: { anio: anio },
    dataType: "json",
    success: function (data) {
      const ventasSimples = data.ventas_simples || {};
      const ventasFlex = data.ventas_flex || {};
      const ventasRecorridos = data.ventas_recorridos || {};
      let saldo = parseFloat(data.saldo_inicial || 0);
      let acumulado = saldo;

      let fila1 = `<td>Saldo Inicial</td>`;
      let fila2 = `<td>Ventas Simples</td>`;
      let fila3 = `<td>Ventas Flex</td>`;
      let fila4 = `<td>Ventas Recorridos</td>`;
      let fila5 = `<td>Total Ventas</td>`;
      let fila6 = `<td>Saldo Final</td>`;
      let fila7 = `<td>Saldo Acumulado</td>`;

      let primero = true;

      headers.forEach((hm) => {
        const [mesAbrev, anioCorto] = hm.split("-");
        const mesIndex = meses.indexOf(mesAbrev) + 1;
        const anioCompleto = parseInt("20" + anioCorto);
        const key = `${anioCompleto}-${String(mesIndex).padStart(2, "0")}`;

        const v1 = parseFloat(ventasSimples[key] || 0);
        const v2 = parseFloat(ventasFlex[key] || 0);
        const v3 = parseFloat(ventasRecorridos[key] || 0);
        const totalVenta = v1 + v2 + v3;

        const formato = {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        };

        fila1 += `<td>${
          primero ? "$ " + saldo.toLocaleString("es-AR", formato) : ""
        }</td>`;
        fila2 += `<td>$ ${v1.toLocaleString("es-AR", formato)}</td>`;
        fila3 += `<td>$ ${v2.toLocaleString("es-AR", formato)}</td>`;
        fila4 += `<td>$ ${v3.toLocaleString("es-AR", formato)}</td>`;
        fila5 += `<td>$ ${totalVenta.toLocaleString("es-AR", formato)}</td>`;

        saldo += totalVenta;
        fila6 += `<td>$ ${saldo.toLocaleString("es-AR", formato)}</td>`;
        acumulado += totalVenta;
        fila7 += `<td>$ ${acumulado.toLocaleString("es-AR", formato)}</td>`;

        primero = false;
      });

      $("#cashflow-body").html(`
          <tr>${fila1}</tr>
          <tr>${fila2}</tr>
          <tr>${fila3}</tr>
          <tr>${fila4}</tr>
          <tr class="table-success fw-bold">${fila5}</tr>
          <tr>${fila6}</tr>
          <tr>${fila7}</tr>
        `);
    },
    error: function (err) {
      console.error("Error cargando cashflow:", err);
    },
  });
}

$(document).ready(function () {
  const anioActual = new Date().getFullYear();
  cargarCashflow(anioActual);
});
