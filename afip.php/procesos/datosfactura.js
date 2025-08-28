$("#facturar_boton").click(function(){
//   alert('vamos a facturar!');
var dato={'TipoDeDocumento':99,
          'Documento':0,
          'ImpTotal':121,
          'ImpTotalConc':0,
          'ImpNeto':100,
          'ImpIVA':21,
          'ImpTrib':0};
  
  $.ajax({
        data: dato,
        url:'../afip.php/procesos/CreateVoucher.php',
        type:'post',
//         beforeSend: function(){
//         $("#buscando").html("Buscando...");
//         },
        success: function (respuesta) {
//           console.log('afip',respuesta);
          var jsonData= JSON.parse(respuesta);  
        $('#CAE').html(jsonData.CAE);
//         alert(jsonData.Numero);
        } 
        });
});