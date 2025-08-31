$('#dates'). change(function(){

var array = $("#dates").val();
console.log(array);


})
var datatable = $('#recorridos').DataTable({
    dom: 'Bfrtip',
    buttons: ['pageLength', 'copy', 'excel', 'pdf'],
    paging: true,
    searching: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All']
    ],

  ajax: {
       url:"Proceso/php/recorridos.php",
       data:{'Recorridos':1},
       processing: true,
       type:'post',
      },
      columns: [
          {data:"Numero",
           render: function (data, type, row) {

            if(row.Activo==0){
            var color='muted';    
            }else{
            color='black'    
            }
            return `<td><a class="text-${color}">${row.Numero}</td>`;  
            }
          },
          {data:"Nombre",
          render: function (data,type,row){
            if(row.Activo==0){
                var color='muted';    
                }else{
                color='success'    
                }   
          return `<td><b>${row.Nombre}</br><td><i class='mdi mdi-18px mdi-map-marker text-${color}'></i><a class='text-muted'>${row.Zona}</td>`;
          }
        
        
          },
          {data:"Zona",
          render: function (data, type, row) { 
            if(row.Activo==0){
                var color='muted';    
                }else{
                color='success'    
                }
             return `<td><i class="mdi mdi-18px mdi-map-marker-distance text-${color}"></i> <b>${row.Kilometros}</b></td>`;

            }
          },
          {data:"Peajes",
          render: function (data, type, row) { 
            if(row.Activo==0){
                var color='muted';    
                }else{
                color='success'    
                }
               return `<td><i class="mdi mdi-18px mdi-cash-marker text-${color}"></i> <b>${row.Peajes}</b></td>`;
            }  
          },
          {data:"CodigoProductos",
          render: function (data, type, row) {
            if(row.Activo==0){
                var color='muted';    
                }else{
                color='success'    
                }  
              return `<td class="table-action col-xs-3"><a><b>${row.nombrecliente}</b></a><br/><a class="text-primary"  data-toggle="modal" data-target="#modal_seguimiento" data-id="${row.nombrecliente}"data-title="${data.CodigoProductos}" data-fieldname="${data}"><b>${row.CodigoProductos}</b></a></br><span class="badge badge-${color}"> $ ${row.PrecioVenta}</span></td>`;
            }
          },
          {data:"CodigoProductos",
            render: function (data, type, row) {

                return `<td class="table-action col-xs-3"><span  style="cursor:pointer" id="${row.Numero}" onclick="ver_fijos(this.id);"class="badge badge-primary badge-pill">${row.Total}</span>`;
                       

                }          
          },
          {data:"DiaSalida"},
          {data:"Color",
          render: function (data, type, row) {
            if(row.Activo==0){
                var color='muted';    
                }else{
                color=row.Color;    
                }

            return `<td class="table-action"><a class="action-icon"> <i class="mdi mdi-truck" style="color:#${color}"></i></a></td>`; 

            }
          },
          {data:"Activo",
         render: function (data, type, row) {
             if(row.Activo==1){
                 var activo="Activo";
                 var color="success";
             }else{
                 activo="Inactivo";
                 color="danger";
             }
             return '<td><h5><span class="badge badge-'+color+'"> <b>'+ activo+'</b></span></h5></td>';
            }
          },
          {data:"id",
         render: function (data, type, row) {
             if(row.Activo==1){
             var activo="mdi-eye text-success";  
             }else{
             var activo="mdi-eye-off text-danger";  
             }
              return `<td class="table-action"><a id="${row.id}" onclick="modificar(this.id,${row.Activo});" class="action-icon"> <i class="mdi ${activo}"></i></a>`+
                     `<td class="table-action"><a onclick="showmodal(${row.id});" class="action-icon"> <i class="mdi mdi-border-color text-warning"></i></a></td>`;
            }
          },
         
      ]
});

