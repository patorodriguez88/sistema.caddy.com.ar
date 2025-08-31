$(document).ready(function() {
    
    // getdeails será nuestra función para enviar la solicitud ajax    
    var getdetails = function(ide) {
        
        return $.getJSON("entregas.php", {
            
            "ide": ide
            
        });
        
    }
    
    // Al hacer click sobre cualquier elemento que tenga el atributo data-user.....
    $('[data-user]').click(function(e) {
        
        // Detenemos el comportamiento normal del evento click sobre el elemento clicado
        e.preventDefault();
        
        // Mostramos texto de que la solicitud está en curso
        $("#lista-container").html("<p>Buscando...</p>");
        
        // this hace referencia al elemento que ha lanzado el evento click
        // con el método .data('user') obtenemos el valor del atributo data-user de dicho elemento y lo pasamos a la función getdetails definida anteriormente
        getdetails( $(this).data('user') )
        
        .done(function(response) {
            
            //done() es ejecutada cuándo se recibe la respuesta del servidor. response es el objeto JSON recibido
            if (response.success) {
                
                var output = "<h1>" + response.data.message + "</h1>";
                
                // recorremos cada usuario
                $.each(response.data.users, function(key, value) {
                    
                    output += "<h2>Listado de Entregas " + value['id'] + "</h2>";
                    
                    // recorremos los valores de cada usuario
                    $.each(value, function(userkey, uservalue) {
                    document.getElementById('lista-container').style.display='block';
//                     document.getElementById('listado').style.display='none';  
  
//                         output += '<ul>';
//                         output += '<li>' + userkey + ': ' + uservalue + "</li>";
//                         output += '</ul>';
                     if ( userkey =='NumeroComprobante'){
                       output += '<h2>Numero de Remito: '+ uservalue +'</h2>';  
                     }
//                         output += "<h2>Numero de Remito: $row[NumeroComprobante]</h2>";	
//                         output += "<h2>Codigo de Seguimiento: $row[CodigoSeguimiento]</h2>";	
//                         output += "<h2>Localizacion: $row[DomicilioDestino]</h2>";
//                         output += "<a href='http://maps.google.com/?q=$row[DomicilioDestino]', '_system', 'location=yes');'>Abrir Mapa</a>";
//                         output += "<h2>Cliente: ($idProveedor[idProveedor]) $row[ClienteDestino]</h2>";
//                         output += "<h2>Cantidad de Bultos: $row[Cantidad]</h2>";

                      
                      
                      
                      
                    });
                    
                });
                
                // Actualizamos el HTML del elemento con id="#response-container"
                $("#lista-container").html(output);
                
                } else {
                
                //response.success no es true
                $("#lista-container").html('No ha habido suerte: ' + response.data.message);
                
            }
            
        })
        
        .fail(function(jqXHR, textStatus, errorThrown) {
            
            $("#lista-container").html("Algo ha fallado: " + textStatus);
            
        });
        
    });
    
});