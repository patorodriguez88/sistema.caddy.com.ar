function getCleanedString(cadena){
   // Definimos los caracteres que queremos eliminar
   var specialChars = "!@#$^&%*()+=-[]\/{}|:<>?,.";

   // Los eliminamos todos
   for (var i = 0; i < specialChars.length; i++) {
       cadena= cadena.replace(new RegExp("\\" + specialChars[i], 'gi'), '');
   }   

   // Lo queremos devolver limpio en minusculas
//    cadena = cadena.toLowerCase();

   // Quitamos espacios y los sustituimos por _ porque nos gusta mas asi
   cadena = cadena.replace(/ /g," ");

   // Quitamos acentos y "ñ". Fijate en que va sin comillas el primer parametro
   cadena = cadena.replace(/á/gi,"a");
   cadena = cadena.replace(/é/gi,"e");
   cadena = cadena.replace(/í/gi,"i");
   cadena = cadena.replace(/ó/gi,"o");
   cadena = cadena.replace(/ú/gi,"u");
   cadena = cadena.replace(/ñ/gi,"n");

  return cadena;
}
var selected_device;
var devices = [];
function setup()
{
	//Get the default device from the application as a first step. Discovery takes longer to complete.
	BrowserPrint.getDefaultDevice("printer", function(device)
			{
		
				//Add device to list of devices and to html select element
				selected_device = device;
				devices.push(device);
				var html_select = document.getElementById("selected_device");
				var option = document.createElement("option");
				option.text = device.name;
				html_select.add(option);
				
				//Discover any other devices available to the application
				BrowserPrint.getLocalDevices(function(device_list){
					for(var i = 0; i < device_list.length; i++)
					{
						//Add device to list of devices and to html select element
						var device = device_list[i];
						if(!selected_device || device.uid != selected_device.uid)
						{
							devices.push(device);
							var option = document.createElement("option");
							option.text = device.name;
							option.value = device.uid;
							html_select.add(option);
						}
					}
					
				}, function(){alert("Error getting local devices")},"printer");
				
			}, function(error){
// 				alert('Error'+error);
			})
}
function getConfig(){
	BrowserPrint.getApplicationConfiguration(function(config){
		alert(JSON.stringify(config))
	}, function(error){
		alert(JSON.stringify(new BrowserPrint.ApplicationConfiguration()));
	})
}
var errorCallback = function(errorMessage){
	alert("Error: " + errorMessage);	
}
function readFromSelectedPrinter()
{

	selected_device.read(readCallback, errorCallback);
	
}
function getDeviceCallback(deviceList)
{
	alert("Devices: \n" + JSON.stringify(deviceList, null, 4))
}

function onDeviceSelected(selected)
{
 
	for(var i = 0; i < devices.length; ++i){
		if(selected.value == devices[i].uid)
		{
			selected_device = devices[i];
			return;
		}
	}
}

