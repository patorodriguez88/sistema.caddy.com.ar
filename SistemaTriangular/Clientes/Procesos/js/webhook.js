$('#save_webhook').click(function(){
var id = document.getElementById('buscarcliente').value;
let checked = $("#relations_webhook").prop('checked');
let endpoint=$("#endpoint_webhook").val();
let token=$("#token_webhook").val();

if(checked==true){
    checked=1;
}else{
    checked=0;
}
$.ajax({
    data: {'ActualizarDatosWebhook':1,'Relaciones':checked,'idCliente':id,'endpoint':endpoint,'token':token},
    url: 'Procesos/php/webhook.php',
    type: 'post',
    success: function(response) {
    var jsonData = JSON.parse(response); 
    if(jsonData.success==0){
        toast("error", "Error !", "No se realizaron cambios.");      
    }
    if(jsonData.success_update==1){
        toast("success", "Registro Actualizado!", "Se actualizo un registro de webhook.");      
    }
    if(jsonData.success==1){
        toast("success", "Registro Ingresado!", "Se ingreso un registro de webhook.");      
    } 
    if(jsonData.success==2){
        toast("success", "Registros Actualizados !", "Se actualizaron los registros de los webhook de todos los clientes relacionados.");      
    }
    }
  });

});

$('#buscarcliente').change(function(){
var id = document.getElementById('buscarcliente').value;
var dato = {
  "Webhook": 1,
  "idCliente": id
};
$.ajax({
  data: dato,
  url: 'Procesos/php/webhook.php',
  type: 'post',
  //         beforeSend: function(){
  //         $("#buscando").html("Buscando...");
  //         },
  success: function(response) {
  var jsonData = JSON.parse(response);  
  $('#endpoint_webhook').val(jsonData.data[0].Endpoint);
  $('#token_webhook').val(jsonData.data[0].Token);

  if(jsonData.data[0].Activo==1){
    document.getElementById("webhook_switch").checked = true;
    $('#title_switch_webhook').html('Webhook Activo');
   }else{
    $('#title_switch_webhook').html('Webhook Inactivo');   
   }
  }
});

$.ajax({
    data: {'AdminEnvios':1,'id':id},
    url: 'Procesos/php/tablas.php',
    type: 'post',
    //         beforeSend: function(){
    //         $("#buscando").html("Buscando...");
    //         },
    success: function(response) {
    var jsonData = JSON.parse(response);  
    // console.log('rows',jsonData);
    if(jsonData.total!=0){
        $('#title_relations_webhook').html('Atencion, el cliente tiene relacionado '+jsonData.total+' clientes.</br> Activar para actualizar el endpoint y el token con los clientes relacionados');
        }else{
        $('#title_relations_webhook').html('El cliente no tiene relaciones');
        $('#relations_webhook').prop('disabled',true); 
        }    
    }
  });


});
$(document).on('change', 'input[name="edit_webhook"]', function(e) {
    if (this.checked==true){
    $('#endpoint_webhook').prop('disabled',false);  
    $('#token_webhook').prop('disabled',false); 
    $('#save_webhook').prop('disabled',false); 
    }else{
    $('#endpoint_webhook').prop('disabled',true);  
    $('#token_webhook').prop('disabled',true);      
    $('#save_webhook').prop('disabled',true);    
}
});
$(document).on('change', 'input[name="webhook_switch"]', function(e) {
    var id = document.getElementById('buscarcliente').value;
    if (this.checked==true){
    var act=1;   
    $('#title_switch_webhook').html('Webhook Activo');   
    }else{
    act=0;    
    $('#title_switch_webhook').html('Webhook Inactivo');    
}
    
        $.ajax({
            data: {'Webhook_activo':1,"idCliente":id,"Activo":act},
            url: 'Procesos/php/webhook.php',
            type: 'post',
            //         beforeSend: function(){
            //         $("#buscando").html("Buscando...");
            //         },
            success: function(response) {
            var jsonData = JSON.parse(response);  
            // console.log('rows',jsonData);
            if(jsonData.success==1){
                // $('#endpoint_webhook').prop('disabled',true);
            //   document.getElementById("webhook_switch").checked = true;
            //   $('#endpoint_webhook').val(jsonData.data[0].Endpoint);
            //   $('#token_webhook').val(jsonData.data[0].Token);
             }else{

             }
            }
          });
            
    
  });
  
// Caddy avisa a los destinatarios de este cliente (Clientes.AvisarDestinatario). Lo usan la app de reparto
// (Mail/notices.php) y la API al crear el envío (EnviarMail).
$('#buscarcliente').change(function(){
  var id = document.getElementById('buscarcliente').value;
  $('#avisar_destinatario_switch').prop('checked', true);
  $.post('Procesos/php/webhook.php', {'AvisarDestinatario_get': 1, 'idCliente': id}, function(response) {
    var jsonData = JSON.parse(response);
    if (jsonData.success == 1) $('#avisar_destinatario_switch').prop('checked', jsonData.valor == 1);
  });
});
$(document).on('change', 'input[name="avisar_destinatario_switch"]', function() {
  var id = document.getElementById('buscarcliente').value;
  var valor = this.checked ? 1 : 0;
  $.post('Procesos/php/webhook.php', {'AvisarDestinatario_set': 1, 'idCliente': id, 'Valor': valor}, function(response) {
    var jsonData = JSON.parse(response);
    if (jsonData.success == 1) {
      toast("success", "Guardado", valor ? "Caddy avisa a los destinatarios de este cliente." : "Caddy no les escribe a los destinatarios de este cliente.");
    } else {
      toast("error", "Error !", "No se pudo guardar.");
      $('#avisar_destinatario_switch').prop('checked', !valor);
    }
  });
});

// Webhook de estados (Webhook.EventosEstado): Datos/Procesos/php/cron_webhook_estados.php
$('#buscarcliente').change(function(){
  var id = document.getElementById('buscarcliente').value;
  $('#eventos_estado_switch').prop('checked', false);
  $.post('Procesos/php/webhook.php', {'EventosEstado_get': 1, 'idCliente': id}, function(response) {
    var jsonData = JSON.parse(response);
    $('#eventos_estado_switch').prop('disabled', jsonData.success != 1);
    if (jsonData.success == 1) $('#eventos_estado_switch').prop('checked', jsonData.valor == 1);
  });
});
$(document).on('change', 'input[name="eventos_estado_switch"]', function() {
  var id = document.getElementById('buscarcliente').value;
  var valor = this.checked ? 1 : 0;
  $.post('Procesos/php/webhook.php', {'EventosEstado_set': 1, 'idCliente': id, 'Valor': valor}, function(response) {
    var jsonData = JSON.parse(response);
    if (jsonData.success == 1) {
      toast("success", "Guardado", valor ? "Se avisan todos los cambios de estado al webhook." : "Ya no se avisan los cambios de estado.");
    } else {
      toast("error", "Error !", "Primero cargá el EndPoint y el Token del webhook.");
      $('#eventos_estado_switch').prop('checked', !valor);
    }
  });
});
