
abrir_tabla();

// El select de Recorrido se auto-inicializa como select2 al cargar la
// pagina (app.js, [data-toggle="select2"]), sin dropdownParent. Select2
// por defecto cuelga su desplegable (con el buscador) de <body>, AFUERA
// del modal - el focus-trap de Bootstrap 5 (que fuerza el foco de vuelta
// al modal si detecta que se va a un elemento que no es descendiente
// suyo) le saca el foco al buscador apenas se lo toca: la lista de
// recorridos se ve bien, pero no se puede escribir para filtrar.
// Se reinicializa apuntando el dropdown al propio modal cada vez que se
// abre, asi el buscador queda DENTRO del modal.
$('#standard-modal-rec').on('shown.bs.modal', function () {
    var $sel = $('#recorrido_t');
    if ($sel.data('select2')) {
        $sel.select2('destroy');
    }
    $sel.select2({ dropdownParent: $('#standard-modal-rec'), width: '100%' });
});

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
    responsive: true, // misma correccion que Pendientes.php: la tabla tiene
                       // dt-responsive + CSS/JS de Responsive cargados pero
                       // nunca se prendia la opcion.
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
                return '<span class="badge bg-danger">Eliminado</span>';
                }else{
                return '<span class="badge bg-success">Cargado</span>';
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

                if (checked != 0) {

                    // FIX (reportado: "cuando se pone aceptar queda pensando
                    // mucho tiempo el modal"): antes esto disparaba un
                    // $.ajax por cada colecta seleccionada TODOS JUNTOS (un
                    // for sin esperar la respuesta), y cada CargarVenta hace
                    // trabajo pesado del lado del servidor (varios INSERT,
                    // una llamada a Google para la distancia, etc). Con 10-30
                    // colectas seleccionadas eso son 10-30 requests pesados
                    // en paralelo pisándose en la base — encima cada uno
                    // recargaba la tabla entera (otro request más) apenas
                    // terminaba, multiplicando todavía más la carga. Ahora
                    // van de a UNO, en orden, y la tabla se recarga una sola
                    // vez al final. El tiempo total no cambia mucho (es la
                    // suma de lo mismo), pero deja de trabarse porque no
                    // compiten entre sí, y el operador ve el progreso real.
                    var $btn = $(this).prop('disabled', true);
                    var total = checked.length;
                    var ok = 0;
                    var fallidos = [];

                    function procesarUno(idx) {
                        if (idx >= total) {
                            $btn.prop('disabled', false).text('Aceptar');
                            var datatable = $('#colecta').DataTable();
                            datatable.ajax.reload();
                            if (fallidos.length === 0) {
                                toast("success", "Listo", "Se generaron " + ok + " de " + total + " colectas.");
                            } else {
                                toast("error", "Terminado con errores", ok + " de " + total + " generadas. Fallaron: " + fallidos.join(', '));
                            }
                            return;
                        }

                        var id = checked[idx];
                        $btn.text('Aceptando ' + (idx + 1) + ' de ' + total + '…');
                        $('#pensando' + id).css('display', 'block');

                        $.ajax({
                            data: { 'CargarVenta': 1, 'id': id },
                            type: "POST",
                            url: "Procesos/php/colecta.php",
                            success: function (response) {
                                var jsonData = JSON.parse(response);
                                if (jsonData.success == 1) {
                                    ok++;
                                } else {
                                    fallidos.push(id);
                                }
                            },
                            error: function () {
                                fallidos.push(id);
                            },
                            complete: function () {
                                procesarUno(idx + 1);
                            },
                        });
                    }

                    procesarUno(0);

                }else{

                    toast("error", "No hay Registros Seleccionados !", "No se han actualizado registros.");

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
            // .off().on() para no acumular un handler nuevo (con su propio
            // "checked" viejo en el closure) cada vez que se abre el modal.
            $('#modificarrecorrido_all_ok').off('click').on('click', function(){
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

                    toast("success", "Registros Actualizados !", "Se ha actualizado al nuevo recorrido todos los registros seleccionados.");      
                    
                    }else{
                
                    toast("error", "Registro No Actualizado !", "No pudimos actualizar los Recorridos.");        
                    }
                }
                });

            });
        });
    