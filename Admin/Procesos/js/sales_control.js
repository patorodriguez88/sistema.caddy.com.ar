//AL MARCAR EL ENVIO DE LA FACTURA
$('#factura_enviada').click(function(){
    
    $('#right-modal').modal('hide');
    $('#success-header-modal').modal('show');
    $('#success-header-modal-ok').css('display','block');
    $('#success-header-modal-ok-reclamo').css('display','none');
    $('#success-header-modal_header').removeClass('bg-danger').addClass('bg-success');
    $('#success-header-modalLabel').html('Confirmar envío de Factura');
    $('#success-info').val('');

});

//AL MARCAR EL RECLAMO DE PAGO
$('#reclamo_enviado').click(function(){

    $('#right-modal').modal('hide');
    $('#success-header-modal').modal('show');
    $('#success-header-modal-ok').css('display','none');
    $('#success-header-modal-ok-reclamo').css('display','block');
    $('#success-header-modal_header').removeClass('bg-success').addClass('bg-danger');
    $('#success-header-modalLabel').html('Confirmar Reclamo de Pago');
    $('#success-info').val('');

    var idFacturacion = $('#right-modal_id').val();

    $.ajax({
        data: {
          'Datos_Clientes': 1,
          'idFacturacion':idFacturacion                   
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
        var jsonData = JSON.parse(response);

        if (jsonData.Telefono !== '' && jsonData.Telefono !== null) {
   
            // Obtener la hora actual
            var horaActual = new Date().getHours();

            // Definir el mensaje predeterminado
            var saludo = "Buenas tardes "+jsonData.Nombre;
            
            // Comparar la hora actual con el horario deseado
            if (horaActual >= 8 && horaActual < 13) {
                saludo = "Buen día "+jsonData.Nombre;
            }

            // Crear un objeto Date basado en la fecha original
            var fecha = new Date(jsonData.Fecha);

            // Obtener los componentes de la fecha
            var dia = fecha.getDate();
            var mes = fecha.getMonth() + 1; // Los meses en JavaScript se cuentan desde 0, por lo que necesitamos sumar 1
            var anio = fecha.getFullYear();

            // Formatear la fecha como "dd.mm.yyyy"
            var fechaFormateada = dia + '.' + mes + '.' + anio;

            var phone=jsonData.Telefono;
            var comprobante=jsonData.NumeroComprobante;
            var fechacomprobante=fechaFormateada;            
            

            // Reemplazar la coma por el punto para tener un formato válido de número
            var numero = parseFloat(jsonData.Total.replace(',', '.'));

            // Formatear el número
            var numeroFormateado = numero.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });

            // Resultado
            var valorcomprobante = numeroFormateado;

            var text=saludo + ", este es un mensaje automático generado por el departamento de cobranzas. Te recordamos que se encuentra pendiente de cancelación el comprobante "+comprobante+" del "+fechacomprobante+" oportunamente enviado por un importe de "+valorcomprobante+". Muchas gracias. Saludos cordiales.";
                        

            // $('#btn_wp').html(`<a target='_blank' href='https://api.whatsapp.com/send?phone=${phone}&text=${text}' class='btn btn-success mt-2'>Enviar x Wp</a>`);
            $('#btn_wp').html(`<a target='_blank' href='https://web.whatsapp.com/send?phone=${phone}&text=${text}' class='btn btn-success mt-2'>Enviar x Wp</a>`);
            $('#success-info').val('Reclamo enviado por Whats App');
            $('#btn_wp').show();
        }else{
            $('#btn_wp').hide();
            $('#success-info').val('');
        }
        }
    });

});

