function filterArray(inputArr){
    var found ={};
    var out = inputArr.filter(function(element){
        return found.hasOwnProperty(element)? false : (found[element]=true);
    });
    return out;
}

$(document).ready(function(){
    initMap();
    $.ajax({
            data:{'Limpiar':1},
            type: "POST",
            url: "Mapas/php/zonas.php",
            success: function(response)
            {
            }
        });
      
    $.ajax({
            data:{'BuscarRecorridos':1},
            type: "POST",
            url: "Proceso/php/pendientes.php",
            success: function(response)
            {
            $('.selector-recorrido1 select').html(response).fadeIn();
            }
        });
    });
    
    let selected=[];
    
    $('#select_rec_mapa').change(function(){
      selected=[];  
        $(this).find('option:selected').each(function(i,e){
            selected.push(e.value);
        });
        
        //   initMap();

    });

    var zonas_selected=[];
    var zona=[];

    $('.btn-dark').click(function(){
        var id = $(this).attr('id');
        zonas_selected.push(id);    
        var filtro=filterArray(zonas_selected); 
        
        // initMap(filtro);
        zona=filtro;

        $('.header-title').html('Zonas google Maps '+zona);  
        
    });

$('#ver_mapa').click(function(){
// alert(selected);
// alert(zona);

initMap();

});
    // This example adds a user-editable rectangle to the map.
    // When the user changes the bounds of the rectangle,
    // an info window pops up displaying the new bounds.
    let rectangle;
    let map;
    let infoWindow;
    let milat;
    let milng;
    
    
    function initMap() {
    var divMapa = document.getElementById('map');
    var xhttp;
    var resultado = [];
    var markers = [];
    var co =[];
    
    
      map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: -31.4448988, lng: -64.177743 },
        zoom: 10,
      });
      
      $.ajax({
          data: {'Buscar_poly':1,'zona':zona,'rec':selected},
          url:'Mapas/php/zonas.php',
          type:'post',
          success: function(response)
           {
             var jsonData = JSON.parse(response);            
             
             var total_zona=zona.length;

             for(var p = 1; p <= zona.length;p++){
                var rectangle='rectangle'+p;
                var popup='popup'+p;
                var verticePoligono= 'verticePoligono'+p; 
                var verticesPoligono=`verticesPoligono${p}`;  
                verticesPoligono=[];  

                //DIBUJO LOS POLIGONOS
                for(var i = 0; i < jsonData.data.length;i++){
                    if(jsonData.data[i].Numero==p){

                    // var color=jsonData.data[i].Color;                
                    
                    var verticePoligono = {
                        lat: Number(jsonData.data[i].Latitud),
                        lng: Number(jsonData.data[i].Longitud)
                    };
                    verticesPoligono.push(verticePoligono);
                  }
                }
                
                
                if(p==1){
                color='rgb(63,91,169)';
                color_stroke='rgb(63,91,169)';
                }
                if(p==2){
                color='RGB(0,157,87)';    
                color_stroke='RGB(0,157,87)';    
                }
                if(p==3){
                color='RGB(166,27,74)';    
                color_stroke='RGB(166,27,74)';    
                }
                if(p==4){
                color='RGB(0,157,87)';    
                color_stroke='RGB(0,157,87)';    
                }
                if(p==5){
                color='RGB(244,235,55)';  
                color_stroke='RGB(244,235,55)';    
                }
                if(p==6){
                color='RGB(124,53,146)';    
                color_stroke='RGB(124,53,146)';    
                }
                if(p==7){
                color='RGB(238,156,150)';    
                color_stroke='RGB(238,156,150)';    
                }
                if(p==8){
                color='RGB(121,80,70)';    
                color_stroke='RGB(121,80,70)';
                }
                if(p==9){
                color='RGB(219,68,54)';    
                color_stroke='RGB(219,68,54)';    
                }
                if(p==10){
                color='RGB(147,215,232)';    
                color_stroke='RGB(147,215,232)';    
                }

                rectangle = new google.maps.Polygon({
                  path: verticesPoligono,
                  map: map,
                  strokeColor: color_stroke,
                  fillColor: color,
                  strokeWeight: 4,
                  // bounds: bounds,
                  editable: false,
                  draggable: false,
                  
                });
                
                rectangle.setMap(map);

                    popup = new google.maps.InfoWindow();
        
                    rectangle.addListener('click', function (e) {
                        popup.setPosition(e.latLng);
                        popup.setContent('Zona '+p+' '+e.latLng);
                        popup.open(map);                        
                    });

                 }
                 
                // // //DEFINO VARIABLES PARA LOS TOTALES
                var numero1 = 0;
                var numero2 = 0;
                var numero3 = 0;
                var numero4 = 0;
                var numero5 = 0;
                var numero6 = 0;
                var numero7 = 0;
                var numero8 = 0;
                var numero9 = 0;
                var numero10 = 0;


            //CONTROLO LOS MARKERS QUE HAY DENTRO DE LOS POLIGONOS
            function checkInPolygon(marker,name) {
                
            for(var q = 1; q <= total_zona;q++){
                var rectanglem='rectanglem'+q;
                var popupm='popup_m'+q;
                var verticePoligonom= 'verticePoligonom'+q; 
                var verticesPoligonom=`verticesPoligonom${q}`;  
                verticesPoligonom=[];

                console.log(zona[q]);
                
                //DIBUJO LOS POLIGONOS
                for(var r = 0; r < jsonData.data.length;r++){
                    if(jsonData.data[r].Numero==q){
                    // var verticePoligono= 'verticesPoligono'+i;     
                    
                    var verticePoligonom = {
                        lat: Number(jsonData.data[r].Latitud),
                        lng: Number(jsonData.data[r].Longitud)
                    };
                    verticesPoligonom.push(verticePoligonom);
                }
                }

                rectanglem = new google.maps.Polygon({
                path: verticesPoligonom,
                //   map: map,
                strokeColor: 'rgb(255, 0, 0)',
                fillColor: 'rgb(255, 255, 0)',
                //   strokeWeight: 4,
                // bounds: bounds,
                //   editable: true,
                //   draggable: true,
                
                });
                


            if (google.maps.geometry.poly.containsLocation(marker.getPosition(), rectanglem)) {

                var zona=q+1;

                if (zona == 1) {numero1 += 1;}
                if (zona == 2) {numero2 += 1;}
                if (zona == 3) {numero3 += 1;}
                if (zona == 4) {numero4 += 1;}
                if (zona == 5) {numero5 += 1;}
                if (zona == 6) {numero6 += 1;}
                if (zona == 7) {numero7 += 1;}
                if (zona == 8) {numero8 += 1;}
                if (zona == 9) {numero9 += 1;}
                if (zona == 10) {numero10 += 1;}                    

                    } 

                }

            };

            //INCLUIMOS LOS ICONOS DE LOS RECORRIDOS SELECCIONADOS    
            xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                resultado = xhttp.responseText;
                var objeto_json = JSON.parse(resultado);
                
                $('#cantidad').html(objeto_json.data.length + ' Servicios dentro de Zonas');

                for (var j = 0; j < objeto_json.data.length; j++) {
                
                //ICONO DE COLORES
                    function pinSymbol(color) {
                        return {
                        path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                        fillColor: '#'+color,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: 1,
                        scale: 1,
                        };
                        } 
                        var icono=pinSymbol(objeto_json[0][j]);      
                        var latlong = objeto_json.data[j].coordenadas.split(',');
                        myLatLng = {
                            lat: Number(latlong[0]),
                            lng: Number(latlong[1])
                        };
                
                        // console.log('latlong',myLatLng);

                        var marker= new google.maps.Marker({
                        position: myLatLng,
                        map: map,
                        title: objeto_json.data[j].nombrecliente,
                        icon: icono
                        });

                        markers.push(marker);
                        
                        checkInPolygon(marker,objeto_json.data[j].nombrecliente); 
                            
                        console.log('zonas',zona.length);


                        var total_zonas = numero1+numero2+numero3+numero4+numero5+numero6+numero7+numero8+numero9+numero10;
                        $('#total_1').html(numero1+' Clientes.');
                        $('#total_2').html(numero2+' Clientes.');
                        $('#total_3').html(numero3+' Clientes.');
                        $('#total_4').html(numero4+' Clientes.');
                        $('#total_5').html(numero5+' Clientes.');
                        $('#total_6').html(numero6+' Clientes.');
                        $('#total_7').html(numero7+' Clientes.');
                        $('#total_8').html(numero8+' Clientes.');
                        $('#total_9').html(numero9+' Clientes.');
                        $('#total_10').html(numero10+' Clientes.');
                        $('#total_zonas').html(total_zonas+' Clientes.');
                    
                }

            }
            }
      
            var url = "Mapas/php/datos_zonas.php";
            xhttp.open("POST", url, true);
            xhttp.send(); 
               
           }//success
      });

    
    };//INIT MAP

    /** Show the new coordinates for the rectangle in an info window. */
    function showNewRect() {
                  
      const ne = rectangle.getBounds().getNorthEast();
      const sw = rectangle.getBounds().getSouthWest();
      const contentString =
        "<b>"+zona+"<b><br>" +
        "New north-east corner: " +
        ne.lat() +
        ", " +
        ne.lng() +
        "<br>" +
        "New south-west corner: " +
        sw.lat() +
        ", " +
        sw.lng();
      // Set the info window's content and position.
      infoWindow.setContent(contentString);
      infoWindow.setPosition(ne);
      infoWindow.open(map);
      $.ajax({
          data: {'zona':zona,'Subir':1,'nelat':ne.lat(),'nelng':ne.lng(),'swlat':sw.lat(),'swlng':sw.lng(),'rec':selected},
          url:'Mapas/php/zonas.php',
          type:'post',
          success: function(response)
           {
              var jsonData = JSON.parse(response);
    
             if(jsonData.success==1){
             
               $('#cantidad').html(jsonData.Total + ' Servicios dentro de ' + zona);
    
             }
           }
      });
    }
    
    // $('#agregarzonas').click(function(){
    // var nombrezona=$('#nombrezona').val();
    // $.ajax({
    //       data: {'AgregarZona':1,'nombrezona':nombrezona},
    //       url:'Mapas/php/zonas.php',
    //       type:'post',
    //       success: function(response)
    //        {
    //       $('#zona-modal').modal('hide');
    //       $.NotificationApp.send("Exito !","Se agrego la Zona.!","bottom-right","#FFFFFF","success");       
    //        }
    //     });
      
    // });
    // var zonas_selected=[];
    
    // $('.btn-dark').click(function(){
    //     var id = $(this).attr('id');
    //     zonas_selected.push(id);    
    //     var filtro=filterArray(zonas_selected); 
        
    //     // initMap(filtro);
    //     zona=filtro;

    //     $('.header-title').html('Zonas google Maps '+zona);  
        
    // });

    // $('.card-header').click(function(){
    // var id = $(this).attr('id');
    // initMap(id);
    //   zona=id;
    // $('.header-title').html('Zonas google Maps '+zona);  
    // });
    
    $('#cambiar_recorrido').click(function(){
      $('#renderizar-modal').modal('show');
    $.ajax({
            data:{'BuscarRecorridos':1},
            type: "POST",
            url: "Proceso/php/pendientes.php",
            success: function(response)
            {
            $('.selector-recorrido select').html(response).fadeIn();
            }
        });
    });
    
    $('#renderizar_ok').click(function(){
    var recnew=$('#recorrido_t').val();
    // alert(selected+' '+zona+' '+recnew);  
    $.ajax({
            data:{'CambiarRecorridos':1,'Recnew':recnew,'Zona':zona,'Recorridos':selected},
            type: "POST",
            url: "Mapas/php/zonas.php",
            beforeSend: function () {
            $('#renderizar-modal').modal('hide');  
            $("#info-alert-modal").modal('show');
           },
            success: function(response)
            {
             var jsonData = JSON.parse(response);
              if(jsonData.success==1){
              console.log('veamos',jsonData.exito);  
              $("#info-alert-modal").modal('hide');  
              $.NotificationApp.send("Exito !","Se movieron "+jsonData.cuenta+" registros.!","bottom-right","#FFFFFF","success");         
              initMap();  
              }
            }
        });  
    });