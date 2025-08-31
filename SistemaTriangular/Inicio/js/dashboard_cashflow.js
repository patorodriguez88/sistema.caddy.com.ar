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

//Insertar encabezados Gastos
window.addEventListener("DOMContentLoaded", function () {
  const thead = document.querySelector("#cashflow-meses_gastos");
  if (thead) {
    thead.innerHTML =
      `<tr class="bg-danger text-white">
         <th class="text-center fs-5 py-2" colspan="${headers.length + 2}">
           <i class="mdi mdi-cash-multiple me-2"></i>Detalle de Gastos Caddy - Últimos 12 Meses
         </th>
       </tr>
       <tr>
         <th>Cuenta</th><th>Nombre</th>` +
      headers.map((m) => `<th>${m}</th>`).join("") +
      `</tr>`;
  }
});
// === Mapeo de cuentas a grupos de gasto ===
const MAP_CUENTAS_A_GRUPO = {
  // PERSONAL
  "000420700": "Personal",
  "000420800": "Personal",
  "000421400": "Personal",
  "000402400": "Personal",
  // LOGISTICA / OPERACIONES
  "000420600": "Logistica",
  "000422700": "Logistica",
  "000421600": "Logistica",
  "000421800": "Logistica",
  "000420200": "Logistica",
  // GENERALES / ADMIN
  "000421200": "Generales",
  "000421300": "Generales",
  "000421700": "Generales",
  "000420900": "Generales",
  "000424200": "Generales",
  "000424100": "Generales",
  "000422500": "Generales",
  "000424000": "Generales",
  "000421900": "Generales",
  "000425000": "Generales",
  "0004210000": "Generales",
  // FINANCIEROS / IMPUESTOS
  "000421000": "Financieros",
  "000420300": "Financieros",
  "000423900": "Financieros",
  "000423300": "Financieros",
  "000423400": "Financieros",
  "000422800": "Financieros",
  "000423200": "Financieros",
  "000424600": "Financieros",
  "000424700": "Financieros",
  "000424800": "Financieros",
  "000423700": "Financieros",
  "000423600": "Financieros",
};
function grupoPorCuenta(cuenta) {
  if (!cuenta) return "Generales";
  const key = String(cuenta).trim();
  return MAP_CUENTAS_A_GRUPO[key] || "Generales";
}
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
      const gastos = data.gastos || {};
      const ventasCobranza = data.ventas_cobranza || {};

      let fila2 = `<td>Ventas Simples</td>`;
      let fila3 = `<td>Ventas Flex</td>`;
      let fila4 = `<td>Ventas Recorridos</td>`;
      let fila5 = `<td>Total Ventas</td>`;
      let filaGastos = `<td>Gastos</td>`;
      let fila6 = `<td>Saldo Final</td>`;
      let filaCobranza = `<td>Cobranza (5%)</td>`;

      // let primero = true;
      const formato = {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      };

      headers.forEach((hm) => {
        const [mesAbrev, anioCorto] = hm.split("-");
        const mesIndex = meses.indexOf(mesAbrev) + 1;
        const anioCompleto = parseInt("20" + anioCorto);
        const key = `${anioCompleto}-${String(mesIndex).padStart(2, "0")}`;

        // Convertir todas las ventas sin IVA
        const v1 = parseFloat(ventasSimples[key] || 0) / 1.21;
        const v2 = parseFloat(ventasFlex[key] || 0) / 1.21;
        const v3 = parseFloat(ventasRecorridos[key] || 0) / 1.21;
        const vc = parseFloat(ventasCobranza[key] || 0);
        const vg = parseFloat(gastos[key] || 0);

        const totalVenta = v1 + v2 + v3 + vc;
        const saldoFinal = totalVenta - vg;

        filaCobranza += `<td>$ ${vc.toLocaleString("es-AR", formato)}</td>`;

        fila2 += `<td>$ ${v1.toLocaleString("es-AR", formato)}</td>`;
        fila3 += `<td>$ ${v2.toLocaleString("es-AR", formato)}</td>`;
        fila4 += `<td>$ ${v3.toLocaleString("es-AR", formato)}</td>`;
        fila5 += `<td>$ ${totalVenta.toLocaleString("es-AR", formato)}</td>`;
        filaGastos += `<td>$ ${vg.toLocaleString("es-AR", formato)}</td>`;
        fila6 += `<td>$ ${saldoFinal.toLocaleString("es-AR", formato)}</td>`;
      });
      $("#cashflow-body").html(`

        <tr>${fila2}</tr>
        <tr>${fila3}</tr>
        <tr>${fila4}</tr>
        <tr>${filaCobranza}</tr>
        <tr class="table-success fw-bold">${fila5}</tr>
        <tr class="table-danger fw-bold">${filaGastos}</tr>
        <tr>${fila6}</tr>
      `);
    },
    error: function (err) {
      console.error("Error cargando cashflow:", err);
    },
  });
}

