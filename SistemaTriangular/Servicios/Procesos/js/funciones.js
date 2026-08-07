function _googleAcDebounce(inputEl, opciones) {
    var cfg = Object.assign(
        { fields: ['address_components'], componentRestrictions: { country: 'AR' },
          types: ['geocode','establishment'], debounce: 400, minLength: 3, onSelect: null },
        opciones || {}
    );
    var svc = new google.maps.places.AutocompleteService();
    var placeSvc = new google.maps.places.PlacesService(document.createElement('div'));
    var token = new google.maps.places.AutocompleteSessionToken();
    var timer = null;
    var wrapper = inputEl.parentElement;
    if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
    var ul = document.createElement('ul');
    ul.style.cssText = 'position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;' +
        'overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;' +
        'background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);';
    wrapper.appendChild(ul);
    function close() { ul.style.display = 'none'; }
    function selectPlace(placeId, description) {
        inputEl.value = description; close();
        placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function(place, status) {
            token = new google.maps.places.AutocompleteSessionToken();
            if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
        });
    }
    inputEl.addEventListener('input', function() {
        clearTimeout(timer);
        var val = this.value.trim();
        if (val.length < cfg.minLength) { close(); return; }
        var snap = val;
        timer = setTimeout(function() {
            svc.getPlacePredictions(
                { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
                function(predictions, status) {
                    ul.innerHTML = '';
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
                    predictions.forEach(function(p) {
                        var li = document.createElement('li');
                        li.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                        li.textContent = p.description;
                        li.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                        li.addEventListener('mouseout', function() { this.style.background = ''; });
                        li.addEventListener('mousedown', function(e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
                        ul.appendChild(li);
                    });
                    ul.style.display = 'block';
                }
            );
        }, cfg.debounce);
    });
    inputEl.addEventListener('blur', function() { setTimeout(close, 200); });
}

function BuscarDireccion() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) return;
    var inputstart = document.getElementById('direccion_nc');
    if (!inputstart) return;
    _googleAcDebounce(inputstart, {
        onSelect: function(place) {
            if (!place || !place.address_components) return;
            place.address_components.forEach(function(c) {
                var t = c.types[0];
                if (t === 'locality') document.getElementById('ciudad_nc').value = c.long_name;
                else if (t === 'postal_code') document.getElementById('cp_nc').value = c.short_name;
                else if (t === 'neighborhood') document.getElementById('Barrio_nc').value = c.long_name;
                else if (t === 'street_number') document.getElementById('Numero_nc').value = c.long_name;
                else if (t === 'route') document.getElementById('Calle_nc').value = c.long_name;
            });
        }
    });
}
$(document).ready(function(){
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
var recorrido = getParameterByName('Recorrido');

// $(document).ready(function(){
  BuscarDireccion();
 //BUSCO EL TOTAL DE ENVIOS 
     $.ajax({
      data:{'TotalEnvios':1,'Recorrido':recorrido},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        $('#seguimiento_header').html('RECORRIDO '+recorrido+' | '+jsonData.totalservicios+' Servicios | '+jsonData.chofer); 
        $('#seguimiento_header2').html('VEHICULO '+jsonData.vehiculo); 
         
       }  
      });     

  // $('#seguimiento_header').html('RETIROS Y ENTREGAS RECORRIDO '+recorrido);
var datatable = $('#seguimiento').DataTable({
    ajax: {
         url:"Procesos/php/funciones.php",
         data:{'Pendientes':1,'Recorrido':recorrido},
         processing: true,
         type:'post'
         },
        columns: [
            {data:"id",
             render: function (data, type, row) {
               if(row.Estado=='No se Pudo entregar'){
               var color='danger';  
               }else if(row.Estado=='Entregado al Cliente'){
               var color='success';    
               }else{
               var color='warning';      
               }
              return '<h4><span class="badge badge-'+color+' text-white">'+row.Posicion+'</span></h4>';
              }
            },
            {data:"id",
              render: function (data, type, row) {
                if(row.Estado=='No se Pudo entregar'){
                 var circle='danger'; 
                }else if(row.Estado=='Entregado al Cliente'){
                 var circle='success';  
                }else{
                 var circle='warning'; 
                }
                if(row.Retirado==1){
                var Destino=row.ClienteDestino; 
                var Direccion=row.DomicilioDestino;  
                }else{
                var Destino=row.RazonSocial;
                var Direccion=row.DomicilioOrigen
                }
                
              return '<td><i class="mdi mdi-18px mdi-circle text-'+circle+'"></i> '+Destino+'</br>'+  
                     '<i class="mdi mdi-18px mdi-map-marker text-muted"></i><a class="text-muted">'+Direccion+'</td>';
              
              }
            },
            {data:"CodigoSeguimiento",
              render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a style="cursor:pointer"  data-toggle="modal" data-target="#modal_seguimiento" data-id="' + row.CodigoSeguimiento + '"' +
                'data-title="' + data.Destino + '" data-fieldname="' + data + '"><b>'+row.CodigoSeguimiento+'</b></a></td>';          
              }
            },
            {data:"Retirado",
                  render: function (data, type, row) {
                if(row.Retirado==1){
                    return '<td>Entrega</td>';  
                }else{
                    return '<td>Retiro</td>';  
                  }
                }
            },
          {data:"id",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil text-success"></i></a>'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a>'+
                '</td>';
            }
          }
        ]
  });
});
   function eliminar(e) {
       $.ajax({
      data:{'BuscarDatos':1,'id':e},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
       $('#warning-modal').modal('show');
        $('#id_eliminar').val(e);
        $('#codigoseguimiento_eliminar').val(jsonData.CodigoSeguimiento);  
       if(jsonData.Entregado==1){
         $('#warning-modal-ok').css('display','none');
         $('#warning-modal-body').html('El Registro '+e+ ' Origen '+jsonData.RazonSocial+' no se puede eliminar porque ya fue entregado.');  
         }else{  
        $('#warning-modal-body').html('Estas por eliminar el Registro '+e+ ' Origen '+jsonData.RazonSocial);
         $('#warning-modal-ok').css('display','block');
         }
       }
      });
   }
    $('#warning-modal-ok').click(function(){
      var id=$('#id_eliminar').val();
      var cs=$('#codigoseguimiento_eliminar').val();
      $.ajax({
            data:{'EliminarRegistro':1,'id':id,'CodigoSeguimiento':cs},
            url:'Procesos/php/funciones.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
              $('#warning-modal').modal('hide');
              if(jsonData.success==1){
               if(jsonData.hojaderuta==1){ 
                toast("success", "Registro Borrado !", "Se ha borrado el registro en Hoja de Ruta correctamente.");  
               var datatable = $('#seguimiento').DataTable();
                datatable.ajax.reload();   
               }else{
                toast("error", "Error !", "No se han realizado cambios en Hoja de Ruta.");       
               } 
               if(jsonData.transclientes==1){
               toast("success", "Registro Borrado !", "Se ha borrado el registro en Trans Clientes correctamente.");  
               var datatable = $('#seguimiento').DataTable();
               datatable.ajax.reload();  
               }else{
               toast("error", "Error !", "No se han realizado cambios en Trans Clientes.");       
               } 
              }else{
              toast("error", "Error !", "No se han realizado cambios.");    
              }
            }
        });  
     });

  function modificar(e){

   $('#id_nc').val(e);
    $.ajax({
      data:{'BuscarDatos':1,'id':e},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
         
        var jsonData = JSON.parse(response);
         
        $('#standard-modal').modal('show');

        $('#myCenterModalLabel').html('Modificar Direccion a '+jsonData.RazonSocial);  
        $('#direccion_nc').val(jsonData.Domicilio);
        $('#idCliente').val(jsonData.idCliente);
        $('#codigoseguimiento').val(jsonData.CodigoSeguimiento);
        $('#servicio').val(jsonData.Servicio); 
        $('#estado_seguimiento_t_label').html('Estado Actual: '+jsonData.EstadoSeguimiento); 
        
         if(jsonData.EstadoHdr=='Abierto'){
           $('#estadohdr_t').prop('checked', true);
           $('#estadohdr_t_label').html('Abierto');
           $('#estadohdr_t_label2').html('Esto mostrara este envio en la Hoja de Ruta.');
         }else{
           $('#estadohdr_t').prop('checked', false);
           $('#estadohdr_t_label').html('Cerrado');
           $('#estadohdr_t_label2').html('No se mostrara este envio en la Hoja de Ruta.');
         }
         
         if(jsonData.Retirado==1){
           $('#retirado_t').prop('checked', true);
           $('#retirado_t_label').html('Ya Retirado');
           $('#retirado_t_label2').html('El envío ya se encuentra Retirado de Origen.');
         }else{
           $('#retirado_t').prop('checked', false);
           $('#retirado_t_label').html('No Retirado');
           $('#retirado_t_label2').html('El envío todavia se encuentra sin Retirar de Origen.');
         }
         if(jsonData.Entregado==1){
           $('#entregado_t').prop('checked', true);

           $('#entregado_t_label2').html('El envío ya se entrego.');
           $('#direccion_nc').prop('disabled',true); 
           $('#estado_seguimiento').prop('disabled',true); 
           $('#estadohdr_t').prop('disabled',true);
           $('#recorrido_t').prop('disabled',true);
           $('#retirado_t').prop('disabled',true);
           $('#entregado_t').prop('disabled',true);
           $('#cobranzaintegrada_t').prop('disabled',true);
           $('#cobrarcaddy_t').prop('disabled',true);
           $('#modificardireccion_ok').css('display','none');
           
         }else{
           $('#entregado_t').prop('checked', false);
           $('#direccion_nc').prop('disabled',false); 
           $('#estado_seguimiento').prop('disabled',false); 
           $('#estadohdr_t').prop('disabled',false);
           $('#recorrido_t').prop('disabled',false);
           $('#retirado_t').prop('disabled',false);
           $('#entregado_t').prop('disabled',false);
           $('#cobranzaintegrada_t').prop('disabled',false);
           $('#cobrarcaddy_t').prop('disabled',false);
           $('#modificardireccion_ok').css('display','block');


         }
         if(jsonData.CobrarEnvio==1){
           $('#cobranzaintegrada_t').prop('checked', true);
         }else{
           $('#cobranzaintegrada_t').prop('checked', false);
         }
         if(jsonData.CobrarCaddy==1){
           $('#cobrarcaddy_t').prop('checked', true);
         }else{
           $('#cobrarcaddy_t').prop('checked', false);
         }
         }
      });
    }







