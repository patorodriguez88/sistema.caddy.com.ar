
//GENERAR COMPROBANTE AFIP (Nota de Crédito / Débito) - modal propio #ncnd-modal,
// independiente del modal de Facturación (no requiere seleccionar remitos).

var TIPOS_NCND = {
    '2': 'NOTAS DE DEBITO A',
    '3': 'NOTAS DE CREDITO A',
    '7': 'NOTAS DE DEBITO B',
    '8': 'NOTAS DE CREDITO B'
};

$("#confirmar_ncnd_boton").click(function() {

    var fecha = document.getElementById('ncnd_fecha').value;
    var id = document.getElementById('buscarcliente').value;
    var neto = document.getElementById('ncnd_neto').value;
    var iva = document.getElementById('ncnd_iva').value;
    var total = document.getElementById('ncnd_total').value;

    //FACTURACION (datos fiscales del cliente, ya cargados al seleccionarlo)
    var razonsocial_f = document.getElementById('razonsocial_facturacion').value;
    var direccion_f = document.getElementById('direccion_facturacion').value;
    var tipodocumento_f = document.getElementById('tipodocumento_facturacion').value;
    var documento_f = document.getElementById('cuit_facturacion').value;
    var fecha_desde = fecha;
    var fecha_hasta = fecha;

    if (document.getElementById('nueva_condicion_facturacion').value != '') {
        var condiva_f = document.getElementById('nueva_condicion_facturacion').value;
    } else {
        var condiva_f = document.getElementById('condicion_facturacion').value;
    }

    var comprobante_tipo = $('#ncnd_comprobante_tipo').val();
    var comprobante = TIPOS_NCND[comprobante_tipo];

    var $asociado = $('#ncnd_comprobante_asociado').find(':selected');
    var idComprobanteAsociado = $('#ncnd_comprobante_asociado').val();
    var cbteasoc_tipo_n = $asociado.attr('data-tipo-n');
    var cbteasoc_ptovta = $asociado.attr('data-ptovta');
    var cbteasoc_nro = $asociado.attr('data-nro');

    var observaciones_ctasctes = document.getElementById('ncnd_observaciones').value;

    // Validaciones minimas antes de mandar a AFIP
    if (!comprobante) {
        toast("error", "Error", "Falta seleccionar el tipo de Nota de Crédito/Débito a generar.");
        return;
    }
    if (!idComprobanteAsociado || !cbteasoc_nro) {
        toast("error", "Error", "Falta elegir el comprobante que se quiere corregir.");
        return;
    }
    if (!total || Number(total) <= 0) {
        toast("error", "Error", "El importe total debe ser mayor a 0.");
        return;
    }

   var dato = {
      'Fecha':fecha,
      'razonsocial_f': razonsocial_f,
      'direccion_f': direccion_f,
      'condiva_f': condiva_f,
      'tipodocumento_f': tipodocumento_f,
      'documento_f': documento_f,
      'Documento': 99,
      'ImpTotal': total,
      'ImpTotalConc': 0,
      'ImpNeto': neto,
      'ImpIva': iva,
      'ImpTrib': 0,
      'Comprobante':comprobante,
      'fecha_desde':fecha_desde,
      'fecha_hasta':fecha_hasta,
      'Comprobante_tipo':comprobante_tipo,
      'cbteasoc_tipo_n':cbteasoc_tipo_n,
      'CbtesAsoc_PtoVta':cbteasoc_ptovta,
      'cbteasoc_nro':cbteasoc_nro,
      'Observaciones_ctasctes':observaciones_ctasctes,

    };

    $.ajax({
      data: dato,
      url:'../afip.php/procesos/CreateVoucherncnb.php', //PRODUCCION AFIP - sin cert de homologacion disponible por ahora (ver notas 2026-08-06)
      type: 'post',

    beforeSend: function() {

        $('#ncnd-modal').modal('hide');
        $('#warning-alert-modal').modal('show');

        $("#warning_text").html("Enviando los datos a AFIP");

      },

      success: function(respuesta) {

        try {

        var jsonData = $.parseJSON(respuesta);

        if(jsonData.data==1){

            if (jsonData.CAE != '') {

            $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-checkmark h1 text-success');

            $('#warning_mt2_alert').html('Exito !');

            $("#warning_text").html("Exito ! Comprobante N "+jsonData.Numero);

            //FACTURO EN EL SISTEMA
            var datofacturasistema = {
                'Facturar': 3,
                'fecha': fecha,
                'razonsocial_f': razonsocial_f,
                'direccion_f': direccion_f,
                'condiva_f': condiva_f,
                'tipodocumento_f': tipodocumento_f,
                'documento_f': documento_f,
                'Documento': 99,
                'NumeroDocumento': documento_f,
                'ImpTotal': total,
                'ImpTotalConc': 0,
                'ImpNeto': neto,
                'ImpIva': iva,
                'ImpTrib': 0,
                'exento_t': 0,
                'cantidad_t': 1,
                'titulo_t': comprobante,
                'observaciones_t': observaciones_ctasctes,
                'tipodecomprobante_t': comprobante,
                'id': id,
                'NumeroComprobante': jsonData.Numero,
                'PtoVta': jsonData.PtoVta,
                'Comprobante': comprobante,
                'CAE': jsonData.CAE,
                'FechaVencimientoCAE': jsonData.VencimientoCAE,
                'Observaciones_ctasctes': observaciones_ctasctes,
                'idComprobanteAsociado': idComprobanteAsociado,
                'alcance_nc': obtenerAlcanceNc(),
                'servicios_liberar': obtenerServiciosALiberar()
            };

            $.ajax({
                data: datofacturasistema,
                url: 'Procesos/php/facturar.php',
                type: 'post',
                success: function(respuesta) {
                var jsonData1 = JSON.parse(respuesta);
                if (jsonData1.success == 1) {
                    if (jsonData1.msg) {
                        toast("warning", "Comprobante Generado, con un aviso", jsonData1.msg);
                    } else {
                        toast("success", "Comprobante Generado con Exito !", "Se han realizado cambios.");
                    }

                    //ACTUALIZO LA TABLA CTA CTE
                    var tabla_ctacte = $('#basic').DataTable();
                    tabla_ctacte.ajax.reload();

                } else if (jsonData1.success == 0) {

                    toast("error", "Error al Intentar Generar el Comprobante !", "El comprobante ya se emitió en AFIP con CAE " + jsonData.CAE + " pero no se pudo guardar en el sistema. Avisá a sistemas con este CAE.");

                } else if (jsonData1.success == 3) {

                    toast("error", "Error en el Codigo de Afip del Cliente !", "El comprobante ya se emitió en AFIP con CAE " + jsonData.CAE + " pero no se pudo guardar en el sistema. Avisá a sistemas con este CAE.");
                }
                }
            });

            }
    }else{

    $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');

    $('#warning_mt2_alert').html('Error !');

    $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);


    }

} catch (err) {

    $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');

    $('#warning_mt2_alert').html('Error !');

    $("#warning_text").html("Error! Comprobante No Facturado Error: "+err.message);

    console.log('Error: ', err.message);
  }

}
});
});