function ver_fijos(i){

    $('#bs-fijos-modal-lg').modal('show');

    var datatable=$('#envios_fijos').DataTable();
    datatable.destroy();

          var datatable = $('#envios_fijos').DataTable({
            paging: true,
            searching: true,        
          ajax: {
               url:"Proceso/php/recorridos.php",
               data:{'VerFijos':1,'id':i},
               processing: true,
               type:'post',
              },
              columns: [
                  {data:"nombre1"},
                  {data:"nombre2"},
                  {data:"id",
                
                  render: function (data, type, row) {

                    return `<td class="table-action col-xs-3"><a style="cursor:pointer" id="${row.id}" onclick="eliminar_fijo(this.id);" ><i class="mdi mdi-18px mdi-trash-can-outline"></i>`;
                           
    
                    }   
                
                }                 
              ]
        });
    }
        
function eliminar_fijo(i){

    // alert(i);

    $('#remove_permanent_warning-header-modal').modal('show');
    
    $('#btn_remove_permanent').click(function(){
        $.ajax({
            data:{'EliminarFijo':1,'id':i},
            url:'Proceso/php/recorridos.php',
            type:'post',
            success: function(response)
            {

            var jsonData = JSON.parse(response);
            if(jsonData.success==1){
            var datatable = $('#envios_fijos').DataTable();
            datatable.ajax.reload();
            var datatable_1 = $('#recorridos').DataTable();
            datatable_1.ajax.reload();
            $('#remove_permanent_warning-header-modal').modal('hide');
            
            }
            }
        });
    })

}


function modificar(i,a){
    // var a=$(this).attr("data-id");
    // alert(a);

    $.ajax({
        data:{'ActivarRecorridos':1,'id':i,'Activo':a},
        url:'Proceso/php/recorridos.php',
        type:'post',
        success: function(response)
         {
          var jsonData = JSON.parse(response);
          var datatable = $('#recorridos').DataTable();
          datatable.ajax.reload();
        //   $.NotificationApp.send("Exito","Recorrido Activado.","bottom-right","success","success");            
        if(a!=1){  
         $.toast({
            heading: 'Listo!',
            text: 'Recorrido Activado',
            position: 'bottom-right',
            stack: false,
            icon: 'success'
         });
         }else{
            $.toast({
                heading: 'Listo!',
                text: 'Recorrido Desactivado',
                position: 'bottom-right',
                stack: false,
                icon: 'error'
             });
         }
        
        }  
    });
}

function showmodal(i){

    $('#id_mod_rec').val(i);

    $.ajax({
        data:{'Rec_datos':1,'Rec':i},
        url:'Proceso/php/recorridos.php',
        type:'post',
        success: function(response)
        {
        $('#standard-modal-rec').modal('show');
        $("#recorrido_ok").css('display','none');
        $("#recorrido_mod_ok").css('display','block');    
      
        var jsonData = JSON.parse(response);

        // Obtén la referencia al elemento del interruptor
        var switchElement = document.getElementById("fijo_switch");

        if(jsonData.data[0].Fijo==0){
         
        // Cambia el estado del interruptor a "false"
        switchElement.checked = false;
  
        }else{
        // Cambia el estado del interruptor a "false"
        switchElement.checked = true;
            
        }

        $('#recorrido_number').val(jsonData.data[0].Numero);
        $('#recorrido_name').val(jsonData.data[0].Nombre);
        $('#recorrido_zone').val(jsonData.data[0].Zona);
        $('#recorrido_km').val(jsonData.data[0].Kilometros);
        $('#recorrido_toll').val(jsonData.data[0].Peajes);        
        
        $('#standard-modal-rec-header').removeClass('modal-header modal-colored-header bg-success');
        $('#standard-modal-rec-header').addClass('modal-header modal-colored-header bg-warning');
        $('#myCenterModalLabel_rec').html('MODIFICAR RECORRIDO NUMERO '+jsonData.data[0].Numero);
        
        var values=jsonData.data[0].DiaSalida.split(",");
            
        for(var i=0;i<values.length;i++){    

            $('#dates').append(`<option value="${values[i]}"selected>${values[i]}</option>`);   
        
        }


        const select = document.querySelector("#recorrido_guest");        
        const option = document.createElement('option');
        option.setAttribute('selected',true);
        const valor = jsonData.data[0].Cliente;
        option.value = valor;
        option.text = `${jsonData.data[0].Cliente} - ${jsonData.data[0].nombrecliente}  (Dir.:${jsonData.data[0].Direccion})`;
        select.appendChild(option);
                
        const select_service = document.querySelector("#recorrido_service");
        const option_service = document.createElement('option');
        option_service.setAttribute('selected',true);
        const valor_service = jsonData.data[0].CodigoProductos;
        option_service.value = valor_service;
        option_service.text = `${jsonData.data[0].CodigoProductos} - ${jsonData.data[0].Titulo}  $ ${jsonData.data[0].PrecioVenta}`;
        select_service.appendChild(option_service);

        $('#recorrido_color').val('#'+jsonData.data[0].Color);

        }  
    });

  
}

