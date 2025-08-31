function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var id_comprobante=getParameterByName('id');

$("#compose-modal").on('show.bs.modal', function (e) {
    var id=$('#factura_codigo').html();
    // console.log('codigo cliente',id);
    
    
    $('#txtId').val(id_comprobante);
    // console.log('id_comprobante',id_comprobante);

    var dato = {"mail_clientes": 1,"id": id};
        $.ajax({
        data: dato,
        url: 'Procesos/php/invoice.php',
        type: 'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            // console.log('email',jsonData.data[0].email);

            $('#txtAsunto').val('NUEVO COMPROBANTE CADDY');

            var select = $('#txtEmail');
            var mountainTimeZone = $('#email_contactos');

            // Obtener opciones desde Ajax
            var opcionesAjax = jsonData.data;

            // Crear y agregar las opciones al grupo "Mountain Time Zone"
            for (var i = 0; i < opcionesAjax.length; i++) {
                var option = new Option(opcionesAjax[i].email, opcionesAjax[i].email, false, false);
                mountainTimeZone.append(option);
            }

            // Actualizar el select utilizando Select2
            select.select2();
        

        }
    });
});


$('#txtSelect').change(function() {

    // Inicializa un array vacío
    var mails = [];

    // Obtén los valores seleccionados del elemento select
    var miArray = $('#txtSelect').val();

    // Verifica si miArray es diferente de null antes de recorrerlo
    if (miArray) {
        // Recorre los valores y agrégalos al array mails
        for (var i = 0; i < miArray.length; i++) {
            // console.log(miArray[i]);
            mails.push(miArray[i]);
        }
    }

    // Asigna los valores al elemento de entrada con id txtEmail
    $('#txtEmail').val(mails.join(', ')); // Puedes ajustar el separador según tus necesidades

});

$('#button_sendmail').click(function() {

    var id_cliente=$('#factura_codigo').html();
    var email=$('#txtEmail').val();
    
    var name=$('#factura_razonsocial').html();

    $('#txtName').val(name);

    var total=$('#factura_total').html();
    $('#txtTotal').val(total);

    var periodo = 'Desde: '+$('#desde_f').html()+' Hasta: '+$('#hasta_f').html();    
    $('#txtPeriodo').val(periodo);
    
    var vencimiento=$('#venc_pago').html();
    $('#txtVencimiento').val(vencimiento);

    var txtNumfactura=$('#factura_titulo2').html()+' '+$('#NumeroComprobante').html();
    $('#txtNumfactura').val(txtNumfactura);
    
    //NOTIFICATIONS
    var dato = {"Notifications_mail": 1,"id_comprobante": id_comprobante,"id_cliente":id_cliente,"email":email,"name":name,
    "Comprobante":txtNumfactura,"vencimiento":vencimiento,"total":total,"periodo":periodo};
    
    $.ajax({        
    data: dato,
    url: 'Procesos/php/invoice.php',
    type: 'post',
    success: function(response) {
        var jsonData = JSON.parse(response);
        
        console.log('genero',jsonData);
        
        if(jsonData.success==1){
        $('#txtToken').val(jsonData.token);
        // console.log('genero_nuevo',$('#txtToken').val());

        var id_insertado=jsonData.id_insertado;
        // console.log('id_insertado',id_insertado);

        // Obtener los datos del formulario
        var formData = $('#miFormulario').serialize();

        // Realizar la solicitud AJAX
        $.ajax({
            type: 'POST',
            url: $('#miFormulario').attr('action'),
            data: formData,
            success: function(response) {
                // Manejar la respuesta del servidor si es necesario
                // console.log('Solicitud exitosa:', response);
                var jsonData = JSON.parse(response);

                if(jsonData.success==1){
                    $.NotificationApp.send("Correo enviado con éxito !", `El comprobante fue enviado a ${$('#txtEmail').val()}`, "bottom-right", "#FFFFFF", "success");
                }else{
                    $.NotificationApp.send("Correo no enviado !", `El comprobante no fue enviado Error: ${jsonData.error}`, "bottom-right", "#FFFFFF", "danger");
                }

                //UPDATE EN NOTIFICATIONS
                $.ajax({
                    data: {"update_notifications":1,"id_notifications":id_insertado,'id_ctasctes':id_comprobante},
                    url: 'Procesos/php/invoice.php',
                    type: 'post',
                    success: function(response) {

                    }
                });

            },
            error: function(error) {
                // Manejar errores en la solicitud AJAX
                console.error('Error en la solicitud AJAX:', error);

            }
        });
        }
}
});

});




