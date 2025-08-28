$(document).ready(function() {
        $('#info-alert-modal').modal('show');
});

//SELECT CLIENTES
  $.ajax({
    data: {
      'DatosDeposito': 1
    },
    type: "POST",
    url: "https://www.caddy.com.ar/Warehouse.php",
    beforeSend: function(){
        // $('#info-alert-modal').modal('show');
    },

    success: function(response) {
      $('.selector-cliente select').html(response).fadeIn();
      $('#dashboard-content').css('display','block');

    }
  });
  