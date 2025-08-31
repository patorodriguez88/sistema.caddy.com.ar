function currencyFormat(num) {
  return '$ ' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
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
    console.log('codigo cliente',id);
    
    
    $('#txtId').val(id_comprobante);
    console.log('id_comprobante',id_comprobante);

    var dato = {"mail_clientes": 1,"id": id};
        $.ajax({
        data: dato,
        url: '../Procesos/php/recibo.php',
        type: 'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            console.log('email',jsonData.data[0].email);

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
        console.log(miArray[i]);
        mails.push(miArray[i]);
    }
}


// Asigna los valores al elemento de entrada con id txtEmail
$('#txtEmail').val(mails.join(', ')); // Puedes ajustar el separador según tus necesidades
console.log('ver_mail',mails.join(', '));
});

$('#button_sendmail').click(function() {

    var id_cliente=$('#factura_codigo').html();
    var email=$('#txtEmail').val();
    var name=$('#factura_razonsocial').html();
    $('#txtName').val(name);
    
    //NOTIFICATIONS
    var dato = {"Notifications": 1,"id_comprobante": id_comprobante,"id_cliente":id_cliente,"email":email,"name":name};
    
    $.ajax({
    data: dato,
    url: '../Procesos/php/recibo.php',
    type: 'post',
    success: function(response) {
        var jsonData = JSON.parse(response);
        
        console.log('genero',jsonData.token);
        
        if(jsonData.success==1){
        $('#txtToken').val(jsonData.token);
        console.log('genero_nuevo',$('#txtToken').val());

        var id_insertado=jsonData.id_insertado;
        console.log('id_insertado',id_insertado);

        // Obtener los datos del formulario
        var formData = $('#miFormulario').serialize();

        // Realizar la solicitud AJAX
        $.ajax({
            type: 'POST',
            url: $('#miFormulario').attr('action'),
            data: formData,
            success: function(response) {
                // Manejar la respuesta del servidor si es necesario
                console.log('Solicitud exitosa:', response);
                var jsonData = JSON.parse(response);

                if(jsonData.success==1){
                    $.NotificationApp.send("Correo enviado con éxito !", `El comprobante fue enviado a ${$('#txtEmail').val()}`, "bottom-right", "#FFFFFF", "success");
                }else{
                    $.NotificationApp.send("Correo no enviado !", `El comprobante no fue enviado Error: ${jsonData.error}`, "bottom-right", "#FFFFFF", "danger");
                }

                //UPDATE EN NOTIFICATIONS
                $.ajax({
                    data: {"update_notifications":1,"id_notifications":id_insertado,'id_ctasctes':id_comprobante},
                    url: '../Procesos/php/recibo.php',
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

$('#row_tabla_recorridos').css('display','none');

    var idCtaCte = getParameterByName('id');

                var dato = {
                "Datos": 1,
                "idCtaCte": idCtaCte
                    };
                $.ajax({
                data: dato,
                url: '../Procesos/php/recibo.php',
                type: 'post',
                success: function(response) {
                    var jsonData = JSON.parse(response);
                    if (jsonData.success == "1") {

                        if(jsonData.FormaDePagoTeso==='MERCADO PAGO'){
                            $('#fp_name_0').html('Fecha Operación: ');
                            $('#fp_text_0').html(jsonData.FechaTrans);    
                            $('#fp_name_1').html('Numero de Operación: ');
                            $('#fp_text_1').html(jsonData.NumeroTrans);    
                        }
                        if(jsonData.FormaDePagoTeso==='Cheques de Terceros'){
                            $('#fp_name_0').html('Banco: ');
                            $('#fp_text_0').html(jsonData.Banco);    
                            $('#fp_name_1').html('Numero de Cheque: ');
                            $('#fp_text_1').html(jsonData.NumeroCheque);    
                        }
                        if(jsonData.FormaDePagoTeso==='Transferencia Bancaria'){
                            $('#fp_name_0').html('Fecha Transferencia: ');
                            $('#fp_text_0').html(jsonData.FechaTrans);    
                            $('#fp_name_1').html('Número de Transferencia: ');
                            $('#fp_text_1').html(jsonData.NumeroTrans);    
                        }
                        
                    $('#title_page_invoice').html(jsonData.TipoDeComprobante+' '+jsonData.NumeroFactura);    
                    $('#factura_titulo').html(jsonData.TipoDeComprobante+ ' N '+jsonData.NumeroFactura);
                    $('#NumeroComprobante').html(jsonData.NumeroFactura);
                    $('#FechaComprobante').html(jsonData.FechaComprobante);
                    $('#factura_titulo2').html(jsonData.TipoDeComprobante);
                    $('#factura_codigo').html(jsonData.id);
                    $('#factura_razonsocial').html(jsonData.RazonSocial);
                    $('#factura_direccion').html(jsonData.direccion);
                    $('#factura_localidad').html(jsonData.localidad);
                    $('#factura_provincia').html(jsonData.provincia);
                    $('#factura_celular').html(jsonData.celular);
                    $('#factura_cuit').html(jsonData.Cuit);
                    $('#factura_condicion').html(jsonData.Condicion);
                    $('#factura_email').html(jsonData.Mail);
                    $('#factura_ingresosbrutos').html(jsonData.IngresosBrutos);
                    $('#factura_formadepago').html(jsonData.FormaDePagoTeso);
                    $('#factura_total').html(currencyFormat(Number(jsonData.TotalTeso)));
                    $('#usuario').html(jsonData.Usuario);
                    $('#id').html(jsonData.idCtasCtes);
                    }
                }
                });

            });
