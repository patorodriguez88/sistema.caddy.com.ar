function writeToSelectedPrinter(id){

$.ajax({
    data:{'Rotulo':1,'cs':id},
    type: "POST",
    url: "https://www.caddy.com.ar/SistemaTriangular/Ticket/Procesos/php/datos.php",
    success: function(response)
    {
    var jsonData = JSON.parse(response); 
     console.log('ver',response);

          }
});
}