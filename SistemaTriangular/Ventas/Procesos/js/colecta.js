
abrir_tabla();

$('#fecha_actual').on("change", function() {
    var datatable = $('#colecta').DataTable();
    datatable.destroy();

    abrir_tabla();
});
    

function abrir_tabla(){
    
    var date=$('#fecha_actual').val();

    var datatable = $('#colecta').DataTable({
    paging: false,
    searching: true,
    ajax: {
         url:"Procesos/php/colecta.php",
         data:{'datos':1,'date':date},
         type:'post'
         },
        columns: [          
            {data:"Fecha",
            render: function (data,type,row){
            var fecha=row.Fecha.split("-").reverse().join('/');
                return `<td>${fecha}</td>`;
            }
            }, 
            {data:"Cliente",
            render: function (data, type, row) { 
               return `<td><b>${row.Cliente}</br><i class="mdi mdi-18px mdi-map-marker text-success"></i><a class="text-muted">${row.Direccion}</td>`;
                  }
                },            
            {data:"Cantidad",
            render: function (data, type, row) {

                if(row.Cantidad!=row.Cantidad_m){
                
                return '<a class="text-danger">'+row.Cantidad+'</a>';
                
                }else{
                
                return '<a class="text-black">'+row.Cantidad+'</a>';
                
                }
            }
            },
            {data:"Cantidad_m",
            render: function (data, type, row) {
                
                if(row.Cantidad_m!=0){

                    if(row.Cantidad!=row.Cantidad_m){
                    
                    return '<a class="text-success">'+row.Cantidad_m+'</a>';

                    }else{
                
                    return '<a class="text-black">'+row.Cantidad_m+'</a>';
                    
                    }   
                }else{

                    return '';

                }
            }         
            },
            {data:"CodigoSeguimiento",
            render: function (data, type, row) {
            return '<td><i id="pensando'+row.id+'" class="mdi mdi-spin mdi-18px text-success mdi-circle-slice-1" style="display:none"></i>'+row.CodigoSeguimiento+'</td>';  
            }
            },
            {data:"Recorrido"},  
            {data:"Recorrido",  
            render: function (data, type, row) {

                if (typeof row !== 'undefined' && typeof row.Cantidad !== 'undefined' && Number(row.Cantidad) < 10) {
                    if(row.CodigoSeguimiento==''){
                    var status='checked';
                    }
                }else{
                    var status='';
                }
            if(row.CodigoSeguimiento){
                if(row.Eliminado==1){
                return '<span class="badge badge-danger">Eliminado</span>';
                }else{
                return '<span class="badge badge-success">Cargado</span>';
                }
            }else{

            
              return  `<td><div class="custom-control custom-checkbox  custom-checkbox-success mb-2">`+
                      `<input  name="customCheck1" type="checkbox" class="custom-control-input" value="${row.id}" data-id="${row.Recorrido}" id="${row.id}"  ${status}>`+
                      `<label class="custom-control-label" for="${row.id}"></label></div></td>`;
                }
            }
            },
            ],
            select: {
                style: 'os',
                selector: 'td:not(:last-child)' // no row selection on last column
                
              },
              rowCallback: function(row, data) {
                // Set the checked state of the checkbox in the table
                    
                $('custom-control-input', row).prop('checked', data.id == 0);
                
              }
               
            });

        }


            $('#aceptar').click(function(e){
                //Creamos un array que almacenará los valores de los input "checked"
                var checked = [];

                //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
                $("input.custom-control-input:checked").each(function() {
                
                //Mediante la función push agregamos al arreglo los values de los checkbox
                if ($(this).attr("value") != null) {
            
                    checked.push(($(this).attr("value")));

                    }
                });
              
                // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
                        
                if (checked != 0) {
                    
                    console.log('total',checked.length);

                    for (var i = 0; i < checked.length; i++) {
                        console.log("Elemento en el índice " + i + ": " + checked[i]);
                        

                        $('#pensando'+checked[i]).css('display','block');
                        
                            $.ajax({
                            data:{'CargarVenta':1,'id':checked[i]},
                            type: "POST",
                            url: "Procesos/php/colecta.php",
                            beforeSend: function(){                                
                                
                            },            
                            success: function(response)
                            {
                            var jsonData = JSON.parse(response);

                            if(jsonData.success==1){    

                            //ACTUALIZO LA TABLA COLECTA
                            var datatable = $('#colecta').DataTable();
                                    
                            datatable.ajax.reload();     

                            }
                            
                            }
                        });
                        
                    }
                    
                }else{
            
                    $.NotificationApp.send("No hay Registros Seleccionados !","No se han actualizado registros.","bottom-right","#FFFFFF","danger");      
                
                }

                });
            

                function modificarrecorrido(i){
                    $('#cs_modificar_REC').val(i); 
                    $.ajax({
                            data:{'BuscarRecorridos':1,'cs':i},
                            type: "POST",
                            url: "Procesos/php/preventa.php",
                            success: function(response)
                            {
                            $('.selector-recorrido select').html(response).fadeIn();
                            }
                        });
                    
                    $('#myCenterModalLabel_rec').html('Modificar Recorrido a Código '+i);   
                    $('#standard-modal-rec').modal('show');
                    $('#modificarrecorrido_ok').css('display','block');
                    $('#modificarrecorrido_all_ok').css('display','none');
                  
                    }

                    // DESDE ACA MODIFICAR _ ALL

        $('#modificar_recorrido_all').click(function(){
            //Creamos un array que almacenará los valores de los input "checked"
            var checked = [];
            var recorridos=[];
            //Recorremos todos los input checkbox con name = Colores y que se encuentren "checked"
            $("input.custom-control-input:checked").each(function() {
            
            //Mediante la función push agregamos al arreglo los values de los checkbox
            if ($(this).attr("value") != null) {

                checked.push(($(this).attr("value")));
                recorridos.push(($(this).attr("data-id")));

                }
            });
        
            // Utilizamos console.log para ver comprobar que en realidad contiene algo el arreglo
                    
            if (checked != 0) {
            modificarrecorrido();
            $('#myCenterModalLabel_rec').html("Modificar de Recorrido "+ checked.length+ " registros seleccionados");   
            $('#standard-modal-rec').modal('show');
            $('#modificarrecorrido_ok').css('display','none');
            $('#eliminarrecorrido_all_ok').css('display','none');
            $('#query_selector_recorrido_t').css('display','block');

            $('#modificarrecorrido_all_ok').css('display','block');
            $('.modal-header.modal-colored-header.bg-danger').removeClass('bg-danger');
            $('.modal-header.modal-colored-header').addClass('bg-primary');    
        }

            //BOTON GUARDAR CAMBIOS EN MODIFICAR RECORRIDOS _ ALL
            $('#modificarrecorrido_all_ok').click(function(){   
                // Obtengo el recorrdido seleccionado
                var r = $('#recorrido_t').val();
                
                console.log('rec',r);
                console.log('id',checked);
                $.ajax({
                    data:{'ActualizaRecorrido':1,'r':r,'id':checked},
                    type: "POST",
                    url: "Procesos/php/colecta.php",
                    success: function(response)
                    {
                    var jsonData=JSON.parse(response);
                    if(jsonData.success==1){
                    
                        var datatable = $('#colecta').DataTable();
                    datatable.ajax.reload(); 

                    $('#standard-modal-rec').modal('hide');                                  

                    $.NotificationApp.send("Registros Actualizados !","Se ha actualizado al nuevo recorrido todos los registros seleccionados.","bottom-right","#FFFFFF","success");      
                    
                    }else{
                
                    $.NotificationApp.send("Registro No Actualizado !","No pudimos actualizar los Recorridos.","bottom-right","#FFFFFF","danger");        
                    }
                }
                });

            });
        });
    