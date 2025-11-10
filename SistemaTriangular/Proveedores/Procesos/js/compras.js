
// $('#').click(function(){
    // $('#bs-example-modal-lg').modal('show');
// });



//DESDE ACA LAS FUNCIONES HEREDADAS DE COMPRAS

function cargarplata(){
    var miimporte= document.getElementById('importepago_t').value;
    var Subtotal= document.getElementById('total_t').value;
    var mi =Subtotal.replace(",",".");
    
     var saldo= mi-miimporte;
//      alert('total '+saldo);
//      alert('Mi importe '+mi);
//      alert('Saldo '+saldo); 
     if (saldo < 0){
    alertify.error("El importe" + miimporte + "no puede superar las facturas");
    document.getElementById('importepago_t').value =0;
    document.getElementById('botonaceptar').style.display='none';  
   }  
   if (saldo >= 0){
   document.getElementById('botonaceptar').style.display='block'; 
   }
    
  }


  function botonpagar(){
    var nr=document.getElementsByName('NR[]');
     if(nr.checked){
      document.getElementById('boton1191').disabled=true;  
     }else{ 
      document.getElementById('boton1191').disabled=false;  
     }
   } 



   function sumaselecc(){
  
    var n2 = document.getElementsByName('NP[]');
    var n1 =document.getElementsByName('valor');
    var total_t = document.getElementById('total_t').value;
    // alert(total_t);
      var total= n2.length;
       if(n2.checked){
        document.formulariox.importepago_t.value=0; 
       } 
    var elementos = 0;  
    var Suma = 0;
    var SumaFinal = 0;  
      for(var i=0; i<=total; i++){
        if(n2[i].checked == true){
          elementos++;
         var Suma =+ Suma+parseInt(n1[i].value);
         var Sumacdecimales=Suma.toFixed(2);
         
          //aca me fijo si el importe que estoy seleccionando supera el total a pagar si es asi, freno todo 
          if(Suma > total_t){
          alertify.error('No puede superar el total de $ ' + total_t,"",0);
          n2[i].checked=false;
          var SumaFinal =+ SumaFinal;
          var SumacdecimalesFinal=SumaFinal.toFixed(2);
          return;
          }else{
          var SumaFinal =+ SumaFinal+parseInt(n1[i].value);
          var SumacdecimalesFinal=SumaFinal.toFixed(2);
          }  
         document.getElementById('importepago_t').value=SumacdecimalesFinal;
        }
          if(elementos==0){
           document.formulariox.importepago_t.value=0; 
          }
      }
      
      document.getElementById('Disponible').value=n1;
    }  

    function sumar(){
        var n1 = parseFloat(document.MyForm.importeneto_t.value); 
        var n2 = parseFloat(document.MyForm.iva1_t.value); 
        var n3 = parseFloat(document.MyForm.iva2_t.value); 
        var n4 = parseFloat(document.MyForm.iva3_t.value);
        var n8 = parseFloat(document.MyForm.iva4_t.value);
          
        var n5 = parseFloat(document.MyForm.exento_t.value);
        var n6 = parseFloat(document.MyForm.perciva_t.value);
        var n7 = parseFloat(document.MyForm.perciibb_t.value);
        document.MyForm.total_t.value=n1+n2+n3+n4+n5+n6+n7+n8; 
          
        document.MyForm.totaliva_t.value=n2+n3+n4+n8; 
        document.MyForm.totalSiniva_t.value=n1+n5; 
        }

        function buscardatos(){
            var dir=document.getElementById('tercero_t').value;
            document.getElementById('VerFCheque3').style.display='block';
            document.getElementById('VerNCheque3').style.display='block';
            document.getElementById('botonaceptar').style.display='block'; 
            document.getElementById('VeridCheque3').style.display='block'; 
        
          if(dir!=''){
            var dir= dir.split(','); 
            document.getElementById('importepago_t').value=dir[1];
            document.getElementById('FCheque3').value=dir[2];
            document.getElementById('NCheque3').value=dir[3];
            document.getElementById('idCheque3').value=dir[0];
            
          }
        }

        function buscar(){
                var n1 = parseFloat(document.MyForm.importeneto_t.value); 
                var n2 = parseFloat(document.MyForm.iva1_t.value); 
                var n3 = parseFloat(document.MyForm.iva2_t.value); 
                var n4 = parseFloat(document.MyForm.iva3_t.value);
                var n8 = parseFloat(document.MyForm.iva4_t.value);  
                var n5 = parseFloat(document.MyForm.exento_t.value);
                var n6 = parseFloat(document.MyForm.perciva_t.value);
                var n7 = parseFloat(document.MyForm.perciibb_t.value);
                document.MyForm.total_t.value=n1+n2+n3+n4+n5+n6+n7; 
                document.MyForm.totaliva_t.value=n2+n3+n4; 
                document.MyForm.totalSiniva_t.value=n1+n5+n6+n7;               
            }

            function mostrarx(){
                var x1 = parseFloat(document.formulariox.formadepago_t.value);
                var x2 = parseFloat(document.formulariox.anticipodisponible_t.value); 
                var x3 = parseFloat(document.formulariox.totalfacturas_t.value); 
                
                  if (x1=='3'){   //EFECTIVO 000111100
                document.formulariox.importepago_t.value=x3; 
                document.getElementById('total').style.display = 'block';
                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('Disponible').style.display='none';
                document.getElementById('tabla1').style.display='none';
                
                  }
                  
                if (x1=='20'){ // CHEQUES DE TERCEROS
                document.getElementById('TercerosOculto').style.display = 'block';
                document.getElementById('total').style.display = 'block';
                document.getElementById('total').readonly=true;

                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('Disponible').style.display = 'none';
                document.getElementById('tabla1').style.display='none';
                
                }  
                if (x1=='4'){    //111200 TRANSFERENCIAS BANCARIAS
                document.formulariox.importepago_t.value=x3; 
                document.getElementById('oculto').style.display = 'block';
                document.getElementById('oculto1').style.display = 'block';
                document.getElementById('BancoOculto').style.display = 'block';
                document.getElementById('total').style.display = 'block';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('Disponible').style.display = 'none';
                document.getElementById('tabla1').style.display='none';                                         
                }

                if (x1=='5'){  // CHEQUES PROPIOS
                document.formulariox.importepago_t.value=x3; 
                document.getElementById('NumeroChequeOculto').style.display = 'block';
                document.getElementById('FechaChequeOculto').style.display = 'block';
                document.getElementById('BancoOculto').style.display = 'block';
                document.getElementById('total').style.display = 'block';
                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('Disponible').style.display = 'none';
                document.getElementById('tabla1').style.display='none';
                }
                 if (x1=='22'){   //ANTICIPO A PROVEEDORES
                document.formulariox.importepago_t.value=0; 
                document.getElementById('Disponible').style.display = 'block';
                document.getElementById('total').style.display = 'block';
                document.getElementById('tabla1').style.display='block';
                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';                    
                  } 
                }

                function mostrary(){
                    var x1 = parseFloat(document.formularioy.formadepago_t.value);
                      
                if (x1=='3'){   //EFECTIVO 000111100
                document.getElementById('total').style.display = 'block';
                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                }
                      
                    if (x1=='20'){ // CHEQUES DE TERCEROS
                document.getElementById('TercerosOculto').style.display = 'block';

                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                document.getElementById('total').style.display = 'none';
                    }  
                    if (x1=='4'){    //111200 TRANSFERENCIAS BANCARIAS
                document.getElementById('oculto').style.display = 'block';
                document.getElementById('oculto1').style.display = 'block';
                document.getElementById('BancoOculto').style.display = 'block';
                document.getElementById('total').style.display = 'block';

                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                document.getElementById('NumeroChequeOculto').style.display = 'none';
                document.getElementById('FechaChequeOculto').style.display = 'none';
                document.getElementById('BancoOculto').style.display = 'none';
                                
                    }
                    if (x1=='5'){  // CHEQUES PROPIOS
                document.getElementById('NumeroChequeOculto').style.display = 'block';
                document.getElementById('FechaChequeOculto').style.display = 'block';
                document.getElementById('BancoOculto').style.display = 'block';
                document.getElementById('total').style.display = 'block';

                document.getElementById('oculto').style.display = 'none';
                document.getElementById('oculto1').style.display = 'none';
                document.getElementById('TercerosOculto').style.display = 'none';
                         }
                    }

                    function comprobar2(){

                        var valor = parseFloat(document.MyForm.total_t.value);
                        var tipo = document.MyForm.tipodecomprobante_t.value;
                        var autorizado=document.getElementById('ctaasignada_t').value;
                          
                          if (tipo==''){
                          alertify.error("Seleccione un Tipo de Comprobante","",0);
                          }else{
                            if((tipo =='NOTAS DE CREDITO A')
                            ||(tipo =='NOTAS DE DEBITO A')
                            ||(tipo =='NOTAS DE CREDITO B')     
                            ||(tipo =='NOTAS DE DEBITO B')
                            ||(tipo =='NOTAS DE CREDITO C')
                            ||(tipo =='NOTAS DE DEBITO C')
                            ||(tipo =='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
                            ||(tipo =='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
                            ||(tipo =='NOTAS DE CREDITO M')
                            ||(tipo =='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
                            ||(tipo =='RECIBOS FACTURA DE CREDITO')
                            ||(tipo =='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
                            ||(tipo =='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
                            ||(tipo =='NOTA DE CREDITO DE ASIGNACION')){
                            document.getElementById('codigodeaprobacion').style.display = 'none';
                            alertify.error("No Requiere Codigo de Aprobacion","",0);
                            }else{   //EFECTIVO 000111100
                              if(autorizado==1){
                              document.getElementById('codigodeaprobacion').style.display = 'none';
                              alertify.error("No Requiere Codigo de Aprobacion","",0);
                              }else{
                              document.getElementById('codigodeaprobacion').style.display = 'block';
                              alertify.success("Requiere Codigo de Aprobacion","",0);
                              }
                            }
                          }
                        }

                        function comprobar(){
                            var autorizado=document.getElementById('ctaasignada_t').value;
                            
                            var valor = parseFloat(document.MyForm.total_t.value);
                            var tipo = document.MyForm.tipodecomprobante_t.value;
                            if(autorizado==0){
                              if(valor>'1000'){
                                if((tipo =='NOTAS DE CREDITO A')
                                ||(tipo =='NOTAS DE DEBITO A')
                                ||(tipo =='NOTAS DE CREDITO B')     
                                ||(tipo =='NOTAS DE DEBITO B')
                                ||(tipo =='NOTAS DE CREDITO C')
                                ||(tipo =='NOTAS DE DEBITO C')
                                ||(tipo =='NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
                                ||(tipo =='NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
                                ||(tipo =='NOTAS DE CREDITO M')
                                ||(tipo =='NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
                                ||(tipo =='RECIBOS FACTURA DE CREDITO')
                                ||(tipo =='NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
                                ||(tipo =='AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
                                ||(tipo =='NOTA DE CREDITO DE ASIGNACION')){
                                document.getElementById('codigodeaprobacion').style.display = 'none';
                                }else{   //EFECTIVO 000111100
                                document.getElementById('codigodeaprobacion').style.display = 'block';
                                alertify.success("Requiere Codigo de Aprobacion","",0);
                                }
                                }else{
                                document.getElementById('codigodeaprobacion').style.display = 'none';
                                alertify.error("No Requiere Codigo de Aprobacion","",0);
                                }
                              }else{
                              document.getElementById('codigodeaprobacion').style.display = 'none';
                              alertify.error("No Requiere Codigo de Aprobacion","",0);
                            }
                            }

