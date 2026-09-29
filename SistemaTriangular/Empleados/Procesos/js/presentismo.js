// Presentismo y Km de choferes (Empleados/Presentismo.php) -> Procesos/php/presentismo.php
(function () {
  "use strict";

  const URL = "Procesos/php/presentismo.php";
  const fmt0 = new Intl.NumberFormat("es-AR", { maximumFractionDigits: 0 });
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const fecha = (f) => (f ? String(f).slice(0, 10).split("-").reverse().join("/") : "");
  const hm = (min) => { min = Math.round(min || 0); return `${Math.floor(min / 60)}:${String(min % 60).padStart(2, "0")}`; };
  const post = (data) => $.ajax({ url: URL, type: "POST", data, dataType: "json" });
  const hoy = () => { const d = new Date(); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10); };

  let resumenData = null;
  let kmData = null;

  // ---------- Planilla del día ----------
  function minutos(inp, egr, otro) {
    const p = (s) => { const m = /^(\d{1,2}):(\d{2})/.exec(s || ""); return m ? +m[1] * 60 + +m[2] : null; };
    const a = p(inp), b = p(egr);
    if (a === null || b === null) return null;
    return b - a + (otro || b < a ? 1440 : 0);
  }

  function cargarPlanilla() {
    const f = $("#pr-fecha").val();
    $("#pr-planilla tbody").html('<tr><td class="cf-loading" colspan="7">Cargando…</td></tr>');
    post({ accion: "planilla", fecha: f, externos: $("#pr-externos").is(":checked") ? 1 : 0 }).done(function (r) {
      if (!r.ok) return Swal.fire("Error", r.error || "No se pudo cargar", "error");
      const cargados = r.filas.filter((x) => x.idHorario).length;
      $("#pr-planilla-info").text(`${cargados} de ${r.filas.length} con horario cargado`);
      $("#pr-planilla tbody").html(r.filas.map((x) => `
        <tr class="${x.idHorario ? "cargado" : ""}" data-id="${x.id}" data-nombre="${esc(x.NombreCompleto)}">
          <td class="cf-first">${esc(x.NombreCompleto)}${x.UsuarioCarga ? `<div class="cp-sub">cargó ${esc(x.UsuarioCarga)}</div>` : ""}</td>
          <td>${esc(x.Puesto)}${+x.Aliados ? ' <span class="badge bg-light text-muted">externo</span>' : ""}</td>
          <td><input type="time" class="form-control form-control-sm pr-in" value="${esc(x.Ingreso || "")}"></td>
          <td><input type="time" class="form-control form-control-sm pr-eg" value="${esc(x.Egreso || "")}"></td>
          <td><input type="checkbox" class="form-check-input pr-otro" ${+x.OtroDia ? "checked" : ""}></td>
          <td class="pr-horas">${x.Horas || "–"}</td>
          <td>${x.idHorario ? `<button type="button" class="btn btn-sm btn-light text-danger pr-borrar" data-idh="${x.idHorario}" title="Borrar el horario de este día"><i class="mdi mdi-trash-can-outline"></i></button>` : ""}</td>
        </tr>`).join(""));
    });
  }

  function guardarPlanilla() {
    const filas = $("#pr-planilla tbody tr[data-id]").map(function () {
      const $t = $(this);
      return { idEmpleado: this.dataset.id, nombre: this.dataset.nombre, ingreso: $t.find(".pr-in").val(), egreso: $t.find(".pr-eg").val(), otro_dia: $t.find(".pr-otro").is(":checked") ? 1 : 0 };
    }).get().filter((x) => x.ingreso || x.egreso);
    if (!filas.length) return Swal.fire("Nada para guardar", "Cargá al menos un ingreso y egreso.", "info");
    const $b = $("#pr-guardar").prop("disabled", true);
    post({ accion: "guardar", fecha: $("#pr-fecha").val(), filas: JSON.stringify(filas) })
      .done(function (r) {
        if (!r.ok) return Swal.fire("Error", r.error || "No se pudo guardar", "error");
        if (r.errores.length) Swal.fire({ icon: "warning", title: `Se guardaron ${r.guardados}`, html: "No se guardaron:<br>" + r.errores.map(esc).join("<br>") });
        else toast("success", "Horarios guardados", `${r.guardados} empleados del ${fecha($("#pr-fecha").val())}.`);
        cargarPlanilla();
        resumenData = null;
      })
      .always(() => $b.prop("disabled", false));
  }

  function moverDia(d) {
    const x = new Date($("#pr-fecha").val() + "T12:00:00");
    x.setDate(x.getDate() + d);
    $("#pr-fecha").val(x.toISOString().slice(0, 10)).trigger("change");
  }

  // ---------- Resumen del mes ----------
  function cargarResumen() {
    $("#pr-resumen tbody").html('<tr><td class="cf-loading" colspan="6">Cargando…</td></tr>');
    $("#pr-excel-resumen").prop("disabled", true);
    post({ accion: "resumen", mes: $("#pr-mes").val() }).done(function (r) {
      if (!r.ok) return Swal.fire("Error", r.error || "No se pudo cargar", "error");
      resumenData = r;
      if (!r.resumen.length) {
        $("#pr-resumen tbody").html('<tr><td class="cf-loading" colspan="6">No hay horarios cargados en ese mes.</td></tr>');
        return;
      }
      $("#pr-resumen tbody").html(r.resumen.map((e) => `
        <tr data-emp="${e.idEmpleado}">
          <td class="cf-first">${esc(e.nombre)}</td><td>${esc(e.puesto)}</td><td>${e.dias}</td>
          <td class="pr-horas">${hm(e.minutos)}</td><td>${hm(e.minutos / e.dias)}</td>
          <td><button type="button" class="btn btn-sm btn-light pr-ver-emp" data-emp="${e.idEmpleado}"><i class="mdi mdi-chevron-down"></i> Días</button></td>
        </tr>`).join(""));
      $("#pr-excel-resumen").prop("disabled", false);
    });
  }

  function toggleDetalleEmp(idEmp, $fila) {
    const $sig = $fila.nextUntil(":not(.pr-detalle)");
    if ($sig.length) return $sig.remove();
    const dias = resumenData.detalle.filter((d) => String(d.idEmpleado) === String(idEmp));
    $fila.after(dias.map((d) => `<tr class="pr-detalle"><td class="cf-first">${fecha(d.Fecha)}</td><td></td><td>${esc(d.Ingreso)} → ${esc(d.Egreso)}${+d.OtroDia ? " (+1)" : ""}</td><td class="pr-horas">${hm(d.Minutos)}</td><td colspan="2" class="text-muted">cargó ${esc(d.UsuarioCarga || "")}</td></tr>`).join(""));
  }

  // ---------- Km choferes ----------
  function cargarKm() {
    $("#pr-km tbody").html('<tr><td class="cf-loading" colspan="6">Cargando…</td></tr>');
    $("#pr-excel-km").prop("disabled", true);
    post({ accion: "km", mes: $("#pr-km-mes").val(), solo_propios: $("#pr-km-propios").is(":checked") ? 1 : 0 }).done(function (r) {
      if (!r.ok) return Swal.fire("Error", r.error || "No se pudo cargar", "error");
      kmData = r;
      if (!r.choferes.length) {
        $("#pr-km tbody").html('<tr><td class="cf-loading" colspan="6">No hay salidas en ese mes.</td></tr>');
        return;
      }
      $("#pr-km tbody").html(r.choferes.map((c, i) => `
        <tr>
          <td class="cf-first">${esc(c.chofer)}</td><td>${c.salidas}</td><td class="pr-horas">${fmt0.format(c.km)} km</td>
          <td>${c.vehiculos.map((p) => `<span class="cp-patente">${esc(p)}</span>`).join(" ")}</td>
          <td>${c.alertas ? `<span class="pr-alerta">${c.alertas} para revisar</span>` : '<span class="text-muted">–</span>'}</td>
          <td><button type="button" class="btn btn-sm btn-light pr-ver-km" data-i="${i}"><i class="mdi mdi-chevron-down"></i> Salidas</button></td>
        </tr>`).join(""));
      $("#pr-excel-km").prop("disabled", false);
    });
  }

  function toggleDetalleKm(i, $fila) {
    const $sig = $fila.nextUntil(":not(.pr-detalle)");
    if ($sig.length) return $sig.remove();
    const c = kmData.choferes[i];
    const vs = kmData.viajes.filter((v) => (v.NombreChofer || "(sin chofer)") === c.chofer);
    $fila.after(vs.map((v) => `<tr class="pr-detalle">
      <td class="cf-first">${fecha(v.Fecha)} · orden ${esc(v.NumerodeOrden)}</td>
      <td>Rec. ${esc(v.Recorrido)}</td>
      <td>${fmt0.format(+v.KmSalida || 0)} → ${+v.KmRegreso ? fmt0.format(+v.KmRegreso) : "–"} = <b>${fmt0.format(v.Km)} km</b></td>
      <td><span class="cp-patente">${esc(v.Patente)}</span></td>
      <td>${v.alerta ? `<span class="pr-alerta">${esc(v.alerta)}</span>` : ""}</td><td></td></tr>`).join(""));
  }

  // ---------- Excel ----------
  let excelJs = null;
  function cargarExcelJs() {
    if (window.ExcelJS) return Promise.resolve();
    if (!excelJs) excelJs = new Promise((ok, mal) => {
      const s = document.createElement("script");
      s.src = "https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js";
      s.onload = ok;
      s.onerror = () => { excelJs = null; mal(new Error("No se pudo cargar ExcelJS")); };
      document.head.appendChild(s);
    });
    return excelJs;
  }

  async function descargar(nombre, armar) {
    try {
      await cargarExcelJs();
      const wb = new ExcelJS.Workbook();
      wb.creator = "Sistema Caddy";
      armar(wb);
      const buf = await wb.xlsx.writeBuffer();
      const a = document.createElement("a");
      a.href = URL_.createObjectURL(new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }));
      a.download = nombre;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL_.revokeObjectURL(a.href); a.remove(); }, 1000);
    } catch (e) {
      Swal.fire("No se pudo generar el Excel", e.message || String(e), "error");
    }
  }
  const URL_ = window.URL;

  function estilarHoja(ws, anchos) {
    ws.columns.forEach((c, i) => (c.width = anchos[i] || 14));
    ws.getRow(1).font = { bold: true, size: 13 };
    ws.getRow(3).eachCell((c) => { c.font = { bold: true }; c.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFF1F3FA" } }; });
  }

  function excelResumen() {
    const r = resumenData;
    descargar(`Presentismo_${$("#pr-mes").val()}.xlsx`, function (wb) {
      const ws = wb.addWorksheet("Resumen");
      ws.addRow([`Presentismo ${fecha(r.desde)} al ${fecha(r.hasta)}`]);
      ws.addRow([]);
      ws.addRow(["Empleado", "Puesto", "Días", "Horas", "Horas (decimal)", "Promedio por día"]);
      r.resumen.forEach((e) => ws.addRow([e.nombre, e.puesto, e.dias, hm(e.minutos), +(e.minutos / 60).toFixed(2), hm(e.minutos / e.dias)]));
      estilarHoja(ws, [34, 18, 8, 10, 14, 16]);
      const wd = wb.addWorksheet("Detalle por día");
      wd.addRow([`Presentismo ${fecha(r.desde)} al ${fecha(r.hasta)} — detalle`]);
      wd.addRow([]);
      wd.addRow(["Empleado", "Fecha", "Ingreso", "Egreso", "Terminó al otro día", "Horas", "Horas (decimal)", "Cargó"]);
      r.detalle.forEach((d) => wd.addRow([d.NombreCompleto, fecha(d.Fecha), d.Ingreso, d.Egreso, +d.OtroDia ? "Sí" : "", hm(d.Minutos), +(d.Minutos / 60).toFixed(2), d.UsuarioCarga]));
      estilarHoja(wd, [34, 12, 10, 10, 18, 10, 14, 24]);
    });
  }

  function excelKm() {
    const r = kmData;
    descargar(`Km_choferes_${$("#pr-km-mes").val()}.xlsx`, function (wb) {
      const ws = wb.addWorksheet("Km por chofer");
      ws.addRow([`Km de choferes ${fecha(r.desde)} al ${fecha(r.hasta)}${$("#pr-km-propios").is(":checked") ? " (vehículos propios)" : ""}`]);
      ws.addRow([]);
      ws.addRow(["Chofer", "Salidas", "Km del mes", "Vehículos", "Para revisar"]);
      r.choferes.forEach((c) => ws.addRow([c.chofer, c.salidas, c.km, c.vehiculos.join(", "), c.alertas || ""]));
      estilarHoja(ws, [32, 10, 14, 26, 14]);
      ws.getColumn(3).numFmt = "#,##0";
      const wd = wb.addWorksheet("Detalle de salidas");
      wd.addRow([`Salidas ${fecha(r.desde)} al ${fecha(r.hasta)}`]);
      wd.addRow([]);
      wd.addRow(["Chofer", "Fecha", "Orden", "Recorrido", "Patente", "Km salida", "Km regreso", "Km", "Acompañante", "Para revisar"]);
      r.viajes.forEach((v) => wd.addRow([v.NombreChofer, fecha(v.Fecha), +v.NumerodeOrden, `${v.Recorrido} ${v.RecorridoNombre || ""}`.trim(), v.Patente, +v.KmSalida || 0, +v.KmRegreso || 0, v.Km, v.NombreChofer2 || "", v.alerta]));
      estilarHoja(wd, [30, 12, 9, 26, 11, 11, 11, 9, 24, 26]);
    });
  }

  // ---------- Inicio ----------
  $(function () {
    const mes = hoy().slice(0, 7);
    $("#pr-fecha").val(hoy());
    $("#pr-mes, #pr-km-mes").val(mes);
    cargarPlanilla();

    $("#pr-fecha, #pr-externos").on("change", cargarPlanilla);
    $("#pr-dia-ant").on("click", () => moverDia(-1));
    $("#pr-dia-sig").on("click", () => moverDia(1));
    $("#pr-guardar").on("click", guardarPlanilla);
    $("#pr-planilla").on("input change", "input", function () {
      const $t = $(this).closest("tr");
      $t.addClass("cambiado");
      const m = minutos($t.find(".pr-in").val(), $t.find(".pr-eg").val(), $t.find(".pr-otro").is(":checked"));
      $t.find(".pr-horas").text(m === null ? "–" : hm(m));
    });
    $("#pr-planilla").on("click", ".pr-borrar", function () {
      const id = this.dataset.idh;
      Swal.fire({ icon: "warning", title: "¿Borrar el horario de este día?", showCancelButton: true, confirmButtonText: "Sí, borrar", cancelButtonText: "Cancelar", confirmButtonColor: "#fa5c7c" })
        .then((x) => { if (x.isConfirmed) post({ accion: "eliminar", id }).done(() => { cargarPlanilla(); resumenData = null; }); });
    });

    $("#btn-tab-resumen").on("shown.bs.tab", () => { if (!resumenData) cargarResumen(); });
    $("#pr-mes").on("change", cargarResumen);
    $("#pr-resumen").on("click", ".pr-ver-emp", function () { toggleDetalleEmp(this.dataset.emp, $(this).closest("tr")); });
    $("#pr-excel-resumen").on("click", excelResumen);

    $("#btn-tab-km").on("shown.bs.tab", () => { if (!kmData) cargarKm(); });
    $("#pr-km-mes, #pr-km-propios").on("change", cargarKm);
    $("#pr-km").on("click", ".pr-ver-km", function () { toggleDetalleKm(+this.dataset.i, $(this).closest("tr")); });
    $("#pr-excel-km").on("click", excelKm);
  });
})();
