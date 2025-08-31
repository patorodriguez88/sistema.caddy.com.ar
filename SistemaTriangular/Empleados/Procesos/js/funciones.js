function veo(a){

  $.ajax({
    data:{'NuevoUsuario':1,'id':a},
    url:'Procesos/php/funciones.php',
    type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
  success: function(response)
   {
      var jsonData = JSON.parse(response);
      $('#nombre_t').val(jsonData.data[0].NombreCompleto);
      $('#nuevopass_t').val(jsonData.data[0].Dni);
      $('#confnuevopass_t').val(jsonData.data[0].Dni);
      $('#id_empleado').val(jsonData.data[0].id);
   }
  });

  
//   $('#nombre_t').val();  
}
$('#CrearPass').click(function(){
     var nuevopass = $('#nuevopass_t').val();
     var usuario = $('#usuario_t').val();
     var nombre= $('#nombre_t').val();
     var nivel =$('#nivel_t').val();
     var idempleado=$('#id_empleado').val();
     

$.ajax({
    data:{'CrearPass':1,'nuevopass_t':nuevopass,'usuario_t':usuario,'nombre_t':nombre,'nivel_t':nivel,'id_empleado':idempleado},
    url:'Procesos/php/funciones.php',
    type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
  success: function(response)
   {
      var jsonData = JSON.parse(response);
      $('#nombre_t').val(jsonData.data[0].NombreCompleto);
      $('#nuevopass_t').val(jsonData.data[0].Dni);
      $('#confnuevopass_t').val(jsonData.data[0].Dni);
   }
  });

  
});