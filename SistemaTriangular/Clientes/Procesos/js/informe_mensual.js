// Informe mensual de envios del cliente: selector mes/anio + "Ver PDF" + "Enviar por mail".
// Vive en la pestana Estadisticas de Clientes.php / starter.php.

(function () {
  "use strict";

  var MESES = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
  ];

  function idCliente() {
    var el = document.getElementById("codigo");
    return el ? (el.value || "").trim() : "";
  }

  function poblarSelectores() {
    var selM = document.getElementById("informe_mes");
    var selA = document.getElementById("informe_anio");
    if (!selM || !selA || selM.options.length) return;

    var hoy = new Date();
    // por defecto: mes anterior
    var d = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
    var mesDef = d.getMonth() + 1;
    var anioDef = d.getFullYear();

    for (var m = 1; m <= 12; m++) {
      var o = document.createElement("option");
      o.value = String(m);
      o.textContent = MESES[m - 1];
      if (m === mesDef) o.selected = true;
      selM.appendChild(o);
    }
    for (var a = hoy.getFullYear(); a >= hoy.getFullYear() - 3; a--) {
      var oa = document.createElement("option");
      oa.value = String(a);
      oa.textContent = String(a);
      if (a === anioDef) oa.selected = true;
      selA.appendChild(oa);
    }
  }

  function periodoElegido() {
    var m = document.getElementById("informe_mes");
    var a = document.getElementById("informe_anio");
    return {
      mes: m ? m.value : "",
      anio: a ? a.value : "",
    };
  }

  function avisar(tipo, titulo, msg) {
    if (typeof toast === "function") toast(tipo, titulo, msg);
    else alert(titulo + ": " + msg);
  }

  // ---- Ver PDF ----
  function verPdf() {
    var id = idCliente();
    if (!id) {
      avisar("error", "Sin cliente", "Abrí primero un cliente.");
      return;
    }
    var p = periodoElegido();
    window.open(
      "Informes/InformeMensualClientePdf.php?id=" + encodeURIComponent(id) +
        "&anio=" + encodeURIComponent(p.anio) + "&mes=" + encodeURIComponent(p.mes),
      "_blank",
    );
  }

  // ---- Enviar por mail ----
  // --- chips de mails agregados a mano ---
  var chipsManual = [];
  var chipsBinded = false;

  function esEmail(s) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
  }

  function renderChips() {
    var cont = document.getElementById("informe_mail_chips");
    if (!cont) return;
    cont.innerHTML = chipsManual
      .map(function (m) {
        return (
          '<span class="badge bg-danger d-inline-flex align-items-center gap-1" style="font-size:12px">' +
          m +
          '<a href="#" class="text-white informe-chip-x" data-mail="' +
          m +
          '" style="text-decoration:none">&times;</a></span>'
        );
      })
      .join("");
  }

  function addChip(raw) {
    var input = document.getElementById("informe_mail_input");
    (raw || "")
      .split(/[,;\s]+/)
      .map(function (s) {
        return s.trim();
      })
      .filter(Boolean)
      .forEach(function (m) {
        if (!esEmail(m)) {
          if (input) input.classList.add("is-invalid");
          return;
        }
        if (input) input.classList.remove("is-invalid");
        var low = m.toLowerCase();
        if (
          !chipsManual.some(function (x) {
            return x.toLowerCase() === low;
          })
        ) {
          chipsManual.push(m);
        }
      });
    renderChips();
  }

  function bindChips() {
    if (chipsBinded) return;
    chipsBinded = true;
    var input = document.getElementById("informe_mail_input");
    var box = document.getElementById("informe_mail_chips_box");
    var chips = document.getElementById("informe_mail_chips");
    if (!input || !box || !chips) return;

    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === "," || e.key === ";") {
        e.preventDefault();
        addChip(this.value);
        this.value = "";
      } else if (e.key === "Backspace" && this.value === "" && chipsManual.length) {
        chipsManual.pop();
        renderChips();
      }
    });
    input.addEventListener("blur", function () {
      if (this.value.trim()) {
        addChip(this.value);
        this.value = "";
      }
    });
    box.addEventListener("click", function () {
      input.focus();
    });
    chips.addEventListener("click", function (e) {
      var a = e.target.closest(".informe-chip-x");
      if (!a) return;
      e.preventDefault();
      var m = a.getAttribute("data-mail");
      chipsManual = chipsManual.filter(function (x) {
        return x !== m;
      });
      renderChips();
    });
  }

  function abrirModalMail() {
    var id = idCliente();
    if (!id) {
      avisar("error", "Sin cliente", "Abrí primero un cliente.");
      return;
    }
    var cont = document.getElementById("informe_mail_lista");
    var p = periodoElegido();
    document.getElementById("informe_mail_periodo").textContent =
      MESES[parseInt(p.mes, 10) - 1] + " " + p.anio;
    cont.innerHTML = '<div class="text-muted">Cargando contactos...</div>';
    document.getElementById("informe_mail_estado").textContent = "";

    chipsManual = [];
    renderChips();
    var inp = document.getElementById("informe_mail_input");
    if (inp) inp.value = "";
    bindChips();

    var modalEl = document.getElementById("modal_informe_mail");
    var modal = window.bootstrap
      ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
      : null;
    if (modal) modal.show();
    else $(modalEl).modal("show");

    $.ajax({
      url: "Informes/enviar_informe_mensual_mail.php",
      type: "post",
      data: { ObtenerMailsInforme: 1, id: id },
      dataType: "json",
      success: function (r) {
        if (!r || r.success !== 1) {
          cont.innerHTML =
            '<div class="text-danger">' + ((r && r.msg) || "No se pudieron cargar los contactos") + "</div>";
          return;
        }
        if (!r.mails.length) {
          cont.innerHTML =
            '<div class="text-warning">Este cliente no tiene mails cargados (Clientes ni Contactos).</div>';
          return;
        }
        var html = "";
        r.mails.forEach(function (m, i) {
          var badges = "";
          if (m.administrativo)
            badges += ' <span class="badge bg-primary">Administrativo</span>';
          if (m.operativo)
            badges += ' <span class="badge bg-info">Operativo</span>';
          if (m.sector)
            badges += ' <span class="badge bg-light text-dark">' + m.sector + "</span>";
          badges += ' <span class="badge bg-light text-muted">' + m.fuente + "</span>";
          html +=
            '<div class="form-check">' +
            '<input class="form-check-input informe-mail-chk" type="checkbox" value="' +
            m.email +
            '" id="infmail_' +
            i +
            '">' +
            '<label class="form-check-label" for="infmail_' +
            i +
            '"><strong>' +
            (m.nombre || m.email) +
            "</strong> &lt;" +
            m.email +
            "&gt;" +
            badges +
            "</label></div>";
        });
        cont.innerHTML = html;
      },
      error: function () {
        cont.innerHTML = '<div class="text-danger">Error de red al cargar contactos</div>';
      },
    });
  }

  function enviarMail() {
    var id = idCliente();
    var p = periodoElegido();

    // por las dudas, si quedó algo tipeado sin confirmar
    var inp = document.getElementById("informe_mail_input");
    if (inp && inp.value.trim()) {
      addChip(inp.value);
      inp.value = "";
    }

    var marcados = Array.prototype.slice
      .call(document.querySelectorAll(".informe-mail-chk:checked"))
      .map(function (c) {
        return c.value;
      });

    // dedupe case-insensitive entre contactos marcados + chips manuales
    var seleccion = [];
    var vistos = {};
    marcados.concat(chipsManual).forEach(function (m) {
      var low = String(m).trim().toLowerCase();
      if (low && !vistos[low]) {
        vistos[low] = 1;
        seleccion.push(String(m).trim());
      }
    });

    if (!seleccion.length) {
      document.getElementById("informe_mail_estado").innerHTML =
        '<span class="text-danger">Elegí un contacto o agregá al menos un mail.</span>';
      return;
    }
    var btn = document.getElementById("btn_informe_mail_enviar");
    btn.disabled = true;
    document.getElementById("informe_mail_estado").innerHTML =
      '<span class="text-muted">Enviando...</span>';

    $.ajax({
      url: "Informes/enviar_informe_mensual_mail.php",
      type: "post",
      data: {
        EnviarInformeMensual: 1,
        id: id,
        anio: p.anio,
        mes: p.mes,
        mails: seleccion,
      },
      dataType: "json",
      success: function (r) {
        btn.disabled = false;
        if (r && r.success === 1) {
          document.getElementById("informe_mail_estado").innerHTML =
            '<span class="text-success">' + r.msg + "</span>";
          avisar("success", "Enviado", r.msg);
        } else {
          document.getElementById("informe_mail_estado").innerHTML =
            '<span class="text-danger">' + ((r && r.msg) || "No se pudo enviar") + "</span>";
        }
      },
      error: function () {
        btn.disabled = false;
        document.getElementById("informe_mail_estado").innerHTML =
          '<span class="text-danger">Error de red al enviar</span>';
      },
    });
  }

  // ============================================================
  //  Resumen del mes en la pestana (misma data que el PDF)
  // ============================================================
  var irCharts = {};
  var C = {
    verde: "#0acf97",
    rojo: "#fa5c7c",
    naranja: "#e24f30",
    gris: "#98a6ad",
    grisClaro: "#dee2e6",
  };

  function fmtNum(n) {
    return (n || 0).toLocaleString("es-AR");
  }
  function fmtPct(n) {
    return (Math.round((n || 0) * 10) / 10).toString().replace(".", ",") + "%";
  }

  function pintarChart(elId, opts) {
    var el = document.getElementById(elId);
    if (!el || typeof ApexCharts === "undefined") return;
    if (irCharts[elId]) {
      try {
        irCharts[elId].destroy();
      } catch (e) {}
    }
    irCharts[elId] = new ApexCharts(el, opts);
    irCharts[elId].render();
  }

  function donut(elId, labels, values, colors) {
    var total = values.reduce(function (a, b) {
      return a + b;
    }, 0);
    var idx = [];
    labels.forEach(function (_l, i) {
      if (values[i] > 0) idx.push(i);
    });
    pintarChart(elId, {
      chart: { type: "donut", height: 170, sparkline: { enabled: false } },
      series: idx.length ? idx.map(function (i) { return values[i]; }) : [1],
      labels: idx.length ? idx.map(function (i) { return labels[i]; }) : ["Sin datos"],
      colors: idx.length ? idx.map(function (i) { return colors[i]; }) : [C.grisClaro],
      legend: { position: "bottom", fontSize: "11px" },
      dataLabels: {
        enabled: true,
        formatter: function (val) {
          return Math.round(val) + "%";
        },
        style: { fontSize: "10px" },
      },
      tooltip: {
        y: {
          formatter: function (v) {
            return fmtNum(v) + (total ? " (" + fmtPct((v * 100) / total) + ")" : "");
          },
        },
      },
      plotOptions: { pie: { donut: { size: "62%" } } },
      noData: { text: "Sin datos" },
    });
  }

  function kpiCol(valor, etiqueta) {
    return (
      '<div class="col"><div class="border rounded py-2">' +
      '<div class="fw-bold text-danger" style="font-size:18px">' +
      valor +
      "</div>" +
      '<div class="text-muted text-uppercase" style="font-size:9px">' +
      etiqueta +
      "</div></div></div>"
    );
  }

  function renderBloqueResumen(b) {
    var diasProm = b.dias_promedio !== null && b.dias_promedio !== undefined
      ? String(b.dias_promedio).replace(".", ",") + " d"
      : "-";
    document.getElementById("ir_kpis").innerHTML =
      kpiCol(fmtNum(b.total), "Total") +
      kpiCol(fmtPct(b.pct_entrega), "Entregados") +
      kpiCol(fmtPct(b.pct_no_entrega), "Sin entregar") +
      kpiCol(diasProm, "Prom. entrega") +
      kpiCol(fmtNum(b.localidades), "Localidades");

    var otros = (b.en_proceso || 0) + (b.retiros || 0);
    donut(
      "ir_estado_chart",
      ["Entregado", "Devuelto", "No entregado", "Otros"],
      [b.entregados || 0, b.devueltos || 0, b.no_entregados || 0, otros],
      [C.verde, C.rojo, C.naranja, C.gris],
    );
    donut("ir_modalidad_chart", ["Flex", "Simple"], [b.flex || 0, b.simple || 0], [C.naranja, C.gris]);
    donut(
      "ir_destino_chart",
      ["Capital", "Interior", "Sin localidad"],
      [b.capital || 0, b.interior || 0, b.sin_localidad || 0],
      [C.naranja, C.gris, C.grisClaro],
    );

    var dow = b.volumen_dow || [];
    pintarChart("ir_dow_chart", {
      chart: { type: "bar", height: 190, toolbar: { show: false } },
      series: [{ name: "Envíos", data: dow }],
      xaxis: { categories: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"] },
      colors: [C.naranja],
      plotOptions: { bar: { borderRadius: 4, columnWidth: "55%", distributed: false } },
      dataLabels: { enabled: true, style: { fontSize: "10px", colors: ["#333"] }, offsetY: -16 },
      grid: { borderColor: "#eee" },
      legend: { show: false },
    });

    var loc = b.top_localidades || [];
    document.getElementById("ir_toploc").innerHTML = loc.length
      ? '<table class="table table-sm mb-0"><thead><tr><th>Localidad</th><th class="text-end">Env.</th><th class="text-end">% entr.</th></tr></thead><tbody>' +
        loc
          .map(function (l) {
            return (
              "<tr><td>" +
              l.localidad +
              '</td><td class="text-end">' +
              fmtNum(l.envios) +
              '</td><td class="text-end">' +
              fmtPct(l.pct_entrega) +
              "</td></tr>"
            );
          })
          .join("") +
        "</tbody></table>"
      : '<div class="text-muted">Sin localidades.</div>';

    document.getElementById("ir_extra").textContent =
      "Kilómetros recorridos: " +
      fmtNum(b.km_total) +
      "     Valor declarado transportado: $ " +
      fmtNum(b.valor_total);
  }

  function cargarResumen() {
    var card = document.getElementById("ir_card");
    if (!card) return;
    var id = idCliente();
    var p = periodoElegido();
    var per = MESES[parseInt(p.mes, 10) - 1] + " " + p.anio;
    document.getElementById("ir_periodo").textContent = "— " + per;
    document.getElementById("ir_estado").textContent = "";

    if (!id) {
      document.getElementById("ir_estado").textContent = "Abrí un cliente para ver el resumen.";
      return;
    }
    document.getElementById("ir_estado").textContent = "Cargando...";

    $.ajax({
      url: "Informes/informe_mensual_json.php",
      type: "post",
      data: { id: id, anio: p.anio, mes: p.mes },
      dataType: "json",
      success: function (d) {
        if (!d || !d.ok) {
          document.getElementById("ir_estado").textContent = (d && d.error) || "No se pudo cargar.";
          return;
        }
        document.getElementById("ir_estado").textContent = "";
        var env = d.envios || {};
        if ((env.total || 0) === 0 && !(env.mostrar)) {
          document.getElementById("ir_kpis").innerHTML =
            '<div class="col text-muted">Sin envíos en el período.</div>';
        } else {
          renderBloqueResumen(env);
        }

        var rec = d.recepciones || {};
        document.getElementById("ir_recep").innerHTML =
          (rec.total || 0) > 0
            ? '<div class="alert alert-light border mb-0 py-2" style="font-size:12px">' +
              "<strong>Recepciones del mes:</strong> " +
              fmtNum(rec.total) +
              " recibidos · " +
              fmtPct(rec.pct_entrega) +
              " entregados." +
              "</div>"
            : "";
      },
      error: function () {
        document.getElementById("ir_estado").textContent = "Error de red.";
      },
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    poblarSelectores();
    var bp = document.getElementById("btn_informe_pdf");
    var bm = document.getElementById("btn_informe_mail");
    if (bp) bp.addEventListener("click", verPdf);
    if (bm) bm.addEventListener("click", abrirModalMail);
    var be = document.getElementById("btn_informe_mail_enviar");
    if (be) be.addEventListener("click", enviarMail);

    ["informe_mes", "informe_anio"].forEach(function (sid) {
      var s = document.getElementById(sid);
      if (s) s.addEventListener("change", cargarResumen);
    });

    // el tab se arma / muestra: poblar selectores y cargar el resumen
    var tab = document.getElementById("botonestadisticas");
    if (tab) {
      tab.addEventListener("click", function () {
        poblarSelectores();
        setTimeout(cargarResumen, 150);
      });
    }
  });
})();