$('#success-header-modal-ok-reclamo').click(function(){
    var idFacturacion = $('#right-modal_id').val();
    var noti_text=$('#success-info').val();
    
    if(noti_text==''){
        noti_text='Se envió el reclamo de pago al cliente.';
    }
    $.ajax({
        data: {
          'Notificaciones': 1,
          'Agregar':1,
          'MarcaReclamo':1,
          'idFacturacion':idFacturacion,
          'mensaje':noti_text
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
            //CIERRO EL MODAL
            $('#success-header-modal').modal('hide');
            
            //MUESTRO NOTIFICACIONES
            mostrarNotificaciones(idFacturacion);
            $('#notificaciones_text').val('');
            
            //NOTIFICO
            $.NotificationApp.send("Reclamo Generado ", "Se actualizo la marca en la tabla.", "bottom-right", "#FFFFFF", "success");   
            
            //ACTUALIZO TABLA
            var datatable1 = $('#librocontrolventas').DataTable();            
            datatable1.ajax.reload(null,false);

        }
    });
});

$('#success-header-modal-ok').click(function(){

    var idFacturacion = $('#right-modal_id').val();
    var noti_text=$('#success-info').val();
    
    if(noti_text==''){
        noti_text='Se envió el comprobante al cliente.';
    }

    $.ajax({
        data: {
          'Notificaciones': 1,
          'Agregar':1,
          'MarcaEnvioFactura':1,
          'idFacturacion':idFacturacion,
          'mensaje':noti_text
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
            //CIERRO EL MODAL
            $('#success-header-modal').modal('hide');
            
            //MUESTRO NOTIFICACIONES
            mostrarNotificaciones(idFacturacion);
            $('#notificaciones_text').val('');
            
            //NOTIFICO
            $.NotificationApp.send("Factura Enviada ", "Se agrego una marca a la tabla.", "bottom-right", "#FFFFFF", "success");   
            
            //ACTUALIZO TABLA
            var datatable1 = $('#librocontrolventas').DataTable();            
            datatable1.ajax.reload(null,false);

        }
    });

});

//AL CERRAR EL MODAL RIGHT MODAL
$('#right-modal').on('hide.bs.modal', function() {

    var container = $("#notificaciones-container");
    container.html("");

});

//AL ABRIR EL MODAL RIGHT MODAL
$('#right-modal').on('shown.bs.modal', function() {
    
    var idFacturacion = $('#right-modal_id').val();
    var container = $("#notificaciones-container");
    
    mostrarNotificaciones(idFacturacion); 

    var container = $("#notificaciones-container");
    
    container.scrollTop(container[0].scrollHeight);    

});

function mostrarNotificaciones(idFacturacion) {
    
    $.ajax({
        url: '../Admin/Procesos/php/tablas.php',
        method: 'POST',
        dataType: 'json',
        data: {
            'Notificaciones': 1,
            'Ver': 1,
            'idFacturacion': idFacturacion
        },
        success: function(data) {
            if (data && Array.isArray(data)) {
                $('#notificaciones-container').html("");
                // Utiliza los datos obtenidos en el bucle forEach

                data.forEach(function(notificacion) {
                    var htmlNotificacion = `
                        <div class="mt-0 text-left">
                            <hr class="">
                            <p class="mt-0 mb-0" style="font-size: 10px;"><strong><i class="uil uil-users-alt"> </i> ${notificacion.usuario} ${notificacion.fecha}:</strong></p>
                            <p style="font-size: 10px;">${notificacion.mensaje}</p>
                        </div>
                    `;
                    $('#notificaciones-container').append(htmlNotificacion);
                });
            }
        },
        error: function(error) {
            console.error('Error al obtener las notificaciones:', error);
        }
    });

}