function cargarGastosDetalle(anio) {
  $.post(
    "../Inicio/php/dashboard_cashflow_gastos.php",
    { anio },
    function (res) {
      const { datos } = JSON.parse(res);

      let rows = [];

      datos.forEach((fila) => {
        const grupo = grupoPorCuenta(fila.cuenta);
        const rowClass = "grp-" + grupo.toLowerCase();
        let row = `<tr class="${rowClass}" data-grupo="${grupo}">`;
        row += `<td>${fila.cuenta}</td>`;
        row += `<td>${fila.nombre}</td>`;

        // Obtener el valor máximo de la fila (para resaltar)
        let valoresFila = headers.map((hm) => fila[hm] ?? 0);
        let maxValor = Math.max(...valoresFila);

        headers.forEach((hm) => {
          const valor = fila[hm] ?? 0;
          const esMaximo = valor === maxValor && valor !== 0;
          row += `<td class="text-end ${
            esMaximo ? "text-danger fw-bold" : ""
          }">$ ${valor.toLocaleString("es-AR", {
            minimumFractionDigits: 0,
          })}</td>`;
        });

        row += `</tr>`;
        rows.push(row);
      });

      // Totales por mes
      let totalesPorMes = {};
      headers.forEach((hm) => (totalesPorMes[hm] = 0));

      datos.forEach((fila) => {
        headers.forEach((hm) => {
          const valor = fila[hm] ?? 0;
          totalesPorMes[hm] += valor;
        });
      });

      const groupOrder = [
        "grp-personal",
        "grp-logistica",
        "grp-generales",
        "grp-financieros",
      ];
      rows.sort((a, b) => {
        const gA = groupOrder.findIndex((g) => a.includes(g));
        const gB = groupOrder.findIndex((g) => b.includes(g));
        return gA - gB;
      });

      let tbody = rows.join("");

      let filaTotal = `<tr class="fw-bold bg-light"><td colspan="2">TOTAL POR MES</td>`;
      headers.forEach((hm) => {
        filaTotal += `<td class="text-end">$ ${totalesPorMes[hm].toLocaleString(
          "es-AR",
          {
            minimumFractionDigits: 0,
          }
        )}</td>`;
      });
      filaTotal += `</tr>`;
      tbody += filaTotal;

      $("#cashflow-body_gastos").html(tbody);
    }
  );
}

function cargarGraficosCashflow(anio) {
  $.post(
    "../Inicio/php/dashboard_cashflow_graficos.php",
    { anio },
    function (res) {
      const {
        ventas_simples,
        ventas_recorridos,
        ventas_cobranza,
        gastos,
        saldo,
      } = JSON.parse(res);

      const meses = Object.keys({
        ...ventas_simples,
        ...ventas_recorridos,
        ...ventas_cobranza,
        ...gastos,
        ...saldo,
      }).sort();

      // const serieVentas = meses.map((m) => ventas[m] ?? 0) / 1.21;
      const serieVentas = meses.map((m) => {
        const simples =
          ventas_simples[m] !== undefined ? ventas_simples[m] / 1.21 : 0;
        const recorridos =
          ventas_recorridos[m] !== undefined ? ventas_recorridos[m] / 1.21 : 0;
        const cobranza =
          ventas_cobranza[m] !== undefined ? ventas_cobranza[m] : 0;
        return simples + recorridos + cobranza;
      });
      const serieGastos = meses.map((m) =>
        gastos[m] !== undefined ? gastos[m] : 0
      );

      const serieSaldo = meses.map((m) =>
        saldo[m] !== undefined ? saldo[m] : 0
      );

      const options = {
        chart: {
          type: "line",
          height: 350,
          toolbar: { show: false },
        },
        series: [
          { name: "Ventas Totales", data: serieVentas },
          { name: "Gastos", data: serieGastos },
          { name: "Saldo Final", data: serieSaldo },
        ],
        xaxis: {
          categories: meses.map((m) => {
            const [y, mo] = m.split("-");
            return `${mo}/${y.slice(2)}`;
          }),
        },
        yaxis: {
          labels: {
            formatter: (val) =>
              "$ " + val.toLocaleString("es-AR", { minimumFractionDigits: 0 }),
          },
        },
        colors: ["#3bafda", "#fa5c7c", "#10c469"],
      };

      const chart = new ApexCharts(
        document.querySelector("#grafico-cashflow"),
        options
      );
      chart.render();
    }
  );
}
// ======= PARTICIPACIÓN DE GASTOS =======
function construirEncabezadosParticipacion() {
  // Pesos
  const thPesos = `
    <tr class="bg-light">
      <th class="text-center fs-6 py-2" colspan="${headers.length + 1}">
        <i class="mdi mdi-cash-multiple me-2"></i>Gastos por Grupo – Últimos 12 Meses (en $)
      </th>
    </tr>
    <tr><th>Grupo</th>${headers.map((h) => `<th>${h}</th>`).join("")}</tr>`;
  $("#part-meses-pesos").html(thPesos);

  // Porcentajes
  const thPorc = `
    <tr class="bg-dark text-white">
      <th class="text-center fs-6 py-2" colspan="${headers.length + 1}">
        <i class="mdi mdi-percent me-2"></i>Participación por Grupo – Últimos 12 Meses (%)
      </th>
    </tr>
    <tr><th>Grupo</th>${headers.map((h) => `<th>${h}</th>`).join("")}</tr>`;
  $("#part-meses-porc").html(thPorc);
}

