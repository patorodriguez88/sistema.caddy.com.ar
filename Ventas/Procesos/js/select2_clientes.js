$(document).ready(function(){
    $("#id_origen").select2({
        ajax: { 
            url: "Procesos/php/select2_clientes.php",
            type: "get",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term /* search term */
                };
                
            },
            processResults: function (response) {
                return {
                    results: response
                    
                };
            },
            
            cache: true
        }
    });
    
    $("#id_destino").select2({
        ajax: { 
            url: "Procesos/php/select2_clientes.php",
            type: "get",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term /* search term */
                };
                
            },
            processResults: function (response) {
                return {
                    results: response
                    
                };
            },
            
            cache: true
        }
    });
});

