// $(document).on(function(){
    $.ajax({
        data:{'Renderize':1},
        type: "POST",
        url: "Proceso/php/demo-calendar1.php",
        beforeSend: function() {
        // setting a timeout
        // $('#info-alert-modal').modal('show');
        },
        success: function(response)
        {
        var jsonData= JSON.parse(response);
        // $('#info-alert-modal').modal('hide');
        }
    });
// })