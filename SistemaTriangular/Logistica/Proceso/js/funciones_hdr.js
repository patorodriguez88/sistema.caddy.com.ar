$(document).ready(function(){
  $('#card_tabla').hide();
  paneles();  
  renderizar_datos();

});

function renderizar_datos(){
    $.ajax({
        data:{'Renderizar':1},
        type: "POST",
        url: "Proceso/php/roadmap_end.php",
        beforeSend: function() {
        // setting a timeout
        $('#info-alert-modal').modal('show');
        $('#info-alert-modal-title').html('Renderizando Tabla Roadmap');
        },
        success: function(response)
        {
        $('#info-alert-modal').modal('hide');
        }
        });    
}

function color(c,r){
$.ajax({
          data:{'Color':1,'Recorrido':r,'ColorSeleccionado':c},
          type: "POST",
          url: "Proceso/php/funciones_hdr.php",
          beforeSend: function() {
          // setting a timeout
          $('#info-alert-modal').modal('show');
          $('#info-alert-modal-title').html('Actualizando...');
          },
          success: function(response)
          {
          var jsonData= JSON.parse(response);
          $('#info-alert-modal').modal('hide');
          }
      });
}

//FUNCION PARA MOSTRAR LOS PANELES
function paneles(){
$.ajax({
          data:{'FormaDePago':1},
          type: "POST",
          url: "Proceso/php/funciones_hdr.php",
          beforeSend: function() {
            // setting a timeout
            $('#info-alert-modal').modal('show');
            $('#info-alert-modal-title').html('Cargando Hojas de Ruta');
            },  
          success: function(response)
          {
            $('#info-alert-modal').modal('hide');    
          $('#hdractivas').html(response).fadeIn();
        
          }
      });
}

//BOTONERA CAMBIAR RECORRIDO
  $('#cambiar_recorrido').click(function(){
  $('#hdractivas').show();
  $('#card_mapa').hide();
  $('#card_tabla').hide();  
  paneles();  
});

//BOTONERA VER TODOS
  $('#todos_recorrido').click(function(){
  $.ajax({
          data:{'Todos':1},
          type: "POST",
          url: "Mapas/php/datos_hojaderuta.php",
          success: function(response)
          {
            var jsonData= JSON.parse(response);
            $('#header-title').html('Servicios Pendientes');
            $('#card_tabla').show();
            var c='t';
            initMap(c); 
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();
            $('#ordenar_recorrido').css('display','none');

          }
      });
  });

function veo(i){
  $.ajax({
          data:{'Mapa':1,'Rec':i},
          type: "POST",
          url: "Mapas/php/datos_hojaderuta.php",
          success: function(response)
          {
            var jsonData= JSON.parse(response);
            $('#recorrido').html(jsonData.Recorrido);
            $('#hdractivas').hide();
            $('#card_mapa').show();
            $('#header-title2').html(jsonData.Color);
            if(jsonData.NombreChofer==''){
                if(jsonData.Estado=='Alta'){
                    var color='warning';
                }
                if(jsonData.Estado=='Cargada'){
                    var color='success';
                }
                if(jsonData.Estado=='Cerrada'){
                    var color='danger';
                }
                
            $('#header-title').html('Servicios Pendientes Recorrido '+jsonData.Recorrido+' Estado <a class="text-'+color+'"> '+jsonData.Estado+'</a>');
            }else{
            $('#header-title').html('Servicios Pendientes Recorrido '+jsonData.Recorrido+' Estado <a class="text-'+color+'"> '+jsonData.Estado+'</a> Chofer: '+jsonData.NombreChofer);    
            }
            $('#card_tabla').show();
            $('#ordenar_recorrido').css('display','block');
            initMap(jsonData.Color); 
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();
          }
      });
}

function imprimirhdr(i){
alert(i);  
var myWindow = window.open("", "_self");
myWindow.document.write("<p>I replaced the current window.</p>");  
}

function eliminarrecorrido(i){
  $('#warning-modal-body').html('Estas por eliminar el Recorrido '+i);
  $('#id_eliminar').val(i);

   }
