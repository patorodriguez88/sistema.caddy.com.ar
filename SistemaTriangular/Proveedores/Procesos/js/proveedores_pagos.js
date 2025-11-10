$("#cargar_pagos_factura").click(function(){
    $("#modal_pagos_comprobantes").modal('hide');
    $("#modal_cargar_pagos").modal('show');
    $('#modal_cargar_pagos_header').html('Ingresar Pagos para comprobante');
    $('#cargar_pago_btn_ok').css('display','none');
    $('#cargar_pago_btn_ok_n').show();
});

$('#cargar_pago_btn_ok_n').click(function(){
    
        var val=$('#formadepago_t').val();    
        var fecha=$('#fecha_p').val();
        var asiento=$('#nasiento_p').val();

        switch (val) {
            case '3': //EFECTIVO 000111100

              var importe=$('#importepago_t').val();

              break
            case '20': // CHEQUES DE TERCEROS
                
            var importe=$('#importepago_t').val();            
            var ncheque3=$('#tercero_t').val();

              break
            
            case '4': //111200 TRANSFERENCIAS BANCARIAS
                
            var importe=$('#importepago_t').val();
            var num_transferencia=$('#numerotransferencia_t').val();
            var fecha_transferencia=$('#fechatransferencia_t').val();
            var banco_transferencia=$('#bancotransferencia_t').val();
            
              break
    
            case '5': // CHEQUES PROPIOS

            var importe=$('#importepago_t').val();
            var num_cheque_propio=$('#numerocheque_t').val();
            var fecha_cheque_propio=$('#fechacheque_t').val();
            var banco_cheque_propio=$('#banco_t').val();
                
            break

            case '42': // CHEQUES PROPIOS

            var importe=$('#importepago_t').val();
            var num_cheque_propio=$('#numerocheque_t').val();
            var fecha_cheque_propio=$('#fechacheque_t').val();
            var banco_cheque_propio=$('#banco_t').val();
                
            break

            default:
                
                var importe=$('#importepago_t').val();
    
              break
          }
          var RazonSocial=$('#razonsocial_p').val();
          var Cuit=$('#cuit_p').val();
          let idproveedor=$('#buscarproveedor').val();
                            
          var datos={'CargarAnticipo':1,'idproveedor':idproveedor,'RazonSocial':RazonSocial,'Cuit':Cuit,'formadepago':val,'fecha':fecha,
          'asiento':asiento,'importe':importe,'ncheque3':ncheque3,
          'num_transferencia':num_transferencia,'fecha_transferencia':fecha_transferencia,'banco_transferencia':banco_transferencia,
          'num_cheque_propio':num_cheque_propio,'fecha_cheque_propio':fecha_cheque_propio,'banco_cheques_propio':banco_cheque_propio};
          
          $.ajax({
            data: datos,
            url:'Procesos/php/pagos.php',
            type:'post',
            success: function(response)
             {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
                    
                    var table = $('#basic').DataTable();
                    table.ajax.reload();

                    $('#modal_cargar_pagos').modal('hide');
                    
                    $.NotificationApp.send("Exito !","Pago Cargado.","bottom-right","success","bg-success");  

                }else{
                    $.NotificationApp.send("Error !","El cheque ya está cargado.","bottom-right","danger","danger");  
                }
            }
        });
        
        
    
    });
    

//PAGOS
$('#modal_pagos_comprobantes').on('hidden.bs.modal',function(e){
    
    var datatable = $('#scroll-vertical-datatable').DataTable();
    datatable.destroy();

    var datatable = $('#tabla_anticipos').DataTable();
    datatable.destroy();

});

$('#modal_pagos_comprobantes').on('show.bs.modal',function(e){

        var datatable = $('#scroll-vertical-datatable').DataTable();
        datatable.destroy();

        var datatable = $('#tabla_anticipos').DataTable();
        datatable.destroy();


        $('#total_anticipos_control').val(0);
        $('#footer_saldo').val(0);
        $('#footer_saldo').val(0);

        // sumar_anticipos();

});