//FACTURA AFIP

$("#confirmarfactura_AFIP_boton").click(function() {
    
    var fecha = document.getElementById('fecha_up').value;
    var id = document.getElementById('buscarcliente').value;
    var neto = document.getElementById('factura_neto_f').value;
    var iva = document.getElementById('factura_iva_f').value;
    var total = document.getElementById('factura_total_f').value;
    var sitfiscal = document.getElementById('condicion').value;
    var fecha_desde=$('#desde_f').html();
    var fecha_hasta=$('#hasta_f').html();
    var vencpago=$('#venc_pago').html();    

    //FACTURACION
    var razonsocial_f = document.getElementById('razonsocial_facturacion').value;
    var direccion_f = document.getElementById('direccion_facturacion').value;
    var tipodocumento_f = document.getElementById('tipodocumento_facturacion').value;
    var documento_f = document.getElementById('cuit_facturacion').value;
    var cai_f = document.getElementById('cai_facturacion').value;    
    

    //CUADRO FACTURACION
    var fecha = document.getElementById('fecha_up').value;
    var comprobante = document.getElementById('comprobante_up').value;
    var observaciones_ctasctes=document.getElementById('observaciones_ctasctes').value;

    if (document.getElementById('nueva_condicion_facturacion').value != '') {
      var condiva_f = document.getElementById('nueva_condicion_facturacion').value;
    } else {
      var condiva_f = document.getElementById('condicion_facturacion').value;
    }

    var comprobante_tipo=$('#comprobante_tipo').val();

    // Reusamos la selección capturada al apretar "Facturar" (funciones.js),
    // NO volvemos a escanear el DOM acá.
    var checked = remitosSeleccionadosFacturar;
    if (!checked || checked.length === 0) {
      toast("error", "Error", "No hay Remitos Seleccionados. No se puede facturar.");
      return;
    }

   var ncomp = $('#ncomprobante_up').val();

   var dato = {
      'Fecha':fecha,
      'razonsocial_f': razonsocial_f,
      'direccion_f': direccion_f,
      'condiva_f': condiva_f,
      'tipodocumento_f': tipodocumento_f,
      'documento_f': documento_f,
      'Documento': 99,
      'ImpTotal': total,
      'ImpTotalConc': 0,
      'ImpNeto': neto,
      'ImpIva': iva,
      'ImpTrib': 0,
      'Comprobante':comprobante,
      'fecha_desde':fecha_desde,
      'fecha_hasta':fecha_hasta,
      'Comprobante_tipo':comprobante_tipo
    };
  
    $.ajax({
      data: dato, 
      url:'../afip.php/procesos/CreateVoucher.php', //HABILITAR PARA FACTURA AFIP _false PARA HOMOLOGACION       
      type: 'post',
    
    beforeSend: function() {

        $('#warning-alert-modal').modal('show');

        $("#warning_text").html("Enviando los datos a AFIP");

      },
  
      success: function(respuesta) {

        try {
        
        var jsonData = $.parseJSON(respuesta);

        if(jsonData.data==1){
        
            if (jsonData.hasOwnProperty('CAE') && jsonData.CAE !== null) {                                    
            
            $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-checkmark h1 text-success');
        
            $('#warning_mt2_alert').html('Exito !');

            $("#warning_text").html("Exito ! Comprobante N "+jsonData.Numero);
            

            document.getElementById('datos_cae').style.display = "block";
            
            $('#CAE').html(jsonData.CAE);//HABILITAR PARA FACTURA AFIP 
            
            var FechaVenc=jsonData.VencimientoCAE.split('-').reverse().join('/');//HABILITAR PARA FACTURA AFIP 
            
            $('#VencimientoCAE').html(FechaVenc);//HABILITAR PARA FACTURA AFIP 
            
            if(condiva_f==1){
            
            $('#factura_titulo2').html('FACTURA A');//HABILITAR PARA FACTURA AFIP  
            $('#factura_titulo').html('FACTURA A'); //HABILITAR PARA FACTURA AFIP 
            
            }else{

            $('#factura_titulo2').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            $('#factura_titulo').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            
            }
            
            $('#NumeroComprobante').html(jsonData.Numero);  //HABILITAR PARA FACTURA AFIP 
            
            //FACTURO EN EL SISTEMA
            var datofacturasistema = {
                'Facturar': 1, //0 PRUEBA
                'fecha': fecha,
                'razonsocial_f': razonsocial_f,
                'direccion_f': direccion_f,
                'condiva_f': condiva_f,
                'tipodocumento_f': tipodocumento_f,
                'documento_f': documento_f,
                'Documento': 99,
                'ImpTotal': total,
                'ImpTotalConc': 0,
                'ImpNeto': neto,
                'ImpIva': iva,
                'ImpTrib': 0,                
                'Remitos': checked,
                'id': id,
                'condicion': sitfiscal,
                'NumeroComprobante': jsonData.Numero,
                'Comprobante':comprobante,
                'CAE':jsonData.CAE,
                'FechaVencimientoCAE':jsonData.VencimientoCAE,
                'PtoVta':jsonData.PtoVta,
                'Observaciones_ctasctes':observaciones_ctasctes,
                'Vencpago':vencpago
            };
            
            $.ajax({
                data: datofacturasistema,
                url: 'Procesos/php/facturar.php',
                type: 'post',
                success: function(respuesta) {
                var jsonData1 = JSON.parse(respuesta);
                if (jsonData1.success == 1) {
                    toast("success", "Comprobante Generado con Exito !", "Se han realizado cambios.");
                    //DESTRUIMOS LA TABLA FACTURACION
                    var table = $('#tabla_facturacion_proforma').DataTable();
                    table.destroy();
                    
                    var tabla_facturacion = $('#facturacion_tabla').DataTable();
                    tabla_facturacion.ajax.reload();
                    
                    //ACTUALIZO LA TABLA CTA CTE
                    var tabla_ctacte = $('#basic').DataTable();
                    tabla_ctacte.ajax.reload();
    
                    document.getElementById('factura_primerpaso').style.display = "block";
                    document.getElementById('factura_proforma').style.display = "none";
                    
                } else if (jsonData1.success == 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'El comprobante quedó a medio guardar',
                        text: jsonData1.msg || 'AFIP ya autorizó el comprobante (CAE ya emitido) pero no se pudo guardar en el sistema. Avisá a sistemas.',
                        confirmButtonText: 'Entendido'
                    });
                } else if (jsonData1.success == 3) {
                    toast("error", "Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.");
                }
                }
            });
            }

        }else{
        
        $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
    
        $('#warning_mt2_alert').html('Error !');

        $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);
    
          
        }

    } catch (err) {
        
        $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
        
        $('#warning_mt2_alert').html('Error !');

        $("#warning_text").html("Error! Comprobante No Facturado Error: "+err.message);

        console.log('Error: ', err.message);
      }

    }
    });
  });


