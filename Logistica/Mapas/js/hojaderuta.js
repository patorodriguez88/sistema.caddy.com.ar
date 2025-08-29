function asignacion_recorrido(){

    $('#asignaciones').show();  

}

function abrir_todos(i){
console.log('todos',i);

    $.ajax({
        data:{'Abrir_todos':1,'Recorrido':i},
        url: 'Mapas/php/abrir_todos.php',
        type: 'post',
        success: function(response)
        {
    
        }
    });
}

function verentabla(a){
  var table = $('#seguimiento').DataTable();
  table.search(a).draw();
}
const pato=22;

function initMap(c) {

    var divMapa = document.getElementById('map');
    var xhttp;
    var resultado = [];
    var markers = [];
    var co =[];
  
    var markerss=[];
    
    var infowindowActivo = false;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        resultado = xhttp.responseText;
        var objeto_json = JSON.parse(resultado);
        
        $('#header-title2').html(c);
        
        for (var i = 0; i < objeto_json.data.length; i++) {
            
        //ICONO DE COLORES
              function pinSymbol(color) {
                  return {
                  path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',                  
                  fillColor: '#'+color,
                  fillOpacity: 1,
                  strokeColor: '#FFFFFF',
                  strokeWeight: 1,
                  scale: 1,
                  labelOrigin: new google.maps.Point(0, -29),
                  };
                } 
                function pinLabel(Posicion){
                    return{
                        
                        color: 'white',
                        fontWeight: 'bold',
                        text:Posicion
                        
                    };
                }
                
               var a=Number(objeto_json.data.length);
               
               $('#marker').html('Total '+objeto_json.data.length);
               $('#marker_2').html('Errores '+Number(a-(markers.length+1)));
               $('#marker_0').html('Entregas '+objeto_json.Total_entregas);//ENCONTRADOS EN TABLA
               $('#marker_1').html('Retiros '+objeto_json.Total_retiros);       
                 
                if(c!=''){

                  if(c=='t'){

                    if(objeto_json[0][i]==null){
                     
                        var icono= null;
                    
                    }else{                                            
                    
                        var icono=pinSymbol(objeto_json[0][i]);      
                    } 

                    }else{
                        
                        if((objeto_json.data[i].Retirado==0)&&(objeto_json.data[i].Entrega==0)){   
                        var valor_retirado=0;
                        var icono=pinSymbol('ffc107');  
                        // var Posicion=objeto_json.data[i].Posicion_retiro;                    
                        }else{                    
                        var valor_retirado=1;
                        // var Posicion=objeto_json.data[i].Posicion;
                        var icono=pinSymbol(c);  
                        $('#marker_0').css('color',`#${c}`);

                        }

                  }
                }else{
                icono=null;  
                }

                var latlong = objeto_json.data[i].coordenadas.split(',');
                myLatLng = {
                    lat: Number(latlong[0]),
                    lng: Number(latlong[1])
                };

                var marker= new google.maps.Marker({
                  position: myLatLng,
                  map: map,
                  label: pinLabel(objeto_json.data[i].Posicion),
                  title: objeto_json.data[i].nombrecliente,
                  icon: icono,
                  pato: valor_retirado
                });

               markers.push(marker);
                
                var tel1=objeto_json.data[i].Celular;
                var tel2=objeto_json.data[i].Telefono;
                var tel3=objeto_json.data[i].Telefono2;
                // console.log('markers',markers.length);

                if(tel2==tel1){
                 var cel=tel1; 
                }else{
                 var cel=tel1+' | '+tel2; 
                }

                var contentString = '<h4 id="firstHeading" class="firstHeading">' +
                    objeto_json.data[i].nombrecliente + '</h4>'+ '<div id="bodyContent">'+
                    '<p><b>Recorrido: ' + objeto_json.data[i].Recorrido + '</b></p>'+
                    '<p><b><a target="t_blank" href="https://api.whatsapp.com/send?phone='+ cel +
                    '&text=Hola '+ objeto_json.data[i].nombrecliente +' !,%20 nos comunicamos de Caddy Logística, tenemos un envío para entregarte, pero necesitamos corroborar tu dirección, ya que nuestro cliente nos indicó que la misma era '+ objeto_json.data[i].Direccion +'... pero no logramos ubicarnos. Nos podrás ayudar ?. "> Teléfono: '+ cel +'</a></b></p>'+
                    '<p><b>Seguimiento: ' + objeto_json.data[i].Seguimiento + '</b></p>'+
                    '<p>Dir:' + objeto_json.data[i].Direccion +'</p>'+
                    '<td class="table-action">'+
                    '<a style="cursor:pointer" data-id="' + objeto_json.data[i].Seguimiento + '" id="'+objeto_json.data[i].Seguimiento+'" onclick="verentabla(this.id)"><b class="text-primary">Ver en Tabla</b></a>'+
                    '</td></div>';
                    markers[i].id=objeto_json.data[i].idHojaderuta;
                    markers[i].Recorrido=objeto_json.data[i].Recorrido;

                    markers[i].infoWindow = new google.maps.InfoWindow({
                    content: contentString
                    });

                                    
                    google.maps.event.addListener(markers[i], 'click', function(){     
                        if($('#alert_ordenar').css('display') == 'block'){    
                            // console.log('marker',this);
                            var send_id=this.id;
                            var valorpato=this;
                            
                            console.log('valor',valorpato);

                            let Posicion=$('#next_number').html();  
                            $.ajax({
                            data:{'NewOrder':1,'Recorrido':this.Recorrido,'idhdr':send_id,'valor_retirado':this.pato,'Posicion':Posicion},
                            url: 'Mapas/php/cambiar_posicion.php',
                            type: 'post',
                            success: function(response)
                            {
                            var jsonData = JSON.parse(response);
                            
                            if (jsonData.resultado == "1")
                             {    
                                $('#next_number').html(jsonData.new_p);  
                               // console.log('marker',markers[_i]);
                               valorpato.icon=pinSymbol('#CCCCCC');  
                               valorpato.setMap(null);
                               valorpato.setMap(map);
                               valorpato.setAnimation(google.maps.Animation.DROP);
                               valorpato.label={
                               color: 'gray',
                               fontWeight: 'bold',
                               text:jsonData.newPosicion
                               };        
                              }
                             }
                            });

                        }else{

                            if(infowindowActivo){
                            infowindowActivo.close();
                          }   

                          infowindowActivo = this.infoWindow;
                          infowindowActivo.open(map, this);
                        }   
                    });
            }
      }
    }
     var myLatLng = {
              lat: -31.4448988,
              lng: -64.177743
          };
    var url = "Mapas/php/datos_hojaderuta.php";
    xhttp.open("POST", url, true);
    xhttp.send(); 
  
  map = new google.maps.Map(document.getElementById("map"), {
    center: new google.maps.LatLng(-31.4448988, -64.177743),
    zoom: 10,
  });
}

