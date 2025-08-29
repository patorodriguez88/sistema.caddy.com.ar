function selecdescuento(){
document.getElementById("desc_id").style.display='block';
document.getElementById("desc_botom").style.display='block';
}

function Aplicar_Descuento(){    
var descuento=document.getElementById("descuentootorgado_t").value;
if (window.confirm("Seguro desea aplicar el descuento de "+descuento+" % ?")){
  

  $.ajax({
      data:{'Descontar':1,'Descuento':descuento},
      url:'Procesos/php/descuento.php',
      type:'post',
      success: function(response)
       {

       }  
  });   
  window.location.reload();  
//   alertify.success("Registros Actualizados! ", "", 0);  
}else{
	alertify.success("No se aplico el descuento.", "", 0);
  }
}
  