//ACEPTAR AGREGAR RECORRIDO

$('#recorrido_ok').click(function(){
    
    var number = $('#recorrido_number').val();
    var name = $('#recorrido_name').val();
    var zone = $('#recorrido_zone').val();
    var km = $('#recorrido_km').val();
    var toll = $('#recorrido_toll').val();
    var guest = $('#recorrido_guest').val();
    var service = $('#recorrido_service').val();
    var color=$('#recorrido_color').val();
    
    $.ajax({
        data:{'AgregarRecorridos':1,'name':name,'zone':zone,'km':km,'toll':toll,'guest':guest,'service':service,'color':color,'number':number},
        url:'Proceso/php/recorridos.php',
        type:'post',
        success: function(response)
        {
        var jsonData = JSON.parse(response);
        var datatable = $('#recorridos').DataTable();
        datatable.ajax.reload();
        $("#standard-modal-rec").modal('hide');    
        }  
    });



});

//ACEPTAR MODIFICAR RECORRIDO

$('#recorrido_mod_ok').click(function(){

    var number = $('#recorrido_number').val();
    var name = $('#recorrido_name').val();
    var zone = $('#recorrido_zone').val();
    var km = $('#recorrido_km').val();
    var toll = $('#recorrido_toll').val();
    var guest = $('#recorrido_guest').val();
    var service = $('#recorrido_service').val();
    var color=$('#recorrido_color').val();
    var id=$('#id_mod_rec').val();
    var dias = $("#dates").val();
    $.ajax({
        data:{'ModificarRecorridos':1,'id':id,'name':name,'zone':zone,'km':km,'toll':toll,'guest':guest,
        'service':service,'color':color,'number':number,'dias':dias},
        url:'Proceso/php/recorridos.php',
        type:'post',
        success: function(response)
        {
        var jsonData = JSON.parse(response);
        if(jsonData.success==1){
        var datatable = $('#recorridos').DataTable();
        datatable.ajax.reload();
        
        $.toast({
            heading: 'Listo!',
            text: 'Recorrido Modificado',
            position: 'bottom-right',
            stack: false,
            icon: 'success'
         });

        }else{
            $.toast({
                heading: 'Error!',
                text: 'El Recorrido no fue modificado',
                position: 'bottom-right',
                stack: false,
                icon: 'error'
             });
   
        }

        $("#standard-modal-rec").modal('hide');
        
        
        }  
    });



});

  $('#agregar_rec_btn').click(function(){
    $("#recorrido_mod_ok").css('display','none');
    $("#recorrido_ok").css('display','block');    
    $(".form-control").val("");  
    $('#standard-modal-rec-header').removeClass('modal-header modal-colored-header bg-warning');
    $('#standard-modal-rec-header').addClass('modal-header modal-colored-header bg-success');
    $('#myCenterModalLabel_rec').html('AGREGAR NUEVO RECORRIDO');

    $("#recorrido_guest").prop("selected", false).trigger("change");
    $("#recorrido_service").prop("selected", false).trigger("change");       

  $.ajax({
        data:{'Rec_num':1},
        url:'Proceso/php/recorridos.php',
        type:'post',
        success: function(response)
        {
        var jsonData = JSON.parse(response);
        $("#recorrido_number").val(jsonData.next_num_rec);

        }  
    });

});