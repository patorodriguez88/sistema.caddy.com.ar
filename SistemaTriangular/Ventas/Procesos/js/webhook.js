
function enviar_webhook_woocomerce(checked){

    // console.log(checked);
        
if (checked != 0) {
    
    $.ajax({
        data:{'id':checked},
        type: "POST",
        url: "Procesos/php/woocomerce.php",
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            console.log(jsonData);

        }
    });

    }
};

function enviar_webhook_tiendanube(checked){

    console.log(checked);
        
if (checked != 0) {
    
    $.ajax({
        data:{'id':checked},
        type: "POST",
        url: "Procesos/php/tiendanube.php",
        success: function(response)
        {
            var jsonData = JSON.parse(response);
            console.log(jsonData);

        }
    });

    }
};