//FACTURA AFIP RECORRIDOS

$("#confirmarfacturaxrecorrido_AFIP_boton").click(function() {
  
    var id = document.getElementById('buscarcliente').value;
    var neto = document.getElementById('factura_neto_f').value;
    var iva = document.getElementById('factura_iva_f').value;
    var total = document.getElementById('factura_total_f').value;
    var sitfiscal = document.getElementById('condicion').value;
    var fecha_desde=$('#desde_f').html();
    var fecha_hasta=$('#hasta_f').html();

    //FACTURACION
    var razonsocial_f = document.getElementById('razonsocial_facturacion').value;
    var direccion_f = document.getElementById('direccion_facturacion').value;
  
    //CUADRO FACTURACION
    var fecha = document.getElementById('fecha_up_r').value;
    var condiva_f=document.getElementById('comprobante_up_r').value;    
    // var observaciones=document.getElementById('observaciones_up_r').value;

    var tipodocumento_f = document.getElementById('tipodocumento_facturacion').value;
    var comprobante = document.getElementById('select_up_r').value;
    var documento_f = document.getElementById('cuit_facturacion').value;
    var cai_f = document.getElementById('cai_facturacion').value;
    
    if (document.getElementById('nueva_condicion_facturacion').value != '') {
      var condiva_f = document.getElementById('nueva_condicion_facturacion').value;
    } else {
      var condiva_f = document.getElementById('condicion_facturacion').value;
    }
    
    const newLocal = '#comprobante_tipo_r';
    var comprobante_tipo=$(newLocal).val();

    // Reusamos la selección capturada al apretar "Facturar Recorridos"
    // (funciones.js), NO volvemos a escanear el DOM acá (y NO usamos el
    // selector genérico custom-control-input, que también matchea la
    // tabla de remitos).
    var checked = recorridosSeleccionadosFacturar;
    if (!checked || checked.length === 0) {
      toast("error", "Error", "No hay Recorridos Seleccionados. No se puede facturar.");
      return;
    }

    var dato = {
      'Fecha': fecha,
      'razonsocial_f': razonsocial_f,
      'direccion_f': direccion_f,
      'condiva_f': condiva_f,
      'tipodocumento_f': tipodocumento_f,
      'documento_f': documento_f,
      'Documento': 99,
      'ImpTotal': total,
      'ImpTotalConc': 0,
      'ImpNeto': neto,
      'ImpIva': iva,
      'ImpTrib': 0,
      'Comprobante':comprobante,
      'fecha_desde':fecha_desde,
      'fecha_hasta':fecha_hasta,
      'Comprobante_tipo':comprobante_tipo
    };
  
    $.ajax({
      data: dato,
      url:'../afip.php/procesos/CreateVoucher.php', //HABILITAR PARA FACTURA AFIP 
    //   url:'../afip.php/procesos/CreateVoucher_false.php', //HABILITAR PARA FACTURA AFIP 
      type: 'post',
      beforeSend: function() {

        $('#warning-alert-modal').modal('show');

        $("#warning_text").html("Enviando los datos a AFIP");

      },

      success: function(respuesta2) {
          
        try {

        var jsonData = $.parseJSON(respuesta2);

        if(jsonData.data==1){

        if (jsonData.hasOwnProperty('CAE') && jsonData.CAE !== null) {

        $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-checkmark h1 text-success');
    
        $('#warning_mt2_alert').html('Exito !');

        $("#warning_text").html("Exito ! Comprobante N "+jsonData.Numero);

          document.getElementById('datos_cae').style.display = "block";

          $('#CAE').html(jsonData.CAE);
          
          var FechaVenc=jsonData.VencimientoCAE.split('-').reverse().join('/');//HABILITAR PARA FACTURA AFIP 
          
          $('#VencimientoCAE').html(FechaVenc);//HABILITAR PARA FACTURA AFIP 

          $('#NumeroComprobante').html(jsonData.Numero);  //HABILITAR PARA FACTURA AFIP 

          //FACTURO EN EL SISTEMA
          var datofacturasistema = {
            'Facturar': 2,
            'fecha': fecha,
            'razonsocial_f': razonsocial_f,
            'direccion_f': direccion_f,
            'condiva_f': condiva_f,
            'tipodocumento_f': tipodocumento_f,
            'documento_f': documento_f,
            'Documento': 99,
            'ImpTotal': total,
            'ImpTotalConc': 0,
            'ImpNeto': neto,
            'ImpIva': iva,
            'ImpTrib': 0,
            'Remitos': checked,
            'id': id,
            'condicion': sitfiscal,
            'NumeroComprobante': jsonData.Numero,
            'Comprobante':comprobante,
            'CAE':jsonData.CAE,
            'FechaVencimientoCAE':jsonData.VencimientoCAE,
            'PtoVta':jsonData.PtoVta
            // 'Observaciones_ctasctes':observaciones_ctasctes

          };
          
          $.ajax({
            data: datofacturasistema,
            url: 'Procesos/php/facturar.php',
            type: 'post',
            success: function(respuesta) {

              var jsonData1 = JSON.parse(respuesta);
              
              if (jsonData1.success == 1) {
                
                //ACTUALIZO LA TABLA CTA CTE
                var tabla_ctacte = $('#basic').DataTable();
                tabla_ctacte.ajax.reload();
                
                document.getElementById('factura_primerpaso').style.display = "block";
                document.getElementById('factura_proforma').style.display = "none";
                toast("success", "Comprobante Generado con Exito !", "Se procesaron "+jsonData1.cuento+ " recorridos.");

                //REFRESCAMOS LA TABLA FACTURACION X RECORRIDO
                var tabla_recorridos = $('#recorridos_tabla').DataTable();
                tabla_recorridos.ajax.reload(null,false);
              
              } else if (jsonData1.success == 0) {

                Swal.fire({
                    icon: 'error',
                    title: 'El comprobante quedó a medio guardar',
                    text: jsonData1.msg || 'AFIP ya autorizó el comprobante (CAE ya emitido) pero no se pudo guardar en el sistema. Avisá a sistemas.',
                    confirmButtonText: 'Entendido'
                });

              } else if (jsonData1.success == 3) {

                toast("error", "Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.");
              }
            }
          });
        }

        }else{

            $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
    
            $('#warning_mt2_alert').html('Error !');
    
            $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);
            // toast("error", "Error !", jsonData.error);
        }

        } catch (err2) {
        
        $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
        
        $('#warning_mt2_alert').html('Error !');

        $("#warning_text").html("Error 2! Comprobante No Facturado Error: "+err2.message);

      
        }

      }
  
  
    });

});

