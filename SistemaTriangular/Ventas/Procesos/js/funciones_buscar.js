$('#buscarventa_buttom').click(function(){
var n=document.getElementById('buscarventa_input').value;  

  $.ajax({
      data:{'BuscarVenta':1,'cs':n},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
           console.log(jsonData); 
           $('#retiro_t').val(jsonData.data[0].Retirado);
           $('#id_origen').val(jsonData.data[0].RazonSocial); 
           $('#id_destino').val(jsonData.data[0].ClienteDestino);
           $('#codigocliente').val(jsonData.data[0].CodigoProveedor);
           $('#observaciones').val(jsonData.data[0].Observaciones);
           $('#entregaen_t').val(jsonData.data[0].EntregaEn);
           $('#formadepago_t').val(jsonData.data[0].FormaDePago);
           $('#cobranzadelenvio_t').val(jsonData.data[0].CobrarCaddy);
      $('#cobranzadelenvio_t').selectmenu('refresh', true);    
      $('#formadepago_t').selectmenu('refresh', true); 
      $("#retiro_t").selectmenu('refresh', true); 
      $("#entregaen_t").selectmenu('refresh', true);

       }
  });   
  $.ajax({
          data:{'cobro_a_cuenta':1,'cs':n},
          type: "POST",
          url: "Procesos/php/funciones.php",
          success: function(response)
          {
          var jsonData = JSON.parse(response);  
            if(jsonData.success==1){
            $("#cobroacuenta_input").val(jsonData.CobrarEnvio);  
            $("#customSwitch1").prop('checked', true);
            $('#cobroacuenta_input').prop('disabled', false);
            $('#cobroacuenta_button').prop('disabled', false);
            }
          }
      });


  $.ajax({
          data:{'BuscarRecorridos':1,'cs':n},
          type: "POST",
          url: "Procesos/php/funciones.php",
          success: function(response)
          {
          $('.selector-recorrido select').html(response).fadeIn();
          }
      });

var datatable = $('#basic').DataTable({
    paging: false,
    searching: false,
    ajax: {
         url:"Procesos/php/ventas-tablas.php",
         data:{'cs_cliente':n,'datos_buscar':1},
         type:'post'
         },
        columns: [
            {data:"Codigo"},
            {data:"Titulo"},
            {data:"Cantidad"},
            {data:"Precio"},
            {data:"Total"},
            {data:"Comentario"},
            {data:"idPedido",
            render: function (data, type, row) {
                  return "<td>"+
                         "<a class='action-icon' onclick='eliminar("+ row.idPedido +")'><i class='uil-trash-alt'></i></a>"+
                         "</td>"
                         }
                  }
                ]
});
var datatable1 = $('#basic-total').DataTable({
    paging: false,
    searching: false,
    bPaginate: false,
    bLengthChange: false,
    bFilter: false,
    bInfo: false,
    bAutoWidth: false,
    ajax: {
         url:"Procesos/php/ventas-tablas.php",
         data:{'cs_cliente':n,'totales_buscar':1},
         type:'post'
         },
        columns: [
            {data:"Neto"},
            {data:"Iva"},
            {data:"Total"},
                ]
  });
});

function calcularcantidad(total){
 var nuevototal=document.getElementById('precioventa').value*total; 
 document.getElementById('total').value=nuevototal; 
}

function cargar(id){
  var dato={"id_servicio":id};    
  $.ajax({
      data: dato,
      url:'Procesos/php/funciones.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
           document.getElementById('precioventa').value=jsonData.PrecioVenta;
           document.getElementById('codigo').value=jsonData.Codigo;
           var cantidad=document.getElementById('cantidad').value;
           document.getElementById('total').value=jsonData.PrecioVenta*cantidad; 
              if(jsonData.Codigo!=''){
              $("#subir").prop('disabled', false);
              }
           }
        }  
  });
}

$('#cobroacuenta_button').click(function(){
// ESTE DATO SE PUEDE PARAMETRIZAR DESDE CLIENTES POR DEFECTO 4%
// var idOrigen=document.getElementById('id_origen').value;  
if(document.getElementById('id_origen').value==null){
var idOrigen=document.getElementById('id_origen2').value;   
}else{
var idOrigen=document.getElementById('id_origen').value;   
}  
var dato='178';// ID DEL CODIGO DEL COBRO A CUENTA
var cantidad=1;  
var importe=document.getElementById('cobroacuenta_input').value;  
var comentario='COBRANZA INTEGRADA ($ '+importe+')';  
var nventa=document.getElementById('nventa').value;
  var seguimiento=document.getElementById('seguimiento').value;
$.ajax({
      data:{'id':dato,'cantidad':cantidad,'Comentario':comentario,'importe':importe,'cargarcobroacuenta':1,'idOrigen':idOrigen,'nventa':nventa,'seguimiento':seguimiento},
      url:'Procesos/php/AgregarVenta.php',
      type:'post',
        beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
            var table = $('#basic').DataTable();
            table.ajax.reload();
            var tabletotales = $('#basic-total').DataTable();
            tabletotales.ajax.reload();

          }else if(jsonData.success == "2"){
           $.NotificationApp.send("Error","No seleccionaste ningún cliente origen ","bottom-right","#FFFFFF","error"); 

          }else{
           $.NotificationApp.send("Error","No seleccionaste ningún servicio ","bottom-right","#FFFFFF","error"); 
           
          }
       }  
  });
});