$('#estadohdr_t').change(function(e) {
    if(this.checked) {
        $('#estadohdr_t').val(1);
        $('#estadohdr_t_label').html('Abierto');
        $('#estadohdr_t_label2').html('Esto mostrara este envio en la Hoja de Ruta.');
        }else{
        $('#estadohdr_t').val(0);
        $('#estadohdr_t_label').html('Cerrado');
        $('#estadohdr_t_label2').html('No se mostrara este envio en la Hoja de Ruta.');
        } 
});

$('#retirado_t').change(function(e) {
    if(this.checked) {
        $('#retirado_t').val(1);
        $('#retirado_t_label').html('Ya Retirado');
        $('#retirado_t_label2').html('El envío ya se encuentra Retirado de Origen.');
        }else{
        $('#retirado_t').val(0);
        $('#retirado_t_label').html('No Retirado');
        $('#retirado_t_label2').html('El envío todavia se encuentra sin Retirar de Origen.');
        } 
});
$('#entregado_t').change(function(e) {
    if(this.checked) {
        $('#entregado_t').val(1);
        }else{
        $('#entregado_t').val(0);
        } 
});$('#cobranzaintegrada_t').change(function(e) {
    if(this.checked) {
        $('#cobranzaintegrada_t').val(1);
        }else{
        $('#cobranzaintegrada_t').val(0);
        } 
});$('#cobrarcaddy_t').change(function(e) {
    if(this.checked) {
        $('#cobrarcaddy_t').val(1);
        }else{
        $('#cobrarcaddy_t').val(0);
        } 
});