$("#notificaciones_ok").click(function(){

var noti_text=$("#notificaciones_text").val();
var idFacturacion=$('#right-modal_id').val();

    $.ajax({
        data: {
        'Notificaciones': 1,
        'Agregar':1,
        'idFacturacion':idFacturacion,
        'mensaje':noti_text
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {

            mostrarNotificaciones(idFacturacion);
            $('#notificaciones_text').val('');
        }
    });
});



function modify_coments(id){

    $('#standard-modal').modal('show');
    $('#id_coments').val(id);
    
    $.ajax({
        data: {
          'Coments': 1,
          'id':id
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
            var jsonData = JSON.parse(response);

            $('#coments-textarea').val(jsonData.coments);
            
        }
    });
}

$('#coments_ok').click(function(){

    var id= $('#id_coments').val();
    var comentario=$('#coments-textarea').val();
    $.ajax({
        data: {
          'Coments_agreg': 1,
          'id':id,
          'comentario':comentario
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
            var jsonData = JSON.parse(response);
            if(jsonData.success==1){

             $.NotificationApp.send("Registro Actualizado !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");   
            
            }
            
            var datatable1 = $('#librocontrolventas').DataTable();
            
            datatable1.ajax.reload(null,false);
        }
        
    });
    
    $('#standard-modal').modal('hide');

});

$('#right-modal_obs_ok').click(function(){
    
    var id= $('#right-modal_id').val();
    var obs=$('#right-modal_obs').val();
    $.ajax({
        data: {
          'Obs_agreg': 1,
          'id':id,
          'obs':obs
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {

            var datatable1 = $('#librocontrolventas').DataTable();
            
            datatable1.ajax.reload(null,false);
        }
        
    });

});

function modify_status(){

    console.log('id',id);
    console.log('saldo',saldo);
    var id= $('#right-modal_id').val();
    var saldo=$('#right-modal_saldo').val();

    $('#modal_id').val(id);
    $('#right-modal').modal('hide');

    if(saldo>0){

    $('#warning-header-modal_header').removeClass('bg-success').addClass('bg-warning');
    
    var modal_text="Este registro aún contiene un saldo $ "+saldo;
    
    }else{
    
        var modal_text="Confirma la Solución de este registro de control?.";
    
        $('#warning-header-modal_header').removeClass('bg-warning').addClass('bg-success');

    }
    modal_text=$('#modal_text').html(modal_text);

    $('#warning-header-modal').modal('show');
 
}

$('#header-modal-ok').click(function(){
    
    var id=$('#modal_id').val();
    
    $.ajax({
        data: {
          'Modify_status': 1,
          'id': id
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {
            var jsonData = JSON.parse(response);
            var datatable1 = $('#librocontrolventas').DataTable();
            
            datatable1.ajax.reload(null,false);
            // Suponiendo que jsonData.sumStatusTrue es el valor numérico que deseas formatear
            var sumStatusTrue = parseFloat(jsonData.sumStatusTrue);

            // Suponiendo que jsonData.sumStatusTrue es el valor numérico que deseas formatear
            var sumStatusFalse = parseFloat(jsonData.sumStatusFalse);

            // Formatear como moneda con separador de miles y dos decimales
            var formattedSumfalse = sumStatusFalse.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });
            var formattedSumtrue = sumStatusTrue.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });

            $('#solucionados_total').html(jsonData.countStatusTrue);
            $('#pendientes_total').html(jsonData.countStatusFalse);
            $('#solucionados_total_importe').html('Total Solucionados: '+formattedSumtrue);
            $('#pendientes_total_importe').html('Total Pendientes: '+formattedSumfalse);
        
            } 
        });

        $('#warning-header-modal').modal('hide');
});

function normalizarFormato(numero) {
    // Eliminar guiones existentes y espacios
    numero = numero.replace(/[-\s]/g, '');
  
    // Asegurarse de que el número tiene al menos 9 dígitos
    while (numero.length < 9) {
      numero = '0' + numero;
    }
  
    // Agregar guiones en las posiciones adecuadas
    return numero.replace(/(\d{2})(\d{8})(\d{1})/, '$1-$2-$3');
  }
  
function currencyFormat(num) {
    return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  }
  

