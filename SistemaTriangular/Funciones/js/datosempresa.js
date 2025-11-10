$(document).ready(function(){
  $.ajax({
        data: {'Empresa':1},
        url:'https://www.caddy.com.ar/SistemaTriangular/Funciones/php/datosempresa.php',
        type:'post',
        success: function (respuesta) {
        var jsonData= JSON.parse(respuesta);  
        $('#Emp_RazonSocial').html(jsonData.data[0].RazonSocial);
        $('#Emp_NombreComercial').html(jsonData.data[0].NombreComercial);
        $('#Emp_Direccion').html(jsonData.data[0].Direccion);  
        $('#Emp_Cuit').html(jsonData.data[0].Cuit);    
        $('#Emp_Telefono').html(jsonData.data[0].Telefono);    
        $('#Emp_Mail').html(jsonData.data[0].Mail);    
        $('#Emp_Web').html(jsonData.data[0].Web);
        $('#Emp_IngresosBrutos').html(jsonData.data[0].IngresosBrutos);  
        $('#Emp_InicioActividades').html(jsonData.data[0].InicioActividades);  
        } 
        });
});
