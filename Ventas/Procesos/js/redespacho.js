$('#button-redespacho').click(function(){
  var codigo=$("#seguimiento").html();  
  var Destino=$('#destino_ok').html();
 var Redespacho=$('#redespacho').html();

 if(Redespacho!=''){
  $('#aceptar_redespacho').hide();  
 }
 
 if(codigo==''){
   
    $('#redespacho-modal').modal('hide');  
    $("#warning-redespacho-modal").modal('show');
    $('#warning-redespacho-text').html('Falta el Codigo de Seguimiento');
   
    }else if(Destino==''){
    $('#redespacho-modal').modal('hide');  
    $("#warning-redespacho-modal").modal('show');
    $('#warning-redespacho-text').html('Falta el Destino');
    }else{ 
    $('#redespacho-modal').modal('show');
    }
});

$('#redespacho-modal').on('shown.bs.modal',function(e){
  var codigo=$("#seguimiento").html();  
  var Destino=$('#destino_ok').html();
    var f = new Date();
    var Fecha=f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear();
    $('#fecha_redespacho').val(Fecha);
    $('#codigoseguimiento_redespacho').val(codigo);
    $('#destino_redespacho').val(Destino);
});

//CANCELAR REDESPACHO
$('#cancelar_redespacho').click(function(){
  $('#redespacho-modal').modal('hide');  
});

//ACEPTAR REDESPACHO
$('#aceptar_redespacho').click(function(){

var Fecha = new Date();
var idProveedor=$('#proveedor_redespacho').val();
var Destino=$('#destino_redespacho').val();
var Observaciones=$('#observaciones_redespacho').val();
var CodigoSeguimiento=$('#codigoseguimiento_redespacho').val();
var CodigoRedespacho=$('#codigo_redespacho').val();
var Importe=$('#importe_redespacho').val();
var idTransClientes=$('#idtransclientes_redespacho').val();
var nventa=$('#nventa').html();

console.log('nventa',nventa);
if(document.getElementById('id_origen').value==null){
  var idOrigen=document.getElementById('id_origen2').value;   
  }else{
  var idOrigen=document.getElementById('id_origen').value;   
  }
  
$.ajax({
    data:{'Redespacho':1,'NumeroRepo':nventa,'idOrigen':idOrigen,'Fecha':Fecha,'idProveedor':idProveedor,'Destino':Destino,'Observaciones':Observaciones,
    'CodigoSeguimiento':CodigoSeguimiento,'CodigoRedespacho':CodigoRedespacho,'Importe':Importe,'idTransClientes':idTransClientes},
    url:'Procesos/php/redespacho.php',
    type:'post',
      beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
      },
    success: function(response)
     {
        var jsonData = JSON.parse(response);
        if (jsonData.success == "1")
        {
        $('#redespacho-modal').modal('hide');  
          var tabletotales = $('#basic-total').DataTable();
          tabletotales.ajax.reload();
          
          var table = $('#basic').DataTable();
          table.ajax.reload();

          $.NotificationApp.send("Listo!","Redespacho cargado correctamente ","bottom-right","#FFFFFF","success"); 
          $('#redespacho').html('Redespacho');
          $('#button-redespacho').removeClass( "btn btn-secondary text-white" ).addClass( "btn btn-warning text-white" );           
          $('#redespacho_nc').val(1);
        }
     }  
  });
});