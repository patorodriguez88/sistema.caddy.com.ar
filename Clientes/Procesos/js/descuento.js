$('#confirmardescuento_botton').click(function(){
  //Creamos un array que almacenará los valores de los input "checked"
  var checked = [];
  //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
  $("input.custom-control-input:checked").each(function ()
  {
    if($(this).attr("value")!=null){
  //Mediante la función push agregamos al arreglo los values de los checkbox
    checked.push(($(this).attr("value")));
    }
  });

  var descuento=document.getElementById("descuentootorgado_t").value;

  if (window.confirm("Seguro desea aplicar el descuento de "+descuento+" % ?")){
  $.ajax({
      data:{'Descontar':1,'Descuento':descuento,'Remitos':checked},
      url:'Procesos/php/descuento.php',
      type:'post',
      success: function(response)
       {
        var desc = Number($('#factura_descuento').html())+Number(descuento); 
        $('#factura_descuento').html(desc);  
        $('#descuento-modal').modal('hide');
         
      var tabla_facturacion_proforma= $('#tabla_facturacion_proforma').DataTable();
      tabla_facturacion_proforma.ajax.reload();
      $.NotificationApp.send("Aplicaste el desccuento en los remitos seleccionados !","Se han realizado cambios.","bottom-right","#FFFFFF","warning");   
      }  
  });   
}else{
	alertify.success("No se aplico el descuento.", "", 0);
  }
});
  
