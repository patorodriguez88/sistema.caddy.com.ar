$('#confirmardescuento_botton').click(function(){
  // Reusamos la selección capturada al apretar "Facturar" (funciones.js),
  // NO escaneamos el DOM acá: evita aplicar el descuento sobre una
  // selección desincronizada (mismo bug que causaba facturas incompletas).
  var checked = remitosSeleccionadosFacturar;

  if (!checked || checked.length === 0) {
    toast("error", "Error", "No hay Remitos Seleccionados. No se puede aplicar el descuento.");
    return;
  }

  var descuento = document.getElementById("descuentootorgado_t").value;

  Swal.fire({
    icon: 'warning',
    title: 'Aplicar descuento',
    text: 'Seguro desea aplicar el descuento de ' + descuento + ' % ?',
    showCancelButton: true,
    confirmButtonText: 'Sí, aplicar',
    cancelButtonText: 'Cancelar'
  }).then(function (result) {
    if (!result.isConfirmed) {
      toast("info", "Cancelado", "No se aplicó el descuento.");
      return;
    }

    $.ajax({
      data: { 'Descontar': 1, 'Descuento': descuento, 'Remitos': checked },
      url: 'Procesos/php/descuento.php',
      type: 'post',
      dataType: 'json',
      success: function (response) {
        if (!response || response.success != 1) {
          toast("error", "Error", (response && response.msg) || "No se pudo aplicar el descuento.");
          return;
        }

        var desc = Number($('#factura_descuento').html()) + Number(descuento);
        $('#factura_descuento').html(desc);
        $('#descuento-modal').modal('hide');

        // Recargamos las mismas tablas que se actualizan tras confirmar una
        // factura (ver #confirmarfactura_AFIP_boton), ya que el descuento
        // modifica los mismos importes (TransClientes/Ctasctes) que ellas muestran.
        var tabla_facturacion_proforma = $('#tabla_facturacion_proforma').DataTable();
        tabla_facturacion_proforma.ajax.reload();

        // El botón ahora también queda visible en la pantalla de Detalle
        // (antes se ocultaba ahí) - si el descuento se aplicó desde esa
        // pantalla, hay que refrescar SU tabla, no la del resumen.
        if ($.fn.DataTable.isDataTable('#tabla_facturacion_proforma_detalle')) {
          $('#tabla_facturacion_proforma_detalle').DataTable().ajax.reload();
        }

        if ($.fn.DataTable.isDataTable('#facturacion_tabla')) {
          $('#facturacion_tabla').DataTable().ajax.reload();
        }

        if ($.fn.DataTable.isDataTable('#basic')) {
          $('#basic').DataTable().ajax.reload();
        }

        toast("success", "Descuento aplicado", "Se aplicó el descuento en los remitos seleccionados.");
      },
      error: function () {
        toast("error", "Error", "No se pudo aplicar el descuento.");
      }
    });
  });
});