$('#ordenar_recorrido_automatic').click(function(){

    $('#full-width-modal_order').modal('show');
    var datatable = $('#flota').DataTable();
    datatable.destroy();

    var fechas1=(new Date().getUTCMonth()-1)+'/'+new Date().getUTCDate()+'/'+new Date().getUTCFullYear();
    var fechas2=(new Date().getUTCMonth()+1)+'/'+new Date().getUTCDate()+'/'+new Date().getUTCFullYear();
    var fechas_control=fechas1+" - "+fechas2;    

    init_datatable(fechas_control);

});

$('#ordenar_recorrido').click(function(){

    if($('#alert_ordenar').css('display') == 'block'){    
        $('#alert_ordenar').hide();
        $('#map').css('min-height','400px');
        $('#ordenar_recorrido').html('Ordenar');    
    }else{
        $('#alert_ordenar').show();    
        $('#map').css('min-height','450px');
        $('#ordenar_recorrido').html('Cerrar Orden');
    }
    var Recorrido=$('#recorrido').html();

    $.ajax({
        data:{'ViewOrder':1,'Recorrido':Recorrido},
        url: 'Mapas/php/cambiar_posicion.php',
        type: 'post',
        success: function(response)
        {
        var jsonData = JSON.parse(response);

        if (jsonData.resultado == "1"){    
            
         var new_p = Number(jsonData.newPosicion)+1;

          $('#next_number').html(new_p);
         
         }
        }
    });
    
});


$('#restaurar_orden').click(function(){
    var Recorrido=$('#recorrido').html();
    $('#warning-alert-modal').modal('show');
   
    $('#warning-alert-modal-ok').click(function(){
    
    $.ajax({

        data:{'RestartOrder':1,'Recorrido':Recorrido},
        url: 'Mapas/php/cambiar_posicion.php',
        type: 'post',
        beforeSend: function() {
            $('#info-alert-modal').modal('show');
            $('#info-alert-modal-title').html('Restableciendo Todo el Orden al Recorrido '+ Recorrido);
            },
  
        success: function(response)
        {
        var jsonData = JSON.parse(response);

        if (jsonData.resultado == "1"){ 
            
            veo(Recorrido);
            renderizar_datos();
            
            $('#info-alert-modal').modal('hide');            
            $('#next_number').html(1);
            
            var datatable = $('#seguimiento').DataTable();
            datatable.ajax.reload();
         }
        }
    });
    });
    $('#warning-alert-modal-cancel').click(function(){

    });
});

$('#orden_automatico').click(function(){
    var Recorrido=$('#recorrido').html();
    $.ajax({
        data:{'Orden_Automatic':1,'Recorrido':Recorrido},
        url: 'Mapas/php/orden_automatico.php',
        type: 'post',
        beforeSend: function() {
            $('#info-alert-modal').modal('show');

            $('#info-alert-modal-title').html('Analizando Distancias del Recorrido '+ Recorrido);
            
            },
  
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            if (jsonData.resultado == "1"){ 
            veo(Recorrido);          
            $('#info-alert-modal').modal('hide');                        
            }
        }
    });

});

$('#orden_anterior').click(function(){
    var Recorrido=$('#recorrido').html();
    $.ajax({
        data:{'Orden_Anterior':1,'Recorrido':Recorrido},
        url: 'Mapas/php/orden_anterior.php',
        type: 'post',
        beforeSend: function() {
            $('#info-alert-modal').modal('show');

            $('#info-alert-modal-title').html('Analizando Posiciones Anteriores del Recorrido '+ Recorrido);
            
            },
  
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            console.log(jsonData);
            if (jsonData.success == "1"){ 
                
                $('#info-alert-modal').modal('hide');
                var datatable = $('#seguimiento').DataTable();
                datatable.ajax.reload();
                veo(Recorrido);
             
            }
        }
    });

});

