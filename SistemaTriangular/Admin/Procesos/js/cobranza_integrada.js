let fechas;
function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

var LOCALE_FECHAS_AR = {
  format: 'DD/MM/YYYY',
  applyLabel: 'Aplicar',
  cancelLabel: 'Cancelar',
  daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
  monthNames: [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
  ],
};

// FIX (reportado: "cuando abro el modal me pone la fecha mal, primero mes
// después día"): #fecha_receptor (campo "Fecha Entrega" del modal Generar
// Liquidación) se inicializaba sin locale.format - la librería arranca en
// su default MM/DD/YYYY (formato US). surrender_time se guarda como texto
// libre sin volver a parsearse en ningún lado, así que acá es seguro
// mostrarlo en DD/MM/YYYY como el resto del sistema.
if (window.jQuery && jQuery.fn.daterangepicker) {
  $('#fecha_receptor').daterangepicker({
    singleDatePicker: true,
    autoUpdateInput: true,
    startDate: moment(),
    locale: LOCALE_FECHAS_AR,
    cancelClass: 'btn-light',
    applyButtonClasses: 'btn-success',
  });

  // FIX (reportado: "el filtro me abre las fechas atrás del modal, y en
  // formato mes/día"): #singledaterange (rango del modal de filtro) antes
  // se inicializaba solo, vía el scan genérico data-toggle="date-picker" de
  // hyper/app.js - sin locale (formato US) y sin saber que vive dentro de
  // un modal Bootstrap, así que el calendario se pintaba fuera de stacking
  // del modal y quedaba tapado por él. Se inicializa acá a mano: con
  // locale DD/MM/YYYY para que se vea bien, y con parentEl apuntando
  // DENTRO del modal para que el calendario comparta su mismo contexto de
  // apilamiento y se vea siempre encima.
  //
  // El VALOR que este campo manda a Procesos/php/pagos.php (VerFechas) se
  // sigue armando en MM/DD/YYYY (ver #btn_filtrar más abajo) porque ese
  // endpoint lo parsea así - no se toca pagos.php, es compartido con otras
  // pantallas.
  $('#singledaterange').daterangepicker({
    autoUpdateInput: true,
    startDate: moment(),
    endDate: moment(),
    parentEl: '#modalFiltro .modal-body',
    locale: LOCALE_FECHAS_AR,
    cancelClass: 'btn-light',
    applyButtonClasses: 'btn-success',
  });
}

// FIX (recuperado de Caddy_produccion, a pedido): esta pantalla no tenía
// forma de filtrar por recorrido puntual ni de elegir "solo pendientes de
// rendición" - siempre traía todo. Se recupera el modal de filtro, que se
// abre solo al entrar y con el botón "Buscar" para volver a ajustarlo.
$(document).ready(function () {
  $('#modalFiltro').modal('show');
});

$('#cobranza_integrada_search').click(function () {
  $('#modalFiltro').modal('show');
});

$('#btn_filtrar').on('click', function () {
  var recorrido = $('#input_recorrido').val() || null;
  var soloPendientes = document.getElementById('customCheckcolor1').checked ? 1 : 0;

  var picker = $('#singledaterange').data('daterangepicker');
  if (!picker) {
    if (window.toast) toast('error', 'Cobranza Integrada', 'Seleccioná un rango de fechas.');
    else alert('Seleccioná un rango de fechas.');
    return;
  }

  // El input se muestra en DD/MM/YYYY (ver locale arriba), pero pagos.php
  // espera MM/DD/YYYY - se arma el string en ese formato acá, a partir de
  // las fechas ya elegidas en el picker (no del texto visible).
  fechas = picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY');

  $('#modalFiltro').modal('hide');
  cargarTabla(fechas, recorrido, soloPendientes);
});

