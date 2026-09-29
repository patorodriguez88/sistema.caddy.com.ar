// Resultados / CashFlow (Inicio/dashboard_.php)
// Datos: php/dashboard_cashflow.php (ventas y gastos por mes) y
// php/dashboard_cashflow_gastos.php (gastos por cuenta). La estructura de gastos
// por grupo se calcula acá desde el detalle, así los tres cuadros suman lo mismo.
// "Descargar Excel" arma el .xlsx en el navegador (ExcelJS) con el Cashflow
// arriba y el Detalle de Gastos abajo.
(function () {
  "use strict";

  const MESES = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
  const GRUPOS = ["Personal", "Logistica", "Generales", "Financieros"];
  const GRUPO_LABEL = { Personal: "Personal", Logistica: "Logística", Generales: "Generales", Financieros: "Financieros / Impuestos" };
  const GRUPO_COLOR = { Personal: "#727cf5", Logistica: "#39afd1", Generales: "#ffbc00", Financieros: "#fa5c7c" };
  const IVA = 1.21;

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
    "000420400": "Generales",
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
  function grupoDe(fila) {
    if (fila.grupo && GRUPOS.includes(fila.grupo)) return fila.grupo; // gastos extras
    const key = String(fila.cuenta || "").trim();
    return MAP_CUENTAS_A_GRUPO[key] || "Generales";
  }

  // Últimos 12 meses: key "2026-09" (dashboard_cashflow.php) y label "sep-26" (dashboard_cashflow_gastos.php)
  const hoy = new Date();
  const COLS = [];
  for (let i = 11; i >= 0; i--) {
    const f = new Date(hoy.getFullYear(), hoy.getMonth() - i, 1);
    COLS.push({
      key: `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, "0")}`,
      label: `${MESES[f.getMonth()]}-${String(f.getFullYear()).slice(-2)}`,
      parcial: i === 0,
    });
  }

  let cash = null; // filas del cashflow, por mes
  let gastos = null; // [{cuenta, nombre, grupo, valores[12], total}]
  const charts = {};
  let partModo = "pesos";

  // ---------- Formato ----------
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });
  const fmt2 = new Intl.NumberFormat("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const pesos = (v) => { const r = Math.round(v || 0); return (r < 0 ? "-$ " : "$ ") + fmt0.format(Math.abs(r)); };
  const pesosCorto = (v) => {
    const a = Math.abs(v);
    if (a >= 1e6) return (v < 0 ? "-$ " : "$ ") + fmt2.format(a / 1e6).replace(/,00$/, "") + " M";
    if (a >= 1e3) return (v < 0 ? "-$ " : "$ ") + fmt0.format(a / 1e3) + " K";
    return pesos(v);
  };
  const pct = (v) => (isFinite(v) ? fmt2.format(v).replace(/,?0+$/, "").replace(/,$/, "") : "0") + " %";
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const celda = (v, extra = "") => `<td class="${v === 0 ? "cf-zero " : ""}${extra}">${pesos(v)}</td>`;
  const suma = (arr) => arr.reduce((a, b) => a + b, 0);

  // ---------- Carga ----------
  function cargar() {
    $("#cf-excel").prop("disabled", true);
    $("#cf-actualizar .mdi").addClass("mdi-spin");
    const pCash = $.ajax({ url: "php/dashboard_cashflow.php", type: "POST", dataType: "json" });
    const pGastos = $.ajax({ url: "php/dashboard_cashflow_gastos.php", type: "POST", dataType: "json" });

    $.when(pCash, pGastos)
      .done(function (rCash, rGastos) {
        armarCash(rCash[0] || {});
        armarGastos((rGastos[0] || {}).datos || []);
        renderTodo();
        $("#cf-excel").prop("disabled", false);
      })
      .fail(function (xhr) {
        console.error("CashFlow: error cargando datos", xhr);
        $("#cf-tabla-cashflow tbody, #cf-tabla-gastos tbody").html('<tr><td class="cf-loading" colspan="15">No se pudieron cargar los datos. Probá con Actualizar.</td></tr>');
      })
      .always(function () {
        $("#cf-actualizar .mdi").removeClass("mdi-spin");
      });
  }

  function armarCash(d) {
    const v = (obj, k) => parseFloat((obj || {})[k] || 0);
    const simples = COLS.map((c) => v(d.ventas_simples, c.key) / IVA);
    const flex = COLS.map((c) => v(d.ventas_flex, c.key) / IVA);
    const recorridos = COLS.map((c) => v(d.ventas_recorridos, c.key) / IVA);
    const cobranza = COLS.map((c) => v(d.ventas_cobranza, c.key));
    const total = COLS.map((_, i) => simples[i] + flex[i] + recorridos[i] + cobranza[i]);
    const gasto = COLS.map((c) => v(d.gastos, c.key));
    const saldo = COLS.map((_, i) => total[i] - gasto[i]);
    cash = { simples, flex, recorridos, cobranza, total, gasto, saldo };
  }

  function armarGastos(datos) {
    gastos = datos.map((f) => {
      const valores = COLS.map((c) => parseFloat(f[c.label] || 0));
      return { cuenta: f.cuenta || "", nombre: f.nombre || "", grupo: grupoDe(f), valores, total: suma(valores) };
    });
    gastos.sort((a, b) => GRUPOS.indexOf(a.grupo) - GRUPOS.indexOf(b.grupo) || b.total - a.total);
  }

  function totalesPorGrupo() {
    const t = {};
    GRUPOS.forEach((g) => (t[g] = COLS.map(() => 0)));
    gastos.forEach((f) => f.valores.forEach((v, i) => (t[f.grupo][i] += v)));
    return t;
  }

  // ---------- Render ----------
  function renderTodo() {
    const primero = COLS[0].label, ultimo = COLS[COLS.length - 1].label;
    $("#cf-rango").text(`Últimos 12 meses · ${primero} a ${ultimo} · actualizado ${new Date().toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" })}`);
    renderKpis();
    renderChartCash();
    renderTablaCash();
    renderParticipacion();
    renderTablaGastos();
  }

  function renderKpis() {
    // Último mes cerrado (el anterior al actual) vs el mes previo
    const iu = COLS.length - 2, ip = COLS.length - 3;
    const mesU = COLS[iu].label;
    const delta = (a, b, inverso) => {
      if (!b) return "";
      const d = ((a - b) / Math.abs(b)) * 100;
      const bueno = inverso ? d <= 0 : d >= 0;
      return `<span class="cf-delta ${bueno ? "up" : "down"}"><i class="mdi mdi-arrow-${d >= 0 ? "up" : "down"}"></i>${fmt0.format(Math.abs(d))}%</span>`;
    };
    const v12 = suma(cash.total), g12 = suma(cash.gasto), s12 = v12 - g12;
    const margen12 = v12 ? (s12 / v12) * 100 : 0;
    const margenU = cash.total[iu] ? (cash.saldo[iu] / cash.total[iu]) * 100 : 0;
    const margenP = cash.total[ip] ? (cash.saldo[ip] / cash.total[ip]) * 100 : 0;

    const kpi = (color, icono, label, valor, foot, claseValor = "") => `
      <div class="cf-kpi" style="--cf-accent:${color}">
        <div class="cf-kpi-label"><i class="mdi ${icono}"></i>${label}</div>
        <div class="cf-kpi-value ${claseValor}" title="${esc(valor)}">${valor}</div>
        <div class="cf-kpi-foot">${foot}</div>
      </div>`;

    $("#cf-kpis").html(
      kpi("#727cf5", "mdi-trending-up", "Ventas 12 meses", pesosCorto(v12),
        `${mesU}: <b>${pesosCorto(cash.total[iu])}</b> ${delta(cash.total[iu], cash.total[ip])}`) +
      kpi("#fa5c7c", "mdi-cash-minus", "Gastos 12 meses", pesosCorto(g12),
        `${mesU}: <b>${pesosCorto(cash.gasto[iu])}</b> ${delta(cash.gasto[iu], cash.gasto[ip], true)}`) +
      kpi(s12 >= 0 ? "#0acf97" : "#fa5c7c", "mdi-scale-balance", "Resultado 12 meses", pesosCorto(s12),
        `${mesU}: <b class="${cash.saldo[iu] >= 0 ? "cf-pos" : "cf-neg"}">${pesosCorto(cash.saldo[iu])}</b>`, s12 >= 0 ? "cf-pos" : "cf-neg") +
      kpi("#ffbc00", "mdi-percent-outline", "Margen 12 meses", pct(margen12),
        `${mesU}: <b>${pct(margenU)}</b> <span class="text-muted">(mes anterior ${pct(margenP)})</span>`)
    );
  }

  function temaOscuro() {
    return document.documentElement.getAttribute("data-bs-theme") === "dark";
  }

  function renderChartCash() {
    const opciones = {
      chart: { type: "line", height: 340, toolbar: { show: false }, fontFamily: "inherit", foreColor: temaOscuro() ? "#aab8c5" : "#6c757d" },
      theme: { mode: temaOscuro() ? "dark" : "light" },
      series: [
        { name: "Ventas", type: "column", data: cash.total.map(Math.round) },
        { name: "Gastos", type: "column", data: cash.gasto.map(Math.round) },
        { name: "Resultado", type: "line", data: cash.saldo.map(Math.round) },
      ],
      colors: ["#727cf5", "#fa5c7c", "#0acf97"],
      stroke: { width: [0, 0, 3], curve: "smooth" },
      markers: { size: [0, 0, 4], strokeWidth: 0 },
      plotOptions: { bar: { columnWidth: "55%", borderRadius: 4 } },
      fill: { opacity: [0.9, 0.75, 1] },
      xaxis: { categories: COLS.map((c) => (c.parcial ? c.label + " *" : c.label)) },
      yaxis: { labels: { formatter: (v) => pesosCorto(v) } },
      tooltip: { shared: true, intersect: false, y: { formatter: (v) => pesos(v) } },
      grid: { borderColor: temaOscuro() ? "rgba(255,255,255,.07)" : "rgba(0,0,0,.06)", strokeDashArray: 4 },
      legend: { position: "top", horizontalAlign: "right" },
      dataLabels: { enabled: false },
    };
    if (charts.cash) charts.cash.destroy();
    charts.cash = new ApexCharts(document.querySelector("#grafico-cashflow"), opciones);
    charts.cash.render();
  }

  function theadMeses(primera, extraAntes = "", conTotal = true) {
    return `<tr>${extraAntes}<th class="cf-first">${primera}</th>` +
      COLS.map((c) => `<th class="${c.parcial ? "cf-parcial" : ""}">${c.label}</th>`).join("") +
      (conTotal ? `<th class="cf-col-total">Total 12 m</th>` : "") + `</tr>`;
  }

  function renderTablaCash() {
    const fila = (label, arr, clase = "", colorear = false) => {
      const tds = arr.map((v) => colorear ? `<td class="${v >= 0 ? "cf-pos" : "cf-neg"}">${pesos(v)}</td>` : celda(v)).join("");
      const t = suma(arr);
      return `<tr class="${clase}"><td class="cf-first">${label}</td>${tds}<td class="cf-col-total ${colorear ? (t >= 0 ? "cf-pos" : "cf-neg") : ""}">${pesos(t)}</td></tr>`;
    };
    const margen = cash.total.map((v, i) => (v ? (cash.saldo[i] / v) * 100 : 0));
    const margenTotal = suma(cash.total) ? (suma(cash.saldo) / suma(cash.total)) * 100 : 0;

    $("#cf-tabla-cashflow thead").html(theadMeses("Concepto"));
    $("#cf-tabla-cashflow tbody").html(
      fila("Ventas Simples", cash.simples) +
      fila("Ventas Flex", cash.flex) +
      fila("Ventas Recorridos", cash.recorridos) +
      fila("Cobranza (5%)", cash.cobranza) +
      fila("Total Ventas", cash.total, "cf-row-total") +
      fila("Gastos", cash.gasto, "cf-row-gastos") +
      fila("Resultado", cash.saldo, "cf-row-saldo", true) +
      `<tr class="cf-row-margen"><td class="cf-first">Margen</td>${margen.map((m) => `<td>${pct(m)}</td>`).join("")}<td class="cf-col-total">${pct(margenTotal)}</td></tr>`
    );
  }

  function renderParticipacion() {
    const t = totalesPorGrupo();
    const totalMes = COLS.map((_, i) => GRUPOS.reduce((a, g) => a + t[g][i], 0));
    const porc = (g, i) => (totalMes[i] ? (t[g][i] / totalMes[i]) * 100 : 0);

    const opciones = {
      chart: { type: "bar", height: 320, stacked: true, stackType: "100%", toolbar: { show: false }, fontFamily: "inherit", foreColor: temaOscuro() ? "#aab8c5" : "#6c757d" },
      theme: { mode: temaOscuro() ? "dark" : "light" },
      series: GRUPOS.map((g) => ({ name: GRUPO_LABEL[g], data: t[g].map(Math.round) })),
      colors: GRUPOS.map((g) => GRUPO_COLOR[g]),
      plotOptions: { bar: { columnWidth: "55%", borderRadius: 3 } },
      xaxis: { categories: COLS.map((c) => (c.parcial ? c.label + " *" : c.label)) },
      yaxis: { labels: { formatter: (v) => fmt0.format(v) + "%" } },
      tooltip: {
        shared: true,
        intersect: false,
        y: { formatter: (v, o) => `${pesos(v)} · ${pct(porc(GRUPOS[o.seriesIndex], o.dataPointIndex))}` },
      },
      grid: { borderColor: temaOscuro() ? "rgba(255,255,255,.07)" : "rgba(0,0,0,.06)", strokeDashArray: 4 },
      legend: { position: "top", horizontalAlign: "right" },
      dataLabels: { enabled: false },
    };
    if (charts.part) charts.part.destroy();
    charts.part = new ApexCharts(document.querySelector("#grafico-participacion"), opciones);
    charts.part.render();

    const total12 = suma(totalMes);
    const filas = GRUPOS.map((g) => {
      const dot = `<span class="cf-grupo-dot" style="background:${GRUPO_COLOR[g]}"></span>`;
      const tds = COLS.map((_, i) => (partModo === "pesos" ? celda(t[g][i]) : `<td>${pct(porc(g, i))}</td>`)).join("");
      const tot = partModo === "pesos" ? pesos(suma(t[g])) : pct(total12 ? (suma(t[g]) / total12) * 100 : 0);
      return `<tr><td class="cf-first">${dot}${GRUPO_LABEL[g]}</td>${tds}<td class="cf-col-total">${tot}</td></tr>`;
    }).join("");
    const filaTotal = partModo === "pesos"
      ? `<tr class="cf-row-grantotal"><td class="cf-first">Total gastos</td>${totalMes.map((v) => `<td>${pesos(v)}</td>`).join("")}<td class="cf-col-total">${pesos(total12)}</td></tr>`
      : "";
    $("#cf-tabla-participacion thead").html(theadMeses("Grupo"));
    $("#cf-tabla-participacion tbody").html(filas + filaTotal);
  }

  function renderTablaGastos() {
    const t = totalesPorGrupo();
    let html = "";
    GRUPOS.forEach((g) => {
      const filas = gastos.filter((f) => f.grupo === g);
      if (!filas.length) return;
      const clase = "cf-grupo-" + g.toLowerCase();
      html += `<tr class="cf-grupo ${clase}" data-grupo="${g}">
        <td class="cf-cuenta"><i class="mdi mdi-chevron-down"></i></td>
        <td class="cf-first"><span class="cf-grupo-dot"></span>${GRUPO_LABEL[g]} <span class="text-muted fw-normal">(${filas.length})</span></td>
        ${t[g].map((v) => `<td>${pesos(v)}</td>`).join("")}
        <td class="cf-col-total">${pesos(suma(t[g]))}</td></tr>`;
      filas.forEach((f) => {
        const max = Math.max(...f.valores);
        const tds = f.valores.map((v) => celda(v, v === max && v > 0 ? "cf-max" : "")).join("");
        html += `<tr class="cf-cta" data-grupo="${g}" data-buscar="${esc((f.cuenta + " " + f.nombre).toLowerCase())}">
          <td class="cf-cuenta">${esc(f.cuenta)}</td><td class="cf-first">${esc(f.nombre)}</td>${tds}
          <td class="cf-col-total">${pesos(f.total)}</td></tr>`;
      });
    });
    const totalMes = COLS.map((_, i) => gastos.reduce((a, f) => a + f.valores[i], 0));
    html += `<tr class="cf-row-grantotal"><td class="cf-cuenta"></td><td class="cf-first">TOTAL POR MES</td>
      ${totalMes.map((v) => `<td>${pesos(v)}</td>`).join("")}<td class="cf-col-total">${pesos(suma(totalMes))}</td></tr>`;

    $("#cf-tabla-gastos thead").html(theadMeses("Nombre", `<th class="cf-cuenta">Cuenta</th>`));
    $("#cf-tabla-gastos tbody").html(html);
    filtrarGastos();
  }

  function filtrarGastos() {
    const q = ($("#cf-buscar").val() || "").trim().toLowerCase();
    $("#cf-tabla-gastos tr.cf-grupo").each(function () {
      const g = this.getAttribute("data-grupo");
      const cerrado = this.classList.contains("cerrado") && !q;
      let visibles = 0;
      $(`#cf-tabla-gastos tr.cf-cta[data-grupo="${g}"]`).each(function () {
        const ok = !q || this.getAttribute("data-buscar").includes(q);
        if (ok) visibles++;
        this.style.display = ok && !cerrado ? "" : "none";
      });
      this.style.display = visibles ? "" : "none";
    });
  }

  // ---------- Excel ----------
  let excelJsCargando = null;
  function cargarExcelJs() {
    if (window.ExcelJS) return Promise.resolve();
    if (!excelJsCargando) {
      excelJsCargando = new Promise((ok, mal) => {
        const s = document.createElement("script");
        s.src = "https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js";
        s.onload = ok;
        s.onerror = () => { excelJsCargando = null; mal(new Error("No se pudo cargar ExcelJS")); };
        document.head.appendChild(s);
      });
    }
    return excelJsCargando;
  }

  async function descargarExcel() {
    const $b = $("#cf-excel");
    const htmlBoton = $b.html();
    $b.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Generando…');
    try {
      await cargarExcelJs();
      const buf = await construirExcel(window.ExcelJS);
      const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
      const a = document.createElement("a");
      const f = new Date();
      a.href = URL.createObjectURL(blob);
      a.download = `Cashflow_Caddy_${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, "0")}-${String(f.getDate()).padStart(2, "0")}.xlsx`;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
    } catch (e) {
      console.error(e);
      if (window.Swal) Swal.fire("No se pudo generar el Excel", e.message || String(e), "error");
    } finally {
      $b.prop("disabled", false).html(htmlBoton);
    }
  }

  // Una hoja: Cashflow arriba, Detalle de Gastos abajo. Columnas A (cuenta) y B (concepto/nombre),
  // meses desde la C, total al final: así los meses de las dos tablas quedan alineados.
  async function construirExcel(ExcelJS) {
    const wb = new ExcelJS.Workbook();
    wb.creator = "Sistema Caddy";
    wb.created = new Date();
    const ws = wb.addWorksheet("Cashflow", { views: [{ state: "frozen", xSplit: 2 }] });

    const nMeses = COLS.length;
    const colTotal = 3 + nMeses; // A,B + meses + total
    const FMT = '"$" #,##0.00;[Red]-"$" #,##0.00';
    const borde = { style: "thin", color: { argb: "FFE3E6EF" } };
    const bordes = { top: borde, left: borde, bottom: borde, right: borde };
    const relleno = (argb) => ({ type: "pattern", pattern: "solid", fgColor: { argb } });

    ws.getColumn(1).width = 12;
    ws.getColumn(2).width = 50;
    for (let c = 3; c <= colTotal; c++) ws.getColumn(c).width = 15;
    ws.getColumn(colTotal).width = 17;

    const titulo = ws.addRow(["Caddy · Cashflow y detalle de gastos"]);
    titulo.font = { bold: true, size: 15, color: { argb: "FF313A46" } };
    ws.addRow([`Últimos 12 meses: ${COLS[0].label} a ${COLS[nMeses - 1].label}. Generado el ${new Date().toLocaleString("es-AR")}.`]).font = { italic: true, color: { argb: "FF6C757D" } };
    ws.addRow(["Ventas sin IVA (÷ 1,21). El mes en curso es parcial."]).font = { italic: true, color: { argb: "FF6C757D" } };
    ws.addRow([]);

    const banda = (texto, argb) => {
      const r = ws.addRow([texto]);
      ws.mergeCells(r.number, 1, r.number, colTotal);
      r.height = 22;
      r.getCell(1).fill = relleno(argb);
      r.getCell(1).font = { bold: true, size: 12, color: { argb: "FFFFFFFF" } };
      r.getCell(1).alignment = { vertical: "middle", horizontal: "center" };
    };
    const encabezado = (a, b) => {
      const r = ws.addRow([a, b, ...COLS.map((c) => (c.parcial ? c.label + " (parcial)" : c.label)), "Total 12 meses"]);
      r.eachCell((cell) => {
        cell.font = { bold: true, color: { argb: "FF313A46" } };
        cell.fill = relleno("FFF1F3FA");
        cell.border = bordes;
        cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
      });
      r.height = 20;
      return r;
    };
    // Fila con fórmula de total (así el Excel se puede tocar y recalcula)
    const filaDatos = (a, b, valores, estilo = {}) => {
      const r = ws.addRow([a, b, ...valores.map((v) => Math.round(v * 100) / 100)]);
      const ini = ws.getColumn(3).letter, fin = ws.getColumn(2 + nMeses).letter;
      r.getCell(colTotal).value = { formula: `SUM(${ini}${r.number}:${fin}${r.number})`, result: Math.round(suma(valores) * 100) / 100 };
      for (let c = 1; c <= colTotal; c++) {
        const cell = r.getCell(c);
        cell.border = bordes;
        if (c >= 3) cell.numFmt = FMT;
        if (estilo.fill) cell.fill = relleno(estilo.fill);
        if (estilo.bold || c === colTotal) cell.font = { bold: true, color: estilo.color ? { argb: estilo.color } : undefined };
        else if (estilo.color) cell.font = { color: { argb: estilo.color } };
      }
      return r;
    };

    // --- Cashflow ---
    banda("Cashflow Caddy - Últimos 12 Meses", "FF313A46");
    const hCash = encabezado("Concepto", "");
    ws.mergeCells(hCash.number, 1, hCash.number, 2);
    const conceptos = [
      ["Ventas Simples", cash.simples],
      ["Ventas Flex", cash.flex],
      ["Ventas Recorridos", cash.recorridos],
      ["Cobranza (5%)", cash.cobranza],
    ];
    const filasCash = {};
    conceptos.forEach(([n, arr]) => { filasCash[n] = filaDatos(n, "", arr); ws.mergeCells(filasCash[n].number, 1, filasCash[n].number, 2); });
    const rTotal = filaDatos("Total Ventas", "", cash.total, { bold: true, fill: "FFE6F4EA" });
    const rGastos = filaDatos("Gastos", "", cash.gasto, { bold: true, fill: "FFFDE8EC", color: "FFC0392B" });
    const rSaldo = filaDatos("Resultado", "", cash.saldo, { bold: true });
    [rTotal, rGastos, rSaldo].forEach((r) => ws.mergeCells(r.number, 1, r.number, 2));
    // Total Ventas y Resultado como fórmulas sobre las filas de arriba
    for (let c = 3; c <= 2 + nMeses; c++) {
      const L = ws.getColumn(c).letter, i = c - 3;
      const filasV = conceptos.map(([n]) => `${L}${filasCash[n].number}`).join(",");
      rTotal.getCell(c).value = { formula: `SUM(${filasV})`, result: Math.round(cash.total[i] * 100) / 100 };
      rSaldo.getCell(c).value = { formula: `${L}${rTotal.number}-${L}${rGastos.number}`, result: Math.round(cash.saldo[i] * 100) / 100 };
    }
    const rMargen = ws.addRow(["Margen", ""]);
    ws.mergeCells(rMargen.number, 1, rMargen.number, 2);
    for (let c = 3; c <= colTotal; c++) {
      const L = ws.getColumn(c).letter;
      const i = c - 3;
      const res = c === colTotal ? (suma(cash.total) ? suma(cash.saldo) / suma(cash.total) : 0) : (cash.total[i] ? cash.saldo[i] / cash.total[i] : 0);
      const cell = rMargen.getCell(c);
      cell.value = { formula: `IF(${L}${rTotal.number}=0,0,${L}${rSaldo.number}/${L}${rTotal.number})`, result: res };
      cell.numFmt = "0.0%";
      cell.font = { italic: true, color: { argb: "FF6C757D" } };
      cell.border = bordes;
    }
    rMargen.getCell(1).border = bordes;

    ws.addRow([]);
    ws.addRow([]);

    // --- Detalle de gastos ---
    banda("Detalle de Gastos Caddy - Últimos 12 Meses", "FFE2445C");
    encabezado("Cuenta", "Nombre");
    const COLOR_GRUPO_XLS = { Personal: "FFE9EAFE", Logistica: "FFE3F4F9", Generales: "FFFFF6DC", Financieros: "FFFDE8EC" };
    const filasSubtotal = [];
    GRUPOS.forEach((g) => {
      const filas = gastos.filter((f) => f.grupo === g);
      if (!filas.length) return;
      const tg = COLS.map((_, i) => filas.reduce((a, f) => a + f.valores[i], 0));
      const rg = filaDatos("", GRUPO_LABEL[g], tg, { bold: true, fill: COLOR_GRUPO_XLS[g] });
      filasSubtotal.push(rg);
      const desde = rg.number + 1;
      filas.forEach((f) => {
        const r = filaDatos(f.cuenta, f.nombre, f.valores);
        r.getCell(1).alignment = { horizontal: "left" };
        r.outlineLevel = 1;
      });
      const hasta = desde + filas.length - 1;
      for (let c = 3; c <= 2 + nMeses; c++) {
        const L = ws.getColumn(c).letter;
        rg.getCell(c).value = { formula: `SUM(${L}${desde}:${L}${hasta})`, result: Math.round(tg[c - 3] * 100) / 100 };
      }
    });
    const totalMes = COLS.map((_, i) => gastos.reduce((a, f) => a + f.valores[i], 0));
    const rGT = filaDatos("", "TOTAL POR MES", totalMes, { bold: true, fill: "FFF1F3FA" });
    for (let c = 3; c <= 2 + nMeses; c++) {
      const L = ws.getColumn(c).letter;
      rGT.getCell(c).value = { formula: filasSubtotal.map((r) => `${L}${r.number}`).join("+") || "0", result: Math.round(totalMes[c - 3] * 100) / 100 };
    }
    ws.properties.outlineProperties = { summaryBelow: false };

    return wb.xlsx.writeBuffer();
  }

  // ---------- Eventos ----------
  $(function () {
    cargar();
    $("#cf-actualizar").on("click", cargar);
    $("#cf-excel").on("click", descargarExcel);
    $("#cf-buscar").on("input", filtrarGastos);
    $("#cf-tabla-gastos").on("click", "tr.cf-grupo", function () {
      this.classList.toggle("cerrado");
      filtrarGastos();
    });
    $(".cf-toggle").on("click", "button", function () {
      partModo = this.getAttribute("data-part");
      $(".cf-toggle button").removeClass("active");
      $(this).addClass("active");
      if (gastos) renderParticipacion();
    });
    // Si se cambia claro/oscuro, los gráficos se rearman con los colores del tema
    new MutationObserver(() => { if (cash) { renderChartCash(); renderParticipacion(); } })
      .observe(document.documentElement, { attributes: true, attributeFilter: ["data-bs-theme"] });
  });
})();
