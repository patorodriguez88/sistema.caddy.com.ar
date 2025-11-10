$(document).ready(function(){
  $.ajax({
      data:{'Buscar':1},
      url:'Procesos/php/funciones.php',
      type:'post',
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.success == "1")
          {
//           $('#nombrecliente').html(jsonData.Nombre);
//            $('#direccioncliente').html(jsonData.Direccion); 
           $('#title').html('Recorrido '+jsonData.Recorrido);

          }
       }  
  });   
return false;
});

function muestroboton(){
document.getElementById('Subir').style.display='block';  

 var codigo =document.getElementsByName('cs').value;
//  var titulo=document.getElementsByName('titulo[]');
//  var edicion=document.getElementsByName('edicion[]');
//  var precio=document.getElementsByName('precio[]');
//  var cantidad=document.getElementsByName('cantidad[]');
 var total= 100;

  $("#cuerpo").html("");
  
          $.ajax({
            url:'Procesos/subirfoto.php',
            type: 'post',
            data: {'cs':codigo,'buscarfotos':1},
            contentType: false,
            processData: false,
            success: function(response) {
                if (response != 0) {
                } else {
                alert('Formato de imagen incorrecto.');
                }
            }
        });

  
  
//   var totalventa=0;
//     for(var i=0; i<=total; i++){
//       if (cantidad[i].value != 0){
//         var Suma=cantidad[i].value*precio[i].value;
//         var conDecimal = Suma.toFixed(2);
//         var totalventa = (totalventa)+(cantidad[i].value*precio[i].value);
//         var totalventaf = totalventa.toFixed(2);
//        document.getElementById('totalventa').value = totalventa;  
//        document.getElementById('totalfinal').value = totalventa;  
   
//         var tr = `<tr>
//         <td>`+codigo[i].value+`</td>
//         <td>`+titulo[i].value+`</td>
//         <td>`+edicion[i].value+`</td>
//         <td>$ `+precio[i].value+`</td>
//         <td>`+cantidad[i].value+`</td>
//         <td>$ `+conDecimal+`</td>
//         </tr>`;
//         $("#cuerpo").append(tr);
//       }
//     }
  }

  $('#startbutton').click(function(){
  document.getElementById('camfirmbut').style.display='none';  
  
  document.getElementById('firma_d').style.display='block';
  document.getElementById('nombre').style.display='none';
  document.getElementById('dni').style.display='none';
//   document.getElementById('image').style.display='none';
  document.getElementById('observaciones').style.display='none';
//   document.getElementById('startbutton').style.display='none';
  document.getElementById('botonfinal').style.display='none';
    
//   document.getElementById('Subir').style.display='none';
// alert('hola');
  });
function firmaof(){
document.getElementById('firma_d').style.display='none';
  document.getElementById('nombre').style.display='block';
  document.getElementById('dni').style.display='block';
  document.getElementById('image').style.display='block';
  document.getElementById('observaciones').style.display='block';
  document.getElementById('startbutton').style.display='block';
  document.getElementById('botonfinal').style.display='block';
}
$('#endbutton').click(function(a,b,c){ 
document.getElementById('firma_d').style.display='none';
  document.getElementById('nombre').style.display='block';
  document.getElementById('dni').style.display='block';
  document.getElementById('image').style.display='block';
  document.getElementById('observaciones').style.display='block';
  document.getElementById('startbutton').style.display='block';
  document.getElementById('botonfinal').style.display='block';
});
function subirfoto(){  
        Dropzone.options.myAwesomeDropzone = {
          paramName: "files", // The name that will be used to transfer the file
          maxFilesize: 2, // MB
          accept: function(file, done) {
//             if (file.name == "justinbieber.jpg") {
//               done("Naha, you don't.");
//             }
//             else { done(); }
          }
        };
        var cs=document.getElementById('cs').value;

        var formData = new FormData();
        
        var files = $('#image')[0].files[0];
        formData.append('file',files);
        formData.append('cs',cs);
  
        $.ajax({
            url:'Procesos/subirfoto.php',
            type: 'post',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response != 0) {
                alert('Foto Subida con Exito!');  
                } else {
                alert('Formato de imagen incorrecto.');
                }
            }
        });
        return false;
    }