//ANTICIPOS
$('#modal_cargar_pagos').on('hidden.bs.modal',function(e){
    
    // $('#CargarPago').trigger("reset");
    $("#importepago_label_t").html(''); 
    $("#CargarPago")[0].reset();
// location.reload();
   
});

$('#modal_cargar_pagos').on('show.bs.modal',function(e){

//PRIMERO GENERO EL SELECT DE FORMA DE PAGO
    $.ajax({
        data: {'cuadro_forma_de_pago':1},
        url:'Procesos/php/proveedores.php',
        type:'post',
        beforeSend: function(){
        },
        success: function(response)
         {
            $('#cuadro_forma_de_pago').html(response).fadeIn();;

    $('#BancoOcultopropio').css('display','none');
    $('#banco_t').val('');
    $('#NumeroChequeOculto').css('display','none');
    $('#numerocheque_t').val('');
    $('#numerochequera_t').css('display','none');
    $('#FechaChequeOculto').css('display','none');
    $('#fechacheque_t').val('');
    $('#oculto').css('display','none');
    $('#numerotransferencia_t').val('');
    $('#oculto1').css('display','none');
    $('#fechatransferencia_t').val('');
    $('#BancoOculto').css('display','none');
    $('#bancotransferencia_t').val('');
    $('#footer_1').html(' <b> Efectivo </b>');

    let id=$('#buscarproveedor').val();
    
    $.ajax({
        data: {'Proveedores':1,'id':id},
        url:'Procesos/php/tablas.php',
        type:'post',
        success: function(response)
         {
            var jsonData = JSON.parse(response);
            if (jsonData.success == "1")
            {
                $('#razonsocial_p').val(jsonData.data[0].RazonSocial);
                $('#cuit_p').val(jsonData.data[0].Cuit);

                $.ajax({
                    data: {'Asiento':1},
                    url:'Procesos/php/tablas.php',
                    type:'post',
                    success: function(response)
                     {
                        var jsonData_asiento = JSON.parse(response);
                        if (jsonData_asiento.success == "1")
                        {
                            $('#nasiento_p').val(jsonData_asiento.Asiento);
                        }
                    }
                })
            }
        }
    });    
    }
  });
});

$("#importepago_t").keyup(function(e){

    let valor=Number(this.value);
    let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#importepago_label_t").html(val);

});

$("#numerocheque_t").keyup(function(e){

    $('#footer_2').html('Numero de Cheque: ');
    $('#footer_2_1').html(this.value);

});

$("#fechacheque_t").keyup(function(e){

    $('#footer_3').html('Fecha de Cheque: ');
    $('#footer_3_1').html(this.value);

});