$("#rotulos-modal").on('show.bs.modal', function(e) {
  let triggerLink = $(e.relatedTarget);
  let id = triggerLink[0].dataset['id'];
//   console.log('id',id);
  if(id!=null){
    $("#body-rotulos").html('Se imprimirá el rótulo del Código '+id);
    $('#imp_rot').show();
    $('#imp_rot_rec').hide();

$('#imp_rot').click(function writeToSelectedPrinter(){	
  
  $.ajax({
    data:{'Rotulo':1,'cs':id},
    type: "POST",
    url: "https://www.caddy.com.ar/SistemaTriangular/Ticket/Procesos/php/datos.php",
    success: function(response)
    {
    var jsonData = JSON.parse(response);
    for(var i = 1; i <= jsonData.data[0].Cantidad; i++)
					{
          var d=jsonData.data[0];  
          var p=jsonData.posicion[0];  
//             console.log('posicion',p);
            var flex='^XA'+
            '^CI28'+
            '^LH0,10'+
            '^FX  Is Product  ^FS'+
            '^FX  Quantity  ^FS'+
            '^FO30,120^A0N,70,70^FB160,1,0,C^FD'+i+'^FS'+
            '^FX Logo Caddy^FS^'+
            '^FO15,5^ILE:LOGOCADD.GRF^FS'+        
            '^FO35,200^A0N,25,25^FB150,1,0,C^FH^FDCantidad^FS'+
            '^FX  Product title  ^FS'+
            '^FO250,25^A0N,50,50^FB570,1,-1^FH^FD'+ d.LocalidadDestino +'^FS'+
            '^FO700,25^A0N,50,50^FB570,1,-1^FH^FD'+ p +'^FS'+
            '^FX  Variations  ^FS'+
            '^FO190,70^A0N,20,20^FB570,1,-1^FDDestino^FS'+    
            '^FO190,95^A0N,24,24^FB570,1,-1^FDNombre: '+ d.ClienteDestino+'^FS'+
            // '^FO201,95^A0N,24,24^FB570,1,-1^FH^FDNombre:^FS'+
            '^FO190,120^A0N,20,20^FB570,1,-1^FDDireccion: '+ d.DomicilioDestino+'^FS'+
            // '^FO201,120^A0N,24,24^FB570,1,-1^FH^FDDireccion: ^FS'+
            '^FO190,145^A0N,24,24^FB570,1,-1^FDRecorrido: '+ d.Recorrido +'^FS'+
            // '^FO191,145^A0N,24,24^FB570,1,-1^FH^FDRecorrido:^FS'+
            '^FX SKU ^FS'+
            '^FO200,190^A0N,30,30^FDSKU: ^FS'+
            '^FO265,192^A0N,25,25^FB510,1,-1^FH^FD'+ d.id +'^FS'+
            '^FO0,225^GB850,2,1^FS'+
            '^FX Order id ^FS'+
            '^FO40,245^A0N,28,28^FDN.Venta: ^FS'+
            '^FO41,245^A0N,28,28^FDN.Venta: ^FS'+
            '^FO130,249^A0N,25,25^FD^FS'+
            '^FO192,245^A0N,30,30^FD'+ d.NumeroVenta +'^FS'+
            '^FO193,245^A0N,30,30^FD'+ d.NumeroVenta +'^FS'+
            '^FX Tracking number ^FS'+
            '^FO299,245^A0N,28,28^FDTracking: ^FS'+
            '^FO300,245^A0N,28,28^FDTracking: ^FS'+
            '^FO410,246^A0N,26,26^FD'+ d.CodigoSeguimiento +'^FS'+
            '^FO0,300^GB850,1,1^FS'+
            '^LH0,320^FX  HEADER  ^FS'+
            '^FO120,0^A0N,20,20^FH^FDOrigen^FS'+
            '^FO120,22^A0N,24,24^FH^FDNombre: #'+ d.IngBrutosOrigen+ ' ' +d.RazonSocial +'^FS'+
            '^FO120,65^A0N,24,24^FB660,2,0,L^FH^FDDireccion Destino: '+ d.DomicilioOrigen +'^FS'+
            '^FO120,100^A0N,24,24^FB660,2,0,L^FH^FD  CP 1437^FS'+
            '^FO120,135^A0N,24,24^FDN.Venta: ^FS'+
            '^FO255,132^A0N,27,27^FD'+ d.NumeroVenta +'^FS'+
            '^FO500,135^A0N,24,24^FDSKU TC: ^FS'+
            '^FO652,132^A0N,27,27^FD'+ d.id +'^FS^FX 1 Horizontal Line ^FS^FO0,170^GB850,0,2^FS'+
            '^FO0,185^A0N,48,48^FB800,1,0,C^FDCaddy Yo lo llevo!^FS'+
            '^FX 2 Horizontal Line ^FS'+
            '^FO0,235^GB850,0,2^FS'+
            '^FX QR Code ^FS'+
            '^FO10,250^A0N,16,18^FDPodes seguir tu envío en nuestra web con el Código '+ d.CodigoSeguimiento +' o escaneando con tu teléfono el QR.^FS'+    
            '^FO260,270^BY4,4,0^BQN,2,4^FDLA,{\"id\":\"https://www.caddy.com.ar/seguimiento.html?codigo='+d.CodigoSeguimiento+'\",\"sender_id\":3987654312,\"hash_code\":\"fyePAxtasdOM/kZgZZDSAH+h1JBckgknsg2R3754ERKI=\",\"security_digit\":\"0\"}^FS'+
            '^FO10,290^A0N,20,20^FDCódigo Wepoint:^FS'+
            '^FO20,310^BY3,2,0^BQN,2,4^FDLA,'+d.CodigoSeguimiento+'^FS'+
            '^FO10,440^A0N,20,20^FDwww.caddy.com.ar^FS'+
            '^FO500,440^A0N,20,20^FDUsuario: '+d.Usuario+'^FS'+
            '^XZ';

             selected_device.send(flex, undefined, errorCallback);
            }
          }
       });
   });
   
  $('#rotulos-modal').modal('hide');  

  }else{
   var rec=$('#idRecorridoPendientes').html(); 
  $("#body-rotulos").html('Se imprimiran todos los rotulos del recorrido '+rec);
  $('#imp_rot').hide();
  $('#imp_rot_rec').show();
  }
});