// Arma (o rearma) la grilla de remitos pendientes/rendidos según el filtro
// elegido. Separado del handler de "Aceptar" del modal para poder llamarlo
// desde ahí sin duplicar la lógica de armado de la DataTable.
function cargarTabla(fechasElegidas, recorrido, soloPendientes) {
  var datatableActual = $.fn.DataTable.isDataTable('#cobranza_integrada') ? $('#cobranza_integrada').DataTable() : null;
  if (datatableActual) datatableActual.destroy();

  $.ajax({
    data: { 'VerFechas': 1, 'Fechas': fechasElegidas },
    url: 'Procesos/php/pagos.php',
    type: 'post',
    success: function (response) {
      // Defensivo por consistencia con los otros $.ajax de este archivo:
      // pagos.php no manda Content-Type: application/json, así que jQuery
      // entrega esto como texto y hace falta parsearlo - pero por las
      // dudas de que cambie, acepta los dos casos.
      var jsonData = (typeof response === 'string') ? JSON.parse(response) : response;
      var Inicio = jsonData.Inicio;
      var Final = jsonData.Final;

      var datatable = $('#cobranza_integrada').DataTable({
        dom: 'Bfrtip',
        buttons: buildDtButtons(["pageLength", "copy", "csv", "excel", "pdf", "print"]),
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, 'All']
        ],
        paging: true,
        searching: true,
        footerCallback: function (row, data, start, end, display) {
          total = this.api()
            .column(6, { page: 'current' }) // CobrarEnvio - ver el array "columns" de más abajo para el índice
            .data()
            .reduce(function (a, b) {
              return Number(a) + Number(b);
            }, 0);
          var saldo = currencyFormat(total);

          $(this.api().column(6).footer()).html(saldo);
        },

        ajax: {
          url: "Procesos/php/cobranza_integrada.php",
          data: { 'Pendientes': 1, 'Inicio': Inicio, 'Final': Final, 'Recorrido': recorrido, 'SoloPendientes': soloPendientes },
          processing: true,
          type: 'post',
        },
        columns: [
          {
            data: "FechaPedido",
            render: function (data, type, row) {
              var Fecha = row.FechaPedido.split('-').reverse().join('.');
              return '<td><span style="display: none;">' + row.FechaPedido + '</span>' + Fecha + '</td>';
            }
          },
          { data: "Usuario" },
          // FIX (recuperado de Caddy_produccion, a pedido): esta columna no
          // existía - sin ella no había forma de saber a qué recorrido
          // pertenecía cada remito desde esta pantalla.
          { data: "Recorrido" },
          {
            data: "Cliente",
            // FIX (reportado: "la tabla no entra, achicá la letra"): esta
            // celda tenía 2 <h6 class="font-15"> apilados (15px c/u, con su
            // propio margen) más badges - inflaba mucho el alto de cada
            // fila. Se compacta a una sola línea "Origen → Destino" +
            // badges chicos (clase .ci-fila-compacta, ver CSS de la pantalla).
            // FIX (reportado: "sigue sobresalido, achicá más"): estaba en
            // 2 líneas (texto + badges en su propia fila). Se junta el
            // badge en la MISMA línea del texto - queda 1 sola línea la
            // mayoría de las veces (2 solo si el nombre es muy largo).
            render: function (data, type, row) {
              var entregado = row.Entregado == 1
                ? '<span class="badge rounded-pill bg-success text-white">Entregado</span>'
                : '<span class="badge rounded-pill bg-danger text-white">No Entregado</span>';
              var devuelto = row.Devuelto == 1
                ? ' <span class="badge rounded-pill bg-warning text-white">Devuelto</span>'
                : '';
              return '<div class="ci-fila-titulo">' + row.Cliente + ' &rarr; ' + row.ClienteDestino +
                ' ' + entregado + devuelto + '</div>';
            }
          },
          {
            data: "Titulo",
            // FIX (recuperado de Caddy_produccion, a pedido): antes el código
            // de proveedor y el de seguimiento se mostraban como texto gris
            // suelto - ahora van como badges. Se juntan Código Proveedor +
            // ambos badges en UNA sola línea de subtítulo (antes eran 2
            // líneas separadas) para que la celda ocupe menos alto.
            render: function (data, type, row) {
              return '<div class="ci-fila-titulo">' + row.Titulo + '</div>' +
                '<div class="ci-fila-sub">Cód. Prov: ' + (row.CodigoProveedor || '-') + ' &nbsp; ' +
                '<span class="badge rounded-pill bg-warning text-white">' + row.NumeroRepo + '</span> ' +
                '<span class="badge rounded-pill bg-success text-white">' + row.NumPedido + '</span></div>';
            }
          },
          { data: "Comentario" },
          {
            data: "CobrarEnvio",
            render: $.fn.dataTable.render.number(',', '.', 2, '$ ')
          },
          {
            data: "surrender_name",
            render: function (data, type, row) {
              return '<div class="ci-fila-titulo">' + row.surrender_name + '</div>' +
                '<div class="ci-fila-sub">' + row.surrender_time + '</div>';
            }
          },
          {
            data: "idPedido",
            render: function (data, type, row) {
              if (row.surrender_name != '') {
                return `<td class="table-action"><i class="mdi mdi-18px mdi-file-document-outline" onclick="imp(${row.surrender_number});"></i></td>`;
              } else {
                return `<td class="table-action"><input data-id="${row.CobrarEnvio}" id="surrender_checkbox" value="${row.idPedido}" type="checkbox" class"custom-control-input">
                        <td class="table-action"><i class="mdi mdi-18px mdi-pencil text-warning" onclick="change(${row.idPedido},${row.CobrarEnvio});"></i></td>`;
              }
            }
          },

        ]
      });
    }
  });
}