function mostrary(x1){
    console.log(x1);

      switch (x1) {
        case '3':
          //EFECTIVO 000111100
          $('#footer_1').html('Efectivo: ');
          document.getElementById('total').style.display = 'block';
          document.getElementById('oculto').style.display = 'none';
          document.getElementById('oculto1').style.display = 'none';
          document.getElementById('BancoOculto').style.display = 'none';
          document.getElementById('NumeroChequeOculto').style.display = 'none';
          document.getElementById('FechaChequeOculto').style.display = 'none';
          document.getElementById('TercerosOculto').style.display = 'none';
          document.getElementById('BancoOcultopropio').style.display = 'none';
          $('#importepago_t').prop('disabled',false);
          $('#importepago_t').val('');
          $("#importepago_label_t").html('');
          $('#footer_2').html('');
          $('#footer_2_1').html('');
  
          break
        case '20':
            // CHEQUES DE TERCEROS    
            $('#footer_1').html(' Cheque de Terceros');            
            document.getElementById('TercerosOculto').style.display = 'block';    
            document.getElementById('oculto').style.display = 'none';
            document.getElementById('oculto1').style.display = 'none';
            document.getElementById('BancoOcultopropio').style.display = 'none';
            document.getElementById('NumeroChequeOculto').style.display = 'none';
            document.getElementById('FechaChequeOculto').style.display = 'none';
            document.getElementById('BancoOculto').style.display = 'none';
            document.getElementById('total').style.display = 'block';
            $('#importepago_t').prop('disabled',true);
            $("#importepago_label_t").html('');
            $('#footer_2').html('');
            $('#footer_2_1').html('');
  
    
          //Declaraciones ejecutadas cuando el resultado de expresión coincide con el valor2
          break
        
        case '4':
            //111200 TRANSFERENCIAS BANCARIAS
            $('#footer_1').html(' Transferencia Bancaria ');             
            document.getElementById('oculto').style.display = 'block';
            document.getElementById('oculto1').style.display = 'block';
            document.getElementById('BancoOcultopropio').style.display = 'block';
            document.getElementById('total').style.display = 'block';
            document.getElementById('NumeroChequeOculto').style.display = 'none';
            document.getElementById('FechaChequeOculto').style.display = 'none';
            document.getElementById('TercerosOculto').style.display = 'none';            
            
            $('#importepago_t').val('');
            $("#importepago_label_t").html('');
            $('#footer_2').html('');
            $('#footer_2_1').html('');
    
          //Declaraciones ejecutadas cuando el resultado de expresión coincide con valorN
          break

        case '5':
          
            // CHEQUES PROPIOS CUENTA 355-3   
            $('#footer_1').html(' Cheques propios');
            // document.getElementById('NumeroChequeraOculto').style.display = 'block';        
            document.getElementById('NumeroChequeOculto').style.display = 'block';
            document.getElementById('FechaChequeOculto').style.display = 'block';
            // document.getElementById('BancoOcultopropio').style.display = 'block';
            document.getElementById('total').style.display = 'block';    
            document.getElementById('oculto').style.display = 'none';
            document.getElementById('oculto1').style.display = 'none';
            document.getElementById('TercerosOculto').style.display = 'none';
            $('#importepago_t').val('');
            $("#importepago_label_t").html('');
            $('#footer_2').html('');
            $('#footer_2_1').html('');                    
            $('#importepago_t').prop('disabled',false);


          break

          case '42':
          
            // CHEQUES PROPIOS CUENTA NUEVA
            $('#footer_1').html(' Cheques Propios ');
            // document.getElementById('NumeroChequeraOculto').style.display = 'block';
            document.getElementById('NumeroChequeOculto').style.display = 'block';
            document.getElementById('FechaChequeOculto').style.display = 'block';            
            // document.getElementById('BancoOcultopropio').style.display = 'block';
            document.getElementById('total').style.display = 'block';    
            document.getElementById('oculto').style.display = 'none';
            document.getElementById('oculto1').style.display = 'none';
            document.getElementById('TercerosOculto').style.display = 'none';
            $('#importepago_t').val('');
            $("#importepago_label_t").html('');
            $('#footer_2').html('');
            $('#footer_2_1').html('');                    
            $('#importepago_t').prop('disabled',false);

          break

        default:
            $('#footer_1').html('Otros: ');
            document.getElementById('total').style.display = 'block';
            document.getElementById('oculto').style.display = 'none';
            document.getElementById('oculto1').style.display = 'none';
            document.getElementById('BancoOcultopropio').style.display = 'none';            
            document.getElementById('NumeroChequeOculto').style.display = 'none';
            document.getElementById('FechaChequeOculto').style.display = 'none';
            document.getElementById('TercerosOculto').style.display = 'none';
            
            $('#importepago_t').val('');
            $("#importepago_label_t").html('');
            $('#footer_2').html('');
            $('#footer_2_1').html('');
  
          //Declaraciones ejecutadas cuando ninguno de los valores coincide con el valor de la expresión
          break
      }

    }


    // $('#banco_t').change(function(){

    //   var banco=$('#banco_t').val();

    //   $.ajax({
    //     data: {'datos_chequera':1,'Banco':banco},
    //     url:'Procesos/php/proveedores.php',
    //     type:'post',
    //     beforeSend: function(){
    //     },
    //     success: function(response)
    //      {                              
    //         document.getElementById('NumeroChequeraOculto').style.display = 'block';
            
    //         $('#num_chequera').html(response).fadeIn();            
            
    //         $('#footer_1').html(' Cheques Propios ');  
            
    //         var chequera_preliminar=$('#numerochequera_t').val();
            
    //         ver_cheques(chequera_preliminar);

    //       }
    //     });

    // });


