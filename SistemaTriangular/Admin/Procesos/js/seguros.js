// ── Estado global ─────────────────────────────────────────────────────────────
var lastData  = null;   // datos del último Consultar
var dtCon     = null;
var dtSin     = null;
var dtExc     = null;

// ── Inicializar fechas al mes actual ──────────────────────────────────────────
(function () {
    var hoy = new Date();
    var y   = hoy.getFullYear();
    var m   = String(hoy.getMonth() + 1).padStart(2, '0');
    var d   = String(hoy.getDate()).padStart(2, '0');
    document.getElementById('filtro_desde').value = y + '-' + m + '-01';
    document.getElementById('filtro_hasta').value = y + '-' + m + '-' + d;
})();

// ── Formateo ──────────────────────────────────────────────────────────────────
function fmt(n) {
    return '$ ' + parseFloat(n || 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
function fmtNum(n) {
    return parseFloat(n || 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
function fmtFecha(iso) {
    // "2025-04-17" → "17/04/2025"
    if (!iso) return '';
    var p = iso.split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : iso;
}

// ── Cargar clientes en selects ────────────────────────────────────────────────
function cargarClientes() {
    $.ajax({
        url: 'Procesos/php/seguros.php',
        method: 'POST',
        data: { accion: 'cargar_clientes' },
        success: function (response) {
            var data;
            try { data = JSON.parse(response); } catch (e) { return; }
            if (data.success !== 1) return;

            var optCliente = '<option value="">— Todos los clientes —</option>';
            var optExcluir = '';

            data.data.forEach(function (c) {
                var sure  = c.sure == 1 ? ' ✓' : '';
                optCliente += '<option value="' + c.id + '">' + c.nombrecliente + sure + '</option>';
                optExcluir += '<option value="' + c.id + '">' + c.nombrecliente + '</option>';
            });

            $('#filtro_cliente').html(optCliente).trigger('change.select2');
            $('#filtro_excluir').html(optExcluir).trigger('change.select2');
        }
    });
}

// ── Ocultar excluir cuando se elige cliente específico ────────────────────────
$(document).on('change', '#filtro_cliente', function () {
    if ($(this).val() && $(this).val() !== '') {
        $('#col_excluir').hide();
        $('#filtro_excluir').val(null).trigger('change');
    } else {
        $('#col_excluir').show();
    }
});

// ── Helper suma ───────────────────────────────────────────────────────────────
function sumar(arr, campo) {
    return arr.reduce(function (acc, r) { return acc + parseFloat(r[campo] || 0); }, 0);
}

// ── Helper toggle tabla / no-data ─────────────────────────────────────────────
function toggleSeccion(dt, tableId, wrapId, noDataId, rows) {
    if (rows.length === 0) {
        if (dt) { dt.destroy(); }
        $('#' + tableId + ' tbody').empty();
        $('#' + noDataId).show();
        $('#' + wrapId).hide();
        return null;
    }
    $('#' + noDataId).hide();
    $('#' + wrapId).show();
    return dt;
}

// ── Config DataTable base ─────────────────────────────────────────────────────
function buildDT(tableId, columns, rows) {
    if ($.fn.DataTable.isDataTable('#' + tableId)) {
        $('#' + tableId).DataTable().destroy();
        $('#' + tableId + ' tbody').empty();
    }
    return $('#' + tableId).DataTable({
        dom: 'Blfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="mdi mdi-file-excel-outline"></i> Excel', title: 'Seguros Caddy' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf-box"></i> PDF', title: 'Seguros Caddy', orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print', text: '<i class="mdi mdi-printer"></i> Imprimir' },
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy"></i> Copiar' }
        ],
        data: rows,
        columns: columns,
        paging: true,
        searching: true,
        order: [[0, 'desc']],
        language: {
            lengthMenu:  '_MENU_ por página',
            search:      'Buscar:',
            zeroRecords: 'Sin registros',
            info:        'Mostrando _START_ a _END_ de _TOTAL_',
            infoEmpty:   'Sin datos',
            paginate:    { next: 'Sig.', previous: 'Ant.' }
        }
    });
}

// ── Render del Comprobante (Nro + CodigoSeguimiento) ─────────────────────────
function renderComprobante(d, t, row) {
    var seg = row.CodigoSeguimiento
        ? '<br><small class="text-muted" style="font-size:10px;letter-spacing:.3px">' + row.CodigoSeguimiento + '</small>'
        : '';
    return (d || '—') + seg;
}

// ── Renderizar las 3 tablas y los resúmenes ───────────────────────────────────
function renderTablas(data) {
    lastData = data;
    var montoMin = data.monto_min_global;

    // ── TABLA 1: Con seguro ─────────────────────────────────────────────────
    dtCon = toggleSeccion(dtCon, 'tabla_con_seguro', 'wrap_table_con', 'no_data_con', data.con_seguro);
    if (data.con_seguro.length > 0) {
        dtCon = buildDT('tabla_con_seguro', [
            {
                data: 'Fecha',
                render: function (d) {
                    return '<span style="display:none">' + d + '</span>' + fmtFecha(d);
                }
            },
            {
                data: 'NumeroComprobante',
                render: renderComprobante
            },
            {
                data: 'RazonSocial',
                render: function (d, t, row) {
                    return '<b>' + d + '</b><br><small class="text-muted">' + (row.CodigoProveedor || '') + '</small>';
                }
            },
            { data: 'ValorDeclarado',   className: 'text-end', render: function (d) { return fmtNum(d); } },
            {
                data: 'perc_aplicado',
                className: 'text-center',
                render: function (d) {
                    return '<span class="badge bg-info text-dark">' + d + '%</span>';
                }
            },
            { data: 'valor_efectivo',   className: 'text-end', render: function (d) { return fmtNum(d); } },
            { data: 'monto_minimo',     className: 'text-end', render: function (d) { return fmtNum(d); } },
            {
                data: 'valor_a_asegurar',
                className: 'text-end',
                render: function (d) {
                    return '<span class="text-success fw-bold">' + fmtNum(d) + '</span>';
                }
            }
        ], data.con_seguro);
    }

    var totConDecl = sumar(data.con_seguro, 'ValorDeclarado');
    var totConEfec = sumar(data.con_seguro, 'valor_efectivo');
    var totConAseg = sumar(data.con_seguro, 'valor_a_asegurar');
    $('#tfoot_con_declarado').text(fmt(totConDecl));
    $('#tfoot_con_efectivo').text(fmt(totConEfec));
    $('#tfoot_con_asegurar').text(fmt(totConAseg));

    // ── TABLA 2: Sin seguro ─────────────────────────────────────────────────
    dtSin = toggleSeccion(dtSin, 'tabla_sin_seguro', 'wrap_table_sin', 'no_data_sin', data.sin_seguro);
    if (data.sin_seguro.length > 0) {
        dtSin = buildDT('tabla_sin_seguro', [
            {
                data: 'Fecha',
                render: function (d) {
                    return '<span style="display:none">' + d + '</span>' + fmtFecha(d);
                }
            },
            {
                data: 'NumeroComprobante',
                render: renderComprobante
            },
            {
                data: 'RazonSocial',
                render: function (d, t, row) {
                    return '<b>' + d + '</b><br><small class="text-muted">' + (row.CodigoProveedor || '') + '</small>';
                }
            },
            { data: 'ValorDeclarado',   className: 'text-end', render: function (d) { return fmtNum(d); } },
            { data: 'monto_minimo',     className: 'text-end', render: function (d) { return fmtNum(d); } },
            {
                data: 'valor_a_asegurar',
                className: 'text-end',
                render: function (d) {
                    return '<span class="text-warning fw-bold">' + fmtNum(d) + '</span>';
                }
            }
        ], data.sin_seguro);
    }

    var totSinDecl = sumar(data.sin_seguro, 'ValorDeclarado');
    var totSinAseg = sumar(data.sin_seguro, 'valor_a_asegurar');
    $('#tfoot_sin_declarado').text(fmt(totSinDecl));
    $('#tfoot_sin_minimo').text(fmt(montoMin));
    $('#tfoot_sin_asegurar').text(fmt(totSinAseg));

    // ── TABLA 3: Excluidos ──────────────────────────────────────────────────
    dtExc = toggleSeccion(dtExc, 'tabla_excluidos', 'wrap_table_exc', 'no_data_exc', data.excluidos);
    if (data.excluidos.length > 0) {
        dtExc = buildDT('tabla_excluidos', [
            {
                data: 'Fecha',
                render: function (d) {
                    return '<span style="display:none">' + d + '</span>' + fmtFecha(d);
                }
            },
            {
                data: 'NumeroComprobante',
                render: renderComprobante
            },
            {
                data: 'RazonSocial',
                render: function (d, t, row) {
                    return '<b>' + d + '</b><br><small class="text-muted">' + (row.CodigoProveedor || '') + '</small>';
                }
            },
            { data: 'ValorDeclarado', className: 'text-end', render: function (d) { return fmtNum(d); } },
            {
                data: 'sure',
                className: 'text-center',
                render: function (d) {
                    return d == 1
                        ? '<span class="badge bg-success">Con Seguro</span>'
                        : '<span class="badge bg-secondary">Sin Seguro</span>';
                }
            }
        ], data.excluidos);
    }

    // ── Cards de resumen ────────────────────────────────────────────────────
    var cntCon = data.con_seguro.length;
    var cntSin = data.sin_seguro.length;
    var cntExc = data.excluidos.length;

    $('#resumen_con_cant').text(cntCon + ' servicios');
    $('#resumen_con_total').html('A asegurar: <b>' + fmt(totConAseg) + '</b>');
    $('#resumen_sin_cant').text(cntSin + ' servicios');
    $('#resumen_sin_total').html('A asegurar: <b>' + fmt(totSinAseg) + '</b>');
    $('#resumen_exc_cant').text(cntExc + ' servicios');
    $('#resumen_exc_total').html('Val. declarado: <b>' + fmt(sumar(data.excluidos, 'ValorDeclarado')) + '</b>');
    $('#resumen_total').text(fmt(totConAseg + totSinAseg));
    $('#label_monto_min_global').text('Mínimo global: ' + fmt(montoMin));

    $('#badge_con_seguro').text(cntCon + ' registros');
    $('#badge_sin_seguro').text(cntSin + ' registros');
    $('#badge_excluidos').text(cntExc + ' registros');

    // Mostrar secciones (siempre las 3 — vacías o no)
    $('#row_resumen').show();
    $('#row_calculo').show();
    $('#row_con_seguro').show();
    $('#row_sin_seguro').show();
    $('#row_excluidos').toggle(cntExc > 0);

    // Calcular % en tiempo real
    recalcularMonto();
}

// ── Recalcular monto del seguro según % ──────────────────────────────────────
function recalcularMonto() {
    if (!lastData) return;

    var perc       = parseFloat($('#filtro_perc').val()) || 1;
    var totConAseg = sumar(lastData.con_seguro, 'valor_a_asegurar');
    var totSinAseg = sumar(lastData.sin_seguro, 'valor_a_asegurar');
    var total      = totConAseg + totSinAseg;
    var monto      = total * (perc / 100);

    $('#resumen_perc_display').text(perc + '%');
    $('#resumen_monto_seguro').text(fmt(monto));
    $('#resumen_perc_formula').text(fmt(total) + ' × ' + perc + '% = ' + fmt(monto));
}

$('#filtro_perc').on('input change', recalcularMonto);

// ── Botón Consultar ───────────────────────────────────────────────────────────
$('#btn_consultar').on('click', function () {
    var desde = $('#filtro_desde').val();
    var hasta = $('#filtro_hasta').val();

    if (!desde || !hasta) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un rango de fechas.' });
        return;
    }

    var $btn      = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Consultando...');
    var excluidos = $('#filtro_excluir').val() || [];
    var cliente   = $('#filtro_cliente').val() || '';

    $.ajax({
        url: 'Procesos/php/seguros.php',
        method: 'POST',
        data: { accion: 'datos_seguros', desde: desde, hasta: hasta, cliente: cliente, excluidos: excluidos },
        success: function (response) {
            var data;
            try { data = JSON.parse(response); } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta no válida del servidor.' });
                return;
            }
            if (data.success == 1) {
                renderTablas(data);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener los datos.' });
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="mdi mdi-magnify me-1"></i>Consultar');
        }
    });
});

// ── Generador de PDF General ──────────────────────────────────────────────────
$('#btn_pdf_general').on('click', function () {
    if (!lastData) {
        Swal.fire({ icon: 'info', title: 'Sin datos', text: 'Primero realizá una consulta.' });
        return;
    }
    generatePDF();
});

function buildPdfRows(rows, tipo) {
    if (rows.length === 0) {
        return [{ text: 'Sin registros para el período seleccionado.', italics: true, color: '#888', margin: [0, 4, 0, 8], fontSize: 8 }];
    }

    var cols = {
        con: ['Fecha', 'Comprobante', 'Cód. Seguimiento', 'Cliente', 'Val. Declarado', '%', 'Val. Efectivo', 'Mínimo', 'A Asegurar'],
        sin: ['Fecha', 'Comprobante', 'Cód. Seguimiento', 'Cliente', 'Val. Declarado', 'Mín. Global', 'A Asegurar'],
        exc: ['Fecha', 'Comprobante', 'Cód. Seguimiento', 'Cliente', 'Val. Declarado', 'Tipo']
    };
    var widths = {
        con: [45, 55, 65, '*', 65, 25, 65, 55, 65],
        sin: [45, 55, 70, '*', 70, 65, 70],
        exc: [45, 55, 75, '*', 70, 55]
    };

    var headerRow = cols[tipo].map(function (h) {
        return { text: h, bold: true, fillColor: '#e8e8e8', fontSize: 7, margin: [2, 3, 2, 3] };
    });

    var body = [headerRow];
    rows.forEach(function (r, i) {
        var bg = i % 2 === 0 ? null : '#f9f9f9';
        var cellStyle = function(txt, align) {
            return { text: String(txt || ''), fontSize: 7, fillColor: bg, alignment: align || 'left', margin: [2, 2, 2, 2] };
        };
        var row = [];
        if (tipo === 'con') {
            row = [
                cellStyle(fmtFecha(r.Fecha)),
                cellStyle(r.NumeroComprobante || '—'),
                cellStyle(r.CodigoSeguimiento || '—'),
                cellStyle(r.RazonSocial),
                cellStyle(fmtNum(r.ValorDeclarado), 'right'),
                cellStyle(r.perc_aplicado + '%', 'center'),
                cellStyle(fmtNum(r.valor_efectivo), 'right'),
                cellStyle(fmtNum(r.monto_minimo), 'right'),
                { text: fmtNum(r.valor_a_asegurar), fontSize: 7, bold: true, fillColor: bg, alignment: 'right', color: '#0acf97', margin: [2,2,2,2] }
            ];
        } else if (tipo === 'sin') {
            row = [
                cellStyle(fmtFecha(r.Fecha)),
                cellStyle(r.NumeroComprobante || '—'),
                cellStyle(r.CodigoSeguimiento || '—'),
                cellStyle(r.RazonSocial),
                cellStyle(fmtNum(r.ValorDeclarado), 'right'),
                cellStyle(fmtNum(r.monto_minimo), 'right'),
                { text: fmtNum(r.valor_a_asegurar), fontSize: 7, bold: true, fillColor: bg, alignment: 'right', color: '#e0a800', margin: [2,2,2,2] }
            ];
        } else {
            row = [
                cellStyle(fmtFecha(r.Fecha)),
                cellStyle(r.NumeroComprobante || '—'),
                cellStyle(r.CodigoSeguimiento || '—'),
                cellStyle(r.RazonSocial),
                cellStyle(fmtNum(r.ValorDeclarado), 'right'),
                cellStyle(r.sure == 1 ? 'Con Seguro' : 'Sin Seguro', 'center')
            ];
        }
        body.push(row);
    });

    // Fila de totales
    if (tipo === 'con') {
        body.push([
            { text: 'TOTALES', bold: true, fontSize: 7, colSpan: 4, fillColor: '#e8e8e8', margin: [2,3,2,3] }, {}, {}, {},
            { text: fmtNum(sumar(rows, 'ValorDeclarado')),   bold: true, fontSize: 7, alignment: 'right', fillColor: '#e8e8e8', margin: [2,3,2,3] },
            { text: '', fillColor: '#e8e8e8' },
            { text: fmtNum(sumar(rows, 'valor_efectivo')),   bold: true, fontSize: 7, alignment: 'right', fillColor: '#e8e8e8', margin: [2,3,2,3] },
            { text: '', fillColor: '#e8e8e8' },
            { text: fmtNum(sumar(rows, 'valor_a_asegurar')), bold: true, fontSize: 7, alignment: 'right', fillColor: '#e8e8e8', color: '#0acf97', margin: [2,3,2,3] }
        ]);
    } else if (tipo === 'sin') {
        body.push([
            { text: 'TOTALES', bold: true, fontSize: 7, colSpan: 4, fillColor: '#e8e8e8', margin: [2,3,2,3] }, {}, {}, {},
            { text: fmtNum(sumar(rows, 'ValorDeclarado')),   bold: true, fontSize: 7, alignment: 'right', fillColor: '#e8e8e8', margin: [2,3,2,3] },
            { text: '', fillColor: '#e8e8e8' },
            { text: fmtNum(sumar(rows, 'valor_a_asegurar')), bold: true, fontSize: 7, alignment: 'right', fillColor: '#e8e8e8', color: '#e0a800', margin: [2,3,2,3] }
        ]);
    }

    return [{
        table: { headerRows: 1, widths: widths[tipo], body: body },
        layout: { hLineWidth: function() { return 0.3; }, vLineWidth: function() { return 0.3; }, hLineColor: function() { return '#ccc'; }, vLineColor: function() { return '#ccc'; } },
        margin: [0, 4, 0, 12]
    }];
}

function generatePDF() {
    var perc     = parseFloat($('#filtro_perc').val()) || 1;
    var desde    = $('#filtro_desde').val();
    var hasta    = $('#filtro_hasta').val();
    var now      = new Date();
    var fechaHora = now.toLocaleDateString('es-AR') + ' ' + now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

    var totConAseg = sumar(lastData.con_seguro, 'valor_a_asegurar');
    var totSinAseg = sumar(lastData.sin_seguro, 'valor_a_asegurar');
    var totExcDecl = sumar(lastData.excluidos,  'ValorDeclarado');
    var totalGlobal = totConAseg + totSinAseg;
    var montoSeguro = totalGlobal * (perc / 100);

    var headerDef = function(currentPage, pageCount) {
        var columns = [];
        if (LOGO_BASE64) {
            columns.push({ image: LOGO_BASE64, width: 55, margin: [20, 6, 0, 0] });
        }
        columns.push({
            stack: [
                { text: 'Caddy Triangular S.A.', bold: true, fontSize: 12, color: '#2c3e50' },
                { text: 'INFORME DE CÁLCULO DE SEGUROS', fontSize: 10, color: '#555' },
                { text: 'Período: ' + fmtFecha(desde) + ' al ' + fmtFecha(hasta), fontSize: 8, color: '#777' }
            ],
            alignment: 'center',
            margin: [0, 6, 0, 0]
        });
        columns.push({
            text: 'Pág. ' + currentPage + ' / ' + pageCount,
            alignment: 'right',
            fontSize: 7,
            color: '#999',
            margin: [0, 12, 20, 0]
        });
        return {
            columns: columns,
            margin: [0, 0, 0, 5]
        };
    };

    var footerDef = function() {
        return {
            columns: [
                { text: 'sistema.caddy.com.ar', fontSize: 7, color: '#aaa', margin: [20, 5, 0, 0] },
                { text: 'Usuario: ' + USUARIO_LOGADO + '   |   Generado: ' + fechaHora, alignment: 'center', fontSize: 7, color: '#888' },
                { text: '', margin: [0, 0, 20, 0] }
            ]
        };
    };

    var sectionTitle = function(txt, color) {
        return {
            text: txt,
            fontSize: 10,
            bold: true,
            color: color || '#333',
            margin: [0, 10, 0, 2],
            decoration: 'underline'
        };
    };

    var summaryTable = {
        table: {
            widths: ['*', 120],
            body: [
                [
                    { text: 'RESUMEN DEL INFORME', bold: true, fontSize: 9, fillColor: '#2c3e50', color: '#fff', colSpan: 2, alignment: 'center', margin: [4,6,4,6] },
                    {}
                ],
                [
                    { text: 'Período', fontSize: 8, fillColor: '#ecf0f1' },
                    { text: fmtFecha(desde) + ' al ' + fmtFecha(hasta), fontSize: 8, fillColor: '#ecf0f1', alignment: 'right' }
                ],
                [
                    { text: 'Con Seguro Propio (' + lastData.con_seguro.length + ' registros)', fontSize: 8 },
                    { text: fmt(totConAseg), fontSize: 8, alignment: 'right', bold: true, color: '#0acf97' }
                ],
                [
                    { text: 'Sin Seguro Propio (' + lastData.sin_seguro.length + ' registros)', fontSize: 8, fillColor: '#ecf0f1' },
                    { text: fmt(totSinAseg), fontSize: 8, alignment: 'right', bold: true, color: '#e0a800', fillColor: '#ecf0f1' }
                ],
                [
                    { text: 'Excluidos (' + lastData.excluidos.length + ' registros)', fontSize: 8 },
                    { text: fmt(totExcDecl), fontSize: 8, alignment: 'right', color: '#6c757d' }
                ],
                [
                    { text: 'TOTAL A ASEGURAR (con + sin seguro)', fontSize: 9, bold: true, fillColor: '#d6eaf8' },
                    { text: fmt(totalGlobal), fontSize: 9, bold: true, alignment: 'right', fillColor: '#d6eaf8', color: '#1a5276' }
                ],
                [
                    { text: '% Aplicado para el Seguro', fontSize: 8, fillColor: '#ecf0f1' },
                    { text: perc + '%', fontSize: 8, bold: true, alignment: 'right', fillColor: '#ecf0f1', color: '#c0392b' }
                ],
                [
                    { text: 'MONTO DEL SEGURO (' + fmt(totalGlobal) + ' × ' + perc + '%)', fontSize: 9, bold: true, fillColor: '#fde8e8' },
                    { text: fmt(montoSeguro), fontSize: 11, bold: true, alignment: 'right', fillColor: '#fde8e8', color: '#c0392b' }
                ]
            ]
        },
        layout: { hLineWidth: function() { return 0.5; }, vLineWidth: function() { return 0.5; }, hLineColor: function() { return '#bbb'; }, vLineColor: function() { return '#bbb'; } },
        margin: [0, 8, 0, 0],
        alignment: 'center'
    };

    var content = [
        sectionTitle('Con Seguro Propio (' + lastData.con_seguro.length + ' registros)', '#0acf97')
    ].concat(
        buildPdfRows(lastData.con_seguro, 'con'),
        [sectionTitle('Sin Seguro Propio (' + lastData.sin_seguro.length + ' registros)', '#e0a800')],
        buildPdfRows(lastData.sin_seguro, 'sin')
    );

    if (lastData.excluidos.length > 0) {
        content = content.concat(
            [sectionTitle('Excluidos del Cálculo (' + lastData.excluidos.length + ' registros)', '#6c757d')],
            buildPdfRows(lastData.excluidos, 'exc')
        );
    }

    content.push({ text: '', pageBreak: 'before' });
    content.push(summaryTable);

    var docDef = {
        pageSize:        'A4',
        pageOrientation: 'landscape',
        pageMargins:     [20, 65, 20, 40],
        header:          headerDef,
        footer:          footerDef,
        content:         content,
        defaultStyle: {
            font: 'Roboto'
        }
    };

    pdfMake.createPdf(docDef).download(
        'seguros_' + desde + '_a_' + hasta + '_' + perc + 'pct.pdf'
    );
}

// ── Init ──────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    cargarClientes();
});