function finalizado(){
  
  var retirado=document.getElementById('retirado_t').value;
  var cs=document.getElementById('cs').value;
  var estado=document.getElementById('entregado_t').value;
  var nombre=document.getElementById('nombrecliente_t').value;
  var observaciones=document.getElementById('observaciones').value;
  var output=document.getElementById('output').value;
  var razones=document.getElementById('razones').value;
  var dni=document.getElementById('dni').value;
  var nombre2=document.getElementById('nombre').value;
  var dato={
      "retirado_t":retirado,
      "codigoseguimiento_t":cs, 
      "entregado_t":estado,
      "nombre_t":nombre,
      "observaciones_t":observaciones,
      "output":output,
      "razones":razones,
      "dni":dni,
      "nombre2":nombre2
      };
      $.ajax({
      data: dato,
      url:'Procesos/estadoenvio1.php',
      type:'post',
      contentType: false,
      processData: false,
      beforeSend: function(){
  
        $("#procesando").show();
        },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.resultado == "1")
          {
          var i = document.getElementById('idveo').value;
          document.getElementById('okcobrar').style.display='none';   
          document.getElementById('ok').style.display='none'; 
          document.getElementById('todos').style.display='block';
          document.getElementById(i).style.display='none';    
          }
          else if(jsonData.resultado == '2')
          {
          alert('Paquete ya Entregado');
          }
        }
 });
}
  function verok(id,i,p){
    var idveo=i;
//     var cs=document.getElementById('codigoseguimiento_t').value;
//     document.getElementById('okcobrar').style.display='none'; 
    document.getElementById('todos').style.display='none';  
    var dato={
        "id": id,  
        };
        $.ajax({
        data: dato,
        url:'Procesos/php/buscodatos.php',
        type:'post',
        beforeSend: function(){
//         $("#buscando").html("Buscando...");
        },
            success: function(response)
            {
                var jsonData = JSON.parse(response);
                if (jsonData.success == "1")
                {

//                   if(jsonData.ImporteCobro!=0 || jsonData.Importecobrocaddy!=0){
//                   alert('Envio Cobrado'+jsonData.EnvioCobrado);
//                   alert('Importe Cobro'+jsonData.ImporteCobro);
//                   alert('Importe cobro caddy'+jsonData.Importecobrocaddy);
                  if((jsonData.EnvioCobrado==0)&&(jsonData.ImporteCobro!=0 || jsonData.Importecobrocaddy!=0)){
                  document.getElementById('okcobrar').style.display='block';
                  var importetotal= Number(jsonData.ImporteCobro)+Number(jsonData.Importecobrocaddy);
                  document.getElementById('cscobro').value=jsonData.Seguimiento;   
                  $("#importecobro").html('El cliente pidio que cobremos: $'+jsonData.ImporteCobro); 
                  $("#importecobrocaddy").html('Costo Caddy Envios: $'+jsonData.Importecobrocaddy);
                  $("#importecobrototal").html('Total a Cobrar: $'+importetotal); 
                  document.getElementById('importecobrocliente_m').value=jsonData.ImporteCobro;
                  document.getElementById('importecobrocaddy_m').value=jsonData.Importecobrocaddy;
                  }else{
                  document.getElementById('ok').style.display='block';  
                  $("#nombrecliente").html(jsonData.Nombre);  
                  $("#posicion").html(jsonData.Posicion); 
                  $("#domicilio").html(jsonData.Domicilio); 
                  document.getElementById('idveo').value=idveo; 
                  document.getElementById('cs').value= jsonData.Seguimiento;   
                  if(p=='botonera1'){
                    document.getElementById('nombre').style.display='none';
                    document.getElementById('dni').style.display='none';

                    document.getElementById('botones').style.display='block'; 
                    document.getElementById('firma').style.display='none';
                    document.getElementById('botonfinal').style.background='red';
                    document.getElementById('botonfinal').style.border='red';
                    document.getElementById('botonfinal').value='MARCAR COMO CANCELADO';  
                    document.getElementById('entregado_t').value='0';
                    }else{
                      if(jsonData.ImporteCobro!=0){
                        $("#importecobro").html('ATENCION: El cliente pidio que cobremos: $'+jsonData.ImporteCobro);   
                        }else{
                        document.getElementById('importecobro').style.display='none';                   
                      }  
                    document.getElementById('botones').style.display='none';                    
                    document.getElementById('firma').style.display='block'; 
                    document.getElementById('botonfinal').style.background='green';
                    document.getElementById('botonfinal').style.border='green';
                    document.getElementById('retirado_t').value=jsonData.Retirado;  
                    if(jsonData.Retirado=="1"){
                      document.getElementById('botonfinal').value='MARCAR COMO ENTREGADO';
                      }else{
                      document.getElementById('botonfinal').value='MARCAR COMO RETIRADO';  
                      }
                      document.getElementById('entregado_t').value='1';
                    }  
                  }
                }else{
                alert('Invalid Credentials s s!');
                }
              }
            
      }); 
    }