function ver_cheques(i){
  var banco=$('#banco_t').val();  

  $.ajax({
    data: {'datos_cheques':1,'Banco':banco,'Chequera':i},
    url:'Procesos/php/proveedores.php',
    type:'post',
    beforeSend: function(){
    },
    success: function(response)
     {                              
        // document.getElementById('NumeroChequeOculto').style.display = 'block';
        // $('#num_cheque').html(response).fadeIn();
        var jsonData = JSON.parse(response);
        $('#numerocheque_t').val(jsonData.dato);
        $('#footer_1').html(' Cheques Propios ');  
        document.getElementById('NumeroChequeOculto').style.display = 'block';
        document.getElementById('FechaChequeOculto').style.display = 'block';
                  
      }
    });

}


    $('#cargar_pago_btn_ok').click(function(){
    
        var val=$('#formadepago_t').val();    
        var fecha=$('#fecha_p').val();
        var asiento=$('#nasiento_p').val();

        switch (val) {
            case '3': //EFECTIVO 000111100

              var importe=$('#importepago_t').val();

              break
            case '20': // CHEQUES DE TERCEROS
                
            var importe=$('#importepago_t').val();            
            var ncheque3=$('#tercero_t').val();

              break
            
            case '4': //111200 TRANSFERENCIAS BANCARIAS
                
            var importe=$('#importepago_t').val();
            var num_transferencia=$('#numerotransferencia_t').val();
            var fecha_transferencia=$('#fechatransferencia_t').val();
            var banco_transferencia=$('#bancotransferencia_t').val();
            
              break
    
            case '5': // CHEQUES PROPIOS

            var importe=$('#importepago_t').val();
            var num_cheque_propio=$('#numerocheque_t').val();
            var fecha_cheque_propio=$('#fechacheque_t').val();
            var banco_cheque_propio=$('#banco_t').val();
                
            break

            case '42': // CHEQUES PROPIOS CUENTA NUEVA

            var importe=$('#importepago_t').val();
            var num_cheque_propio=$('#numerocheque_t').val();
            var fecha_cheque_propio=$('#fechacheque_t').val();
            var banco_cheque_propio=$('#banco_t').val();
                
            break

            default:
                
                var importe=$('#importepago_t').val();
    
              break
          }
                    
          var RazonSocial=$('#razonsocial_p').val();
          var Cuit=$('#cuit_p').val();
          let idproveedor=$('#buscarproveedor').val();
                            
          var datos={'CargarAnticipo':1,'idproveedor':idproveedor,'RazonSocial':RazonSocial,'Cuit':Cuit,'formadepago':val,'fecha':fecha,
          'asiento':asiento,'importe':importe,'ncheque3':ncheque3,
          'num_transferencia':num_transferencia,'fecha_transferencia':fecha_transferencia,'banco_transferencia':banco_transferencia,
          'num_cheque_propio':num_cheque_propio,'fecha_cheque_propio':fecha_cheque_propio,'banco_cheques_propio':banco_cheque_propio};
          
          $.ajax({
            data: datos,
            url:'Procesos/php/pagos.php',
            type:'post',
            success: function(response)
             {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
                    
                    var table = $('#basic').DataTable();
                    table.ajax.reload();
        
                    $('#modal_cargar_pagos').modal('hide');

                    $.NotificationApp.send("Exito !","Pago Cargado.","bottom-right","success","bg-success");  
                }else{
                    $.NotificationApp.send("Error !","El cheque ya está cargado.","bottom-right","danger","danger");  
                }
            }
        });
    
    });
    
    function buscardatos(v){

        $.ajax({
            data: {'BuscarCheque':1,'id':v},
            url:'Procesos/php/tablas.php',
            type:'post',
            success: function(response)
             {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {
                    let valor=Number(jsonData.Total);
                    let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                    
                    $('#importepago_t').val(valor);
                    $("#importepago_label_t").html(val);
                    $('#footer_2').html('Numero de Cheque: ');
                    $('#footer_2_1').html(jsonData.NumeroCheque);
                }
            }
        });
    }

