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
            { data: 'valor_real',       className: 'text-end', render: function (d) { return fmtNum(d); } },
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

    var totConDecl = sumar(data.con_seguro, 'valor_real');
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

// ── Informe PDF General: abre SegurosPdf.php en una pestaña nueva ─────────────
// (mismo patrón que "Ver / Imprimir Mayor" en Contabilidad: el PDF se genera
// en el servidor con Output('I', ...), así el navegador lo muestra online
// antes de descargarlo, en vez de forzar la descarga directa como hacía
// pdfMake acá antes.)
$('#btn_pdf_general').on('click', function () {
    if (!lastData) {
        Swal.fire({ icon: 'info', title: 'Sin datos', text: 'Primero realizá una consulta.' });
        return;
    }

    var desde    = $('#filtro_desde').val();
    var hasta    = $('#filtro_hasta').val();
    var perc     = parseFloat($('#filtro_perc').val()) || 1;
    var cliente  = $('#filtro_cliente').val() || '';
    var excluidos = $('#filtro_excluir').val() || [];

    var params = new URLSearchParams({ Desde: desde, Hasta: hasta, Perc: perc });
    if (cliente) { params.set('Cliente', cliente); }
    if (excluidos.length) { params.set('Excluidos', excluidos.join(',')); }

    window.open('Informes/SegurosPdf.php?' + params.toString(), '_blank');
});

// ── Init ──────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    cargarClientes();
});