function cerrar(){
  document.getElementById('ok').style.display='none'; 
  document.getElementById('todos').style.display='block';   
  document.getElementById('okcobrar').style.display='none'; 
  //   document.getElementById('veo').style.display='block'; 
}
function mostrar(){
  var codigoseguimiento=document.getElementById('remito').value;
  alert(codigoseguimiento);
  if(document.getElementById(codigoseguimiento).style.display=='block'){
  document.getElementById(codigoseguimiento).style.display='none';
  }else{
  document.getElementById(codigoseguimiento).style.display='block';
  }
}
    function verboton(){
    document.getElementById('veo').style.display='block';
    document.getElementById('listado').style.display='none';  
    }
function cobrofinalizado(cobro){
  var cscobro=document.getElementById('cscobro').value;
  var emailrecibo=document.getElementById('emailrecibo').value; 
  var impcliente=document.getElementById('importecobrocliente_m').value;
  var impcaddy=document.getElementById('importecobrocaddy_m').value;

  var dato={
      "cobro":cobro,
      "codigoseguimiento_t":cscobro,
      "emailrecibo":emailrecibo,
      "impcaddy":impcaddy,
      "impcliente":impcliente,
            };
      $.ajax({
      data: dato,
      url:'Procesos/cobro.php',
      type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
      success: function(response)
       {
          var jsonData = JSON.parse(response);
          if (jsonData.resultado == "1")
          {
          alert('Cobrado Ok! Ahora podés volver al cliente y marcar la entrega.');
          document.getElementById('okcobrar').style.display='none';
          document.getElementById('todos').style.display='block';  
          }
         if (jsonData.resultado == "0")
           {
          alert('No pudiste cobrar? Avisar en administracion!');
             
           }
         
        }
 });
}
  function select(i){
    event.preventDefault();
    if (i=='razones1'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)';
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones2').style.background='rgb(90, 107, 134)';
    document.getElementById('razones2').style.color='white';
    document.getElementById('razones3').style.background='rgb(90, 107, 134)';
    document.getElementById('razones3').style.color='white';
    document.getElementById('razones').value='NADIE EN CASA';
    }else if(i=='razones2'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)'; 
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones1').style.background='rgb(90, 107, 134)';
    document.getElementById('razones1').style.color='white';
    document.getElementById('razones3').style.background='rgb(90, 107, 134)';
    document.getElementById('razones3').style.color='white';
    document.getElementById('razones').value='DIRECCION EQUIVOCADA';
    }else if(i=='razones3'){
    document.getElementById(i).style.background='white';
    document.getElementById(i).style.color='rgb(90, 107, 134)';  
    document.getElementById(i).style.border='rgb(90, 107, 134)';  
    document.getElementById('razones1').style.background='rgb(90, 107, 134)';
    document.getElementById('razones1').style.color='white';
    document.getElementById('razones2').style.background='rgb(90, 107, 134)';
    document.getElementById('razones2').style.color='white';
    document.getElementById('razones').value='OTRAS RAZONES';
      }
  }