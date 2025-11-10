    function Eliminar(nc){
      var dato={"id":nc};
      $.ajax({
      data: dato,
      url:'Procesos/Funciones.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {

           alertify.success("Elemnto Eliminado con Exito!"); 
           location.reload();
          }else{
           alertify.error("Elemnto No Eliminado!");  
          }
        }  
    });
   } 