$('#modificardireccion_ok').click(function(){
  var estadohdr =$('#estadohdr_t').val();
  var retirado =$('#retirado_t').val();
  var entregado =$('#entregado_t').val();
  var cobranzaintegrada=$('#cobranzaintegrada_t').val();
  var cobrarcaddy=$('#cobrarcaddy_t').val();
  var estado_seguimiento=$('#estado_seguimiento').val();
  var dir=$('#direccion_nc').val();
  var calle= $('#Calle_nc').val();
  var barrio= $('#Barrio_nc').val();
  var numero= $('#Numero_nc').val();
  var ciudad= $('#ciudad_nc').val();
  var cp= $('#cp_nc').val();
  var id=$('#id_nc').val();
  var idCliente=$('#idCliente').val();
  var codigoseguimiento=$('#codigoseguimiento').val();
  var servicio=$('#servicio').val();
  $.ajax({
      data:{'ActualizarDireccion':1,'idCliente':idCliente,'CodigoSeguimiento':codigoseguimiento,'Servicio':servicio,
            'Direccion':dir,'id':id,'calle':calle,'barrio':barrio,'numero':numero,'ciudad':ciudad,'cp':cp,
            'Estado':estadohdr,'Retirado':retirado,'Entregado':entregado,'Cobranzaintegrada':cobranzaintegrada,
            'Cobrarcaddy':cobrarcaddy,'EstadoSeguimiento':estado_seguimiento},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        var datatable = $('#seguimiento').DataTable();
        datatable.ajax.reload();  
       $('#standard-modal').modal('hide');
      toast("success", "Registro Actualizado !", "Se ha actualizado la tabla Clientes correctamente."+jsonData.estado);    
       var datatable = $('#seguimiento').DataTable();
        datatable.ajax.reload();  
       }  
  });   
});
