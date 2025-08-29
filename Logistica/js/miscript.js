$(document).ready(function(){
    //getdeails será nuestra función para enviar la solicitud ajax
    var getdetails = function(id){
        return $.getJSON( "js/personas.php", { "id" : id });
    }
    
    //al hacer click sobre cualquier elemento que tenga el atributo data-user.....
    $('[name=patente_t]').change(function(e){
//       alert(document.getElementById('patente').value);
      var dominio=document.getElementById('patente').value;
      //Detenemos el comportamiento normal del evento click sobre el elemento clicado
        e.preventDefault();
        //Mostramos texto de que la solicitud está en curso
//         $("#response-container").html("<p>Buscando...</p>");
        //this hace referencia al elemento que ha lanzado el evento click
        //con el método .data('user') obtenemos el valor del atributo data-user de dicho elemento y lo pasamos a la función getdetails definida anteriormente
        getdetails(dominio)
//         getdetails($(this).data('user'))
          .done( function( response ) {
//                 alert(response);
          //done() es ejecutada cuándo se recibe la respuesta del servidor. response es el objeto JSON recibido
            if( response.success ) {

//                 var output = "<h1>" + response.data.message + "</h1>";
              var calle;
              var numero;
              var ciudad;

              //recorremos cada usuario
                $.each(response.data.users, function( key, value ) {
//                     output += "<h2>Detalles del usuario " + value['ID'] + "</h2>";
                    //recorremos los valores de cada usuario
                    $.each( value, function ( userkey, uservalue) {
                      if(userkey=='Kilometros'){
                      calle=uservalue;  
                      }
                      if(userkey=='Numero'){
                      numero=uservalue;  
                      }
                     if(userkey=='Ciudad'){
                      ciudad=uservalue;  
                      }
//                                          
                    });
                });
                
                //Actualizamos el HTML del elemento con id="#response-container"
//                 $("#response-contai/ner").html(output);
//                 $("#startcalle").html(output);
//                 $("#startcalle").html(output);
                $('#km').val(calle);
//                 $('#startnumero').val(numero);
//                 $('#startciudad').val(ciudad);
              
   
                
                } else {
                //response.success no es true
                $("#response-container").html('No ha habido suerte: ' + response.data.message);
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $("#response-container").html("Algo ha fallado: " +  textStatus);
        });
    });
});        