
function mail_status_notice(cs,st){
    //cs= Codigo de Seguimiento
    //st= ?
console.log('cs',cs);    
$.ajax({
    data:{'Avisos':1,'cs':cs,'st':st},
    url:'https://www.caddy.com.ar/SistemaTriangular/Mail/Proceso/php/notices.php',
    type:'post',
    success: function(response)
     {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1")
        {
            console.log('ver',jsonData);
        // var user=jsonData.mail;    
        var mensaje = ', Queremos avisarte que el envío '+cs+' que nos diste para entregar a '+jsonData.destination_name+' se encuentra '+st+'.';
        var asunto='Tu Envio de Caddy !';
        var html='Recupero';
        var name=jsonData.name; 
        var user=jsonData.mail; 
        $.ajax({
            data:{'txtEmail':user,'txtName':name,'txtAsunto':asunto,'txtMensa':mensaje,'$txtHtml':html},
            url:'https://www.caddy.com.ar/SistemaTriangular/Mail/delivered.php',
            type:'post',
            success: function(response1)
             {
             var jsonData1 = JSON.parse(response1);
            if (jsonData1.success == "1")
            {  
                console.log('mail','enviado');
             }else{
             alert(jsonData1.error);
             }
           }
        });          
        }
     }  
  });
  //DESTINO
  $.ajax({
    data:{'Avisos':2,'cs':cs,'st':st},
    url:'https://www.caddy.com.ar/SistemaTriangular/Mail/Proceso/php/notices.php',
    type:'post',
    success: function(response)
     {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1")
        {
            console.log('ver',jsonData);
        // var user=jsonData.mail;    
        var mensaje = ', recibiste tu pedido !.';
        if(st=='Entregado al Cliente'){
            var mensaje = ', Recibiste tu envío '+cs+' de '+jsonData.origen_name+' !.';    
        }else{
            var mensaje = ', Queremos avisarte que el envío '+cs+' de '+jsonData.origen_name+' esta '+st;
        }

        var asunto='Tu Envio de Caddy !';
        var html='Recupero';
        var name=jsonData.name; 
        var user=jsonData.mail; 
        $.ajax({
            data:{'txtEmail':user,'txtName':name,'txtAsunto':asunto,'txtMensa':mensaje,'$txtHtml':html},
            url:'https://www.caddy.com.ar/SistemaTriangular/Mail/delivered.php',
            type:'post',
            success: function(response1)
             {
             var jsonData1 = JSON.parse(response1);
            if (jsonData1.success == "1")
            {  
                console.log('mail','enviado');
             }else{
             alert(jsonData1.error);
             }
           }
        });          
        }
     }  
  });  
};