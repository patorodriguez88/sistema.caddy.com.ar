
orden_alta();

function orden_alta(){
    $.ajax({
        data:{'Alta':1},
        type: "POST",
        url: "Proceso/php/logisticanew.php",
        beforeSend: function() {
        // setting a timeout
        // $('#info-alert-modal').modal('show');
        // $('#info-alert-modal-title').html('Renderizando Tabla Roadmap');
        },
        success: function(response)
        {
        var jsonData= JSON.parse(response);
        $('#numero_orden').val(jsonData.data);
        }
        });    
}

$('#cerrar_orden').click(function(){
    
    var norden=$('#numero_orden').val();
    
    $.ajax({
        data:{'Cerrar_orden':1,'Numero_orden':norden},
        type: "POST",
        url: "Proceso/php/logisticanew.php",
        beforeSend: function() {
        // setting a timeout
        // $('#info-alert-modal').modal('show');
        // $('#info-alert-modal-title').html('Renderizando Tabla Roadmap');
        },
        success: function(response)
        {
        var jsonData= JSON.parse(response);
            
            $('#numero_orden').val(jsonData.dato);
        }
        });    

})
