// Libros IVA (Admin/LibrosIva.php).
// Los datos, columnas visibles y totales vienen armados del servidor
// (Procesos/php/libros_iva.php), con la misma lógica que el PDF
// (Informes/LibroIvapdf.php), así pantalla, Excel y PDF dan lo mismo.

(function ($) {
  "use strict";

  var ultimo = null; // última respuesta generada (para Excel/PDF)
  var tabla = null;

  var fmt = new Intl.NumberFormat("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  function moneda(v) {
    return "$ " + fmt.format(Number(v) || 0);
  }
  function num(v) {
    return fmt.format(Number(v) || 0);
  }
  function esc(v) {
    return String(v == null ? "" : v)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }
  function pad(n) {
    return (n < 10 ? "0" : "") + n;
  }

  // ---------- Período ----------
  function rangoDelMes(valor) {
    var p = String(valor || "").split("-");
    if (p.length !== 2) return null;
    var y = Number(p[0]), m = Number(p[1]);
    var ultimoDia = new Date(y, m, 0).getDate();
    return { desde: p[0] + "-" + p[1] + "-01", hasta: p[0] + "-" + p[1] + "-" + pad(ultimoDia) };
  }
  function sincronizarMes() {
    // Si desde/hasta ya no son un mes completo, el selector de mes queda vacío.
    var d = $("#liv-desde").val(), h = $("#liv-hasta").val();
    var r = rangoDelMes(d.slice(0, 7));
    $("#liv-mes").val(r && r.desde === d && r.hasta === h ? d.slice(0, 7) : "");
  }
  function inicializarPeriodo() {
    var hoy = new Date();
    var ant = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1); // el libro que se cierra es el del mes anterior
    var mes = ant.getFullYear() + "-" + pad(ant.getMonth() + 1);
    var r = rangoDelMes(mes);
    $("#liv-mes").val(mes);
    $("#liv-desde").val(r.desde);
    $("#liv-hasta").val(r.hasta);
  }

  function libroElegido() {
    return $("input[name='liv-libro']:checked").val() === "compras" ? "compras" : "ventas";
  }

  // ---------- Generar ----------
  function generar() {
    var libro = libroElegido();
    var desde = $("#liv-desde").val();
    var hasta = $("#liv-hasta").val();
    if (!desde || !hasta) {
      alerta("warning", "Falta el período", "Elegí el mes o las fechas desde y hasta.");
      return;
    }
    if (desde > hasta) {
      alerta("warning", "Período inválido", "La fecha desde no puede ser mayor que la hasta.");
      return;
    }

    var $btn = $("#liv-generar").prop("disabled", true);
    var htmlBtn = $btn.html();
    $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Generando...');

    $.ajax({
      url: "Procesos/php/libros_iva.php",
      type: "POST",
      dataType: "json",
      data: { libro: libro, desde: desde, hasta: hasta },
    })
      .done(function (r) {
        if (!r || r.success != 1) {
          alerta("error", "No se pudo generar", (r && r.msg) || "Error desconocido");
          return;
        }
        ultimo = r;
        pintar(r);
      })
      .fail(function () {
        alerta("error", "Error", "No se pudo conectar con el servidor.");
      })
      .always(function () {
        $btn.prop("disabled", false).html(htmlBtn);
      });
  }

  function pintar(r) {
    var tituloCompleto = r.titulo + " · " + r.periodo.texto;
    $("#liv-titulo").text(tituloCompleto);
    $("#liv-card-titulo").text(r.titulo + " — " + r.periodo.texto);
    $("#liv-resultado").removeClass("d-none");
    $("#liv-pdf, #liv-excel").prop("disabled", false);

    // KPIs
    var t = r.totales;
    var nc = 0;
    r.filas.forEach(function (f) {
      if (Number(f.total) < 0) nc++;
    });
    $("#liv-k-cant").text(r.cantidad);
    $("#liv-k-cant-foot").text(nc ? nc + " nota" + (nc > 1 ? "s" : "") + " de crédito" : "Sin notas de crédito");
    $("#liv-k-neto").text(moneda(t.neto));
    $("#liv-k-neto-foot").text("Exento / no gravado: " + moneda(t.exento));
    $("#liv-k-iva-label").text(r.libro === "ventas" ? "Débito fiscal" : "Crédito fiscal");
    $("#liv-k-iva").text(moneda(t.iva));
    $("#liv-k-iva-foot").text(
      r.alicuotas.length ? r.alicuotas.map(function (a) { return a.label.replace("IVA ", "") + ": " + moneda(a.importe); }).join(" · ") : "Sin IVA en el período"
    );
    $("#liv-k-total").text(moneda(t.total));

    pintarTabla(r);
    pintarResumenes(r);
  }

  // ---------- Tabla principal ----------
  function pintarTabla(r) {
    if (tabla) {
      tabla.destroy();
      tabla = null;
    }

    var ths = "", tfs = "";
    var columnas = r.columnas.map(function (c, i) {
      var esMonto = c.tipo === "monto";
      ths += '<th class="' + (esMonto ? "liv-num" : "") + '">' + esc(c.label) + "</th>";
      if (esMonto) {
        tfs += '<th class="liv-num">' + num(r.totales[c.key]) + "</th>";
      } else {
        tfs += i === 0 ? "<th>Total</th>" : "<th></th>";
      }

      var col = { data: c.key, title: c.label };
      if (esMonto) {
        col.className = "liv-num";
        col.render = function (v, type) {
          if (type !== "display") return Number(v) || 0;
          return '<span class="' + (Number(v) < 0 ? "liv-neg" : "") + '">' + num(v) + "</span>";
        };
      } else if (c.key === "fecha") {
        col.render = function (v, type, row) {
          return type === "sort" || type === "type" ? row.fecha_iso : v;
        };
      } else if (c.key === "razon") {
        col.className = "liv-razon";
        col.render = function (v, type) {
          return type === "display" ? '<span title="' + esc(v) + '">' + esc(v) + "</span>" : v;
        };
      } else if (c.key === "comprobante") {
        col.render = function (v, type, row) {
          return type === "display" ? '<span title="' + esc(row.tipo) + '">' + esc(v) + "</span>" : v;
        };
      } else {
        col.render = function (v, type) {
          return type === "display" ? esc(v) : v;
        };
      }
      return col;
    });

    $("#liv-tabla-wrap").html(
      '<table id="liv-tabla" class="table table-sm table-hover table-centered w-100 mb-0">' +
        "<thead><tr>" + ths + "</tr></thead><tfoot><tr>" + tfs + "</tr></tfoot></table>"
    );

    var nombreArchivo = (r.libro === "ventas" ? "LibroIvaVentas_" : "LibroIvaCompras_") + r.periodo.archivo;
    var tituloExport = r.empresa.razon + " - CUIT " + r.empresa.cuit + " - " + r.titulo + " - " + r.periodo.texto;
    var totalesExport = r.columnas.map(function (c, i) {
      if (c.tipo === "monto") return Number(r.totales[c.key]) || 0;
      return i === 0 ? "Total del período" : "";
    });

    tabla = $("#liv-tabla").DataTable({
      data: r.filas,
      columns: columnas,
      order: [],
      pageLength: 25,
      lengthMenu: [
        [25, 50, 100, -1],
        [25, 50, 100, "Todos"],
      ],
      autoWidth: false,
      dom: "<'d-flex flex-wrap justify-content-between align-items-center gap-2'Bf>rt<'d-flex flex-wrap justify-content-between align-items-center mt-2'ip>",
      buttons: {
        dom: { button: { className: "" } },
        buttons: [
          dtButtonConfig("pageLength", true),
          dtButtonConfig("copy", true),
          dtButtonConfig({ extend: "csv", filename: nombreArchivo, footer: true, exportOptions: { orthogonal: "export" } }, true),
          dtButtonConfig({
            extend: "excel",
            filename: nombreArchivo,
            title: tituloExport,
            footer: true,
            exportOptions: { orthogonal: "export" },
            customizeData: function (d) {
              d.footer = totalesExport; // totales numéricos (no el texto con $)
            },
          }, true),
          {
            text: '<i class="mdi mdi-file-pdf-box me-1"></i>PDF',
            className: "btn btn-sm btn-soft-danger",
            action: abrirPdf,
          },
        ],
      },
      language: {
        search: "",
        searchPlaceholder: "Buscar...",
        info: "_START_ a _END_ de _TOTAL_ comprobantes",
        infoEmpty: "Sin comprobantes",
        infoFiltered: "(filtrado de _MAX_)",
        zeroRecords: "No hay comprobantes en el período",
        emptyTable: "No hay comprobantes en el período",
        paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
      },
    });
  }

  function pintarResumenes(r) {
    var $tb = $("#liv-por-tipo tbody").empty();
    r.por_tipo.forEach(function (x) {
      $tb.append(
        "<tr><td>" + esc(x.tipo) + "</td><td>" + x.cantidad + "</td><td>" + moneda(x.neto) + "</td><td>" +
          moneda(x.iva) + "</td><td>" + moneda(x.exento) + '</td><td class="' + (x.total < 0 ? "cf-neg" : "") + '">' + moneda(x.total) + "</td></tr>"
      );
    });
    if (!r.por_tipo.length) $tb.append('<tr><td colspan="6" class="text-muted">Sin comprobantes</td></tr>');
    var t = r.totales;
    $("#liv-por-tipo tfoot").html(
      '<tr class="cf-row-total"><td>Total</td><td>' + r.cantidad + "</td><td>" + moneda(t.neto) + "</td><td>" + moneda(t.iva) +
        "</td><td>" + moneda(t.exento) + "</td><td>" + moneda(t.total) + "</td></tr>"
    );

    var $ta = $("#liv-alicuotas tbody").empty();
    r.alicuotas.forEach(function (a) {
      $ta.append("<tr><td>" + esc(a.label) + "</td><td>" + moneda(a.importe) + "</td></tr>");
    });
    if (!r.alicuotas.length) $ta.append('<tr><td colspan="2" class="text-muted">Sin IVA en el período</td></tr>');
    $("#liv-alicuotas tfoot").html(
      '<tr class="cf-row-total"><td>Total ' + (r.libro === "ventas" ? "débito" : "crédito") + " fiscal</td><td>" + moneda(t.iva) + "</td></tr>"
    );
  }

  function abrirPdf() {
    if (!ultimo) return;
    var q = $.param({ libro: ultimo.libro, desde: ultimo.desde, hasta: ultimo.hasta });
    window.open("Informes/LibroIvapdf.php?" + q, "_blank");
  }

  // ---------- Eventos ----------
  $(function () {
    inicializarPeriodo();

    $("#liv-mes").on("change", function () {
      var r = rangoDelMes(this.value);
      if (r) {
        $("#liv-desde").val(r.desde);
        $("#liv-hasta").val(r.hasta);
      }
    });
    $("#liv-desde, #liv-hasta").on("change", sincronizarMes);

    // Cambiar de libro o de período invalida lo generado (para no exportar otro libro).
    $("input[name='liv-libro'], #liv-mes, #liv-desde, #liv-hasta").on("change", function () {
      if (ultimo) $("#liv-pdf, #liv-excel").prop("disabled", true);
      $("#liv-titulo").text(libroElegido() === "ventas" ? "Libro IVA Ventas" : "Libro IVA Compras");
    });

    $("#liv-generar").on("click", generar);
    $("#liv-pdf").on("click", abrirPdf);
    $("#liv-excel").on("click", function () {
      if (tabla) tabla.button(".buttons-excel").trigger();
    });

    $("#liv-titulo").text("Libro IVA Ventas");
  });
})(jQuery);
