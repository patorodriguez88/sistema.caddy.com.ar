
var Nivel='';

//FUNCION PARA VERIFICAR SI EXISTE UN ARCHIVO
function verificarArchivo(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var tamañoEnBytes = xhr.getResponseHeader('Content-Length');
                var tamañoEnKilobytes = Math.floor(tamañoEnBytes / 1024);
                var tamañoEnMegabytes = tamañoEnKilobytes / 1024;
                callback(true,tamañoEnBytes, tamañoEnKilobytes, tamañoEnMegabytes); // Llamar al callback con el resultado verdadero
            } else {
                callback(false); // Llamar al callback con el resultado falso
            }
        }
    };
    xhr.open('HEAD', url);
    xhr.send();
}

//FUNCION SUBIR ARCHIVO
function subirArchivo() {
    var archivoInput = document.getElementById('archivoInput');
    var archivo = archivoInput.files[0];
     
    // Verificar la extensión del archivo
     if (!archivo.name.toLowerCase().endsWith('.pdf')) {
        
        $.NotificationApp.send("Error!", "Por favor selecciona un archivo con extensión .pdf.", "bottom-right", "#FFFFFF", "danger");  
        return;
    
    }
    
    var formData = new FormData();
    var nombreArchivoInput = 'OP'+$('#id_t').val()+'P'+$('#id_p').val();

    formData.append('archivo', archivo);
    formData.append('nombreArchivo', nombreArchivoInput);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'subir_1.php', true);

    xhr.onload = function () {

        if (xhr.status === 200) {

            $.NotificationApp.send("Exito!", "El archivo se ha subido correctamente.", "bottom-right", "#FFFFFF", "success");  
            
            // Puedes realizar acciones adicionales aquí, como cerrar la ventana modal
        } else {

            $.NotificationApp.send("Error!", "Ha ocurrido un error al subir el archivo.", "bottom-right", "#FFFFFF", "danger");  

        }
    };

    xhr.send(formData);
}

//FUNCION BORRAR ARCHIVO
$('#archivo_subido_borrar').click(function(){
    var name = 'OP'+$('#id_t').val()+'P'+$('#id_p').val()+'.pdf';
    
    $.ajax({
        data: {'borrar_archivo':1,'nombre_archivo':name},
        url:'subir_1.php',
        type:'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            if(jsonData.success==1){
                
                $.NotificationApp.send("Exito!", "Archivo eliminado correctamente.", "bottom-right", "#FFFFFF", "success");  
                
                $('#subir_archivo').css('display','block');
                $('#archivo_subido').css('display', 'none');

            }else{
                $.NotificationApp.send("Error!", jsonData.error, "bottom-right", "#FFFFFF", "danger");  
            }
        }
    });

});

//FUNCTION PRESUPUESTOS
function presupuestos_edit(id){

    $.ajax({
        data: {'Presupuestos_edit':1,'id':id},
        url:'Procesos/php/ordendecompra.php',
        type:'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            if(jsonData){
                
                console.log('dato',jsonData.error);

                if(jsonData.error==='Sin permisos'){

                    $.NotificationApp.send("Error!", "Ud. no posee permisos suficientes para esta acción.", "bottom-right", "#FFFFFF", "danger");  

                }else{
                
                $('#presupuesto_new').modal('show');
                $('#ordendecompra_new').modal('hide');
                $('#presupuesto_aceptar').css('display','none');
                $('#presupuesto_aceptar_cnl').css('display','none');
                $('#presupuesto_editar_ok').css('display','inline');
                $('#presupuesto_editar_cnl').css('display','inline');
                $('#id_p').val(jsonData.data[0].id);
                var Fecha = jsonData.data[0].Fecha.split('-').reverse().join('/');
                $('#fecha_p').val(Fecha).prop('disabled', true);
                $('#presupuesto_new_title').html('Editar Presupuesto #'+id);
                $('#descripcion_p').val(jsonData.data[0].Descripcion);
                $('#formadepago_p').val(jsonData.data[0].FormaDePago);
                $('#cantidad_p').val(jsonData.data[0].Cantidad);
                $('#total_p').val(jsonData.data[0].Total);
                $('#observaciones_p').val(jsonData.data[0].Observaciones);
                $('#proveedor_p').prop('disabled', true);

                var valorAsignado = jsonData.data[0].Proveedor;

                // Encuentra el elemento select por su ID
                var select = document.getElementById("proveedor_p");

                // Crea un nuevo elemento option con el valor deseado
                var nuevaOpcion = document.createElement("option");
                nuevaOpcion.value = valorAsignado;
                nuevaOpcion.text = valorAsignado; // Texto visible de la opción

                // Agrega el nuevo elemento option al select
                select.appendChild(nuevaOpcion);

                // Selecciona el nuevo elemento option
                select.value = valorAsignado;


                //VERIFICO SI EXITE ARCHIVO SUBIDO 
                var nombreArchivoInput = 'OP'+$('#id_t').val()+'P'+$('#id_p').val()+'.pdf';
                var urlArchivo="../Presupuestos/"+nombreArchivoInput;
                
                verificarArchivo(urlArchivo, function(existe,tamañoEnBytes, tamañoEnKilobytes, tamañoEnMegabytes) {
                        
                    if (existe) {
                        $('#subir_archivo').css('display','none');
                        $('#archivo_subido').css('display', 'block');
                        $('#archivo_subido_nombre').html(nombreArchivoInput);
                        $('#archivo_subido_size').html(tamañoEnKilobytes+' Kb');
                        
                    } else {
                        $('#subir_archivo').css('display','block');
                        $('#archivo_subido').css('display', 'none');
                    
                    }
                });
                
                



                }
            }
        }
    });

}

