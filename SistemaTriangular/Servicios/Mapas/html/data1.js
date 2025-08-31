$.ajax({
    data: dato,
    url:'php/tabla.php',
    type:'post',
    success: function(response)
       {
  var jsonData = JSON.parse(response);
    
   }
});

