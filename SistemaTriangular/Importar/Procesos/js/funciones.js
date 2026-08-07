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
//CALCULO DISTANCIA
function distancia(){

  
}


$('#submit').click(function(){
var  datatable = $('#seguimiento').DataTable();
  datatable.ajax.reload();
});
var relacion=$('#relacion_nc').val();
console.log('relacion',relacion);

$(document).ready(function() {
var datatable = $('#seguimiento').DataTable({
  dom: 'Bfrtip',
  buttons: ['pageLength'],
  paging: true,
  searching: true,
  lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
  ajax: {
         url:"Procesos/php/funciones.php",
         data:{'Importaciones':1,'Relacion':relacion},
         type:'post'
         },
        columns: [
            {data:"NombreCliente",
              render: function (data, type, row) {
                if(row.NombreClienteClientes===null){
                 var a='No Existe en Clientes'; 
                 var tr='text-success';
                }else{
                 var a="Si Existe en Clientes"; 
                var tr='text-danger';
                }
                if(row.Latitud!=''){
                var marker='success';
                var lat=row.Latitud;
                var lng=row.Longitud;  
                }else{
                var marker='muted';  
                var lat='Cordenadas';
                var lat='Error';  
                }
                if(row.Km>100){
                var atencion='Atencion ! '+row.Km;  
                }else{
                atencion=row.Km;  
                }
                  return '<tr class="table-success"><td><span class="'+tr+'"><dt>['+row.idProveedor+'] '+row.NombreCliente+' '+a+'</dt></span></br>'+  
                  '<i class="mdi mdi-18px mdi-map-marker text-'+marker+' "></i><a class="text-muted">'+row.Direccion +' ('+lat+' '+lng+')('+atencion+')</td></tr>';
              }
            },
          {data:"Cantidad"},
          {data:"id",
          render: function (data, type, row) {
            return  '<div class="progress progress-sm">'+
                    '<div class="progress-bar progress-lg bg-info" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>'+
                    '</div>';
            }
          },
          
          {data:"id",
           render: function (data, type, row) {
                  return '<td class="table-action">'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);"class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
                '</td>';
            }
          }
        ],
          select: {
            style:    'os',
            selector: 'td:first-child'
        },
  
  });  




$('#seguimiento').DataTable().on("draw", function(){
$('#outer').hide();
  card();
});

  
});


function card(){
    var relacion=$('#relacion_nc').val();
    $.ajax({
            data:{'Cantidades':1,'Relacion':relacion},
            url:'Procesos/php/funciones.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
            $('#clientesnuevos_card').html(jsonData.noexisten+' Clientes Nuevos');
            $('#clientesexistentes_card').html(jsonData.existen+' Clientes Existentes');
            $('#ventas_card').html(jsonData.todos+' Ventas Nuevas');
            $('#card').show();
            }
        });  
}
 
function modificar(e) {

   $('#id_nc').val(e);
    $.ajax({
      data:{'BuscarDatos':1,'id':e},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
        $('#standard-modal').modal('show');
        $('#myCenterModalLabel').html('Modificar Direccion a '+jsonData.data[0].nombrecliente);  
        $('#direccion_nc').val(jsonData.data[0].Direccion); 
         
       }  
  });   

$('#modificardireccion_ok').click(function(){
  var dir=$('#direccion_nc').val();
  var calle= $('#Calle_nc').val();
  var barrio= $('#Barrio_nc').val();
  var numero= $('#Numero_nc').val();
  var ciudad= $('#ciudad_nc').val();
  var cp= $('#cp_nc').val();
  var id=$('#id_nc').val();
  
  var origen="Reconquista 4986, Cordoba, Argentina";

  $.ajax({
      data:{'BuscarDistancia':1,'origen':origen,'destino':dir},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
      var km=jsonData.distancia/1000;
  
      $.ajax({
          data:{'ActualizarDireccion':1,'Direccion':dir,'id':id,'calle':calle,'barrio':barrio,'numero':numero,'ciudad':ciudad,'cp':cp,'km':km},
          url:'Procesos/php/funciones.php',
          type:'post',
          success: function(response)
           {
            var jsonData = JSON.parse(response);
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();  
           $('#standard-modal').modal('hide');
           }  
        });
      }
    });
});
}

function eliminar(e) { //ELIMINAR
$('#warning-modal-body').html('Realmente eliminaras el registro '+e+ ' ?'); 
$('#id_eliminar').html(e);  
$('#warning-modal').modal('show');
}

$('#warning-modal-ok').click(function(){
  var e=$('#id_eliminar').html();
      $.ajax({
            data:{'EliminarRegistro':1,'id':e},
            url:'Procesos/php/funciones.php',
            type:'post',
            success:function(response){
            var jsonData = JSON.parse(response);
              if(jsonData.success==1){
              $('#warning-modal').modal('hide');  
              toast("success", "Registro Borrado !", "Se ha borrado el registro correctamente.");  
              var datatable = $('#seguimiento').DataTable();
              datatable.ajax.reload();  
              card();  
              }else{
              toast("error", "Error !", "No se han realizado cambios.");    
              }
            }
        });  
     });



function vaciar_tabla(){
$.ajax({
      data:{'VaciarTabla':1},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
        if(jsonData.success==1){
        toast("success", "Tabla Borrada !", "Se han borrado " + jsonData.regborrados + " registros.");  
        $('#tabla').hide();
        $('#VaciarTabla').hide();
        $('#response').hide();  
        $('#card').hide();   
        $('#outer').show();
        $('#ImportarTabla').hide();
          window.location("https:www.caddy.com.ar/SistemaTriangular/Importar/index.php");
        }else{
        toast("error", "Error !", "No se han realizado cambios.");    
        }
      }
  });  
}
$('#ImportarTabla').click(function(){
  var nuevosclientes=$('#clientesnuevos_card').html();
  var nuevasventas=$('#ventas_card').html();
 $('#importar-modal-body').html('Confirmas Importar la tabla ?'+ 'se crearan '+nuevosclientes+'. Y cargaremos '+ nuevasventas);
 $('#importar-modal').modal('show');
  
});


$('#importar-modal-ok').click(function(){
    var relacion=$('#relacion_nc').val();
    $.ajax({
      data:{'ImportarTabla':1,'Relacion':relacion},
      url:'Procesos/php/funciones.php',
      type:'post',
      success:function(response){
      var jsonData = JSON.parse(response);
        if(jsonData.success==1){
        $('#importar-modal').modal('hide');
        $('#success-alert-modal').modal('show');  
        $('#success-info').html('Se ingresaron '+jsonData.importados+' Clientes y se cargaron '+jsonData.preventa+' envios a Preventa');  
        }else{
        toast("error", "Error !", "No se han ingresado registros a Clientes.");    
        }
      }
  });  
});
                          
$('#VaciarTabla').click(function(){
vaciar_tabla();
});