//IMPRIME TODO UN RECORRIDO
$('#imp_rot_rec').click(function writeToSelectedPrinter(id){	
var rec=$('#idRecorridoPendientes').html();   
$.ajax({
    data:{'RotuloRec':1,'rec':rec},
    type: "POST",
    url: "https://www.caddy.com.ar/SistemaTriangular/Ticket/Procesos/php/datos.php",
    success: function(response)
    {
    var jsonData = JSON.parse(response);

     

   for(var i = 0; i <= jsonData[0]; i++){   
 
      for(var c = 1; c <= jsonData.data[i].Cantidad; c++)
					{
          var d=jsonData.data[i];  
          var p=jsonData.posicion[i]; 
            
            
            var flex='^XA'+
            '^CI28'+
            '^LH0,10'+
            '^FX  Is Product  ^FS'+
            '^FX  Quantity  ^FS'+
            '^FO30,120^A0N,70,70^FB160,1,0,C^FD'+c+'^FS'+
            '^FX Logo Caddy^FS^'+
            '^FO15,3^ILE:LOGOCADD.GRF^FS'+        
            '^FO35,200^A0N,25,25^FB150,1,0,C^FH^FDCantidad^FS'+
            '^FX  Product title  ^FS'+
            '^FO250,25^A0N,50,50^FB570,1,-1^FH^FD'+ d.LocalidadDestino +'^FS'+
            '^FO700,25^A0N,50,50^FB570,1,-1^FH^FD'+ p +'^FS'+    
            '^FX  Variations  ^FS'+
            '^FO250,70^A0N,20,20^FB570,1,-1^FDDestino^FS'+    
            '^FO190,95^A0N,24,24^FB570,1,-1^FDNombre: '+ d.ClienteDestino+'^FS'+
            '^FO191,95^A0N,24,24^FB570,1,-1^FH^FDNombre:^FS'+
            '^FO190,120^A0N,23,23^FB570,1,-1^FDDireccion: '+ d.DomicilioDestino+'^FS'+
            '^FO191,120^A0N,23,23^FB570,1,-1^FH^FDDireccion: ^FS'+
            '^FO190,145^A0N,24,24^FB570,1,-1^FDRecorrido: '+ d.Recorrido +'^FS'+
            '^FO191,145^A0N,24,24^FB570,1,-1^FH^FDRecorrido:^FS'+
            '^FX SKU ^FS'+
            '^FO200,190^A0N,30,30^FDSKU: ^FS'+
            '^FO265,192^A0N,25,25^FB510,1,-1^FH^FD'+ d.id +'^FS'+
            '^FO0,225^GB850,2,1^FS'+
            '^FX Order id ^FS'+
            '^FO40,245^A0N,28,28^FDN.Venta: ^FS'+
            '^FO41,245^A0N,28,28^FDN.Venta: ^FS'+
            '^FO130,249^A0N,25,25^FD^FS'+
            '^FO192,245^A0N,30,30^FD'+ d.NumeroVenta +'^FS'+
            '^FO193,245^A0N,30,30^FD'+ d.NumeroVenta +'^FS'+
            '^FX Tracking number ^FS'+
            '^FO299,245^A0N,28,28^FDTracking: ^FS'+
            '^FO300,245^A0N,28,28^FDTracking: ^FS'+
            '^FO410,246^A0N,26,26^FD'+ d.CodigoSeguimiento +'^FS'+
            '^FO0,300^GB850,1,1^FS'+
            '^LH0,320^FX  HEADER  ^FS'+
            '^FO120,0^A0N,20,20^FH^FDOrigen^FS'+
            '^FO120,22^A0N,24,24^FH^FDNombre: #'+ d.IngBrutosOrigen+ ' ' +d.RazonSocial +'^FS'+
            '^FO120,65^A0N,24,24^FB660,2,0,L^FH^FDDireccion Destino: '+ d.DomicilioOrigen +'^FS'+
            '^FO120,100^A0N,24,24^FB660,2,0,L^FH^FD  CP 1437^FS'+
            '^FO120,135^A0N,24,24^FDN.Venta: ^FS'+
            '^FO255,132^A0N,27,27^FD'+ d.NumeroVenta +'^FS'+
            '^FO500,135^A0N,24,24^FDSKU TC: ^FS'+
            '^FO652,132^A0N,27,27^FD'+ d.id +'^FS^FX 1 Horizontal Line ^FS^FO0,170^GB850,0,2^FS'+
            '^FO0,185^A0N,48,48^FB800,1,0,C^FDCaddy Yo lo llevo!^FS'+
            '^FX 2 Horizontal Line ^FS'+
            '^FO0,235^GB850,0,2^FS'+
            '^FX QR Code ^FS'+
            '^FO10,250^A0N,16,18^FDPodes seguir tu envío en nuestra web con el Código '+ d.CodigoSeguimiento +' o escaneando con tu teléfono el QR.^FS'+    
            '^FO260,270^BY4,4,0^BQN,2,4^FDLA,{\"id\":\"https://www.caddy.com.ar/seguimiento.html?codigo='+d.CodigoSeguimiento+'\",\"sender_id\":3987654312,\"hash_code\":\"fyePAxtasdOM/kZgZZDSAH+h1JBckgknsg2R3754ERKI=\",\"security_digit\":\"0\"}^FS'+
            '^FO10,440^A0N,20,20^FDwww.caddy.com.ar^FS'+
            '^FO500,440^A0N,20,20^FDUsuario: '+d.Usuario+'^FS'+
            '^XZ';

             selected_device.send(flex, undefined, errorCallback);
            }
          }
        
        }


    });
   $('#rotulos-modal').modal('hide');  
});