// FIX: apuntaba a www.caddy.com.ar (el sitio público, no el sistema) con
// una URL sin ".php" - nunca abría nada real. Ahora abre, en pestaña
// aparte, la liquidación en PDF (mismo formato que el resto de los
// comprobantes del sistema - ver Admin/Informes/CobranzaIntegradaPdf.php).
// De paso 't_blank' -> '_blank' (typo: con nombre custom, si ya había una
// pestaña con ese nombre la reusaba en vez de abrir una nueva).
function imp(i) {
  window.open('Informes/invoice_cobranza_integrada.php?id=' + i, '_blank');
}
// FIX: la tabla pagina (paging:true) - DataTables solo mantiene en el
// DOM las filas de la página actual. Dos bugs distintos por esto:
//  1) El badge de cantidad se recalculaba consultando el DOM cada vez
//     ($('#surrender_checkbox:checked').length) - solo contaba lo
//     tildado en la página visible en ese momento, un remito tildado
//     en otra página "desaparecía" del conteo al cambiar de página.
//  2) MÁS GRAVE: el botón "Aceptar" armaba la lista a enviar de la
//     MISMA forma ($('#surrender_checkbox:checked').each(...)) - si
//     el operador tildaba remitos en más de una página, al aceptar
//     solo se procesaban los de la página que estaba visible en ese
//     momento; los de las otras páginas se perdían en silencio, sin
//     ningún aviso.
// Se lleva la selección real en un objeto propio (idPedido -> true),
// que sobrevive a que DataTables reemplace las filas al paginar. El
// total en $ ya se acumulaba en una variable aparte y no tenía este
// problema.
var seleccionadosCobranza = {};

function cantidadSeleccionados() {
  return Object.keys(seleccionadosCobranza).length;
}

function actualizarBadgeCantidad(cantidad) {
  $('#cobranza_integrada_cantidad').text(cantidad + (cantidad === 1 ? ' remito' : ' remitos'));
}

$(document).on('change', 'input[type="checkbox"]', function (e) {
  var total = Number($('#cobranza_integrada_header').html());
  if (this.id == "surrender_checkbox") {
    e.preventDefault();
    var elemento = e.target;

    var dataID = elemento.getAttribute('data-id');
    var idPedido = elemento.getAttribute('value');
    if (this.checked) {
      total = total + Number(dataID);
      if (idPedido) seleccionadosCobranza[idPedido] = true;
    } else {
      total = total - Number(dataID);
      if (idPedido) delete seleccionadosCobranza[idPedido];
    }

    $('#cobranza_integrada_header').html(total);
    actualizarBadgeCantidad(cantidadSeleccionados());
    if (total != 0) {
      $('#cobranza_integrada_clear').prop('disabled', false);
      $('#cobranza_integrada_report').prop('disabled', false);
    } else {
      $('#cobranza_integrada_clear').prop('disabled', true);
      $('#cobranza_integrada_report').prop('disabled', true);
    }
  }
});

