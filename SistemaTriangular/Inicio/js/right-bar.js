function right(r,o){
$('.m-0').html('Recorrido '+r+' Numero de Orden: '+o);
 $.ajax({
      data:{'Right-bar':1,'Recorrido':r,'Orden':o},
      url:'https://www.caddy.com.ar/SistemaTriangular/Inicio/php/right-bar.php',
      type:'post',
        beforeSend: function(){

        },
      success: function(response)
       {
           console.log('dato',response);
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
            $('.logistica-titulo-1').html('Salida: '+jsonData.Fecha+' | '+jsonData.HoraSalida);
            $('.logistica-titulo-2').html('Chofer: '+r);
            $('.logistica-titulo-3').html('Vehiculo: '+r);

            $('.retiros-titulo-1').html('Total a Retirar: '+jsonData.Retiros);
            $('.retiros-titulo-2').html('Retirados: '+jsonData.Retirado);
            $('.retiros-titulo-3').html('Retiros Fallidos: '+jsonData.NoRetirado);
            $('.retiros-titulo-4').html('Pendientes:  '+r);

            $('.entregas-titulo-1').html('Total a Entregar: '+jsonData.Entregas);
            $('.entregas-titulo-2').html('Entregados: '+jsonData.Entregado);
            $('.entregas-titulo-3').html('Entregas Fallidas: '+r);
            $('.entregas-titulo-4').html('Pendientes:  '+r);

          }else{
              alert('err');
          }
       }  
  });


}
