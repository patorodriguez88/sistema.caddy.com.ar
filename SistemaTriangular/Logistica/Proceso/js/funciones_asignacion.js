$.ajax({
      data: {'Buscar':1,'zona':z},
      url:'Mapas/php/zonas.php',
      type:'post',
      success: function(response)
       {
         var jsonData = JSON.parse(response);
         const bounds = {
          north:Number(jsonData.LatitudN),
          south:Number(jsonData.LatitudS),
          east: Number(jsonData.LongitudE),
          west: Number(jsonData.LongitudO),
       }
     }
});