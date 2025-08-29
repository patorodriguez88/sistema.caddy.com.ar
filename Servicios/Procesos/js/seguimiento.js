$("#modal_seguimiento").on('show.bs.modal', function (e) {
        let triggerLink = $(e.relatedTarget);
        let id = triggerLink[0].dataset['id'];
        
        $.ajax({
          data:{'Seguimiento_Visitas':1,'CodigoSeguimiento':id},
          type: "POST",
          url: "https://www.sistemacaddy.com.ar/SistemaTriangular/Funciones/php/tablas.php",
          success: function(response)
          {
            var jsonData = JSON.parse(response);   
            if(jsonData.success==1){
            $("#myCenterModalLabel").html('Seguimiento de Codigo '+id+ ' Visitas: '+jsonData.Visitas);

            }
          }
        });

    
        $("#myCenterModalLabel2").html('Movimientos del Codigo '+id);
 
        $('#cambiar_estado').click(function () { 
        $('#codigo_seguimiento').val(id);
        $('#fill-warning-modal').modal('show');
        
       });
      
       $('#cambiar_estado_ok').click(function(){
       
         if( $('#nueva_visita_check').prop('checked') ) {
         var check=1;
         }else{
           check=0;
         }
         
          $.ajax({
          data:{'CambiarEstado':1,'CodigoSeguimiento':id,'ctacte':check},
          type: "POST",
          url: "https://www.sistemacaddy.com.ar/SistemaTriangular/Funciones/php/cambiarestado.php",
          success: function(response)
          {
            var jsonData = JSON.parse(response);   
            if(jsonData.success==1){
            $('#fill-warning-modal').modal('hide');
            $('#modal_seguimiento').modal('hide');
            $.NotificationApp.send("Registro Actualizado !","Se ha actualizado el registro correctamente.","bottom-right","#FFFFFF","success");  
            var tabla = $('#guias_recibidas_tabla').DataTable();
            tabla.ajax.reload();
            }
          }

        });
        });

       $.ajax({
          data:{'Seguimiento_Modal':1,'CodigoSeguimiento':id},
          type: "POST",
          url: "https://www.sistemacaddy.com.ar/SistemaTriangular/Funciones/php/tablas.php",
          success: function(response)
          {
          var jsonData = JSON.parse(response);   
          if(jsonData.data[0].Entregado==1){
            
          $("#modal_seguimiento_header").prop('class','modal-header modal-colored-header bg-success');   
          $("#modal_seguimiento_content").prop('class','modal-content bg-success');   
          }else{
  
          $("#modal_seguimiento_header").prop('class','modal-header modal-colored-header bg-primary');   
           $("#modal_seguimiento_content").prop('class','modal-content bg-primary'); 
          } 
          console.log('response',jsonData);
          //ORIGEN 
          $('#cliente_origen_seguimiento').html(jsonData.data[0].RazonSocial); 
          $('#cliente_origen_direcccion_seguimiento').html(
          jsonData.data[0].DomicilioOrigen+'<br>'+
          '<li><p class="mb-0"><span class="font-weight-bold mr-2">Telefono:</span>'+ jsonData.data[0].TelefonoOrigen +'</p></li>');
          //DESTINO
          $('#cliente_destino_seguimiento').html(jsonData.data[0].ClienteDestino); 
          $('#cliente_destino_direcccion_seguimiento').html(
          jsonData.data[0].DomicilioDestino+'<br>'+
          '<li><p class="mb-0"><span class="font-weight-bold mr-2">Telefono:</span>'+ jsonData.data[0].TelefonoDestino +'</p></li>');
          //GUIA
          $('#header_title_guia_seguimiento').html('Información de la Guia '+jsonData.data[0].NumeroComprobante);  
//           $('#guia_seguimiento').html(jsonData.data[0].TipoDeComprobante+' | '+jsonData.data[0].NumeroComprobante); 
          $('#info_guia_seguimiento').html(
          '<tbody>'+
          '<tr>'+
          '<th>Cantidad</th><td> '+ jsonData.data[0].Cantidad+ '</td>'+
          '<th>Entregar en </th><td>'+ jsonData.data[0].EntregaEn +'</td>'+
          '<th>Cod.Proveedor </th><td>'+ jsonData.data[0].CodigoProveedor +'</td>'+
          '</tr>'+
          '<tr>'+
          '<th>Valor Declarado</th><td>'+ jsonData.data[0].ValorDeclarado +'</td>'+
          '<th>Cobrar Envio </th><td>'+ jsonData.data[0].CobrarEnvio +'</td>'+
          '<th>Cobrar Caddy </th><td>'+ jsonData.data[0].CobrarCaddy +'</td>'+
          '</tr>'+
          '<tr>'+
          '<th>Recorrido</th><td> '+ jsonData.data[0].Recorrido+ '</td>'+
          '<th>Transportista </th><td>'+ jsonData.data[0].Transportista +'</td>'+
          '<th>Kilometros </th><td>'+ jsonData.data[0].Kilometros +'</td>'+
          '</tr>'+
          '<tr>'+
          '<th>Observaciones:</th><td colspan="3">'+jsonData.data[0].Observaciones+'</td>'+
          '</tr>'+
          '</tbody>');
            
            var datatable_seguimiento= $('#seguimiento_tabla').DataTable({
            paging: false,
            searching: false,
            ajax: {
              url:"https://www.sistemacaddy.com.ar/SistemaTriangular/Funciones/php/tablas.php",
              data:{'Seguimiento_Tabla':1,'CodigoSeguimiento':id},
              type:'post'
              },
              columns: [
              {data:"Fecha"},  
              {data:"Hora"},  
              {data:"Usuario"},  
              {data:"Observaciones"}, 
              {data:"Estado"}
              ]
           });  
          
          },
          error: function(err){
          console.log('error',err);  
          }
      });
     });
      $('#modal_seguimiento').on('hidden.bs.modal', function () { 
      var tabla_seguimiento = $('#seguimiento_tabla').DataTable();
      tabla_seguimiento.destroy();
     });
