$(document).ready(function(){

  $.ajax({
      data:{'Menu':1},
      url:'../MenuSmartphone/Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
          $('#retiros').html(jsonData.Retiros);  
          $('#entregas').html(jsonData.Entregas);  

            //           https://www.caddy.com.ar/SistemaTriangular/Logistica/Polizas/<? echo $NumeroComprobante;?>.pdf">Ver Poliza <? echo $Dato[Patente];?></a> 
          }
       }  
  });  
  
  
});