$(document).ready(function(){


ver_tabla(0);


function ver_tabla(filtro){    
    var countStatusTrue = 0;
    var countStatusFalse = 0;
    
var datatable1 = $('#librocontrolventas').DataTable({
    dom: 'Bfrtip',
    pageLength: '50',
    buttons: [
        'pageLength','copy', 'csv', 'excel',
      {
        extend: 'pdf',
        text: 'PDF',
        orientation: 'landscape',
        title: 'Control Ventas Triangular S.A. ',
        filename: 'ControlVentas', 
        header: true,
        pageSize: 'A4',
    }
    ],
    lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
      paging: true,
      searching: true,

    footerCallback: function(row, data, start, end, display) {

        total = this.api()
        //   .column(6) //numero de columna a sumar
          .column(4, {page: 'current'})//para sumar solo la pagina actual
          .data()
          .reduce(function(a, b) {
            return Number(a) + Number(b);
            //                 return parseInt(a) + parseInt(b);
          }, 0);
        var saldo = currencyFormat(total);
        
        $(this.api().column(4).footer()).html(saldo);
    
      },
    
    ajax: {
      url:"../Admin/Procesos/php/tablas.php",
      data:{'Sales_control':1,'Filtro':filtro},
      type:'post'
      },
      columns: [
        {data:"Fecha",
        render: function(data,type,row){
            var Fecha=row.Fecha.split('-').reverse().join('.');
            return `<td>${row.Fecha}</td>`;
        }
        },
        {data: null,
         render: function (data, type, row) {
            return '<td>['+row.idCliente+'] <b>'+row.nombrecliente+'</b><br>Facturado a: '+row.RazonSocial+'<br>'+normalizarFormato(row.Cuit)+'</td>';
        }
        },                   
        {data:"TipoDeComprobante",
        render: function (data, type, row) {

            if(row.Observaciones!=''){
                var obs='<i class="mdi mdi-18 mdi-alert-octagon text-danger"></i>';
            }else{
                obs="";
            }
             if(row.Notificaciones!='0000-00-00'){
                var not='<i class="mdi mdi-18px mdi-email text-success">';
            }else{
                not="";
            }

            if(row.Reclamos!='0'){
                var reclamo=`<i class="mdi mdi-18px mdi-numeric-${row.Reclamos}-circle text-danger">`;
            }else{
                reclamo="";
            }

             return '<td>'+row.TipoDeComprobante+'<br>'+row.NumeroComprobante+' '+ obs +' ' + not+ ' '+ reclamo +'</td>';

        }
         },                   
        {data:"Total",
        render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        {data:"Ingresos",
        render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        {data:"Saldo",
        render:$.fn.dataTable.render.number( '.', ',', 2, '$ ' )},
        {data:"Comentario",
        render: function (data, type, row) {
         return '<td><i id="'+row.id+'" onclick="modify_coments(this.id)" class="mdi mdi-pencil-outline"></i>'+row.Comentario+'</td>';
         }
         },
         {data:"Status",
         render: function (data, type, row) {

             if(row.Status==0){
             
                var status='Pendiente';
             
                var status_text='danger';
             
                }else{
             
                    status='Solucionado';
             
                    status_text='success';
             }

             if(row.Vencimiento=='0000-00-00'){
                var res = new Date(row.Fecha);
    
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
    
                var vencimiento=(res.getDate()+ "/" + (res.getMonth() +1) + "/" + res.getFullYear());
                var fechavencimiento=(res.getFullYear()+"-"+(res.getMonth() +1)+"-"+res.getDate());

                // SI LA FECHA DE VENCIMIENTO ES 00-00-000 Actualizamos la fecha con la generada
                $.ajax({
                    data: {
                    'Actualizar': 1,
                    'id':row.id,
                    'FechaVencimiento':fechavencimiento
                    },
                    type: "POST",
                    url: "../Admin/Procesos/php/tablas.php",
                    success: function(response) {
    
                    }
                })




            }else{
                var vencimiento =row.Vencimiento.split('-').reverse().join('/');
            }
            
                // Obtén la fecha actual
                var fechaActual = new Date();

                // Obtén la fecha del elemento HTML (en este caso, el párrafo con id "miFecha")
                
                var partesFecha = vencimiento.split('/');
                var fecha = new Date(partesFecha[2], partesFecha[1] - 1, partesFecha[0]);

                // Compara las fechas
                if (fecha < fechaActual) {
                    var color_fecha='danger';
                }else{
                    color_fecha='success';
                }

                 return `<a class="text-${color_fecha}">${vencimiento}<a><br><span id="${row.id}" class="badge badge-${status_text}">${status}</span>`;
            }
          },
        //  {data:null,
        //  render: function (data, type, row) {
        //   return '<td><i class="mdi mdi-pencil-outline"></i>'+
        //          '<i class="mdi mdi-pencil-outline"></i>'+
        //          '<i class="mdi mdi-pencil-outline"></i></td>';
        //   }
        //   },

      ]    
}); 

    var table = $('#librocontrolventas').DataTable();

   $('#librocontrolventas tbody').on('click', 'tr', function() {
    // Obtiene los datos del renglón clickeado
    var rowData = table.row(this).data();
    
    // Ejecuta tu función con los datos del renglón
    if (rowData) {
        // Puedes acceder a los datos específicos, por ejemplo, rowData[1] para la segunda columna
        console.log("Haz clic en el renglón:", rowData['id']);
        // Llama a tu función y pasa los datos según sea necesario
        tuFuncion(rowData);
    }
});

$.ajax({
    data: {
      'Totales': 1
    },
    type: "POST",
    url: "../Admin/Procesos/php/tablas.php",
    success: function(response) {
    var jsonData = JSON.parse(response);
    // Suponiendo que jsonData.sumStatusTrue es el valor numérico que deseas formatear
    var sumStatusTrue = parseFloat(jsonData.sumStatusTrue);

    // Suponiendo que jsonData.sumStatusTrue es el valor numérico que deseas formatear
    var sumStatusFalse = parseFloat(jsonData.sumStatusFalse);

    // Formatear como moneda con separador de miles y dos decimales
    var formattedSumfalse = sumStatusFalse.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });
    var formattedSumtrue = sumStatusTrue.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });

    $('#solucionados_total').html(jsonData.countStatusTrue);
    $('#pendientes_total').html(jsonData.countStatusFalse);
    $('#solucionados_total_importe').html('Total Solucionados: '+formattedSumtrue);
    $('#pendientes_total_importe').html('Total Pendientes: '+formattedSumfalse);
    
    }
    });

}