$('#cobranza_integrada_clear').click(function () {
  var datatable = $('#cobranza_integrada').DataTable();
  datatable.ajax.reload();
  $('#cobranza_integrada_header').html(0);
  seleccionadosCobranza = {};
  actualizarBadgeCantidad(0);
  $('#cobranza_integrada_clear').prop('disabled', true);
  $('#cobranza_integrada_report').prop('disabled', true);
});

$('#cobranza_integrada_report').click(function () {
  $('#standard-modal').modal('show');
});

$('#generar_informe_ok').click(function () {
  // FIX: antes se armaba esta lista consultando el DOM
  // ($('#surrender_checkbox:checked').each(...)) - con la tabla paginada,
  // eso solo encuentra los remitos tildados en la página que está
  // visible en ESTE momento. Si el operador tildó remitos en más de una
  // página, los de las otras páginas se perdían en silencio (nunca se
  // enviaban al server). Ahora se usa la selección real, llevada aparte
  // en seleccionadosCobranza, que no depende de qué página esté
  // mostrando la tabla ahora mismo.
  var checked = Object.keys(seleccionadosCobranza);

  if (checked.length > 0) {
    let nombre = $("#nombre_receptor").val();
    let dni = $('#dni_receptor').val();
    let obs = $('#observaciones_receptor').val();
    let fecha = $('#fecha_receptor').val();
    let hora = $('#hora_receptor').val();

    var dato = {
      "Cobranza_Integrada": 1,
      "id": checked,
      "nombre": nombre,
      "dni": dni,
      "obs": obs,
      "fecha": fecha,
      "hora": hora
    };

    $.ajax({
      data: dato,
      url: 'Procesos/php/cobranza_integrada.php',
      type: 'post',
      success: function (response) {
        // FIX: cobranza_integrada.php manda Content-Type: application/json ->
        // jQuery YA entrega esto parseado como objeto (no como texto). Volver a
        // pasarlo por JSON.parse() rompía con "[object Object] is not valid JSON"
        // y el botón "Aceptar" no hacía nada. Se acepta cualquiera de los dos
        // casos por las dudas (si algún día cambia el Content-Type, sigue andando).
        var jsonData = (typeof response === 'string') ? JSON.parse(response) : response;

        // FIX: acá se mostraba #standard-modal-invoice, un modal que
        // nunca tuvo ningún JS que lo llenara de datos (quedaba vacío,
        // "no se sabe si terminó"). Ahora abre, en pestaña aparte, la
        // misma liquidación en PDF que ya usa el botón "Ver" (ícono de
        // documento) de la grilla - mismo formato que el resto de los
        // comprobantes del sistema.
        $('#standard-modal').modal('hide');
        window.open('Informes/invoice_cobranza_integrada.php?id=' + jsonData.surrender_number, '_blank');
        console.log('ver', jsonData.surrender_number);
      }
    });
  }

});


function change(id, imp) {

  $('#label_change_import').html(id);

  $('#modal_change_import').modal('show');

  $('#number_change_import').val(imp);
}


$('#btn_ok_change_import').click(function () {

  let id = $('#label_change_import').html();

  var importe = $('#number_change_import').val();

  $.ajax({
    data: { 'Change_import': 1, 'id': id, 'Importe': importe },
    url: 'Procesos/php/cobranza_integrada.php',
    type: 'post',
    success: function (response) {
      // FIX: mismo motivo que en generar_informe_ok - cobranza_integrada.php
      // manda Content-Type: application/json, jQuery ya lo entrega parseado.
      var jsonData = (typeof response === 'string') ? JSON.parse(response) : response;

      if (jsonData.success == 1) {

        $('#modal_change_import').modal('hide');

        var datatable = $('#cobranza_integrada').DataTable();
        datatable.ajax.reload();
        toast("success", "Registro Actualizado !", "Se han realizado cambios.");

      } else {

        toast("error", "Ocurrio un Error !", "No se realizaron cambios.");

      }

    }
  });

})
