function addZero(i) {
    if (i < 10) {i = "0" + i}
    return i;
  }
$(document).ready(function(){
    
    var hoy = new Date();    
    var fecha = `${hoy.getDate()}-${hoy.getMonth() + 1}-${hoy.getFullYear()}`;
    var hora = addZero(hoy.getHours()) + ':' + addZero(hoy.getMinutes()) + ':' + addZero(hoy.getSeconds());
    
    $('#myCenterModalLabel2').html('Server Webhook Iniciado '+fecha+' H: '+hora);

});

window.setInterval(function () {
    var datatable = $('#webhook_tabla').DataTable();
    datatable.destroy();
  
    trackWebhooks();
    updateWebhooks();
    
}, 1800000);//1800000 (media hora)

//RASTREO SERVICIOS PENDIENTES DE ENVIO EN LA TABLA SEGUIMIENTO QUE NO ESTEN EN NOTIFICACIONES WEBHOOK
function trackWebhooks() {
$.ajax({
    data: {
      'Webhook_track': 1
    },
    url: "https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/webhook.php",
    type: 'post',
    success: function(response) {
        $.NotificationApp.send("Trackeando Webhooks...", "", "bottom-left", "#FFFFFF", "success");
        $('#myCenterModalLabel3').html('Tracker Actualizado '+fecha+' H: '+hora);
    }
  });
  var datatable = $('#webhook_tabla').DataTable();
  datatable.ajax.reload();

}


function updateWebhooks() {
var hoy = new Date();    
var fecha = `${hoy.getDate()}-${hoy.getMonth() + 1}-${hoy.getFullYear()}`;
var hora = addZero(hoy.getHours()) + ':' + addZero(hoy.getMinutes()) + ':' + addZero(hoy.getSeconds());

          $.ajax({
              data: {
                'SendWebhooks': 1
              },
              url: "https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/webhook.php",
              type: 'post',
              //         beforeSend: function(){
              //         $("#buscando").html("Buscando...");
              //         },
              success: function(response) {
                // var jsonData = JSON.parse(response);
                // if (jsonData.success == "1") {
                  $.NotificationApp.send("Actualizando Webhooks !", "", "bottom-right", "#FFFFFF", "warning");
                  $('#myCenterModalLabel2').html('Actualizado '+fecha+' H: '+hora);
                // } else {
                //   $.NotificationApp.send("Ocurrio un Error !", "No se realizaron cambios.", "bottom-right", "#FFFFFF", "danger");
                // }
              }
            });
            var datatable = $('#webhook_tabla').DataTable();
            datatable.ajax.reload();
        }

        //TABLA WEBHOOKS NOTIFICATIONS
        var datatable_webhooks = $('#webhook_tabla').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'excel', 'pdf'],
            paging: true,
            searching: true,
            ajax: {
              url: "https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/webhook.php",
              data: {
                'Webhook': 1                
              },
              type: 'post'
            },
            columns: [{
                data: "id"
              },
              {
                data: "idCliente"
              },
              {
                data: "idCaddy"
              },
              {
                data: "idProveedor"
              },
              {
                data: "Servidor"
              },
              {
                data: "State"    

              },
              {
                data: "User"
              },
              {
                data: "Response",
                render: function(data, type, row) {
                    if(row.Response==200){
                    var color='success';    
                    }else{
                    color='danger';
                    }
                    // var Fecha = row.Fecha.split('-').reverse().join('.');
                    return '<td><a class="text-'+color+'">' + row.Response + '</span></td>';
                  }
              },
              {
                data: "TimeStamp"                 
              }]
  
          });
        