//DESDE ACA PARA CARGAR PAGOS A FACTURAS DESDE ANTICIPOS

$('#cargar_pago_btn_continuar').click(function(){
    
    let valor_saldo=Number($('#footer_total_saldo').val());
    let valor_saldo_1=Number($('#footer_total').val());
    
    console.log('Valor Saldo',valor_saldo);
    console.log('valor Saldo_1',valor_saldo_1);

    if(valor_saldo>0){

        
        $.NotificationApp.send("Error !", 'No puede ser menor el pago que el valor de los comprobantes.', "bottom-right", "#FFFFFF", "danger");

    }else{

    
    var oTable = $('#tabla_anticipos').dataTable();
        
    var allPages = oTable.fnGetNodes();

    //Creamos un array que almacenará los valores de los input "checked"
    var checked_anticipos = [];
            
            //Recorremos todos los input checkbox que se encuentren "checked"
            $("input.form-check-input:checked",allPages).each(function() {
                
            //Mediante la función push agregamos al arreglo los values de los checkbox
            if ($(this).attr("data-id") != null) {
        
                checked_anticipos.push(($(this).attr("data-id")));
                                
            }
        });

    var oTable = $('#basic').dataTable();

    var allPages = oTable.fnGetNodes();

        //Creamos un array que almacenará los valores de los input "checked"
        var checked_facturas = [];
        //Recorremos todos los input checkbox que se encuentren "checked"
        $("input.form-check-input:checked",allPages).each(function() {
            
        //Mediante la función push agregamos al arreglo los values de los checkbox
        if ($(this).attr("value") != null) {
    
            checked_facturas.push(($(this).attr("value")));
            
        }
    });
    
    if(checked_anticipos.length==0){
    
        alert('Debe seleccionar al menos un anticipo');


    }else{

    var RazonSocial=$('#razonsocial').val();
    var Cuit=$('#cuit').val();
    let idproveedor=$('#buscarproveedor').val();
    var TotalAnticipos=$('#total_anticipos_control').val(); 
    var SaldoFinal=Number($('#footer_total_saldo').val()*-1);

     var datos={'PagoDesdeAnticipos':1,'RazonSocial':RazonSocial,'Cuit':Cuit,'idProveedor':idproveedor,'idFacturas':checked_facturas,'idAnticipos':checked_anticipos,'TotalAnticipos':TotalAnticipos,'SaldoFinal':SaldoFinal};          
      
      $.ajax({
        data: datos,
        url:'Procesos/php/pagos.php',
        type:'post',
        success: function(response)
         {
            var jsonData = JSON.parse(response);
            if (jsonData.success == "1")
            {
                var table = $('#basic').DataTable();
                table.ajax.reload();
                
                $('#modal_pagos_comprobantes').modal('hide');

                $.NotificationApp.send("Exito !", 'Pago Cargado.', "bottom-right", "#FFFFFF", "success");
                
            }else{
                $.NotificationApp.send("Error !","El cheque ya está cargado.","bottom-right","danger","danger");  
            }
        }
    });
    }
  }
});

