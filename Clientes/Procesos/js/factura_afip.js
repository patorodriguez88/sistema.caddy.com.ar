
//GENERAR COMPROBANTE AFIP

$("#confirmar_generar_comprobante_AFIP_boton").click(function() {

    var fecha = document.getElementById('fecha_up').value;
    var id = document.getElementById('buscarcliente').value;
    var neto = document.getElementById('neto_up').value;
    var iva = document.getElementById('iva_up').value;
    var total = document.getElementById('total_up').value;
    var sitfiscal = document.getElementById('condicion').value;

    //FACTURACION
    var razonsocial_f = document.getElementById('razonsocial_facturacion').value;
    var direccion_f = document.getElementById('direccion_facturacion').value;
    var tipodocumento_f = document.getElementById('tipodocumento_facturacion').value;
    var documento_f = document.getElementById('cuit_facturacion').value;
    var fecha_desde=fecha;
    var fecha_hasta=fecha;  
    
    var cbteasoc_tipo=document.getElementById('cbteasoc_tipo').value;

    if(cbteasoc_tipo=='FACTURAS A'){
        
        var cbteasoc_tipo_n='1';

    }else if(cbteasoc_tipo=='FACTURAS B'){
        
        cbteasoc_tipo_n='6';
    }

    var cbteasoc_ptovta=document.getElementById('cbteasoc_ptovta').value;
    var cbteasoc_nro=document.getElementById('cbteasoc_nro').value;
    
    var observaciones_ctasctes=document.getElementById('observaciones_ctasctes').value;
    
    //CUADRO FACTURACION
    var comprobante = document.getElementById('comprobante_up').value;
    
    if (document.getElementById('nueva_condicion_facturacion').value != '') {
        var condiva_f = document.getElementById('nueva_condicion_facturacion').value;
    } else {
        var condiva_f = document.getElementById('condicion_facturacion').value;
    }
    
    //CONDICION IVA
    if(condiva_f==1){
    
        var nuevocomprobante=document.getElementById('comprobante_nc_nd_selectA').value;
    
    }else if(condiva_f==6){
    
        var nuevocomprobante=document.getElementById('comprobante_nc_nd_selectB').value;
    }
    
    var comprobante_tipo=$('#comprobante_tipo').val();

    console.log('Comprobante Log',comprobante_tipo);

    console.log('Fecha:',fecha);
    console.log('id',id);
    console.log('neto',neto);
    console.log('iva',iva);
    console.log('total',total);
    console.log('sitfiscal',sitfiscal);
    
    console.log('razonsocial_f',razonsocial_f);
    console.log('direccion_f',direccion_f);
    console.log('tipodocumento',tipodocumento_f);
    console.log('documento_f',documento_f);
    
    console.log('comprobante',comprobante);
    console.log('condiva',condiva_f);
    console.log('Comprobante asociado tipo',cbteasoc_tipo);
    console.log('Comprobante asociado Tipo Numero',cbteasoc_tipo_n);
    console.log('Comprobante asociado punto de venta',cbteasoc_ptovta);
    console.log('Comprobante asociado numero',cbteasoc_nro);
    console.log('Nuevo Comprobante',nuevocomprobante);


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
      'Comprobante_tipo':comprobante_tipo,
      'cbteasoc_tipo_n':cbteasoc_tipo_n,
      'CbtesAsoc_PtoVta':cbteasoc_ptovta,
      'cbteasoc_nro':cbteasoc_nro,
      'Observaciones_ctasctes':observaciones_ctasctes,
      
    };
  
    $.ajax({
      data: dato, 
      url:'../afip.php/procesos/CreateVoucherncnb_false.php', //HABILITAR PARA FACTURA AFIP _false PARA HOMOLOGACION       
      type: 'post',
    
    beforeSend: function() {

        $('#warning-alert-modal').modal('show');

        $("#warning_text").html("Enviando los datos a AFIP");

      },
  
      success: function(respuesta) {

        try {
        
        var jsonData = $.parseJSON(respuesta);

        if(jsonData.data==1){
        
            if (jsonData.CAE != '') {                                    
            
            $('#warning_icono').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-checkmark h1 text-success');
        
            $('#warning_mt2').html('Exito !');

            $("#warning_text").html("Exito ! Comprobante N "+jsonData.Numero);
            

            document.getElementById('datos_cae').style.display = "block";
            
            $('#CAE').html(jsonData.CAE);//HABILITAR PARA FACTURA AFIP 
            
            var FechaVenc=jsonData.VencimientoCAE.split('-').reverse().join('/');//HABILITAR PARA FACTURA AFIP 
            
            $('#VencimientoCAE').html(FechaVenc);//HABILITAR PARA FACTURA AFIP 
            
            if(condiva_f==1){
            
            $('#factura_titulo2').html('FACTURA A');//HABILITAR PARA FACTURA AFIP  
            $('#factura_titulo').html('FACTURA A'); //HABILITAR PARA FACTURA AFIP 
            
            }else{

            $('factura_titulo2').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            $('factura_titulo').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            
            }
            
            $('#NumeroComprobante').html(jsonData.Numero);  //HABILITAR PARA FACTURA AFIP 
           
    //         //FACTURO EN EL SISTEMA

    //        var datofacturasistema = {
    //         'Facturar': 3, //0 PRUEBA
    //         'fecha': fecha,
    //         'razonsocial_f': razonsocial_f,
    //         'direccion_f': direccion_f,
    //         'condiva_f': condiva_f,
    //         'tipodocumento_f': tipodocumento_f,
    //         'documento_f': documento_f,
    //         'Documento': 99,
    //         'ImpTotal': total,
    //         'ImpTotalConc': 0,
    //         'ImpNeto': neto,
    //         'ImpIva': iva,
    //         'ImpTrib': 0,                
    //         // 'Remitos': checked,
    //         'id': id,
    //         'condicion': sitfiscal,
    //         'NumeroComprobante': jsonData.Numero,
    //         'Comprobante':comprobante,
    //         'CAE':jsonData.CAE,
    //         'FechaVencimientoCAE':jsonData.VencimientoCAE,
    //         'PtoVta':jsonData.PtoVta,
    //         'Observaciones_ctasctes':observaciones_ctasctes            
        };
            
        // $.ajax({
        //     data: datofacturasistema,
        //     url: 'Procesos/php/facturar.php',
        //     type: 'post',
        //     success: function(respuesta) {
        //     var jsonData1 = JSON.parse(respuesta);
        //     if (jsonData1.success == 1) {
        //         $.NotificationApp.send("Comprobante Generado con Exito !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");
        //         //DESTRUIMOS LA TABLA FACTURACION
        //         var table = $('#tabla_facturacion_proforma').DataTable();
        //         table.destroy();
                
        //         var tabla_facturacion = $('#facturacion_tabla').DataTable();
        //         tabla_facturacion.ajax.reload();
                
        //         //ACTUALIZO LA TABLA CTA CTE
        //         var tabla_ctacte = $('#basic').DataTable();
        //         tabla_ctacte.ajax.reload();

        //         document.getElementById('factura_primerpaso').style.display = "block";
        //         document.getElementById('factura_proforma').style.display = "none";
                
        //         } else if (jsonData1.success == 0) {

        //         $.NotificationApp.send("Error al Intentar Generar el Comprobante !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
            
        //         } else if (jsonData1.success == 3) {
            
        //         $.NotificationApp.send("Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
            
            //   }
            // }
        // });
        // }

    }else{
    
    $('#warning_icono').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');

    $('#warning_mt2').html('Error !');

    $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);

      
    }

} catch (err) {
    
    $('#warning_icono').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
    
    $('#warning_mt2').html('Error !');

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

    //Creamos un array que almacenará los valores de los input "checked"
    var checked = [];
    //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
    $("input.custom-control-input:checked").each(function() {

      if ($(this).attr("value") != null) {
        //Mediante la función push agregamos al arreglo los values de los checkbox
        checked.push(($(this).attr("value")));
      }
      
    });

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

            $('factura_titulo2').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            $('factura_titulo').html('FACTURA B');     //HABILITAR PARA FACTURA AFIP 
            
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
                    $.NotificationApp.send("Comprobante Generado con Exito !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");
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
                    $.NotificationApp.send("Error al Intentar Generar el Comprobante !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
                } else if (jsonData1.success == 3) {
                    $.NotificationApp.send("Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
                }
                }
            });
            }

        }else{
        
        $('#warning_icono').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
    
        $('#warning_mt2').html('Error !');

        $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);
    
          
        }

    } catch (err) {
        
        $('#warning_icono').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
        
        $('#warning_mt2').html('Error !');

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

    //Creamos un array que almacenará los valores de los input "checked"
    var checked = [];
    //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
    $("input.custom-control-input:checked").each(function() {
      if ($(this).attr("value") != null) {
        //Mediante la función push agregamos al arreglo los values de los checkbox
        checked.push(($(this).attr("value")));
      }
    });

    var ncomp = $('#ncomprobante_up_r').val();

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
            'NumeroComprobante': ncomp,
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
                $.NotificationApp.send("Comprobante Generado con Exito !", "Se procesaron "+jsonData1.cuento+ " recorridos.", "bottom-right", "#FFFFFF", "success");

                //REFRESCAMOS LA TABLA FACTURACION X RECORRIDO
                var tabla_recorridos = $('#recorridos_tabla').DataTable();
                tabla_recorridos.ajax.reload(null,false);
              
              } else if (jsonData1.success == 0) {

                $.NotificationApp.send("Error al Intentar Generar el Comprobante !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
              
              } else if (jsonData1.success == 3) {
            
                $.NotificationApp.send("Error en el Codigo de Afip del Cliente !", "No se han realizado cambios.", "bottom-right", "#FFFFFF", "danger");
              }
            }
          });
        }

        }else{

            $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
    
            $('#warning_mt2_alert').html('Error !');
    
            $("#warning_text").html("Error! Comprobante No Facturado Error: "+jsonData.error);
            // $.NotificationApp.send("Error !", jsonData.error, "bottom-right", "#FFFFFF", "danger");
        }

        } catch (err2) {
        
        $('#warning_icono_alert').removeClass('dripicons-warning h1 text-warning').addClass('dripicons-wrong h1 text-danger');
        
        $('#warning_mt2_alert').html('Error !');

        $("#warning_text").html("Error 2! Comprobante No Facturado Error: "+err2.message);

      
        }

      }
  
  
    });

});