$('#presupuesto_editar_ok').click(function(){

            var id=$('#id_p').val();
            var descripcion=$('#descripcion_p').val();
            var formadepago=$('#formadepago_p').val();
            var cantidad=$('#cantidad_p').val();
            var total=$('#total_p').val();
            var observaciones=$('#observaciones_p').val();
            $.ajax({
                data: {'Presupuestos_edit_ok':1,'id':id,'Descripcion':descripcion,'Formadepago':formadepago,'Cantidad':cantidad,'Total':total,'Observaciones':observaciones},
                url:'Procesos/php/ordendecompra.php',
                type:'post',
                success: function(response) {
                    var jsonData = JSON.parse(response);
                    if(jsonData.success==1){
                        $('#presupuesto_new').modal('hide');
                        $('#ordendecompra_new').modal('show');
                        var table = $('#presupuestos').DataTable();
                        table.ajax.reload();
                        $.NotificationApp.send("Presupuesto Modificado!", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");  
                    }else{
                        $.NotificationApp.send("Error!", jsonData.error, "bottom-right", "#FFFFFF", "danger");  
                    }

                }
            });

});

//CERRAR PRESUPUESTOS
$('#presupuesto_editar_cnl').click(function(){
    
    $('#presupuesto_new').modal('hide');
    $('#ordendecompra_new').modal('show');
    

});

function presupuestos_check(id){

    if(Nivel==1){
    // var rowData = table.row(this).data(); // Obtener los datos de la fila
    // if (rowData) { // Verificar si se han obtenido datos válidos
        // var id = rowData.id; // Obtener el valor de la columna 'id'    
        var idOrden = $('#id_t').val();
        var id=id;

        $('#aprobar_presupuesto').modal('show').css('z-index', 1041);
        $('#ordendecompra_new').modal('hide');

        const newLocal = '#aprobar_presupuesto_text';
        $(newLocal).html('Estas por aprobar el presupuesto '+id+' presiona <b class="text-success">Aceptar</b> para aprobarlo.');
        
        $('#aprobar_presupuesto_ok').click(function(){

            $.ajax({
                data: {'AprobarPresupuesto':1,'id':id,'idOrden':idOrden},
                url:'Procesos/php/ordendecompra.php',
                type:'post',
                success: function(response) {
                    var jsonData = JSON.parse(response);
                    if(jsonData.success==1){
                        
                        var procedimiento='AP';
                        var asunto = 'Aprobamos un presupuesto';
                        var mensaje = 'aprobado';
                                                        
                        enviarmail(procedimiento,id,asunto,mensaje);

                        $('#aprobar_presupuesto').modal('hide');                                    

                        $.NotificationApp.send("Presupuesto Aprobado!", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");  
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al obtener la orden de compra:", error);
                }
            });

        });

    } else {

        $.NotificationApp.send("Error!", "Ud. no posee permisos suficientes para esta acción.", "bottom-right", "#FFFFFF", "danger");  

    }
};

//procedimiento puede ser OC (Orden de Compra)PO (Presupuestos) PA (Aprobar Presupuestos)
function enviarmail(procedimiento,id,asunto,mensaje,cantidad){
    $.ajax({
        data: {'procedimiento':procedimiento,'id':id,'asunto':asunto,'mensaje':mensaje,'cantidad':cantidad},
        url:'Procesos/php/enviarmail.php',
        type:'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            
            if(jsonData.success==1){
                $.NotificationApp.send("Exito !","Mail enviado con éxito.","bottom-right","#FFFFFF","success");  
            }else{
                $.NotificationApp.send("Error !","No se pudo enviar el mail.","bottom-right","#FFFFFF","warning");  
            }
        }
    });
    // return jsonData;
}

function calcularTotalPrecios() {
    
    $.ajax({
        data: {'Total_pendientes':1},
        url:'Procesos/php/ordendecompra.php',
        type:'post',
        success: function(data) {
            var jsonData=JSON.parse(data);
            console.log(jsonData.Total_Aceptadas);
            console.log(jsonData.Total_Cargadas);

            $('#total_cargadas').html(jsonData.Total_Cargadas.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' }));
            $('#total_aceptadas').html(jsonData.Total_Aceptadas.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' }));
            var Total_Pendientes=jsonData.Total_Cargadas+jsonData.Total_Aceptadas;
            $('#total_pendientes').html(Total_Pendientes.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' }));
            $('#total_cargadas_cant').html(jsonData.Total_Cargadas_cant);
            $('#total_aceptadas_cant').html(jsonData.Total_Aceptadas_cant);
            var Total_Pendientes_cant=jsonData.Total_Cargadas_cant+jsonData.Total_Aceptadas_cant;
            $('#total_pendientes_cant').html(Total_Pendientes_cant);
            
        },        
        error: function(xhr, status, error) {
            console.error("Error al obtener el dato:", error);
        }
    });
}


//BUSCO NIVEL
$.ajax({
    data: {'Nivel':1},
    url:'Procesos/php/ordendecompra.php',
    type:'post',
    success: function(response) {
        var jsonData = JSON.parse(response);
        Nivel=jsonData.Nivel;
    },
    error: function(xhr, status, error) {
        console.error("Error al obtener el dato:", error);
    }
});

function edit_oc(id){
    console.log('edit',id);
    add_pres(id);
}

function add_pres(id) {
    $('#presupuestos_agregar').css('display', 'block');

    //BOTONES
    $('#presupuesto_aceptar').css('display','inline');
    $('#presupuesto_aceptar_cnl').css('display','inline');
    $('#presupuesto_editar_ok').css('display','none');
    $('#presupuesto_editar_cnl').css('display','none');

    $('#ordendecompra_aceptar').css('display', 'none');
    $('#ordendecompra_estado_aceptar').css('display','none');
    $('#presupuestos').DataTable().destroy();

    // Realizar la solicitud AJAX para obtener datos de la orden de compra
    $.ajax({
        data: {'OrdenDeCompra_pres': 1, 'id': id},
        url: 'Procesos/php/ordendecompra.php',
        type: 'post',
        success: function(response) {
            var jsonData = JSON.parse(response);

            $('#ordendecompra_new_title').html('Orden de Compra #'+id);

            $('#ordendecompra_new').modal('show');

            $('#id_t').val(jsonData.data[0].id).prop('readonly', true);
            $('#titulo_t').val(jsonData.data[0].Titulo).prop('readonly', true);
            $('#tipodeorden_t').val(jsonData.data[0].TipoDeOrden).prop('disabled', true);
            $('#motivo_t').val(jsonData.data[0].Motivo).prop('readonly', true);
            $('#usuariocarga_t').val(jsonData.data[0].UsuarioCarga);
            $('#precio_t').val(jsonData.data[0].Precio).prop('readonly', true);
            $('#fechadeorden_t').val(jsonData.data[0].FechaOrden).prop('readonly', true);
            $('#fechaaprobado_t').val(jsonData.data[0].FechaAprobado).prop('readonly', true);            
            $('#observaciones_t').val(jsonData.data[0].Observaciones).prop('readonly', true);            
            $('.alert.alert-success').css('display', 'block');
            $('#alert_title').html('Orden de Compra '+jsonData.data[0].Estado);
            
            //ACA DETERMINO ALGUNOS ASPECTOS SEGUN EL ESTADO
            if(jsonData.data[0].Estado=='Cargada'){
                
                $('#ordendecompra_aprobar').css('display','none');
                
                if(Nivel==1){
                    
                    $('#ordendecompra_estado_aceptar').css('display','inline');
                }
                // SI ESTA CARGADA NO PERMITE CARGAR PRESUPUESTOS, HASTA QUE LA ACEPTEMOS Y LE PIDAMOS LA CANTIDAD NECESARIA
                $('#presupuestos_agregar').css('display','none'); 

                $('.modal-header').removeClass().addClass('modal-header modal-colored-header bg-secondary');
                
                $('#aceptar_orden_title').html('Aceptar Orden de Compra');
                

            }else if(jsonData.data[0].Estado=='Aceptada'){
                
                if(Nivel==1){
                
                    $('#ordendecompra_aprobar').css('display','inline');
                }
                
                $('.modal-header').removeClass().addClass('modal-header modal-colored-header bg-info');
            
            }else{

                $('.modal-header').removeClass();
            }
            
            var valorAsignado = jsonData.data[0].TipoDeOrden;

            // Encuentra el elemento select por su ID
            var select = document.getElementById("tipodeorden_t");

            // Crea un nuevo elemento option con el valor deseado
            var nuevaOpcion = document.createElement("option");
            nuevaOpcion.value = valorAsignado;
            nuevaOpcion.text = valorAsignado; // Texto visible de la opción

            // Agrega el nuevo elemento option al select
            select.appendChild(nuevaOpcion);

            // Selecciona el nuevo elemento option
            select.value = valorAsignado;

            $('#presupuestos').hide();

            // function verificarArchivo(url, callback) {
            //     var xhr = new XMLHttpRequest();
            //     xhr.onreadystatechange = function() {
            //         if (xhr.readyState === 4) {
            //             if (xhr.status === 200) {
            //                 callback(true); // Llamar al callback con el resultado verdadero
            //             } else {
            //                 callback(false); // Llamar al callback con el resultado falso
            //             }
            //         }
            //     };
            //     xhr.open('HEAD', url);
            //     xhr.send();
            // }
            
           
            var table = $('#presupuestos').DataTable({
                bDeferRender: true,
                paging: false,
                searching: false,
                bInfo: false,
                ordering: false,
                ajax: {
                    url: "../Proveedores/Procesos/php/ordendecompra.php",
                    data: function() {
                        return {'Presupuestos': 1, 'id': id}; // Aquí se incluye la variable filtro
                    },
                    type: 'post'
                },
                columns: [
                    // {data: "id"},
                    {data: "Fecha",
                        render: function(data, type, row) {
                            var Fecha = row.Fecha.split('-').reverse().join('.');
                            return `<td><span style="display: none;">${row.Fecha}</span>${Fecha}<br><span class="badge badge-primary">${row.Usuario}</span></td></td>`;
                        }
                    },
                    {data: "Proveedor"},
                    {data: "Descripcion"},
                    {data: "FormaDePago"},
                    {data: "Cantidad"},
                    {data: "Total",
                        render: $.fn.dataTable.render.number('.', ',', 2, '$ ')
                    },
                    // {data: "Usuario"},
                    {data: null},
                    {data: null,
                      render: function(data,type,row){
                        
                        if(Nivel==1){  
                        
                            return '<i onclick="presupuestos_edit('+row.id+')" style="cursor:pointer" class="mdi mdi-18px mdi-pencil text-warning"></i>'+
                            '<i onclick="presupuestos_check('+row.id+')" style="cursor:pointer" class="mdi mdi-18px mdi-check-bold text-success ml-2"></i>';
                        
                        }else{
                            
                            return '<i onclick="presupuestos_edit('+row.id+')" style="cursor:pointer" class="mdi mdi-18px mdi-pencil text-warning"></i>';
                        
                        }
                        }
                    
                    }

                ],
                drawCallback: function() {
                    var api = this.api();
                    api.rows().every(function() {
                        var data = this.data();
                        var row = this.node();
                        var urlArchivo = '../Presupuestos/OP' + id + 'P' + data.id + '.pdf';
                        verificarArchivo(urlArchivo, function(existe) {
                            var icono = existe ? '<i class="mdi mdi-18px mdi-file-document-outline"></i>' : '';
                            var enlace = existe ? '<a href="' + urlArchivo + '" target="_blank">' + icono + '</a>' : '';
                            $('td:eq(6)', row).html(enlace);
                        });
                    });
                }           
             });
            
            
            // Evento que se activa cada vez que se dibuja la tabla
            table.on('draw.dt', function() {
                // Verificar si hay datos en la tabla
                if (table.data().count() === 0) {
                    // Si no hay datos, ocultar la tabla
                    $('#presupuestos').hide();
                } else {
                    // Si hay datos, mostrar la tabla
                    $('#presupuestos').show();
                }
                table.rows().every(function() {
                    var data = this.data();
                    
                    // Verificar si el presupuesto está aprobado
                    if (data['Aprobado'] === 1) {
                        // Obtener el nodo de la fila
                        var rowNode = this.node();
                        
                        // Aplicar la clase de fondo verde
                        $(rowNode).addClass('bg-success');
                        
                        // Cambiar el color del texto a blanco en todas las celdas de la fila
                        $(rowNode).find('td').css('color', 'white');
                    } else {
                        // Si el presupuesto no está aprobado, eliminar la clase de fondo verde y restaurar el color del texto
                        $(rowNode).removeClass('bg-success');
                        $(rowNode).find('td').css('color', ''); // Restaura el color del texto al valor predeterminado
                    }
                });
            

            });

            table.on('draw.dt', function() {
                var count = table.rows().count();
                var totalPresupuestos = jsonData.data[0].Presupuestos;

                // Actualizar el mensaje de alerta
                $('#alert_text').html('. Hay ' + count + ' presupuestos de ' + totalPresupuestos + ' solicitados');
            });

        
            $('#ordendecompra_aprobar').click(function() {
                var count = parseInt(table.rows().count(), 10); // Convertir a númerotable.rows().count();
                var totalPresupuestos = parseInt(jsonData.data[0].Presupuestos, 10); 
                var tieneAprobado = false;
                table.rows().every(function() {
                    var data = this.data();
                    if (data['Aprobado'] === 1) {
                        tieneAprobado = true;
                        return false; // Detener la iteración
                    }
                });

                var idOrden=$('#id_t').val();

                if(totalPresupuestos==0){
                    
                    console.log('id',idOrden);

                    $('#idOrden_estado_aprobar').val(idOrden);
                    $('#aprobar_orden_title').html('Orden de Compra #'+idOrden);
                    $('#ordendecompra_new').modal('hide');
                    $('#aprobar_orden').modal('show');

                }else if (count < totalPresupuestos) {
                
                    console.log('error', 'Faltan Presupuestos');
                
                    $.NotificationApp.send("Error !","Faltan Presupuestos.","bottom-right","#FFFFFF","warning");  

                } else if (!tieneAprobado) {

                    console.log('ERROR', 'Debe haber al menos 1 presupuesto aprobado');

                    $.NotificationApp.send("Error !","Debe haber al menos 1 presupuesto aprobado.","bottom-right","#FFFFFF","warning"); 
                
                } else {
                    
                    $('#idOrden_estado_aprobar').html(idOrden);
                    $('#aprobar_orden_title').html('Orden de Compra #'+idOrden);
                    $('#ordendecompra_new').modal('hide');
                    $('#aprobar_orden').modal('show');

                }

            });

        },
        error: function(xhr, status, error) {
            console.error("Error al obtener la orden de compra:", error);
        }
    });
}

$('#aprobar_presupuesto').on('hidden.bs.modal', function () {
    
    $('#ordendecompra_new').modal('show');
    var table= $('#presupuestos').DataTable();
    table.ajax.reload(null,false);

});

function delete_oc(id){

$('#warning-alert-modal').modal('show');

$('#warning-alert-modal_text').html("¿Estás seguro de que deseas eliminar la orden de compra " + id + "?");

// Si el usuario confirmó la eliminación, llama a la función 
$('#warning-alert-modal_button_ok').click(function(){

    var datatable = $('#ordendecompra').DataTable();
    $.ajax({
        data: {'OrdenDeCompra_delete': 1, 'delete_oc': id},
        url: 'Procesos/php/ordendecompra.php',
        type: 'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            if (jsonData.success == 1) {
                $.NotificationApp.send("Registro Eliminado !", "Se han realizado cambios.", "bottom-right", "#FFFFFF", "success");  
                
                datatable.ajax.reload();
                calcularTotalPrecios();
            } else {
                // $.NotificationApp.send("Error!", jsonData.error, "bottom-right", "#FFFFFF", "success");  
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener la orden de compra:", error);
        }
    });
});
}


$(document).ready(function(){


    var filtro = '';

    var datatable = $('#ordendecompra').DataTable({
        paging: true,
        searching: true,
        bInfo: false,        
        ajax: {            
            url: "../Proveedores/Procesos/php/ordendecompra.php",
            data: function() {
                return {'OrdenesDeCompra': 1, 'filtro': filtro}; // Aquí se incluye la variable filtro
            },
            type: 'post'
        },
        columns: [            
            {data: "id"},    
            {data: "Fecha",              
                render: function(data, type, row) {
                    var Fecha = row.Fecha.split('-').reverse().join('.');
                    return `<td><span style="display: none;">${row.Fecha}</span>${Fecha}<br><span class="badge badge-primary">${row.UsuarioCarga}</span></td>`;
                }
            },
            {data: "TipoDeOrden"},
            {data: "Motivo",
                render: function (data, type, row) {
                    return "<td style='max-width: 15px;'>" + row.Motivo + "</td>";
                }
            },
            {data: "Precio",
                render: $.fn.dataTable.render.number('.', ',', 2, '$ ')
            },
            {data: "FechaOrden",
                render: function(data, type, row) {
                    var FechaOrden = row.FechaOrden.split('-').reverse().join('.');
                    return '<td><span style="display: none;">' + row.FechaOrden + '</span>' + FechaOrden + '</td>';
                }
            },
            {data: "FechaAprobado",
            render: function(data, type, row) {
                var FechaOrden = row.FechaAprobado.split('-').reverse().join('.');
                return '<td><span style="display: none;">' + row.FechaAprobado + '</span>' + FechaOrden + '</td>';
            }
        },

            {data: "Estado",
                render: function(data, type, row){
                    if(row.Estado == 'Aceptada') {
                        return '<td><span class="badge badge-info">Aceptada</span><br>'+
                                '<span class="badge badge-info">'+row.Presupuestos+' Presupuestos </span></td>';
                    } else if(row.Estado == 'Aprobada') {
                        return '<td><span class="badge badge-success text-white">Aprobada</span><br>'+
                        '<td><span style="cursor:pointer" class="badge badge-dark text-white">'+row.CodigoAprobacion+'</span></td>';
                    } else if(row.Estado == 'Rechazada') {
                        return '<td><span class="badge badge-danger">Rechazada</span></td>';
                    } else if(row.Estado == 'Cargada') {
                        return '<td><span class="badge badge-secondary">Cargada</span></td>';
                    } else {
                        return '<td></td>';
                    }
                }
            },
            {data: null,
                
            render: function(data,type,row){
                
                if (row.Estado == 'Cargada') {

                    return '<i onclick="edit_oc('+row.id+')" class="mdi mdi-18px mdi-pencil-outline text-warning"></i><i  onclick="delete_oc('+row.id+')" class="ml-2 mdi mdi-18px mdi-trash-can-outline text-danger"></i>';
                
                } else if(row.Estado == 'Aceptada') {

                    return '<tr><i onclick="add_pres('+row.id+')" class="mdi mdi-18px mdi-file-powerpoint text-success"></i>'+
                           '<i onclick="delete_oc('+row.id+')" class="ml-2 mdi mdi-18px mdi-trash-can-outline text-danger"></i></tr>';
                
                } else{

                    return '<td></td>';
                
                }
                
            }
            }
        ],
        columnDefs: [
            {
                targets: [3], // Índice de la columna que deseas justificar (0-indexed)
                className: 'text-justify' // Clase CSS que aplica justificación de texto
            }
        ]
    });

    // Calcular la suma total de los precios después de que se carguen los datos en el DataTable
    calcularTotalPrecios();

    $('#filtro_cargadas').click(function(){
        filtro = 'Cargada';
        datatable.ajax.reload();
        $('#ordendecompra_title').html('ORDENES DE COMPRA EN ESTADO # '+filtro);
    });

    $('#filtro_aceptadas').click(function(){
        filtro = 'Aceptada';
        datatable.ajax.reload();
        $('#ordendecompra_title').html('ORDENES DE COMPRA EN ESTADO # '+filtro);
    });
    
    $('#filtro_aprobadas').click(function(){
        filtro = 'Aprobada';
        datatable.ajax.reload();        
        $('#ordendecompra_title').html('ORDENES DE COMPRA EN ESTADO # '+filtro);
    });

    $('#filtro_pagadas').click(function(){
        filtro = 'Pagada';
        datatable.ajax.reload();
        $('#ordendecompra_title').html('ORDENES DE COMPRA EN ESTADO # '+filtro);
    });

    $('#filtro_rechazadas').click(function(){
        filtro = 'Rechazada';
        datatable.ajax.reload();
        $('#ordendecompra_title').html('ORDENES DE COMPRA EN ESTADO # '+filtro);
    });



$('#filtro_new').click(function(){
    $('#Formulario_new')[0].reset();
    $('#presupuestos').css('display','none');
    $('#presupuestos_agregar').css('display','none');
    $('#ordendecompra_aprobar').css('display','none');
    $('#ordendecompra_estado_aceptar').css('display','none');
    $('#ordendecompra_aceptar').css('display','inline');

    $('#titulo_t').prop('readonly', false);
    $('#tipodeorden_t').prop('disabled', false);
    $('#motivo_t').prop('readonly', false);
    $('#precio_t').prop('readonly', false);
    $('#fechadeorden_t').prop('readonly', false);
    $('#observaciones_t').prop('readonly', false);
    $(".modal-header").removeClass().addClass('modal-header modal-colored-header bg-warning');

        // Realizar la solicitud AJAX para obtener datos de la orden de compra
        $.ajax({
            data: {'OrdenDeCompra_id':1},
            url:'Procesos/php/ordendecompra.php',
            type:'post',
            success: function(response) {
                var jsonData = JSON.parse(response);
                $('#id_t').val(jsonData.id);
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener la orden de compra:", error);
            }
        });
    
        // Realizar la solicitud AJAX para obtener las cuentas
        $.ajax({
            data: {'OrdenDeCompra_cuentas':1},
            url: "Procesos/php/ordendecompra.php",
            type: "POST",
            success: function(response) {
                try {
                    // Parsear la respuesta JSON
                    var options = JSON.parse(response);
                    
                    // Obtener el elemento select
                    var select = $("#tipodeorden_t");
                    
                    // Limpiar el select antes de agregar nuevas opciones
                    select.empty();
                    
                    // Agregar las opciones al select
                    options.forEach(function(option) {
                        // Escapar los datos antes de insertarlos en el DOM para evitar XSS
                        var safeOption = $("<option>").text(option).val(option);
                        select.append(safeOption);
                    });
                } catch (error) {
                    console.error("Error al parsear las opciones de las cuentas:", error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener las cuentas:", error);
            }
        });
    });

    $('#ordendecompra_aceptar').click(function(){

        var titulo=$('#titulo_t').val();
        var tipodeorden=$('#tipodeorden_t').val();
        var motivo=$('#motivo_t').val()
        var precio =$('#precio_t').val();
        var fechaorden = $('#fechadeorden_t').val();
        var observaciones = $('#observaciones_t').val();
        var fechaaprobado=$('#fechaaprobado_t').val();
        $.ajax({
            data: {'OrdenDeCompra_new':1,'Titulo':titulo,'TipoDeOrden':tipodeorden,'Motivo':motivo,'Precio':precio,'FechaOrden':fechaorden,'Observaciones':observaciones,'FechaAprobado':fechaaprobado},
            url:'Procesos/php/ordendecompra.php',
            type:'post',
            success: function(response) {
                var jsonData = JSON.parse(response);
                
                if(jsonData.success==1){
                    $.NotificationApp.send("Registro Cargado !","Se han realizado cambios.","bottom-right","#FFFFFF","success");  
                    datatable.ajax.reload();

                    var procedimiento='OC';
                    var asunto = 'Se dio el alta una Orden de Compra';
                    var mensaje = 'Generado';

                    enviarmail(procedimiento,jsonData.id,asunto,mensaje);
                    
                    $("#ordendecompra_new").modal('hide');

                }else{
                    
                    $.NotificationApp.send("Error !",jsonData.error,"bottom-right","#FFFFFF","warning");  
                    
                }

            },
            error: function(xhr, status, error) {
                console.error("Error al obtener la orden de compra:", error);
            }
        });

    });    
    
// $('#ordendecompra_new').on('hidden.bs.modal', function() {

$('#ordendecompra_cerrar').on('hidden.bs.modal', function() {    
    // Limpiar los campos del formulario al cerrar el modal
    $('#Formulario_new')[0].reset();
});


//AL ABRIR EL FORMULARIO DE PRESUPUESTOS
$('#presupuestos_agregar').click(function(){
    $('#Formulario_presupuesto_new')[0].reset();
    $('#presupuesto_new_title').html('Agregar Nuevo Presupuesto');
    $(".modal-header").removeClass().addClass('modal-header modal-colored-header bg-warning');

    var id=$('#id_t').val();
    console.log('id',id);

    $('#ordendecompra_new').modal('hide');
    $('#presupuesto_new').modal('show');
    $('#id_p').val(id);

    $.ajax({
        data: {'Proveedores_cuentas':1},
        url: "Procesos/php/ordendecompra.php",
        type: "POST",
        success: function(response) {
            try {
                // Parsear la respuesta JSON
                var options = JSON.parse(response);
                
                // Obtener el elemento select
                var select = $("#proveedor_p");
                
                // Limpiar el select antes de agregar nuevas opciones
                select.empty();
                
                // Agregar las opciones al select
                options.forEach(function(option) {
                    // Escapar los datos antes de insertarlos en el DOM para evitar XSS
                    var safeOption = $("<option>").text(option).val(option);
                    select.append(safeOption);
                });
            } catch (error) {
                console.error("Error al parsear las opciones de las cuentas:", error);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener las cuentas:", error);
        }
    });
})

//AL ACEPTAR EL FORMULARIO DE PRESUPUESTOS

$('#presupuesto_aceptar').click(function(){

    var idOrden=$('#id_p').val();
    var fecha=$('#fecha_p').val();
    var proveedor=$('#proveedor_p').val()
    var descripcion =$('#descripcion_p').val();
    var formadepago = $('#formadepago_p').val();
    var cantidad = $('#cantidad_p').val();
    var total = $('#total_p').val();
    var observaciones=$('#observaciones_p').val();


    $.ajax({
        data: {'Presupuestos_new':1,'IdOrden':idOrden,'Fecha':fecha,'Proveedor':proveedor,'Descripcion':descripcion,'FormaDePago':formadepago,
    'Cantidad':cantidad,'Total':total,'Observaciones':observaciones},
        url:'Procesos/php/ordendecompra.php',
        type:'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            
            if(jsonData.success==1){
                $.NotificationApp.send("Registro Cargado !","Se han realizado cambios.","bottom-right","#FFFFFF","success");  
                datatable.ajax.reload();

                $('#presupuesto_new').modal('hide');

                var procedimiento='PO';
                var asunto = 'Nuevo Presupuesto Cargado';
                var mensaje = 'Cargado';
                
                enviarmail(procedimiento,jsonData.id,asunto,mensaje);

            }else{
                
                $.NotificationApp.send("Error !",jsonData.error,"bottom-right","#FFFFFF","warning");  
                
            }


        },
        error: function(xhr, status, error) {
            console.error("Error al obtener la orden de compra:", error);
        }
    });

});
 
$('#ordendecompra_estado_aceptar').click(function(){

var idOrden=$('#id_t').val();    

$('#aceptar_orden').modal('show');

$('#idOrden_estado_aceptar').val(idOrden);

$('#ordendecompra_new').modal('hide');

});

$('#aceptar_orden_ok').click(function(){

    var np=$('#aceptar_orden_cant_presupuestos').val();
    var id=$('#idOrden_estado_aceptar').val();

    $.ajax({
        data: {'Aceptar_orden_ok':1,'NPresup':np,'idOrden':id},
        url:'Procesos/php/ordendecompra.php',
        type:'post',
        success: function(response) {
        var jsonData = JSON.parse(response);
            
            if(jsonData.success==1){

                var procedimiento='AO';
                var asunto = 'Aceptamos la Orden de Compra';
                var mensaje = 'Aceptado';
                var cantidad = np;
            
                enviarmail(procedimiento,id,asunto,mensaje,cantidad);

                $('#aceptar_orden').modal('hide');
                var datatable = $('#ordendecompra').DataTable();
                datatable.ajax.reload();
            
            }
        }
    });


});

//APROBAR ORDEN
$('#aprobar_orden_ok').click(function(){
    var fecha_aprobada=$('#aprobar_orden_fecha').val();
    var idOrden=$('#idOrden_estado_aprobar').val();

    if(fecha_aprobada){

        $.ajax({
            data: {'ordendecompra_aprobar':1,'idOrden':idOrden,'FechaAprobado':fecha_aprobada},
            url:'Procesos/php/ordendecompra.php',
            type:'post',
            success: function(response) {
                var jsonData = JSON.parse(response);
                
                if(jsonData.success==1){

                    $.NotificationApp.send("Éxito !","Se Aprobó la Orden de Compra.","bottom-right","#FFFFFF","success");
                    $("#ordendecompra_new").modal('hide');
                    $('#aprobar_orden').modal('hide');
                    $('#ordendecompra').DataTable().ajax.reload();
                    calcularTotalPrecios();
                    var procedimiento='APO';
                    var asunto = 'Aprobamos una Orden de Compra';
                    var mensaje = 'aprobado';
                                                    
                    enviarmail(procedimiento,id,asunto,mensaje);

                }                            
            }
        });


    }else{
    
        $.NotificationApp.send("Error !","Fecha de Aprobacion no puede ser NULL.","bottom-right","#FFFFFF","waring");  
    
    }
});


$('#observaciones_agregar').click(function(){

   $('#observar_presupuesto').modal('show');
   $('#ordendecompra_new').modal('hide');

});

$('#observar_presupuesto_ok').click(function(){
    var obs=$('#observar_presupuesto_text').val();
    var id=$('#id_t').val();

    $.ajax({
        data: {'Observaciones':1,'obs':obs,'id':id},
        url:'Procesos/php/ordendecompra.php',
        type:'post',
        success: function(response) {
            var jsonData = JSON.parse(response);
            $('#observar_presupuesto').modal('hide');
            $('#ordendecompra_new').modal('show');
         
            if(jsonData.success==1){

                $('#observaciones_t').val(jsonData.Observaciones);
                var procedimiento='OB';
                var asunto = 'Nueva Observacion en Orden de Compra '+id;
                var mensaje = obs;
                                                
                enviarmail(procedimiento,id,asunto,mensaje);


                $.NotificationApp.send("Exito !","Mail enviado con éxito.","bottom-right","#FFFFFF","success");  
            }else{
                $.NotificationApp.send("Error !","No se pudo enviar el mail.","bottom-right","#FFFFFF","warning");  
            }
        }
    });

});

$('#observar_presupuesto').on('hidden.bs.modal', function () {
    
    $('#ordendecompra_new').modal('show');

});

});