$('#imp_rot_rec_700x200').click(function writeToSelectedPrinter(id){	
    // var rec=$('#idRecorridoPendientes').html();   
    // $.ajax({
    //     data:{'RotuloGaby':1,'rec':rec},
    //     type: "POST",
    //     url: "https://www.caddy.com.ar/SistemaTriangular/Ticket/Procesos/php/datos.php",
    //     success: function(response)
    //     {
    //     var jsonData = JSON.parse(response);
    
    //      console.log('data',jsonData.data.length);
    
    //    for(var i = 0; i <= jsonData.data.length; i++){   
    //     var d=jsonData.data[i];  

    //     // let cadena = d.DomicilioDestino;
    //     // let Domicilio_1 = getCleanedString(cadena.substring(0, 30));
    //     // let Domicilio_2 = getCleanedString(cadena.substring(30, 60));

    //     // let Nombre_cadena=d.ClienteDestino;
    //     let Nombre_cadena=d.nombrecliente;
    //     let nombre_1 = getCleanedString(Nombre_cadena.substring(0, 32));
    //     let nombre_2 = getCleanedString(Nombre_cadena.substring(32, 60));
    //     // console.log('Cliente',Nombre_cadena);
    //             var flex='^XA'+
    //             '^FO210, 50^ADN, 30, 7^FD '+nombre_1+'^FS'+
    //             '^FO210, 50^ADN, 30, 7^FD '+nombre_1+'^FS'+
    //             '^FO210, 50^ADN, 30, 7^FD '+nombre_1+'^FS'+
    //             '^FO210, 50^ADN, 30, 7^FD '+nombre_1+'^FS'+
    //             '^FO210, 80^ADN, 30, 7^FD '+nombre_2+'^FS'+
    //             '^FO210, 80^ADN, 30, 7^FD '+nombre_2+'^FS'+
    //             '^FO210, 80^ADN, 30, 7^FD '+nombre_2+'^FS'+
    //             '^FO210, 80^ADN, 30, 7^FD '+nombre_2+'^FS'+
    //             // '^FO150, 110^ADN, 11, 7^FD'+Domicilio_1+'^FS'+
    //             // '^FO150, 140^ADN, 11, 7^FD'+Domicilio_2+'^FS'+
    //             // '^FO150, 170^ADN, 11, 7^FD '+d.CodigoSeguimiento+' ^FS'+
    //             // '^FO150, 140^ADN, 11, 7'+
    //             // '^BCN, 150, Y, Y, N^FD corptectr>147896325 ^FS'+
    //             '^XZ';

    //             selected_device.send(flex, undefined, errorCallback);

    //           }
            
    //         }
    
    
    //     });
    //    $('#rotulos-modal').modal('hide');  
    });
    

window.onload = setup;