// Función de ejemplo que puedes personalizar según tus necesidades
function tuFuncion(data) {

    var idCliente=data['idCliente'];
    
    $.ajax({
        data: {
          'Observaciones_facturacion_clientes': 1,
          'idCliente':idCliente
        },
        type: "POST",
        url: "../Admin/Procesos/php/tablas.php",
        success: function(response) {

            var jsonData = JSON.parse(response);
            
            if(jsonData.Observaciones_f){

                $('#alert-coment').css('display','block');
                $('#right-modal_coment').html(jsonData.Observaciones_f);
                $('#idCliente-info').val(idCliente);

            }else{

                $('#alert-coment').css('display','none');

            }
        }
        
    });



    $('#right-modal').modal('show');
    $('#right-modal_id').val(data['id']);
    $('#right-modal_saldo').val(data['Saldo']);

    
    // if(data['Comentario']!=''){

    //     $('#alert-coment').css('display','block');
    
    // }else{
    
    //     $('#alert-coment').css('display','none');
    
    // }
    
    // $('#right-modal_coment').html(data['Comentario']);
    $('#right-modal_titulo').html('Datos del Registro #'+data['id']);
    $('#right-modal_obs').val(data['Observaciones']);
    $('#fecha_emision').html('Fecha Emision: '+data['Fecha']);
    $('#fecha_vencimiento').html(`Fecha Vencimiento: ${data['Vencimiento']}`);
    // Obtener las fechas desde el HTML
    var fechaEmisionString = data['Fecha'];
    var fechaVencimientoString = data['Vencimiento'];

    // Convertir las cadenas de fecha a objetos Date
    var fechaEmision = new Date(fechaEmisionString);
    var fechaVencimiento = new Date(fechaVencimientoString);

    // Calcular la diferencia en milisegundos
    var diferenciaMilisegundos = fechaVencimiento - fechaEmision;

    // Calcular la diferencia en días
    var diferenciaDias = Math.ceil(diferenciaMilisegundos / (1000 * 60 * 60 * 24));

    // Mostrar la diferencia en días
    $('#fecha_dias').html('Días: ' + diferenciaDias);

}  

//MUESTRO TOTALES
$("#button_solucionados").click(function(){

    $('#librocontrolventas').DataTable().destroy();
    ver_tabla(1);

});

$("#button_pendientes").click(function(){

    $('#librocontrolventas').DataTable().destroy();
    ver_tabla(0);


});



});