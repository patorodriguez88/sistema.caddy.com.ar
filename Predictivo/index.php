<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
   		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
   		<link href="css/jqueryui.css" type="text/css" rel="stylesheet"/>
        <script>
	       	$(document).ready(function(){ 	
				$( "#Cliente" ).autocomplete({
      				source: "BuscarClientes.php",
      				minLength: 2
    			});
    			
    			$("#Cliente").focusout(function(){
    				$.ajax({
    					url:'Clientes.php',
    					type:'POST',
    					dataType:'json',
    					data:{ Cliente:$('#Cliente').val()}
    				}).done(function(respuesta){
    					$("#Cliente").val(respuesta.Cliente);
    					$("#NdeCliente").val(respuesta.NdeCliente);
    				});
    			});    			    		
			});
        </script>
    </head>
    <body>
        
       	<form>
       		<label for="Cliente">Cliente:</label>
	    	<input type="text" id="Cliente" name="Cliente" value=""/>
		    <label for="NdeCliente">NdeCliente:</label>
		    <input type="text" id="NdeCliente" name="NdeCliente" value=""/>
		</form>
    </body>
</html>