$('#valordeclarado_button').click(function(){
// ESTE DATO SE PUEDE PARAMETRIZAR DESDE CLIENTES POR DEFECTO 4%
if(document.getElementById('id_origen').value==null){
var idOrigen=document.getElementById('id_origen2').value;   
}else{
var idOrigen=document.getElementById('id_origen').value;   
}
  
var dato='177';// ID DEL CODIGO DE VALOR DECLARADO
var cantidad=1;  
var importe=document.getElementById('valordeclarado_input').value;  
var comentario='SEGURO SEGUN VALOR DECLARADO ($ '+importe+')';  
var nventa=document.getElementById('nventa').value;
  var seguimiento=document.getElementById('seguimiento').value;
$.ajax({
      data:{'id':dato,'cantidad':cantidad,'Comentario':comentario,'importe':importe,'valordeclarado':1,'idOrigen':idOrigen,'nventa':nventa,'seguimiento':seguimiento},
      url:'Procesos/php/AgregarVenta.php',
      type:'post',
        beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
            var table = $('#basic').DataTable();
            table.ajax.reload();
            
            var tabletotales = $('#basic-total').DataTable();
            tabletotales.ajax.reload();

          }else if(jsonData.success == "2"){
           $.NotificationApp.send("Error","No seleccionaste ningún cliente origen ","bottom-right","#FFFFFF","error"); 
          }else if(jsonData.success == "3"){
           $.NotificationApp.send("Error","El importe del Valor Declarado debe ser mayor a $ 5000","bottom-right","#FFFFFF","error"); 
          }else{
           $.NotificationApp.send("Error","No seleccionaste ningún servicio ","bottom-right","#FFFFFF","error"); 
           
          }
       }  
  });
});

function cobroacuenta(a){
  if ($('[name="my-checkbox"]').is(':checked')){ 
  $('#cobroacuenta_input').prop('disabled', false);
  $('#cobroacuenta_button').prop('disabled', false);
  }else{ 
  $('#cobroacuenta_button').prop('disabled', true);
  $('#cobroacuenta_input').prop('disabled', true);
  $('#cobroacuenta_input').val('');    

  }
}
function valordeclarado(a){
  if ($('[name="my-checkbox2"]').is(':checked')){ 
  $('#valordeclarado_input').prop('disabled', false);
  $('#valordeclarado_button').prop('disabled', false);
  }else{ 
  $('#valordeclarado_button').prop('disabled', true);
  $('#valordeclarado_input').prop('disabled', true);
  $('#valordeclarado_input').val('');    

  }
}

function subir(){
var dato=document.getElementById('codigo').value;
if(dato==0){
$.NotificationApp.send("Error","No seleccionaste ningún servicio ","bottom-right","#FFFFFF","error");
document.getElementById('servicio').style.background='red';
}else{
  if(document.getElementById('id_origen').value==null){
  var idOrigen=document.getElementById('id_origen2').value;   
  }else{
  var idOrigen=document.getElementById('id_origen').value;   
  }  
// var idOrigen=document.getElementById('id_origen').value;
var cantidad=document.getElementById('cantidad').value;  
var comentario=document.getElementById('comentario').value;  
var importe=document.getElementById('precioventa').value; 
var nventa=document.getElementById('nventa').value;
var seguimiento=document.getElementById('seguimiento').value;
  
  
$.ajax({
      data:{'subir':1,'id':dato,'cantidad':cantidad,'Comentario':comentario,'importe':importe,'idOrigen':idOrigen,'nventa':nventa,'seguimiento':seguimiento},
      url:'Procesos/php/AgregarVenta.php',
      type:'post',
        beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
            var table = $('#basic').DataTable();
            table.ajax.reload();
            
            var tabletotales = $('#basic-total').DataTable();
            tabletotales.ajax.reload();

          }else{
           $.NotificationApp.send("Error","No seleccionaste ningún servicio ","bottom-right","#FFFFFF","error"); 
           
          }
       }  
  });
}
}




function calculo(a){
var cantidad= document.getElementById('cantidad').value;
document.getElementById('total').value=cantidad*a;
}

function eliminar(a){
// var idOrigen=document.getElementById('id_origen').value;  
if(document.getElementById('id_origen').value==null){
var idOrigen=document.getElementById('id_origen2').value;   
}else{
var idOrigen=document.getElementById('id_origen').value;   
}
  $.ajax({
      data:{'id':a,'Eliminar':1,'id_origen':idOrigen},
      url:'Procesos/php/AgregarVenta.php',
      type:'post',
        beforeSend: function(){
//           document.getElementById("spinner").style.display="block";
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
            var tabletotales = $('#basic-total').DataTable();
            tabletotales.ajax.reload();
            
            var table = $('#basic').DataTable();
            table.ajax.reload();

            $.NotificationApp.send("Listo!","Eliminaste el registro correctamente ","bottom-right","#FFFFFF","success"); 
            
          }
       }  
  });
}