function cargarParticipacion(anio) {
  $.post(
    "../Inicio/php/dashboard_cashflow_participacion.php",
    { anio },
    function (res) {
      const { grupos, meses, totales, total_mes, porcentajes } =
        JSON.parse(res);

      // ------ Gráfico 100% Stacked (ApexCharts) ------
      const categorias = meses.map((m) => {
        const [yy, mm] = m.split("-");
        return `${mm}/${yy.slice(2)}`; // mm/aa
      });

      const series = grupos.map((g) => ({
        name: g,
        data: meses.map((m) => porcentajes[m]?.[g] ?? 0),
      }));

      const options = {
        chart: {
          type: "bar",
          height: 360,
          stacked: true,
          stackType: "100%",
          toolbar: { show: false },
        },
        series,
        xaxis: { categories: categorias },
        yaxis: { labels: { formatter: (v) => v.toFixed(0) + "%" } },
        dataLabels: { enabled: false },
        tooltip: {
          y: {
            formatter: (v) =>
              v.toLocaleString("es-AR", { minimumFractionDigits: 2 }) + " %",
          },
        },
        legend: { position: "top" },
      };
      const chart = new ApexCharts(
        document.querySelector("#grafico-participacion"),
        options
      );
      chart.render();

      // ------ Tablas ($ y %) con tu mismo header 'headers' ------
      const formatoPesos = { minimumFractionDigits: 0 };
      const cuerpoPesos = [];
      const cuerpoPorc = [];

      const groupOrder = ["Personal", "Logistica", "Generales", "Financieros"];

      // Asumo que 'meses' y 'headers' están alineados 1:1 en orden (últimos 12 hasta hoy)
      groupOrder.forEach((g) => {
        let tdsPesos = `<td>${g}</td>`;
        let tdsPorc = `<td>${g}</td>`;
        meses.forEach((m, idx) => {
          const val = totales[m]?.[g] ?? 0;
          const pct = porcentajes[m]?.[g] ?? 0;
          tdsPesos += `<td class="text-end">$ ${val.toLocaleString(
            "es-AR",
            formatoPesos
          )}</td>`;
          tdsPorc += `<td class="text-end">${pct.toLocaleString("es-AR", {
            minimumFractionDigits: 2,
          })}%</td>`;
        });
        cuerpoPesos.push(`<tr class="grp-${g.toLowerCase()}">${tdsPesos}</tr>`);
        cuerpoPorc.push(`<tr class="grp-${g.toLowerCase()}">${tdsPorc}</tr>`);
      });

      // Fila TOTAL GASTOS (solo para $)
      let filaTotal = `<tr class="fw-bold bg-light"><td>Total Gastos</td>`;
      meses.forEach((m, idx) => {
        const tm = total_mes[m] ?? 0;
        filaTotal += `<td class="text-end">$ ${tm.toLocaleString(
          "es-AR",
          formatoPesos
        )}</td>`;
      });
      filaTotal += `</tr>`;

      $("#part-body-pesos").html(cuerpoPesos.join("") + filaTotal);
      $("#part-body-porc").html(cuerpoPorc.join(""));
    }
  );
}
$(document).ready(function () {
  const anioActual = new Date().getFullYear();
  cargarCashflow(anioActual);
  cargarGastosDetalle(anioActual);
  cargarGraficosCashflow(anioActual);

  construirEncabezadosParticipacion();
  cargarParticipacion(anioActual);
});