$(document).ready(function(){

// inicializar el componente Select2 
    // $('#txtEmail').select2();
    

    var idCtaCte = getParameterByName('id');

                var dato = {
                "Datos": 1,
                "idCtaCte": idCtaCte
                    };
                $.ajax({
                data: dato,
                url: 'Procesos/php/invoice.php',
                type: 'post',
                //         beforeSend: function(){
                //         $("#buscando").html("Buscando...");
                //         },
                success: function(response) {
                    var jsonData = JSON.parse(response);
                    
                    $('#factura_razonsocial').html(jsonData.RazonSocial);

                    if (jsonData.success == "1") {


                       if(jsonData.idLogistica != 0){
                    
                        $('#row_tabla_recorridos').css('display','block');
                        $('#row_tabla_facturacion').css('display','none');    

                        //TABLA FACTURACION PROFORMA RECORRIDOS

                        var datatable_facturacion_recorridos = $('#tabla_facturacion_proforma_recorridos').DataTable({
                            paging: false,
                            searching: false,
                            footerCallback: function(row, data, start, end, display) {
                            total = this.api()
                                .column(4, {
                                page: 'current'
                                }) //numero de columna a sumar
                                //.column(1, {page: 'current'})//para sumar solo la pagina actual
                                .data()
                                .reduce(function(a, b) {
                                return Number(a) + Number(b);
                                }, 0);
                            var saldo = currencyFormat(total);
                            var sumadebe = currencyFormat(total / 1.21);
                            $(this.api().column(4).footer()).html(sumadebe);
                            //TOTALES
                            var neto = total / 1.21;
                            var iva = Number(total - neto);
                            $('#factura_neto').html(currencyFormat(neto));
                            $('#factura_iva').html(currencyFormat(iva));
                            $('#factura_total').html(saldo);
                            $('#factura_neto_f').val(neto.toFixed(2));
                            $('#factura_iva_f').val(iva.toFixed(2));
                            $('#factura_total_f').val(total.toFixed(2));
                            
                            //INGRESO DATOS EN CUADRO FINAL DE FACTURACION        
                            $('#neto_up_r').val(neto.toFixed(2));
                            $('#iva_up_r').val(iva.toFixed(2));
                            $('#total_up_r').val(total.toFixed(2));
        
                            },
                            ajax: {
                            url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/invoice.php",
                            data: {
                                'FacturacionProformaRecorridos': 1,
                                'id': idCtaCte
                            },
                            type: 'post'
                            },
                            columns: [{
                                data: "Fecha",
                                render: function(data, type, row) {
                                var Fecha = row.Fecha.split('-').reverse().join('.');
                                return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
                    
                                }
                            },
                            {
                                data: "TipoDeComprobante",
                                render: function(data, type, row) {
                                return row.TipoDeComprobante;
                                }
                            },
                            {
                                data: "NumeroVenta"
                            },
                            {
                                data: "Observaciones"
                            },
                            {
                                data: "Debe",
                                render: function(data, type, row) {
                                var DebeNeto = row.Debe / 1.21;
                                return currencyFormat(DebeNeto);
                                }
                            }
                            ],
                        });
    
                        }else{
                        
                            $('#row_tabla_recorridos').css('display','none');
                            $('#row_tabla_facturacion').css('display','block');

                            //TABLA FACTURACION PROFORMA REMITOS  

                            var datatable_facturacion = $('#tabla_facturacion_proforma').DataTable({
                        
                            paging: false,
                            searching: false,
                            footerCallback: function(row, data, start, end, display) {
                            total = this.api()
                                .column(columna, {
                                page: 'current'
                                }) //numero de columna a sumar
                                //.column(1, {page: 'current'})//para sumar solo la pagina actual
                                .data()
                                .reduce(function(a, b) {
                                return Number(a) + Number(b);
                                }, 0);
                            var saldo = currencyFormat(total);
                            var sumadebe = currencyFormat(total / 1.21);
                            $(this.api().column(columna).footer()).html(sumadebe);
                            
                            //TOTALES
                            // console.log('total',total);
                            // console.log('saldo',saldo);
                    
                            var neto = total / 1.21;
                            
                            var iva = Number(total - neto);
                            
                            $('#factura_neto').html(currencyFormat(neto));
                            $('#factura_iva').html(currencyFormat(iva));
                            $('#factura_total').html(saldo);
                    
                            $('#factura_neto_f').val(neto.toFixed(2));
                            $('#factura_iva_f').val(iva.toFixed(2));
                            $('#factura_total_f').val(total.toFixed(2));
        
                            //INGRESO DATOS EN CUADRO FINAL DE FACTURACION        
                            $('#neto_up').val(neto.toFixed(2));
                            $('#iva_up').val(iva.toFixed(2));
                            $('#total_up').val(total.toFixed(2));
                            
                            },
                            ajax: {
                            url: "https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/invoice.php",
                            data: {
                                'FacturacionProforma': 1,
                                "idCtaCte": idCtaCte          
                            },
                            type: 'post'
                            },
                            columns: [{
                                data: "Fecha",
                                render: function(data, type, row) {
                                var Fecha = row.Fecha.split('-').reverse().join('.');
                                return '<td><span style="display: none;">' + row.Fecha + '</span>' + Fecha + '</td>';
                    
                                }
                            },
                            {
                                data: "CodigoSeguimiento"
                            },
                            {
                                data: "TipoDeComprobante",
                                render: function(data, type, row) {
                                return row.TipoDeComprobante + ' ' + row.NumeroComprobante;
                                }
                            },
                            {
                                data: "ClienteDestino"
                            },
                            {
                            data: "CodigoProveedor",
                                render: function (data, type, row) { 
                                if(row.CodigoProveedor==''){
                                var dato='S/D';  
                                var color='muted';      
                                }else{
                                var dato=row.CodigoProveedor;    
                                }  

                                // if(getParameterByName('token')===null){
                                
                                    return '<td class="table-action">' +
                                    '<a style="cursor:pointer"  data-toggle="modal" data-target="#standard-modal-codcliente" data-id="' + row.CodigoSeguimiento + '"' +
                                    'data-title="' + dato + '" data-fieldname="' + data + '"><b class="text-'+color+'">' + dato + '</b></a></td>';
                                
                                // }else{
                                
                                    // return '<td>'+dato+'</td>';
                                // }
                              
                                }
                            },
                    
                            {
                                data: "Debe",
                                render: function(data, type, row) {
                                var DebeNeto = row.Debe / 1.21;
                                return currencyFormat(DebeNeto);
                                }
                            }
                            ],
                        });   

                }

                if(jsonData.TipoDeComprobante=='FACTURA PROFORMA'){

                //DESDE ACA FACTURA PROFORMA
                    $('#letra').html('P');   
                    

                }else if(jsonData.TipoDeComprobante=='FACTURAS A'){
                
                    $('#letra').html('A');
                    
                }else if(jsonData.TipoDeComprobante=='FACTURAS B'){
                    
                    $('#letra').html('B');
                
                } 

                    $('#title_page_invoice').html(jsonData.TipoDeComprobante+' '+jsonData.NumeroFactura);    
                    $('#factura_titulo').html(jsonData.TipoDeComprobante);
                    $('#NumeroComprobante').html(jsonData.NumeroFactura);
                    $('#FechaComprobante').html(jsonData.FechaComprobante);
                    var res = new Date(jsonData.FechaPura);


                    // Función para verificar si un día es hábil (lunes a viernes)
                    function esDiaHabil(dia) {
                    return dia.getDay() !== 0 && dia.getDay() !== 6; // No es domingo (0) ni sábado (6)
                    }

                    // Lista de días festivos ficticios (mes y día)
                    const diasFestivos = [
                    { mes: 12, dia: 25 }, // Navidad
                    { mes: 1, dia: 1 },    // Año Nuevo
                    { mes: 5, dia: 1 }    // Dia del Trabajador
                    // Agrega más días festivos según sea necesario
                    ];

                    // Asegurarse de que la fecha resultante sea 7 días corridos hábiles y sin días festivos
                    for (let i = 0; i < 7;) {
                    res.setDate(res.getDate() + 1);

                    // Si es un día hábil y no es un día festivo, incrementar el contador
                    if (esDiaHabil(res) && !diasFestivos.some(festivo => festivo.mes === (res.getMonth() + 1) && festivo.dia === res.getDate())) {
                        i++;
                    }
                    }

                    // console.log("Fecha después de 7 días corridos hábiles y sin días festivos:", res.toLocaleDateString());

                    // res.setDate(res.getDate() + 7);
                    var f=(res.getDate()+ "/" + (res.getMonth() +1) + "/" + res.getFullYear());
                    $('#venc_pago').html(f);                    
                    $('#factura_titulo2').html(jsonData.TipoDeComprobante);
                    $('#factura_codigo').html(jsonData.id);
                    $('#factura_razonsocial').html(jsonData.RazonSocial);

                    
                    if(jsonData.Direccion_f){
                        $('#factura_direccion').html(jsonData.Direccion_f);    
                    }else{
                        $('#factura_direccion').html(jsonData.direccion);
                    }
                    if(jsonData.Cuit_f){
                        $('#factura_cuit').html(jsonData.Cuit_f);
                    }else{
                        $('#factura_cuit').html(jsonData.Cuit);
                    }
                    $('#factura_localidad').html(jsonData.localidad);
                    $('#factura_provincia').html(jsonData.provincia);
                    $('#factura_celular').html(jsonData.celular);                    
                    $('#factura_condicion').html(jsonData.Condicion);
                    $('#factura_email').html(jsonData.Mail);
                    $('#factura_ingresosbrutos').html(jsonData.IngresosBrutos);
                    $('#CAE').html(jsonData.Cae);
                    $('#VencimientoCAE').html(jsonData.VencimientoCAE);
                    
                      if(jsonData.Cae==""){

                        $('#img_qr').css('display','none');
                        $('#afip_pie').css('display','none');
                        $('#afip_pie1').css('display','none');
                        
                        }else{

                        $('#img_qr').css('display','block');
                        $('#afip_pie').css('display','block');
                        $('#afip_pie1').css('display','block');    
                        
                      }
                    }
                }
                });

                
                //TABLA FACTURACION 
                var datatable_facturacion = $('#tabla_facturacion_proforma').DataTable();
                datatable_facturacion.destroy();

                if($('#letra').html()=='P'){

                        var columna=4;
    
                    }else{
                    
                        columna=5;
                    } 


            $("#standard-modal-codcliente").on('show.bs.modal', function (e) {
            var triggerLink = $(e.relatedTarget);
            var cs = triggerLink[0].dataset['id'];
            var dato= triggerLink[0].dataset['title'];
            $('#cs_codigocliente').val(cs);
            $('#myCenterModalLabel_codcliente').html('MODIFICAR CODIGO CLIENTE # '+cs);
            if(dato == 'S/D'){
            $('#codigocliente_t').prop('placeholder','S/D');
            }else{
            $('#codigocliente_t').val(dato);             
            }
            });

        $('#modificarcodigocliente_ok').click(function(){
            var codcliente=$('#codigocliente_t').val();
            var codigos=$('#cs_codigocliente').val();  
            if(codcliente!=''){
            $.ajax({
                data: {
                    'CodigoCliente': 1,
                    'CS': codigos,
                    'Dato':codcliente
                },
                url: 'Procesos/php/funciones.php',
                type: 'post',
                //         beforeSend: function(){
                //         $("#buscando").html("Buscando...");
                //         },
                success: function(response) {
                    var jsonData = JSON.parse(response);
                    if (jsonData.success == 1) {
                    $.NotificationApp.send("Registro Actualizado !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");
                    $('#codigocliente_t').val(''); 
                    var table = $('#tabla_facturacion_proforma').DataTable();
                    table.ajax.reload();
                    $('#standard-modal-codcliente').modal('hide');  
                    } else {
                    $.NotificationApp.send("Ocurrio un Error !", "No se realizaron cambios.", "bottom-right", "#FFFFFF", "danger");
                    }
                }
                });
            }
            });

            //FECHAS
            $.ajax({
                data: {
                'Fechas_invoice': 1,
                'id': idCtaCte
                },
                url: 'Procesos/php/funciones.php',
                type: 'post',
                success: function(response) {
                var jsonData = JSON.parse(response);
                $('#desde_f').html(jsonData.Desde);
                $('#hasta_f').html(jsonData.Hasta);
                }
            });  

});