function sumar_anticipos(){

    var oTable = $('#tabla_anticipos').dataTable();
        
    var allPages = oTable.fnGetNodes();

                //Creamos un array que almacenará los valores de los input "checked"
                var checked = [];
                var importes = Number(0);
        

                        //Recorremos todos los input checkbox que se encuentren "checked"
                        $("input.form-check-input:checked",allPages).each(function() {
                            
                        //Mediante la función push agregamos al arreglo los values de los checkbox
                        if ($(this).attr("value") != null) {
                    
                            checked.push(($(this).attr("value")));

                            importes=importes+Number($(this).attr("value"));

                            }else{

                            importes = Number(0);
                                
                            }                        
                            
                            let valor=Number(importes);
                            let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

                            $('#footer_total_anticipos').html(val);
                            $('#total_anticipos_control').val(valor);
                            var valor_comprobantes=$('#footer_total_input').val();    
                            
                            

                            var footer_saldo=Number(valor_comprobantes-valor);
                            console.log('Final',footer_saldo.toFixed(2));

                            if(checked.length>1 && footer_saldo<0){
                                
                                // $(this).prop('checked',false);
                                
                                let valor_ant=Number(valor_comprobantes);
                                let val_ant='$ '+valor_ant.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    
                                // $('#footer_total_anticipos').html(val_ant);                                
                                let valor_saldo=Number(footer_saldo.toFixed(2));
                                let val_saldo='$ '+valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                                $('#footer_saldo').html(val_saldo);
                                $('#footer_total_saldo').val(valor_saldo);

                            }else{                                
                                
                                let valor_saldo=Number(footer_saldo.toFixed(2));
                                let val_saldo='$ '+valor_saldo.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    
                                $('#footer_saldo').html(val_saldo);
                                $('#footer_total_saldo').val(valor_saldo);
    
                            }

                        
                        });
                        if(checked.length==0){
                            let valor=Number(0);
                            let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                            $('#footer_total_anticipos').html(val);
                            $('#total_anticipos_control').val(0);
                            let valor_comprobantes=Number($('#footer_total_input').val());
                            let val_comprobantes='$ '+valor_comprobantes.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                            $('#footer_saldo').html(val_comprobantes);
                            $('#footer_total_saldo').val(valor_comprobantes);

                        }
        }

    $('#btn_pago_facturas').click(function(e){

        var oTable = $('#basic').dataTable();
        
        var allPages = oTable.fnGetNodes();
    
                    //Creamos un array que almacenará los valores de los input "checked"
                    var checked = [];
                    
                    //Recorremos todos los input checkbox que se encuentren "checked"
                    $("input.form-check-input:checked",allPages).each(function() {
                    
                    //Mediante la función push agregamos al arreglo los values de los checkbox
                    if ($(this).attr("value") != null) {
                
                        checked.push(($(this).attr("value")));
                        
                        }
                    });
                  
                    // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
                            
                    if (checked != 0) {
                        $('#id_facturas').val(checked);
                        $('#modal_pagos_comprobantes').modal('show');
                        // $('#tabla_pago_facturas').css('display','block');

                        var datatable = $('#scroll-vertical-datatable').DataTable({
                            paging: false,
                            searching: false,
                            bInfo : false,
                            footerCallback: function ( row, data, start, end, display ) {
                        
                                total = this.api()
                                .column(3)//numero de columna a sumar
                                .data()
                                .reduce(function (a, b) {
                                return Number(a) + Number(b);
                                }, 0 );

                            let valor=Number(total);

                            let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                            $('#footer_total').html(val);

                            $('#footer_total_input').val(valor);    
                            $('#footer_total_saldo').val(valor)
                            
                              },
                             ajax: {
                              url:"https://www.caddy.com.ar/SistemaTriangular/Proveedores/Procesos/php/tablas.php",
                              data:{'Facturas_seleccionadas':1,'id':checked},
                              type:'post'
                              },              
                              columns: [
                              {data:"Fecha",              
                              render: function(data, type, row) {
                                var Fecha = row.Fecha.split('-').reverse().join('.');
                                return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
                              }
                            },
                              {data:"TipoDeComprobante",
                              render: function (data, type, row) {
                                    return `${row.TipoDeComprobante}`;
                                 }
                              },
                              {data:"Descripcion",
                               render: function (data, type, row) {
                                return "<td style='max-width: 15px;'>"+
                                   row.Descripcion+
                                   "</td>"
                                   }
                              },
                              {data: "Debe",
                               render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )                                
                              },
                              {data: "Haber",
                               render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )                                
                              },
                              {data: null,
                                render: function (data, type, row) {
                                    
                                    let valor=Number(row.Debe-row.Haber);
                                    let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                                    return val;

                                    }                            
                                  }                                
                              ]
                           });

                           //ANTICIPOS
                           let id_proveedor=$('#buscarproveedor').val();
                           var datatable = $('#tabla_anticipos').DataTable({
                            paging: false,
                            searching: false,
                            bInfo : false,
                            footerCallback: function ( row, data, start, end, display ) {
                        
                                total = this.api()
                                .column(3)//numero de columna a sumar
                                .data()
                                .reduce(function (a, b) {
                                return parseInt(a) + parseInt(b);
                                }, 0 );
                            
                            
                              },
                             ajax: {
                              url:"https://www.caddy.com.ar/SistemaTriangular/Proveedores/Procesos/php/tablas.php",
                              data:{'Anticipos_seleccionadas':1,'id':id_proveedor},
                              type:'post'
                              },              
                              columns: [
                              {data:"Fecha",              
                              render: function(data, type, row) {
                                var Fecha = row.Fecha.split('-').reverse().join('.');
                                return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
                              }
                            },
                              {data:"TipoDeComprobante",
                              render: function (data, type, row) {
                                    return `${row.TipoDeComprobante} - ${row.NumeroComprobante}`;
                                 }
                              },
                              {data:"Descripcion",
                               render: function (data, type, row) {
                                return "<td style='max-width: 15px;'>"+
                                   row.Descripcion+
                                   "</td>"
                                   }
                              },
                              {data: "Haber",
                                render: function (data, type, row) {
                                    
                                    let valor=Number(row.Haber);
                                    let val='$ '+valor.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                                    return val;

                                    }                            
                                  },
                                  {data:null,
                                  render: function (data,type,row){
                                    
                                    return '<td class="dtr-control dt-checkboxes-cell">'+
                                    '<div class="form-check"><input data-id="'+row.id+'" value="'+row.Haber+'" type="checkbox" class="form-check-input dt-checkboxes" onclick="sumar_anticipos()" >'+
                                    '<label class="form-check-label">&nbsp;</label></div></td>';
                                    
                                  }
                                }                                                                            
                              ]
                           });


                    
                        // $.ajax({
                        //     data:{'Exportar':1,'id_cobranza':checked},
                        //     url:'control/procesos/php/exportar.php',
                        //     type:'post',
                        //     success: function(response)
                        //      {
                        //         var jsonData = JSON.parse(response);
                        //         if(jsonData.success==1){
    
                        //             $('.modal-footer').css('display','none');
                                    
                        //             var datatable_seguimiento= $('#cobranzas_tabla').DataTable();
                        //             datatable_seguimiento.ajax.reload();                                  
                                    
                        //             $('#selectAll').prop('checked', false);  
    
                        //             $.NotificationApp.send("Exito !", 'Generaste el archivo '+jsonData.name+'.txt podés descargarlo desde la pestaña Exportados.', "bottom-right", "#FFFFFF", "success");
                                    
                        //         }else{
                        //             $.NotificationApp.send("Error !", 'No se pudo generar el archivo. Intente nuevamente.', "bottom-right", "#FFFFFF", "danger");
                        //         }
                        //      }  
                        // });  
    
    
                    }else{
                        $('#modal_pagos_comprobantes').modal('hide');
                        $.NotificationApp.send("Error !", 'Seleccione al menos una factura de Compras.', "bottom-right", "#FFFFFF", "bg-danger");
                    }          
    
    });
