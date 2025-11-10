     $.ajax({
      data:{'Api':1},
      url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/api.php',
      type:'post',
        beforeSend: function(){
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);

          if (jsonData.success == "1")
          {
              $('#link').val(jsonData.dato[0].Link);
              $('#usuario').val(jsonData.dato[0].User);
              $('#password').val(jsonData.dato[0].Password);
          }
        }
    });

    $('#modificar').click(function(){
    
    var link = $('#link').val();
    var user = $('#usuario').val();
    var pass = $('#password').val();    

    $.ajax({
      data:{'ModificarApi':1,'link':link,'user':user,'pass':pass},
      url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/api.php',
      type:'post',
        beforeSend: function(){
        },
        success: function(response)
       {
          var jsonData = JSON.parse(response);

          if (jsonData.success == "1")
          {
              $('#link').val(jsonData.dato[0].Link);
              $('#usuario').val(jsonData.dato[0].User);
              $('#password').val(jsonData.dato[0].Password);
          }
        }
    });
        
    });
